<?php
/*
 * 公用管理
 */
namespace Mobile\Controller;
use Think\Controller;
class PublicController extends Controller {
    protected $Wechat = ''; //微信类
   
    public function __construct() {
        parent::__construct();
      
       //微信浏览器中才去调用
        $is_weixin = is_weixin();
        $this->assign('is_weixin',$is_weixin);
        
//        if(!APP_DEBUG && !$is_weixin){
//           echo '<h2>请在微信中打开!</h2>';
//           die;
//        }
       
        $action_name = ACTION_NAME;
        $this->assign('action_name',$action_name);
    }
    
    public function test1() {
        $this->display('test');
    }
    
    public function test() {
        header('Content-Type: text/event-stream'); 
        header('Cache-Control: no-cache'); 

        $time = date('r'); 
        echo "data: The server time is: {$time}\n\n"; 
        flush(); 
        die;
//                dump(md5('youlbaopay'));die;
        $str = '130632198702109043';
       dump(isCard($str));
        die;
        ini_set("max_execution_time", 0);
        $money = 10001;
        $mod_money = 20000; //微信限制单笔最高金额
        if($money > $mod_money){
            $mod_sup_money = $money%$mod_money;
            $sup_step = ceil($money/$mod_money);
            $pay_money = $mod_money;
        }else{
            $sup_step = 1;
            $pay_money = $money;
        }
        
        $pay_total_money = 0; //累计支付总金额
        G('begin');
        for($pi=1;$pi <= $sup_step;$pi++){
            if($pi > 1 && $sup_step == $pi){
                $pay_money = $mod_sup_money;
            }
            
            $pay_total_money += $pay_money;
            
            dump($pay_money);
            
            G('end');
            dump(G('begin','end',6).'s');
        }
        
        dump($pay_total_money);DIE;
        

        $options['token'] = C('WX_TOKEN');
        $options['appid'] = C('WX_APPID');
        $options['secret'] = C('WX_APPSECRET');
        $options['payKey'] = C('WX_PAY_KEY');
        $options['mch_id'] = C('WX_MCH_ID');
      
        $Wechat = new \Org\Util\Wechat($options);
        
        //查询企业付款
        $pay_result = $Wechat->getTransfersInfo($partner_trade_no  = '1288272801201607188396325184');
        dump($pay_result);die;
        
        
//        $base_info = $Wechat->getOauthAccessToken($callback = '', $state='', $scope='snsapi_base'); //snsapi_userinfo  snsapi_base
//        $openid = $base_info['openid'];
        
        $openid = 'oB2snuOfsiRHr302V4kzdp-Jxk6c';
        
        
        
    }
    
    
    public function clearAllCache() {
//      
//        $LabelCode = D('LabelCode');
//        dump($LabelCode->getDetail(array('id'=>790104)));die;
//        
        
        $Redis = new \Think\Cache\Driver\Redis();
        S('aa',111);
        dump(S('aa'));
        dump($Redis->clear());
        dump(S('aa'));
//        S('aa',111);
    }
    
