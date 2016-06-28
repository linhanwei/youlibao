<?php
namespace Mobile\Controller;
use Think\Controller;
class WeiXinController extends Controller {
    var $Wechat = ''; //微信类
    var $postObj = '';
    var $openid='';
    var $toUsername = '';
    var $msgType = '';
    var $msgId = '';
    var $content = '';
    var $access_token = '';
    
    function __construct() {
        parent::__construct();

        $options['token'] = C('WX_TOKEN');
        $options['appid'] = C('WX_APPID');
        $options['secret'] = C('WX_APPSECRET');
       // $options['access_token'] = $options['access_token'];
//        $options['debug'] = $options['debug'];
//	$options['encode']= true;
//        $options['aeskey'] = $options['aeskey'];
//        $options['mch_id'] = $options['mch_id'];
//        $options['payKey'] = $options['payKey'];
//        $options['pem'] = $options['pem'];
//        
        $this->Wechat = new \Org\Util\Wechat($options);
        $result = $this->Wechat->valid(); //验证令牌是否正确

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            
        //初始化公众号数据
        if ($postStr) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            $this->postObj =$postObj;
            $this->openid = $postObj -> FromUserName;
            $this->toUsername = $postObj -> ToUserName;
            $this->msgType = $postObj -> MsgType;
            $this->content = trim($postObj -> Content);
            $this->msgId = $postObj ->MsgId;
        }
        
