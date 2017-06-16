<?php
namespace Admin\Controller;
use Think\Controller;
// use Think\Upload;

/**
 * 商品管理
 */
class GoodsController extends CommonController{
    public function goods_list(){
        //查询总记录数
        $model = M('Goods');
        $total=$model->count();
        //显示条数
        $pagesize = 5;
        //分页
        $page = new \Think\Page($total,$pagesize);
        //自定义属性和配置
        $page->rollPage = 6;
        $page->lastSuffix= false;
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('first','首页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');        
        //获取分页栏的html代码
        $page_html = $page->show();
        $this->assign('page_html',$page_html);
        //查询每页显示的数据
        $data = $model->limit($page->firstRow,$page->listRows)->select();
        $this->assign("data",$data);
        $this->display();
    }

    public function goods_add(){
        if(IS_POST){
            //post 处理表单
            //接受表单提交的数据
           $data = I('post.');
           //需要对goods_introduce字段做特殊处理(以下两种二选一)
            $data['goods_introduce'] = remove_xss($_POST['goods_introduce']);
           //实例化模型类
           $model = D('Goods');
        //    \dump($_FILES);die();
           //判断当前上传的文件的error信息是否为0
			if($_FILES['goods_img']['error'] == 0){
				//没有错误，可以上传
				//直接调用模型中封装的方法
				$data = $model -> upload_logo($_FILES, $data);
				if(!$data){
					$this -> error('上传失败');
				}
			}
           
            //使用create方法创建数据集
            if(!$model -> create($data)){
                //获取模型的错误信息
                $error = $model -> getError();
                $this -> error($error);
            }

           //调用add方法
           $res = $model->add($data);
           //判断结果
           if($res){
				
				foreach($data['attr_name'] as $k => $v){
					//$k 就是attr_id 值
					foreach($v as $attr){
						// $attr 就是 attr_value 值
						$attr_data[] = array(
							//上面添加商品时返回值就是添加成功的主键id
							'goods_id' => $res,
							'attr_id' => $k,
							'attr_value' => $attr
						);
					}
				}
				//多条属性数据的批量添加操作
				$attr_res = M('GoodsAttr') -> addAll($attr_data);
                // dump($attr_res);die;
				//对于$attr_res 这个结果，可以不做处理了。
				//商品相册
				//删除$_FILES里面的goods_img字段,不建议直接操作$_FILES ,可以重新赋值一个变量
				$files = $_FILES;
				unset($files['goods_big_img']);
				//调用模型的upload_pics方法，传入参数文件数组和商品id 这里是$res
				//调用成功之后的返回值，可以不关心
				$model -> upload_pics($files, $res);
				//商品添加成功之后，完成相册图片的上传操作  使用Upload类完成上传
				//把多文件上传的功能封装到Goods模型中一个方法 upload_pics方法
				$this -> success('添加成功', U('Admin/Goods/goods_list'));
				// $this -> error('添加失败，请重试');
			}else{
				//添加失败，跳回添加页
				$this -> error('添加失败，请重试');
            }

        }else{
            //get请求 展示页面
            //查询商品分类信息
            $category = M('Category') -> select();
            $this -> assign('category', $category);
            $type =M('Type')->select();
            // dump($type);die;
            $this->assign('type',$type);
            $this->display();
        }

        

    }

    public function goods_edit(){
        $model = D('Goods');
        if(IS_POST){
            $data = I('post.');
            //对商品描述字段做特殊处理 防范xss攻击 使用htmlpurifier
            $data['goods_introduce'] = I('post.goods_introduce', '', 'remove_xss');
            $goods =$model -> find($data['id']);
            //图片上传
            $flag = 0;
            if($_FILES['goods_img']['error'] == 0){
                //没有错误，可以上传
                $data = $model ->upload_logo($_FILES,$data);
                if(!$data){
                    $this->error('上传失败');
                }
                $flag=1;
           }
           //使用create方法自动创建数据集（才可以使用字段映射等功能）
            if(! $model -> create($data) ){
                //创建失败，获取错误信息
                $error = $model -> getError();
                $this -> error($error);
            }
            //调用sava 方法 
           $res = $model->save($data);
            if($res !== false){
                foreach($data['attr_name'] as $k => $v){
					//$k 就是attr_id 值
					foreach($v as $attr){
						// $attr 就是 attr_value 值
						$attr_data[] = array(
							//上面添加商品时返回值就是添加成功的主键id
							'goods_id' => $data['id'],
							'attr_id' => $k,
							'attr_value' => $attr
						);
					}
				}
                //先删除商品原来的属性
                M('GoodsAttr') -> where("goods_id={$data['id']}") -> delete();
            
                
				//多条属性数据的批量添加操作
				$attr_res = M('GoodsAttr') -> addAll($attr_data);
                // \dump($attr_res);die;
                //修改成功
				if($flag){
					//删除旧图片 需要获取原始图片在数据库中保存的路径
					//删除大图
					unlink(WEB_ROOT . $goods['goods_big_img']);
					//删除缩略图
					unlink(WEB_ROOT . $goods['goods_small_img']);
				}
				//上传相册图片
				//调用Goods模型中的upload_pics方法 ，传递文件数组 和商品id
				//文件数组$_FILES中也还有goods_img字段，也是需要先删除
				//另外一种方式，只需要保证$files 结构 和$_FILES保持一直
				// dump($_FILES);die;
				$files = array($_FILES['goods_pics']);
				$model -> upload_pics($files, $data['id']);
                //修改成功
                $this->success('修改成功',U('Admin/Goods/goods_list'));
            }else {
                $this->error('修改失败,请重试');
            }
        }else{
            //修改操作
            $id = I('get.id');
            //查询对应的商品信息
            $goods = $model->find($id);
            $this->assign('goods',$goods);

            // //查询商品分类信息
            $category = M('Category') -> select();
            $this -> assign('category', $category);

            //查询商品的类型信息
            $type =M('Type')->select();
            // dump($type);die;
            $this->assign('type',$type);
            
            //获取该商品对应的商品类型对应的所有属性（tpshop_attribute表）
            $attribute = M('Attribute') -> where("type_id={$goods['type_id']}") -> select();
            // dump($attribute);
            //把每个属性中的可选值转化为数组（方便页面上遍历操作）
            foreach($attribute as $k => &$v){
                $v['attr_values'] = explode(',', $v['attr_values']);
            }
            unset($k, $v);
            // dump($attribute);die;
            $this -> assign('attribute', $attribute);
            //获取当前商品拥有的所有属性（tpshop_goods_attr表）
            $goods_attr = M('GoodsAttr') -> where("goods_id=$id") -> select();
            //对goods_attr做处理，转化成
            // array('attr_id' => array('attr_value','attr_value'))即：
            // array('10' => array('北京昌平'),'11'=>array('210g'),'12'=>array('原味','奶油','炭烧'))
            // 形式，方便页面判断
            $new_goods_attr = array();
            foreach($goods_attr as $k => $v){
                $new_goods_attr[ $v['attr_id'] ][] = $v['attr_value'];
            }
            unset($k, $v);
            // dump($new_goods_attr);die;
            $this -> assign('new_goods_attr', $new_goods_attr);
            //查询商品的相册图片
            $goods_pics = M('Goodspics') -> where(array('goods_id' => $id)) -> select();
            $this -> assign('goods_pics', $goods_pics);
            $this->display();
        }
    }

