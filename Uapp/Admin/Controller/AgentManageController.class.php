<?php
/*
 * 代理商管理
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class AgentManageController extends CommonController {
    
    //代理商列表
    public function index() {
      
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        $search_value = I('search_value');
        $search_field = I('search_field');
        $parent_id = I('pid');
        $star = I('star');
        $page = I('p',1);
        $status = I('status');
      
        if($search_field && $search_value){
            $where[$search_field] = $search_value;
        }
        
        $parent_id ? $where['parent_id'] = $parent_id : '';
        $star ? $where['star'] = $star : '';
        $status ? (is_numeric($status) ? $where['stat'] = $status : $where['stat'] = array('in',$status)) : '';
        
        $limit=10;
        
        $Agent = D('Agent');
        
        $count      = $Agent->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $Agent->getList($where,$limit,$page,'star',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($list){
            foreach ($list as $k => $v) {
                $list[$k]['lv_name'] = $agent_lv_list[$v['star']]['name'];
                $list[$k]['agent_count'] = $Agent->getCount(array('parent_id'=>$v['agentid']),array('key'=>false,'expire'=>null,'cache_type'=>null));
                $list[$k]['date_end_time'] = date('Y-m-d',$v['endtime']);
                $agent_info = $this->getAgent($v['parent_id']);
                $list[$k]['parent_name'] = $agent_info['name'];
            }
        }
        
        //搜索条件
        $search['search_field'] = $search_field;
        $search['search_value'] = $search_value;
        $search['parent_id'] = $parent_id;
        $search['star'] = $star;
        $search['stat'] = $status;
        $this->assign('search',$search);
       
        $this->assign('list_count',$count);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display();
    }
    
    //下载证书
    public function downAuthBook() {
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            echo '<p>请选择代理</p>';
            die;
        }
        
        $agent_info = $this->getAgent($agent_id);
        
        //生成授权证书
        $auth_img = make_agent_auth($agent_info);
        
        $filename = './Public/'.$auth_img;
        
        $DownLoad = new \Org\Util\DownLoad('php,exe,html',false);  
        if(!$DownLoad->downloadfile($filename)){  
            echo '<p>'.$DownLoad->geterrormsg().'</p>';
        } 
        
        die;  
    }
    
    //查看证书
    public function showAuthBook() {
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            echo '<p>请选择代理</p>';
            die;
        }
        
        $agent_info = $this->getAgent($agent_id);
        
        //生成授权证书
        $auth_img = make_agent_auth($agent_info);
        $this->assign('auth_img',$auth_img);
        
        $this->display('showAuthBook');
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
    
    //修改代理商页面
    public function editAgentView() {
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            dump('请选择代理商');
            die;
        }
        
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        $info = $this->getAgent($agent_id);
      
        $this->assign('info',$info);
        $this->display('editAgentView');
    }
    
   //修改代理商
    public function editAgent() {
        $return = array('status'=>0,'msg'=>'更改代理失败','result'=>'');
        $post = I('post.');
        $p_weixin =$post['p_weixin'];
        $agent_id = $post['aid'];
        $name = $post['name'];
        $weixin = $post['weixin'];
        $agentno = $post['agentno'];
        $tel = $post['tel'];
        $cardno = $post['cardno'];
        $star = $post['star'];
        $startime = $post['startime'];
        $endtime = $post['endtime'];
        $qq = $post['qq'];
        $province = $post['province'];
        $city = $post['city'];
        $county = $post['county'];
        $address = $post['address'];
        $stat = $post['stat'];
        $password = $post['password'];
        $password2 = $post['password2'];
        $y_parent_id = $post['y_parent_id'];
        
        if($password && $password != $password2){
            $return['msg'] = '修改密码与确认密码不一致!';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }

        if(empty($name)){
            $return['msg'] = '请输入姓名';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agentno)){
            $return['msg'] = '授权号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($tel)){
            $return['msg'] = '手机号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($cardno)){
            $return['msg'] = '身份证号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($weixin)){
            $return['msg'] = '微信号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($star)){
            $return['msg'] = '代理星别不能为空';
            $this->ajaxReturn($return,'json');
        }

        //添加代理信息
        $Agent = D('Agent');
        
        if($p_weixin && $p_weixin != 1){
            $p_where['weixin'] = $p_weixin;
            $pid = $Agent->where($p_where)->getField('agentId');
            if(empty($pid)){
                $return['msg'] = '上级微信号不存在,请换一个微信号!';
                $this->ajaxReturn($return,'json');
            }
        }
        
        if($p_weixin == 1){
            $pid = 1;
        }
       
        $Agent->startTrans(); //开启事务
        $time = time();
        $parent_id = $pid;
        
        $addData['star'] = $star;
        $parent_id ? $addData['parent_id'] = $parent_id : '';
        $addData['name'] = $name;
        $addData['weixin'] = $weixin;
        $addData['add_time'] = $time;
        $addData['qq'] = $qq;
        $addData['agentNo'] = $agentno;
        $addData['cardNo'] = $cardno;
        $addData['tel'] = $tel;
        $addData['stat'] = $stat;
        $addData['province'] = $province;
        $addData['city'] = $city;
        $addData['county'] = $county;
        $addData['address'] = $address;
        $addData['startime'] = strtotime($startime);
        $addData['endtime'] = strtotime($endtime);
        $password ? $addData['password'] = md5($password) : '';
        
        $ag_result = $Agent->editData(array('agentId'=>$agent_id),$addData);
       
        //添加代理关系
        $AgentRelation = D('AgentRelation');
        
        if($parent_id){
            
            //更改下级的上两级
            $next_agent_where['pid'] = $agent_id;
            $next_agent_where['_string'] = ' agent1_id = "'.$y_parent_id.'" OR agent2_id = "'.$y_parent_id.'" OR agent3_id = "'.$y_parent_id.'" ';
            $nextData['agent1_id'] = $parent_id;
            $nextData['agent2_id'] = $parent_id;
            $nextData['agent3_id'] = $parent_id;
            $next_result = $AgentRelation->editData($next_agent_where,$nextData);
            
            $arData['pid'] = $parent_id;

        }
        
        //更改自己的信息
        $arData['agent_grade'] = $star;
        $arData['is_cancel'] = 0;
        $arData['is_agent'] = 1;
        $arData['is_validate'] = $stat;
        
        $result = $AgentRelation->editData(array('member_id'=>$agent_id),$arData);
        
        if($ag_result || $result){
            $return = array('status'=>1,'msg'=>'修改成功','result'=>'');
            $Agent->commit();//提交事务
            S('AGENT_INFO'.$agent_id,NULL);
        }else{
            $Agent->rollback(); //事务回滚
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //添加代理商页面
    public function addAgentView() {
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        //授权号
        $Agent = D('Agent');
        $agentno = $Agent->makeAgentNo();
        
        $info['agentno'] = $agentno;
        $info['startime'] = date('Y-m-d H:i');
        $info['endtime'] = date('Y-m-d H:i',strtotime('+1 year -1 day'));
        $this->assign('info',$info);
       
        $this->display('addAgentView');
    }
    
    //添加代理商
    public function addAgent() {
        $return = array('status'=>0,'msg'=>'添加代理失败','result'=>'');
        $post = I('post.');
        $team_name = $post['team_name'];
        $name = $post['name'];
        $weixin = $post['weixin'];
        $agentno = $post['agentno'];
        $tel = $post['tel'];
        $cardno = $post['cardno'];
        $star = $post['star'];
        $startime = $post['startime'];
        $endtime = $post['endtime'];
        $qq = $post['qq'];
        $province = $post['province'];
        $city = $post['city'];
        $county = $post['county'];
        $address = $post['address'];
        $stat = $post['stat'];
        $password = $post['password'];
        $password2 = $post['password2'];
//        
//        if(empty($team_name)){
//            $return['msg'] = '请输入团队名称!';
//            $this->ajaxReturn($return,'json');
//        }
//        
        if(empty($name)){
            $return['msg'] = '请输入姓名';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agentno)){
            $return['msg'] = '授权号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($tel)){
            $return['msg'] = '手机号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($cardno)){
            $return['msg'] = '身份证号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($weixin)){
            $return['msg'] = '微信号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(!is_numeric($star)){
            $return['msg'] = '代理星别不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if($star > 1){
            $return['msg'] = '您只能添加省级代理';
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
        
        $Agent->startTrans(); //开启事务
        $time = time();
        $parent_id = 1;
        
        $addData['star'] = $star;
        $addData['parent_id'] = $parent_id;
        $addData['name'] = $name;
        $addData['weixin'] = $weixin;
        $addData['add_time'] = $time;
        $addData['qq'] = $qq;
        $addData['agentNo'] = $agentno;
        $addData['cardNo'] = $cardno;
        $addData['tel'] = $tel;
        $addData['stat'] = $stat;
        $addData['province'] = $province;
        $addData['city'] = $city;
        $addData['county'] = $county;
        $addData['address'] = $address;
        $addData['startime'] = strtotime($startime);
        $addData['endtime'] = strtotime($endtime);
        $addData['team_name'] = $team_name;
        $password ? $addData['password'] = md5($password) : '';
        
        $member_id = $Agent->addData($addData);
        
        //添加代理关系
        $AgentRelation = D('AgentRelation');
        $agent_where['member_id'] = $parent_id;
        $agent_info = $AgentRelation->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        $agent1_id = $agent_info['agent1_id'] ? $agent_info['agent1_id'] : ($star == 2 ? $parent_id : 0);
        $agent2_id = $agent_info['agent2_id'] ? $agent_info['agent2_id'] : ($star == 3 ? $parent_id : 0);
        $agent3_id = $agent_info['agent3_id'] ? $agent_info['agent3_id'] : ($star == 4 ? $parent_id : 0);
        $agent4_id = $agent_info['agent4_id'] ? $agent_info['agent4_id'] : 0;
        
        $is_agent = $star == 0 ? 0 : 1;
        $arData['member_id'] = $member_id;
        $arData['agent1_id'] = $agent1_id;
        $arData['agent2_id'] = $agent2_id;
        $arData['agent3_id'] = $agent3_id;
        $arData['agent4_id'] = $agent4_id;
        $arData['pid'] = $parent_id;
        $arData['agent_grade'] = $star;
        $arData['is_cancel'] = 0;
        $arData['is_agent'] = $is_agent;
        $arData['is_validate'] = $stat;
        
        $result = $AgentRelation->addData($arData);
        
        if($member_id && $result){
            $return = array('status'=>1,'msg'=>'注册成功','result'=>'');
            $Agent->commit();//提交事务
        }else{
            $Agent->rollback(); //事务回滚
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //删除代理 实际是加入黑名单
    public function delAgent() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        $ar_where['member_id'] = $where['agentId'] = $agent_id;
        
        $agent_info = $this->getAgent($agent_id);
        if($agent_info['stat'] == -2){
            $return['msg'] = '您不能删除待审核的代理!';
            $this->ajaxReturn($return,'json');
        }
        
        $Agent = D('Agent');
        $AgentRelation = D('AgentRelation');
        $Agent->startTrans();
        
        $areData['is_validate'] = $aeData['stat'] = -1;
        
        $a_result = $Agent->editData($where,$aeData);
        $ar_result = $AgentRelation->editData($ar_where,$areData);
        
        if($a_result && $ar_result){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
            S(C('AGENT_INFO').$agent_id,NULL);
            $Agent->commit();
        }else{
            $Agent->rollback();
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //通过代理审核
    public function adoptAgent() {
        $return = array('status'=>0,'msg'=>'通过失败','result'=>'');
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        $ar_where['member_id'] = $where['agentId'] = $agent_id;
        $Agent = D('Agent');
        $AgentRelation = D('AgentRelation');
        $Agent->startTrans();
        
        $areData['is_validate'] = $aeData['stat'] = 1;
        
        $a_result = $Agent->editData($where,$aeData);
        $ar_result = $AgentRelation->editData($ar_where,$areData);
        
        if($a_result && $ar_result){
            $return = array('status'=>1,'msg'=>'通过成功','result'=>'');
            $Agent->commit();
        }else{
            $Agent->rollback();
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //代理商导入
    public function importAgent() {
        
    }
    
    //代理商导出
    public function exportAgent() {
        
    }
    
    //发展下级页面
    public function growthAgentView() {
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        
        $name = I('name');
        $star = I('star');
        $team_name = I('team_name');
        $top2_id = I('top2_id',0);
        
        if($name){
            
            $time = time();
            $String  = new \Org\Util\String();
            $str = $String->randString(6,1);
            $inviteCode = $time.$str;

            $addData['inviteCode'] = $inviteCode;
            $addData['name'] = $name;
            $addData['team_name'] = $team_name;
            $addData['agentId'] = 1;
            $addData['star'] = $star;
            $addData['stat'] = 0;
            $addData['top2_id'] = $top2_id;
            
            if($top2_id > 0){
                $AgentRelation = D('AgentRelation');
                $agent_where['member_id'] = $top2_id;
                $agent_info = $AgentRelation->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                $addData['top1_id'] = $agent_info['pid'] ? $agent_info['pid'] : 0;
            }

            $Invitecode = D('Invitecode');
            $result = $Invitecode->addData($addData);

            if($result){
                $url = get_share_url();
                $url = $url.'/Login/reg.html?code='.$inviteCode;
                $return = array('url'=>$url,'code'=>$inviteCode);
                $this->assign('return',$return);
            }
        }
      
        $this->display('growthAgentView');
    }
    
    //邀请码管理
    public function inviteManege() {
        
        $name = I('name');
        $page = I('p',1);
        
        $name ? $where['name'] = $name : '';
        
        $limit=10;
        
        $Invitecode = D('Invitecode');
        
        $count      = $Invitecode->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $Invitecode->getList($where,$limit,$page,'star',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        //搜索条件
        $search['name'] = $name;
        $this->assign('search',$search);
       
        $this->assign('list_count',$count);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display('inviteManege');
    }
    
    //删除邀请码
    public function delInviteCode() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        
        $inviteId = I('aid');
        
        if(empty($inviteId)){
            $return['msg'] = '请选择邀请码';
            $this->ajaxReturn($return,'json');
        }
        
        $where['inviteId'] = $inviteId;
        $Invitecode = D('Invitecode');
        $result = $Invitecode->delData($where);
        
        if($result){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
        }
        
        $this->ajaxReturn($return,'json');
        
    }
    
    
}