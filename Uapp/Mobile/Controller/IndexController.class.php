<?php
namespace Mobile\Controller;
use Think\Controller;
class IndexController extends Controller {
    
    public function __construct() {
        parent::__construct();
        
        //微信浏览器中才去调用
        $is_weixin = is_weixin();
        
//        if(!APP_DEBUG && !$is_weixin){
//           echo '<h2>请在微信中打开!</h2>';
//           die;
//        }
    }

    public function index(){
        
        $this->display('Public:agentCheckView');
    }
}