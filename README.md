Images-Crossfader
=================

PHP library for generating pure CSS3 gallery with smooth crossfading between as many images as you want. This tool allow auto cropping/resizing images. 

## Instruction

* create an object of Slider class
	* in constructor set width and height of slider
	* third argument is name of your slider (it will be id parameter in HTML code)
	* border (optional, default none, example: 10px solid #000000)
	* last argument is optional - that is where slider.php and image.php is placed (default ./)
* add as many images as you want
	* PLEASE do not use "-" in source names and new names of images
	* it should be .jpg or .jpeg extensions (in the future there will be more extensions)
	* arguments are:
		* path - full path of source image (with extension)
		* alt - image description (used in HTML code, quite important)
		* new_name (optional) if this argument is empty, name from source image is taken
		* title - title of image used in HTML code, optional, default empty
		* crop_pos - there are 3 capabilities of cropping source image:
			* Image::TOP
			* Image::MIDDLE (default)
			* Image::BOTTOM
	* if you don't set new_name argument, images are numbered (for example name-1.jpg)
	* library do not overwrite existing files - that's why is faster, but if you want 3 images with different crop position from one source image (so with the same image name) it is considered (for example name.jpg, name-1.jpg, name-2.jpg)
* set time of presentation one image (optional, default is 5s)
* set time of fade (optional, default is 1s)
* generate CSS code and HTML code (look at example)
	* there is posibility to generate CSS file and place it in 
	\<link rel="stylesheet" href="HERE NAME OF GENERATED CSS FILE" type="text/css" /\> 
	in HTML code

## Example

Demo: http://diversipes.com/demo/

Demo source:
```php
<?php
	include("slider.php");
	$slider = new Slider(800, 300, "header", "10px solid #000000");
	$slider->addImage("octocat.jpg", "octocat"); 
	$slider->addImage("octocat.jpg", "octocat", null, null, Image::BOTTOM); 
	$slider->addImage("tux.jpg", "tux", null, "This is tux"); 
	$slider->addImage("wilber.jpg", "wilber"); 
	//optional:
	$slider->setImageDuration("3");
	$slider->setFadeTime("0.5");
?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8" />
	<title>Images-Crossfader</title>
	<style type="text/css">
	<?php
		echo $slider->getCssCode();
	?>
	</style>
</head>
<body>
	<?php
		echo $slider->getHtmlCode();
	?>
</body>
```


License
=======

GNU/GPLv3
