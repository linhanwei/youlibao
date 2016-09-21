<?php
namespace Admin\Controller;
use Think\Controller;
class FinanceController extends CommonController {
    
    private $CompanyReportsLog;
    private $AgentProfitLog;
    private $Agent_lv_list;
    private $AgentMonthProfit;
    private $Wechat;
    private $CompanyPaymentAgentLog;
            
    function __construct(){ //定义构造函数
        //继承父类
        parent::__construct();
        $this->Agent_lv_list=C('MEMBER_LEVEL');
    }
    
    //列表
    public function index(){
        //进行跳转到对应页面:显示对应的数据
        $star = I('star',5);
        $title = I('title');
        $flag = I('flag',2);
        $year = I('year');
        $month = I('month');
        $search_value = I('search_value');
        $search_field = I('search_field');
        
        $string_where = '';
        
        $star_sql = ' 1=1 ';
        if($star > 0 && $star < 5){
            $star_sql = ' star='.$star;
        }
        
        $search_sql = '';
        if($search_field && $search_value){
            $search_sql = ' AND '.$search_field.' = "'.$search_value.'"';
            
            $search['search_field'] = $search_field;
            $search['search_value'] = $search_value;
            $this->assign('search', $search);
        }
        
        $string_where = $star_sql.$search_sql;
        if($string_where){
            $where['_string']= ' agent_id IN(SELECT agentId FROM `agent` WHERE '.$string_where.')';
            
        }
        
         //每页进行10条
         $limit=10;
         $order='edit_time desc';
         $page = I('p',1);
         //总的条件为：flag : 1:已返利管理, 2:已提现管理, 3:未提现管理
         switch ($flag) {
             case 1:
                    $where['is_profit'] = 1;     
//                    $where['is_cash'] = 1;     
                 break;
             case 2:
                    $where['is_profit'] = 1;     
//                    $where['is_cash'] = 1;    
                 break;

            case 3:
                    $where['is_profit'] = 2;     
//                    $where['is_cash'] = 2;    
                 break;
         }
         
        $where['company_profit'] = array('gt',0); 
        
         //获取的数据库模型的对象
         $AgentMonthProfit = D('AgentMonthProfit');
         $Agent = D('Agent');
         $agent_lv_list = C('MEMBER_LEVEL');
         if(I('debug')){
             dump($where);
         }
         $count      = $AgentMonthProfit->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
         $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
         $show       = $Page->show();// 分页显示输出
         $list = $AgentMonthProfit->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
          if($list){
            foreach ($list as $k => $v) {
                $agenId=$list[$k]['agent_id'];
                $agentModel=$Agent->getDetail(array('agentId'=>$agenId),array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                $list[$k]['agentName'] =$agentModel['name'];
                $list[$k]['star'] =$agentModel['star'];
                $list[$k]['agent_true_name'] =$agentModel['agent_true_name'];
                $list[$k]['bank_account'] =$agentModel['bank_account'];
                $list[$k]['bank_name'] =$agentModel['bank_name'];
                $list[$k]['team_name'] =$agentModel['team_name'];
                $list[$k]['tel'] =$agentModel['tel'];
                $list[$k]['weixin'] =$agentModel['weixin'];
                $list[$k]['lv_name'] = $agent_lv_list[$agentModel['star']]['name'];
            }
        }
        
        
        //先显示的全部的数据先
        $this->assign('star',$star);
        $this->assign('title', $title);
        $this->assign('flag', $flag);
        $this->assign('year', $year);
        $this->assign('month', $month);
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('year',$year);
        $this->assign('count',$count);
        
        //输出查询年份
        $dateYear = date('Y');
        $year_html = '<option value="2016">2016</option>';
        for($y=2016;$y<$dateYear;$y++){
            $year_html .= '<option value="'.$y.'">'.$y.'</option>';
        }
        
        $this->assign('year_html',$year_html);
      
        
        $this->assign('agent_lv_list',$agent_lv_list);
        $this->assign('page',$show);// 赋值分页输出
       
        $this->display('index');
    }
    
    //数据统计页面
    public function operate(){
        $year = I('year');
        $month = I('month');
        $page = I('p',1);
        
        $where=array();
        
        if($year){
            $where['year']=$year;
        }
        
        if($month){
            $where['month']=$month;
        }
        
        $CompanyReportsLog = D('CompanyReportsLog');
        
        //每页进行10条
        $limit=10;
        $count      = $CompanyReportsLog->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $list = $CompanyReportsLog->getList($where,$limit,$page,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        //输出查询年份
        $dateYear = date('Y');
        $year_html = '<option value="2016">2016</option>';
        for($y=2016;$y<$dateYear;$y++){
            $year_html .= '<option value="'.$y.'">'.$y.'</option>';
        }
        
        $this->assign('year_html',$year_html);
        
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->display('operate');
    }
    
    //查看对应的详情
    public function  showAllDetail(){
        $title = I('title');
        $year = I('year');
        $month = I('month');
        $is_profit = I('is_profit');
        $agent_id = I('agent_id');
        $page = I('p',1);
        
        if($year && $month){
           //条件内容
           $where['year']=$year;
           $where['month']=$month;
        }
        
        //增加对应的选择条件
        if($is_profit){
            $where['is_profit']=$is_profit;
        }
        
        if($agent_id){
            $where['profit_agent_id']=$agent_id;
        }
         
        $AgentProfitLog = D('AgentProfitLog');
        
        $limit=10;
        $count      = $AgentProfitLog->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $list = $AgentProfitLog->getList($where,$limit,$page,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
      
        if($list){
            foreach ($list as $k => $v) {
                $list[$k]['profit_agent_lv_name'] = $this->Agent_lv_list[$v['profit_agent_lv']]['name'];
                $list[$k]['buy_agent_lv_name'] = $this->Agent_lv_list[$v['buy_agent_lv']]['name'];
                //状态的标示
                if($v['is_profit']=="1"){
                      $list[$k]['status_name']='已提现';
                }  
                else {
                      $list[$k]['status_name']='未提现';
                }
              
            }
        }
        
        $this->assign('title', $title);
        $this->assign('year',$year);
        $this->assign('month',$month);
        $this->assign('is_profit',$is_profit);
        $this->assign('agent_id',$agent_id);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->display('detail');
    }
    
    //企业支付查询
    public function payCheck() {
        $return = array('status'=>0,'msg'=>'支付还没有完成,请进行支付!','result'=>'');
        
        $month_profit_id = I('mpid');
        
        if(empty($month_profit_id)){
            $return['msg'] = '请选择代理分润!';
            $this->ajaxReturn($return,'json');
        }
        
        $this->Wechat = self::newWechat();
        $this->CompanyPaymentAgentLog = D('CompanyPaymentAgentLog');
        $this->AgentMonthProfit = D('AgentMonthProfit');
        $this->AgentProfitLog = D('AgentProfitLog');
        
        //获取代理每月分润信息
        $agent_month_profit_where['id'] = $month_profit_id;
        $agentMonthProfitInfo = $this->AgentMonthProfit->getDetail($agent_month_profit_where);
        
        //分润的信息
        $agent_id = $agentMonthProfitInfo['agent_id'];
        $profit_year = $agentMonthProfitInfo['year'];
        $profit_month = $agentMonthProfitInfo['month'];
       
        $money = self::checkWeixinPay($agentMonthProfitInfo);
        
        if($money === TRUE){
            //修改代理分润信息
            self::editAgentProfitInfo($month_profit_id,$agent_id,$profit_year,$profit_month);
            
            $return['status'] = 1;
            $return['msg'] = '支付完成!';
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //审核支付
    public function payMoney() {
        ini_set("max_execution_time", 0);
        $return = array('status'=>0,'msg'=>'','result'=>'');
        
        $month_profit_id = I('mpid');
        
        if(empty($month_profit_id)){
            $return['msg'] = '请选择代理分润!';
            $this->ajaxReturn($return,'json');
        }
        
        //获取管理员信息
        $admin_info = session('admin_info');
        
        if(empty($admin_info)){
            $return['msg'] = '您还没有登录,请登录!';
            $this->ajaxReturn($return,'json');
        }
        
        $admin_id = $admin_info['userid'];
        $admin_name = $admin_info['username'];
        
        $Agent = D('Agent');
        $this->CompanyPaymentAgentLog = D('CompanyPaymentAgentLog');
        $this->AgentMonthProfit = D('AgentMonthProfit');
        $this->AgentProfitLog = D('AgentProfitLog');
        $this->CompanyReportsLog  = D('CompanyReportsLog');
        
        //获取代理每月分润信息
        $agent_month_profit_where['id'] = $month_profit_id;
        $agentMonthProfitInfo = $this->AgentMonthProfit->getDetail($agent_month_profit_where);
        
        if(empty($agentMonthProfitInfo)){
            $return['msg'] = '代理这个月没有分润金额!';
            $this->ajaxReturn($return,'json');
        }
        
        if($agentMonthProfitInfo['is_profit'] == 1){
            $return['msg'] = '代理本月已经分润!';
            $this->ajaxReturn($return,'json');
        }
        
        //分润的信息
        $agent_id = $agentMonthProfitInfo['agent_id'];
        $profit_year = $agentMonthProfitInfo['year'];
        $profit_month = $agentMonthProfitInfo['month'];
        $company_profit_total = $agentMonthProfitInfo['company_profit'];
        
        //获取代理信息
        $agent_where['agentId'] = $agent_id;
        $agentInfo = $Agent->where($agent_where)->find();
        
        //只有审核通过的代理才能返利
        if($agentInfo['stat'] != 1){
            $return['msg'] = '代理不是已审核状态,不能返利!';
            $this->ajaxReturn($return,'json');
        }
        
        //检查是否已经绑定
        if(empty($agentInfo['openid'])){
            $return['msg'] = '代理还没有绑定微信号,不能返利!';
            $this->ajaxReturn($return,'json');
        }
        
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $dateTime = date('Y-m-d H:i:s');
        
        //每月15日之后才能分润
        if($day < 15){
            $return['msg'] = '亲~~要到15号才能返利哦!';
            $this->ajaxReturn($return,'json');
        }
        
        //当月不能分润
        if($profit_year == $year && $profit_month == $month){
            $return['msg'] = '亲~~分润时间还没到哦,请耐心等待!';
            $this->ajaxReturn($return,'json');
        }
        
        //统计分润日志总分润金额
        $agent_profit_log_where['year'] = $profit_year;
        $agent_profit_log_where['month'] = $profit_month;
        $agent_profit_log_where['profit_agent_id'] = $agent_id;
        $agentProfitLogTotal = $this->AgentProfitLog->getSum($agent_profit_log_where,'profit_total_money');
        $agentProfitLogTotal = $agentProfitLogTotal ? $agentProfitLogTotal : 0;
        
        //判断分润日志总金额是否与月份统计总金额一致
        if($agentProfitLogTotal != $company_profit_total){
            $return['msg'] = '分润日志总金额与月份统计总金额一致!';
            $return['a'] = $agentProfitLogTotal.'/////'.$company_profit_total;
            $this->ajaxReturn($return,'json');
        }
       
        $this->Wechat = self::newWechat();
        
        //查询支付日志,看下是否有支付失败的订单,有就修改支付状态
        $money = self::checkWeixinPay($agentMonthProfitInfo);
        
        if($money === TRUE){
            //修改代理分润信息
            self::editAgentProfitInfo($month_profit_id,$agent_id,$profit_year,$profit_month);
            
            $return['status'] = 1;
            $return['msg'] = '该本条记录已经分润,不能再分!';
            $this->ajaxReturn($return,'json');
        }
        
        //不能少于1元
        if($money < 1){
            $return['msg'] = '付款金额不能小于1元!';
            $this->ajaxReturn($return,'json');
        }
        
        //一日之内不能多于100万
        $pay_sucess_total_where['year'] = $year;
        $pay_sucess_total_where['month'] = $month;
        $pay_sucess_total_where['day'] = $day;
        $paySucessTotal = $this->CompanyPaymentAgentLog->getSum($pay_sucess_total_where,'money');
        $paySucessTotal = $paySucessTotal ? $paySucessTotal : 0;
        
        if($paySucessTotal >= 1000000){
            $return['msg'] = '一日之内支付不能多于100万!';
            $this->ajaxReturn($return,'json');
        }
        
        $mod_money = C('WX_COMPANY_PAY_MAX_MONEY'); //微信限制单笔最高金额
        $desc = C('WX_COMPANY_PAY_DESC'); //企业转账说明

        if($money > $mod_money){
            $mod_sup_money = $money%$mod_money;
            $sup_step = ceil($money/$mod_money);
            $pay_money = $mod_money;
        }else{
            $sup_step = 1;
            $pay_money = $money;
        }
        
        $is_pay_success = TRUE; //是否全部支付成功
        $pay_total_money = 0; //累计支付总金额
        
        for($pi=1;$pi <= $sup_step;$pi++){
            //如果付款金额大于微信最大金额时取微信最大金额,最后再去余下的金额支付
            if($pi > 1 && $sup_step == $pi){
                $pay_money = $mod_sup_money;
            }
            
            $pay_total_money += $pay_money;
            
            if($pay_money > 0){
                $payResult = self::weixinPay($admin_id,$admin_name,$agentMonthProfitInfo,$agentInfo,$pay_money,$desc,$year,$month,$day,$dateTime);
                
                if($payResult === false){
                    $is_pay_success = FALSE;
                    $return['msg'] = $this->Wechat->getError();
                }
            }
            
            //同一用户第二次支付不能少于15秒
            if($sup_step > 1){
                sleep(15);
            }
            
        }
        
        if($is_pay_success){
            //修改代理分润信息
            self::editAgentProfitInfo($month_profit_id,$agent_id,$profit_year,$profit_month);
            
            $return['status'] = 1;
            $return['msg'] = '支付完成!';
           
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //导出创始人分润的界面
    public function exportProfitView() {
        $star = I('star',5);
        $title = I('title');
        $flag = I('flag',2);
        $year = I('year');
        $month = I('month');
        $search_value = I('search_value');
        $search_field = I('search_field');
        
        $string_where = '';
        
        $star_sql = '';
        if($star > 0 && $star < 5){
            $star_sql = ' AND star='.$star;
        }
        
        $search_sql = '';
        if($search_field && $search_value){
            $search_sql = ' AND '.$search_field.' = "'.$search_value.'"';
            
            $search['search_field'] = $search_field;
            $search['search_value'] = $search_value;
            $this->assign('search', $search);
        }
        
        $string_where = ' star = 1'.$star_sql.$search_sql;
        if($string_where){
            $where['_string']= ' agent_id IN(SELECT agentId FROM `agent` WHERE '.$string_where.')';
            
        }
        
        //找出分润的年份与月份
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        //当前月如果为1月份时,分润年月就应该是上一年的12月
        if($month == 1){
            $year = $year - 1;
            $month = 12;
        }else{
            $month = $month - 1;
        }
        
         //每页进行10条
         $limit=20;
         $order='edit_time desc';
         $page = I('p',1);
         
        $where['is_profit'] = 2;     
        $where['year'] = $year;     
        $where['month'] = $month;     
        $where['company_profit'] = array('gt',0); 
        
         //获取的数据库模型的对象
         $AgentMonthProfit = D('AgentMonthProfit');
         $Agent = D('Agent');
         $agent_lv_list = C('MEMBER_LEVEL');
         
         $count      = $AgentMonthProfit->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
         $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
         $show       = $Page->show();// 分页显示输出
         $list = $AgentMonthProfit->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
          if($list){
            foreach ($list as $k => $v) {
                $agenId=$list[$k]['agent_id'];
                $agentModel=$Agent->getDetail(array('agentId'=>$agenId),array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                $list[$k]['agentName'] =$agentModel['name'];
                $list[$k]['star'] =$agentModel['star'];
                $list[$k]['agent_true_name'] =$agentModel['agent_true_name'];
                $list[$k]['bank_account'] =$agentModel['bank_account'];
                $list[$k]['bank_name'] =$agentModel['bank_name'];
                $list[$k]['team_name'] =$agentModel['team_name'];
                $list[$k]['tel'] =$agentModel['tel'];
                $list[$k]['weixin'] =$agentModel['weixin'];
                $list[$k]['lv_name'] = $agent_lv_list[$agentModel['star']]['name'];
            }
        }
        
        
        //先显示的全部的数据先
        $this->assign('star',$star);
        $this->assign('title', $title);
        $this->assign('flag', $flag);
        $this->assign('year', $year);
        $this->assign('month', $month);
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('year',$year);
        $this->assign('count',$count);
        
        //输出查询年份
        $dateYear = date('Y');
        $year_html = '<option value="2016">2016</option>';
        for($y=2016;$y<$dateYear;$y++){
            $year_html .= '<option value="'.$y.'">'.$y.'</option>';
        }
        
        $this->assign('year_html',$year_html);
      
        
        $this->assign('agent_lv_list',$agent_lv_list);
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display('exportProfitView');
    }
    
    //导出分润
    public function exportProfit() {
        ini_set("max_execution_time", 0);
        
        $agent_id = I('aid');
        $is_profit = I('is_profit',0);
    
        $MEMBER_LEVEL = C('MEMBER_LEVEL');
        
        if(empty($agent_id)){
            exit('<h1>请选择代理!</h1>');
        }
        
        //获取管理员信息
        $admin_info = session('admin_info');
        
        if(empty($admin_info)){
            exit('<h1>您还没有登录,请登录!</h1>');
        }
        
        //找出分润的年份与月份
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        //当前月如果为1月份时,分润年月就应该是上一年的12月
        if($month == 1){
            $year = $year - 1;
            $month = 12;
        }else{
            $month = $month - 1;
        }
//        
//        if($day < 15){
//            exit('<h1>分润时间还没到,请耐心等待!</h1>');
//        }
        
        $Agent = D('Agent');
        $AgentMonthProfit = D('AgentMonthProfit');
        $AgentProfitLog = D('AgentProfitLog');
        $this->CompanyReportsLog  = D('CompanyReportsLog');
        $this->CompanyPaymentAgentLog = D('CompanyPaymentAgentLog');
        
        $where['agentId'] = $agent_id;
        $agent_info = $Agent->where($where)->find();
        
        if(empty($agent_info)){
            exit('<h1>代理不存在!</h1>');
        }
        
        if($agent_info['star'] != 1){
            exit('<h1>代理不是'.$MEMBER_LEVEL[1]['name'].'!</h1>');
        }
        
        //查找该条线是否有可分润的代理,代理必须是已审核的状态
//        $sql = 'SELECT ar.*,amp.*,a.name,a.weixin,a.agent_true_name,a.bank_account,a.bank_name FROM agent_month_profit amp '
//                . 'LEFT JOIN agent a ON a.agentId = amp.agent_id '
//                . 'LEFT JOIN agent_relation ar ON amp.agent_id = ar.member_id '
//                . 'WHERE amp.agent_id IN(SELECT member_id FROM agent_relation WHERE (agent1_id = '.$agent_id.' OR member_id = '.$agent_id.') AND is_validate = 1) '
//                . ' AND  amp.is_profit = 2'
//                . ' AND amp.company_profit > 0'
//                . ' AND amp.year= '.$year
//                . ' AND amp.month = '.$month
//                . ' ORDER BY ar.agent_grade ASC';
        
        $sql = 'SELECT ar.*,amp.*,a.name,a.weixin,a.agent_true_name,a.bank_account,a.bank_name,SUM(apl.profit_total_money) all_profit_total_money FROM agent_month_profit amp '
                . 'LEFT JOIN agent a ON a.agentId = amp.agent_id '
                . 'LEFT JOIN agent_relation ar ON amp.agent_id = ar.member_id '
                . 'LEFT JOIN agent_profit_log apl ON apl.profit_agent_id = amp.agent_id '
                . 'WHERE amp.agent_id IN(SELECT member_id FROM agent_relation WHERE (agent1_id = '.$agent_id.' OR member_id = '.$agent_id.') AND is_validate = 1) '
                . ' AND  amp.is_profit = 2'
                . ' AND amp.company_profit > 0'
                . ' AND amp.year = '.$year
                . ' AND amp.month = '.$month
                . ' AND apl.year = '.$year
                . ' AND apl.month = '.$month
                . ' GROUP BY amp.agent_id'
                . ' ORDER BY ar.agent_grade ASC';
     
        $agent_list = $AgentMonthProfit->query($sql);
        
        if(I('debug')){
            dump($sql);
            dump($agent_list);die;
        }
        
        if($agent_list){
            
            //循环找出各自的下线与分润金额
            foreach ($agent_list as $k => $v) {
                $agent_grade = $v['agent_grade'];
                $member_id = $v['member_id'];
                $company_profit = $v['all_profit_total_money'];
                
                //查询代理下级所有的金额总和
                $profit_sum_where['year'] = $year;
                $profit_sum_where['month'] = $month;
//                $profit_sum_where['_string']= ' agent_id IN(SELECT member_id FROM agent_relation WHERE agent'.$agent_grade.'_id = '.$member_id.')';
//                $profitSumMoney = $AgentMonthProfit->getSum($profit_sum_where,'company_profit');
                
                $profit_sum_where['_string']= ' profit_agent_id IN(SELECT member_id FROM agent_relation WHERE agent'.$agent_grade.'_id = '.$member_id.')';
                $profitSumMoney = $AgentProfitLog->getSum($profit_sum_where,'profit_total_money');
                
                $profitSumMoney = $profitSumMoney ? $profitSumMoney : 0;
                
                $new_v  = array(
                    'name'=>$v['name'],
                    'grade_name'=>$MEMBER_LEVEL[$agent_grade]['name'],
                    'my_money'=>$company_profit,
                    'next_sum_money'=>$profitSumMoney,
                    'children'=>array()
                );
                
                if($is_profit == 1){
                    //添加支付日志
                    self::addPayLog($v,$admin_info,$company_profit);
                }
                
                switch ($agent_grade) {
                    case 1:
                            $new_agent_list[] = $new_v;

                        break;
                    case 2:
                            $new_agent_list[0]['children'][] = array();
                            $new_agent_list[0]['children'][$member_id] = $new_v;

                        break;
                    case 3:
                         
                            $new_agent_list[0]['children'][$v['agent2_id']]['children'][$member_id] = $new_v;

                        break;
                    case 4:
                            $new_agent_list[0]['children'][$v['agent2_id']]['children'][$v['agent3_id']]['children'][$member_id] = $new_v;

                        break;

                }
            }
            
            //组装导出的数据
            $export_data = array();
            foreach ($new_agent_list as $ag1) {
                
                $data["name"] =$ag1["name"];
                $data["grade_name"] = $ag1["grade_name"];
                $data["my_money"] = $ag1["my_money"];
                $data["next_sum_money"] = $ag1["next_sum_money"];
                
                if($ag1["next_sum_money"] > 0 || $ag1["my_money"] > 0){
                    $data["pay_sum_money"] = $ag1["next_sum_money"] + $ag1["my_money"];
                }else{
                    $data["pay_sum_money"] = NULL;
                }
                $export_data[] = $data;
                
                $ag1_children = $ag1['children'];
                if($ag1_children){
                    foreach ($ag1_children as $ag2) {
                        $ag2_children = $ag2['children'];
                        
                        $data["name"] =$ag2["name"];
                        $data["grade_name"] = $ag2["grade_name"];
                        $data["my_money"] = $ag2["my_money"];
                        $data["next_sum_money"] = $ag2["next_sum_money"];
                        
                        if($ag2["next_sum_money"] > 0 || $ag2["my_money"] > 0){
                            $data["pay_sum_money"] = $ag2["next_sum_money"] + $ag2["my_money"];
                        }else{
                            $data["pay_sum_money"] = NULL;
                        }
                        $export_data[] = $data;
                        
                        if($ag2_children){
                            foreach ($ag2_children as $ag3) {
                                $data["name"] =$ag3["name"];
                                $data["grade_name"] = $ag3["grade_name"];
                                $data["my_money"] = $ag3["my_money"];
                                $data["next_sum_money"] = $ag3["next_sum_money"];
                                
                                if($ag3["next_sum_money"] > 0 || $ag3["my_money"] > 0){
                                    $data["pay_sum_money"] = $ag3["next_sum_money"] + $ag3["my_money"];
                                }else{
                                    $data["pay_sum_money"] = NULL;
                                }
                                $export_data[] = $data;
                            }
                        }
                    }
                }
            }
            
            //修改代理分润状态 开始
                if($is_profit == 1){
                    //月分润
                    $edit_company_month_profit_where['_string'] = 'agent_id IN(SELECT member_id FROM agent_relation WHERE (agent1_id = '.$agent_id.' OR member_id = '.$agent_id.'))';
                    $edit_company_month_profit_where['is_profit'] = 2;
                    $edit_company_month_profit_where['year'] = $year;
                    $edit_company_month_profit_where['month'] = $month;
                    $editCompanyMonthProfitData['is_profit'] = 1;

                    $AgentMonthProfit->editData($edit_company_month_profit_where,$editCompanyMonthProfitData);

                    //分润日志
                    $edit_agent_profit_log_where['_string'] = 'profit_agent_id IN(SELECT member_id FROM agent_relation WHERE (agent1_id = '.$agent_id.' OR member_id = '.$agent_id.'))';
                    $edit_agent_profit_log_where['is_profit'] = 2;
                    $edit_agent_profit_log_where['year'] = $year;
                    $edit_agent_profit_log_where['month'] = $month;
                    $editAgentProfitLogData['is_profit'] = 1;

                    $AgentProfitLog->editData($edit_agent_profit_log_where,$editAgentProfitLogData);

                    //公司报表
                    $pay_money = $export_data[0]['pay_sum_money'];
                    self::editCompanyReport($pay_money);
                }
            //修改代理分润状态 结束
                
            $agent1_name = $export_data[0]['name'];
                
//            dump($export_data);die;
            $excel_title = $agent1_name.'_'.$year.'年'.$month.'月分润统计';
            $config = array(
                'fields'=>array('姓名','级别','自己的分润','下级总分润','上级应付总金额'),//导入/导出文件字段[导入时为数据字段,导出时为字段标题]
                 'data'=>$export_data, //导出Excel的数组
                 'savename'=>$excel_title,
                 'title'=>$excel_title,     //导出文件栏目标题
                 'suffix'=>'xlsx',//文件格式
            );
        
            $Excel = new \Common\Library\Excel($config);
            $Excel::export($export_data);
        }else{
        
            exit('<h1>本条线没有可分润的代理!</h1>');
        }
       
    }
    
    /**
     * 分润支付记录日志
     * @param type $agentInfo 分润代理信息
     * @param type $admin_info 管理员信息
     * @param type $pay_money 支付金额
     * @return type
     */
    private function addPayLog($agentInfo,$admin_info,$pay_money) {
        $agent_id = $agentInfo['agent_id'];
        $profit_year = $agentInfo['year'];
        $profit_month = $agentInfo['month'];
        
        $agent_name = $agentInfo['name'];
        $weixin = $agentInfo['weixin'];
        $bank_account = $agentInfo['bank_account'];
        $bank_name = $agentInfo['bank_name'];
        $agent_true_name = $agentInfo['agent_true_name'];
        $bank_name = $bank_name ? $bank_name : '人工转账';
        $bank_account = $bank_account ? $bank_account : '';
        $agent_true_name = $agent_true_name ? $agent_true_name : $agent_name;
        
        $admin_id = $admin_info['userid'];
        $admin_name = $admin_info['username'];
        
        $addCompanyPaymentAgentLogData['admin_id'] = $admin_id;
        $addCompanyPaymentAgentLogData['admin_name'] = $admin_name;
        $addCompanyPaymentAgentLogData['agent_id'] = $agent_id;
        $addCompanyPaymentAgentLogData['agent_name'] = $agent_name;
        $addCompanyPaymentAgentLogData['weixin'] = $weixin;
        $addCompanyPaymentAgentLogData['bank_account'] = $bank_account;
        $addCompanyPaymentAgentLogData['bank_name'] = $bank_name;
        $addCompanyPaymentAgentLogData['profit_year'] = $profit_year;
        $addCompanyPaymentAgentLogData['profit_month'] = $profit_month;
        $addCompanyPaymentAgentLogData['year'] = date('Y');
        $addCompanyPaymentAgentLogData['month'] = date('m');
        $addCompanyPaymentAgentLogData['day'] = date('d');
        $addCompanyPaymentAgentLogData['status'] = 2;
        $addCompanyPaymentAgentLogData['money'] = $pay_money;
        
        //添加付款日志
        $result = $this->CompanyPaymentAgentLog->addData($addCompanyPaymentAgentLogData);
        
        return $result;
    }
    
    /**
     * 查询代理支付是否成功,不成功返回支付总金额
     * @param type $agentMonthProfitInfo
     * @return boolean
     */
    private function checkWeixinPay($agentMonthProfitInfo) {
        //分润的信息
        $agent_id = $agentMonthProfitInfo['agent_id'];
        $profit_year = $agentMonthProfitInfo['year'];
        $profit_month = $agentMonthProfitInfo['month'];
        $company_profit_total = $agentMonthProfitInfo['company_profit'];
        
        $sel_pay_agent_log_where['profit_year'] = $profit_year;
        $sel_pay_agent_log_where['profit_month'] = $profit_month;
        $sel_pay_agent_log_where['status'] = 1; //付款是否成功:1:否,2:是
        $sel_pay_agent_log_where['agent_id'] = $agent_id;
        $payAgentLogList = $this->CompanyPaymentAgentLog->getAllList($sel_pay_agent_log_where,'',array('field'=>array('id','partner_trade_no'),'is_opposite'=>false));
        
        if($payAgentLogList){
            foreach ($payAgentLogList as $pav) {
                $check_pay_result = $this->Wechat->getTransfersInfo($pav['partner_trade_no']);
                
                if($check_pay_result){
                    $editCompanyPaymentAgentLogData['payment_no'] = $check_pay_result['detail_id'];
                    $editCompanyPaymentAgentLogData['payment_time'] = $check_pay_result['transfer_time'];
                    $editCompanyPaymentAgentLogData['status'] = 2;

                    //支付成功修改订单信息
                    $this->CompanyPaymentAgentLog->editData(array('id'=>$pav['id']),$editCompanyPaymentAgentLogData);
                }
            }
        }
        
        //查询支付成功的总金额
        $agent_pay_sucess_where['profit_year'] = $profit_year;
        $agent_pay_sucess_where['profit_month'] = $profit_month;
        $agent_pay_sucess_where['agent_id'] = $agent_id;
        $agent_pay_sucess_where['status'] = 2;
        $agentPaySucessTotal = $this->CompanyPaymentAgentLog->getSum($agent_pay_sucess_where,'money');
        $money = $company_profit_total - $agentPaySucessTotal;
        
        if($money == 0){
            return TRUE;
        }
        
        return $money;
    }
    
    
    /**
     * 微信企业支付
     * @param type $admin_id  管理员id
     * @param type $admin_name 管理员名字
     * @param type $agentMonthProfitInfo 分润月份信息
     * @param type $agentInfo 代理信息
     * @param type $pay_money 支付金额
     * @param type $desc 付款描述
     * @param type $year 年份
     * @param type $month 月份
     * @param type $day 日
     * @param type $dateTime 当前时间
     * @return boolean
     */
    private function weixinPay($admin_id,$admin_name,$agentMonthProfitInfo,$agentInfo,$pay_money,$desc,$year,$month,$day,$dateTime) {
        $agent_id = $agentMonthProfitInfo['agent_id'];
        $profit_year = $agentMonthProfitInfo['year'];
        $profit_month = $agentMonthProfitInfo['month'];
        
        $agent_name = $agentInfo['name'];
        $weixin = $agentInfo['weixin'];
        $bank_account = $agentInfo['bank_account'];
        $bank_name = $agentInfo['bank_name'];
        $agent_true_name = $agentInfo['agent_true_name'];
        $openid = $agentInfo['openid'];
        $bank_name = $bank_name ? $bank_name : '微信支付';
        $bank_account = $bank_account ? $bank_account : $openid;
        $agent_true_name = $agent_true_name ? $agent_true_name : $agent_name;
        
        $addCompanyPaymentAgentLogData['admin_id'] = $admin_id;
        $addCompanyPaymentAgentLogData['admin_name'] = $admin_name;
        $addCompanyPaymentAgentLogData['openid'] = $openid;
        $addCompanyPaymentAgentLogData['agent_id'] = $agent_id;
        $addCompanyPaymentAgentLogData['agent_name'] = $agent_name;
        $addCompanyPaymentAgentLogData['weixin'] = $weixin;
        $addCompanyPaymentAgentLogData['bank_account'] = $bank_account;
        $addCompanyPaymentAgentLogData['bank_name'] = $bank_name;
        $addCompanyPaymentAgentLogData['profit_year'] = $profit_year;
        $addCompanyPaymentAgentLogData['profit_month'] = $profit_month;
        $addCompanyPaymentAgentLogData['year'] = $year;
        $addCompanyPaymentAgentLogData['month'] = $month;
        $addCompanyPaymentAgentLogData['day'] = $day;
        $addCompanyPaymentAgentLogData['add_time'] = $dateTime;
        $addCompanyPaymentAgentLogData['status'] = 1;
        $addCompanyPaymentAgentLogData['money'] = $pay_money;
        $addCompanyPaymentAgentLogData['partner_trade_no'] = $this->Wechat->createMchBillNo();

        //添加付款日志
        $companyPaymentAgentLogId = $this->CompanyPaymentAgentLog->addData($addCompanyPaymentAgentLogData);

        if($companyPaymentAgentLogId && $openid){
            //企业付款
            $payResult = $this->Wechat->payTransfers($openid,$agent_true_name,$pay_money,$desc);

            //付款成功
            if($payResult){
                $editCompanyPaymentAgentLogData['payment_no'] = $payResult['payment_no'];
                $editCompanyPaymentAgentLogData['payment_time'] = $payResult['payment_time'];
                $editCompanyPaymentAgentLogData['status'] = 2;

                //支付成功修改订单信息
                $this->CompanyPaymentAgentLog->editData(array('id'=>$companyPaymentAgentLogId),$editCompanyPaymentAgentLogData);

                //修改公司报表统计
                self::editCompanyReport($pay_money);

                return TRUE;
            }
        }
        
        return FALSE;
    }


    /**
     * 实例化微信类
     * @return \Org\Util\Wechat
     */
    private function newWechat() {
        $options['token'] = C('WX_TOKEN');
        $options['appid'] = C('WX_APPID');
        $options['secret'] = C('WX_APPSECRET');
        $options['payKey'] = C('WX_PAY_KEY');
        $options['mch_id'] = C('WX_MCH_ID');
      
        $Wechat = new \Org\Util\Wechat($options);
        
        return $Wechat;
    }

    /**
     * 修改代理分润信息
     * 
     * @param type $month_profit_id  分润月份id
     * @param type $agent_id  代理id
     * @param type $profit_year 分润年份
     * @param type $profit_month 分润月份
     * @return boolean
     */
    private function editAgentProfitInfo($month_profit_id,$agent_id,$profit_year,$profit_month) {
        
        $edit_company_month_profit_where['id'] = $month_profit_id;
        $editCompanyMonthProfitData['is_profit'] = 1;
        
        $monthResult = $this->AgentMonthProfit->editData($edit_company_month_profit_where,$editCompanyMonthProfitData);

        $edit_agent_profit_log_where['profit_agent_id'] = $agent_id;
        $edit_agent_profit_log_where['year'] = $profit_year;
        $edit_agent_profit_log_where['month'] = $profit_month;
        $editAgentProfitLogData['is_profit'] = 1;
        
        $logResult = $this->AgentProfitLog->editData($edit_agent_profit_log_where,$editAgentProfitLogData);
        
        if($monthResult && $logResult){
            return TRUE;
        }
        
        return FALSE;
    }


    /**
     * 修改公司报表
     * @param type $money  //支付金额
     * @return boolean
     */
    private function editCompanyReport($money) {
        
        if($money <= 0){
            return FALSE;
        }
        
        $get_where['status'] = 2; //状态:1:不可更改,2:可更改,3:最新
        $edit_id = $this->CompanyReportsLog->where($get_where)->getField('id');
        
        if(empty($edit_id)){
            $get_where['status'] = 3; //状态:1:不可更改,2:可更改,3:最新
            $edit_id = $this->CompanyReportsLog->where($get_where)->getField('id');
            
            $editData['status'] = 2; //如果状态为3时更改为2
        }
        
        if($edit_id){
            
            $edit_where['id'] = $edit_id;
            
            $editData['real_profit'] = array('exp','real_profit + '.$money); //每月实际支出利润
            $editData['not_profit'] = array('exp','not_profit - '.$money); //每月未支出利润
            $editData['all_real_profit'] = array('exp','all_real_profit + '.$money); //实际总支出
            $editData['all_surplus_profit'] = array('exp','all_surplus_profit - '.$money); //剩余总利润
           
            $result = $this->CompanyReportsLog->editData($edit_where,$editData);
            
            return $result;
        }
        
        return false;
    }

}