<?php

$GLOBALS['config']['email'] = array(
	'mode'			=> 'smtp', // smtp, mail, sendmail
	'smtp_server'	=> 'box869.bluehost.com',
	'smtp_port'		=> 26,
	'smtp_user'		=> 'notifications+gloc.it',
	'smtp_pass'		=> 'L0U4NDU%%',
	'sendmail'		=> '/usr/sbin/exim -bs',
	'default_name'		=> 'GLOC | booking system',
	'default_address'	=> 'notifications@gloc.it',
	'default_reply'		=> 'team@gloc.it',
	'default_bounce'	=> 'team@gloc.it',
	'bcopy_client'		=> array('notifications@gloc.it'=>'Notifications','team@gloc.it'=>'Team Gloc'),
	'bcopy_vendor'		=> 'notifications@gloc.it',
	'html_format'		=> true,
	'subject_client_new'	=> 'New tasks',
	'subject_client_upd'	=> 'Tasks updated',
	'subject_vendor_new'	=> 'New tasks assigned',
	'subject_vendor_upd'	=> 'Assigned tasks updated'
);