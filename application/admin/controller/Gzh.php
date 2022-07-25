<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Request;
use think\Db;

/**
 * 冠字号查询
 * Class Index
 * @package app\admin\controller
 */
class Gzh extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 冠字号查询
     * @return mixed
     */
    public function index()
    {
        $gzh = $this->request->param('gzh');
        $data = [];
        $is_search = '';
        if($gzh != ''){
            $result = doCurlGetRequest(['gzh'=>$gzh],'/query_gzh',config('new_interface_url'));
            if($result['err_code'] == 0){
                $data = $result['data'];
            }else{

            }
        }
        if(isset($result)){
            $is_search = 1;
        }
        $this->assign('is_search',$is_search);
        $this->assign('data',$data);
        $this->assign('gzh',$gzh);
        return $this->fetch();
    }
    /**
     * 新版冠字号查询
     * @return mixed
     */
    public function search()
    {
        $code = $this->request->param('code');
        $this->assign('code',$code);
        return $this->fetch(self::$version.self::$actionVersion);
    }
    /**
     * 新版冠字号查询轨迹
     * @return mixed
     */
    public function record()
    {
        $code = $this->request->param('code');
        $this->assign('code',$code);
        return $this->fetch(self::$version.self::$actionVersion);
    }
    /**
     * 冠字号查询
     * @return mixed
     */
    public function gzh()
    {
        $gzh = $this->request->param('gzh');
        $data = [];
        $is_search = '';
        if($gzh != ''){
            $result = doCurlGetRequest(['gzh'=>$gzh],'/query_gzh_bundle',config('new_interface_url'));
            if($result['err_code'] == 0){
                $data = $result['data'];
            }else{

            }
        }
        if(isset($result)){
            $is_search = 1;
        }
        $this->assign('is_search',$is_search);
        $this->assign('data',$data);
        $this->assign('gzh',$gzh);
        return $this->fetch();
    }
    /**
     * 冠字号查询详情
     * @return mixed
     */
    public function info()
    {
        $gzh = $this->request->param('gzh');
        $bundle_code = $this->request->param('bundle_code');
        $data = [];
        $is_search = '';
        if($gzh != ''){
            $result = doCurlGetRequest(['bundle_code'=>$bundle_code],'/query_task_bundle');
            //print_r($result);
            if($result['err_code'] == 0){
                $data = $result['data'];
            }else{

            }
        }
        if(isset($result)){
            $is_search = 1;
        }
        $this->assign('is_search',$is_search);
        $this->assign('data',$data);
        $this->assign('gzh',$gzh);
        $this->assign('bundle_code',$bundle_code);
        return $this->fetch();
    }
}