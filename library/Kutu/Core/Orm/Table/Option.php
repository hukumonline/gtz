<?php

class Kutu_Core_Orm_Table_Option extends Zend_Db_Table_Abstract 
{
	protected $_name = 'options';
	
	public function insert (array $data)
	{
		if (empty($data['guid']))
		{
			$guidMan = new Kutu_Core_Guid;
			$data['guid'] = $guidMan->generateGuid();
		}
		
		return parent::insert($data);
		
	}
}