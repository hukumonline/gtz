<?php

class Widgets_FolderController extends Zend_Controller_Action 
{
	function kategoriAction()
	{
		$parentGuid = ($this->_getParam('node'))? $this->_getParam('node') : 'lt49f812abe4488';
		
		$columns = 3;
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowsetFolder = $tblFolder->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid","title ASC");
		
		$num_rows = count($rowsetFolder);
		$rows = ceil($num_rows/$columns);
		
		if($num_rows < 3)
			$columns = $num_rows;
		if($num_rows==0)
		{
			
		}
		
		$folder = 0;
		$data = array();
		
		foreach ($rowsetFolder as $rowFolder)
		{
			$data[$folder][0] = $rowFolder->title;
			$data[$folder][1] = $rowFolder->description;
			$data[$folder][2] = $rowFolder->guid;
			$folder++;
		}
		
		$this->view->rows = $rows;
		$this->view->columns = $columns;
		$this->view->data = $data;
		$this->view->numberOfFolders = $num_rows;
		$this->view->node = $parentGuid;
		
	}
	function peraturanAction()
	{
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		
		if ($this->_getParam('node')) {
			$parentGuid = $this->_getParam('node');
			$rowsetFolder = $tblFolder->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid","title ASC");
		} else {
			$parentGuid = 'lt49c706c641081';	
			$rowsetFolder = $tblFolder->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid","title ASC");
		}
		
		$columns = 2;
		
		$num_rows = count($rowsetFolder);
		$rows = ceil($num_rows/$columns);
		
		if($num_rows < 3)
			$columns = $num_rows;
		if($num_rows==0)
		{
			
		}
		
		$folder = 0;
		$data = array();
		
		foreach ($rowsetFolder as $rowFolder)
		{
			$data[$folder][0] = $rowFolder->title;
			$data[$folder][1] = $rowFolder->description;
			$data[$folder][2] = $rowFolder->guid;
			$folder++;
		}
		
		$this->view->rows = $rows;
		$this->view->columns = $columns;
		$this->view->data = $data;
		$this->view->numberOfFolders = $num_rows;
		$this->view->node = $parentGuid;
		
	}
	function putusanAction()
	{
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		
		if ($this->_getParam('node')) {
			$parentGuid = $this->_getParam('node');
			$rowsetFolder = $tblFolder->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid","title ASC");
		} else {
			$parentGuid = 'lt49c7077cce4ce';	
			$rowsetFolder = $tblFolder->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid","title ASC");
		}
		
		$columns = 1;
		 
		$num_rows = count($rowsetFolder);
		$rows = ceil($num_rows/$columns);
		
		if($num_rows < 3)
			$columns = $num_rows;
		if($num_rows==0)
		{
			
		}
		
		$folder = 0;
		$data = array();
		
		foreach ($rowsetFolder as $rowFolder)
		{
			$data[$folder][0] = $rowFolder->title;
			$data[$folder][1] = $rowFolder->description;
			$data[$folder][2] = $rowFolder->guid;
			$folder++;
		}
		
		$this->view->rows = $rows;
		$this->view->columns = $columns;
		$this->view->data = $data;
		$this->view->numberOfFolders = $num_rows;
		$this->view->node = $parentGuid;
		
	}
	function viewCatalogsAction()
	{
		$time_start = microtime(true);
		
    	$folderGuid = ($this->_getParam('node'))? $this->_getParam('node') : 'lt49f812abe4488';
    	
    	$page = $this->_getParam('page',1);
		
		$db = Zend_Db_Table::getDefaultAdapter()->query
		("SELECT catalogGuid AS guid FROM KutuCatalogFolder WHERE folderGuid='$folderGuid'");
		
		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
		
		$indexingEngine = Kutu_Search::manager();
		
		$numi = count($rowset);
		$sSolr = "id:(";
		for($i=0;$i<$numi;$i++)
		{
			$row = $rowset[$i];
			$sSolr .= $row->guid .' ';
		}
		$sSolr .= ')';
		
		if(!$numi)
			$sSolr="id:(hfgjhfdfka)";
			
		$solrResult = $indexingEngine->find($sSolr);
		$solrNumFound = count($solrResult);
		
		$paginator = Zend_Paginator::factory($solrResult);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(20);
		
		$this->view->hits = $paginator;
		$this->view->folderGuid = $folderGuid;
		
			
		/*
		if($folderGuid=='root')
		{	
			$a = array();
			$a['totalCount'] = 0;
			$a['totalCountRows'] = 0;
			$a['folderGuid'] = $folderGuid;
			$limit = 20;
			$a['limit'] = $limit;
		}
		else 
		{
			$a = array();
			$a['folderGuid'] = $folderGuid;

    		$db = Zend_Db_Table::getDefaultAdapter()->query
    		("SELECT guid from KutuCatalog, KutuCatalogFolder where guid=catalogGuid AND folderGuid='$folderGuid'");
    		
    		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
    		
			$a['totalCount'] = count($rowset);	
			$a['totalCountRows'] = count($rowset);	
    		$limit = 20;
			$a['limit'] = $limit;
		}
			
		$this->view->aData = $a;
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
//		echo'<br>WAKTU EKSEKUSI: '. $time;
		$this->view->time = round($time,2) . ' detik';
		*/
	}
	function viewCatalogsPeraturanAction()
	{
		$time_start = microtime(true);
		
    	$folderGuid = ($this->_getParam('node'))? $this->_getParam('node') : 'root';
		
		if($folderGuid=='root')
		{	
			$a = array();
			$a['totalCount'] = 0;
			$a['totalCountRows'] = 0;
			$a['folderGuid'] = $folderGuid;
			$limit = 20;
			$a['limit'] = $limit;
		}
		else 
		{
			$a = array();
			$a['folderGuid'] = $folderGuid;

    		$db = Zend_Db_Table::getDefaultAdapter()->query
    		("SELECT guid from KutuCatalog, KutuCatalogFolder where guid=catalogGuid AND folderGuid='$folderGuid'");
    		
    		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
    		
			$a['totalCount'] = count($rowset);	
			$a['totalCountRows'] = count($rowset);	
    		$limit = 20;
			$a['limit'] = $limit;
		}
			
		$this->view->aData = $a;
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
//		echo'<br>WAKTU EKSEKUSI: '. $time;
		$this->view->time = round($time,2) . ' detik';
	}
	function viewCatalogsPutusanAction()
	{
		$time_start = microtime(true);
		
    	$folderGuid = ($this->_getParam('node'))? $this->_getParam('node') : 'root';
		
		if($folderGuid=='root')
		{	
			$a = array();
			$a['totalCount'] = 0;
			$a['totalCountRows'] = 0;
			$a['folderGuid'] = $folderGuid;
			$limit = 20;
			$a['limit'] = $limit;
		}
		else 
		{
			$a = array();
			$a['folderGuid'] = $folderGuid;

    		$db = Zend_Db_Table::getDefaultAdapter()->query
    		("SELECT guid from KutuCatalog, KutuCatalogFolder where guid=catalogGuid AND folderGuid='$folderGuid'");
    		
    		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
    		
			$a['totalCount'] = count($rowset);	
			$a['totalCountRows'] = count($rowset);	
    		$limit = 20;
			$a['limit'] = $limit;
		}
			
		$this->view->aData = $a;
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
//		echo'<br>WAKTU EKSEKUSI: '. $time;
		$this->view->time = round($time,2) . ' detik';
	}
	function viewFolderNavigationAction()
	{
		$browserUrl = KUTU_ROOT_URL . '/view/node';
		
		$folderGuid = ($this->_getParam('node'))? $this->_getParam('node') : 'lt49f812abe4488';
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		
    	$aPath = array();
    	
    	if($folderGuid == 'root')
    	{
    		$aPath[0]['title'] = 'Root';
    		$aPath[0]['url'] = $browserUrl;
    	}
    	else 
    	{
    		$rowFolder = $tblFolder->find($folderGuid)->current();
   			if (!isset($rowFolder)){
    			$tblCatalogFolder = new Kutu_Core_Orm_Table_CatalogFolder();
    			$rowsetCatalogFolder = $tblCatalogFolder->fetchRow("catalogGuid='$folderGuid'");
    			if ($rowsetCatalogFolder) $rowFolder = $tblFolder->find($rowsetCatalogFolder->folderGuid)->current();
   			}
    		
    		if(!empty($rowFolder->path))
    		{
	    		$aFolderGuid = explode("/", $rowFolder->path);
	    		$sPath = 'root >';
	    		$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$i = 1;
	    		if(count($aFolderGuid))
	    		{
	    			$sPath1 = '';
	    			foreach ($aFolderGuid as $guid)
	    			{
	    				if(!empty($guid))
	    				{
	    					$rowFolder1 = $tblFolder->find($guid)->current();
	    				 	$sPath1 .= $rowFolder1->title . ' > ';
	    				 	$aPath[$i]['title'] = $rowFolder1->title . ' > ';
    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder1->guid;
	    				 	$i++;
	    				}
	    			}
	    			
	    			$aPath[$i]['title'] = $rowFolder->title;
					$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
	    		}
	    		
    		}
    		else 
    		{
    			$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$aPath[1]['title'] = $rowFolder->title;
    			$aPath[1]['url'] = $browserUrl.'/'.$rowFolder->guid;
    		}
    	}
    	
    	$this->view->aPath = $aPath;
    	
	}
	function viewFolderNavigationPeraturanAction()
	{
		$browserUrl = KUTU_ROOT_URL . '/pusatdata/view/nprt';
		
		$folderGuid = ($this->_getParam('nprt'))? $this->_getParam('nprt') : 'root';
//		$wview = $this->_request->getParams('nprt');print_r($wview['node']);
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		
    	$aPath = array();
    	
    	if($folderGuid == 'root')
    	{
    		$aPath[0]['title'] = 'Root';
    		$aPath[0]['url'] = $browserUrl;
    	}
    	else 
    	{
    		$rowFolder = $tblFolder->find($folderGuid)->current();
    		
    		if(!empty($rowFolder->path))
    		{
	    		$aFolderGuid = explode("/", $rowFolder->path);
	    		$sPath = 'root >';
	    		$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$i = 1;
	    		if(count($aFolderGuid))
	    		{
	    			$sPath1 = '';
	    			foreach ($aFolderGuid as $guid)
	    			{
	    				if(!empty($guid))
	    				{
	    					$rowFolder1 = $tblFolder->find($guid)->current();
	    				 	$sPath1 .= $rowFolder1->title . ' > ';
	    				 	$aPath[$i]['title'] = $rowFolder1->title . ' > ';
    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder1->guid;
	    				 	$i++;
	    				}
	    			}
	    			
	    			$aPath[$i]['title'] = $rowFolder->title;
					$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
	    		}
	    		
    		}
    		else 
    		{
    			$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$aPath[1]['title'] = $rowFolder->title;
    			$aPath[1]['url'] = $browserUrl.'/'.$rowFolder->guid;
    		}
    	}
    	
    	$this->view->aPath = $aPath;
    	
	}
	function viewFolderNavigationPutusanAction()
	{
		$browserUrl = KUTU_ROOT_URL . '/pusatdata/view/npts';
		
		$folderGuid = ($this->_getParam('npts'))? $this->_getParam('npts') : 'root';
//		$wview = $this->_request->getParams('nprt');print_r($wview['node']);
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		
    	$aPath = array();
    	
    	if($folderGuid == 'root')
    	{
    		$aPath[0]['title'] = 'Root';
    		$aPath[0]['url'] = $browserUrl;
    	}
    	else 
    	{
    		$rowFolder = $tblFolder->find($folderGuid)->current();
    		
    		if(!empty($rowFolder->path))
    		{
	    		$aFolderGuid = explode("/", $rowFolder->path);
	    		$sPath = 'root >';
	    		$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$i = 1;
	    		if(count($aFolderGuid))
	    		{
	    			$sPath1 = '';
	    			foreach ($aFolderGuid as $guid)
	    			{
	    				if(!empty($guid))
	    				{
	    					$rowFolder1 = $tblFolder->find($guid)->current();
	    				 	$sPath1 .= $rowFolder1->title . ' > ';
	    				 	$aPath[$i]['title'] = $rowFolder1->title . ' > ';
    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder1->guid;
	    				 	$i++;
	    				}
	    			}
	    			
	    			$aPath[$i]['title'] = $rowFolder->title;
					$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
	    		}
	    		
    		}
    		else 
    		{
    			$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$aPath[1]['title'] = $rowFolder->title;
    			$aPath[1]['url'] = $browserUrl.'/'.$rowFolder->guid;
    		}
    	}
    	
    	$this->view->aPath = $aPath;
    	
	}
	function viewFolderNavigationDetailAction()
	{
		$browserUrl = KUTU_ROOT_URL . '/app/hold/browser/view/node';
		$browserUrlP = KUTU_ROOT_URL . '/app/hold/browser/view/nprt';
		$browserUrlT = KUTU_ROOT_URL . '/app/hold/browser/view/npts';
		
		$folderGuid = ($this->_getParam('node'))? $this->_getParam('node') : 'root';
//		$wview = $this->_request->getParams('nprt');print_r($wview['node']);
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		
    	$aPath = array();
    	
    	if($folderGuid == 'root')
    	{
    		$aPath[0]['title'] = 'Root';
    		$aPath[0]['url'] = $browserUrl;
    	}
    	else 
    	{
    		$rowFolder = $tblFolder->find($folderGuid)->current();
    			if (!isset($rowFolder)){
    			$tblCatalogFolder = new Kutu_Core_Orm_Table_CatalogFolder();
    			$rowsetCatalogFolder = $tblCatalogFolder->fetchRow("catalogGuid='$folderGuid'");
    			$rowFolder = $tblFolder->find($rowsetCatalogFolder->folderGuid)->current();
    			}
    		if(!empty($rowFolder->path))
    		{
	    		$aFolderGuid = explode("/", $rowFolder->path);
	    		$sPath = 'root >';
	    		$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$i = 1;
	    		if(count($aFolderGuid))
	    		{
	    			$sPath1 = '';
	    			foreach ($aFolderGuid as $guid)
	    			{
	    				if(!empty($guid))
	    				{
	    					$rowFolder1 = $tblFolder->find($guid)->current();
	    				 	$sPath1 .= $rowFolder1->title . ' > ';
	    				 	$aPath[$i]['title'] = $rowFolder1->title;
	    				 	$rowFolder2 = $tblFolder->find($rowFolder1->guid)->current();
	    				 	if ($rowFolder2->parentGuid == 'lt49714f3105801' || $rowFolder->parentGuid == $folderGuid) {
		    				 	if (in_array($rowFolder2->guid,array('658','761','971'))) {
	    						$aPath[$i]['url'] = $browserUrlP.'/'.$rowFolder1->guid;
		    				 	} elseif (in_array($rowFolder2->guid,array('137','711'))) {
		    				 	$aPath[$i]['url'] = $browserUrlT.'/'.$rowFolder1->guid;	
		    				 	}
		    				 	else {
	    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder1->guid;
		    				 	}
	    				 	} 
	    				 	else
	    				 	{
		    				 	if (in_array($rowFolder2->parentGuid,array('658','761','971'))) {
	    						$aPath[$i]['url'] = $browserUrlP.'/'.$rowFolder1->guid;
		    				 	} elseif (in_array($rowFolder2->parentGuid,array('137','711'))) {
		    				 	$aPath[$i]['url'] = $browserUrlT.'/'.$rowFolder1->guid;	
		    				 	} else {
	    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder1->guid;
		    				 	}
	    				 	}
	    				 	$i++;
	    				}
	    			}
	    			
	    			$aPath[$i]['title'] = $rowFolder->title;
//					$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
	    				 	if ($aFolderGuid[0] == 'lt49714f3105801' || $aFolderGuid[0] == $folderGuid) {
		    				 	if (in_array($folderGuid,array('658','761','971'))) {
	    						$aPath[$i]['url'] = $browserUrlP.'/'.$rowFolder->guid;
		    				 	} elseif (in_array($folderGuid,array('137','711'))) {
		    				 	$aPath[$i]['url'] = $browserUrlT.'/'.$rowFolder->guid;	
		    				 	}
		    				 	else {
	    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
		    				 	}
	    				 	} 
	    				 	else
	    				 	{
		    				 	if (in_array($aFolderGuid[0],array('658','761','971'))) {
	    						$aPath[$i]['url'] = $browserUrlP.'/'.$rowFolder->guid;
		    				 	} elseif (in_array($aFolderGuid[0],array('137','711'))) {
		    				 	$aPath[$i]['url'] = $browserUrlT.'/'.$rowFolder->guid;	
		    				 	} else {
	    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
		    				 	}
	    				 	}//print_r($aFolderGuid[0]);
	    		}
	    		
    		}
    		else 
    		{
    			$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$aPath[1]['title'] = $rowFolder->title;
    			$aPath[1]['url'] = $browserUrl.'/'.$rowFolder->guid;
    		}
    	}
    	
    	$this->view->aPath = $aPath;
    	
	}
}

?>