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
          if($flag=="0"){
             $where['is_profit']=2;     
          }else{
              $where['is_profit']=1; 
          }
//          dump($flag=="0");
//          dump($flag);
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
                $list[$k]['lv_name'] = $agent_lv_list[$agentModel['star']]['name'];
            }
        }
        //先显示的全部的数据先
//      $Alllist= $AgentMonthProfit->getAllList();
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('year',$year);
        $this->assign('count',$count);
        
        if($year){
             $this->assign('year','<option value='.$year.'>'.$year.'</option>');
        }else{
             $this->assign('year','<option value="">年份</option>');
        }
        if($month){
             $this->assign('month','<option value='.$month.'>'.$month.'</option>');
        }else{
              $this->assign('month','<option value="">月份</option>');
        }
        $this->assign('page',$show);// 赋值分页输出
        if($flag){
             $this->display('list'); 
        }else{
             $this->display('index');
        }
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
//        dump($count);
//        dump($list);
//        dump($year);
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
         $flag = I('flag');
         $page = I('p',1);
         if($year && $month){
            //条件内容
            $where['year']=$year;
            $where['month']=$month;
         }
         //增加对应的选择条件
         if($is_profile){
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
        if($year){
             $this->assign('year','<option value='.$year.'>'.$year.'</option>');
        }else{
             $this->assign('year','<option value="">年份</option>');
        }
        if($month){
             $this->assign('month','<option value='.$month.'>'.$month.'</option>');
        }else{
              $this->assign('month','<option value="">月份</option>');
        } 
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('flag',$flag);
        $this->assign('count',$count);
        $this->display('detail');
    }




    public function shOneData(){
        $id = I('id');
        
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