    //导出特约的库存与清空特约的库存
    public function exportTyStock() {
        ini_set("max_execution_time", 0);
       
        $OrderGoods = D('OrderGoods');
        $AgentGoodsStockRale = D('AgentGoodsStockRale');
        $CashPrizeLog = D('CashPrizeLog');
        
//        $OrderGoods = M('OrderGoods','','DB_CONFIG1'); 
//        $AgentGoodsStockRale = M('AgentGoodsStockRale','','DB_CONFIG1'); 
//        $CashPrizeLog = M('CashPrizeLog','','DB_CONFIG1'); 
        
        $data = $OrderGoods->field('a2.agentId AS zd_member_id,a2.name AS zd_name,a2.weixin AS zd_weixin,a2.star AS zd_star,a.agentId AS ty_member_id,a.name AS ty_name,a.weixin AS ty_weixin,a.star AS ty_star,agsr.goods_stock AS zy_goods_stock,SUM(og.goods_number) AS count_goods_num')
                ->where(array('ar.agent_grade'=>4))
                ->join('og LEFT JOIN agent_relation ar ON ar.member_id = og.member_id')
                ->join('LEFT JOIN agent a ON a.agentId = og.member_id')
                ->join('LEFT JOIN agent a2 ON ar.pid = a2.agentId')
                ->join('LEFT JOIN agent_goods_stock_rale agsr ON ar.member_id = agsr.agent_id')
                ->group('og.member_id')
                ->having('count_goods_num > 0')
//                ->order('a.agentId')
                ->order('count_goods_num')
                ->select();
        
//        dump($data);
//        die;
        
        if($data){
            $is_edit_success = TRUE;
            $CASH_PRIZE_NUMBER = C('CASH_PRIZE_NUMBER');
            $url = C('GET_CASH_PRIZE_CODE_URL');
            $dataTime = date('Y-m-d H:i:s');
            
            $AgentGoodsStockRale->startTrans();
           
            foreach ($data as $sk => $sv) {
                $member_id = $sv['ty_member_id'];
                $count_goods_num = $sv['count_goods_num'];
                
                //清空特约的库存
                $editResult = $AgentGoodsStockRale->where(array('agent_id'=>$member_id))->save(array('goods_stock'=>0)); ;
                            
                
                if(!$editResult){
                    $is_edit_success = FALSE;
                }
                
                //分配获奖码
                if($count_goods_num >= 10){
                    $prize_count = $CashPrizeLog->where(array('agent_id'=>$member_id))->count();

                    $code_number = floor(($count_goods_num - $prize_count*$CASH_PRIZE_NUMBER)/$CASH_PRIZE_NUMBER);

                    if($code_number > 0){

                        $url_params = array('num'=>$code_number,'pw'=>1);
                        $url_method = 'GET';

                        $return_data = http($url, $url_params, $url_method);

                        if($return_data !== FALSE){
                            $json_data = json_decode($return_data);
                            $return_result = $json_data->result->list;

                            if($json_data->status == 1 && !empty($return_result)){

                                foreach ($return_result as $rk => $rv) {
                                    $prize_code = $rv->number;

                                    if($prize_code){
                                        $addResultData[$rk]['agent_id'] = $member_id;
                                        $addResultData[$rk]['prize_code'] = $prize_code;
                                        $addResultData[$rk]['is_prize'] = 2; //是否兑奖(1:已兑奖,2:未兑奖)
                                        $addResultData[$rk]['get_time'] = $dataTime;
                                        $addResultData[$rk]['out_time'] = date('Y-m-d H:i:s',strtotime(' +7 day'));
                                        $addResultData[$rk]['add_time'] = $dataTime;
                                    }
                                }

                                $addCashResult = $CashPrizeLog->addAll($addResultData);

                                if(!$addCashResult){
                                    $AgentGoodsStockRale->rollback(); //事务回滚
                                    exit('添加兑奖码失败');
                                }

                            }else{

                                $AgentGoodsStockRale->rollback(); //事务回滚
                                exit('获取兑奖码失败');
                            }
                        }else{

                            $AgentGoodsStockRale->rollback(); //事务回滚
                            exit('请求兑奖码失败');
                        }
                    }
                }
            }
           
            if($is_edit_success){
                $AgentGoodsStockRale->commit();
                
                $config = array(
                    'fields'=>array('总代ID','总代姓名','总代微信号','总代等级','特约ID','特约姓名','特约微信号','特约等级','特约库存数量','特约进库数量'),//导入/导出文件字段[导入时为数据字段,导出时为字段标题]
                     'data'=>$data, //导出Excel的数组
                     'savename'=>date('Y-m-d_H_i_s').'_特约库存数量',
                     'title'=>'特约库存数量',     //导出文件栏目标题
                     'suffix'=>'xlsx',//文件格式
                   );

                $Excel = new \Common\Library\Excel($config);
                $Excel::export($data);
            }else{
                $AgentGoodsStockRale->rollback();
                exit('修改特约库存失败');
            }
        }else{
            exit('没有查询到数据');
        }
        
    }
    
