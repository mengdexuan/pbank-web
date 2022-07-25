<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Booking extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 预约交取款首页
     * @return mixed
     */
    public function index()
    {

        $query_date = $this->request->param('query_date');
        $data = [];
        $task_save = [];
        $task_take = [];
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $take_banks = [];
        $save_banks = [];
        $curr_day = strtotime($query_date);
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $result = doCurlGetRequest(['query_date'=>$query_date],'/query_plan');
        if($result['err_code'] == 0){
            $data = $result['data'];
//            print_r($data);
//            if($data['tasks']){
//                foreach ($data['tasks'] as $key=>$val){
//
//                    if(isset($task_take[$val['in_bank']])){
//                        //$task_take[$val['in_bank']] = $task_take[$val['in_bank']] + $val['task_amount'];
//                        if($val['in_bank'] == get_cur_bank()){
//                            $task_take[$val['in_bank']]['toc'] = $task_take[$val['in_bank']]['toc'] + $val['task_amount'];
//                        }else{
//                            $task_take[$val['in_bank']]['tox'] = $task_take[$val['in_bank']]['tox'] + $val['task_amount'];
//                        }
//                        if(!isset($task_take[$val['in_bank']]['toc'])){
//                            $task_take[$val['in_bank']]['toc'] = 0;
//                        }
//                        if(!isset($task_take[$val['in_bank']]['tox'])){
//                            $task_take[$val['in_bank']]['tox'] = 0;
//                        }
//                        $task_take[$val['in_bank']]['total'] = $task_take[$val['in_bank']]['toc'] + $task_take[$val['in_bank']]['tox'];
//                    }else{
//                        if($val['in_bank'] == get_cur_bank()){
//                            $task_take[$val['in_bank']]['toc'] = $val['task_amount'];
//                        }else{
//                            $task_take[$val['in_bank']]['tox'] = $val['task_amount'];
//                        }
//                        $task_take[$val['in_bank']]['total'] = $val['task_amount'];
//                    }
//                    if(isset($task_save[$val['out_bank']])){
//                        if($val['out_bank'] == get_cur_bank()){
//                            $task_save[$val['out_bank']]['toc'] = $task_save[$val['out_bank']]['toc'] + $val['task_amount'];
//                        }else{
//                            $task_save[$val['out_bank']]['tox'] = $task_save[$val['out_bank']]['tox'] + $val['task_amount'];
//                        }
//                        if(!isset($task_save[$val['out_bank']]['toc'])){
//                            $task_save[$val['out_bank']]['toc'] = 0;
//                        }
//                        if(!isset($task_save[$val['out_bank']]['tox'])){
//                            $task_save[$val['out_bank']]['tox'] = 0;
//                        }
//                        $task_save[$val['out_bank']]['total'] = $task_save[$val['out_bank']]['toc'] + $task_save[$val['out_bank']]['tox'];
//                    }else{
//                        if($val['out_bank'] == get_cur_bank()){
//                            $task_save[$val['out_bank']]['toc'] = $val['task_amount'];
//                        }else{
//                            $task_save[$val['out_bank']]['tox'] = $val['task_amount'];
//                        }
//                        $task_save[$val['out_bank']]['total'] = $val['task_amount'];
//                    }
//
//                }
//            }
            $task_take = $this->task_num($data['tasks'],'in_bank',0);
            $task_save = $this->task_num($data['tasks'],'out_bank',0);
            $task_damaged = $this->task_num($data['tasks'],'out_bank',1);
            //print_r($task_damaged);
            if($data['take_plan']){
                foreach($data['take_plan'] as $key=>$val){
                    $data['take_plan'][$key]['tox'] = isset($task_take[$val['bank_code']]['tox'])?$task_take[$val['bank_code']]['tox']:0;
                    $data['take_plan'][$key]['toc'] = isset($task_take[$val['bank_code']]['toc'])?$task_take[$val['bank_code']]['toc']:0;
                    $data['take_plan'][$key]['total'] = isset($task_take[$val['bank_code']]['total'])?$task_take[$val['bank_code']]['total']:0;
                    $data['take_plan'][$key]['overdue'] = 0;
                    if(time() > strtotime($val['plan_time'])+3600*24){
                        $data['take_plan'][$key]['overdue'] = 1;
                    }
                    if(!in_array($val['bank_code'],$take_banks) && ($data['take_plan'][$key]['plan_amount'] - $data['take_plan'][$key]['total']) > 0){
                        $take_banks[] = $val['bank_code'];
                    }
                }
            }
            if(isset($data['save_plan']) && $data['save_plan']){
                foreach($data['save_plan'] as $key=>$val){
                    $data['save_plan'][$key]['tox'] = isset($task_save[$val['bank_code']]['tox'])?$task_save[$val['bank_code']]['tox']:0;
                    $data['save_plan'][$key]['toc'] = isset($task_save[$val['bank_code']]['toc'])?$task_save[$val['bank_code']]['toc']:0;
                    $data['save_plan'][$key]['total'] = isset($task_save[$val['bank_code']]['total'])?$task_save[$val['bank_code']]['total']:0;
                    $data['save_plan'][$key]['overdue'] = 0;
                    if(time() > strtotime($val['plan_time'])+3600*24){
                        $data['save_plan'][$key]['overdue'] = 1;
                    }
                    if(!in_array($val['bank_code'],$save_banks) && ($data['save_plan'][$key]['plan_amount'] - $data['save_plan'][$key]['total']) > 0){
                        $save_banks[] = $val['bank_code'];
                    }
                }
            }
            if($data['damaged']){
                foreach($data['damaged'] as $key=>$val){

                    $data['damaged'][$key]['tox'] = isset($task_damaged[$val['bank_code']]['tox'])?$task_damaged[$val['bank_code']]['tox']:0;
                    $data['damaged'][$key]['toc'] = isset($task_damaged[$val['bank_code']]['toc'])?$task_damaged[$val['bank_code']]['toc']:0;
                    $data['damaged'][$key]['total'] = isset($task_damaged[$val['bank_code']]['total'])?$task_damaged[$val['bank_code']]['total']:0;
                    $data['damaged'][$key]['overdue'] = 0;
                    if(time() > strtotime($val['plan_time'])+3600*24){
                        $data['damaged'][$key]['overdue'] = 1;
                    }
                }
            }
        }
        $banks = get_bank('all');
        //别的地方要用 就粘贴下面这行就行了 assign就行了
        $valuta_info = get_valuta_info(0,0);
        //print_r($valuta_info);
        //print_r($take_banks);
        //print_r($save_banks);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('take_banks',$take_banks);
        $this->assign('save_banks',$save_banks);
        $this->assign('task_take',$task_take);
        $this->assign('task_save',$task_save);
        $this->assign('query_date',$query_date);
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        $this->assign('data',$data);
        $this->assign('banks',$banks);
        return $this->fetch(self::$version.self::$actionVersion);
    }

    private function task_num($data,$type,$task_type){
        $result = [];
        $cbank = 'in_bank';
        if($type == 'in_bank'){
            $cbank = 'out_bank';
        }
        if($data){
            foreach ($data as $key=>$val){
                if($val['task_type'] != $task_type){
                    continue;
                }
                if(isset($result[$val[$type]])){
                    if(!isset($result[$val[$type]]['toc'])){
                        $result[$val[$type]]['toc'] = 0;
                    }
                    if(!isset($result[$val[$type]]['tox'])){
                        $result[$val[$type]]['tox'] = 0;
                    }
                    if($val[$cbank] == get_cur_bank()){
                        $result[$val[$type]]['toc'] = $result[$val[$type]]['toc'] + $val['task_amount'];
                    }else{
                        $result[$val[$type]]['tox'] = $result[$val[$type]]['tox'] + $val['task_amount'];
                    }
                    $result[$val[$type]]['total'] = $result[$val[$type]]['toc'] + $result[$val[$type]]['tox'];
                }else{
                    if($val[$cbank] == get_cur_bank()){
                        $result[$val[$type]]['toc'] = $val['task_amount'];
                    }else{
                        $result[$val[$type]]['tox'] = $val['task_amount'];
                    }
                    $result[$val[$type]]['total'] = $val['task_amount'];
                }
            }
        }
        //print_r($result);
        return $result;
    }
    /**
     * 创建预约
     * @return mixed
     */
    public function create_booking()
    {

        $this->assign('cur_bank',get_cur_bank());
        return $this->fetch();
    }
    /**
     * 上链确权页面
     * @return mixed
     */
    public function confirmationList()
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