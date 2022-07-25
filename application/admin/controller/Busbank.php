<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
　* 商行交取款统计
 * Class Busbank
 * @package app\common\controller\AdminBase
 */
class Busbank extends AdminBase
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
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $this->assign('interface_url',config('interface_url'));
        $this->assign('query_date',$query_date);
        return $this->fetch('index');
    }
  }