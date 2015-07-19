<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$msg = array(
		"APP_DB_HOST" => $_POST["APP_DB_HOST"],
		"APP_DB_USER" => $_POST["APP_DB_USER"],
		"APP_DB_PASS" => $_POST["APP_DB_PASS"],
		"APP_DB_BASE" => $_POST["APP_DB_BASE"],
		"APP_DB_PREFIX" => $_POST["APP_DB_PREFIX"]
	);
	$conn = new mysqli($msg["APP_DB_HOST"], $msg["APP_DB_USER"], $msg["APP_DB_PASS"], $msg["APP_DB_BASE"]);
	
	if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
	}
	$conf_temp = file_get_contents("app/config/db.php.template");
	foreach ($msg as $key => $value)
		$conf_temp = str_replace("{{".$key."}}",$value,$conf_temp);
	file_put_contents("app/config/db.php", $conf_temp);
	// Temporary variable, used to store current query
	$templine = '';
	$lines = file("DOCS/gloc.sql");
	// Loop through each line
	foreach ($lines as $line)
	{
		// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '')
		continue;
		// Add this line to the current segment
		$templine .= $line;
		// If it has a semicolon at the end, it's the end of the query
		if (substr(trim($line), -1, 1) == ';')
		{
		// Perform the query
		$conn->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
		// Reset temp variable to empty
		$templine = '';
		}
	}
	
	echo "Tables imported successfully";

	$query = "INSERT INTO `member` (`id`, `company_id`, `class`, `nickname`, `email`, `username`, `password`, `salt`, `auto_login`, `address`, `time_zone`, `language`, `date_format_us`, `creation_date`, `expiration_date`, `last_login_date`, `last_login_address`, `last_change_date`, `visits`, `bad_access`, `activation`, `enabled`, `rate_translate`, `rate_review`, `rate_hourly`, `payterms`, `hidden`) VALUES 	(1,1,'manager','{{nickname}}','{{email}}','{{username}}','{{pass}}','{{salt}}',0,'','','en',0,'2010-06-11 01:30:00','0000-00-00','2015-07-07 00:06:22','125.14.86.80','2014-06-20 05:52:01',2387,0,'',1,1,1,1,60,0);";
	$string = $_POST["password"];
	$email = $_POST["email"];
	$username = $_POST["username"];
	$nickname = $_POST["nickname"];
	$salt = uniqid(mt_rand(),true);
	$pass = crypt($string, $salt);
	$msg = array(
		"pass" => $pass,
		"email" => $email,
		"username" => $username,
		"nickname" => $nickname,
		"salt" => $salt
	);
	foreach ($msg as $key => $value)
		$query = str_replace("{{".$key."}}",$value,$query);
	//echo "javascript:alert('".$query."');";
	$conn->query($query);

	$q2 = "INSERT INTO `acl_user` (`user_id`, `acl_id`) VALUES (1,2);"; //admin
	$conn->query($q2);

	$q2 = "INSERT INTO `acl_user` (`user_id`, `acl_id`) VALUES (1,3);"; //memo creator
	$conn->query($q2);

	$q2 = "INSERT INTO `acl_user` (`user_id`, `acl_id`) VALUES (1,4);"; //project creator
	$conn->query($q2);
	
	$q2 = "INSERT INTO `acl_user` (`user_id`, `acl_id`) VALUES (1,8);"; //user creator
	$conn->query($q2);

	header("Location: /louandu/");
	die();

} else { ?>

<!DOCTYPE html>
<html>
	<head>
		<title>Louandu Setup</title>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
	</head>
	<body class="container">
		<div class="jumbotron">
			<h1>Louandu <small>Database Setup</small></h1>
		</div>
		<div>Conf writable: <?php echo is_writable("app/config")?"true":"false"; ?></div>
		<form method="POST" class="form-horizontal">
			<div class="form-group">
				<label for="APP_DB_HOST" class="col-sm-2 control-label">Host:</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" name="APP_DB_HOST" value="localhost">
				</div>
			</div>
			<div class="form-group">
				<label for="APP_DB_USER" class="col-sm-2 control-label">Username:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="APP_DB_USER">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Password:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="APP_DB_PASS">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">DB Name:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="APP_DB_BASE">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">table prefix:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="APP_DB_PREFIX">
				</div>
			</div>
			<hr/>
			<div class="form-group">
				<label class="col-sm-2 control-label">Admin Nickname:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="nickname" value="admin">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Admin Username:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="username" value="admin">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Admin Email:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="email">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">Admin password:</label>
				<div class="col-sm-10">
					<input class="form-control"type="text" name="password">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-default" <?php echo is_writable("app/config")?"":"disabled"?>>Install</button>
				</div>
			</div>
		</form>
	</body>
</html>

<?php } ?>