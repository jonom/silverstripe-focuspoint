<?php

namespace JonoM\FocusPoint\Tests\Extensions;


use JonoM\FocusPoint\Extensions\FocusPointAssetFormFactoryExtension;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;

class FocusPointAssetFormFactoryExtensionTest extends SapphireTest
{

    protected static $fixture_file = '../ImageManipulationTest.yml';

    public function testUpdateFormFieldsOnImageEditForm()
    {
        $ext = new FocusPointAssetFormFactoryExtension();

        $fields = FieldList::create(
            TabSet::create(
                'Editor',
                Tab::create(
                    'Details',
                    TextField::create('Title')
                )
            )
        );
        $controller = new Controller();
        $formName = 'fileEditForm';
        $context = [
            'Record' => $this->objFromFixture(Image::class, 'pngLeftTop')
        ];


        $ext->updateFormFields($fields, $controller, $formName, $context);

        $focusField = $fields->fieldByName('Editor.Details.FocusPoint');
        $this->assertNotEmpty($focusField, 'Focus field has been added to image edit form.');
    }

    public function testUpdateFormFieldsOnPlacementForm()
    {
        $ext = new FocusPointAssetFormFactoryExtension();

        $fields = FieldList::create(
            TextField::create('Title')
        );
        $controller = new Controller();
        $formName = 'fileEditForm';
        $context = [
            'Record' => $this->objFromFixture(Image::class, 'pngLeftTop')
        ];


        $ext->updateFormFields($fields, $controller, $formName, $context);

        $focusField = $fields->fieldByName('Editor.Details.FocusPoint');
        $this->assertEmpty($focusField, 'Focus field has NOT been added to the form.');
    }

    public function testUpdateFormFieldsOnNonImageForm()
    {
        $ext = new FocusPointAssetFormFactoryExtension();

        $fields = FieldList::create(
            TabSet::create(
                'Editor',
                Tab::create(
                    'Details',
                    TextField::create('Title')
                )
            )
        );
        $controller = new Controller();
        $formName = 'fileEditForm';
        $context = [
            'Record' => $this->objFromFixture(Folder::class, 'folder1')
        ];

        $ext->updateFormFields($fields, $controller, $formName, $context);

        $focusField = $fields->fieldByName('Editor.Details.FocusPoint');
        $this->assertEmpty($focusField, 'Focus field has NOT been added to the form.');
    }
}
