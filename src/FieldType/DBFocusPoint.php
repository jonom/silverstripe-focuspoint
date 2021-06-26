<?php

namespace JonoM\FocusPoint\FieldType;


use InvalidArgumentException;
use JonoM\FocusPoint\Forms\FocusPointField;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\ORM\FieldType\DBComposite;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;

/**
 * Focus point composite field.
 *
 * @property double $X
 * @property double $Y
 * @property int $Width
 * @property int $Height
 * @property DBFile|Image $record
 */
class DBFocusPoint extends DBComposite
{
    /**
     * Describes the focus point coordinates on an image.
     * FocusX: Decimal number between -1 & 1, where -1 is far left, 0 is center, 1 is far right.
     * FocusY: Decimal number between -1 & 1, where -1 is top, 0 is center, 1 is bottom.
     */
    private static $composite_db = [
        'X'      => 'Double',
        'Y'      => 'Double',
        'Width'  => 'Int', // Cached width for this image
        'Height' => 'Int', // Cached height for this image
    ];

    /**
     * Focus X
     * @return double Decimal number between -1 & 1, where -1 is far left, 0 is center, 1 is far right.
     */
    public function getX(): float
    {
        return (float)$this->getField('X');
    }

    /**
     * Set the focus point X coordinate
     *
     * @param float $value
     * @return $this
     */
    public function setX(float $value): self
    {
        $this->setField('X', min(1, max(-1, $value)));
        return $this;
    }

    /**
     * Focus Y
     * @return float Decimal number between -1 & 1, where -1 is top, 0 is center, 1 is bottom.
     */
    public function getY(): float
    {
        return (float)$this->getField('Y');
    }

    /**
     * Get width of the original image.
     *
     * @return int
     */
    public function getWidth(): int
    {
        $width = $this->getField('Width');
        if ($width) {
            return intval($width);
        }
        if ($this->record) {
            return intval($this->record->getWidth());
        }

        return 0;
    }

    /**
     * Get height of the original image
     *
     * @return int
     */
    public function getHeight(): int
    {
        $height = $this->getField('Height');
        if ($height) {
            return intval($height);
        }
        if ($this->record) {
            return intval($this->record->getHeight());
        }
        return 0;
    }

    /**
     * Set the focus point Y coordinate
     * @param float $value
     * @return $this
     */
    public function setY(float $value): self
    {
        $this->setField('Y', min(1, max(-1, $value)));
        return $this;
    }

    public function exists(): bool
    {
        // Is always true for this composite field, since it defaults to 0,0
        return true;
    }

    /**
     * @inheritdoc
     * @return FocusPointField
     */
    public function scaffoldFormField($title = null, $params = null): FocusPointField
    {
        return FocusPointField::create(
            $this->name,
            $title,
            $this->record instanceof Image ? $this->record : null
        );
    }

    /**
     * Turn a focus x/y coordinate in to an offset from left or top
     *
     * @param float $coord the coordinate to transform
     * @return float
     */
    public static function focusCoordToOffset(float $coord): float
    {
        return ($coord + 1) * 0.5;
    }

    /**
     * Turn a left/top offset in to a focus x/y coordinate
     *
     * @param float $offset the offset to transform
     * @return float
     */
    public static function focusOffsetToCoord(float $offset)
    {
        return $offset * 2 - 1;
    }

    /**
     * Caluclate crop data given the desired width and height, as well as original width and height.
     * Calculates required crop coordinates using current FocusX and FocusY
     * @param int|null $width desired width. Can be omitted as long as $height is provided.
     * @param int|null $height desired height. Can be omitted as long as $width is provided.
     * @param bool $upscale Is this being upscaled?
     * @return array|null Array with fields x, y, each with array of FocusPoint, OriginalLength and TargetLength
     * Can return null if error
     */
    public function calculateCrop(?int $width, ?int $height, bool $upscale): ?array
    {
        // Requested to resize to 0 results in an error
        if (empty($width) && empty($height)) {
            throw new InvalidArgumentException("Width and Height cannot be resized to 0");
        }

        // Not so much an error, but ignore empty files
        if (empty($this->Width) || empty($this->Height)) {
            return null;
        }

        // Assign targets, considering that we may only be scaling on one dimension
        $targetWidth = $width ?: $this->Width;
        $targetHeight = $height ?: $this->Height;

        // Handle upscaling
        if (!$upscale) {
            $widthRatio = $this->Width / $targetWidth;
            $heightRatio = $this->Height / $targetHeight;
            if ($widthRatio < 1 && $widthRatio <= $heightRatio) {
                $targetWidth = $this->Width;
                $targetHeight = intval(round($targetHeight * $widthRatio));
            } elseif ($heightRatio < 1) {
                $targetHeight = $this->Height;
                $targetWidth = intval(round($targetWidth * $heightRatio));
            }
        }


        // Work out how to crop the image and provide new focus coordinates
        $cropData = [
            'CropAxis'   => 0,
            'CropOffset' => 0,
        ];

        $cropData['x'] = [
            'FocusPoint'     => $this->getX(),
            'OriginalLength' => $this->Width,
            'TargetLength'   => $targetWidth,
            'ScaleRatio'     => $this->Width / $targetWidth,
        ];

        $cropData['y'] = [
            'FocusPoint'     => $this->getY(),
            'OriginalLength' => $this->Height,
            'TargetLength'   => $targetHeight,
            'ScaleRatio'     => $this->Height / $targetHeight,
        ];

        // Work out which axis to crop on
        $cropAxis = null;
        $scaleRatio = null;
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
            $focusOffset = $this->focusCoordToOffset($cropData[$cropAxis]['FocusPoint']);
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
            $cropData[$cropAxis]['FocusPoint'] = $this->focusOffsetToCoord($newFocusOffset);
        }

        return $cropData;
    }

    /**
     * Apply a cropData array to an Image_Backend instance
     *
     * @param Image_Backend $backend the image to crop. If not set, the current record will be used
     * @param array $cropData Crop data to apply to this
     * @return Image_Backend|null New image if resized, or null if no resize needed
     */
    public function applyCrop(Image_Backend $backend, array $cropData): ?Image_Backend
    {
        $width = $cropData['x']['TargetLength'];
        $height = $cropData['y']['TargetLength'];
        $cropAxis = $cropData['CropAxis'];
        $cropOffset = $cropData['CropOffset'];

        // Resize based on axis
        switch ($cropAxis) {
            case 'x':
                //Generate image
                return $backend
                    ->resizeByHeight($height)
                    ->crop(0, $cropOffset, $width, $height);
            case 'y':
                //Generate image
                return $backend
                    ->resizeByWidth($width)
                    ->crop($cropOffset, 0, $width, $height);
            default:
                //Generate image without cropping
                return $backend->resize($width, $height);
        }
    }

    /**
     * Generate a percentage based description of x focus point for use in CSS.
     * Range is 0% - 100%. Example x=.5 translates to 75%
     * Use in templates with {$PercentageX}%.
     *
     * @return int
     */
    public function PercentageX(): int
    {
        return intval(round(DBFocusPoint::focusCoordToOffset($this->getX()) * 100));
    }

    /**
     * Generate a percentage based description of y focus point for use in CSS.
     * Range is 0% - 100%. Example y=-.5 translates to 25%
     * Use in templates with {$PercentageY}%.
     *
     * @return int
     */
    public function PercentageY(): int
    {
        return intval(round(DBFocusPoint::focusCoordToOffset($this->getY()) * 100));
    }
}
