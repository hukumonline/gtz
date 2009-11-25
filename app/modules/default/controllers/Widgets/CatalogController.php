<?php

class Widgets_CatalogController extends Zend_Controller_Action
{
	function viewerAction()
	{
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		$node = ($this->_getParam('node'))? $this->_getParam('node') : '';
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		if(!empty($catalogGuid))
		{
			$rowCatalog = $tblCatalog->find($catalogGuid)->current();
			
			if ($rowCatalog)
			{
			
			$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
			
			$tableProfileAttribute = new Kutu_Core_Orm_Table_ProfileAttribute();
			$profileGuid = $rowCatalog->profileGuid;
			$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
			$rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
			
			$aAttribute = array();
			$i = 0;
			$tblAttribute = new Kutu_Core_Orm_Table_Attribute();
			foreach ($rowsetProfileAttribute as $rowProfileAttribute)
			{
				if($rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid))
				{
					$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid);
					
					$rowsetAttribute = $tblAttribute->find($rowCatalogAttribute->attributeGuid);
					if(count($rowsetAttribute))
					{
						$rowAttribute = $rowsetAttribute->current();
						$aAttribute[$i]['name'] =  $rowAttribute->name;
					}
					else 
					{
						$aAttribute[$i]['name'] =  '';
					}
					$aAttribute[$i]['value'] = $rowCatalogAttribute->value;
					
				}
				else 
				{
					
				}
				$i++;
			}
			
		$this->view->aAttribute = $aAttribute;
		$this->view->rowCatalog = $rowCatalog;
		$this->view->rowsetCatalogAttribute = $rowsetCatalogAttribute;
		$this->view->node = $node;
		$this->view->catalogGuid = $catalogGuid;
		$this->view->profileGuid = $profileGuid;
		
		$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedExpired');
		if(!empty($rowCatalogAttribute->value))
		{
			$tDate = $rowCatalogAttribute->value;
			$aDate = explode('-', $tDate);
			$year=$aDate[0];
			$month=$aDate[1];
			$day=$aDate[2];
			$hour="00";
			$minute="00";
			$second="00";
			
			$event="My birthday";
			
			$time=mktime($hour, $minute, $second, $month, $day, $year);
			
			$timecurrent=date('U');
			$cuntdowntime=$time-$timecurrent;
			$cuntdownminutes=$cuntdowntime/60;
			$cuntdownhours=$cuntdowntime/3600;
			$cuntdowndays=$cuntdownhours/24;
			$cuntdownmonths=$cuntdowndays/30;
			$cuntdownyears=$cuntdowndays/365;
			
			if($cuntdowndays < 0)
			{
				echo "<script>alert('Dokumen perjanjian ini telah berakhir masa berlakunya.');</script>";
				echo "<br><strong>Dokumen perjanjian ini telah berakhir masa berlakunya.</strong>";
			}
			else 
			{
				echo "<br><strong>Dokumen perjanjian ini akan berakhir masa berlakunya dalam ".round($cuntdowndays)." hari.</strong>";
			}
		}
		
			}
		}
		
	}
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper', 'Kutu_View_Helper');		
	}
}