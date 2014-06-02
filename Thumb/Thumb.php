<?php namespace Thumb;

class Thumb {

    public static $cache = 'thumbs';

	public $color;

	public $width = 300;

	public $height = 200;

	protected $image;

	protected function cache () {
		$pathinfo = pathinfo($this->image);
		$ext = $pathinfo['extension'];

		$hash = md5("$this->image:$this->color:$this->width:$this->height");

		$cache = self::$cache."/$hash.$ext";

		$pathinfo = pathinfo($_SERVER['SCRIPT_FILENAME']);
		$root = $pathinfo['dirname'];

		if (!file_exists("$root/$cache")) {
			echo "Saving $root/$cache";
			$this->save("$root/$cache");
		}

		return $cache;
	}

	protected function save ($path) {
		$pathinfo = pathinfo($path);

		if (!file_exists($pathinfo['dirname'])) {
			mkdir($pathinfo['dirname'], 0777, true);
		}

		try {
			$image = new \Imagick($this->image);
			$image->thumbnailImage($this->width, $this->height, true);

			if ($this->color) {
				$thumb = $image;

				$image = new \Imagick();
				$image->newImage($this->width, $this->height, $this->color, $pathinfo['extension']);

				$size = $thumb->getImageGeometry();

				$x = ($this->width - $size['width']) / 2;
				$y = ($this->height - $size['height']) / 2;

				$image->compositeImage($thumb, \imagick::COMPOSITE_OVER, $x, $y);
				$thumb->destroy();
			}

			$pathinfo = pathinfo($_SERVER['SCRIPT_FILENAME']);
			$root = $pathinfo['dirname'];

			$image->writeImage($path);
			$image->destroy();
		}

		catch (Exception $e) {
		}

		return $this;
	}

	public function __construct ($image, $width = 0, $height = 0, $color = null) {
		$this->image = $image;

		if ($width) {
			$this->size($width, $height);
		}

		if ($color) {
			$this->color($color);
		}
	}

	public function __toString () {
		return $this->cache();
	}

	public function color ($color) {
		$this->color = $color;
		return $this;
	}

	public function size ($width, $height = 0) {
		$this->width = $width;
		$this->height = $height ? $height : $width;
		return $this;
	}

}
