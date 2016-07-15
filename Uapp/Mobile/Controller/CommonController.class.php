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
        $linBug = I('linBug');
        
        if(!$linBug && !$is_weixin){
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
            $info['show_time'] = date('Y-m-d',  strtotime($info['edit_time']));
            S($cacheKey,  serialize($info)); //缓存半个小时
        }else{
            $info = unserialize($info);
        }
       
        return $info;
    }
    
    
    //获取代理商
    public function getAgent($agent_id) {
        if(empty($agent_id)){
            return FALSE; 
        }
        
        $agent_info = S(C('AGENT_INFO').$agent_id);
        
        if(empty($agent_info)){
            $Agent = D('Agent');
            $agent_where['agentId'] = $agent_id;
            $agent_info = $Agent->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            if($agent_info){
                S(C('AGENT_INFO').$agent_id,  serialize($agent_info),2*60);
            }
        }else{
            $agent_info = unserialize($agent_info);
        }
        
        return $agent_info;
    }
    
    
    //获取商品
    public function getGoods($goods_id) {
        if(empty($goods_id)){
            return false;
        }
        
        $cacheKey = md5(C('GOODS_INFO').$goods_id);
        $goods_info = S($cacheKey);
        
        if(empty($goods_info)){
            $where['id'] = $goods_id;
            $Goods = D('Goods');
            $goods_info = $Goods->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            if($goods_info){
                S($cacheKey,  serialize($goods_info));
            }
        }else{
            $goods_info = unserialize($goods_info);
        }
        
        return $goods_info;
    }
    
    //获取代理等级商品价格与利润
    public function getAgentGoodsProfit($goods_id,$agent_lv) {
        
        if(empty($goods_id)){
            return false;
        }
        
        if(empty($agent_lv)){
            return false;
        }
        
        $cacheKey = md5(C('AGENT_GOODS_PROFIT').$goods_id.'_'.$agent_lv);
        $goods_profit = S($cacheKey);
        
        if(empty($goods_profit)){
            $where['goods_id'] = $goods_id;
            $where['agent_lv'] = $agent_lv;
            $AgentGoodsProfitRale = D('AgentGoodsProfitRale');
            $goods_profit = $AgentGoodsProfitRale->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
           
            if($goods_profit){
                S($cacheKey,  serialize($goods_profit));
            }
        }else{
            $goods_profit = unserialize($goods_profit);
        }
        
        return $goods_profit;
    }
    

}