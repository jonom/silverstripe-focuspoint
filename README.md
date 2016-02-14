# FocusPoint: Smarter Image Cropping for SilverStripe

The goal of this module is to provide some control over automatic image cropping in SilverStripe.

**Problem:** SilverStripe crops all images from the centre. If the subject is off-centre, it may be cropped out.

**Solution:** FocusPoint allows you to tag the subject in an image and ensures it is not lost during cropping.

![Comparison of cropping with and without FocusPoint](screenshots/comparison.jpg)

## Requirements

SilverStripe 3.2

## Installation

### Composer (best practice)

[Packagist listing](https://packagist.org/packages/jonom/focuspoint) and [installation instructions](http://doc.silverstripe.org/framework/en/trunk/installation/composer#adding-modules-to-your-project)

### Manually

Download and extract, rename the module folder 'focuspoint', place it in your project root and run a dev/build?flush=1.

**Note:** Prior to SilverStripe v3.1.9 *dev/build?flush=1* did not flush .ss template files. If using SilverStripe 3.1.8 or earlier you'll need to flush the templates on a page URL e.g. *yoursite.dev/?flush=1*.

Fun fact: you don't generally need to manually flush template files in SilverStripe after changing them - except when they are new or are rendered via `<% include %>`.

## Basic usage

### In the CMS

When you edit an image in the CMS there should be an extra 'Focus Point' field. Click on the subject of the image to set the focus area and save the image.

### In templates and PHP

Use just like existing cropping functions but swap out the names:

- $Fill --> $FocusFill
- $FillMax --> $FocusFillMax
- $CropWidth --> $FocusCropWidth
- $CropHeight --> $FocusCropHeight

## Advanced usage


### Method chaining

Image method chaining e.g. `$Image.ScaleHeight(200).FocusCropWidth(200)` should work from SilverStripe 3.3 onwards and update the focus point accordingly. This is helpful if you want to make use of the focal point in templates e.g. for the Responsive Cropping example below.

A small catch is that you can't include regular (non FocusPoint) cropping methods in the chain, as these won't re-calculate the focus point after cropping. For example `$Image.Fill(200,200).FocusCropWidth(100)` won't work properly... Not that you'd ever want to do that!

### Responsive cropping

You can use this module in combination with [jQuery FocusPoint ](https://github.com/jonom/jquery-focuspoint)to accomplish 'responsive cropping' on your website. Check out a [demo here](http://jonom.github.io/jquery-focuspoint/demos/grid/lizard.html). To set this up do something like this in your templates:

```html
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

#### CSS-only version

Try something like this to get a full-screen background image that preserves your focus point as you resize the browser window.

```html
<body
	<% with $BGImage %>
		style="background-image: url($Link);
			background-position: $PercentageX% $PercentageY%;
			background-size: cover;"
	<% end_with %>
>
```

### Partial cache busting

If you are caching page content that includes a FocusFill and you edit the image (i.e. by changing the focus point) but not the page, you may want to invalidate the page's cache as the updated FocusFill will have a different filename. Gordon Banderson has written a [robust extension](https://github.com/gordonbanderson/weboftalent-imageeditpartialcachebust) to help you achieve this.

### Fine-tuned cropping in individual contexts

SilverStripe FocusPoint provides an easy and automated way to get better results when forcing an image to be a different aspect ratio. I have some vague plans to offer more fine-grained control over individual crops in the future, but until then I recommend checking out Will Morgan's [SilverStripe CropperField](https://github.com/willmorgan/silverstripe-cropperfield) as an alternative.

### Flush generated images on focus point change

You can specify that resampled versions of an image should be flushed when its focus point is changed by setting the `FocusPointImage.flush_on_change` config value. For example:

```yml
# config.yml
FocusPointImage:
  flush_on_change: true
```

## Upgrading from 1.x

- `FocusPointField` has been refactored. If you're using the class in your own code you will need to update it.

## Love this module?

I spend a lot of time on open-source projects. If you'd like to buy me a coffee, make a recurring donation or just shoot me an email to keep me motivated it's always appreciated!

[<img src="https://www.paypalobjects.com/en_AU/i/btn/btn_donate_LG.gif" alt="Donate">](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Z5HEZREZSKA6A)

## Troubleshooting

### The FocusPoint field in the CMS appears broken

If the focus point field shows a non-interactive image and a text field with a comma in it, make sure the module folder is named 'focuspoint' and try visiting *yoursite.dev/?flush=1* again.

### Focus point has been changed but image has not updated

As a cache-busting mechanism this module includes approximate focus point coordinates in generated filenames. This means that if the focus point is updating correctly in the CMS but you're not seeing images change on your website, it's likely that you're viewing cached HTML output and need to invalidate that to see the updated image.

Other SilverStripe modules can also prevent images being regenerated when the focus point is changed. You can work around this by telling SilverStripe to [delete resampled versions of an image when its focus point is changed](#flush-generated-images-on-focus-point-change).

## To Do

 * Override Fill() instead of adding new method (Note: I've tried everything I could think of to do this. It may be impossible)
 * ImageMagick support (maybe already works - can anyone confirm?)
 * Internationalisation
 * Advanced cropping options and interfaces (may be an additional module)
 * Auto detect focus point via Imagga API

## Maintainer contact

[jonathonmenz.com](http://jonathonmenz.com)
