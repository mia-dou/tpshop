<?php
//①声明命名空间 使用namespace关键字，名称：分组目录名称\Controller(控制器目录名称)
namespace Home\Controller;
//②引入父类控制器 使用use关键字  Think是命名空间名称，Controller是控制器父类的名称
use Think\Controller;
//③定义控制器类  控制器类名称和文件名保持一致	//定义具体的方法
class TestController extends Controller{
	//定义具体的方法
	public function index(){
		$res = curl_request('http://www.baidu.com', false);
		// $res = curl_request(U("Home/Test/test",'','.html',true), false);
		// dump($res);die;
		echo $res;die;
		$res = curl_request(U("Home/Test/test",'','.html',true), true, array('username'=>'zhenzhen'));
		dump($res);die;
		phpinfo();die;
		layout(false);
		$id = $_GET['id'];
		// echo 'Home Test index', $id;

		//使用U函数动态生成url
		// echo U('Home/Test/index'),'<br>';
		// echo U('Home/Test/index',array('id'=>100,'page'=>10)),'<br>';
		// echo U('Home/Test/index',array('id'=>100,'page'=>10),'.htm'),'<br>';
		// echo U('Home/Test/index',array('id'=>100,'page'=>10),true,true),'<br>';
		// echo U('Home/Test/index','id=100&page=10');
		// $this -> display();
		// $this -> display('index');//可以传递不带后缀的模板文件名称
		$this -> display('/Test/index');//可以指定控制器名文件夹和不带后缀的模板文件名称

	}

	public function test(){
		$data = I('post.');
		echo json_encode($data);die;
		// $person = array('name' => 'zhenzhen', 'age' => 18);
		// echo '<pre>';
		// var_dump($person);
		// dump($person, false);
	}
}