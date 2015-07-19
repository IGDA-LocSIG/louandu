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
 * Login
 * 
 * @since 
 */
class IdentLogin extends AppController {

	public function __construct() {
		parent::__construct(false);
	}
	
	public function mainAction() {
	
		if (!empty($_POST)) {
			$this->fc->user->fields('username,password');
			$this->fc->user->set($this->fc->request);
			// note: login uses the model defined by APP_SETUP_USER_MODEL in app/config/core.php 
			if ($this->fc->user->login($this->fc->user->get('username'), $this->fc->user->get('password'))) {
				// auto login ?
				if ($this->fc->request->chk('remember')) {
					$this->fc->user->setAutoLogin();
				}
				NaviHelper::redirect(APP_WWW_URI);
			}
		}
	
		$this->fc->user->addHelper('html_form');
		$this->page->set('title','GLOC '.TR::get('security','login'));
		$this->setView('login');
		$this->view();
	}
}