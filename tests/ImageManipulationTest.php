<?php

namespace JonoM\FocusPoint\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\InterventionBackend;
use SilverStripe\Assets\Tests\Storage\AssetStoreTest\TestAssetStore;
use SilverStripe\Dev\SapphireTest;


class ImageManipulationTest extends SapphireTest
{
    protected static $fixture_file = 'ImageManipulationTest.yml';

    public function setUp()
    {
        parent::setUp();

        // Set backend root to /images
        TestAssetStore::activate('images');

        // Copy test images for each of the fixture references
        /** @var File $image */
        $files = File::get()->exclude('ClassName', Folder::class);
        foreach ($files as $image) {
            $sourcePath = __DIR__ . '/images/' . $image->Name;
            $image->setFromLocalFile($sourcePath, $image->Filename);
        }

        // Set default config
        InterventionBackend::config()->set('error_cache_ttl', [
            InterventionBackend::FAILED_INVALID => 0,
            InterventionBackend::FAILED_MISSING => '5,10',
            InterventionBackend::FAILED_UNKNOWN => 300,
        ]);
    }

    public function tearDown()
    {
        TestAssetStore::reset();
        parent::tearDown();
    }

    /**
     * Get some image objects with various focus points.
     */
    private function Images()
    {
        $pngLeftTop = $this->objFromFixture(Image::class, 'pngLeftTop');
        $pngLeftTop->VerticalSliceTopLeftColor = '#ff0000';
        $pngLeftTop->VerticalSliceBottomRightColor = '#00ff00';
        $pngLeftTop->HorizontalSliceTopLeftColor = '#ff0000';
        $pngLeftTop->HorizontalSliceBottomRightColor = '#ffff00';

        $pngRightTop = $this->objFromFixture(Image::class, 'pngRightTop');
        $pngRightTop->VerticalSliceTopLeftColor = '#ffff00';
        $pngRightTop->VerticalSliceBottomRightColor = '#0000ff';
        $pngRightTop->HorizontalSliceTopLeftColor = '#ff0000';
        $pngRightTop->HorizontalSliceBottomRightColor = '#ffff00';

        $pngRightBottom = $this->objFromFixture(Image::class, 'pngRightBottom');
        $pngRightBottom->VerticalSliceTopLeftColor = '#ffff00';
        $pngRightBottom->VerticalSliceBottomRightColor = '#0000ff';
        $pngRightBottom->HorizontalSliceTopLeftColor = '#00ff00';
        $pngRightBottom->HorizontalSliceBottomRightColor = '#0000ff';

        $pngLeftBottom = $this->objFromFixture(Image::class, 'pngLeftBottom');
        $pngLeftBottom->VerticalSliceTopLeftColor = '#ff0000';
        $pngLeftBottom->VerticalSliceBottomRightColor = '#00ff00';
        $pngLeftBottom->HorizontalSliceTopLeftColor = '#00ff00';
        $pngLeftBottom->HorizontalSliceBottomRightColor = '#0000ff';

        return array($pngLeftTop, $pngRightTop, $pngRightBottom, $pngLeftBottom);
    }

    public function testFocusFill()
    {
        $images = $this->Images();

        // Test focus crop
        foreach ($images as $img) {
            // Crop a vertical slice
            $croppedVert = $img->FocusFill(1, 50);
            $this->assertTrue($croppedVert->isSize(1, 50));
            $im = $croppedVert->getImageBackend()->getImageResource();
            $leftTopColor = $im->pickColor(0, 0, 'hex');
            $bottomRightColor =  $im->pickColor(0, 49, 'hex');
            $this->assertEquals($img->VerticalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->VerticalSliceBottomRightColor, $bottomRightColor);

            // Crop a horizontal slice
            $croppedHorz = $img->FocusFill(50, 1);
            $this->assertTrue($croppedHorz->isSize(50, 1));
            $im = $croppedHorz->getImageBackend()->getImageResource();
            $leftTopColor = $im->pickColor(0, 0, 'hex');
            $bottomRightColor = $im->pickColor(49, 0, 'hex');
            $this->assertEquals($img->HorizontalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->HorizontalSliceBottomRightColor, $bottomRightColor);

        }
    }