    //修改有问题的小码
    public function editMinCode() {
        ini_set("max_execution_time", 0);
       
//        $OrderGoods = D('OrderGoods');
      
        $OrderGoods = M('OrderGoods','','DB_CONFIG1'); 
        $sql = 'SELECT id,CODE,add_time FROM order_goods 
                WHERE CODE IN(SELECT middle_code FROM label_code WHERE middle_code IN(SELECT CODE FROM order_goods WHERE goods_number=1)) 
                AND goods_number=1';
        $data = $OrderGoods->query($sql);
        $suc_num = 0;
        if($data){
            foreach ($data as $k => $v) {
                $v['code'] = 'm'.$v['code'];
//                dump($v);
                $result = $OrderGoods->save($v); 
                if($result){
                    $suc_num++;
                }
            }
        }
        dump($suc_num);
        
    }
    
    //更改代理销售信息
    public function editAgentSaleInfo() {
        ini_set("max_execution_time", 0);
       
        $AgentMonthProfit = D('AgentMonthProfit');
        $AgentGoodsStockRale = D('AgentGoodsStockRale');
        $Agent = D('Agent');
        
//        $AgentMonthProfit = M('AgentMonthProfit','','DB_CONFIG1'); 
//        $AgentGoodsStockRale = M('AgentGoodsStockRale','','DB_CONFIG1'); 
//        $Agent = M('Agent','','DB_CONFIG1'); 
        
        $where['ar.agent_grade'] = 4;
        $where['agsr.buy_total_stock'] = array('gt',0);
        
        $data = $AgentGoodsStockRale->field('agsr.*')
                ->where($where)
                ->join(' agsr LEFT JOIN agent_relation ar ON ar.member_id = agsr.agent_id')
                ->select();
        
        
        dump(count($data));
//        dump($data);
//        die;
        
        if($data){
            $is_edit_success = TRUE;
            $sucess_number = 0; 
           
            $AgentGoodsStockRale->startTrans();
           
            foreach ($data as $sk => $sv) {
                $agent_id = $sv['agent_id'];
                $sale_number = $sv['buy_total_stock'] - $sv['goods_stock'];
                $sale_total_profit = $sale_number*39; //销售总利润
                $sale_total_money = $sale_number*99; //销售总金额
                $sale_goods_number = $sv['sale_total_stock']; //已经卖出去的商品数量
                
                //有卖过产品的代理才需要修改
                if($sale_number > 0 && $sale_goods_number != $sale_number){
                    $sucess_number += 1;
                    
                    //修改代理总销售额与数量
                    $agent_where['agentId'] = $agent_id;
                    $editAgentData['all_sale_total_money'] = $sale_total_money;
                    $editAgentData['all_sale_total_profit'] = $sale_total_profit;
                    $editAgentData['all_sale_total_stock'] = $sale_number;
                    
                    $agentResult = $Agent->where($agent_where)->save($editAgentData);
                    
                    if(empty($agentResult)){
                        $is_edit_success = FALSE;
                        $msg = '总失败:'.$agent_id;
                    }

                    //修改代理月销售额与数量
                    $month_where['agent_id'] = $agent_id;
                    $month_where['year'] = date('Y');
                    $month_where['month'] = date('m');
                    $editMonthData['sale_profit'] = $sale_total_profit;
                    $editMonthData['sale_total_money'] = $sale_total_money;
                    $editMonthData['sale_total_stock'] = $sale_number;
                    
                    $monthResult = $AgentMonthProfit->where($month_where)->save($editMonthData);
                    
                    if(empty($monthResult)){
                        $is_edit_success = FALSE;
                        $msg = '月失败:'.$agent_id;
                    }

                    //修改代理商品销售额与数量  
                    $goods_where['goods_id'] = 31;
                    $goods_where['agent_id'] = $agent_id;
                    $editGoodsData['sale_total_stock'] = $sale_total_money;
                    $editGoodsData['sale_total_money'] = $sale_number;
                  
                    $goodsResult = $AgentGoodsStockRale->where($goods_where)->save($editGoodsData);
                    
                    if(empty($goodsResult)){
                        $is_edit_success = FALSE;
                        $msg = '商品失败:'.$agent_id;
                    }
                }
               
            }
           
            if($is_edit_success){
                dump($sucess_number);
                $AgentGoodsStockRale->commit();
                exit('修改特约库存与金额成功');
            }else{
                $AgentGoodsStockRale->rollback();
                exit($msg);
            }
        }else{
            exit('没有查询到数据');
        }
        
    }
    
