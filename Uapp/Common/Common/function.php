<?php

    /**
    * 判断是否手机号码
    * @param  {[type]}  $tel [手机号码]
    * @return {Boolean}     [返回值: ture:是,false:否]
    */
   function isMobile($tel) {
       if(empty($tel)){
           return FALSE;
       }
       
       $result = preg_match('/^1[34578]{1}[0-9]{9}$/',$tel);
       
       if($result == 1){
           return TRUE;
       }
       
       return FALSE;
   }
   
   /**
    * [isEmail 验证电子邮箱格式是否正确]
    * @param  {[type]}  $email [电子邮箱]
    * @return {Boolean}       [返回值: ture:是,false:否]
    */
    function isEmail($email){
        if(empty($email)){
            return FALSE;
        }
        
        $result = preg_match('/^\\w+(([+-_\.])*\\w+)*@\\w+\.(\\w+\.)*\\w+$/',$email);
        
        if($result == 1){
           return TRUE;
        }
       
        return FALSE;
    }
    
    /**
    * [isChinaWord 只能输入数字跟字母]
    * @param  {[type]}  $word [字符串]
    * @param  {[type]}  $min  [最少输入的数量]
    * @param  {[type]}  $max  [最多输入的数量]
    * @return {Boolean}      [返回值: true:是,false:否]
    */
    function isChinaWord($word,$min = 1,$max = 6){
        
        if(empty($word)){
            return FALSE;
        }
        
        $min = $min > 0 ? $min : 1;
        $max = $max > $min ? $max : ($min + 1); 
        
        $result = preg_match('/^\\w{'.$min.','.$max.'}$/',$word);
        
        if($result == 1){
           return TRUE;
        }
       
        return FALSE;
        
    }

    /**
    * 判断是否身份证号码
    * @param  {[type]}  $card [身份证号码]
    * @return {Boolean}     [返回值: ture:是,false:否]
    */
    function isCard($card) {
        
        $card_len = strlen($card);
        
        //判断身份证的长度,只有: 15 位与 18位两种
        if($card_len != 18 && $card_len != 15){
            return false;
        }

        //判断是否是数字
        $is_card = preg_match('/^[0-9]{15,17}[0-9x]?$/',$card);
        if($is_card == 0){
            return false;
        }

        //15位的身份证不做验证
        if($card_len == 15){
            return true;
        }else{ //获取18位身份证最后一位数
            $last_num = strtolower(substr($card, -1));    ;
        }

        $sum = 0; //初始化和
        $W = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2); //加权因子值
        $validCode = array(1,0,'x',9,8,7,6,5,4,3,2);  //校验码

        //求和
        for($i=0;$i < 17 ;$i++){
            $sum = $sum + $card[$i] * $W[$i];
        }
       
        $mod = $sum%11;
       
        if($validCode[$mod] == $last_num){
            return true;
        }else{
            return false;
        }
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
    
    /**
    * 发送HTTP请求方法，目前只支持CURL发送请求
    * @param  string  $url    请求URL
    * @param  array   $params 请求参数
    * @param  string  $method 请求方法GET/POST
    * @param  boolean $ssl    是否进行SSL双向认证
    * @return array   $data   响应数据
    * @author 、lin
    */
    function http($url, $params = array(), $method = 'GET'){
            $opts = array(
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
            );
            /* 根据请求类型设置特定参数 */
            switch(strtoupper($method)){
                    case 'GET':
                            $getQuerys = !empty($params) ? '?'. http_build_query($params) : '';
                            $opts[CURLOPT_URL] = $url . $getQuerys;

                            break;
                    case 'POST':
                            $opts[CURLOPT_URL] = $url;
                            $opts[CURLOPT_POST] = 1;
                            $opts[CURLOPT_POSTFIELDS] = $params;
                            break;
            }
           
            /* 初始化并执行curl请求 */
            $ch     = curl_init();
            curl_setopt_array($ch, $opts);
            $data   = curl_exec($ch);
            $err    = curl_errno($ch);
            $errmsg = curl_error($ch);
            curl_close($ch);
            if ($err > 0) {
                   
                    return false;
            }else {
                    return $data;
            }
    }


