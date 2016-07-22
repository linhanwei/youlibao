<?php
/*
 * 个人资料
 */
namespace Mobile\Controller;
use Think\Controller;
class ProfileController extends CommonController {
    
    private $Wechat; //定义微信类变量
    
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
        
        $data = $this->recommonList(2);
        
        $this->assign('list',$data['list']);
                
        $this->display('directRecommon');
    }
    
    //间接推荐
    public function indirectRecommon() {
        
        $data = $this->recommonList(1);
        
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
    
    //微信绑定页面
    public function weixinBingView() {
        
        $is_base = I('is_base',1); //是否唔感知获取openid:  1:是,2:否
        $is_weixin = is_weixin();
        
        if($is_weixin){
            
            $options['token'] = C('WX_TOKEN');
            $options['appid'] = C('WX_APPID');
            $options['secret'] = C('WX_APPSECRET');

            $this->Wechat = new \Org\Util\Wechat($options);
            
            //获取微信信息
            $this->getWeixinInfo();
           
        }
        
        $this->display('weixinBingView');
    }
    
    //获取微信用户信息
    public function getWeixinInfo() {
        $url = C('WEB_URL').__SELF__;
        $url_result = strrpos($url,'?');
        if($url_result){
            $url .= '&is_base=2';
        }else{
            $url .= '?is_base=2';
        }
        
        $base_info = $this->Wechat->getOauthAccessToken($url,'','snsapi_userinfo'); //snsapi_userinfo  snsapi_base
      
        if($base_info){
            $wx_info = $this->Wechat->getOauthUserInfo($base_info['access_token'],$base_info['openid']);
            if($wx_info){
                session(array('name'=>'session_id','expire'=>7100));
                session('weixin_info', $wx_info);
            }
        }else{ //获取OauthAccessToken失败,重新获取
            $this->Wechat->getOAuthRedirect($url,'','snsapi_userinfo');
        }
       
    }
    
    
    //微信绑定
    public function weixinBing() {
        $return = array('status'=>0,'msg'=>'绑定失败','result'=>'');
        
        $password = I('password');
        
        if(empty($password)){
            $return['msg'] = '请输入密码!';
            $this->ajaxReturn($return,'json');
        }
        
        $agent_id = session('member_id');
        
        if(empty($agent_id)){
            $return['msg'] = '您还没登录,请重新登录!';
            $this->ajaxReturn($return,'json');
        }
        
        //微信信息
        $wx_info = session('weixin_info');
        
        if(empty($wx_info)){
            $return['msg'] = '微信信息没有获取成功!';
            $this->ajaxReturn($return,'json');
        }
        
        $headimgurl = $wx_info['headimgurl'];
        $openid = $wx_info['openid'];
        
        $Agent = D('Agent');
        
        $where['agentId'] = $agent_id;
        
        $agent_info = $Agent->where($where)->find();
        
        $password = md5($password);
        $agent_password = $agent_info['password'];
        
        if($password != $agent_password){
            $return['msg'] = '输入密码不正确,请重新输入!';
            $this->ajaxReturn($return,'json');
        }
        
        if($agent_info['openid']){
            $return['msg'] = '您已经绑定微信,不能再绑定!';
            $this->ajaxReturn($return,'json');
        }
        
        $editData['openid'] = $openid;
        $editData['head_img'] = $headimgurl;
        $result = $Agent->editData($where,$editData);
        
        if($result){
            $return = array('status'=>1,'msg'=>'绑定成功','result'=>'');
        }
        
        $this->ajaxReturn($return,'json');
    }
}