<?php
/**
 * Tzn Framework
 * 
 * @package tzn_core_classes
 * @author Stan Ozier <framework@tirzen.com>
 * @version 0.4
 * @copyright GNU Lesser General Public License (LGPL) version 3
 */
 
/**
 * Front Controller
 * 
 * The is the root class of the application, the mother of initialization process
 * It's a singleton, defining mostly static methods
 * @since 0.1
 */
class FrontController extends HelpableSingleton {

	private static $instance;
	
	protected $autoPath;
		
	public $settings;
	public $user;
	public $db;

	/**
	 * private constructor (singleton)
	 */
	private function __construct() {
		
		$this->autoPath = $authoPath = array();
		include APP_CONFIG_PATH.'path.php';
		$this->autoPath = $autoPath;
		unset($autoPath);
		
		$this->_helpers = array();
		
		$this->db = array();
		
		if (!isset($_SESSION['appVariables'])) {
			$_SESSION['appVariables'] = array();
		}

	}

	/**
	 * the getInstance() method returns a single instance of the object
	 */
	public static function getInstance() {
		if(!isset(self::$instance)){
			spl_autoload_register(array('FrontController','autoLoad'));
			$object= __CLASS__;
			self::$instance=new $object;
			self::$instance->initApp();
		}
		return self::$instance;
	}
	
	// ---- APP INITIALIZATION ---------------------------------------------------
	
	/**
	 * app initialization (from config)
	 */
	protected function initApp() {
	
		if (APP_SETUP_DATABASE) {
		
			// load up database settings (host, username, etc...)
			include APP_CONFIG_PATH.'db.php';
		
			// connect to database
			$this->initDatabase();
		
		}
		
		if (APP_SETUP_MESSAGING) {
			// initialize messaging (trans-pages messages)
			$this->initMessaging();
		}
		
		if (APP_SETUP_DATABASE) {
			
			// load general settings
			if (APP_SETUP_GLOBAL_SETTINGS) {
				$this->loadSettings();
			}
			
			// load user (if needed)
			$this->loadUser(APP_SETUP_USER_MODEL);

			// load user preferences
			if (APP_SETUP_USER_SETTINGS) {
				$this->loadUserSettings();
			}
		}
		
		if (APP_SETUP_TRANSLATOR) {
			// initialize translations system
			$this->initTranslator();
		}
		
		if (APP_SETUP_NAVI) {
			// initialize navigation (referers, redirects and URL manipulation)
			$this->initNavi();
		}
	}
	
	/**
	 * initialize database connection
	 */
	public function initDatabase() {
		$i = count($this->db);
		$this->db[$i] = new DbConnector();
		$this->db[$i]->connect();
	}
	
	/**
	 * load application settings
	 * @todo load settings
	 * @todo set default controller and action
	 */
	public function loadSettings() {
		$this->settings = new SettingModel();
		// -TODO- load 'em up
	}
	
	/**
	 * load and authenticate user
	 */
	public function loadUser($class) {
		if (empty($class)) {
			return false;
		}
		$class = StringHelper::flatToCamel($class,true).'Model';
		$this->user = new $class;
		$this->user->enableAuthentication();
		$this->user->connectDb();
		$this->user->checkLogin();
	}
	
	/**
	 * load user settings
	 * @todo load user settings and override global settings
	 */
	public function loadUserSettings() {
		// might overload default controller and action
		// and language
		// -TODO-
		if ($this->user->isLoggedIn()) {
			$this->setSessionDefault('usertask', $this->user->getUid());
		}
	}
	
	/**
	 * initialize messaging system
	 */
	public function initMessaging() {
		$this->addHelper('messaging');
	}
	
	/**
	 * initialize referrers system
	 */
	public function initNavi() {
		$this->addHelper('navi');
	}
	
	/**
	 * initialize translator
	 */
	public function initTranslator() {
		$this->addHelper('translator');
		$this->loadLangConfig();
		$this->loadLangFilesFromConfig();
	}
	
	public function addPath($type, $path) {
		array_unshift($this->autoPath[$type], $path);
	}
	
