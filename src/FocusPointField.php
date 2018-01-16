<?php

namespace JonoM\FocusPoint;

use SilverStripe\Assets\Image;
use SilverStripe\View\Requirements;
use JonoM\FocusPoint\FocusPointField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldGroup;

/**
 * FocusPointField class.
 * Facilitates the selection of a focus point on an image.
 *
 * @extends FieldGroup
 */
class FocusPointField extends FieldGroup
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

    public function __construct(Image $image)
    {
        // Create the fields
        $previewImage = $image->FitMax($this->config()->get('max_width'), $this->config()->get('max_height'));
        $fields = array(
            LiteralField::create('FocusPointGrid', $previewImage->renderWith(FocusPointField::class)),
            TextField::create('FocusX'),
            TextField::create('FocusY'),
        );

        parent::__construct($fields);

        $this->setName('FocusPoint');
        $this->setTitle(_t('JonoM\\FocusPoint\\FocusPointField.FOCUSPOINT', 'Focus Point'));
        $this->addExtraClass('focuspoint-fieldgroup');
        if (Director::isDev() && $this->config()->get('debug')) {
            $this->addExtraClass('debug');
        }
    }
}
