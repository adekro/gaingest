<?php

/**
 * 
 * Gain Studios - Login alla procedura
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20141002 file creato
 * 20150807 jQuery
 *
 */

session_start();
if(!empty($_SESSION['utente'])) {
	header('Location: home.php');
}
?>

<!doctype html>
<html>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
		<meta http-equiv='CACHE-CONTROL' content='NO-CACHE'>
		<meta http-equiv='PRAGMA' content='NO-CACHE'>
		<meta name='robots' content='noindex'/>
		<title>Gain Studios</title>
		<link rel="stylesheet" href="static/login.css"/>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="static/jquery.ui.shake.js"></script>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'/>	
	</head>
	<body>
	<div id="main">
		<div id="logo"><img src='static/login.jpg' title='Gain Studios' alt='Gain Studios' align='center'/></div>
		<div id="box">
			<form action="" method="post">
				<label>Utente:</label> <input type="text" name="utente" class="input" id="utente"/>
				<label>Password:</label> <input type="password" name="password" class="input" id="password"/><br/>
				<input type="submit" class="button button-primary" value="Accesso" id="login"/> 
				<span class='msg'></span> 
				<div id="error"></div>	
			</form>	
		</div>
		<div id="forgotpassword"><a href='forgotpassword.php'>Ho dimenticato la mia password</a></div>
	</div>

	<script>
		$(document).ready(function() {
			$('#login').click(function() {
				var username=$("#utente").val();
				var password=$("#password").val();
				var dataString = 'utente='+username+'&password='+password;
				if($.trim(utente).length>0 && $.trim(password).length>0) {
					$.ajax({
						type: "POST",
						url: "index.server.php",
						data: dataString,
						cache: false,
						beforeSend: function(){ $("#login").val('Attendere...');},
						success: function(data) {
							if(data) {
								$("body").load("home.php").hide().fadeIn(1500).delay(6000);
	            } else {
	            	$('#box').shake();
	            	$("#login").val('Accesso');
	            	$("#error").html("<span style='color:#cc0000'>Errore:</span> Utente o password errati");
	            }
	          }
	        });
				}
				return false;
			});
		});
	</script>
	</body>
</html>
