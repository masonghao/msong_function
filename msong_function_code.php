<?php
/*
* 1. thinkphp3.2版本内dump函数代码
* 2. 把点格式的ip地址转换成整数表示的ip地址  13761238751
* 3. CURL《 1)GET 请求  2)POST 请求  3)get方式批量抓取数据 》
* 4. 分页输出类：$totalPage总页数、$page当前页、$gets其它参数数组
* 5. 据库查询、连接函数
* 6. 输入表单数据处理之纯字母英文用户名
* 7. 接口信息
* 8. PHP calss 汉字转拼音
* 9. PHP calss blowfish加密ecb模式
* 10. PHP 5.4版本前，hex2bin替代函数 myhex2bin
* 11. 迭代读取数组中分级数据父id子id为带缩进字符串的方法
* 12. 此函数获取目录下全部文件到一个数组
* 13. 分页html代码输出
* 14. 网络图片下载到本地
* 15. 读取目录下所有文件和目录到数组
* 16. 读取csv文件到一个数组
*/

// 1. thinkphp3.2版本内dump函数代码
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

// 2. 把点格式的ip地址转换成整数表示的ip地址  
function EncodeIp($strDotquadIp) {
    $arrIpSep = explode('.', $strDotquadIp);

    if (count($arrIpSep) != 4){
        $intIp = 0;
    }else{
        $intIp = 0;
        foreach ($arrIpSep as $k => $v){
            $intIp += (int)$v * pow(256, 3 - $k);
        }
    }
    return $intIp;
}


/** 3.1
 * GET 请求
 * @param string $url           
 */
function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

/** 3.2
 * POST 请求
 *
 * @param string $url           
 * @param array $param          
 * @return string content
 */
