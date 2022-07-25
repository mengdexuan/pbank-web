<?php
namespace app\api\controller;

use app\common\model\UploadLog;
use think\Controller;
use think\Session;
/**
 * 银行接口
 * Class Upload
 * @package app\api\controller
 */
class Bank extends Controller
{
    private $interface_url = '';
    protected $upload_log_model;
    static protected $version = null;
    protected function _initialize()
    {
        parent::_initialize();
        if (is_null(self::$version)) {
            $unified_version = config('unified_version');
            if ($unified_version == '宁波') {
                self::$version = 'n';
            }else if ($unified_version == '江苏') {
                self::$version = 'j';
            }else{
                self::$version = 'j';
            }
        }
        if (!Session::has('admin_id')) {
            $result = [
                'error'   => 1,
                'message' => '未登录'
            ];

            return json($result);
        }
        $this->interface_url = config('interface_url');
    }

    /**
     * 上传捆接口
     * @return \think\response\Json
     */
    public function upload_bundle_file(){
        if ($this->request->isPost()) {

            $config = [
                'size' => 2097152,
                'ext'  => 'fsn,zip,gzh'
            ];

            $file = $this->request->file('file');
            $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
            $info        = $file->validate($config)->move($upload_path);
            if ($info) {
                $upload_log_model = new UploadLog();
                $username = session('admin_name');
                if (class_exists('\CURLFile')) {
                    $multiple_files = new \CURLFile(realpath($info->getPathname()));
                } else {
                    $multiple_files = '@' . realpath($info->getPathname());
                }
                $data = [
                    'op_name'=>$username,
                    'multiple_files'=>$multiple_files
                ];
                $res = doCurlPostRequest($data,'/upload_bundle_file');
                if($res){
                    $server_res = json_decode($res);
                    if($server_res->err_code == 0){
                        $data = [
                            'file_name'=>  $info->getFilename(),
                            'path'=>  $info->getPath(),
                            'uploader'=>  $username,
                            'dateline'=>  time()
                        ];
                        $upload_log_model->save($data);

                    }
                    $result = [
                        'err_code' => $server_res->err_code,
                        'err_msg'   => $server_res->err_msg,
                        'data' => isset($server_res->data)?$server_res->data:[]
                    ];

                }else{
                    throw exception('上传超时',500);
                }

            } else {
                $result = [
                    'err_code' => 1,
                    'err_msg'   => $file->getError()
                ];
            }

            return json($result);
        }
    }

