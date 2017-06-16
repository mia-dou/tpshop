<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
	//自动验证
	protected $_validate = array(
		array('email', 'require', '邮箱不能为空'),
		array('email', 'email', '邮箱格式不正确'),
		array('email', '', '邮箱已被注册', 0, 'unique'),
		array('password', 'require', '密码不能为空'),
		array('re_password', 'password', '两次密码输入不一致', 0, 'confirm'),

		array('phone', 'require', '手机号不能为空'),
		array('phone', '/^\d{11}$/', '手机号格式不正确'),
		array('phone', '', '手机号已被注册', 0, 'unique'),

	);

	//自动完成
	protected $_auto = array(
		array('create_time', 'time', 1, 'function'),
		array('password', 'encrypt_password', 1, 'function')
	);
}