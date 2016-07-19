<?php
namespace Admin\Controller;
use Think\Controller;
class FinanceController extends CommonController {
    
    protected $CompanyReportsLog;
    protected $AgentProfitLog;
    protected $Agent_lv_list;
    function __construct(){ //定义构造函数
        //继承父类
        parent::__construct();
        $this->CompanyReportsLog=D('CompanyReportsLog');
        $this->AgentProfitLog=D('AgentProfitLog');
        $this->Agent_lv_list=C('MEMBER_LEVEL');
    }
    
    public function index(){
        //进行跳转到对应页面:显示对应的数据
        $flag = I('flag');
        $year = I('year');
        $month = I('month');
        $dateYear = date('Y');
        
        $search_value = I('search_value');
        $search_field = I('search_field');
        if($search_field && $search_value){
            $search['search_field'] = $search_field;
            $search['search_value'] = $search_value;
            $where['_string']= ' agent_id IN(SELECT agentId FROM `agent` WHERE '.$search_field.' = "'.$search_value.'")';
            
            $this->assign('search', $search);
        }
         
         if($year && $month){
            //条件内容
            $where['year']=$year;
            $where['month']=$month;
         }else{
            //获取时间戳
            $time = time();
            //对时间戳进行格式化
            $where['year']=date('Y',$time);
            $where['month']=date('m',$time);
         }
         //每页进行10条
         $limit=10;
         $order='add_time desc';
         $page = I('p',1);
         //总的条件为：未分润  is_profit=2
          if($flag == 2){
             $where['is_profit']=2;     
          }else{
              $where['is_profit']=1; 
          }
          
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
                $agentModel=$Agent->getDetail($where=array('agentId'=>$agenId),$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
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
        $this->assign('flag', $flag);
        $this->assign('year', $year);
        $this->assign('month', $month);
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('year',$year);
        $this->assign('count',$count);
        
        //输出查询年份
        $year_html = '<option value="2016">2016</option>';
        for($y=2016;$y<$dateYear;$y++){
            $year_html .= '<option value="'.$y.'">'.$y.'</option>';
        }
        
        $this->assign('year_html',$year_html);
      
        $this->assign('page',$show);// 赋值分页输出
       
        $this->display('index');
    }
    
    //数据统计页面
    public function operate(){
        $year = I('year');
        $page = I('p',1);
        if($year){
            $where['year']=$year;
        }else{
            $where=array();
        }
        //每页进行10条
        $limit=10;
        $count      = $this->CompanyReportsLog->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $list=$this->CompanyReportsLog->getList($where,$limit,$page,$order='',$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($year){
             $this->assign('year','<option value='.$year.'>'.$year.'</option>');
        }else{
             $this->assign('year','<option value="">年份</option>');
        }

        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->display('operate');
    }
    
    //查看对应的详情
    public function  showAllDetail(){
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
        $limit=10;
        $count      = $this->AgentProfitLog->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $list=$this->AgentProfitLog->getList($where,$limit,$page,$order='',$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
        //$this->Agent_lv_list
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
//        if($year){
//             $this->assign('year','<option value='.$year.'>'.$year.'</option>');
//        }else{
//             $this->assign('year','<option value="">年份</option>');
//        }
//        if($month){
//             $this->assign('month','<option value='.$month.'>'.$month.'</option>');
//        }else{
//              $this->assign('month','<option value="">月份</option>');
//        } 
        
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
    
    //审核支付
    public function payMoney() {
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
        $CompanyPaymentAgentLog = D('CompanyPaymentAgentLog');
        $AgentMonthProfit = D('AgentMonthProfit');
        $AgentProfitLog = D('AgentProfitLog');
        
        //获取代理每月分润信息
        $agent_month_profit_where['id'] = $month_profit_id;
        $agentMonthProfitInfo = $AgentMonthProfit->getDetail($agent_month_profit_where);
        
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
        $agent_where['agnetId'] = $agent_id;
        $agentInfo = $Agent->where($agent_where)->find();
        
        //只有审核通过的代理才能返利
        if($agentInfo['stat'] != 1){
            $return['msg'] = '代理不是已审核状态,不能返利!';
            $this->ajaxReturn($return,'json');
        }
        
        $agent_name = $agentInfo['name'];
        $weixin = $agentInfo['weixin'];
        $bank_account = $agentInfo['bank_account'];
        $bank_name = $agentInfo['bank_name'];
        $agent_true_name = $agentInfo['agent_true_name'];
        $openid = $agentInfo['openid'];
        $bank_name = $bank_name ? $bank_name : '微信支付';
        $bank_account = $bank_account ? $bank_account : $openid;
        $agent_true_name = $agent_true_name ? $agent_true_name : $agent_name;
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $dateTime = date('Y-m-d H:i:s');
        
        //统计分润日志总分润金额
        $agent_profit_log_where['year'] = $profit_year;
        $agent_profit_log_where['month'] = $profit_month;
        $agent_profit_log_where['profit_agent_id'] = $agent_id;
        $agentProfitLogTotal = $AgentProfitLog->getSum($agent_profit_log_where,'profit_total_money');
        $agentProfitLogTotal = $agentProfitLogTotal ? $agentProfitLogTotal : 0;
        
        //判断分润日志总金额是否与月份统计总金额一致
        if($agentProfitLogTotal != $company_profit_total){
            $return['msg'] = '分润日志总金额与月份统计总金额一致!';
            $this->ajaxReturn($return,'json');
        }
        
        $options['token'] = C('WX_TOKEN');
        $options['appid'] = C('WX_APPID');
        $options['secret'] = C('WX_APPSECRET');
        $options['payKey'] = C('WX_PAY_KEY');
        $options['mch_id'] = C('WX_MCH_ID');
      
        $Wechat = new \Org\Util\Wechat($options);
        
        //查询支付日志,看下是否有支付失败的订单,有就修改支付状态
        $sel_pay_agent_log_where['profit_year'] = $profit_year;
        $sel_pay_agent_log_where['profit_month'] = $profit_month;
        $sel_pay_agent_log_where['status'] = 1; //付款是否成功:1:否,2:是
        $sel_pay_agent_log_where['agent_id'] = $agent_id;
        $payAgentLogList = $CompanyPaymentAgentLog->getAllList($sel_pay_agent_log_where,'',array('field'=>array('id','partner_trade_no'),'is_opposite'=>false));
        
        if($payAgentLogList){
            foreach ($payAgentLogList as $pav) {
                $check_pay_result = $Wechat->getTransfersInfo($pav['partner_trade_no']);
                
                if($check_pay_result){
                    $editCompanyPaymentAgentLogData['payment_no'] = $check_pay_result['detail_id'];
                    $editCompanyPaymentAgentLogData['payment_time'] = $check_pay_result['transfer_time'];
                    $editCompanyPaymentAgentLogData['status'] = 2;

                    //支付成功修改订单信息
                    $CompanyPaymentAgentLog->editData(array('id'=>$pav['id']),$editCompanyPaymentAgentLogData);
                }
            }
        }
        
        //查询支付成功的总金额
        $agent_pay_sucess_where['profit_year'] = $profit_year;
        $agent_pay_sucess_where['profit_month'] = $profit_month;
        $agent_pay_sucess_where['agent_id'] = $agent_id;
        $agent_pay_sucess_where['status'] = 2;
        $agentPaySucessTotal = $CompanyPaymentAgentLog->getSum($agent_pay_sucess_where,'money');
        $money = $company_profit_total - $agentPaySucessTotal;
        
        if($money == 0){
            //修改代理分润信息
            $edit_company_month_profit_where['id'] = $month_profit_id;
            $editCompanyMonthProfitData['is_profit'] = 1;
            $AgentMonthProfit->editData($edit_company_month_profit_where,$editCompanyMonthProfitData);
            
            $edit_agent_profit_log_where['profit_agent_id'] = $agent_id;
            $edit_agent_profit_log_where['year'] = $profit_year;
            $edit_agent_profit_log_where['month'] = $profit_month;
            $editAgentProfitLogData['is_profit'] = 1;
            $AgentProfitLog->editData($edit_agent_profit_log_where,$editAgentProfitLogData);
            
            $return['status'] = 1;
            $return['msg'] = '支付完成!';
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
        $paySucessTotal = $CompanyPaymentAgentLog->getSum($pay_sucess_total_where,'money');
        $paySucessTotal = $paySucessTotal ? $paySucessTotal : 0;
        
        if($paySucessTotal >= 1000000){
            $return['msg'] = '一日之内支付不能多于100万!';
            $this->ajaxReturn($return,'json');
        }
        
        $mod_money = 20000; //微信限制单笔最高金额
        
        $desc = '优利宝推广佣金测试';

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
        
        if($money > $mod_money){
            $mod_sup_money = $money%$mod_money;
            $sup_step = ceil($money/$mod_money);
            $pay_money = $mod_money;
        }else{
            $sup_step = 1;
            $pay_money = $money;
        }
        
        $pay_total_money = 0; //累计支付总金额
        
        for($pi=1;$pi <= $sup_step;$pi++){
            //如果付款金额大于微信最大金额时取微信最大金额,最后再去余下的金额支付
            if($pi > 1 && $sup_step == $pi){
                $pay_money = $mod_sup_money;
            }else{
                $pay_money = $mod_money;
            }
            
            $pay_total_money += $pay_money;
            
            if($pay_money > 0){
                $addCompanyPaymentAgentLogData['money'] = $pay_money;
                $addCompanyPaymentAgentLogData['partner_trade_no'] = $Wechat->createMchBillNo();

                //添加付款日志
                $companyPaymentAgentLogId = $CompanyPaymentAgentLog->addData($addCompanyPaymentAgentLogData);

                if($companyPaymentAgentLogId){
                    //企业付款
                    $payResult = $Wechat->payTransfers($openid,$agent_true_name,$pay_money,$desc);

                    //付款成功
                    if($payResult){
                        $editCompanyPaymentAgentLogData['payment_no'] = $payResult['payment_no'];
                        $editCompanyPaymentAgentLogData['payment_time'] = $payResult['payment_time'];
                        $editCompanyPaymentAgentLogData['status'] = 2;

                        //支付成功修改订单信息
                        $CompanyPaymentAgentLog->editData(array('id'=>$companyPaymentAgentLogId),$editCompanyPaymentAgentLogData);

                        $return = array('status'=>1,'msg'=>'付款成功!','result'=>'');
                    }else{
                        $return['msg'] = $Wechat->getError();
                    }
                }
            }
        }
        
        if($money == $pay_total_money){
            //修改代理分润信息
            $edit_company_month_profit_where['id'] = $month_profit_id;
            $editCompanyMonthProfitData['is_profit'] = 1;
            $AgentMonthProfit->editData($edit_company_month_profit_where,$editCompanyMonthProfitData);
            
            $edit_agent_profit_log_where['profit_agent_id'] = $agent_id;
            $edit_agent_profit_log_where['year'] = $profit_year;
            $edit_agent_profit_log_where['month'] = $profit_month;
            $editAgentProfitLogData['is_profit'] = 1;
            $AgentProfitLog->editData($edit_agent_profit_log_where,$editAgentProfitLogData);
            
            $return['status'] = 1;
            $return['msg'] = '支付完成!';
           
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //审核
    public function shOneData(){
        $id = I('id');
        exit('暂时不能审核');
        //实体模型对象
        $AgentMonthProfit = D('AgentMonthProfit');
        $Agent = D('Agent');
        $AgentProfitLog=D('AgentProfitLog');
        //开启事务
//        $Agent->startTrans();
        //进行用户单表分润记录表的操作[根据id来查找对应记录]
        $model=$AgentMonthProfit->getDetail($where=array('id'=>$id),$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null),$order_by='');
        //根据对应的对象的id来获取的对应的记录【条件为：agentId  year  month】
        if($model){
            //个人总记录需要改对应的标示
            $model['is_profit']=1;
            $AgentMonthProfit->editData($Am=array('id'=>$id),$model);
            //进行把对应的分润记录拿出来[条件：年  月  代理Id]
            $Lwhere['year']=$model['year'];
            $Lwhere['month']=$model['month'];
            $Lwhere['is_profit']=2;
            //是否退货
            $Lwhere['is_refund']=1;
            $Lwhere['profit_agent_id']=$model['agent_id'];
            //获取全部数据的列表
            $list=$AgentProfitLog->getAllList($Lwhere,$order='',$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            //进行对代理分润记录表的记录的集合进行全部查出
           if($list){
            foreach ($list as $k => $v) {
                $v['is_profit']=1;
                //进行标示的修改
                $AgentProfitLog->editData($Pwhere=array('id'=>$v['id']),$v);
            }
            //在财务分润表当中进行添加与修改对应的值【这是公司的财务的总表(条件):year  month】
            $this->doCompanyAccountData( $model['company_profit']);
//            $Agent->commit();
        } 
        }
//        $this->display('index');
        $this->redirect('index');
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
   
    
    //公司的总账表的修改
    public function doCompanyAccountData($money){
         
         //$Nwhere['_string'] = 'status=2 OR status=3';   
         $Nwhere['status']=2;
         //查出对应的数据[状态为:2  3标示]
         $report=$this->CompanyReportsLog->getDetail($Nwhere,$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
         if($report){
             //剩余总利润
             $report['all_surplus_profit']=$report['all_surplus_profit']-$money;
             //实际总支出
             $report['all_real_profit']=$report['all_real_profit']+$money;
             //每月实际支出利润
             $report['real_profit']=$report['real_profit']+$money;
             //每月未支出利润[每月总支出-每月实际支出]
             $report['not_profit']=$report['total_profit']-$report['real_profit'];
             $dateTime=date("Y-m-d H:i:s");
             $report['edit_time']=$dateTime;             
             $this->CompanyReportsLog->editData($where=array('id'=>$report['id']),$report);
         }
    }
        
        
        
    
   
    
}