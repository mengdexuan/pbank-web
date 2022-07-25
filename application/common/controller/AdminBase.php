<?php
namespace app\common\controller;

use org\Auth;
use think\Loader;
use think\Controller;
use think\Db;
use think\Session;

/**
 * 后台公用基础控制器
 * Class AdminBase
 * @package app\common\controller
 */
class AdminBase extends Controller
{
    static protected $actionVersion = null;
    static protected $version = null;
    protected function _initialize()
    {
        parent::_initialize();

        $this->checkAuth();
        $this->getMenu();
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
        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $name = $module . '/' . $controller . '/' . $action;
        get_cur_bank();
        $cur_bank = session('cur_bank');
        $this->assign('cur_bank',$cur_bank);
        $this->assign('cur_url',$name);
        $this->assign('version',self::$version);
        // 输出当前请求控制器（配合后台侧边菜单选中状态）
        $this->assign('controller', Loader::parseName($this->request->controller()));
        $this->assign('mothod', Loader::parseName($this->request->action()));
    }

    /**
     * 权限检查
     * @return bool
     */
    protected function checkAuth()
    {

        if (!Session::has('admin_id')) {
            $this->redirect('admin/login/index');
        }

        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        // 排除权限
        $not_check = ['admin/Index/index', 'admin/AuthGroup/getjson', 'admin/System/clear'];

        if (!in_array($module . '/' . $controller . '/' . $action, $not_check)) {
            $auth     = new Auth();
            $admin_id = Session::get('admin_id');
            $admin_name = Session::get('admin_name');
            if (!$auth->check($module . '/' . $controller . '/' . $action, $admin_id) && $admin_id != 1 && $admin_name != 'admin') {
                $this->error('没有权限');
            }
        }
    }

    /**
     * 获取侧边栏菜单
     */
    protected function getMenu()
    {
        $menu     = [];
        $admin_id = Session::get('admin_id');
        $admin_name = Session::get('admin_name');
        $auth     = new Auth();

        $auth_rule_list = Db::name('auth_rule')->where('status', 1)->order(['sort' => 'DESC', 'id' => 'ASC'])->select();

        foreach ($auth_rule_list as $value) {
            if ($auth->check($value['name'], $admin_id) || $admin_id == 1 || $admin_name == 'admin') {
                $menu[] = $value;
            }
        }
        $menu = !empty($menu) ? array2tree($menu) : [];

        $this->assign('menu', $menu);
    }
}