        //获取access_token
        $this->getToken();

    }
    
    //获取access_token
    public function getToken() {
        $this->Wechat->access_token = $this->access_token = S('access_token');
        if(empty($this->access_token)){
            $this->Wechat->access_token = $this->access_token = $this->Wechat->getToken();
            S('access_token',$this->access_token,60*60+60*50);
        }
        
        return $this->access_token;
    }

    public function index(){
        //wxid,openid为空就退出
        if(empty($this->openid)){
            exit('');
        }

        $time = time();
        
        //插入公众号推送信息
        if ($this->msgType == 'text') {
            $mData['openid'] = $this->openid;
            $mData['message'] = $this->content;
            $mData['add_time'] = $time;
            $WxMessage = D('WxMessage');
            $WxMessage->addData($mData);
        } 

        //获取微信会员信息
        $userInfo = S("$this->openid");
        if(!$userInfo){
            $UserView = D('Member');
            $userInfo = $UserView->getDetail(array('openid'=>"$this->openid"),array('field'=>array(),'is_opposite'=>false),array('key'=>"$this->openid",'expire'=>24*60*60,'cache_type'=>null));
            
        }
        
        //该会员没有注册时就注册  返回注册结果消息
        $reg_result = $this->regMember($userInfo,$time,'');
        
        //如果是新注册会员就显示出注册结果
        if($reg_result['status'] == 1){
            $this->textXml($reg_result['msg']);
        }
        
        $user_id = $userInfo ? $userInfo['member_id'] : $reg_result['member_id'];
        $user_name = $userInfo ? $userInfo['member_name'] : $reg_result['member_name'];
        
        //自定义菜单事件
        if($this->postObj->Event == 'CLICK'){
            //我的二维码
            if($this->postObj->EventKey == 'qrcode'){
                $this->makeQrcode($user_id,$user_name);
            }

            //订单快递
            if($this->postObj->EventKey == 'kdcx'){
                $this->getOrder($user_id,$time);
            }

            //大转盘
            if($this->postObj->EventKey == 'dzp'){
                $this->dzp($user_id,$time);
            }

            //砸金蛋
            if($this->postObj->EventKey == 'zjd'){
                $this->zjd($user_id,$time);
            }

            //签到
            if($this->postObj->EventKey == 'qiandao'){
                $qd_result = $this->qiandao($user_id);
                if($qd_result['status'] == 1){
                    $messge = $qd_result['msg'];
                }else{
                    $messge = '对不起,您今天的签到次数已经用完,请明天再来!';
                }

                $this->textXml($this->fromUsername,  $this->toUsername,$messge);
            }
        }
          
        $this->Wechat->sendMsg($this->openid,'2222'.'/'.$a, $msgtype = 'text');
        exit('');
    }
    
    /*
    * 生成会员二维码
    * 
    */
    public function makeQrcode($user_id = 0 ,$user_name='',$type = 'tj') {

        if(empty($user_id) && empty($user_name)){
            exit('');
        }

        $go_url = $this->base_url . 'wechat/egg/index1.php?scene_id=' . $user_id;
        $pic_url = $this->db->getOne("SELECT `qr_path` FROM `wxch_qr` WHERE `scene_id`='$user_id'");

        if(empty($pic_url)){
            //创建长久二维码 限制数量:10万
            $json_arr = array('action_name'=>"QR_LIMIT_SCENE",'action_info'=>array('scene'=>array('scene_id'=>$user_id)));
            $data = json_encode($json_arr);

            $access_token = $this ->access_token();

            if($access_token){
                $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
                $res_json =$this -> curl_grab_page($url, $data);
                $json = json_decode($res_json);
            }

            $ticket = $json->ticket;

            if($ticket){
                //下载二维码
                $ticket_url = urlencode($ticket);
                $ticket_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket_url;
                $imageinfo = $this -> downloadimageformweixin($ticket_url);

                $rand = rand(1,100);
                $time = time();
                $detime = date('Ymd',$time).'/';
                $pic_name = $detime.$time.$rand;
                $pic_url = $this->base_url .'/images/qrcode/'.$pic_name.'.jpg';
                //目录不存在则创建
                $path = '../images/qrcode/'.$detime;

                if(!file_exists($path)){
                    mkdir($path);

                }

                $path = $path.$time.$rand.'.jpg';
                $local_file=fopen($path,'a');

                if(false !==$local_file){
                    if(false !==fwrite($local_file,$imageinfo)){
                        fclose($local_file);
                        //将生成的二维码图片的地址放到数据库中
                        $insert_sql = "INSERT INTO `wxch_qr` (`type`,`action_name`,`ticket`, `scene_id`, `scene` ,`qr_path`,`function`,`affiliate`,`endtime`,`dateline`) VALUES
                        ('$type','$action_name', '$ticket','$user_id', '$user_name' ,'$pic_url','$function','$user_id','$time','$time')";
                        $this->db->query($insert_sql);
                    }
                }

            }
        }

        $des="扫描二维码可以获得推荐关系！";
        $title = '二维码推荐';
        $this->newsXml($this->fromUsername, $this->toUsername,$title ,$des ,$pic_url,$go_url);
    }
    
    /*
    * 注册会员
    * $wx_info 微信会员信息
    * $user_info 网站会员信息
    */
    public function regMember($userInfo = '',$time = '',$reg_pwd='') {

        $time = $time ? $time : time();
        $result = array('status'=>0,'msg'=>'');
        $WxUser =  D('WxUser');
        
        if(empty($userInfo)){
            
            $reg_name = 'ttegou'; //自动注册用户名
            $pwd_num = $this->randomkeys(6); //注册密码随机后缀
            $reg_pwd = 123456;  //自动注册密码
            $md5_reg_pwd = md5($reg_pwd); 

            if (!empty($this->openid)) {
                    
                    //注册普通会员
                    if(empty($userInfo['member_id'])){
                        $Users = D('Member');
                        
                        $maxUserId = $Users->max('user_id');
                        $reg_name = $reg_name . ($maxUserId+1); 
                        
                        $userData['member_name'] = $reg_name;
                        $userData['member_passwd'] = $md5_reg_pwd;
                        $userData['openid'] = $this->openid;
                        $userData['member_time'] = $time;
                        
                        $user_id = $Users->addData($userData);
                        
                        $result['msg'] = "恭喜您注册成为我们的会员,您的账号：".$reg_name."密码：".$reg_pwd;
                        $result['status'] = 1;
                        $result['member_id'] = $user_id;
                        $result['member_name'] = $reg_name;
                    }
                    
                    //获取微信会员信息
                    $wx_url_result = $this->Wechat->user($this->openid);
                   
                    //注册微信会员
                    if($wx_url_result['errcode']){
                        $regData['subscribe'] = 1;
                        $regData['openid'] = $this->openid;
                        $regData['add_time'] = $time;
                    }else{
                        $regData['subscribe'] = 1;
                        $regData['openid'] = $this->openid;
                        $regData['nickname'] = $wx_url_result['nickname'];
                        $regData['sex'] = $wx_url_result['sex'];
                        $regData['languages'] = $wx_url_result['language'];
                        $regData['headimgurl'] = $wx_url_result['headimgurl'];
                        $regData['add_time'] = $time;
                        $regData['country'] = $wx_url_result['country'];
                        $regData['province'] = $wx_url_result['province'];
                        $regData['city'] = $wx_url_result['city'];
                        $regData['unionid'] = $wx_url_result['unionid'];
                    }
                    
                    $WxUser->addData($regData);
                      
                }

        }else{ //没有关注时就设置为关注
            if($userInfo['subscribe'] == 0){
                $wxData['subscribe'] = 1;
                $wxData['add_time'] = $time;
                $WxUser->editData(array('wxid'=>"$this->openid"),$wxData);
               
            }
        }

        return $result;
    }
    
    /*
     * 开发修复生成随机密码
     * $length 获取长度
     */
    public function randomkeys($length){
        $pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,35)};    
        }
        return $key;
    }
    
    /*
     * 文本XML输出
     * $content 回复的内容
     */
    public function textXml($content='') {
        $time = time();
        $resultStr = '';
        $content = trim($content);

        if ($content) {
            $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";

            $resultStr = sprintf($textTpl, $this->openid, $this->toUsername,$time, 'text', $content);
        } 

        exit($resultStr);
    }
    
}