<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;

/**
 * 后台残损券统计
 * Class Damaged
 * @package app\admin\controller
 */
class Damaged extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 统计首页
     * @return mixed
     */
    public function index()
    {
        $query_date = $this->request->param('query_date');
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        $stock_info = [];
        $bank_list = [];
        $data_info =  [];
        $total = 0;
        $op_name = $this->request->param('op_name','管理员');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $yesterday = date('Y-m-d',time()-3600*24);
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $valuta_info = get_valuta_info(2);
        $tatistics = doCurlGetRequest(['start_date'=>$start_date,'end_date'=>$end_date],'/appointment_report');
        $report = '';
        $saveTaskTotal = 0;
        $takeTaskTotal = 0;
        $reporttotal['valutaName']='合计';
        $reporttotal['saveAppointmentAmt']=0;
        $reporttotal['mutualAmount']=0;
        $reporttotal['saveAmount']=0;
        $reporttotal['saveTaskAmount']=0;
        $reporttotal['saveBox']=0;
        $reporttotal['takeAppointmentAmt']=0;
        $reporttotal['takeAmount']=0;
        $reporttotal['takeTaskAmount']=0;
        $reporttotal['takeBox']=0;
        $reporttotal['damageAppointmentAmt']=0;
        $reporttotal['damageAmount']=0;
        $reporttotal['damageTaskAmount']=0;
        $reporttotal['damageBag']=0;
        if($tatistics['err_code'] == 0 && $tatistics['data']['report']){
            $report = $tatistics['data']['report'];
            $reporttotal = $tatistics['data']['total'];
            $saveTaskTotal = $tatistics['data']['saveTaskTotal'];
            $takeTaskTotal = $tatistics['data']['takeTaskTotal'];
        }
        $this->assign('report',$report);
        $this->assign('reporttotal',$reporttotal);
        $this->assign('saveTaskTotal',$saveTaskTotal);
        $this->assign('takeTaskTotal',$takeTaskTotal);
        $this->assign('query_date',$query_date);
        $result = doCurlGetRequest(['start_date'=>$start_date,'end_date'=>$end_date],'/query_damage_total');
        if($result['err_code'] == 0 && $result['data']['damages']){
            $stock_info = $result['data']['damages'];
            $total = $result['data']['total'];
        }
        $valuta_info = get_damage_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('total',$total);
        $this->assign('op_name',$op_name);
        $this->assign('bank_list',$bank_list);
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('yesterday',$yesterday);
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        $data = doCurlGetRequest(['start_date'=>$start_date,'end_date'=>$end_date,'cur_num'=>40],'/query_damage_tasks');
        if($data['err_code'] == 0 && $data['data']['stock_tasks']){
            $data_info = $data['data']['stock_tasks'];
        }
        $this->assign('data_info',$data_info);

        $list = doCurlGetRequest(['start_date'=>$start_date,'end_date'=>$end_date,'cur_num'=>40],'/query_damage_stock_records');
        $list_info = '';
        if($list['err_code'] == 0 && $list['data']['stock_records']){
            $list_info = $list['data']['stock_records'];
        }
        $this->assign('list_info',$list_info);
        $this->assign('app_stage',config('app_stage'));

        $banks = get_bank('all');
        $this->assign('banks',$banks);
        return $this->fetch('index');
    }
    /**
     * 库存详情
     * @return mixed
     */
    public function info(){
        $query_date = $this->request->param('query_date');
        $bank_code = $this->request->param('bank_code');
        $stock_id = $this->request->param('stock_id');
        $is_lib = $this->request->param('is_lib',0);
        $op_name = $this->request->param('op_name','管理员');
        if(trim($query_date) == ''){
            $this->redirect('/admin/damage/index');
        }
        if(trim($bank_code) == ''){
            $this->redirect('/admin/damage/index');
        }
        if(trim($stock_id) == ''){
            $this->redirect('/admin/damage/index');
        }
        $last_time = '--';
        $stock_time = '--';
        $stock_info = [];
        $stock_status = 0;
        $stock_id = 0;
        $valuta_info = get_valuta_info(2);
        $note = '';
        $result = doCurlGetRequest(['bank_code'=>$bank_code,'query_date'=>$query_date],'/query_damage_stock');
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $last_time = $stock_info['op_time'];
            $stock_time = $stock_info['stock_time'];
            $stock_date = $stock_info['stock_date'];
            $stock_status = $stock_info['stock_status'];
            $task_status = $stock_info['task_status'];
            $stock_id = $stock_info['stock_id'];
            $note = $stock_info['note'];
        }
        $valuta_info = get_damage_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('stock_date',$stock_date);
        $this->assign('stock_time',$stock_time);
        $this->assign('last_time',$last_time);
        $this->assign('bank_code',$bank_code);
        $this->assign('stock_status',$stock_status);
        $this->assign('task_status',$task_status);
        $this->assign('stock_id',$stock_id);
        $this->assign('is_lib',$is_lib);
        $this->assign('op_name',$op_name);
        //$this->assign('bank_key',$bank_key);
        $this->assign('note',$note);
        return $this->fetch();
    }
}