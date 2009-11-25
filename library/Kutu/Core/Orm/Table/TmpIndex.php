<?php

/**
 * @package kutump
 * @copyright 2008-2009 hukumonline.com/en.hukumonline.com
 * @author Nihki Prihadi <nihki@hukumonline.com>
 *
 * $Id: KutuTmpIndex.php 2009-04-13 11:01: $
 */

class Kutu_Core_Orm_Table_TmpIndex extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuIndexTmp';
	
	public function insert(array $data)
	{
		if(empty($data['guid']))
		{
	    	$guidMan = new Kutu_Core_Guid();
	    	$data['guid'] = $guidMan->generateGuid();
		}
		if (empty($data['createdDate']))
		{
			$data['createdDate'] = date('Y-m-d H:i:s');
		}
		return parent::insert($data);
	}
	public function delete($where)
	{
		return parent::delete($where);
	}
    function countCatalogsTempIndex()
    {
    	$db = $this->_db->query
    	("SELECT count(*) count From KutuIndexTmp");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
//    function countCatalogsTempIndexPeraturan()
//    {
//    	$db = $this->_db->query
//    	("SELECT count(*) count From KutuIndexTmp WHERE profileGuid IN ('kutu_doc','kutu_peraturan','kutu_putusan','kutu_peraturan_kolonial','kutu_rancangan_peraturan')");
//    	
//    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
//    	
//    	return ($dataFetch[0]['count']);
//    }
//    function countCatalogsTempIndexArticle()
//    {
//    	$db = $this->_db->query
//    	("SELECT count(*) count From KutuIndexTmp WHERE profileGuid IN ('aktual','suratpembaca','komunitas','news','talks','resensi','isuhangat','fokus','kolom','tokoh','jeda','tajuk','info','utama')");
//    	
//    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
//    	
//    	return ($dataFetch[0]['count']);
//    }
}


?>