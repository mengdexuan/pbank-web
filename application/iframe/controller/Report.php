<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;


/**
 * 报表
 * Class Index
 * @package app\admin\controller
 */
class Report extends IframeBase
{
    private $report_url = '';
    protected function _initialize()
    {
        parent::_initialize();
        $this->report_url = config('report_url');
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
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



    /**
     * 现金库存报表-填写
     * @return mixed
     */
    public function cashstock_add(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['reportNum'=>$reportNum],'/rows',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
        }else{

        }
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/cashstock/add');
    }

    /**
     * 现金库存报表-修改
     * @return mixed
     */
    public function cashstock_edit(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/cash-stock',$this->report_url);
        $data = [];
        /*$list = get_report_name('R0001','R00010001');
        print_r($list);*/
        if($result['err_code'] == 0){
            $data = $result['data'];
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/cashstock/edit');
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
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/cash-stock',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['bankName'] = get_bank($val['bankCode']);
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/cashstock/detail');
    }




    /**
     * 中小面额报表-填写
     * @return mixed
     */
    public function smallamount_add(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['reportNum'=>$reportNum],'/rows',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
        }else{

        }
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/smallamount/add');
    }

    /**
     * 中小面额报表-修改
     * @return mixed
     */
    public function smallamount_edit(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/put-ret-statis',$this->report_url);
        $data = [];

        if($result['err_code'] == 0){
            $data = $result['data'];
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/smallamount/edit');
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
                    $data['bankList'][$key]['bankName'] = get_bank($val['bankCode']);
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/smallamount/detail');
    }




    /**
     * 现金投放报表-填写
     * @return mixed
     */
    public function cashdelivery_add(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['reportNum'=>$reportNum],'/rows',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            foreach($data['rowList'] as $key=>$val){
                $data['rowList'][$key]['groupType_str'] = set_group_type($val['groupType']);
            }
        }else{

        }
        //print_r($data);
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/cashdelivery/add');
    }

    /**
     * 现金投放报表-修改
     * @return mixed
     */
    public function cashdelivery_edit(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/supply-return-plan',$this->report_url);
        $data = [];

        if($result['err_code'] == 0){
            $data = $result['data'];
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/cashdelivery/edit');
    }

    /**
     * 现金投放报表-详情
     * @return mixed
     */
    public function cashdelivery_detail(){
        $year = $this->request->param('year');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $head = 1;
        if ($bankCode != '') {
            $head = 0;
        }
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year],'/get/supply-return-plan',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['bankName'] = get_bank($val['bankCode']);
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/cashdelivery/detail');
    }




    /**
     * 现金清分报表-填写
     * @return mixed
     */
    public function cashclear_add(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['reportNum'=>$reportNum],'/rows',$this->report_url);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
        }else{

        }
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/cashclear/add');
    }

    /**
     * 现金清分报表-修改
     * @return mixed
     */
    public function cashclear_edit(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/clean-statis',$this->report_url);
        $data = [];

        if($result['err_code'] == 0){
            $data = $result['data'];
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        return $this->fetch('report/cashclear/edit');
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
        //print_r($result);
        $data = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['bankList']){
                foreach($data['bankList'] as $key=>$val){
                    $data['bankList'][$key]['bankName'] = get_bank($val['bankCode']);
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
        }else{

        }
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('head',$head);
        return $this->fetch('report/cashclear/detail');
    }




    /**
     * 硬币自循环报表-填写
     * @return mixed
     */
    public function coinloop_add(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['reportNum'=>$reportNum],'/rows',$this->report_url);
        $data = [];
        $rowlist = [];
        $rowlist['coin'] = [];
        $rowlist['equipment'] = [];
        $coinlist = [];
        $coinlist['return'] = [];
        $coinlist['delivery'] = [];
        if($result['err_code'] == 0){
            $data = $result['data']['rowList'];
            $rowlist = format_rowlist_type($data);
            $coinlist = format_coin_type($rowlist['coin']);
        }else{

        }
        //print_r($coinlist);
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('rowlist',$rowlist);
        $this->assign('coinlist',$coinlist);
        return $this->fetch('report/coinloop/add');
    }

    /**
     * 硬币自循环报表-修改
     * @return mixed
     */
    public function coinloop_edit(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/coin-loop',$this->report_url);
        $data = [];
        $coinlist = [];
        $coinlist['return'] = [];
        $coinlist['delivery'] = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            if($data['coinCirculation']){
                $coinlist = format_coin_type($data['coinCirculation']);
            }
        }else{

        }

        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('coinlist',$coinlist);
        return $this->fetch('report/coinloop/edit');
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
                    $data['bankList'][$key]['bankName'] = get_bank($val['bankCode']);
                    $data['bankList'][$key]['filloutType'] =get_fill_type($val['fillout']);
                }
            }
            if($data['coinCirculation']){
                $coinlist = format_coin_type($data['coinCirculation']);
            }
        }else{

        }
        //print_r($coinlist);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('coinlist',$coinlist);
        $this->assign('head',$head);
        return $this->fetch('report/coinloop/detail');
    }




    /**
     * 现金收支报表-填写
     * @return mixed
     */
    public function cashcollect_add(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['reportNum'=>$reportNum],'/rows',$this->report_url);
        $data = [];
        $rowlist = [];
        $rowlist['coin'] = [];
        $rowlist['equipment'] = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
            $rowlist = format_rowlist_type($data['rowList']);
        }else{

        }

        $this->assign('data',$data);
        $this->assign('rowlist',$rowlist);


        $lastYear = $year-1;
        $result2 = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$lastYear, 'quarter'=>$quarter],'/get/income-expenditure',$this->report_url);
        $data2 = [];
        $rowlist2 = [];
        $rowlist2['out'] = [];
        $rowlist2['in'] = [];
        $rowlist2['total'] = [];
        if($result2['err_code'] == 0){
            $data2 = $result2['data'];
            if($data2['tableData']){
                $rowlist2 = format_collect_type($data2['tableData']);
            }
        }else{

        }
        //print_r($data);
        //print_r($rowlist2);
        $this->assign('year',$year);
        $this->assign('quarter',$quarter);
        $this->assign('bankCode',$bankCode);
        $this->assign('reportNum',$reportNum);
        $this->assign('data',$data);
        $this->assign('rowlist',$rowlist);
        $this->assign('data2',$data2);
        $this->assign('rowlist2',$rowlist2);

        return $this->fetch('report/cashcollect/add');
    }

    /**
     * 现金收支报表-修改
     * @return mixed
     */
    public function cashcollect_edit(){
        $year = $this->request->param('year');
        $quarter = $this->request->param('quarter');
        $bankCode = $this->request->param('bankCode');
        $reportNum = $this->request->param('reportNum');
        $result = doCurlGetRequest(['bankCode'=>$bankCode,'year'=>$year, 'quarter'=>$quarter],'/get/income-expenditure',$this->report_url);
        $data = [];
        $rowlist = [];
        $rowlist['out'] = [];
        $rowlist['in'] = [];
        $rowlist['total'] = [];
        if($result['err_code'] == 0){
            $data = $result['data'];
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
        return $this->fetch('report/cashcollect/edit');
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