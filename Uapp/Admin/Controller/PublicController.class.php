<?php
namespace Admin\Controller;
use Think\Controller;
class PublicController extends Controller {
    
    public function __construct() {
        parent::__construct();
        
    }
    
    //获取省份
    public function province() {
        $list = D('Area')->where(array('ParentId'=>0))->select();
        $this->ajaxReturn($list);
    }
    
    //获取市
    public function city() {
        $parent_id = I('pid');
        $parent_id = $parent_id ? $parent_id : 3;
        $list = D('Area')->where(array('ParentId'=>$parent_id))->select();
        $this->ajaxReturn($list);
    }
    
    //获取区
    public function area() {
        $parent_id = I('pid');
        $parent_id = $parent_id ? $parent_id : 36;
        $list = D('Area')->where(array('ParentId'=>$parent_id))->select();
        $this->ajaxReturn($list);
    }

}