    public function testFocusFillMax()
    {
        $images = $this->Images();

        foreach ($images as $img) {
            // Downscale

            // Crop a vertical slice
            $croppedVert = $img->FocusFillMax(1, 50);
            $this->assertTrue($croppedVert->isSize(1, 50));
            $im = $croppedVert->getImageBackend()->getImageResource();
            $leftTopColor = $im->pickColor(0, 0, 'hex');
            $bottomRightColor = $im->pickColor(0, 49, 'hex');
            $this->assertEquals($img->VerticalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->VerticalSliceBottomRightColor, $bottomRightColor);

            // Crop a horizontal slice
            $croppedHorz = $img->FocusFillMax(50, 1);
            $this->assertTrue($croppedHorz->isSize(50, 1));
            $im = $croppedHorz->getImageBackend()->getImageResource();
            $leftTopColor = $im->pickColor(0, 0, 'hex');
            $bottomRightColor = $im->pickColor(49, 0, 'hex');
            $this->assertEquals($img->HorizontalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->HorizontalSliceBottomRightColor, $bottomRightColor);

            // Upscale

            // Crop a vertical slice
            $croppedVert = $img->FocusFillMax(1, 200);
            $this->assertTrue($croppedVert->isSize(1, 100));
            $im = $croppedVert->getImageBackend()->getImageResource();
            $leftTopColor = $im->pickColor(0, 0, 'hex');
            $bottomRightColor = $im->pickColor(0, 99, 'hex');
            $this->assertEquals($img->VerticalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->VerticalSliceBottomRightColor, $bottomRightColor);

            // Crop a horizontal slice
            $croppedHorz = $img->FocusFillMax(200, 1);
            $this->assertTrue($croppedHorz->isSize(100, 1));
            $im = $croppedHorz->getImageBackend()->getImageResource();
            $leftTopColor = $im->pickColor(0, 0, 'hex');
            $bottomRightColor = $im->pickColor(99, 0, 'hex');
            $this->assertEquals($img->HorizontalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->HorizontalSliceBottomRightColor, $bottomRightColor);
        }
    }

    public function testPercentages()
    {
        $pngLeftTop = $this->objFromFixture(Image::class, 'pngLeftTop');
        $this->assertEquals(38, $pngLeftTop->PercentageX());
        $this->assertEquals(38, $pngLeftTop->PercentageY());

        $pngLeftTop->FocusPoint->setX(0)->setY(0.5);
        $this->assertEquals(50, $pngLeftTop->PercentageX());
        $this->assertEquals(75, $pngLeftTop->PercentageY());

        $pngLeftTop->FocusPoint->setX(1)->setY(-1);
        $this->assertEquals(100, $pngLeftTop->PercentageX());
        $this->assertEquals(0, $pngLeftTop->PercentageY());
    }

    public function testImageChaining()
    {
        // Grab an image and set its focus point to bottom left
        $pngLeftBottom = $this->objFromFixture(Image::class, 'pngLeftBottom');
        $pngLeftBottom->FocusPoint->setY(0.5)->setX(-0.5);

        $this->assertEquals(-0.5, $pngLeftBottom->FocusPoint->getX());
        $this->assertEquals(0.5, $pngLeftBottom->FocusPoint->getY());

        // crop to half the width, and full height
        $cropped = $pngLeftBottom->FocusFillMax(50,100);
        $this->assertEquals(0, $cropped->FocusPoint->getX());
        $this->assertEquals(.5, $cropped->FocusPoint->getY());

        // crop the cropped image again to .75 of the height
        $cropped = $cropped->FocusFillMax(50, 75);
        $this->assertEquals(0, $cropped->FocusPoint->getX());
        $this->assertEquals(1/3, $cropped->FocusPoint->getY());

        // crop the cropped image again to square
        $cropped = $cropped->FocusFillMax(50, 50);
        $this->assertEquals(0, $cropped->FocusPoint->getX());
        $this->assertEquals(0, $cropped->FocusPoint->getY());
    }
}
