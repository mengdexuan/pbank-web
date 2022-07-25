<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 商行管理
 * Class Bank
 * @package app\admin\controller
 */
class Bank extends AdminBase
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
        return $this->fetch('index');
    }
    /**
     * 查看商行
     * @return mixed
     */
    public function show()
    {
        $bank_code = $this->request->param('bank_code');
        if(trim($bank_code) == ''){
            $this->redirect('/admin/bank/index');
        }
        $data = [];
        if($bank_code != ''){
            $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_bank_key');
            //print_r($result);
            if($result['err_code'] == 0 && $result['data']['bank_key']){
                $data = $result['data']['bank_key'][0];
            }else{

            }
        }
        //print_r($data);
        $bank = get_bank($bank_code);
        $this->assign('bank',$bank);
        $this->assign('data',$data);
        return $this->fetch('show');
    }
    /**
     * 添加商行
     * @return mixed
     */
    public function add()
    {
        return $this->fetch('add');
    }
    /**
     * 修改商行
     * @return mixed
     */
    public function edit()
    {
        $bank_code = $this->request->param('bank_code');
        if(trim($bank_code) == ''){
            $this->redirect('/admin/bank/index');
        }
        $data = [];
        if($bank_code != ''){
            $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_bank_key');
            //print_r($result);
            if($result['err_code'] == 0 && $result['data']['bank_key']){
                $data = $result['data']['bank_key'][0];
            }else{
                $this->redirect('/admin/bank/index');
            }
        }
        //print_r($data);
        $bank = get_bank($bank_code);
        $this->assign('bank',$bank);
        $this->assign('data',$data);
        return $this->fetch('edit');
    }
    /**
     * 添加网点
     * @return mixed
     */
    public function dot()
    {
        $bank_code = $this->request->param('bank_code');
        if(trim($bank_code) == ''){
            $this->redirect('/admin/bank/index');
        }
        $data = [];
        if($bank_code != ''){
            $result = doCurlGetRequest(['bank_code'=>$bank_code],'/query_bank_key');
            //print_r($result);
            if($result['err_code'] == 0 && $result['data']['bank_key']){
                $data = $result['data']['bank_key'][0];
            }else{

            }
        }
        //print_r($data);
        $bank = get_bank($bank_code);
        $this->assign('bank_code',$bank_code);
        $this->assign('bank',$bank);
        $this->assign('data',$data);
        return $this->fetch('dot');
    }

    /**
     * 查看单个网点信息
     * @return mixed
     */
    public function view()
    {
        $branch_code = $this->request->param('branch_code');
        if(trim($branch_code) == ''){
            $this->redirect('/admin/bank/index');
        }
        if($branch_code != ''){
            $result = doCurlGetRequest(['branch_code'=>$branch_code],'/get_bank_branch');
            if($result['err_code'] == 0 && $result['data']){
                $data = $result['data'];
            }else{

            }
        }

        $branch_name = get_bank($branch_code);
        $this->assign('branch_code',$branch_code);
        $this->assign('branch_name',$branch_name);
        $this->assign('data',$data);
        return $this->fetch('view');
    }

    /**
     * 修改单个网点信息
     * @return mixed
     */
    public function modify()
    {
        $branch_code = $this->request->param('branch_code');
        if(trim($branch_code) == ''){
            $this->redirect('/admin/bank/index');
        }
        if($branch_code != ''){
            $result = doCurlGetRequest(['branch_code'=>$branch_code],'/get_bank_branch');
            if($result['err_code'] == 0 && $result['data']){
                $data = $result['data'];
            }else{

            }
        }
        //print_r($data);
        $bank_name = get_bank($branch_code);
        $this->assign('branch_code',$branch_code);
        $this->assign('bank_name',$bank_name);
        $this->assign('data',$data);
        return $this->fetch('modify');
    }
}