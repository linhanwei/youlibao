<?php
/*
 * 代理管理后台
 */
namespace Mobile\Controller;
use Think\Controller;
class AgentManageController extends CommonController {
    public function __construct() {
        parent::__construct();
        
        $action_name = 'agentManage';
        $this->assign('action_name',$action_name);
    }
    
    //首页
    public function index(){
       
        //下级限制总人数
        $next_agent_count = C('NEXT_AGENT_COUNT');
        $this->assign('next_agent_count',$next_agent_count);
        
        //代理信息 开始
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        $agent_grade = $member_info['agent_grade'];
        $is_agent = $member_info['is_agent'];
        
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
        
        //激励语
        $encourage_info = $this->getEncourage();
        $this->assign('encourage_info', $encourage_info);
        
        //收入:公司返利收入与销售收入 开始
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        $this->assign('month',$month);
        
        //每个月最后一天
        $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
        $month_last_day = date('d', strtotime("$BeginDate +1 month -1 day"));
        
        
        $OrderGoods = D('OrderGoods');
        $AgentMonthProfit = D('AgentMonthProfit');
        $AgentProfitLog = D('AgentProfitLog');
        
        //今日收入: 
            //销售收入
            $sale_day_where['year'] = $year;
            $sale_day_where['month'] = $month;
            $sale_day_where['day'] = $day;
            $sale_day_where['is_refund'] = 1;//是否退货:1:否,2:是
            $sale_day_where['admin_id'] = $member_id;
            $sale_day_total = $OrderGoods->getSum($sale_day_where,'goods_total_profit');
            $sale_day_total = $sale_day_total ? $sale_day_total : 0;
            
            //返利收入
            $profit_day_where['year'] = $year;
            $profit_day_where['month'] = $month;
            $profit_day_where['day'] = $day;
            $profit_day_where['profit_agent_id'] = $member_id;
            $profit_day_where['is_refund'] = 1; //是否退货:1:否,2:是
            
            //下级分润
            $profit_day_where2 = $profit_day_where;
            $profit_day_where2['profit_type'] = 1;
            $profit_day_total2 = $AgentProfitLog->getSum($profit_day_where2,'profit_total_money');
            $profit_day_total2 = $profit_day_total2 ? $profit_day_total2 : 0;
            
            //推荐分润
            $profit_day_where1 = $profit_day_where;
            $profit_day_where1['profit_type'] = 2;
            $profit_day_total1 = $AgentProfitLog->getSum($profit_day_where1,'profit_money');
            $profit_day_total1 = $profit_day_total1 ? $profit_day_total1 : 0;
           
            $this->assign('day_total_profit',$sale_day_total+$profit_day_total1+$profit_day_total2);
            
        //本月收入
        $month_profit_where['year'] = $year;
        $month_profit_where['month'] = $month;
        $month_profit_where['agent_id'] = $member_id;
        $month_info = $AgentMonthProfit->where($month_profit_where)->field('company_profit+sale_profit as month_total_profit')->find();
        $this->assign('month_total_profit', $month_info ? $month_info['month_total_profit'] : 0);
       
        //累计收入
        $all_profit_where['agent_id'] = $member_id;
        $all_info = $AgentMonthProfit->where($all_profit_where)->field('SUM(company_profit+sale_profit) as all_total_profit')->find();
        $this->assign('all_total_profit', $all_info['all_total_profit'] ? $all_info['all_total_profit'] : 0);
        
        //本月每日收入统计
            //销售收入
            $new_sale_one_day_list = array();
            $sale_one_day_where['year'] = $year;
            $sale_one_day_where['month'] = $month;
            $sale_one_day_where['is_refund'] = 1;//是否退货:1:否,2:是
            $sale_one_day_where['admin_id'] = $member_id;
            $sale_one_day_list = $OrderGoods->where($sale_one_day_where)->field('day,SUM(goods_total_profit) as sale_day_total')->group('day')->order('day')->select();
            if($sale_one_day_list){
                foreach ($sale_one_day_list as $sv) {
                    $new_sale_one_day_list[$sv['day']] = $sv['sale_day_total'];
                }
            }
         
            //下级返利收入
            $profit_one_day_where['year'] = $year;
            $profit_one_day_where['month'] = $month;
            $profit_one_day_where['profit_agent_id'] = $member_id;
            $profit_one_day_where['is_refund'] = 1; //是否退货:1:否,2:是
            
            $new_profit_one_day_list2 = array();
            $profit_one_day_where2 = $profit_one_day_where;
            $profit_one_day_where2['profit_type'] = 1;
            $profit_one_day_list = $AgentProfitLog->where($profit_one_day_where2)->field('day,SUM(profit_total_money) as profit_day_total')->group('day')->order('day')->select();

            if($profit_one_day_list){
                foreach ($profit_one_day_list as $pv) {
                    $new_profit_one_day_list2[$pv['day']] = $pv['profit_day_total'];
                }
            }
            
            //推荐返利收入
            $new_profit_one_day_list1 = array();
           
            $profit_one_day_where1 = $profit_one_day_where;
            $profit_one_day_where1['profit_type'] = 2;
            $profit_one_day_list = $AgentProfitLog->where($profit_one_day_where1)->field('day,SUM(profit_money) as profit_day_total')->group('day')->order('day')->select();

            if($profit_one_day_list){
                foreach ($profit_one_day_list as $pv) {
                    $new_profit_one_day_list1[$pv['day']] = $pv['profit_day_total'];
                }
            }
        
            $new_month_day_list = array();
            $new_month_day = array();
            
            if(empty($new_sale_one_day_list) && empty($profit_one_day_total1) && empty($profit_one_day_total2)){
                for($d=1;$d<=$month_last_day;$d++){
                    
                    $new_month_day_list[] = 0;
                    $new_month_day[] = $d;
                }
            }else{
                for($d=1;$d<=$month_last_day;$d++){
                    $sale_one_day_total = $new_sale_one_day_list[$d] ? $new_sale_one_day_list[$d] : 0;
                    $profit_one_day_total1 = $new_profit_one_day_list1[$d] ? $new_profit_one_day_list1[$d] : 0;
                    $profit_one_day_total2 = $new_profit_one_day_list2[$d] ? $new_profit_one_day_list2[$d] : 0;
                
                    $new_month_day_list[] = $sale_one_day_total + $profit_one_day_total1 + $profit_one_day_total2;
                    $new_month_day[] = $d;
                }
            }
            
            if($new_sale_one_day_list || $profit_one_day_list){
                
            }else{
                
            }

            $new_month_day_list = implode(',', $new_month_day_list);
            $this->assign('month_day_list',$new_month_day_list);
            $new_month_day = implode(',', $new_month_day);
            $this->assign('new_month_day',$new_month_day);
        
        //收入:公司返利收入与销售收入 结束
            
        //代理下线统计
            $AgentRelation = D('AgentRelation');

            if($is_agent == 1){ //代理
                //团队总人数
                $team_where['agent'.$agent_grade.'_id'] = $member_id;
                
                //直接下线总人数
                $next_where['pid'] = $member_id;
                $next_where['agent_grade'] = $agent_grade+1;
                
                //下线列表
                $list_where['ar.pid'] = $member_id;
                $list_where['ar.agent_grade'] = $agent_grade+1;
            }else{ //工厂
                //团队总人数
                $team_where['agent_grade'] = 1;
                
                //直接下线总人数
                $next_where['agent_grade'] = 1;
                
                //下线列表
                $list_where['ar.agent_grade'] = 1;
            }
            
            //团队总人数
            $team_count = $AgentRelation->getCount($team_where);
            $this->assign('team_count', $team_count);
            
            //直接下线总人数
            $next_count = $AgentRelation->getCount($next_where);
            $this->assign('next_count', $next_count);
           
            //下线列表
            $join = ' ar LEFT JOIN agent a on ar.member_id = a.agentId';
            $order = 'all_buy_total_stock DESC';
            $next_list = $AgentRelation->getAllList($list_where,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);

            if($next_list){
                foreach ($next_list as $ak => $av) {
                    $next_all_where['pid'] = $av['member_id'];
                    $next_all_where['agent_grade'] = $av['agent_grade']+1;
                    $next_all_count = $AgentRelation->getCount($next_all_where);
                    
                    $next_list[$ak]['next_count'] = $next_all_count;
                }
            }
            
            $this->assign('next_list', $next_list);
          
        $this->display();
    }
    
