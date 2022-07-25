<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Sorter extends AdminBase
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
        $banks = get_bank('all');
        $data = [];
        $result = doCurlGetRequest([],'/query_sorted_machine');
        if($result['err_code'] == 0 && $result['data']['machines'] != null){
            $data = $result['data']['machines'];
        }
        //print_r($data);
        $this->assign('banks',$banks);
        $this->assign('data',$data);
        //print_r($data);
        return $this->fetch();
    }


}
