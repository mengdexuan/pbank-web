<?php
namespace app\admin\controller;

use think\Config;
use think\Controller;
use think\Db;
use think\Session;

/**
 * 后台登录
 * Class Login
 * @package app\admin\controller
 */
class Login extends Controller
{
    /**
     * 后台登录
     * @return mixed
     */
    public function index()
    {
        if(session('admin_name')){
            $this->redirect('/');
        }
        $bank_name = get_cur_bank('bank_name');
        $bank_logo = get_cur_bank('bank_logo');
        $this->assign('bank_name',$bank_name);
        $this->assign('bank_logo',$bank_logo);
        return $this->fetch();
    }

    /**
     * 登录验证
     * @return string
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->only(['username', 'password', 'verify']);
            $validate_result = $this->validate($data, 'Login');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $where['loginname'] = $data['username'];
                $where['password'] = md5($data['password'] . Config::get('salt'));

                $admin_user = Db::name('admin_user')->field('id,username,loginname,status')->where($where)->find();

                if (!empty($admin_user)) {
                    if ($admin_user['status'] != 1) {
                        $this->error('当前用户已禁用');
                    } else {
                        Session::set('admin_id', $admin_user['id']);
                        Session::set('admin_name', $admin_user['username']);
                        Session::set('login_name', $admin_user['loginname']);
                        Db::name('admin_user')->update(
                            [
                                'last_login_time' => date('Y-m-d H:i:s', time()),
                                'last_login_ip'   => $this->request->ip(),
                                'id'              => $admin_user['id']
                            ]
                        );
                        $this->success('登录成功', 'admin/index/index');
                    }
                } else {
                    $this->error('用户名或密码错误');
                }
            }
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
//        Session::delete('admin_id');
//        Session::delete('admin_name');
//        Session::delete('banks_info');
//        Session::delete('cur_bank');
        Session::clear();
        \think\Cookie::clear('parame');
        $this->success('退出成功', 'admin/login/index');
    }
}
