<?php

class Api_CatalogController extends Zend_Controller_Action 
{
	public function getcatalogsinfolderAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$r = $this->getRequest();
		$folderGuid = $r->getParam('folderGuid');
		$start = ($r->getParam('start'))? $r->getParam('start') : 0;
		$limit = ($r->getParam('limit'))? $r->getParam('limit'): 0;
		$sort = ($r->getParam('sort'))? $r->getParam('sort') : 'regulationType desc, year desc';
		
		$a = array();
		$a['folderGuid'] = $folderGuid;

//		$db = Zend_Db_Table::getDefaultAdapter()->query
//		("SELECT catalogGuid as guid from KutuCatalogFolder where folderGuid='$folderGuid'");
//		
//		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
//		
//		$indexingEngine = Kutu_Search::manager();
//		
//		$numi = count($rowset);
//		$sSolr = "id:(";
//		for($i=0;$i<$numi;$i++)
//		{
//			$row = $rowset[$i];
//			$sSolr .= $row->guid .' ';
//		}
//		$sSolr .= ')';
//		
//		if(!$numi)
//			$sSolr="id:(hfgjhfdfka)";
			
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->fetchFromFolder($folderGuid,$start,$limit);		
		
		$solrNumFound = count($rowset);

//		$solrResult = $solrAdapter->findAndSort($sSolr,$start,$limit, array('sort'=>$sort));
//		$indexingEngine->testFind($sSolr,$start,$limit);die();
//		print_r($sSolr);die();
//		$solrResult = new ArrayObject($indexingEngine->find($sSolr));
//		$solrResult = $indexingEngine->find($sSolr,$start,$limit);
//		$solrNumFound = count($solrResult);//$solrResult->response->numFound;
//		$solrNumFound = count($solrResult->response->docs);

		$ii=0;
		if($solrNumFound==0)
		{
			$a['catalogs'][0]['guid']= 'XXX';
			$a['catalogs'][0]['title']= "No Data";
			$a['catalogs'][0]['subTitle']= "";
			$a['catalogs'][0]['createdDate']= '';
			$a['catalogs'][0]['modifiedDate']= '';
		}
		else 
		{
//			if($solrNumFound>$limit)
//				$numRowset = $limit ; 
//			else 
//				$numRowset = $solrNumFound;

//			$solrResult = new LimitIterator($solrResult->getIterator(),$start * $limit, $limit);
			
//			for($ii=0;$ii<$numRowset;$ii++)
			foreach ($rowset as $row)
			{
				$rowsetCatalogAttribute = $row->findDependentRowsetCatalogAttribute();
				$title = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle');
				$subTitle = $rowsetCatalogAttribute->findByAttributeGuid('fixedSubTitle');
//				if($ii>=$start && $ii<$start+$limit){
//				$row = $solrResult->response->docs[$ii];
//				if(!empty($row))
//				{
					$a['catalogs'][$ii]['guid']= $row->guid;
					
					if($row->profileGuid == 'kutu_doc')
						$stitle = 'File : '.$title->value;
					else 
						$stitle = $title->value;
						
					$a['catalogs'][$ii]['title']= $stitle;
					
					if(!isset($subTitle->value))
		        		$a['catalogs'][$ii]['subTitle'] = '';
		        	else 
		        		$a['catalogs'][$ii]['subTitle']= $subTitle->value;
		        		
					$a['catalogs'][$ii]['createdDate']= $row->createdDate;
					$a['catalogs'][$ii]['modifiedDate']= $row->modifiedDate;
					
					$ii++;
//				}
//				}
			}
		}
		
		echo Zend_Json::encode($a);
	}
	public function getsearchAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$r = $this->getRequest();
		
		$query = ($r->getParam('query'))? $r->getParam('query') : '';
		$start = ($r->getParam('start'))? $r->getParam('start') : 0;
		$limit = ($r->getParam('limit'))? $r->getParam('limit'): 20;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'regulationOrder';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$a = array();

		$a['query']	= $query;
		
		$indexingEngine = Kutu_Search::manager();
		$hits = $indexingEngine->find($query, $start, $limit);

		$num = $hits->response->numFound;
		
		$solrNumFound = count($hits->response->docs);
		
		$ii=0;
		if($solrNumFound==0)
		{
			$a['catalogs'][0]['guid']= 'XXX';
			$a['catalogs'][0]['title']= "No Data";
			$a['catalogs'][0]['subTitle']= "";
			$a['catalogs'][0]['createdDate']= '';
			$a['catalogs'][0]['modifiedDate']= '';
		}
		else 
		{
			if($solrNumFound>$limit)
				$numRowset = $limit ; 
			else 
				$numRowset = $solrNumFound;
				
			for($ii=0;$ii<$numRowset;$ii++)
			{
				$row = $hits->response->docs[$ii];
				if(!empty($row))
				{
					if($row->profile == 'kutu_doc')
					{
						$title = 'File : '.$row->title;
						
						$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
						$rowset = $tblRelatedItem->fetchRow("itemGuid='$row->id'");
						
						if ($rowset)
							$guid = $rowset->relatedGuid;
						else 
							$guid = $row->id;
							
					} else {
						$title = $row->title;
						$guid = $row->id;
					}
					
					$a['catalogs'][$ii]['title']= $title;
					$a['catalogs'][$ii]['guid']= $guid;
					
					if(!isset($row->subTitle))
		        		$a['catalogs'][$ii]['subTitle'] = '';
		        	else 
		        		$a['catalogs'][$ii]['subTitle']= $row->subTitle;
		        	
		        	if($row->profile == 'kutu_doc') {
		        		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		        		$rowsetRelatedItem = $tblRelatedItem->fetchRow("itemGuid='$row->id' AND relateAs='RELATED_FILE'");
		        		if ($rowsetRelatedItem) {
							$parentGuid= $rowsetRelatedItem->relatedGuid;
		        		} else {
		        			$parentGuid = '';
		        		}
		        	}
		        	else 
		        	{
		        		$tblCatalogFolder = new Kutu_Core_Orm_Table_CatalogFolder();
		        		$rowsetCatalogFolder = $tblCatalogFolder->fetchRow("catalogGuid='$row->id'");
		        		if ($rowsetCatalogFolder)
		        			$parentGuid= $rowsetCatalogFolder->folderGuid;
		        		else 
		        			$parentGuid='';
		        	}
		        	
					$a['catalogs'][$ii]['folderGuid'] = $parentGuid;
					$a['catalogs'][$ii]['createdDate']= $row->createdDate;
					$a['catalogs'][$ii]['modifiedDate']= $row->modifiedDate;
				}
			}
		}
		
		echo Zend_Json::encode($a);
	}
}

?>