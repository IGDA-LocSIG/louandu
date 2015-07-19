<?php
/**
 * Tzn Framework
 * 
 * @package tzn_core_classes
 * @author Stan Ozier <framework@tirzen.com>
 * @version 0.3
 * @copyright GNU Lesser General Public License (LGPL) version 3
 */

/**
 * Controller super class (abstract)
 * 
 * All controllers must extend this class
 * @since 0.1
 * @todo constuctor parameter login : could require specific level
 */
abstract class AppController extends Pluginable {

	protected $view;
	
	protected $fc;
	
	protected $page;
	
	protected $path;

	/**
	 * constructor
	 * @param mixed $login is user login required
	 */
	public function __construct() {
		parent::__construct();
		
		// get front controller instance
		$this->fc = FrontController::getInstance();
		
		// instantiate page
		$this->page = new PageModel();
		
		// setup views
		$this->view = array();
		
	}
	
	public function setPath($path) {
		$this->path = $path;
	}
	
	protected function checkLogin($redir = true) {
		// check login ?
		if (APP_SETUP_USER_MODEL) {
			if ($this->fc->user->isLoggedIn()) {
				return true;
			} else if ($redir) {
				if (is_string($login)) {
					if ($this->fc->controller != StringHelper::flatToCamel($login,true) || $this->fc->action != 'login') {
						NaviHelper::redirect($this->fc->getUrl($login,'login'));
					}
				} else {
					NaviHelper::redirect($this->fc->getUrl('login'));
				}
			}
		}
		return false;
	}
	
	/**
	 * setting the file containing HTML which is sent to the browser
	 */
	public function setView($v, $idx=10) {
		if (empty($v)) {
			unset($this->view[$idx]);
		} else {
			// $v = (strrpos($v, '.php')?$v:($v.'.php'));
			$this->view[$idx] = $v;
		}
	}
	
	/**
	 * dispatch controller's action view
	 * automatically decides if headers need to be included (ajax request or not)
	 */ 
	public function view() {
	
		if ($this->fc->request->isAjax()) {
			$this->ajaxView();
		} else {
			$this->fullView();
		}
		
	}
	
	/**
	 * displath controller's action view (normal mode)
	 */
	public function fullView() {
	
		$this->page->dispatchHeader();
		
		// view inclusions
		ksort($this->view);
		foreach ($this->view as $idx => $view) {
			// include PRJ_VIEW_PATH.$this->path.$view;
			$this->incView($view);
		}
		
		$this->page->dispatchFooter();
		
	}
	
	/**
	 * displath controller's action view (AJAX mode)
	 * typically no headers, just the body
	 */
	public function ajaxView() {
		//echo '<html><body>';
		if ($this->view[10]) {
			$this->incView($this->view[10]);
		}
		// echo '</body></html>';
	}
	
	/**
	 * include a partial / external file in view
	 */
	
	protected function incView($v) {
		$v = (strrpos($v, '.php')?$v:($v.'.php'));
		if (isset($this->viewpath)) {
			include $this->viewpath.$v;
		} else if (is_file(APP_CORE_PATH.'view/'.$this->path.$v)) {
			include APP_CORE_PATH.'view/'.$this->path.$v;
		} else if (is_file(APP_CORE_PATH.'view/'.$v)) {
			include APP_CORE_PATH.'view/'.$v;
		} else {
			include $v;
		}
	}
	
	protected function xmlView($encoding='UTF-8') {
		echo '<'.'?xml version="1.0" encoding="'.$encoding.'"?'.">\n";
		include APP_VIEW_PATH.$this->view[10].'.php';
	}
	
	protected function rawView() {
		include APP_VIEW_PATH.$this->view[10].'.php';
	}
	
	/**
	 * reset Navi::referrers to the current page
	 * this should be called in any top level controller action
	 */
	protected function resetReferrer() {
		$this->fc->setReferrer($this->fc->thisUrl());
	}
	
	/**
	 * any controller must implement at least the default action
	 */
	abstract function mainAction();
	
}