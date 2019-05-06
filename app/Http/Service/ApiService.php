<?php

namespace App\Http\Service;


/**
 * Api服务
 * Class ApiService
 * @package service
 *
 */
class ApiService
{
    /**
     * 返回成功的操作
     * @param mixed $msg 消息内容
     * @param array $data 返回数据
     * @param boolean $convert 数据类型是否强转
     * @param int $code 返回代码
     * @return array
     */
    public static function success($code = 0, $data = [], $convert = true)
    {
        if ($convert) {
            $data = is_string($data) ? $data : (empty($data) ? null : $data);
        }
        $result = ['code' => $code, 'msg' => '成功', 'data' => $data];
        $return = json_encode($result, JSON_UNESCAPED_UNICODE);
        //self::addAccessLog($return);
        return $return;
    }

    /**
     * 返回失败的请求
     * @param mixed $msg 消息内容
     * @param array $data 返回数据
     * @param boolean $convert 数据类型是否强转
     * @param int $code 返回代码
     * @return array
     */
    public static function error($code, $data = [], $convert = true)
    {
        if ($convert) {
            $data = is_string($data) ? $data : (empty($data) ? null : $data);
        }
        if (!empty(config("code.{$code}"))) {
            $result = ['code' => $code, 'msg' => '失败', 'data' => $data];
        } else {
            $result = ['code' => '40000', 'msg' => '失败', 'data' => $data];
        }
        $return = json_encode($result, JSON_UNESCAPED_UNICODE);
//        self::addAccessLog($return);
        return $return;
    }


    public static function check()
    {
        $isCheck = config('api.is_check');
        $ipLimit = config('api.ip_limit');
        $accessLimit = config('api.access_limit');

        //接收data数据--业务参数
        $param = array_merge(request()->get(), request()->post());

        //公共参数验证
        if (!isset($param['sign'])) {
            return 41001;
        }

        if (!isset($param['timestamp'])) {
            return 41002;
        }

        if (!isset($param['source']) || !in_array($param['source'], ['app', 'robot'])) {
            return 41003;
        }

        //是否开启签名校验
        if ($isCheck) {
            //时间校验
            $timestamp = $param['timestamp'];
            if (($timestamp < time() - 86400) || ($timestamp > time() + 86400)) {
                return 40003;
            }

            //验签
            $sign = $param['sign'];
            unset($param['sign']);

            $method = request()->method();
            $pathinfo = request()->pathinfo();

            $auth_result = self::checkAuth($method, $pathinfo, $param, $sign);
            if ($auth_result === false) {
                return 40002;
            }
        }


        $ip = request()->ip();//客户端ip
        $url = request()->url(true);//接口地址

        //黑名单校验
        if ($ipLimit) {
            $number = (int)\Think\Db::table('api_ip_ask_num')->where(['ip' => $ip])->value('num');
            if ($number >= $ipLimit) {
                return 50001;
            }
        }

        //接口访问次数校验
        if ($accessLimit) {
            $limit = self::is_access_limit($ip, $url);
            if ($limit) {
                return 45001;
            }
        }

        return 0;
    }


    /**
     * 设置Token字段值
     * @param string $token 用户Token
     * @param string $field Token字段名
     * @param string $value Token字段值
     * @return bool
     */
    public static function setTokenField($token, $field, $value)
    {
        $redis_key = config('api.token_prefix') . $token;
        $redis = Cache::store('redis')->handler();
        $redis->hset($redis_key, $field, $value);
        // 更新Token有效期
        $redis->expire($redis_key, 86400 * 30);
        if ($redis->hget($redis_key, $field) != $value) {
            return false;
        }
        return true;
    }

    /**
     * 获取Token字段值
     * @param string $token 用户Token
     * @param string $field Token字段名
     * @return bool|string
     */
    public static function getTokenField($token, $field)
    {
        $redis_key = config('api.token_prefix') . $token;
        $redis = Cache::store('redis')->handler();
        if (!$redis->hexists($redis_key, $field)) {
            return false;
        }
        return $redis->hget($redis_key, $field);
    }

    /**
     * 设置App登陆用户锁
     * @param int $uid 用户ID
     * @param string $token 用户Token
     * @return bool
     */
    public static function setAppUserLock($uid, $token)
    {
        $redis_key = config('api.app_lock_prefix') . $uid;
        $redis = Cache::store('redis')->handler();
        return $redis->set($redis_key, $token) !== false;
    }

