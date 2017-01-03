## Troubleshooting

### Upgrading from 1.x

`FocusPointField` was refactored for 2.x. If you're using the class in your own code you will need to update it.

### Debug CMS field

Use this YML configuration to reveal fields for FocusX and FocusY in the CMS:

```yml
FocusPointField:
  debug: true
```

### Debug front-end image

Add `.DebugFocusPoint` to the end of an image object in your template to output html markup showing you the focuspoint of the image.

```html
$MyImage.ScaleWidth(200).DebugFocusPoint
```

### The FocusPoint field in the CMS appears broken

If the focus point field shows a non-interactive image and a text field with a comma in it, make sure the module folder is named 'focuspoint' and try visiting *yoursite.dev/?flush=1* again.

### Focus point has been changed but image has not updated

As a cache-busting mechanism this module includes approximate focus point coordinates in generated filenames. This means that if the focus point is updating correctly in the CMS but you're not seeing images change on your website, it's likely that you're viewing cached HTML output and need to invalidate that to see the updated image.

Other SilverStripe modules can also prevent images being regenerated when the focus point is changed. You can work around this by telling SilverStripe to [delete resampled versions of an image when its focus point is changed](#flush-generated-images-on-focus-point-change).
