<?php
namespace Mobile\Controller;
use Think\Controller;
/**
 * 代理公司分润管理
 */
class ProfitManageController extends CommonController {
    
    //裂变分润
    public function index(){
        
        $profit_type = I('type'); //分润的类型: 1:下级,2:推荐,3:买断首次分润
        
        $MEMBER_LEVEL = C('MEMBER_LEVEL'); //代理等级
        $this->assign('MEMBER_LEVEL', $MEMBER_LEVEL);
        
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        $page = I('page');
        $limit = 100;
        $order = 'add_time desc';
        
        $where['profit_agent_id'] = $member_id;
        $where['profit_type'] = $profit_type;
        $where['is_profit'] = 2; //是否已分润:1:已分润,2:未分润
        
        $AgentProfitLog = D('AgentProfitLog');
        
        
        $list = $AgentProfitLog->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        $where['is_refund'] = 1; //是否退货: 1:否,2:是
        $count = $AgentProfitLog->getSum($where,'profit_total_money');
        $this->assign('count',$count ? $count : 0);
        
        $this->assign('list',$list);
        $this->assign('type',$profit_type);
        $this->display();
    }
    
    //结算记录
    public function statementsLog() {
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        $year = date('Y');
        $month = date('m');
        
        $this->assign('year', $year);
        
        $AgentMonthProfit = D('AgentMonthProfit');
        $order='month';
        
        $where['is_profit'] = 1; //是否已分润:1:已分润,2:未分润
        $where['year'] = $year;
        $where['agent_id'] = $member_id;
        
        $list = $AgentMonthProfit->getAllList($where,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        $new_list = array();
        
        for($m=1;$m<$month;$m++){
            $new_list[] = $list[$m-1]['company_profit'] ? $list[$m-1]['company_profit'] : 0;
        }
     
        $new_list = implode(',', $new_list);
        $this->assign('new_list',$new_list);
        $this->display('statementsLog');
    }
    
    //结算分润明细
    public function statementsLogList() {
        $month_name = I('month'); 
        $month = trim(str_replace('月', ' ', $month_name));
        
        $year = date('Y');
        
        $MEMBER_LEVEL = C('MEMBER_LEVEL'); //代理等级
        $this->assign('MEMBER_LEVEL', $MEMBER_LEVEL);
        
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        $page = I('page');
        $limit = 100;
        $order = 'add_time desc';
        
        $where['profit_agent_id'] = $member_id;
        $where['is_profit'] = 1; //是否已分润:1:已分润,2:未分润
        $where['year'] = $year;
        $where['month'] = $month;
        
        $AgentProfitLog = D('AgentProfitLog');
        
        
        $list = $AgentProfitLog->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        $where['is_refund'] = 1; //是否退货: 1:否,2:是
        $count = $AgentProfitLog->getSum($where,'profit_total_money');
        $this->assign('count',$count ? $count : 0);
        
        $this->assign('list',$list);
        
        $this->display('statementsLogList');
    }
    
    //我的营收
    public function saleProfit() {
        
        $name = I('name');
        
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        if($name){
            $where['member_name'] = $name;
        }
        
        $where['admin_id'] = $member_id;
        
        $page = I('page');
        $limit = 100;
        $order = 'add_time desc';
        
        $OrderInfo = D('OrderInfo');
        
        $list = $OrderInfo->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        $count = $OrderInfo->getSum($where,'goods_total_stock');
        $this->assign('count',$count ? $count : 0);
        
        $this->assign('list',$list);
        $this->assign('name',$name);
        $this->display('saleProfit');
    }
    
}