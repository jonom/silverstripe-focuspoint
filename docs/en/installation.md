# Installation

### Composer (best practice)

```
composer require jonom/focuspoint
```

See the [packagist listing](https://packagist.org/packages/jonom/focuspoint) and [installation instructions](https://docs.silverstripe.org/en/getting_started/composer/#adding-modules-to-your-project)

### Manually

I promise it's worth your time to learn how to use Composer. If painless updating isn't your thing though you can download and extract this project, rename the module folder 'focuspoint', place it in your project root and run a dev/build?flush=1.


### Using Focus Point with ImageMagick

If you are using the ImageMagick you must use [injector](https://docs.silverstripe.org/en/developer_guides/extending/injector/) to replace the ImagickBackend with the wrapper FPImagickBackend for compatibility using the below:
```yml
Injector:
  ImagickBackend:
    class: "FPImagickBackend"
```