    public function goods_detail(){
        $id =I('get.id');
        $model = D('Goods');
        $row = $model->find($id);
        $this->assign('row',$row);
        //获取图表信息
        $data_arr=[
            ['name'=>'online','data'=>[]],
            ['name'=>'offline','data'=>[]]
        ];

        // $this -> assign('goods', $goods);
        // //获取highcharts图表需要的数据信息
        // //先确定最终要组装的数据格式
        // $data_arr = array(
        //     array('name'=>'online','data'=>array()),
        //     array('name'=>'offline','data'=>array()),
        // );
        //获取图表信息
        $online_model = M('Saleonline');
        $online_data = $online_model->where(['goods_id'=>$id])->order('month asc')->select();
        foreach($online_data as $k => $v){
            $data_arr[0]['data'][] = floatval($v['money']);
        }
        unset($k); unset($v);
        $offline_model = M('Saleoffline');
        $offline_data = $offline_model->where(['goods_id'=>$id])->order('month asc')->select();
        foreach($offline_data as $k => $v){
            $data_arr[1]['data'][] = floatval($v['money']);
        }
        
        $data_json = json_encode($data_arr);
        // \dump($data_json);die();
        $this->assign('data_json',$data_json);
        $this->display();
    }
    public function goods_delete(){
        $model = M('Goods');
        $id = I('get.id');
        $res = $model->delete($id);
        if($res !== false){
                //删除成功
                $this->success('删除成功',U('Admin/Goods/goods_list'));
        }else {
                $this->error('添加失败,请重试');
        }
    }
    //删除相册图片 ajax请求
    public function delete_pics(){
        $id = I('get.id');
        $pics = M('Goodspics') -> find($id);
        $res = M('Goodspics') -> delete($id);
        if($res !== false){
            unlink(WEB_ROOT . $pics['pics_origin']);
            unlink(WEB_ROOT . $pics['pics_big']);
            unlink(WEB_ROOT . $pics['pics_mid']);
            unlink(WEB_ROOT . $pics['pics_sma']);
            //返回值
            $return = [
                'code' => 10000,
                'msg'  => 'success'
            ];
        }else{
            //返回值
            $return = [
                'code' => 10001,
                'msg'  => 'fail'
            ];
        }
        $this -> ajaxReturn($return);
    }
   
   //获取属性
   public function getattr(){
       $type_id = I('post.type_id');
       //检测type_id 格式
       if($type_id != intval($type_id)){
        $return = [
            'code'  => 10001,
            'msg'   => '参数格式不正确',
            'data'  => []
        ];
        $this -> ajaxReturn($return);
       }
       //查询type_id信息
       $attrs = M('Attribute') -> where("type_id=$type_id") -> select();
       //返回
       $return = [
            'code'  => 10000,
            'msg'   => 'success',
            'data'  => $attrs
       ];
       $this -> ajaxReturn($return);
   }

    //ajax
    public function lists(){
        $this->display();
    }

    public function goods_ajax(){
        $nowPage = I('get.nowPage');
        $model = D('Goods');
        $pagesize = 5;
        $offset =($nowPage - 1) * $pagesize;
        $data = $model->limit($offset,$pagesize)->select();
       //获取总页数
        //先获取总记录数
        $total = $model -> count();
        $totalPage = ceil($total / $pagesize);
        //返回数据（json格式的数据） 通常包括 code  msg  data 三大部分
        $return = array(
            'code' => 10000,
            'msg' => 'success',
            'data' => $data,
            'totalPage' => $totalPage
        );
        $this->ajaxReturn($return);

    }
} 