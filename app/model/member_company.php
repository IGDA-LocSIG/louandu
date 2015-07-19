<?php
/**
 * TaskFreak! Time Tracker
 * 
 * @package 
 * @author Stan Ozier <taskfreak@gmail.com>, Hervé Renault <rv@tirzen.com>
 * @version 
 * @copyright GNU General Public License (GPL) version 3
 */
 
/**
 * MemberCompanyModel
 * @since 
 */
class MemberCompanyModel extends UserModel {

	public function __construct() {
		parent::__construct('member');
		$this->addProperties(array(
			'nickname'			=> 'STR',
			'email'				=> 'EML',
			'address'			=> 'STR',
			'company' 			=> 'OBJ',
			'class'				=> 'STR,'.json_encode($GLOBALS['config']['users']['class']),
			'rate_translate' 	=> 'DEC,%.3f',
			'rate_review' 		=> 'DEC,%.3f',
			'rate_hourly'		=> 'DEC,%.3f',
			'payterms'			=> 'NUM,'.json_encode($GLOBALS['config']['users']['invoice']),
			'hidden'			=> 'BOL'
		));
	}
	
	public function getLabel() {
		return $this->value('nickname');
	}
		
	public function check() {
		$id = $this->get('id'); // an existing user ?
		$username = $this->get('username');
		$password = $this->get('password');
		if ($id && !$password) {
			return parent::check('nickname,username,email');
		}
		// check AND encode password before saving to the db
		if ($password && !$this->setPassword($password)) {
			return false;
		}
		// new user, check if username already exists
		if (!$id && $username && !$this->setLogin($username)) {
			return false;
		}
		return parent::check('nickname,username,email');
	}
	
	public static function getCompanyId($mid) {
		if (isset($this) && is_object($this)) {
			if ($this->getUid()) {
				return intval($this->get('company')->getUid());
			}
		}
		$obj = new MemberCompanyModel();
		$obj->connectDb();
		$obj->setUid($mid);
		if ($obj->load()) {
			return intval($obj->get('company')->getUid());
		}
		// echo 'Nothing ';
		return 0;
	}
	
	public static function genJavascript() {
		$obj = new MemberCompanyModel();
		$obj->connectDb();
		$obj->orderBy('company.name ASC, member.nickname ASC');
		$obj->loadList();
		$ccy = null;
		$arr = array();
		while ($obj->next()) {
			$ncy = $obj->get('company')->getUid();
			if ($ccy != $ncy) {
				$arr[$ncy] = array();
				$ccy = $ncy;
			}
			$arr[$ccy][$obj->getUid()] = $obj->getLabel();
		}
		return json_encode($arr);
	}
	
}
