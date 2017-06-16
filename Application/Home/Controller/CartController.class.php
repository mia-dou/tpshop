<?php
namespace Home\Controller;
use Think\Controller;
class CartController extends Controller{
	public function addcart(){
		$data = I('post.');
		// dump($data);die;
		//调用Cart模型的addCart方法
		$model = D('Cart');
		$model -> addCart($data['goods_id'], $data['goods_attr_ids'], $data['number']);
		//添加成功 需要显示一个页面
		//查询当前购买记录中的商品信息
		$goods = M('Goods') -> find($data['goods_id']);
		$this -> assign('goods', $goods);
		$this -> display();
	}
	//购物车列表页
	public function cart(){
		//获取购物车数据
		$data = D('Cart') -> getAllCart();
		//获取商品的基本信息和商品属性信息
		foreach($data as $k => &$v){
			//商品基本信息
			$goods = M('Goods') -> find($v['goods_id']);
			$v['goods_name'] = $goods['goods_name'];
			$v['goods_small_img'] = $goods['goods_small_img'];
			$v['goods_price'] = $goods['goods_price'];
			//商品属性信息（购买记录中选中的单选属性）(主要获取属性名称和属性值)
			$goods_attr = M('GoodsAttr') ->alias('t1') -> field('t1.*, t2.attr_name') -> join("left join tpshop_attribute t2 on t1.attr_id=t2.attr_id") -> where("t1.id in ({$v['goods_attr_ids']})") -> select();
			$v['goods_attr'] = $goods_attr;
		}
		$this -> assign('data', $data);
		$this -> display();
	}
	//ajax获取购物车商品购买的总数量方法
	public function ajaxgetnum(){
		//调用cart模型的getNumber方法获取数量
		$total_number = D('Cart') -> getNumber();
		//返回数据

		$return = array(
			'code' => 10000,
			'msg' => 'success',
			'total_number' => $total_number
		);
		$this -> ajaxReturn($return);
	}

	//ajax修改购物车商品购买数量
	public function ajaxchangenum(){
		//接收ajax数据
		$data = I('post.');
		D('Cart') -> changeNumber($data['goods_id'], $data['goods_attr_ids'], $data['number']);
		//获取一次购物车商品购买总数量
		$total_number = D('Cart') -> getNumber();
		//返回数据
		$return = array(
			'code' => 10000,
			'msg' => 'success',
			'total_number' => $total_number

		);
		$this -> ajaxReturn($return);
	}

	//ajax删除购物记录
	public function ajaxdelcart(){
		$data = I('post.');
		D('Cart') -> delCart($data['goods_id'], $data['goods_attr_ids']);
		//获取一次购物车商品购买总数量
		$total_number = D('Cart') -> getNumber();
		//返回数据
		$return = array(
			'code' => 10000,
			'msg' => 'success',
			'total_number' => $total_number
		);
		$this -> ajaxReturn($return);

	}

	//结算页面
	public function flow2(){
		if(session('?user_info')){
			$user_id = session('user_info.id');
			//收货地址信息
			$address = M('Address') -> where("user_id = $user_id") -> select();
			$this -> assign('address', $address);
			//获取购物车表中指定的数据 I('get.cart_ids')  
			$cart_ids = I('get.cart_ids');
			//连表查询  tpshop_goods表
			$cart = M('Cart') -> alias('t1') -> field('t1.*, t2.goods_name,t2.goods_small_img,t2.goods_price') -> join("left join tpshop_goods t2 on t1.goods_id=t2.id") -> where("t1.id in ($cart_ids)") ->  select();
			//查询商品属性信息 连表 tpshop_attribute
			foreach($cart as $k => &$v){
				// $v['goods_attr_ids']
				$v["goods_attr"] = M('GoodsAttr') -> alias('t3') -> field('t3.*,t4.attr_name') -> join("left join tpshop_attribute t4 on t3.attr_id = t4.attr_id") -> where("t3.id in ({$v['goods_attr_ids']})") -> select();

				//计算总金额
				$total_price += $v['goods_price'] * $v['number'];
			}
			// dump($cart);die;
			$this -> assign('cart', $cart);
			$this -> assign('total_price', $total_price);
			$this -> display();
		}else{
			//这里登录成功之后要跳回到购物车列表页
			session('back_url', U('Home/Cart/cart'));
			$this -> redirect('Home/User/login');
		}
	}

	//生成订单
	public function createorder(){
		if(IS_POST){
			$data = I('post.');
			// dump($data);die;
			//生成订单记录保存到订单表
			//生成订单编号，纯数字格式
			$data['order_sn'] = date('YmdHis') . rand(10000, 99999);
			//用户id
			$data['user_id'] = session('user_info.id');
			//创建时间
			$data['create_time'] = time();
			//订单总金额  $data['cart_ids']
			$goods_data = M('Cart') -> alias('t1') -> field('t1.*, t2.goods_price') -> join('left join tpshop_goods t2 on t1.goods_id=t2.id') -> where("t1.id in ({$data['cart_ids']})") -> select();
			foreach($goods_data as $k => $v){
				$data['order_amount'] += $v['goods_price'] * $v['number'];
			}
			unset($k, $v);//如果$v 使用了引用  &$v  后面还要继续使用$v变量，就需要unset
			//保存数据到订单表
			$order_id = M('Order') -> add($data);
			if($order_id){
				//保存数据到订单商品表
				foreach($goods_data as $k => $v){
					$order_data['order_id'] = $order_id;
					$order_data['goods_id'] = $v['goods_id'];
					$order_data['goods_price'] = $v['goods_price'];
					$order_data['number'] = $v['number'];
					$order_data['goods_attr_ids'] = $v['goods_attr_ids'];
					M('OrderGoods') -> add($order_data);
				}
				//接下来就是跳转到第三方支付
				// echo 'to pay';
				// sleep(2);
				// //跳转到支付成功页面
				// $this -> redirect('Home/Cart/flow3');
				//组装form表单，自动提交
				$form = "<form action='/Application/Tools/alipay/alipayapi.php' name='alipayform' class='alipayform' method='post' style='display:none'>
<input type='text' name='WIDout_trade_no' id='out_trade_no' value='{$data['order_sn']}'></div>
<input type='text' name='WIDsubject' value='tp电商支付'></div>
<input type='text' name='WIDtotal_fee' value='{$data['order_amount']}'></div>
<input type='text' name='WIDbody' value='你支付了我也不发货不退款'></div>
</form>
<script>document.alipayform.submit();</script>
";
				echo $form;
			}else{
				$this -> error('提交订单失败');
			}
			
		}
		
	}

	//支付成功页面
	public function flow3(){
		$data = I('get.');
		if($data['trade_status'] == 'TRADE_SUCCESS'){
			$order_sn = $data['out_trade_no'];
			$total_fee = $data['total_fee'];
			$this -> assign('order_sn', $order_sn);
			$this -> assign('total_fee', $total_fee);
			$this -> display();
		}
		
	}

	public function alipaynotify(){
		//本地的地址，支付宝是访问不到的，必须上线之后才能访问
		$data = I('post.');
		//所有订单的状态的修改以及数据库的修改都需要在这里进行。
	}
}