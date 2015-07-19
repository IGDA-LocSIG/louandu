<?php
/**
 * Tzn Framework
 * 
 * @package tzn_helpers
 * @author Stan Ozier <framework@tirzen.com>
 * @version 0.5
 * @since 0.5
 * @copyright GNU Lesser General Public License (LGPL) version 3
 */

/**
 * CalendarHelper
 * 
 * Display calendar
 */
class CalendarHelper extends Helper {

	protected $fc;
	protected $mode;
	protected $today;
	protected $selected;
	protected $incDay;
	protected $curDay;
	protected $curMonth;
	protected $curYear;
	protected $selDay;
	protected $selMonth;
	protected $selYear;
	protected $week;
	protected $firstDay;
	protected $lastDay;
	protected $events;
	
	public function __construct($obj) {
		parent::__construct($obj);
		// $this->today = $this->incDay = new DateTime();
		$this->events = array();
		$this->today = new DateTime('today');
		$this->fc = FC::getInstance();
	}
		
	public function initCal($date=null, $mode='month', $slidestart=0) {
		// error_log('init to date '.$date);
		if (empty($date) || (!checkdate(substr($date,5,2), substr($date,8), substr($date,0,4)))) {
			$this->selected = $this->today;
		} else {
			$this->selected = new DateTime($date);
		}
		
		$this->curDay = intval($this->today->format('j'));
		$this->curMonth = intval($this->today->format('n'));
		$this->curYear = intval($this->today->format('Y'));
		
		$this->selDay = intval($this->selected->format('j'));
		$this->selMonth = intval($this->selected->format('n'));
		$this->selYear = intval($this->selected->format('Y'));
		
		// error_log('-> selected date : '.$this->selected->format('j/n/Y'));
		
		switch ($mode) {
		case 'month':
			// current month
			$this->mode = 'month';
			$last = cal_days_in_month(CAL_GREGORIAN, $this->selMonth, $this->selYear);
			$this->incDay = new DateTime($this->selected->format('Y-m-01'));
			$this->lastDay = new DateTime($this->selYear.'-'.$this->selMonth.'-'.($last<10?'0':'').$last);
			break;
		default:
			// sliding calendar (following weeks)
			$this->mode = intval($mode);
			if (!$this->mode) {
				$this->mode = 1; // show week
			}
			$this->incDay = clone($this->selected);
			
			// slide
			if ($slidestart > 0) {
				$this->incDay->add(new DateInterval('P'.$slidestart.'W'));
			} else if ($slidestart < 0) {
				$this->incDay->sub(new DateInterval('P'.abs($slidestart).'W'));
			}
			
			break;
		}
		
		// get first day of the week
		$this->weekDay = clone($this->incDay);
		// skip till monday
		$this->skip = 0;
		while ($this->weekDay->format('w') != 1) { // -TODO- this is a monday
			$this->weekDay->modify('-1 day');
			$this->skip++;
		}
		
		if (is_int($this->mode)) {
			$this->incDay = clone($this->weekDay);
			$this->skip = 0;
			$this->lastDay = clone($this->incDay);
			$this->lastDay->modify('+'.(($this->mode*7)-1).' days');
		}
		
		// $this->firstDay = intval($this->incDay->format('w'));
	}
	
	public function firstDate() {
		return $this->incDay->format('Y-m-d');
	}
	
	public function lastDate() {
		return $this->lastDay->format('Y-m-d');
	}
	
	public function initEvents($date, $title) {
		while ($this->obj->next()) {
			/*
			$day = intval(substr($this->obj->get($date),8));
			$this->events[$day][] = $this->obj->html($title);
			*/
			$day = $this->obj->get($date);
			$this->events[$day][] = CalendarEvent::factory($this->obj->html($title), $this->obj->getUid());
			
		}
	}
	
	public function getCal($link='') {
		switch ($this->mode) {
		case 'month':
			return $this->getMonthCal($link);
		default:
			return $this->getSlideCal($link);
		}
	}
	
