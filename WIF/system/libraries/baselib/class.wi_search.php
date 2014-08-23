<?php
/**
* Copyright (c) 2010, 
* All rights reserved.
* @file class.wi_search.php
* @brief SolrËÑË÷²Ù×÷Àà·â×°
* Created by: chenjin(wind.golden@gmail.com)
* Project : wind_frame
* Time: 2010-6-3
* Version: 1.0
*/
require(__DIR__.'/SolrPhpClient/Apache/Solr/Service.php');

class WI_Search extends Apache_Solr_Service
{
	function WI_Search($host = '', $port = '', $path = '')
	{
		global $_WI_CONFIG;
	    $host = ($host == '' ? $_WI_CONFIG['default_search_engine']['host'] : $host);
		$port = ($port == '' ? $_WI_CONFIG['default_search_engine']['port'] : $port);
		$path = ($path == '' ? $_WI_CONFIG['default_search_engine']['path'] : $path);
		parent::__construct($host, $port, $path);
		if (! $this->ping()) {
			//trigger_error('search service not responding.',E_USER_ERROR);
		}
	}

	/**
	 * Simple Search interface
	 *
	 * @param string $query The raw query string
	 * @param int $offset The starting offset for result documents
	 * @param int $limit The maximum number of result documents to return
	 * @param array $params key / value pairs for other query parameters (see Solr documentation), use arrays for parameter keys used more than once (e.g. facet.field)
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	function query($query, $offset = 0, $limit = 10, $params = array(), $method = 'GET')
	{
		if (! $this->ping()) {
			@include APP_ROOT_PATH.'tool/default_search_response.php';
			return array('response'=>$response);
		}
		$response = $this->search($query, $offset, $limit, $params , $method);
		
		if ( $response->getHttpStatus() == 200 ) { 
		  return json_decode($response->getRawResponse(),true);
		}
		else {
		  return $response->getHttpStatusMessage();
		}

	}
}
