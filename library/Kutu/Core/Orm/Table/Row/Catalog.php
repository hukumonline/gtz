<?php

/**
 * manage Table_Row_Catalog
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_Row_Catalog extends Zend_Db_Table_Row_Abstract
{
	protected function _insert()
	{
		//add your custom logic here
		if(empty($this->guid))
		{
	    	$guidMan = new Kutu_Core_Guid();
	    	$this->guid = $guidMan->generateGuid();
		}
		
		if(!empty($this->shortTitle))
		{
			$sTitleLower = strtolower($this->shortTitle);
			$sTitleLower = preg_replace("/[^a-zA-Z0-9 s]/", "", $sTitleLower);
			$sTitleLower = str_replace(' ', '-', $sTitleLower);
			$this->shortTitle = $sTitleLower;
		}
		
		$today = date('Y-m-d H:i:s');
		if(empty($this->createdDate))
			$this->createdDate = $today;
		if(empty($this->modifiedDate) || $this->modifiedDate=='0000-00-00 00:00:00')
			$this->modifiedDate = $today;
		$this->deletedDate = '0000-00-00 00:00:00';
		
		if(empty($this->createdBy))
		{
			$auth = Zend_Auth::getInstance();
			if($auth->hasIdentity())
			{
				$this->createdBy = $auth->getIdentity()->username;
			}
		}
		
		if(empty($this->modifiedBy))
			$this->modifiedBy = $this->createdBy;
			
	}
	protected function _update()
	{
		$today = date('Y-m-d H:i:s');
    	$this->modifiedDate = $today;
    	
    	$userName = '';
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
		}
		
		$this->modifiedBy = $userName;
	}
	protected function _postDelete()
	{
		//find related docs and delete them
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$rowsetRelatedDocs = $tblRelatedItem->fetchAll("relatedGuid='$this->guid' AND relateAs='RELATED_FILE'");
		if(count($rowsetRelatedDocs))
		{
			foreach ($rowsetRelatedDocs as $rowRelatedDoc) 
			{
				$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
				$rowCatalog = $tblCatalog->find($rowRelatedDoc->itemGuid)->current();	
				$rowCatalog->delete();
			}
		}
		
		if($this->profileGuid == 'kutu_doc')
		{
			//get parentGuid
			$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
			$rowsetRelatedItem = $tblRelatedItem->fetchAll("itemGuid='$this->guid' AND relateAs='RELATED_FILE'");
			if(count($rowsetRelatedItem))
			{
				foreach($rowsetRelatedItem as $rowRelatedItem)
				{
					//must delete the physical files
					$rowsetCatAtt = $this->findDependentRowsetCatalogAttribute();
					$systemname = $rowsetCatAtt->findByAttributeGuid('docSystemName')->value;
					$parentGuid = $rowRelatedItem->relatedGuid;
				
					$sDir1 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$systemname;
					$sDir2 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
				
					if(file_exists($sDir1))
					{
						//delete file
						unlink($sDir1);
					}
					else 
						if(file_exists($sDir2))
						{
							//delete file
							unlink($sDir2);
						}
				}
			}
		}
			
		//delete from table CatalogAttribute
		$tblCatalogAttribute = new Kutu_Core_Orm_Table_CatalogAttribute();
		$tblCatalogAttribute->delete("catalogGuid='$this->guid'");
		
		//delete catalogGuid from table CatalogFolder
		$tblCatalogFolder = new Kutu_Core_Orm_Table_CatalogFolder();
		$tblCatalogFolder->delete("catalogGuid='$this->guid'");
		
		//delete guid from table AssetSetting
		$tblAssetSetting = new Kutu_Core_Orm_Table_AssetSetting();
		$tblAssetSetting->delete("guid='$this->guid'");
		
		//delete from table relatedItem
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$tblRelatedItem->delete("itemGuid='$this->guid'");
		$tblRelatedItem->delete("relatedGuid='$this->guid'");
		
		//delete from lucene index
		//check if guid exist in index, then delete
		
//		$indexEngine = new Kutu_Search_Index_Engine();
//		$indexEngine->deleteCatalogFromIndex($this->guid);
		
		
		$indexingEngine = Kutu_Search::manager();		
		
		try {		
			$hits = $indexingEngine->deleteCatalogFromIndex($this->guid);
		}
		catch (Exception $e)
		{
			
		}
		
		
		/*
		$tblTmpIndex = new Kutu_Core_Orm_Table_TmpIndex();
		$rowTmpIndex = $tblTmpIndex->fetchNew();
		$rowTmpIndex->catalogGuid = $this->guid;
		$rowTmpIndex->status = 'delete';
		$rowTmpIndex->save();
		*/
		
		$tblTmpIndex = new Kutu_Core_Orm_Table_TmpIndex();
		$tblTmpIndex->delete("catalogGuid='$this->guid'");
		
		//delete physical catalog folder from uploads/files/[catalogGuid]
		$sDir = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$this->guid;
		try {
			if(is_dir($sDir))
				rmdir($sDir);
		}
		catch (Exception $e)
		{
			
		}
		
		$sDir = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images';
		try {
		   	if (file_exists($sDir."/".$this->guid.".gif")) 		{ unlink($sDir."/".$this->guid.".gif"); 	}
		   	if (file_exists($sDir."/tn_".$this->guid.".gif")) 	{ unlink($sDir."/tn_".$this->guid.".gif"); 	}
		   	if (file_exists($sDir."/".$this->guid.".jpg")) 		{ unlink($sDir."/".$this->guid.".jpg"); 	}
	   	   	if (file_exists($sDir."/tn_".$this->guid.".jpg")) 	{ unlink($sDir."/tn_".$this->guid.".jpg"); 	}
		   	if (file_exists($sDir."/".$this->guid.".jpeg")) 	{ unlink($sDir."/".$this->guid.".jpeg"); 	}
	   	   	if (file_exists($sDir."/tn_".$this->guid.".jpeg")) 	{ unlink($sDir."/tn_".$this->guid.".jpeg"); }
		   	if (file_exists($sDir."/".$this->guid.".png")) 		{ unlink($sDir."/".$this->guid.".png"); 	}
	   	   	if (file_exists($sDir."/tn_".$this->guid.".png")) 	{ unlink($sDir."/tn_".$this->guid.".png"); 	}
		}
		catch (Exception $e)
		{
			
		}
		
		//delete from ACL
	}
	
	public function test()
	{
		echo $this->getTableClass();
	}
	
	public function findDependentRowsetCatalogAttribute()
	{
		return $this->findDependentRowset('Kutu_Core_Orm_Table_CatalogAttribute');
	}
	
	public function relateTo($relatedGuid, $as='RELATED_ITEM', $valRelation = 0)
	{
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		if(empty($this->guid))
			throw new Zend_Exception('Can not relate to empty GUID');
		
		$rowsetRelatedItem = $tblRelatedItem->find($this->guid, $relatedGuid, $as);
		if(count($rowsetRelatedItem) > 0)
		{
			if ($as == "RELATED_COMMENT") {
				$row = $rowsetRelatedItem->current();
				$row->valueIntRelation = $valRelation;
			}
		}
		else 
		{
			$row = $tblRelatedItem->createNew();
			$row->itemGuid = $this->guid;
			$row->relatedGuid = $relatedGuid;
			$row->relateAs = $as;
			$row->valueIntRelation = $valRelation;
		}
		$row->save();
	}
}

?>