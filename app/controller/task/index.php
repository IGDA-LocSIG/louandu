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
 
class TaskIndex extends TaskController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	public function mainAction() {
	
		parent::init();
		
		$this->alertOverdue = true;
		
		$this->buttons = array(
			'1' => array(
				'css'	=> 'btn btn-info',
				'label'	=> 'Mark as done'
			),
			'archive' => array(
				'css'	=> 'btn',
				'label'	=> 'Archive'
			)
		);
		
		// default order and view
		$order = 'task.deadline ASC, task.deadtime ASC';
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
		
		$this->initTasks(0, $order);
		
		$this->page->set('title','TF! Tasks Todo');
		
		$this->url = APP_WWW_URI.'task/index';
		
		$this->setView($view);
		
		$this->view();	
	}
}