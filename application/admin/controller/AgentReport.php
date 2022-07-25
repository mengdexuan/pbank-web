<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 代理业务
 * Class Index
 * @package app\admin\controller
 */
class AgentReport extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 代理任务图标报表
     * @return mixed
     */
    public function index()
    {
        $query_flag = $this->request->param('query_flag','2');
        $start = config('startYear');
        $cha = date('Y',time()) - $start;
        $years = [$start];
        if($cha != 0){
            for($i=0;$i<$cha;$i++){
                $years[] = end($years)+1;
            }
        }
        $month_weeks = [];
        $cur_month_week = '01';
        if($query_flag == 1){
            $result = doCurlGetRequest(['query_year'=>date('Y',time())],'/query_week_info');
            if($result['err_code'] == 0 && $result['data'] != null){
                $data = $result['data'];
                $num = count($data['weeks']);
                for($i=0;$i<$num;$i++){
                    // $month_weeks[$data['weeks'][$i]['week_index']] = '第'.$data['weeks'][$i]['week_index'].'周'.' ('.$data['weeks'][$i]['start_date'].' ~ '.$data['weeks'][$i]['end_date'].')';
                    $month_weeks[$data['weeks'][$i]['week_index']] = $data['weeks'][$i]['start_date'].','.$data['weeks'][$i]['end_date'];
                }
                $cur_month_week = $data['cur_week'];
            }
        }elseif($query_flag == 0){
            $month_weeks = [
              '01'=>'1月',
              '02'=>'2月',
              '03'=>'3月',
              '04'=>'4月',
              '05'=>'5月',
              '06'=>'6月',
              '07'=>'7月',
              '08'=>'8月',
              '09'=>'9月',
              '10'=>'10月',
              '11'=>'11月',
              '12'=>'12月'
            ];
            $cur_month_week = date('m',time());
        }
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('type','appoint');
        $this->assign('cur_month_week',$cur_month_week);
        $this->assign('years',$years);
        $this->assign('month_weeks',$month_weeks);
        $this->assign('query_flag',$query_flag);
        return $this->fetch(self::$version.self::$actionVersion);
    }

    public function download_agent_report(){
        $data = $this->request->param();

        $type = $data['query_flag'];
       $downloadType = $data['downloadType'];
//        switch ($downloadType) {
//            case 'index':
//                $baseUrl = config('interface_url').'/download_ext_report?index=index';
//                break;
//            case 'abnormal':
//                $baseUrl = config('interface_url').'/download_ext_report?index=abnormal';
//                break;
//            case 'taskpoin':
//                $baseUrl = config('interface_url').'/download_ext_report?index=taskpoin';
//                break;
//            case 'task':
//                $baseUrl = config('interface_url').'/download_ext_report?index=task';
//                break;
//            case 'appoint':
//                $baseUrl = config('interface_url').'/download_ext_report?index=appoint';
//                break;
//        }
        $baseUrl = config('task_interface_url') . '/mbank-ext/planReport/report/download?';
        if ($downloadType == 'abnormal') {
            $baseUrl = config('task_interface_url') . '/mbank-ext/planReport/badReport/download?';
        }
        if ($type == 2){
            $beginTime = $data['beginTime'];
            $endTime = $data['endTime'];
            $url = $baseUrl . 'beginTime='.$beginTime.'&endTime='.$endTime.'&queryType=1'.'&type='.$data['type'].'&inOutType='.$data['inOutType'];
        }elseif ($type == 1) {
            $tempArr = explode(',',$data['date']);
            $url = $baseUrl . 'beginTime='.$tempArr[0].'&endTime='.$tempArr[1].'&queryType=2'.'&type='.$data['type'].'&inOutType='.$data['inOutType'];
        }elseif ($type === '0') {
            $beginTime = $data['year'] . '-' .$data['month'];
            if ($data['month'] < 10) {
                $beginTime = $data['year'] . '-0' .$data['month'];
            }
            $url = $baseUrl . 'beginTime='.$beginTime.'&end_date=&queryType=3'.'&type='.$data['type'].'&inOutType='.$data['inOutType'];
        }
        header('location:'.$url);
//        echo $url;exit;
//        httpcopy($url,'代理统计.xlsx');
    }

    /**
     *预约统计
     */
    public function appoint()
    {
        $query_flag = $this->request->param('query_flag','2');
        $start = config('startYear');
        $cha = date('Y',time()) - $start;
        $years = [$start];
        if($cha != 0){
            for($i=0;$i<$cha;$i++){
                $years[] = end($years)+1;
            }
        }
        $month_weeks = [];
        $cur_month_week = '01';
        if($query_flag == 1){
            $result = doCurlGetRequest(['query_year'=>date('Y',time())],'/query_week_info');
            if($result['err_code'] == 0 && $result['data'] != null){
                $data = $result['data'];
                $num = count($data['weeks']);
                for($i=0;$i<$num;$i++){
                    // $month_weeks[$data['weeks'][$i]['week_index']] = '第'.$data['weeks'][$i]['week_index'].'周'.' ('.$data['weeks'][$i]['start_date'].' ~ '.$data['weeks'][$i]['end_date'].')';
                    $month_weeks[$data['weeks'][$i]['week_index']] = $data['weeks'][$i]['start_date'].','.$data['weeks'][$i]['end_date'];
                }
                $cur_month_week = $data['cur_week'];
            }
        }elseif($query_flag == 0){
            $month_weeks = [
              '01'=>'1月',
              '02'=>'2月',
              '03'=>'3月',
              '04'=>'4月',
              '05'=>'5月',
              '06'=>'6月',
              '07'=>'7月',
              '08'=>'8月',
              '09'=>'9月',
              '10'=>'10月',
              '11'=>'11月',
              '12'=>'12月'
            ];
            $cur_month_week = date('m',time());
        }
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('type','appoint');
        $this->assign('cur_month_week',$cur_month_week);
        $this->assign('years',$years);
        $this->assign('month_weeks',$month_weeks);
        $this->assign('query_flag',$query_flag);
        return $this->fetch();
    }

    /**
     * 任务统计
     */
    public function task()
    {
        $query_flag = $this->request->param('query_flag','2');
        $start = config('startYear');
        $cha = date('Y',time()) - $start;
        $years = [$start];
        if($cha != 0){
            for($i=0;$i<$cha;$i++){
                $years[] = end($years)+1;
            }
        }
        $month_weeks = [];
        $cur_month_week = '01';
        if($query_flag == 1){
            $result = doCurlGetRequest(['query_year'=>date('Y',time())],'/query_week_info');
            if($result['err_code'] == 0 && $result['data'] != null){
                $data = $result['data'];
                $num = count($data['weeks']);
                for($i=0;$i<$num;$i++){
                    // $month_weeks[$data['weeks'][$i]['week_index']] = '第'.$data['weeks'][$i]['week_index'].'周'.' ('.$data['weeks'][$i]['start_date'].' ~ '.$data['weeks'][$i]['end_date'].')';
                    $month_weeks[$data['weeks'][$i]['week_index']] = $data['weeks'][$i]['start_date'].','.$data['weeks'][$i]['end_date'];
                }
                $cur_month_week = $data['cur_week'];
            }
        }elseif($query_flag == 0){
            $month_weeks = [
              '01'=>'1月',
              '02'=>'2月',
              '03'=>'3月',
              '04'=>'4月',
              '05'=>'5月',
              '06'=>'6月',
              '07'=>'7月',
              '08'=>'8月',
              '09'=>'9月',
              '10'=>'10月',
              '11'=>'11月',
              '12'=>'12月'
            ];
            $cur_month_week = date('m',time());
        }
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('type','appoint');
        $this->assign('cur_month_week',$cur_month_week);
        $this->assign('years',$years);
        $this->assign('month_weeks',$month_weeks);
        $this->assign('query_flag',$query_flag);
        return $this->fetch();
    }
    
    public function taskpoin()
    {
        $query_flag = $this->request->param('query_flag','2');
        $start = config('startYear');
        $cha = date('Y',time()) - $start;
        $years = [$start];
        if($cha != 0){
            for($i=0;$i<$cha;$i++){
                $years[] = end($years)+1;
            }
        }
        $month_weeks = [];
        $cur_month_week = '01';
        if($query_flag == 1){
            $result = doCurlGetRequest(['query_year'=>date('Y',time())],'/query_week_info');
            if($result['err_code'] == 0 && $result['data'] != null){
                $data = $result['data'];
                $num = count($data['weeks']);
                for($i=0;$i<$num;$i++){
                    // $month_weeks[$data['weeks'][$i]['week_index']] = '第'.$data['weeks'][$i]['week_index'].'周'.' ('.$data['weeks'][$i]['start_date'].' ~ '.$data['weeks'][$i]['end_date'].')';
                    $month_weeks[$data['weeks'][$i]['week_index']] = $data['weeks'][$i]['start_date'].','.$data['weeks'][$i]['end_date'];
                }
                $cur_month_week = $data['cur_week'];
            }
        }elseif($query_flag == 0){
            $month_weeks = [
              '01'=>'1月',
              '02'=>'2月',
              '03'=>'3月',
              '04'=>'4月',
              '05'=>'5月',
              '06'=>'6月',
              '07'=>'7月',
              '08'=>'8月',
              '09'=>'9月',
              '10'=>'10月',
              '11'=>'11月',
              '12'=>'12月'
            ];
            $cur_month_week = date('m',time());
        }
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('type','appoint');
        $this->assign('cur_month_week',$cur_month_week);
        $this->assign('years',$years);
        $this->assign('month_weeks',$month_weeks);
        $this->assign('query_flag',$query_flag);
        return $this->fetch();
    }
    public function abnormal()
    {
        $query_flag = $this->request->param('query_flag','2');
        $start = config('startYear');
        $cha = date('Y',time()) - $start;
        $years = [$start];
        if($cha != 0){
            for($i=0;$i<$cha;$i++){
                $years[] = end($years)+1;
            }
        }
        $month_weeks = [];
        $cur_month_week = '01';
        if($query_flag == 1){
            $result = doCurlGetRequest(['query_year'=>date('Y',time())],'/query_week_info');
            if($result['err_code'] == 0 && $result['data'] != null){
                $data = $result['data'];
                $num = count($data['weeks']);
                for($i=0;$i<$num;$i++){
                    // $month_weeks[$data['weeks'][$i]['week_index']] = '第'.$data['weeks'][$i]['week_index'].'周'.' ('.$data['weeks'][$i]['start_date'].' ~ '.$data['weeks'][$i]['end_date'].')';
                    $month_weeks[$data['weeks'][$i]['week_index']] = $data['weeks'][$i]['start_date'].','.$data['weeks'][$i]['end_date'];
                }
                $cur_month_week = $data['cur_week'];
            }
        }elseif($query_flag == 0){
            $month_weeks = [
              '01'=>'1月',
              '02'=>'2月',
              '03'=>'3月',
              '04'=>'4月',
              '05'=>'5月',
              '06'=>'6月',
              '07'=>'7月',
              '08'=>'8月',
              '09'=>'9月',
              '10'=>'10月',
              '11'=>'11月',
              '12'=>'12月'
            ];
            $cur_month_week = date('m',time());
        }
        $start_date = $this->request->param('start_date');
        $end_date = $this->request->param('end_date');
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $this->assign('query_date',$query_date);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('type','appoint');
        $this->assign('cur_month_week',$cur_month_week);
        $this->assign('years',$years);
        $this->assign('month_weeks',$month_weeks);
        $this->assign('query_flag',$query_flag);
        return $this->fetch();
    }
}