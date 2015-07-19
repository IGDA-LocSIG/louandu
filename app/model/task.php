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
 * Class representing a task
 * @since 0.1
 */
class TaskModel extends Model {

	public function __construct() {
		parent::__construct('task');
		$this->addProperties(array(
			'id'				=> 'UID',
			'title'				=> 'STR',
			'note'				=> 'BBS',
			// 'priority'			=> 'NUM,'.json_encode($GLOBALS['config']['task']['priority']),
			'begin'				=> 'DTE',
			'deadline'			=> 'DTE',
			'deadtime'			=> 'STR',
			'status'			=> 'NUM,{"options":'.json_encode($GLOBALS['config']['task']['status']['options']).',"default":'.$GLOBALS['config']['task']['status']['default'].'}',
			'archived'			=> 'BOL',
			'member_id'			=> 'NUM', // assigned user
			'author_id'			=> 'NUM', // author of the task
			'public'			=> 'BOL', // set to 1 if shown to all
			// gloc specfific
			'po'				=> 'STR',
			'pid'				=> 'STR',
			'work'				=> 'NUM,{"options":'.json_encode($GLOBALS['config']['task']['work']['options']).',"default":'.$GLOBALS['config']['task']['work']['default'].'}',
			'words'				=> 'DEC,2',
			'rate'				=> 'DEC,3',
			'context'			=> 'NUM,{"options":'.json_encode($GLOBALS['config']['task']['context']['options']).',"default":'.$GLOBALS['config']['task']['context']['default'].'}',
			'invoice_id'		=> 'NUM'
		));
	}
	
	/**
	 * set time
	 */
	public function setTime($val) {
		$tm = '';
		$val = trim($val);
		if (preg_match('/^([0-9]{1,2})([:|\.]([0-9]{1,2}))?([ ]?pm|am)?$/', $val, $arr)) {
			$h = intval($arr[1]);
			if (isset($arr[4])) {
				if (trim($arr[4]) == 'pm') {
					$h += 12;
				}
			}
			$tm = str_pad($h, 2, '0', STR_PAD_LEFT);
			if (isset($arr[3])) {
				$tm .= ':'.str_pad(trim($arr[3]), 2, '0', STR_PAD_LEFT);
			}
		}
		$this->set('deadtime', $tm);
	}
	
	/**
	 * set rate
	 */
	public function setRate($mode, $work, $data, $rate=0) {
		if ($rate) {
			$this->set('rate', $rate);
		} else if (is_array($data) && isset($data[$work]) && $data[$work] > 0) {
			$this->set('rate', $data[$work]);
		} else {
			$this->set('rate', $GLOBALS['config']['task']['work'][$mode][$work]);
		}
	}
	
	/**
	 * set work, words and rate
	 */
	public function setWorkAndRate($mode, $work, $words, $data, $rate=0) {
		
		$this->set('work', $work);
		
		if ($rate) {
			$this->set('rate', $rate);
		} else if ($work == 4) {
			// fixed rate
			$this->set('rate', $words);
		} else {
			// other word or timed based work
			$this->set('words', $words);
			$this->setRate('sell', $work, $data);
		}
	}
	
	/**
	 * check submitted data before saving task
	 */
	public function check($usrId, $pid='') {
		if ($this->isEmpty('begin')) {
			$this->set('begin','9999-00-00');
		}
		if ($this->isEmpty('deadline')) {
			$this->set('deadline','9999-00-00');
		}
		if ($this->isEmpty('author_id')) {
			$this->set('author_id', $usrId);
		}
		if ($this->isEmpty('pid') && $pid) {
			$this->set('pid', $pid);
		}
		return parent::check('title');
	}
	
	/**
	 * parse a single line string for task params
	 * @return a task object
	 */
	public static function parse($str) {
		if (!preg_match('/^(@[^ ]+ )?([+|-][0-9]{0,2}|[0-9]{2}\/[0-9]{2}(\/[0-9]{2,4})?)?( [0-9]{2}:[0-9]{2})?(.+)$/',$str, $arr)) {
			return false;
		}
		ArrayHelper::arrayTrim($arr);
		$obj = new TaskModel();
		
		// assign to user
		if ($user = self::searchUser($arr[1])) {
			$obj->set('member_id', $user['id']);
		}
				
		// date
		$obj->set('deadline', self::parseDate($arr[2], $arr[3]));
		
		// time
		if ($time = trim($arr[4])) {
			$obj->setTime($time);
		}
		
		
		// title
		$title = trim($arr[5], ' "');
		$obj->set('title',$title);
		
		// $obj->set('priority',$prio);
		return $obj;
	}
	
