<?php
namespace App\Http\Service;

class ReflectionService
{
    public $docTag = array(
        "@title"   => "标题",
        "@desc"    => "描述",
        "@param"   => "参数",
        "@example" => "返回示例",
        "@return"  => "返回值",
        "@version" => "版本信息",
        "@throws"  => "抛出的错误异常",
    );

    public $typeMaps = array(
        'string'   => '字符串',
        'int'      => '整型',
        'float'    => '浮点型',
        'boolean'  => '布尔型',
        'date'     => '日期',
        'array'    => '数组',
        'fixed'    => '固定值',
        'enum'     => '枚举类型',
        'object'   => '对象',
    );

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function getClass()
    {
        $rc = new \ReflectionClass($this->className);
        $res = explode("\n", $rc->getDocComment());
        $res = $this->processor($res);
        return $res;
    }

    public function getMethod($method)
    {
        $rm = new \ReflectionMethod($this->className, $method);
        $res = explode("\n", $rm->getDocComment());
        $res = $this->processor($res);
        return $res;
    }

    /**
     * @param $res
     * @return array|bool
     */
    private function processor($res)
    {
        $result = array();
        if (is_array($res)) {
            foreach ($res as $v) {
                $pos = 0;
                $content = "";
                preg_match("/@[a-z]*/i", $v, $tag);

                if (isset($tag[0]) && array_key_exists($tag[0], $this->docTag)) {
                    $pos = stripos($v, $tag[0]) + strlen($tag[0]);
                    if ($pos > 0) {
                        $content = trim(substr($v, $pos));
                    }
                    if ($content && ($tag[0]=='@param' || $tag[0]=='@return' || $tag[0]=='@example')) {
                        $result[$tag[0]][] = $content;
                    }elseif($content){
                        $result[$tag[0]] = $content;
                    }
                }
            }
            return $result;
        } else {
            return false;
        }
    }
}