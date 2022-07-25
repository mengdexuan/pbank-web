<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Confirmation extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 上链确权页面
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
        // $reporttotal['valutaName']='合计';
        // $reporttotal['saveAppointmentAmt']=0;
        // $reporttotal['mutualAmount']=0;
        // $reporttotal['saveAmount']=0;
        // $reporttotal['saveTaskAmount']=0;
        // $reporttotal['saveBox']=0;
        // $reporttotal['takeAppointmentAmt']=0;
        // $reporttotal['takeAmount']=0;
        // $reporttotal['takeTaskAmount']=0;
        // $reporttotal['takeBox']=0;
        // $reporttotal['damageAppointmentAmt']=0;
        // $reporttotal['damageAmount']=0;
        // $reporttotal['damageTaskAmount']=0;
        // $reporttotal['damageBag']=0;
        if($tatistics['err_code'] == 0 && $tatistics['data']['report']){
            $report = $tatistics['data']['report'];
            $reporttotal = $tatistics['data']['total'];
            $saveTaskTotal = $tatistics['data']['saveTaskTotal'];
            $takeTaskTotal = $tatistics['data']['takeTaskTotal'];
        }
        $this->assign('report',$report);
        // $this->assign('reporttotal',$reporttotal);
        $this->assign('saveTaskTotal',$saveTaskTotal);
        $this->assign('takeTaskTotal',$takeTaskTotal);
        $this->assign('query_date',$query_date);







        $query_date = $this->request->param('query_date');
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        $status = $this->request->param('status','');
        if(!in_array($status,[1,2,3])){
            $status = '';
        }
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $this->assign('app_stage',config('app_stage'));
        $this->assign('status',$status);
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        //print_r($result);
        return $this->fetch();
    }


}