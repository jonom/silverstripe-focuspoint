<?php

/**
 * Override default cropping methods with FocusPoint versions
 *
 * @extends Image
 */
class FPImage extends Image
{
    public function Fill($width, $height)
    {
        return $this->FocusFill($width, $height);
    }

    public function FillMax($width, $height)
    {
        return $this->FocusFillMax($width, $height);
    }

    public function CropWidth($width)
    {
        return $this->FocusCropWidth($width);
    }

    public function CropHeight($height)
    {
        return $this->FocusCropHeight($height);
    }
}

/**
 * Ensure manipulated images get their methods overidden too, for method chaining
 *
 * @extends Image_cached
 */
class FPImage_Cached extends Image_Cached {

    public function Fill($width, $height) {
        return $this->FocusFill($width, $height);
    }

    public function FillMax($width, $height) {
        return $this->FocusFillMax($width, $height);
    }

    public function CropWidth($width) {
        return $this->FocusCropWidth($width);
    }

    public function CropHeight($height) {
        return $this->FocusCropHeight($height);
    }
}
