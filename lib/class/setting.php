<?php
/**
 * Tzn Framework Settings
 * 
 * @package tzn_models
 * @author Stan Ozier <framework@tirzen.com>
 * @since 1.0
 * @copyright GNU Lesser General Public License (LGPL) version 3
 */

/**
 * Setting
 * 
 * 
 */
class SettingModel extends Model {

	public function __construct() {
		parent::__construct();
		$this->addProperties(array(
			'setting_key'	=> 'STR',
			'setting_value'	=> 'XML',
			'section'		=> 'STR',
			'user_id'		=> 'NUM'
		));
	}
}
