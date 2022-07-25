<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 *柜面管理 大额
 * Class Cabinet
 * @package app\admin\controller
 */
class Cabinet extends AdminBase
{
	//大额查看
	public function detail()
	{
		$query_date = $this->request->param('query_date');
		$keyword = $this->request->param('keyword');
		$beginTime = $this->request->param('beginTime');
        $endTime = $this->request->param('endTime');
		$page = $this->request->param('page');
		$query_date_end = date('Y-m-d',time());
		if(!$query_date || $query_date == 'today') {
			if ($query_date == 'today') {
				$checkTime = 'today';
			}
            $query_date = $query_date_end;
        }elseif ($query_date == 'week') {
        	$checkTime = 'week';
            // $query_date = date('Y-m-d',strtotime($query_date_end) - 86400*7);
        	$query_date = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));//自然
            $query_date_end = date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));//自然
        }elseif ($query_date == 'month') {
        	$checkTime = 'month';
            // $query_date = date('Y-m-d',strtotime($query_date_end) - 86400*30);
        	$query_date = date('Y-m-01',time());//自然
            $query_date_end = date('Y-m-d', strtotime("$query_date +1 month -1 day"));//自然
        }elseif ($query_date == 'tmonth') {
        	$checkTime = 'tmonth';
        	// $query_date = date('Y-m-d',strtotime($query_date_end) - 86400*90);
            $first = date('Y-m-01',time());//自然
            $query_date = date('Y-m-d', strtotime('-2 month', strtotime(date('Y-m', time()) . '-01 00:00:00')));;//自然
            $query_date_end = date('Y-m-d', strtotime("$first +1 month -1 day"));//自然
        }

        if ($beginTime && $endTime) {
        	$query_date_end = $endTime;
        	$query_date = $beginTime;
        }
        if (!$page) $page = 1;
      	$res = doCurlGetRequest(['beginTime'=>$query_date,'endTime'=>$query_date_end,'searchKey'=>$keyword,'pageNumber'=>$page],'/large_cash/pageQuery');
        if (isset($res['success']) && $res['success']) {
            $data = $res['data']['list'];
            $totalPage = $res['data']['totalPage'];//总页数
        }else{
            $data = false;
        }
		$this->assign('query_date',$query_date);
		$this->assign('query_date_end',$query_date_end);
		$this->assign('checkTime',isset($checkTime)?$checkTime:'');
		$this->assign('keyword',$keyword);
        $this->assign('data',$data);
        $this->assign('page',$page);
        $this->assign('requestUrl',$_SERVER['REQUEST_URI']);
        $this->assign('totalPage',isset($totalPage)?$totalPage:'');
		return $this->fetch();
	}

    //银行查看
    public function amount()
    {
        $param = $this->request->param();
        $page = $this->request->param('page');
        //当前页
        if (!$page) $page = 1;
        $data = doCurlGetRequest(['beginTime'=>$param['beginTime'],'endTime'=>$param['endTime'],'searchKey'=>$param['keyword'],'bankCode'=>$param['bankCode'],'pageNumber'=>$page],'/large_cash/pageQueryBranchList');
        return json($data);
    }

    //笔数查看
    public function three()
    {
        $param = $this->request->param();
        $page = $this->request->param('page');
        //当前页
        if (!$page) $page = 1;
        $data = doCurlGetRequest(['beginTime'=>$param['beginTime'],'endTime'=>$param['endTime'],'searchKey'=>$param['keyword'],'branchCode'=>$param['branchCode'],'pageNumber'=>$page],'/large_cash/detail');//,'pageSize'=>1
        // if ($data['success'] && !empty($data['data'])) {
        //     foreach ($data['data'] as $k => $v) {
        //         $data['data'][$k]['submitTime'] = date('Y-m-d H:i:s',$v['submitTime']/1000);
        //     }
        // }
        return json($data);
    }
}