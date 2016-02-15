<?php

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

/*
// This is required to ensure manipulated images get their methods overidden too,
// but it doesn't work because Image_cached isn't compatible with the injector.
// Not having this in place means chained method chaining doesn't work properly
// e.g. `$Image.ScaleHeight(200).CropWidth(200)` will not use $FocusCropWidth.
class FPImage_cached extends Image_cached {

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
*/
