<?php
namespace app\iframe\controller;

use app\common\controller\IframeBase;
use think\Db;

/**
 *黑名单
 * Class Black
 * @package app\admin\controller
 */
class Black extends IframeBase
{
	//首页
	public function index()
	{
		$endTime = $this->request->param('endTime');
		$beginTime = $this->request->param('beginTime');
		$page = $this->request->param('page','1');
		$seqNo = $this->request->param('seqNo','');
		$bankCode = $this->request->param('bankCodeSea','');
		$bank_code_param = $this->request->param('bank_code_param','');
		// $bundleCode = $this->request->param('bundleCode','');
		//开始和结束时间默认为今天
		$time = time();
		if ($beginTime === NULL || strpos($beginTime,'-') === false) {
			$beginTime = date('Y-m-d',$time);
		}
		if ($endTime === NULL || strpos($endTime,'-') === false) {
			$endTime = date('Y-m-d',$time);
		}
		$param = ['startDateTime'=>$beginTime.' 00:00:00','endDateTime'=>$endTime.' 23:59:59','page'=>$page,'seqNo'=>$seqNo,'bankCode'=>$bankCode];
		$res = doCurlGetRequest($param,'/blacklist/search',false,config('black_interface_url'));
		//类型
		$resType = doCurlGetRequest($param,'/blacklist-dictionary/type',false,config('black_interface_url'));
		//币值
		$resValuta = doCurlGetRequest($param,'/blacklist-dictionary/valuta',false,config('black_interface_url'));
		//版别
		$resVer = doCurlGetRequest($param,'/blacklist-dictionary/ver',false,config('black_interface_url'));
		// echo '<pre/>';
		// var_dump($resValuta);exit;
        // $cur_bank = session('cur_bank');
        // $this->assign('cur_bank',$cur_bank);
        $bank_code = $this->bank_code;
        if($bank_code_param) $bank_code = $bank_code_param;	
		$this->assign('bank_code',$bank_code);
		$this->assign('param',$param);
		$this->assign('beginTime',$beginTime);
		$this->assign('endTime',$endTime);
		$this->assign('resType',$resType);
		$this->assign('resValuta',$resValuta);
		$this->assign('resVer',$resVer);
		$this->assign('res',$res['data']);
		return $this->fetch();
	}

	//黑名单新增和修改 
	public function edit()
	{
		if ($this->request->isPost()) {
			$data = $this->request->param('data');
			$data = explode(',',$data);
			foreach ($data as $k => $v) {
				if ($k%2 == 0 && isset($data[$k+1])) {
					$res[$v] = $data[$k+1];
				}
			}
			$res['type'] = (int)$res['type'];
			$res['valuta'] = (int)$res['valuta'];
			$res['ver'] = (int)$res['ver'];
			// echo '<pre/>';
			// var_dump($res);exit;
			$resJava = doCurlPostRequest(json_encode($res),'/blacklist/add',config('black_interface_url'),10,true);
			return json($resJava);
		}
	}

	//监控黑名单
	public function detail()
	{

		$endTime = $this->request->param('endTime');
		$beginTime = $this->request->param('beginTime');
		$page = $this->request->param('page','1');
		$seqNo = $this->request->param('seqNo','');
		$bankCode = $this->request->param('bankCode','');
		$bundleCode = $this->request->param('bundleCode','');
		$bank_code_param = $this->request->param('bank_code_param','');
		//开始和结束时间默认为今天
		$time = time();
		if ($beginTime === NULL || strpos($beginTime,'-') === false) {
			$beginTime = date('Y-m-d',$time);
		}
		if ($endTime === NULL || strpos($endTime,'-') === false) {
			$endTime = date('Y-m-d',$time);
		}
		$param = ['startDateTime'=>$beginTime.' 00:00:00','endDateTime'=>$endTime.' 23:59:59','page'=>$page,'seqNo'=>$seqNo,'bankCode'=>$bankCode,'bundleCode'=>$bundleCode];
		$resJava = doCurlGetRequest($param,'/blacklist-monitor/search',false,config('black_interface_url'));

		// echo '<pre/>'; 
		// var_dump($resJava['data']);exit;
		$this->assign('res',$resJava['data']);
		$this->assign('param',$param);
		$this->assign('beginTime',$beginTime);
		$this->assign('endTime',$endTime);
		$this->assign('bank_code_param',$bank_code_param);
		return $this->fetch();
	}

	//黑名单删除
	public function del()
	{
		$id = $this->request->param('data');
		// var_dump($id);exit;
		$resJava = doCurlGetRequest(['ids'=>$id],'/blacklist/del',true,config('black_interface_url'));
		return json($resJava);
	}

	//下载
	public function input_black()
	{
		$bankCode = $this->request->param('bankCode');
		$tmp_filename = $_FILES['file']['tmp_name'];
		// echo '<pre/>'; 
		// var_dump();
		// $dir = $_SERVER['DOCUMENT_ROOT'].'/upload/tmp.xlsx';
		// if(!move_uploaded_file($tmp_filename,$dir)) {
		// 	return json(['errorCode'=>'11002','message'=>'文件上传失败']);
		// }
		$data = ['file'=> new \CURLFile(realpath($tmp_filename),'','tmp.xlsx'),'bankCode'=>$bankCode];
		$result = doCurlPostRequest($data,'/blacklist/import',config('black_interface_url'));
		return json($result);
	}
}