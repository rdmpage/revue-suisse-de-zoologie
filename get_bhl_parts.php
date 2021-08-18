<?php

$config['api_key'] = '0d4f0303-712e-49e0-92c5-2113a5959159';
$config['cache']	= dirname(__FILE__) . '/cache';


//----------------------------------------------------------------------------------------
function get($url)
{
	$data = '';
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	curl_setopt ($ch, CURLOPT_HEADER,		  1);  
	
	// timeout (seconds)
	curl_setopt ($ch, CURLOPT_TIMEOUT, 120);

	curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST,		  0);  
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER,		  0);  
	
	$curl_result = curl_exec ($ch); 
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		
		// print_r($info);		
		 
		$header = substr($curl_result, 0, $info['header_size']);
		
		// echo $header;
		
		//exit();
		
		$data = substr($curl_result, $info['header_size']);
		
	}
	return $data;
}


//----------------------------------------------------------------------------------------

function get_item($ItemID, $force = false)
{
	global $config;
	
	// get BHL item
	$filename = $config['cache'] . '/' . $ItemID . '.json';

	if (!file_exists($filename) || $force)
	{
		$url = 'https://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemParts&itemid=' 
			. $ItemID . '&apikey=' . $config['api_key'] . '&format=json';
			
		echo $url . "\n";

		$json = get($url);
		file_put_contents($filename, $json);
	}

	$json = file_get_contents($filename);
	$item_data = json_decode($json);
	
	return $item_data;
		

}


//----------------------------------------------------------------------------------------
// title
function get_title($TitleID, $force = false)
{
	global $config;
	
	$filename = $config['cache'] . '/title-' . $TitleID . '.json';

	if (!file_exists($filename))
	{
		$url = 'https://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetTitleMetadata&titleid=' 
			. $TitleID . '&items=t&apikey=' . $config['api_key'] . '&format=json';

		$json = get($url);
		file_put_contents($filename, $json);
	}

	$json = file_get_contents($filename);

	$title_data = json_decode($json);
	
	
	$items = array();
	
	foreach ($title_data->Result->Items as $item)
	{
		$items[] = $item->ItemID;
	}
	
	foreach ($items as $item)
	{
		$item_data = get_item($item, $force);
		
		
		//print_r($item_data);
		
		foreach ($item_data->Result as $part)
		{
			//print_r($part);
		
		
			echo "TY  - JOUR\n";
			echo 'L1  - ' . $part->PartUrl . "\n";
			
			$keys = array('GenreName', 'Title', 'Volume', 'Issue', 'PageRange', 'Doi', 'ContainerTitle', 'StartPageNumber', 'EndPageNumber', 'Date');
		
			foreach ($keys as $k)
			{
				if (isset($part->{$k}) && ($part->{$k} != ''))
				{
					switch ($k)
					{
					
						case 'Title':
							echo "TI  - " . $part->{$k} . "\n";
							break;
									
						case 'ContainerTitle':
							echo "JO  - " .$part->{$k} . "\n";
							echo "SN  - 0035-418X\n";
							break;
				
						case 'Volume':
							echo "VL  - " .$part->{$k} . "\n";
							break;

						case 'Issue':
							echo "IS  - " .$part->{$k} . "\n";
							break;

						case 'StartPageNumber':
							echo "SP  - " .$part->{$k} . "\n";
							break;

						case 'EndPageNumber':
							echo "EP  - " .$part->{$k} . "\n";
							break;

						case 'Doi':
							echo "DO  - " .$part->{$k} . "\n";
							break;

						case 'Date':
							echo "Y1  - " . substr($part->{$k}, 0, 4) . "\n";
							break;
				
						default:
							break;
					}
				}		
			}	
			
			foreach ($part->Authors as $author)
			{
				echo "AU  - " . $author->Name . "\n";
			}
						
			echo "ER  - \n\n";
		
		}
		
		
	}	


}

//----------------------------------------------------------------------------------------


get_title(8981, false);


?>