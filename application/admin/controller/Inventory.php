<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 库存
 * Class Index
 * @package app\admin\controller
 */
class Inventory extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 库存
     * @return mixed
     */
    public function index()
    {

        return $this->fetch();

    }
    /**
     * 装包
     * @return mixed
     */
    public function show_pack()
    {

        return $this->fetch('show_pack');

    }

}