<?php
namespace Admin\Model;
use Think\Model;

class GoodsModel extends Model{
	//字段映射
	protected $_map = array(
		//'表单中的name名称' => '数据表中的字段名称'
		'name' => 'goods_name',
		'price' => 'goods_price',
		'number' => 'goods_number'
	);
	//自动验证

	protected $_validate = array(
		//array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
		array('goods_name','require','商品名称字段不能为空',0,'',3),
		array('goods_price','require','商品价格字段不能为空',0,'',3),
		array('goods_price','currency','商品价格字段格式不正确',0,'',3),
		array('goods_number','require','商品数量字段不能为空',0,'',3),
		array('goods_number','number','商品数量字段不能为空',0,'',3),
	);
	//自动完成
	//protected $_auto = array(
		//array(完成字段1,完成规则,[完成条件,附加规则]), 
		// array('goods_create_time','time',1,'function')
	// );
    protected $_auto = [
        ['goods_create_time','time',self::MODEL_INSERT,'function']
    ];
//商品logo图片上传
	public function upload_logo($files, $data){
		//实例化文件上传类
		$config = array(
			'maxSize' => 2 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)(单位byte)
			'exts' => array('jpg','png','gif','jpeg'), //允许上传的文件后缀
			'rootPath' => WEB_ROOT . UPLOAD_PATH, //保存根路径
		);
		$upload = new \Think\Upload($config);
		//使用upload 或者uploadOne 方法完成文件上传
		$upload_res = $upload -> uploadOne($files['goods_img']);
		if($upload_res){
			//文件上传成功
			// dump($upload_res);die;
			$data['goods_big_img'] = UPLOAD_PATH . $upload_res['savepath'] . $upload_res['savename'];
			//接下来直接在下方add方法就可以添加数据到数据库了。

			//在文件上传成功之后生成缩略图
			//实例化Image类
			$image = new \Think\Image();
			// ①打开一幅图像 open方法
			$image -> open(WEB_ROOT . UPLOAD_PATH . $upload_res['savepath'] . $upload_res['savename']);
			// ②生成缩略图 thumb 方法 （需要指定最大宽高）
			$image -> thumb(50, 30);
			// ③保存缩略图 save方法
			$save_path = UPLOAD_PATH . $upload_res['savepath'] . 'thumb_' . $upload_res['savename'];
			$image -> save(WEB_ROOT . $save_path);
			//把缩略图的路径保存到数据库
			$data['goods_small_img'] = $save_path;
			//返回添加了两个新字段之后的$data。
			return $data;
		}else{
			//文件上传失败
			return false;
		}
	}

	//相册图片的上传
	public function upload_pics($files,$goods_id){
		//判断是否有文件需要上传
		//方法：判断$files['goods_pics']['error'] 这个数组中 最小值 如果为0 表示有文件需要上传，否则所有文件都发生了错误，不需要上传
		// if判断条件中 如果是判断一个变量 == 值， 为了防止少写一个=，可以把值写在前面 变量写在等号后面 这样如果少些一个等号 直接会报错
		if(0 != min($files['goods_pics']['error'])){
			return false;
		}
	
		//实例化文件上传类
		$config = array(
			'maxSize' => 2 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)(单位byte)
			'exts' => array('jpg','png','gif','jpeg'), //允许上传的文件后缀
			'rootPath' => WEB_ROOT . UPLOAD_PATH, //保存根路径
		);
		$upload = new \Think\Upload($config);
		//使用upload方法完成多文件的上传
		$res = $upload -> upload($files);
		// dump($res);die;
		if(!$res){
			return false;
		}
		//上传成功，需要对$res进行处理，要上传成功的图片地址都保存到数据库tpshop_goodspics表
		//多文件上传，需要同时向数据库插入多条记录  add()  addAll($data)
		//如何组装多条数据的数组
		$data = array();
		foreach($res as $k => $v){
			$data[$k]['goods_id'] = $goods_id;
			$data[$k]['pics_origin'] = UPLOAD_PATH . $v['savepath'] . $v['savename'];

			//生成缩略图  
			$image = new \Think\Image();
			$image -> open(WEB_ROOT . $data[$k]['pics_origin']);
			//生成大图 800 * 800
			$image -> thumb(800, 800);
			$image -> save(WEB_ROOT . UPLOAD_PATH . $v['savepath'] . 'big_' . $v['savename']);
			//生成中图 350 * 350
			$image -> thumb(350, 350);
			$image -> save(WEB_ROOT . UPLOAD_PATH . $v['savepath'] . 'mid_' . $v['savename']);
			//生成中图 50 * 50
			$image -> thumb(50, 50);
			$image -> save(WEB_ROOT . UPLOAD_PATH . $v['savepath'] . 'sma_' . $v['savename']);

			$data[$k]['pics_big'] = UPLOAD_PATH . $v['savepath'] . 'big_' . $v['savename'];
			$data[$k]['pics_mid'] = UPLOAD_PATH . $v['savepath'] . 'mid_' . $v['savename'];
			$data[$k]['pics_sma'] = UPLOAD_PATH . $v['savepath'] . 'sma_' . $v['savename'];
		}
		//添加多条数据到数据表
		$result = M('Goodspics') -> addAll($data);
		if($result){
			return true;
		}else{
			return false;
		}

	}
}