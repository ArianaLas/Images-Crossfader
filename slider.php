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
			$this->root = $root;
			foreach (array("/", " ", "\\") as $forbidden) {
				if (strpos($name, $forbidden) !== false) {
					throw new SliderException("Dangerous name given, forbidden character: '" . $forbidden . "'");
				}
			}
			$this->name = $name;
			$this->path = $root . "sliders/" . $name;
			$umask = umask();
			umask(0076);
			if (!is_dir($this->path)) {
				if (!@mkdir($this->path, 0777, true)) {
					throw new SliderException("Unable to create directory " . $this->path);
				}
			}
			umask($umask);
		}

		public function addImage($path, $alt, $new_name = null, $title = null, $crop_pos = Image::TOP) {
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
			$i = 0;
			$flag = false;
			foreach ($this->images as $image) {
				$n = $image["name"];
				$pos = strrpos($n, "-");
				$dot_pos = strrpos($n, ".");
				if ($pos !== false) {
					$n = substr($n, 0, $pos) . substr($n, $dot_pos);
				}
				if ($n == $new_name) {
					if ($image["crop_pos"] != $crop_pos) {
						++$i;
					} else { // exactly the same
						$flag = true;
					}
				}
			}
			if ($i != 0) {
				$dot_pos = strrpos($new_name, ".");
				if ($dot_pos !== false) {
					$new_name = substr($new_name, 0, $dot_pos) . "-" . $i . substr($new_name, $dot_pos);
				}
			}
			$internal = $this->path . "/" . $new_name;
			if (!file_exists($internal)) {
				$this->save($path, $internal, $crop_pos);
			}
			if ($flag !== true) {
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
			for ($i = count($this->images) - 1; $i >= 0; --$i) {
				$image = $this->images[$i];
				 $string .= "\t" . '<img src="' . $this->path . '/' . $image["name"] . '" alt="' . $image["alt"] . '" ' . ($image["title"] ? 'title="' . $image["title"] . '" /' : '/>') . "\n";
			}
			$string .= "</section>" . "\n";
			return $string;
		}

		public function getCssCode() {
			$amount = count($this->images);
			$duration = ($this->image_duration + $this->fade_time) * $amount;
			$delay = $duration / $amount;

			$string = "#" . $this->name . " {" . "\n";
			$string .= "\t" . "position: relative;" . "\n";
			$string .= "\t" . "width: " . $this->width . "px;" . "\n";
			$string .= "\t" . "height: " . $this->height . "px;" . "\n";
			$string .= "}" . "\n\n";
			$string .= "#" . $this->name . " > img {" . "\n";
			$string .= "\t" . "position: absolute;" . "\n";
			foreach (array("-moz-", "-webkit-", "-o-",  "-ms-", "") as $prefix) {
				$string .= "\t" . $prefix . "animation-name: " . $this->name . "_fade;" . "\n";
				$string .= "\t" . $prefix . "animation-timing-function: ease-in-out;" . "\n";
				$string .= "\t" . $prefix . "animation-iteration-count: infinite;" . "\n";
				$string .= "\t" . $prefix . "animation-duration: " . $duration . "s;" . "\n";
			}
			$string .= "}" . "\n\n";
			for ($i = 0; $i < $amount; ++$i) {
				$string .= "#" . $this->name . " > img:nth-child(" . ($i + 1) . ") {" . "\n";
				$string .= "\t" . "-moz-animation-delay: " . ($duration - $delay - $i * $delay) . "s;" . "\n";
				$string .= "\t" . "-webkit-animation-delay: " . ($duration - $delay - $i * $delay) . "s;" . "\n";
				$string .= "\t" . "-o-animation-delay: " . ($duration - $delay - $i * $delay) . "s;" . "\n";
				$string .= "\t" . "-ms-animation-delay: " . ($duration - $delay - $i * $delay) . "s;" . "\n";
				$string .= "\t" . "animation-delay: " . ($duration - $delay - $i * $delay) . "s;" . "\n";
				$string .= "}" . "\n\n";
			}
			foreach (array("-moz-", "-webkit-", "-o-",  "-ms-", "") as $prefix) {
				$string .= "@" . $prefix . "keyframes " . $this->name . "_fade {" . "\n";
				$string .= "\t" . "0% {" . "\n";
				$string .= "\t\t" . "opacity: 1;" . "\n";
				$string .= "\t" . "}" . "\n";
				$string .= "\t" . round($this->image_duration / $duration * 100) . "% {" . "\n";
				$string .= "\t\t" . "opacity: 1;" . "\n";
				$string .= "\t" . "}" . "\n";
				$string .= "\t" . round($delay / $duration * 100) . "% {" . "\n";
				$string .= "\t\t" . "opacity: 0;" . "\n";
				$string .= "\t" . "}" . "\n";
				$string .= "\t" . round(100 - (($this->fade_time / $duration) * 100)) . "% {" . "\n";
				$string .= "\t\t" . "opacity: 0;" . "\n";
				$string .= "\t" . "}" . "\n";
				$string .= "\t" . "100% {" . "\n";
				$string .= "\t\t" . "opacity: 1;" . "\n";
				$string .= "\t" . "}" . "\n";
				$string .= "}" . "\n\n";
			}
			return $string;
		}

		public function createCssFile() {
			$fp = @fopen($root . $this->name . "-slider.css", "w");
			if ($fp === false) {
				throw new SliderException("Cannot create or open file " . $root . $nazwa . "-slider.css");
			}
			fwrite($fp, $this->getCssCode());
			fclose($fp);
		}

		private $image_duration = 5;
		private $fade_time = 1;
		private $width;
		private $height;
		private $images = array();
		private $path;
		private $name;
		private $root;
	}
?>
