<?php
namespace app\api\controller;

use app\common\model\UploadLog;
use think\Controller;
use think\Session;

/**
 * 坐支接口
 * Class Upload
 * @package app\api\controller
 */
class Inout extends Controller
{
    private $inout_url = '';
    protected $upload_log_model;
    protected function _initialize()
    {
        parent::_initialize();
        if (!Session::has('admin_id')) {
            $result = [
                'error'   => 1,
                'message' => '未登录'
            ];

            return json($result);
        }
        $this->inout_url = config('inout_url');
    }

    /**
     * 各商业银行坐支情况汇总
     * @return \think\response\Json
     */
    public function all_bank_data(){
        $data = $this->request->param();
        $result = curl_request($data, '/in-out/all-bank-data', false, $this->inout_url);
        return json($result);
    }

    /**
     * 支行情况统计
     * @return \think\response\Json
     */
    public function branch_bank_data(){
        $data = $this->request->param();
        $result = curl_request($data, '/in-out/branch-bank-data', false, $this->inout_url);
        if($result['errorCode'] == 0  && $result['data']['list'] != null){
            foreach ($result['data']['list'] as $key => $val){
                $result['data']['list'][$key]['branchBankOutTotal_str'] = number_format($val['branchBankOutTotal'],2,'.',',');
                $result['data']['list'][$key]['inOutTotal_str'] = number_format($val['inOutTotal'],2,'.',',');
            }
        }
        return json($result);
    }

    /**
     * 商业银行网点现金支出情况统计
     * @return \think\response\Json
     */
    public function bank_netbank_search(){
        $data = $this->request->param();
        $result = curl_request($data, '/in-out/bank-netbank-search', false, $this->inout_url);
        if($result['errorCode'] == 0  && $result['data'] != null){
            $list = $result['data'];
            $tmp = [];
//            foreach ($list as $key => $val){
//                $arr = [];
//                if($val[0]['businessType'] == 1){
//                    $arr['in'] = $val[0];
//                    $arr['out'] = $val[1];
//                }elseif($val[0]['businessType'] == 2){
//                    $arr['out'] = $val[0];
//                    $arr['in'] = $val[1];
//                }
//
//                $tmp[] = $arr;
//            }
//            $result['data']['list'] = $tmp;
        }
        return json($result);
    }   


    /**
     * 修改-商业银行网点现金支出情况统计
     * @return \think\response\Json
     */
    public function update_netbank_inout_data(){
        $data = $this->request->param();
        $result = curl_request(json_encode($data), '/in-out/update-netbank-inout-data', true, $this->inout_url);
        return json($result);
    }

    /**
     * 业务查询
     * @return \think\response\Json
     */
    public function bis_search(){
        $data = $this->request->param();
        $result = curl_request($data, '/bis/search', false, $this->inout_url);
        if($result['errorCode'] == 0  && $result['data']['detailResponseList'] != null){
            foreach ($result['data']['detailResponseList'] as $key => $val){
                $result['data']['detailResponseList'][$key]['bisMoneyTotal_str'] = number_format($val['bisMoneyTotal'],2,'.',',');
                $result['data']['detailResponseList'][$key]['branchCode_str'] = get_bank_branch($val['branchCode']);
                $result['data']['detailResponseList'][$key]['bisType_str'] = show_business_type($val['bisType']);
            }
        }
        return json($result);
    }

    /**
     * 业务查询详情
     * @return \think\response\Json
     */
    public function bis_detail(){
        $data = $this->request->param();
        $result = curl_request($data, '/bis/detail', false, $this->inout_url);
        if($result['errorCode'] == 0  && $result['data']['list'] != null){
            foreach ($result['data']['list'] as $key => $val){
                $result['data']['list'][$key]['status_str'] = show_gzh_type($val['status']);
            }
        }
        return json($result);
    }
}
