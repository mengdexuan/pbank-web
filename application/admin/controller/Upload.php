<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 上传文件
 * Class Index
 * @package app\admin\controller
 */
class Upload extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 上传页
     * @return mixed
     */
    public function index()
    {

        return $this->fetch();

    }

}