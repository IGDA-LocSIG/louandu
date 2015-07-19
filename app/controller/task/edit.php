<?php
/**
 * Tasks
 *
 * @package taskfreak
 * @author Stan Ozier <taskfreak@gmail.com>, Hervé Renault <rv@tirzen.com>
 * @version 
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * Tasks
 *
 * List of current users
 * @since 
 */
 
/* include_once(APP_CORE_PATH.'controller/web.php'); */
 
class TaskEdit extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	public function mainAction() {
	
		$this->memid = $this->fc->user->getUid(); // default selected user
	
		$this->data = new TaskModel(); 
		$this->data->connectDb();		
		
		if ($id = $this->fc->request->get('id')) {
			$this->data->setUid($id);
			if (!$this->data->load()) {
				NaviHelper::redirect(APP_WWW_URI,'ERROR:Not found');
			}
			
			// check rights
			if ($this->data->get('author_id') != $this->memid && !$this->fc->user->checkAcl('task_edit_all')) {
				NaviHelper::redirect(APP_WWW_URI,'ERROR:Access Denied');
			}
			
			// update default selected user
			$this->memid = $this->data->get('member_id');
		}
		
		if (isset($_POST['status'])) {
		
			$this->data->set($_POST);
			
			// public or not ?
			$this->data->set('public', $this->fc->request->chk('public')?'1':'0');
	
			// check data
			if ($this->data->check($this->fc->user->getUid())) {
				// save to DB
				$this->data->save();
				// redirect to list
				NaviHelper::redirect(APP_WWW_URI);
			}
		}
		
		$this->page->set('title','GLOC edit memo task');

		$this->data->addHelper('html_form');
		
		$this->initCompanyMemberView();
		
		$this->setView('edit');
		
		$this->view();
	}
	
	public function newAction() {
	
		// check if task definition is correct
		if ($obj = TaskModel::parse($this->fc->request->get('fulltxt'))) {
			// set author id as logged in user
			if ($obj->check($this->fc->user->getUid())) {
				// public or not ?
				$obj->set('public', $this->fc->request->chk('public')?'1':'0');
				// connect object to DB
				$obj->connectDb();
				// insert memo in database
				$obj->insert();
				// redirect
				NaviHelper::redirect(APP_WWW_URI);
			} else {
				$this->data = $obj;
				$this->data->addHelper('html_form');
				
				$this->initCompanyMemberView($this->fc->user->getUid());
				
				$this->setView('edit');
			}
		}
		
		$this->mainAction();
		
	}
	
	
}