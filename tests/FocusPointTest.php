<?php

namespace JonoM\FocusPoint;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\Folder;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Filesystem;
use SilverStripe\Dev\SapphireTest;

class FocusPointTest extends SapphireTest
{
    protected static $fixture_file = 'FocusPointTest.yml';

    /**
     * Set up images used for tests (Copied from framework/tests/mode/ImageTest.php).
     */
    public function setUp()
    {
        parent::setUp();

        $this->origBackend = Image::get_backend();

        if ($this->skipTest) {
            return;
        }

        if (!file_exists(ASSETS_PATH)) {
            mkdir(ASSETS_PATH);
        }

        // Create a test folders for each of the fixture references
        $folderIDs = $this->allFixtureIDs(Folder::class);

        foreach ($folderIDs as $folderID) {
            $folder = DataObject::get_by_id(Folder::class, $folderID);

            if (!file_exists(BASE_PATH."/$folder->Filename")) {
                mkdir(BASE_PATH."/$folder->Filename");
            }
        }

        // Copy test images for each of the fixture references
        $imageIDs = $this->allFixtureIDs(Image::class);
        foreach ($imageIDs as $imageID) {
            $image = DataObject::get_by_id(Image::class, $imageID);
            $filePath = BASE_PATH."/$image->Filename";
            $sourcePath = str_replace('assets/ImageTest/', FOCUSPOINT_DIR.'/tests/', $filePath);
            if (!file_exists($filePath)) {
                if (!copy($sourcePath, $filePath)) {
                    user_error('Failed to copy test images', E_USER_ERROR);
                }
            }
        }
    }

    /**
     * Remove images used for tests (Copied from framework/tests/mode/ImageTest.php).
     */
    public function tearDown()
    {
        if ($this->origBackend) {
            Image::set_backend($this->origBackend);
        }

        // Remove the test files that we've created
        $fileIDs = $this->allFixtureIDs(Image::class);
        foreach ($fileIDs as $fileID) {
            $file = DataObject::get_by_id(Image::class, $fileID);
            if ($file && file_exists(BASE_PATH."/$file->Filename")) {
                unlink(BASE_PATH."/$file->Filename");
            }
        }

        // Remove the test folders that we've created
        $folderIDs = $this->allFixtureIDs(Folder::class);
        foreach ($folderIDs as $folderID) {
            $folder = DataObject::get_by_id(Folder::class, $folderID);
            if ($folder && file_exists(BASE_PATH."/$folder->Filename")) {
                Filesystem::removeFolder(BASE_PATH."/$folder->Filename");
            }
            if ($folder && file_exists(BASE_PATH.'/'.$folder->Filename.'_resampled')) {
                Filesystem::removeFolder(BASE_PATH.'/'.$folder->Filename.'_resampled');
            }
        }

        parent::tearDown();
    }

