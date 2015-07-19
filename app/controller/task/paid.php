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
 * List of invoiced tasks
 * @since 
 */
 
include_once(APP_CORE_PATH.'controller/web.php');
 
class TaskPaid extends TaskController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	public function mainAction() {
	
		parent::init('pay');
		
		$this->buttons = array(
			'2' => array(
				'css'	=> 'btn btn-warning',
				'label'	=> 'Unpaid',
			),
			'archive' => array(
				'css'	=> 'btn',
				'label'	=> 'Archive'
			)
		);
				
		$this->initInvoices(3, $this->order.' ASC, invoice_id ASC');
		
		$this->page->set('title','TF! Tasks Paid');
		
		$this->url = APP_WWW_URI.'task/paid';
		
		$this->setView('list/invoiced');
		
		$this->view();	
	}
}