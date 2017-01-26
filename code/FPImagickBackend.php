<?php
if(class_exists('Imagick')) {
    class FPImagickBackend extends ImagickBackend {
        /**
         * Crop's part of image.
         * @param top y position of left upper corner of crop rectangle
         * @param left x position of left upper corner of crop rectangle
         * @param width rectangle width
         * @param height rectangle height
         * @return ImagickBackend
         */
        public function crop($top, $left, $width, $height) {
            $new = clone $this;
            $new->cropImage($width, $height, $left, $top);

            return $new;
        }
    }
}
?>
