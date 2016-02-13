<?php 

ini_set('memory_limit', '2G');
ini_set('display_errors', 1);

echo 'Tesing graphic libraries for image manipulation.' . '<br/><br/>' . "\n\n";

//==== Test #01 =================================================================================//
echo 'Test #01: open a png file as image' . '<br/>' . "\n";
try{
	$imageFile = "samplePngImage.png";
	$image = imagecreatefrompng($imageFile);
	if(!$image){
		echo 'Failed. Cannot open imange.' . '<br/>' . "\n";
	}else{
		echo 'Success. Image opened.' . '<br/>' . "\n";
		var_dump(getimagesize($imageFile));
	}
}catch(Exception $e){
	echo 'Failed. Server error: ' . $e->getMessage() . '<br/>' . "\n";
}
echo '<br/><br/>' . "\n\n";

//==== Test #02 =================================================================================//
echo 'Test #02: open a gif file as image' . '<br/>' . "\n";
try{
	$imageFile = "sampleGifImage.gif";
	$image = imagecreatefromgif($imageFile);
	if(!$image){
		echo 'Failed. Cannot open imange.' . '<br/>' . "\n";
	}else{
		echo 'Success. Image opened.' . '<br/>' . "\n";
		var_dump(getimagesize($imageFile));
	}
}catch(Exception $e){
	echo 'Failed. Server error: ' . $e->getMessage() . '<br/>' . "\n";
}
echo '<br/><br/>' . "\n\n";