<?php

namespace JonoM\FocusPoint\Extensions;

use JonoM\FocusPoint\Forms\FocusPointField;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

/**
 * FocusPoint Asset Form Factory extension.
 * Extends the CMS detail form to allow focus point selection.
 *
 * @extends Extension
 */
class FocusPointAssetFormFactoryExtension extends Extension
{

    /**
     * Add FocusPoint field for selecting focus.
     */
    public function updateFormFields(FieldList $fields, $controller, $formName, $context)
    {
        $image = isset($context['Record']) ? $context['Record'] : null;
        if ($image && $image->appCategory() === 'image') {
            $fields->insertAfter(
                'Title',
                FocusPointField::create('FocusPoint', $image->fieldLabel('FocusPoint'), $image)
                    ->setReadonly($formName === 'fileSelectForm')
            );
        }
    }
}
