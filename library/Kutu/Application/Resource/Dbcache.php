<?php

class Kutu_Application_Resource_Dbcache extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
    {
		$options = array_change_key_case($this->getOptions(), CASE_LOWER);
		if (isset($options['enable'])) 
		{
			if($options['enable'])
			{
				$frontendOptions = array(
				'lifetime' => 7200, // cache lifetime of 2 hours
			    'automatic_serialization' => true
			    );

				$backendOptions  = array(
				    'cache_dir' => KUTU_ROOT_DIR.'/tmp/cache'
				    );

				$cacheDbTable = Zend_Cache::factory('Core',
				                             'File',
				                             $frontendOptions,
				                             $backendOptions);

				Zend_Db_Table_Abstract::setDefaultMetadataCache($cacheDbTable);
			}
		}
    }
}

?>