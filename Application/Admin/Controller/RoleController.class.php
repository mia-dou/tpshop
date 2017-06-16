<?php
namespace Admin\Controller;
class RoleController extends CommonController{
    public function role_list(){
         //查询总记录数
        $model = M('Role');
        $total=$model->count();
        //显示条数
        $pagesize = 5;
        //分页
        $page = new \Think\Page($total-1,$pagesize);
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
        $roles = $model->where("role_id > 1")->limit($page->firstRow,$page->listRows)->select();
        $this -> assign('roles',$roles);
        $this -> display();
    }
    public function setauth(){
        // $model = M('Role');
        if(IS_POST){
            $data = I('post.');
            $data['role_auth_ids'] = implode(',',$data['id']);
            $auth = M('Auth') -> where("id in ({$data['role_auth_ids']})") -> select();
            $role_auth_ac = "";
            foreach($auth as $k => $v){
                if($v['auth_c'] && $v['auth_a']){
                    $role_auth_ac .= $v['auth_c'] . '-' . $v['auth_a'] . ',';
                }
            }
            //去除后面的英文逗号
            $role_auth_ac = trim($role_auth_ac,',');
            $data['role_auth_ac'] = $role_auth_ac;
            $res = M('Role') -> save($data);
            if($res !== false){
                $this -> success('保存成功',U("Admin/Role/setauth/id/{$data['role_id']}"));
            }else{
                $this -> error('保存失败');
            }
        }else{
            $role_id = I('get.id');
            $role = M('Role')->find($role_id);
            // dump($role);die;
            //获取全部权限
            $top = M('Auth') -> where('pid = 0') -> select();
            $second = M('Auth') -> where("pid > 0") ->select();
            //已有权限
            // $role_auth_ids = $role['role_auth_ids'];
            // dump($top);die;
            $this -> assign('top',$top);
            $this -> assign('second',$second);
            $this -> assign('role_auth_ids',$role_auth_ids);
            $this -> assign('role_id', $role_id);
            $this -> assign('role', $role);            
            $this -> display();
        }
        
    }
    //删除角色
    public function role_delete(){
        $id = I('get.id');
        $res = M('Role') -> delete($id);
        if($res !== false){
                $this -> success('删除成功',U('Admin/Role/role_list'));
            }else {
                $this -> error('删除失败');
            }
    }
    //添加角色
    public function add(){
        if(IS_POST){
            $data = I('post.');
            $data['role_auth_ids'] = \implode(',',$data['id']);
            $auth = M('Auth') -> where("id in ({$data['role_auth_ids']})") -> select();
            $role_auth_ac = "";
            foreach($auth as $k => $v){
                if($v['auth_c'] && $v['auth_a']){
                    $role_auth_ac .= $v['auth_c'] . '-' . $v['auth_a'] . ',';
                }
            }
            $role_auth_ac = trim($role_auth_ac,'.');
            $data['role_auth_ac'] = $role_auth_ac;
            $res = M('Role') -> add($data);
            if($res){
                $this -> success('添加成功',U("Admin/Role/role_list"));
            }else{
                $this -> error('添加失败');
            }
        }else{
            $top = M('Auth') -> where('pid = 0') -> select();
            $this -> assign('top',$top);
            $second = M('Auth') -> where("pid > 0") ->select();
            $this -> assign('second',$second);
            $this -> display();
        }
    }
}