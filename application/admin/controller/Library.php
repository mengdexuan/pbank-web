<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;


/**
 * 发行库首页
 * Class Index
 * @package app\admin\controller
 */
class Library extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 残损券统计首页
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
        $op_name = $this->request->param('op_name','default');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        if($start_date == '' && $end_date == '') {
            //$start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $yesterday = date('Y-m-d',time()-3600*24);
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);

        $valuta_info = get_valuta_info(2);
        $result = doCurlGetRequest(['query_date'=>$query_date],'/query_damage_total');
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
        $data = doCurlGetRequest(['query_date'=>$query_date,'cur_num'=>40],'/query_damage_tasks');
        if($data['err_code'] == 0 && $data['data']['stock_tasks']){
            $data_info = $data['data']['stock_tasks'];
        }
        $this->assign('data_info',$data_info);
        return $this->fetch(self::$version.self::$actionVersion);
    }

    /**
     * 残损券统计首页
     * @return mixed
     */
    public function damaged(){
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        if($start_date == '' && $end_date == '') {
            //$start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $stock_info = [];
        $total = 0;
        $result = doCurlGetRequest([],'/query_damage_total');
        if($result['err_code'] == 0 && $result['data']['damages']){
            $stock_info = $result['data']['damages'];
            $total = $result['data']['total'];
        }
        $this->assign('stock_info',$stock_info);
        $this->assign('total',$total);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        return $this->fetch('damaged');
    }

    /**
     * 库存详情
     * @return mixed
     */
    public function info(){
        $query_date = $this->request->param('query_date');
        $bank_code = $this->request->param('bank_code');
        $stock_id = $this->request->param('stock_id');
        $is_lib = $this->request->param('is_lib');
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
            $stock_time = $stock_info['stock_date'];
            $stock_status = $stock_info['stock_status'];
            $stock_id = $stock_info['stock_id'];
            $note = $stock_info['note'];
        }
        $valuta_info = get_damage_data($valuta_info,$stock_info);
        $this->assign('is_lib',$is_lib);
        $this->assign('valuta_info',$valuta_info);
        $this->assign('stock_info',$stock_info);
        $this->assign('stock_time',$stock_time);
        $this->assign('last_time',$last_time);
        $this->assign('bank_code',$bank_code);
        $this->assign('stock_status',$stock_status);
        $this->assign('stock_id',$stock_id);
        $this->assign('note',$note);
        return $this->fetch();
    }
}