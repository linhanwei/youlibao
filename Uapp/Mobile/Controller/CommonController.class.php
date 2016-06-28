<?php
/*
 * 登录管理
 */
namespace Mobile\Controller;
use Think\Controller;
class CommonController extends Controller {
    
    protected  $member_id = 0;

    public function __construct() {
        parent::__construct();
        
        //微信浏览器中才去调用
        $is_weixin = is_weixin();
        
        if(!APP_DEBUG && !$is_weixin){
           echo '<h2>请在微信中打开!</h2>';
           die;
        }
        
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
            $Member = D('Agent');
            $member_info = $Member->getDetail(array('agentId'=>  $this->member_id),array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            
            //没有邀请码,自动生成一个
            if(!$member_info['invitationcode']){
//                $code = $this->isExit($Member);
//                $member_info['invitationcode'] = $code;
            }
            session('member_info',$member_info);
        }
        
        return $member_info;
    }
    
    //判断会员是否能够够权限使用功能
    public function authStatus() {
        $return = array('status'=>0,'msg'=>'');
        $result = $this->memberInfo();
        $time = time();
        $end_time = $result['endtime'];
        if($time > $end_time){
            $return['msg'] = '您的授权时间已经到期,请重新授权';
            return $return;
        }
        
        $stat = $result['stat'];
        if($stat != 1){
            switch ($stat) {
                case 0:
                    $return['msg'] = '待总部审核';
                    break;
                case -1:
                    $return['msg'] = '您已经别列入黑名单';
                    break;
                case -2:
                    $return['msg'] = '等待审核';
                    break;
                case -3:
                    $return['msg'] = '资料不正确,等待修改!';
                    break;

            }
            return $return;
        }
        
        $return = array('status'=>1,'msg'=>'验证通过');
        return $return;
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
    
    /**
     * 获取最后更新的激励语
     */
    public function getEncourage() {
        
        $is_select = 1;
        $cacheKey = md5(C('ENCOURAGE_INFO').$is_select);
        
        $info = S($cacheKey);
        
        if(!$info){
            $Encourage = D('Encourage');
            $info = $Encourage->getDetail(array('is_select'=>$is_select));
            $info['show_time'] = date('Y-m-d',$info['edit_time']);
            S($cacheKey,  serialize($info)); //缓存半个小时
        }else{
            $info = unserialize($info);
        }
       
        return $info;
    }

}