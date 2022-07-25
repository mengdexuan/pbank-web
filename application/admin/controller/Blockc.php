<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 *区块链
 * Class Black
 * @package app\admin\controller
 */
class Blockc extends AdminBase
{
	//首页
	public function index()
	{
	    $parseUrl = parse_url($_SERVER['HTTP_REFERER']);
	    $qkUrl = '/khdk/blockchain/main/index';
        $block_chain = 'http://' .$parseUrl['host'] .$qkUrl;
        if (isset($parseUrl['port'])){
            $block_chain = 'http://' .$parseUrl['host'] . ':' . $parseUrl['port'] .$qkUrl;
        }
        $block_chain = config('block_chain');
		$this->assign('bank_code',base64_encode(session('cur_bank')['bank_code']));
		$this->assign('type',2);
		$this->assign('version',config('manage_version'));
		$this->assign('block_chain',$block_chain);
		return $this->fetch();
	}
}