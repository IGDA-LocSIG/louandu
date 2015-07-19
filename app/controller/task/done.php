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
 
include_once(APP_CORE_PATH.'controller/web.php');
 
class TaskDone extends TaskController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	public function mainAction() {
	
		parent::init();
		
		$this->buttons = array(
			'0' => array(
				'css'	=> 'btn btn-success',
				'label'	=> 'Re-open',
			),
			'2' => array(
				'css'	=> 'btn btn-info',
				'label'	=> 'Invoice',
				'panel'	=> 'myInvoices'
			),
			'archive' => array(
				'css'	=> 'btn',
				'label'	=> 'Archive'
			)
		);
		
		// default order and view
		$order = 'task.deadline DESC, task.deadtime DESC';
		$view = 'list/date';
		
		// other order and view options
		switch ($this->order) {
			case 'contact':
				$order = 'company.name ASC, member.nickname, task.deadline ASC, task.deadtime ASC';
				$view = 'list/contact';
				break;
			case 'project':
				$order = 'task.po DESC, task.deadline ASC, task.deadtime ASC';
				$view = 'list/project';
				break;
			case 'date':
			default:
				break;
		}
				
		$this->initTasks(1, $order);
		
		$this->invoices = new InvoiceModel();
		$this->invoices->connectDb();
		$this->invoices->where("paydate = '0000-00-00'");
		$this->invoices->orderBy('code ASC, invdate ASC');
		$this->invoices->loadList();
		
		$this->page->set('title','TF! Tasks Done');
		
		$this->url = APP_WWW_URI.'task/done';
		
		$this->setView($view);
		
		$this->view();	
	}
}