	public function htmlPriority() {
		$arr = $this->getPropertyOptions('priority');
		$st = $this->get('priority');
		return $st.') '.TR::html('priority',$arr['options'][$st]);
	}
	
	public function htmlStatus() {
		$arr = $this->getPropertyOptions('status');
		$str = $this->get('status');
		$str = TR::html('task',$arr['options'][$str]);
		if ($this->get('archived')) {
			$str .= ' ('.TR::html('task','archived').')';
		}
		return $str;
	}
	
	public function htmlDeadline($inlist=true) {
		if ($this->isEmpty('deadline')) {
			return $inlist?'-':'';
		} else {
			$date = $inlist?$this->html('deadline','%d/%m'):$this->html('deadline','%d/%m/%y');
			return $date.' '.$this->html('deadtime');
		}
	}
		
	public function getDeadlineValue() {
		return $this->html('deadline','');
	}
	
	public function htmlRate($sym=false) {
		if ($r = $this->value('rate')) {
			return ($sym?'&euro; ':'').number_format($r, 3);
		} else {
			return '-';
		}
	}
	
	public function getWorkTotal() {
		if (($r = $this->value('rate')) > 0) {
			if (($w = $this->value('words')) > 0) {
				return $r * $w;
			} else {
				return $r;
			}
		} else {
			return 0;
		}
	}
	
	public function htmlTotal($sym=false) {
		if ($r = $this->value('rate')) {
			$m = $this->value('words');
			if (!$m) {
				$m = 1;
			}
			return ($sym?'&euro; ':'').number_format($r * $m,2);
		} else {
			return '-';
		}
	}
	
	public function mailBody($po='', $title='') {
		$str = '<table style="width:600px">'
			.'<colgroup><col style="width:100px;"></col><col></col></colgroup>';
		if ($po || $title) {
			$str .= '<thead>'
				.'<tr><th>'.$po.'</th>'."\n".'<td>'.$title.'</td></tr>'
				.'</thead>'."\n";
		}
		return $str 
			.'<tbody>'
			.'<tr><th style="text-align:left">Deadline : </th><td>'.$this->htmlDeadline(false).'</td></tr>'."\n"
			.'<tr><th style="text-align:left">Work : </th><td>'.$GLOBALS['config']['task']['work']['options'][$this->get('work')].'</td></tr>'."\n"
			.'<tr><th style="text-align:left">Qty : </th><td>'.$this->html('words').'</td></tr>'."\n"
			.'<tr><th style="text-align:left">Rate : </th><td>'.$this->htmlRate().'</td></tr>'."\n"
			.'<tr><th style="text-align:left">Total :</th><td>'.$this->htmlTotal(true).'</td></tr>'."\n"
			.'</tbody></table>'."\n";
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
		$sta = '';
		switch ($action) {
			case 'report':
				$num = intval($_POST['report']);
				if ($num > 0) {
					$sta = 'deadline = DATE_ADD(deadline, INTERVAL '.$num.' DAY)';
				} else {
					$sta = 'deadline = DATE_SUB(deadline, INTERVAL '.abs($num).' DAY)';
				}
				break;
			case 'rtoday':
				$sta = 'deadline = CURDATE()';
				break;
			case 'rtomorrow':
				$sta = 'deadline = DATE_ADD(CURDATE(), INTERVAL 1 DAY)';
				break;
			case 'rdeadline':
				$fc = FrontController::getInstance();
				$dead = VarDte::sani($fc->getReqVar('rdeadline'));
				if (!$dead) {
					return false;
				}
				$sta = "deadline = '".$dead."'";
				break;
			case 'rsleep':
				$sta = "deadline = '9999-00-00'";
				break;
			case '0': // reopen
			case '3':
			case '4':
			case '5':
			case '6':
				$sta = 'status='.$action;
				break;
			case '1': // done
				$filter = 'id IN ('.implode(',',$arr).')';
				// mark invoicable tasks as done
				DbConnector::query('UPDATE task SET status=1 WHERE '.$filter.' AND context > 0');
				// archive memo tasks
				DbConnector::query('UPDATE task SET archived=1 WHERE '.$filter.' AND context = 0');
				break;
			case '2':
				// invoice
				$invId = 0;
				$obj = new InvoiceModel();
				$obj->connectDb();
				if (!empty($_POST['selinvoice'])) {
					FC::log_debug('ADD to invoice : '.$_POST['selinvoice']);
					$invId = intval($_POST['selinvoice']);
					$obj->setUid($invId);
					$obj->load();
				} else if ($_POST['newinvoice']) {
					FC::log_debug('CREATE invoice : '.$_POST['newinvoice']);
					$obj->set('code', $_POST['newinvoice']);
					if ($obj->check()) {
						$obj->insert();
					}
					$invId = $obj->getUid();
				}
				if ($invId) {
					DbConnector::query('UPDATE task SET status='.$action.', invoice_id='.$invId.' WHERE id IN ('.implode(',',$arr).')');
					$obj->updateData(true);
				}
				break;
			case 'archive':
				$sta = 'archived=1';
				break;
			case 'unarchive':
				$sta = 'archived=0';
				break;
		}
		if ($sta) {
			$filter = 'id IN ('.implode(',',$arr).')';
			FC::log_debug('-> UPDATE task SET '.$sta.' WHERE '.$filter);
			DbConnector::query('UPDATE task SET '.$sta.' WHERE '.$filter);
			return true;
		}
		return false;
	}
	
