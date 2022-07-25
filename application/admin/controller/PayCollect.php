<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 查坐支
 * Class Index
 * @package app\admin\controller
 */
class PayCollect extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 商行坐支情况统计
     * @return mixed
     */
    public function index()
    {
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $banks = get_banks();
        /*print_r($banks);*/
        $this->assign('banks',$banks);
        $this->assign('query_date',$query_date);
        return $this->fetch('index');
    }
}