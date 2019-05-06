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
 * @return array
 */
function array_to_group($group_field,$data){
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