	/**
	 * Search user from @string
	 */
	public static function searchUser($user) {
		$user = trim($user,' @');
		if ($user) {
			// search for user to send task to
			$objUser = new MemberCompanyModel();
			$objUser->connectDb();
			$user = str_replace("'","''", $user);
			if ($objUser->load("(username='$user' OR nickname='$user' OR email='$user') AND hidden=0")) {
				return array(
					'id'	=>	$objUser->getUid(),
					'name'	=>	$objUser->getLabel(),
					1		=>	$objUser->value('rate_translate'),
					2		=>	$objUser->value('rate_review'),
					3		=>	$objUser->value('rate_hourly')
				);
			}
		}
		return false;
	}
	
	/**
	 * Parse date
	 */
	public static function parseDate($date, $year='') {
		$date = trim($date);
		if (empty($date)) {
			return '9999-00-00';
		} else if (preg_match('/^[+|-]/', $date)) {
			return $date.' days';
		} else {
			$year = trim($year);
			if ($year) {
				$date .= $year;
			}
			return $date;
			// return date_format(new DateTime('now', $GLOBALS['config']['datetime']['timezone_user']), 'Y-m-d');
		}
	}
}

/**
 * GLOC specific class and methods
 */
class TaskSummary extends TaskModel {

	public function __construct() {
		parent::__construct();
		$this->removeProperties('member_id');
		$this->addProperties(array(
			'member' 	=> 'OBJ',
			'company'	=> 'OBJ'
		));
	}
	
	public function getSummary() {
		$stt = 'Other';
		switch ($this->get('work')) {
			case '1':
				$stt = 'Translation';
				break;
			case '2':
				$stt = 'Revision';
				break;
			case '3':
				$stt = 'Hour rate';
				break;
			case '4':
				$stt = 'Fixed';
				break;
			default:
				break;
		}
		$dtt = '';
		if (!$this->isEmpty('deadline')) {
			$dtt = ' ('.$this->html('deadline','%d/%m/%y').')';
		}
		return 'PO'.$this->get('po').' - '.$this->get('title').' - '.$stt.$dtt;
	}
	
	public function getBillingAddress() {
		$adr = '';
		$mc = new MemberCompanyModel();
		$mc->connectDb();
		$mc->setUid($this->get('member')->getUid());
		$mc->load();
		$co = $mc->get('company');
		if ($co->getUid()) {
			$adr = $co->get('address');
		}
		if ($adr) {
			$adr = $co->get('name')."\n".$adr;
		} else {
			$adr = $mc->get('address');
		}
		return $adr;
	}
	
	public function htmlCompany() {
		return $this->get('company')->html('name');
	}
	
	public function htmlWorkPo() {
		$po = $this->value('po');
		if ($po != '' && $po != '0') {
			return $this->html('po');
		} else {
			return '-';
		}
	}
	
	public function getWork() {
		$w = intval($this->get('work'));
		return $GLOBALS['config']['task']['work']['options'][$w];
	}
	
	public function htmlWorkSpec() {
		$str = $stt = '';
		if ($this->value('words') > 0) {
			$str = '&nbsp;'.$this->html('words');
		}
		switch ($this->get('work')) {
			case '1':
				$stt = 'T';
				break;
			case '2':
				$stt = 'R';
				break;
			case '3':
				$stt = 'M';
				break;
			default:
				$stt = 'O';
				break;
		}
		return '<span class="label">'.$stt.'</span>'.$str;
	}
	
	public function htmlWorkRate() {
		if ($this->value('rate') > 0) {
			return $this->html('rate');
		} else {
			return '-';
		}
	}
	
