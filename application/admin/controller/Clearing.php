<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 清分首页
 * Class Index
 * @package app\admin\controller
 */
class Clearing extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 清分
     * @return mixed
     */
    public function index()
    {
        $query_date = $this->request->param('query_date');
        if($query_date == '') {
            $query_date = date('Y-m-d',time());
        }
        $overview = [];
        $result = doCurlGetRequest([],'/get_home_page_info');
        if($result['err_code'] == 0){
            $overview = $result['data'];
        }
        $this->assign('data',$overview);
        $this->assign('query_date',$query_date);
        return $this->fetch(self::$version.self::$actionVersion);
    }
    public function clearing_info()
    {
        $bank_code = $this->request->param('bank_code');
        $this->assign('bank_code',$bank_code);
        return $this->fetch();
    }

}