<?php
namespace Admin\Controller;
use Think\Controller;
class ManagerController extends CommonController
{
    /**
     * list
     */
    public function manager_list()
    {
        //查询用户等级
        $datas = M('Role')->select();
        //    dump($datas);die;
        $this->assign('data',$datas);
        if (IS_POST) {
            $data = I('post.');
            //管理员列表查询
            $username = $data['username'];
            $role_id = $data['role_id'];
            if(!empty($username) || $role_id != ''){
                 $res = M('Manager')
                ->where("username = '$username' 
                    OR role_id= '$role_id' ")
                        ->select();
                $this->assign('res',$res);
                $this->display();
            }
            if($role_id == "") {
                $res = M('Manager')->select();
                $this->assign('res',$res);
                $this->display();
            }
           
            
        } else {
            $model = D('Manager');
            //查询总记录数
            $total = $model->count();
            //显示条数
            $pagesize = 10;
            //分页
            $page = new \Think\Page($total, $pagesize);
            //自定义属性和配置
            $page->rollPage = 6;
            $page->lastSuffix = false;
            $page->setConfig('prev', '上一页');
            $page->setConfig('next', '下一页');
            $page->setConfig('first', '首页');
            $page->setConfig('last', '尾页');
            $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            //获取分页栏的html代码
            $page_html = $page->show();
            $this->assign('page_html', $page_html);
            //查询每页显示的数据
            $res = $model->limit($page->firstRow, $page->listRows)->select();
            $this->assign('res', $res);
            $this->display();
        }

    }

    /**
     * ADD
     */
    public function manager_add()
    {
        if (IS_POST) {
            $model = D('Manager');
            $data = I('post.');
//            dump($data);die;
            $data['password'] = md5($data['password']);
            $model->create($data);
            $res = $model->add();
            if ($res) {

                $this->success('添加成功', U('Admin/Manager/manager_list'));
            } else {
                $this->error('添加失败');
            }

        } else {
            $data = M('Role')->where("role_id  >1")->select();
            $this->assign('data', $data);
            $this->display();
        }

    }

    /**
     * Edit
     */
    public function manager_edit()
    {
        $model = D('Manager');
        if (IS_POST) {
            $data = I('post.');
            $data['password'] = md5($data['password']);
            // \dump($data);die;
            $res = $model->save($data);
            if ($res != false) {
                $this->success('修改成功', U('Admin/Manager/manager_list'));
            } else {
                $this->error('修改失败，请重试');
            }
        } else {
            $id = I('get.id');
            $data = $model->find($id);
            $this->assign('data', $data);
            $da = M('Role')->where("role_id  >1")->select();
            $this->assign('da', $da);
            $this->display();
        }

    }

    /**
     * 删除
     */
    public function manager_delete()
    {
        $model = D('Manager');
        $id = I('get.id');
        $res = $model->delete($id);
        if ($res !== false) {
            //删除成功
            $this->success('删除成功', U('Admin/Manager/manager_list'));
        } else {
            $this->error('添加失败,请重试');
        }
    }

}
