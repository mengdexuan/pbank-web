<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;


/**
 * iframe 库存上报
 * Class Balance
 * @package app\iframe\controller
 */
class Balance extends IframeBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 库存上报
     * @return mixed
     */
    public function index(){
        $stock_info = [];
        $last_time = '--';
        $note = '';
        $is_stock = 0;
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;
        $op_name = $this->request->param('op_name','default');
        $company = $this->request->param('showMoneyUnit','');
        $parameDoConversionIframe = $company == 1 ? '万元' : '元';
        \think\Cookie::set('parameDoConversionIframe',$parameDoConversionIframe);
        $valuta_info = get_valuta_info(1);
        //print_r($valuta_info);
        $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_buss_stock');
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $last_time = $stock_info['op_time'];
            if(date('Y-m-d',time()) == $stock_info['stock_date']){
                $is_stock = $stock_info['stock_status'];
            }
            $note = $stock_info['note'];
        }
        $valuta_info = get_balance_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
        $this->assign('op_name',$op_name);
        $this->assign('last_time',$last_time);
        $this->assign('is_stock',$is_stock);
        $this->assign('note',$note);
        return $this->fetch();
    }

    /**
     * 库存上报
     * @return mixed
     */
    public function add(){
        $stock_info = [];
        $last_time = '--';
        $note = '';
        $is_stock = 0;
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;
        $op_name = $this->request->param('op_name','default');
        $valuta_info = get_valuta_info(1);
        //print_r($valuta_info);
        $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_buss_stock');
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $last_time = $stock_info['op_time'];
            if(date('Y-m-d',time()) == $stock_info['stock_date']){
                $is_stock = $stock_info['stock_status'];
            }
            $note = $stock_info['note'];
        }
        $valuta_info = get_balance_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
        $this->assign('op_name',$op_name);
        $this->assign('last_time',$last_time);
        $this->assign('is_stock',$is_stock);
        $this->assign('note',$note);
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
            $this->redirect('iframe/balance/index',['bank_code'=>$bank_key]);
        }
        $last_time = '--';

        $stock_info = [];
        $valuta_info = get_valuta_info(1);
        $result = doCurlGetRequest(['bank_code'=>$bank_code,'query_date'=>$query_date],'/query_buss_stock');
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $last_time = $stock_info['op_time'];
            $note = $stock_info['note'];
        }
        $valuta_info = get_balance_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('last_time',$last_time);
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
        $this->assign('note',$note);
        return $this->fetch();
    }
    public function test(){
        print_r($_SERVER);
    }
}