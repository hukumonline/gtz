<?php

/**
 * General Bootstrapping class
 * @author Nihki Prihadi <nihki@madaniyah.com>
 *
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap 
{
	protected function _initAutoload()
	{
		$moduleLoader = new Zend_Application_Module_Autoloader(array(
			'namespace' => '',
			'basePath' => APPLICATION_PATH));
		return $moduleLoader;
	}
	protected function _initMessages()
	{
		// save to registry
		$registry = Zend_Registry::getInstance();
		$registry->set('files', $_FILES);
		unset($config);
	}
	protected function _initSession()
	{
		Zend_Session::Start();
	}
}

?>