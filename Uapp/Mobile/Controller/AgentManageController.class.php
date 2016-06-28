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
        
        //代理信息
        $member_info = $this->memberInfo();
        
        //获取团队名称
        if(!$member_info['team_name']){
            $agent1_info = $this->getAgent($member_info['agent1_id']);
            $member_info['team_name'] = $agent1_info['team_name'];
        }

        //上级名称
        $MEMBER_LEVEL = C('MEMBER_LEVEL');
        $parent_info = $this->getAgent($member_info['pid']);
        $member_info['parent_name'] = $parent_info ? $parent_info['name'] : $MEMBER_LEVEL[0]['name'];
       
        $this->assign('member_info', $member_info);
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
        $star = $member_info['star'] + 1;
        
        //限制人数: 每个等级只能有10个人 ,官方发展大区: 如果大区满10人的情况,必须要等下面的所有人全部发展完才能发展第二条线
        $AgentRelation = D('AgentRelation');
        
        //获取直接下级总人数
        $direct_where['pid'] = $member_id;
        $direct_next_agent_count = $AgentRelation->getCount($direct_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        //如果是大区,算下下面有没1110满人,不然就不能发展下级
        if($member_info['star'] == 1){
            
            //获取最后一条下线数字
            $num_where['agent1_id'] = $member_id;
            $line_number = $AgentRelation->where($num_where)->order('line_number DESC')->getField('line_number');
            
            //间接总人数
            $indirect_where['agent1_id'] = $member_id;
            $indirect_where['line_number'] = $line_number;
            $indirect_next_agent_count = $AgentRelation->getCount($indirect_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
            
            if($direct_next_agent_count >= 10 && $indirect_next_agent_count < 1110){
                $return['msg'] = '您的第'.$line_number.'条线还没全部满员,不能生成邀请码!';
                $this->ajaxReturn($return,'json');
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
    
    //选择代理
    public function selAgent() {
        
        $member_info = $this->memberInfo();
        $agent_name = trim(I('name'));
        $page = I('p',1);
        $limit=10;
//       dump($member_info);
        if($member_info['agent_grade'] < 4){
//            $list = session('agent_list_'.$member_info['agentid'].'_p'.$page);
            $agent_name ? $list = '' : '';
//            if(empty($list)){
                $AgentRelation = D('AgentRelation');
                $agent_name ? $where['_string'] = ' ((a.name = "'.$agent_name.'")  OR ( a.weixin = "'.$agent_name.'"))' : '';
                $where['ar.is_cancel'] = 0;
                $where['ar.is_validate'] = 1;
                $join = ' ar LEFT JOIN agent a ON ar.member_id = a.agentId ';
                if($member_info['is_agent'] == 0){ //工厂
                    //显示所有省代
                    $where['a.star'] = 1;
                }else{
                    //显示所有下级
                    $where['ar.pid'] = $member_info['agentid'];
                    $where['ar.agent_grade'] = $member_info['agent_grade']+1;
                }
                
                $count      = $AgentRelation->where($where)->join($join)->count();// 查询满足要求的总记录数
                $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
                $show       = $Page->show();// 分页显示输出
                $this->assign('page',$show);// 赋值分页输出
            
                $list = $AgentRelation->getList($where,$limit,$page,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);

//                dump($AgentRelation->getLastSql());
//                $agent_name ? '' : session('agent_list_'.$member_info['agentid'], $list);
//            }
//            dump($list);
            
            $this->assign('name',$agent_name);
            $this->assign('empty','<span class="empty">没有代理</span>');
            $this->assign('list',$list);
            $this->display('selAgent');
        }else{
            dump('不能发货!');
        }
        
        return FALSE;
    }
    
    //搜索选择代理
    public function selSearchAgent() {
        
        $member_info = $this->memberInfo();
        $agent_name = I('name');
        $page = I('p',1);
        $limit=10;
//        dump($member_info);
        if($member_info['agent_grade'] < 4){
            $agent_name ? $list = '' : '';
//            if(empty($list)){
                $AgentRelation = D('AgentRelation');
                if($agent_name){
                    $where_string = ' AND ((a.name = "'.$agent_name.'")  OR ( a.agentNo = "'.$agent_name.'"))';
                }
              
                $where['ar.is_cancel'] = 0;
                $where['ar.is_validate'] = 1;
                $join = ' ar RIGHT JOIN agent a ON ar.member_id = a.agentId ';
                
                if($member_info['is_agent'] == 0){ //工厂显示全部
                    //显示所有省代
                    $where['_string'] = '1=1 '.$where_string;
//                    $where['a.star'] = 1;
                }else{
                    //显示所有属于他的下级
                    $where['_string'] = ' (ar.agent1_id = "'.$member_info['agentid'].'"  OR ar.agent2_id = "'.$member_info['agentid'].'" OR ar.agent3_id = "'.$member_info['agentid'].'" OR a.parent_id="'.$member_info['agentid'].'") '.$where_string;
                 
                }
                
                $count      = $AgentRelation->where($where)->join($join)->count();// 查询满足要求的总记录数
                $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
                $show       = $Page->show();// 分页显示输出
                $this->assign('page',$show);// 赋值分页输出
            
                $list = $AgentRelation->getList($where,$limit,$page,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),$join);
                
//                dump($AgentRelation->getLastSql());
//                $agent_name ? '' : session('agent_list_'.$member_info['agentid'], $list);
//            }
//            dump($list);
            
            $this->assign('name',$agent_name);
            $this->assign('empty','<span class="empty">没有代理</span>');
            $this->assign('list',$list);
            $this->display('selAgent');
        }else{
            dump('不能发货!');
        }
        
        return FALSE;
    }
    
    //删除购物车的商品
    public function delCardGoods() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        
        $id = I('id');
        $agent_id = I('aid');
        
        if(empty($id)){
            $return['msg'] = '请选择要删除的商品!';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理!';
            $this->ajaxReturn($return,'json');
        }
        
        $member_info = $this->memberInfo();
        $admin_id = $member_info['agentid'];
        
        $Cards = D('Cards');
        $where['admin_id'] = $admin_id;
        $where['member_id'] = $agent_id;
        $where['id'] = $id;
        
        $result = $Cards->delData($where);
        
        if($result){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
        }
        
        $this->ajaxReturn($return,'json');
        
    }
    
    //发货页面
    public function deliverGoodsView() {
        
        $agent_id = I('aid');
        
        $member_info = $this->memberInfo();
        $admin_id = $member_info['agentid'];
        
        //获取微信JS_SDK配置信息
        $weixin_js_sdk_info = A('Public')->getWxJsInfo();
        if($weixin_js_sdk_info){
            $this->assign('wxjsinfo',$weixin_js_sdk_info);
        }
        
        //获取代理信息
        $agent_info = $this->getAgent($agent_id);
        $this->assign('agent_info',$agent_info);
        
        //获取商品
        $Cards = D('Cards');
        $card_where['admin_id'] = $admin_id;
        $card_where['member_id'] = $agent_id;
        $goods_list = $Cards->getAllList($card_where,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        $this->assign('goods_list',$goods_list);
        
        $this->assign('is_agent',$member_info['is_agent']);
        $this->assign('agent_id',$agent_id);
        $this->display('deliverGoodsView');
      
    }
    
    //发货
    public function deliverGoods(){
        ini_set("max_execution_time", 0);
        ini_set('memory_limit', '1024M');
        
        $member_info = $this->memberInfo();
        $return = array('status'=>0,'msg'=>'发货失败,请重新发货','result'=>'');
        $agent_id = I('agent_id'); //收货人ID
        $admin_id = $member_info['agentid']; //发货人ID
        $admin_name = $member_info['name']; //发货人
        $admin_lv = $member_info['agent_grade'] ? $member_info['agent_grade'] : 0; //发货人代理等级
        $admin_is_agent = $member_info['is_agent'];
        $is_add_success = TRUE; //是否添加成功
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理商!';
            $this->ajaxReturn($return,'json');
        }
       
        $agent_info = $this->getAgent($agent_id);
        if(empty($agent_info)){
            $return['msg'] = '请选择正确的代理商!';
            $this->ajaxReturn($return,'json');
        }
        
        $Cards = D('Cards');
        $card_where['admin_id'] = $admin_id;
        $card_where['member_id'] = $agent_id;
        $goods_list = $Cards->getAllList($card_where,'',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if(empty($goods_list)){
            $return['msg'] = '请选择商品!';
            $this->ajaxReturn($return,'json');
        }
      
        $agent_name = $agent_info['name'];
        $agent_lv = $agent_info['agent_grade'];
       
        $time = time();
        $OrderInfo = D('OrderInfo');
        
        $OrderInfo->startTrans(); //开启事务
        
        //添加订单
        $order_sn = $this->makeOrderSn();
        $add_order_data['add_time'] = date('Y-m-d H:i:s');
        $add_order_data['order_sn'] = $order_sn;
        $add_order_data['admin_id'] = $admin_id;
        $add_order_data['admin_name'] = $admin_name;
        $add_order_data['admin_lv'] = $admin_lv;
        $add_order_data['order_status'] = 1; //订单状态: 1:发货,2:退货
        $add_order_data['member_id'] = $agent_id;
        $add_order_data['member_name'] = $agent_name;
        $add_order_data['member_lv'] = $agent_lv;
       
        $order_id = $OrderInfo->addData($add_order_data);
        
        if($order_id){
            
            $OrderGoods = D('OrderGoods');
            $DeliverGoods = D('DeliverGoods');
            $AgentGoodsStockRale = D('AgentGoodsStockRale');
            $AgentMonthProfit = D('AgentMonthProfit'); 
            $Agent = D('Agent');
            $AgentProfitLog = D('AgentProfitLog');
            $CompanyReportsLog = D('CompanyReportsLog');
            
            //初始化订单总金额与销售利润金额
            $order_total_money = 0;
            $order_total_profit = 0;
            
            foreach ($goods_list as $k => $v) {
                //添加订单商品
                $dataTime = date('Y-m-d H:i:s');
                $year = date('Y');
                $month = date('m');
                $day = date('d');
                $code = $v['code'];
                $code_type = $v['code_type'];
                $goods_id = $v['goods_id'];
                $goods_name = $v['goods_name'];
                $goods_number = $v['goods_number'];
                $admin_price = $v['admin_price'];
                $member_price = $v['member_price'];
                $sale_total_profit = ($member_price - $admin_price)*$goods_number;
                $sale_total_money = $goods_number*$member_price;
                $add_order_goods[$k]['order_sn'] = $order_sn;
                $add_order_goods[$k]['order_id'] = $order_id;
                $add_order_goods[$k]['code'] = $code;
                $add_order_goods[$k]['code_type'] = $code_type;
                $add_order_goods[$k]['goods_id'] = $goods_id;
                $add_order_goods[$k]['goods_name'] = $goods_name;
                $add_order_goods[$k]['goods_number'] = $goods_number;
                $add_order_goods[$k]['market_price'] = $v['market_price'];
                $add_order_goods[$k]['admin_price'] = $admin_price;
                $add_order_goods[$k]['member_price'] = $member_price;
                $add_order_goods[$k]['goods_profit'] = $v['goods_profit'];
                $add_order_goods[$k]['is_gift'] = 0;
                $add_order_goods[$k]['admin_id'] = $admin_id;
                $add_order_goods[$k]['member_id'] = $agent_id;
                
                //统计订单总金额与销售总利润
                $order_total_money += $sale_total_money;
                $order_total_profit += $sale_total_profit;
                
                //获取商品代理等级分润金额
                $goodsProfitLv1 = $this->getAgentGoodsProfit($goods_id, 1); 
                $goodsProfitLv2 = $this->getAgentGoodsProfit($goods_id, 2);
                $goodsProfitLv3 = $this->getAgentGoodsProfit($goods_id, 3);
                
                //每一盒产品公司应返总利润
                $companyOneGoodsTotalProfit = $goodsProfitLv1['top1_profit'] + $goodsProfitLv1['top2_profit'] + $goodsProfitLv2['agent1_profit'] + $goodsProfitLv3['agent1_profit'] + $goodsProfitLv3['agent2_profit'] + $goodsProfitLv3['agent3_profit'];
                    
                //添加分润记录公用数据
                $profitLogData['order_id'] = $order_id;
                $profitLogData['order_sn'] = $order_sn;
                $profitLogData['code'] = $code;
                $profitLogData['code_type'] = $code_type;
                $profitLogData['goods_id'] = $goods_id;
                $profitLogData['goods_name'] = $goods_name;
                $profitLogData['goods_num'] = $goods_number;
                $profitLogData['goods_price'] = $member_price;
                $profitLogData['year'] = $year;
                $profitLogData['month'] = $month;
                $profitLogData['day'] = $day;
                $profitLogData['is_profit'] = 2; //是否已分润:1:已分润,2:未分润
                $profitLogData['is_refund'] = 1;//是否退货: 1:否,2:是
               
                //发货人为代理,减库存,增加出库总数量与出库总金额
                if($admin_is_agent == 1){
                    
                    //产品统计
                    $sale_where['agent_id'] = $admin_id;
                    $sale_where['goods_id'] = $goods_id;

                    $AgentGoodsStockRale->editDec($sale_where,'goods_stock',$goods_number,1); //减少库存
                    $AgentGoodsStockRale->editInc($sale_where,'sale_total_stock',$goods_number,1); //增加出库总数量
                    $AgentGoodsStockRale->editInc($sale_where,'sale_total_money',$sale_total_money,1); //增加出库总金额
                   
                    //每月统计
                    $month_sale_where['year'] = $year;
                    $month_sale_where['month'] = $month;
                    $month_sale_where['agent_id'] = $admin_id;
                   
                    $AgentMonthProfit->editInc($month_sale_where,'sale_total_stock',$goods_number,1);//增加出库总数量
                    $AgentMonthProfit->editInc($month_sale_where,'sale_profit',$sale_total_profit,1);//增加销售利润总金额
                    $AgentMonthProfit->editInc($month_sale_where,'sale_total_money',$sale_total_money,1); //增加出库总金额
                   
                    //代理总的统计
                    $agent_where['agentId'] = $admin_id;
                    $Agent->editInc($agent_where,'all_sale_total_money',$sale_total_money,1); //增加代理出货总金额
                    $Agent->editInc($agent_where,'all_sale_total_profit',$sale_total_profit,1); //增加代理销售额总利润
                    $Agent->editInc($agent_where,'all_buy_total_stock',$goods_number,1); //增加出库总库存
                    
                    //公司返利的统计: 目前只有大区与总代发货才有返利  开始
                        //添加公司每月分润总额报表
                        $companyAllGoodsTotalProfit = $companyOneGoodsTotalProfit*$goods_number;
                        $company_reports_where['year'] = $year;
                        $company_reports_where['month'] = $month;

                        $company_log_result = $CompanyReportsLog->editInc($company_reports_where,'total_profit',$companyAllGoodsTotalProfit,0); 

                        if(!$company_log_result){
                            $is_add_success = FALSE;
                        }

                        switch ($admin_lv) {
                            case 2: //大区发货:公司只能返利给上级官方
                                
                                    $profit_agent1_id = $member_info['agent1_id'];
                                    $profit_agent1_info = $this->getAgent($profit_agent1_id);
                        
                                    //必须是未取消跟审核通过的才能享受分润
                                    if($profit_agent1_info && $profit_agent1_info['is_cancel'] == 0 && $profit_agent1_info['is_validate'] == 1){
                                        
                                        $agent1_total_profit = $goodsProfitLv2['agent1_profit']*$goods_number;
                                        
                                        //公司返利的记录
                                        $profitLogData['profit_type'] = 1;//分润的类型: 1:下级,2:推荐,3:买断首次分润
                                        $profitLogData['profit_agent_id'] = $profit_agent1_id;
                                        $profitLogData['profit_agent_name'] = $profit_agent1_info['name'];
                                        $profitLogData['profit_agent_lv'] = $profit_agent1_info['agent_grade'];
                                        $profitLogData['buy_agent_id'] = $agent_id;
                                        $profitLogData['buy_agent_name'] = $agent_name;
                                        $profitLogData['buy_agent_lv'] = $agent_lv;
                                        $profitLogData['profit_total_money'] = $agent1_total_profit;
                                        $profitLogData['profit_money'] = $goodsProfitLv2['agent1_profit'];

                                        $agent_profit_result = $AgentProfitLog->addData($profitLogData);
                                        
                                        //添加该代理公司总返利
                                        $profit_total_result = $Agent->editInc(array('agentId'=>$profit_agent1_id),'company_total_profit',$agent1_total_profit,0); 
                                        
                                        //添加该代理公司月返利
                                        $month_profit_where['agent_id'] = $profit_agent1_id;
                                        $month_profit_where['year'] = $year;
                                        $month_profit_where['month'] = $month;
                                        
                                        $month_profit_total_result = $AgentMonthProfit->editInc($month_profit_where,'company_profit',$agent1_total_profit,0); 
                                        
                                        if(!($agent_profit_result && $profit_total_result && $month_profit_total_result)){
                                            $is_add_success = FALSE;
                                        }
                                       
                                    }

                                break;

                            case 3: //总代发货:公司返利给直接:官方,大区,总代自己
                                    
                                    //官方返利
                                        $profit_agent1_id = $member_info['agent1_id'];
                                        $profit_agent1_info = $this->getAgent($profit_agent1_id);

                                        //必须是未取消跟审核通过的才能享受分润
                                        if($profit_agent1_info && $profit_agent1_info['is_cancel'] == 0 && $profit_agent1_info['is_validate'] == 1){
                                            $agent1_profit = $goodsProfitLv3['agent1_profit'];
                                            $agent1_total_profit = $agent1_profit*$goods_number;

                                            //公司返利的记录
                                            $profitLogData['profit_type'] = 1;//分润的类型: 1:下级,2:推荐,3:买断首次分润
                                            $profitLogData['profit_agent_id'] = $profit_agent1_id;
                                            $profitLogData['profit_agent_name'] = $profit_agent1_info['name'];
                                            $profitLogData['profit_agent_lv'] = $profit_agent1_info['agent_grade'];
                                            $profitLogData['buy_agent_id'] = $agent_id;
                                            $profitLogData['buy_agent_name'] = $agent_name;
                                            $profitLogData['buy_agent_lv'] = $agent_lv;
                                            $profitLogData['profit_total_money'] = $agent1_total_profit;
                                            $profitLogData['profit_money'] = $agent1_profit;

                                            $agent_profit_result = $AgentProfitLog->addData($profitLogData);

                                            //添加该代理公司总返利
                                            $profit_total_result = $Agent->editInc(array('agentId'=>$profit_agent1_id),'company_total_profit',$agent1_total_profit,0); 

                                            //添加该代理公司月返利
                                            $month_profit_where['agent_id'] = $profit_agent1_id;
                                            $month_profit_where['year'] = $year;
                                            $month_profit_where['month'] = $month;

                                            $month_profit_total_result = $AgentMonthProfit->editInc($month_profit_where,'company_profit',$agent1_total_profit,0); 

                                            if(!($agent_profit_result && $profit_total_result && $month_profit_total_result)){
                                                $is_add_success = FALSE;
                                            }

                                        }
                                        
                                    //大区返利
                                        $profit_agent2_id = $member_info['agent2_id'];
                                        $profit_agent2_info = $this->getAgent($profit_agent2_id);

                                        //必须是未取消跟审核通过的才能享受分润
                                        if($profit_agent2_info && $profit_agent2_info['is_cancel'] == 0 && $profit_agent2_info['is_validate'] == 1){
                                            $agent2_profit = $goodsProfitLv3['agent2_profit'];
                                            $agent2_total_profit = $agent2_profit*$goods_number;

                                            //公司返利的记录
                                            $profitLogData['profit_type'] = 1;//分润的类型: 1:下级,2:推荐,3:买断首次分润
                                            $profitLogData['profit_agent_id'] = $profit_agent2_id;
                                            $profitLogData['profit_agent_name'] = $profit_agent2_info['name'];
                                            $profitLogData['profit_agent_lv'] = $profit_agent2_info['agent_grade'];
                                            $profitLogData['buy_agent_id'] = $agent_id;
                                            $profitLogData['buy_agent_name'] = $agent_name;
                                            $profitLogData['buy_agent_lv'] = $agent_lv;
                                            $profitLogData['profit_total_money'] = $agent2_total_profit;
                                            $profitLogData['profit_money'] = $agent2_profit;

                                            $agent_profit_result = $AgentProfitLog->addData($profitLogData);

                                            //添加该代理公司总返利
                                            $profit_total_result = $Agent->editInc(array('agentId'=>$profit_agent2_id),'company_total_profit',$agent2_total_profit,0); 

                                            //添加该代理公司月返利
                                            $month_profit_where['agent_id'] = $profit_agent2_id;
                                            $month_profit_where['year'] = $year;
                                            $month_profit_where['month'] = $month;

                                            $month_profit_total_result = $AgentMonthProfit->editInc($month_profit_where,'company_profit',$agent2_total_profit,0); 

                                            if(!($agent_profit_result && $profit_total_result && $month_profit_total_result)){
                                                $is_add_success = FALSE;
                                            }

                                        }
                                        
                                    //总代返利
                                        $profit_agent3_id = $member_info['agent3_id'];
                                        $profit_agent3_info = $this->getAgent($profit_agent3_id);

                                        //必须是未取消跟审核通过的才能享受分润
                                        if($profit_agent3_info && $profit_agent3_info['is_cancel'] == 0 && $profit_agent3_info['is_validate'] == 1){
                                            $agent3_profit = $goodsProfitLv3['agent3_profit'];
                                            $agent3_total_profit = $agent3_profit*$goods_number;

                                            //公司返利的记录
                                            $profitLogData['profit_type'] = 1;//分润的类型: 1:下级,2:推荐,3:买断首次分润
                                            $profitLogData['profit_agent_id'] = $profit_agent3_id;
                                            $profitLogData['profit_agent_name'] = $profit_agent3_info['name'];
                                            $profitLogData['profit_agent_lv'] = $profit_agent3_info['agent_grade'];
                                            $profitLogData['buy_agent_id'] = $agent_id;
                                            $profitLogData['buy_agent_name'] = $agent_name;
                                            $profitLogData['buy_agent_lv'] = $agent_lv;
                                            $profitLogData['profit_total_money'] = $agent3_total_profit;
                                            $profitLogData['profit_money'] = $agent3_profit;

                                            $agent_profit_result = $AgentProfitLog->addData($profitLogData);

                                            //添加该代理公司总返利
                                            $profit_total_result = $Agent->editInc(array('agentId'=>$profit_agent3_id),'company_total_profit',$agent3_total_profit,0); 

                                            //添加该代理公司月返利
                                            $month_profit_where['agent_id'] = $profit_agent3_id;
                                            $month_profit_where['year'] = $year;
                                            $month_profit_where['month'] = $month;

                                            $month_profit_total_result = $AgentMonthProfit->editInc($month_profit_where,'company_profit',$agent3_total_profit,0); 

                                            if(!($agent_profit_result && $profit_total_result && $month_profit_total_result)){
                                                $is_add_success = FALSE;
                                            }

                                        }

                                break;
                        }

                    //公司返利的统计: 目前只有大区与总代发货才有返利  结束
                    
                    
                 }else{ //发货人为工厂:直接返利给直接官方与间接官方  公司返利
                     
                    //添加公司每月分润总额报表
                    $companyAllGoodsTotalProfit = $companyOneGoodsTotalProfit*$goods_number;
                    $company_reports_where['year'] = $year;
                    $company_reports_where['month'] = $month;
                    
                    $company_log_count = $CompanyReportsLog->getCount($company_reports_where);
                    
                    if($company_log_count > 0){
                        $company_log_result = $CompanyReportsLog->editInc($company_reports_where,'total_profit',$companyAllGoodsTotalProfit,0); 
                    }else{
                        $companyReportsData['year'] = $year;
                        $companyReportsData['month'] = $month;
                        $companyReportsData['total_profit'] = $companyAllGoodsTotalProfit;
                        $companyReportsData['add_time'] = $dataTime;

                        $company_log_result = $CompanyReportsLog->addData($companyReportsData);
                    
                    }
                    
                    if(!$company_log_result){
                        $is_add_success = FALSE;
                    }
                    
                    
                    $agent_top1_id = $agent_info['top1_id'];
                    $agent_top2_id = $agent_info['top2_id'];
                            
                    $profitLogData['profit_type'] = 2;//分润的类型: 1:下级,2:推荐,3:买断首次分润
                    $profitLogData['buy_agent_id'] = $agent_id;
                    $profitLogData['buy_agent_name'] = $agent_name;
                    $profitLogData['buy_agent_lv'] = $agent_lv;
                    
                    if($agent_top1_id){
                        $agent_top1_info = $this->getAgent($agent_top1_id);
                        
                        //必须是未取消跟审核通过的才能享受分润
                        if($agent_top1_info && $agent_top1_info['is_cancel'] == 0 && $agent_top1_info['is_validate'] == 1){
                            $top1_profit = $goodsProfitLv1['top1_profit'];
                            $top1_profit_total_money = $top1_profit*$goods_number;
                            
                            //添加间接官方分润记录
                            $profitLogData['profit_agent_id'] = $agent_top1_id;
                            $profitLogData['profit_agent_name'] = $agent_top1_info['name'];
                            $profitLogData['profit_agent_lv'] = $agent_top1_info['agent_grade'];
                            $profitLogData['profit_total_money'] = $top1_profit_total_money;
                            $profitLogData['profit_money'] = $top1_profit;
                            
                            $profit_result = $AgentProfitLog->addData($profitLogData);
                            
                            //添加该代理公司总返利
                            $profit_total_result = $Agent->editInc(array('agentId'=>$agent_top1_id),'company_total_profit',$top1_profit_total_money,0); 
                            
                            //添加该代理公司月返利
                            $month_profit_where['agent_id'] = $agent_top1_id;
                            $month_profit_where['year'] = $year;
                            $month_profit_where['month'] = $month;
                            $month_profit_total_count = $AgentMonthProfit->getCount($month_profit_where);
                            
                            if($month_profit_total_count > 0){
                                $month_profit_total_result = $AgentMonthProfit->editInc($month_profit_where,'company_profit',$top1_profit_total_money,0); 
                            }else{
                                $monthProfitData['agent_id'] = $agent_top1_id;
                                $monthProfitData['year'] = $year;
                                $monthProfitData['month'] = $month;
                                $monthProfitData['company_profit'] = $top1_profit_total_money;
                                $monthProfitData['is_profit'] = 2;
                                $monthProfitData['add_time'] = $dataTime;
                                
                                $month_profit_total_result = $AgentMonthProfit->addData($monthProfitData);
                            }
                            
                            if(!($profit_result && $month_profit_total_result && $profit_total_result)){
                                $is_add_success = FALSE;
                            }
                        }
                        
                    }
                    
                    if($agent_top2_id){
                        $agent_top2_info = $this->getAgent($agent_top2_id);
                        
                        //必须是未取消跟审核通过的才能享受分润
                        if($agent_top2_info && $agent_top2_info['is_cancel'] == 0 && $agent_top2_info['is_validate'] == 1){
                            
                            $top2_profit = $goodsProfitLv1['top2_profit'];
                            $top2_profit_total_money = $top2_profit*$goods_number;
                            
                            //添加直接官方分润记录
                            $profitLogData['profit_agent_id'] = $agent_top2_id;
                            $profitLogData['profit_agent_name'] = $agent_top2_info['name'];
                            $profitLogData['profit_agent_lv'] = $agent_top2_info['agent_grade'];
                            $profitLogData['profit_total_money'] = $top2_profit;
                            $profitLogData['profit_money'] = $top2_profit_total_money;

                            $profit_result = $AgentProfitLog->addData($profitLogData);
                            
                            //添加该代理公司总返利
                            $profit_total_result = $Agent->editInc(array('agentId'=>$agent_top2_id),'company_total_profit',$top2_profit_total_money,0); 
                            
                            //添加该代理公司月返利
                            $month_profit_where['agent_id'] = $agent_top2_id;
                            $month_profit_where['year'] = $year;
                            $month_profit_where['month'] = $month;
                            $month_profit_total_count = $AgentMonthProfit->getCount($month_profit_where);
                            
                            if($month_profit_total_count > 0){
                                $month_profit_total_result = $AgentMonthProfit->editInc($month_profit_where,'company_profit',$top2_profit_total_money,0); 
                            }else{
                                $monthProfitData['agent_id'] = $agent_top2_id;
                                $monthProfitData['year'] = $year;
                                $monthProfitData['month'] = $month;
                                $monthProfitData['company_profit'] = $top2_profit_total_money;
                                $monthProfitData['is_profit'] = 2;
                                $monthProfitData['add_time'] = $dataTime;
                                
                                $month_profit_total_result = $AgentMonthProfit->addData($monthProfitData);
                            }
                            
                            if(!($profit_result && $month_profit_total_result && $profit_total_result)){
                                $is_add_success = FALSE;
                            }
                        }
                    }
                    
                 }
                
                //增加收货人库存,增加进库总数量与进库总金额
                 if($agent_lv >= 1){
                     
                    //产品统计
                    $buy_where['agent_id'] = $agent_id;
                    $buy_where['goods_id'] = $goods_id;

                    $buy_count = $AgentGoodsStockRale->getCount($buy_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
                    if($buy_count > 0){
                        $AgentGoodsStockRale->editInc($buy_where,'goods_stock',$goods_number,1); //增加库存
                        $AgentGoodsStockRale->editInc($buy_where,'buy_total_stock',$goods_number,1); //增加进库总数量
                        $AgentGoodsStockRale->editInc($buy_where,'buy_total_money',$sale_total_money,1); //增加进库总金额
                    }else{
                        $stockData['agent_id'] = $agent_info['agentid'];
                        $stockData['goods_id'] = $goods_id;
                        $stockData['goods_stock'] = $goods_number;
                        $stockData['buy_total_stock'] = $goods_number; //进库总数量
                        $stockData['buy_total_money'] = $sale_total_money; //进库总金额
                        
                        $AgentGoodsStockRale->addData($stockData);
                    }
                    
                    //每月统计
                    $month_buy_where['year'] = $year;
                    $month_buy_where['month'] = $month;
                    $month_buy_where['agent_id'] = $agent_id;
                    $month_buy_count = $AgentMonthProfit->getCount($month_buy_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
                    
                    if($month_buy_count > 0){
                        $AgentMonthProfit->editInc($month_buy_where,'buy_total_stock',$goods_number,1);//增加出库总数量
                        $AgentMonthProfit->editInc($month_buy_where,'buy_total_money',$sale_total_money,1); //增加出库总金额
                    }else{
                        $monthSaleData['agent_id'] = $agent_id;
                        $monthSaleData['year'] = $year;
                        $monthSaleData['month'] = $month;
                        $monthSaleData['buy_total_money'] = $sale_total_money;
                        $monthSaleData['buy_total_stock'] = $goods_number;
                        $monthSaleData['is_profit'] = 2;
                        $monthSaleData['add_time'] = $dataTime;
                        $AgentMonthProfit->addData($monthSaleData);
                    }
                    
                    //代理总的统计
                    $agent_where['agentId'] = $agent_id;
                    $Agent->editInc($agent_where,'all_buy_total_money',$sale_total_money,1); //增加代理进货总金额
                    $Agent->editInc($agent_where,'all_sale_total_stock',$goods_number,1); //增加进库总库存
                    
                }
                
                //添加物流记录 
                $add_deliver_data[$k]['order_id'] = $order_id;
                $add_deliver_data[$k]['order_sn'] = $order_sn;
                $add_deliver_data[$k]['goods_id'] = $goods_id;
                $add_deliver_data[$k]['agent_id'] = $agent_id;
                $add_deliver_data[$k]['code'] = $code;
                $add_deliver_data[$k]['code_type'] = $code_type;
                $add_deliver_data[$k]['admin_id'] = $admin_id;
                $add_deliver_data[$k]['add_time'] = $time;
                
                //如果是中标或者大标的时候,需要重新设置小标的缓存
                if(in_array($code_type,array(1,4))){
                    $code_list = S(C('CODE_LABEL').$code);
                    
                    if(empty($code_list)){
                        switch ($code_type) {
                            case 1:
                                $code_where['max_code'] = $code;
                                break;
                            case 4:
                                $code_where['middle_code'] = $code;
                                break;
                        }

                        $code_list = $LabelCode->getAllList($code_where,'',array('field'=>array('min_code'),'is_opposite'=>false));
                        if($code_list){
                            S(C('CODE_LABEL').$code,serialize($code_list));
                        }
                    }else{
                        $code_list = unserialize($code_list);
                    }
                   
                    foreach ($code_list as $ck => $cv) {
                        $cache_info = array('goods_id'=>$goods_id,'agent_id'=>$agent_id);
                        $suc_code = $cv['min_code'];
                        S($suc_code,serialize($cache_info));
                        $suc_code_list[] = $suc_code;
                    }
                  
                }else{
                    $cache_info = array('goods_id'=>$goods_id,'agent_id'=>$agent_id);
                    S($code,serialize($cache_info));
                    $suc_code_list[] = $code;
                }

            }
            
            //修改订单总金额与订单总利润
            $order_edit_result = $OrderInfo->editData(array('order_id'=>$order_id),array('order_total_money'=>$order_total_money,'order_total_profit'=>$order_total_profit));
            
            //添加发货商品
            $order_goods_result = $OrderGoods->addAll($add_order_goods);

            //添加物流记录
            $deliver_result = $DeliverGoods->addAll($add_deliver_data);
               
        }
       
        if($order_goods_result && $deliver_result && $is_add_success && $order_edit_result){
            //删除购物车的商品
            $del_where['admin_id'] = $admin_id;
            $del_where['member_id'] = $agent_id;
            $Cards->delData($del_where);
            
            $result_data['order_sn'] = $order_sn;
            $return = array('status'=>1,'msg'=>'发货成功','result'=>$result_data);
            $OrderInfo->commit(); //提交事务
        }else{
            $OrderInfo->rollback(); //事务回滚
            
            //清除缓存
            if(empty($suc_code_list)){
                foreach ($suc_code_list as $scv) {
                    S($code,NULL);
                }
            }
        }

        $this->ajaxReturn($return,'json');
    }
    
    //发货列表
    public function deliverList() {
        $member_id = $this->member_id;
        $limit=10;
        $page=I('p',1);
        $order='order_id DESC';
        
        $OrderInfo = D('OrderInfo');
        
        $where['admin_id'] = $member_id;
        $count      = $OrderInfo->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        
        $list = S('order_list_'.$member_id.'_'.$page);
        if(empty($list)){
            $list = $OrderInfo->getList($where,$limit,$page,$order,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            if($list){
                S('order_list_'.$member_id.'_'.$page,  serialize($list),3*60);
            }
        }else{
            $list = unserialize($list);
        }
        
        $this->assign('empty','<span class="empty">没有发货信息</span>');
        $this->assign('list',$list);
        
        $this->display('deliverList');
    }
    
    //发货详情
    public function showOrderInfo() {
        $order_sn = I('order_sn');
        if(empty($order_sn)){
            $this->error('请选择订单');
            return FALSE;
        }
      
        $cache_info = S(C('ORDER_GOODS').$order_sn);
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
                S(C('ORDER_GOODS').$order_sn,  serialize($cache_info));
            }
        }else{
            $cache_info = unserialize($cache_info);
        }
        
        $this->assign('empty','<span class="empty">没有查询到订单</span>');
        $this->assign('list',$cache_info['list']);
        $this->assign('order_info',$cache_info['order_info']);
        $this->display('showOrderInfo');
    }
    
    //验证条形码是否正确
    public function verifyCode() {
        $admin_info = $this->memberInfo();
        $return = array('status'=>0,'msg'=>'查询失败','result'=>'');
        $code = I('code');
        $admin_lv = $admin_info['agent_grade']; //发货人等级
        $is_agent = $admin_info['is_agent'];
        $admin_id = $admin_info['agentid']; //发货人ID
        $member_id = I('agent_id'); //收货人ID
        
        if(empty($code)){
            $return['msg'] = '条形码不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($member_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        //收货人信息
        $member_info = $this->getAgent($member_id);
        $member_lv = $member_info['agent_grade']; //收货人等级
        
        $Cards = D('Cards');
        
        //判断购物车中是否存在该条码
        $card_where['code'] = $code;
        $card_count = $Cards->getCount($card_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($card_count > 0){
            $return['msg'] = '该条码已经扫过,不能再扫!';
            $this->ajaxReturn($return,'json');
        }
        
        //标签长度 10位为旧标签,11位为新标签
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
                    break;
                case 4:
                    $where['middle_code'] = $code;
                    break;
                default :
                    $where['security_code'] = 0;
                    break;
            }
        }
        
        //查询标签
        $LabelCode = D('LabelCode');
        $label_info = $LabelCode->getDetail($where,array('field'=>array('max_code','min_code','middle_code','min_number','middle_number'),'is_opposite'=>false));
        
        if(!$label_info){
            $return['msg'] = '此条形码不存在!';
            $this->ajaxReturn($return,'json');
        }
        
        //获取大标与中标对应的小标数量
        switch ($code_type) {
            case 1:  //大标
                $laber_cache_key = 'all_maxmin_code_'.$code;
                $label_count = $label_info['min_number'];
                break;
            case 4: //中标
                $laber_cache_key = 'all_midmin_code_'.$code;
                $label_count = $label_info['middle_number'];
                break;
            default:
                $label_count = 1;
                break;
        }
        
        $DeliverGoods = D('DeliverGoods');
        
        //同一条码自己不能发两次货
        $my_where['admin_id'] = $admin_id;
        $my_where['code'] = $code;
        $my_delive = $DeliverGoods->getCount($my_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($my_delive > 0){
            $return['msg'] = '此商品您已经发过一次货,不能再发货!';
            $this->ajaxReturn($return,'json');
        }
        
        //工厂一定要是大标才能发货  是否是代理: 0:否,1:是
        if($is_agent == 0){
//            if($code_type != 1){
//                $return['msg'] = '只能发大标的商品!';
//                $this->ajaxReturn($return,'json');
//            }
            
            $goods_id = I('goods_id');
            $goods_info = $this->getGoods($goods_id);
        }
        
        if($is_agent == 1){
            
            //如果是代理: 
            if(in_array($code_type, array(1,4))){
                
                //获取大标或者中标对应小标的总数量
                $new_min_code_list = S($laber_cache_key);
                if(empty($new_min_code_list)){
                    $min_code_list = $LabelCode->getAllList($where,'',array('field'=>array('min_code'),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                    if($min_code_list){
                        foreach ($min_code_list as $cv) {
                            $new_min_code_list[] = $cv['min_code'];
                        }
                        S($laber_cache_key,serialize($new_min_code_list));
                    }
                }else{
                    $new_min_code_list = unserialize($new_min_code_list);
                }
                
                //如果代理发了大标或者中标中的小标商品,就不能再扫大标或者小标
                $de_all_where['code'] = array('IN',$new_min_code_list);
                $deliv_count = $DeliverGoods->getCount($de_all_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
                
                if($deliv_count > 0){
                    $return['msg'] = '因为您已经拆分了大标的商品,不能再发大标的商品!';
                    $this->ajaxReturn($return,'json');
                }
                
                //判断上级是否已经发货,不然下级不能发货
                $de_where['agent_id'] = $admin_id;
                $de_where['code']  = array(array('eq',$label_info['max_code']),array('eq',$label_info['middle_code']),array('eq',$code),'or'); 
                $is_delive = $DeliverGoods->getCount($de_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
                if($is_delive == 0){
                    $return['msg'] = '此条码未出库,您不能扫描!';
                    $this->ajaxReturn($return,'json');
                }
                
            }
            
            //判断上级是否已经发货,不然下级不能发货
            if($code_type == 2){
                
                $de_where['agent_id'] = $admin_id;
                $de_where['code']  = array(array('eq',$label_info['max_code']),array('eq',$label_info['middle_code']),array('eq',$code),'or'); 
                $is_delive = $DeliverGoods->getCount($de_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
               
                if($is_delive == 0){
                    $return['msg'] = '此条码未出库,您不能扫描!';
                    $this->ajaxReturn($return,'json');
                }
                
            }
            
            //查询商品信息
            $goodswhere['code'] = array(array('eq',$label_info['max_code']),array('eq',$label_info['middle_code']),array('eq',$code),'or'); 
            $OrderGoods = D('OrderGoods');
            $goodsOrderInfo = $OrderGoods->getDetail($goodswhere,array('field'=>array('goods_id'),'is_opposite'=>false));
            
            if($goodsOrderInfo){
                $goods_id = $goodsOrderInfo['goods_id'];
                $goods_info = $this->getGoods($goods_id);
            }
        }
        
        if(empty($goods_info)){
            $return['msg'] = '请选择商品!';
            $this->ajaxReturn($return,'json');
        }
        
        //获取商品的价格与利润
        $sale_profit = $this->getAgentGoodsProfit($goods_id,$admin_lv); //发货人的商品价格信息
        $buy_profit = $this->getAgentGoodsProfit($goods_id,$member_lv); //收货人的商品价格信息
        
        if(empty($sale_profit)){
            $return['msg'] = '没有发货人金额!';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($buy_profit)){
            $return['msg'] = '没有收货人金额!';
            $this->ajaxReturn($return,'json');
        }
        
        $goods_profit = $buy_profit['agent_price'] - $sale_profit['agent_price']; //商品利润
        
        //添加到购物车
        $addData['admin_id'] = $admin_id;
        $addData['member_id'] = $member_id;
        $addData['code'] = $code;
        $addData['code_type'] = $code_type;
        $addData['goods_id'] = $goods_id;
        $addData['goods_name'] = $goods_info['title'];
        $addData['goods_number'] = $label_count;
        $addData['market_price'] = $goods_info['mark_price'];
        $addData['admin_price'] = $sale_profit['agent_price'];
        $addData['member_price'] = $buy_profit['agent_price'];
        $addData['goods_profit'] = $goods_profit;
        
        //添加到购物车
        $card_id = $Cards->addData($addData);
        
        if(empty($card_id)){
            $this->ajaxReturn($return,'json');
        }
        
        $goods_info['card_id'] = $card_id;
        $return = array('status'=>1,'msg'=>'查询成功','result'=>$goods_info);
        $this->ajaxReturn($return,'json');
       
    }
    
    //获取商品
    public function getGoods($goods_id) {
        if(empty($goods_id)){
            return false;
        }
        
        $cacheKey = md5(C('GOODS_INFO').$goods_id);
        $goods_info = S($cacheKey);
        
        if(empty($goods_info)){
            $where['id'] = $goods_id;
            $Goods = D('Goods');
            $goods_info = $Goods->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
            if($goods_info){
                S($cacheKey,  serialize($goods_info));
            }
        }else{
            $goods_info = unserialize($goods_info);
        }
        
        return $goods_info;
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
    
    //生成随机订单号
    public function makeOrderSn() {
    
        for($i=0;$i<6;$i++){
            $str .=rand(0,9);
        }
        
        $format = date('YmdHis');
        $order_sn = $format.$str;
     
        return $order_sn;
    }
    
    //搜索商品
    public function searchGoods() {
        $return = array('status'=>0,'msg'=>'没有该商品,请重新输入','result'=>'');
        $goods_name =  I('goods_name');
        
        if(empty($goods_name)){
            $return['msg'] = '请输入商品名称';
        }else{
            $Goods = D('Goods');
            $where['title'] = array('LIKE','%'.$goods_name.'%');
            $list = $Goods->getAllList($where,$order='',$field=array('field'=>array(),'is_opposite'=>false),$cache=array('key'=>false,'expire'=>null,'cache_type'=>null));
            if($list){
                $return = array('status'=>1,'msg'=>'','result'=>$list); 
            }
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
    
    //提交授权页面
//    public function accreditSub() {
//        //代理等级
//        $agent_lv_list = C('MEMBER_LEVEL');
//        $member_info = $this->memberInfo();
//        $star = $member_info['star']+1;
//        
//        //授权号        
//        $Agent = D('Agent');
//        $agentNo = $Agent->makeAgentNo();
//        $this->assign('agentNo',$agentNo);
//        
//        //时间
//        $start_time = date('Y-m-d');
//        $this->assign('start_time',$start_time);
//        
//        $end_time = date('Y-m-d',  strtotime('+1 year -1 day'));
//        $this->assign('end_time',$end_time);
//        
//        $this->display();
//    }
    
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
    
}