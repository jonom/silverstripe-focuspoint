# Basic usage

## In the CMS

When you edit an image in the CMS there should be an extra 'Focus Point' field. Click on the subject of the image to set the focus area and save the image.

## In templates and PHP

Use just like existing cropping functions but swap out the names:

- `$Fill` --> `$FocusFill`
- `$FillMax` --> `$FocusFillMax`
- `$CropWidth` --> `$FocusCropWidth`
- `$CropHeight` --> `$FocusCropHeight`

Or use the existing names... see 'Method replacement' in [Advanced usage](advanced-usage.md).
