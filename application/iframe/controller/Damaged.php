<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;


/**
 * iframe 残损券上报
 * Class Damaged
 * @package app\iframe\controller
 */
class Damaged extends IframeBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 残损券上报
     * @return mixed
     */
    public function index(){
        $stock_info = [];
        $last_time = '--';
        $stock_time = '--';
        $note = '';
        $is_stock = 0;
        $bank_flag = '';
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;
        $op_name = $this->request->param('op_name','default');
        $company = $this->request->param('showMoneyUnit','');
        $parameDoConversionIframe = $company == 1 ? '万元' : '元';
        \think\Cookie::set('parameDoConversionIframe',$parameDoConversionIframe);
        $valuta_info = get_valuta_info(2);
        //print_r($valuta_info);
        $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_damage_stock');
        //print_r($result);
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $last_time = $stock_info['op_time'];
            $stock_time = $stock_info['stock_date'];
            if(date('Y-m-d',time()) == $stock_info['stock_date']){
                $is_stock = $stock_info['stock_status'];
            }
            $note = $stock_info['note'];
        }
        $valuta_info = get_damage_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
        $this->assign('op_name',$op_name);
        $this->assign('stock_time',$stock_time);
        $this->assign('last_time',$last_time);
        $this->assign('is_stock',$is_stock);
        $this->assign('note',$note);
        $this->assign('bank_flag',$bank_flag);
        return $this->fetch();
    }

    /**
     * 库存详情
     * @return mixed
     */
    public function info(){
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;
        $query_date = $this->request->param('query_date');
        if(trim($query_date) == ''){
            $this->redirect('iframe/damaged/index',['bank_code'=>$bank_key]);
        }
        $last_time = '--';
        $stock_time = '--';

        $stock_info = [];
        $valuta_info = get_valuta_info(2);
        $result = doCurlGetRequest(['bank_code'=>$bank_code,'query_date'=>$query_date],'/query_damage_stock');
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $last_time = $stock_info['op_time'];
            $stock_time = $stock_info['stock_time'];
            $stock_date = $stock_info['stock_date'];
            $stock_status = $stock_info['stock_status'];
            $task_status = $stock_info['task_status'];
            $note = $stock_info['note'];
        }
        $valuta_info = get_damage_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('last_time',$last_time);
        $this->assign('stock_time',$stock_time);
        $this->assign('stock_date',$stock_date);
        $this->assign('bank_code',$bank_code);
        $this->assign('stock_status',$stock_status);
        $this->assign('task_status',$task_status);
        $this->assign('bank_key',$bank_key);
        $this->assign('note',$note);
        return $this->fetch();
        //print_r($stock_time);
    }
    public function test(){
        print_r($_SERVER);
    }
}