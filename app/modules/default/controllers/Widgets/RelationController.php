<?php

class Widgets_RelationController extends Zend_Controller_Action
{
	public function documentAction()
	{
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_FILE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		$this->view->rowsetRelatedItem = $rowsetRelatedItem;
	}
	public function viewDasarHukumAction()
	{
		$node = ($this->_getParam('node'))? $this->_getParam('node') : '';
		
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';

		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_BASE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		$tblCatalogAttribute  = new Kutu_Core_Orm_Table_CatalogAttribute();
		
		$this->view->rowsetRelatedItem = $rowsetRelatedItem;
		$this->view->catalogGuid = $catalogGuid;
		
		if ($nprt)
		{
	    	$this->view->node = $nprt;
		}
		elseif ($npts)
		{
	    	$this->view->node = $npts;
		}
		else 
		{
	    	$this->view->node = $node;
		}
	}
	public function peraturanPelaksanaAction()
	{
		$node = ($this->_getParam('node'))? $this->_getParam('node') : '';
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "itemGuid='$catalogGuid' AND relateAs='RELATED_BASE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		$tblCatalogAttribute  = new Kutu_Core_Orm_Table_CatalogAttribute();
		
		$this->view->rowsetRelatedItem = $rowsetRelatedItem;
		
		if ($nprt)
		{
	    	$this->view->node = $nprt;
		}
		elseif ($npts)
		{
	    	$this->view->node = $npts;
		}
		else 
		{
	    	$this->view->node = $node;
		}
		
	}
	public function viewHistoryAction()
	{
		$node = ($this->_getParam('node'))? $this->_getParam('node') : '';
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		
		$a2 = array();
		$aNodesTraversed = array();
		$this->_traverseHistory($aNodesTraversed, $a2,$catalogGuid);
		
		$tblCatalogAttribute  = new Kutu_Core_Orm_Table_CatalogAttribute();
		$aTmp2['node'] = $catalogGuid;
		$aTmp2['nodeLeft'] = 'tmpLeft';
		$aTmp2['nodeRight'] =  'tmpRight';
		$aTmp2['description'] = '';
		$aTmp2['relationType'] = '';
		
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedTitle'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
		if(isset($rowCatalogAttribute->value))
			$aTmp2['title'] = $rowCatalogAttribute->value;
		else
			return 'No-Title';
			
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedSubTitle'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
		if(isset($rowCatalogAttribute->value))
			$aTmp2['subTitle'] = $rowCatalogAttribute->value;
		else
			return 'No-Title';
		
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedDate'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
		if(isset($rowCatalogAttribute->value))
			$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
		else
			return '';
		
		array_push($a2, $aTmp2);
		
		UtilHistorySort::sort($a2, 'fixedDate', false);
		
		$this->view->aData = $a2;
		$this->view->catalogGuid = $catalogGuid;
		
		if ($nprt)
		{
	    	$this->view->node = $nprt;
		}
		elseif ($npts)
		{
	    	$this->view->node = $npts;
		}
		else 
		{
	    	$this->view->node = $node;
		}
	}
	function _traverseHistory(&$aNodesTraversed, &$a2, $node)
	{
		array_push($aNodesTraversed, $node);
		$aTmp = $this->_getNodes($node);
		
		foreach ($aTmp as $node2)
		{
			if(!$this->_checkTraverse($aNodesTraversed, $node2['node']))
			{
				array_push($a2, $node2);
				$this->_traverseHistory($aNodesTraversed, $a2, $node2['node']);
			}
		}
		return true;
	}
	function _getNodes($node)
	{
		$a = array();
		
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$tblCatalogAttribute  = new Kutu_Core_Orm_Table_CatalogAttribute();
		
		$where = "relatedGuid='$node' AND relateAs='RELATED_HISTORY'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		foreach ($rowsetRelatedItem as $row)
		{
			$aTmp2['node'] = $row->itemGuid;
			$aTmp2['nodeLeft'] = $row->itemGuid;
			$aTmp2['nodeRight'] =  $node;
			$aTmp2['description'] = $row->description;
			$aTmp2['relationType'] = $row->relationType;
			
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['title'] = $rowCatalogAttribute->value;
			else
				$aTmp2['title'] = 'No-Title';
				
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedSubTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['subTitle'] = $rowCatalogAttribute->value;
			else
				$aTmp2['subTitle'] = 'No-Title';
			
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedDate'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
			else
				$aTmp2['fixedDate'] = '';
			
			array_push($a, $aTmp2);	
		}
		
		$where = "itemGuid='$node' AND relateAs='RELATED_HISTORY'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		foreach ($rowsetRelatedItem as $row)
		{
			$aTmp2['node'] = $row->relatedGuid;
			$aTmp2['nodeLeft'] = $node;
			$aTmp2['nodeRight'] =  $row->relatedGuid;
			$aTmp2['description'] = $row->description;
			$aTmp2['relationType'] = $row->relationType;
			
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['title'] = $rowCatalogAttribute->value;
			else
				$aTmp2['title'] = 'No-Title';
				
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedSubTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['subTitle'] = $rowCatalogAttribute->value;
			else
				$aTmp2['subTitle'] = 'No-Title';
			
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedDate'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2); 
			if(isset($rowCatalogAttribute->value))
				$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
			else
				$aTmp2['fixedDate'] = '';
			
			array_push($a, $aTmp2);	
		}
		
		return $a;
	}
	function _checkTraverse($a, $node)
	{
		foreach($a as $row)
		{
			if($row == $node)
			{
				return true;
			}
		}
		return false;
	}
	public function otherAction()
	{
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_OTHER'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		
		$this->view->rowsetRelatedItem = $rowsetRelatedItem;
	}
	public function viewFolderAction()
	{
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($catalogGuid)->current();
		
		if ($rowCatalog)
		{
		$rowsetFolder = $rowCatalog->findManyToManyRowset('Kutu_Core_Orm_Table_Folder', 'Kutu_Core_Orm_Table_CatalogFolder');
		
		$this->view->rowsetFolder = $rowsetFolder;
		$this->view->catalogGuid = $catalogGuid;
		}
	}
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper', 'Kutu_View_Helper');
	}
}

class UtilHistorySort 
{
    static private $sortfield = null;
    static private $sortorder = 1;
    static private function sort_callback(&$a, &$b) {
        if($a[self::$sortfield] == $b[self::$sortfield]) return 0;
        return ($a[self::$sortfield] < $b[self::$sortfield])? -self::$sortorder : self::$sortorder;
    }
    static function sort(&$v, $field, $asc=true) {
        self::$sortfield = $field;
        self::$sortorder = $asc? 1 : -1;
        usort($v, array('UtilHistorySort', 'sort_callback'));
    }
}

?>