<?php

	include_once('db_config.php');

	function addSth($arr){
		if(!in_array($arr['table'], ['room', 'tag', 'thread', 'thread_tag', 'reply']))
																				return ['success'	=>false,
																						'code'		=>2,
																						'msg'		=>'Table not available.'];
		$conn = connect_db();

		$query = "INSERT INTO ".$arr['table']." (";
		foreach ($arr as $key => $value){
			if($key == 'table')
				continue;
			$query .= "$key, ";
		}
		$query = substr($query, 0, -2).") VALUES (";
		foreach ($arr as $key => $value){
			if($key == 'table')
				continue;
			$query .= ":$key, ";
		}
		$query = substr($query, 0, -2).");";
		try{
			$stmt = $conn->prepare($query);
			$stmt->execute(array_diff_key($arr, ['table'=>'']));
			$stmt = $conn->prepare("SELECT LAST_INSERT_ID() FROM ".$arr['table']." ;");
			$stmt->execute();
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
		foreach ($stmt as $row) {
																				return ['success'	=>true,
																						'id'		=>$row[0]];
		}
	}

	function setSth($arr){
		if(!in_array($arr['table'], ['room', 'tag', 'thread', 'reply']))
																				return ['success'	=>false,
																						'code'		=>2,
																						'msg'		=>'Table not available.'];
		$conn = connect_db();

		$tableId = $arr['table']."_id";

		$query = "UPDATE ".$arr['table']." SET ";
		foreach ($arr as $key => $value) {
			if(in_array($key, ['table', $tableId]))
				continue;
			$query .= "$key = :$key, ";
		}
		$query = substr($query, 0, -2)." WHERE ".$tableId." = :".$tableId." ;";

		try{
			$stmt = $conn->prepare($query);
			$stmt->execute(array_diff_key($arr, ['table'=>'']));
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
																				return ['success'	=>true,
																						'num_row'	=>$stmt->rowCount()];
	}

	function getSth($arr){
		if(!in_array($arr['table'], ['room', 'tag', 'thread', 'reply']))
																				return ['success'	=>false,
																						'code'		=>2,
																						'msg'		=>'Table not available.'];
		$conn = connect_db();

		$tableId = $arr['table']."_id";

		$query = "SELECT ";
		foreach ($arr as $key => $value) {
			if(in_array($key, ['table', $tableId]))
				continue;
			$query .= "$key, ";
		}
		$query = substr($query, 0, -2)." FROM ".$arr['table']." WHERE ".$tableId." = :".$tableId." ;";
		try{
			$stmt = $conn->prepare($query);
			$stmt->execute([$tableId=>$arr[$tableId]]);
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}

		if(!$stmt->rowCount())
																				return ['success'	=>false,
																						'code'		=>0,
																						'msg'		=>$tableId.' not found.'];

		foreach ($stmt as $row) {
			$row['success'] = true;
			$row['num_row'] = $stmt->rowCount();
																				return $row;
		}
	}

	function delSth($arr){
		if(!in_array($arr['table'], ['room', 'tag', 'thread', 'reply']))
																				return ['success'	=>false,
																						'code'		=>2,
																						'msg'		=>'Table not available.'];
		$conn = connect_db();

		$query = "DELETE FROM ".$arr['table']." WHERE ";
		foreach ($arr as $key => $value) {
			if($key == 'table')
				continue;
			$query .= "$key = :$key AND ";
		}
		$query = substr($query, 0, -4).";";
		
		try{
			$stmt = $conn->prepare($query);
			$stmt->execute(array_diff_key($arr, ['table'=>'']));
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
																				return ['success'	=>true,
																						'num_row'	=>$stmt->rowCount()];
	}

	function delthread_tag($arr){
		$conn = connect_db();

		$stmt = $conn->prepare("DELETE FROM thread_tag WHERE thread_id = :thread_id AND tag_id = tag_id ;");
		
		try{
			$stmt->execute($arr);
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
																				return ['success'	=>true,
																						'num_row'	=>$stmt->rowCount()];
	}

?>