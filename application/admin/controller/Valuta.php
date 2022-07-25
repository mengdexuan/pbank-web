<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;

/**
 * 后台币值管理
 * Class Valuta
 * @package app\admin\controller
 */
class Valuta extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 币值列表
     * @return mixed
     */
    public function index()
    {

        return $this->fetch('index');
    }
    /**
     * 添加币值
     * @return mixed
     */
    public function add()
    {

        return $this->fetch('add');
    }
    /**
     * 添加币值
     * @return mixed
     */
    public function edit()
    {
        $valuta_code = $this->request->param('valuta_code');
        if(trim($valuta_code) == ''){
            $this->redirect('/admin/valuta/index');
        }
        $data = [];
        if($valuta_code != ''){
            $result = doCurlGetRequest(['valuta_code'=>$valuta_code],'/get_valuta_info');

            if($result['err_code'] == 0 && $result['data']){
                $data = $result['data'];
            }else{
                $this->redirect('/admin/valuta/index');
            }
        }

        $this->assign('data',$data);

        return $this->fetch('edit');
    }
}