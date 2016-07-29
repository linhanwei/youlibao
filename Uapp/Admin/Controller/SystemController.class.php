<?php
namespace Admin\Controller;
use Think\Controller;
class SystemController extends CommonController {
    
    //管理员列表
    public function index(){
        $UserCom = D('UserCom');
        $post = I('post.');
        $account = $post['account'];
        $companyName = $post['companyName'];
        
        $page = I('p');
        $limit = 15;
       
        $where = array();
        
        if($account){
            $where['userName'] = $account;
        }
        if($companyName){
            $where['companyName'] = $companyName;
        }
        
        $count      = $UserCom->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数
      
        $show       = $Page->show();// 分页显示输出
     
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $UserCom->getList($where,$limit,$page,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        $this->assign('post',$post);
        $this->assign('list',$list);  //赋值数据集
        $this->assign('page',$show);// 赋值分页输出
       
        $this->display();
    }
    
    //添加或修改管理员页面
    public function editUserView() {
        $id = I('id');
        
        if($id){
            $UserCom = D('UserCom');
            
            $info = $UserCom->getDetail(array('userId'=>$id));
            $this->assign('info', $info);
            $action_name = '修改管理员';
        }else{
            $action_name = '添加管理员';
        }
        
        $this->assign('action_name', $action_name);
        $this->display('editUserView');
    }
    
    //添加或修改管理员页面
    public function editUser() {
        $return = array('status'=>0,'msg'=>'','result'=>'');
        
        $id = I('id');
        $companyName = I('companyName');
        $userName = I('userName');
        $password = I('password');
        $confirm_password = I('confirm_password');
        
        if(empty($companyName)){
            $return['msg'] = '管理员姓名不能为空!';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($userName)){
            $return['msg'] = '登录账号不能为空!';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($id) || $password){
            if(empty($password)){
                $return['msg'] = '登录密码不能为空!';
                $this->ajaxReturn($return,'json');
            }
            
            if($password != $confirm_password){
                $return['msg'] = '密码与确认密码不一致!';
                $this->ajaxReturn($return,'json');
            }
            
            $editData['password'] = md5($password);
        }
        
        $editData['companyName'] = $companyName;
        $editData['userName'] = $userName;
        
        $UserCom = D('UserCom');
            
        if($id){
           
            $result = $UserCom->editData(array('userId'=>$id),$editData);
           if($result){
               $return['status'] = 1;
               $return['msg'] = '修改成功';
           }else{
               $return['msg'] = '修改失败';
           }
            
        }else{
            $result = $UserCom->addData($editData);
            if($result){
                $return['status'] = 1;
                $return['msg'] = '添加成功';
           }else{
                $return['msg'] = '添加失败';
           }
         
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //系统设置页面
    public function systemSetView() {
        
        $this->display('systemSetView');
    }
    
    //系统设置页面
    public function systemSet() {
        
        
    }
    
}