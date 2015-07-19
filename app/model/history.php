<?php

class HistoryModel extends Model {

	public function __construct() {
		parent::__construct('history');
		$this->addProperties(array(
			'parachute_id'		=> 'NUM',
			'log_date'			=> 'DTM',
			'nb_sauts'			=> 'NUM',
			'cumul_sauts'		=> 'NUM',
			'note'				=> 'BBS',
			'status'			=> 'NUM,'.json_encode($GLOBALS['config']['para']['status'])
		));
	}
	
	/**
	 * check submitted data before saving task
	 */
	public function check() {
		if ($this->isEmpty('log_date')) {
			$this->set('log_date','NOW');
		}
		return parent::check('parachute_id');
	}
	
	public function htmlStatus() {
		$obj = new ParaModel('');
		$obj->set('status', $this->get('status'));
		return $obj->htmlStatus();
	}
	
	public static function selectNotePreset() {
		$obj = new HtmlFormHelper();
		return $obj->iSelect('note', $GLOBALS['config']['para']['notes']);
	}
	
}

class HistoryFull extends HistoryModel {

	public function __construct() {
		parent::__construct();
		$this->removeProperties('parachute_id');
		$this->addProperties(array(
			'parachute'		=> 'OBJ'
		));
	}
}