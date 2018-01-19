#  Ch-ch-ch-ch-Changes

> 🎶 Turn and face the strange 🎶

## 3.0

* Changed from two individual Fields on `Image` to a composite DB-field.
* Injector support was removed as it was buggy. You'll have to use the FocusPoint method names for now instead of SilverStripe's built in cropping methods. :(
* `flush_on_change` config option was removed as their is no longer a public API for removing resampled images.

#### Migration

Field values will automatically migrate from 2.x to 3.x. If you need to migrate back from 3.x to 2.x, follow these steps:

 - revert the module to the 2.x version
 - do `dev/build`
 - Run the `FocusPointMigrationTask` with "direction=down". Eg. `sake dev/tasks/FocusPointMigrationTask "direction=down"`

## 2.0

* `FocusPointField` was refactored for 2.x. If you're using the class in your own code you will need to update it.