    //xml转成JS
    public function xmlToArray() {
        $filename = './file/province_data.xml';
        $xmlstring = file_get_contents($filename);
        $content = json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
        
        foreach ($content['province'] as $sk => $sv) {
            $allList[$sk]['name'] = urlencode($sv['@attributes']['name']);
            if($sv['city'][0]){
                foreach ($sv['city'] as $ck =>$cv) {
                    $allList[$sk]['cityList'][$ck]['name'] = urlencode($cv['@attributes']['name']);
                    foreach ($cv['district'] as $dk => $dv) {
                        $allList[$sk]['cityList'][$ck]['areaList'][$dk] = urlencode($dv['@attributes']['name']);
                    }
                }
            }else{
                $allList[$sk]['cityList'][$sk]['name'] = urlencode($sv['city']['@attributes']['name']);
                foreach ($sv['city']["district"] as $dk => $dv) {
                    $allList[$sk]['cityList'][$sk]['areaList'][] = urlencode($dv['@attributes']['name']);
                }
            }
        }
        
        $json = urldecode(json_encode($allList));
        $count = file_put_contents ('new_address.plist',$json);
        dump($count);
        dump($json);
        dump($allList);
        dump($content);
    }
    
    //PHP数组转换为苹果plist XML或文本格式
    public function xmlToPlist() {
        $filename = './file/province_data.xml';
        $xmlstring = file_get_contents($filename);
        $content = json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
        
        foreach ($content['province'] as $sk => $sv) {
            if($sv['city'][0]){
                foreach ($sv['city'] as $ck =>$cv) {
                    foreach ($cv['district'] as $dk => $dv) {
                        $a[] = $dv['@attributes']['name'];
                    }
                    $allList[$sv['@attributes']['name']][][$cv['@attributes']['name']] = $a;
                    unset($a);
                }
            }else{
              
                foreach ($sv['city']["district"] as $dk => $dv) {
                    $allList[$sv['@attributes']['name']][$sk][$sv['city']['@attributes']['name']][] = $dv['@attributes']['name'];
                }
            }
        }
    
        $PropertyList = new \Org\Util\PropertyList($allList);
        $data = $PropertyList->xml();
       
        $count = file_put_contents ('./file/new_address.plist',$data);
        
        
    }
    
    //显示代理查询页面
    public function agentCheckView() {
   
        $this->display('agentCheckView');
    }
    