    public function test(){
        dump($this->request->post());
        return json($this->request->post());
    }
    /**
     * 下载捆接口
     * @return \think\response\Json
     */
    public function upload_file(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $result = doCurlGetRequest($data);
            return json($result);
        }
    }
    /**
     * 打包接口
     * @return \think\response\Json
     */
    public function pack(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['op_name'] = $username;
            $data['bundle_count'] = count($data['bundle_codes']);
            if($data['bundle_count'] == 20 || $data['bundle_count'] == 40){
                $data['pack_amount'] = $data['bundle_count']*100000;
                $data['bundle_codes'] = implode(',',$data['bundle_codes']);
                $result = doCurlPostRequest($data,'/pack');
            }else{
                $result = [
                    'err_code' => 1,
                    'err_msg'   => '提交失败，捆数不符合要求，只能打包20捆或40捆。'
                ];

            }

            return json($result);
        }
    }
    /**
     * 拆包接口
     * @return \think\response\Json
     */
    public function unpack(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['op_name'] = $username;
            $result = doCurlPostRequest($data,'/unpack');
            return json($result);
        }
    }

    /**
     *Gzh生成策略
     * @return \think\response\Json
     */
    public function filesGzhEdit(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $result = doCurlPostRequest(json_encode($data),'/gzhmake/gzhMakeStrategy/addOrUpdate',config('gen_star'),10,true);
            return json($result);
        }
    }

    /**
     *获取GZH生成策略
     * @return \think\response\Json
     */
    public function getGzhEdit(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/gzhmake/gzhMakeStrategy/get',false,config('gen_star'));
        return json($result);
    }

    /**
     *GZH文件列表查询
     */
    public function FgzhList(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/gzhmake/gzhFile/pageQuery',false,config('gen_star'));
        if ($result['success'] && $result['data'] != null){
            foreach ($result['data']['pageInfo']['content'] as $k => $v){
                $temp = explode(',',$v['bizType']);
                $bizType = '';
                foreach ($temp as $vt){
                    $bizType .= getFilesGzhType($vt) . "<br/>";
                }
                $result['data']['pageInfo']['content'][$k]['bizType'] = $bizType;
                $result['data']['pageInfo']['content'][$k]['taskType'] = getFilesGzhType($v['taskType']);
            }
        }
        return json($result);
    }
    /**
     * 创建任务单接口
     * @return \think\response\Json
     */
    public function create_task(){
        if ($this->request->isPost()) {
            $data = $this->request->param(false);
            $username = session('admin_name');
            $data['op_name'] = $username;
            if (strpos($data['task_amount'],'.')) {
                $data['task_amount']  = strstr($data['task_amount'],'.',true);
            }
            if (strpos($data['task_amount'],',')) {
                $data['task_amount'] = str_replace(',','',$data['task_amount']);
            }
            $result = doCurlPostRequest($data,'/create_task');
            $result['url'] = url('/admin/task/'.self::$version.'index');
            return json($result);
        }
    }
    /**
     * 修改任务单接口
     * @return \think\response\Json
     */
    public function update_task(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $valutaList = explode(',',$data['valutaList']);
            foreach ($valutaList as $k => $v) {
                $res['valutaList'][$k]['valutaCode'] = explode(';',$v)[0];
                $res['valutaList'][$k]['valutaAmount'] = explode(';',$v)[1];
            }
            $data['valutaList'] = $res['valutaList'];
            $result = doCurlPostRequest(json_encode($data),'/taskChange/change','',10,true);
            return json($result);
        }
    }
    /**
     * 修改任务单接口
     * @return \think\response\Json
     */
    public function modify_task(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['op_name'] = $username;
            $data['task_amount'] = $data['task_amount'] * 10000;
            $result = doCurlPostRequest($data,'/modify_task');
            $result['url'] = url('/admin/plan/list_plan');
            return json($result);
        }
    }
    /**
     * 删除任务单接口
     * @return \think\response\Json
     */
    public function remove_task(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['op_name'] = $username;
            $result = doCurlPostRequest($data,'/remove_task');
            if (isset($data['url'])){
                $result['url']  = $data['url'];
            }else{
                $result['url'] = url('/admin/plan/list_plan');
            }
            return json($result);
        }
    }
    /**
     * 审核任务单接口
     * @return \think\response\Json
     */
    public function approve_task(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['op_name'] = $username;
            $result = doCurlPostRequest($data,'/approve_task');
            $result['url'] = url('/admin/plan/list_plan');
            return json($result);
        }
    }
    /**
     * 出库接口
     * @return \think\response\Json
     */
    public function out_room(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['op_name'] = $username;
            if(isset($data['pack_codes'])){
                if(count($data['pack_codes']) > 0){
                    $data['pack_codes'] = implode(',',$data['pack_codes']);
                }
            }else{
                $data['pack_codes'] = '';
            }
            //兼容捆和把
            if(isset($data['bundle_codes'])){
                if(count($data['bundle_codes']) > 0){
                    $data['bundle_codes'] = implode(',',$data['bundle_codes']);
                }
            }else{
                $data['bundle_codes'] = '';
            }
            if(isset($data['handle_codes'])){
                if(count($data['handle_codes']) > 0){
                    $data['handle_codes'] = implode(',',$data['handle_codes']);
                }
            }else{
                $data['handle_codes'] = '';
            }
            //print_r($data);
            $result = doCurlPostRequest($data,'/out_room');
            return json($result);
        }
    }
    /**
     * 入库接口
     * @return \think\response\Json
     */
    public function in_room(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['opName'] = $username;
            $data['packs'] = explode(',', $data['dataBKH']['packs']);
            $data['bundles'] = explode(',', $data['dataBKH']['hundles']);
            $data['handles'] = explode(',', $data['dataBKH']['handles']);
            unset($data['dataBKH']);
            $data['packs'][0] ? $data['packs'] : $data['packs'] = null;
            $data['bundles'][0] ? $data['bundles'] : $data['bundles'] = null;
            $data['handles'][0] ? $data['handles'] : $data['handles'] = null;
            // echo '<pre/>';
            // var_dump($data);exit;
            $result = doCurlPostRequest(json_encode($data),'/in_room','',10,true);
            return json($result);
        }
    }
    /**
     * 查询捆详情
     * @return \think\response\Json
     */
    public function query_bundle_detail(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_bundle_detail');
        //print_r($result);
        if($result['err_code'] == 0 && $result['data'] != null ){

            $result['data']['creator_bank_str'] = get_bank($result['data']['creator_bank']);
            $result['data']['owner_bank_str'] = get_bank($result['data']['owner_bank']);
            $result['data']['bundle_amount_str'] = number_format($result['data']['bundle_amount'],2,'.',',');
            $result['data']['is_owner'] = 0;
            if($result['data']['owner_bank'] == get_cur_bank()){
                $result['data']['is_owner'] = 1;
            }

        }
        return json($result);
    }
    /**
     * 查询任务中的捆捆
     * @return \think\response\Json
     */
    public function query_bundle_pack_task(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_bundle_pack_task');
        //print_r($result);
        if($result['err_code'] == 0 && $result['data'] != null ){

            $result['data']['creator_bank_str'] = get_bank($result['data']['creator_bank']);
            $result['data']['owner_bank_str'] = get_bank($result['data']['owner_bank']);
            $result['data']['bundle_amount_str'] = number_format($result['data']['bundle_amount'],2,'.',',');
            $result['data']['is_owner'] = 0;
            if($result['data']['owner_bank'] == get_cur_bank()){
                $result['data']['is_owner'] = 1;
            }

        }
        return json($result);
    }
    /**
     * 查询捆中的冠字号
     * @return \think\response\Json
     */
    public function query_gzh_in_bundle(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $result = doCurlGetRequest($data);
            return json($result);
        }
    }
    /**
     * 查询包详情
     * @return \think\response\Json
     */
    public function query_pack_detail(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_pack_detail');
        //print_r($result);
        if($result['err_code'] == 0 && $result['data'] != null){
            foreach ($result['data']['bundles'] as $key => $val){
                $result['data']['bundles'][$key]['creator_bank_str'] = get_bank($val['creator_bank']);
                $result['data']['bundles'][$key]['creator_bank_short_str'] = get_bank($val['creator_bank'],'bank_short_name');
                $result['data']['bundles'][$key]['creator_bank_subordinate_str'] = get_bank($val['creator_bank'],'bank_subordinate');
                $result['data']['bundles'][$key]['owner_bank_str'] = get_bank($val['owner_bank']);
                $result['data']['bundles'][$key]['owner_bank_short_str'] = get_bank($val['owner_bank'],'bank_short_name');
                $result['data']['bundles'][$key]['owner_bank_subordinate_str'] = get_bank($val['owner_bank'],'bank_subordinate');
                $result['data']['is_owner'] = 0;
                if($result['data']['owner_bank'] == get_cur_bank()){
                    $result['data']['is_owner'] = 1;
                }
            }
            $result['data']['pack_amount_str'] = number_format($result['data']['pack_amount'],2,'.',',');
        }
        return json($result);
    }
    /**
     * 查询任务中的包详情
     * @return \think\response\Json
     */
    public function query_pack_task(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_pack_task');
        //print_r($result);
        if($result['err_code'] == 0 && $result['data'] != null){
            foreach ($result['data']['bundles'] as $key => $val){
                $result['data']['bundles'][$key]['creator_bank_str'] = get_bank($val['creator_bank']);
                $result['data']['bundles'][$key]['creator_bank_short_str'] = get_bank($val['creator_bank'],'bank_short_name');
                $result['data']['bundles'][$key]['creator_bank_subordinate_str'] = get_bank($val['creator_bank'],'bank_subordinate');
                $result['data']['bundles'][$key]['owner_bank_str'] = get_bank($val['owner_bank']);
                $result['data']['bundles'][$key]['owner_bank_short_str'] = get_bank($val['owner_bank'],'bank_short_name');
                $result['data']['bundles'][$key]['owner_bank_subordinate_str'] = get_bank($val['owner_bank'],'bank_subordinate');
                $result['data']['is_owner'] = 0;
                if($result['data']['owner_bank'] == get_cur_bank()){
                    $result['data']['is_owner'] = 1;
                }
            }
            $result['data']['pack_amount_str'] = number_format($result['data']['pack_amount'],2,'.',',');
        }
        return json($result);
    }

    /**
     * 查询任务中的捆状态
     * @return \think\response\Json
     */
    public function fsn_file_status(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/fsn_file_status');
        return json($result);
    }


    /**
     * 查询库存包列表
     * @return \think\response\Json
     */
    public function query_available_packs(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_available_packs');
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['packs'] as $key => $val){
                $result['data']['packs'][$key]['creator_bank_str'] = getBankNameByCode($val['creator_bank']);
                $result['data']['packs'][$key]['owner_bank_str'] = getBankNameByCode($val['owner_bank']);
                $result['data']['packs'][$key]['pack_amount_str'] = number_format($val['pack_amount'],2,'.',',');
            }
        }
        return json($result);
    }
    /**
     * 查询残损券
     * @return \think\response\Json
     */
    public function query_damaged_stock(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_damaged_stock');
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['damaged'] as $key => $val){
                if($val['valuta'] == 0){
                    $result['data']['damaged'][$key]['valuta'] = '其他';
                    $result['data']['damaged'][$key]['valuta_number'] = '--';
                }
                $result['data']['damaged'][$key]['amount'] = number_format($val['amount'],2,'.',',');

            }
        }
        return json($result);
    }
    /**
     * 查询残损券任务
     * @return \think\response\Json
     */
    public function query_stock_task(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_stock_task');
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['tasks'] as $key => $val){
                $result['data']['tasks'][$key]['in_bank_str'] = get_bank($val['in_bank']);
                $result['data']['tasks'][$key]['out_bank_str'] = get_bank($val['out_bank']);
                $result['data']['tasks'][$key]['task_amount'] = number_format($val['task_amount'],2,'.',',');
                $result['data']['tasks'][$key]['task_status_str'] = get_task_status($val['task_status']);
            }
        }
        return json($result);
    }
    /**
     * 查询库存捆列表
     * @return \think\response\Json
     */
    public function query_available_bundles(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $result = doCurlGetRequest($data);
            return json($result);
        }
    }
    /**
     * 查询任务单详情
     * @return \think\response\Json
     */
    public function query_task_detail(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_task_detail');
        //print_r($result);
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['packs'] as $key => $val){
                $result['data']['packs'][$key]['creator_bank_str'] = get_bank($val['creator_bank']);
                $result['data']['packs'][$key]['pack_status_str'] = get_task_status($val['pack_status']);
                $result['data']['packs'][$key]['pack_amount_str'] = number_format($val['pack_amount'],2,'.',',');
            }
        }
        return json($result);
    }
    /**
     * 条件查询任务单
     * @return \think\response\Json
     */
    public function query_tasks(){

        $data = $this->request->param();
        if(isset($data['date_type']) && $data['date_type'] == 'today'){
            $data['begin_time'] = date('Y-m-d',time());
            $data['end_time'] = $data['begin_time'];
        }elseif(isset($data['date_type']) && $data['date_type'] == 'yesterday'){
            $data['begin_time'] = date('Y-m-d',(time()-60*60*24));
            $data['end_time'] = $data['begin_time'];
        } elseif(isset($data['date_type']) && $data['date_type'] == 'tomorrow'){
            $data['begin_time'] = date('Y-m-d',(time()+60*60*24));
        }
        $result = doCurlGetRequest($data,'/query_tasks');
        $cur_bank = get_cur_bank();
        if($result['err_code'] == 0  && $result['data'] != null){

            foreach ($result['data']['tasks'] as $key => $val){
                $is_curr = 0;
                if($val['in_bank'] == get_cur_bank()){
                    $result['data']['tasks'][$key]['in_bank_str'] = '本行';
                    $is_curr = 1;
                }else{
                    $result['data']['tasks'][$key]['in_bank_str'] = get_bank($val['in_bank'],'bank_short_name');
                }
                if($val['out_bank'] == get_cur_bank()){
                    $result['data']['tasks'][$key]['out_bank_str'] = '本行';
                    $is_curr = 1;
                }else{
                    $result['data']['tasks'][$key]['out_bank_str'] = get_bank($val['out_bank'],'bank_short_name');
                }
                $result['data']['tasks'][$key]['is_curr'] = $is_curr;
                $result['data']['tasks'][$key]['task_status_str'] = get_task_status($val['task_status']);
                $result['data']['tasks'][$key]['track_amount_str'] = number_format($val['task_double_amount'],2,'.',',');
                // $result['data']['tasks'][$key]['untrack_amount_str'] = $val['task_amount']-$val['track_amount'] > 0?number_format($val['task_amount']-$val['track_amount'],2,'.',','):0;
                $result['data']['tasks'][$key]['task_amount_str'] = number_format($val['task_amount'],2,'.',',');
                $result['data']['tasks'][$key]['task_type_str'] = get_task_type($val['task_type']);
                $result['data']['tasks'][$key]['is_out'] = 0;
                $result['data']['tasks'][$key]['is_in'] = 0;
                if($val['task_status'] == 1 && $val['out_bank'] == $cur_bank && $val['task_time'] == date('Y-m-d',time())){
                    $result['data']['tasks'][$key]['is_out'] = 1;
                }
                if($val['task_status'] == 2 && $val['in_bank'] == $cur_bank){
                    $result['data']['tasks'][$key]['is_in'] = 1;
                }
            }
        }
        //print_r($result);
        return json($result);
    }
    /**
     * 条件查询上链确权列表
     * @return \think\response\Json
     */
    public function query_confirmations(){

        $data = $this->request->param();
        if(isset($data['date_type']) && $data['date_type'] == 'today'){
            $data['beginDate'] = date('Y-m-d',time());
            $data['endDate'] = $data['beginDate'];
        }elseif(isset($data['date_type']) && $data['date_type'] == 'yesterday'){
            $data['beginDate'] = date('Y-m-d',(time()-60*60*24));
            $data['endDate'] = $data['beginDate'];
        } elseif(isset($data['date_type']) && $data['date_type'] == 'tomorrow'){
            $data['beginDate'] = date('Y-m-d',(time()+60*60*24));
        }
        $result = doCurlGetRequest($data,'/query_confirm_task');
        $cur_bank = get_cur_bank();

        foreach ($result['taskInfo'] as $key => $val){
            $result['taskInfo'][$key]['taskAmountStr'] = number_format($val['taskAmount'],2,'.',',');
            $result['taskInfo'][$key]['syncAmountStr'] = number_format($val['syncAmount'],2,'.',',');
        }
        return json($result);
    }
    /**
     * 上链确权
     * @return \think\response\Json
     */
    public function update_confirm_status(){

        $data = $this->request->param();

        $result = doCurlPostRequest($data,'/update_confirm_status');
        $cur_bank = get_cur_bank();
        //print_r($result);
        return json($result);
    }
    /**
     * 条件查询数字货币结算列表
     * @return \think\response\Json
     */
    public function query_settlements(){

        $data = $this->request->param();
        if(isset($data['date_type']) && $data['date_type'] == 'today'){
            $data['beginDate'] = date('Y-m-d',time());
            $data['endDate'] = $data['beginDate'];
        }elseif(isset($data['date_type']) && $data['date_type'] == 'yesterday'){
            $data['beginDate'] = date('Y-m-d',(time()-60*60*24));
            $data['endDate'] = $data['beginDate'];
        } elseif(isset($data['date_type']) && $data['date_type'] == 'tomorrow'){
            $data['beginDate'] = date('Y-m-d',(time()+60*60*24));
        }
        $result = doCurlGetRequest($data,'/query_account_task');
        if (!isset($result['taskInfo'])) {
            return json(null);
        }
        $cur_bank = get_cur_bank();

        foreach ($result['taskInfo'] as $key => $val){
            $result['taskInfo'][$key]['taskAmountStr'] = number_format($val['taskAmount'],2,'.',',');
            $result['taskInfo'][$key]['syncAmountStr'] = number_format($val['syncAmount'],2,'.',',');
        }
        return json($result);
    }
    /**
     * 数字货币结算
     * @return \think\response\Json
     */
    public function update_settlement_status(){

        $data = $this->request->param();

        $result = doCurlPostRequest($data,'/update_account_status');
        $cur_bank = get_cur_bank();
        //print_r($result);
        return json($result);
    }
    /**
     * 清分质量
     * @return \think\response\Json
     */
    public function query_work_load(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $result = doCurlGetRequest($data);
            return json($result);
        }
    }
    /**
     * 查询数字货币流水
     * @return \think\response\Json
     */
    public function query_bank_record_detail(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_bank_record_detail');
        //print_r($result);
        if($result['err_code'] == 0  && $result['data'] != null){
            $result['data']['last_balance_str'] = number_format($result['data']['last_balance'],2,'.',',');
            foreach ($result['data']['records'] as $key => $val){
                $result['data']['records'][$key]['balance_str'] = number_format($val['balance'],2,'.',',');
                $result['data']['records'][$key]['amount_str'] = number_format($val['amount'],2,'.',',');
            }
        }
        return json($result);
    }
    /**
     * 查询库存量信息
     * @return \think\response\Json
     */
    public function query_work_store(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_work_store');
        //print_r($result);
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['work_stores'] as $key => $val){
                $result['data']['work_stores'][$key]['bank_str'] = getBankNameByCode(query_bank_key($val['bank']));
                $result['data']['work_stores'][$key]['pack_amount_str'] = number_format($val['pack_amount'],2,'.',',');
            }
        }
        return json($result);
    }
    /**
     * 查询清分概览
     * @return \think\response\Json
     */
    public function query_work_overview(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_work_overview');
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['work_loads'] as $key => $val){
                $result['data']['work_loads'][$key]['bank_str'] = query_bank_key($val['bank']);
                $result['data']['work_loads'][$key]['bundle_amount_str'] = number_format($val['bundle_amount'],2,'.',',');
            }
        }
        return json($result);
    }
    /**
     *  查询清分详情
     * @return \think\response\Json
     */
    public function query_work_load_info(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_work_load_info');
        if($result['err_code'] == 0  && $result['data'] != null){
                $result['data']['bank_code_str'] = get_bank($result['data']['bank_code']);
                $result['data']['work_load_amount_str'] = number_format($result['data']['work_load_amount'],2,'.',',');
                $result['data']['status_str'] = '正常';
            foreach ($result['data']['work_loads'] as $key => $val){
                $result['data']['amount_list'][] = $val['amount'];
                $result['data']['date_list'][] = $val['op_date'];
                $result['data']['relation_list'][] = $val['relation_amount'];
            }
        }
        return json($result);
    }
    /**
     * 预约存取款审核
     * @return \think\response\Json
     */
    public function approve_plan(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['op_name'] = $username;
            $result = doCurlPostRequest($data,'/create_task');
            $result['url'] = url('/admin/booking/'.self::$version.'index',['query_date'=>$data['task_time']]);
            return json($result);
        }
    }
    /**
     * 查询库存量信息
     * @return \think\response\Json
     */
    public function get_home_page_info(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/get_home_page_info');
        //print_r($result);
        if($result['err_code'] == 0  && $result['data'] != null){
            $result['data']['already_amt_str'] = number_format($result['data']['already_amt'],2,'.',',');
            $result['data']['on_the_way_amt_str'] = number_format($result['data']['on_the_way_amt'],2,'.',',');
            $result['data']['out_of_stock_amt_str'] = number_format($result['data']['out_of_stock_amt'],2,'.',',');
            $result['data']['total_amt_str'] = number_format($result['data']['total_amt'],2,'.',',');

            $result['data']['work_load']['bundle_amount_str'] = number_format($result['data']['work_load']['bundle_amount'],2,'.',',');
            $result['data']['work_store']['pack_amount_str'] = number_format($result['data']['work_store']['pack_amount'],2,'.',',');
            $result['data']['work_store']['pack_amount_today'] = number_format($result['data']['work_store']['pack_amount_today'],2,'.',',');

            $result['data']['save_take']['save_amt_str'] = number_format($result['data']['save_take']['save_amt'],2,'.',',');
            $result['data']['save_take']['take_amt_str'] = number_format($result['data']['save_take']['take_amt'],2,'.',',');
        }
        return json($result);
    }

    /**
     *  跨行调款按日统计-图表
     * @return \think\response\Json
     */
    public function query_adjustment_statistics(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_adjustment_statistics');
        //print_r($result);
        if($result['err_code'] == 0  && $result['data'] != null){

            foreach ($result['data']['statistics'] as $key => $val){
                $result['data']['save_list'][] = $val['save_amount'];
                $result['data']['take_list'][] = $val['take_amount'];
                $result['data']['date_list'][] = $val['op_date'];
                $result['data']['task_list'][] = $val['task_amount'];
            }

        }
        return json($result);
    }

    /**
     *  跨行调款按日统计-报表
     * @return \think\response\Json
     */
    public function query_plan_statistics(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_plan_statistics');
        //print_r($result);
        if($result['err_code'] == 0  && $result['data'] != null){

            foreach ($result['data']['statistics'] as $key => $val){
                if($result['data']['statistics'][$key]['op_date'] == 'total'){
                    $result['data']['statistics'][$key]['op_date'] = '合计';
                }
                $result['data']['statistics'][$key]['take_amount'] = number_format($val['take_amount'],2,'.',',');
                $result['data']['statistics'][$key]['save_amount'] = number_format($val['save_amount'],2,'.',',');
                $result['data']['statistics'][$key]['each_other_amount'] = number_format($val['each_other_amount'],2,'.',',');
                $result['data']['statistics'][$key]['take_center_amount'] = number_format($val['take_center_amount'],2,'.',',');
                $result['data']['statistics'][$key]['save_center_amount'] = number_format($val['save_center_amount'],2,'.',',');
                $result['data']['statistics'][$key]['total_amount'] = number_format($val['each_other_amount']+$val['take_center_amount']+$val['save_center_amount'],2,'.',',');
                $result['data']['statistics'][$key]['each_track_amount'] = number_format($val['each_track_amount'],2,'.',',');
                $result['data']['statistics'][$key]['take_track_amount'] = number_format($val['take_track_amount'],2,'.',',');
                $result['data']['statistics'][$key]['save_track_amount'] = number_format($val['save_track_amount'],2,'.',',');
                $result['data']['statistics'][$key]['track_amount'] = number_format($val['each_track_amount']+$val['take_track_amount']+$val['save_track_amount'],2,'.',',');
            }

        }
        return json($result);
    }
    /**
     *  代理统计-报表
     * @return \think\response\Json
     */
    public function query_agent_list(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/mbank-ext/planReport/report',false,config('task_interface_url'));
        return json($result);
    }
    public function query_agent_abnormal(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/mbank-ext/planReport/badReport',false,config('task_interface_url'));
        return json($result);
    }
    //盐城报表
    public function bank_transfer_report(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/bankTransfer/report');
        // echo '<pre/>';
        // var_dump($endDate);exit;
        return json($result);
    }
    /**
     * 查询数字货币信息
     * @return \think\response\Json
     */
    public function query_account_balance(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_account_balance');
        //print_r($result);
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['balance'] as $key => $val){
                $result['data']['balance'][$key]['bank_str'] = get_bank($val['bank_code']);
                $result['data']['balance'][$key]['balance_str'] = number_format($val['balance'],2,'.',',');
            }
        }
        return json($result);
    }

    /**
     * 测试接口
     * @return \think\response\Json
     */
    public function query_test(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_test');
        return json($result);
    }

    /**
     * 冠字号模糊查询
     * @return \think\response\Json
     */
    public function query_gzh(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_gzh',config('new_interface_url'));
        return json($result);
    }
    /**
     * 冠字号查询
     * @return \think\response\Json
     */
    public function query_bundle_gzh(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_gzh_bundle',config('new_interface_url'));
        if($result['err_code'] == 0 && $result['data'] != null){
            /*foreach ($result['data']['bundles'] as $key => $val){
                $result['data']['bundles'][$key]['creator_bank_str'] = get_bank($val['creator_bank']);
                $result['data']['bundles'][$key]['creator_bank_short_str'] = get_bank($val['creator_bank'],'bank_short_name');
                $result['data']['bundles'][$key]['creator_bank_subordinate_str'] = get_bank($val['creator_bank'],'bank_subordinate');
                $result['data']['bundles'][$key]['owner_bank_str'] = get_bank($val['owner_bank']);
                $result['data']['bundles'][$key]['owner_bank_short_str'] = get_bank($val['owner_bank'],'bank_short_name');
                $result['data']['bundles'][$key]['owner_bank_subordinate_str'] = get_bank($val['owner_bank'],'bank_subordinate');
            }*/
        }
        return json($result);
    }
    /**
     * 冠字号详情（根据捆查询捆的流转）
     * @return \think\response\Json
     */
    public function query_task_bundle(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_task_bundle');
        //print_r($result);
        if($result['err_code'] == 0 && $result['data'] != null){
            $result['creator_bank_str'] = get_bank($result['creator_bank']);
            $result['creator_bank_short_str'] = get_bank($result['creator_bank'],'bank_short_name');
            $result['creator_bank_subordinate_str'] = get_bank($result['creator_bank'],'bank_subordinate');
            $result['owner_bank_str'] = get_bank($result['owner_bank']);
            $result['owner_bank_short_str'] = get_bank($result['owner_bank'],'bank_short_name');
            $result['owner_bank_subordinate_str'] = get_bank($result['owner_bank'],'bank_subordinate');
            foreach ($result['data']['tasks'] as $key => $val){
                $result['data']['tasks'][$key]['in_bank_str'] = get_bank($val['in_bank']);
                $result['data']['tasks'][$key]['out_bank_str'] = get_bank($val['out_bank']);
                $result['data']['tasks'][$key]['task_status_str'] = get_task_status($val['task_status']);
            }
        }
        return json($result);
    }

    /**
     * 创建任预约接口
     * @return \think\response\Json
     */
    public function create_plan(){

        $data = $this->request->param(false);
        if (strpos($data['plan_amount'],'.')) {
            $data['plan_amount']  = strstr($data['plan_amount'],'.',true);
        }
        if (strpos($data['plan_amount'],',')) {
            $data['plan_amount'] = str_replace(',','',$data['plan_amount']);
        }
        $result = doCurlPostRequest($data,'/create_plan');
        return json($result);

    }
    /**
     * 删除预约存取款接口
     * @return \think\response\Json
     */
    public function remove_plan(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/remove_plan');
        return json($result);

    }

    /**
     * 修改任预约接口
     * @return \think\response\Json
     */
    public function modify_plan(){

        $data = $this->request->param(false);
        if (strpos($data['plan_amount'],'.')) {
            $data['plan_amount']  = strstr($data['plan_amount'],'.',true);
        }
        if (strpos($data['plan_amount'],',')) {
            $data['plan_amount'] = str_replace(',','',$data['plan_amount']);
        }
        $result = doCurlPostRequest($data,'/modify_plan');
        return json($result);

    }
    /**
     * 查询任预约接口
     * @return \think\response\Json
     */
    public function query_plan_detail(){

        $data = $this->request->param(false);
        //$data['plan_amount'] = $data['plan_amount'] * 10000;
        //$data['valuta'] = 100;
        //print_r($data);
        $result = doCurlPostRequest($data,'/query_plan_detail');
        if($result['err_code'] == 0 && $result['data'] != null){
            $result['data']['plan_info']['bank_str'] = get_bank($result['data']['plan_info']['bank_code']);
        }
        //print_r($result);
        //$result['url'] = url('/admin/booking/'.self::$version.'index');
        return json($result);

    }

    /**
     * 查询商行
     * @return \think\response\Json
     */
    public function query_bank_key(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_bank_key');
        return json($result);
    }
    /**
     * 删除商行
     * @return \think\response\Json
     */
    public function del_bank_info(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/del_bank_info');
        $result['url'] = url('/admin/bank/index');
        return json($result);
    }
    /**
     * 添加商行
     * @return \think\response\Json
     */
    public function add_bank_info(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/add_bank_info');
        $result['url'] = url('/admin/bank/index');
        return json($result);
    }
    /**
     * 修改商行
     * @return \think\response\Json
     */
    public function update_bank_info(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/update_bank_info');
        $result['url'] = url('/admin/bank/index');
        return json($result);
    }

    /**
     * 程序版本列表查询接口
     * @return \think\response\Json
     */
    public function query_update_file_list(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_update_file_list');
        if($result['err_code'] == 0 && $result['data'] != null){
            foreach ($result['data']['files'] as $key => $val){
                $result['data']['files'][$key]['program_type_str'] = get_program_type($val['program_type']);
            }
        }
        return json($result);
    }
    /**
     * 程序文件上传接口
     * @return \think\response\Json
     */
    public function upload_update_file(){
        if ($this->request->isPost()) {
            $config = [
                'size' => 200097152,
                'ext' => 'zip'
            ];

            $file = $this->request->file('files');
            $program_type = $this->request->post('program_type');
            $program_name = $this->request->post('program_name');
            $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
            $info = $file->validate($config)->move($upload_path);
            if ($info) {

                if (class_exists('\CURLFile')) {
                    $files = new \CURLFile(realpath($info->getPathname()));
                } else {
                    $files = '@' . realpath($info->getPathname());
                }
                $data = [
                    'program_type' => $program_type,
                    'program_name' => $program_name,
                    'files' => $files
                ];
                $result = doCurlPostRequest($data, '/upload_update_file');
                if($result){
                    if($result['err_code'] == 0){
                        $result['url'] = url('admin/update/index');
                    }

                }else{
                    throw exception('上传超时',500);
                }

            } else {
                $result = [
                    'err_code' => 1,
                    'err_msg'   => $file->getError()
                ];
            }
            return json($result);
        }
    }


    /**
     * 金融报表（营管部2016年3季度银行业金融机构现金库存情况统计表）
     * @return \think\response\Json
     */
    public function save_report(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/save_report');
        return json($result);
    }
    /**
     * 根据年份获取周信息
     * @return \think\response\Json
     */
    public function query_week_info(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/query_week_info');
        return json($result);
    }
	
    /**
     * 查询指定交取款任务
     * @return mixed
     */
    public function query_plan_task()
    {
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/query_plan_task');
        return json($result);
    }

    /*
     * 币值信息获取接口
     * @return \think\response\Json
    */
    public function query_valuta_info(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_valuta_info');
        $valuta_status_str = '';
        if($result['err_code'] == 0 && $result['data'] != null){
            foreach ($result['data']['valuta_info'] as $key => $val){
                if($val['valuta_status'] == 0){
                    $valuta_status_str = '正常';
                }elseif($val['valuta_status'] == -1){
                    $valuta_status_str = '已删除';
                }
                $result['data']['valuta_info'][$key]['valuta_status_str'] = $valuta_status_str;
                $result['data']['valuta_info'][$key]['valuta_flag_str'] = get_valuta_flag($val['valuta_flag']);
            }
        }
        return json($result);
    }

    /*
     * 币值信息删除接口
     * @return \think\response\Json
    */
    public function delete_valuta_info(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/delete_valuta_info');
        $result['url'] = url('/admin/valuta/index');
        return json($result);
    }

    /*
     * 币值信息添加接口
     * @return \think\response\Json
    */
    public function add_valuta_info(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/add_valuta_info');
        $result['url'] = url('/admin/valuta/index');
        return json($result);
    }
    /*
     * 币值信息添加接口
     * @return \think\response\Json
    */
    public function modify_valuta_info(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/modify_valuta_info');
        $result['url'] = url('/admin/valuta/index');
        return json($result);
    }



    /*
     * 查询商行库存信息
     * @return \think\response\Json
    */
    public function query_buss_stock(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_buss_stock');
        if($result['err_code'] == 0 && $result['data'] != null){
            foreach ($result['data']['valuta_info'] as $key => $val){
                if($val['valuta_status'] == 0){
                    $valuta_status_str = '正常';
                }elseif($val['valuta_status'] == -1){
                    $valuta_status_str = '已删除';
                }
                $result['data']['valuta_info'][$key]['valuta_status_str'] = $valuta_status_str;
                $result['data']['valuta_info'][$key]['valuta_flag_str'] = get_valuta_flag($val['valuta_flag']);
            }
        }
        return json($result);
    }
    /*
     * 查询商行库存填报记录信息
     * @return \think\response\Json
    */
    public function query_buss_stock_records(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_buss_stock_records');
        if($result['err_code'] == 0 && $result['data']['stock_records'] != null){
            foreach ($result['data']['stock_records'] as $key => $val){
                $result['data']['stock_records'][$key]['amount_str'] = number_format($val['amount'],2,'.',',');
                $result['data']['stock_records'][$key]['full_amount_str'] = number_format($val['full_amount'],2,'.',',');
                $result['data']['stock_records'][$key]['damaged_amount_str'] = number_format($val['damaged_amount'],2,'.',',');
                $result['data']['stock_records'][$key]['stock_status_str'] = get_balance_status($val['stock_status']);
            }
        }
        return json($result);
    }

    /*
     * 新增商行库存信息
     * @return \think\response\Json
    */
    public function add_buss_stock(){
        $data = $this->request->param();
        $cash = [];
        if(count($data['valuta_value']) > 0){
            foreach ($data['valuta_value'] as $key=>$val){
                if((isset($data['full_amount'][$key]) && $data['full_amount'][$key] > 0) || (isset($data['damaged_amount'][$key]) && $data['damaged_amount'][$key] > 0)){
                    $tmp = [];
                    $tmp['valuta_code'] = $key;
                    $tmp['valuta_value'] = $val;
                    if(isset($data['full_amount'][$key]) && $data['full_amount'][$key] != ''){
                        $tmp['full_amount'] = $data['full_amount'][$key];
                    }else{
                        $tmp['full_amount'] = 0;
                    }
                    if(isset($data['damaged_amount'][$key]) && $data['damaged_amount'][$key] != ''){
                        $tmp['damaged_amount'] = $data['damaged_amount'][$key];
                    }else{
                        $tmp['damaged_amount'] = 0;
                    }
                    $cash[] = $tmp;
                }
            }
        }
        $datas['stock_date'] = date('Y-m-d',time());
        $datas['op_name'] = $data['op_name'];
        $datas['bank_code'] = $data['bank_code'];
        $datas['note'] = $data['note'];
        $datas['cash'] = json_encode($cash);

        $result = doCurlPostRequest($datas,'/add_buss_stock');
        //$result['url'] = url('/admin/valuta/index');
        return json($result);
    }

    /*
     * 查询清分机设置
     * @return \think\response\Json
    */
    public function query_sorted_machine(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_sorted_machine');
        return json($result);
    }
    /*
     * 清分机设置
     * @return \think\response\Json
    */
    public function add_sorted_machine(){
        $data = $this->request->param(false);
        $result = doCurlGetRequest($data,'/add_sorted_machine');
        return json($result);
    }



    /*
         * 取消审核商行填报的残损券
         * @return \think\response\Json
        */
    public function cancel_approve_damage_stock(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/cancel_approve_damage_stock');
        $result['url'] = url('/admin/damaged/index');
        return json($result);
    }


    /*
    * 查询商行上报残损券信息
    * @return \think\response\Json
   */
    public function query_damage_stock(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_damage_stock');
        if($result['err_code'] == 0 && $result['data']['bank_code']){
            $result['data']['bank_str'] = get_bank($result['data']['bank_code']);
        }
        return json($result);
    }

    /*
             * 取消审核商行填报的库存记录
             * @return \think\response\Json
            */
    public function cancel_approve_buss_stock(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/cancel_approve_buss_stock');
        return json($result);
    }


    /*
     * 查询商行残损券填报记录信息
     * @return \think\response\Json
    */
    public function query_damage_stock_records(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/query_damage_stock_records');
        if($result['err_code'] == 0 && $result['data']['stock_records'] != null){
            foreach ($result['data']['stock_records'] as $key => $val){
                /*$result['data']['stock_records'][$key]['amount_str'] = number_format($val['amount'],2,'.',',');
                $result['data']['stock_records'][$key]['task_amount_str'] = number_format($val['task_amount'],2,'.',',');
                $result['data']['stock_records'][$key]['stock_status_str'] = get_balance_status($val['stock_status']);*/
                $result['data']['stock_records'][$key]['amount_str'] = number_format($val['amount'],2,'.',',');
                $result['data']['stock_records'][$key]['task_amount_str'] = number_format($val['task_amount'],2,'.',',');
                $result['data']['stock_records'][$key]['approve_amount_str'] = number_format($val['approve_amount'],2,'.',',');
                $result['data']['stock_records'][$key]['stock_status_str'] = get_balance_status($val['stock_status']);
                $time_arr = explode(':',$result['data']['stock_records'][$key]['stock_time']);
                $result['data']['stock_records'][$key]['hour'] = $time_arr[0];
                $result['data']['stock_records'][$key]['minute'] = $time_arr[1];
            }
        }
        return json($result);
    }

    /*
     * 新增商行残损券信息
     * @return \think\response\Json
    */
    public function add_damage_stock(){
        $data = $this->request->param();
        $cash = [];
        if(count($data['valuta_value']) > 0){
            foreach ($data['valuta_value'] as $key=>$val){
                if((isset($data['amount'][$key]) && $data['amount'][$key] > 0)){
                    $tmp = [];
                    $tmp['valuta_code'] = $key;
                    $tmp['valuta_value'] = $val;
                    if(isset($data['amount'][$key]) && $data['amount'][$key] != ''){
                        $tmp['amount'] = $data['amount'][$key];
                    }else{
                        $tmp['amount'] = 0;
                    }

                    $cash[] = $tmp;
                }
            }
        }
        $datas['stock_time'] =  $data['hour'].':'.$data['minute'];
        $datas['stock_date'] = $data['stock_date'];
        $username = session('admin_name');
        $datas['approver'] = $username;
        $datas['op_name'] = $data['op_name'];
        $datas['bank_code'] = $data['bank_code'];
        $datas['note'] = $data['note'];
        $datas['bank_flag'] = $data['bank_flag'];
        $datas['cash'] = json_encode($cash);

        $result = doCurlPostRequest($datas,'/add_damage_stock');
        //$result['url'] = url('/admin/valuta/index');
        return json($result);
    }



    /*
    * 人行创建商行残损币任务
    * @return \think\response\Json
   */
    public function damage_create_task(){
        $data = $this->request->param();
        $username = session('admin_name');
        $cash = [];
        if(count($data['valuta_value']) > 0){
            foreach ($data['valuta_value'] as $key=>$val){
                if((isset($data['amount'][$key]) && $data['amount'][$key] > 0)){
                    $tmp = [];
                    $tmp['valutaCode'] = $key;
                    $tmp['valutaValue'] = $val;
                    if(isset($data['amount'][$key]) && $data['amount'][$key] != ''){
                        $tmp['amount'] = $data['amount'][$key];
                    }else{
                        $tmp['amount'] = 0;
                    }

                    $cash[] = $tmp;
                }
            }
        }
        $datas['bankCode'] = $data['bank_code'];
        $datas['opName'] = $username;
        $datas['stockDate'] = $data['stock_date'];
        $datas['stockTime'] =  $data['hour'].':'.$data['minute'];
        $datas['note'] = $data['note'];
        $datas['damageDetails'] = $cash;
        //print_r($datas);
        $result = curl_request(json_encode($datas),'/damage_create_task',true,$this->interface_url);
        //$result = doCurlPostRequest($datas,'/damage_create_task');
        //$result['url'] = url('/admin/valuta/index');
        return json($result);
    }









    /*
     * 修改商行残损券信息
     * @return \think\response\Json
    */
    public function modify_damage_stock(){
        $data = $this->request->param();
        $cash = [];
        if(count($data['valuta_value']) > 0){
            foreach ($data['valuta_value'] as $key=>$val){
                if((isset($data['amount'][$key]) && $data['amount'][$key] > 0)){
                    $tmp = [];
                    $tmp['valuta_code'] = $key;
                    $tmp['valuta_value'] = $val;
                    if(isset($data['amount'][$key]) && $data['amount'][$key] != ''){
                        $tmp['amount'] = $data['amount'][$key];
                    }else{
                        $tmp['amount'] = 0;
                    }

                    $cash[] = $tmp;
                }
            }
        }
        $datas['stock_time'] =  $data['hour'].':'.$data['minute'];
        $datas['stock_date'] = $data['stock_date'];
        $datas['op_name'] = $data['op_name'];
        $datas['bank_code'] = $data['bank_code'];
        $datas['note'] = $data['note'];
        $datas['bank_flag'] = $data['bank_flag'];
        $datas['stock_id'] = $data['stock_id'];

        $datas['cash'] = json_encode($cash);

        $result = doCurlPostRequest($datas,'/modify_damage_stock');
        //$result['url'] = url('/admin/valuta/index');
        return json($result);
    }

    /*
     * 商行残损券出库
     * @return \think\response\Json
    */
    public function damage_out_room(){
        $data = $this->request->param();

        $cash = [];
        if(count($data['valuta_value']) > 0){
            foreach ($data['valuta_value'] as $key=>$val){
                if((isset($data['amount'][$key]) && $data['amount'][$key] > 0)){
                    $tmp = [];
                    $tmp['valuta_code'] = $key;
                    $tmp['valuta_value'] = $val;
                    if(isset($data['amount'][$key]) && $data['amount'][$key] != ''){
                        $tmp['amount'] = $data['amount'][$key];
                    }else{
                        $tmp['amount'] = 0;
                    }

                    $cash[] = $tmp;
                }
            }
        }
        $datas['op_name'] = $data['op_name'];
        $datas['bank_code'] = $data['bank_code'];
        $datas['cash'] = json_encode($cash);
        $datas['stock_id'] = $data['stock_id'];
        $result = doCurlPostRequest($datas,'/damage_out_room');
        $result['url'] = url('/admin/damaged/index');
        return json($result);
    }

    /*
     * 商行残损券入库
     * @return \think\response\Json
    */
    public function damage_in_room(){
        $data = $this->request->param(false);
        $username = session('admin_name');
        $data['op_name'] = $username;
        $data['stock_id'] = $data['inroom_stock_id'];
        $cash = [];
        if(count($data['valuta_value']) > 0){
            foreach ($data['valuta_value'] as $key=>$val){
                if((isset($data['amount'][$key]) && $data['amount'][$key] > 0)){
                    $tmp = [];
                    $tmp['valuta_code'] = $key;
                    $tmp['valuta_value'] = $val;
                    if(isset($data['amount'][$key]) && $data['amount'][$key] != ''){
                        $tmp['amount'] = $data['amount'][$key];
                    }else{
                        $tmp['amount'] = 0;
                    }

                    $cash[] = $tmp;
                }
            }
        }
        $new_data['op_name'] = $username;
        $new_data['stock_id'] = $data['inroom_stock_id'];
        $new_data['cash'] = json_encode($cash);
        $result = doCurlPostRequest($new_data,'/damage_in_room');
        $result['url'] = url('/admin/damaged/index');
        return json($result);
    }

    /*
     * 人行审核残损券
     * @return \think\response\Json
    */
    public function approve_damage_stock(){
        $data = $this->request->param();
        $cash = [];
        if(count($data['valuta_value']) > 0){
            foreach ($data['valuta_value'] as $key=>$val){
                if((isset($data['amount'][$key]) && $data['amount'][$key] > 0)){
                    $tmp = [];
                    $tmp['valuta_code'] = $key;
                    $tmp['valuta_value'] = $val;
                    if(isset($data['amount'][$key]) && $data['amount'][$key] != ''){
                        $tmp['amount'] = $data['amount'][$key];
                    }else{
                        $tmp['amount'] = 0;
                    }

                    $cash[] = $tmp;
                }
            }
        }
        $datas['stock_id'] = $data['stock_id'];
        $datas['stock_date'] = $data['stock_date'];
        $datas['stock_time'] =  $data['hour'].':'.$data['minute'];
        $datas['op_name'] = $data['op_name'];
        $datas['bank_code'] = $data['bank_code'];
        $datas['note'] = $data['note'];
        $datas['cash'] = json_encode($cash);
        $result = doCurlPostRequest($datas,'/approve_damage_stock');
        //$result['url'] = url('/admin/damaged/index',['query_date'=>$data['query_date']]);
        //$result['url'] = url('/admin/damaged/index',['query_date'=>$data['query_date']]);
        return json($result);
    }

    /*
     * 人行查询残损券
     * @return \think\response\Json
    */
    public function query_damage_tasks(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/query_damage_tasks');
        if($result['err_code'] == 0 && $result['data']['stock_tasks'] != null){
            foreach ($result['data']['stock_tasks'] as $key => $val){
                $result['data']['stock_tasks'][$key]['bank_code_str'] = getBankNameByCode($val['bank_code']);
                $result['data']['stock_tasks'][$key]['amount_str'] = number_format($val['amount'],2,'.',',');
                $result['data']['stock_tasks'][$key]['task_amount_str'] = number_format($val['task_amount'],2,'.',',');
                $result['data']['stock_tasks'][$key]['stock_type_str'] = get_damage_type($val['stock_type']);
                $result['data']['stock_tasks'][$key]['stock_status_str'] = get_damage_status($val['stock_status']);
                $result['data']['stock_tasks'][$key]['task_status_str'] = get_damage_status($val['task_status']);
            }
        }
        return json($result);
    }

    /*
    * 删除商行上报残损券信息
    * @return \think\response\Json
   */
    public function del_damage_stock(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/del_damage_stock');
        return json($result);
    }
    
    /*
     * 人行审核报库存
     * @return \think\response\Json
    */
    public function approve_buss_stock(){
        $data = $this->request->param();
        $username = session('admin_name');
        $data['op_name'] = $username;
        $result = doCurlPostRequest($data,'/approve_buss_stock');
        return json($result);
    }

    /*
     * 人行批量审核报库存
     * @return \think\response\Json
    */
    public function batch_approve_buss_stock(){
        $data = $this->request->param(false);
        $username = session('admin_name');
        $data['op_name'] = $username;
        $data['stocks'] = json_decode($data['stocks']);
        //print_r(json_encode($data));
        //$result = doCurlPostRequest($data,'/batch_approve_buss_stock');
        $result = curl_request(json_encode($data),'/batch_approve_buss_stock',true,$this->interface_url);
        return json($result);
    }

    /**
     * 添加网点
     * @return \think\response\Json
     */
    public function add_bank_branch(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/add_bank_branch');
        return json($result);
    }

    /**
     * 查询商行网点信息
     * @return \think\response\Json
     */
    public function query_bank_branch(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/query_bank_branch');

        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data']['branchs'] as $key => $val){
                $result['data']['branchs'][$key]['branch_type_str'] = get_branch_type($val['branch_type']);
                $result['data']['branchs'][$key]['agent_type_str'] = get_agent_type($val['agent_type'],true);
                $result['data']['branchs'][$key]['agent_branch_code_str'] = get_bank_branch($val['agent_branch_code']);
            }
        }
        $result['show'] = $this->request->param('a','');
        return json($result);
    }

    /**
     * 修改网点
     * @return \think\response\Json
     */
    public function update_bank_branch(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/update_bank_branch');
        return json($result);
    }


    /**
     * 删除网点
     * @return \think\response\Json
     */
    public function del_bank_branch(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/del_bank_branch');
        return json($result);
    }

    /* 参数设置 */
    /**
     * 查询设置信息列表
     * @return \think\response\Json
     */
    public function dictionarys(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/dictionarys');
        return json($result);
    }
    /**
     * 修改参数设置
     * @return \think\response\Json
     */
    public function update_dictionary(){
        $data = $this->request->param();
        $result = doCurlPostRequest($data,'/update_dictionary');
        return json($result);
    }


    /**
     * 代理任务列表
     * @return \think\response\Json
     */
    public function tasks_list(){
        $data = $this->request->param(false);
        $result = doCurlGetRequest($data,'/ext_tasks');
        if($result['err_code'] == 0  && $result['data'] != null){

            foreach ($result['data']['tasks'] as $key => $val){
                $result['data']['tasks'][$key]['in_bank_str'] = get_bank_branch($val['inBankOrg']);
                $result['data']['tasks'][$key]['out_bank_str'] = get_bank_branch($val['outBankOrg']);
                if($val['taskType'] == 1){
                    $result['data']['tasks'][$key]['task_type_str'] = '残损券';
                }else{
                    $result['data']['tasks'][$key]['task_type_str'] = '跨行调款';
                }
                $result['data']['tasks'][$key]['task_status_str'] = get_agent_task_status($val['taskStatus']);
                $result['data']['tasks'][$key]['track_amount_str'] = number_format($val['taskDoubleAmount'],2,'.',',');
                // $result['data']['tasks'][$key]['untrack_amount_str'] = $val['taskAmount']-$val['trackAmount'] > 0?number_format($val['taskAmount']-$val['trackAmount'],2,'.',','):0;
                $result['data']['tasks'][$key]['task_amount_str'] = number_format($val['taskAmount'],2,'.',',');
            }
        }
        return json($result);
    }
    /**
     * 代理预约列表
     * @return \think\response\Json
     */
    public function tasks_appoint(){
        $data = $this->request->param(false);

        if ($data['planStatusList'] === '') {
            $data['planStatusList'] = [];
        }else{
            $data['planStatusList'] = [$data['planStatusList']];
        }
        
        $result = doCurlPostRequest(json_encode($data),'/mbank-ext/plan/pageList',config('task_interface_url'),10,true);
        if($result['success']  && $result['data'] != null){
            foreach ($result['data']['pageInfo']['content'] as $key => $val){
                if($val['planType'] == 0){
                    $result['data']['pageInfo']['content'][$key]['plan_type_str'] = '被代理行交款';
                }else if($val['planType'] == 1){
                    $result['data']['pageInfo']['content'][$key]['plan_type_str'] = '被代理行取款';
                }
                $result['data']['pageInfo']['content'][$key]['plan_status_str'] = get_agent_appoint_status($val['planStatus']);
            }
        }
        return json($result);
    }

    /**
     * 代理任务统计-图表格式
     * @return \think\response\Json
     */
    public function ext_task_chart(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/ext_task_chart');
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data'] as $key => $val){
                $result['agentBankOrgName'][] = $val['agentBankOrgName'];
                $result['byAgentBankOrgName'][] = $val['byAgentBankOrgName'];
                $result['paymentAmount'][] = $val['paymentAmount'];
                $result['takeAmount'][] = $val['takeAmount'];
            }
        }
        return json($result);
    }

    /**
     * 代理任务统计-报表格式
     * @return \think\response\Json
     */
    public function ext_task_report(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/ext_task_report');
        if($result['err_code'] == 0  && $result['data'] != null){

            foreach ($result['data'] as &$val){
                if($val['reports']){
                    foreach ($val['reports'] as &$v){
                        $v['agentBankCode_str'] = get_bank_branch($v['agentBankCode']);
                        $v['byAgentBankCode_str'] = get_bank_branch($v['byAgentBankCode']);
                        $v['takeTaskAmount_str'] = number_format($v['takeTaskAmount'],2,'.',',');
                        $v['takeFlowAmount_str'] = number_format($v['takeFlowAmount'],2,'.',',');
                        $v['paymentTaskAmount_str'] = number_format($v['paymentTaskAmount'],2,'.',',');
                        $v['paymentFlowAmount_str'] = number_format($v['paymentFlowAmount'],2,'.',',');
                        $v['taskAmount_str'] = number_format($v['taskAmount'],2,'.',',');
                        $v['flowAmount_str'] = number_format($v['flowAmount'],2,'.',',');
                    }
                }

            }
        }
//        echo '<pre/>';
//        var_dump($result);exit;
        return json($result);
    }

    /**
     * 代理终止
     * @return \think\response\Json
     */
    public function dstop(){
        if ($this->request->isPost()) {
            $data = $this->request->param(false);
            if ($data['planCodeList'] === '') {
                $data['planCodeList'] = [];
            }else{
                $data['planCodeList'] = [$data['planCodeList']];
            }
            $result = doCurlPostRequest(json_encode($data),'/mbank-ext/plan/audit',config('task_interface_url'),10,true);
            return json($result);
        }
    }


    /**
     * 调款统计-报表
     * @return \think\response\Json
     */
    public function query_transfer(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/task_statistics');
        if($result['err_code'] == 0  && $result['data'] != null){
            foreach ($result['data'] as $key => $val){
                if($result['data'][$key]['date'] == 'total'){
                    $result['data'][$key]['date'] = '合计';
                }
                $result['data'][$key]['eachOtherAmount'] = number_format($val['eachOtherAmount'],2,'.',',');
                $result['data'][$key]['eachTrackAmount'] = number_format($val['eachTrackAmount'],2,'.',',');
                $result['data'][$key]['saveCenterAmount'] = number_format($val['saveCenterAmount'],2,'.',',');
                $result['data'][$key]['saveTrackAmount'] = number_format($val['saveTrackAmount'],2,'.',',');
                $result['data'][$key]['takeCenterAmount'] = number_format($val['takeCenterAmount'],2,'.',',');
                $result['data'][$key]['takeTrackAmount'] = number_format($val['takeTrackAmount'],2,'.',',');
                $result['data'][$key]['agentAmount'] = number_format($val['agentAmount'],2,'.',',');
                $result['data'][$key]['agentTrackAmount'] = number_format($val['agentTrackAmount'],2,'.',',');
                $result['data'][$key]['sumAmount'] = number_format($val['sumAmount'],2,'.',',');
                $result['data'][$key]['sumTrackAmount'] = number_format($val['sumTrackAmount'],2,'.',',');
            }
        }
        return json($result);
    }

    /**
     * 公告列表查询接口
     * @return \think\response\Json
     */
    public function query_notice_file_list(){
        $data = $this->request->param();
        $result = doCurlGetRequest($data,'/announcement_list');
        if($result['err_code'] == 0 && $result['data'] != null){
            foreach ($result['data']['records'] as $key => $val){
                $result['data']['records'][$key]['status_str'] = get_notice_type($val['status']);
            }
        }
        return json($result);
    }
    /**
     * 公告上传接口
     * @return \think\response\Json
     */
    public function upload_notice_file(){
        if ($this->request->isPost()) {
            $config = [
                'size' => 200097152
            ];
            $file = $this->request->file('files');
            $files = '';
            $title = $this->request->post('title');
            $content = $this->request->post('content');
            $opName = session('admin_name');
            if($file){
                $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
                $info = $file->validate($config)->move($upload_path);
                if ($info) {
                    if (class_exists('\CURLFile')) {
                        $files = new \CURLFile(realpath($info->getPathname()));
                    } else {
                        $files = '@' . realpath($info->getPathname());
                    }
                } else {
                    $result = [
                        'err_code' => 1,
                        'err_msg'   => $file->getError()
                    ];
                }
            }
            $data = [
                'title' => $title,
                'content' => $content,
                'files' => $files,
                'opName' => $opName
            ];
            $result = doCurlPostRequest($data, '/create_announcement');
            if($result && $result['err_code'] == 0){
                $result['url'] = url('admin/notice/index');
            }else{
                throw exception('上传超时',500);
            }
            return json($result);
        }
    }

    /**
     * 公告修改接口
     * @return \think\response\Json
     */
    public function eidt_notice_file(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $username = session('admin_name');
            $data['opName'] = $username;
            $result = curl_request(json_encode($data), '/modify_announcement', true, $this->interface_url);
            if($result){
                if($result['err_code'] == 0){
                    $result['url'] = url('admin/notice/index');
                }
            }else{
                throw exception('上传超时',500);
            }
            return json($result);
        }
    }

     /**
     * 增加公告附件接口
     * @return \think\response\Json
     */
        public function add_notice_file(){
            if ($this->request->isPost()) {
                $config = [
                    'size' => 200097152
                ];
                $file = $this->request->file('files');
                $files = '';
                $announcementId = $this->request->post('announcementId');
                $opName = session('admin_name');
                if($file){
                    $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
                    $info = $file->validate($config)->move($upload_path);
                    if ($info) {
                        if (class_exists('\CURLFile')) {
                            $files = new \CURLFile(realpath($info->getPathname()));
                        } else {
                            $files = '@' . realpath($info->getPathname());
                        }
                    } else {
                        $result = [
                            'err_code' => 1,
                            'err_msg'   => $file->getError()
                        ];
                    }
                }
                $data = [
                    'announcementId' => $announcementId,
                    'files' => $files,
                    'opName' => $opName
                ];
                $result = doCurlPostRequest($data, '/add_enclosure');
                if($result){
                    if($result['err_code'] == 0){
                        $result['url'] = url('admin/notice/index');
                    }
                     else{
                        $result['url'] = url('admin/notice/index');
                    } 
                }else{
                    throw exception('上传超时',500);
                }
                return json($result);
            }
        }

    /**
     * 删除公告附件接口
     * @return \think\response\Json
     */
    public function remove_notice_file(){
        $data = $this->request->param();
        $username = session('admin_name');
        $data['opName'] = $username;
        $result = curl_request(json_encode($data), '/del_enclosure', true, $this->interface_url);
        $result['url'] = url('admin/notice/index');
        return json($result);
    }

    /**
     * 下载公告附件接口
     * @return \think\response\Json
     */
    public function down_notice_file(){
        $data = $this->request->param();
        $username = session('admin_name');
        $data['opName'] = $username;
        $result = curl_request(json_encode($data), '/download_enclosure', true, $this->interface_url);
        $result['url'] = url('admin/notice/index');
        return json($result);
    }
}