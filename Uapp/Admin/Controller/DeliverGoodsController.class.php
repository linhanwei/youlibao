<?php
/*
 * 出库管理
 */
namespace Admin\Controller;
use Think\Controller;
class DeliverGoodsController extends CommonController {
    
    //商品列表
    public function index(){
        
        $star = I('star');
        $web_type = I('web_type',2);
        $search_field = I('search_field');
        $search_value = I('search_value');
        $page = I('p');
        $limit = 10;
       
        $where = array();
        $star ? $where['star'] = $star : '';
        $search_field && $search_value ? $where[$search_field] = $search_value : '';
       
        if($search_field == 'code' && $search_value){
            $OrderGoods = D('OrderGoods');
            $og_where['code'] = $search_value;
            $order_goods_list = $OrderGoods->distinct(true)->where($og_where)->field('order_sn')->select();
            
            if(empty($order_goods_list)){
                $rog_where['code'] = array('in',$this->getLabelCode($search_value));
                $order_goods_list = $OrderGoods->distinct(true)->where($rog_where)->field('order_sn')->select();
            }
            
            $where['order_sn'] = 0;
            
            if($order_goods_list){
                foreach ($order_goods_list as $gk => $gv) {
                    $order_sn_list[] = $gv['order_sn'];
                }
                
                $where['order_sn'] = array('in',$order_sn_list);
            }
            
        }
        
        $OrderInfo = D('OrderInfo');
        $order_by = 'order_id DESC';
            
        $count      = $OrderInfo->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $OrderInfo->getList($where,$limit,$page,$order_by,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
      
        $this->assign('list',$list);  //赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('list_count',$count);
        
        //代理等级
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        //搜索条件
        $search['search_field'] = $search_field;
        $search['search_value'] = $search_value;
        $search['star'] = $star;
        $search['web_type'] = $web_type;
        $this->assign('search',$search);
       
        $this->display();
    }
    
    public function getLabelCode($code) {
        $code_lenth = strlen($code);
        if($code_lenth == 10){
            
            //获取号码的类型 前缀类型:1:大标,2:小标:3:防伪标
            $new_code = substr($code,0,3);  
           
            //设置查询条件
            switch ($new_code) {
                case 700:
                    $code_type = 1;
                    $where['max_code'] = $code;
                    break;
                case 100:
                    $code_type = 2;
                    $where['min_code'] = $code;
                    $LabelCode = D('LabelCode');
                    $label_info = $LabelCode->getDetail($where,array('field'=>array('max_code','min_code','middle_code'),'is_opposite'=>false));
                    break;
                default :
                    $code_type = 3;
                    $where['security_code'] = 0;
                    break;
            }
        }else{
             //获取号码的类型 前缀类型:1:大标,2:小标:3:防伪标,4:中标
            $code_type = judge_code_type($code);
            
            //设置查询条件
            switch ($code_type) {
                case 1:
                    $where['max_code'] = $code;
                    break;
                case 2:
                    $where['min_code'] = $code;
                    $LabelCode = D('LabelCode');
                    $label_info = $LabelCode->getDetail($where,array('field'=>array('max_code','min_code','middle_code'),'is_opposite'=>false));
                    break;
                case 4:
                    $where['middle_code'] = $code;
                    $LabelCode = D('LabelCode');
                    $label_info = $LabelCode->getDetail($where,array('field'=>array('max_code','min_code','middle_code'),'is_opposite'=>false));
                    break;
                default :
                    $where['security_code'] = 0;
                    break;
            }
        }
        
        if($label_info){
            
            return $label_info['middle_code'] ? array($label_info['max_code'],$label_info['middle_code'],$label_info['min_code']) : array($label_info['max_code'],$label_info['min_code']);
        }else{
            return $code;
        }
    }
    
    //发货详情
    public function showDetail() {
        $order_id = I('aid');
        $web_type = I('web_type');
        
        if(empty($order_id)){
            dump('请选择发货单');
            return FALSE;
        }
//        
//        if(empty($web_type)){
//            dump('请选择站点');
//            return FALSE;
//        }
//        
        $cache_info = S(C('ORDER_GOODS').$order_id.'_'.$web_type);
        if(empty($cache_info)){
           
            $OrderGoods = D('OrderGoods');
            $OrderInfo = D('OrderInfo');
            
            $where['order_id'] = $order_id;
            $list = $OrderGoods->getAllList($where,'',array('field'=>array(),'is_opposite'=>false));
            
            if($list){
                foreach ($list as $k => $v) {
                    $goods_id = $v['goods_id'];
                    $goods_info = $this->getGoods($goods_id);
                    $list[$k]['index_pic'] = $goods_info['index_pic'];
                }
            }
          
            $order_where['order_id'] = $order_id;
            $order_info = $OrderInfo->getDetail($order_where,array('field'=>array(),'is_opposite'=>false));
            
            if($list && $order_info){
                $order_info['add_date_time'] = date('Y-m-d H:i:s',$order_info['add_time']);
                $cache_info = array('list'=>$list,'order_info'=>$order_info);
                S(C('ORDER_GOODS').$order_sn.'_'.$web_type,  serialize($cache_info));
            }
        }else{
            $cache_info = unserialize($cache_info);
        }
        
        //获取收货人信息
        $admin_info = $this->getAgent($cache_info['order_info']['admin_id']);
        $this->assign('admin_info', $admin_info);
      
        //获取发货人信息
        $member_info = $this->getAgent($cache_info['order_info']['member_id']);
        $this->assign('member_info', $member_info);
       
        $this->assign('empty','<span class="empty">没有查询到订单</span>');
        $this->assign('list',$cache_info['list']);
        $this->assign('order_info',$cache_info['order_info']);
        $this->display('showDetail');
    }
    
    //获取商品
    public function getGoods($goods_id) {
        if(empty($goods_id)){
            return false;
        }
        
        $goods_info = S(C('GOODS_INFO').$goods_id);
        
        if(empty($goods_info)){
            $where['id'] = $goods_id;
            $Goods = D('Goods');
            $goods_info = $Goods->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            if($goods_info){
                S(C('GOODS_INFO').$goods_id,  serialize($goods_info));
            }
        }else{
            $goods_info = unserialize($goods_info);
        }
        
        return $goods_info;
    }
    
    //删除发货订单
    public function delOrder() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        $order_id = I('order_id');
        
        if(empty($order_id)){
            $return['msg'] = '请选择订单';
            $this->ajaxReturn($return,'json');
        }
        
        $OrderInfo = D('OrderInfo');
        
        $where['order_id'] = $order_id;
        
        //获取订单信息
        $order_info = $OrderInfo->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if(empty($order_info)){
            $return['msg'] = '该订单不存在';
            $this->ajaxReturn($return,'json');
        }
        
        if($order_info['order_status'] == 2){
            $return['msg'] = '该订单已经退货!';
            $this->ajaxReturn($return,'json');
        }
        
        $order_info_time = strtotime($order_info['add_time']);
        $order_year = date('Y',$order_info_time);
        $order_month = date('m',$order_info_time);
        $admin_id = $order_info['admin_id'];
        $admin_lv = $order_info['admin_lv'];
        $member_id = $order_info['member_id'];
        $order_sale_all_money = $order_info['order_total_money'];//订单销售总金额
        $order_sale_all_profit = $order_info['order_total_profit'];//订单销售总利润
        $order_goods_total_stock = $order_info['goods_total_stock'];//订单销售总库存
        
        $OrderGoods = D('OrderGoods');
        
        //如果该订单中有商品已经退货,该订单就不能再退货
        $get_order_goods_count_where['order_id'] = $order_id;
        $get_order_goods_count_where['is_refund'] = 2;
        $getOrderGoodsCount = $OrderGoods->getCount($get_order_goods_count_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($getOrderGoodsCount > 0){
            $return['msg'] = '该订单已经有退货商品,不能再退货,您只能进去单个退货!';
            $this->ajaxReturn($return,'json');
        }
            
        //如果该订单代理已经分润,不能再退货
        $AgentMonthProfit = D('AgentMonthProfit');
            
        $agent_month_profit_count_where['year'] = $order_year;
        $agent_month_profit_count_where['month'] = $order_month;
        $agent_month_profit_count_where['agent_id'] = $member_id;
        $agent_month_profit_count_where['is_profit'] = 1;
        $agentMonthProfitCount = $AgentMonthProfit->getCount($agent_month_profit_count_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($agentMonthProfitCount > 0){
            $return['msg'] = '该订单已经分润,不能再退货!';
            $this->ajaxReturn($return,'json');
        }
        
        //查找出该订单所有没有退货的条形码
        $order_goods_where['order_id'] = $order_id;
        $order_goods_where['is_refund'] = 1;
        $de_list = $OrderGoods->getAllList($order_goods_where,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($de_list){
         
            $Agent = D('Agent');
            $DeliverGoods = D('DeliverGoods');
            $AgentProfitLog = D('AgentProfitLog');
            $CompanyReportsLog = D('CompanyReportsLog');
            $AgentGoodsStockRale = D('AgentGoodsStockRale');
            
            //开启事务
            $OrderGoods->startTrans();
            
            //初始化数据
            $is_vilid_success = TRUE;//是否验证成功
            $company_all_profit_money = 0; //公司在该订单中支出的总分润
            
            //检查本人是否已经发货,如果本人已经发货,就不能再删除
            foreach ($de_list as $v) {
                $code = $v['code'];
                $code_type = $v['code_type'];
                $goods_id = $v['goods_id'];
                $goods_number = $v['goods_number'];
                $goods_profit = $v['goods_profit'];
                $member_price = $v['member_price'];
                $sale_total_profit = $goods_profit*$goods_number; //发货人商品销售总利润
                $buy_total_money = $member_price*$goods_number;//收货人商品购买总金额
                
                
                //获取号码的类型 前缀类型:1:大标,2:小标:3:防伪标,4:中标
                switch ($code_type) {
                    case 1:
                            
                            //检查大标签是否发货
                            $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE  = "'.$code.'" AND admin_id ='.$member_id;

                            $label_code_retult = $DeliverGoods->query($sql);
                            $deliv_count = $label_code_retult[0]['counts'];
                            if($deliv_count > 0){
                                $return['msg'] = '因为您已经发了标签:'.$code.'的商品,不能退货!';
                                $is_vilid_success = FALSE;
                            }
                            
                            //检查中标签是否发货
                            $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE IN(SELECT middle_code FROM label_code WHERE max_code = "'.$code.'") AND admin_id ='.$member_id;

                            $label_code_retult = $DeliverGoods->query($sql);
                            $deliv_count = $label_code_retult[0]['counts'];
                            if($deliv_count > 0){
                                $return['msg'] = '因为您已经发了标签:'.$code.'中标签的商品,不能退货!';
                                $is_vilid_success = FALSE;
                            }
                            
                            //检查小标签是否发货
                            $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE IN(SELECT min_code FROM label_code WHERE max_code = "'.$code.'") AND admin_id ='.$member_id;

                            $label_code_retult = $DeliverGoods->query($sql);
                            $deliv_count = $label_code_retult[0]['counts'];
                            if($deliv_count > 0){
                                $return['msg'] = '因为您已经发了标签:'.$code.'小标签的商品,不能退货!';
                                $is_vilid_success = FALSE;
                            }
                            
                        break;
                    case 2:
                            //检查小标签是否发货
                            $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE = "'.$code.'" AND admin_id ='.$member_id;
                            
                            $label_code_retult = $DeliverGoods->query($sql);
                            $deliv_count = $label_code_retult[0]['counts'];
                            
                            if($deliv_count > 0){
                                $return['msg'] = '因为您已经发了标签:'.$code.'的商品,不能退货!';
                                $is_vilid_success = FALSE;
                            }
                        
                        break;
                    case 4:
                            
                            //检查中标签是否发货
                            $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE  = "'.$code.'" AND admin_id ='.$member_id;

                            $label_code_retult = $DeliverGoods->query($sql);
                            $deliv_count = $label_code_retult[0]['count'];
                            if($deliv_count > 0){
                                $return['msg'] = '因为您已经发了标签:'.$code.'的商品,不能退货!';
                                $is_vilid_success = FALSE;
                            }
                            
                            //检查小标签是否发货
                            $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE IN(SELECT min_code FROM label_code WHERE max_code = "'.$code.'") AND admin_id ='.$member_id;

                            $label_code_retult = $DeliverGoods->query($sql);
                            $deliv_count = $label_code_retult[0]['count'];
                            if($deliv_count > 0){
                                $return['msg'] = '因为您已经发了标签:'.$code.'小标签的商品,不能退货!';
                                $is_vilid_success = FALSE;
                            }
                            
                        break;
                }
                
                //验证不通过
                if($is_vilid_success === FALSE){
                
                    $this->ajaxReturn($return,'json');
                }
                
                //发货人为代理 减发货人出货金额与出货利润与出货库存 开始
                if(in_array($admin_lv, array(1,2,3,4))){
                    //减发货人出货金额与出货利润与加出货库存
                        //代理商品实际库存,购买库存与金额
                        $edit_sale_goods_stock_where['agent_id'] = $admin_id;
                        $edit_sale_goods_stock_where['goods_id'] = $goods_id;
                       
                        $editSaleGoodsStockData['goods_stock'] = array('exp','goods_stock+'.$goods_number);
                        $editSaleGoodsStockData['sale_total_stock'] = array('exp','sale_total_stock-'.$goods_number);
                        $editSaleGoodsStockData['sale_total_money'] = array('exp','sale_total_money-'.$buy_total_money);
                        $editSaleGoodsStockData['all_sale_total_profit'] = array('exp','all_sale_total_profit-'.$sale_total_profit);
                        $editSaleGoodsStockResult = $AgentGoodsStockRale->editData($edit_sale_goods_stock_where,$editSaleGoodsStockData);

                        if(!$editSaleGoodsStockResult){
                            $is_vilid_success = FALSE;
                            $return['msg'] = '出货人更改商品实际库存,销售库存与金额失败!';
                        }

                        //代理每月报表
                        $edit_sale_month_profit_where['agent_id'] = $admin_id;
                        $edit_sale_month_profit_where['year'] = $order_year;
                        $edit_sale_month_profit_where['month'] = $order_month;
                        
                        $editSaleMonthProfitData['sale_total_money'] = array('exp','sale_total_money-'.$buy_total_money);
                        $editSaleMonthProfitData['sale_total_stock'] = array('exp','sale_total_stock-'.$goods_number);
                        $editSaleMonthProfitData['sale_profit'] = array('exp','sale_profit-'.$sale_total_profit);
                        $editSaleMonthProfitResult = $AgentMonthProfit->editData($edit_sale_month_profit_where,$editSaleMonthProfitData);
                       
                        if(!$editSaleMonthProfitResult){
                            $is_vilid_success = FALSE;
                            $return['msg'] = '出货人更改每月销售库存与金额失败!';
                        }
                }else{ //发货人为工厂

                    //获取商品代理等级分润金额
                    $goodsProfitLv1 = $this->getAgentGoodsProfit($goods_id, 1); 
                    $goodsProfitLv2 = $this->getAgentGoodsProfit($goods_id, 2);
                    $goodsProfitLv3 = $this->getAgentGoodsProfit($goods_id, 3);

                    //每一盒产品公司应返总利润
                    $companyOneGoodsTotalProfit = $goodsProfitLv1['top1_profit'] + $goodsProfitLv1['top2_profit'] + $goodsProfitLv2['agent1_profit'] + $goodsProfitLv3['agent1_profit'] + $goodsProfitLv3['agent2_profit'] + $goodsProfitLv3['agent3_profit'];
                    $company_all_profit_money += $companyOneGoodsTotalProfit*$goods_number;
                
                }
                //发货人为代理  减发货人出货金额与出货利润与出货库存 结束
               
                //减收货人进库金额与进库数量  开始
                    if($member_id > 0){
                        //代理商品实际库存,购买库存与金额
                        $edit_buy_goods_stock_where['agent_id'] = $member_id;
                        $edit_buy_goods_stock_where['goods_id'] = $goods_id;
                        $edit_buy_goods_stock_where['goods_stock'] = array('egt',$goods_number);

                        $editBuyGoodsStockData['goods_stock'] = array('exp','goods_stock-'.$goods_number);
                        $editBuyGoodsStockData['buy_total_stock'] = array('exp','buy_total_stock-'.$goods_number);
                        $editBuyGoodsStockData['buy_total_money'] = array('exp','buy_total_money-'.$buy_total_money);
                        $editBuyGoodsStockResult = $AgentGoodsStockRale->editData($edit_buy_goods_stock_where,$editBuyGoodsStockData);

                        if(!$editBuyGoodsStockResult){
                            $is_vilid_success = FALSE;
                            $return['msg'] = '收货人更改商品实际库存,购买库存与金额失败!';
                        }

                        //代理每月报表
                        $edit_buy_month_profit_where['agent_id'] = $member_id;
                        $edit_buy_month_profit_where['year'] = $order_year;
                        $edit_buy_month_profit_where['month'] = $order_month;

                        $editBuyMonthProfitData['buy_total_money'] = array('exp','buy_total_money-'.$buy_total_money);
                        $editBuyMonthProfitData['buy_total_stock'] = array('exp','buy_total_stock-'.$goods_number);
                        $editBuyMonthProfitResult = $AgentMonthProfit->editData($edit_buy_month_profit_where,$editBuyMonthProfitData);

                        if(!$editBuyMonthProfitResult){
                            $is_vilid_success = FALSE;
                            $return['msg'] = '收货人更改每月购买库存与金额失败!';
                        }
                    }
                    
                //减收货人进库金额与进库数量  结束
            }
            
            //发货人为代理
            if(in_array($admin_lv, array(1,2,3,4))){
                //减发货人出货总金额与总利润与总库存
                $edit_admin_agent_info_where['agentId'] = $admin_id;
                
                $editAdminAgentInfoData['all_sale_total_stock'] = array('exp','all_sale_total_stock-'.$order_goods_total_stock);
                $editAdminAgentInfoData['all_sale_total_money'] = array('exp','all_sale_total_money-'.$order_sale_all_money);
                $editAdminAgentInfoData['all_sale_total_profit'] = array('exp','all_sale_total_profit-'.$order_sale_all_profit);
                $editAdminAgentInfoResult = $Agent->editData($edit_admin_agent_info_where,$editAdminAgentInfoData);

                if(!$editAdminAgentInfoResult){
                   $is_vilid_success = FALSE;
                   $return['msg'] = '发货人更改代理总销售库存与金额失败!';
                }
            }else{ //发货人为工厂
                
                //减去公司总返利
                $edit_company_reports_log_where['year'] = $order_year;
                $edit_company_reports_log_where['month'] = $order_month;

                $editCompanyReportsLogData['total_profit'] = array('exp','total_profit-'.$company_all_profit_money);
                $editCompanyReportsLogData['all_total_profit'] = array('exp','all_total_profit-'.$company_all_profit_money);
                $editCompanyReportsLogResult = $CompanyReportsLog->editData($edit_company_reports_log_where,$editCompanyReportsLogData);
                
                if(!$editCompanyReportsLogResult){
                    $is_vilid_success = FALSE;
                    $return['msg'] = '修改公司总返利失败!';
                }
              
            }
            
            //减收货人该订单购买总库存与总金额
            if($member_id > 0){
                $edit_agent_info_where['agentId'] = $member_id;

                $editAgentInfoData['all_buy_total_stock'] = array('exp','all_buy_total_stock-'.$order_goods_total_stock);
                $editAgentInfoData['all_buy_total_money'] = array('exp','all_buy_total_money-'.$order_sale_all_money);
                $editAgentInfoResult = $Agent->editData($edit_agent_info_where,$editAgentInfoData);

                if(!$editAgentInfoResult){
                   $is_vilid_success = FALSE;
                   $return['msg'] = '收货人更改代理总销售库存与金额失败!';
                }
            }
                    
            $edit_order_info_where['order_id'] = $order_id;
                
            //修改订单状态为退货: order_status : 订单状态: 1:发货,2:退货
            $editOrderInfoData['order_status'] = 2;
            $editOrderInfoResult = $OrderInfo->editData($edit_order_info_where,$editOrderInfoData);
            
            if(!$editOrderInfoResult){
                $is_vilid_success = FALSE;
                $return['msg'] = '修改订单状态失败!';
            }
            
            //修改订单商品为退货: is_refund : 是否退货:1:否,2:是
            $editOrderGoodsData['is_refund'] = 2;
            $editOrderGoodsResult = $OrderGoods->editData($edit_order_info_where,$editOrderGoodsData);
            
            if(!$editOrderGoodsResult){
                $is_vilid_success = FALSE;
                $return['msg'] = '修改订单商品状态失败!';
            }
            
            //修改分润记录为退货: is_refund : 是否退货: 1:否,2:是
            $edit_agent_profit_log_where['order_id'] = $order_id;
            $edit_agent_profit_log_where['is_refund'] = 1;
            $agentProfitLogList = $AgentProfitLog->getAllList($edit_agent_profit_log_where,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            
            if($agentProfitLogList){ //有分润记录才需要更改
                
                foreach ($agentProfitLogList as $pk =>$pv) {
                    //把该订单代理的公司分润减掉
                    $profit_total_money = $pv['profit_total_money'];
                    $profit_agent_id = $pv['profit_agent_id'];
                    
                    $edit_month_profit_where['year'] = $order_year;
                    $edit_month_profit_where['month'] = $order_month;
                    $edit_month_profit_where['agent_id'] = $profit_agent_id;
                    
                    $editAgentMonthProfitData['company_profit'] = array('exp','company_profit-'.$profit_total_money);//减少公司分润;
                    $editAgentMonthProfitResult = $AgentMonthProfit->editData($edit_month_profit_where,$editAgentMonthProfitData);
                    
                    if(!$editAgentMonthProfitResult){
                        $OrderGoods->rollback();
                        $return['msg'] = '修改代理每月分润失败!';
                        $this->ajaxReturn($return,'json');
                    }
                    
                    //减去代理分润总金额
                    $edit_agent_total_profit_where['agentId'] = $profit_agent_id;
                    
                    $editAgentTotalProfitData['company_total_profit'] = array('exp','company_total_profit-'.$profit_total_money);//减少公司分润;
                    $editAgentTotalProfitResult = $Agent->editData($edit_agent_total_profit_where,$editAgentTotalProfitData);
                    
                    if(!$editAgentTotalProfitResult){
                        $OrderGoods->rollback();
                        $return['msg'] = '代理总分润更改失败!';
                        $this->ajaxReturn($return,'json');
                    }
                }
                
                $editProfitData['is_refund'] = 2;
                $editProfitLogResult = $AgentProfitLog->editData($edit_order_info_where,$editProfitData);

                if(!$editProfitLogResult){
                    $is_vilid_success = FALSE;
                }
            }
            
            //删除条码记录
            if($is_vilid_success){
                $deliverResult = $DeliverGoods->delData($edit_order_info_where);
                if(!$deliverResult){
                    $OrderGoods->rollback();
                    $return['msg'] = '该订单条形码删除失败!';
                    $this->ajaxReturn($return,'json');
                }
            }

            if($is_vilid_success){
                $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
                $OrderGoods->commit();

                //清除缓存
                $Redis = new \Think\Cache\Driver\Redis();
                $Redis->clear();
            }else{
                $OrderGoods->rollback();
            }
            
        }else{
            $return['msg'] = '该订单不存在';
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //删除单个商品
    public function delOrderGoods() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        $order_id = I('order_id');
        $code = I('code');
        $is_vilid_success = TRUE;
       
        if(empty($order_id)){
            $return['msg'] = '请选择订单';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($code)){
            $return['msg'] = '请选择商品';
            $this->ajaxReturn($return,'json');
        }
        
        $where['order_id'] = $order_id;
        
        $OrderInfo = D('OrderInfo');
        
        //获取订单信息
        $order_info = $OrderInfo->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if(empty($order_info)){
            $return['msg'] = '该订单不存在';
            $this->ajaxReturn($return,'json');
        }
        
        if($order_info['order_status'] == 2){
            $return['msg'] = '该订单已经退货!';
            $this->ajaxReturn($return,'json');
        }
        
        $order_info_time = strtotime($order_info['add_time']);
        $order_year = date('Y',$order_info_time);
        $order_month = date('m',$order_info_time);
        $admin_id = $order_info['admin_id'];
        $admin_lv = $order_info['admin_lv'];
        $member_id = $order_info['member_id'];
        
        $OrderGoods = D('OrderGoods');
        
        //查找出该商品信息
        $order_goods_where['order_id'] = $order_id;
        $order_goods_where['code'] = $code;
        
        $order_goods_info = $OrderGoods->getDetail($order_goods_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if(empty($order_goods_info)){
            $return['msg'] = '该商品不存在!';
            $this->ajaxReturn($return,'json');
        }
        
        if($order_goods_info['is_refund'] == 2){
            $return['msg'] = '该商品已经退货!';
            $this->ajaxReturn($return,'json');
        }
        
        //如果该订单代理已经分润,不能再退货
        $AgentMonthProfit = D('AgentMonthProfit');
            
        $agent_month_profit_count_where['year'] = $order_year;
        $agent_month_profit_count_where['month'] = $order_month;
        $agent_month_profit_count_where['agent_id'] = $member_id;
        $agent_month_profit_count_where['is_profit'] = 1;
        $agentMonthProfitCount = $AgentMonthProfit->getCount($agent_month_profit_count_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($agentMonthProfitCount > 0){
            $return['msg'] = '该订单已经分润,不能再退货!';
            $this->ajaxReturn($return,'json');
        }
        
        $DeliverGoods = D('DeliverGoods');
        $AgentGoodsStockRale = D('AgentGoodsStockRale');
        $CompanyReportsLog = D('CompanyReportsLog');
        $AgentProfitLog = D('AgentProfitLog');
        
        $OrderGoods->startTrans(); //开启事务
        
        $code = $order_goods_info['code'];
        $code_type = $order_goods_info['code_type'];
        $goods_id = $order_goods_info['goods_id'];
        $goods_profit = $order_goods_info['goods_profit'];
        $member_price = $order_goods_info['member_price'];
        $order_goods_total_stock = $goods_number = $order_goods_info['goods_number'];
        $order_sale_all_profit = $sale_total_profit = $goods_profit*$goods_number; //发货人商品销售总利润
        $order_sale_all_money = $buy_total_money = $member_price*$goods_number;//收货人商品购买总金额
        
        //获取号码的类型 前缀类型:1:大标,2:小标:3:防伪标,4:中标
        switch ($code_type) {
            case 1:

                    //检查大标签是否发货
                    $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE  = "'.$code.'" AND admin_id ='.$member_id;

                    $label_code_retult = $DeliverGoods->query($sql);
                    $deliv_count = $label_code_retult[0]['counts'];
                    if($deliv_count > 0){
                        $return['msg'] = '因为您已经发了标签:'.$code.'的商品,不能退货!';
                        $is_vilid_success = FALSE;
                    }

                    //检查中标签是否发货
                    $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE IN(SELECT middle_code FROM label_code WHERE max_code = "'.$code.'") AND admin_id ='.$member_id;

                    $label_code_retult = $DeliverGoods->query($sql);
                    $deliv_count = $label_code_retult[0]['counts'];
                    if($deliv_count > 0){
                        $return['msg'] = '因为您已经发了标签:'.$code.'中标签的商品,不能退货!';
                        $is_vilid_success = FALSE;
                    }

                    //检查小标签是否发货
                    $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE IN(SELECT min_code FROM label_code WHERE max_code = "'.$code.'") AND admin_id ='.$member_id;

                    $label_code_retult = $DeliverGoods->query($sql);
                    $deliv_count = $label_code_retult[0]['counts'];
                    if($deliv_count > 0){
                        $return['msg'] = '因为您已经发了标签:'.$code.'小标签的商品,不能退货!';
                        $is_vilid_success = FALSE;
                    }

                break;
            case 2:
                    //检查小标签是否发货
                    $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE = "'.$code.'" AND admin_id ='.$member_id;

                    $label_code_retult = $DeliverGoods->query($sql);
                    $deliv_count = $label_code_retult[0]['counts'];
                    if($deliv_count > 0){
                        $return['msg'] = '因为您已经发了标签:'.$code.'的商品,不能退货!';
                        $is_vilid_success = FALSE;
                    }

                break;
            case 4:

                    //检查中标签是否发货
                    $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE  = "'.$code.'" AND admin_id ='.$member_id;

                    $label_code_retult = $DeliverGoods->query($sql);
                    $deliv_count = $label_code_retult[0]['count'];
                    if($deliv_count > 0){
                        $return['msg'] = '因为您已经发了标签:'.$code.'的商品,不能退货!';
                        $is_vilid_success = FALSE;
                    }

                    //检查小标签是否发货
                    $sql = 'SELECT COUNT(*) AS counts FROM deliver_goods WHERE CODE IN(SELECT min_code FROM label_code WHERE max_code = "'.$code.'") AND admin_id ='.$member_id;

                    $label_code_retult = $DeliverGoods->query($sql);
                    $deliv_count = $label_code_retult[0]['count'];
                    if($deliv_count > 0){
                        $return['msg'] = '因为您已经发了标签:'.$code.'小标签的商品,不能退货!';
                        $is_vilid_success = FALSE;
                    }

                break;
        }

        //验证不通过
        if($is_vilid_success === FALSE){

            $this->ajaxReturn($return,'json');
        }
        
        $Agent = D('Agent');
        
        //发货人为代理 减发货人出货金额与出货利润与出货库存 开始
        if(in_array($admin_lv, array(1,2,3,4))){
            //减发货人出货金额与出货利润与加出货库存
                //代理商品实际库存,购买库存与金额
                $edit_sale_goods_stock_where['agent_id'] = $admin_id;
                $edit_sale_goods_stock_where['goods_id'] = $goods_id;

                $editSaleGoodsStockData['goods_stock'] = array('exp','goods_stock+'.$goods_number);
                $editSaleGoodsStockData['sale_total_stock'] = array('exp','sale_total_stock-'.$goods_number);
                $editSaleGoodsStockData['sale_total_money'] = array('exp','sale_total_money-'.$buy_total_money);
                $editSaleGoodsStockData['all_sale_total_profit'] = array('exp','all_sale_total_profit-'.$sale_total_profit);
                $editSaleGoodsStockResult = $AgentGoodsStockRale->editData($edit_sale_goods_stock_where,$editSaleGoodsStockData);

                if(!$editSaleGoodsStockResult){
                    $is_vilid_success = FALSE;
                    $return['msg'] = '出货人更改商品实际库存,销售库存与金额失败!';
                }

                //代理每月报表
                $edit_sale_month_profit_where['agent_id'] = $admin_id;
                $edit_sale_month_profit_where['year'] = $order_year;
                $edit_sale_month_profit_where['month'] = $order_month;

                $editSaleMonthProfitData['sale_total_money'] = array('exp','sale_total_money-'.$buy_total_money);
                $editSaleMonthProfitData['sale_total_stock'] = array('exp','sale_total_stock-'.$goods_number);
                $editSaleMonthProfitData['sale_profit'] = array('exp','sale_profit-'.$sale_total_profit);
                $editSaleMonthProfitResult = $AgentMonthProfit->editData($edit_sale_month_profit_where,$editSaleMonthProfitData);

                if(!$editSaleMonthProfitResult){
                    $is_vilid_success = FALSE;
                    $return['msg'] = '出货人更改每月销售库存与金额失败!';
                }
                
            //减发货人出货总金额与总利润与总库存
                $edit_admin_agent_info_where['agentId'] = $admin_id;
                
                $editAdminAgentInfoData['all_sale_total_stock'] = array('exp','all_sale_total_stock-'.$order_goods_total_stock);
                $editAdminAgentInfoData['all_sale_total_money'] = array('exp','all_sale_total_money-'.$order_sale_all_money);
                $editAdminAgentInfoData['all_sale_total_profit'] = array('exp','all_sale_total_profit-'.$order_sale_all_profit);
                $editAdminAgentInfoResult = $Agent->editData($edit_admin_agent_info_where,$editAdminAgentInfoData);

                if(!$editAdminAgentInfoResult){
                   $is_vilid_success = FALSE;
                   $return['msg'] = '发货人更改代理总销售库存与金额失败!';
                }
        }else{ //发货人为工厂
            
            //获取商品代理等级分润金额
            $goodsProfitLv1 = $this->getAgentGoodsProfit($goods_id, 1); 
            $goodsProfitLv2 = $this->getAgentGoodsProfit($goods_id, 2);
            $goodsProfitLv3 = $this->getAgentGoodsProfit($goods_id, 3);

            //每一盒产品公司应返总利润
            $companyOneGoodsTotalProfit = $goodsProfitLv1['top1_profit'] + $goodsProfitLv1['top2_profit'] + $goodsProfitLv2['agent1_profit'] + $goodsProfitLv3['agent1_profit'] + $goodsProfitLv3['agent2_profit'] + $goodsProfitLv3['agent3_profit'];
            $company_all_profit_money = $companyOneGoodsTotalProfit*$goods_number;

            //减去公司总返利
                $edit_company_reports_log_where['year'] = $order_year;
                $edit_company_reports_log_where['month'] = $order_month;

                $editCompanyReportsLogData['total_profit'] = array('exp','total_profit-'.$company_all_profit_money);
                $editCompanyReportsLogData['all_total_profit'] = array('exp','all_total_profit-'.$company_all_profit_money);
                $editCompanyReportsLogResult = $CompanyReportsLog->editData($edit_company_reports_log_where,$editCompanyReportsLogData);
                
                if(!$editCompanyReportsLogResult){
                    $is_vilid_success = FALSE;
                    $return['msg'] = '修改公司总返利失败!';
                }
        }
        //发货人为代理  减发货人出货金额与出货利润与出货库存 结束
        
        if($member_id > 0){
            //减收货人进库金额与进库数量  开始

                //代理商品实际库存,购买库存与金额
                $edit_buy_goods_stock_where['agent_id'] = $member_id;
                $edit_buy_goods_stock_where['goods_id'] = $goods_id;
                $edit_buy_goods_stock_where['goods_stock'] = array('egt',$goods_number);

                $editBuyGoodsStockData['goods_stock'] = array('exp','goods_stock-'.$goods_number);
                $editBuyGoodsStockData['buy_total_stock'] = array('exp','buy_total_stock-'.$goods_number);
                $editBuyGoodsStockData['buy_total_money'] = array('exp','buy_total_money-'.$buy_total_money);
                $editBuyGoodsStockResult = $AgentGoodsStockRale->editData($edit_buy_goods_stock_where,$editBuyGoodsStockData);

                if(!$editBuyGoodsStockResult){
                    $is_vilid_success = FALSE;
                    $return['msg'] = '收货人更改商品实际库存,购买库存与金额失败!';
                }

                //代理每月报表
                $edit_buy_month_profit_where['agent_id'] = $member_id;
                $edit_buy_month_profit_where['year'] = $order_year;
                $edit_buy_month_profit_where['month'] = $order_month;

                $editBuyMonthProfitData['buy_total_money'] = array('exp','buy_total_money-'.$buy_total_money);
                $editBuyMonthProfitData['buy_total_stock'] = array('exp','buy_total_stock-'.$goods_number);
                $editBuyMonthProfitResult = $AgentMonthProfit->editData($edit_buy_month_profit_where,$editBuyMonthProfitData);

                if(!$editBuyMonthProfitResult){
                    $is_vilid_success = FALSE;
                    $return['msg'] = '收货人更改每月购买库存与金额失败!';
                }

            //减收货人进库金额与进库数量  结束

            //减收货人该订单购买总库存与总金额
            $edit_agent_info_where['agentId'] = $member_id;

            $editAgentInfoData['all_buy_total_stock'] = array('exp','all_buy_total_stock-'.$order_goods_total_stock);
            $editAgentInfoData['all_buy_total_money'] = array('exp','all_buy_total_money-'.$order_sale_all_money);
            $editAgentInfoResult = $Agent->editData($edit_agent_info_where,$editAgentInfoData);

            if(!$editAgentInfoResult){
               $is_vilid_success = FALSE;
               $return['msg'] = '收货人更改代理总销售库存与金额失败!';
            }
        }
        
        //修改订单商品为退货: is_refund : 是否退货:1:否,2:是
        $edit_order_goods_where['order_id'] = $order_id;
        $edit_order_goods_where['code'] = $code;
        
        $editOrderGoodsData['is_refund'] = 2;
        $editOrderGoodsResult = $OrderGoods->editData($edit_order_goods_where,$editOrderGoodsData);
        
        if(!$editOrderGoodsResult){
            $is_vilid_success = FALSE;
            $return['msg'] = '修改订单商品状态失败!';
        }

        //如果该订单的商品都退货,这是才更改订单的状态
        $edit_order_goods_count_where['order_id'] = $order_id;
        $edit_order_goods_count_where['is_refund'] = 1;
        $editOrderGoodsCount = $OrderGoods->getCount($edit_order_goods_count_where);
        
        if($editOrderGoodsCount == 0){
            //修改订单状态为退货: order_status : 订单状态: 1:发货,2:退货
            $edit_order_info_where['order_id'] = $order_id;
        
            $editOrderInfoData['order_status'] = 2;
            $editOrderInfoResult = $OrderInfo->editData($edit_order_info_where,$editOrderInfoData);

            if(!$editOrderInfoResult){
                $is_vilid_success = FALSE;
                $return['msg'] = '修改订单状态失败!';
            }
        }

        //修改分润记录为退货: is_refund : 是否退货: 1:否,2:是
        $edit_agent_profit_log_where['order_id'] = $order_id;
        $edit_agent_profit_log_where['is_refund'] = 1;
        $edit_agent_profit_log_where['code'] = $code;
        $agentProfitLogList = $AgentProfitLog->getAllList($edit_agent_profit_log_where,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));

        if($agentProfitLogList){ //有分润记录才需要更改

            foreach ($agentProfitLogList as $pv) {
                //把该订单代理的公司分润减掉
                $profit_total_money = $pv['profit_total_money'];
                $profit_agent_id = $pv['profit_agent_id'];

                $edit_month_profit_where['year'] = $order_year;
                $edit_month_profit_where['month'] = $order_month;
                $edit_month_profit_where['agent_id'] = $profit_agent_id;

                $editAgentMonthProfitData['company_profit'] = array('exp','company_profit-'.$profit_total_money);//减少公司分润;
                $editAgentMonthProfitResult = $AgentMonthProfit->editData($edit_month_profit_where,$editAgentMonthProfitData);

                if(!$editAgentMonthProfitResult){
                    $OrderGoods->rollback();
                    $return['msg'] = '修改代理每月分润失败!';
                    $this->ajaxReturn($return,'json');
                }

                //减去代理分润总金额
                $edit_agent_total_profit_where['agentId'] = $profit_agent_id;

                $editAgentTotalProfitData['company_total_profit'] = array('exp','company_total_profit-'.$profit_total_money);//减少公司分润;
                $editAgentTotalProfitResult = $Agent->editData($edit_agent_total_profit_where,$editAgentTotalProfitData);

                if(!$editAgentTotalProfitResult){
                    $OrderGoods->rollback();
                    $return['msg'] = '代理总分润更改失败!';
                    $this->ajaxReturn($return,'json');
                }
            }
            
            //修改分润记录为退货
            $edit_profit_log_where['order_id'] = $order_id;
            $edit_profit_log_where['code'] = $code;
                    
            $editProfitData['is_refund'] = 2;
            $editProfitLogResult = $AgentProfitLog->editData($edit_profit_log_where,$editProfitData);

            if(!$editProfitLogResult){
                $is_vilid_success = FALSE;
            }
        }

        //删除条码记录
        if($is_vilid_success){
            $del_deliver_goods_where['order_id'] = $order_id;
            $del_deliver_goods_where['code'] = $code;
            $deliverResult = $DeliverGoods->delData($del_deliver_goods_where);
            if(!$deliverResult){
                $OrderGoods->rollback();
                $return['msg'] = '该订单条形码删除失败!';
                $this->ajaxReturn($return,'json');
            }
        }
        
        if($is_vilid_success){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>array('order_id'=>$order_id));
            $OrderGoods->commit();
            
            //清除缓存
            $Redis = new \Think\Cache\Driver\Redis();
            $Redis->clear();
           
        }else{
            $OrderGoods->rollback();
        }
        
        $this->ajaxReturn($return,'json');
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
    
    //清除对应的条形码缓存
    public function clearCodeCache($code,$LabelCode) {
        if(empty($code)){
            return FALSE;
        }
        
        $count = 1;
        
        //标签长度 10位为旧标签,11位为新标签
        $code_lenth = strlen($code);
        if($code_lenth == 10){
            
            //获取号码的类型 前缀类型:1:大标,2:小标:3:防伪标
            $new_code = substr($code,0,3);  
           
            //设置查询条件
            switch ($new_code) {
                case 700:
                    $count = 2;
                    $code_type = 1;
                    $where['max_code'] = $code;
                    break;
                case 100:
                    $code_type = 2;
                    $where['min_code'] = $code;
                    break;
                default :
                    $code_type = 3;
                    $where['security_code'] = 0;
                    break;
            }
        }else{
            //获取号码的类型 前缀类型:1:大标,2:小标:3:防伪标,4:中标
            $code_type = judge_code_type($code);
            
            //设置查询条件
            switch ($code_type) {
                case 1:
                    $count = 2;
                    $where['max_code'] = $code;
                    break;
                case 2:
                    $where['min_code'] = $code;
                    break;
                case 4:
                    $count = 2;
                    $where['middle_code'] = $code;
                    break;
                default :
                    $where['security_code'] = 0;
                    break;
            }
        }
        
        if($count > 1){
            //查询标签
//            $LabelCode = D('LabelCode');
            $label_list = $LabelCode->getAllList($where,'',array('field'=>array('min_code'),'is_opposite'=>false));
            
            if($label_list){
                foreach ($label_list as $k => $v) {
                    S($v['min_code'],NULL);
                }
            }
            
        }else{
            S($code,NULL);
        }
        
        return TRUE;
    }
    
    
}