	public function htmlWorkTotal() {
		if ($r = $this->getWorkTotal()) {
			return $r;
		} else {
			return '-';
		}
	}
	
	public function getContext() {
		switch ($this->value('context')) {
		case 0:
			return 'memo';
		case 1:
			return 'client';
		case 2:
			return 'vendor';
		default:
			return 'invoice';
		}
	}
	
	public function htmlContext() {
		switch ($this->value('context')) {
		case 0:
			return '<span class="label label-inverse">memo</span>';
		case 1:
			return '<span class="label label-info">client</span>';
		case 2:
			return '<span class="label label-warning">vendor</span>';
		default:
			return '<span class="label">invoice</span>';
		}
	}
		
	public function chkDeadline() {
		if ($this->isEmpty('deadline')) {
			$this->_diff = 9999;
		} else {
			$dead = strtotime($this->get('deadline'));
			// -TODO- optimize ! maybe using DateTime diff ?
			$usernow = date_timestamp_get(new DateTime('now', $GLOBALS['config']['datetime']['timezone_user']));
			$this->_diff = ceil(($dead - $usernow) / 3600 / 24);
		}
		return $this->_diff;	
	}
	
	public function getEditUrl() {
		if ($pid = $this->get('pid')) {
			return APP_WWW_URI.'task/project/edit/'.$pid.'.html';
		} else {
			return APP_WWW_URI.'task/edit/'.$this->getUid().'.html';
		}
	}
	
	public function isOpened($user_id) {
		// -TODO- if no "validate" option, do not allow on closed tasks
		return ($this->get('status') < 2 && (!$this->get('archived')) && ($this->get('member_id') == $user_id));
	}
	
	public function curCss($default='') {
		$arr = array();
		if ($this->_diff < 0) {
            $arr[] = 'overdue';
		} else if ($this->_diff == 0) {
			$arr[] = 'today';
		} else {
			$arr[] = 'future';
		}
		if ($default) {
			$arr[] = $default;
		}
		if (count($arr)) {
			return ' class="'.implode(' ',$arr).'"';
		} else {
			return '';
		}
	}
	
	public function htmlDate() {
		$str = $this->html('end_date',APP_DATE_FRM,'no_date');
		if ($css = $this->curCss()) {
			return '<span'.$css.'>'.$str.'</span>';
		} else {
			return $str;
		}
	}
	
	public static function htmlTime($spent, $stopped=true) {
		if (empty($spent)) {
			if ($stopped) {
				return '--:--';
			} else {
				return TR::html('task','running');
			}
		}
		$h = floor($spent / 60);
		$m = $spent - ($h*60);
		return str_pad($h, 2, '0',STR_PAD_LEFT).':'.str_pad($m, 2, '0',STR_PAD_LEFT);
	}
	
	/**
	 * export data in array for the mobile version (ajax requests)
	 */
	public function exportData($method='html') {
		// prepare general info
		$arrInfo = array();
		$arr = $this->getFields();
		foreach ($arr as $key => $type) {
			$arrInfo[$key] = $this->$method($key);
		}
		
		// prepare timer history and totals
		$total = 0;
		$arrSpent = array();
		if ($this->get('spent')) {
			do {
				$total += $this->get('spent');
				// start and stop times
				$times = $this->htmlTimes();
				$spent = $this->getTimeSpent();
				$arrSpent[$times] = $spent;
			} while ($this->next());
		}
		
		$arrInfo['total'] = $this->htmlTime($total);
		
		return array('info' => $arrInfo, 'spent' => $arrSpent);
		// return $arrInfo;
	}
	
	/**
	* update current task status
	*/
	public function updateStatus($status) {
		$this->connectDb();
		$this->set('status',$status);
		$this->fields('status');
		return parent::update();
	}
	
	/**
	 * override load function
	 */
	public function load($filter='') {
		$this->select('id, title, begin, deadline, start, stop, status, public, archived, '
			.'CEIL(spent/60) as spent');
		$this->from('task');
		$this->leftJoin('timer','task.id=timer.task_id');
		return parent::load($filter, false);
	}
	
	/**
	 * override loadlist function
	 */
	public function loadList($filter='') {
		$this->select('task.id, `task`.`title`, `task`.`note`, `task`.`begin`, `task`.`deadline`, `task`.`deadtime`, '
			.'`task`.`status`, `task`.`archived`, `task`.`author_id`, `task`.`public`, `task`.`context`,'
			.'`task`.`po`, `pid`, `task`.`work`, `task`.`words`, `task`.`rate`,'
			.'`member`.`id` AS member__id, `member`.`username` AS member__username, `member`.`time_zone` AS member__time_zone, '
			.'`member`.`nickname` AS member__nickname,`member`.`email` AS member__email,'
			.'`company`.`id` AS company__id, `company`.`name` AS company__name');
		$this->from('task');
		$this->leftJoin('member','task.member_id=member.id');
		$this->leftJoin('company','member.company_id=company.id');
		parent::loadList(false);
	}
	
