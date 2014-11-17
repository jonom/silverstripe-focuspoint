# FocusPoint: Smarter Image Cropping for SilverStripe

The goal of this module is to provide some control over automatic image cropping in SilverStripe.

**Problem:** SilverStripe crops all images from the centre. If the subject is off-centre, it may be cropped out.

**Solution:** FocusPoint allows you to tag the subject in an image and ensures it is not lost during cropping.

![Comparison of cropping with and without FocusPoint](screenshots/comparison.jpg)

## Requirements

SilverStripe 3.1

## Installation

###Composer (best practice)###

[Packagist listing](https://packagist.org/packages/jonom/focuspoint) and [installation instructions](http://doc.silverstripe.org/framework/en/trunk/installation/composer#adding-modules-to-your-project)

###Manually###

Download, place the folder in your project root and run a dev/build?flush=1.

Note: Currently *dev/build?flush=1* does not flush .ss template files. Usually template flushing happens automatically but ocassionally there can be problems with `<% include %>` templates not being flushed. If the FocusPoint field in the CMS looks broken try flushing the current URL e.g. *yoursite.com/admin/?flush=1*

## Basic usage

###In the CMS###

When you edit an image in the CMS there should be an extra 'Focus Point' field. Click on the subject of the image to set the focus area and save the image.

###In templates and PHP###

Use just like CroppedImage, but use CroppedFocusedImage instead.

## Advanced usage

###Prevent upscaling###

You can specify that images should not be upscaled by passing a third argument: `$image->CroppedFocusedImage($w,$h,$upscale=false)`.

###Responsive cropping###

You can use this module in combination with [jQuery FocusPoint ](https://github.com/jonom/jquery-focuspoint)to accomplish 'responsive cropping' on your website. Check out a [demo here](http://jonom.github.io/jquery-focuspoint/demos/grid/lizard.html). To set this up do something like this in your templates:

```
<% with $SomeImage %>
	<div class="focuspoint"
		data-focus-x="$FocusX"
		data-focus-y="$FocusY"
		data-image-w="$Width"
		data-image-h="$Height">
		<img src="$Link" alt="" />
	</div>
<% end_with %>
```

####CSS-only version####

Try something like this to get a full-screen background image that preserves your focus point.

```
<body
	<% with $BGImage %>
		style="background-image: url($Link);
			background-position: $PercentageX $PercentageY; 
			background-size: cover;"
	<% end_with %>
>
```

###Partial cache busting:###

If you are caching page content that includes a CroppedFocusedImage and you edit the image (i.e. by changing the focus point) but not the page, you may want to invalidate the page's cache as the updated CroppedFocusedImage will have a different filename. Gordon Banderson has written a [robust extension](https://github.com/gordonbanderson/weboftalent-imageeditpartialcachebust) to help you achieve this.


## To Do

 * Override CroppedImage() instead of adding new method (Note: I've tried everything I could think of to do this. It may be impossible)
 * ImageMagick support (maybe already works - need to test)
 
## Maintainer contact

[jonathonmenz.com](http://jonathonmenz.com)
