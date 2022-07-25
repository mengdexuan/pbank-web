<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Digital extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 数字货币
     * @return mixed
     */
    public function index()
    {

        $banks = get_bank('all');
        $this->assign('banks',$banks);
        return $this->fetch();
    }
    public function digital_info()
    {
        $bank_code = $this->request->param('bank_code');
        $this->assign('bank_code',$bank_code);
        return $this->fetch();
    }

}