<?php

class Widgets_SearchController extends Zend_Controller_Action
{
	function viewResultAction()
	{
		$time_start = microtime(true);
		
		$query = ($this->_getParam('query'))? $this->_getParam('query') : '';
		$keys = ($this->_getParam('keys'))? $this->_getParam('keys') : '';

		switch ($keys) {
			case 'title':
				$query = 'title:'.$query;
			break;
			case 'number':
				$query = 'number:'.$query;
			break;
			case 'judge':
				$query = 'judge:'.$query;
			break;
			case 'subtitle':
				$query = 'subtitle:'.$query;
			break;
			case 'year':
				$query = 'year:'.$query;
			break;
			default:
				$query = $query;
		}
		
		
		$a = array();
		
		$a['query']	= $query;

		$indexingEngine = Kutu_Search::manager();
		$hits = $indexingEngine->find($query);
		
		$paginator = Zend_Paginator::factory($hits);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(20);
		
		$this->view->hits = $paginator;
		
		/*
		$num = $hits->response->numFound;
		$limit = 20;
		
		$a['totalCount'] = $num;
		$a['limit'] = $limit;
		
		$ii=0;
		
		if($a['totalCount']==0)
		{
			$a['catalogs'][0]['guid']= 'XXX';
			$a['catalogs'][0]['title']= "No Data";
			$a['catalogs'][0]['subTitle']= "";
			$a['catalogs'][0]['createdDate']= '';
			$a['catalogs'][0]['modifiedDate']= '';
		}
					
		
		$this->view->aData = $a;
		$this->view->query = $query;
		$this->view->hits = $hits;
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
//		echo'<br>WAKTU EKSEKUSI: '. $time;
		$this->view->time = round($time,2);
		*/
	}
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper', 'Kutu_View_Helper');  
	}
}

?>