<?php

	include_once('db/db_auth.php');
	include_once('db/db_profile.php');
	include_once('db/db_thread.php');

	// print_r(addSth(['table'	=>'thread',
	// 				'usr'	=>'u4',
	// 				'thread_topic'=>'topic',
	// 				'thread_body'=>'body']));
	print_r(addSth(['table'=>'room',
					'room_name'=>'room1',
					'room_des'=>'des']));
?>