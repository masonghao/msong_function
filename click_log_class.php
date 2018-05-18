<?php
ini_set('display_errors',1);
/*
 * 日志记录
 * 每天生成一个日志文件，当文件超过指定大小则备份日志文件并重新生成新的日志文件
 *
 * <script src="http://47.100.7.234/ms/js/jquery1.10.2.js" type="text/javascript"></script>
 * <script>$.get("http://47.100.7.234/c/c_click.php?mc="+encodeURIComponent(window.location.href), function(result){ });</script>
 *
*/
class Log {
	private $maxsize = 1024000; //最大文件大小1M
	
	//写入日志
	public function writeLog($filename,$msg){
		$res = array();
		$res['msg'] = $msg;
		$res['logtime'] = date("Y-m-d H:i:s",time());

		//如果日志文件超过了指定大小则备份日志文件
		if(file_exists($filename) && (abs(filesize($filename)) > $this->maxsize)){
			$newfilename = dirname($filename).'/'.time().'-'.basename($filename);
			rename($filename, $newfilename);
		}

		//如果是新建的日志文件，去掉内容中的第一个字符逗号
		if(file_exists($filename) && abs(filesize($filename))>0){
			$content = ",".json_encode($res);
		}else{
			$content = json_encode($res);
		}

		//往日志文件内容后面追加日志内容
	    file_put_contents($filename, $content, FILE_APPEND);
	}


	//读取日志
	public function readLog($filename){
		if(file_exists($filename)){
			$content = file_get_contents($filename);
			$json = json_decode('['.$content.']',true);
		}else{
			$json = '{"msg":"The file does not exist."}';
		}
		return $json;
	}
    public function getIpAdress(){ 
        if(getenv('HTTP_CLIENT_IP')) {
          $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
          $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
          $onlineip = getenv('REMOTE_ADDR');
        } else {
          $onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        }   
        return $onlineip;
    }
        