	/**
	 * load current running timer
	 */
	public static function loadCurrent($id=0) {
		$obj = new TaskSummary();
		$obj->connectDb();
		if ($id) {
			$obj->setUid($id);
			if ($obj->load()) {
				return $obj;
			}
		} else {
			$ftr = "stop='0000-00-00 00:00:00'";
			if (!empty($_SESSION['appUserId'])) {
				$ftr .= " AND member_id='".$_SESSION['appUserId']."'";
			}
			if ($obj->load($ftr)) {
				return $obj;
			}
		}
		return false;
	}

	public function loadCompactList() {
	/*
		SELECT task.*, MIN(start) as start, MAX(stop) as stop, SUM(CEIL(spent/60)) as spent
		FROM `task` 
		LEFT JOIN timer ON task.id = timer.task_id
		WHERE status < 2
		GROUP BY id
	*/
		$this->select('task.*, MIN(start) as start, MAX(stop) as stop, '
			.'SUM(CEIL(spent/60)) as spent, COUNT(timer.task_id) AS timers');
		$this->from('task');
		$this->leftJoin('timer','task.id=timer.task_id');
		$this->groupBy('id');
		return parent::loadList(false);
	}
	
	public function loadExpandList() {
		$this->select('task.*, start, stop, CEIL(spent/60) as spent');
		$this->from('task');
		$this->leftJoin('timer','task.id=timer.task_id');
		return parent::loadList(false);
	}
	
}

/**
 * TaskFull
 */
class TaskFull extends TaskSummary
{

	function __construct() {
		parent::__construct();
		$this->removeProperties('invoice_id');
		$this->addProperties(array(
			'invoice'	=> 'OBJ'
		));
	}
	
	/**
	 * orverride loadlist function
	 */
	public function loadList($filter='') {
		$this->select('task.id, `task`.`title`, `task`.`note`, `task`.`begin`, `task`.`deadline`, `task`.`deadtime`, '
			.'`task`.`status`, `task`.`archived`, `task`.`author_id`, `task`.`context`,'
			.'`task`.`po`, `pid`, `task`.`work`, `task`.`words`, `task`.`rate`,'
			.'`member`.`id` AS member__id, `member`.`username` AS member__username, `member`.`time_zone` AS member__time_zone, '
			.'`member`.`nickname` AS member__nickname,`member`.`email` AS member__email,'
			.'`company`.`id` AS company__id, `company`.`name` AS company__name,'
			.'`invoice`.`id` AS invoice__id, `invoice`.`code` AS invoice__code, `invoice`.`amount` AS invoice__amount, '
			.'`invoice`.`invdate` AS invoice__invdate, `invoice`.`duedate` AS invoice__duedate, `invoice`.`paydate` AS invoice__paydate'
		);
		$this->from('task');
		$this->leftJoin('member','task.member_id=member.id');
		$this->leftJoin('invoice','task.invoice_id=invoice.id');
		$this->leftJoin('company','member.company_id=company.id');
		TaskModel::loadList(false);
	}

}


/**
 * Project
 * 
 * Class representing a project (related tasks)
 * @since 0.1
 */
class Project {

	public $pid;
	public $po;
	public $title;
	public $client;
	public $ctasks;
	public $vtasks;

	public function __construct() {
		$this->ctasks = array();
		$this->vtasks = array();
	}
	
	public function isLoaded() {
		if (!count($this->ctasks)) {
			return false;
		}
		//error_log('isloaded : '.$this->ctasks[0]->getUid());
		return $this->ctasks[0]->getUid()?true:false;
	}
	
