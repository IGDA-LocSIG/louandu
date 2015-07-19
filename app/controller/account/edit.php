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
 * Users
 *
 * Edit a user
 * @since 
 */
class AccountEdit extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('MemberAccountModel');
	}
	
	public function mainAction() {
		
		$id = $_SESSION['appUserId'];
		
		$this->data = New MemberAccountModel();
		$this->data->connectDb();

		$this->data->setUid($id);
		
		if ($this->fc->request->chk('save')) {
			$this->data->set($_POST);
			$ignoreFields = array('username'); // we don't allow the user to modify her username
			if (!$this->fc->request->get('password')) {
				// if the user (admin or normal user) didn't type a password while editing a user
				// then it means she doesn't want change it.
				$ignoreFields = array_merge($ignoreFields, array('password','salt'));
			}
			$this->data->ignore(implode(',', $ignoreFields));
			
			if ($this->data->check()) {
				$this->data->save();
				$this->data->addHelper('auth',$this->data);
				$this->data->updateSessionVariables();
				$this->fc->redirect(APP_WWW_URI.'account'); 
			}
		} elseif ($id) {
			$this->data->load();
		}
		
		$this->data->addHelper('html_form');
				
		$this->page->set('title','GLOC '.TR::get('security','my_account'));
		
		$this->setView('edit');
		
		$this->view();	
	}
}