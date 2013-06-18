# FocusPoint module for SilverStripe

## Overview

The goal of this module is to improve automatic image cropping in SilverStripe by allowing a focus point to be set on an image.

Notable features:

 * Back-end: Ensures focal point of image is not cropped out when using $CroppedImage (Use CroppedFocusedImage instead)
 * Front-end: Focus coordinates are available in templates, which can be used to ensure that the focal point is not lost on responsive or full screen images

## Requirements

Silverstripe 3.0.x / 3.1.x

## Installation

Manually: Download, place the folder in your project root and run a dev/build?flush=1.

Composer/Packagist: Add "jonom/focuspoint" to your requirements.

## Usage

In templates: Use just like CroppedImage, but use CroppedFocusedImage instead.

In the CMS: When you edit an image in the CMS there should be an extra 'Focus Point' field. Click on the image to choose a focus area and save the image.

Responsive images: see example of how to set up a full-screen responsive image in the jquery-focuspoint folder. Note: this is an early proof of concept, I may move this to a separate project in the future.

## To Do

 * Allow pixel level precision for choosing focus point in CMS instead of restricting to grid
 * Override CroppedImage() instead of adding new method
 * ImageMagick support
 
## Maintainer contact

www.jonathonmenz.com