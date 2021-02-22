<?php

use SilverStripe\Assets\Image;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Versioned\Versioned;

class HydrateFocusPointTask extends BuildTask
{
    private static $segment = 'HydrateFocusPointTask';

    protected $title = 'Hydrate the focuspoint extension image size cache';

    protected $description = 'Run this task to cache all image sizes, and speed up image generation';

    /**
     * @param HTTPRequest $request
     * @throws ValidationException
     */
    public function run($request)
    {
        // Get all images missing a width / height
        $images = Versioned::get_by_stage(Image::class, Versioned::DRAFT)->filterAny([
            'FocusPointWidth'  => 0,
            'FocusPointHeight' => 0,
        ]);
        Debug::message('Found ' . $images->count() . ' images to hydrate');

        /** @var Image $image */
        foreach ($images as $image) {
            // Skip images that aren't on the filesystem
            if (!$image->exists()) {
                continue;
            }

            // Save, and maybe publish
            $image->write();
            if ($image->isPublished()) {
                $image->publishSingle();
            }
        }
    }
}
