<?php
/**
 * Parachutes
 *
 * @package taskfreak_tt 
 * @author Stan Ozier <taskfreak@gmail.com>, Hervé Renault <rv@tirzen.com>
 * @version 
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * Users
 *
 * List of current users
 * @since 
 */
class CompaniesIndex extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('CompanyModel');
	}
	
	public function mainAction() {
		
		$this->breadcrumb = array(
				array(
						APP_WWW_URI.'companies/index' 	=> TR::html('security','companies') // only choice, at this time
					)
				);
		 
		$this->plusbutton = array(
				TR::get('button','new_company') => APP_WWW_URI.'companies/edit'
				);
				
		$this->data = New CompanyModel(); 
		$this->data->connectDb();
				
		$this->data->orderBy('company.name');
		
		$this->data->page($GLOBALS['config']['task']['pagination_default'],$this->fc->request->get('pg'));
		$this->data->loadList();
		
		$this->setView('main');
		
		$this->view();	
	}
}