<?php
	class Image {

		public function __construct($path, $auto_load = true) {
			$this->path = $path;
			$info = getimagesize($path);
			$this->type = $info[2];
			if ($auto_load) {
				if ($this->type == IMAGETYPE_JPEG) {
					$this->image = imagecreatefromjpeg($path);
				}
			}
			//TODO: another types of image
			$this->_width = $info[0];
			$this->_height = $info[1];
		}

		public function show() {
			header("Content-Type: " . $this->info["mime"]);
			if ($type == IMAGETYPE_JPEG) {
				imagejpeg($this->image);
			}
			//TODO: another types of image
		}

		public function save($path = NULL) {
			$umask = umask();
			umask(0073);
			if ($path == NULL) {
				$path = $this->path;
			//TODO: add prefix
			}
			$pos = strrpos($path, ".");
			if ($this->type == IMAGETYPE_JPEG) {
				if ($pos !== false) {
					if (substr($path, $pos) != ".jpg") {
						$path .= ".jpg";
					}
				}
				imagejpeg($this->image, $path);
			}
			//TODO: another types of image
			umask($umask);
		}

		public function width() {
			return $this->_width;
		}

		public function height() {
			return $this->_height;
		}

		public function resizeToHeight($height) {
			$ratio = $height / $this->_height;
			$width = $this->_width * $ratio;
			$this->resize($width, $height);
		}

		public function resizeToWidth($width) {
			$ratio = $width / $this->_width;
			$height = $this->_height * $ratio;
			$this->resize($width, $height);
		}

		public function resize($width, $height) {
			$new = imagecreatetruecolor($width, $height);
			imagecopyresampled($new, $this->image, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
			$this->image = $new;
			$this->_width = $width;
			$this->_height = $height;
		}

		public function autoCrop($width, $height, $position = self::TOP) {
			$this->resizeToWidth($width);
			if ($position == self::TOP) {
				$start = 0;
			} else if ($position == self::MIDDLE) {
				$start = $this->_height / 2 - ($height / 2);
			} else if ($position == self::BOTTOM) {
				$start = $this->_height - $height;
			}
			$new = imagecreatetruecolor($width, $height);
			imagecopyresampled($new, $this->image, 0, 0, 0, $start, $width, $height, $width, $height);
			$this->image = $new;
			$this->_width = $width;
			$this->_height = $height;
		}

		public function toBase64() {
			$fp = fopen($this->path, "r");
			$data = fread($fp, filesize($this->path));
			fclose($fp);
			$this->binary = base64_encode($data);
			return $this->binary;
		}

		private $path;
		private $binary;
		private $image;
		private $type;
		private $info;
		private $_width;
		private $_height;
		const TOP = 1;
		const MIDDLE = 2;
		const BOTTOM = 3;
	}
?>
