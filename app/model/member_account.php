<?php
/**
 * TaskFreak! Time Tracker
 * 
 * @package 
 * @author Stan Ozier <taskfreak@gmail.com>, Hervé Renault <rv@tirzen.com>
 * @version 
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * MemberAccountModel
 * @since 
 */
class MemberAccountModel extends UserModel {

	public function __construct() {
		parent::__construct('member');
		$this->addProperties(array(
			'nickname'	=> 'STR',
			'address'	=> 'STR',
		));
		$this->removeProperties(array(
				'enabled',
				'auto_login',
				'date_format_us',
				'creation_date',
				'expiration_date',
				'last_login_date',
				'last_login_address',
				'visits',
				'bad_access',
				'activation'));
	}
		
	public function check() {
		$password = $this->get('password');
		// check AND encode password before saving to the db
		if ($password && !$this->setPassword($password)) {
			return false;
		}
		return true;
	}
	
}
