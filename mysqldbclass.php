<?php
/*
 数据库操作类
*/
class Dbclass {
    //打开数据库连接
    function __construct($host, $uname, $password, $dbname){
        $this->db_host = $host;
        $this->db_user = $uname;
        $this->db_pswd = $password;
        $this->db_name = $dbname;
        $this->con = $this->getconn();
    }
    //连接数据库服务器
    public function getconn(){
        //连接数据库
        $con=mysqli_connect($this->db_host, $this->db_user,$this->db_pswd,$this->db_name);
        mysqli_query($con,"set names 'utf8'");
        // Check connection
        if (mysqli_connect_errno($con)){
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }
        return $con;
    }
    //数据统计——getcount(表名,条件);
    public function getcount($tables=null,$wheres=null){
        $table  = $tables ? $tables :'';
        $where  = $wheres ? ' WHERE '.$wheres : '';

        $sql = 'SELECT * FROM '.$table.$where;
        $result = mysqli_query($this->con,$sql);
        $sum = mysqli_num_rows($result);
        return $sum;
    }
    //数据查询——getselect(表名,字段,条件,排序,limit值);
    public function getselect($tables=null,$wheres=null,$fields=null,$orders=null,$limits=10){
        $table  = $tables ? $tables :'';
        $field  = $fields ? $fields : '*';
        $where  = $wheres ? ' WHERE '.$wheres : '';
        $order  = $orders ? ' ORDER BY '.$orders : '';
        $limit  = $limits ? ' LIMIT '.$limits : '';
        $arr = '';
        $sql = 'SELECT '.$field.' FROM '.$table.$where.$order.$limit;
        $result = mysqli_query($this->con,$sql);
        if($result){
            while($row=mysqli_fetch_array($result,MYSQLI_ASSOC)){
                $arr[] = $row;
            }
        }else{
            $arr = null;
        }

        return $arr;
    }
    //插入数据
    public function addinfo($table=null,$fields=null,$values=null){
        $sql = "INSERT INTO ".$table." (".$fields.") VALUES(".$values.")";
        $result = mysqli_query($this->con,$sql);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    //修改数据
    public function upinfo($table=null,$wheres=null,$fields=null){
        $sql = "UPDATE ".$table." SET ".$fields." WHERE ".$wheres;
        $result = mysqli_query($this->con,$sql);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    //删除信息
    public function delinfo($table=null,$where=null){
        $sql = "DELETE FROM ".$table." WHERE ".$where;
        $result = mysqli_query($this->con,$sql);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    //关闭数据库连接
    public function __destruct(){
        mysqli_close($this->con);
    }
}
