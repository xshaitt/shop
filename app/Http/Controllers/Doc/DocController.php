<?php
namespace App\Http\Controllers\Doc;

use App\Http\Controllers\Controller;
use App\Models\ZkDocuments;
use Illuminate\Support\Facades\DB;


/**
 * @title 文档管理
 * @auth 邹柯
 */
class DocController extends Controller
{
    protected $action;
    protected $config;
    protected $item;
    protected $sidebar_nav;

    public function __construct()
    {
        $this->db = DB::connection('mindoc');
        //$this->redirect('http://doc.changrentech.com:8181/docs/mindoc');
        $this->request = Request();
        $this->action = explode("?",explode("/",$this->request->getRequestUri())[2])[0];
        //获取接口文档配置文件
        $this->config = config('documents');
        //项目名称
        $this->item = $this->request->input('item');
        //侧边栏
        $this->sidebar_nav = $this->request->input('sidebar_nav');

        if(in_array($this->action,['documents','apiDebug'])) {
            if (empty($this->item)) {
                header("Location: ?item=api");
                exit;
            }
        }

        if($this->action == 'dbStructDocuments') {
            if(empty($this->item)){
                header("Location: ?item=3");
                exit;
            }else{
                if(empty($this->sidebar_nav)){
                    $info = $this->db->table('md_documents')->select('document_id')->where('book_id',$this->item)->first()->toArray();
                    header("Location: ?item=".$this->item."&sidebar_nav=".$info['document_id']);
                    exit;
                }
            }
        }

        if($this->action == 'apiDevDocuments') {
            if(empty($this->item)){
                header("Location: ?item=1");
                exit;
            }else{
                if(empty($this->sidebar_nav)){
                    $info = $this->db->table('md_documents')->select('document_id')->where([
                        ['book_id','=',$this->item],['order_sort','=',0]
                    ])->first()->toArray();
                    header("Location: ?item=".$this->item."&sidebar_nav=".$info['document_id']);
                    exit;
                }
            }
        }

        if($this->action == 'apiPublicDocuments') {
            if(empty($this->item)){
                header("Location: ?item=6");
                exit;
            }else{
                if(empty($this->sidebar_nav)){
                    $info = $this->db->table('md_documents')->select('document_id')->where('book_id',$this->item)->first()->toArray();
                    header("Location: ?item=".$this->item."&sidebar_nav=".$info['document_id']);
                    exit;
                }
            }
        }
    }

    /**
     * @title 接口列表页
     * @auth 邹柯
     */
    public function documents()
    {
        $documents = new ZkDocuments($this->item);
        $list = $documents->getApiDocuments();

        if($this->item == "api"){
            $book_id = 1;
        }else{
            $book_id = 4;
        }
        return view('apidoc.documents', compact('list','nav','item','book_id'));

    }

    /**
     * @title api调试
     * @auth 邹柯
     */
    public function apiDebug(){
        $documents = new ZkDocuments($this->item);
        $result = $documents->getApiDocuments();

        foreach($result as $k=>$v){
            //去除类注释标题为空的项
            if(empty($v['title'])){
                unset($result[$k]);
            }else{
                if(!empty($v['method'])){
                    foreach($v['method'] as $k2=>$v2){
                        //去除方法注释标题为空的项
                        if(empty($v2['title'])){
                            unset($result[$k]['method'][$k2]);
                        }
                    }
                }else{ //去除method为空的项
                    unset($result[$k]['method']);
                }
            }
        }
        $data=array_values($result);

        //组装数据
        $data2 = [];
        foreach($data as $k=>$v){
            $arr2 = [];
            foreach($v['method'] as $k2=>$v2){
                $in_start = stripos($v2['desc'],"/");
                $in_end = stripos($v2['desc'],"<");
                $api_url = substr($v2['desc'], $in_start, $in_end - $in_start);

                $in_method_start = stripos($v2['desc'],"请求方式：") + 15;
                $method = explode("<",substr($v2['desc'], $in_method_start))[0];

                $controller = explode("-",$v['param'])[3];
                $action = $v2['name'];

                $arr2[$k2] = [
                    'name' => trim($v['title']),
                    'explan' => trim($v2['title']),
                    'path' => trim($api_url),
                    'method' => trim($method),
                    'parameter'=>[
                        'd'=> $this->item,
                        'c'=> $controller,
                        'a'=> $action,
                    ]
                ];
                if(!empty($v2['params'])){
                    foreach($v2['params'] as $k3=>$v3){
                        $arr2[$k2]['parameter'][$v3['name']] = trim($v3['type'])." | ".trim(strip_tags($v3['require']))." | ".trim($v3['desc']);
                    }
                }
            }
            $data2[$k]=$arr2;
        }
        static $res_info= [];
        foreach($data2 as $k=>$v){
            $res_info=array_merge($res_info,$v);
        }
        $api_debug_info = json_encode($res_info,JSON_UNESCAPED_UNICODE);
        return view('debug',[
            'list'=>$api_debug_info,
            'nav'=>'apiDebug'
        ]);
    }

