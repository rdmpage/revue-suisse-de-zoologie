<?php


$filename = "rsz_table.txt";
//$filename = "1911.txt";
// $filename = "x.txt";
$file_handle = fopen($filename, "r");

$failed = array();
		
while (!feof($file_handle)) 
{
	$text = trim(fgets($file_handle));
	
	if ($text == "") continue;

//	$text = 'ANDRÉ, E. 1893. Contribution à l\'anatomie et à la physiologie des Ancylus lacustris et fluviatilis. 1 (3): 427-461 BÉRANECK, E. 1893. L\'organe auditif des Alciopides. 1 (3): 463-500 BEDOT, M. 1894. Herman Fol, sa vie et ses travaux. 2 (1): 1-21 JOUBIN, L. 1894. Céphalopodes d\'Amboine. 2 (1): 23-64 LOCARD, A. 1894. Les Bythinia du système européen. 2 (1): 65-134 ZEHNTNER, L. 1894. Crustacés de l\'Archipel Malais. 2 (1): 135-214 FUHRMANN, O. 1894. Die Turbellarien des Umgebung von Basel. 2 (2): 215-290';
	
	// break into lines
	
	$text = preg_replace('/(:\.?\s+\d+[-|-|–]?\d+\.?)\s+([A-Z])/', "$1|$2", $text);
//	preg_replace('/(\d+\.?)\s+([A-Z])/', "$1|$2", $text);
	
	$refs = explode("|", $text);
	
	// print_r($refs);
	
	//if (count($refs) > 1)
	if (preg_match('/[0-9]{4}/', $text))
	{
		//print_r($refs);
		
		// extract ref.
		
		foreach ($refs as $ref)
		{
			$matched = false;
			
			if (!$matched)
			{
		
				if (
				preg_match('/(?<authors>[^\d]+)\s+(?<year>[0-9]{4}(-[0-9]{4})?)\.\s+(?<title>.*)[\.|\?]\)?(\s+Revue suisse de Zoologie)?\s+(?<volume>\d+)\s*(\((?<issue>[^\)]+)\))?\s*:\.?\s*(?<spage>\d+)([-|-|–](?<epage>\d+))?/', $ref, $matches))
			
				{
					//print_r($matches);
				
					echo "TY  - JOUR\n";
					echo "T1  - " . mb_convert_encoding($matches['title'], 'UTF-8') . "\n";
					echo "JF  - Revue suisse de Zoologie\n";
				
					$authors = array();
				
				
					$astring = mb_convert_encoding($matches['authors'], 'UTF-8') ;
					$astring = mb_convert_case($astring, MB_CASE_TITLE, 'UTF-8');
					$astring = preg_replace("/&/u", "|", $astring);
				
					$a1 = explode("|", $astring);
				
					//print_r($a1);
				
					$last_author = '';
					if (count($a1) == 2)
					{
						$last_author = $a1[1];					
					}
					$chunks = preg_split('/,/u', $a1[0]);
				
					$n = count($chunks);
					for($i=0;$i<$n;$i+=2)
					{
						$authors[] = $chunks[$i] . ', ' . trim($chunks[$i+1]);
					}
					if ($last_author != '')
					{
						$authors[] = $last_author;
					}
				
				
					//$astring = preg_replace("/((.*),\s+(.*),)/u", "$1|", $astring);
					//echo $astring . "\n";
				
					/*
				
					//echo $astring . "\n";
				
					$astring = preg_replace("/([A-Z][A-Z]+)/u", "|$1", $astring);
					//echo $astring . "\n";
					$astring = preg_replace("/&/u", "|", $astring);
					//echo $astring . "\n";
					$astring = preg_replace("/\|\s*\|/u", "|", $astring);
					//echo $astring . "\n";
					$astring = preg_replace("/^\|/u", "", $astring);
					//echo $astring . "\n";
					$astring = preg_replace("/([A-Z][a-z]+)\.$/u", "$1", $astring);
				
					//echo $astring . "\n";
				
					*/
				
					foreach ($authors as $a)
					{
						if (preg_match('/\s[A-Z]\.$/u', $a))
						{
						}
						else
						{
							$a = $a = preg_replace("/\.$/u", "", $a);
						}
						echo "A1  - " . trim($a) . "\n";
					}			
				
					echo "VL  - " . $matches['volume'] . "\n";
					if ($matches['issue'] != '')
					{
						echo "IS  - " . $matches['issue']  . "\n";
					}
					echo "SP  - " . $matches['spage']  . "\n";
					
					if ($matches['epage'] != '')
					{
						echo "EP  - " . $matches['epage']  . "\n";
					}
					
					
					echo "Y1  - " . $matches['year'] . "///\n";
					echo "KW  - " . $matches['year'] . "\n";
					echo "ER  -\n\n";
				
					$matched = true;
				
				}
			}
			
			
			if (!$matched)
			{
				echo "*** Failed *** \n";
				echo $ref . "\n";
				$failed[] = $ref;
				//exit();
			}
		}
		
		
	}
	//echo $text;
	
	
}

print_r($failed);


?>