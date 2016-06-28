<?php
/*
 * 个人资料
 */
namespace Mobile\Controller;
use Think\Controller;
class ProfileController extends CommonController {
    public function __construct() {
        parent::__construct();
        
        $action_name = 'agentManage';
        $this->assign('action_name',$action_name);
    }
    
    //首页
    public function index(){
        $member_info = $this->memberInfo();
     
        $this->assign('member_info',$member_info);
        $this->display();
    }
    
    //修改密码
    public function editPassword() {
        $return = array('status'=>0,'msg'=>'修改失败','result'=>'');
        $agent_id = $this->member_id;
        $password = I('password');
        $confir_password = I('confir_password');
        
        if(empty($password) || empty($confir_password)){
            $return['msg'] = '密码与确认密码不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if($password != $confir_password){
            $return['msg'] = '密码与确认密码不一致';
            $this->ajaxReturn($return,'json');
        }
        
        $Agent = D('Agent');
        $where['agentId'] = $agent_id;
        $editData['password'] = md5($password);
        
        $result = $Agent->editData($where,$editData);
        
        if($result){
            $return = array('status'=>1,'msg'=>'修改成功','result'=>'');
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    
    
    
}