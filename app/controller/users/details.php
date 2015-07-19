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
 * Details of a user
 * @since 
 */
class UsersDetails extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('MemberCompanyModel');
	}
	
	public function mainAction() {
		
		$id = $this->fc->request->get('id','INT');
		
		if (!$this->fc->user->checkAcl('admin_user,view_user') && $id != $_SESSION['appUserId']) {
			$this->fc->redirect(APP_WWW_URI, 'ERROR:access_denied');
		}
		
		if ($this->fc->user->checkAcl('admin_user')) {
			$this->breadcrumb = array(
				APP_WWW_URI.'users/index/main/filter' 
				=> 
				$GLOBALS['config']['users']['class']['options'] + array('hidden' => '(deleted)', 'all' => 'all')
			);
			$this->plusbutton = array(
				TR::get('button','edit') 	=> APP_WWW_URI.'users/edit/'.$id,
			);
		} else if ($this->fc->user->checkAcl('view_user')) {
			$this->breadcrumb = array(
				APP_WWW_URI.'users/index/main/filter' 
				=> 
				$GLOBALS['config']['users']['class']['options'] + array('all' => 'all')
			);
			
		}
		
		$this->data = New MemberCompanyModel();
		$this->data->connectDb();
		
		$this->data->setUid($id);
		$this->data->load();
		
		$this->data->addHelper('html_form');
		
		$this->page->set('title','GLOC '.TR::get('security','view_user_profile'));
		
		$this->setView('details');
		
		$this->view();	
	}
}