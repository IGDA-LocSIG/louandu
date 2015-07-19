<?php

require_once APP_CONFIG_PATH.'email.php';
require_once APP_LIB_PATH.'swift_mailer/swift_required.php';

class Email {

	private $_direction;
	private $_mailer;
	protected $_mode;
	protected $_html;
	public $message;
	
	function __construct() {
	
		$this->_mode = $GLOBALS['config']['email']['mode'];
		
		$this->_html = $GLOBALS['config']['email']['html_format'];
	
		if ($this->_mailer) {
			return true;
		}
	
		// Create the Transport
		switch ($this->_mode) {
			case 'smtp':
				$transport = Swift_SmtpTransport::newInstance($GLOBALS['config']['email']['smtp_server'], $GLOBALS['config']['email']['smtp_port'])
				  ->setUsername($GLOBALS['config']['email']['smtp_user'])
				  ->setPassword($GLOBALS['config']['email']['smtp_pass']);
				break;
			case 'sendmail':
				$transport = Swift_SendmailTransport::newInstance($GLOBALS['config']['email']['sendmail']);
				break;
			default:
				$transport = Swift_MailTransport::newInstance();
				break;
		}
		
		$this->_mailer = Swift_Mailer::newInstance($transport);
		
		$this->init();
		
		return true;
	}
	
	public function init() {
	
		$this->message = Swift_Message::newInstance();
		
	}
	
	public function subject($subject) {
		$this->message->setSubject($subject);
	}
	
	public function from($email, $name='') {
		$this->direction = 'in';
		if ($name) {
			$this->message->setFrom(array($email => $name));
		} else {
			$this->message->setFrom(array($email));
		}
	}
	
	public function to($email, $name='') {
		$this->direction = 'out';
		$func = 'setTo';
		if ($this->message->getTo()) {
			$func = 'addTo';
		}
		if ($name) {
			$this->message->$func(array($email => $name));
		} else {
			$this->message->$func(array($email));
		}
	}
	
	public function cc($email, $name='') {
		$func = 'setCc';
		if ($this->message->getTo()) {
			$func = 'addCc';
		}
		if ($name) {
			$this->message->$func(array($email => $name));
		} else {
			$this->message->$func(array($email));
		}
	}
	
	/**
	 * body in HTML if any
	 * alt : true if auto stripped from HTML
	 */
	public function body($body, $alt=false) {
	
		if ($this->_html) {
			$this->message->setBody($body, 'text/html');
			// Add alternative parts with addPart()
			if ($alt) {
				$this->message->addPart($alt, 'text/plain');	
			} else {
				$this->message->addPart(strip_tags($body), 'text/plain');	
			}
		} else {
			$this->message->setBody($body);
		}
	}
	
	/**
	 * send prepared message
	 */
	public function send() {
	
		// checks
		switch ($this->direction) {
			case 'in':
				$this->message->setTo(array($GLOBALS['config']['email']['default_address'] => $GLOBALS['config']['email']['default_name']));
				break;
			case 'out':
				$this->message->setFrom(array($GLOBALS['config']['email']['default_address'] => $GLOBALS['config']['email']['default_name']));
				if ($GLOBALS['config']['email']['default_reply']) {
					$this->message->setReplyTo($GLOBALS['config']['email']['default_reply']);
				}
				break;
			default:
				return false;
		}
		if ($GLOBALS['config']['email']['default_bounce']) {
			$this->message->setReturnPath($GLOBALS['config']['email']['default_bounce']);
		}
	
		// Send the message
		return $this->_mailer->send($this->message);
	}
	
}