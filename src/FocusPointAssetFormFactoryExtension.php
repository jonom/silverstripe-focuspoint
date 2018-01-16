<?php

namespace JonoM\FocusPoint;

use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Extension;

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
        if ($image) {
            $fields->insertAfter(
                'Title',
                FocusPointField::create($image)
            );
        }
    }
}
