<?php

namespace CoreHelpers;
/**
* Image
*/
class Image{

	public static function resize($image, $width, $height, $outbound='outbound'){
		$info = pathinfo($image);
		$dest = $info['dirname'] . '/' . $info['filename'] . "_$width" . "x$height" . '.' . $info['extension'];
		if (file_exists($dest)) {
			return $dest;
		}
		$imagine = new Imagine\Gd\Imagine();
		$size = new Imagine\Image\Box($width,$height);
		$imagine->open($image)->thumbnail($size, $outbound)->save($dest);
		return $dest;
	}
}

?>