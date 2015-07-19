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
class CompanyModel extends Model {

	public function __construct() {
		parent::__construct('company');
		$this->addProperties(array(
			'id'			=> 'UID',
			'name'			=> 'STR',
			'address'		=> 'STR'
		));
	}
		
	public function check() {
		return parent::check('name');
	}
	
	public function getLabel() {
		return $this->value('name');
	}
	
}
