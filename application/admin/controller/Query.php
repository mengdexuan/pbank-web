<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 查询
 * Class Index
 * @package app\admin\controller
 */
class Query extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 调拨任务单
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }
    /**
     * 任务详情
     * @return mixed
     */
    public function view_task($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/query/index');
        }
        $result = doCurlGetRequest(['task_code'=>$task_code],'/query_task_detail');
        if($result['err_code'] == 0){
            $this->assign('data',$result['data']);
        }else{
            $this->redirect('/admin/query/index');
        }
        return $this->fetch();
    }
    /**
     * 任务中的包查询
     * @return mixed
     */
    public function pack()
    {
        $pack_code = $this->request->param('pack_code');
        $task_code = $this->request->param('task_code');
        $data = [];
        if($pack_code != ''){
            $result = doCurlGetRequest(['pack_code'=>$pack_code,'task_code'=>$task_code],'/query_pack_task');
            //print_r($result);
            if($result['err_code'] == 0){
                $data = $result['data'];
            }else{

            }
        }
        $this->assign('data',$data);
        $this->assign('task_code',$task_code);
        $this->assign('pack_code',$pack_code);
        return $this->fetch();
    }
    /**
     * 任务中的捆查询
     * @return mixed
     */
    public function bundle()
    {
        $task_code = $this->request->param('task_code');
        $pack_code = $this->request->param('pack_code');
        $bundle_code = $this->request->param('bundle_code');
        $data = $gzhs = [];
        if($bundle_code != ''){
            $result = doCurlGetRequest(['bundle_code'=>$bundle_code,'task_code'=>$task_code,'pack_code'=>$pack_code],'/query_bundle_pack_task');
            if($result['err_code'] == 0){
                $data = $result['data'];
            }else{

            }
            $gzh_result = doCurlGetRequest(['bundle_code'=>$bundle_code],'/query_gzh_in_bundle');
            if($gzh_result['err_code'] == 0){
                //print_r($gzh_result);
                $gzhs = $gzh_result['data'];
                $gzhs['count'] = count($gzhs['gzh_codes']);
            }else{

            }
        }
        $this->assign('data',$data);
        $this->assign('gzhs',$gzhs);
        $this->assign('bundle_code',$bundle_code);
        return $this->fetch();
    }
}