<?php
namespace App\Models;

use App\Http\Service\ReflectionService;

class Doc
{
    public function __construct($item = null)
    {
        //获取接口文档配置文件
        $this->config = config('documents');
        $this->item = $item;
    }

    /**
     * @title 判断配置文件是否存在
     */
    public function is_config()
    {
        if (!$this->config) {
            echo "<h1>没有找到配置文件 documents.php</h1>";
            return;
        }
    }

    /**
     * @title 根据类名和方法名获取方法上的注释参数
     * @param $class 类路径
     * @param $method 方法名
     * @return array 返回的注释参数
     */
    public function Item($class, $method)
    {
        $re = new ReflectionService($class);
        $res = $re->getMethod($method);
        //获取方法上的注释
        $item = $this->getData($res);

        return [
            'title' => isset($item['title']) && !empty($item['title']) ? $item['title'] : '未配置标题',
            'desc' => isset($item['desc']) && !empty($item['desc']) ? $item['desc'] : '未配置描述信息',
            'params' => isset($item['params']) && !empty($item['params']) ? $item['params'] : [],
            'returns' => isset($item['returns']) && !empty($item['returns']) ? $item['returns'] : [],
            'example' => isset($item['example']) && !empty($item['example']) ? $item['example'] : [],
        ];
    }

    /**
     * @desc 获取类名称
     * @param $class
     * @return mixed
     */
    public function Ctitle($class)
    {
        $re = new ReflectionService($class);
        $res = $re->getClass();
        //获取类上的注释
        $item = $this->getData($res);
        return $item['title'];
    }

    /**
     * @title 获取类中非继承方法和重写方法
     * @param $classname string 类名
     * @param $access string 方法的访问权限(public、protected、private、final)
     * @return array
     * 只获取在本类中声明的方法，包含重写的父类方法，其他继承自父类但未重写的，不获取
     * 例
     * class A{
     *      public function a1(){}
     *      public function a2(){}
     * }
     * class B extends A{
     *      public function b1(){}
     *      public function a1(){}
     * }
     * getMethods('B')返回方法名b1和a1，a2虽然被B继承了，但未重写，故不返回
     */
    public function getMethods($classname, $access = null)
    {
        $class = new \ReflectionClass($classname);
        $methods = $class->getMethods();
        $returnArr = array();

        foreach ($methods as $value) {
            //过滤重写的构造函数
            if ($value->class == $classname && $value->name != '__construct') {
                if ($access != null) {
                    $methodAccess = new \ReflectionMethod($classname, $value->name);

                    switch ($access) {
                        case 'public':
                            if ($methodAccess->isPublic()) array_push($returnArr, $value->name);
                            break;
                        case 'protected':
                            if ($methodAccess->isProtected()) array_push($returnArr, $value->name);
                            break;
                        case 'private':
                            if ($methodAccess->isPrivate()) array_push($returnArr, $value->name);
                            break;
                        case 'final':
                            if ($methodAccess->isFinal()) $returnArr[$value->name] = 'final';
                            break;
                    }
                } else {
                    array_push($returnArr, $value->name);
                }

            }
        }

        return $returnArr;
    }

    /**
     * @title 获取注释信息
     * @param $res
     * @return array
     */
    private function getData($res)
    {
        $title = $description = $example = '';
        $param = $params = $return = $returns = array();

        foreach ($res as $key => $val) {
            if ($key == '@title') {
                $title = $val;
            }
            if ($key == '@desc') {
                $description = implode("<br>", (array)json_decode($val));
            }
            if ($key == '@param') {
                $param = $val;
            }
            if ($key == '@return') {
                $return = $val;
            }
            if ($key == '@example') {
                $example = $val;
            }
        }

        //过滤接收参数
        foreach ($param as $key => $rule) {
            $rule = (array)json_decode($rule);
            if (!empty($rule)) {
                $name = $rule['name'];
                if (!isset($rule['type'])) {
                    $rule['type'] = 'string';
                }
                $type = isset($rule['type']) ? $rule['type'] : '';
                $require = isset($rule['required']) && $rule['required'] ? '<font color="red">必须</font>' : '可选';
                $default = isset($rule['default']) ? $rule['default'] : '';
                if($default === NULL) {
                    $default = 'NULL';
                }elseif(is_array($default)){
                    $default = json_encode($default);
                }elseif(!is_string($default)){
                    //将数组转换成字符串
                    $default = var_export($default, true);
                }
                $desc = isset($rule['desc']) ? trim($rule['desc']) : '';
                $params[] = array('name' => $name, 'type' => $type, 'require' => $require, 'default' => $default, 'desc' => $desc);
            }
        }
        //过滤返回参数
        foreach ($return as $item) {
            $item = (array)json_decode($item);
            if (!empty($item)) {
                $type = $item['type'];
                $name = "";
                $required = $item['required'] ? '是' : '否';
                $detail = $item['desc'];
                for ($i = 1; $i < $item['level']; $i++) {
                    $name .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                $name .= $item['name'];
                $returns[] = array('name' => $name, 'type' => $type, 'required' => $required, 'detail' => $detail);
            }

        }

        //过滤示例参数
        foreach ((array)$example as $v){
            $examples[] = json_decode($v,true);
        }
        return array('title' => $title, 'desc' => $description, 'params' => $params, 'returns' => $returns,'example' => $examples);
    }

    /**
     * @title 获取接口文档
     * @return array
     */
    public function getApiDocuments(){
        //判断配置文件是否存在
        $this->is_config();
        //要生成接口文档的类路径
        $class = $this->config['class'][$this->item];
        $result = $data = array();
        foreach ($class as $val) {
            //获取当前类中的所有方法名
            $methods = $this->getMethods($val, 'public');
            foreach ($methods as $k => $v) {
                //根据类名和方法名获取方法上的注释参数
                $meth_v = $this->Item($val, $v);
                //将参数方法名组装进数组$meth_v中
                $meth_v['name'] = $v;

                $methods[$k] = $meth_v;
            }
            //获取类名称--用户管理
            $data['title'] = $this->Ctitle($val);
            //类路径--app\api\controller\User
            $data['class'] = $val;
            //格式化类路径--app-api-controller-User
            $data['param'] = str_replace('\\', '-', $val);
            $data['method'] = $methods;
            $result[] = $data;
        }

        return $result;

    }
}
