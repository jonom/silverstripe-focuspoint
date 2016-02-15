<?php

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

    public function __construct(Image $image)
    {
        // Load necessary scripts and styles
        Requirements::javascript(FRAMEWORK_DIR.'/thirdparty/jquery/jquery.js');
        Requirements::javascript(FRAMEWORK_DIR.'/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');
        Requirements::javascript(FOCUSPOINT_DIR.'/javascript/FocusPointField.js');
        Requirements::css(FOCUSPOINT_DIR.'/css/FocusPointField.css');

        // Create the fields
    $fields = array(
            LiteralField::create('FocusPointGrid', $image->renderWith('FocusPointField')),
            TextField::create('FocusX'),
            TextField::create('FocusY'),
    );
        $this->setName('FocusPoint');
        $this->setTitle('Focus Point');
        $this->addExtraClass('focuspoint-fieldgroup');
        if (Director::isDev() && $this->config()->get('debug')) {
            $this->addExtraClass('debug');
        }

        parent::__construct($fields);
    }
}
