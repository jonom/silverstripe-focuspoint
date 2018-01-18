<?php

namespace JonoM\FocusPoint;

use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;

/**
 * FocusPointField class.
 * Facilitates the selection of a focus point on an image.
 *
 * @extends FieldGroup
 */
class FocusPointField extends CompositeField
{
    /**
     * Enable to view Focus X and Focus Y fields while in Dev mode.
     *
     * @var bool
     * @config
     */
    private static $debug = false;

    /**
     * Maximum width of preview image
     *
     * @var integer
     * @config
     */
    private static $max_width = 300;

    /**
     * Maximum height of preview image
     *
     * @var integer
     * @config
     */
    private static $max_height = 150;

    public function __construct($name, $title = null, Image $image = null)
    {
        // Create the fields
        $fields = [
            TextField::create('FocusX'),
            TextField::create('FocusY')
        ];

        if ($image) {
            $previewImage = $image->FitMax($this->config()->get('max_width'), $this->config()->get('max_height'));
            array_unshift($fields, LiteralField::create('FocusPointGrid', $previewImage->renderWith(FocusPointField::class)));
        }

        parent::__construct($fields);

        $this->setName($name);
        $this->setTitle($title);
        $this->addExtraClass('focuspoint-fieldgroup');
        if (Director::isDev() && $this->config()->get('debug')) {
            $this->addExtraClass('debug');
        }
    }
}
