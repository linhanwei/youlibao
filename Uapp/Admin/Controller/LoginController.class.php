<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
   
    public function __construct() {
        parent::__construct();
       
    }
    
    //用户登录页面
    public function index(){
//        dump(md5(123456)); //e10adc3949ba59abbe56e057f20f883e
        $this->display();
    }
    
    //用户登录
    public function loginSub() {
        
        $name = I('name');
        $password = I('password');
        $code = I('code');
        
        if(!$this->check_verify($code)){
            $this->error('验证码不正确,请重新输入','index',3);
        }
        
        $where['userName'] = $name;
        $where['password'] = md5($password);
        
        $result = D('User')->getDetail($where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
       
        if(!$result){
            $this->error('用户名或者密码不正确,请重新输入','index',3);
        }
        
        session('member_id',$result['userid']);
        session('admin_info',$result);
        $this->redirect('Index/index', '', 0, '页面跳转中...');
    }
    
    //退出
    public function logout() {
        session('member_info', NULL);
        session('member_id',NULL);
        $this->redirect('index', '', 0, '页面跳转中...');
    }
    
    //用户注册页面
    public function reg() {
        $code = I('code');
        
        $this->assign('code',$code);
        $this->display();
    }
    
    //提交注册
    public function regSub() {
        $code = I('code');
        $post = I('post.');
      
        if(!$this->check_verify($code)){
            $this->error('验证码不正确,请重新输入','reg',3);
        }
        
        $post['Tel'] = $post['UserName'];
        
        $invit_code = $post['InvitationCode'];
        $Member =D('User');
        
        $where['InvitationCode'] = $invit_code;
        $member_info = $Member->where($where)->find();
       
        $post['Lv'] = $member_info['lv']+1;
        $post['ParentID'] = $member_info['id'];
        $post['InvitationCode'] = $this->isExit($Member);
        
        $result = $Member->add($post);
       
        if($result){
            session('member_id',$result);
            $this->redirect('Index/index', '', 0, '页面跳转中...');
        }else{
            $this->error('注册失败,请重新注册','reg?code='.$invit_code,3);
        }
        
    }
    
    //验证邀请码是否存在
    public function isExit($model) {
        $code = $this->randomkeys(8);
        $where['InvitationCode'] = $code;
        $count = $model->where($where)->count();
        
        if($count > 0){
            $this->isExit($model);
        }
        
        return $code;
    }
    
    /*
     * 随机生成邀请码
     * $length 获取长度
     */
    public function randomkeys($length){
        $pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,35)};    
        }
        return strtoupper($key);
    }
    
    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    function check_verify($code, $id = ''){    
        $verify = new \Think\Verify();    
        return $verify->check($code, $id);
        
    }
    
    //生成验证码
    public function entry() {
        $config =    array(    
            'fontSize'    =>    30,    // 验证码字体大小    
            'length'      =>    4,     // 验证码位数    
            'useNoise'    =>    false, // 关闭验证码杂点
        );
        
        $Verify =     new \Think\Verify($config);
        $Verify->entry();
    }
}