	public function getTaskIds() {
		$arr = array();
		foreach ($this->ctasks as $idx => $obj) {
			if ($id = $obj->getUid()) {
				$arr[] = $id;
			}
		}
		foreach ($this->vtasks as $idx => $obj) {
			if ($id = $obj->getUid()) {
				$arr[] = $id;
			}
		}
		return $arr;
	}
	
	
	/**
	 * parse a multi line string for task params
	 * @return a project object
	 */
	public static function parse($txt) {
	
		$project = new Project();
	
		$arp = explode("\n", trim($txt));
		
		// parse first line : defines project
		// @client 31/12 12:00 "project title" PO1234 T1000 R1000
		$str = array_shift($arp);
		
		// title
		$title = preg_match('/("[^"]+")/', $str, $arr);
		if (!$title) {
			return $project;
		}
		$project->title = trim($arr[1],'"');
		$str = preg_replace('/("[^"]+")[ ]?/', '', $str);
		
		// parse the rest
		
		$art = array(); // tasks defined by client
		$arc = explode(' ',trim($str, "\n\r "));
		
		$cc = 0;
		$date = $time = '';
		
		foreach ($arc as $stc) {
		
			$stc = trim($stc);
			
			// 1. client
			if (preg_match('/^@/', $stc)) {
				$project->client = TaskModel::searchUser($stc);
				continue;
			}
			
			// 2. date
			if (preg_match('/^([+|-][0-9]{0,2}|[0-9]{2}\/[0-9]{2}(\/[0-9]{2,4})?)$/', $stc, $arr)) {
				$date = TaskModel::parseDate($arr[1], isset($arr[2])?$arr[2]:'');
				continue;
			}
			
			// 3. time
			if (preg_match('/^([0-9]{1,2})([:|\.]([0-9]{1,2}))?([ ]?pm|am)?$/', $stc)) {
				$time = $stc;
				continue;
			}
			
			// PO
			if (preg_match('/^PO/i', $stc)) {
				$project->po = trim($stc, ' POpo:');
				continue;
			}
			
			// tasks 
			if (preg_match('/^([TRFH]):?([0-9\.]+)$/', $stc, $arr)) {
				$art[$cc] = array(
					0	=> $arr[1],
					1	=> $arr[2]
				);
				$cc++;
				continue;
			}
			
		}
		
		// CREATE CLIENT TASKS
		$tc = 0;
		if ($cc) {
			foreach ($art as $arr) {
				$obj = new TaskModel();
				
				// set defaults
				$obj->set('po', $project->po);
				$obj->set('title', $project->title);
				$obj->set('deadline', $date);
				$obj->setTime($time);
				
				// create client task
				if ($project->client) {
					$obj->set('member_id', $project->client['id']);
				}
								
				// set task work
				// 'W='.$work.' - '.$GLOBALS['config']['task']['work']['short'][$work];
				$work = $arr[0];
				$work = $GLOBALS['config']['task']['work']['short'][$work];
				
				// set work, words and rate
				$obj->setWorkAndRate('sell', $work, $arr[1], $project->client);
				
				$obj->set('context', 1); // client
				
				$project->ctasks[$tc] = clone($obj);
				$tc++;
			}
		}
		
		// CREATE VENDOR TASKS
		
		$cv = count($arp);
		$tv = 0;
		
		// if no vendor task defined, use client task definition
		if ($cv < $tc) {
			$cv = $tc;
		} else if (!$tc) {
			// check if tasks multi defined by vendor line
			$arn = array();
			foreach ($arp as $str) {
				if (preg_match_all('/([TRFH]):?([0-9\.]+)/', $str, $arm)) {
					if (count($arm[0]) > 1) {
						$stb = preg_replace('/([TRFH]):?([0-9\.]+)/', '', $str);
						foreach ($arm[0] as $stm) {
							$arn[] = $stb.' '.$stm;
						}
					} else {
						$arn[] = $str;
					}
				} else {
					$arn[] = $str;
				}
				
			}
			$arp = $arn;
			$cv = count($arp);
		}
		
		// load default user
		$fc = FC::getInstance();
		$defuser = TaskModel::searchUser('@'.$fc->user->get('username'));
		
		// check each task
		for ($i = 0; $i < $cv; $i++) {
			
			$obj = new TaskModel();
			
			// set defaults
			$obj->set('po', $project->po);
			$obj->set('title', $project->title);
			$obj->set('deadline', $date);
			$obj->setTime($time);
			
			$work = 'O';
			$words = 0;
			$rate = 0;
			if (isset($art[$i])) {
				$work = $art[$i][0];
				$words = $art[$i][1];
			}
			
			$c = $v = $vendor = $vdate = $vtime = $multi = 0;
			
			// parse vendor line
			// @user 22/12 18:00 R1000 E.12
			if ($str = array_shift($arp)) {
			
				$arc = explode(' ',trim($str, "\n\r "));
				
				foreach ($arc as $stc) {
				
					$stc = trim($stc, "\n\r ");
					
					// 1. vendor
					if (preg_match('/^@/', $stc)) {
						$vendor = TaskModel::searchUser($stc);
						continue;
					}
					
					// 2. date
					if (preg_match('/([+|-][0-9]{0,2}|[0-9]{2}\/[0-9]{2}(\/[0-9]{2,4})?)/', $stc, $arr)) {
						$vdate = TaskModel::parseDate($arr[1], isset($arr[2])?$arr[2]:'');
						continue;
					}
					
					// 3. time
					if (preg_match('/^([0-9]{1,2})([:|\.]([0-9]{1,2}))?([ ]?pm|am)?$/', $stc)) {
						$vtime = $stc;
						continue;
					}
					
					// tasks 
					if (preg_match('/^([TRFH]):?([0-9\.]+)$/', $stc, $arr)) {
						if ($multi) {
							// line already parsed, use again for different task
							$str = preg_replace('/'.$stc.'/', '', $str);
							array_unshift($arp, $str);
							break;
						} else {
							$work	= $arr[1];
							$words	= $arr[2];
							$multi = $stc;
						}
						continue;
					}
					
					// rate
					if (preg_match('/^E:?([0-9\.]+)$/', $stc)) {
						$rate = floatval(trim($str,'E: '));
						continue;
					}
					
				}
			}
			
			// vendor
			if (!$vendor) {
				$vendor = $defuser;
			}
			
			// set task work
			// 'W='.$work.' - '.$GLOBALS['config']['task']['work']['short'][$work];
			$work = $GLOBALS['config']['task']['work']['short'][$work];
			
			// create client task if needed
			if (!$tc) {
				if ($project->client) {
					$obj->set('member_id', $project->client['id']);
				}
				if (!$date && $vdate) {
					$obj->set('deadline', $vdate);
				}
				if (!$time && $vtime) {
					$obj->setTime($vtime);
				}
				$obj->setWorkAndRate('sell', $work, $words, $project->client);
				$obj->set('context', 1); // client
				
				$project->ctasks[$tv] = clone($obj);
			}
			
			// parse vendor info
			if ($vendor) {
				$obj->set('member_id', $vendor['id']);
			}
			if ($vdate) {
				$obj->set('deadline', $vdate);
				$obj->set('deadtime', '');
			}
			if ($vtime) {
				$obj->setTime($vtime);
			}
			
			$obj->setWorkAndRate('buy', $work, $words, $vendor, $rate);
			
			$obj->set('context', 2); // vendor
			
			// create vendor task
			$project->vtasks[$tv] = $obj;
			$tv++;
			
		} // for each task
		
		return $project;
	}
	
