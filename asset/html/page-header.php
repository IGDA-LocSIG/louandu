<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title><?php echo $this->html('title'); ?></title>
<?php
if (!$this->isEmpty('description')) { 
?>
<meta name="description" content="<?php echo $this->html('description'); ?>" />
<?php
}

if (!$this->isEmpty('keywords')) { 
?>
<meta name="keywords" content="<?php echo $this->html('keywords'); ?>" />
<?php
}

if (file_exists(APP_WWW_PATH.'favicon.ico')) {
	echo '<link rel="SHORTCUT ICON" href="'.APP_WWW_URI.'favicon.ico" />'."\n";
}

$this->callHelper('html_asset','headerStuff'); 
?>
</head>
<body>
