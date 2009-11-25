<?php

/**
 * @package kutump
 * @copyright 2008-2009 hukumonline.com/en.hukumonline.com
 * @author Nihki Prihadi <nihki@hukumonline.com>
 *
 * $Id: Userstatus.php 2009-01-10 19:57: $
 */

class Kutu_Core_Orm_Table_UserStatus extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserStatus';
	protected $_referenceMap = array(
		'User' => array(
			'columns'		=> 'accountStatusId',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_UserDetail',
			'refColumns'	=> 'periodeId'
		)
	);
}