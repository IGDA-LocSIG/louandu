<?php
/**
 * Parachutes
 *
 * @package taskfreak_tt 
 * @author Stan Ozier <taskfreak@gmail.com>
 * @version 0.4
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * Dashboard
 *
 * List of last events of things to notice
 * @since 0.1
 */
class DashboardIndex extends WebController {

	public function __construct() {
		parent::__construct(true);	
	}
	
	public function mainAction() {
	
		// list of buttons groups (uri => label) TODO libellés à traduire...
		$this->breadcrumb = array(
				'order' => array(
						'date'		=> 'date', 
						'contact' 	=> 'contact', 
						'project' 	=> 'project'),
				'filter' => array(
						'all'		=> 'all', 
						'my_tasks' 	=> 'my tasks')
				);
		// set order/filter according to request, default to 'date'/'all'
		$this->order = $this->fc->request->set('order', 'date', true);
		$this->filter = $this->fc->request->set('filter', 'all', true);
		
		$this->plusbutton = array();
		if ($this->fc->user->checkAcl('create_memo')) {
			$this->plusbutton[TR::get('button', 'new_memo')]=APP_WWW_URI.'memo/edit';
		}
		if ($this->fc->user->checkAcl('create_project')) {
			$this->plusbutton[TR::get('button', 'new_project')]=APP_WWW_URI.'project/edit';
		}
				
		$this->setView('main');
		
		$this->view();
		
	}
	
}