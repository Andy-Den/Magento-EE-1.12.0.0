<?php
/**
 * Product load class
 *
 * Handles the loading of the product data, does the traffic cop work on that part
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\Product
 */
class rdi_swatch extends rdi_general
{
	public $web_colors = array();
	public $file = '';
	public $last_colors = array();
	
	public function round_color($rgb)
	{
		return array(round($rgb[0] * 2, -1) / 2,round($rgb[1] * 2, -1) / 2,round($rgb[2] * 2, -1) / 2);
	}

	public function get_web_colors()
	{
		if(empty($this->web_colors))
		{
			//init the array
			$r = array();
			$g = array();
			$b = array();
			$n = array();
			
			$row = 0;
			if (($handle = fopen("in/colors.csv", "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					if($row == 0)
					{
						$row++;
						continue;
					}
					
					$R = round($data[1] * 2, -1) / 2;
					$G = round($data[2] * 2, -1) / 2;
					$B = round($data[3] * 2, -1) / 2;
					
					$r[$R][] = "{$data[1]},{$data[2]},{$data[3]}";
					$g[$G][] = "{$data[1]},{$data[2]},{$data[3]}";
					$b[$B][] = "{$data[1]},{$data[2]},{$data[3]}";
					$n["{$data[1]},{$data[2]},{$data[3]}"] = $data[0];
				}
				fclose($handle);
			}
			
			$this->web_colors = array(0=>$r,1=>$b,2=>$g,3=>$n);
		}
		//$this->_print_r($this->web_colors); exit;
		return $this->web_colors;
	}

	public function get_color_range($rgb)
	{
		$_rgb = array($rgb['red'], $rgb['green'],$rgb['blue']);
		
		$matches = array();
		
		foreach ($_rgb as $key => $color)
		{
			$max = ($color > 250?255:$color + 5);
			$min = ($color < 6?0:$color - 5);
			for($i = $min; $i < $max; $i++)
			{
				$_color = $_rgb;
				$_color[$key] = $i;
				$matches[] = implode(",", $_color);
			}
		}
		
		return $matches;
	}

	public function get_color_match($first_color, $second_color)
	{
		$_first_color = array($first_color['red'], $first_color['green'],$first_color['blue']);
		$_second_color = array($second_color['red'], $second_color['green'],$second_color['blue']);
		
		//$this->_print_r($_first_color);
		//$this->_print_r($_second_color);
		
		$colors_match = true;
		foreach($_first_color as $key => $color)
		{
			if(abs($color - $_second_color[$key]) > 15)
			{
				$colors_match = false;
			}
		}
		//$this->_var_dump($colors_match);
		return $colors_match;
	}


	public function get_colors_from_image($source_file)
	{
		global $cart;
		// histogram options

		$maxheight = 300;
		$barwidth = 2;
		$thumbnail_image_width=10;
		$thumbnail_image_height=10;
		$tolerance = 2;
		//echo "<img src=\"{$source_file}\" height = 100/><br>";
		if(strstr($source_file,'.png'))
		{
			$im = ImageCreateFromPng($source_file);
		}
		else
		{
			$im = ImageCreateFromJpeg($source_file);
		}
		
		/*$source_image_height = imagesx($im);
		$source_image_width = imagesy($im);
		
		$thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
		 
		imagecopyresampled($thumbnail_gd_image, $im, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
		
		$im = $thumbnail_gd_image;
		*/
		$imgw = imagesx($im);
		$imgh = imagesy($im);

		// n = total number or pixels
		
		$sample = range(0,$imgh - 1);
		$sample_size = round(.05 * $imgh);
		
		$n = $imgw*$imgh;
		
			//$this->_echo($imgw);
			
			//$this->_echo($imgh);
			//$this->_echo($n);
		$colors = array();
		
		for ($i=30; $i<$imgw; $i++)
		{
			$first_color = ImageColorAt($im, $i, 0);
			
			$first_colors = imagecolorsforindex($im, $first_color);
			//$first_color_range = $this->get_color_range($first_colors);
			$height_sample = array_rand($sample, $sample_size);
			$same = true;
			//$same = false;
			//$this->_print_r($height_sample);
			$row_colors = array();
			$start = 0;
			$end = 0;
			foreach ($height_sample as $j)
			{
				// get the rgb value for current pixel
				$rgb = ImageColorAt($im, $i, $j);
				$rgbs = imagecolorsforindex($im, $rgb);
				$current_color = "{$rgbs['red']},{$rgbs['green']},{$rgbs['blue']}";
				//$row_colors[$j] = "{$rgbs['red']},{$rgbs['green']},{$rgbs['blue']}";
				
				if(!$this->get_color_match($rgbs, $first_colors))
				{
					if(!$start)
						$start = $j;
					$same = false;
					//$this->_echo("same");
				}
				else
				{
					if(!$same)
					{
						$end = $j;
						//$this->_echo("not same");
					}
					$same = true;
				}
			}
			//$this->_echo($start);
			//$this->_echo($end);
			
			//$this->_print_r($row_colors);
			
			if(abs($start - $end) > 5)
			{
				for($j = $start; $j < $end; $j++)
				{
					$rgb = ImageColorAt($im, $i, $j);
					$rgbs = imagecolorsforindex($im, $rgb);
					
					$color = "{$rgbs['red']},{$rgbs['green']},{$rgbs['blue']}";
					// get the Value from the RGB value
					if(!isset($colors[$color]))
					{
						$colors[$color] = 0;
					}
					
					$colors[$color]++;
				}
			}
			
			/*if(!empty($colors))
			{
				$this->_echo($start);
				$this->_echo($end);
				$this->_print_r($colors);
			}*/
			//exit;
		}
		//$this->_var_dump($colors); exit;
		if(empty($colors))
		{
			return array("{$first_colors['red']},{$first_colors['green']},{$first_colors['blue']}"=>1);
		}

		arsort($colors);
		//$whites = array_values(array_slice($colors, 0, 1));
		//$colors = array_slice($colors, 1, 100);
		
		//$n = $n - $whites[0];
		$out = array($n ,$colors);
		return $out;
	}

	public function dist($col1,$col2) {
	  $delta_r = $col1[0] - $col2[0];
	  $delta_g = $col1[1] - $col2[1];
	  $delta_b = $col1[2] - $col2[2];
	  return $delta_r * $delta_r + $delta_g * $delta_g + $delta_b * $delta_b;
	} 


	public function merge_colors(&$colors)
	{
		$this->get_web_colors();
		
		$original_colors = $colors;
		
		$matches = array();
		
		foreach($colors as $color1 => $count1)
		{
			$p1 = explode(",", $color1);
			
			//rounded RGB values
			$P = $this->round_color($p1);
			$colors2 = array();
			for($i = 0; $i<3; $i++)
			{
				if(!empty($this->web_colors[$i][$P[$i]]))
				{
					$colors2 = array_merge($colors2, $this->web_colors[$i][$P[$i]]);			
				}
			}
			
			//$cart->_print_r($colors2);
			
			if(!empty($colors2))
			{
				foreach(array_unique($colors2) as $color2)
				{
					//$cart->_echo("compare colors[{$color1}][{$color2}]");
					$p2 = explode(",", $color2);
					//3d distance between points
					$distance = (int) pow($p1[0] - $p2[0],2) + pow($p1[1] - $p2[1],2) + pow($p1[2] - $p2[2],2);
					//$cart->_echo($distance);
					if($distance < 500)
					{
						if(!isset($matches[$color2]))
						{
							$matches[$color2] = 1;
						}
						else
						{
							$matches[$color2]++;
						}
						
						//$colors[$color1] += (int) $original_colors[$color2];
					}
				}
			}
		}

		
		$this->_print_r($matches);
		
		//return $matches;
		
		//exit;
		
		if(!empty($matches))
		{		
			$value = max($matches);

			$key = array_search($value, $matches);
			
			return $key;
		}
		return $matches;
	}
	
	public function get_swatch($file)
	{
		$this->file = $file;
		$out = $this->get_colors_from_image($file);

		$this->last_colors = $out[1];
		
		$color = key($out[1]);
		
		if($color == '255,255,255')
		{
			next($out[1]);
		}
		
		$file = $this->create_swatch(key($out[1]),$height=100, $width=100);
		
		return $file;
	}
	
	public function create_swatch($rgb,$height=100, $width=100, $file = false)
	{
		//$this->_print_r($rgb);
		list($r,$g,$b) = explode(",",$rgb);
			
		$image	= imagecreate( $width, $height );
		$main_color	= imagecolorallocate( $image, $r, $g, $b );

		imagefilledrectangle( $image, 0, 0, ($width), ($height), $main_color);

		if(!$file)
		{
			$path = pathinfo($this->file); 
			
			$file = $path['dirname'] . "/" . $path['filename'] . "-swatch.png";
		}

		imagepng($image, $file);
		
		imagedestroy($image);
		
		return $file;
	}
	
	public function hex2rgb($hex) 
	{
	   $hex = str_replace("#", "", $hex);

	   if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	   return implode(",", $rgb); // returns the rgb values separated by commas
	   //return $rgb; // returns an array with the rgb values
	}
	
	public function rgb2hex($_rgb) 
	{
		$rgb = explode(",", $_rgb);
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

		return $hex; // returns the hex value including the number sign (#)
	}
	
	public function getlastcolorshex($number = 10)
	{
		$colors = array();
		
		for($i = 0; $i<$number; $i++)
		{
			$colors[] = $this->rgb2hex(key($this->last_colors));
			next($this->last_colors);
		}
		
		return $colors;
	}
	
}
?>
