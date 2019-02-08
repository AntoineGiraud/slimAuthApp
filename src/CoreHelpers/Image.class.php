<?php

namespace CoreHelpers;
/**
* Image
* https://imagine.readthedocs.io/en/stable/
*/
class Image{
    /**
     * resize : permet d'afficher la thumbnail d'une image à des dimentions données
     * @param  String $image    path vers l'image
     * @param  int    $width
     * @param  int    $height
     * @param  string $outbound manière de croper {inset, outbound}
     * @return string path resized img
     * exemple : <img src="<?= Image::resize("img/news/".$p['thumbnail'],100,100); ?>" alt="<?= $p['thumbnail'] ?>">
     */
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

/*
// Exemple :

// Sauvegarde image & compression
if (isset ($_FILES['pres_img']) && !empty($_FILES['pres_img']['name'])) {
    $filename = Functions::cleanImageName($_FILES['pres_img']['name']);
    $info = pathinfo($filename);
    if (in_array($info['extension'], array('jpg','png'))) {
        $dir = IMG_PATH;
        if (!file_exists($dir)) mkdir($dir,0777);

        $width  = 120;  $height = 150;
        // debug(getimagesize($_FILES['pres_img']['tmp_name']),'getimagesize');
        $filename = 'pres_'.$info['filename'] . '_' . $width . 'x'.$height . '.' . $info['extension'];
        $dest = $dir . '/' . $filename;
        // Appel à la librairie imagine.phar pour éditer l'image :)
        $imagine = new Imagine\Gd\Imagine();
        $size = new Imagine\Image\Box($width,$height);
        $image = $imagine->open($_FILES['pres_img']['tmp_name'])->thumbnail($size, 'inset');
        $image->save($dest, array('quality' => 100));
        $params['pres_img']=$filename;
        Functions::setConfig('pres_img',$filename);
    }else{
        Functions::setFlash("Erreur : le fichier n'est pas une image jpg ou png",'error');
    }
}

//*/