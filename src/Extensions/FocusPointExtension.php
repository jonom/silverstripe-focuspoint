<?php

namespace JonoM\FocusPoint\Extensions;

use JonoM\FocusPoint\FieldType\DBFocusPoint;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;

/**
 * FocusPoint Image extension.
 * Abstract extension applied to either DBFile or File dataobject
 * Extends Image to allow automatic cropping from a selected focus point.
 *
 * @extends DataExtension
 * @property DBFocusPoint $FocusPoint
 * @property DBFile|Image|FocusPointImageExtension $owner
 */
class FocusPointExtension extends Extension
{
    /**
     * Generate a percentage based description of x focus point for use in CSS.
     * Range is 0% - 100%. Example x=.5 translates to 75%
     * Use in templates with {$PercentageX}%.
     *
     * @return int
     */
    public function PercentageX(): int
    {
        $field = $this->owner->FocusPoint;
        if ($field) {
            return intval(round(DBFocusPoint::focusCoordToOffset($field->getX()) * 100));
        }
        return 0;
    }

    /**
     * Generate a percentage based description of y focus point for use in CSS.
     * Range is 0% - 100%. Example y=-.5 translates to 75%
     * Use in templates with {$PercentageY}%.
     *
     * @return int
     */
    public function PercentageY(): int
    {
        $field = $this->owner->FocusPoint;
        if ($field) {
            return intval(round(DBFocusPoint::focusCoordToOffset($field->getY()) * 100));
        }
        return 0;
    }

    /**
     * Debug output for this focus point image
     *
     * @return DBHTMLText
     */
    public function DebugFocusPoint(): DBHTMLText
    {
        Requirements::css('jonom/focuspoint: client/dist/styles/debug.css');
        return $this->owner->renderWith('JonoM/FocusPoint/FocusPointDebug');
    }


    /**
     * Crop this image to the aspect ratio defined by the specified width and
     * height, centred on focal point of image, then scale down the image to those
     * dimensions if it exceeds them. Similar to FocusFill but without
     * up-sampling. Use in templates with $FocusFillMax.
     *
     * @param int $width Width to crop to
     * @param int $height Height to crop to
     * @return Image|DBFile|null
     */
    public function FocusFillMax(int $width, int $height)
    {
        $cropData = $this->owner->FocusPoint->calculateCrop($width, $height, false);
        $variant = $this->owner->variantName(__FUNCTION__, $width, $height);
        return $this->manipulateImageCropData($variant, $cropData);
    }

    /**
     * Crop an image to a maximum width, but will not make it wider
     *
     * @param int $width
     * @return Image|DBFile|null
     */
    public function FocusCropWidth(int $width)
    {
        // Don't upscale
        if ($this->owner->FocusPoint->Width <= $width) {
            return $this->owner;
        }

        $cropData = $this->owner->FocusPoint->calculateCrop($width, null, true);
        $variant = $this->owner->variantName(__FUNCTION__, $width);
        return $this->manipulateImageCropData($variant, $cropData);
    }

    /**
     * Crop an image to a maximum height, but will not make it taller
     *
     * @param int $height
     * @return Image|DBFile|null
     */
    public function FocusCropHeight(int $height)
    {
        // Don't upscale
        if ($this->owner->FocusPoint->Height <= $height) {
            return $this->owner;
        }

        $cropData = $this->owner->FocusPoint->calculateCrop(null, $height, true);
        $variant = $this->owner->variantName(__FUNCTION__, $height);
        return $this->manipulateImageCropData($variant, $cropData);
    }

    /**
     * Generate a resized copy of this image with the given width & height,
     * cropping to maintain aspect ratio and focus point. Use in templates with
     * $FocusFill.
     *
     * Use {@see FocusFillMax} to prevent upscaling
     *
     * @param int $width Width to crop to
     * @param int $height Height to crop to
     *
     * @return Image|DBFile|null
     */
    public function FocusFill(int $width, int $height)
    {
        $cropData = $this->owner->FocusPoint->calculateCrop($width, $height, true);
        $variant = $this->owner->variantName(__FUNCTION__, $width, $height);
        return $this->manipulateImageCropData($variant, $cropData);
    }

    /**
     * Manipulate helper, but ensure we have a FocusPoint field on the result
     *
     * @param string $variant
     * @param array|null $cropData
     * @return DBFile|Image|null
     */
    protected function manipulateImageCropData(string $variant, ?array $cropData)
    {
        // Crop failed, no image
        if (!$cropData) {
            return null;
        }

        // Respect force_resample
        if ($cropData['x']['TargetLength'] === $cropData['x']['OriginalLength']
            && $cropData['y']['TargetLength'] === $cropData['y']['OriginalLength']
            && !Config::inst()->get(DBFile::class, 'force_resample')
        ) {
            return $this->owner;
        }

        // Defer to main manipulation
        $newImage = $this->owner->manipulateImage($variant, function (Image_Backend $backend) use ($cropData) {
            return $this->owner->FocusPoint->applyCrop($backend, $cropData);
        });

        // Crop failed, no image
        if (!$newImage) {
            return null;
        }

        // Hydrate focus point
        if (empty($newImage->FocusPoint)) {
            $newFocusPoint = DBFocusPoint::create();
            $newFocusPoint->setValue([
                'X'      => $cropData['x']['FocusPoint'],
                'Y'      => $cropData['y']['FocusPoint'],
                'Width'  => $cropData['x']['TargetLength'],
                'Height' => $cropData['y']['TargetLength'],
            ], $newImage);
            $newImage->FocusPoint = $newFocusPoint;
        }

        return $newImage;
    }
}