<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 升级
 * Class Index
 * @package app\admin\controller
 */
class Notice extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 公告列表
     * @return mixed
     */
    public function index()
    {
        return $this->fetch('index');
    }
    /**
     * 添加公告
     * @return mixed
     */
    public function add()
    {
        return $this->fetch('add');
    }
    /**
     * 公告详情
     * @return mixed
     */
    public function view()
    {
        $id = $this->request->param('id');
        $result = doCurlGetRequest(['announcementId'=>$id],'/announcement');
        if($result['err_code'] == 0){
            $this->assign('data',$result['data']);
        }else{
            $this->redirect('/admin/notice/index');
        }
        return $this->fetch();
    }
    /**
     * 公告修改
     * @return mixed
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $result = doCurlGetRequest(['announcementId'=>$id],'/announcement');
        if($result['err_code'] == 0){
            $this->assign('data',$result['data']);
        }else{
            $this->redirect('/admin/notice/index');
        }
        return $this->fetch();
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