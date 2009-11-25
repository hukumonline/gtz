<?php

class Kutu_Search
{
	public static function factory($adapter, $config = array())
    {
    	switch (strtolower($adapter)) 
    	{
    		case 'solr':
    			$solrHost = $config['host'];
    			$solrPort = $config['port'];
    			$solrHomeDir = $config['dir'];
    			$newAdapter = new Kutu_Search_Adapter_Solr($solrHost, $solrPort, $solrHomeDir);
    			
    			return $newAdapter;
    			break;
    		case 'zendlucene':
    			if(isset($config['dir']))
    				$luceneHomeDir = $config['dir'];
    			else 
    				$luceneHomeDir = null;
    			$newAdapter = new Kutu_Search_Adapter_ZendLucene($luceneHomeDir);
    			
    			return $newAdapter;
    			break;
    	}
    	return false;
    }
	
	public static function manager()
	{
		$registry = Zend_Registry::getInstance(); 
		$application = $registry->get(ZEND_APPLICATION_REGISTER);
		
		$application->getBootstrap()->bootstrap('indexing');
		
	   	return $application->getBootstrap()->getResource('indexing');
	}
}

?>