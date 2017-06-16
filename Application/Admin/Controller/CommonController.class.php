<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller{
    public function __construct() {
        parent::__construct();
        if(!session('?manager_user')){
            $this->error('请登录',U('Admin/Login/login'));
        }
        $this -> getNav();
        $this -> checkAuth();
    }
     //获取菜单权限
    public function getNav(){
        //判断session 有没有权限信息
        if(session('?top_nav') && session('?second_nav')){
            return ;
        }
        //获取 role_id
        $role_id = session('manager_user.role_id');
        if($role_id == 1){
            $top = M('Auth') -> where("pid = 0 and is_nav=1") -> select();
            $second = M('Auth') -> where("pid > 0 and is_nav=1") -> select();
        }else{
            //其它管理员
            $role = M('Role') -> find($role_id);
            $role_auth_ids = $role['role_auth_ids'];
            //顶级权限
             $top = M('Auth') -> where("pid = 0 and id in($role_auth_ids) and is_nav=1") -> select();
             //二级权限
            $second = M('Auth') -> where("pid > 0 and id in($role_auth_ids) and is_nav=1") -> select();
        }
        //保存菜单权限到session
        // \dump($second);die();
        session('top_nav',$top);
        session('second_nav',$second);
    }
    //检测权限
    public function checkAuth(){
        $role_id = session('manager_user.role_id');
        if($role_id == 1){
            return ;
        }
        $role = M('Role') -> find($role_id);
        $role_auth_ac = $role['role_auth_ac'];
        $role_auth_ac_arr = explode(',',$role_auth_ac);
        $c = CONTROLLER_NAME;
        $a = ACTION_NAME;
        //首页所有用户都可以访问 不需要权限
        if(strtolower($c) == 'index' && strtolower($a) == 'index'){
            return ;
        }
        $ac = $c . '-' . $a;
        // \dump($ac);die;
        if(!in_array($ac,$role_auth_ac_arr)){
            $this -> error('没有访问权限',U('Admin/Index/index'));
        }
    }
    
}