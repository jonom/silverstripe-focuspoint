<?php

namespace JonoM\FocusPoint\Extensions;

use JonoM\FocusPoint\Dev\FocusPointMigrationTask;
use JonoM\FocusPoint\FieldType\DBFocusPoint;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\DBFile;

/**
 * FocusPoint Image extension.
 * Extends Image to allow automatic cropping from a selected focus point.
 *
 * @extends DataExtension
 * @property DBFocusPoint $FocusPoint
 * @property Image|DBFile|FocusPointImageExtension $owner
 */
class FocusPointImageExtension extends FocusPointExtension
{
    /**
     * Describes the focus point coordinates on the image.
     */
    private static $db = [
        'FocusPoint' => DBFocusPoint::class,
    ];

    public function requireDefaultRecords()
    {
        $autoMigrate = FocusPointMigrationTask::create();
        $autoMigrate->up();
    }

    public function onBeforeWrite()
    {
        // Ensure that we saved the cached image width / height whenever we change the file hash
        if (
            (
                $this->owner->isChanged('FileHash')
                || empty($this->FocusPoint->Width)
                || empty($this->FocusPoint->Height)
            ) && $this->owner->exists()
        ) {
            $this->FocusPoint->Width = $this->owner->getWidth();
            $this->FocusPoint->Height = $this->owner->getHeight();
        }
    }
}
