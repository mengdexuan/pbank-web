<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 概览
 * Class Overview
 * @package app\admin\controller
 */
class Overview extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }



    /**
     * 任务调拨
     * @return mixed
     */
    public function list_task()
    {
        return $this->fetch('index');
    }

}