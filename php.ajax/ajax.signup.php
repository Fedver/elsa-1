<?php

    session_start();

	include("../classi/mysqli.connect.php");
	include("../classi/account.class.php");

	$account = new Account($mysqli);

	if ($_REQUEST['email'] && $_REQUEST['pass']){
		
		$account->newUser($_REQUEST['email'], $_REQUEST['pass']);

		echo $account->message;

	}

?>