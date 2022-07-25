<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Request;
use think\Db;

/**
 * 查询
 * Class Index
 * @package app\admin\controller
 */
class Pack extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 包查询
     * @return mixed
     */
    public function index()
    {
        $pack_code = $this->request->param('pack_code');
        $data = [];
        $is_search = '';
        $bundles = 0;
        $columns = 4;
        if($pack_code != ''){
            $result = doCurlGetRequest(['pack_code'=>$pack_code,'cur_num'=>1000],'/query_pack_detail');
            //print_r($result);
            if($result['err_code'] == 0){
                $data = $result['data'];
                $bundles = count($data['bundles']);
            }else{

            }
        }
        if(isset($result)){
            $is_search = 1;
        }
        if($bundles == 25){
            $columns = 5;
        }
        $box = 0;
        if ($columns != 0){
            $box = ceil($bundles/$columns);
        }
        $box_width = $columns*185+$columns*5;
        $this->assign('box',$box);
        $this->assign('box_width',$box_width);
        $this->assign('is_search',$is_search);
        $this->assign('data',$data);
        $this->assign('pack_code',$pack_code);
        return $this->fetch();
    }

    /**
     * 新版包查询
     * @return mixed
     */
    public function search(){
        $code = $this->request->param('code');
        $this->assign('code',$code);
        return $this->fetch(self::$version.self::$actionVersion);
    }
    /**
     * 新版包查询轨迹
     * @return mixed
     */
    public function record()
    {
        $code = $this->request->param('code');
        $this->assign('code',$code);
        return $this->fetch();
    }
}