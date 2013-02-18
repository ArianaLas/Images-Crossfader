<?php
	include_once("image.php");
	class SliderException extends Exception {}

	class Slider {

		public function __construct($width, $height, $name, $root = "./") {
			$this->width = $width;
			$this->height = $height;
			if (substr($root, -1) != "/") {
				$root .= "/";
			}
			foreach (array("/", " ", "\\") as $forbidden) {
				if (strpos($name, $forbidden) !== false) {
					throw new SliderException("Dangerous name given, forbidden character: '" . $forbidden . "'");
				}
			}
			$this->name = $name;
			$this->path = $root . "sliders/" . $name;
			if (!is_dir($this->path)) {
				if (!@mkdir($this->path, 0777, true)) {
					throw new SliderException("Unable to create directory " . $this->path);
				}
			}
		}

		public function addImage($path, $alt, $new_name = null, $auto_numeric = true, $title = null, $crop_pos = Image::TOP) {
			if (!file_exists($path)) {
				throw new SliderException("File " . $path . " does not exists");
			}
			$pos = strrpos($path, "/");
			if ($pos === false) {
				$pos = 0;
			}
			if ($new_name == null) {
				$new_name = substr($path, $pos);
			}
			if ($auto_numeric === true) {
				$i = 0;
				foreach ($this->images as $image) {
					$n = $image["name"];
					$pos = strrpos($n, "-");
					$dot_pos = strrpos($n, ".");
					if ($pos !== false) {
						$n = substr($n, 0, $pos) . substr($n, $dot_pos);
					}
					if ($n == $new_name) {
						if ($image["crop_pos"] != $crop_pos) {
							$i++;
						}
					}
				}
				if ($i != 0) {
					$dot_pos = strrpos($new_name, ".");
					if ($dot_pos !== false) {
						$new_name = substr($new_name, 0, $dot_pos) . "-" . $i . substr($new_name, $dot_pos);
					}
				}
			}
			$internal = $this->path . "/" . $new_name;
			if (!file_exists($internal)) {
				$this->save($path, $internal, $crop_pos);
				array_push($this->images, array("name" => $new_name, "alt" => $alt, "title" => $title, "crop_pos" => $crop_pos));
			}
		}

		private function save($path, $internal, $crop_pos = Image::TOP) {
			$image = new Image($path);
			$image->autoCrop($this->width, $this->height, $crop_pos);
			$image->save($internal);
		}

		public function setImageDuration($duration_sec) {
			$this->image_duration = $duration_sec;
		}

		public function setFadeTime($time_sec) {
			$this->fade_time = $time_sec;
		}

		public function getHtmlCode() {
			$string = '<section id="' . $this->name . '">' . "\n";
			foreach ($this->images as $image) {
				 $string .= "\t" . '<img src="' . $this->path . '/' . $image["name"] . '" alt="' . $image["alt"] . '" ' . ($image["title"] ? 'title="' . $image["title"] . '" /' : '/>') . "\n";
			}
			$string .= "</section>" . "\n";
			return $string;
		}


		private $image_duration = 5;
		private $fade_time = 1;
		private $width;
		private $height;
		private $images = array();
		private $path;
		private $name;
	}
?>
