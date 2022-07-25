<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 查坐支
 * Class Index
 * @package app\admin\controller
 */
class DotPay extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 商行网点坐支统计计
     * @return mixed
     */
    public function index()
    {
        $bankCode = $this->request->param('bankCode');
        $start_time = $this->request->param('startTime');
        $end_time = $this->request->param('endTime');
        if($start_time == '') {
            $start_time = date('Y-m-d',time());
        }
        if($end_time == '') {
            $end_time = date('Y-m-d',time());
        }
        $banks = get_banks();
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('bankCode',$bankCode);
        $this->assign('banks',$banks);
        return $this->fetch('index');
    }
}