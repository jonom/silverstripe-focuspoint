<?php

namespace JonoM\FocusPoint;

use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\Requirements;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\ORM\DataExtension;

/**
 * FocusPoint Image extension.
 * Extends Image to allow automatic cropping from a selected focus point.
 *
 * @extends DataExtension
 */
class FocusPointImageExtension extends DataExtension
{
    /**
     * Describes the focus point coordinates on the image.
     * FocusX: Decimal number between -1 & 1, where -1 is far left, 0 is center, 1 is far right.
     * FocusY: Decimal number between -1 & 1, where -1 is bottom, 0 is center, 1 is top.
     */
    private static $db = array(
        'FocusX' => 'Double',
        'FocusY' => 'Double',
    );

    /**
     * Preserve default behaviour of cropping from center.
     */
    private static $defaults = array(
        'FocusX' => '0',
        'FocusY' => '0',
    );

    /**
     * Add FocusPoint field for selecting focus.
     */
    public function updateCMSFields(FieldList $fields)
    {
        $f = new FocusPointField($this->owner);
        if ($fields->hasTabSet()) {
            $fields->addFieldToTab('Root.Main', $f);
        } else {
            $fields->add($f);
        }
    }

    /**
     * Generate a percentage based description of x focus point for use in CSS.
     * Range is 0% - 100%. Example x=.5 translates to 75%
     * Use in templates with {$PercentageX}%.
     *
     * @return int
     */
    public function PercentageX()
    {
        return round($this->focusCoordToOffset('x', $this->owner->FocusX) * 100);
    }

    /**
     * Generate a percentage based description of y focus point for use in CSS.
     * Range is 0% - 100%. Example y=-.5 translates to 75%
     * Use in templates with {$PercentageY}%.
     *
     * @return int
     */
    public function PercentageY()
    {
        return round($this->focusCoordToOffset('y', $this->owner->FocusY) * 100);
    }

    public function DebugFocusPoint()
    {
        Requirements::css('jonom/focuspoint: client/css/focuspoint-debug.css');
        return $this->owner->renderWith('JonoM/FocusPoint/FocusPointDebug');
    }

    /**
     * Pre-render CSS for positioning crosshairs in focuspoint field.
     * This prevents lag or miscalculation.
     *
     * @return string
     */
    public function FieldGridBackgroundCSS()
    {
        // Calculate background positions
        $backgroundWH = 605; // Width (and also height, since it's square) of grid crosshair background image
        $bgOffset = floor(-$backgroundWH/2);
        $fieldW = $this->owner->getWidth();
        $fieldH = $this->owner->getHeight();
        $leftBG = $bgOffset+(($this->owner->FocusX/2 +.5)*$fieldW);
        $topBG = $bgOffset+((-$this->owner->FocusY/2 +.5)*$fieldH);

        // Line up crosshairs with click position
        return 'background-position: ' . $leftBG . 'px ' . $topBG . 'px;';
    }

    public function focusCoordToOffset($axis, $coord)
    {
        // Turn a focus x/y coordinate in to an offset from left or top
        if ($axis == 'x') {
            return ($coord + 1) * 0.5;
        }
        if ($axis == 'y') {
            return ($coord - 1) * -0.5;
        }
    }

    public function focusOffsetToCoord($axis, $offset)
    {
        // Turn a left/top offset in to a focus x/y coordinate
        if ($axis == 'x') {
            return $offset * 2 - 1;
        }
        if ($axis == 'y') {
            return $offset * -2 + 1;
        }
    }

