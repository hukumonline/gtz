<?php

define('KUTU_ROOT_DIR',dirname(__FILE__));
define('KUTU_ROOT_PATH',dirname(__FILE__));
define('KUTU_LIB_PATH' , KUTU_ROOT_PATH . '/library') ;
define('APPLICATION_PATH', KUTU_ROOT_PATH . '/app');
define('MODULE_PATH' , KUTU_ROOT_PATH . '/app/modules') ;

// define the path for configuration file
define('CONFIG_PATH' , KUTU_ROOT_PATH . '/app/configs') ;
 
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'development'));

// Include path
set_include_path(
	KUTU_LIB_PATH . PATH_SEPARATOR . get_include_path()	
);

require_once('Kutu/Core/Util.php');
$kutuUtil = new Kutu_Core_Util();
define('KUTU_ROOT_URL', $kutuUtil->getRootUrl(KUTU_ROOT_DIR));

/** Zend_Application */
define('ZEND_APPLICATION_REGISTER', 'application');
define('APPLICATION_CONFIG_FILENAME', 'config.ini');
// Zend_Application
require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    CONFIG_PATH . '/'.APPLICATION_CONFIG_FILENAME
);

$registry = Zend_Registry::getInstance();
$registry->set(ZEND_APPLICATION_REGISTER, $application);

$application->bootstrap()
			->run();

?>