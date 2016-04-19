<?php

	include_once('db_config.php');

	function setPersonInfo($arr){
		$conn = connect_db();

		$query = "UPDATE person SET ";
		foreach ($arr as $key => $value) {
			if($key == 'usr')
				continue;
			$query .= "$key = :$key, ";
		}
		$query = substr($query, 0, -2)." WHERE id IN (SELECT id FROM account WHERE usr = :usr);";
		try{
			$stmt = $conn->prepare($query);
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

	function getPersonInfo($arr){
		$conn = connect_db();

		$query = "SELECT ";
		foreach ($arr as $key => $value) {
			if($key == 'usr')
				continue;
			$query .= "$key, ";
		}
		$query = substr($query, 0, -2)." FROM person WHERE id IN (SELECT id FROM account WHERE usr = :usr);";
		try{
			$stmt = $conn->prepare($query);
			$stmt->execute(['usr'=>$arr['usr']]);
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
		foreach ($stmt as $row){
			$row['success'] = true;
			return $row;
		}
	}

?>