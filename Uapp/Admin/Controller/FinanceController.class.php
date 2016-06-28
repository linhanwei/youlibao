<?php
namespace Admin\Controller;
use Think\Controller;
class FinanceController extends CommonController {
    
    public function index(){
        
        //进行跳转到对应页面:显示对应的数据
         $year = I('year');
         $month = I('month');
         
         if($year && $month){
              //条件内容
            $where['year']=$year;
            $where['month']=$month;
         }else{
            $where['year']=2016;
            $where['month']=6;
         }
        
         //每页进行10条
         $limit=10;
         $order='add_time desc';
         
         $page = I('p',1);
         
         //获取的数据库模型的对象
         $AgentMonthProfit = D('AgentMonthProfit');
         $Agent = D('Agent');
         $agent_lv_list = C('MEMBER_LEVEL');
         
         $count      = $AgentMonthProfit->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
         $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
         $show       = $Page->show();// 分页显示输出
//         $Page=round($count/$limit);
//           $Page=1;
//         dump($page).'---------------<';
//         echo  $count;
//         die();
//         $show       = $Page->show();// 分页显示输出
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
//         dump($list);
//         die();
        //先显示的全部的数据先
//        $Alllist= $AgentMonthProfit->getAllList();
//        $this->assign('mlist',$Alllist);
        $this->assign('count',$count);
        $this->assign('list',$list);
        $this->assign('year',$year);
        if($year){
             $this->assign('year','<option value='.$year.'>'.$year.'</option>');
        }else{
             $this->assign('year','<option value="">年份</option>');
        }
//        $this->assign('month',$month);
        if($month){
             $this->assign('month','<option value='.$month.'>'.$month.'</option>');
        }else{
              $this->assign('month','<option value="">月份</option>');
        }
        $this->assign('page',$show);// 赋值分页输出
        $this->display('index');
    }
    
   
    
}