    //代理查询
    public function agentCheckResult() {
        $name = I('name');
        $weixin = I('weixin');
        $tel = I('tel');
        
        if(empty($name) && empty($weixin) && empty($tel)){
            $this->error('请输入姓名或者微信号或者手机号码');
        }
        
        $name ? $where['name'] = $name : '';
        $weixin ? $where['weixin']=$weixin : '';
        $tel ? $where['tel']= $tel : '';
        
        $Agent = D('Agent');
        $UserCom = D('UserCom');
           
        $agent_info = $Agent->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        $admin_info = $UserCom->getDetail('',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
       
        if($agent_info){
                $stat = $agent_info['stat'];
                $MEMBER_STAT = C('MEMBER_STAT');
                switch ($stat) {
                    case 1: //
                        $notice_msg = $admin_info['agent_notice'];
                        
                        //生成授权证书
                        $auth_img = make_agent_auth($agent_info);
                        $this->assign('auth_img',$auth_img);

                        break;
                    case -1: //黑名单
                        $notice_msg = $admin_info['agent_hei_notice']; 
                        break;
                    default :
                            $notice_msg = ' 该代理授权'.$MEMBER_STAT[$stat]['name'].'请谨慎购买。';
                        break;

                }
                
                if(I('debug')){
                    dump($notice_msg);
                    dump($agent_info);
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
        $this->display('agentCheckResult');
        
        
    }
    
     //防伪码查询
    public function searchSecurityView() {
        $this->display('searchSecurityView');
    }
    
    //防伪码查询
    public function searchSecurityResult() {
        $code = I('code');
        
        $return = array('status'=>0,'msg'=>'没有该防伪码 请谨慎购买!','result'=>'');
                
        if(empty($code)){
            $$return['msg'] = '请输入防伪码';
            $this->ajaxReturn($return,'json');
        }
        
        $code_info = S($code);
      
        if(empty($code_info)){
            $where['security_code'] =  $code;
            $code_pre = substr($code,0,9);

            if($code_pre == 145561693){
                $where['security_code'] =  $this->handleCode($code);
                $ErrLabelCode = M('ErrLabelCode');
                $code_info = $ErrLabelCode->where($where)->find();
                if($code_info){
                    S($code,  serialize($code_info));
                }
               
            }else{
                $LabelCode = D('LabelCode');
                $code_info = $LabelCode->getDetail($where);
                if($code_info){
                    S($code,  serialize($code_info));
                }
             
            }
        }else{
            $code_info = unserialize($code_info);
        }
      
        if($code_info){
            
            $is_add = TRUE; //是否添加日志
            
            //$code_info 不存在时为假货,代理被拉黑也显示假货
            if($code_info['middle_code']){
                $del_where['code'] = array(array('eq',$code_info['max_code']),array('eq',$code_info['middle_code']),array('eq',$code_info['min_code']),'OR');
            }else{
                $del_where['code'] = array(array('eq',$code_info['max_code']),array('eq',$code_info['min_code']),'OR');
            }

            $DeliverGoods = D('DeliverGoods');
            $del_list = $DeliverGoods->getList($del_where,1,1,'id DESC',array('field'=>array('agent_id'),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
           
            if($del_list){
                $agent_id = $del_list['0']['agent_id'];
                $agent_info = $this->getAgent($agent_id);
                
                if($agent_info && $agent_info['stat'] == -1){
                    $is_add = FALSE;
                    $code_info =  FALSE;
                }
            }
            
            if($is_add){
                $SecurityCheckLog = D('SecurityCheckLog');

                $addData['code'] = $code;
                $SecurityCheckLog->addData($addData);

                $log_where['code'] = $code;
                $result['list'] = $SecurityCheckLog->getList($log_where,10,1,'add_time DESC',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                
                $result['log_count'] = count($result['list']);
               
            }
        }else{
            $this->ajaxReturn($return,'json');
        }
        
        $return = array('status'=>1,'msg'=>'查询正确','result'=>$result);
        $this->ajaxReturn($return,'json');
    }
    
    //处理四舍五入防伪码
    private function handleCode($code){
        
        $last_num = substr($code,-1);
        $code_pre = substr($code,0,strlen($code)-1);
        $new_code = $code_pre.'0';
     
        if($last_num >= 5){
            $code_pre = substr($code,0,9);
            $new_code = $code_pre.(intval(substr($new_code,9))+10);
        }
        
        return $new_code;
    }
    
    //产品名片查询页面
    public function serchProductView() {
        //获取微信JS_SDK配置信息
        $weixin_js_sdk_info = $this->getWxJsInfo();
       
        if($weixin_js_sdk_info){
            $this->assign('wxjsinfo',$weixin_js_sdk_info);
        }
        $this->display('serchProductView');
    }
    
    //产品名片查询
    public function serchProductResult() {
        
        $code = I('code');
      
        if(empty($code)){
            echo '<h3>请输入条形码</h3>';
            die;
//            $this->error('请输入条形码');
        }
        
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
        
        $code_info = S($code);
        
        if(empty($code_info)){
            $DeliverGoods = D('DeliverGoods');

            $co_where['code'] = $code;
            $code_info = $DeliverGoods->getDetail($co_where,array('field'=>array('goods_id','agent_id','admin_id'),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),'id DESC');
           
            if(empty($code_info)){
                $co_where['code'] = array(array('eq',$label_info['max_code']),array('eq',$label_info['middle_code']),'or'); 
                $code_info = $DeliverGoods->getDetail($co_where,array('field'=>array('goods_id','agent_id','admin_id'),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),'id DESC');
            }
           
            if($code_info){
                S($code,serialize($code_info));
            }
        }else{
            $code_info = unserialize($code_info);
        }
        
        if($code_info){
            $goods_id = $code_info['goods_id'];
            $agent_id = $code_info['agent_id'];
            $admin_id = $code_info['admin_id'];
            
            $agent_id = $agent_id ? $agent_id : $admin_id;
            
            $agent_info = S(C('AGENT_INFO').$agent_id);
            
            if(empty($agent_info)){
                $Agent = D('Agent');
                $agent_where['agentId'] = $agent_id;
                $agent_info = $Agent->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                if($agent_info){
                    S(C('AGENT_INFO').$agent_id,  serialize($agent_info));
                }
            }else{
                $agent_info = unserialize($agent_info);
            }
            
            if(I('debug')){
                dump($admin_id);
                dump($agent_id);
                dump($code_info);
                dump($agent_info);
            }
            if(empty($agent_info) || $agent_info['stat'] == -1){
                
                echo '<center><h3>您所购买的商品为假货!</h3></center>';
                die;
            }
            
            $this->assign('agent_info',$agent_info);
//            dump($agent_info);
            //商品信息
            $goods_info = S(C('GOODS_INFO').$goods_id);

            if(empty($goods_info)){
                $Goods = D('Goods');
                $goods_where['id'] = $goods_id;
                $goods_info = $Goods->getDetail($goods_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                if($goods_info){
                    S(C('GOODS_INFO').$goods_id,  serialize($goods_info));
                }
            }else{
                $goods_info = unserialize($goods_info);
            }
            $this->assign('goods_info',$goods_info);
        }
       
        $this->display('serchProductResult');
        
    }
    
     //查询代理
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
                S(C('AGENT_INFO').$agent_id,  serialize($agent_info));
            }
        }else{
            $agent_info = unserialize($agent_info);
        }
        
        return $agent_info;
    }
    
    /**
    * 获取微信JS_SDK配置信息
    */
   public function getWxJsInfo() {
       
        $debug = I('debug');
        $is_bool = 0;
        
        if($debug){
            $is_bool = 1;
            $is_weixin = TRUE;
        }
        
        $this->assign('debug',$is_bool);
        
        $options['appid'] = C('WX_APPID');
        $options['secret'] = C('WX_APPSECRET');
        $this->Wechat = new \Org\Util\Wechat($options);
        
        //获取access_token
        $this->Wechat->access_token = S('access_token');
        if(empty($this->access_token)){
            $this->Wechat->access_token = $this->Wechat->getToken();
            S('access_token',$this->Wechat->access_token,60*60+60*50);

        }
        
        //获取jsapi_ticket
        $jsapi_ticket = S('jsapi_ticket');
        if(empty($jsapi_ticket)){
            $jsapi_ticket = $this->Wechat->getJsapiTicket();
            S('jsapi_ticket',$jsapi_ticket,60*60+60*50);
        }
        
        //如果jsapi_ticket获取失败重新获取access_token
        if(empty($jsapi_ticket)){
            $this->Wechat->access_token = '';
            $this->Wechat->access_token = $this->Wechat->getToken();
            S('access_token',$this->Wechat->access_token,60*60+60*50);
          
            $jsapi_ticket = $this->Wechat->getJsapiTicket();
            S('jsapi_ticket',$jsapi_ticket,60*60+60*50);
        }
        
        //获取签名验证
        $timestamp = time();
        $noncestr = $this->_getRandomStr();
        $signature = $this->Wechat->getJSSDKSHA1($jsapi_ticket, $timestamp,$noncestr,$is_bool);
        
        $wxInfo = array(
            'appId'=> $options['appid'], // 必填，公众号的唯一标识
            'timestamp'=> $timestamp, // 必填，生成签名的时间戳
            'nonceStr'=> $noncestr, // 必填，生成签名的随机串
            'signature'=> $signature,// 必填，签名，见附录1
            'jsapi_ticket'=>$jsapi_ticket,
            'access_token'=>$this->Wechat->access_token,
        );
        
        if($is_bool){
            dump($wxInfo);
        }
        return $wxInfo;
   }
   
    /**
    * 返回随机填充的字符串
    */
   private function _getRandomStr($lenght = 16)	{
           $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
           return substr(str_shuffle($str_pol), 0, $lenght);
   }
    
    
}