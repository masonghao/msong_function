<?php
/*
 数据库操作类
require('dbclass.php');
$dbc = new Dbclass('localhost', 'root', 'mc3321', 'msbooks');
$list = $dbc->getselect('ms_news');
echo '<pre>';
var_dump($list);
*/
class Dbclass {
    // 打开数据库连接
    function __construct($host, $uname, $password, $dbname){
        $con=mysqli_connect($host, $uname,$password,$dbname);
        mysqli_query($con,"set names 'utf8'");
        // Check connection
        if (mysqli_connect_errno($con)){
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }
        $this->con = $con;
    }

    // 执行sql语句
    public function do_sql($sql, $res_arr = 0){
        $result = mysqli_query($this->con, $sql);
        if($result){
            if( $res_arr ){
                $arr = array();
                if($result)
                    while($row=mysqli_fetch_array($result, MYSQLI_ASSOC))
                        $arr[] = $row;
                return $arr;
            }
            return mysqli_affected_rows($this->con);
        }else{
            return false;
        }
    }
    // 统计结果数量
    public function get_counts($sql){
        $result = mysqli_query($this->con, $sql);
        return mysqli_num_rows($result);
    }

    // 数据统计——getcount(表名,条件);
    public function getcount($tables=null,$wheres=null){
        $table  = $tables ? $tables :'';
        $where  = $wheres ? ' WHERE '.$wheres : '';
        $sql = 'SELECT * FROM '.$table.$where;
        return $this->get_counts($sql);
    }

    // 数据查询——getselect(表名,字段,条件,排序,limit值);
    public function getselect($tables=null, $wheres=null, $fields=null, $orders=null, $limits=10){
        $table  = $tables ? $tables :'';
        $field  = $fields ? $fields : '*';
        $where  = $wheres ? ' WHERE '.$wheres : '';
        $order  = $orders ? ' ORDER BY '.$orders : '';
        $limit  = $limits ? ' LIMIT '.$limits : '';
        $sql = 'SELECT '.$field.' FROM '.$table.$where.$order.$limit;
        return $this->do_sql($sql, 1);
    }

    // 插入数据
    public function addinfo($table, $infoarray){
        $k_v = $this->arrayto_k_v($infoarray);
        $sql = "INSERT INTO ".$table." (".$k_v['fileds'].") VALUES(".$k_v['values'].")";
        return $this->do_sql($sql);
    }

    // 修改数据
    public function updateinfo($table=null,$wheres=null,$fields=null){
        $sql = "UPDATE ".$table." SET ".$fields." WHERE ".$wheres;
        return $this->do_sql($sql);
    }

    // 删除信息
    public function delinfo($table=null,$where=null){
        $sql = "DELETE FROM ".$table." WHERE ".$where;
        return $this->do_sql($sql);
    }

    // 数组转化 sql 字段素材
    public function arrayto_k_v($arr){
        $res = array();
        $keystr = $valuestr = '';
        foreach ($arr as $key => $value) {
            $keystr .= $key.',';
            $valuestr .= '"'.$value.'",';
        }
        $res['fileds'] = rtrim($keystr, ',');
        $res['values'] = rtrim($valuestr, ',');
        return $res;
    }

    //关闭数据库连接
    public function __destruct(){
        mysqli_close($this->con);
    }
}
