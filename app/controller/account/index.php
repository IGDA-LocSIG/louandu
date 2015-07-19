<?php
/**
 * GLOC
 *
 * @package taskfreak_tt 
 * @author Stan Ozier <taskfreak@gmail.com>, Hervé Renault <rv@tirzen.com>
 * @version 
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * Accounts
 *
 * Account of a user
 * @since 
 */
class AccountIndex extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('MemberAccountModel');
	}
	
	public function mainAction() {
		
		$id = $_SESSION['appUserId'];
				
		$this->plusbutton = array(
				TR::get('button','edit') 	=> APP_WWW_URI.'account/edit'
		);
		
		$this->data = New MemberAccountModel();
		$this->data->connectDb();
		
		$this->data->setUid($id);
		$this->data->load();
		
		$this->data->addHelper('html_form');
		
		$this->page->set('title','GLOC '.TR::get('security','my_account'));
		
		$this->setView('main');
		
		$this->view();	
	}
}