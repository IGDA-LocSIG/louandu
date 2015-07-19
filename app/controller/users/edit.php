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
class UsersEdit extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('MemberCompanyModel');
	}
	
	public function mainAction() {
	
		$id = $this->fc->request->get('id','INT');
		
		$this->initUser($id);
		
		if ($this->fc->request->chk('save')) {
			$this->data->set($_POST);
			$ignoreFields = array( // don't update or insert these
					'id',
					'last_login_date',
					'last_login_address',
					'visits',
					'bad_access',
					// these are GLOC choices :
					'time_zone',
					// unused (for updates)
					'activation',
					'auto_login',
					'date_format_us',
					'expiration_date',
					'hidden'
					); 
			if (!$this->fc->user->checkAcl('admin_user') && $id != $_SESSION['appUserId']) {
				// non admin can cano give access to system or change password
				// current user can change his own password though
				$ignoreFields = array_merge($ignoreFields, array('password','salt'));
			} else if (!$this->fc->request->get('password')) {
				// if the user (admin or normal user) didn't type a password while editing a user
				// then it means she doesn't want change it.
				$ignoreFields = array_merge($ignoreFields, array('password','salt'));
			}
			// existing user, ignore creation date
			if ($this->fc->request->get('id')) {
				$ignoreFields = array_merge($ignoreFields, array('creation_date'));
			}
			$this->data->ignore(implode(',', $ignoreFields));
				
			if ($this->data->check()) {
				$this->data->save();
				$this->data->addHelper('auth_acl', $this->data);
				$this->data->updateACL('general', $this->data->get('class'));
				$this->fc->redirect(APP_WWW_URI.'users/details/'.$this->data->get('id')); 
			}
		} elseif ($id) {
			$this->data->load();
		} else {
			// set default rates
			$this->data->set('rate_translate', $GLOBALS['config']['task']['work']['buy'][1]);
			$this->data->set('rate_review', $GLOBALS['config']['task']['work']['buy'][2]);
		}
		
		$this->data->addHelper('html_form');
		
		$this->companies = New CompanyModel();
		$this->companies->connectDb();
		$this->companies->orderBy('name');
		$this->companies->loadList();
		
		$this->page->set('title','GLOC '.TR::get('security','edit_user_profile'));
		
		$this->setView('edit');
		
		$this->view();	
	}
	
	public function deleteAction() {
	
		$this->loadUser($this->fc->request->get('id','INT'));
		$this->data->set('hidden', 1);
		$this->data->set('enabled', 0);
		$this->data->fields('hidden,enabled');
		$this->data->update();
		
		$this->fc->redirect(APP_WWW_URI.'users/details/'.$this->data->get('id'));
		
	}
	
	public function restoreAction() {
		
		$this->loadUser($this->fc->request->get('id','INT'));
		$this->data->set('hidden', 0);
		$this->data->fields('hidden');
		$this->data->update();
		
		$this->fc->redirect(APP_WWW_URI.'users/details/'.$this->data->get('id'));
		
	}
	
	protected function initUser($id) {
		
		if (!$this->fc->user->checkAcl('admin_user') && $id != $_SESSION['appUserId']) {
			if ($id || !$this->fc->user->checkAcl('create_user')) {
				$this->fc->redirect(APP_WWW_URI, 'ERROR:access_denied');
			}
		}

		if ($this->fc->user->checkAcl('admin_user')) {
			$this->breadcrumb = array(
					APP_WWW_URI.'users/index/main/filter'
					=>
					$GLOBALS['config']['users']['class']['options'] + array('hidden' => '(deleted)','all' => 'all')
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
		
		if ($id) {
			$this->data->setUid($id);
		}
		
	}
	
	protected function loadUser($id) {
		$this->initUser($id);
		if (!$this->data->load()) {
			$this->fc->redirect(APP_WWW_URI, 'ERROR:data_not_found');
		}
	}
}