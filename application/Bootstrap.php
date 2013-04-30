<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public $_frontController = null;

	public $_root = '';

	public $_registry = null;

	public function run()
	{
		parent::run();
	}

	public function _initPrepare()
	{
		self::setupEnvironment();
		self::setupRegistry();
		self::setupConfiguration();
		self::setupView();
		self::setupDatabase();
		self::setupModules();
		self::setupFrontController();
		self::setupRouter();
		self::setupLocale();
		self::setupLayout();
	}

	private function setupEnvironment()
	{
		$this->_root = dirname(dirname(__FILE__));
	}

	private function setupRegistry()
	{
		$this->_registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
		
		if (Zend_Session::$_unitTestEnabled) {
			Zend_Registry::_unsetInstance();
		}
		
		Zend_Registry::setInstance($this->_registry);
		
		Zend_Registry::set('prefixFolders', '.');
		
		Zend_Registry::set('monthsOfYear', 
			array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 
				'Outubro', 'Novembro', 'Dezembro'));
	}

	private function setupConfiguration()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/site.config.ini');
		
		$this->_registry->application_config = self::getOption('resources');
		$this->_registry->config = $config;
	}

	private function setupView()
	{
		$view = new Zend_View();
		
		$view->setEncoding('UTF-8');
		$view->doctype('HTML5');
		
		//$view->addFilterPath('Core/View/Filter', 'Core_View_Filter');
		//$view->setFilter('Translate');
		
		$view_renderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
		
		Zend_Controller_Action_HelperBroker::addHelper($view_renderer);
		
		//$view->addHelperPath('Core/Snippets', 'Core_Snippets');
	}

	private function sendResponse(Zend_Controller_Response_Http $response)
	{
		$response->setHeader('Content-Type', 'text/html; charset=UTF-8', true);
		$response->sendResponse();
	}

	private function setupDatabase()
	{
		$config = $this->_registry->config;
		
		$db = Zend_Db::factory($config->database->db->adapter, $config->database->db->config);
		$db->query("SET NAMES 'utf8'");
		
		$this->_registry->db = $db;
		
		Zend_Db_Table::setDefaultAdapter($db);
	}

	private function setupModules()
	{
		$this->bootstrap("frontController");
		$front = $this->getResource("frontController");
	}

	private function setupRouter()
	{
		//Zend_Registry::set('router', $this->_frontController->getRouter());
		
		$router = $this->_frontController->getRouter();
		
		$route = new Zend_Controller_Router_Route(
				'/empresa',
				array('module' => 'default', 'controller' => 'Index', 'action' => 'empresa')
		);
		$router->addRoute('EmpresaRouter', $route);
	}

	private function setupFrontController()
	{
		$this->_frontController = Zend_Controller_Front::getInstance();
		$this->_frontController->throwExceptions(true);
		$this->_frontController->returnResponse(false);
		$this->_frontController->setControllerDirectory(array('default' => APPLICATION_PATH . '/controllers'));
		$this->_frontController->setParam('registry', $this->_registry);
	}

	private function setupLocale()
	{
		$config = $this->_registry->config;
		
		$locale_value = $config->environment->language;
		
		$locale = new Zend_Locale($locale_value);
		
		Zend_Registry::set('Zend_Locale', $locale);
		
		$namespace = new Zend_Session_Namespace('SHOPLIMPEZA', false);
		
		setlocale(LC_ALL, $locale_value, $locale_value.'UTF8', 'pt-br', 'bra', 'brazil');
		date_default_timezone_set($config->timezone->locale);
	}

	private function setupLayout()
	{
		Zend_Layout::startMvc(array());
		
		$this->_layout = Zend_Layout::getMvcInstance();
		$this->_layout->setLayoutPath(APPLICATION_PATH . '/views/layouts');
		
		$this->_layout->enableLayout();
	}
}