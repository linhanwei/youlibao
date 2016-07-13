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
    
    public function test() {
        $Agent = D('Agent');
        
        $agent_id = 2;
        $edit_next_agent3_where['_string'] = ' agentId IN(SELECT member_id FROM agent_relation WHERE agent2_id = "'.$agent_id.'" AND agent_grade = 3)';
                             
        $editNextAgent3Result = $Agent->getDetail($edit_next_agent3_where);
        dump($Agent->_sql());die;
                                            
        $url = 'http://msb.kudouys.me/Public/searchSecurityResult.html';
        $url_params = array();
        $url_method = 'GET';
        
        $LabelCode = D('LabelCode');
        $admin_id = 1;
        $code = '10011247606';
        $sql = 'SELECT COUNT(*) AS COUNT FROM deliver_goods WHERE CODE IN(SELECT min_code FROM label_code WHERE max_code = "'.$code.'" OR middle_code = "'.$code.'") AND admin_id ='.$admin_id; //发了小标签,不能再发中标或者大标
        $sql = 'SELECT COUNT(*) AS COUNT FROM deliver_goods WHERE CODE IN(SELECT middle_code FROM label_code WHERE max_code = "'.$code.'") AND admin_id ='.$admin_id; //发了中标,不能再发大标
        $sql = 'SELECT COUNT(*) AS COUNT FROM deliver_goods WHERE CODE IN(SELECT max_code FROM label_code WHERE middle_code = "'.$code.'") AND admin_id ='.$admin_id; //发了大标不能发中标
        $sql = 'SELECT COUNT(*) AS COUNT FROM deliver_goods WHERE CODE IN(SELECT max_code FROM label_code WHERE min_code = "'.$code.'") AND admin_id ='.$admin_id; //发了大标不能发小标
        $sql = 'SELECT COUNT(*) AS COUNT FROM deliver_goods WHERE CODE IN(SELECT middle_code FROM label_code WHERE min_code = "'.$code.'") AND admin_id ='.$admin_id; //发了中标不能发小标
        $label_code_retult = $LabelCode->query($sql);
        
        
        dump($label_code_retult[0]['count']);
//        $return_data = http($url, $url_params, $url_method);
//        dump(date('Y-m-d',strtotime(' +3 day')));
//        dump(json_decode($return_data));
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
           
                switch ($agent_info['stat']) {
                    case 1: //
                        $notice_msg = $admin_info['agent_notice'];
                        
                        //生成授权证书
                        $auth_img = make_agent_auth($agent_info);
                        $this->assign('auth_img',$auth_img);

                        break;
                    case -1: //黑名单
                        $notice_msg = $admin_info['agent_hei_notice']; 
                        break;

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
            $code_info = $DeliverGoods->getDetail($co_where,array('field'=>array('goods_id','agent_id'),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),'id DESC');
           
            if(empty($code_info)){
                $co_where['code'] = array(array('eq',$label_info['max_code']),array('eq',$label_info['middle_code']),'or'); 
                $code_info = $DeliverGoods->getDetail($co_where,array('field'=>array('goods_id','agent_id'),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null),'id DESC');
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