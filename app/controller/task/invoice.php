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
 
class TaskInvoice extends WebController {

	public function __construct() {
	
		parent::__construct(true);
		
		$this->fc->loadModel('TaskModel');
		$this->fc->loadModel('InvoiceModel');
	}
	
	/**
	 * Print options
	 */
	public function mainAction() {
		
		$this->setView('main');
		
		$this->view();
		
	}
	
	/**
	 * Print invoice
	 */
	public function pdfAction() {
	
		// check access
		if (!$this->fc->user->checkAcl('invoicing')) {
			echo 'Can not access invoice';
			exit;
		}
		
		// load invoice	
		$id = $this->fc->request->get('id');
	
		// load invoice
		$this->data = new InvoiceModel(); 
		$this->data->connectDb();
		$this->data->setUid($id);
		if (!$this->data->load()) {
			echo 'Invoice not found';
			exit;
		}
		
		// set invoice date
		if ($this->data->isEmpty('invdate')) {
			$this->data->set('invdate', 'NOW');
			$this->data->fields('invdate');
			$this->data->update();
		}
		
		// load tasks
		$this->loadTasks();
		
		// load client/vendor address
		$address = array();
		$nf = false;
		do {
			switch ($this->tasks->get('context')) {
			case 2: // vendor task
				$address['client'] = $GLOBALS['config']['billing']['address'];
				$nf = 'supplier';
				break;
			case 1: // client task
				$address['supplier'] = $GLOBALS['config']['billing']['address'];
				$nf = 'client';
				break;
			}
		} while (!$nf && $this->tasks->next());
		
		// the other address
		$address[$nf] = $this->tasks->getBillingAddress();
				
		// prepare invoice
		
		$this->tasks->rewind();
	
		include APP_LIB_PATH.'tcpdf/config/lang/eng.php';
		include APP_LIB_PATH.'tcpdf/tcpdf.php';
		
		$pdf = new InvoicePdfModel(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

		$title = 'Invoice # '.$this->data->get('code');
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('GLOC');
		$pdf->SetTitle($title);
		
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		// $pdf->SetHeaderData('', 66, $address['client'], $address['supplier']);
		// $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 12));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 12));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 12, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(20);
		
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 20);
		
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
		
		//set some language-dependent strings
		$pdf->setLanguageArray('eng'); 
		
		// ---------------------------------------------------------
		
		// set font
		$pdf->SetFont('helvetica', '', 11);
		
		// add a page
		$pdf->AddPage();
		
		// set color for filler
		$pdf->SetFillColor(255, 255, 0);
		
		// --- Billing info ---
		
		if ($nf == 'client' && $GLOBALS['config']['billing']['logo']) {
			$pdf->Image($GLOBALS['config']['billing']['logo'], 120, 10, 70, 20, 'JPG', '', '', false, 300);
		}
		
		$pdf->SetX(13);
		$pdf->MultiCell(98, 7, $address['supplier'], 0, 'L', 0, 1);
		
		$pdf->SetY(45);
		$pdf->SetX(121);
		$pdf->MultiCell(75, 7, $address['client'], 0, 'L', 0, 1);
		
		
		// --- Invoice number and date ---
		
		$y = $pdf->GetY()+15;
		
		$pdf->SetFont('helvetica', '', 18);
		$pdf->MultiCell(100, 12, $title, 0, 'L', 0, 1, 40, $y);
		
		$pdf->SetFont('helvetica', '', 12);
		// $pdf->SetTextColor(102, 102, 102);
		$pdf->MultiCell(100, 5, 'Invoice date : '.$this->data->html('invdate','%e %b %Y'), 0, 'L', 0, 1, 40);
		$pdf->SetFont('helvetica', '', 12);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->MultiCell(100, 5, 'Due date : '.$this->data->html('duedate','%e %b %Y'), 0, 'L', 0, 1, 40);
		
		// --- Data ---
		
		$pdf->SetFont('helvetica', 'I', 11);
		$pdf->SetY($y+35);
		$pdf->SetDrawColor(128);
		
		// table header
		$pdf->SetX(13);
		$pdf->Cell(115, 9, 'Description', 'B', 0, 'L');
		$pdf->Cell(20, 9, 'Quantity', 'B', 0, 'R');
		$pdf->Cell(24, 9,  'Unit Price', 'B', 0, 'R');
		$pdf->Cell(25, 9, 'Total', 'B', 1, 'R');
		
		// tasks
		
		while ($this->tasks->next()) {
		
			$pdf->SetFont('helvetica', '', 10);
		
			$txt = $this->tasks->getSummary();
			$lh = $pdf->getNumLines($txt, 115) * 5 + 4;
			
			$y = $pdf->GetY();
			if ($y+$lh > 270) {
				$pdf->AddPage();
			}
			
			$pdf->SetX(13);
			
			// $pdf->Cell(115, 9, $txt, 'B', 0, 'L');
			$pdf->MultiCell(115, $lh, $txt, 'B', 'L', false, 0, '', '', true, 0, false, true, $lh, 'M');
			
			$pdf->Cell(20, $lh, $this->tasks->get('words'), 'B', 0, 'R');
			$pdf->Cell(24, $lh,  $this->tasks->get('rate'), 'B', 0, 'R');
			
			$pdf->SetFont('helvetica', '', 11);
			$pdf->Cell(25, $lh, number_format($this->tasks->getWorkTotal(),2), 'B', 1, 'R');
						
		}
		
		$dd = '';
		if (!$this->data->isEmpty('paydate')) {
			$dd = 'paid on '.$this->data->html('paydate','%B %e, %Y');
		} else if (!$this->data->isEmpty('duedate')) {
			$dd = 'due by '.$this->data->html('duedate','%B %e, %Y');
		}
		
		$pdf->SetX(13);
		$pdf->SetFont('helvetica', 'I', 12);
		$pdf->Cell(154, 11, 'Grand total '.$dd.'.', '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->Cell(30, 11, '€ '.number_format($this->data->get('amount'),2), '', 1, 'R');
		
		// disclaimer in footer
		
		if ($nf == 'client' && $GLOBALS['config']['billing']['footer']) {
		
			$y = $pdf->GetY();
			if ($y > 250) {
				$pdf->AddPage();
			}
			
			$pdf->SetY(255);
			
			$pdf->SetFont('helvetica', '', 7);
			$pdf->writeHTML($GLOBALS['config']['billing']['footer'],
				true, false, false, false, 'C');
		}
		
		// reset pointer to the last page
		$pdf->lastPage();
		
		// ---------------------------------------------------------
		
		//Close and output PDF document
		$pdf->Output('order_'.str_replace(' ','-',$this->data->get('code')).'.pdf', 'I');
		
		exit;

	}
	
	/**
	 * Edit invoice
	 */
	public function editAction() {
	
		$this->data = new InvoiceModel(); 
		
		$id = $this->fc->request->get('id');
		
		$this->data->connectDb();
		$this->data->setUid($id);
		$this->data->load();
		
		$this->loadTasks();
		
		if (isset($_POST['id'])) {
		
			$this->data->ignore('amount');
			$this->data->set($_POST);
			$this->data->save();
			
			$status = intval($_POST['status']);
			$archive = (isset($_POST['archived'])?1:0);

			if ($this->status != $status || $this->archived != $archive) {
				
				do {
					// error_log('SAVE ID='.$this->tasks->getUid());
					DbConnector::query('UPDATE task'
						.' SET status='.$status.', archived='.$archive
						.' WHERE id='.$this->tasks->getUid());
				} while ($this->tasks->next());
				
				$this->loadTasks();
			}
			
			$status = ($status == 2)?'invoiced':'paid';
			
			NaviHelper::redirect(APP_WWW_URI.$GLOBALS['config']['pages'][$status]);
			
		}
		
		$this->data->addHelper('html_form');
	
		$this->setView('invoice/edit');
		
		$this->view();
		
	}
	
	/**
	 * Remove task from invoice
	 */
	public function removeAction() {
		
		$objTask = new TaskModel();
		$objTask->connectDb();
		$objTask->setUid($this->fc->request->get('id'));
		$objTask->load();
		$id = $objTask->value('invoice_id');
		
		$objTask->set('invoice_id', 0);
		$objTask->set('status', 1); // set as done
		$objTask->update();
		
		$objInvoice = new InvoiceModel();
		$objInvoice->connectDb();
		$objInvoice->setUid($id);
		$objInvoice->load();
		
		$status = $objInvoice->isEmpty('paydate')?'invoiced':'paid';
		
		if ($objInvoice->updateAmount()) {
			// redirect to invoice edit
			NaviHelper::redirect(APP_WWW_URI.'task/invoice/edit/'.$id.'.html');

		} else {
			// redirect to invoice list
			NaviHelper::redirect(APP_WWW_URI.$GLOBALS['config']['pages'][$status]);
		}
		
	}
	
	/**
	 * Delete invoice
	 */
	public function deleteAction() {
	
		$id = $this->fc->request->get('id');
		
		$objInvoice = new InvoiceModel();
		$objInvoice->connectDb();
		$objInvoice->setUid($id);
		
		$status = 'done';
		
		if ($objInvoice->load()) {
		
			$status = $objInvoice->isEmpty('paydate')?'invoiced':'paid';
			
			// update tasks
			DbConnector::query('UPDATE task'
				.' SET status=1, invoice_id=0'
				.' WHERE invoice_id='.$id);
			
			$objInvoice->delete();
		}
		
		NaviHelper::redirect(APP_WWW_URI.$GLOBALS['config']['pages'][$status]);
		
	}
	
	/**
	 * load invoice's tasks
	 */
	protected function loadTasks() {
		$this->tasks = $this->data->loadTasks();
		$this->tasks->next();
		
		$this->status = $this->tasks->value('status');
		$this->archived = $this->tasks->value('archived');
	}
	
	
}