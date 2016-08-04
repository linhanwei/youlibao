<?php
namespace Mobile\Controller;
use Think\Controller;
class LoginController extends Controller {
    
    private $Wechat; //定义微信类变量
    
    public function __construct() {
        parent::__construct();
       
    }
    
    //用户登录页面
    public function index(){
//        dump(md5(123456)); //e10adc3949ba59abbe56e057f20f883e
        
        $agent_info = array(); //代理信息
        $is_base = I('is_base',1); //是否唔感知获取openid:  1:是,2:否
        $is_weixin = is_weixin();
        
        //唔感知获取openid
        /*
        if($is_weixin){
            
            $options['token'] = C('WX_TOKEN');
            $options['appid'] = C('WX_APPID');
            $options['secret'] = C('WX_APPSECRET');

            $this->Wechat = new \Org\Util\Wechat($options);
            
            if($is_base == 1){
                $base_info = $this->Wechat->getOauthAccessToken($callback = '', $state='', $scope='snsapi_base'); //snsapi_userinfo  snsapi_base

                //获取失败重新调用
                if(!$base_info){
                    $base_info = $this->Wechat->getOauthAccessToken($callback = '', $state='', $scope='snsapi_base'); //snsapi_userinfo  snsapi_base
                }
                
                $openid = $base_info['openid'];
               
                //根据openid查询代理信息
                $Agent = D('Agent');
                $where['openid'] = $openid;
                $agent_info = $Agent->where($where)->find();

                if($agent_info){

                    session('member_id',$agent_info['agentid']);
                    session('member_info',$agent_info);

                    redirect(C('WEB_URL').'/AgentManage/index.html'); //跳转到代理首页

                }
            }
            
            $wx_info = session('weixin_info');
            if(empty($wx_info)){  //如果获取微信用户信息失败重新获取
                $this->getWeixinInfo();
            }
           
        }
        */
        $this->display();
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
    
    //用户登录
    public function loginSub() {
        
        $return = array('status'=>0,'msg'=>'','result');
        $name = I('name');
        $password = I('password');
        $code = I('code');
        
//        if(!$this->check_verify($code)){
//            $return['msg'] = '验证码不正确,请重新输入';
//            $this->ajaxReturn($return,'json');
//        }
        
        $where['weixin'] = $name;
        $m_password = md5($password);
//        $where['password'] = $m_password;
//        $where['is_cancel'] = 0;
//        $where['is_validate'] = 1;
        
        $Agent = D('Agent');
        $result = $Agent->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
       
        if(!$result){
          
            $return['msg'] = '用户名不正确,请重新输入';
            $this->ajaxReturn($return,'json');
        }
        
        if($m_password != $result['password']){
         
            $return['msg'] = '密码不正确,请重新输入';
            $this->ajaxReturn($return,'json');
        }
        
        if($result['is_agent'] != 0 ){
//            if($result['star'] == 4){
//                $MEMBER_LEVEL = C('MEMBER_LEVEL');
//                $return['msg'] = $MEMBER_LEVEL[4]['name'].'暂时不支持登录,请在代理查询页面下载授权书,或者联系经销商升级处理!';
//                $this->ajaxReturn($return,'json');
//            }

            $time = time();
            $end_time = $result['endtime'];
            if($time > $end_time){
                $return['msg'] = '您的授权时间已经到期,请重新授权';
                $this->ajaxReturn($return,'json');
            }

            $stat = $result['stat'];
            if($stat != 1){
                switch ($stat) {
                    case 0:
                        $return['msg'] = '待总部审核';
                        break;
                    case -1:
                        $return['msg'] = '您已经被列入黑名单';
                        break;
                    case -2:
                        $return['msg'] = '等待审核';
                        break;
                    case -3:
                        $return['msg'] = '资料不正确,等待修改!';
                        break;

                }

                $this->ajaxReturn($return,'json');
            }
        }
        
        $member_id = $result['agentid'];
        
        //添加代理openid 与 头像
        $weixin_info = session('weixin_info');
        
        if($weixin_info){
            $headimgurl = $weixin_info['headimgurl'];
            $openid = $weixin_info['openid'];
            
            $result['head_img'] = $headimgurl;
            $result['openid'] = $openid;
            $edit_where['agentId'] = $member_id;
            $editData['openid'] = $openid;
            $editData['head_img'] = $headimgurl;
            $Agent->editData($edit_where,$editData);
        }
        
        session('member_id',$member_id);
        session('member_info',$result);
        
        $return = array('status'=>1,'msg'=>'','result');
        $this->ajaxReturn($return,'json');
    }
    
    //退出
    public function logout() {
        session('member_info', NULL);
        session('member_id',NULL);
        $this->redirect('index', '', 0, '页面跳转中...');
    }
    
    //用户注册页面
    public function reg() {
        $code = I('code');
        
        if(empty($code)){
            $this->error('请选择邀请人');
        }
        
        $Invitecode = D('Invitecode');
        $where['inviteCode'] = $code;
        $code_info = $Invitecode->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if(empty($code_info)){
            $this->error('该邀请码已经失效!');
        }
        
        $info['name'] = $code_info['name'];
        $info['line_number'] = $code_info['line_number'];
        $info['code'] = $code;
        $star = $code_info['star'];
        
        $lv_name_list = C('MEMBER_LEVEL');
        $info['lv_name'] = $lv_name_list[$star]['name'];
        
        $this->assign('info',$info);
        $this->display();
    }
    
    //提交注册
    public function regSub() {
        $return = array('status'=>0,'msg'=>'注册失败','result'=>'');
        $code = I('code');
        $line_number = I('line_number');
        $weixin = I('weixin');
        $name = I('name');
        $province = I('province');
        $city = I('city');
        $county = I('county');
        $address = I('address');
        $qq = I('qq');
        $tel = I('tel');
        $cardno = I('cardno');
        $password = I('password');
        $password2 = I('password2');
        
        if(empty($name)){
            $return['msg'] = '请输入姓名';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($weixin)){
            $return['msg'] = '请输入微信号码';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($province)){
            $return['msg'] = '请选择省份';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($city)){
            $return['msg'] = '请选择城市';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($county)){
            $return['msg'] = '请选择县区';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($address)){
            $return['msg'] = '请输入详细地址';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($qq)){
            $return['msg'] = '请输入QQ号码';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($tel)){
            $return['msg'] = '请输入手机号码';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($cardno)){
            $return['msg'] = '请输入身份证号码';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($password)){
            $return['msg'] = '请输入登录密码';
            $this->ajaxReturn($return,'json');
        }
        
        if($password != $password2){
            $return['msg'] = '登录密码与确认登录密码不一致';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($code)){
            $return['msg'] = '请选择邀请人';
            $this->ajaxReturn($return,'json');
        }
        
        $Invitecode = D('Invitecode');
        $where['inviteCode'] = $code;
        $code_info = $Invitecode->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if(empty($code_info)){
            $return['msg'] = '该邀请码已经失效!';
            $this->ajaxReturn($return,'json');
        }
        
        //添加代理信息
        $Agent = D('Agent');
        
        $is_where['weixin'] = $weixin;
        $is_count = $Agent->getCount($is_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($is_count > 0){
            $return['msg'] = '该微信号已经存在,请换一个微信号!';
            $this->ajaxReturn($return,'json');
        }
        
        $tel_where['tel'] = $tel;
        $is_count = $Agent->getCount($tel_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($is_count > 0){
            $return['msg'] = '该手机号已经存在,请换一个手机号!';
            $this->ajaxReturn($return,'json');
        }
        
        $cardNo_where['cardNo'] = $cardno;
        $is_count = $Agent->getCount($cardNo_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($is_count > 0){
            $return['msg'] = '身份证号码已经存在,请换一个!';
            $this->ajaxReturn($return,'json');
        }
        
        $Agent->startTrans(); //开启事务
        
        $parent_id = $code_info['agentid'];
        $star = $code_info['star'];
        $code_top1_id = $code_info['top1_id'];
        $code_top2_id = $code_info['top2_id'];
        $is_founder = $code_info['is_founder'];
        $team_name = $code_info['team_name'];
        $stat = 1;
        
        //授权号
        $agentNo = $Agent->makeAgentNo();
       
        $addData['star'] = $star;
        $addData['parent_id'] = $parent_id;
        $addData['name'] = $name;
        $addData['weixin'] = $weixin;
        $addData['password'] = md5($password);
        $addData['add_time'] = date('Y-m-d H:i:s');
        $addData['qq'] = $qq;
        $addData['agentNo'] = $agentNo;
        $addData['cardNo'] = $cardno;
        $addData['tel'] = $tel;
        $addData['stat'] = $stat;
        $addData['province'] = $province;
        $addData['city'] = $city;
        $addData['county'] = $county;
        $addData['address'] = $address;
        $addData['startime'] = time();
        $addData['endtime'] = strtotime("+1 year -1 day");
        $addData['team_name'] = $team_name;
        
        $member_id = $Agent->addData($addData);
        
        //添加代理关系
        $AgentRelation = D('AgentRelation');
        $agent_where['member_id'] = $parent_id;
        $agent_info = $AgentRelation->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        $top1_id = $star == 1 ? $code_top1_id : $agent_info['top1_id'];
        $top2_id = $star == 1 ? $code_top2_id : $agent_info['top2_id'];
        $agent1_id = $agent_info['agent1_id'] ? $agent_info['agent1_id'] : ($star == 2 ? $parent_id : 0);
        $agent2_id = $agent_info['agent2_id'] ? $agent_info['agent2_id'] : ($star == 3 ? $parent_id : 0);
        $agent3_id = $agent_info['agent3_id'] ? $agent_info['agent3_id'] : ($star == 4 ? $parent_id : 0);
        $agent4_id = $agent_info['agent4_id'] ? $agent_info['agent4_id'] : 0;
        $line_number = $line_number ? $line_number : $agent_info['line_number'];
        
        $arData['member_id'] = $member_id;
        $arData['top1_id'] = $top1_id;
        $arData['top2_id'] = $top2_id;
        $arData['agent1_id'] = $agent1_id;
        $arData['agent2_id'] = $agent2_id;
        $arData['agent3_id'] = $agent3_id;
        $arData['agent4_id'] = $agent4_id;
        $arData['line_number'] = $line_number;
        $arData['pid'] = $parent_id;
        $arData['agent_grade'] = $star;
        $arData['is_cancel'] = 0;
        $arData['is_agent'] = 1;
        $arData['is_validate'] = $stat;
        $arData['is_founder'] = $is_founder;
        
        
        $result = $AgentRelation->addData($arData);
        
        if($member_id && $result){
            $return = array('status'=>1,'msg'=>'注册成功','result'=>'');
            $Invitecode = D('Invitecode');
            $del_where['inviteCode'] = $code;
            $Invitecode->delData($del_where);
            $Agent->commit();//提交事务
        }else{
            $Agent->rollback(); //事务回滚
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //验证邀请码是否存在
    public function isExit($model) {
        $code = $this->randomkeys(8);
        $where['InvitationCode'] = $code;
        $count = $model->where($where)->count();
        
        if($count > 0){
            $this->isExit($model);
        }
        
        return $code;
    }
    
    //忘记密码页面
    public function forgetPasswordView() {
        $this->display('forgetPasswordView');
    }
    
    //修改忘记密码
    public function forgetPassword() {
        $return = array('status'=>0,'msg'=>'重置密码失败!');
        $weixin = I('weixin');
        
        if(empty($weixin)){
            $return['msg'] = '请输入微信号!';
            $this->ajaxReturn($return, 'json');
        }
        
        $Agent = D('Agent');
        $where['weixin'] = $weixin;
        $info = $Agent->where($where)->find();
        
        if(empty($info)){
            $return['msg'] = '该微信号不存在,请重新输入!';
            $this->ajaxReturn($return, 'json');
        }
        
        $cardNo = $info['cardno'];
        
        if(empty($cardNo)){
            $return['msg'] = '该微信号没有填写身份证号码,密码不能重置!';
            $this->ajaxReturn($return, 'json');
        }
        
        $password = trim(substr($cardNo, -6));
        
        $edit_where['agentId'] = $info['agentid'];
        $editData['password'] = md5($password);
        
        if($editData['password'] == $info['password']){
            $return = array('status'=>1,'msg'=>'重置密码成功,密码为身份证号码的后6位数字!');
            $this->ajaxReturn($return, 'json');
        }
       
        $result = $Agent->editData($edit_where,$editData);
        
        if($result){
            $return = array('status'=>1,'msg'=>'重置密码成功,密码为身份证号码的后6位数字!');
        }
        $this->ajaxReturn($return, 'json');
    }
    
    /*
     * 随机生成邀请码
     * $length 获取长度
     */
    public function randomkeys($length){
        $pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,35)};    
        }
        return strtoupper($key);
    }
    
    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    function check_verify($code, $id = ''){    
        $verify = new \Think\Verify();    
        return $verify->check($code, $id);
        
    }
    
    //生成验证码
    public function entry() {
        $config =    array(    
            'fontSize'    =>    30,    // 验证码字体大小    
            'length'      =>    4,     // 验证码位数    
            'useNoise'    =>    false, // 关闭验证码杂点
        );
        
        $Verify =     new \Think\Verify($config);
        $Verify->entry();
    }
}