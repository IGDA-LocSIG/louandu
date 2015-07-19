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
 
class TaskArchived extends TaskController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	public function mainAction() {
	
		parent::init();
		
		$this->buttons = array(
			'unarchive' => array(
				'css'	=> 'btn',
				'label'	=> 'Un-archive'
			)
		);
		
		if (isset($_POST['task'])) {
			// multi update
			TaskModel::updateManyAtOnce($_POST['mode'], $_POST['task']);
		}
				
		$this->initInvoices('archived','invoice_id DESC, deadline DESC, deadtime DESC');
		
		$this->page->set('title','TF! Tasks Archives');
		
		$this->url = APP_WWW_URI.'task/archived';
		
		$this->setView('list/invoiced');
		
		$this->view();	
	}
}