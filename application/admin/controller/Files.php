<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 生成文件
 * Class Files
 * @package app\admin\controller
 */
class Files extends AdminBase
{
    /**
     * gzh文件列表
     */
    public function gzh()
    {
        $beginTime = $this->request->param('beginTime');
        $endTime = $this->request->param('endTime');
        if($beginTime == '' && $endTime == '') {
            $beginTime = date('Y-m-d',time());
            $endTime = date('Y-m-d',time());
        }
        $this->assign('beginTime',$beginTime);
        $this->assign('endTime',$endTime);
        return $this->fetch();
    }

    /**
     * 生成gzh文件
     */
    public function gedit()
    {
        $type = $this->request->param('type');
        $result = doCurlGetRequest(['type'=>$type],'/gzhmake/gzhMakeStrategy/get',false,config('gen_star'));
        if ($result['success']){
            if (is_null($result['data'])){
                $result['data']['id'] = '';
                $result['data']['gzhType'] = '';
                $result['data']['bizType'] = '';
                $result['data']['gzhCron'] = '';
                $result['data']['gzhPath'] = '';
                $result['data']['gzhCreateBeginTime'] = date('Y-m-d',time());
            }
        }
        $this->assign('result',$result);
        return $this->fetch();
    }
}