    public function calculateCrop($width, $height)
    {
        // Work out how to crop the image and provide new focus coordinates
        $cropData = array(
            'CropAxis' => 0,
            'CropOffset' => 0,
        );
        $cropData['x'] = array(
            'FocusPoint' => $this->owner->FocusX,
            'OriginalLength' => $this->owner->getWidth(),
            'TargetLength' => round($width),
        );
        $cropData['y'] = array(
            'FocusPoint' => $this->owner->FocusY,
            'OriginalLength' => $this->owner->getHeight(),
            'TargetLength' => round($height),
        );

        // Avoid divide by zero error
        if (!($cropData['x']['OriginalLength'] > 0 && $cropData['y']['OriginalLength'] > 0)) {
            return false;
        }

        // Work out which axis to crop on
        $cropAxis = false;
        $cropData['x']['ScaleRatio'] = $cropData['x']['OriginalLength'] / $cropData['x']['TargetLength'];
        $cropData['y']['ScaleRatio'] = $cropData['y']['OriginalLength'] / $cropData['y']['TargetLength'];
        if ($cropData['x']['ScaleRatio'] < $cropData['y']['ScaleRatio']) {
            // Top and/or bottom of image will be lost
            $cropAxis = 'y';
            $scaleRatio = $cropData['x']['ScaleRatio'];
        } elseif ($cropData['x']['ScaleRatio'] > $cropData['y']['ScaleRatio']) {
            // Left and/or right of image will be lost
            $cropAxis = 'x';
            $scaleRatio = $cropData['y']['ScaleRatio'];
        }
        $cropData['CropAxis'] = $cropAxis;

        // Adjust dimensions for cropping
        if ($cropAxis) {
            // Focus point offset
            $focusOffset = $this->focusCoordToOffset($cropAxis, $cropData[$cropAxis]['FocusPoint']);
            // Length after scaling but before cropping
            $scaledImageLength = floor($cropData[$cropAxis]['OriginalLength'] / $scaleRatio);
            // Focus point position in pixels
            $focusPos = floor($focusOffset * $scaledImageLength);
            // Container center in pixels
            $frameCenter = floor($cropData[$cropAxis]['TargetLength'] / 2);
            // Difference beetween focus point and center
            $focusShift = $focusPos - $frameCenter;
            // Limit offset so image remains filled
            $remainder = $scaledImageLength - $focusPos;
            $croppedRemainder = $cropData[$cropAxis]['TargetLength'] - $frameCenter;
            if ($remainder < $croppedRemainder) {
                $focusShift -= $croppedRemainder - $remainder;
            }
            if ($focusShift < 0) {
                $focusShift = 0;
            }
            // Set cropping start point
            $cropData['CropOffset'] = $focusShift;
            // Update Focus point location for cropped image
            $newFocusOffset = ($focusPos - $focusShift) / $cropData[$cropAxis]['TargetLength'];
            $cropData[$cropAxis]['FocusPoint'] = $this->focusOffsetToCoord($cropAxis, $newFocusOffset);
        }

        return $cropData;
    }

    /**
     * Crop this image to the aspect ratio defined by the specified width and
     * height, centred on focal point of image, then scale down the image to those
     * dimensions if it exceeds them. Similar to FocusFill but without
     * up-sampling. Use in templates with $FocusFillMax.
     *
     * @param int $width  Width to crop to
     * @param int $height Height to crop to
     *
     * @return Image|null
     */
    public function FocusFillMax($width, $height)
    {
        return $this->owner->FocusFill($width, $height, $upscale = false);
    }

    public function FocusCropWidth($width)
    {
        return ($this->owner->getWidth() > $width)
            ? $this->owner->FocusFill($width, $this->owner->getHeight())
            : $this->owner;
    }

    public function FocusCropHeight($height)
    {
        return ($this->owner->getHeight() > $height)
            ? $this->owner->FocusFill($this->owner->getWidth(), $height)
            : $this->owner;
    }

    /**
     * Generate a resized copy of this image with the given width & height,
     * cropping to maintain aspect ratio and focus point. Use in templates with
     * $FocusFill.
     *
     * @param int  $width   Width to crop to
     * @param int  $height  Height to crop to
     * @param bool $upscale Will prevent upscaling if set to false
     *
     * @return Image|null
     */
    public function FocusFill($width, $height, $upscale = true)
    {
        $width = intval($width);
        $height = intval($height);
        $imgW = $this->owner->getWidth();
        $imgH = $this->owner->getHeight();
        // Don't enlarge
        if (!$upscale) {
            $widthRatio = $imgW / $width;
            $heightRatio = $imgH / $height;
            if ($widthRatio < 1 && $widthRatio <= $heightRatio) {
                $width = $imgW;
                $height = intval(round($height * $widthRatio));
            } elseif ($heightRatio < 1) {
                $height = $imgH;
                $width = intval(round($width * $heightRatio));
            }
        }
        //Only resize if necessary
        if ($this->owner->isSize($width, $height) && !Config::inst()->get(Image::class, 'force_resample')) {
            return $this->owner;
        } elseif ($cropData = $this->calculateCrop($width, $height)) {
            $cropAxis = $cropData['CropAxis'];
            $cropOffset = $cropData['CropOffset'];

            $variant = $this->owner->variantName(__FUNCTION__, $width, $height, $cropAxis, $cropOffset);
            $img = $this->owner->manipulateImage($variant, function (Image_Backend $backend) use ($width, $height, $cropAxis, $cropOffset) {
                if ($cropAxis == 'x') {
                    //Generate image
                    return $backend
                        ->resizeByHeight($height)
                        ->crop(0, $cropOffset, $width, $height);
                } elseif ($cropAxis == 'y') {
                    //Generate image
                    return $backend
                        ->resizeByWidth($width)
                        ->crop($cropOffset, 0, $width, $height);
                } else {
                    //Generate image without cropping
                    return $backend->resize($width, $height);
                }
            });
            if (!$img) {
                return null;
            }

            // Update FocusPoint
            $img->FocusX = $cropData['x']['FocusPoint'];
            $img->FocusY = $cropData['y']['FocusPoint'];

            return $img;
        }
    }
}
