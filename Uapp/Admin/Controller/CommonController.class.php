<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
    
    protected  $member_id = 0;

    public function __construct() {
        parent::__construct();
        
        $this->member_id = session('member_id');
        if(!$this->member_id){
            $this->redirect('Login/index', '', 0, '页面跳转中...');
        }
        
        $member_info = $this->memberInfo();
        $this->assign('member_info',$member_info);
//        
//        $member_level = C('MEMBER_LEVEL');
//        $level_name = $member_level[$member_info['lv']];
//        
//        $this->assign('log_m_level',$level_name);
      
    }
    
    //获取会员信息
    public function memberInfo($is_update = false) {
        
        $member_info = session('member_info');
        
        if(!$member_info || $is_update){
            $Member = D('User');
            $member_info = $Member->getDetail(array('userId'=>  $this->member_id),array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            
            //没有邀请码,自动生成一个
            if(!$member_info['invitationcode']){
//                $code = $this->isExit($Member);
//                $member_info['invitationcode'] = $code;
            }
            session('member_info',$member_info);
        }
        
        return $member_info;
    }
    
    //获取代理信息
    public function getAgent($aid) {
        $info = S(C('AGENT_INFO').$aid);
        
        if(empty($info)){
            $Agent = D('Agent');
            $where['agentId'] = $aid;
            $info = $Agent->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
            if($info){
                S(C('AGENT_INFO').$aid,  serialize($info));
            }
        }else{
            $info = unserialize($info);
        }
       
        return $info;
       
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

}