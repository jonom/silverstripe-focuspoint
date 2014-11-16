<?php

class FocusPointCacheBustExtension extends DataExtension {

	/*
	This is intended to be added to Image as an extension.  When an image is refocussed  checks are made
	against a configurable list of classes mapped to the image ID field.  If the image ID field value matches
	the  ID of the image being refocussed, the DataObject's LastEdited field is updated.
	*/
	public function onBeforeRefocusedImageWrite() {
		$config = Config::inst();
		$classes = $config->get('RefocusImageCacheBust', 'classes');
		$stages = $config->get('RefocusImageCacheBust', 'stages');

		foreach ($classes as $clazz => $idfield) {
			$instanceofclass = Injector::inst()->create($clazz);
			$objectsWithImage = $instanceofclass::get()->filter($idfield, $this->owner->ID);
			foreach ($objectsWithImage as $objectWithImage) {
				foreach ($stages as $stage) {
					$suffix = '_'.$stage;
					$suffix = str_replace('_Stage', '', $suffix);
					$sql = "UPDATE `SiteTree{$suffix}` SET LastEdited=NOW() where ID=".$objectWithImage->ID;
					DB::query($sql);
				}
			}
		}
	}
}