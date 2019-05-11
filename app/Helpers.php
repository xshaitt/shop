<?php
/**
 * Json数据格式化
 *
 * @author 邹柯
 * @param $json
 * @param bool $html
 * @return string
 */
function format_json($json, $html = false)
{
    $tabcount = 0;
    $result = '';
    $inquote = false;
    $ignorenext = false;
    if ($html) {
        $tab = "   ";
        $newline = "<br/>";
    } else {
        $tab = "\t";
        $newline = "\n";
    }
    for ($i = 0; $i < strlen($json); $i++) {
        $char = $json[$i];
        if ($ignorenext) {
            $result .= $char;
            $ignorenext = false;
        } else {
            switch ($char) {
                case '{':
                    $tabcount++;
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '}':
                    $tabcount--;
                    $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                    break;
                case ',':
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '"':
                    $inquote = !$inquote;
                    $result .= $char;
                    break;
                case '\\':
                    if ($inquote) $ignorenext = true;
                    $result .= $char;
                    break;
                default:
                    $result .= $char;
            }
        }
    }
    return $result;
}


/**
 * 对象转数组
 *
 * @author 邹柯
 * @param $object
 * @return array
 */
function object_to_array($object){
    $array = json_decode($object,true);
    
    return $array;
}


/**
 * 二维数组按某个字段进行分组
 *
 * @author 邹柯
 * @param $group_field string 分组字段
 * @param $data array 要分组的二维数组
 * @param $additional array 分组额外显示字段
 * @return array
 */
function array_to_group($group_field,$data,$additional){
    //按分类进行分组
    foreach($data as $k=>$v){
        $group_name = $v[$group_field];
        unset($v[$group_field]);
        $arr[$group_name][] = $v;
    }
    foreach($arr as $k=>$v){
        $result[] = [
            $group_field => $k,
            'child_info'=>$v
        ];
    }

    return $result;
}


/**
 * 生成订单号
 *
 * @author 邹柯
 * @return string
 */
function createOrderNo() {
    return date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
}


/**
 * 判断是否是json
 *
 * @author 邹柯
 * @param $str
 * @return bool
 */
function is_not_json($str){
    return is_null(json_decode($str));
}

/**
 * 隐藏电话号码/手机号码中间4位
 *
 * @param $phone
 * @return string|string[]|null
 */
function hidtel($phone){
    //隐藏邮箱
    if (strpos($phone, '@')) {
        $email_array = explode("@", $phone);
        $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($phone, 0, 3); //邮箱前缀
        $count = 0;
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $phone, -1, $count);
        $rs = $prevfix . $str;
        return $rs;
    } else {
        //隐藏联系方式中间4位
        $Istelephone = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
        if ($Istelephone) {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);
        } else {
            return preg_replace('/(1[0-9]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
        }
    }
}
