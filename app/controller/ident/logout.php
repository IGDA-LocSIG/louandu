<?php
/**
 *  TaskFreak! Time Tracker
 * 
 * @package taskfreak_tt
 * @author Stan Ozier <taskfreak@gmail.com>, Hervé Renault <rv@tirzen.com>
 * @version 
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * Logout
 * 
 * @since 
 */
class IdentLogout extends AppController {

	public function __construct() {
		parent::__construct(false);
	}
	
	/**
	 * Logs user out
	 * @todo show logout summary page
	 */
	public function mainAction() {
		$this->fc->user->logout();
		NaviHelper::redirect(APP_WWW_URI);
	}
}