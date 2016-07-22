<?php
/*
 * 代理商管理
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class AgentManageController extends CommonController {
    
    //代理商列表
    public function index() {
      
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        $search_value = I('search_value');
        $search_field = I('search_field');
        $parent_id = I('pid');
        $star = I('star');
        $page = I('p',1);
        $status = I('status',1);
      
        if($search_field && $search_value){
            $where[$search_field] = $search_value;
        }
        
        $parent_id ? $where['parent_id'] = $parent_id : '';
        $star ? $where['star'] = $star : '';
        $status ? (is_numeric($status) ? $where['stat'] = $status : $where['stat'] = array('in',$status)) : '';
        
        $limit=10;
        
        $Agent = D('Agent');
        
        $count      = $Agent->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $Agent->getList($where,$limit,$page,'star',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($list){
            foreach ($list as $k => $v) {
                $list[$k]['lv_name'] = $agent_lv_list[$v['star']]['name'];
                $list[$k]['agent_count'] = $Agent->getCount(array('parent_id'=>$v['agentid']),array('key'=>false,'expire'=>null,'cache_type'=>null));
                $list[$k]['date_end_time'] = date('Y-m-d',$v['endtime']);
                $agent_info = $this->getAgent($v['parent_id']);
                $list[$k]['parent_name'] = $agent_info['name'];
            }
        }
       
        //搜索条件
        $search['search_field'] = $search_field;
        $search['search_value'] = $search_value;
        $search['parent_id'] = $parent_id;
        $search['star'] = $star;
        $search['stat'] = $status;
        $this->assign('search',$search);
       
        $this->assign('list_count',$count);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display();
    }
    
    //下载证书
    public function downAuthBook() {
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            echo '<p>请选择代理</p>';
            die;
        }
        
        $agent_info = $this->getAgent($agent_id);
        
        //生成授权证书
        $auth_img = make_agent_auth($agent_info);
        
        $filename = './Public/'.$auth_img;
        
        $DownLoad = new \Org\Util\DownLoad('php,exe,html',false);  
        if(!$DownLoad->downloadfile($filename)){  
            echo '<p>'.$DownLoad->geterrormsg().'</p>';
        } 
        
        die;  
    }
    
    //查看证书
    public function showAuthBook() {
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            echo '<p>请选择代理</p>';
            die;
        }
        
        $agent_info = $this->getAgent($agent_id);
        
        //生成授权证书
        $auth_img = make_agent_auth($agent_info);
        $this->assign('auth_img',$auth_img);
        
        $this->display('showAuthBook');
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
    
    //修改代理商页面
    public function editAgentView() {
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            dump('请选择代理商');
            die;
        }
        
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        $info = $this->getAgent($agent_id);
      
        $this->assign('info',$info);
        $this->display('editAgentView');
    }
    
   //修改代理商
    public function editAgent() {
        $return = array('status'=>0,'msg'=>'更改代理失败','result'=>'');
        $post = I('post.');
        $p_weixin =$post['p_weixin'];
        $agent_id = $post['aid'];
        $name = $post['name'];
        $weixin = $post['weixin'];
        $agentno = $post['agentno'];
        $tel = $post['tel'];
        $cardno = $post['cardno'];
        $star = $post['star'];
        $startime = $post['startime'];
        $endtime = $post['endtime'];
        $qq = $post['qq'];
        $province = $post['province'];
        $city = $post['city'];
        $county = $post['county'];
        $address = $post['address'];
        $stat = $post['stat'];
        $password = $post['password'];
        $password2 = $post['password2'];
        $y_parent_id = $post['y_parent_id'];
        $team_name = $post['team_name'];
        $agent2_id = $post['agent2_id'];
        $agent3_id = $post['agent3_id'];
        
        if($password && $password != $password2){
            $return['msg'] = '修改密码与确认密码不一致!';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }

        if(empty($name)){
            $return['msg'] = '请输入姓名';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agentno)){
            $return['msg'] = '授权号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($tel)){
            $return['msg'] = '手机号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($cardno)){
            $return['msg'] = '身份证号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($weixin)){
            $return['msg'] = '微信号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($star)){
            $return['msg'] = '代理星别不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        //代理等级
        $MEMBER_LEVEL = C('MEMBER_LEVEL');

        $Agent = D('Agent');
        $AgentRelation = D('AgentRelation');
        
        //获取当前代理信息
        $agent_info = $this->getAgent($agent_id);
        
        if(empty($agent_info)){
            $return['msg'] = '请选择代理!';
            $this->ajaxReturn($return,'json');
        }
        
        //升级为创始人,团队名称不能为空
        if($star == 1 && empty($team_name)){
            $return['msg'] = '团队名称不能为空!';
            $this->ajaxReturn($return,'json');
        }
        
        $agent_lv = $agent_info['agent_grade'];//当前会员等级
        $agent_top1_id = $agent_info['top1_id'];//当前会员间接创始人
        $agent_top2_id = $agent_info['top2_id'];//当前会员直接创始人
        $agent_agent1_id = $agent_info['agent1_id'];//当前会员创始人
        $agent_agent2_id = $agent_info['agent2_id'];//当前会员大区
        
        //代理不能降级
        if($star > $agent_lv){
            $return['msg'] = '代理不能降级!';
            $this->ajaxReturn($return,'json');
        }
        
        $Agent->startTrans(); //开启事务
        $is_edit_success = TRUE;
        $parent_id = 0;
        
        //判断代理是否要升级
        if($agent_lv != $star){
            
            //没有库存的代理才能升级
            $AgentGoodsStockRale = D('AgentGoodsStockRale');
            
            $agent_goods_stock_rale_where['agent_id'] = $agent_id;
            $agent_goods_stock_rale_where['goods_stock'] = array('gt',0);
            $agent_goods_stock_rale_count = $AgentGoodsStockRale->getCount($agent_goods_stock_rale_where);
            if($agent_goods_stock_rale_count > 0){
                $return['msg'] = '代理的商品库存不为零,不能升级!';
                $this->ajaxReturn($return,'json');
            }
            
            switch ($agent_lv) {
                case 2: //大区升级创始人
                        
                        /*
                         * ****************************************************************************************************************************************
                         * 修改大区ID相关信息  开始
                         * ****************************************************************************************************************************************
                         */
                            //判断总代是否有下级,没有的话可以直接升级
                            $get_agent2_count_where['agent'.$agent_lv.'_id'] = $agent_id;
                            $getAgent2Count = $AgentRelation->getCount($get_agent2_count_where);
                           
                            if($getAgent2Count > 0  || $agent2_id  || $agent3_id){
                                if(empty($agent2_id)){
                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID不能为空!';
                                    $this->ajaxReturn($return,'json');
                                }

                                //获取大区信息
                                $agent2_info = $this->getAgent($agent2_id);

                                if(empty($agent2_info)){
                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID不存在!';
                                    $this->ajaxReturn($return,'json');
                                }
                                
                                //大区ID的级别只能是大区或者新注册的会员
                                if($agent2_info['agent_grade'] && $agent2_info['agent_grade'] != 3){
                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID不是总代,不能作为大区!';
                                    $this->ajaxReturn($return,'json');
                                }

                                //大区ID有商品库存时不能升级
                                $agent_goods_stock_rale_where['agent_id'] = $agent2_id;
                                $agent_goods_stock_rale_where['goods_stock'] = array('gt',0);
                                $agent_goods_stock_rale_count = $AgentGoodsStockRale->getCount($agent_goods_stock_rale_where);
                                if($agent_goods_stock_rale_count > 0){
                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID的商品库存不为零,不能升级!';
                                    $this->ajaxReturn($return,'json');
                                }

                                //修改大区ID代理信息
                                $edit_agent2_where['agentId'] = $agent2_id;

                                $editAgent2Data['star'] = 2;
                                $editAgent2Data['parent_id'] = $agent_id;
//                                $editAgent2Data['stat'] = 1;
                                $editAgent2Result = $Agent->editData($edit_agent2_where,$editAgent2Data);

                                if(!$editAgent2Result){
                                    $is_edit_success = FALSE;
                                    $return['msg'] = '修改'.$MEMBER_LEVEL[2]['name'].'ID信息失败!';
                                }

                                //修改大区ID的关系信息
                                $edit_agent2_relation_where['member_id'] = $agent2_id;
                                
                                $editAgent2RelationData['member_id'] = $agent2_id;
                                $editAgent2RelationData['top1_id'] = $agent_top2_id;
                                $editAgent2RelationData['top2_id'] = $agent_agent1_id;
                                $editAgent2RelationData['agent1_id'] = $agent_id;
                                $editAgent2RelationData['agent2_id'] = 0;
                                $editAgent2RelationData['agent3_id'] = 0;
                                $editAgent2RelationData['agent4_id'] = 0;
                                $editAgent2RelationData['agent_grade'] = 2;
                                $editAgent2RelationData['pid'] = $agent_id;
                                
                                $editAgent2RelationCount = $AgentRelation->getCount($edit_agent2_relation_where);
                                
                                //判断大区ID关系信息是否存在,存在就修改,否则就添加
                                if($editAgent2RelationCount > 0){
                                    $editAgent2RelationResult = $AgentRelation->editData($edit_agent2_relation_where,$editAgent2RelationData);
                                }else{
                                    $editAgent2RelationResult = $AgentRelation->addData($editAgent2RelationData);
                                }
                                
                                if(!$editAgent2RelationResult){
                                    $is_edit_success = FALSE;
                                    $return['msg'] = '修改'.$MEMBER_LEVEL[2]['name'].'ID关系信息失败!';
                                }
                                
                                //判断代理是否有总代,有的话更改总代关系信息
                                $edit_agent_next_agent3_where['agent2_id'] = $agent_id;
                                $edit_agent_next_agent3_where['agent_grade'] = 3;

                                $editAgentNextAgent3Count = $AgentRelation->getCount($edit_agent_next_agent3_where);
                                if($editAgentNextAgent3Count > 0){
                                    //修改下级总代代理信息
                                    $edit_next_agent3_where['_string'] = ' agentId IN(SELECT member_id FROM agent_relation WHERE agent2_id = "'.$agent_id.'" AND agent_grade = 3)';

                                    $editNextAgent3Data['parent_id'] = $agent2_id;
                                    $editNextAgent3Result = $Agent->editData($edit_next_agent3_where,$editNextAgent3Data);

                                    if(!$editNextAgent3Result){
                                        $is_edit_success = FALSE;
                                        $return['msg'] = '修改'.$MEMBER_LEVEL[2]['name'].'ID下级'.$MEMBER_LEVEL[3]['name'].'信息失败!';
                                    }

                                    //修改下级总代关系信息
                                    $editAgentNextAgent3Data['top1_id'] = $agent_top2_id;
                                    $editAgentNextAgent3Data['top2_id'] = $agent_agent1_id;
                                    $editAgentNextAgent3Data['agent1_id'] = $agent_id;
                                    $editAgentNextAgent3Data['agent2_id'] = $agent2_id;
                                    $editAgentNextAgent3Data['pid'] = $agent2_id;
                                    $editAgentNextAgent3Result = $AgentRelation->editData($edit_agent_next_agent3_where,$editAgentNextAgent3Data);

                                    if(!$editAgentNextAgent3Result){
                                        $is_edit_success = FALSE;
                                        $return['msg'] = '修改'.$MEMBER_LEVEL[2]['name'].'ID下级'.$MEMBER_LEVEL[3]['name'].'关系信息失败!';
                                    }
                                }
                                
                                //判断是否有特约,有的话更改特约的关系信息
                                $edit_agent_next_agent4_where['agent2_id'] = $agent_id;
                                $edit_agent_next_agent4_where['agent_grade'] = 4;

                                $editAgentNextAgent4Count = $AgentRelation->getCount($edit_agent_next_agent4_where);

                                if($editAgentNextAgent4Count > 0){
                                    //修改下级特约关系信息
                                    $editAgentNextAgent4Data['top1_id'] = $agent_top2_id;
                                    $editAgentNextAgent4Data['top2_id'] = $agent_agent1_id;
                                    $editAgentNextAgent4Data['agent1_id'] = $agent_id;
                                    $editAgentNextAgent4Data['agent2_id'] = $agent2_id;
                                    $editAgentNextAgent4Result = $AgentRelation->editData($edit_agent_next_agent4_where,$editAgentNextAgent4Data);

                                    if(!$editAgentNextAgent4Result){
                                        $is_edit_success = FALSE;
                                        $return['msg'] = '修改'.$MEMBER_LEVEL[2]['name'].'ID下级'.$MEMBER_LEVEL[4]['name'].'关系信息失败!';
                                    }
                                }
                                
                            /*
                             * ****************************************************************************************************************************************
                             * 修改大区ID相关信息  结束
                             * ****************************************************************************************************************************************
                             */
                             
                            /*
                             * ****************************************************************************************************************************************
                             * 修改总代ID相关信息  开始
                             * ****************************************************************************************************************************************
                             */
                                    //判断大区ID是否有下线,有就要填写总代ID,没有就不用
                                    $get_agent2_next_count_where['agent'.$agent2_info['agent_grade'].'_id'] = $agent2_id;
                                    $getAgent2NextCount = $AgentRelation->getCount($get_agent2_next_count_where);
                                    
                                    if($getAgent2NextCount > 0){
                                        if(empty($agent3_id)){
                                            $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID不能为空!';
                                            $this->ajaxReturn($return,'json');
                                        }

                                        //获取总代信息
                                        $agent3_info = $this->getAgent($agent3_id);

                                        if(empty($agent3_info)){
                                            $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID不存在!';
                                            $this->ajaxReturn($return,'json');
                                        }
                                        
                                        //判断总代ID是否属于大区ID的下线,不然不能代替自己的位置
                                        if($agent3_info['agent3_id'] != $agent2_id){
                                            $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID不是'.$MEMBER_LEVEL[2]['name'].'ID的下线!';
                                            $this->ajaxReturn($return,'json');
                                        }
                                        
                                        //判断总代ID商品库存是否为零,不是就不能升级
                                        $agent_goods_stock_rale_where['agent_id'] = $agent3_id;
                                        $agent_goods_stock_rale_where['goods_stock'] = array('gt',0);
                                        $agent_goods_stock_rale_count = $AgentGoodsStockRale->getCount($agent_goods_stock_rale_where);
                                        if($agent_goods_stock_rale_count > 0){
                                            $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID的商品库存不为零,不能升级!';
                                            $this->ajaxReturn($return,'json');
                                        }
                                        
                                        //修改总代ID代理信息
                                        $edit_agent3_where['agentId'] = $agent3_id;

                                        $editAgent3Data['star'] = 3;
                                        $editAgent3Data['parent_id'] = $agent2_id;
                                        $editAgent3Result = $Agent->editData($edit_agent3_where,$editAgent3Data);

                                        if(!$editAgent3Result){
                                            $is_edit_success = FALSE;
                                            $return['msg'] = '修改'.$MEMBER_LEVEL[3]['name'].'ID信息失败!';
                                        }

                                        //修改总代ID的关系信息
                                        $edit_agent3_relation_where['member_id'] = $agent3_id;

                                        $editAgent3RelationData['top1_id'] = $agent_top2_id;
                                        $editAgent3RelationData['top2_id'] = $agent_agent1_id;
                                        $editAgent3RelationData['agent1_id'] = $agent_id;
                                        $editAgent3RelationData['agent2_id'] = $agent2_id;
                                        $editAgent3RelationData['agent3_id'] = 0;
                                        $editAgent3RelationData['pid'] = $agent2_id;
                                        $editAgent3RelationData['agent_grade'] = 3;
                                        
                                        //判断总代ID是否存在,存在就修改,否则就添加
                                        $editAgent3RelationCount = $AgentRelation->getCount($edit_agent3_relation_where);
                                        
                                        if($editAgent3RelationCount > 0){
                                            $editAgent3RelationResult = $AgentRelation->editData($edit_agent3_relation_where,$editAgent3RelationData);
                                        }else{
                                            $editAgent3RelationResult = $AgentRelation->addData($editAgent3RelationData);
                                        }
                                        
                                        if(!$editAgent3RelationResult){
                                            $is_edit_success = FALSE;
                                            $return['msg'] = '修改'.$MEMBER_LEVEL[3]['name'].'ID关系信息失败!';
                                        }
                                        
                                        //判断总代ID是否有下线
                                        $edit_agent31_where['agent3_id'] = $agent2_id;
                                        $edit_agent31_where['agent_grade'] = 4;
                                        $editAgent31Count = $AgentRelation->getCount($edit_agent31_where);
                                      
                                        if($editAgent31Count > 0){
                                            
                                            //修改代理信息
                                            $edit_agent32_where['_string'] = ' agentId IN(SELECT member_id FROM agent_relation WHERE agent3_id = "'.$agent2_id.'" AND agent_grade = 4)';
                                            $editAgent32Data['parent_id'] = $agent3_id;

                                            $editAgent32Result = $Agent->editData($edit_agent32_where,$editAgent32Data);
                                           
                                            if(!$editAgent32Result){
                                                $is_edit_success = FALSE;
                                                $return['msg'] = '修改'.$MEMBER_LEVEL[3]['name'].'ID下级代理信息失败!';
                                            }
                                            
                                            //修改代理关系
                                            $editAgent31Data['top1_id'] = $agent_top2_id;
                                            $editAgent31Data['top2_id'] = $agent_agent1_id;
                                            $editAgent31Data['agent1_id'] = $agent_id;
                                            $editAgent31Data['agent2_id'] = $agent2_id;
                                            $editAgent31Data['agent3_id'] = $agent3_id;
                                            $editAgent31Data['pid'] = $agent3_id;
                                            $editAgent31Result = $AgentRelation->editData($edit_agent31_where,$editAgent31Data);

                                            if(!$editAgent31Result){
                                                $is_edit_success = FALSE;
                                                $return['msg'] = '添加'.$MEMBER_LEVEL[3]['name'].'ID下级关系信息失败!';
                                            }

                                        }
                                        
                                    }
                                    
                            /*
                             * ****************************************************************************************************************************************
                             * 修改总代ID相关信息  结束
                             * ****************************************************************************************************************************************
                             */
                              
                        }
                    break;
                case 3: //总代升级
                        //判断总代是否有下级,没有的话可以直接升级
                        $get_agent3_count_where['agent'.$agent_lv.'_id'] = $agent_id;
                        $getAgent3Count = $AgentRelation->getCount($get_agent3_count_where);
                        
                        if($getAgent3Count > 0 || $agent2_id  || $agent3_id){
                            
                            //判断总代ID信息
                            if(empty($agent3_id)){
                               
                                $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID不能为空!';
                                $this->ajaxReturn($return,'json');
                            }
                            
                            $agent3_info = $this->getAgent($agent3_id);

                            if(empty($agent3_info)){

                                $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID不存在!';
                                $this->ajaxReturn($return,'json');
                            }
                            
                            $agent_goods_stock_rale_where['agent_id'] = $agent3_id;
                            $agent_goods_stock_rale_where['goods_stock'] = array('gt',0);
                            $agent_goods_stock_rale_count = $AgentGoodsStockRale->getCount($agent_goods_stock_rale_where);
                            if($agent_goods_stock_rale_count > 0){
                                $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID的商品库存不为零,不能升级!';
                                $this->ajaxReturn($return,'json');
                            }

                            //判断是取下级还是会员替代总代的位置
                            if($agent3_info['agent_grade'] > 0 && $agent3_info['agent3_id'] != $agent_id){
                             
                                $return['msg'] = $MEMBER_LEVEL[3]['name'].'ID不是代理的下级!';
                                $this->ajaxReturn($return,'json');
                            }

                            //升级为创始人
                            if($star == 1){
                                //判断大区ID信息
                                if(empty($agent2_id)){
                               
                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID不能为空!';
                                    $this->ajaxReturn($return,'json');
                                }

                                $agent2_info = $this->getAgent($agent2_id);

                                if(empty($agent2_info)){

                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID不存在!';
                                    $this->ajaxReturn($return,'json');
                                }
                                
                                $agent_goods_stock_rale_where['agent_id'] = $agent2_id;
                                $agent_goods_stock_rale_where['goods_stock'] = array('gt',0);
                                $agent_goods_stock_rale_count = $AgentGoodsStockRale->getCount($agent_goods_stock_rale_where);
                                if($agent_goods_stock_rale_count > 0){
                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID的商品库存不为零,不能升级!';
                                    $this->ajaxReturn($return,'json');
                                }

                                //判断是取下级还是会员替代总代的位置
                                if($agent2_info['agent_grade'] > 0 && $agent2_info['agent3_id'] != $agent_id){
                                    
                                    $return['msg'] = $MEMBER_LEVEL[2]['name'].'ID不是代理的下级!';
                                    $this->ajaxReturn($return,'json');
                                }

                                //修改大区ID信息
                                $edit_agent330_where['agentId'] = $agent2_id;
                                $editAgent330Data['star'] = 2;
                                $editAgent330Data['parent_id'] = $agent_id;
                                $editAgent330Result = $Agent->editData($edit_agent330_where,$editAgent330Data);

                                if(!$editAgent330Result){
                                    $is_edit_success = FALSE;
                                    $return['msg'] = '修改'.$MEMBER_LEVEL[2]['name'].'ID代理信息失败!';
                                }

                                $edit_agent330_rale_where['member_id'] = $agent2_id;

                                $editAgent330RelaData['member_id'] = $agent2_id;
                                $editAgent330RelaData['top1_id'] = $agent_top2_id;
                                $editAgent330RelaData['top2_id'] = $agent_agent1_id;
                                $editAgent330RelaData['agent1_id'] = $agent_id;
                                $editAgent330RelaData['agent2_id'] = 0;
                                $editAgent330RelaData['agent3_id'] = 0;
                                $editAgent330RelaData['agent4_id'] = 0;
                                $editAgent330RelaData['pid'] = $agent_id;
                                $editAgent330RelaData['agent_grade'] = 2;

                                $editAgent330RelaCount = $AgentRelation->getCount($edit_agent330_rale_where);

                                if($editAgent330RelaCount > 0){
                                    $editAgent330RelaResult = $AgentRelation->editData($edit_agent330_rale_where,$editAgent330RelaData);
                                }else{
                                    $editAgent330RelaResult = $AgentRelation->addData($editAgent330RelaData);
                                }

                                if(!$editAgent330RelaResult){
                                    $is_edit_success = FALSE;
                                    $return['msg'] = '修改'.$MEMBER_LEVEL[2]['name'].'ID关系信息失败!';
                                }

                                //大区ID信息
                                $agent3_top1_id = $agent_top2_id;
                                $agent3_top2_id = $agent_agent1_id;
                                $agent3_agent1_id = $agent_id;
                                $agent3_agent2_id = $agent2_id;
                                $agent3_parent_id = $agent2_id;

                            }else{//升级为大区
                                $agent3_top1_id = $agent_top1_id;
                                $agent3_top2_id = $agent_top2_id;
                                $agent3_agent1_id = $agent_agent1_id;
                                $agent3_agent2_id = $agent_id;
                                $agent3_parent_id = $agent_id;
                            }

                            //修改总代ID的信息
                            $edit_agent331_where['agentId'] = $agent3_id;
                            $editAgent331Data['star'] = 3;
                            $editAgent331Data['parent_id'] = $agent3_parent_id;
                            $editAgent331Result = $Agent->editData($edit_agent331_where,$editAgent331Data);

                            if(!$editAgent331Result){
                                $is_edit_success = FALSE;
                                $return['msg'] = '修改'.$MEMBER_LEVEL[3]['name'].'ID信息失败!';
                            }

                            $edit_agent331_rale_where['member_id'] = $agent3_id;

                            $editAgent331RelaData['member_id'] = $agent3_id;
                            $editAgent331RelaData['top1_id'] = $agent3_top1_id;
                            $editAgent331RelaData['top2_id'] = $agent3_top2_id;
                            $editAgent331RelaData['agent1_id'] = $agent3_agent1_id;
                            $editAgent331RelaData['agent2_id'] = $agent3_agent2_id;
                            $editAgent331RelaData['agent3_id'] = 0;
                            $editAgent331RelaData['agent4_id'] = 0;
                            $editAgent331RelaData['pid'] = $agent3_parent_id;
                            $editAgent331RelaData['agent_grade'] = 3;

                            $editAgent331RelaCount = $AgentRelation->getCount($edit_agent331_rale_where);
                            
                            if($editAgent331RelaCount > 0){
                                $editAgent331RelaResult = $AgentRelation->editData($edit_agent331_rale_where,$editAgent331RelaData);
                            }else{
                                $editAgent331RelaResult = $AgentRelation->addData($editAgent331RelaData);
                            }
                            
                            if(!$editAgent331RelaResult){
                                $is_edit_success = FALSE;
                                $return['msg'] = '修改'.$MEMBER_LEVEL[3]['name'].'ID关系信息失败!';
                            }
                           
                            //修改代理所有下级为总代ID的下级
                                //判断该代理是否有特约
                                $edit_agent_next_agent3_where['agent3_id'] = $agent_id;
                                $edit_agent_next_agent3_where['agent_grade'] = 4;
                                
                                $editAgentNextAgent3Count = $AgentRelation->getCount($edit_agent_next_agent3_where);
                             
                                if($editAgentNextAgent3Count > 0){
                                    //修改下级总代代理信息
                                    $edit_next_agent3_where['_string'] = ' agentId IN(SELECT member_id FROM agent_relation WHERE agent3_id = "'.$agent_id.'" AND agent_grade = 4)';

                                    $editNextAgent3Data['parent_id'] = $agent3_id;
                                    $editNextAgent3Result = $Agent->editData($edit_next_agent3_where,$editNextAgent3Data);
                                    
                                    if(!$editNextAgent3Result){
                                        $is_edit_success = FALSE;
                                        $return['msg'] = '修改代理下级为'.$MEMBER_LEVEL[3]['name'].'ID下级信息失败!';
                                    }

                                    //修改下级总代关系信息
                                    $editAgentNextAgent3Data['top1_id'] = $agent3_top1_id;
                                    $editAgentNextAgent3Data['top2_id'] = $agent3_top2_id;
                                    $editAgentNextAgent3Data['agent1_id'] = $agent3_agent1_id;
                                    $editAgentNextAgent3Data['agent2_id'] = $agent3_agent2_id;
                                    $editAgentNextAgent3Data['agent3_id'] = $agent3_id;
                                    $editAgentNextAgent3Data['pid'] = $agent3_id;
                                    $editAgentNextAgent3Result = $AgentRelation->editData($edit_agent_next_agent3_where,$editAgentNextAgent3Data);

                                    if(!$editAgentNextAgent3Result){
                                        $is_edit_success = FALSE;
                                        $return['msg'] = '修改代理下级为'.$MEMBER_LEVEL[3]['name'].'ID下级关系信息失败!';
                                    }

                                }
                        }

                    break;
                
            }
            
            //修改代理关系信息
            switch ($star) {
                case 1:
                        $editAgentRelaData['top1_id'] = $agent_top2_id;
                        $editAgentRelaData['top2_id'] = $agent_agent1_id;
                        $editAgentRelaData['agent1_id'] = 0;
                        $editAgentRelaData['agent2_id'] = 0;
                        $editAgentRelaData['agent3_id'] = 0;
                        $parent_id = $agent_agent1_id;

                    break;
                case 2:
                        $editAgentRelaData['top1_id'] = $agent_top1_id;
                        $editAgentRelaData['top2_id'] = $agent_top2_id;
                        $editAgentRelaData['agent1_id'] = $agent_agent1_id;
                        $editAgentRelaData['agent2_id'] = 0;
                        $editAgentRelaData['agent3_id'] = 0;
                        $parent_id = $agent_agent1_id;
                    break;
                case 3:
                        $editAgentRelaData['top1_id'] = $agent_top1_id;
                        $editAgentRelaData['top2_id'] = $agent_top2_id;
                        $editAgentRelaData['agent1_id'] = $agent_agent1_id;
                        $editAgentRelaData['agent2_id'] = $agent_agent2_id;
                        $editAgentRelaData['agent3_id'] = 0;
                        $parent_id = $agent_agent2_id;
                    break;

            }

            $editAgentRelaData['agent_grade'] = $star;
            $editAgentRelaData['pid'] = $parent_id;
            
            $editAgentData['parent_id'] = $parent_id;
        }
        
        
        //修改代理关系信息
        $edit_agent_rela_where['member_id'] = $agent_id;
        
        $agent_info['is_validate'] != $stat ? $editAgentRelaData['is_validate'] = $stat : '';
        
        if($editAgentRelaData){
            $editAgentRelaResult = $AgentRelation->editData($edit_agent_rela_where,$editAgentRelaData);
           
            if(!$editAgentRelaResult){
                $is_edit_success = FALSE;
                $return['msg'] = '修改代理关系信息失败!';
            }
        }
      
        //修改代理自己的信息
        $edit_agent_where['agentId'] = $agent_id;
        
        $editAgentData['team_name'] = $team_name;
        $editAgentData['star'] = $star;
        $editAgentData['name'] = $name;
        $editAgentData['weixin'] = $weixin;
        $editAgentData['qq'] = $qq;
        $editAgentData['agentNo'] = $agentno;
        $editAgentData['cardNo'] = $cardno;
        $editAgentData['tel'] = $tel;
        $editAgentData['stat'] = $stat;
        $editAgentData['province'] = $province;
        $editAgentData['city'] = $city;
        $editAgentData['county'] = $county;
        $editAgentData['address'] = $address;
        $editAgentData['startime'] = strtotime($startime);
        $editAgentData['endtime'] = strtotime($endtime);
        $password ? $editAgentData['password'] = md5($password) : '';
        
        $editAgentResult = $Agent->editData($edit_agent_where,$editAgentData);
       
        if(!$editAgentResult){
            $is_edit_success = FALSE;
            $return['msg'] = '修改代理信息失败!';
        }
        
        if($is_edit_success){
            $return = array('status'=>1,'msg'=>'修改成功','result'=>'');
            $Agent->commit();//提交事务
            
            //清除缓存
            $Redis = new \Think\Cache\Driver\Redis();
            $Redis->clear();
        }else{
            $Agent->rollback(); //事务回滚
        }
        
        $this->ajaxReturn($return,'json');
        
    }
    
    //添加代理商页面
    public function addAgentView() {
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        //授权号
        $Agent = D('Agent');
        $agentno = $Agent->makeAgentNo();
        
        $info['agentno'] = $agentno;
        $info['startime'] = date('Y-m-d H:i');
        $info['endtime'] = date('Y-m-d H:i',strtotime('+1 year -1 day'));
        $this->assign('info',$info);
       
        $this->display('addAgentView');
    }
    
    //添加代理商
    public function addAgent() {
        $return = array('status'=>0,'msg'=>'添加失败','result'=>'');
        $post = I('post.');
        $team_name = $post['team_name'];
        $name = $post['name'];
        $weixin = $post['weixin'];
        $agentno = $post['agentno'];
        $tel = $post['tel'];
        $cardno = $post['cardno'];
        $star = $post['star'];
        $startime = $post['startime'];
        $endtime = $post['endtime'];
        $qq = $post['qq'];
        $province = $post['province'];
        $city = $post['city'];
        $county = $post['county'];
        $address = $post['address'];
        $stat = $post['stat'];
        $password = $post['password'];
        $password2 = $post['password2'];
//        
//        if(empty($team_name)){
//            $return['msg'] = '请输入团队名称!';
//            $this->ajaxReturn($return,'json');
//        }
//        
        if(empty($name)){
            $return['msg'] = '请输入姓名';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($agentno)){
            $return['msg'] = '授权号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($tel)){
            $return['msg'] = '手机号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($cardno)){
            $return['msg'] = '身份证号不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if(empty($weixin)){
            $return['msg'] = '微信号不能为空';
            $this->ajaxReturn($return,'json');
        }
       
        /*
            if(!is_numeric($star)){
                $return['msg'] = '代理星别不能为空';
                $this->ajaxReturn($return,'json');
            }

            if($star > 1){
                $return['msg'] = '您只能添加省级代理';
                $this->ajaxReturn($return,'json');
            }
        */
        
        if(empty($password)){
            $return['msg'] = '密码不能为空';
            $this->ajaxReturn($return,'json');
        }
        
        if($password != $password2){
            $return['msg'] = '密码与确认密码不一致!';
            $this->ajaxReturn($return,'json');
        }
        
        //添加代理信息
        $Agent = D('Agent');
        
        //判断微信号是否存在
        $weixin_where['weixin'] = $weixin;
        $is_count = $Agent->getCount($weixin_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($is_count > 0){
            $return['msg'] = '该微信号已经存在,请换一个微信号!';
            $this->ajaxReturn($return,'json');
        }
        
        //判断手机号是否存在
        $tel_where['tel'] = $tel;
        $is_count = $Agent->getCount($tel_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($is_count > 0){
            $return['msg'] = '该手机号已经存在,请换一个手机号!';
            $this->ajaxReturn($return,'json');
        }
        
        //判断身份证号是否存在
        $cardNo_where['cardNo'] = $cardno;
        $is_count = $Agent->getCount($cardNo_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        if($is_count > 0){
            $return['msg'] = '该身份证号已经存在,请换一个身份证号!';
            $this->ajaxReturn($return,'json');
        }
        
//        $Agent->startTrans(); //开启事务
        $datetime = date('Y-m-d H:i:s');
        $parent_id = 0;
        
        $addData['star'] = $star ? $star : 5;
        $addData['parent_id'] = $parent_id;
        $addData['name'] = $name;
        $addData['weixin'] = $weixin;
        $addData['add_time'] = $datetime;
        $addData['qq'] = $qq;
        $addData['agentNo'] = $agentno;
        $addData['cardNo'] = $cardno;
        $addData['tel'] = $tel;
        $addData['stat'] = $stat;
        $addData['province'] = $province;
        $addData['city'] = $city;
        $addData['county'] = $county;
        $addData['address'] = $address;
        $addData['startime'] = strtotime($startime);
        $addData['endtime'] = strtotime($endtime);
//        $addData['team_name'] = $team_name;
        $password ? $addData['password'] = md5($password) : '';
        
        $member_id = $Agent->addData($addData);
        
        /*
        //添加代理关系
        $AgentRelation = D('AgentRelation');
        $agent_where['member_id'] = $parent_id;
        $agent_info = $AgentRelation->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        $agent1_id = $agent_info['agent1_id'] ? $agent_info['agent1_id'] : ($star == 2 ? $parent_id : 0);
        $agent2_id = $agent_info['agent2_id'] ? $agent_info['agent2_id'] : ($star == 3 ? $parent_id : 0);
        $agent3_id = $agent_info['agent3_id'] ? $agent_info['agent3_id'] : ($star == 4 ? $parent_id : 0);
        $agent4_id = $agent_info['agent4_id'] ? $agent_info['agent4_id'] : 0;
        
        $is_agent = $star == 0 ? 0 : 1;
        $arData['member_id'] = $member_id;
        $arData['agent1_id'] = $agent1_id;
        $arData['agent2_id'] = $agent2_id;
        $arData['agent3_id'] = $agent3_id;
        $arData['agent4_id'] = $agent4_id;
        $arData['pid'] = $parent_id;
        $arData['agent_grade'] = $star;
        $arData['is_cancel'] = 0;
        $arData['is_agent'] = $is_agent;
        $arData['is_validate'] = $stat;
        
        $result = $AgentRelation->addData($arData);
        */
        
        if($member_id){
            $return = array('status'=>1,'msg'=>'添加成功','result'=>'');
//            $Agent->commit();//提交事务
        }else{
//            $Agent->rollback(); //事务回滚
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //删除代理 实际是加入黑名单
    public function delAgent() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        $agent_info = $this->getAgent($agent_id);
        
        if(empty($agent_info)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        //查询代理是否有发货记录
        $DeliverGoods = D('DeliverGoods');
        $deliver_goods_where['agent_id'] = $agent_id;
        $dgCount = $DeliverGoods->getCount($deliver_goods_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($dgCount > 0){
            $return['msg'] = '该代理已经发过货,不能删除!';
            $this->ajaxReturn($return,'json');
        }
        
        //查询代理是否有下级
        $AgentRelation = D('AgentRelation');
        $agent_relation_where['pid'] = $agent_id;
        $arCount = $AgentRelation->getCount($agent_relation_where,array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        if($arCount > 0){
            $return['msg'] = '该代理已经有下级代理,不能删除!';
            $this->ajaxReturn($return,'json');
        }
        
        $Agent = D('Agent');
        
        $Agent->startTrans();
        $is_edit_success = TRUE;
        
        $a_result = $Agent->delData(array('agentId'=>$agent_id));
        
        if(!$a_result){
            $is_edit_success = FALSE;
            $return['msg'] = '删除代理信息失败!';
        }
        
        //判断是否有关系信息,有就删除
        $del_rale_where['member_id'] = $agent_id;
        $raleCount = $AgentRelation->getCount($del_rale_where);
        
        if($raleCount > 0){
            $ar_result = $AgentRelation->delData($del_rale_where);
            if(!$ar_result){
                $is_edit_success = FALSE;
                $return['msg'] = '删除代理关系信息失败!';
            }
        }
        
        if($is_edit_success){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
            S(C('AGENT_INFO').$agent_id,NULL);
            $Agent->commit();
        }else{
            $Agent->rollback();
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //通过代理审核
    public function adoptAgent() {
        $return = array('status'=>0,'msg'=>'通过失败','result'=>'');
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        $ar_where['member_id'] = $where['agentId'] = $agent_id;
        $Agent = D('Agent');
        $AgentRelation = D('AgentRelation');
        $Agent->startTrans();
        
        $areData['is_validate'] = $aeData['stat'] = 1;
        
        $a_result = $Agent->editData($where,$aeData);
        $ar_result = $AgentRelation->editData($ar_where,$areData);
        
        if($a_result && $ar_result){
            $return = array('status'=>1,'msg'=>'通过成功','result'=>'');
            $Agent->commit();
        }else{
            $Agent->rollback();
        }
        
        $this->ajaxReturn($return,'json');
    }
    
    //代理商导入
    public function importAgent() {
        
    }
    
    //代理商导出
    public function exportAgent() {
        
    }
    
    //发展下级页面
    public function growthAgentView() {
        $agent_lv_list = C('MEMBER_LEVEL');
        $this->assign('agent_lv_list',$agent_lv_list);
        
        
        $name = I('name');
        $star = I('star');
        $team_name = I('team_name');
        $top2_id = I('top2_id',0);
        $is_founder = I('is_founder');
        
        if($name){
            
            $time = time();
            $String  = new \Org\Util\String();
            $str = $String->randString(6,1);
            $inviteCode = $time.$str;

            $addData['inviteCode'] = $inviteCode;
            $addData['name'] = $name;
            $addData['team_name'] = $team_name;
            $addData['agentId'] = $top2_id;
            $addData['star'] = $star;
            $addData['stat'] = 0;
            $addData['top2_id'] = $top2_id;
            $addData['is_founder'] = $is_founder;
            
            if($top2_id > 0){
                $AgentRelation = D('AgentRelation');
                $agent_where['member_id'] = $top2_id;
                $agent_info = $AgentRelation->getDetail($agent_where,array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
                $addData['top1_id'] = $agent_info['pid'] ? $agent_info['pid'] : 0;
            }

            $Invitecode = D('Invitecode');
            $result = $Invitecode->addData($addData);

            if($result){
                $url = get_share_url();
                $url = $url.'/Login/reg.html?code='.$inviteCode;
                $return = array('url'=>$url,'code'=>$inviteCode);
                $this->assign('return',$return);
            }
        }
      
        $this->display('growthAgentView');
    }
    
    //邀请码管理
    public function inviteManege() {
        
        $name = I('name');
        $page = I('p',1);
        
        $name ? $where['name'] = $name : '';
        
        $limit=10;
        
        $Invitecode = D('Invitecode');
        
        $count      = $Invitecode->getCount($where,array('key'=>false,'expire'=>null,'cache_type'=>null));// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$limit);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        
        $list = $Invitecode->getList($where,$limit,$page,'star',array('field'=>array(),'is_opposite'=>false),array('key'=>false,'expire'=>null,'cache_type'=>null));
        
        //搜索条件
        $search['name'] = $name;
        $this->assign('search',$search);
       
        $this->assign('list_count',$count);
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        
        $this->display('inviteManege');
    }
    
    //删除邀请码
    public function delInviteCode() {
        $return = array('status'=>0,'msg'=>'删除失败','result'=>'');
        
        $inviteId = I('aid');
        
        if(empty($inviteId)){
            $return['msg'] = '请选择邀请码';
            $this->ajaxReturn($return,'json');
        }
        
        $where['inviteId'] = $inviteId;
        $Invitecode = D('Invitecode');
        $result = $Invitecode->delData($where);
        
        if($result){
            $return = array('status'=>1,'msg'=>'删除成功','result'=>'');
        }
        
        $this->ajaxReturn($return,'json');
        
    }
    
    //微信解绑
    public function weixinUnbing() {
        $return = array('status'=>0,'msg'=>'更改失败','result'=>'');
        
        $agent_id = I('aid');
        
        if(empty($agent_id)){
            $return['msg'] = '请选择代理';
            $this->ajaxReturn($return,'json');
        }
        
        $Agent = D('Agent');
        
        $where['agentId'] = $agent_id;
        
        $openid = $Agent->where($where)->getField('openid');
        
        if(empty($openid)){
            $return['msg'] = '该代理还没有绑定微信!';
            $this->ajaxReturn($return,'json');
        }
        
        $editData['openid'] = NULL;
        $editData['head_img'] = NULL;
        
        $result = $Agent->editData($where,$editData);
        
        if($result){
            $return = array('status'=>1,'msg'=>'更改成功','result'=>'');
        }
        
        $return['result'] = $Agent->_sql();
        
        $this->ajaxReturn($return,'json');
        
    }
    
    
}