    /**
     * 删除App登陆用户锁
     * @param int $uid 用户ID
     * @return bool
     */
    public static function rmAppUserLock($uid)
    {
        $redis_key = config('api.app_lock_prefix') . $uid;
        $redis = Cache::store('redis')->handler();
        if ($redis->exists($redis_key)) {
            return $redis->del($redis_key);
        }
        return true;
    }

    /**
     * App单点登陆情况下获取指定用户Token值
     * @param int $uid 用户ID
     * @return bool|string
     */
    public static function getAppUserToken($uid)
    {
        $redis_key = config('api.app_lock_prefix') . $uid;
        $redis = Cache::store('redis')->handler();
        if ($redis->exists($redis_key)) {
            return $redis->get($redis_key);
        }
        return false;
    }

    /**
     * App单点登陆情况下判断用户是否已经在App端登陆
     * @param int $uid 用户ID
     * @return bool
     */
    public static function isAlreadyLoggedOnApp($uid)
    {
        $redis_key = config('api.app_lock_prefix') . $uid;
        $redis = Cache::store('redis')->handler();
        return $redis->exists($redis_key) && $redis->exists(config('api.token_prefix') . $redis->get($redis_key));
    }

    /**
     * 添加用户Token到会话池
     * @param int $uid
     * @param string $token
     * @return bool
     */
    public static function addTokenPool($uid, $token)
    {
        $redis_key = config('api.user_tokens_prefix') . $uid;
        $redis = Cache::store('redis')->handler();

        // 触发清空无效Token
        if (false !== $result = $redis->sadd($redis_key, $token)) {
            $count = $redis->scard($redis_key);
            if ($count > 8) {
                self::getTokenPool($uid);
            }
        }

        return $result;
    }

    /**
     * 获取用户会话池中的所有有效Token
     * @param int $uid
     * @return array
     */
    public static function getTokenPool($uid)
    {
        $redis_key = config('api.user_tokens_prefix') . $uid;
        $redis = Cache::store('redis')->handler();
        $tokens = $redis->smembers($redis_key);

        foreach ($tokens as $token) {
            if (!self::isValidToken($token)) {
                $redis->srem($redis_key, $token);
            }
        }

        return $redis->smembers($redis_key);
    }

    /**
     * 强制Token失效
     * @param string $token
     * @return mixed
     */
    public static function invalidToken($token)
    {
        $redis_key = config('api.token_prefix') . $token;
        $redis = Cache::store('redis')->handler();
        if ($redis->exists($redis_key)) {
            return $redis->del($redis_key);
        }
        return true;
    }

    /**
     * 强制用户所有Token失效
     * @param int $uid
     * @return bool
     */
    public static function invalidUserTokens($uid)
    {
        $tokens = self::getTokenPool($uid);

        foreach ($tokens as $token) {
            self::invalidToken($token);
        }

        return self::getTokenPool($uid) ? false : true;
    }

    /**
     * 判断Token是否有效
     * @param string $token
     * @return bool
     */
    public static function isValidToken($token)
    {
        $redis_key = config('api.token_prefix') . $token;
        $redis = Cache::store('redis')->handler();
        return $redis->exists($redis_key);
    }

    /**
     * 获取登录后的接口调用Header中的token值
     * @return string
     */
    public static function getToken()
    {
        $header = request()->header();
        $token = isset($header['token']) ? $header['token'] : '';
        return $token;
    }

    /**
     * Token验证
     * @param bool|string $token
     * @return bool|array
     */
    public static function checkToken($token = false)
    {
        $token = $token ? $token : self::getToken();
        if (!$token) {
            return false;
        }

        $redis_key = config('api.token_prefix') . $token;
        $redis = Cache::store('redis')->handler();

        if (!$redis->hexists($redis_key, 'uid')) {
            return false;
        }
        //更新token过期时间
        $redis->expire($redis_key, 30 * 86400);

        return $redis->hgetall($redis_key);
    }

    /**
     * 获取当前用户ID
     * @param bool $member 是否优先获取当前登陆的成员ID
     * @return bool|mixed
     */
    public static function getUserId($member = true)
    {
        $user = self::checkToken();
        if (!$user) {
            return false;
        }
        if (!$member) {
            return $user['uid'];
        }
        // 优先返回当前登陆家庭成员ID
        return isset($user['mid']) ? $user['mid'] : $user['uid'];
    }

    /**
     * 获取当前会话关联的家庭ID
     * @return bool|int
     */
    public static function getFamilyId()
    {
        $user = self::checkToken();
        if (!$user) {
            return false;
        }
        // 优先返回当前登陆家庭成员ID
        return isset($user['fid']) ? $user['fid'] : false;
    }