	public function setDetails($data, $pid='') {
	
		$error = false;
	
		if (isset($data['pid'])) {
			$this->pid = $data['pid'];
		} else if ($pid) {
			$this->pid = $pid;
		}
		$this->po = $data['po'];
		$this->title = $data['title'];
		$this->client = array('id' => intval($data['clientmr']));
		
		// set client tasks
		foreach ($data['cowork'] as $idx => $work) {
			$obj = new TaskModel();
			if (isset($data['coid'][$idx])) {
				$obj->setUid($data['coid'][$idx]);
				$obj->connectDb();
				$obj->load();
			}
			$obj->set('pid', $this->pid);
			$obj->set('po', $this->po);
			$obj->set('title', $this->title);
			$obj->set('deadline', $data['codeadline'][$idx]);
			$obj->setTime($data['codeadtime'][$idx]);
			$obj->set('work',  $work);
			$obj->set('words',  $data['cowords'][$idx]);
			$obj->set('rate',  $data['corate'][$idx]);
			$obj->set('member_id', $this->client['id']);
			if (isset($data['status'])) {
				if (strlen($data['status'])) {
					$obj->set('status', $data['status']);
				}
				$obj->set('archived', isset($data['archived'])?1:0);
			}
			$obj->set('context',1);
			if ($obj->isEmpty('member_id')) {
				$error = true;
			}
			$this->ctasks[$idx] = $obj;
		}
		
		// set vendor tasks
		if (isset($data['work'])) {
			foreach ($data['work'] as $idx => $work) {
				$obj = new TaskModel();
				if (isset($data['vid'][$idx])) {
					$obj->setUid($data['vid'][$idx]);
					$obj->connectDb();
					$obj->load();
				}
				$obj->set('po', $this->po);
				$obj->set('title', $this->title);
				$obj->set('deadline', $data['deadline'][$idx]);
				$obj->setTime($data['deadtime'][$idx]);
				$obj->set('work',  $work);
				$obj->set('words',  $data['words'][$idx]);
				$obj->set('rate',  $data['rate'][$idx]);
				$obj->set('member_id', $data['member'][$idx]);
				if (isset($data['status'])) {
					if (strlen($data['status'])) {
						$obj->set('status', $data['status']);
					}
					$obj->set('archived', isset($data['archived'])?1:0);
				}
				$obj->set('context',2);
				if ($obj->isEmpty('member_id')) {
					$error = true;
				}
				$this->vtasks[$idx] = $obj;
			}
		}
		
		return !$error;
	}
	
