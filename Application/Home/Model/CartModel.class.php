<?php
namespace Home\Model;
use Think\Model;
class CartModel extends Model{
	//添加购物数据到购物车
	//参数要求 $goods_id  $goods_attr_ids  $number
	public function addCart($goods_id, $goods_attr_ids, $number){
		//根据登录状态来确定保存位置
		if(session('?user_info')){
			//已登录，把数据保存到数据库
			$user_id = session('user_info.id');
			//组装要添加的数据
			$data = array(
				'user_id' => $user_id,
				'goods_id' => $goods_id,
				'goods_attr_ids' => $goods_attr_ids,
				// 'number' => $number
			);
			//保存到数据库
			//需要先查询 当前购买商品记录是否已经存在在购物车中（user_id 和 goods_id goods_attr_ids）
			$info = $this -> where($data) -> find();
			if($info){
				//如果记录已经存在，则修改商品数量 （原来的数量 + 新增的数量）
				$info['number'] += $number;
				//修改查询到的数据中的number 然后重新保存
				$this -> save($info);
			}else{
				//如果记录不存在，则直接新增新的记录
				$data['number'] = $number;
				$this -> add($data);
			}
		}else{
			//未登录，把数据保存到cookie
			$key = $goods_id . '-' . $goods_attr_ids;

			//获取购物车已有数据
			$cart_data = cookie('cart') ? unserialize(cookie('cart')) : array();
			if(isset($cart_data[$key])){
				// 查询当前记录是否已经存在，存在，增加新的数量
				$cart_data[$key] += $number;
			}else{
				//不存在,直接保存数量
				$cart_data[$key] = $number;
			}
			//如果记录不存在
			// $cart_data = array(
			// 	$key => $number
			// );
			//序列化数组变成字符串
			$cart_data_str = serialize($cart_data);
			//保存到cookie
			cookie('cart', $cart_data_str);

		}
	}

	//获取所有的购物车数据
	public function getAllCart(){
		//根据登录状态分别去cookie和数据库查询数据
		if(session('?user_info')){
			//已登录直接查询数据表tpshop_cart表
			//只获取当前用户的数据
			$user_id = session('user_info.id');
			$return = $this -> where("user_id=$user_id") -> select();//结果是二维数组
			// return $cart_data;
		}else{
			//未登录从cookie中获取
			$cart_data = cookie('cart') ? unserialize(cookie('cart')) : array();//一维数组
			// key : goods_id-goods_attr_ids  value: number
			//把结果组装成二维数组（格式和从数据库查询的格式保持一致）
			foreach($cart_data as $k => $v){
				$ids = explode('-', $k);
				$row = array(
					'goods_id' => $ids[0],
					'goods_attr_ids' => $ids[1],
					'number' => $v
				);
				$return[] = $row;
			}
			//得到的$return 就是最终需要的二维数组
			// return $return;
		}
		return $return;
	}

	//获取购物车商品购买总数量
	public function getNumber(){
		//先获取购物车中所有的数据
		$cart_data = $this -> getAllCart();
		//再对number字段进行累加
		$total_number = 0;
		foreach ($cart_data as $key => $value) {
			$total_number += $value['number'];
		}
		return $total_number;
	}

	//修改商品购买数量
	public function changeNumber($goods_id, $goods_attr_ids, $number){
		//判断登录状态
		if(session('?user_info')){
			$user_id = session('user_info.id');
			//取出当前这条数据
			$info = $this -> where("user_id=$user_id and goods_id=$goods_id and goods_attr_ids='$goods_attr_ids'") -> find();
			//修改数量
			$info['number'] = $number;
			//保存
			$this -> save($info);
		}else{
			//取出cookie中存的所有数据
			$cart_data = unserialize(cookie('cart'));
			$key = $goods_id . '-' . $goods_attr_ids;
			//根据$key 去修改指定的记录的数量
			$cart_data[$key] = $number;
			//重新保存购物车数据
			cookie('cart', serialize($cart_data));
		}
	}

	//删除购物记录
	public function delCart($goods_id, $goods_attr_ids){
		//判断登录状态
		if(session('?user_info')){
			//已登录，删除数据库中记录
			$user_id = session('user_info.id');
			$this -> where("user_id=$user_id and goods_id=$goods_id and goods_attr_ids='$goods_attr_ids'") -> delete();
		}else{
			//未登录
			$cart_data = unserialize(cookie('cart'));
			$key = $goods_id . '-' . $goods_attr_ids;
			//从数组中删除一条记录
			// $cart_data[$key] = null;
			unset($cart_data[$key]);
			//重新保存购物车数据到cookie
			cookie('cart', serialize($cart_data));
		}
	}

	//将cookie中的购物车数据保存到数据库
	public function cookieTodb(){
		//获取cookie中所有的购物车数据
		//由于当前方法是在登录之后再调用的，需要直接从cookie获取数据
		$cart_data = unserialize(cookie('cart'));
		// key  $goods_id . '-' . $goods_attr_ids; value number
		foreach($cart_data as $k => $v){
			$ids = explode('-', $k);
			$goods_id = $ids[0];
			$goods_attr_ids = $ids[1];
			$number = $v;
			//把当前这条数据保存到数据表tpshop_cart中
			//这里是已登录，也相当于 “加入购物车” 功能，可以直接调用addCart方法
			$this -> addCart($goods_id, $goods_attr_ids, $number);
		}
		//删除cookie中的购物车数据
		cookie('cart', null);
	}
}