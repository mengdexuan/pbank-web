<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Statistics extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 预约交取款统计首页
     * @return mixed
     */
    public function index()
    {
        //echo date("W",strtotime('2017-01-01'));
        $query_flag = $this->request->param('query_flag','0');
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
                    $month_weeks[$data['weeks'][$i]['week_index']] = '第'.$data['weeks'][$i]['week_index'].'周'.' ('.$data['weeks'][$i]['start_date'].' ~ '.$data['weeks'][$i]['end_date'].')';
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

        $this->assign('cur_month_week',$cur_month_week);
        $this->assign('years',$years);
        $this->assign('month_weeks',$month_weeks);
        $this->assign('query_flag',$query_flag);
        return $this->fetch('index');
    }
    public function export_report_take(){
        $year = $this->request->param('year');
        $month = $this->request->param('month/s');
        $url = config('interface_url').'/export_report_take?query_date='.$year.'-'.$month;
        $filename = '('.$year.'.'.$month.')银行业金机构相互取现统计表.xlsx';
        httpcopy($url,$filename);
    }
    public function export_report_daily(){
        $year = $this->request->param('year');
        $month = $this->request->param('month/s');
        $date = $this->request->param('date');
        $url = config('interface_url').'/export_report_daily?report_num=R0009&query_year='.$year.'&query_week='.$month;
        $filename = $year.'年'.$date.'跨行取现系统跨行取现数据周报.xlsx';
        httpcopy($url,$filename);
    }
    //年报表
    public function export_report_month()
    {
        $year = $this->request->param('year');
        $url = config('interface_url').'/export_report_daily?query_year='.$year;
        $filename = $year.'年跨行取现系统跨行取现数据周报.xlsx';
        httpcopy($url,$filename); 
    }
}