<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;
class LoginController extends Controller
{
    public function login()
    {
        if (IS_POST) {
            $data = I('post.');
            //判断验证码
            $verify = new Verify;
            if (!$verify->check($data['verify'])) {
                $this->error('验证码错误');
            }
            //根据用户名查询数据库
            $model = M('Manager');
            $user = $model->where(array('username' => $data['username']))->find();
            if ($user) {
                if (encrypt_password($data['password'] == $user['password'])) {
                    $user['last_login_time'] = time();
                    session('manager_user', $user);
                    $this->success('登录成功', U('Admin/Index/index'));
                } else {
                    $this->error('密码错误');
                }
            } else {
                $this->error('账号不存在');
            }
        }else {
            $this->display();
        }
    }
    //生成验证码方法
    public function captcha(){
        //生成验证码图片并显示
        // $verify = new \Think\Verify();
        //自定义配置数组
        $config = array(
            'useCurve'  =>  false,            // 是否画混淆曲线
            'useNoise'  =>  false,            // 是否添加杂点
            'length'    =>  4,               // 验证码位数
        );
        //实例化验证码类
        $verify = new Verify($config);
        //调用entry方法生成并输出验证码
        $verify -> entry();
    }
    // //退出
    // public function logout()
    // {   
    //     M('Manager')
    //         ->where(['username' => session('user.username')])
    //            ->save(['last_login_time' => session('user.last_login_time')]);
    //     session(null);
    //     $this->redirect('Admin/Login/login');
    // }
}