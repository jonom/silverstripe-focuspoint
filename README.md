# FocusPoint: Smarter Image Cropping for SilverStripe

## Overview

The goal of this module is to provide some control over automatic image cropping in SilverStripe.

**Problem:** SilverStripe crops all images from the centre. If the subject is off-centre, it may be cropped out.

**Solution:** FocusPoint allows you to tag the subject in an image and ensures it is not lost during cropping.

## Requirements

SilverStripe 3.1

## Installation

Manually: Download, place the folder in your project root and run a dev/build?flush=1.

Composer/Packagist: Add "jonom/focuspoint" to your requirements.

## Usage

In templates: Use just like CroppedImage, but use CroppedFocusedImage instead.

In the CMS: When you edit an image in the CMS there should be an extra 'Focus Point' field. Click on the subject of the image to set the focus area and save the image.

Responsive images: see example of how to set up a full-screen responsive image in the jquery-focuspoint folder. Note: this is an early proof of concept, I may move this to a separate project in the future.

## To Do

 * Override CroppedImage() instead of adding new method
 * ImageMagick support (maybe already works - need to test)
 
## Maintainer contact

[jonathonmenz.com]

[jonathonmenz.com]:http://jonathonmenz.com
