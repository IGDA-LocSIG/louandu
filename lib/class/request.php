<?php


class Request {

	private static $_instance;

	protected $_path;
	protected $_controller;
	protected $_action;
	
	protected $_ajax;
	
	private function __construct() {
	
		$this->_path = $GLOBALS['config']['app']['default_path'];		
		$this->_controller = $GLOBALS['config']['app']['default_controller'];
		$this->_action = $GLOBALS['config']['app']['default_action'];
		
		$this->_ajax = false;
		
		$this->_helpers = array();
		
		if (!isset($_SESSION['appVariables'])) {
			$_SESSION['appVariables'] = array();
		}

	}

	/**
	 * the getInstance() method returns a single instance of the object
	 */
	public static function getInstance() {
		if(!isset(self::$_instance)){
			$object= __CLASS__;
			self::$_instance=new $object;
			self::$_instance->clean();
			self::$_instance->parseUrl();
		}
		return self::$_instance;
	}
	
	/**
	 * clean $_POST, $_GET and $_REQUEST if magic quotes are on
	 */
	protected static function clean() {
		if (!get_magic_quotes_gpc()) {
			return true;
		}
		$arrReq = array('_POST','_GET','_REQUEST');
		foreach ($arrReq as $var) {
			if (count($GLOBALS[$var])) {
				foreach($GLOBALS[$var] as $key => $val) {
					if (is_string($val)) {
						$GLOBALS[$var][$key] = stripslashes($val);
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * checks if variable is submitted by HTTP request
	 * @param $mix an array or string containing key(s) to be checked
	 * @param $sani option to sanitize the variable
	 * @return first found in request
	 */
	public function chk($mix, $sani='') {
		$cl = (strlen($sani) == 3)?('Var'.ucfirst(strtolower($sani))):false;
		if (is_array($mix)) {
			$arr = array();
			foreach($mix as $key) {
				if (isset($this->$key)) {
					$val = $this->$key;
					if ($cl && $val) {
						$this->$key = $cl::sani($val);
					}
					$arr[] = $key;
				}
			}
			if (count($arr) == count($mix)) {
				return true;
			}
		} else if (isset($this->$mix)) {
			if ($cl && $this->$mix) {
				$this->$mix = $cl::sani($this->$mix);
			}
			return $mix;
		}
		
		return false;
	}
	
	/**
	 * set a session variable default's value
	 * @param $key the name of variable
	 * @param $default the default value
	 * @param $insession search and save in session or not
	 * @param $sani option to sanitize the variable
	 * @return value if already set, or default if none
	 */
	public function set($key, $default, $insession=false, $sani='') {
		if (!is_bool($insession) && is_string($insession)) {
			// third arg can be sanitizer option (if string)
			// eg. $this->request->set('name','value','STR');
			$sani = $insession;
			$insession = false;
		}
		$val = $this->get($key, $insession, $insession, $sani);
		if (!is_null($val)) {
			// already set by http request
			return $val;
		} else {
			if ($sani && !is_null($default)) {
				$cl = 'Var'.ucfirst(strtolower($sani));
				$default = $cl::sani($val);
			}
			$this->$key = $default;
			return $this->$key;
		}
	}
	
	/**
	 * gets HTTP variable
	 * @param $key the name of variable
	 * @param $save save in session or not
	 * @param $recover try to reload variable from session
	 * @param $sani option to sanitize the variable
	 * @return null if not found, value if set
	 */
	public function get($key, $save=false, $recover=false, $sani='') {
	
		// check params
		if (!is_bool($save) && is_string($save)) {
			// second arg can be sanitizer option (if string)
			// eg. $this->request->get('name','STR');
			$sani = $save;
			$save = false;
		}
	
		$val = null;
		if (isset($this->$key)) {
			$val = $this->$key; 
		}
		
		if (is_null($val) && $recover && isset($_SESSION[$key])) {
			$val = $_SESSION[$key];
		}
		
		if ($sani && !is_null($val)) {
			$cl = 'Var'.ucfirst(strtolower($sani));
			$val = $cl::sani($val);
		}
		
		if ($save) {
			// save to session
			if (is_null($val)) {
				unset($_SESSION[$key]);
			} else {
				$_SESSION[$key] = $val;
			}
		}
		
		if (is_null($val)) {
			unset($this->$key);
		} else {
			$this->$key = $val;
		}
		
		return $val;
	}	
		
	/**
	 * reset one or many session variables
	 * @param $mix an array or string containing key(s) to be cleared from session
	 */
	public function del($mix) {
		$keys = StringHelper::mixedToArray($mix);
		foreach($keys as $key) {
			if (isset($_SESSION[$key])) {
				unset($_SESSION[$key]);
			}
		}
	}
	
	/**
	 * parse URL / path for section, controller and action
	 */
	public function parseAction(&$arrReq) {
	
		$id = 0;
		$arr = array('_path','_controller','_action');
		
		$req = $arrReq;
		
		foreach($req as $val) {
			if (preg_match('/^[0-9]+$/', $val) || preg_match('/^[a-z0-9]{16}$/', $val)) {
				// skip ID
				$id = array_shift($arrReq);
				continue;
			}
			if (!count($arr)
				|| preg_match('/^[a-f0-9]{16}$/i', $val)
				|| preg_match('/\.html$/', $val)
				|| preg_match('/,/', $val))
			{
				break;
			} else {
				$key = array_shift($arr);
				$val = array_shift($arrReq);
				$this->$key = $val;
			}	
		}
		
		if ($id) {
			array_unshift($arrReq, $id);
		}
	
	}
	
	/**
	 * parse request for controller, action and other parameters
	 * /path/ (controller = index, action=main)
	 * /path/controller/action
	 * /path/controller/action/id.html (specific ID)
	 * /path/controller/action/1234 (numeric ID)
	 * /path/controller/action/a123456789b123456789c12 (MD5 key)
	 * /path/controller/action/param1,value1/param2,value2/param3
	 * /index.php?p=path&c=controller&a=action&param1=value1
	 */
	public function parseUrl() {

		$req = $_SERVER['REQUEST_URI'];
		
		// remove subfolder setup if non virtual host
		if (strlen(APP_WWW_URI) > 1) {
			$req = str_ireplace(APP_WWW_URI,'',$req);
		}
		// remove trailing slashes
		$req = trim($req,'/');
		// parse if non empty
		if ($req) {
			if ($pos = strrpos($req,'?')) {
				$req = substr($req, 0, $pos);
			}
			
			$arrReq = explode('/',$req);
			$this->parseAction($arrReq);

			while(count($arrReq)) {
				$val = $key = '';
				$mix = explode(',',array_shift($arrReq));
				if (count($mix) > 1) {
					$key = $mix[0];
					$val = $mix[1];
				} else if
					(preg_match('/^[0-9]+$/', $mix[0])
					|| preg_match('/^[a-f0-9]{32}$/i', $mix[0])
					|| preg_match('/\.html$/', $mix[0]))
				{
					$key = 'id';
					$val = $mix[0];
				} else {
					$key = $mix[0];
				}
				
				if ($pos = strrpos($val,'.html')) {
					$val = substr($val, 0, $pos);
				}
				
				$this->$key = urldecode($val);				
			}
		}
		
		// parse query string
		if (count($_REQUEST)) {
			foreach($_REQUEST as $key => $val) {
				switch($key) {
				case 'u':
					$arr = explode('/',$val);
					$this->parseAction($arr);
					break;
				case 'p':
					$this->_path = $val;
					break;
				case 'c' :
					$this->_controller = $val;
					break;
				case 'a' :
					$this->_action = $val;
					break;
				default :
					$this->$key = $val;
					break;		
				}
			}
		} 
		
		// check if ajax request
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->_ajax = true;
		}
	}
	
	public function getPath() {
		return ($this->_path?($this->_path.'/'):'');
	}
	
	public function getFile() {
		return $this->_controller;
	}
	
	public function getController() {
		return StringHelper::flatToCamel($this->_path.'-'.$this->_controller, true,'-');
	}
	
	public function getAction() {
		return StringHelper::flatToCamel($this->_action, false,'-');
	}
	
	/**
	 * generate URL
	 */
	public static function getUrl($path='', $controller='', $action = '', $params = '') {
		$url = APP_WWW_URI;
		
		if (!$path) {
			return $url;
		}
		
		if (@constant('APP_URL_REWRITE')) {
			$url .= StringHelper::camelToFlat($path,'-');
			$url .= '/'.StringHelper::camelToFlat($controller,'-');
			if ($action) {
				$url .= '/'.StringHelper::camelToFlat($action,'-');
			}
		} else {
			$url .= 'index.php?p='.StringHelper::camelToFlat($path,'-');
			if ($controller) {
				$url .= '&c='.StringHelper::camelToFlat($controller,'-');
			}
			if ($action) {
				$url .= '&a='.StringHelper::camelToFlat($action,'-');
			}
		}
		
		if (is_array($params)) {
			if (@constant('APP_URL_REWRITE')) {
				foreach ($params as $key => $val) {
					if (is_string($val)) {
						$url .= '/'.urlencode($key).','.urlencode($val);
					}
				}
			} else {
				foreach ($params as $key => $val) {
					if (is_string($val)) {
						$url .= '&'.urlencode($key).'='.urlencode($val);
					}
				}
			}
		}
		return $url;
	}
	
	/**
	 * returns current URL
	 * @param mixed params false if no parameter, true if all current parameters, array to submit other parameters
	 */
	public function thisUrl($params=false) {
		if (is_array($params)) {
			return self::getUrl($this->_path, $this->_controller, $this->_action, $params);
		} else if ($params) {
			$params = array();
			foreach(get_object_vars($this) as $k => $p) {
				if (!preg_match('/^_|PHPSESSID|pg/', $k) && !empty($p) && !is_array($p) && preg_match('/^$[a-z0-9\-\_\+]+/i', $p)) {
					$params[$k] = $p;
				}
			}
			return self::getUrl($this->_path, $this->_controller, $this->_action, $params);
		} else {
			return self::getUrl($this->_path, $this->_controller, $this->_action, false);
		}
	}
	
	public function isAjax() {
		return $this->_ajax;
	}
	
	/**
	 * return navigation menu
	 */
	public function menu($id='pages') {
    	$str = '<ul id="'.$id.'">';
		foreach ($GLOBALS['config'][$id] as $key => $url) {
			$arr = explode('/',$url);
			$arr[0] = ucfirst($arr[0]);
			if (!$arr[1]) {
				$arr[1] = 'main';
			}
			$str .= '<li';
			if ($this->controller == $arr[0] && $this->action == $arr[1]) {
				$str .= ' class="active"';
			}
			$str .= '><a href="'.FrontController::getUrl($url).'">'.$key.'</a></li>';
		}
    	$str .= '</ul>';
    	return $str;
    }

}