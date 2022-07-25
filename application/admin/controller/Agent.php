<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 代理业务
 * Class Index
 * @package app\admin\controller
 */
class Agent extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 代理任务
     * @return mixed
     */
    public function index()
    {
        $branch_info = session('branch_info');
        $agent_type = $branch_info['agent_type'];
        $this->assign('branch_info',$branch_info);
        $this->assign('agent_type',$agent_type);
        $query_date = $this->request->param('query_date');
        $status = $this->request->param('status','');
        if(!in_array($status,[1,2,3])){
            $status = '';
        }
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $curr_day_end = $curr_day+3600*24-1;
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $data = [];
        $result = doCurlGetRequest(['agent_branch_code'=>session('branch_code'),'cur_num'=>10000],'/query_bank_branch');
        $branch_code = session('branch_code');
        $branch_name = get_bank($branch_code);
        if($result['err_code'] == 0){
            $data = $result['data']['branchs'];
        }
        $this->assign('data',$data);
        $this->assign('branch_name',$branch_name);
        $this->assign('query_date',$query_date);
        $this->assign('status',$status);
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        $this->assign('curr_day',$curr_day);
        $this->assign('curr_day_end',$curr_day_end);
        return $this->fetch('index');
    }
    /**
     * 任务详情
     * @return mixed
     */
    public function view_task($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/agent/index');
        }
        $result = doCurlGetRequest(['task_code'=>$task_code],'/ext_task_detail');
        if($result['err_code'] == 0){
            $this->assign('data',$result['data']);
        }else{
            $this->redirect('/admin/agent/index');
        }
        $query_date = date('Y-m-d',time());
        $this->assign('data',$result['data']);
        $this->assign('query_date',$query_date);
        $this->assign('task_code',$task_code);
        return $this->fetch(self::$version.self::$actionVersion);
    }
    public function appoint()
    {
        $branch_info = session('branch_info');
        $agent_type = $branch_info['agent_type'];
        $this->assign('branch_info',$branch_info);
        $this->assign('agent_type',$agent_type);
        $query_date = $this->request->param('query_date');
        $status = $this->request->param('status','');
        if(!in_array($status,[1,2,3])){
            $status = '';
        }
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $curr_day_end = $curr_day+3600*24-1;
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $data = [];
        $result = doCurlGetRequest(['agent_branch_code'=>session('branch_code'),'cur_num'=>10000],'/query_bank_branch');
        $branch_code = session('branch_code');
        $branch_name = get_bank($branch_code);
        if($result['err_code'] == 0){
            $data = $result['data']['branchs'];
        }
        $this->assign('data',$data);
        $this->assign('branch_name',$branch_name);
        $this->assign('query_date',$query_date);
        $this->assign('status',$status);
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        $this->assign('curr_day',$curr_day);
        $this->assign('curr_day_end',$curr_day_end);
        return $this->fetch();
    }
    /**
     * 预约详情
     * @return mixed
     */
    public function view_appoint($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/agent/appoint');
        }
        $result = doCurlGetRequest(['planCode'=>$task_code],'/mbank-ext/plan/get',false,config('task_interface_url'));
        if($result['success'] && $result['success'] != null){
            $this->assign('data',$result['data']);
        }else{
            $this->redirect('/admin/agent/appoint');
        }
        $query_date = date('Y-m-d',time());
        $this->assign('data',$result['data']);
        $this->assign('query_date',$query_date);
        $this->assign('task_code',$task_code);
        return $this->fetch();
    }
}