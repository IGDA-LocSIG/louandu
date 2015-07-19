<?php
/**
 * TaskFreak! GLOC
 * 
 * @package taskfreak_gloc
 * @author Stan Ozier <taskfreak@gmail.com>
 * @version 0.1
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * Task
 * 
 * Class representing an invoice
 * @since 0.1
 */
class InvoiceModel extends Model {

	public function __construct() {
		parent::__construct('invoice');
		$this->addProperties(array(
			'id'				=> 'UID',
			'code'				=> 'STR',
			'invdate'			=> 'DTE',
			'duedate'			=> 'DTE',
			'paydate'			=> 'DTE',
			'amount'			=> 'DEC,3'
		));
	}
	
	/**
	 * load related tasks
	 */
	function loadTasks() {
		$obj = new TaskSummary();
		$obj->connectDb();
		$obj->where('invoice_id='.$this->getUid());
		$obj->orderBy('deadline, deadtime');
		$obj->loadList();
		return $obj;
	}
	
	/**
	 * show payment or invoice date
	 */
	public function htmlDate($icon=false, $order='duedate') {
		$lbl = 'due';
		$str = $this->html('duedate');
		if (!$this->isEmpty('paydate')) {
			$lbl = 'paid';
			$str = $this->html('paydate');
		}
		if ($order == 'invdate') {
			$str = '<span title="'.$lbl.' on '.$str.'" class="tip">'.$this->html('invdate','','not invoiced').'</span>';
		} else {
			if (!$this->isEmpty('invdate')) {
				$str = '<span title="Printed on '.$this->html('invdate').'" class="tip">'.$str.'</span>';
				if ($icon) {
					$str .= ' <i class="icon-ok"></i>';
				}
			}
		}
		return $str;
	}
	
	/**
	 * display invoice amount
	 */
	public function htmlAmount($icon=false) {
		$str = $this->html('amount'); 
		if ($icon && $this->isEmpty('paydate')) {
			$str .= ' <i class="icon-ok"></i>';
		}
		return $str;
	}
	
	/**
	 * link to print invoice (PDF)
	 */
	public function getPdfUrl() {
		return APP_WWW_URI.'task/invoice/pdf/'.$this->getUid().'.html';
	}
	
	/**
	 * link to edit invoice
	 */
	public function getEditUrl() {
		return APP_WWW_URI.'task/invoice/edit/'.$this->getUid().'.html';
	}
	
	/**
	 * check submitted data before saving task
	 */
	public function check($paid=false) {
		// set invoice date
		if ($this->isEmpty('duedate')) {
			$this->set('duedate',date('Y-m-'.$GLOBALS['config']['users']['invoice']['mode']));
		}
		// set paid if necessary
		if ($paid) {
			if ($this->isEmpty('paydate')) {
				$this->set('paydate',date('Y-m-d'));
			}
		} else {
			$this->set('paydate','0000-00-00');
		}
		// check code
		return parent::check('code');
	}
	
	/**
	 * update amount
	 */
	function updateData($due=false) {
	
		$objTasks = new TaskModel();
		$objTasks->connectDb();
		$objTasks->where('invoice_id='.$this->getUid());
		
		$total = 0;
		$terms = 0;
		$inv = false;
		
		if ($objTasks->loadList()) {
			$objTasks->next();
			
			// check payment terms
			if ($due) {
				$objMember = new MemberCompanyModel();
				$objMember->connectDb();
				$objMember->setUid($objTasks->get('member_id'));
				if ($objMember->load()) {
					$terms = $objMember->get('payterms');
				}
			}
			
			// calc total amount
			do {
				$total += $objTasks->getWorkTotal();
			} while ($objTasks->next());
			
		} else {
			$this->delete();
			return false;
		}
		
		$this->set('amount', $total);
		
		if ($due) {
			// error_log('due '.$this->get('duedate').' +'.$terms.' days');
			if ($terms) {
				$dte = $this->get('duedate');
				$arr = explode('-', $dte);
				$tme = strtotime($arr[0].'-'.$arr[1].'-01');
				$day = $arr[2];
				switch($terms) {
					case 30:
						$dte = date('Y-m', strtotime('+1 month', $tme));
						break;
					case 60:
						$dte = date('Y-m', strtotime('+2 month', $tme));
						break;
					case 90:
						$dte = date('Y-m', strtotime('+3 month', $tme));
						break;
					default:
						$dte = date('Y-m-d', strtotime('+'.$terms.' days', strtotime($dte)));
						$day = 0;
						break;
				}
				
				if ($day) {
					$max = date('t', strtotime($dte.'-01'));
					if ($day > $max) {
						$day = $max;
					} else if ($GLOBALS['config']['users']['invoice']['mode'] == 't') {
						$day = $max;
					}
					$dte .= '-'.$day;
				}
				
				// error_log('-> '.$dte);
				$this->set('duedate', $dte);
			}
			// update invoice date automatically ?
			if ($GLOBALS['config']['users']['invoice']['auto'] && $this->isEmpty('invdate')) {
				$this->set('invdate', 'now');
				$inv = true;
			}
		} else {
			// error_log('no due');
		}
		
		$this->fields('amount'.($due?',duedate':'').($inv?',invdate':''));
		$this->update();	
		return true;
	}
	
	function updateAmount() {
		return $this->updateData(false);
	}
	
	/*
	 * set archive status for multiple tasks
	 */
	public static function updateManyAtOnce($action, $arr) {
		if (is_string($arr)) {
			$arr = explode(',',$arr);
		}
		if (!count($arr)) {
			return false;
		}
		
		foreach ($arr as $invId) {
		
			$objInvoice = new InvoiceModel();
			$objInvoice->connectDb();
			$objInvoice->setUid($invId);
			if (!$objInvoice->load()) {
				continue;
			}
			
			$filter = 'invoice_id='.$objInvoice->getUid();
			
			$sta = '';
			switch ($action) {
				
				case '1':
					// delete invoice
					$objInvoice->delete();
					$sta = 'status=1';
					break;
				case '2': // unpaid
				case '3': // paid
					if ($objInvoice->check($action==3?true:false)) {
						$objInvoice->save();
					}
				case '4':
				case '5':
				case '6':
					$sta = 'status='.$action;
					break;
				case 'archive':
					$sta = 'archived=1';
					break;
				case 'unarchive':
					$sta = 'archived=0';
					break;
			}
			if ($sta) {
				// error_log('-> UPDATE task SET '.$sta.' WHERE '.$filter);
				DbConnector::query('UPDATE task SET '.$sta.' WHERE '.$filter);
			}
			
		}
		return true;
	}
}