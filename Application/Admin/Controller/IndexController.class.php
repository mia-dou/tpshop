<?php
namespace Admin\Controller;
// use Think\Controller;
class IndexController extends CommonController {
    /**
     *  后台首页
     */ 
    public function index(){
    	//
        $this->display();
    }
    //退出
	public function logout(){
		//删除所有session
		session(null);
		//跳转到登录页面
		// $this -> success('退出成功', U('Admin/Login/login'));
		$this -> redirect('Admin/Login/login');
	}
	public function test(){

		//使用自定义函数
		// $phone = '15210618793';
		// echo encrypt_phone($phone);die;

		// $password = '123456';
		//使用load函数载入函数文件
		// load('Common/str');
		// echo encrypt_password($password);die;
		//类文件的引入
		// $page = new \Tools\Page();
		// echo $page -> getName();die;
		$model = D('Goods');
		dump($model);
	}

	public function test_cookie(){
		//设置cookie
		cookie('username', 'kongkong');
		//读取cookie
		// echo cookie('username');
		//删除cookie
		// cookie('username', null);
		//读取cookie
		// dump(cookie('username'));
		//设置cookie其他参数
		// $options = 3;//如果第三个参数是数字，表示设置了有效期，单位是秒
		// cookie('email', 'kongkong@itcast.cn', $options);
		$options = array('expire' => 3, 'prefix' => 'shop_');//数组格式的设置
		// $options = "expire=3&prefix=shop_";//数组格式的设置
		cookie('email', 'Miadouli$gmail.com', $options);
		dump( cookie('shop_email'));
		//删除带前缀的cookie
		cookie(null);
		dump(cookie('username'));
		cookie(null,'shop_');
		dump(cookie('shop_email'));
	}

	public function test_session(){
		//设置session
		session('username','mia');
		//读取session
		echo session('username');
		//删除session
		// session('username', null);
		//读取session
		dump(session('username'));

		session('user',array('username'=>'mia','age'=> 18));
		//修改session中数组中的某一个值
		// $user = session('user');
		// $user['age'] = 19;
		// session('user', $user);
		//直接修改
		session('user.age', 19);
		dump(session('user'));
		//判断是否设置session
		dump(session('?user'));
		//读取所有sessoin
		dump(session());
		//删除所有session
		session(null);
		dump(session());
	}
	public function test_xss(){

		//测试strip_tag函数 删除script标签
		// echo strip_tags('abc<script>alert("hello");</script>');die;
		//strip_tag的缺陷
		echo strip_tags('ab<c<script>alert("hello");</script>');die;

		$model = M('Advice', null);
		if(IS_POST){
			// var_dump($_POST);die;
			//存储到advice表
			
			$data = array(
				'user_id' => 100,
				'content' => $_POST['content'],
			);
			$result = $model -> add($data);
			if($result){
				$this -> success('添加成功', U('Admin/Index/test_xss'));
			}else{
				$this -> error('添加失败');
			}
		}else{
			//取数据
			$advice = $model -> find(3);
			$this -> assign('advice', $advice);
			$this -> display();
		}
	}
}
  
