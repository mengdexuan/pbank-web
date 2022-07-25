<?php
namespace app\common\controller;

use think\Controller;
use think\Response;


/**
 * 后台公用基础控制器
 * Class AdminBase
 * @package app\common\controller
 */
class IframeBase extends Controller
{
    public $bank_code;
    public $bank_key;
    static protected $actionVersion = null;
    static protected $version = null;
    protected function _initialize(){
        parent::_initialize();
        $bank_key = $this->request->param('bank_code');
        if (is_null(self::$actionVersion)) {
            self::$actionVersion = $this->request->action();
        }
        if (is_null(self::$version)) {
            $unified_version = config('unified_version');
            if ($unified_version == '宁波') {
                self::$version = 'n';
            }else if ($unified_version == '江苏') {
                self::$version = 'j';
            }else{
                self::$version = 'j';
            }
        }
        // return true;
        // var_dump($bank_key);exit;
        // if(trim($bank_key) == ''){
        //     echo '银行编码错误';
        //     exit();
        // }
        $this->bank_key = $bank_key;
        $bank_key = base64_decode($bank_key);
        $is_bank = 0;
        $bank_code = '';
        $result = doCurlGetRequest([],'/query_bank_key');
        if($result['err_code'] == 0 && $result['data']!=null){
            $banks = $result['data']['bank_key'];
            if(count($banks)> 0){
                foreach ($banks as $val){
                    if ($bank_key == $val['p_key']){
                        $is_bank = 1;
                        $bank_code = $val['bank_code'];
                        break;
                    }
                }
            }
        }
        // if($is_bank == 0){
        //     echo '银行编码解密失败';
        //     exit();
        // }
        $this->bank_code = $bank_code;
        $this->assign('version',self::$version);
    }

}