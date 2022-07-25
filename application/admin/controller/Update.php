<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 升级
 * Class Index
 * @package app\admin\controller
 */
class Update extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 升级列表
     * @return mixed
     */
    public function index()
    {
        $program_types = array(
            '1'=>'商行接口',
            '2'=>'商行php',
            '3'=>'关联盒',
        );
        $this->assign('program_types',$program_types);
        return $this->fetch('index');
    }
    /**
     * 添加版本
     * @return mixed
     */
    public function add()
    {
        $program_types = array(
            '1'=>'商行接口',
            '2'=>'商行php',
            '3'=>'关联盒',
        );
        $this->assign('program_types',$program_types);
        return $this->fetch('add');
    }
}