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
 
class ReportsIndex extends TaskController {

	public function __construct() {
		parent::__construct(true);
		
		$this->fullReport = $this->fc->user->checkAcl('view_report');
		
		$this->fc->loadModel('TaskModel');
		
	}
	
	public function init() {
	
		$this->breadcrumb = array(
			'repfil' => array(
				'project' 	=> 'project',
				'task'		=> 'task'			
			)
		);
		
		$order = 'task.po DESC, task.context ASC';
		
		$this->filter = $this->fc->request->set('repfil', 'project', true);
		switch ($this->filter) {
			case 'task':
				$order = 'task.po DESC, task.deadline ASC, task.deadtime ASC';
				break;
			default:
				$this->filter = 'project';
				break;
		}
				
		$this->begin = $this->fc->request->set('repbegin', date('01/m/y'), true);
		$this->end = $this->fc->request->set('repend', date('t/m/y'), true);
		
		$this->status = $this->fc->request->set('tsk', array(1,2,3,4), true);
		
		$this->archived = ($this->fc->request->chk('reparc'))?true:false;
		
		$this->showMargin = $this->fullReport; // should be false
		
		if ($this->fullReport) {
		
			// manager can select a list of users to see report of
			
			$this->members = new MemberCompanyModel();
			$this->members->connectDb();
			$this->members->where('hidden=0');
			$this->members->orderBy('company.name ASC, member.nickname ASC');
			$this->members->loadList();
		
			$this->mbrs = $this->fc->request->set('mbrs', '', true);
			$this->mbra = array();
			$this->mbrc = 0;
			if ($this->mbrs) {
				$this->mbra = explode(',', $this->mbrs);
				$this->mbrc = count($this->mbra);
			} else {
				$this->showMargin = true;
			}
			
		} else {
		
			// if no access to full report, can only see own tasks
		
			$uid = $this->fc->user->getUid();
			$this->mbra = array($uid);
			$this->mbrs = $uid;
		}
		
		$begin = VarDte::sani($this->begin);
		$end = VarDte::sani($this->end);
		if (!$begin) {
			$begin = date('Y-m-01');
		}
		if (!$end) {
			$end = date('Y-m-t');
		}
		$this->search = $begin.','.$end;
		
		$this->pagination = 0;
		$this->loadTasks($this->status, $order, 'TaskSummary');
	}
	
	protected function precalcProject() {
	
		$this->totvdr = $this->totclt = $this->margin = $this->count = $c = $v = 0;
		$this->arrtot = $this->arrmar = array();
		
		$last = $po = -1;
		while ($this->data->next()) {
			$po = $this->data->get('po');
			if ($last != $po) {
				if ($last) {
					if (!$c) {
						$this->arrtot[$last] = $v;
						$this->arrmar[$last] = 0;
					} else {
						$this->margin += ($c - $v);
						$this->arrtot[$last] = $c;
						$this->arrmar[$last] = $c - $v;
					}
					$c = $v = 0;
				}
				$last = $po;
			}
			$t = $this->data->getWorkTotal();
			switch ($this->data->get('context')) {
				case 2: // vendor
					$v += $t;
					$this->totvdr += $t;
					break;
				case 1: // client
					$c += $t;
					$this->totclt += $t;
					break;
			}
			$this->count++;
		}
		if (!$c) {
			$this->arrtot[$po] = $v;
			$this->arrmar[$po] = 0;
		} else if ($v || $c) {
			$this->margin += ($c - $v);
			$this->arrtot[$po] = $c;
			$this->arrmar[$po] = $c - $v;
		}
		$this->data->rewind();
	}
	
	public function mainAction() {
	
		$this->init();
		
		$this->precalcProject();
		
		$this->page->set('title','TF! Reports');
		
		$this->url = APP_WWW_URI.'reports/index';
		
		$this->specifix = 'inc/rform';
		
		$this->setView('index');
		
		$this->view();	
	}
	
	public function downloadAction() {
	
		$this->init();
	
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');
		
		// output the column headings
		switch ($this->filter) {
			case 'task':
				$this->csvTask($output);
				break;
			default:
				$this->precalcProject();
				$this->csvProject($output);
				break;
		}
		
		exit;
	
	}
	
	protected function csvTask(&$output) {
		// header
		fputcsv($output, array('Context', 'Delivery', 'Title', 'Company', 'Contact', 'PO', 'Work', 'Words', 'Rate', 'Total'));
		
		// data rows
		while ($this->data->next()) {
			fputcsv($output, array(
				$this->data->getContext(),
				$this->data->get('deadline'),
				$this->data->get('title'),
				$this->data->get('company')->get('name'),
				$this->data->get('member')->get('email'),
				$this->data->get('po'),
				$this->data->getWork(),
				$this->data->get('words'),
				$this->data->get('rate'),
				number_format($this->data->getWorkTotal(),3)
			));
		}
	}
	
	protected function csvProject(&$output) {
		// header
		$cols = array('PO', 'Company', 'Contact', 'Sales');
		if ($this->fullReport) {
			$cols[] = 'Margin';
		}
		fputcsv($output, $cols);
		
		// data rows
		
		$last = -1;
		
		while ($this->data->next()) {
			$po = $this->data->get('po');
			
			if ($last != $po) {
			
				$cols = array(
					$po,
					$this->data->get('company')->get('name'),
					$this->data->get('member')->get('email'),
					number_format($this->arrtot[$po],3)
				);
				if ($this->fullReport) {
					$cols[] = number_format($this->arrmar[$po],3);
				}
			
				fputcsv($output, $cols);
				
				$last = $po;
			
			}
		}
	}
}