    //我的下级与待审核下级列表
    public function myAgent() {
        $member_id = $this->member_id;
        $limit=10;
        $page=I('p',1);
        $order='';
        $status = I('status',-2);
        $search = I('search');
        $this->assign('search',$search);
        
//        if($status == 1){
//            $where['a.stat'] = 1;
//            $display = 'myAgent';
//        }else{
//            $where['a.stat'] = -2;
//            $display = 'stayAgent';
//        }
       
        if($search){
            $where['_string'] = '(name = "'.$search.'")  OR ( agentNo = "'.$search.'")';
        }
        $where['ar.pid'] = $member_id;
        $where['ar.is_agent'] = 1;
        
        $join = ' ar LEFT JOIN agent a ON ar.member_id = a.agentId';
        $AgentRelation = D('AgentRelation');
        
        $count      = $AgentRelation->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null),$join);// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
      
        $list = S('agent_list_'.$member_id.'_'.$page.'_'.$status);
        if(empty($list) || $search){
            $list = $AgentRelation->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);
            
            if($list  && empty($search)){
                S('agent_list_'.$member_id.'_'.$page.'_'.$status,  serialize($list),2*60);
            }
        }else{
            $list = unserialize($list);
        }
        $this->assign('empty','<span class="empty">暂时没有我的代理</span>');
        $this->assign('list',$list);
        
        $this->display('myAgent');
    }
    
    //官方查看下级
    public function showMyAgent() {
        
        $pid = I('pid');
        
        if($pid){
            
            $MEMBER_LEVEL = C('MEMBER_LEVEL');
            $this->assign('MEMBER_LEVEL',$MEMBER_LEVEL);
            
            $member_info = $this->memberInfo();
            $member_id = $member_info['agentid'];
            
            //代理信息
            $agent_info = $this->getAgent($pid);
            $this->assign('agent_info', $agent_info);

            $limit=C('NEXT_AGENT_COUNT');
            $page=I('p',1);
            $order='a.all_buy_total_stock DESC';

            $where['ar.pid'] = $pid;
            $where['ar.agent1_id'] = $member_id;
            
            $count_where['pid'] = $pid;
            $count_where['agent1_id'] = $member_id;

            $join = ' ar LEFT JOIN agent a ON ar.member_id = a.agentId';
            $AgentRelation = D('AgentRelation');
            
            $count = $AgentRelation->getCount($count_where);
            $list = $AgentRelation->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);
            
            if($list){
                foreach ($list as $k => $v) {
                    
                    $list[$k]['next_count'] = $AgentRelation->getCount(array('pid'=>$v['agentid']));
                }
            }
        }
        
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->display('showMyAgent');
    }
    
    //查看自己的证书
    public function showAuth() {
        
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        $where['agentId'] = $member_id;
       
        $Agent = D('Agent');
        $UserCom = D('UserCom');
           
        $agent_info = $Agent->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        $admin_info = $UserCom->getDetail('',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
          
        if($agent_info){
           
            switch ($agent_info['stat']) {
                case 1: //
                    $notice_msg = $admin_info['agent_notice'];

                    //生成授权证书
                    $auth_img = make_agent_auth($agent_info);
                    $this->assign('auth_img',$auth_img);

                    break;
                case -1: //黑名单
                    $notice_msg = $admin_info['agent_hei_notice']; 
                    break;

            }

            $time = time(); //代理 合同过期
            if($time > $agent_info['endtime']){
                $notice_msg = $admin_info['agent_guoqi_notice']; 
            }

            $this->assign('agent_info',$agent_info);
           
        }else{
            $notice_msg = $admin_info['agent_nors_notice']; //代理不存在
        }
      
        $this->assign('notice_msg',$notice_msg);
        $this->display('showAuth');
    }
    
    //同意或者拒绝下级
    public function examineAgent() {
        $return = array('status'=>0,'msg'=>'','result'=>'');
        $agentid = I('aid');
        $status = I('status');
        
        //只有自己的下线才能更改下级信息
        $next_where['parent_id'] = $this->member_id;
        $next_where['agentId'] = $agentid;
        
        $Agent = D('Agent');
        $count = $Agent->getCount($next_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($count == 0){
            $return['msg'] = '您只能更改自己的下级代理信息';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agentid)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        switch ($status) {
            case 1:
                $stat = 1;
                break;

            default:
                $stat = -3;
                break;
        }
        
        $Agent->startTrans(); //开启事务
        $where['agentId'] = $agentid;
        $editData['startime'] = time();
        $editData['endtime'] = strtotime("+1 year -1 day");
        
        $result = $Agent->editData($where,$editData);
        
        $arEditData['is_validate'] = $stat;
        $AgentRelation = D('AgentRelation');
        $arResult = $AgentRelation->editData($where,$arEditData);
        
        if($result && $arResult){
            $return = array('status'=>1,'msg'=>'修改成功','result'=>'');
            S(C('AGENT_INFO').$agentid,NULL);
            $Agent->commit(); //提交事务
        }else{
            $return['msg'] = '修改失败';
            $Agent->rollback(); //事务回滚
        }
        $return['result'] = $editData;
        $this->ajaxReturn($return,'json');
    }
    
    //修改我的下级页面
    public function editMyAgentView() {
        $agent_id = I('aid');
        if(empty($agent_id)){
            $this->error('请选择代理');
        }
        
        $agent_info = $this->getAgent($agent_id);
        $this->assign('agent_info',$agent_info);
       
        $this->display('editMyAgentView');
    }
    
    //修改我的下级
    public function editMyAgent() {
        $return = array('status'=>0,'msg'=>'','result'=>'');
        $post = I('post.');
        $province = $post['province'];
        $city = $post['city'];
        $county = $post['county'];
        $address = $post['address'];
        $agentid = $post['agentid'];
        $qq = $post['qq'];
        
        //只有自己的下线才能更改下级信息
        $next_where['parent_id'] = $this->member_id;
        $next_where['agentId'] = $agentid;
        
        $Agent = D('Agent');
        $count = $Agent->getCount($next_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($count == 0){
            $return['msg'] = '您只能更改自己的下级代理信息';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agentid)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        $where['agentId'] = $agentid;
        $editData['province'] = $province;
        $editData['city'] = $city ;
        $editData['county'] = $county;
        $editData['address'] = $address;
        $editData['qq'] = $qq;
        
        $result = $Agent->editData($where,$editData);
        
        if($result){
            $return = array('status'=>1,'msg'=>'修改成功','result'=>'');
            S(C('AGENT_INFO').$agentid,NULL);
        }else{
            $return['msg'] = '修改失败';
        }
        $return['result'] = $editData;
        $this->ajaxReturn($return,'json');
    }
    
    //申请邀请码
    public function makeInviteView() {
        $member_info = $this->memberInfo();
     
        $star = $member_info['star'] + 1;
        $lv_name_list = C('MEMBER_LEVEL');
        $lv_name = $lv_name_list[$star]['name'];
      
        $this->assign('lv_name',$lv_name);
        $this->display('makeInviteView');
    }
    
    //邀请码管理
    public function inviteManage() {
        $agent_id = $this->member_id;
        $limit = 10;
        $page = I('p',1);
        $Invitecode = D('Invitecode');
        
        $where['agentId'] = $agent_id;
        $count      = $Invitecode->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $Invitecode->getList($where,$limit,$page,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
//        dump($list);
        $this->assign('empty','<span>没有邀请码</span>');
        $this->assign('list',$list);
        $this->display('inviteManage');
    }
    
    //删除邀请码
    public function delInvite() {
        $return = array('status'=>0,'msg'=>'','result'=>'');
        $inid = I('inid');
        
        if(empty($inid)){
            $return['msg'] = '请选择邀请码';
            $this->ajaxReturn($return,'json');
        }
        
        $Invitecode = D('Invitecode');
        
        $where['agentId'] = $this->member_id;
        $where['inviteId'] = $inid;
        
        $count = $Invitecode->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($count == 0){
            $return['msg'] = '非法操作';
            $this->ajaxReturn($return,'json');
        }
        
        $result = $Invitecode->delData(array('inviteId'=>$inid));
        if($result){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
        }
        
        $this->ajaxReturn($return,'json');
        
    }
    
    //生成邀请码
    public function makeInvite() {
        $return  = array('status'=>0,'msg'=>'生成邀请码失败','result'=>'');
        $name = I('name');
        
        if(empty($name)){
            $return['msg'] = '请输入姓名';
            $this->ajaxReturn($return,'json');
        }
        
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        $agent_grade = $member_info['agent_grade'];
        $star = $agent_grade + 1;
        
        //限制人数: 每个等级只能有10个人 ,官方发展大区: 如果大区满10人的情况,必须要等下面的所有人全部发展完才能发展第二条线
        $AgentRelation = D('AgentRelation');
        
        //获取直接下级总人数
        $direct_where['agent'.$agent_grade.'_id'] = $member_id;
        $direct_where['agent_grade'] = $star;
        $direct_next_agent_count = $AgentRelation->getCount($direct_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        //如果是大区,算下下面有没1110满人,不然就不能发展下级
        if($member_info['star'] == 1){
            
            //获取最后一条下线数字
            $num_where['agent1_id'] = $member_id;
            $num_where['agent1_id'] = $member_id;
            $line_number = $AgentRelation->where($num_where)->order('line_number DESC')->getField('line_number');
            $line_number = $line_number ? $line_number : 1;
            
            //间接总人数
            $indirect_where['agent1_id'] = $member_id;
            $indirect_where['line_number'] = $line_number;
            $indirect_next_agent_count = $AgentRelation->getCount($indirect_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
            
            if($direct_next_agent_count >= 10){
                if($indirect_next_agent_count < 1110){
                    $return['msg'] = '您的第'.$line_number.'条线还没全部满员,不能生成邀请码!';
                    $this->ajaxReturn($return,'json');
                }
                
            }
        }else{
            if($direct_next_agent_count >= 10){
                $return['msg'] = '您已经满员,不能再发展下级!';
                $this->ajaxReturn($return,'json');
            }
            
        }
        
        $time = time();
        $String  = new \Org\Util\String();
        $str = $String->randString(6,1);
        $inviteCode = $time.$str;
      
        $addData['inviteCode'] = $inviteCode;
        $addData['name'] = $name;
        $addData['agentId'] = $this->member_id;
        $addData['star'] = $star;
        $addData['stat'] = 0;
        $addData['line_number'] = $line_number ? $line_number : 0;
        
        $Invitecode = D('Invitecode');
        $result = $Invitecode->addData($addData);
        
        if($result){
            $url = get_share_url();
            $url = $url.'/Login/reg.html?code='.$inviteCode;
            $return['result'] = array('url'=>$url,'code'=>$inviteCode);
            $return['status'] = 1;
            $return['msg'] = '成功生成邀请码';
        }
        
        $this->ajaxReturn($return,'json');
        
    }
   
    //搜索代理
    public function searchAgent() {
        $return = array('status'=>0,'msg'=>'没有该代理,请重新输入','result'=>'');
        $agent_name = I('agent_name');
        if(empty($agent_name)){
            $return['msg'] = '请输入代理名称';
        }else{
            $where['name'] = array('LIKE','%'.$agent_name.'%');
            $Agent = D('Agent');
            $list = $Agent->getAllList($where,'',array('field'=>array(),'is_opposite'=>false),true);
            if($list){
                $return = array('status'=>1,'msg'=>'','result'=>$list); 
            }
        }
        $this->ajaxReturn($return,'json');
    }
    
    //提交授权页面
    public function accreditSubView() {
        //代理等级
        $agent_lv_list = C('MEMBER_LEVEL');
        $member_info = $this->memberInfo();
        $star = $member_info['star'];
        $lv_name = $agent_lv_list[$star+1]['name'];
        $this->assign('lv_name',$lv_name);
        
        //授权号        
        $Agent = D('Agent');
        $agentNo = $Agent->makeAgentNo();
        $this->assign('agentNo',$agentNo);
        
        //时间
        $start_time = date('Y-m-d');
        $this->assign('start_time',$start_time);
        
        $end_time = date('Y-m-d',  strtotime('+1 year -1 day'));
        $this->assign('end_time',$end_time);
        
        $this->display('accreditSubView');
    }
    
    //建立代理关系表
   public function makeAgent() {
       ini_set("max_execution_time", 0);
       $Agent = D('Agent');
       $AgentRelation = D('AgentRelation');
       
       $this->makeProvinceAgent($Agent,$AgentRelation);
       
       $this->makeCityAgent($Agent,$AgentRelation);
       
       $this->makeQuAgent($Agent,$AgentRelation);
       
       $this->makeVipAgent($Agent,$AgentRelation);
       
       return FALSE;
   }
   
   //建立省代关系
   protected function makeProvinceAgent($Agent,$AgentRelation) {
        $where['star'] = 1;
        $list1 = $Agent->where($where)->select();
        $result_data = 0;
        $all_count = count($list1);
        

        foreach ($list1 as $k1 => $v1) {
            $add_data1['member_id'] = $v1['agentid'];
            $add_data1['agent1_id'] = 0;
            $add_data1['agent2_id'] = 0;
            $add_data1['agent3_id'] = 0;
            $add_data1['agent4_id'] = 0;
            $add_data1['pid'] = 0;
            $add_data1['agent_grade'] = 1;
            $add_data1['is_cancel'] = 0;
            $add_data1['is_agent'] = 1;
            $add_data1['is_validate'] = $v1['stat'];
            $result  = $AgentRelation->addData($add_data1);
            
            if($result){
               $result_data++;
            }
        }
        
        if($result_data == $all_count){
            dump('省代全部添加完');
        }else{
            $sy = $all_count - $result_data;
            dump('省代还有:'.$sy.'没添加');
        }
   }
   
   //建立市代关系
   protected function makeCityAgent($Agent,$AgentRelation) {
        $where['star'] = 2;
        $list1 = $Agent->where($where)->select();
        $result_data = 0;
        $all_count = count($list1);

        foreach ($list1 as $k1 => $v1) {
            $add_data1['member_id'] = $v1['agentid'];
            $add_data1['agent1_id'] = $v1['parent_id'];
            $add_data1['agent2_id'] = 0;
            $add_data1['agent3_id'] = 0;
            $add_data1['agent4_id'] = 0;
            $add_data1['pid'] = $v1['parent_id'];
            $add_data1['agent_grade'] = 2;
            $add_data1['is_cancel'] = 0;
            $add_data1['is_agent'] = 1;
            $add_data1['is_validate'] = $v1['stat'];
            $result  = $AgentRelation->addData($add_data1);
            if($result){
               $result_data++;
            }
        }
        
        if($result_data == $all_count){
            dump('市代全部添加完');
        }else{
            $sy = $all_count - $result_data;
            dump('市代还有:'.$sy.'没添加');
        }
   }
   
   //建立区代关系
   protected function makeQuAgent($Agent,$AgentRelation) {
        $where['star'] = 3;
        $list1 = $Agent->where($where)->select();
        $result_data = 0;
        $all_count = count($list1);

        foreach ($list1 as $k1 => $v1) {
            $agent_where['member_id'] = $v1['parent_id']; 
            $agent_info = $AgentRelation->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            $add_data1['member_id'] = $v1['agentid'];
            $add_data1['agent1_id'] = $agent_info['agent1_id'] ? $agent_info['agent1_id'] : 0;
            $add_data1['agent2_id'] = $v1['parent_id'];
            $add_data1['agent3_id'] = 0;
            $add_data1['agent4_id'] = 0;
            $add_data1['pid'] = $v1['parent_id'];
            $add_data1['agent_grade'] = 3;
            $add_data1['is_cancel'] = 0;
            $add_data1['is_agent'] = 1;
            $add_data1['is_validate'] = $v1['stat'];
            $result  = $AgentRelation->addData($add_data1);
            if($result){
               $result_data++;
            }
        }
        
        if($result_data == $all_count){
            dump('区代全部添加完');
        }else{
            $sy = $all_count - $result_data;
            dump('区代还有:'.$sy.'没添加');
        }
   }
   
   //建立VIP会员关系
   protected function makeVipAgent($Agent,$AgentRelation) {
        $where['star'] = 4;
        $list1 = $Agent->where($where)->select();
        $result_data = 0;
        $all_count = count($list1);

        foreach ($list1 as $k1 => $v1) {
            $agent_where['member_id'] = $v1['parent_id']; 
            $agent_info = $AgentRelation->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            $add_data1['member_id'] = $v1['agentid'];
            $add_data1['agent1_id'] = $agent_info['agent1_id'] ? $agent_info['agent1_id'] : 0;
            $add_data1['agent2_id'] = $agent_info['agent2_id'] ? $agent_info['agent2_id'] : 0;
            $add_data1['agent3_id'] = $v1['parent_id'];
            $add_data1['agent4_id'] = 0;
            $add_data1['pid'] = $v1['parent_id'];
            $add_data1['agent_grade'] = 4;
            $add_data1['is_cancel'] = 0;
            $add_data1['is_agent'] = 1;
            $add_data1['is_validate'] = $v1['stat'];
           $result  =  $AgentRelation->addData($add_data1);
           if($result){
               $result_data++;
            }
        }
        
        if($result_data == $all_count){
            dump('vip全部添加完');
        }else{
            $sy = $all_count - $result_data;
            dump('vip代还有:'.$sy.'没添加');
        }
   }
   
   /*
    * 特约扫码兑奖记录列表
    */
   public function cashPrizeList() {
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        $where['agent_id'] = $member_id;
        $order='add_time DESC';
        
        $CashPrizeLog = D('CashPrizeLog');
        $list = $CashPrizeLog->getAllList($where,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        $this->assign('list', $list);
        $this->display('cashPrizeList');
   }
    
}