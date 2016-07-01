<?php

    /**
    * 验证手机号是否正确
    * @param INT $mobile
    */
    function isMobile($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }

    //XML转成数组
    function xml2arr($xml){
        $obj  = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($obj);
        $arr  = json_decode($json, true);
        return $arr;
    }
    
    //判断是否微信浏览
    function is_weixin() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            // 非微信浏览器禁止浏览
            return FALSE;
        } else {
            // 微信浏览器，允许访问
            return TRUE;

        }
    }

    //获取微信版本号
    function getWxVersion(){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger')) {
            // 非微信浏览器禁止浏览
             preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
             return $matches[2];
        } 

        return '0';
    }

    //生成代理授权书
    function make_agent_auth($agent_info){
        if(empty($agent_info)){
            return FALSE;
        }

        $member_id = $agent_info['agentid'];
        $name = $agent_info['name'];
        $agent_grade = $agent_info['agent_grade'];
        $auth_num = $agent_info['agentno'];
        $card_num = $agent_info['cardno'];
        $weixin = $agent_info['weixin'];
        $lv_name = $agent_info['agent_lv_name'];
        $start_time = explode(':',date('Y:m:d',$agent_info['startime']));
        $end_time = explode(':',date('Y:m:d',$agent_info['endtime']));
        $root_path = './Public/';
        $child_path = 'Common/imges/';
        $path = $root_path.$child_path;
        $font_path = $path.'authbook/';
        $save_path = $path.'myauth/';
        $time = date('Ymd');
        $new_img_path = $save_path.$time.'/'.$member_id.'.jpg'; //生成代理证书存放路径
        $font = 'simhei.ttf'; //字体

        //保存会员授权证书
        $save_img_path = $child_path.'myauth/'.$time.'/'.$member_id.'.jpg'; //保存到数据表的图片路径

        //批量删除文件
        deldir($save_path);

        //没有目录就创建
        if(!is_dir($save_path.$time)){
            mkdir($save_path);
            mkdir($save_path.$time);
        }

        $new_card_num = '';
        $length = strlen($card_num)-10;
        $pre_card = substr($card_num,0,6);
        $hm_card = substr($card_num,-4);
        for($i=0;$i<=$length;$i++){
            $new_card_num .= '*';
        }
        $new_card_num = $pre_card.$new_card_num.$hm_card;
    
        $Image = new \Think\Image(); // 在图片右下角添加水印文字 ThinkPHP 并保存为new.jpg
        $OpenImage = $Image->open($font_path.$agent_grade.'.jpg');
        $width = $Image->width(); // 返回图片的宽度
        $height = $Image->height(); // 返回图片的高度

        $Image->text($auth_num,$font_path.$font,16,'#000000',array(340,590),-100,0) //生成授权号
                ->text($name,$font_path.$font,17,'#000000',array(450,405),-100,0) //生成名字
                ->text($new_card_num,$font_path.$font,18,'#000000',array(730,410),-100,0) //生成身份证号
                ->text($weixin,$font_path.$font,18,'#000000',array(328,448),-100,0) //生成微信号
//                ->text($lv_name,$font_path.$font,17,'#000000',array(308,560),-100,0) //生成代理级别
                ->text($start_time[0],$font_path.$font,13,'#000000',array(335,625),-100,0) //生成开始年
                ->text($start_time[1],$font_path.$font,13,'#000000',array(405,625),-100,0) //生成开始月
                ->text($start_time[2],$font_path.$font,13,'#000000',array(455,625),-100,0) //生成开始日
                ->text($end_time[0],$font_path.$font,13,'#000000',array(525,625),-100,0) //生成结束年
                ->text($end_time[1],$font_path.$font,13,'#000000',array(590,625),-100,0) //生成结束月
                ->text($end_time[2],$font_path.$font,13,'#000000',array(635,625),-100,0) //生成结束日
                ->save($new_img_path); 

        return $save_img_path;
    }

    //批量删除文件
    function deldir($dir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
          if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
          }
        }

        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
          return true;
        } else {
          return false;
        }
    }

    //获取分享邀请URL
    function get_share_url() {
        $domain_rules = array_flip(C('APP_SUB_DOMAIN_RULES'));
        $http_host = explode('.',$_SERVER["HTTP_HOST"]);
        $top_host = $http_host[1].'.'.$http_host[2];
        $url = 'http://'.$domain_rules['Mobile'].'.'.$top_host;

        return $url;
    }

    //判断标签的类型 type: 1:大标,2:小标,3:防伪标,4:中标
    function judge_code_type($code) {
        $LABEL_CODE_SECTION = C('LABEL_CODE_SECTION');
        $code_type = 0;
        $new_code = substr($code, -7);
        foreach ($LABEL_CODE_SECTION as $k => $v) {
            if($new_code >= $v['start'] && $new_code <= $v['end']){
                $code_type = $v['type'];
            }
        }
        
        return $code_type;
        
    }
    