	/**
	 * launch application controller
	 */
	public function run() {
		// parse URL : look for controller, action, parameters...
		$this->request = Request::getInstance();
		
		// load up requested Application Controller
		if ($f = $this->request->getFile()) {
			$pt = APP_CORE_PATH.'controller/'.$this->request->getPath().$f.'.php';
			if (file_exists($pt)) {
				self::log_front("-> manually loading controller class : $pt");
				include_once($pt);
			} else {
				self::log_front("!> cannot load controller class : $pt");
			}
		}
		$con = self::loadController($this->request->getController());
		
		if (!$con) {
			// controller not found, throw exception
			if (isset($GLOBALS['config']['error']['not_found'])) {
				$this->_sendErrorCodeToRobots('404');
				include $GLOBALS['config']['error']['not_found'];
				exit;
			} else {
				try {
					throw new AppException('Controller '.$this->request->getController().' not defined in '.implode(', ', $this->autoPath['controller']));
				} catch(Exception $e) {
					self::log_debug('error loading controller '.$this->request->getController());
					echo $e;
					exit;
				}
			}		
		}

		$obj = new $con;
		$obj->setPath($this->request->getPath());
		
		$act = $this->request->getAction();

		// if submitted, call requested action (mainReaction by default)
		/*
		if (!empty($_POST)) {
			if (method_exists($obj, $act.'Reaction')) {
				if (!call_user_func(array($obj,$act.'Reaction'))) {
					// stop here
					return true;
				}	
			} else {
				self::log_error('method '.$act.'Reaction not defined in controller '.$this->controller);
			}
		}
		*/
		
		// if still needed, call requested action (mainAction by default)
		if (method_exists($obj, $act.'Action')) {
			call_user_func(array($obj, $act.'Action'));
		} else {
			if (isset($GLOBALS['config']['error']['not_found'])) {
				$this->_sendErrorCodeToRobots('404');
				include $GLOBALS['config']['error']['not_found'];
				exit;
			}
			self::log_error('method '.$act.'Action not defined in controller '.$this->request->getController());
			return false;
		}
		return true;
	}
    
    /**
     * send error code (http header) to robots
     */
    protected function _sendErrorCodeToRobots($code) {
		if (preg_match('/'.APP_ROBOT_AGENT.'/', $_SERVER['HTTP_USER_AGENT'])) {
			header("Status : 404 Not Found");
			header("HTTP/1.1 404 Not Found");
		}
    }
	
	// ---- AUTOLOAD setup -------------------------------------------------------
	
	/**
	 * generic method loading classes
	 */
	public function _autoLoad($file, $type) {
		self::log_front("loading $file [$type]");
		foreach($this->autoPath[$type] as $path) {
			if (file_exists($path.$file)) {
				self::log_front("-> Yes in $path");
				include_once($path.$file);
				return true;
			} else {
				self::log_front("!> No $file in $path");
			}
		}
		return false;
	}
	
	/**
	 * static method loading any type of class definition
	 * check all accessible folder that may contain the class definition
	 * and include it if necessary
	 */
	protected static function load($class, $type) {
		if (!class_exists($class)) {
		
			$file = StringHelper::camelToFlat($class);
			if ($idx = strrpos($file, '_'.$type)) {
				$file = substr($file, 0, $idx);
			}
			$file .= '.php';
		
			$obj = self::getInstance();
			if (!$obj->_autoLoad($file, $type)) {
				return false;
			}
		}
		return $class;
	}
	
	/**
	 * load core class
	 */
	public static function loadClass($class) {
		return self::load($class, 'class');
	}
	
	/**
	 * load helper class
	 */
	public static function loadHelper($class) {
		return self::load($class, 'helper');
	}
	
	/**
	 * load a model class
	 */
	public static function loadModel($class) {
		return self::load($class, 'model');
	}
	
	/**
	 * load a controller class
	 */
	public static function loadController($class, $path='') {
		return self::load($class, 'controller', $path);
	}
	
	/**
	 * load a view class
	 */
	public static function loadView($class) {
		return self::load($class, 'view');
	}
	
	/**
	 * autoload class
	 * @parameter would require the type of class to be said in there
	 */
	private static function autoLoad($name) {
		$str = StringHelper::camelToFlat($name);
		$type = 'class';
		$sep = strrpos($str,'_');
		if ($sep) {
			$class = substr($str, 0, $sep);
			$type = substr($str, $sep+1);
			switch ($type){
				case 'helper':
				case 'class':
				case 'controller':
				case 'model':
				case 'view':
					// valid type
					break;
				default:
					// invalid type, must be a core class
					$type = 'class';
					break;
			}
		}
		return self::load($name, $type);
	}
	
	// ---- LOGGING and DEBUGGING ------------------------------------------------
	
	public static function log_front($str) {
		self::log_any($GLOBALS['config']['log_front'], 'front', $str);
	}
	
	public static function log_debug($str) {
		self::log_any($GLOBALS['config']['log_debug'], 'debug', $str);
	}
	
	public static function log_message($str) {
		self::log_any($GLOBALS['config']['log_message'], 'message', $str);
	}
	
	public static function log_warn($str) {
		self::log_any($GLOBALS['config']['log_warn'], 'warning', $str);
	}
	
	public static function log_error($str) {
		self::log_any($GLOBALS['config']['log_error'], 'error', $str);
	}
	
	public static function log_core_debug($str) {
		self::log_any($GLOBALS['config']['log_core'], 'error', $str);
	}
	
	public static function log_any($mode, $head, $str) {
		switch ($mode) {
		case 1:
			$arr = explode("\n", trim($str));
			foreach ($arr as $s) {
				error_log($GLOBALS['config']['log_signature']." $head : $s");
			}
			break;
		case 2:
			echo VarStr::html($GLOBALS['config']['log_signature']." $head : ".nl2br($str));
			break;
		}
	}
}

class FC extends FrontController
{
	
}

class AppException extends Exception {

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        echo "<pre>".parent::__toString()."</pre>";
        return __CLASS__;
    }

}