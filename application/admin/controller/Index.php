<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */
class Index extends AdminBase
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
        $overview = [];
        $result = doCurlGetRequest([],'/get_home_page_info');
        if($result['err_code'] == 0){
            $overview = $result['data'];
        }
        get_cur_bank();
        $cur_bank = session('cur_bank');
        // $resBasic = doCurlGetRequest([],'/disk/use/get',false,config('gen_star'));
        // if (isset($resBasic['success']) && $resBasic['success']) {
        //     $basicInfo = [
        //         [
        //             'name' => '存储FSN总量',
        //             'val' => $resBasic['data']['fsnCount']
        //         ],
        //         [
        //             'name' => '占用磁盘空间',
        //             'val' => $resBasic['data']['diskUsed']
        //         ],
        //         [
        //             'name' => '剩余磁盘空间',
        //             'val' => $resBasic['data']['diskAvail']
        //         ],
        //         [
        //             'name' => '生成GZH总量',
        //             'val' => $resBasic['data']['gzhCount']
        //         ]
        //     ];
        // }else{
        //        $basicInfo = [
        //         [
        //             'name' => '存储FSN总量',
        //             'val' => 0
        //         ],
        //         [
        //             'name' => '占用磁盘空间',
        //             'val' => 0
        //         ],
        //         [
        //             'name' => '剩余磁盘空间',
        //             'val' => 0
        //         ],
        //         [
        //             'name' => '生成GZH总量',
        //             'val' => 0
        //         ]
        //     ]; 
        // }
        $this->assign('cur_bank',$cur_bank);
        $this->assign('overview',$overview);
        $this->assign('basicInfo','');
        $this->assign('versionNum',config('versionNum'));

        return $this->fetch('index');
    }
    public function test()
    {
        $filePushSys = config('file_sys');
//        echo $filePushSys;exit;
        $this->assign('filePushSys',$filePushSys);
        return $this->fetch();
    }
}
