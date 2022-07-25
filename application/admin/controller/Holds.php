<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
/**
 *把查询
 * Class Holds
 * @package app\admin\controller
 */
class Holds extends AdminBase
{
   public function page()
    {
        //把
        $bundle_code = $this->request->param('bundle_code');
        //捆
        $bundleCode = $this->request->param('bundleCode');
        $gzhs = [];
        $is_search = '';
        //根据把查询
        if($bundle_code != ''){
            $result = doCurlGetRequest(['handleCode'=>$bundle_code],'/handle/gzhInfo');
            $handle_detail = doCurlGetRequest(['handle_code'=>$bundle_code],'/query_handle_detail');
            if($handle_detail['err_code'] == 0){
                $handle_detail = $handle_detail['data'];
            }else{
                $handle_detail = [];
            }
        }else{
            //根据捆查询
            $result = doCurlGetRequest(['bundleCode'=>$bundleCode],'/handle/handleInfo');
        }

        if (isset($result['success']) && $result['success']) {
            $gzhs['gzh_codes'] = $result['data']['gzhList'];
            $gzhs['handleCodeList'] = $result['data']['handleCodeList'];
            $gzhs['count'] = count($gzhs['gzh_codes']);
            $gzhs['bundleCode'] = $result['data']['bundleCode'];
        }


        $this->assign('gzhs',$gzhs);
        $this->assign('bundle_code',$bundle_code);
        $this->assign('bundleCode',$bundleCode);
        $this->assign('data',isset($handle_detail)?$handle_detail:'');
        return $this->fetch();        
    }

    /**
     * 新版把查询轨迹
     * @return mixed
     */
    public function search()
    {
        $code = $this->request->param('code');
        $this->assign('code',$code);
        return $this->fetch();
    }
    /**
     * 新版把查询轨迹
     * @return mixed
     */
    public function record()
    {
        $code = $this->request->param('code');
        $this->assign('code',$code);
        return $this->fetch();
    }
    //下载把号
    public function download_handle_file(){
        $handleCode = $this->request->param('handleCode');
        $url = config('interface_url').'/download_handle_zip/'.$handleCode;
        $filename = $handleCode.'.zip';
        httpcopy($url,$filename);
    }
}