    /**
     * Get some image objects with various focus points.
     */
    public function Images()
    {
        $pngLeftTop = $this->objFromFixture(Image::class, 'pngLeftTop');
        $pngLeftTop->VerticalSliceTopLeftColor = 'ff0000';
        $pngLeftTop->VerticalSliceBottomRightColor = '00ff00';
        $pngLeftTop->HorizontalSliceTopLeftColor = 'ff0000';
        $pngLeftTop->HorizontalSliceBottomRightColor = 'ffff00';

        $pngRightTop = $this->objFromFixture(Image::class, 'pngRightTop');
        $pngRightTop->VerticalSliceTopLeftColor = 'ffff00';
        $pngRightTop->VerticalSliceBottomRightColor = '0000ff';
        $pngRightTop->HorizontalSliceTopLeftColor = 'ff0000';
        $pngRightTop->HorizontalSliceBottomRightColor = 'ffff00';

        $pngRightBottom = $this->objFromFixture(Image::class, 'pngRightBottom');
        $pngRightBottom->VerticalSliceTopLeftColor = 'ffff00';
        $pngRightBottom->VerticalSliceBottomRightColor = '0000ff';
        $pngRightBottom->HorizontalSliceTopLeftColor = '00ff00';
        $pngRightBottom->HorizontalSliceBottomRightColor = '0000ff';

        $pngLeftBottom = $this->objFromFixture(Image::class, 'pngLeftBottom');
        $pngLeftBottom->VerticalSliceTopLeftColor = 'ff0000';
        $pngLeftBottom->VerticalSliceBottomRightColor = '00ff00';
        $pngLeftBottom->HorizontalSliceTopLeftColor = '00ff00';
        $pngLeftBottom->HorizontalSliceBottomRightColor = '0000ff';

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
            $im = imagecreatefrompng($croppedVert->getFullPath());
            $leftTopColor = str_pad(dechex(imagecolorat($im, 0, 0)), 6, '0', STR_PAD_LEFT);
            $bottomRightColor = str_pad(dechex(imagecolorat($im, 0, 49)), 6, '0', STR_PAD_LEFT);
            $this->assertEquals($img->VerticalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->VerticalSliceBottomRightColor, $bottomRightColor);

            // Crop a horizontal slice
            $croppedHorz = $img->FocusFill(50, 1);
            $this->assertTrue($croppedHorz->isSize(50, 1));
            $im = imagecreatefrompng($croppedHorz->getFullPath());
            $leftTopColor = str_pad(dechex(imagecolorat($im, 0, 0)), 6, '0', STR_PAD_LEFT);
            $bottomRightColor = str_pad(dechex(imagecolorat($im, 49, 0)), 6, '0', STR_PAD_LEFT);
            $this->assertEquals($img->HorizontalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->HorizontalSliceBottomRightColor, $bottomRightColor);

            // Clear cache since we're changing the focus point
            $img->deleteFormattedImages();
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
            $im = imagecreatefrompng($croppedVert->getFullPath());
            $leftTopColor = str_pad(dechex(imagecolorat($im, 0, 0)), 6, '0', STR_PAD_LEFT);
            $bottomRightColor = str_pad(dechex(imagecolorat($im, 0, 49)), 6, '0', STR_PAD_LEFT);
            $this->assertEquals($img->VerticalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->VerticalSliceBottomRightColor, $bottomRightColor);

            // Crop a horizontal slice
            $croppedHorz = $img->FocusFillMax(50, 1);
            $this->assertTrue($croppedHorz->isSize(50, 1));
            $im = imagecreatefrompng($croppedHorz->getFullPath());
            $leftTopColor = str_pad(dechex(imagecolorat($im, 0, 0)), 6, '0', STR_PAD_LEFT);
            $bottomRightColor = str_pad(dechex(imagecolorat($im, 49, 0)), 6, '0', STR_PAD_LEFT);
            $this->assertEquals($img->HorizontalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->HorizontalSliceBottomRightColor, $bottomRightColor);

            // Upscale

            // Crop a vertical slice
            $croppedVert = $img->FocusFillMax(1, 200);
            $this->assertTrue($croppedVert->isSize(1, 100));
            $im = imagecreatefrompng($croppedVert->getFullPath());
            $leftTopColor = str_pad(dechex(imagecolorat($im, 0, 0)), 6, '0', STR_PAD_LEFT);
            $bottomRightColor = str_pad(dechex(imagecolorat($im, 0, 99)), 6, '0', STR_PAD_LEFT);
            $this->assertEquals($img->VerticalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->VerticalSliceBottomRightColor, $bottomRightColor);

            // Crop a horizontal slice
            $croppedHorz = $img->FocusFillMax(200, 1);
            $this->assertTrue($croppedHorz->isSize(100, 1));
            $im = imagecreatefrompng($croppedHorz->getFullPath());
            $leftTopColor = str_pad(dechex(imagecolorat($im, 0, 0)), 6, '0', STR_PAD_LEFT);
            $bottomRightColor = str_pad(dechex(imagecolorat($im, 99, 0)), 6, '0', STR_PAD_LEFT);
            $this->assertEquals($img->HorizontalSliceTopLeftColor, $leftTopColor);
            $this->assertEquals($img->HorizontalSliceBottomRightColor, $bottomRightColor);

            // Clear cache since we're changing the focus point
            $img->deleteFormattedImages();
        }
    }
}
