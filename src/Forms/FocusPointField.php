<?php

namespace JonoM\FocusPoint\Forms;

use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextField;

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

    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;

    protected $schemaComponent = 'FocusPointField';

    protected $image = null;

    public function __construct($name, $title = null, Image $image = null)
    {
        // Create the fields
        $fields = [
            $x = TextField::create($name . 'X'),
            $y = TextField::create($name . 'Y')
        ];

        if ($image) {
            $this->image = $image;
            $x->setValue($image->getField($name)->getX());
            $y->setValue($image->getField($name)->getY());
        }

        $this->setName($name)->setValue('');
        parent::__construct($title, $fields);
    }

    public function getToolTip()
    {
        return _t(
            __CLASS__ . '.FieldToolTip',
            'Click on the subject of the image to ensure it is not lost during cropping'
        );
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['data'] += [
            'tooltip' => $this->getToolTip(),
            'showDebug' => Director::isDev() && $this->config()->get('debug')
        ];

        if ($this->image) {
            $w = intval($this->config()->get('max_width'));
            $h = intval($this->config()->get('max_height'));
            $previewImage = $this->image->FitMax($w * 2, $h * 2);

            if ($previewImage) {
                $state['data'] += [
                    'previewUrl' => $previewImage->URL,
                    'previewWidth' => $previewImage->getWidth(),
                    'previewHeight' => $previewImage->getHeight(),
                    'X' => $this->image->getField($this->getName())->getX(),
                    'Y' => $this->image->getField($this->getName())->getY()
                ];
            }
        }

        return $state;
    }
}
