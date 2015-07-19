<?php
error_reporting(E_ALL | E_STRICT);

if(!file_exists("./app/config/db.php")){

	require "setup.php";
}else{

	// load config file
	require './app/config/core.php';	// load up core config (paths, etc..)
	require APP_CONFIG_PATH.'app.php';	// load up application specific settings (customizable)

	// minimum needed classes
	require APP_CLASS_PATH.'helpable.php';
	require APP_HELPER_PATH.'string.php';
	require APP_CLASS_PATH.'front.php';

	// start session
	session_start();

	// initialize front controller
	$fc = FrontController::getInstance();

	// launch up (app) controller
	$fc->run();

}