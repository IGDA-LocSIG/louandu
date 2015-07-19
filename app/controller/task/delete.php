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
 
class TaskDelete extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	public function mainAction() {
	
		$this->data = new TaskModel(); 
		$this->data->connectDb();		
		
		if ($id = $this->fc->request->get('id')) {
			$this->data->setUid($id);
			$this->data->delete();
		}
		
		NaviHelper::redirect(APP_WWW_URI);
		
	}
	
}