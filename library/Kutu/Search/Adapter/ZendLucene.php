<?php

/**
 * module search for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Search_Adapter_ZendLucene extends Kutu_Search_Adapter_Abstract 
{
	private $_index;
	
	private $_pdfExtractor;
	private $_wordExtractor;
	
	
	
	public function __construct($indexDir=null)
	{
		if(empty($indexDir))
		{
	    	throw new Zend_Exception('Index Directory can not be empty!');
			
		}
		
		try 
		{
			$indexDir = KUTU_ROOT_DIR.$indexDir;
			$this->_index = Zend_Search_Lucene::open($indexDir);
		}
		catch (Exception $e)
		{
			$this->_index = Zend_Search_Lucene::create($indexDir);
		}
		
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive());
	}
	public function setExtractor($type, $extractor)
	{
		switch (strtolower($type))
		{
			case 'pdf':
				$this->_pdfExtractor = $extractor;
				break;
			case 'word':
				$this->_wordExtractor = $extractor;
				break;
		}
	}
	public function indexCatalog($catalogGuid)
	{
		$index = $this->_index;
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowsetCatalog = $tblCatalog->find($catalogGuid);
		if(count($rowsetCatalog))
		{
			//check if guid exist in index, then delete
			$term = new Zend_Search_Lucene_Index_Term($catalogGuid, 'guid');
			$docIds  = $index->termDocs($term);
			
			foreach ($docIds as $id) {
			    $doc = $index->getDocument($id);
			    $index->delete($id);
			}
			
			$rowCatalog = $rowsetCatalog->current();
			$doc = new Zend_Search_Lucene_Document();
			$doc->addField(Zend_Search_Lucene_Field::Keyword('guid', $rowCatalog->guid));
			
			//fill parentGuid with catalogGuid if it's kutu_doc
			if($rowCatalog->profileGuid=='kutu_doc')
			{
				$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
				$rowset = $tblRelatedItem->fetchAll("itemGuid='$rowCatalog->guid' AND relateAs='RELATED_FILE'");
				if(count($rowset))
				{
					$row = $rowset->current();
					$parentCatalogGuid = $row->relatedGuid;
					$doc->addField(Zend_Search_Lucene_Field::Keyword('parentGuid', $parentCatalogGuid));
				}
			}
			else 
			{
				$doc->addField(Zend_Search_Lucene_Field::Keyword('parentGuid', $rowCatalog->guid));
			}
			
			$doc->addField(Zend_Search_Lucene_Field::Text('profile', $rowCatalog->profileGuid));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('publishedDate', $this->_filterDateTime($rowCatalog->publishedDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('expiredDate', $this->_filterDateTime($rowCatalog->expiredDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('createdBy', $rowCatalog->createdBy));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('modifiedBy', $rowCatalog->modifiedBy));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('createdDate', $this->_filterDateTime($rowCatalog->createdDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('modifiedDate', $this->_filterDateTime($rowCatalog->modifiedDate)));
			$doc->addField(Zend_Search_Lucene_Field::Keyword('status', $rowCatalog->status));
			
			if($rowCatalog->profileGuid=='kutu_doc')
			{
				$doc->addField(Zend_Search_Lucene_Field::Keyword('objectType', 'file'));
			}
			else 
			{
				$doc->addField(Zend_Search_Lucene_Field::Keyword('objectType', 'catalog'));
			}
			
			$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
			
			if(count($rowsetCatalogAttribute))
			{
				foreach ($rowsetCatalogAttribute as $rowCatalogAttribute)
				{
					switch ($rowCatalogAttribute->attributeGuid)
					{
						case 'fixedTitle':
						case 'title':
							$doc->addField(Zend_Search_Lucene_Field::Text('title', $rowCatalogAttribute->value));
							break;
							
						case 'fixedSubTitle':
						case 'subTitle':
							$doc->addField(Zend_Search_Lucene_Field::Text('subtitle', $rowCatalogAttribute->value));
							break;
							
						case 'fixedContent':
						case 'content':
							$docHtml = Zend_Search_Lucene_Document_Html::loadHTML($rowCatalogAttribute->value);
							$cleanedText = $docHtml->getFieldValue('body');
							$doc->addField(Zend_Search_Lucene_Field::UnStored('content', $cleanedText));
							break;
							
						case 'fixedKeywords':
						case 'keywords':
							$doc->addField(Zend_Search_Lucene_Field::UnStored('keywords', $rowCatalogAttribute->value));
							break;
						
						case 'fixedDescription':
						case 'description':
							$doc->addField(Zend_Search_Lucene_Field::Text('description', $rowCatalogAttribute->value));
							break;
						
						case 'ptsKetua':
							$doc->addField(Zend_Search_Lucene_Field::Text('judge', $rowCatalogAttribute->value));
							break;
							
						case 'prtNomor':
						case 'fixedNomor':
						case 'fixedNumber':
						case 'nomor':
						case 'ptsNomor':
							$doc->addField(Zend_Search_Lucene_Field::UnStored('number', $rowCatalogAttribute->value));
							break;
						
						case 'prtTahun':
						case 'fixedTahun':
						case 'fixedYear':
						case 'tahun':
						case 'ptsTahun':
							$doc->addField(Zend_Search_Lucene_Field::UnStored('year', $rowCatalogAttribute->value));
							break;
							
						default:
							//check if attribute is a datetime field
							$tblAttribute = new Kutu_Core_Orm_Table_Attribute();
							$rowAttribute = $tblAttribute->find($rowCatalogAttribute->attributeGuid)->current();
							if($rowAttribute->type == 4)
							{
								$doc->addField(Zend_Search_Lucene_Field::UnStored(strtolower($rowCatalogAttribute->attributeGuid), $this->_filterDateTime($rowCatalogAttribute->value)));
							}
							else 
							{
								if($rowAttribute->type == 2)
								{
									$docHtml = Zend_Search_Lucene_Document_Html::loadHTML($rowCatalogAttribute->value);
									$cleanedText = $docHtml->getFieldValue('body');
									$doc->addField(Zend_Search_Lucene_Field::UnStored(strtolower($rowCatalogAttribute->attributeGuid), $cleanedText));
								}
								else 
								{
									$doc->addField(Zend_Search_Lucene_Field::UnStored(strtolower($rowCatalogAttribute->attributeGuid), $rowCatalogAttribute->value));
								}
							}
							break;
							
					}
				}
				//if profile=kutu_doc, extract text from its file and put it in content field
				if($rowCatalog->profileGuid=='kutu_doc')
				{
					$row = $rowsetCatalogAttribute->findByAttributeGuid('docSystemName');
					$systemName = $row->value;
					$row = $rowsetCatalogAttribute->findByAttributeGuid('docMimeType');
					$mimeType = $row->value;
					$extactedText = $this->_extractText($rowCatalog->guid, $systemName, $mimeType);
					$doc->addField(Zend_Search_Lucene_Field::UnStored('content', $extactedText));
				}
			}
			// if catalog is a kutu_doc, and if field content empty (this means 
			// file can't be read, text can't be extracted, or file empty), do not index
			if($rowCatalog->profileGuid=='kutu_doc')
			{
				$tmpS = $doc->getFieldValue('content');
				if(!empty($tmpS))
				{
					$index->addDocument($doc);
				} else {
					
				}
			}
			else 
			{
				$index->addDocument($doc);
			}
		}
		else 
		{
			// do nothing
		}
	}
	private function _filterDateTime($mySqlDateTime)
	{	
		$aReplace = array(' ',':','-');
		return str_replace($aReplace,'',$mySqlDateTime);
	}
	private function _extractText($guid, $fileName, $mimeType)
	{
	    
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$rowset = $tblRelatedItem->fetchAll("itemGuid='$guid' AND relateAs='RELATED_FILE'");
		if(count($rowset))
		{
			$row = $rowset->current();
			$parentCatalogGuid = $row->relatedGuid;
			$sDir1 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$fileName;
			$sDir2 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentCatalogGuid.DIRECTORY_SEPARATOR.$fileName;
			
			$sDir = '';
			if(file_exists($sDir1))
			{
				$sDir = $sDir1;
			}
			else 
				if(file_exists($sDir2))
				{
					$sDir = $sDir2;
				}
				
			if(!empty($sDir))
			{
				$outpath = $sDir.'.txt';
				
				switch ($mimeType)
				{
					case 'application/pdf':
						$pdfExtractor = $this->_pdfExtractor;
						system("$pdfExtractor ".$sDir.' '.$outpath, $ret);
					    if ($ret == 0)
					    {
					        $value = file_get_contents($outpath);
					        unlink($outpath);
					        echo $value;
					        return $value;
					    }
					    if ($ret == 127)
					        //print "Could not find pdftotext tool.";
					        return '';
					    if ($ret == 1)
					        //print "Could not find pdf file.";
					        return '';
						break;
					case 'text/html':
					case 'text/plain':
						$docHtml = Zend_Search_Lucene_Document_Html::loadHTMLFile($sDir);
						return $docHtml->getFieldValue('body');
						break;
					case 'application/x-javascript':
					case 'application/octet-stream':
					case 'application/msword':
						if(strpos(strtolower($fileName), '.doc'))
						{
							$extractor = $this->_wordExtractor;
							system("$extractor -m cp850.txt ".$sDir.' > '.$outpath, $ret);
						    if ($ret == 0)
						    {
						        $value = file_get_contents($outpath);
						        unlink($outpath);
						        echo $value;
						        return $value;
						    }
						    if ($ret == 127)
						        //print "Could not find pdftotext tool.";
						        return '';
						    if ($ret == 1)
						        //print "Could not find pdf file.";
						        return '';
						}
						else 
						{
							return '';
						}
						break;
					default :
						return '';
						break;
				}
			}
		}
		return '';
	}
	
	public function optimize()
	{
		$this->_index->optimize();
	}
	public function deleteCatalogFromIndex($catalogGuid)
	{
		$index = $this->_index;
			
		//check if guid exist in index, then delete
		$term = new Zend_Search_Lucene_Index_Term($catalogGuid, 'guid');
		$docIds  = $index->termDocs($term);
		if (isset($docIds)) 
		{
			foreach ($docIds as $id) {
			    $doc = $index->getDocument($id);
			    $index->delete($id);
			}
		}
	}
	
	public function emptyIndex()
	{
		$index = $this->_index;
		$query = 'a';
		
		$hits = $index->find($query);

		foreach ($hits as $hit) {
		    echo $hit->score;
		    //echo $hit->guid;
		    //echo $hit->content;
		    $index->delete($hit->id);
		}
		
		$index->optimize();
	}
	
	function testFind($query)
	{
		$index = $this->_index;
		$hits = $index->find($query);

		foreach ($hits as $hit) {
		    echo $hit->score.'<br>';
		    echo $hit->guid.'<br>';
		    echo $hit->title.'<br>';
		    echo $hit->modifiedDate.'<br>';
		    //$index->delete($hit->id);
		}
		
		echo $index->numDocs().'<br>';
		echo $index->count().'<br>';
		$index->optimize();
	}
	
	public function find($query, $start = 0 ,$end = 2000)
	{
		$index = $this->_index;
		$query = str_replace('id:(', 'guid:(', $query);
//		$results = new ArrayObject($index->find($query));
		$results = $index->find($query);
		//die($query);
		
//		foreach($results as $r)
//		{
//			$r->id = $r->guid;
//		}
//		$results = new LimitIterator($results->getIterator(),$start * $end, $end);
//		$return = new SearchResult($results);
		
		/*
		$temp = array();
		$startpos = $end * $start;
		$endpos = $startpos+$start;
		for ($i=$startpos;$i<$endpos;$i++) {
			if (isset($results[$i]))
				$temp[] = $results[$i];
			else 
				break;
		}
		
		return $temp;
		*/
//		return $return;
		return $results;
		
		//print_r($return); die;
		
		//$solr = new Apache_Solr_Service( 'localhost', '8983', '/solr/core0');
		//var_dump($solr->search( $query,0, 2));
		//return $solr->search( $query,0, 1000);
		
//		$solrAdapter = Kutu_Search_Engine::factory('solr',array('host'=>'localhost', 'port'=>'8983','homedir'=>'/solr/core0'));
//		return $solrAdapter->find($query);
	}
	public function findAndSort($query, $start=0, $limit=20, $sortField)
	{
		return $this->find($query);
	}
}
class SearchResult
{
	var $response;
	
	public function __construct($docs)
	{
		$response = new SearchResponse();
		$response->numFound = count($docs);
		$response->docs = $docs;
		
		$this->response = $response;
	}
}
class SearchResponse
{
	var $numFound = null;
	var $docs = null;
	
}
/*class SearchResultDocs
{
	var _docs = null;
	public function __construct($docs)
	{
		$this->_docs = $docs;
	}
}*/	

?>