	/**
	 * Add new client task
	 */
	public function addClientTask() {
		$i = count($this->ctasks);
		$obj = new TaskModel();
		$obj->set('pid', $this->pid);
		$obj->set('po', $this->po);
		$obj->set('title', $this->title);
		$obj->set('member_id', $this->client);
		$this->ctasks[$i] = $obj;
	}
	
	/**
	 * Remove client task
	 */
	public function delClientTask($idx) {
		if (!isset($this->ctasks[$idx])) {
			return false;
		}
		unset($this->ctasks[$idx]);
	}
	
	/**
	 * Add new client task
	 */
	public function addVendorTask($uid) {
		$i = count($this->vtasks);
		$obj = new TaskModel();
		$obj->set('pid', $this->pid);
		$obj->set('po', $this->po);
		$obj->set('title', $this->title);
		$obj->set('member_id', $uid);
		$this->vtasks[$i] = $obj;
	}
	
	/**
	 * Remove vendor task
	 */
	public function delVendorTask($idx) {
		if (!isset($this->vtasks[$idx])) {
			return false;
		}
		unset($this->vtasks[$idx]);
	}
	
	/**
	 * load client and vendor tasks
	 */
	public function loadTasks($pid) {
	
		$this->ctasks = array();
		$this->vtasks = array();
		
		$ic = $iv = 0;
	
		$obj = new TaskModel();
		$obj->connectDb();
		$obj->where("pid='$pid'");
		$obj->orderBy('context');
		if ($obj->loadList()) {
			while ($obj->next()) {
				switch($obj->get('context')) {
				case 1:
					$this->ctasks[$ic] = $obj->cloneData();
					$ic++;
					if (!$this->pid) {
						$this->pid = $obj->get('pid');
						$this->po = $obj->get('po');
						$this->title = $obj->get('title');
						$this->client = array('id' => $obj->get('member_id'));
					}
					break;
				case 2:
					$this->vtasks[$iv] = $obj->cloneData();
					$iv++;
					break;
				}
			}
		}
		
		
	}
	
	public function check($uid) {
		if (!$this->pid) {
			$this->pid = strtolower(StringHelper::genRandom(16));
		}
		$chk = true;
		if (count($this->ctasks) && count($this->vtasks)) {
			// save client tasks
			foreach ($this->ctasks as $i => $obj) {
				if (!$obj->check($uid, $this->pid)) {
					$chk = false;
					//error_log(" > client #$i ERROR");
				} else {
					//error_log(" > client #$i OK");
				}
			}
			// save vendor tasks
			foreach ($this->vtasks as $i => $obj) {
				if (!$obj->check($uid, $this->pid)) {
					$chk = false;
					//error_log(" > vendor #$i ERROR");
				} else {
					//error_log(" > vendor #$i OK");
				}
			}
			return $chk;
		}
		return false;
	}
	
	public function save() {
	
		$arrOldies = $arrNewbies = array();
		if ($this->isLoaded()) {
			$old = new Project();
			$old->loadTasks($this->pid);
			$arrOldies = $old->getTaskIds();
		}
	
		if (count($this->ctasks) && count($this->vtasks)) {
		
			// save client tasks
			foreach ($this->ctasks as $i => $obj) {
				$obj->connectDb();
				$obj->save();
				$arrNewbies[] = $obj->getUid();
				//error_log(" > client #$i OK");
			}
			
			// save vendor tasks
			foreach ($this->vtasks as $i => $obj) {
				$obj->connectDb();
				$obj->save();
				$id = $obj->getUid();
				$arrNewbies[] = $obj->getUid();
				//error_log(" > vendor #$i OK");
			}
			
			// delete removed tasks
			$arrOldies = array_diff($arrOldies, $arrNewbies);
			$arrInvoices = array();
			
			if (count($arrOldies)) {
				$obj = new TaskModel();
				$obj->connectDb();
				// check if part of invoice
				foreach ($arrOldies as $did) {
					$obj->setUid($did);
					if ($obj->load()) {
						if ($iid = $obj->get('invoice_id')) {
							if (!in_array($iid, $arrInvoices)) {
								$arrInvoices[] = $iid;
							}
						}
					}
				}
		
				// remove from project
				$obj->delete("id IN (".implode(',',$arrOldies).")");
				
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
			return true;
		}
		return false;
	}
	
}
