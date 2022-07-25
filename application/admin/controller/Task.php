<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Task extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        $query_date = $this->request->param('query_date');
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        $bankName = $this->request->param('bankName');
        $stock_info = [];
        $bank_list = [];
        $data_info =  [];
        $total = 0;
        $op_name = $this->request->param('op_name','管理员');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $yesterday = date('Y-m-d',time()-3600*24);
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $valuta_info = get_valuta_info(2);
        $tatistics = doCurlGetRequest(['start_date'=>$start_date,'end_date'=>$end_date,'bankName'=>$bankName],'/appointment_report');
        $report = '';
        $saveTaskTotal = 0;
        $takeTaskTotal = 0;
        $reporttotal['valutaName']='合计';
        $reporttotal['saveAppointmentAmt']=0;
        $reporttotal['mutualAmount']=0;
        $reporttotal['saveAmount']=0;
        $reporttotal['saveTaskAmount']=0;
        $reporttotal['saveBox']=0;
        $reporttotal['takeAppointmentAmt']=0;
        $reporttotal['takeAmount']=0;
        $reporttotal['takeTaskAmount']=0;
        $reporttotal['takeBox']=0;
        $reporttotal['damageAppointmentAmt']=0;
        $reporttotal['damageAmount']=0;
        $reporttotal['damageTaskAmount']=0;
        $reporttotal['damageBag']=0;
        $reporttotal['saveBundle']=0;
        $reporttotal['saveHandle']=0;
        $reporttotal['takeBundle']=0;
        $reporttotal['takeHandle']=0;
        if($tatistics['err_code'] == 0 && $tatistics['data']['report']){
            $report = $tatistics['data']['report'];
            $reporttotal = $tatistics['data']['total'];
            $saveTaskTotal = $tatistics['data']['saveTaskTotal'];
            $takeTaskTotal = $tatistics['data']['takeTaskTotal'];
        }
        //18-11-27新需求，可更改双流和非双流金额开始
        $valuta_info = get_valuta_info(0,0);
        $banks = get_bank('all');
        $this->assign('banks',$banks);
        $this->assign('valuta_info',$valuta_info);
        //结束
        $this->assign('report',$report);
        $this->assign('reporttotal',$reporttotal);
        $this->assign('saveTaskTotal',$saveTaskTotal);
        $this->assign('takeTaskTotal',$takeTaskTotal);
        $this->assign('query_date',$query_date);

        $query_date = $this->request->param('query_date');
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        $status = $this->request->param('status','');
        if(!in_array($status,[1,2,3])){
            $status = '';
        }
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $curr_day = strtotime($query_date);
        $prev = date('Y-m-d',$curr_day-3600*24);
        $next = date('Y-m-d',$curr_day+3600*24);
        $this->assign('app_stage',config('app_stage'));
        $this->assign('status',$status);
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('bankName',$bankName);
        $this->assign('end_date',$end_date);
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        return $this->fetch(self::$version.self::$actionVersion);
    }
    /**
     * 创建任务
     * @return mixed
     */
    public function create_task()
    {
        $banks = get_bank('all');
        $this->assign('banks',$banks);
        //$this->assign('cur_bank',get_cur_bank());
        return $this->fetch();
    }
    /**
     * 修改任务
     * @return mixed
     */
    public function edit_task($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/plan/list_plan');
        }
        $result = doCurlGetRequest(['task_code'=>$task_code],'/query_task_detail');
        if($result['err_code'] == 0){
            $result['data']['amount_wan'] = $result['data']['task_amount']/10000;
            $this->assign('data',$result['data']);
        }
        $banks = get_bank('all');
        $this->assign('banks',$banks);
        $this->assign('cur_bank',get_cur_bank());
        return $this->fetch();
    }
    /**
     * 任务调拨
     * @return mixed
     */
    public function list_task()
    {
        return $this->fetch('index');
    }
    /**
     * 任务详情
     * @return mixed
     */
    public function view_task($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/query/index');
        }
        $result = doCurlGetRequest(['task_code'=>$task_code],'/query_task_detail');
        if (empty($result['data'])) {
           $this->redirect('/admin/task/'.self::$version.'index?no=no');
        }
        if($result['err_code'] == 0){
            $this->assign('data',$result['data']);
        }else{
            $this->redirect('/admin/query/index');
        }

        return $this->fetch('query/'.self::$version.self::$actionVersion);
    }
    /**
     * 任务中的包查询
     * @return mixed
     */
    public function pack()
    {
        $pack_code = $this->request->param('pack_code');
        $task_code = $this->request->param('task_code');
        $data = [];
        $bundles = 0;
        $columns = 4;
        if($pack_code != ''){
            $result = doCurlGetRequest(['pack_code'=>$pack_code,'task_code'=>$task_code,'cur_num'=>1000],'/query_pack_task');
            //print_r($result);
            if($result['err_code'] == 0){
                $data = $result['data'];
                $bundles = count($data['bundles']);

            }else{

            }

        }
        //print_r($status_str);
        if($bundles == 25){
            $columns = 5;
        }
        $box = 0;
        if ($columns != 0){
            $box = ceil($bundles/$columns);
        }
        $box_width = $columns*185+$columns*5;
        $this->assign('box',$box);
        $this->assign('box_width',$box_width);
        $this->assign('data',$data);
        $this->assign('task_code',$task_code);
        $this->assign('pack_code',$pack_code);
        return $this->fetch('query/pack');
    }
    /**
     * 任务中的捆查询
     * @return mixed
     */
    public function bundle()
    {
        $task_code = $this->request->param('task_code');
        $pack_code = $this->request->param('pack_code');
        $bundle_code = $this->request->param('bundle_code');
        $data = $gzhs = [];
        if($bundle_code != ''){
            $result = doCurlGetRequest(['bundle_code'=>$bundle_code,'task_code'=>$task_code,'pack_code'=>$pack_code],'/query_bundle_pack_task');
            if($result['err_code'] == 0){
                $data = $result['data'];
            }else{

            }
            $gzh_result = doCurlGetRequest(['bundle_code'=>$bundle_code],'/query_gzh_in_bundle');
            if($gzh_result['err_code'] == 0){
                //print_r($gzh_result);
                $gzhs = $gzh_result['data'];
                $gzhs['count'] = count($gzhs['gzh_codes']);
            }else{

            }

            $status = doCurlGetRequest(['bundle_code'=>$bundle_code],'/fsn_file_status');
            //print_r($status);
            $status_str = set_status_name($status['data']['file_status']);
        }
        $this->assign('data',$data);
        $this->assign('gzhs',$gzhs);
        $this->assign('bundle_code',$bundle_code);
        $this->assign('task_code',$task_code);
        $this->assign('pack_code',$pack_code);
        $this->assign('status_str',$status_str);
        return $this->fetch('query/bundle');
    }
    /**
     * 出库
     * @return mixed
     */
    public function out_room($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/task/list_task');
        }
        $result = doCurlGetRequest(['task_code'=>$task_code],'/query_task_detail');
        if($result['err_code'] == 0){
            $this->assign('data',$result['data']);
        }
        return $this->fetch(self::$version.self::$actionVersion);
    }
    /**
     * 入库
     * @return mixed
     */
    public function in_room($task_code)
    {
        if(trim($task_code) == ''){
            $this->redirect('/admin/task/list_task');
        }
        $result = doCurlGetRequest(['task_code'=>$task_code,'cur_num'=>500],'/query_task_detail');
        if($result['err_code'] == 0){
            if (file_exists("newfile.txt")) {
                $unDoubleCurrent = file_get_contents("newfile.txt");
            }else{
                $unDoubleCurrent = '0';
            }
            $this->assign('unDoubleCurrent',$unDoubleCurrent);
            $this->assign('data',$result['data']);
        }
        //print_r($result);
        return $this->fetch(self::$version.self::$actionVersion);
    }
    //下载任务gzh
    public function download_task_file(){
        $task_code = $this->request->param('task_code');
        $url = config('interface_url').'/download_task_file/'.$task_code;
        $filename = $task_code.'.zip';
        httpcopy($url,$filename);
    }
    //下载包gzh
    public function download_pack_file(){
        $pack_code = $this->request->param('pack_code');
        $url = config('interface_url').'/download_pack_file/'.$pack_code;
        $filename = $pack_code.'.zip';
        httpcopy($url,$filename);
    }
    //下载fsn
    public function download_bundle_file(){
        $bundle_code = $this->request->param('bundle_code');
        $url = config('interface_url').'/download_bundle_file/'.$bundle_code;
        $filename = $bundle_code.'.fsn';
        httpcopy($url,$filename);
    }

    public function export_save_take_report(){
        $data = $this->request->param();
        $start_date = $data['beginTime'];
        $end_date = $data['endTime'];
        /*$start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');*/
        print_r($start_date);
        print_r($end_date);
        $query_date = date('Y-m-d',time());
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $url = config('interface_url').'/export_save_take_report?start_date='.$start_date.'&end_date='.$end_date;
        if( $start_date == $end_date ){
            $filename = '全市银行机构预约存取款汇总表-'.$start_date.'.xlsx';
        }else if( $start_date!='' && $end_date =='' ){
            $filename = '全市银行机构预约存取款汇总表-自'.$start_date.'至'.$query_date.'.xlsx';
        }else if( $start_date=='' && $end_date !='' ){
            $filename = '全市银行机构预约存取款汇总表-截止'.$end_date.'.xlsx';
        }else{
            $filename = '全市银行机构预约存取款汇总表-'.$start_date.'至'.$end_date.'.xlsx';
        }
        httpcopy($url,$filename);
    }
    //任务回退
    public function rollback(){
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $result = doCurlPostRequest(($data),'/taskChange/back');
            return json($result);
        }
    }

    //任务变更详情
    public function getChangeInfo(){
        $data = $this->request->param();
        $result = doCurlGetRequest(($data),'/query_task_detail',config('task_interface_url'));
        return json($result);
    }
    //查询捆包冠字号
    public function getInfoByCondition(){
        $data = $this->request->param();
        $result = doCurlGetRequest(($data),'/mbank-ext/query/code',false,config('task_interface_url'));
        return json($result);
    }
    //19-04-08区块链：只在江苏版本
    public function blockChain(){
        $data = $this->request->param();
        $task_status = get_task_status($data['type']);
        $result = doCurlPostRequest($data,'/block_chain/confirm',config('interface_url'));
        $result['message'] = $task_status.'失败';
        if (isset($result['msg'])) {
           $result['message'] = $result['msg'];
        }
        if (isset($result['success']) && $result['success']) {
            $result['message'] = $task_status.'成功';
        }
        return json($result);
    }
    //2020-03-08完全非双流入库
    public function unDoubleCurrent(){
        $unDoubleCurrent = $this->request->param('unDoubleCurrent');
        $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
        if ($unDoubleCurrent) {
            $txt = "1";
        }else{
            $txt = "0";
        }
        if (fwrite($myfile, $txt)) {
            $result['err_code'] = 999;
            $result['message'] = '成功';
        }else{
            $result['err_code'] = 998;
            $result['message'] = '失败,请检查文件权限';
        }
        fclose($myfile);
        $value = file_get_contents("newfile.txt");
        $result['unDoubleCurrent'] = $value;
        return json($result);
    }
}
