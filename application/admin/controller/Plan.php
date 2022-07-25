<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Plan extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 首页
     * @return mixed
     */
    public function index()
    {


        return ;
    }
    /**
     * 创建任务
     * @return mixed
     */
    public function create_task()
    {
        $banks = get_bank('all');
        $this->assign('banks',$banks);
        $this->assign('cur_bank',get_cur_bank());
        return $this->fetch();
    }
    /**
     * 修改任务
     * @return mixed
     */
    public function edit_task($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/plan/list_plan');
        }
        $result = doCurlGetRequest(['task_code'=>$task_code],'/query_task_detail');
        if($result['err_code'] == 0){
            $result['data']['amount_wan'] = $result['data']['task_amount']/10000;
            $this->assign('data',$result['data']);
        }
        $banks = get_bank('all');
        $this->assign('banks',$banks);
        $this->assign('cur_bank',get_cur_bank());
        return $this->fetch();
    }
    /**
     * 任务调拨
     * @return mixed
     */
    public function list_plan()
    {


        return $this->fetch('index');
    }
}