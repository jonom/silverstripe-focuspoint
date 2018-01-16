<?php

namespace JonoM\FocusPoint;

use Intervention\Image\Image as InterventionImage;
use SilverStripe\Assets\InterventionBackend as SSInterventionBackend;

/**
 * This is intended as a way to add FocusPoint functionality to SilverStripe's
 * built in cropping methods. It works, but if you change the focus point the
 * cache isn't cleared so images that have already been generated will continue
 * to use the old focus point. This isn't ready for prime time until we can bust
 * the cache when the focus point changes. Ideally the focus point coordinates
 * would be part of the file name hash used to generate the image.
 */
class InterventionBackend extends SSInterventionBackend
{

    /**
     * Resize an image to cover the given width/height completely, and crop off any
     * overhanging edges, keeping the focus point as close to the centre as possible.
     *
     * @param int $width
     * @param int $height
     * @return static
     */
    public function croppedResize($width, $height)
    {
        $img = $this->getAssetContainer();
        $cropData = $img->calculateCrop($width, $height);
        $cropAxis = $cropData['CropAxis'];
        $cropOffset = $cropData['CropOffset'];

        return $this->createCloneWithResource(
            function (InterventionImage $resource) use ($width, $height, $cropAxis, $cropOffset) {
                if ($cropAxis == 'x') {
                    //Generate image
                    return $resource
                        ->heighten($height)
                        ->crop($width, $height, $cropOffset, 0);
                } elseif ($cropAxis == 'y') {
                    //Generate image
                    return $resource
                        ->widen($width)
                        ->crop($width, $height, 0, $cropOffset);
                } else {
                    //Generate image without cropping
                    return $resource->resize($width, $height);
                }
                return $resource->crop($width, $height, $left, $top);
            }
        );
    }
}
