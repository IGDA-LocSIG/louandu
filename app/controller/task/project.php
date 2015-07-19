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
 * Create and edit project
 * @since 
 */
 
/* include_once(APP_CORE_PATH.'controller/web.php'); */
 
class TaskProject extends WebController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
	}
	
	/**
	 * View project
	 **/
	public function mainAction() {
		// -TODO-
	}
	
	
	/**
	 * New project (from quick entry)
	 */
	public function newAction() {
	
		// check access
		if (!$this->fc->user->checkAcl('create_project')) {
			echo 'Can not create project';
			exit;
		}
	
		// parse multiline project details
		$this->data = Project::parse($this->fc->request->get('fulltxt'));
		
		// check and edit tasks details
		$this->initCompanyMemberView();
		
		$this->page->set('title','GLOC edit project task');
		
		$this->setView('project/edit');
		
		$this->view();
		
	}
	
	/**
	 * Edit task by default
	 */
	public function editAction() {
	
		// check access
		if (!$this->fc->user->checkAcl('create_project')) {
			echo 'Can not create project';
			exit;
		}
	
		$this->data = new Project(); 
		
		$id = $this->fc->request->get('id');
		
		if (isset($_POST['po'])) {
		
			// submit tasks details
			$check = $this->data->setDetails($_POST, $id);
			
			$this->loadMemberData();
			
			if ($_POST['mode'] || !$check) {
				
				switch ($_POST['mode']) {
					case 'add2client':
						$this->data->addClientTask();
						break;
					case 'add2vendor':
						$this->data->addVendorTask($this->fc->user->getUid());
						break;
					case 'del2client':
						$this->data->delClientTask($_POST['modo']);
						break;
					case 'del2vendor':
						$this->data->delVendorTask($_POST['modo']);
						break;
				}
				
				$this->initCompanyMemberView();
				
				$this->setView('project/edit');

			} else {
			
				// move to 2nd step (status and notifications)
				$this->setView('project/confirm');
				
			}
			
		} else {

			// reload tasks		
			if ($id && $id != 'new') {
				$this->data->loadTasks($id);
			}
			
			// 1st step : edit tasks details
			$this->initCompanyMemberView();
			
			$this->setView('project/edit');
			
		}
		
		$this->view();
	}
	
	/**
	 * Re-edit by conming back from confirm screen
	 */
	public function reeditAction() {
	
		$this->data = new Project(); 
		
		$id = $this->fc->request->get('id');
	
		$this->data->setDetails($_POST, $id);
		
		$this->initCompanyMemberView();
			
		$this->setView('project/edit');
		
		$this->view();
	}
	
	/**
	 * Status and notifications
	 */
	public function confirmAction() {
	
		$this->data = new Project();
		
		//error_log('CONFIRM Action');
		
		$id = $this->fc->request->get('id');
	
		// submit status and notifications
		if (isset($_POST['status'])) {
			
			// re-submit task details
			$this->data->setDetails($_POST, ($id != 'new')?$id:'');
			
			//error_log('OK, set, now checking...');
			
			// set author id as logged in user
			if ($this->data->check($this->fc->user->getUid())) {
				
				// prepare email
				
				$mail = new Email();
				
				$this->loadMemberData();
				
				// send email to client
				
				$sjt = 'subject_client_'.(($id != 'new')?'upd':'new');
				
				if ($this->fc->request->chk('email2client')) {
				
					$mail->subject($this->data->title.' : '.$GLOBALS['config']['email'][$sjt]);
					$mail->to($this->client->get('email'));
					if ($GLOBALS['config']['email']['bcopy_client']) {
						$mail->message->setBcc($GLOBALS['config']['email']['bcopy_client']);
					}
					$body = '<h3>'.$this->data->title.'</h3>'."\n"
						.'<p>PO : '.$this->data->po.'</p>'."\n";
					if ($m = $this->fc->request->get('mess2client')) {
						$body .= '<p>'.nl2br($m).'</p>';
					}
					foreach ($this->data->ctasks as $idx => $obj) {
						$body .= '<div style="border:1px solid #999;padding:10px">'.$obj->mailBody().'</div><br />';
					}
					$mail->body($body);
					$mail->send();
				}
				
				// send email to vendor
				
				$arr = $this->fc->request->get('email2vendor');
				
				$aeml = array();
				
				if ($arr && count($arr)) {
					foreach ($arr as $idx) {
						$obj = $this->data->vtasks[$idx];
						$eml = $this->vendors[$idx]->get('email');
						if (!array_key_exists($eml, $aeml)) {
							$aeml[$eml] = array();
						}
						$aeml[$eml][] = $obj;
					}
					
					$sjt = 'subject_vendor_'.(($id != 'new')?'upd':'new');
					
					foreach ($aeml as $eml => $atsk) {
						$mail->init();
						$mail->subject($this->data->title.' : '.$GLOBALS['config']['email'][$sjt]);
						$mail->to($eml);
						if ($GLOBALS['config']['email']['bcopy_vendor']) {
							$mail->message->setBcc($GLOBALS['config']['email']['bcopy_vendor']);
						}
						$body = '<h3>'.$this->data->title.'</h3>'."\n"
							.'<p>PO : '.$this->data->po.'</p>'."\n";
						if ($m = $this->fc->request->get('mess2vendor')) {
							$body .= '<p>'.nl2br($m).'</p>';
						}
						foreach ($atsk as $obj) {
							$body .= '<div style="border:1px solid #999;padding:10px">'.$obj->mailBody().'</div><br />';
						}
						$mail->body($body);
						$mail->send();
					}
				}
				
				// save in database
				$this->data->save();
				
				// redirect
				NaviHelper::redirect(APP_WWW_URI);
			} else {
				$this->loadMemberData();
			}
		} else if ($id && $id != 'new') {
			// reload tasks ?
			$this->data->loadTasks($id);
		}
		
		$this->setView('project/confirm');
		$this->view();
		
	}
	
	/**
	 * Delete project
	 */
	public function deleteAction() {
	
		if ($pid = $this->fc->request->get('id')) {
		
			$arrInvoices = array();
		
			$obj = new TaskModel();
			$obj->connectDb();
			$obj->where("pid='$pid'");
			if ($obj->loadList()) {
				while($obj->next()) {
					if ($iid = $obj->get('invoice_id')) {
						if (!in_array($iid, $arrInvoices)) {
							$arrInvoices[] = $iid;
						}
					}
					$obj->delete();
				}
			}
			
			// update invoices if necessary
			if (count($arrInvoices)) {
				foreach ($arrInvoices as $iid) {
					$obj = new InvoiceModel();
					$obj->connectDb();
					$obj->setUid($iid);
					// $obj->load();
					$obj->updateData(false); // do not update duedate
				}
			}

		}
		
		// redirect
		NaviHelper::redirect(APP_WWW_URI);
	}
	
	/**
	 * load client and vendors info
	 */
	protected function loadMemberData() {
	
		// client
		$this->client = new MemberCompanyModel();
		$this->client->connectDb();
		$this->client->setUid($this->data->client['id']);
		$this->client->load();
		
		// vendors
		$this->vendors = array();
		foreach ($this->data->vtasks as $idx => $obj) {
			$vendor = new MemberCompanyModel();
			$vendor->connectDb();
			$vendor->setUid($obj->get('member_id'));
			$vendor->load();
			$this->vendors[$idx] = $vendor;
		}
	}
	
}