<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;


/**
 * iframe 预约交取款
 * Class Booking
 * @package app\iframe\controller
 */
class Booking extends IframeBase
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
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;
        $query_date = $this->request->param('query_date');
        $company = $this->request->param('showMoneyUnit','');
        $parameDoConversionIframe = $company == 1 ? '万元' : '元';
        \think\Cookie::set('parameDoConversionIframe',$parameDoConversionIframe);
        $data = [];
        $task_save = [];
        $task_take = [];
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_current_after_plan');
        if($result['err_code'] == 0){
            $data = $result['data'];

            $task_take = $this->task_num($data['tasks'],'in_bank',0,$bank_code);
            $task_save = $this->task_num($data['tasks'],'out_bank',0,$bank_code);
            $task_damaged = $this->task_num($data['tasks'],'out_bank',1,$bank_code);
            //print_r($data);
            if($data['take_plan']){
                foreach($data['take_plan'] as $key=>$val){
                    $plan_time = trim($val['plan_time']);
                    $data['take_plan'][$key]['tox'] = isset($task_take[$plan_time]['tox'])?$task_take[$plan_time]['tox']:0;
                    $data['take_plan'][$key]['toc'] = isset($task_take[$plan_time]['toc'])?$task_take[$plan_time]['toc']:0;
                    $data['take_plan'][$key]['total'] = isset($task_take[$plan_time]['total'])?$task_take[$plan_time]['total']:0;
                }
            }
            if(isset($data['save_plan']) && $data['save_plan']){
                foreach($data['save_plan'] as $key=>$val){
                    $plan_time = trim($val['plan_time']);
                    $data['save_plan'][$key]['tox'] = isset($task_save[$plan_time]['tox'])?$task_save[$plan_time]['tox']:0;
                    $data['save_plan'][$key]['toc'] = isset($task_save[$plan_time]['toc'])?$task_save[$plan_time]['toc']:0;
                    $data['save_plan'][$key]['total'] = isset($task_save[$plan_time]['total'])?$task_save[$plan_time]['total']:0;
                }
            }
            if($data['damaged']){
                foreach($data['damaged'] as $key=>$val){
                    $plan_time = trim($val['plan_time']);
                    $data['damaged'][$key]['tox'] = isset($task_damaged[$plan_time]['tox'])?$task_damaged[$plan_time]['tox']:0;
                    $data['damaged'][$key]['toc'] = isset($task_damaged[$plan_time]['toc'])?$task_damaged[$plan_time]['toc']:0;
                    $data['damaged'][$key]['total'] = isset($task_damaged[$plan_time]['total'])?$task_damaged[$plan_time]['total']:0;
                }
            }
        }
        $valuta_info = get_valuta_info(0,0);
        //print_r($valuta_info);
        //print_r($data['take_plan']);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('task_take',$task_take);
        $this->assign('task_save',$task_save);
        $this->assign('query_date',$query_date);
        $this->assign('data',$data);
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
        $this->assign('company',$company);
        //print_r($bank_key);
        return $this->fetch();
    }

    private function task_num($data,$type,$task_type,$bank_code){
        $result = [];
        $cbank = 'in_bank';
        if($type == 'in_bank'){
            $cbank = 'out_bank';
        }
        if($data){
            foreach ($data as $key=>$val){
                //echo $val[$type].'>>>>>'.$bank_code."<br>";
                if($val['task_type'] != $task_type || $val[$type] != $bank_code){
                    continue;
                }
                $task_time = trim($val['task_time']);
                if(isset($result[$task_time])){
                    if(!isset($result[$task_time]['toc'])){
                        $result[$task_time]['toc'] = 0;
                    }
                    if(!isset($result[$task_time]['tox'])){
                        $result[$task_time]['tox'] = 0;
                    }
                    if($val[$cbank] == get_cur_bank()){
                        $result[$task_time]['toc'] = $result[$task_time]['toc'] + $val['task_amount'];
                    }else{
                        $result[$task_time]['tox'] = $result[$task_time]['tox'] + $val['task_amount'];
                    }
                    $result[$task_time]['total'] = $result[$task_time]['toc'] + $result[$task_time]['tox'];
                }else{
                    if($val[$cbank] == get_cur_bank()){
                        $result[$task_time]['toc'] = $val['task_amount'];
                    }else{
                        $result[$task_time]['tox'] = $val['task_amount'];
                    }
                    $result[$task_time]['total'] = $val['task_amount'];
                }
            }
        }
        //echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><br>";
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
}