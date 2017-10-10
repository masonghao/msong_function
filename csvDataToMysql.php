<?php
/*
 * 脚本作用：读取目录下 csv 文件，保存到指定数据库
 * 数据库配置：请到 82 行 配置 linkdb 函数默认的数据库连接参数。
 * 注意：
 * 1. 请保证 csv 文件以 utf8 格式编码。
 * 2. 数据表列名为第一行的内容。
 * 3. 数据列值自动对齐第一行，多余列会被忽略。
 * 4. 数据表名为文件名 不带“.csv”，请存为英文名。
 * 5. 数据表主键为第一列的列名。
*/
$csv_list = glob("data_csv/*.csv"); //定义选择的目录

foreach($csv_list as $ks=>$csvs){
	$f1 = file($csvs);
	foreach($f1 as $k=>$li){
		$f1[$k]=explode(',', $li);
	}
	
	// 创建数据表
	$sql = 'CREATE TABLE `'.str_replace(".csv","",basename($csvs)).'` ('."\n";
	$names = array();
	foreach($f1[0] as $name){
		if(in_array(trim($name),$names)){
			continue;
		}
		
		$sql .= ' `'.trim($name).'` varchar(200) NULL,'."\n";
		
		$names[] = trim($name);
	}
	$sql .= ' PRIMARY KEY (`'.trim($f1[0][0]).'`)'."\n";
	$sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;'."\n";
	
	//执行数据表创建
	if(do_query($sql)){
		echo '<br /><span style="color:blue;">'.str_replace(".csv","",basename($csvs)).' 数据表创建成功！</span>';
	}else{
		echo '<br /><span style="color:red;">'.str_replace(".csv","",basename($csvs)).' 数据表创建失败！</span>';
		continue;
		
	}
	
	//存进数据表
	$sql = 'INSERT INTO `'.str_replace(".csv","",basename($csvs)).'` VALUES '."\n";
	$num = count($f1[0]);
	foreach($f1 as $k=>$rows){
		if($k == 0){ continue; }
		if($k%2000 == 0){
			$sql = rtrim(trim($sql), ',').';'."\n"; // sql 语句结束一行，以便创建新行
			$sql .= 'INSERT INTO `'.str_replace(".csv","",basename($csvs)).'` VALUES '."\n";
		}
		
		$sql .= '(';
		for($i=0;$i<$num; $i++){
			$name = $rows[$i];
			$sql .= '"'.trim($name).'",';
		}
		
		$sql = rtrim($sql, ',').'),'."\n";
	}
	$sql = rtrim(trim($sql), ',').';'."\n";
	//执行数据写入表
	if(do_query($sql)){
		echo '<br /><span style="color:blue;">'.str_replace(".csv","",basename($csvs)).' 数据表数据写入成功！</span>';
	}else{
		echo '<br /><span style="color:red;">'.str_replace(".csv","",basename($csvs)).' 数据表数据写入失败！</span>';
	}
}

//写入数据库
function do_query($sql,$db=null){
	$db = empty($db) ? linkdb() : $db ;
	$list = $db->query($sql);
	$db->close();
	
	return $list;
}

//连接数据库并选中默认的数据库
function linkdb($host = 'localhost', $uname = 'root', $password = '', $dbname = 'linshi2')
{
	$db_mysqli =new mysqli( $host, $uname, $password, $dbname);	//连接数据库
	if(mysqli_connect_errno())
	{
		die('Connect failed: '.mysqli_connect_error().'\n');
	}
	$db_mysqli->set_charset("utf8");	//设置编码
	
	return $db_mysqli;
}
