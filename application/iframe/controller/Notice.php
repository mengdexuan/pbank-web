<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;


/**
 * iframe 公告
 * Class Notice
 * @package app\iframe\controller
 */
class Notice extends IframeBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 公告列表
     * @return mixed
     */
    public function index(){
        $bank_flag = '';
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;
        $op_name = $this->request->param('op_name','default');
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
        $this->assign('op_name',$op_name);
        $this->assign('bank_flag',$bank_flag);
        return $this->fetch('index');
    }

    /**
     * 库存详情
     * @return mixed
     */
    public function view(){
        $bank_code = $this->bank_code;
        $bank_key = $this->bank_key;$id = $this->request->param('id');
        $result = doCurlGetRequest(['announcementId'=>$id],'/announcement');
        if($result['err_code'] == 0){
            $this->assign('data',$result['data']);
        }else{
            $this->redirect('/admin/notice/index');
        }
        $this->assign('bank_code',$bank_code);
        $this->assign('bank_key',$bank_key);
        return $this->fetch();
        //print_r($stock_time);
    }
    /**
     * 公告下载
     * @return mixed
     */
    public function export_notice_file(){
        $data = $this->request->param();
        $enclosureId = $data['enclosureId'];
        $enclosureName = $data['enclosureName'];
        $url = config('interface_url').'/download_enclosure?enclosureId='.$enclosureId;
        $filename = '附件-'.$enclosureName;
        httpcopy($url,$filename,md5($enclosureId));
    }
}