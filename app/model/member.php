<?php
/**
 * TaskFreak! Time Tracker
 * 
 * @package taskfreak_tt
 * @author Stan Ozier <taskfreak@gmail.com>
 * @version 0.4
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * MemberModel for loggin in (see APP_SETUP_USER_MODEL in config/core.php)
 * @since 0.2
 */
class MemberModel extends UserAclModel {

	public function __construct() {
		parent::__construct('member');
		$this->addProperties(array(
			'nickname'		=> 'STR',
			'email'			=> 'EML'
		));
	}
	
	public function check($fields="") {
		return parent::check('nickname,username');
	}
	
	public function getLabel() {
		return $this->value('nickname');
	}
	
}
