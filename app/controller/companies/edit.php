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
 * Companies
 *
 * Edit a company
 * @since 
 */
class CompaniesEdit extends WebController {

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
		
		$id = $this->fc->request->get('id','INT');

		$this->data = New CompanyModel();
		$this->data->connectDb();
		
		if ($id) {
			$this->data->setUid($id);
			$this->data->load();
		}
		
		if ($this->fc->request->chk('save')) {
			$this->data->set($_POST);
			if ($this->data->check()) {
				$this->data->save();
				$this->fc->redirect(APP_WWW_URI.'companies/details/'.$this->data->get('id'));
			}
		}
		
		$this->data->addHelper('html_form');
		
		$this->setView('edit');
		
		$this->view();	
	}
}