	protected function getSlideCal($link='') {
		// header : navigation
		$str = '<table class="calendar">'
			.'<thead>'
				.'<tr>';
				
		// header : display week days
		for ($i = 0; $i < 7; $i++) {
			$str .= '<td>'.$this->weekDay->format('D').'</td>';
			$this->weekDay->modify('+1 day');
		}
		$str .=	'</tr>'
			.'</thead>';
			
		// actual cal
		$str .= $this->getBodyCal($link='');
		
		$str .= '</table>';
		return $str;
	}
	
	protected function getMonthCal($link='') {
		// header : navigation
		$str = '<table class="calendar">'
			.'<thead>'
				.'<tr>'
					.'<th><a href="#">&lt;</a></th>'
					.'<th colspan="5">'.$this->incDay->format('F Y').'</th>'
					.'<th><a href="#">&gt;</a></th>'
				.'</tr>'
				.'<tr>';
				
		// header : display week days
		for ($i = 0; $i < 7; $i++) {
			$str .= '<td>'.$this->weekDay->format('D').'</td>';
			$this->weekDay->modify('+1 day');
		}
		$str .=	'</tr>'
			.'</thead>';
			
		// actual cal
		$str .= $this->getBodyCal($link='');
		
		$str .= '</table>';
		return $str;
	}
	
	protected function getBodyCal($link='') {
		$d = 0;
		$str = '<tbody>'
			.'<tr>';
			
		// skip first days
		$j = 0;
		for ($i=$this->skip; $i>0; $i--) {
			$str .= '<td>&nbsp;</td>';
			$j++;
		}
		
		while (1) {
		
			if ($d) {
				$this->incDay->modify('+1 day');
			}
			
			$this->incMonth = intval($this->incDay->format('n'));
			$this->incYear = intval($this->incDay->format('Y'));
		
			$days = $this->lastDay->diff($this->incDay)->days;
			$d = $this->incDay->format('j');
			
		
			// classes
			$cl = array();
			if ($d == $this->selDay && $this->selMonth == $this->incMonth && $this->selYear == $this->incYear) {
				$cl[] = 'calsel';
			}
			if ($d == $this->curDay && $this->curMonth == $this->incMonth && $this->curYear == $this->incYear) {
				$cl[] = 'caltoday';
			}
			if ($j >= 5) {
				$cl[] = 'calwe';
			}
			if (count($cl)) {
				$cl = ' class="'.implode(' ',$cl).'"';
			} else {
				$cl = '';
			}
		
			// --- SHOW DAY ---
			$str .= '<td'.$cl.'><a href="#" rel="'.$this->incDay->format('d/m/Y').'">';
			switch ($this->mode) {
			case 'month':
				$str .= $d;
				break;
			default:
				$str .= $this->incDay->format('M d');
				break;
			}
			// $str .= ' ('.$days.')';
			$str .= '</a>';
			// display events
			$dd = $this->incDay->format('Y-m-d');
			if (array_key_exists($dd, $this->events)) {
				foreach ($this->events[$dd] as $event) {
					$str .= '<div title="'.$event->title.'" class="caltip ';
					if ($event->id == $this->fc->request['id']) {
						$str .= ' calthis';
					}
					$str .= '">'.$event->title.'</div>';
				}
			}
			// close day
			$str .= '</td>';
			
			$j++;
			
			if ($days <= 0) {
				break;
			}
			
			// new row when needed
			if ($j == 7) {
				$str .= '</tr><tr>';
				$j = 0;
			}
			
			
		}
		
		// complete calendar last row
		for ($i=$j; $i<7; $i++) {
			$str .= '<td>&nbsp;</td>';
		}
		$str .= '</tr></tbody>';
		return $str;
	}
	
}

class CalendarEvent {

	public $title;
	public $id;

	public function __construct() {
	}
	
	public static function factory($title, $id=0) {
		$obj = new CalendarEvent();
		$obj->title = $title;
		$obj->id = $id;
		return $obj;
	}
}