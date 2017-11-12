<?php
//////////////////////////////////////////
//					//
//	SQLDataBase.php		//
//					//
//	Class to create easy to use	//
//	interface with the database	//
//					//
//	By David Slater			//
//	June 2009			//
//					//
//////////////////////////////////////////


class SQLDataBase {

////////////////Private Variables//////////

	private $link; //mysql database link
	private $host;	//hostname for the database
	private $database; //database name
	private $username; //username to connect to the database
	private $password; //password of the username

////////////////Public Functions///////////
	
	public function __construct($host,$database,$username,$password) {
		$this->open($host,$database,$username,$password);
		
				
	}
	public function __destruct() {
        	
                
        }
	
	//open()
	//$host - hostname
	//$database - name of the database
	//$username - username to connect to the database with
	//$password - password of the username
	//opens a connect to the database
	public function open($host,$database,$username,$password) {
		//Connects to database.
                $this->link = mysql_connect($host,$username,$password) or die("Unable to connect to database");
                @mysql_select_db($database,$this->link) or die("Unable to select database " . $database);
		$this->host = $host;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;

	}

	//close()
	//closes database connection
	public function close() {
		mysql_close($this->link);
	}
	
	//insert_query()
	//$sql - sql string to run on the database
	//returns the id number of the new record, 0 if it fails
	public function insertQuery($sql) {
		if (mysql_query($sql,$this->link)) {
			return mysql_insert_id($this->link);
		}
		else {
			return 0;
		}

        }
        
	//non_select_query()
	//$sql - sql string to run on the database
	//For update and delete queries
	//returns true on success, false otherwise
	public function nonSelectQuery($sql) {
		return mysql_query($sql,$this->link);
	}
        
	//query()
	//$sql - sql string to run on the database
	//Used for SELECT queries
	//returns an associative array of the select query results.
	public function query($sql) {
		$result = mysql_query($sql,$this->link);
		return $this->mysqlToArray($result);
	}

	//count_query()
	//$sql - sql string to run on the database
	//Used for SELECT queries
	//returns number of rows in result
	public function countQuery($sql) {
		$result = mysql_query($sql,$this->link);
		return mysql_num_rows($result);
	}

	//single_query()
	//$sql - sql string to run on the database
	//Used for SELECT queries
	//returns single result value in first row
	public function singleQuery($sql) {
		$result = mysql_query($sql,$this->link);
		return @mysql_result($result, 0);
	}

	//getLink
	//returns the mysql resource link
	public function getLink() {
		return $this->link;
	}

	public function fetchAssoc($sql)
	{
		$result = mysql_query($sql);
		$rows = mysql_fetch_assoc($result);
		return $rows;
	}
/////////////////Private Functions///////////////////
    
	//mysqlToArray
	//$mysqlResult - a mysql result
	//returns an associative array of the mysql result.
	private function mysqlToArray($mysqlResult) {
		$dataArray;
		$i =0;
		while($row = mysql_fetch_array($mysqlResult,MYSQL_ASSOC)){
			foreach($row as $key=>$data) {
				$dataArray[$i][$key] = $data;
			}
		$i++;
		}
		return @$dataArray;
	}	

}

?>
