<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;

/**
 * 查询
 * Class Index
 * @package app\admin\controller
 */
class Pack extends IframeBase
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 新版包查询
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
     * 新版包查询轨迹
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
