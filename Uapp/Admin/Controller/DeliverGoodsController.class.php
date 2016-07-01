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
        
        if($search_field == 'code'){
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
        $order_sn = I('aid');
        $web_type = I('web_type');
        
        if(empty($order_sn)){
            dump('请选择发货单');
            return FALSE;
        }
        
        if(empty($web_type)){
            dump('请选择站点');
            return FALSE;
        }
        
        $cache_info = S(C('ORDER_GOODS').$order_sn.'_'.$web_type);
        if(empty($cache_info)){
           
            $OrderGoods = D('OrderGoods');
            $OrderInfo = D('OrderInfo');
            
            $where['order_sn'] = $order_sn;
            $list = $OrderGoods->getAllList($where,'',array('field'=>array(),'is_opposite'=>false));
            
            if($list){
                foreach ($list as $k => $v) {
                    $goods_id = $v['goods_id'];
                    $goods_info = $this->getGoods($goods_id);
                    $list[$k]['index_pic'] = $goods_info['index_pic'];
                }
            }
          
            $order_where['order_sn'] = $order_sn;
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
        $order_sn = I('order_sn');
        
        if(empty($order_sn)){
            $return['msg'] = '请选择订单';
            $this->ajaxReturn($return,'json');
        }
        
        $OrderInfo = D('OrderInfo');
        $OrderGoods = D('OrderGoods');
        $DeliverGoods = D('DeliverGoods');
        
        $where['order_sn'] = $order_sn;
       
        //如果下级已经发货,就不能再删除
        $admin_id = $OrderInfo->where($where)->getField('member_id');
        $de_list = $DeliverGoods->getAllList($where,'',array('field'=>array('code'),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
     
        if($de_list){
            foreach ($de_list as $k => $v) {
                $code_list[] = $v['code'];
            }
            
            $c_where['admin_id'] = $admin_id;
            $c_where['code'] = array('in',$code_list);
            
            $de_count = $DeliverGoods->getCount($c_where);
            
            if($de_count > 0){
                $return['msg'] = '下级已经发货,不能删除';
                $this->ajaxReturn($return,'json');
            }
        }
        
        $OrderGoods->startTrans();
        
//        $result1 = $OrderInfo->delData($where);
//        $result2 = $OrderGoods->delData($where);
//        $result3 = $DeliverGoods->delData($where);
        
        if($result1 && $result2 && $result3){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
            $OrderGoods->commit();
            
            //清除缓存
            if($code_list){
                $LabelCode = D('LabelCode');
                foreach ($code_list as $cv) {
                    $this->clearCodeCache($cv,$LabelCode);
                }
            }
            
            S(C('ORDER_GOODS').$order_sn,NULL);
        }else{
            $OrderGoods->rollback();
            
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //删除单个商品
    public function delOrderGoods() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        $order_id = I('order_id');
        $code = I('code');
       
        if(empty($order_id)){
            $return['msg'] = '请选择订单';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($code)){
            $return['msg'] = '请选择商品';
            $this->ajaxReturn($return,'json');
        }
        
        $OrderGoods = D('OrderGoods');
        $DeliverGoods = D('DeliverGoods');
        
        $where['order_id'] = $order_id;
        $where['code'] = $code;
        
        //如果下级已经发货,就不能再删除
        $de_info = $DeliverGoods->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),'');
     
        if($de_info){
            $code = $de_info['code'];
            $admin_id = $de_info['agent_id'];
            $c_where['admin_id'] = $admin_id;
            $c_where['code'] = $code;
            $de_count = $DeliverGoods->getCount($c_where);
            
            if($de_count > 0){
                $return['msg'] = '下级已经发货,不能删除';
                $this->ajaxReturn($return,'json');
            }
        }
        
        $OrderGoods->startTrans();
        
//        $result2 = $OrderGoods->delData($where);
//        $result3 = $DeliverGoods->delData($where);
        
        if($result2 && $result3){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>array('order_sn'=>$order_sn));
            $OrderGoods->commit();
            
            $LabelCode = D('LabelCode');
            $this->clearCodeCache($code,$LabelCode);
            
            S(C('ORDER_GOODS').$order_sn,NULL);
           
        }else{
            $OrderGoods->rollback();
        }
        
        $this->ajaxReturn($return,'json');
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