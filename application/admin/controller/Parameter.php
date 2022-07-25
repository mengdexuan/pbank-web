<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\Cookie;
/**
 * 参数设置
 * Class Bank
 * @package app\admin\controller
 */
class Parameter extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        $this->assign('parameDoConversionTime',Cookie::get('parameDoConversionTime') ? Cookie::get('parameDoConversionTime') : '1');
        $this->assign('parameDoConversion',Cookie::get('parameDoConversion') ? Cookie::get('parameDoConversion') : '元');
        return $this->fetch('index');
    }

    //单位设置
    public function parame()
    {
        $param = $this->request->param();
        Cookie::set('parameDoConversion',$param['type'],$param['time']*86400);
        Cookie::set('parameDoConversionTime',$param['time'],$param['time']*86400);
        $res['errMsg'] = '成功';
        return $res;
    }
}