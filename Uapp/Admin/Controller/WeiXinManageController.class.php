<?php
namespace Admin\Controller;
use Think\Controller;
class WeiXinManageController extends CommonController {
    
    private $Wechat; //微信类
    private $access_token;
    
    //初始化数据
    public function __construct() {
        parent::__construct();
        
        $options['token'] = C('WX_TOKEN');
        $options['appid'] = C('WX_APPID');
        $options['secret'] = C('WX_APPSECRET');
        $options['encode']= C('WX_ENCODE'); //消息加密 秘钥
       // $options['access_token'] = $options['access_token'];
//        $options['debug'] = $options['debug'];
//        $options['aeskey'] = $options['aeskey'];
//        $options['mch_id'] = $options['mch_id'];
//        $options['payKey'] = $options['payKey'];
        
        $this->Wechat = new \Org\Util\Wechat($options);
        
        //获取access_token
        $this->getToken();
    }

    public function index(){
        
    }
    
    //自定义菜单页面
    public function createMenuView() {
        
        //菜单类型
        $type_list = $this->Wechat->getMenuType();
        $this->assign('type_list', $type_list);
//        dump($type_list);
        $this->display('createMenuView');
    }
    
    //自定义菜单
    public function createMenu() {
        
        $this->display('createMenuView');
    }
    
    //获取access_token
    protected function getToken() {
        $cache_key = md5(C('ACCESS_TOKEN_CACHE_KEY'));
        $this->Wechat->access_token = $this->access_token = S($cache_key);
        if(empty($this->access_token)){
            $this->Wechat->access_token = $this->access_token = $this->Wechat->getToken();
            S($cache_key,$this->access_token,60*60+60*50);
        }
       
        return $this->access_token;
    }
    
    
}