<?php
namespace Admin\Controller;
class AuthController extends CommonController{
    public function auth_list(){
        $auth = M('Auth') -> select();
        $auth = getTree($auth);
        $this -> assign('auth',$auth);
        $this -> display();
    }
    //添加权限
    public function auth_add(){
        if(IS_POST){
            $data = I('post.');
            $model = M('Auth');
            $res = $model -> add($data);
            if($res){
                $this -> success('添加成功',U('Admin/Auth/auth_list'));
            }else {
                $this -> error('添加失败');
            }
        }else {
              //获取所有顶级分类
        $top = M('Auth') -> where("pid = 0") -> select();
        $this -> assign("top",$top);
        $this -> display();
        }
      
    }
    //编辑权限
    public function auth_edit(){
        if(IS_POST){
            $data = I('post.');
            $model = M('Auth');
            $res = $model -> save($data);
            if($res !== false){
                $this -> success('修改成功',U('Admin/Auth/auth_list'));
            }else {
                $this -> error('修改失败');
            }
        }else{
             $id = I('get.id');
            $data = M('Auth') -> find($id);
            $top = M('Auth') -> where("pid = 0") -> select();
             $this -> assign('data',$data);
            $this -> assign("top",$top);
            $this -> display();
        }
       
    }
    //删除权限
    public function auth_delete(){
        $id = I('get.id');
        $res = M('Auth') -> delete($id);
        if($res !== false){
                $this -> success('删除成功',U('Admin/Auth/auth_list'));
            }else {
                $this -> error('删除失败');
            }
    }
}