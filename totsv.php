<?php

require_once(dirname(__FILE__) . '/ris.php');

$keys = array();

//----------------------------------------------------------------------------------------
function reference_to_tsv($reference)
{
	global $keys;
	
	$row = array();
	foreach ($keys as $k)
	{
		switch ($k)
		{
			case 'authors':
				if (isset($reference->{$k}))
				{
					//$row[] = join("&au=", $reference->{$k});
					$row[] = join(";", $reference->{$k});
				}
				else
				{
					$row[] = '';
				}				
				break;
				
			case 'journal':
				if (isset($reference->{$k}))
				{
					$row[] = $reference->{$k};
				}
				else
				{
					if (isset($reference->secondary_title))
					{
						$row[] = $reference->secondary_title;
					}
					else
					{
						$row[] = '';
					}
				}			
				break;
				
			
			default:
				if (isset($reference->{$k}))
				{
					$row[] = $reference->{$k};
				}
				else
				{
					$row[] = '';
				}
			break;
		}
	}
	
	return $row;
}


function convert($reference)
{
	//echo reference_to_ris($reference);
	$row = reference_to_tsv($reference);
	echo join("\t", $row) . "\n";
}


$filename = '';
if ($argc < 2)
{
	echo "Usage: import.php <RIS file> <mode>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}


$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

$keys = array(
			'id',
			'title',
			'authors',
			'journal',
			'issn',
			//'series',
			'volume',
			'issue',
			'spage',
			'epage',
			'year' ,
			'date',
			//'publisher',
			//'publoc',
			'doi',
			//'url',
			//'pdf',
			//'notes'
		);

echo join("\t", $keys) . "\n";

import_ris_file($filename, 'convert');


?>