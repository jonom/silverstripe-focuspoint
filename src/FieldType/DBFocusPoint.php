<?php

namespace JonoM\FocusPoint\FieldType;


use JonoM\FocusPoint\Forms\FocusPointField;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Assets\Storage\DBFile;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\FieldType\DBComposite;
use SilverStripe\ORM\FieldType\DBField;


class DBFocusPoint extends DBComposite
{
    /**
     * Describes the focus point coordinates on an image.
     * FocusX: Decimal number between -1 & 1, where -1 is far left, 0 is center, 1 is far right.
     * FocusY: Decimal number between -1 & 1, where -1 is top, 0 is center, 1 is bottom.
     */
    private static $composite_db = [
        'X' => 'Double',
        'Y' => 'Double'
    ];

    /**
     * Focus X
     * @return double Decimal number between -1 & 1, where -1 is far left, 0 is center, 1 is far right.
     */
    public function getX()
    {
        return (double)$this->getField('X');
    }

    /**
     * Set the focus point X coordinate
     * @param double $value
     * @return $this
     */
    public function setX($value)
    {
        $this->setField('X', min(1, max(-1, $value)));
        return $this;
    }

    /**
     * Focus Y
     * @return double Decimal number between -1 & 1, where -1 is top, 0 is center, 1 is bottom.
     */
    public function getY()
    {
        return (double)$this->getField('Y');
    }

    /**
     * Set the focus point Y coordinate
     * @param double $value
     * @return $this
     */
    public function setY($value)
    {
        $this->setField('Y', min(1, max(-1, $value)));
        return $this;
    }

    public function exists()
    {
        // Is always true for this composite field, since it defaults to 0,0
        return true;
    }

    /**
     * @inheritdoc
     * @return FocusPointField
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        return FocusPointField::create(
            $this->name,
            $title,
            $this->record instanceof Image ? $this->record : null
        );
    }

    /**
     * Turn a focus x/y coordinate in to an offset from left or top
     * @param double $coord the coordinate to transform
     * @return double
     */
    public static function focusCoordToOffset($coord)
    {
        return ($coord + 1) * 0.5;
    }

    /**
     * Turn a left/top offset in to a focus x/y coordinate
     * @param double $offset the offset to transform
     * @return double
     */
    public static function focusOffsetToCoord($offset)
    {
        return $offset * 2 - 1;
    }

    /**
     * Caluclate crop data given the desired width and height, as well as original width and height.
     * Calculates required crop coordinates using current FocusX and FocusY
     * @param int $width desired width
     * @param int $height desired height
     * @param int $originalWidth original image width
     * @param int $originalHeight original image height
     * @return array|bool
     */
    public function calculateCrop($width, $height, $originalWidth, $originalHeight)
    {
        // Work out how to crop the image and provide new focus coordinates
        $cropData = [
            'CropAxis' => 0,
            'CropOffset' => 0,
        ];

        $cropData['x'] = [
            'FocusPoint' => $this->getX(),
            'OriginalLength' => $originalWidth,
            'TargetLength' => round($width),
        ];

        $cropData['y'] = [
            'FocusPoint' => $this->getY(),
            'OriginalLength' => $originalHeight,
            'TargetLength' => round($height),
        ];

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
     * Generate a cropped version of the given image
     * @param int $width desired width
     * @param int $height desired height
     * @param Image $image the image to crop. If not set, the current record will be used
     * @param bool $upscale whether or not upscaling is allowed
     * @return AssetContainer|null
     */
    public function FocusFill($width, $height, AssetContainer $image, $upscale = true)
    {
        if (!$image && $this->record instanceof Image) {
            $image = $this->record;
        }
        $width = intval($width);
        $height = intval($height);
        $imgW = $image->getWidth();
        $imgH = $image->getHeight();

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
        if ($image->isSize($width, $height) && !Config::inst()->get(DBFile::class, 'force_resample')) {
            return $image;
        } elseif ($cropData = $this->calculateCrop($width, $height, $imgW, $imgH)) {
            $variant = $image->variantName(__FUNCTION__, $width, $height, $cropData['CropAxis'], $cropData['CropOffset']);
            $cropped = $image->manipulateImage($variant, function (Image_Backend $backend) use ($width, $height, $cropData) {
                $img = null;
                $cropAxis = $cropData['CropAxis'];
                $cropOffset = $cropData['CropOffset'];

                if ($cropAxis == 'x') {
                    //Generate image
                    $img = $backend
                        ->resizeByHeight($height)
                        ->crop(0, $cropOffset, $width, $height);
                } elseif ($cropAxis == 'y') {
                    //Generate image
                    $img = $backend
                        ->resizeByWidth($width)
                        ->crop($cropOffset, 0, $width, $height);
                } else {
                    //Generate image without cropping
                    $img = $backend->resize($width, $height);
                }

                if (!$img) {
                    return null;
                }

                return $img;
            });

            // Update FocusPoint
            $cropped->FocusPoint = DBField::create_field(static::class, [
                'X' => $cropData['x']['FocusPoint'],
                'Y' => $cropData['y']['FocusPoint']
            ]);

            return $cropped;
        }
        return null;
    }
}
