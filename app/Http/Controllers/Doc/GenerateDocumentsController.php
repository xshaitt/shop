<?php
namespace App\Http\Controllers\Doc;

use App\Models\ZkDocuments;
use App\Http\Service\ApiService;
use Illuminate\Routing\Controller as BaseController;

/**
 * @title 文档管理
 * @auth 邹柯
 */
class GenerateDocumentsController extends BaseController
{
    //要生成markdown文档的项目名称
    protected $item;
    //数据库名称
    protected $db_name;
    //mindoc数据库配置文件
    protected $db_mindoc;
    //获取接口文档配置文件
    protected $config;
    //mindoc项目id
    protected $book_id;

    public function __construct()
    {
        //获取要生成markdown文档的项目名称
        $this->item = $this->request->get('item');
        //数据库名称
        $this->db_name = $this->request->get('db_name');
        //mindoc数据库配置文件
        $this->db_mindoc = Config('mindoc.');
        //获取接口文档配置文件
        $this->config = config('documents.');
        //获取mindoc项目id
        $this->book_id = $this->request->get('book_id');
    }

    /**
     * @title 生成markdown格式的接口文档
     * @url http://192.168.2.10/apidoc/generateApiDocuments?book_id=1&item=api
     * @auth 邹柯
     */
    public function generateApiDocuments(){
        //接收参数校验
        if(empty($this->book_id)){
            die(ApiService::error("book_id不能为空！"));
        }
        if(empty($this->item)){
            die(ApiService::error("要生成markdown文档的项目名称不能为空！"));
        }

        $ZkDocuments = new ZkDocuments($this->item);
        $data = $ZkDocuments->getApiDocuments();

        if(!empty($data)){
            foreach ($data as $c => $class) {
                //生成一级根目录
                $parent_id = $ZkDocuments->writeToMysql($this->book_id,$class['title'],null,0,false);

                foreach($class['method'] as $a=>$action){
                    $strBuilder = "";

                    $docStr = $ZkDocuments->markdownLine("### ".$action['title']);
                    $strBuilder .= $ZkDocuments->markdownLine("$docStr");

                    $desc = explode("<br>",$action['desc']);
                    foreach($desc as $item){
                        $docStr = $ZkDocuments->markdownLine("> ".$item);
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                    }

                    $docStr = $ZkDocuments->markdownLine("##### (1)接收参数");
                    $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                    if(!empty($action['params'])){
                        $docStr = "| 字段名称 | 字段类型 | 是否必须 | 默认值 | 说明 |";
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                        $docStr = "|--------| --------| --------|--------|--------|";
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                        foreach($action['params'] as $param){
                            $docStr = "| ".trim($param['name'])." | ".trim($param['type'])." | ".trim($param['require'])." | ".trim($param['default'])."|".trim($param['desc'])."|";
                            $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                        }
                    }else{
                        $docStr = "   无";
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                    }
                    $strBuilder .= $ZkDocuments->markdownLine(" ");

                    if(!empty($action['returns'])){
                        $docStr = $ZkDocuments->markdownLine("##### (2)返回参数");
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");

                        $docStr = "| 字段名称 | 字段类型 | 是否必须 | 说明 |";
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                        $docStr = "|--------| --------| --------|--------|";
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                        foreach($action['returns'] as $return){
                            $docStr = "| ".trim($return['name'])." | ".trim($return['type'])." | ".trim($return['required'])." | ".trim($return['detail'])."|";
                            $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                        }
                    }else{
                        $docStr = "   无";
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                    }
                    $strBuilder .= $ZkDocuments->markdownLine(" ");

                    if(!empty($action['example'])){
                        foreach($action['example'] as $key=>$item){
                            $docStr = $ZkDocuments->markdownLine("##### (".($key+3).")返回示例".($key+1));
                            $strBuilder .= $ZkDocuments->markdownLine("$docStr");

                            $docStr = $ZkDocuments->markdownLine("```json");
                            $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                            $docStr = $ZkDocuments->markdownLine(format_json(json_encode($item,JSON_UNESCAPED_UNICODE)));
                            $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                            $docStr = $ZkDocuments->markdownLine("```");
                            $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                        }
                    }else{
                        $docStr = $ZkDocuments->markdownLine("   无");
                        $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                    }

                    $docStr = "-----------------------";
                    $strBuilder .= $ZkDocuments->markdownLine("$docStr");
                    //生成二级目录及接口文档
                    $ZkDocuments->writeToMysql($this->book_id,$action['title'],$strBuilder,$parent_id,false);
                }
            }
        }
        $this->success("生成api接口文档成功!", '/apidoc/documents');
    }

    /**
     * @title 生成markdown格式的数据库表结构文档
     * @url http://192.168.2.10/apidoc/generateDbDocuments?book_id=3&db_name=cradmin
     * @auth 邹柯
     */
    public function generateDbDocuments(){
        //接收参数校验
        if(empty($this->book_id)){
            die(ApiService::error("book_id不能为空！"));
        }
        if(empty($this->db_name)){
            die(ApiService::error("数据库名不能为空！"));
        }

        //数据库配置
        $database_config = [
            'cradmin'=>[
                'hostname'        => '39.105.144.59',
                'database'        => 'cradmin',
                'username'        => 'root',
                'password'        => '4231f7e9e9221721',
                'hostport'        => '3306',
            ],
            'sun'=>[
                'hostname'        => '39.105.144.59',
                'database'        => 'sun',
                'username'        => 'root',
                'password'        => '4231f7e9e9221721',
                'hostport'        => '3306',
            ],
        ];

        //组装数据库表结构文档
        $mysqli = pmd_getMysqli($database_config[$this->db_name]['hostname'], $database_config[$this->db_name]['hostport'], $database_config[$this->db_name]['username'],  $database_config[$this->db_name]['password']);
        $tablesInfoArr = pmd_getTableInfo($mysqli, $this->db_name);
        foreach ($tablesInfoArr as $tabName => $tabComment) {
            $strBuilder = markdownLine(" ");
            $tabComment = $tabComment ? $tabComment : '';
            if (empty($tabComment)) {
                $document_name = "$tabName";
                $strBuilder .= markdownLine("### $tabName", true);
            } else {
                $document_name = "$tabName($tabComment)";
                $strBuilder .= markdownLine("### $tabName($tabComment)", true);
            }
            $strBuilder .= markdownLine(" ", true);
            $fieldInfoArr = pmd_getFieldsInfo($mysqli, $this->db_name, $tabName);
            $strBuilder .= pmd_formatFieldInfoAsMarkDown($fieldInfoArr);
            //将数据库表结构写入mysql
            (new ZkDocuments($this->item))->writeToMysql($this->book_id,$document_name,$strBuilder,0,true);
        }

        $this->success('生成数据库表结构文档成功！','/apidoc/documents');
    }
}