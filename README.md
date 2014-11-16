# FocusPoint: Smarter Image Cropping for SilverStripe

## Overview

The goal of this module is to provide some control over automatic image cropping in SilverStripe.

**Problem:** SilverStripe crops all images from the centre. If the subject is off-centre, it may be cropped out.

**Solution:** FocusPoint allows you to tag the subject in an image and ensures it is not lost during cropping.

![Comparison of cropping with and without FocusPoint](screenshots/comparison.jpg?raw=true)

## Requirements

SilverStripe 3.1

## Installation

**Manually:** Download, place the folder in your project root and run a dev/build?flush=1.

**Composer/Packagist:** Add "jonom/focuspoint" to your requirements.

## Usage

**In the CMS:** When you edit an image in the CMS there should be an extra 'Focus Point' field. Click on the subject of the image to set the focus area and save the image.

**In templates and PHP:** Use just like CroppedImage, but use CroppedFocusedImage instead. Additionally you can specify that images should not be upscaled by passing a third argument: `$image->CroppedFocusedImage($w,$h,$upscale=false)`.

**Responsive cropping:** You can use this module in combination with [jQuery FocusPoint ](https://github.com/jonom/jquery-focuspoint)to accomplish 'responsive cropping' on your website. Check out a [demo here](http://jonom.github.io/jquery-focuspoint/demos/grid/lizard.html). There is an example .ss template included in the jquery-focuspoint folder to help you set this up.

## Optional Usage
An extension is included that allows for updating the LastEdited value of DataObjects associated with Images being refocussed.  This is in order to simplify checking on for example the case where a folder of DataObjects with an image is rendered such that the images show.  If the cache key is based on the LastEdited value of the DataObjects in the folder,  then when an image is refocused it will not show, as the cache key knows nothing about the LastEdited times of the images.  The only alternative here was to include the LastEdited value of all images, or using a method including a database join to get the correct LastEdited value.

In order to invoke the extension, use the extensions mechanism as below, normally with a file called extensions.yml:

```
---
name: your-refocused-image-cache-buster
---
Image:
  extensions:
    ['FocusPointCacheBustExtension']
```

The classes to check for image IDs are configured as follows, in an arbitrarily named .yml file under any module's _config directory.  There are two keys under 'RefocusImageCacheBust'
* stages: the stages configured in your default configuration, the default being Stage and Live
* classes: a nesterd array of ClassName mapped to the field containing the image ID.

In the example below every time an image is refocussed every PageWithImage data object will have it's LastEdited field updated to now (thus busting fragment caches) if the value of MainImageID matches the ID of the image being edited.  Similarly with both SlidePage and Staff, except this time checking the PhotoID field.
```
---
Name: refocusimagecachebust
After: framework/routes#coreroutes
---

RefocusImageCacheBust:
  stages: ['Stage','Live']
  classes:
    PageWithImage : 'MainImageID'
    SlidePage : 'PhotoID'
    Staff : 'PhotoID'
```


## To Do

 * Override CroppedImage() instead of adding new method (Note: I've tried everything I could think of to do this. It may be impossible)
 * ImageMagick support (maybe already works - need to test)
 
## Maintainer contact

[jonathonmenz.com](http://jonathonmenz.com)