function http_post($url, $param) {
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
    }
    if (is_string($param)) {
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach ($param as $key => $val) {
            $aPOST [] = $key . "=" . urlencode($val);
        }
        $strPOST = join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus ["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/** 3.3
 * get方式批量抓取数据
 *
 * @param array urls 待爬取的网页数组   
 * @param string filedir 文件存放的文件目录          
 * @return not return
 */
function getCurlContents($urls=array(), $filedir = './'){
    if( $filedir != './' && !file_exists($filedir) ){
        mkdir($filedir, 0777) OR die('目录创建失败。');
    }
    
    $mh = curl_multi_init();  
    foreach ($urls as $i => $url) {  
        $conn[$i] = curl_init($url);  
        curl_setopt($conn[$i], CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");  
        curl_setopt($conn[$i], CURLOPT_HEADER ,0);  
        curl_setopt($conn[$i], CURLOPT_CONNECTTIMEOUT,60);  
        curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER,true); // 设置不将爬取代码写到浏览器，而是转化为字符串  
        curl_multi_add_handle ($mh,$conn[$i]);  
    }  

    do {  
        curl_multi_exec($mh,$active);  
    } while ($active);  

    foreach ($urls as $i => $url) {  
        $data = curl_multi_getcontent($conn[$i]); // 获得爬取的代码字符串  
        file_put_contents($filedir.$i.'.txt',$data); // 将字符串写入文件。当然，也可以不写入文件，比如存入数据库  
    } // 获得数据变量，并写入文件  

    foreach ($urls as $i => $url) {  
        curl_multi_remove_handle($mh,$conn[$i]);  
        curl_close($conn[$i]);  
    }
    curl_multi_close($mh);
}
// 4.分页输出类：$totalPage总页数、$page当前页、$gets其它参数数组
function makepagelists($totalPage, $page, $gets=null)
{
    //设置get参数
    $getval = '';
    if($gets)foreach($gets as $key=>$val){
       $getval .= '&'.$key.'='.$val;
    }
    
    //设置分页字符串
    $page_str = '';
    $prev = $page-1;
    $next = $page+1;
    if($totalPage == 1){
        $page_str.='<span class="active">1</span>'."\n";
    }else if($totalPage>1){
        //previous
        if ($page > 1){
            $page_str.='<a href="?page='.$prev.$getval.'" class="pre"></a>'."\n";
        }
        
        //pages
        if ($totalPage < 11)    //not enough pages to bother breaking it up
        {
            for ($i = 1; $i <= $totalPage; $i++){
                if ($i == $page){
                    $page_str.='<span class="active">'.$i.'</span>'."\n";
                }else{
                    $page_str.='<a href="?page='.$i.$getval.'">'.$i.'</a>'."\n";
                }
            }
        }else{
            if($page <= 10){
                for ($i = 1; $i <= 10; $i++){
                    if ($i == $page){
                        $page_str.='<span class="active">'.$i.'</span>'."\n";
                    }else{
                        $page_str.='<a href="?page='.$i.$getval.'">'.$i.'</a>'."\n";
                    }
                }
             }else{
                for ($i = $page - 9; $i <= 10 + ($page - 10); $i++)
                {
                    if ($i == $page){
                       $page_str.='<span class="active">'.$i.'</span>'."\n";
                    }else{
                        $page_str.='<a href="?page='.$i.$getval.'">'.$i.'</a>'."\n";
                    }
                }
            }
        }
        //nextpage
        if ($page < $totalPage){
            $page_str.='<a href="?page='.$next.$getval.'" class="next"></a>';
        }
    }
    $page_str.='<span>共'.$totalPage.'页</span>';
    
    return $page_str;
}

// 5.1 据库查询、连接函数
//获取数据表数据到一个数组
function my_select($sql, $dbname='linshi2',$host='localhost',$uname='root',$password=''){
    if(empty($sql)){ return false; }
    
    //连接数据库
    $db_mysqli = new mysqli($host, $uname, $password, $dbname);
    if ($db_mysqli->connect_errno) {
        die('Connect Error: ' . $db_mysqli->connect_errno);
    }
    
    $db_mysqli->set_charset("utf8");//设置编码
    
    //执行查询
    if( $query = $db_mysqli->query($sql) ){
        while( $row = $query->fetch_assoc() ){
            $list[] = $row;// 转存数据
    }}else{
        $list = array();
    }
    
    $db_mysqli->close();//关闭数据库连接
    
    return $list;
}
//执行数据表操作
function do_query($sql, $dbname='linshi2',$host='localhost',$uname='root',$password=''){
    if(empty($sql)){ return false; }
    
    //连接数据库
    $db_mysqli = new mysqli($host, $uname, $password, $dbname);
    if ($db_mysqli->connect_errno) {
        die('Connect Error: ' . $db_mysqli->connect_errno);
    }
    
    $db_mysqli->set_charset("utf8");//设置编码
    
    $list = $db_mysqli->query($sql);//执行查询
    
    // $num = $db_mysqli->affected_rows;//返回影响行数
    
    $db_mysqli->close();//关闭数据库连接
    
    return $list;
}
/*
 * 函数：Mysqli连接数据库方法
 * 参数：1.数据库服务器 2.用户名 3.密码 4.选择的数据库名
 * 备注：执行如，$db = linkdb(); $db->query($sql); $db->close();
 */
function linkdb($host='localhost',$uname='root',$password='',$dbname='che_data')
{
    //连接数据库
    $db_mysqli =new mysqli( $host, $uname, $password, $dbname);
    if ($db_mysqli->connect_errno) {
        die('Connect Error: ' . $db_mysqli->connect_errno);
    }
    
    $db_mysqli->set_charset("utf8");//设置编码
    
    return $db_mysqli;
}

// 6. 输入表单数据处理之纯字母英文用户名
function cleanInput($input){
    $clean = strtolower($input);
    $clean = preg_replace("/[^a-z]/", "", $clean);
    $clean = substr($clean,0,12);
    return $clean;
}
/*
* 7. 接口信息
*   1.淘宝接口获取手机归属地
*   2财付通手机号码归属地接口查询，返回值：省，市，运营商
*   3 通过淘宝接口获取ip信息
*   4 通过百度接口获取身份证号码信息
*/
// 7.1 通过淘宝接口获取手机归属地信息
function get_phoneInfo($phonenumber){
    if(!preg_match("/1[3458]{1}\d{9}$/",$phonenumber)){
        return  null;
    }
    //获取接口数据
    $number_address = @file_get_contents('https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$phonenumber);
    if( !$number_address ){
        $number_address = $this->get_ipInfo($phonenumber);
    }
    //返回数据转数组
    preg_match_all("/(\w+):'([^']+)/", $number_address, $m);
    $number_arr = array_combine($m[1], $m[2]);
    //编码转换
    if(is_array($number_arr) && count($number_arr)>0){
        foreach($number_arr as $k=>$v){
            $number_arr[$k] = iconv("GBK", "UTF-8", $number_arr[$k]);
        }
    }else{
        $number_arr =null;
    }

    return $number_arr;
}
// 7.2财付通手机号码归属地接口查询，返回值：省，市，运营商
function get_phones($p){
    $xml = iconv('gb2312','utf-8',file_get_contents('http://life.tenpay.com/cgi-bin/mobile/MobileQueryAttribution.cgi?chgmobile='.$p));

    $province = trim(substr($xml,stripos($xml,'<province>')+10,stripos($xml,'</province>')-stripos($xml,'<province>')-10));
    $city = trim(substr($xml,stripos($xml,'<city>')+6,stripos($xml,'</city>')-stripos($xml,'<city>')-6));
    $supplier = '中国'.trim(substr($xml,stripos($xml,'<supplier>')+10,stripos($xml,'</supplier>')-stripos($xml,'<supplier>')-10));
    
    $arr = array('province' => $province, 'city' => $city, 'supplier' => $supplier);
    
    return $arr;
}

// 7.3 通过淘宝接口获取ip信息
function get_ipInfo($tip){
    if(!filter_var($tip, FILTER_VALIDATE_IP)){
        return  null;
    }
    
    $ip_address = @file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip='.$tip);
    if( !$ip_address ){
        $ip_address = $this->get_ipInfo($tip);
    }
    
    $ip_address = json_decode($ip_address, true);
    
    $arr_ip = ($ip_address['code'] == 0) ? $ip_address['data'] : null;
    
    return  $arr_ip;
}

// 7.4 通过百度接口获取身份证号码信息
function get_card($num = null){
    if(empty($num)){ return null; }
    $ch = curl_init();
    $url = 'http://apis.baidu.com/apistore/idservice/id?id='.$num;
    $header = array(
        'apikey: 22f290452bda0289afba0967093795f4',
    );
    // 添加apikey到header
    curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // 执行HTTP请求
    curl_setopt($ch , CURLOPT_URL , $url);
    $res = curl_exec($ch);
    curl_close($ch);

    $arr = json_decode($res,true);
    if(isset($arr['retMsg']) && $arr['retMsg'] == 'success'){
        $list['address'] = $arr['retData']['address'];
        $list['sex'] = ($arr['retData']['sex'] == 'M') ? '男' : (($arr['retData']['sex'] == 'F') ? '女' : '未知') ;
        $list['birthday'] = $arr['retData']['birthday'];
    }else{
        return null;
    }

    return $list;
}

/** 8.
 * PHP 汉字转拼音
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140715
 * @package SPFW.core.lib.final
 * @global SEA_PHP_FW_VAR_ENV
 * @example
 *  echo CUtf8_PY::encode('阿里巴巴科技有限公司'); //编码为拼音首字母
 *  echo CUtf8_PY::encode('阿里巴巴科技有限公司', 'all'); //编码为全拼音
 */
class CUtf8_PY {
    /**
     * 拼音字符转换图
     * @var array
     */
    private static $_aMaps = array(
        'a'=>-20319,'ai'=>-20317,'an'=>-20304,'ang'=>-20295,'ao'=>-20292,
        'ba'=>-20283,'bai'=>-20265,'ban'=>-20257,'bang'=>-20242,'bao'=>-20230,'bei'=>-20051,'ben'=>-20036,'beng'=>-20032,'bi'=>-20026,'bian'=>-20002,'biao'=>-19990,'bie'=>-19986,'bin'=>-19982,'bing'=>-19976,'bo'=>-19805,'bu'=>-19784,
        'ca'=>-19775,'cai'=>-19774,'can'=>-19763,'cang'=>-19756,'cao'=>-19751,'ce'=>-19746,'ceng'=>-19741,'cha'=>-19739,'chai'=>-19728,'chan'=>-19725,'chang'=>-19715,'chao'=>-19540,'che'=>-19531,'chen'=>-19525,'cheng'=>-19515,'chi'=>-19500,'chong'=>-19484,'chou'=>-19479,'chu'=>-19467,'chuai'=>-19289,'chuan'=>-19288,'chuang'=>-19281,'chui'=>-19275,'chun'=>-19270,'chuo'=>-19263,'ci'=>-19261,'cong'=>-19249,'cou'=>-19243,'cu'=>-19242,'cuan'=>-19238,'cui'=>-19235,'cun'=>-19227,'cuo'=>-19224,
        'da'=>-19218,'dai'=>-19212,'dan'=>-19038,'dang'=>-19023,'dao'=>-19018,'de'=>-19006,'deng'=>-19003,'di'=>-18996,'dian'=>-18977,'diao'=>-18961,'die'=>-18952,'ding'=>-18783,'diu'=>-18774,'dong'=>-18773,'dou'=>-18763,'du'=>-18756,'duan'=>-18741,'dui'=>-18735,'dun'=>-18731,'duo'=>-18722,
        'e'=>-18710,'en'=>-18697,'er'=>-18696,
        'fa'=>-18526,'fan'=>-18518,'fang'=>-18501,'fei'=>-18490,'fen'=>-18478,'feng'=>-18463,'fo'=>-18448,'fou'=>-18447,'fu'=>-18446,
        'ga'=>-18239,'gai'=>-18237,'gan'=>-18231,'gang'=>-18220,'gao'=>-18211,'ge'=>-18201,'gei'=>-18184,'gen'=>-18183,'geng'=>-18181,'gong'=>-18012,'gou'=>-17997,'gu'=>-17988,'gua'=>-17970,'guai'=>-17964,'guan'=>-17961,'guang'=>-17950,'gui'=>-17947,'gun'=>-17931,'guo'=>-17928,
        'ha'=>-17922,'hai'=>-17759,'han'=>-17752,'hang'=>-17733,'hao'=>-17730,'he'=>-17721,'hei'=>-17703,'hen'=>-17701,'heng'=>-17697,'hong'=>-17692,'hou'=>-17683,'hu'=>-17676,'hua'=>-17496,'huai'=>-17487,'huan'=>-17482,'huang'=>-17468,'hui'=>-17454,'hun'=>-17433,'huo'=>-17427,
        'ji'=>-17417,'jia'=>-17202,'jian'=>-17185,'jiang'=>-16983,'jiao'=>-16970,'jie'=>-16942,'jin'=>-16915,'jing'=>-16733,'jiong'=>-16708,'jiu'=>-16706,'ju'=>-16689,'juan'=>-16664,'jue'=>-16657,'jun'=>-16647,
        'ka'=>-16474,'kai'=>-16470,'kan'=>-16465,'kang'=>-16459,'kao'=>-16452,'ke'=>-16448,'ken'=>-16433,'keng'=>-16429,'kong'=>-16427,'kou'=>-16423,'ku'=>-16419,'kua'=>-16412,'kuai'=>-16407,'kuan'=>-16403,'kuang'=>-16401,'kui'=>-16393,'kun'=>-16220,'kuo'=>-16216,
        'la'=>-16212,'lai'=>-16205,'lan'=>-16202,'lang'=>-16187,'lao'=>-16180,'le'=>-16171,'lei'=>-16169,'leng'=>-16158,'li'=>-16155,'lia'=>-15959,'lian'=>-15958,'liang'=>-15944,'liao'=>-15933,'lie'=>-15920,'lin'=>-15915,'ling'=>-15903,'liu'=>-15889,'long'=>-15878,'lou'=>-15707,'lu'=>-15701,'lv'=>-15681,'luan'=>-15667,'lue'=>-15661,'lun'=>-15659,'luo'=>-15652,
        'ma'=>-15640,'mai'=>-15631,'man'=>-15625,'mang'=>-15454,'mao'=>-15448,'me'=>-15436,'mei'=>-15435,'men'=>-15419,'meng'=>-15416,'mi'=>-15408,'mian'=>-15394,'miao'=>-15385,'mie'=>-15377,'min'=>-15375,'ming'=>-15369,'miu'=>-15363,'mo'=>-15362,'mou'=>-15183,'mu'=>-15180,
        'na'=>-15165,'nai'=>-15158,'nan'=>-15153,'nang'=>-15150,'nao'=>-15149,'ne'=>-15144,'nei'=>-15143,'nen'=>-15141,'neng'=>-15140,'ni'=>-15139,'nian'=>-15128,'niang'=>-15121,'niao'=>-15119,'nie'=>-15117,'nin'=>-15110,'ning'=>-15109,'niu'=>-14941,'nong'=>-14937,'nu'=>-14933,'nv'=>-14930,'nuan'=>-14929,'nue'=>-14928,'nuo'=>-14926,
        'o'=>-14922,'ou'=>-14921,
        'pa'=>-14914,'pai'=>-14908,'pan'=>-14902,'pang'=>-14894,'pao'=>-14889,'pei'=>-14882,'pen'=>-14873,'peng'=>-14871,'pi'=>-14857,'pian'=>-14678,'piao'=>-14674,'pie'=>-14670,'pin'=>-14668,'ping'=>-14663,'po'=>-14654,'pu'=>-14645,
        'qi'=>-14630,'qia'=>-14594,'qian'=>-14429,'qiang'=>-14407,'qiao'=>-14399,'qie'=>-14384,'qin'=>-14379,'qing'=>-14368,'qiong'=>-14355,'qiu'=>-14353,'qu'=>-14345,'quan'=>-14170,'que'=>-14159,'qun'=>-14151,
        'ran'=>-14149,'rang'=>-14145,'rao'=>-14140,'re'=>-14137,'ren'=>-14135,'reng'=>-14125,'ri'=>-14123,'rong'=>-14122,'rou'=>-14112,'ru'=>-14109,'ruan'=>-14099,'rui'=>-14097,'run'=>-14094,'ruo'=>-14092,
        'sa'=>-14090,'sai'=>-14087,'san'=>-14083,'sang'=>-13917,'sao'=>-13914,'se'=>-13910,'sen'=>-13907,'seng'=>-13906,'sha'=>-13905,'shai'=>-13896,'shan'=>-13894,'shang'=>-13878,'shao'=>-13870,'she'=>-13859,'shen'=>-13847,'sheng'=>-13831,'shi'=>-13658,'shou'=>-13611,'shu'=>-13601,'shua'=>-13406,'shuai'=>-13404,'shuan'=>-13400,'shuang'=>-13398,'shui'=>-13395,'shun'=>-13391,'shuo'=>-13387,'si'=>-13383,'song'=>-13367,'sou'=>-13359,'su'=>-13356,'suan'=>-13343,'sui'=>-13340,'sun'=>-13329,'suo'=>-13326,
        'ta'=>-13318,'tai'=>-13147,'tan'=>-13138,'tang'=>-13120,'tao'=>-13107,'te'=>-13096,'teng'=>-13095,'ti'=>-13091,'tian'=>-13076,'tiao'=>-13068,'tie'=>-13063,'ting'=>-13060,'tong'=>-12888,'tou'=>-12875,'tu'=>-12871,'tuan'=>-12860,'tui'=>-12858,'tun'=>-12852,'tuo'=>-12849,
        'wa'=>-12838,'wai'=>-12831,'wan'=>-12829,'wang'=>-12812,'wei'=>-12802,'wen'=>-12607,'weng'=>-12597,'wo'=>-12594,'wu'=>-12585,
        'xi'=>-12556,'xia'=>-12359,'xian'=>-12346,'xiang'=>-12320,'xiao'=>-12300,'xie'=>-12120,'xin'=>-12099,'xing'=>-12089,'xiong'=>-12074,'xiu'=>-12067,'xu'=>-12058,'xuan'=>-12039,'xue'=>-11867,'xun'=>-11861,
        'ya'=>-11847,'yan'=>-11831,'yang'=>-11798,'yao'=>-11781,'ye'=>-11604,'yi'=>-11589,'yin'=>-11536,'ying'=>-11358,'yo'=>-11340,'yong'=>-11339,'you'=>-11324,'yu'=>-11303,'yuan'=>-11097,'yue'=>-11077,'yun'=>-11067,
        'za'=>-11055,'zai'=>-11052,'zan'=>-11045,'zang'=>-11041,'zao'=>-11038,'ze'=>-11024,'zei'=>-11020,'zen'=>-11019,'zeng'=>-11018,'zha'=>-11014,'zhai'=>-10838,'zhan'=>-10832,'zhang'=>-10815,'zhao'=>-10800,'zhe'=>-10790,'zhen'=>-10780,'zheng'=>-10764,'zhi'=>-10587,'zhong'=>-10544,'zhou'=>-10533,'zhu'=>-10519,'zhua'=>-10331,'zhuai'=>-10329,'zhuan'=>-10328,'zhuang'=>-10322,'zhui'=>-10315,'zhun'=>-10309,'zhuo'=>-10307,'zi'=>-10296,'zong'=>-10281,'zou'=>-10274,'zu'=>-10270,'zuan'=>-10262,'zui'=>-10260,'zun'=>-10256,'zuo'=>-10254
    );

    /**
     * 将中文编码成拼音
     * @param string $utf8Data utf8字符集数据
     * @param string $sRetFormat 返回格式 [head:首字母|all:全拼音]
     * @return string
     */
    public static function encode($utf8Data, $sRetFormat='head'){
        $sGBK = iconv('UTF-8', 'GBK', $utf8Data);
        $aBuf = array();
        for ($i=0, $iLoop=strlen($sGBK); $i<$iLoop; $i++) {
            $iChr = ord($sGBK{$i});
            if ($iChr>160)
                $iChr = ($iChr<<8) + ord($sGBK{++$i}) - 65536;
            if ('head' === $sRetFormat)
                $aBuf[] = substr(self::zh2py($iChr),0,1);
            else
                $aBuf[] = self::zh2py($iChr);
        }
        if ('head' === $sRetFormat)
            return implode('', $aBuf);
        else
            return implode(' ', $aBuf);
    }

    /**
     * 中文转换到拼音(每次处理一个字符)
     * @param number $iWORD 待处理字符双字节
     * @return string 拼音
     */
    private static function zh2py($iWORD) {
        if($iWORD>0 && $iWORD<160 ) {
            return chr($iWORD);
        } elseif ($iWORD<-20319||$iWORD>-10247) {
            return '';
        } else {
            foreach (self::$_aMaps as $py => $code) {
                if($code > $iWORD) break;
                $result = $py;
            }
            return $result;
        }
    }
}

/** 
 * 9. PHP calss blowfish加密ecb模式
 *  利用PKCS5Padding填充方式
 */
class crypt_of_blowfish{
    /**
    * blowfish + ecb模式 + pkcs5补码 加密
    * @param string $str 需要加密的数据
    * @return string 加密后bin2hex加密的数据
    */
    //blowfish之ecb模式加密
    public function encryptm($str){
        //pkcs5补码
        $str = $this->pkcs5_pad($str, mcrypt_get_block_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB));
        $key = 'gB9d280c6fd0C398';
        return bin2hex(mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $str, MCRYPT_MODE_ECB));
    }
    //blowfish之ecb模式解密
    public function decryptm($text){
        $key = 'gB9d280c6fd0C398';
        $text = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, hex2bin($text), MCRYPT_MODE_ECB);
        
        return $this->pkcs5_unpad($text);
    }
    
    //PKCS5Padding
    private function pkcs5_pad($text, $blocksize){
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
    private function pkcs5_unpad($str){
        $pad = ord($str[($len = strlen($str)) - 1]);
        return substr($str, 0, strlen($str) - $pad);
    }
	// hex2bin — 转换十六进制字符串为二进制字符串-》5.4.1之前版本替代函数
	private function myhex2bin($data){
		$len = strlen($data); 
		return pack("H" . $len, $data); 
	}
}

// 10. PHP 5.4版本前，hex2bin替代函数 myhex2bin
function myhex2bin($data){
    $len = strlen($data); 
    return pack("H" . $len, $data); 
}

// 11. 迭代读取数组中分级数据父id子id为带缩进字符串的方法
function get_pid($data,$pid = 0,&$arr = '',$i = -1){
    $i++;
    foreach($data as $li){
        if($li['pid'] == $pid){
            $mard = $i>0 ? ' └' : ' ';
            $arr .= '<br />'.str_repeat('&nbsp;',$i).$mard.$li['name'].'';
            get_pid($data,$li['id'],$arr,$i);
        }
    }
    return $arr;
}
// 11.2 多级子类的多维数组
function get_pidarr($pid,$data){
    $arr = array();
    foreach($data as $li){
        if($li['pid'] == $pid){
            $ar = get_pid($li['cid'],$data);
            if( !empty($ar) ){
                $li['subclass'] = $ar;
            }
            $arr[] = $li;
        }
    }
    return $arr;
}

// 12. 此函数获取目录下全部文件到一个数组
function get_filetree($path){
    $tree = array();
    foreach(glob($path.'/*') as $single){
        if(is_dir($single)){
            $tree = array_merge($tree,get_filetree($single));
        }else{
            $tree[] = $single;
        }
    }
    return $tree;
}

/* 13. 分页html代码输出
 * @Name:自定义分页字符串生成函数
 * @Param num       integer     总页数
 * @Param pages     integer     当前页数
 * @Param url       string      当前页面地址(不传参地址)
 * @Param param     array       url的get参数数组
 * @Param shownum   integer     显示分页连接个数
 * @Author: Masong
 * @eg:
 *      $param = $_GET;
 *      $page = !empty($_GET['page']) ? $_GET['page'] : 1;
 *      echo pagelist(2222, $page, './test.php?', $param);
 */
function pagelist($num, $pages, $url, $param, $shownum = 10){
    if(!empty($param['page'])) unset($param['page']);
    $urlparam = $url.http_build_query($param);
    
    $pagelist = '';
    
    //首页
    if($pages > 1)
        $pagelist .= '<a href="'.$urlparam.'&page=1">首页</a>'."\n";
    //上一页
    if($pages > 2)
        $pagelist .= '<a href="'.$urlparam.'&page='.($pages-1).'">上一页</a>'."\n";
    
    //分页列表
    if($num < $shownum)
    {
        for($i = 1; $i<=$num; $i++)
        {
            if($i == $pages)
                $pagelist .= '<span>'.$i.'</span>'."\n";
            else
                $pagelist .= '<a href="'.$urlparam.'&page='.$i.'">'.$i.'</a>'."\n";
        }
    }
    else
    {
        $starpages = ($pages - round($shownum/2)) >= 1 ? ($pages - round($shownum/2)) : 1;
        if( ($starpages + $shownum - 1) <= $num )
            $endpages  = $starpages + $shownum - 1;
        else{
            $starpages = $num - $shownum + 1;
            $endpages = $num;
        }
        
        for($i = $starpages; $i<=$endpages; $i++)
        {
            if($i == $pages)
                $pagelist .= '<span>'.$i.'</span>'."\n";
            else
                $pagelist .= '<a href="'.$urlparam.'&page='.$i.'">'.$i.'</a>'."\n";
        }
    }
    
    //下一页
    if($pages < $num-1)
        $pagelist .= '<a href="'.$urlparam.'&page='.($pages+1).'">下一页</a>'."\n";
    //末页
    if($pages < $num)
        $pagelist .= '<a href="'.$urlparam.'&page='.$num.'">末页</a>'."\n";
    //快捷翻页菜单
    if($num > $shownum+1){
        $pagelist .= '<select onchange="topagelist(this.value)">'."\n";
        for($i = 1; $i<=$num; $i++){
            $pagelist .= '<option><a href="'.$urlparam.'&page='.$i.'">'.$i.'</a></option>'."\n";
        }
        $pagelist .= '</select>'."\n".'<script>'."\n";
        $pagelist .= 'function topagelist(pages){window.location.href="'.$urlparam.'&page="+pages;}'."\n";
        $pagelist .= '</script>'."\n";
    }
    
    return $pagelist;
}


/* 14. 网络图片下载到本地
 * @Name:网络图片下载
 * @Param url       string      图片地址
 * @Param dirname   string      要保存的本地路径
 * @Author: Masong
 */
function downImages($url, $dirname) {
    $header = array("Connection: Keep-Alive", "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3", "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    if (1 == strpos("$".$url, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $content = curl_exec($ch);
    $curlinfo = curl_getinfo($ch);
    //print('<br />'.$curlinfo['url']);
    //关闭连接
    curl_close($ch);

    if ($curlinfo['http_code'] == 200) {
        if ($curlinfo['content_type'] == 'image/jpeg') {
            $exf = '.jpg';
        } else if ($curlinfo['content_type'] == 'image/png') {
            $exf = '.png';
        } else if ($curlinfo['content_type'] == 'image/gif') {
            $exf = '.gif';
        }

        //存放图片的路径及图片名称  *****这里注意 你的文件夹是否有创建文件的权限 chomd -R 777 mywenjian
        $filename = date("YmdHis") . uniqid() . $exf;//这里默认是当前文件夹，可以加路径的 可以改为$filepath = '../'.$filename

        $res = file_put_contents($dirname.$filename, $content);//同样这里就可以改为$res = file_put_contents($filepath, $content);
        return $filename;
    }else{
        return false;
    }
}

/**
 * 15. 读取目录下所有文件和目录到数组
 * 读取目录下所有文件和目录到一个数组
 * @path: 要读取的目标路径
 */
function searchDir($path,&$data=array()){
    if( is_dir($path) ){
        $dp=dir($path);
        while( $file=$dp->read() ){
            if($file!='.'&& $file!='..'){
                searchDir($path.'/'.$file,$data);
            }
        }
        $dp->close();
        
        $data[]=$path;
    }
    
    if( is_file($path) ){
        $data[]=$path;
    }
    
    return $data;
}

/*
* 16. 读取csv文件到一个数组
 * @filename: 要读取的目标文件路径
*/
function input_csv($filename) {
    $handle = fopen($filename, 'r'); 
    $out = array (); 
    $n = 0; 
    while ($data = fgetcsv($handle, 10000)) { 
        $num = count($data); 
        for ($i = 0; $i < $num; $i++) { 
            $out[$n][$i] = $data[$i]; 
        } 
        $n++; 
    } 
    fclose($handle); //关闭指针 
    return $out; 
}