    /**
     * 生成token
     * @return string
     */
    public static function generateToken()
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        } else {
            mt_srand((double)microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
        }
    }

    /**
     * 签名验证
     * @param $method 当前接口请求方式
     * @param $pathinfo 获取当前请求URL的pathinfo信息（含URL后缀）
     * @param $param 客户端请求接口除sign参数外的所有参数数组值
     * @param $sign 客户端请求接口sign参数值
     * @return bool
     */
    private static function checkAuth($method, $pathinfo, $param, $sign)
    {
        //按规则拼接为字符串
        $str = self::createSign($method, $pathinfo, $param, config('api.ppk'));
        if ($str !== $sign) {
            return false;
        }
        return true;
    }

    /**
     * 生成HMAC-SHA1加密签名
     * @param $method 当前接口请求方式
     * @param $pathinfo 获取当前请求URL的pathinfo信息（含URL后缀）
     * @param array $param 除签名外的所有接口参数
     * @param string $encrypt 加密key
     * @param $urlencode 是否url编码
     * @return string
     */
    public static function createSign($method, $pathinfo, array $param, $encrypt = '', $urlencode = false)
    {
        ksort($param);
        $sign = '';
        foreach ($param as $key => $val) {
            if ($key != '') {
                $sign .= "{$key }={$val}&";
            }
        }
        $str = strtoupper($method) . '&' . $pathinfo . '?' . rtrim($sign, "&");
        if ($urlencode !== false) {
            $str = rawurlencode($str);
        }
        return base64_encode(hash_hmac('sha1', $str, $encrypt));
    }


    /**
     * 太阳健康商城接口参数
     * @param $method 请求方式
     * @param $pathinfo 请求URL的pathinfo信息
     * @param $param 接口业务参数
     * @param $encrypt 加密字符串
     * @return array
     */

    public static function createSunApiParam($method, $pathinfo, array $param, $encrypt = '')
    {
        $param['_timestamp'] = time();
        $param['client_type'] = '';
        $param['token'] = '';
        $sign = self::createSign($method, $pathinfo, $param, $encrypt);
        $param['signature'] = $sign;
        return $param;
    }


    /**
     * 接口是否被限制访问
     * @param $ip 客户端ip
     * @param $url 访问链接
     * @param string $token
     * @return bool
     * @throws \think\Exception
     */
    private static function is_access_limit($ip, $url, $token = '')
    {
        $limit_num = config('api.access_limit'); //限制次数
        $limit_time = config('api.access_limit_time');; //有效时间内,单位：秒

        $clientKey = "api_access_count:" . substr(md5("($ip)($url)"), 8, 16);
        //不存在key
        if (!Cache::has($clientKey)) {
            Cache::store('redis')->set($clientKey, 0, $limit_time);
        }

        //访问频率监控
        $accessCount = Cache::store('redis')->inc($clientKey);

        $Db = new Db();
        if ($accessCount > $limit_num) {
            if ($accessCount == $limit_num + 1) {
                $where = ['ip' => $ip];
                $count = $Db::table('api_ip_ask_num')->where($where)->count();
                if ($count > 0) {
                    $Db::table('api_ip_ask_num')->where($where)->setInc('num');
                } else {
                    $Db::table('api_ip_ask_num')->insert([
                        'ip' => $ip,
                        'num' => 1
                    ]);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * 接口访问日志添加
     * @param $return 返回值
     */
    public static function addAccessLog($return)
    {
        $ip = request()->ip();//客户端ip
        $url = request()->url(true);//接口地址
        $method = request()->method();
        $params = request()->param(false);
        $token = self::getToken();
        $uid = self::getUserId();

        //忽略个人头像这类大数据参数
        if (isset($params['avatar'])) {
            unset($params['avatar']);
        }

        //访问日志添加
        $add_data = [
            'ip' => $ip,
            'ask_url' => $url,
            'method' => $method,
            'params' => $method == 'GET' ? '' : stripslashes(json_encode($params)),
            'token' => $token != '' ? $token : '',
            'uid' => $uid,
            'return' => $return,
            'ask_from' => isset($params['source']) ? $params['source'] : (isset($params['key']) ? $params['key'] : ''),
            'create_time' => date('Y-m-d H:i:s', time()),
        ];

        Db::table('api_ip_ask_log')->insert($add_data);
    }

}