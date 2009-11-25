<?php

class IndexController extends Zend_Controller_Action 
{
	function indexAction()
	{
		$this->_forward('view','browser');
	}
}

?>