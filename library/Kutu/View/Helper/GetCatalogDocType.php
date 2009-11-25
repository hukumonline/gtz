<?php

class Kutu_View_Helper_GetCatalogDocType
{
	public function GetCatalogDocType($catalogGuid)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowsetCatalog = $tblCatalog->find($catalogGuid);
		
		if(count($rowsetCatalog))
		{
			$rowCatalog = $rowsetCatalog->current();
			$rowsetCatAtt = $rowCatalog->findDependentRowsetCatalogAttribute();
			
			$docType = $this->imageDocumentType($this->dl_file($rowsetCatAtt->findByAttributeGuid('docOriginalName')->value));
		}
		else 
		{
			$docType = '';
		}
		
		return $docType;
	}
	static function dl_file($file)
	{
	    //Gather relevent info about file
	    $filename = basename($file);
	    $file_extension = strtolower(substr(strrchr($filename,"."),1));		
	    return $file_extension;
	}
	static function imageDocumentType($type)
	{
		switch ($type)
		{
			case 'pdf':
				$type = '<img src="'.KUTU_ROOT_URL.'/resources/img/file_type/pdf.gif">';
			break;
			case 'doc':
				$type = '<img src="'.KUTU_ROOT_URL.'/resources/img/file_type/doc.gif">';
			break;
			case 'xls':
				$type = '<img src="'.KUTU_ROOT_URL.'/resources/img/file_type/xls.gif">';
			break;
			case 'html':
			case 'htm':
				$type = '<img src="'.KUTU_ROOT_URL.'/resources/img/file_type/html.gif">';
			break;
			case 'jpg':
			case 'jpeg':
				$type = '<img src="'.KUTU_ROOT_URL.'/resources/img/file_type/jpg.gif">';
			break;
			default:
				$type = '<img src="'.KUTU_ROOT_URL.'/resources/img/file_type/txt.gif">';
		}
		
		return $type;
	}
}

?>