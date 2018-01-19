<?php

namespace JonoM\FocusPoint\Extensions;

use JonoM\FocusPoint\Dev\FocusPointMigrationTask;
use JonoM\FocusPoint\FieldType\DBFocusPoint;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;

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
     */
    private static $db = array(
        'FocusPoint' => DBFocusPoint::class
    );

    /**
     * Generate a percentage based description of x focus point for use in CSS.
     * Range is 0% - 100%. Example x=.5 translates to 75%
     * Use in templates with {$PercentageX}%.
     *
     * @return int
     */
    public function PercentageX()
    {
        return round($this->focusCoordToOffset('x', $this->owner->getField('FocusPoint')->FocusX) * 100);
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
        return round($this->focusCoordToOffset('y', $this->owner->getField('FocusPoint')->FocusY) * 100);
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
        return $this->FocusFill($width, $height, $upscale = false);
    }

    public function FocusCropWidth($width)
    {
        return ($this->owner->getWidth() > $width)
            ? $this->FocusFill($width, $this->owner->getHeight())
            : $this->owner;
    }

    public function FocusCropHeight($height)
    {
        return ($this->owner->getHeight() > $height)
            ? $this->FocusFill($this->owner->getWidth(), $height)
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
        return $this->owner->getField('FocusPoint')->generateFocusFill($width, $height, $this->owner, $upscale);
    }

    public function requireDefaultRecords()
    {
        $autoMigrate = FocusPointMigrationTask::create();
        $autoMigrate->up();
    }
}
