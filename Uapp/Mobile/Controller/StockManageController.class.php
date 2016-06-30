<?php
namespace Mobile\Controller;
use Think\Controller;
/**
 * 库存管理
 */
class StockManageController extends CommonController {
    
    //我的库存
    public function index(){
       
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        $page = I('page');
        $limit = 100;
        $order = 'gs.add_time desc';
        $join='gs LEFT JOIN goods g ON gs.goods_id = g.id';
        
        $where['gs.agent_id'] = $member_id;
        
        $AgentGoodsStockRale = D('AgentGoodsStockRale');
        
        
        $list = $AgentGoodsStockRale->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);
      
        $this->assign('list',$list);
        $this->display();
    }
    
    //进库记录
    public function buyStuckLog() {
        $data = $this->stockLogList(2);
        
        $this->assign('list',$data['list']);
        
        $this->display('buyStuckLog');
    }
    
    //出库记录
     public function saleStuckLog() {
         
        $data = $this->stockLogList(1);
        
        $this->assign('list',$data['list']);
               
        $this->display('saleStuckLog');
    }
    
    //库存记录列表
    public function stockLogList($type) {
        
        if(empty($type)){
            return FALSE;
        }
        
        $member_info = $this->memberInfo();
        $member_id = $member_info['agentid'];
        
        if($type == 1){ //进库类型:1:出库, 2:进库,
            $join=' og LEFT JOIN agent a ON og.member_id = a.agentId';
            $where['og.admin_id'] = $member_id;
        }else{
            $join='og LEFT JOIN agent a ON og.admin_id = a.agentId';
            $where['og.member_id'] = $member_id;
        }
        
        $page = I('page');
        $limit = 100;
        $order = 'og.add_time desc';
        
        
        $OrderGoods = D('OrderGoods');
        
        
        $return['list'] = $OrderGoods->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);
        
        return $return;
    }
   
    
}