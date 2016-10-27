<?php
/**
* 数据库操作
* @author moufer<moufer@163.com>
* @copyright (C)2001-2007 Moufersoft
* @version $Id: mysql.class.php 5 2007-12-04 09:17 $
*/

class Database 
{
	public $queries = 0;
	public $link;

	function Database($dbhost, $dbuser, $dbpw, $dbname, $pconnect = 0) {
		$this->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	}

	/**
	 * 连接数据库
	 *
	 * @param string $dbhost 数据库服务器地址
	 * @param string $dbuser 数据库用户名
	 * @param string $dbpw 数据库密码
	 * @param string $dbname 数据库名
	 * @param integer $pconnect 是否持久链接 [0=否] [1=是]
	 */
	function connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect = 0) {
        global $dbcharset;
        $func = empty($pconnect) ? "mysql_connect" : "mysql_pconnect";
        if(!$this->link = @$func($dbhost, $dbuser, $dbpw, 1)) {
        	$this->halt("Can not connect to MySQL server");
        }
        if($this->server_info() > '4.1' && $dbcharset)
			mysql_query("SET NAMES '" . $dbcharset . "'", $this->link);
		if($this->server_info() > '5.0')
			mysql_query("SET sql_mode=''", $this->link);
		if($dbname) {
			if (!@mysql_select_db($dbname, $this->link)) $this->halt('Cannot use database '.$dbname);
		}
	}

	/**
	 * 选择一个数据库
	 *
	 * @param string $dbname 数据库名
	 */
	function select_db($dbname) {
		$this->dbname = $dbname;
		if (!@mysql_select_db($dbname, $this->link)) 
			$this->halt('Cannot use database '.$dbname);
	}

	/**
	 * 查询数据库版本信息
	 *
	 * @return string
	 */
	function server_info() {
		return mysql_get_server_info();
	}
	
	function version() {
		return mysql_get_server_info();
	}

	/**
	 * 发送一条 MySQL 查询
	 *
	 * @param string $SQL SQL语法 
	 * @param string $method 查询方式 [空=自动获取并缓存结果集] [unbuffer=并不获取和缓存结果的行]
	 * @return resource 资源标识符
	 */
	function query($SQL, $method = '') {
        if($method == 'unbuffer' && function_exists('mysql_unbuffered_query')) 
			$query = mysql_unbuffered_query($SQL, $this->link);
		else
			$query = mysql_query($SQL, $this->link);
		if (!$query && $method != 'SILENT') 
            $this->halt('MySQL Query Error: ' . $SQL);
        $this->queries++;
        //echo $SQL.'<br />';
		return $query;
	}
	
	/**
	 * 发送一条用于更新，删除的 MySQL 查询
	 *
	 * @param string $SQL
	 * @return resource
	 */
	function update($SQL) {
		return $this->query($SQL, 'unbuffer');
	}
	
	/**
	 * 发送一条SQL查询，并要求返回一个字段值
	 *
	 * @param string $SQL
	 * @param int $result_type
	 * @return string
	 */
    function get_value($SQL, $result_type = MYSQL_NUM) {
        $query = $this->query($SQL,'unbuffer');
        $rs = mysql_fetch_array($query, MYSQL_NUM);
        return $rs[0];
    }

    /**
     * 发送一条SQL查询，并返回一组数据集
     *
     * @param string $SQL
     * @return array
     */
	function get_one($SQL) {
		$query = $this->query($SQL,'unbuffer');
		$rs = mysql_fetch_array($query, MYSQL_ASSOC);
		return $rs;
	}
	
	/**
	 * 发送一条SQL查询，并返回全部数据集
	 *
	 * @param string $SQL
	 * @param int $result_type
	 * @return array
	 */
    function get_all($SQL, $result_type = MYSQL_ASSOC) {
		
        $query = $this->query($SQL);
        while($row = mysql_fetch_array($query, $result_type)) $result[] = $row;
        return $result;
    }

    /**
     * 从结果集中取得一行作为关联数组，或数字数组，或二者兼有
     *
     * @param resource $query
     * @param int $result_type
     * @return array
     */
    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return mysql_fetch_array($query, $result_type);
    }

    /**
     * 返回上一次执行SQL后，被影响修改的条(行)数
     *
     * @return int
     */
	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	/**
	 * 从结果集中取得一行作为枚举数组
	 *
	 * @param resource $query
	 * @return array
	 */
	function fetch_row($query) {
		return mysql_fetch_row($query);
	}

	/**
	 * 取得结果集中行的数目
	 *
	 * @param resource $query
	 * @return int
	 */
	function num_rows($query) {
		return mysql_num_rows($query);
	}

	/**
	 * 取得结果集中字段的数目
	 *
	 * @param resource $query
	 * @return int
	 */
	function num_fields($query) {
		return mysql_num_fields($query);
	}

	/**
	 * 取得结果数据
	 *
	 * @param resource $query
	 * @param int $row 字段的偏移量或者字段名
	 * @return mixed
	 */
	function result($query, $row) {
		$query = mysql_result($query, $row);
		return $query;
	}

	/**
	 *  释放结果内存
	 *
	 * @param resource $query
	 * @return bool 
	 */
	function free_result($query) {
		return mysql_free_result($query);
	}

	/**
	 * 取得上一步 INSERT 操作产生的 ID 
	 *
	 * @return int
	 */
	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	/**
	 * 关闭 MySQL 连
	 *
	 * @return bool
	 */
	function close() {
		return mysql_close($this->link);
	}

	/**
	 * 返回上一个 MySQL 操作产生的文本错误信息
	 *
	 * @return string
	 */
    function error() {
        return (($this->link) ? mysql_error($this->link) : mysql_error());
    }

    /**
     * 返回上一个 MySQL 操作中的错误信息的数字编码 
     *
     * @return integer
     */
    function errno() {
        return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
    }

    /**
     * 查询并返回数据集（只支持单一数据表）
     * 
     * @param string $fields 多字段","分隔
     * @param string $table 数据表名
     * @param array $where 查询条件
     * @return resource
     */
    function select_query($fields, $table, $where) {
        if(!$fields) return;
        if(!$table) return;
        $where = $where ? "WHERE $where" : '';
        return $this->query("SELECT $fields FROM $table $where");
    }

    function select_one($fields, $table, $where) {
        if(!$fields) return;
        if(!$table) return;
        $where = $where ? "WHERE $where" : '';
        return $this->get_one("SELECT $fields FROM $table $where");
    }

    function select_all($fields, $table, $where) {
        if(!$fields) return;
        if(!$table) return;
        $where = $where ? "WHERE $where" : '';
        return $this->get_all("SELECT $fields FROM $table $where");
    }

    function select_value($field, $table, $where) {
        if(!$field) return;
        if(!$table) return;
        $where = $where ? "WHERE $where" : '';
        return $this->get_value("SELECT $field FROM $table $where");
    }

    // 查询返回数量
    function select_count($table, $where) {
        return $this->select_value("COUNT(*)", $table, $where);
    }

    // 删除某条记录
    function delete_new($table, $where) {
        if(!$table) return;
        $where = $where ? "WHERE $where" : '';
        return $this->query("DELETE FROM $table $where");        
    }

    /**
     * 插入/加入表数据
     * 
     * @param string $table 数据表名
     * @param array $uplist 数据数组,数组名对应字段名
     * @return resource
     */
     function insert_new($table, $inlist) {
        if(!$table) return;
        if(!is_array($inlist) || count($inlist) == 0) return;
        foreach($inlist as $key => $val) {
            $set[] = "$key='$val'";
        }
        $SQL = "INSERT $table SET ".implode(", ", $set)." $where";
        return $this->query($SQL);
     }

    /**
     * 更新表数据
     * 
     * @param string $table 数据表名
     * @param string $where 更新条件
     * @param array $uplist 更新的数据数组,数组名对应字段名
     * @return resource
     */
    function update_new($table,$where,$uplist,$replace=0) {
        if(!$table) return;
        if(!is_array($uplist) || count($uplist) == 0) return;
        $where = $where ? "WHERE $where" : '';
        foreach($uplist as $key => $val) {
            $set[] = "$key='$val'";
        }
        if($replace) {
            $SQL = "REPLACE INTO %s SET %s";
        } else {
            $SQL = "UPDATE %s SET %s";
        }
        $SQL = sprintf($SQL, $table, implode(", ", $set)." $where");
        return $this->query($SQL);
    }

	function halt($msg = '') {
        global $charset;
		$message = "<html>\n<head>\n";
		$message .= "<meta content=\"text/html; charset=$charset\" http-equiv=\"Content-Type\">\n";
		$message .= "<style type=\"text/css\">\n";
		$message .=  "body,p,pre {\n";
		$message .=  "font:12px Verdana;\n";
		$message .=  "}\n";
		$message .=  "</style>\n";
		$message .= "</head>\n";
		$message .= "<body bgcolor=\"#FFFFFF\" text=\"#000000\" link=\"#006699\" vlink=\"#5493B4\">\n";

		$message .= "<p>Dabase Failure:</p><pre><b>".htmlspecialchars($msg)."</b></pre>\n";
		$message .= "<b>Mysql error description</b>: ".htmlspecialchars($this->error())."\n<br />";
		$message .= "<b>Mysql error number</b>: ".$this->errno()."\n<br />";
		$message .= "<b>Date</b>: ".date("d M Y @ H:i")."\n<br />";
		$message .= "<b>Script</b>: http://".$_SERVER['HTTP_HOST'].getenv("REQUEST_URI")."\n<br />";

		$message .= "</body>\n</html>";
		echo $message;
		exit;
	}
}
?>