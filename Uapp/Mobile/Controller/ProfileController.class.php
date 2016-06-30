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
        
        //代理信息 开始
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        $agent_grade = $member_info['agent_grade'];
        
        //获取团队名称
        if(!$member_info['team_name']){
            $agent1_info = $this->getAgent($member_info['agent1_id']);
            $member_info['team_name'] = $agent1_info['team_name'];
        }

        //上级名称
        $MEMBER_LEVEL = C('MEMBER_LEVEL');
        $parent_info = $this->getAgent($member_info['pid']);
        $member_info['parent_name'] = $parent_info ? $parent_info['name'] : $MEMBER_LEVEL[0]['name'];
        
        //下一个等级的名称
        $member_info['next_lv_name'] = $MEMBER_LEVEL[$agent_grade+1]['name'];
       
        $this->assign('member_info', $member_info);
        
        //代理信息 结束
        
        $this->display();
    }
    
    //修改密码页面
    public function editPasswordView() {
        
        
        $this->display('editPasswordView');
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
    
    
    //直接推荐
    public function directRecommon() {
        
        $data = $this->recommonList(1);
        
        $this->assign('list',$data['list']);
                
        $this->display('directRecommon');
    }
    
    //间接推荐
    public function indirectRecommon() {
        
        $data = $this->recommonList(2);
        
        $this->assign('list',$data['list']);
        
        $this->display('indirectRecommon');
    }
    
    //官方推荐列表
    public function recommonList($type) {
        
        if(!in_array($type, array(1,2))){
            return FALSE;
        }
        
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        $this->assign('member_info',$member_info);
        
        $MEMBER_LEVEL = C('MEMBER_LEVEL');
        $this->assign('MEMBER_LEVEL', $MEMBER_LEVEL);
        
        $AgentRelation = D('AgentRelation');
        
        $join=' ar LEFT JOIN agent a ON ar.member_id=a.agentId';
        $where['ar.top'.$type.'_id'] = $member_id;
        $where['ar.agent_grade'] = 1;
        
        $return['list'] = $AgentRelation->getAllList($where,'ar.add_time DESC',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);
        
        return $return;
    }
    
}