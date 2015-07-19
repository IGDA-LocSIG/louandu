<?php
/**
 * Tzn Framework
 * 
 * @package tzn_helpers
 * @author Stan Ozier <framework@tirzen.com>
 * @since 1.0
 * #copyright GNU Lesser General Public License (LGPL) version 3
 */

/**
 * HtmlHead Helper
 * 
 * HTML Header, links to CSS, Javascripts, etc...
 * @todo make this a helper of Page, not a class
 */
class HtmlHeadHelper extends Collectable {

	protected $css, $cssCode, $js, $jsCode, $jsCalendar, $rss;

	public function __construct() {
		parent::__construct();
	}
	
	protected function _init($key,$reset=false) {
		if (!is_array($this->$key) || $reset) {
			$this->$key = array();
			if (!$reset && !empty($GLOBALS['config']['header'][$key])) {
				$this->$key = StringHelper::mixedToArray($GLOBALS['config']['header'][$key]);
			}
		}
	}
	
	public function stuff() {
	
		if (count($this->jsCalendar)) {
			$this->add('css',APP_WWW_URI.'assets/css/calendar.css');
			$this->add('jsScript', APP_WWW_URI.'assets/js/calendar.js');
			foreach($this->jsCalendar as $it) {
				if (is_string($it)) {
					$it=trim($it);
					$this->add('jsOnLoad',"new Calendar({ '$it': 'd/m/y' })");
				}
			}
        }
       
        // css
        if (count($this->css)) {
			foreach($this->css as $it) {
				if (preg_match('/(^\/|http:\/\/)/', $it)) {
					echo '<link rel="stylesheet" type="text/css" href="'.$it.'" />'."\n";
				} else {
					foreach ($GLOBALS['config']['path']['css'] as $p) {
						if (file_exists(APP_WWW_PATH.$p.$it)) {
							echo '<link rel="stylesheet" type="text/css" href="'.APP_WWW_URI.$p.$it.'" />'."\n";
							break;
						}
					}
				}
			}
		}
		
		// css code (ie tests)
		if (count($this->cssCode)) {
            echo implode("\n",$this->cssCode)."\n";        
        }
        
        // javascript (include)
		if (count($this->js)) {
			foreach($this->js as $it) {
				$it = trim($it);
				if (preg_match('/^(\/|http:\/\/)/', $it)) {
					echo '<script type="text/javascript" src="'.$it.'" language="javascript"></script>'."\n";
				} else {
					foreach ($GLOBALS['config']['path']['js'] as $p) {
						if (file_exists(APP_WWW_PATH.$p.$it)) {
							echo '<script type="text/javascript" src="'.APP_WWW_URI.$p.$it.'" language="javascript"></script>'."\n";
							break;
						}
					}
				}
			}
		}
		
		// javascrpt direct code
		if (count($this->jsCode)) {
            echo '<script type="text/javascript">'."\n";
            echo implode("\n",$this->jsCode);
            echo "\n</script>\n";           
        }
        
		// xml/rss
        if (count($this->rss)) {
			foreach($this->rss as $it) {
				echo '<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="'
					.CMS_WWW_URL.$it.'" />'."\n";
			}
		}
		
	}
	
}