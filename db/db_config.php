<?php

	function connect_db(){
		$dbname = 'lab';
		$host = '127.0.0.1';
		$usr = 'root';
		$pwd = 'vw0ntg1veup';

		$conn = new PDO("mysql:dbname=$dbname;host=$host;charset=utf8", $usr, $pwd);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $conn;
	}

	function db_query($query, $arr){
		$conn = connect_db();

		try{
			$stmt = $conn->prepare($query);
			$stmt->execute($arr);
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
		$result['result'] = $stmt->fetchAll();
		$result['success'] = true;
		$result['num_row'] = $stmt->rowCount();
																				return $result;
	}
	
?>