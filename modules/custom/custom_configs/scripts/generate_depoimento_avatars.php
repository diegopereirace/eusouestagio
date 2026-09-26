<?php

/**
 * @file
 * Gera avatares geométricos placeholder (sem fotos reais).
 */

$dir = dirname(__DIR__) . '/assets/depoimentos';
if (!is_dir($dir)) {
  mkdir($dir, 0755, TRUE);
}

$colors = [
  [46, 158, 91],
  [0, 48, 96],
  [255, 138, 34],
  [90, 120, 200],
];

foreach ($colors as $i => $rgb) {
  $im = imagecreatetruecolor(100, 100);
  $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
  imagefilledrectangle($im, 0, 0, 100, 100, $bg);
  $fg = imagecolorallocate($im, 255, 255, 255);
  imagefilledellipse($im, 50, 38, 36, 36, $fg);
  imagefilledellipse($im, 50, 90, 60, 50, $fg);
  $path = $dir . '/avatar-' . ($i + 1) . '.png';
  imagepng($im, $path);
  imagedestroy($im);
  echo $path . PHP_EOL;
}
