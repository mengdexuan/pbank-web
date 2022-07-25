<?php
namespace app\api\controller;

use think\Controller;

/**
 * 大数据接口
 * Class Bigdata
 * @package app\api\controller
 */
class Bigdata extends Controller
{
    private $bigdata_url = '';
    protected function _initialize()
    {
        parent::_initialize();

        $this->bigdata_url = config('bigdata_url');
    }
    /*
     * 冠字号模糊查询
     * @return \think\response\Json
     *
     * */
    public function gzh_search(){
        $data = $this->request->param();
        $result = curl_request($data,'/gzh/search',false,config('interface_url'));
        //print_r($result);
        if($result['err_code'] == 0 && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                $data[$key]['update_time'] = date('Y-m-d H:i:s',$data[$key]['updateTime']/1000);
                if($data[$key]['bank'] == null ) {
                    $data[$key]['bank_str'] = '--';
                }else {
                    $data[$key]['bank_str'] = get_bank(get_bank_branch($data[$key]['bank']));
                }
            }
            // //查询把  后期添加
            // $parseUrl = parse_url(config('interface_url'));
            // $interfaceUrl = 'http://'.$parseUrl['host'].':'.$parseUrl['port'];
            // $handCode = doCurlGetRequest(['gzh'=>$this->request->param('handData')],'/buss_center/outer/handle/queryHandleCodeByGzh',$interfaceUrl);
            // $result['handCodeRes'] = $handCode['data'];
            // if (!isset($handCode['data']) || isset(json_decode($handCode['data'],true)['errorCode'])) {
            //    $result['handCodeRes'] = '无';
            // }
            $result['data'] = $data;
        }
        return json($result);
    }
    /*
     * 冠字号模糊查询 (新做 Mon Jul 27 2020 17:03:52 )
     * @return \think\response\Json
     *
     * */
    public function new_gzh_search(){
        $data = $this->request->param();
        $result = curl_request($data,'/gzhRecord',false,config('interface_url'));
        return json($result);
    }
    /*
     * 冠字号轨迹
     * @return \think\response\Json
     *
     * */
    public function gzh_record(){
        $data = $this->request->param();
        $result = curl_request($data,'/gzh/record',false,config('interface_url'));
        if($result['err_code'] == 0 && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                //$data[$key]['update_time'] = date('Y-m-d H:i:s',$data[$key]['updateTime']/1000);
                $data[$key]['status_str'] = get_gzh_type($data[$key]['status']);
                if($data[$key]['bank'] == null ) {
                    $data[$key]['bank_str'] = '--';
                }else {
                    $data[$key]['bank_str'] = get_bank_branch($data[$key]['bank']);
                }
                if($data[$key]['gzhData']['netCode'] == null) {
                    $data[$key]['gzhData']['netCode'] = '--';
                }else{
                    $data[$key]['gzhData']['netCode'] = get_bank_branch($data[$key]['gzhData']['netCode']);
                }

            }
            $result['data'] = $data;
        }
        return json($result);
    }
    /*
     * 包号模糊查询
     * @return \think\response\Json
     *
     * */
    public function packs_search(){
        $data = $this->request->param();
        $result = curl_request($data,'/pack/search',false,config('interface_url'));
        if($result['err_code'] == 0 && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                $data[$key]['status_str'] = get_pack_type($data[$key]['status']);
                $data[$key]['amount_str'] = number_format($data[$key]['amount'],2,'.',',');
                $data[$key]['bank_str'] = get_bank_branch($data[$key]['bank']);
                $data[$key]['update_time'] = date('Y-m-d H:i:s',$data[$key]['updateTime']/1000);
            }
            $result['data'] = $data;
        }
        return json($result);
    }
    /*
     * 包号模糊查询 (新做 Mon Jul 27 2020 14:23:36 )
     * @return \think\response\Json
     *
     * */
    public function new_packs_search(){
        $data = $this->request->param();
        $result = curl_request($data,'/packRecord',false,config('interface_url'));
        return json($result);
    }
    /*
     * 包号轨迹
     * @return \think\response\Json
     *
     * */
    public function packs_record(){
        $data = $this->request->param();
        $result = curl_request($data,'/pack/record',false,config('interface_url'));
        //print_r($result);
        if($result['msg'] == '成功' && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                $data[$key]['status_str'] = get_pack_record_status($data[$key]['status']);
                $data[$key]['bank_str'] = get_bank(get_bank_branch($data[$key]['bank']));
                if (empty($data[$key]['packData']['task'])) {
                    $data[$key]['packData']['task'] = '<a javascript:;>--</a>';
                }
                if($data[$key]['packData']['fromNetCode'] == null) {
                    $data[$key]['packData']['from'] = '--';
                }else{
                    // $data[$key]['packData']['from'] = get_bank(get_bank_branch($data[$key]['packData']['fromNetCode']));
                    $data[$key]['packData']['from'] = getBankNameByCode($data[$key]['packData']['fromNetCode']);
                }
                if($data[$key]['packData']['toNetCode'] == null) {
                    $data[$key]['packData']['to'] = '--';
                }else{
                    // $data[$key]['packData']['to'] = get_bank(get_bank_branch($data[$key]['packData']['toNetCode']));
                    $data[$key]['packData']['to'] = getBankNameByCode($data[$key]['packData']['toNetCode']);
                }
            }
            $result['data'] = $data;
        }
        return json($result);
    }
    /*
     * 捆号模糊查询
     * @return \think\response\Json
     *
     * */
    public function bundle_search(){
        $data = $this->request->param();
        $result = curl_request($data,'/bundle/search',false,config('interface_url'));
        if($result['err_code'] == 0 && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                $data[$key]['status_str'] = get_bundle_type($data[$key]['status']);
                $data[$key]['amount_str'] = number_format($data[$key]['amount'],2,'.',',');
                $data[$key]['bank_str'] = get_bank_branch($data[$key]['bank']);
                $data[$key]['update_time'] = date('Y-m-d H:i:s',$data[$key]['updateTime']/1000);
            }
            
            $result['data'] = $data;
        }
        return json($result);
    }
    /*
     * 捆号模糊查询 (新做 Mon Jul 27 2020 16:54:18 )
     * @return \think\response\Json
     *
     * */
    public function new_bundle_search(){
        $data = $this->request->param();
        $result = curl_request($data,'/bundleRecord',false,config('interface_url'));
        return json($result);
    }

    //把查询轨迹
    public function hand_search()
    {
        //查询把  后期添加
        // $parseUrl = parse_url(config('interface_url'));
        // $interfaceUrl = 'http://'.$parseUrl['host'].':'.$parseUrl['port'];
        // $handCode = doCurlGetRequest(['handleCode'=>$this->request->param('code')],'/buss_center/outer/mbank/handle/queryBundleCodeByHandleCode',$interfaceUrl);
        // if (!isset($handCode['data']) || isset(json_decode($handCode['data'],true)['errorCode'])) {
        //    $msg['err_msg'] = '无数据';
        //    return json($msg);
        // }
        $data = $this->request->param();
        $result = curl_request($data,'/handle/search',false,config('interface_url'));
        if($result['err_code'] == 0 && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                $data[$key]['status_str'] = get_bundle_type($data[$key]['status']);
                $data[$key]['amount_str'] = number_format($data[$key]['amount'],2,'.',',');//去掉除以１０
                $data[$key]['bank_str'] = get_bank(get_bank_branch($data[$key]['bank']));
                $data[$key]['update_time'] = date('Y-m-d H:i:s',$data[$key]['updateTime']/1000);
            }
            //把号
            $result['handCodeRes'] = $this->request->param('code');
            $result['data'] = $data;
        }
        return json($result);
    }
    /*
     * 捆号轨迹
     * @return \think\response\Json
     *
     * */
    public function bundle_record(){
        $data = $this->request->param();
        $result = curl_request($data,'/bundle/record',false,config('interface_url'));
        if($result['err_code'] == 0 && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                $data[$key]['status_str'] = get_bundle_record_status($data[$key]['status']);
                $data[$key]['bank_str'] = get_bank(get_bank_branch($data[$key]['bank']));
                // if($data[$key]['status'] == 1 || $data[$key]['status'] == 2){
                //     $data[$key]['task_type'] = 'agent';
                // }elseif ($data[$key]['status'] == 3 || $data[$key]['status'] == 4 || ($data[$key]['status'] == 20 && $data[$key]['bundleData']['task']) || ($data[$key]['status'] == 40 && $data[$key]['bundleData']['task'])){
                //     $data[$key]['task_type'] = 'task';
                // }else{
                //     $data[$key]['task_type'] = 'other';
                // }
                if (empty($data[$key]['bundleData']['task'])) {
                    $data[$key]['bundleData']['task'] = '<a javascript:;>--</a>';
                }
                if (empty($data[$key]['bundleData']['packCode'])) {
                    $data[$key]['bundleData']['packCode'] = '<a javascript:;>--</a>';
                }
                if($data[$key]['bundleData']['fromNetCode'] == null) {
                    $data[$key]['bundleData']['from'] = '--';
                }else{
                    // $data[$key]['bundleData']['from'] = get_bank(get_bank_branch($data[$key]['bundleData']['fromNetCode']));
                    $data[$key]['bundleData']['from'] = getBankNameByCode($data[$key]['bundleData']['fromNetCode']);
                }
                if($data[$key]['bundleData']['toNetCode'] == null) {
                    $data[$key]['bundleData']['to'] = '--';
                }else{
                    // $data[$key]['bundleData']['to'] = get_bank(get_bank_branch($data[$key]['bundleData']['toNetCode']));
                    $data[$key]['bundleData']['to'] = getBankNameByCode($data[$key]['bundleData']['toNetCode']);
                }

                if($data[$key]['bundleData']['packCode'] == null) {
                    $data[$key]['bundleData']['packCode'] = '';
                }
            }
            $result['data'] = $data;
        }
        return json($result);
    }

    public function handle_record()
    {
        $data = $this->request->param();
        $result = curl_request($data,'/handle/record',false,config('interface_url'));
        if($result['err_code'] == 0 && $result['data']){
            $data = $result['data'];
            foreach ($data as $key => $val){
                $data[$key]['status_str'] = get_bundle_record_status($data[$key]['status']);
                $data[$key]['bank_str'] = get_bank(get_bank_branch($data[$key]['bank']));
                // if($data[$key]['status'] == 1 || $data[$key]['status'] == 2){
                //     $data[$key]['task_type'] = 'agent';
                // }elseif ($data[$key]['status'] == 3 || $data[$key]['status'] == 4 || ($data[$key]['status'] == 20 && $data[$key]['handleData']['task']) || ($data[$key]['status'] == 40 && $data[$key]['handleData']['task'])){
                //     $data[$key]['task_type'] = 'task';
                // }else{
                //     $data[$key]['task_type'] = 'other';
                // }
                if (empty($data[$key]['handleData']['task'])) {
                    $data[$key]['handleData']['task'] = '<a javascript:;>--</a>';
                }
                if (empty($data[$key]['handleData']['packCode'])) {
                    $data[$key]['handleData']['packCode'] = '<a javascript:;>--</a>';
                }
                if($data[$key]['handleData']['fromNetCode'] == null) {
                    $data[$key]['handleData']['from'] = '--';
                }else{
                    // $data[$key]['handleData']['from'] = get_bank(get_bank_branch($data[$key]['handleData']['fromNetCode']));
                    $data[$key]['handleData']['from'] = getBankNameByCode($data[$key]['handleData']['fromNetCode']);
                }
                if($data[$key]['handleData']['toNetCode'] == null) {
                    $data[$key]['handleData']['to'] = '--';
                }else{
                    // $data[$key]['handleData']['to'] = get_bank(get_bank_branch($data[$key]['handleData']['toNetCode']));
                    $data[$key]['handleData']['to'] = getBankNameByCode($data[$key]['handleData']['toNetCode']);
                }

                if($data[$key]['handleData']['packCode'] == null) {
                    $data[$key]['handleData']['packCode'] = '';
                }
            }
            $result['data'] = $data;
        }
        return json($result);        
    }

}