<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 调款统计
 * Class Index
 * @package app\admin\controller
 */
class Transfer extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 调款统计首页
     * @return mixed
     */
    public function index()
    {
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $this->assign('query_date',$query_date);
        return $this->fetch('index');
    }
    public function export_transfer(){
        $data = $this->request->param();
        $type = $data['type'];
        $start_date = $data['beginTime'];
        $end_date = $data['endTime'];
        if($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d',time());
            $end_date = date('Y-m-d',time());
        }
        $url = config('interface_url').'/download_task_statistics?start_date='.$start_date.'&end_date='.$end_date.'&type='.$type;
        if( $start_date == $end_date ){
            $filename = '本地区金融机构调款统计表-'.$start_date.'.xlsx';
        }else{
            $filename = '本地区金融机构调款统计表-'.$start_date.'至'.$end_date.'.xlsx';
        }
        httpcopy($url,$filename);
    }
}
