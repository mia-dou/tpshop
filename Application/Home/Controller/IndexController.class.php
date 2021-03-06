<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	/**
	 * 前台首页
	 */
    public function index(){
        // layout(true);
        //获取商品分类数据
        $category = M('Category') -> select();
        $this -> assign('category', $category);
        //获取指定的分类id为 18 19 20的分类及商品
        //cate_id = 18
        $cate_18 = M('Category') -> find(18);
        $goods_18 = M('Goods') -> where("cate_id = 18") -> select();
        $this -> assign('cate_18', $cate_18);
        $this -> assign('goods_18', $goods_18);

        //cate_id = 19
        $cate_19 = M('Category') -> find(19);
        $goods_19 = M('Goods') -> where("cate_id = 19") -> select();
        $this -> assign('cate_19', $cate_19);
        $this -> assign('goods_19', $goods_19);

        //cate_id = 20
        $cate_20 = M('Category') -> find(20);
        $goods_20 = M('Goods') -> where("cate_id = 20") -> select();
        $this -> assign('cate_20', $cate_20);
        $this -> assign('goods_20', $goods_20);

        $this -> assign('title', '首页');
        $this -> display();
    }

    /**
	 * 前台商品详情页
	 */
    public function detail(){
        //查询商品基本信息
        $id = I('get.id');
        $goods = M('Goods') -> find($id);
        $this -> assign('goods', $goods);
        //单选属性
        $attr_radio = M('GoodsAttr') -> alias('t1') -> field('t1.*, t2.attr_name') -> join('left join tpshop_attribute t2 on t1.attr_id=t2.attr_id') -> where("t1.goods_id=$id and t2.attr_type=1") -> select();
        // dump($attr_radio);die;
        //组装数据 
        /*
        array(
            '12' => array(
                array('id' => 31, 'goods_id' =>29, 'attr_value'=>'value1','attr_name'=>'口味'),
                array('id' => 32, 'goods_id' =>29, 'attr_value'=>'value2','attr_name'=>'口味')
                ),
            '13' => array(
                array('id' => 31, 'goods_id' =>29, 'attr_value'=>'value1','attr_name'=>'口味'),
                array('id' => 32, 'goods_id' =>29, 'attr_value'=>'value2','attr_name'=>'口味')
                ),
        )
        */
        $new_attr_radio = array();
        foreach($attr_radio as $k => $v){
            $new_attr_radio[$v['attr_id']][] = $v;
        }
        $this -> assign('new_attr_radio', $new_attr_radio);
        // dump($new_attr_radio);die;
        //获取唯一属性
        $attr_only = M('GoodsAttr') -> alias('t1') -> field('t1.*, t2.attr_name') -> join('left join tpshop_attribute t2 on t1.attr_id=t2.attr_id') -> where("t1.goods_id=$id and t2.attr_type=0") -> select();
        $this -> assign('attr_only', $attr_only);
        // dump($attr_only);die;

        //查询商品相册图片
        $goods_pics = M('Goodspics') -> where("goods_id=$id") -> select();
        $this -> assign('goods_pics', $goods_pics);

        $this -> assign('title', '商品详情');
    	$this -> display();
    }
}