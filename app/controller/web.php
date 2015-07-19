<?php

abstract class WebController extends AppController {

	public function __construct() {
	
		parent::__construct();
		
		// default search url
		$this->url = '/task/index';
		$this->sinfo = '';
		$this->specifix = false;
		
		if (!$this->fc->user->isLoggedIn()) {
			NaviHelper::redirect(APP_WWW_URI.'ident/login');
		}
		
		if ($this->fc->request->isAjax()) {
		
			$this->page->clean('css');
			$this->page->clean('js');
		
		} else {
		
			// list of buttons groups (uri => label)
			// default value
			$this->breadcrumb = array(array(APP_WWW_URI => 'home'));
			
			$this->plusbutton = $this->plusmemo = $this->plusproject = false;
		
			$this->page->add('css','specific.css');
			$this->setView('header',1);
			$this->setView('breadcrumb',5);
			$this->setView('closer',15);
		}
				
	}
	
	/**
	 * prepare data for edit form
	 */
	protected function initCompanyMemberView() {
	
		$this->companies = new CompanyModel();
		$this->companies->connectDb();
		$this->companies->orderBy('name ASC');
		$this->companies->loadList();
		$this->companies->addHelper('html_form');
				
		$this->page->add('jsCode', 'var memcodata = '.MemberCompanyModel::genJavascript());
		
	}

}

abstract class TaskController extends WebController {

	public function __construct() {
		parent::__construct();
	}
	
	public function init($mode = 'tsk') {
	
		$this->mode = $mode;
	
		// list of buttons groups (uri => label) TODO libellés à traduire...
		$this->breadcrumb = array();
		
		$deforder = 'date';
		
		switch ($mode) {
			case 'due':
				$this->breadcrumb['ordinv'] = array(
					'duedate'	=> 'due',
					'invdate'	=> 'invoice',
					'code' 		=> 'code');
				$deforder = 'duedate';
				$this->mode = 'inv';
				break;
			case 'pay':
				$this->breadcrumb['ordinv'] = array(
					'paydate'	=> 'pay',
					'invdate'	=> 'invoice',
					'code' 		=> 'code');
				$deforder = 'paydate';
				$this->mode = 'inv';
				break;
			default:
				$this->breadcrumb['ordtsk'] = array(
					'date'		=> 'date', 
					'contact' 	=> 'contact', 
					'project' 	=> 'project');
				break;
		}
			
		$this->breadcrumb['filter'] = array(
				'all'		=> 'all', 
				'my_tasks' 	=> 'my tasks'
		);
		
		// set order and check if defined
		$this->order = $this->fc->request->set('ord'.$this->mode, $deforder, true);
		if (!array_key_exists($this->order, $this->breadcrumb['ord'.$this->mode])) {
			$this->order = $deforder;
		}
		
		// set pagination
		$this->pagination = $this->fc->request->set('pgz', $GLOBALS['config']['task']['pagination_default'], true);
		
		
		$this->filter = $this->fc->request->set('filter', 'all', true);
		
		if ($this->fc->user->checkAcl('create_memo')) {
			$this->plusmemo = true;
		}
		if ($this->fc->user->checkAcl('create_project')) {
			$this->plusproject = true;
			// $this->plusbutton[TR::get('button', 'new_project')]=APP_WWW_URI.'project/edit';
		}
		
		$this->alertOverdue = false;
		$this->invoices = false;
		
		$this->canEditAll = $this->fc->user->checkAcl('task_edit_all');
		
		$this->search = $this->fc->request->set('q', '', true);
	}
	
	protected function loadTasks($status, $order, $class) {
		
		$this->data = new $class(); 
		$this->data->connectDb();
		
		if ($this->filter && $this->filter != 'all') {
			// $this->data->where("class = '".$this->filter."'");
		}
		
		if (is_array($status)) {
			$this->data->where('status IN ('.implode(',',$status).')');
			if (!$this->archived) {
				$this->data->where('archived=0');
			}
		} else if (is_integer($status)) {
			$this->data->where('status = '.$status);
			$this->data->where('archived=0');
		} else {
			$this->data->where('archived=1');
			$status = 99;
		}
		
		$uid = $this->fc->user->getUid();
		// reports only
		if (isset($this->mbrs) && !empty($this->mbrs))  {
			if (count($this->mbra) > 1) {
				$this->data->where('member_id IN ('.$this->mbrs.')');
			} else {
				$this->data->where('member_id = '.$this->mbrs);
			}
		} else
			// regular task list
		if ($this->filter && $this->filter == 'my_tasks') {
			// my tasks only
			$this->data->where('((author_id='.$uid.' AND context=0) OR member_id='.$uid.')');
		} else if ($this->fc->user->checkAcl('task_see_all')) {
			// for memos can only see users'
			$this->data->where('(public=1 OR context>0 OR author_id='.$uid.' OR member_id='.$uid.')');
		} else {
			// can see only users' tasks
			$this->data->where('(author_id='.$uid.' OR member_id='.$uid.' OR public=1)');
		}
		
		if ($this->search) {
			if (preg_match('/^@/', $this->search)) {
				if ($user = TaskModel::searchUser($this->search)) {
					$this->data->where('member_id='.$user['id']);
					$this->sinfo = 'listing tasks assigned to <strong>'.$user['name'].'</strong>';
				} else {
					$this->fc->getHelper('messaging')->addMessage('ERROR:can not find user '.$this->search);
				}
			} else if (preg_match('/^([0-9]{2}\/[0-9]{2}(\/[0-9]{2,4})?)$/', $this->search, $arr)) {
				$date = VarDte::sani(TaskModel::parseDate($arr[1], isset($arr[2])?$arr[2]:''));
				$this->data->where("deadline >= '$date'");
				$this->sinfo = 'listing tasks with due date on and after <strong>'.$this->search.'</strong> ('.$date.')'; 
			} else if (preg_match('/^([0-9]{4}\-[0-9]{2}\-[0-9]{2}),([0-9]{4}\-[0-9]{2}\-[0-9]{2})$/',$this->search, $arr)) {
				$this->data->where("(deadline >= '".$arr[1]."' AND deadline <= '".$arr[2]."')");
				$this->search = '';
			} else {
				$search = str_replace("'","''",$this->search);
				if (preg_match('/\*/', $this->search)) {
					$search = str_replace('*', '%', $search);
				} else {
					$search = '%'.str_replace(' ','%', $search).'%';
				}
				$this->data->where("title LIKE '$search'");
				$this->sinfo = 'listing tasks containing &laquo; <strong>'.$this->search.'</strong> &raquo;';
			}
		}
		
		$this->data->orderBy($order);
		
		$this->data->page($this->pagination, $this->fc->request->get('pg'));
		$this->data->loadList();
		
	}
	
	public function initTasks($status, $order) {
	
		if (isset($_POST['task'])) {
			// multi update
			TaskModel::updateManyAtOnce($_POST['mode'], $_POST['task']);
		}
	
		$this->loadTasks($status, $order, 'TaskSummary');
		
	}
	
	public function initInvoices($status, $order) {
	
	
		if (isset($_POST['invoice'])) {
			// multi update
			InvoiceModel::updateManyAtOnce($_POST['mode'], $_POST['invoice']);
		}
	
		$this->loadTasks($status, $order, 'TaskFull');
		
	}
	
}