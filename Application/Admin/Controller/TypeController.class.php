<?php
namespace Admin\Controller;
// use Think\Controller;
class TypeController extends CommonController{
    public function type_add(){
        if(IS_POST){
            $data = I('post.');
            $model = M('Type');
            $res = $model -> add($data);
            if($res){
                $this -> success('添加成功',U('Admin/Type/type_list'));
            }else {
                $this -> error('添加失败，请重试');
            }
        }else{
            $this ->display();
        }  
    }

    //类型列表
    public function type_list(){
        $model = M('Type');
        $data = $model -> select();
        $this -> assign('data',$data);
        $this -> display();
    }

    //类型修改
    public function type_edit(){
       if(IS_POST){
            $data = I('post.');
            $res = M('Type') -> save($data);
            if($res !== false){
                $this -> success('修改成功',U("Admin/Type/type_list"));
            }else {
                $this -> error('修改失败');
            }
       } else {
           $id = I('get.id');
           $data = M('Type') -> find($id);
           $this -> assign('data',$data);
           $this -> display();
       }
    }

    //删除类型
    public function type_delete(){
        $id = I('get.id');
        $res = M("Type") -> delete($id);
        if($res !== false){
            $this -> success('删除成功',U('Admin/Type/type_list'));
        }else{
            $this -> error('删除失败');
        }
    }
}