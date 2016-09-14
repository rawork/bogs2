<?php

namespace Fuga\Component\Form\Widget;

class DiagramWidget {
	
	public $width;
	public $height;
	public $thick;
	private $width4;
	private $height4;
	private $thick4;
	public $fname;
	public $bgcolor;

	function __construct($bgcolor = 'ffffff') {
		$this->bgcolor = trim(str_replace('#', '', $bgcolor));
	}

	function draw($rows, $width = 150, $height = 150, $thick = 15, $fname = '/upload/draw.png'){
		$this->width  = $width;
		$this->height = $height;
		$this->thick  = $thick;
		$this->width4  = $width*4;
		$this->height4 = $height*4;
		$this->thick4  = $thick*4;
		$this->fname = $fname;
		$image = imagecreatetruecolor($this->width4, $this->height4);
		// allocate some colors
		$cred = hexdec(substr($this->bgcolor, 0, 2));
		$cgreen = hexdec(substr($this->bgcolor, 2, 2));
		$cblue = hexdec(substr($this->bgcolor, 4, 2));
		$bg = imagecolorallocate($image, $cred, $cgreen, $cblue);
		$colors = array();
		$total = 0;
		foreach ($rows as $value){
			$total+=$value[0];
			$value[1] = str_replace('#', '', $value[1]);
			$cred = hexdec(substr($value[1], 0, 2));
			$cgreen = hexdec(substr($value[1], 2, 2));
			$cblue = hexdec(substr($value[1], 4, 2));
			$colors[] = array(
				'c' => imagecolorallocate($image, $cred, $cgreen, $cblue),
				'd' => imagecolorallocate($image, $cred ? $cred-25 : 0, $cgreen ? $cgreen-25 : 0, $cblue ? $cblue-25 : 0),
				'h' => $value[1]
			);
		}
		imagefill($image, 0, 0, $bg);

		// make the 3D effect
		for ($i = (($this->height4/2) + $this->thick4); $i > ($this->height4/2); $i--) {

			$j=0;
			$z1=0;
			if ($rows){
				foreach ($rows as $value){
					$z2=$z1+(360/($total/$value[0]));
					imagefilledarc($image, ($this->width4/2), $i, $this->width4, ($this->height4)/2, $z1, $z2, $colors[$j]['d'], IMG_ARC_PIE);
					$z1=$z2;
					$j++;
				}
			}
		}

		$j=0;
		$z1=0;
		$colorback = array();
		foreach ($rows as $value){
			$z2=$z1+(360/($total/$value[0]));
			imagefilledarc($image, ($this->width4/2), ($this->height4/2), $this->width4, ($this->height4)/2, $z1, $z2, $colors[$j]['c'], IMG_ARC_PIE);
			$colorback[]=$colors[$j]['h'];
			$z1=$z2;
			$j++;
		}

		$imd=imagecreatetruecolor($this->width,$this->height);
		imagecopyresampled($imd,$image,0,0,0,0,$this->width,$this->height,$this->width4,$this->height4);
		imagedestroy($image);

		imagepng($imd, PRJ_DIR.$this->fname);
		imagedestroy($imd);
		return $colorback;
	}
}
