<?php

class FocusPointField extends OptionsetField {

	public function __construct($name, $title=null, $imageID=null,  $value='', $form=null) {
	
		$this->setImage($imageID);

		parent::__construct($name, ($title===null) ? $name : $title, $value, $form);
	}

	public function getSource() {
		
		//Generates 12x12 grid
		//@todo Allow selection to the pixel level using javascript + hidden text field insead of OptionsetField / Grid
		//@todo Make this field record the actual decimal coordinates used in cropping (currently requires translating)
	
		$source = array();
		
		$cols = 6;//These values will be doubled
		$rows = 6;
		
		for ($yR = $rows; $yR >= -$rows; $yR--) {
			for ($xC = -$cols; $xC <= $cols; $xC++) {
				
				//Create decimal value
				$x = $xC/$cols;
				$y = $yR/$rows;
				
				//Add to source array with translated field value
				$source[self::sourceCoordsToFieldValue($x,$y)]=$x.','.$y;	

			}	
		}
		
		return $source;
	}

	public function setImage($imageID) {
		if ($imageID) {
			$this->ImageID = $imageID;
		}
	}

	public function getImage() {
		if ($this->ImageID) {
			return Image::get()->byID($this->ImageID);
		}
	}

	public static function sourceCoordsToFieldValue($x,$y) {
		
		//Get rid of trailing zeroes
		$x = (float)$x;
		$y = (float)$y;
		
		//CMS strips out special chars when creating field titles so we translate them
		$xT = str_replace('-','minus',$x);
		$xT = str_replace('.','dot',$xT);
		$yT = str_replace('-','minus',$y);
		$yT = str_replace('.','dot',$yT);
		
		return 'coords' . $xT . 'by' . $yT;
		
	}

	public static function fieldValueToSourceCoords($fieldVal) {
		
		//Undo special char replacement
		$fieldVal = str_replace('coords','',$fieldVal);
		$fieldVal = str_replace('by',',',$fieldVal);
		$fieldVal = str_replace('minus','-',$fieldVal);
		$fieldVal = str_replace('dot','.',$fieldVal);
		
		return explode(',',$fieldVal);
		
	}

/*
  public function dataValue() {
		return $this->value;
	}
*/

}