<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 管理员验证器
 * Class AdminUser
 * @package app\admin\validate
 */
class AdminUser extends Validate
{
    protected $rule = [
        'loginname'         => 'require|unique:admin_user',
        'username'         => 'require',
        'password'         => 'confirm:confirm_password',
        'confirm_password' => 'confirm:password',
        'status'           => 'require',
        'group_id'         => 'require'
    ];

    protected $message = [
        'loginname.require'         => '请输入登录名',
        'loginname.unique'          => '登录名已存在',
        'username.require'         => '请输入姓名',
        //'username.unique'          => '用户名已存在',
        'password.confirm'         => '两次输入密码不一致',
        'confirm_password.confirm' => '两次输入密码不一致',
        'status.require'           => '请选择状态',
        'group_id'                 => '请选择所属权限组'
    ];
}