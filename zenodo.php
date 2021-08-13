<?php

//----------------------------------------------------------------------------------------
// get
function get($url, $format = '')
{
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	if ($format != '')
	{
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: " . $format));	
	}
	
	$response = curl_exec($ch);
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		die($errorText);
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	
	curl_close($ch);
	
	return $response;
}



//----------------------------------------------------------------------------------------
function reference_to_ris($reference)
{
	$field_to_ris_key = array(
		'title' 	=> 'TI',
		'alternativetitle' 	=> 'TT',
		'journal' 	=> 'JO',
		'secondary_title' 	=> 'JO',
		'book' 		=> 'T2',
		'issn' 		=> 'SN',
		'volume' 	=> 'VL',
		'issue' 	=> 'IS',
		'spage' 	=> 'SP',
		'epage' 	=> 'EP',
		'year' 		=> 'Y1',
		'date'		=> 'PY',
		'abstract'	=> 'N2',
		'url'		=> 'UR',
		'pdf'		=> 'L1',
		'doi'		=> 'DO',
		'notes'		=> 'N1',
		'oai'		=> 'ID',

		'publisher'	=> 'PB',
		'publoc'	=> 'PP',
		
		'publisher_id' => 'ID',
		
		'xml'		=> 'XM', // I made this up
		
		// correspondence
		
		);
		
	$ris = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$ris .= "TY  - JOUR\n";
			break;

		case 'chapter':
			$ris .= "TY  - CHAP\n";
			break;

		case 'book':
			$ris .= "TY  - BOOK\n";
			break;

		default:
			$ris .= "TY  - GEN\n";
			break;
	}

	//$ris .= "ID  - " . $result->fields['guid'] . "\n";
	
	// Need journal to be output early as some pasring routines that egnerate BibJson
	// assume journal alreday defined by the time we read pages, etc.
	if (isset($reference->journal))
	{
		$ris .= 'JO  - ' . $reference->journal . "\n";
	}

	foreach ($reference as $k => $v)
	{
		switch ($k)
		{
			// eat this
			case 'journal':
				break;
				
			case 'authors':
				foreach ($v as $a)
				{
					if ($a != '')
					{
						$a = str_replace('*', '', $a);
						$a = trim(preg_replace('/\s\s+/u', ' ', $a));						
						$ris .= "AU  - " . $a ."\n";
					}
				}
				break;

			case 'alternativeauthors':
				foreach ($v as $a)
				{
					if ($a != '')
					{
						$a = str_replace('*', '', $a);
						$a = trim(preg_replace('/\s\s+/u', ' ', $a));						
						$ris .= "AT  - " . $a ."\n";
					}
				}
				break;
				
			case 'editors':
				foreach ($v as $a)
				{
					if ($a != '')
					{
						$ris .= "ED  - " . $a ."\n";
					}
				}
				break;				
				
			case 'date':
				//echo "|$v|\n";
				if (preg_match("/^(?<year>[0-9]{4})\-(?<month>[0-9]{2})\-(?<day>[0-9]{2})$/", $v, $matches))
				{
					//print_r($matches);
					$ris .= "PY  - " . $matches['year'] . "/" . $matches['month'] . "/" . $matches['day']  . "/" . "\n";
					$ris .= "Y1  - " . $matches['year'] . "\n";
				}
				else
				{
					$ris .= "Y1  - " . $v . "\n";
				}		
				break;
				
			case 'handle':
				$ris .= 'UR  - https://hdl.handle.net/' . $v . "\n";
				break;
				
			/*
			case 'jstor':
				$ris .= 'UR  - https://hdl.handle.net/' . $v . "\n";
				break;
			*/

			case 'bhl':
				$ris .= 'UR  - https://www.biodiversitylibrary.org/page/' . $v . "\n";
				break;
				
				
			default:
				if ($v != '')
				{
					if (isset($field_to_ris_key[$k]))
					{
						$ris .= $field_to_ris_key[$k] . "  - " . $v . "\n";
					}
				}
				break;
		}
	}
	
	$ris .= "ER  - \n";
	$ris .= "\n";
	
	return $ris;
}



$filename = 'zenodo.json';

$force = false;

if (!file_exists($filename) || $force)
{
	$url = 'https://zenodo.org/api/records?q=' . urlencode('journal.title:(Revue Suisse de Zoologie)') . '&size=1000';
	$url = 'https://zenodo.org/api/records?q=' . urlencode('journal.title:(Revue suisse de Zoologie)') . '&subtype=article&size=1000';

	$url = 'https://zenodo.org/api/records?q=' . urlencode('"Revue suisse de Zoologie"') . '&type=publication&subtype=article&size=1000';
	
	//$url .= '&page=2';

	$json = get($url);
	
	
	file_put_contents('zenodo.json', $json);
}

$json = file_get_contents($filename);

$obj = json_decode($json);

//print_r($obj);

foreach ($obj->hits->hits as $hit)
{
	if (preg_match("/Revue suisse de Zoologie/i", $hit->metadata->journal->title))
	{
		// echo $hit->doi . "\n";
		
		// export as RIS
		
		$reference = new stdclass;
		
		$reference->doi = $hit->doi;
		$reference->title = $hit->metadata->title;
		
		$reference->authors = array();
		
		foreach ($hit->metadata->creators as $creator)
		{
			$reference->authors[] = $creator->name;
		}
		
		
		$reference->journal = $hit->metadata->journal->title;
		
		
		$reference->issn = '0035-418X';
		
		$reference->volume 	= $hit->metadata->journal->volume;
		$reference->issue 	= $hit->metadata->journal->issue;

		$reference->date 	= $hit->metadata->publication_date;
		
		$pages = $hit->metadata->journal->pages;
		$parts = explode("-", $pages);
		if (count($parts) == 2)
		{
			$reference->spage = $parts[0];
			$reference->epage = $parts[1];
		
		}
		else
		{
			$reference->spage = $pages;
		}
		
		//print_r($reference);
		
		echo reference_to_ris($reference);
		
	}

}



?>