    /**  
     * 获取客户端浏览器信息 添加win10 edge浏览器判断  
     * @param  null  
     * @author  Jea杨  
     * @return string   
     */  
    function get_broswer(){  
         $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串  
         if (stripos($sys, "Firefox/") > 0) {  
             preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);  
             $exp[0] = "Firefox";  
             $exp[1] = $b[1];  //获取火狐浏览器的版本号  
         } elseif (stripos($sys, "Maxthon") > 0) {  
             preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);  
             $exp[0] = "傲游";  
             $exp[1] = $aoyou[1];  
         } elseif (stripos($sys, "MSIE") > 0) {  
             preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);  
             $exp[0] = "IE";  
             $exp[1] = $ie[1];  //获取IE的版本号  
         } elseif (stripos($sys, "OPR") > 0) {  
                 preg_match("/OPR\/([\d\.]+)/", $sys, $opera);  
             $exp[0] = "Opera";  
             $exp[1] = $opera[1];    
         } elseif(stripos($sys, "Edge") > 0) {  
             //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配  
             preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);  
             $exp[0] = "Edge";  
             $exp[1] = $Edge[1];  
         } elseif (stripos($sys, "Chrome") > 0) {  
                 preg_match("/Chrome\/([\d\.]+)/", $sys, $google);  
             $exp[0] = "Chrome";  
             $exp[1] = $google[1];  //获取google chrome的版本号  
         } elseif(stripos($sys,'rv:')>0 && stripos($sys,'Gecko')>0){  
             preg_match("/rv:([\d\.]+)/", $sys, $IE);  
                 $exp[0] = "IE";  
             $exp[1] = $IE[1];  
         }else {  
            $exp[0] = "未知浏览器";  
            $exp[1] = "";   
         }  
         return $exp[0].'('.$exp[1].')';  
    }  
    /**  
     * 获取客户端操作系统信息包括win10  
     * @param  null  
     * @author  Jea杨  
     * @return string   
     */  
    function get_os(){  
    $agent = $_SERVER['HTTP_USER_AGENT'];  
        $os = false; 
        if (strpos($agent, 'Android') !== false) {//strpos()定位出第一次出现字符串的位置，这里定位为0  
            preg_match("/(?<=Android )[\d\.]{1,}/", $agent, $version);  
            // echo 'Platform:Android OS_Version:'.$version[0];  
            $os = 'Android('.$version[0].')';  
        } elseif (strpos($agent, 'iPhone') !== false) {  
            preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $agent, $version);  
            // echo 'Platform:iPhone OS_Version:'.str_replace('_', '.', $version[0]);
            $os = 'iPhone('.$version[0].')';
        } elseif (strpos($agent, 'iPad') !== false) {  
            preg_match("/(?<=CPU OS )[\d\_]{1,}/", $agent, $version);  
            // echo 'Platform:iPad OS_Version:'.str_replace('_', '.', $version[0]);
            $os = 'iPad('.$version[0].')';
        } else if (preg_match('/win/i', $agent) && strpos($agent, '95')) {  
          $os = 'Windows 95';  
        } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {  
          $os = 'Windows ME';  
        } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {  
          $os = 'Windows 98';  
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {  
          $os = 'Windows Vista';  
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {  
          $os = 'Windows 7';  
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {  
          $os = 'Windows 8';  
        } else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {  
          $os = 'Windows 10';#添加win10判断  
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {  
          $os = 'Windows XP';  
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {  
          $os = 'Windows 2000';  
        } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {  
          $os = 'Windows NT';  
        } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {  
          $os = 'Windows 32';  
        } else if (preg_match('/linux/i', $agent)) {  
          $os = 'Linux';  
        } else if (preg_match('/unix/i', $agent)) {  
          $os = 'Unix';  
        } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {  
          $os = 'SunOS';  
        } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {  
          $os = 'IBM OS/2';  
        } else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)) {  
          $os = 'Macintosh';  
        } else if (preg_match('/PowerPC/i', $agent)) {  
          $os = 'PowerPC';  
        } else if (preg_match('/AIX/i', $agent)) {  
          $os = 'AIX';  
        } else if (preg_match('/HPUX/i', $agent)) {  
          $os = 'HPUX';  
        } else if (preg_match('/NetBSD/i', $agent)) {  
          $os = 'NetBSD';  
        } else if (preg_match('/BSD/i', $agent)) {  
          $os = 'BSD';  
        } else if (preg_match('/OSF1/i', $agent)) {  
          $os = 'OSF1';  
        } else if (preg_match('/IRIX/i', $agent)) {  
          $os = 'IRIX';  
        } else if (preg_match('/FreeBSD/i', $agent)) {  
          $os = 'FreeBSD';  
        } else if (preg_match('/teleport/i', $agent)) {  
          $os = 'teleport';  
        } else if (preg_match('/flashget/i', $agent)) {  
          $os = 'flashget';  
        } else if (preg_match('/webzip/i', $agent)) {  
          $os = 'webzip';  
        } else if (preg_match('/offline/i', $agent)) {  
          $os = 'offline';  
        } else {
            $os = '未知操作系统！';
        } 
        return $os;    
    }
}

//测试 mc 代表要记录的信息，如访问的 url
if( isset($_GET['test']) && isset($_GET['mc']) ){
    $L = new Log();
    $info = $L->getIpAdress().' | '.$L->get_os().' | '.$L->get_broswer().' | '.urldecode ($_GET['mc']);
    $L->writeLog("ms_log_test.txt",$info);
    
    echo $info;// 记录输出提示
    exit();
}

// 程序调用开始
if(isset($_GET['mc'])){
    $L = new Log();
    
    $L->writeLog("ms_log.txt",$L->getIpAdress().' | '.$L->get_os().' | '.$L->get_broswer().' | '.urldecode ($_GET['mc']));
    
    echo 'success!';// 记录输出提示
}else if( isset($_GET['m']) ){
    $L = new Log();
    
    $logs = $L->readLog("ms_log.txt");
    foreach($logs as $li){
        if(@$_GET['s']!="1" && substr($li['msg'],0,14) == "*.*.*.*"){
            continue;
        }
        echo $li['logtime'].' | '.$li['msg']."<br />";
        
    }
}else{
    // $content = file_get_contents("ms_log.txt");
    // $json = json_decode('['.$content.']',true);
    // //$json[0] = array_values($json[0]);
    
    // foreach($json as $key=>$li){
    //     if( substr($li['msg'],0,14) == "114.94.185.236" ){
    //         unset( $json[$key] );
    //     }
    // }
    // $file_string = ltrim(rtrim(json_encode($json), ']'), '[');
    // file_put_contents('ms_log.txt', $file_string);
}
