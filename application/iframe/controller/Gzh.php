<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;


/**
 * iframe 公告
 * Class Notice
 * @package app\iframe\controller
 */
class Gzh extends IframeBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

 /**
     * 新版冠字号查询
     * @return mixed
     */
    public function search()
    {
        $code = $this->request->param('code');
        $bank_code = $this->request->param('bank_code');
        $this->assign('code',$code);
        $this->assign('bank_code',$bank_code);
        return $this->fetch(self::$version.self::$actionVersion);
    }
 /**
     * 新版冠字号查询
     * @return mixed
     */
    public function hsearch()
    {
        $code = $this->request->param('code');
        $bank_code = $this->request->param('bank_code');
        $this->assign('code',$code);
        $this->assign('bank_code',$bank_code);
        return $this->fetch();
    }
        /**
     * 新版冠字号查询轨迹
     * @return mixed
     */
    public function record()
    {
        $code = $this->request->param('code');
        $bank_code = $this->request->param('bank_code');
        $this->assign('code',$code);
        $this->assign('bank_code',$bank_code);
        return $this->fetch(self::$version.self::$actionVersion);
    }

}
