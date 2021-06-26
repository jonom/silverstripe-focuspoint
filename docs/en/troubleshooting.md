## Troubleshooting

### Upgrading

See the [changelog](../../changelog.md) for upgrade notes

### Debug CMS field

Use this YML configuration to reveal fields for FocusX and FocusY in the CMS:

```yml
FocusPointField:
  debug: true
```

### Debug front-end image

Add `.DebugFocusPoint` to the end of an image object in your template to output html markup showing you the focus point of the image.

```html
$MyImage.ScaleWidth(200).DebugFocusPoint
```

### The FocusPoint field in the CMS appears broken

Did you run a dev/build after module installation?

### Focus point has been changed but image has not updated

As a cache-busting mechanism this module includes approximate focus point coordinates in generated filenames. This means that if the focus point is updating correctly in the CMS but you're not seeing images change on your website, it's likely that you're viewing cached HTML output and need to invalidate that to see the updated image.
