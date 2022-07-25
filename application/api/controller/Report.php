<?php
namespace app\api\controller;
use think\Controller;

/**
 * 报表接口
 * Class Upload
 * @package app\api\controller
 */
class Report extends Controller
{
    private $report_url = '';
    protected function _initialize()
    {
        parent::_initialize();
        $this->report_url = config('report_url');
    }
    /*创建报表任务*/
    public function create_task(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/create/task',$this->report_url);
        return json($result);
    }

    /*获取任报表列表*/
    public function reports(){
        $data = $this->request->param(false);
        $result = doCurlGetRequest($data,'/report-infos',$this->report_url);
        if($result['err_code'] == 0 && $result['data'] != null){
            foreach ($result['data']['reportInfos'] as $key => $val){
                $result['data']['reportInfos'][$key]['report_str'] = set_report_file($val['reportNum']);
            }
        }
        return json($result);
    }

    /*获取任务列表*/
    public function tasks(){
        $data = $this->request->param(false);
        $data['bank_code'] = get_cur_bank();
        $result = doCurlGetRequest($data,'/tasks',$this->report_url);
        return json($result);
    }

    /*删除任务*/
    public function del(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/delete/task',$this->report_url);
        return json($result);
    }

    /*人行-现金库存-下载报表 - 可共用 */
    public function cash_stock_down(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/download',$this->report_url);
        return json($result);
    }
    public function report_down(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/download',$this->report_url);
        return json($result);
    }


    /*商行-现金库存-填写报表*/
    public function cask_stock_add(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/save/cash-stock',$this->report_url);
        return json($result);
    }
    /*商行-现金库存-修改报表*/
    public function cash_stock_modify(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/modify/cash-stock',$this->report_url);
        return json($result);
    }


    /*商行-中小面额-填写报表*/
    public function small_amount_add(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/save/put-ret-statis',$this->report_url);
        return json($result);
    }
    /*商行-中小面额-修改报表*/
    public function small_amount_modify(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/modify/put-ret-statis',$this->report_url);
        return json($result);
    }



    /*商行-现金投放-填写报表*/
    public function cash_delivery_add(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/save/supply-return-plan',$this->report_url);
        return json($result);
    }
    /*商行-现金投放-修改报表*/
    public function cash_delivery_modify(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/modify/supply-return-plan',$this->report_url);
        return json($result);
    }


    /*商行-现金清分-填写报表*/
    public function cash_clear_add(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/save/clean-statis',$this->report_url);
        return json($result);
    }
    /*商行-现金清分-修改报表*/
    public function cash_clear_modify(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/modify/clean-statis',$this->report_url);
        return json($result);
    }



    /*商行-硬币自循环-填写报表*/
    public function coin_loop_add(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/save/coin-loop',$this->report_url);
        return json($result);
    }
    /*商行-现硬币自循环-修改报表*/
    public function coin_loop_modify(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/modify/coin-loop',$this->report_url);
        return json($result);
    }




    /*商行-现金收支-填写报表*/
    public function cash_collect_add(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/save/income-expenditure',$this->report_url);
        return json($result);
    }
    /*商行-现金收支-修改报表*/
    public function cash_collect_modify(){
        $data = $this->request->param(false);
        $result = doCurlPostRequest($data,'/modify/income-expenditure',$this->report_url);
        return json($result);
    }
}