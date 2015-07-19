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
 * Details of a user
 * @since 
 */
class CompaniesDetails extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('CompanyModel');
	}
	
	public function mainAction() {
		
		$id = $this->fc->request->get('id','INT');
		
		$this->breadcrumb = array(
				array(
						APP_WWW_URI.'companies/index' 	=> TR::html('security','companies') // only choice, at this time
				)
		);
		
		$this->plusbutton = array(
				TR::get('button','edit') 	=> APP_WWW_URI.'companies/edit/'.$id,
		);

		$this->data = New CompanyModel();
		$this->data->connectDb();
		
		if ($id) {
			$this->data->setUid($id);
			$this->data->load();
		}
				
		$this->data->addHelper('html_form');
		
		$this->setView('details');
		
		$this->view();	
	}
}