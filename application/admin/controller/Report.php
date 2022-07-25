<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;


/**
 * 报表
 * Class Index
 * @package app\admin\controller
 */
class Report extends AdminBase
{
    private $report_url = '';
    protected function _initialize()
    {
        parent::_initialize();
        $this->report_url = config('report_url');
    }

    /**
     * 报表首页
     * @return mixed
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * 报表-列表
     * @return mixed
     */
    public function list(){
        $reportNum = $this->request->param('reportNum');
        $this->assign('reportNum',$reportNum);
        return $this->fetch('report/list');
    }
    /*下载报表*/
    public function cash_stock_download(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        //$bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $reportName = show_report_name($reportNum);


        if($reportNum=='R0003'){
            $url = config('report_url').'/download?year='.$year.'&reportNum='.$reportNum;
        }else{
            $url = config('report_url').'/download?year='.$year.'&quarter='.$quarter.'&reportNum='.$reportNum;
        }

        if($reportNum=='R0003'){
            $filename = $year.'年'.$reportName.'.xlsx';
        }else{
            $filename = $year.'年第'.$quarter.'季度'.$reportName.'.xlsx';
        }
        httpcopy($url,$filename);
    }

    /**
     * 现金库存报表-详情
     * @return mixed
     */
    public function cashstock_detail(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $head = 1;
        if ($bankCode != '') {
            $head = 0;
        }
        $result = doCurlGetRequest(['bankCode'=>$bankCode, 'year'=>$year, 'quarter'=>$quarter],'/get/cash-stock',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/cashstock/detail');
    }

    /**
     * 中小面额报表-详情
     * @return mixed
     */
    public function smallamount_detail(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $head = 1;
        if ($bankCode != '') {
            $head = 0;
        }
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/put-ret-statis',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/smallamount/detail');
    }


    /**
     * 现金投放报表-详情
     * @return mixed
     */
    public function cashdelivery_detail(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $head = 1;
        if ($bankCode != '') {
            $head = 0;
        }
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/supply-return-plan',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        //print_r($data);
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/cashdelivery/detail');
    }




    /**
     * 现金清分报表-详情
     * @return mixed
     */
    public function cashclear_detail(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $head = 1;
        if ($bankCode != '') {
            $head = 0;
        }
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/clean-statis',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/cashclear/detail');
    }


    /**
     * 硬币自循环报表-详情
     * @return mixed
     */
    public function coinloop_detail(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $head = 1;
        if ($bankCode != '') {
            $head = 0;
        }
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/coin-loop',$this->report_url);
        $data = [];
        $coinlist = [];
        $coinlist['return'] = [];
        $coinlist['delivery'] = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }

            if($data['coinCirculation']){
                $coinlist = format_coin_type($data['coinCirculation']);
            }
        }else{

        }
        //print_r($data);
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('coinlist',$coinlist);
        $this->assign('head',$head);
        return $this->fetch('report/coinloop/detail');
    }


    /**
     * 现金收支报表-详情
     * @return mixed
     */
    public function cashcollect_detail(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $head = 1;
        if ($bankCode != '') {
            $head = 0;
        }
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/income-expenditure',$this->report_url);
        $data = [];
        $rowlist = [];
        $rowlist['out'] = [];
        $rowlist['in'] = [];
        $rowlist['total'] = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }

            if($data['tableData']){
                $rowlist = format_collect_type($data['tableData']);
            }
        }else{

        }
        //print_r($data);
        //print_r($rowlist);
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('rowlist',$rowlist);
        $this->assign('head',$head);
        return $this->fetch('report/cashcollect/detail');
    }

}