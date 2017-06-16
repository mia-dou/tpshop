<?php
namespace Api\Controller;
use Think\Controller;
class IndexController extends Controller{
	public function qqcallback(){
		//获取到登录的qq账户的基本信息
		require_once("/Application/Tools/qq/API/qqConnectAPI.php");
		$qc = new \QC();
		$access_token = $qc->qq_callback();
		// echo '<br>';
		$openid = $qc->get_openid();
		//这里直接调用get_user_info有时候会报错
		$qc = new \QC($access_token, $openid);
		//获取登录qq用户信息
		$info = $qc -> get_user_info();
		// dump($info);die;
		//进行处理
		$user = M('User') -> where("openid = '$openid'") -> find();
		if($user){
			//老用户
			//直接登录 写session
			session('user_info', $user);
			$url = U("Home/Index/index");
			echo "<script>window.opener.location.href='$url';window.close();</script>";die;
			// $this -> success('登录成功', U('Home/Index/index'));
		}else{
			//新用户 自动注册
			$data = array(
				'openid' => $openid,
				'username' => $info['nickname'],
				'create_time' => time()
			);
			$user_id = M('User') -> add($data);
			if($user_id){
				//自动登录 写session
				$user = M('User') -> find($user_id);
				session('user_info', $user);
				$url = U("Home/Index/index");
				echo "<script>window.opener.location.href='$url';window.close();</script>";die;
				// $this -> success('登录成功', U('Home/Index/index'));
			}else{
				echo "<script>alert('登录失败'); window.close();</script>";die;
				// $this -> error('error');
			}
		}
	}

	//发送短信
	public function sendmsg(){
		$phone = I('post.phone');
		//调用短信接口发送短信
		$url = "http://v.juhe.cn/sms/send?mobile=$phone&tpl_id=34764&key=d36c1d9829185050b5ac21d5beafa4a8&tpl_value=";
		//短信模板中变量  #act#  #code# #rand#
		$act = '注册';
		$code = rand(100000, 999999);
		$rand = rand(1, 99);
		//拼接tpl_value参数
		$tpl_value = "#act#=$act&#code#=$code&#rand#=$rand";
		//进行urlencode编码
		$tpl_value = urlencode($tpl_value);
		$url .= $tpl_value;
		//发送get请求
		$res = curl_request($url, false);
		// dump($res);die;
		if($res['error_code'] == 0){
			//发送成功，保存验证码到session，用于后续的验证
			session('sms_code' . $phone, $code);
			$return = array(
				'code' => 10000,
				'msg' => '发送成功'
			);
		}else{
			$return = array(
				'code' => $res['error_code'],
				'msg' => $res['reason']
			);
		}
		$this -> ajaxReturn($return);
	}
}