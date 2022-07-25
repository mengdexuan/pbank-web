<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;

/**
 * 查询
 * Class Index
 * @package app\admin\controller
 */
class Bundle extends IframeBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 捆查询
     * @return mixed
     */
    public function index()
    {
        $bundle_code = $this->request->param('bundle_code');
        $data = $gzhs = [];
        $is_search = '';
        if($bundle_code != ''){
            $result = doCurlGetRequest(['bundle_code'=>$bundle_code],'/query_bundle_detail');
            if($result['err_code'] == 0){
                $data = $result['data'];
            }else{

            }
            $gzh_result = doCurlGetRequest(['bundle_code'=>$bundle_code],'/query_gzh_in_bundle');
            if($gzh_result['err_code'] == 0){
                //print_r($gzh_result);
                $gzhs = $gzh_result['data'];
                $gzhs['count'] = count($gzhs['gzh_codes']);
            }else{

            }
        }
        if(isset($result)){
            $is_search = 1;
        }
        $this->assign('is_search',$is_search);
        $this->assign('data',$data);
        $this->assign('gzhs',$gzhs);
        $this->assign('bundle_code',$bundle_code);
        return $this->fetch();
    }
    /**
     * 新版捆查询
     * @return mixed
     */
    public function search(){
        $code = $this->request->param('code');
        $bank_code = $this->request->param('bank_code');
        $this->assign('code',$code);
        $this->assign('bank_code',$bank_code);
        return $this->fetch();
    }
    /**
     * 新版捆查询轨迹
     * @return mixed
     */
    public function record()
    {
        $code = $this->request->param('code');
        $bank_code = $this->request->param('bank_code');
        $this->assign('code',$code);
        $this->assign('bank_code',$bank_code);
        return $this->fetch();
    }
}
