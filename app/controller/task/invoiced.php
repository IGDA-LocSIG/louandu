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
 
class TaskInvoiced extends TaskController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	public function mainAction() {
	
		parent::init('due');
		
		$this->buttons = array(
			'1' => array(
				'css'	=> 'btn btn-warning',
				'label'	=> 'Un-invoice',
			),
			'3' => array(
				'css'	=> 'btn btn-success',
				'label'	=> 'Paid',
			),
			'archive' => array(
				'css'	=> 'btn',
				'label'	=> 'Archive'
			)
		);
		
		$this->initInvoices(2, $this->order.' ASC, invoice_id ASC');
		
		$this->page->set('title','TF! Tasks Invoiced');
		
		$this->url = APP_WWW_URI.'task/invoiced';
		
		$this->setView('list/invoiced');
		
		$this->view();	
	}
}