    /**
     * @title 数据库结构文档
     * @auth 邹柯
     */
    public function dbStructDocuments(){
        $api_doc_sidebar_nav = $this->db->table('md_documents')->select('document_id as doc_nav_en,document_name as doc_nav_zh')->where('book_id',$this->item)->get()->toArray();
        if(!empty($api_doc_sidebar_nav)){
            foreach($api_doc_sidebar_nav as $k=>$v){
                $sidebar_nav[] = [
                    'doc_nav_en'=> $v['doc_nav_en'],
                    'doc_nav_zh'=> $v['doc_nav_zh'],
                ];
            }
        }else{
            $sidebar_nav = null;
        }

        $info = $this->db->table('md_documents')->select('markdown')->where([
            ['book_id',$this->item],['document_id',$this->sidebar_nav]
        ])->first()->toArray();
        if(empty($doc_content)){
            $doc_content = $info['markdown'];
        }else{
            $doc_content = null;
        }
        if($this->item == 3){
            $db_name = "cradmin";
        }else{
            $db_name = "sun";
        }
        return view('edit-md',[
            'doc_content'=>$doc_content,
            'api_doc_sidebar_nav'=>$sidebar_nav,
            'nav'=>'dbStructDocuments',
            'item'=>$this->item,
            'db_name'=>$db_name
        ]);
    }

    /**
     * @title 接口开发说明文档
     * @auth 邹柯
     */
    public function apiDevDocuments(){
        $api_doc_sidebar_nav = $this->db->table('md_documents')->select('document_id as doc_nav_en,document_name as doc_nav_zh')->where([
            ['book_id',$this->item],['order_sort',0]
        ])->get()->toArray();
        if(!empty($api_doc_sidebar_nav)){
            foreach($api_doc_sidebar_nav as $k=>$v){
                $sidebar_nav[] = [
                    'doc_nav_en'=> $v['doc_nav_en'],
                    'doc_nav_zh'=> $v['doc_nav_zh'],
                ];
            }
        }else{
            $sidebar_nav = null;
        }

        $info = $this->db->table('md_documents')->select('markdown')->where([
            ['book_id',$this->item],['document_id',$this->sidebar_nav],['order_sort'=>0]
        ])->first()->toArray();
        if(empty($doc_content)){
            $doc_content = $info['markdown'];
        }else{
            $doc_content = null;
        }

        return view('edit-md',[
            'doc_content'=>$doc_content,
            'api_doc_sidebar_nav'=>$sidebar_nav,
            'nav'=>'apiDevDocuments',
            'item'=>$this->item,
        ]);
    }

    /**
     * @title 公共文档
     * @auth 邹柯
     */
    public function apiPublicDocuments(){
        $api_doc_sidebar_nav = $this->db->table('md_documents')->select('document_id as doc_nav_en,document_name as doc_nav_zh')->where('book_id',$this->item)->get()->toArray();
        if(!empty($api_doc_sidebar_nav)){
            foreach($api_doc_sidebar_nav as $k=>$v){
                $sidebar_nav[] = [
                    'doc_nav_en'=> $v['doc_nav_en'],
                    'doc_nav_zh'=> $v['doc_nav_zh'],
                ];
            }
        }else{
            $sidebar_nav = null;
        }

        $info = $this->db->table('md_documents')->select('markdown')->where(
            ['book_id',$this->item],['document_id',$this->sidebar_nav]
        )->first()->toArray();
        if(empty($doc_content)){
            $doc_content = $info['markdown'];
        }else{
            $doc_content = null;
        }

        return view('edit-md',[
            'doc_content'=>$doc_content,
            'api_doc_sidebar_nav'=>$sidebar_nav,
            'nav'=>'apiDevDocuments',
            'item'=>$this->item,
        ]);
    }
}