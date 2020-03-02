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
            $fpField = FocusPointField::create('FocusPoint', $image->fieldLabel('FocusPoint'), $image);

            $titleField = $fields->fieldByName('Editor.Details.Title');
            if ($titleField) {
                if ($titleField->isReadonly()) $fpField = $fpField->performReadonlyTransformation();
                $fields->insertAfter(
                    'Title',
                    $fpField
                );
            }

        }
    }
}
