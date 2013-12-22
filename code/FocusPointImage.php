<?php

class FocusPointImage extends DataExtension {

	private static $db = array(
		'FocusX' => 'Double', // Decimal number between -1 & 1, where -1 is far left, 0 is center, 1 is far right.
		'FocusY' => 'Double' // Decimal number between -1 & 1, where -1 is bottom, 0 is center, 1 is top.
	);

	private static $defaults = array(
		//Preserve default behaviour of cropping from center
		'FocusX' => '0',
		'FocusY' => '0'
	);
	
	public function updateCMSFields(FieldList $fields) {
		//Add FocusPoint field for selecting focus
		$f = new FocusPointField(
			$name = "FocusXY",
			$title = "Focus point",
			$value = $this->owner->FocusX.','.$this->owner->FocusY,
			$imageID = $this->owner->ID
		);
		//$f->setValue(FocusPointField::sourceCoordsToFieldValue($this->owner->FocusX,$this->owner->FocusY));
		$fields->addFieldToTab("Root.Main", $f);
	}
	
	public function onBeforeWrite() {
		//Update individual focus points
		if ($this->owner->FocusXY){
			$coords = FocusPointField::fieldValueToSourceCoords($this->owner->FocusXY);
			$this->owner->FocusX = $coords[0];
			$this->owner->FocusY = $coords[1];
			//Flush images if focus point has changed
			if ($this->owner->isChanged('FocusX') || $this->owner->isChanged('FocusY')) $this->owner->deleteFormattedImages();
		}
		parent::onBeforeWrite();
	}
	
	/**
	 * Generate a broad description of focus point i.e. 'focus-bottom-left' for use in CSS.
	 * Use in templates with $BasicFocusArea
	 * 
	 * @return string
	 */
	public function BasicFocusArea() {
		//Defaults
		$horzFocus = "center";
		$vertFocus = "center";
		
		//Calculate based on XY coords
		if ($this->owner->FocusX > .333) {
			$horzFocus = "right";
		}
		if ($this->owner->FocusX < -.333) {
			$horzFocus = "left";
		}
		if ($this->owner->FocusY > .333) {
			$vertFocus = "top";
		}
		if ($this->owner->FocusY < -.333) {
			$vertFocus = "bottom";
		}
		
		//Combine in to CSS class
		return 'focus-'.$horzFocus.'-'.$vertFocus;
	}
	
	/**
	 * Generate a resized copy of this image with the given width & height, cropping to maintain aspect ratio and focus point.
	 * Use in templates with $CroppedFocusedImage
	 * 
	 * @param integer $width Width to crop to
	 * @param integer $height Height to crop to
	 * @return Image
	 */
	public function CroppedFocusedImage($width,$height) {
		return $this->owner->isSize($width, $height)
			? $this->owner
			: $this->owner->getFormattedImage('CroppedFocusedImage', $width, $height);
	}

	/**
	 * Generate a resized copy of this image with the given width & height, cropping to maintain aspect ratio and focus point.
	 * Use in templates with $CroppedFocusedImage
	 * 
	 * @param Image_Backend $backend
	 * @param integer $width Width to crop to
	 * @param integer $height Height to crop to
	 * @return Image_Backend
	 */
	public function generateCroppedFocusedImage(Image_Backend $backend, $width, $height){
		
		$width = round($width);
		$height = round($height);
		$top = 0;
		$left = 0;
		$originalWidth = $this->owner->width;
		$originalHeight = $this->owner->height;
		
		if ($this->owner->width > 0 && $this->owner->height > 0 ){//Can't divide by zero
		
			//Which is over by more?
			$widthRatio = $originalWidth/$width;
			$heightRatio = $originalHeight/$height;
			
			//Calculate offset required
			
			if ($widthRatio > $heightRatio) {
			
				//Left and/or right of image will be lost
				
				//target center in px
				$croppedCenterX = floor($width/2);
				
				//X axis focus point of scaled image in px
				$focusFactorX = ($this->owner->FocusX + 1)/2; //i.e .333 = one third along
				$scaledImageWidth = floor($originalWidth/$heightRatio);
				$focusX = floor($focusFactorX*$scaledImageWidth);
				
				//Calculate difference beetween focus point and center
				$focusOffsetX = $focusX - $croppedCenterX;
				
				//Reduce offset if necessary so image remains filled
				$xRemainder = $scaledImageWidth - $focusX;
				$croppedXRemainder = $width - $croppedCenterX;
				if ($xRemainder < $croppedXRemainder) $focusOffsetX-= $croppedXRemainder - $xRemainder;
				if ($focusOffsetX < 0) $focusOffsetX =0;
				
				//Set horizontal crop start point
				$left =  $focusOffsetX;
				
				//Generate image
				return $backend->resizeByHeight($height)->crop($top, $left, $width, $height);
				
			} else if ($widthRatio < $heightRatio) {
			
				//Top and/or bottom of image will be lost
			
				//Container center in px
				$croppedCenterY = floor($height/2);
				
				//Focus point of resize image in px
				$focusFactorY = ($this->owner->FocusY + 1)/2; // zero is bottom of image, 1 is top
				$scaledImageHeight = floor($originalHeight/$widthRatio);
				$focusY = $scaledImageHeight - floor($focusFactorY*$scaledImageHeight);
				
				//Calculate difference beetween focus point and center
				$focusOffsetY = $focusY - $croppedCenterY;
				
				//Reduce offset if necessary so image remains filled
				$yRemainder = $scaledImageHeight - $focusY;
				$croppedYRemainder = $height - $croppedCenterY;
				if ($yRemainder < $croppedYRemainder) $focusOffsetY-= $croppedYRemainder - $yRemainder;
				if ($focusOffsetY < 0) $focusOffsetY =0;
				
				//Set vertical crop start point
				$top =  $focusOffsetY;
				
				//Generate image
				return $backend->resizeByWidth($width)->crop($top, $left, $width, $height);
				
			} else {
			
				//Generate image without cropping
				return $backend->resize($width,$height);
			}
		
		}
	}
}