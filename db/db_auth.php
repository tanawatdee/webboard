<?php
	
	include_once('db_config.php');

	function signup($arr){
		$conn = connect_db();

		$query = "INSERT INTO person (";
		foreach ($arr as $key => $value){
			if(in_array($key, ['usr', 'pwd', 'permission']))
				continue;
			$query .= "$key, ";
		}
		$query = substr($query, 0, -2).") VALUES (";
		foreach ($arr as $key => $value){
			if(in_array($key, ['usr', 'pwd', 'permission']))
				continue;
			$query .= ":$key, ";
		}
		$query = substr($query, 0, -2).");";
		try{
			$conn->beginTransaction();
			$stmt = $conn->prepare($query);
			$stmt->execute(array_diff_key($arr, ['usr'=>'', 'pwd'=>'', 'permission'=>'']));
			$stmt = $conn->prepare("INSERT INTO account VALUES (:usr, :pwd_hash, :permission, CURRENT_TIMESTAMP, DEFAULT, LAST_INSERT_ID());");
			$stmt->execute(['usr'		=>$arr['usr'],
							'pwd_hash'	=>password_hash($arr['pwd'], PASSWORD_BCRYPT),
							'permission'=>$arr['permission']]);
		}
		catch (PDOException $e){
			$conn->rollBack();
																				return ['success'	=>	false,
																						'code'		=>	$e->getCode(),
																						'msg'		=>	$e->getMessage()];
		}
		$conn->commit();
																				return login(['usr'=>$arr['usr'], 'pwd'=>$arr['pwd']]);
	}

	function login($arr){
		$conn = connect_db();
		
		$stmt = $conn->prepare("SELECT last_login, permission, pwd_hash FROM account WHERE usr = :usr;");
		try{
			$stmt->execute(['usr'=>	$arr['usr']]);
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
		
		if(!$stmt->rowCount())
																				return ['success'	=>false,
																						'code'		=>0,
																						'msg'		=>'User not found.'];

		foreach($stmt as $row){
			if(password_verify($arr['pwd'], $row['pwd_hash'])){
				$stmt = $conn->prepare("UPDATE account SET last_login = CURRENT_TIMESTAMP WHERE usr = :usr;");
				try{
					$stmt->execute(['usr'=>	$arr['usr']]);
				}
				catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
				}
																				return ['success'	=>true,
																						'last_login'=>$row['last_login'],
																						'permission'=>$row['permission']];
			}
			else
																				return ['success'	=>false,
																						'code'		=>1,
																						'msg'		=>'Incorrect password.'];
		}
	}

	function delAccount($arr){
		$conn = connect_db();
		
		$stmt = $conn->prepare("SELECT id FROM account WHERE usr = :usr;");
		try{
			$stmt->execute(['usr'=>	$arr['usr']]);
			foreach($stmt as $row){
				$num_row += $conn->exec("DELETE FROM person WHERE id = ".$row['id']);
			}
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
																				return ['success'	=>true,
																						'num_row'	=>$num_row];
	}

	function changePwd($arr){
		$result = login(['usr'=>$arr['usr'], 'pwd'=>$arr['pwd']]);
		if(!$result['success']){
																				return $result;
		}

		$conn = connect_db();

		$stmt = $conn->prepare("UPDATE account SET pwd_hash = :newPwd_hash WHERE usr = :usr");
		try{
			$stmt->execute(['usr'			=>$arr['usr'],
							'newPwd_hash'	=>password_hash($arr['newPwd'], PASSWORD_BCRYPT)]);
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
																				return ['success'	=>true,
																						'num_row'	=>$stmt->rowCount()];

	}

	function resetPwd($arr){
		$conn = connect_db();

		try{
			$stmt = $conn->prepare("SELECT email FROM person WHERE id IN (SELECT id FROM account WHERE usr = :usr);");
			$stmt->execute($arr);
			if(!$stmt->rowCount())
																				return ['success'	=>false,
																						'code'		=>0,
																						'msg'		=>'User not found.'];
			foreach($stmt as $row){
				$tempPwd = uniqid($arr['user']);
				$stmt = $conn->prepare("UPDATE account SET pwd_hash = :newPwd_hash WHERE usr = :usr");
				$stmt->execute(['usr'			=>$arr['usr'],
								'newPwd_hash'	=>password_hash($tempPwd, PASSWORD_BCRYPT)]);
																				return ['success'	=>true,
																						'email'		=>$row['email'],
																						'tempPwd'	=>$tempPwd,
																						'num_row'	=>$stmt->rowCount()];
			}
		}
		catch (PDOException $e){
																				return ['success'	=>false,
																						'code'		=>$e->getCode(),
																						'msg'		=>$e->getMessage()];
		}
	}
	
?>
