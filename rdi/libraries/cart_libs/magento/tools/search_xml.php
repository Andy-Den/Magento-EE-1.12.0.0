<?php

//this searches the xml in the in and in/archive folders. This only searches in the main nodes, STYLE(v9), ITEM(CP) and looks for the main identifier for the attribute, if not specified.
//a max of 100 records is used to prevent over load to the browser.
chdir('../../../../');
include 'init.php';

global $cart, $db_lib;

if(!isset($_GET['value']))
{
	exit;
}

$sid = $_GET['value'];

$attribute = isset($_GET['attribute'])?$_GET['attribute']:$db_lib->get_style_sid();

$files = array_merge(glob('in/*.xml'),glob('in/archive/*.xml'));

usort($files, create_function('$b,$a', 'return filemtime($a) - filemtime($b);'));
header ("Content-Type:text/xml");

$found = 0;
$max_found = 100;

echo("<root>");
foreach($files as $file)
{
	$xml = new XMLReader();
	$xml->open($file);
		
	
	$xml->read() && $xml->read();
	
	$nodeName = $xml->name;
	
	while(@$xml->next($nodeName))
    {
		
		if($xml->nodeType == XMLReader::END_ELEMENT)
        {
			
        }       
        else if($xml->nodeType == XMLReader::ELEMENT)
        {
			if($xml->hasAttributes)
            {
				$a = $xml->getAttribute($attribute);
				
				if(strstr(strtolower($a),strtolower($value)))
				{
					$found++;
					if($found < $max_found)
					{
						$r = $xml->readOuterXml();
						echo("<found file=\"{$file}\">{$r}</found>");				
					}
					
				}
				
			}
			
        }
       
        else if($xml->nodeType == XMLReader::TEXT)
        {
        }
		
	}
}
if($found > $max_found)
{
	echo "<alert>Found {$found} records. Only {$max_found} allowed. Try searching for less.</alert>";
}
else
{
	echo "<alert>Found {$found} records. </alert>";
}

echo("</root>");

?>