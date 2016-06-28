<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
        
        $Agent = D('Agent');
        
        //有效代理总数
        $valid_where['stat'] = 1;
        $valid_count = $Agent->getCount($valid_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        $this->assign('valid_count',$valid_count);
        
        //待审核
        $stay_where['stat'] = array('in','0,-2');
        $stay_count = $Agent->getCount($stay_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        $this->assign('stay_count',$stay_count);
        
        //总代理数
        $all_count = $Agent->getCount('',array('key'=>false,'expire'=>null,'cache_type'=>null));
        $this->assign('all_count',$all_count);
        
        $this->display();
    }
    
}