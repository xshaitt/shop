<?php
namespace ApiDoc\Helpers;

class Helpers
{
    /**
     * @title 生成数据库文档
     * @param Mysqli $mysqli
     * @param String /Array<String> $dbName 数据库实例的名
     * @return string
     */
    public static function pmd_generateDoc($mysqli, $dbName)
    {
        $strBuilder = self::markdownLine(" ");
        $strBuilder .= self::pmd_generateDocBody($mysqli, $dbName);
        return $strBuilder;
    }

    /**
     * @title 生成文档内容
     * @param Mysqli $mysqli
     * @param String /Array<String> $dbName 数据库实例的名
     * @return string
     */
    public static function pmd_generateDocBody($mysqli, $dbName)
    {
        $strBuilder = '';
        if (!is_array($dbName)) {
            $dbName = array($dbName);
        }

        foreach ($dbName as $tmpDbName) {
            $strBuilder .= self::pmd_generateMdByDatabase($mysqli, $tmpDbName);
        }
        return $strBuilder;
    }

    /**
     * @title 依数据库的注释生成Markdown文本
     * @param Mysqli $mysqli
     * @param String /Array<String> $dbName 数据库实例的名
     * @return string
     */
    public static function pmd_generateMdByDatabase($mysqli, $dbName)
    {
        $strBuilder = self::markdownLine("# " . $dbName);
        $tablesInfoArr = self::pmd_getTableInfo($mysqli, $dbName);
        foreach ($tablesInfoArr as $tabName => $tabComment) {
            $tabComment = $tabComment ? $tabComment : '';
            if (empty($tabComment)) {
                $strBuilder .= self::markdownLine("### $tabName", true);
            } else {
                $strBuilder .= self::markdownLine("### $tabName -- $tabComment", true);
            }
            $strBuilder .= self::markdownLine(" ", true);
            $fieldInfoArr = self::pmd_getFieldsInfo($mysqli, $dbName, $tabName);
            $strBuilder .= self::pmd_formatFieldInfoAsMarkDown($fieldInfoArr);
        }
        return $strBuilder;
    }

    /**
     * @title 使用mysqli扩展获取连接
     * @param $host 服务器地址
     * @param $port 端口号
     * @param $user 用户名
     * @param $password 密码
     * @param $database 数据库名
     * @desc 见 http://www.php.net/manual/zh/class.mysqli.php
     * @return Mysqli
     */
    public static function pmd_getMysqli($host, $port, $user, $password, $database = 'information_schema')
    {
        $mysqli = mysqli_connect($host, $user, $password, $database, $port);
        mysqli_set_charset($mysqli, "utf8");
        $errStr = mysqli_connect_error();
        if ($errStr) {
            die('Connect error ' . mysqli_connect_errno() . ": $errStr");
        } else {
            return $mysqli;
        }
    }

    /**
     * @title 获取数据库信息
     * @param Mysqli $mysqli
     * @param String /Array<String> $dbName 数据库实例的名
     * @return array
     */
    public static function pmd_getTableInfo($mysqli, $dbName)
    {
        $result = array();
        $sql = "Select TABLE_NAME, TABLE_COMMENT From `information_schema`.`TABLES` Where table_schema = '$dbName'";
        $queryRS = mysqli_query($mysqli, $sql);
        while ($row = mysqli_fetch_assoc($queryRS)) {
            $result[$row['TABLE_NAME']] = $row['TABLE_COMMENT'];
        }
        return $result;
    }

    /**
     * @title 获取数据库$dbName表$tabName的字段信息(字段名/字段类型/字段注释)
     * @param Mysqli $mysqli
     * @param String /Array<String> $dbName 数据库实例的名
     * @param $tabName 表名
     */
    public static function pmd_getFieldsInfo($mysqli, $dbName, $tabName)
    {
        $result = array();
        $sql = "Select
        *
    From
        `information_schema`.`COLUMNS`
    Where
        table_schema = '$dbName'
        And table_name ='$tabName'";

        $queryRS = mysqli_query($mysqli, $sql);
        while ($row = mysqli_fetch_assoc($queryRS)) {
            $tmp = (object)array();
            $tmp->Name = $row['COLUMN_NAME'];
            $tmp->Type = $row['DATA_TYPE'];
            $tmp->Comment = $row['COLUMN_COMMENT'];
            $tmp->COLUMN_TYPE = $row['COLUMN_TYPE'];
            $tmp->IS_NULLABLE = $row['IS_NULLABLE'];
            $result[] = $tmp;
        }
        return $result;
    }

    /**
     * @title 格式化到markdown
     * @param array $fieldInfoArr 数据库表结构信息
     * @return string
     */
    public static function pmd_formatFieldInfoAsMarkDown($fieldInfoArr)
    {
        $strBuilder = '';
        $formatStr = "| 字段名 | 字段类型及存储长度 | 字段注释 | 是否为空 |";
        $strBuilder .= self::markdownLine("$formatStr");
        $formatStr = "|--------| --------| --------|--------|";
        $strBuilder .= self::markdownLine("$formatStr");
        foreach ($fieldInfoArr as $key => $tmpfieldInfo) {
            $formatStr = "| " . $tmpfieldInfo->Name . "| " . $tmpfieldInfo->COLUMN_TYPE . "| " . $tmpfieldInfo->Comment . "| " . $tmpfieldInfo->IS_NULLABLE . "|";
            $strBuilder .= self::markdownLine("$formatStr");
        }
        return $strBuilder;
    }

    /**
     * @title 写入文档到文件中
     * @param string $text 文档内容
     * @param string $model w:写、a:追加
     * @param string $filePath 文件名(路径)
     */
    public static function writeToFile($text,$filePath = "phpmysqldoc.md")
    {
        $file = @fopen($filePath,'w');
        @fwrite($file, $text);
        @fclose($file);
    }

    /**
     * @title 给这行文字加上Markdwon的行结尾表示
     * @param string $str
     * @param bool $ifNeedEscape 是否给文字加上Markdwon的行结尾表示
     * @return string
     */
    public static function markdownLine($str, $ifNeedEscape = false)
    {
        $ifNeedEscape && $str = self::markdownStr($str);
        return $str . "\n";
    }

    /**
     * @param $str
     * @return mixed
     */
    public static function markdownStr($str)
    {
        return str_replace('_', '\_', $str);
    }

}