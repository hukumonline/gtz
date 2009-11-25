<?php

class BrowserController extends Zend_Controller_Action 
{
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
	}
	function viewAction()
	{
		$node = ($this->_getParam('node'))? $this->_getParam('node') : '';
		$page = $this->_getParam('page',1);
		$this->view->node = $node;
		$this->view->page = $page;
	}
	function folderAction()
	{
		
	}
	function detailAction()
	{
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		$node = ($this->_getParam('node'))? $this->_getParam('node') : '';
				
    	$this->view->catalogGuid = $catalogGuid;
    	$this->view->node = $node;
	}
	function searchAction()
	{
		$query = ($this->_getParam('searchQuery'))? $this->_getParam('searchQuery') : '';
		$keys = ($this->_getParam('keys'))? $this->_getParam('keys') : '';
		
		$this->_helper->layout()->searchQuery = $query;
		$this->_helper->layout()->categorySearchQuery = $keys;
//		$this->view->query = $query.' profile:(kutu_putusan)';
		$this->view->query = $query;
		$this->view->keys = $keys;
	}
    function downloadFileAction()
    {
    	$catalogGuid = $this->_getParam('guid');
    	$parentGuid = $this->_getParam('parent');
    	
    	$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
    	$rowsetCatalog = $tblCatalog->find($catalogGuid);
    	
    	if(count($rowsetCatalog))
    	{
    		$rowCatalog = $rowsetCatalog->current();
    		$rowsetCatAtt = $rowCatalog->findDependentRowsetCatalogAttribute();
    		
	    	$contentType = $rowsetCatAtt->findByAttributeGuid('docMimeType')->value;
			$filename = $systemname = $rowsetCatAtt->findByAttributeGuid('docSystemName')->value;
			$oriName = $oname = $rowsetCatAtt->findByAttributeGuid('docOriginalName')->value;
			
			$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
			$rowsetRelatedItem = $tblRelatedItem->fetchAll("itemGuid='$catalogGuid' AND relateAs='RELATED_FILE'");
			
			$flagFileFound = false;
			
			foreach($rowsetRelatedItem as $rowRelatedItem)
			{
				if(!$flagFileFound)
				{
					$parentGuid = $rowRelatedItem->relatedGuid;
					$sDir1 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$systemname;
					$sDir2 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
					$sDir3 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$oname;
					$sDir4 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$oname;
					
					if(file_exists($sDir1))
					{
						$flagFileFound = true;
						header("Content-type: $contentType");
						header("Content-Disposition: attachment; filename=$oriName");
						@readfile($sDir1);
						die();
					}
					else 
						if(file_exists($sDir2))
						{
							$flagFileFound = true;
							header("Content-type: $contentType");
							header("Content-Disposition: attachment; filename=$oriName");
							@readfile($sDir2);
							die();
						}
						if (file_exists($sDir3))
						{
							$flagFileFound = true;
							header("Content-type: $contentType");
							header("Content-Disposition: attachment; filename=$oriName");
							@readfile($sDir3);
							die();
						}
						if (file_exists($sDir4))
						{
							$flagFileFound = true;
							header("Content-type: $contentType");
							header("Content-Disposition: attachment; filename=$oriName");
							@readfile($sDir4);
							die();
						}
						else 
						{
							$flagFileFound = false;
							$this->_forward('forbidden','browser');
						}
				}
			}
			
    	}
    	else 
    	{
    		$flagFileFound = false;
    		$this->_forward('forbidden','browser');
    	}
    }
    function forbiddenAction() 	
    {
//    	$this->_helper->layout()->disableLayout();
    }
}

?>