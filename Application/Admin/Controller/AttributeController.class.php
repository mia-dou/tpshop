<?php
namespace Admin\Controller;

class AttributeController extends CommonController{
    public function attr_add(){
        if(IS_POST){
            $data = I('post.');
            $res = M('Attribute') -> add($data);
            if($res){
                $this -> success('添加成功',U("Admin/Attribute/attr_list"));
            }else{
                $this -> error('添加失败');
            }
        }else{
        //查询商品类型
            $type = M('Type') -> select();
            $this -> assign("type",$type);
            $this -> display();
        }
        
    }
    //属性列表
    public function attr_list(){
        $data = M('Attribute') -> field("t1.*,t2.type_name") -> alias('t1') -> join("left join tpshop_type as t2 on t1.type_id=t2.type_id") -> select();
        $this -> assign('data',$data);
        $this -> display();
    }
    //属性修改
    public function attr_edit(){
        $model = M('Attribute');
        if(IS_POST){
            $data = I('post.');
            $res = M('Attribute')-> save($data);
             if($res != false){
            $this -> success('修改成功',U("Admin/Attribute/attr_list"));
        }else{
              $this -> error('修改失败');
         }
        }else{
            $id = I('get.id');
            $data = $model -> find($id);
            $this -> assign('data',$data);
            //查询商品类型
            $type = M('Type') -> select();
            $this -> assign("type",$type);
            $this -> display();
        }
        
    }
    //属性删除
    public function attr_del(){
        $id = I('get.id');
        $res = M('Attribute') -> delete($id);
        if($res != false){
            $this -> success('删除成功',U("Admin/Attribute/attr_list"));
        }else{
              $this -> error('删除失败');
         }
    }
}