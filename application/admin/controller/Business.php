<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 业务查询
 * Class Index
 * @package app\admin\controller
 */
class Business extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 业务查询
     * @return mixed
     */
    public function index()
    {
        $start_time = date('Y-m-d',time());
        $end_time = date('Y-m-d',time());
        $banks = get_banks();
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $this->assign('banks',$banks);
        return $this->fetch('index');
    }

    /**
     * 业务查询详情
     * @return mixed
     */
    public function view()
    {
        $netBankCode = $this->request->param('netBankCode');
        $businessType = $this->request->param('businessType');
        $businessTypeStr = show_business_type($businessType);
        $bisNo = $this->request->param('bisNo');
        $bisDateTime = $this->request->param('bisDateTime');
        $this->assign('netBankCode',$netBankCode);
        $this->assign('businessType',$businessType);
        $this->assign('businessTypeStr',$businessTypeStr);
        $this->assign('bisNo',$bisNo);
        $this->assign('bisDateTime',$bisDateTime);
        return $this->fetch('view');
    }
}