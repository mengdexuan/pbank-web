<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;

/**
 * 后台库存现金余量统计
 * Class Balance
 * @package app\admin\controller
 */
class Balance extends AdminBase
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
        $stock_info = [];
        $bank_list = [];
        $op_name = $this->request->param('op_name','default');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $yesterday = date('Y-m-d',time()-3600*24);
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $valuta_info = get_valuta_info(1);
        $result = doCurlGetRequest(['query_date'=>$query_date],'/query_sum_buss_stock');
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $bank_list = $result['data']['bank_list'];
        }
        //print_r($bank_list);
        $valuta_info = get_balance_data($valuta_info,$stock_info);

        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('bank_list',$bank_list);
        $this->assign('query_date',$query_date);
        $this->assign('yesterday',$yesterday);
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        $this->assign('op_name',$op_name);
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
        if(trim($query_date) == ''){
            $this->redirect('/admin/balance/index');
        }

        if(trim($bank_code) == ''){
            $this->redirect('/admin/balance/index');
        }
        if(trim($stock_id) == ''){
            $this->redirect('/admin/balance/index');
        }
        //doCurlPostRequest(['stock_id'=>$stock_id],'/approve_buss_stock');
        $last_time = '--';
        $stock_time = '--';
        $stock_info = [];
        $valuta_info = get_valuta_info(1);
        $note = '';
        $result = doCurlGetRequest(['bank_code'=>$bank_code,'query_date'=>$query_date],'/query_buss_stock');
        if($result['err_code'] == 0 && $result['data']['stock_info']){
            $stock_info = $result['data']['stock_info'];
            $last_time = $stock_info['op_time'];
            $stock_time = $stock_info['stock_date'];
            $note = $stock_info['note'];
            $stock_status = $stock_info['stock_status'];
        }
        //print_r($result);
        $valuta_info = get_balance_data($valuta_info,$stock_info);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('stock_time',$stock_time);
        $this->assign('last_time',$last_time);
        $this->assign('bank_code',$bank_code);
        $this->assign('note',$note);
        $this->assign('stock_status',$stock_status);
        $this->assign('stock_id',$stock_id);
        return $this->fetch();
    }
}