<?php
/**
 * Parachutes
 *
 * @package taskfreak_tt 
 * @author Stan Ozier <taskfreak@gmail.com>, Hervé Renault <rv@tirzen.com>
 * @version 
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * Users
 *
 * List of current users
 * @since 
 */
class UsersIndex extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('MemberCompanyModel');
	}
	
	public function mainAction() {
		
		if (!$this->fc->user->checkAcl('admin_user,view_user')) {
			$this->fc->redirect(APP_WWW_URI, 'ERROR:access_denied');
		}
		
		$viewall = $this->fc->user->checkAcl('admin_user');
		
		if ($viewall) {		
			$this->breadcrumb = array('filter' => $GLOBALS['config']['users']['class']['options'] + array('hidden' => '(deleted)', 'all' => 'all'));
		} else {
			$this->breadcrumb = array('filter' => $GLOBALS['config']['users']['class']['options']);
		}
		// set filter according to request, default to 'all'
		$this->filter = $this->fc->request->set('filter', $viewall?'all':'client', false);
		
		if ($this->fc->user->checkAcl('create_user')) {
			$this->plusbutton = array(
				TR::get('button','new_user') 	=> APP_WWW_URI.'users/edit',
			);
		}
		
		$this->data = New MemberCompanyModel(); 
		$this->data->connectDb();
		
		if ($this->filter) {
			if ($this->filter == 'hidden' && $viewall) {
				$this->data->where("hidden = 1");
			} else if ($this->filter != 'all') {
				$this->data->where("class = '".$this->filter."'");
				$this->data->where("hidden = 0");
			} else {
				$this->data->where("hidden = 0");
			}
		} else if ($viewall) {
			$this->data->where("hidden = 1");
		}
		
		$this->data->orderBy('company.name');
		
		$this->data->page($GLOBALS['config']['task']['pagination_default'],$this->fc->request->get('pg'));
		$this->data->loadList();
		
		$this->page->set('title','GLOC '.TR::get('security','users_administration'));
		
		$this->setView('main');
		
		$this->view();	
	}
}