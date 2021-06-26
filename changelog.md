#  Ch-ch-ch-ch-Changes

> 🎶 Turn and face the strange 🎶

## 4.0

* Refactored to facilitate performance improvements (thanks @tractorcow and @kinglozzer)
* Minimum PHP requirement raised to 7.2

#### Upgrading

* If you're using `$PercentageX` and `$PercentageY` in your templates they'll need to be changed to `$FocusPoint.PercentageX` and `$FocusPoint.PercentageY`
* For optimal performance, run the *'Hydrate the focuspoint extension image size cache'* dev task after installing.

## 3.0

* Changed from two individual Fields on `Image` to a composite DB-field.
* Injector support was removed as it was buggy. You'll have to use the FocusPoint method names for now instead of SilverStripe's built in cropping methods. :(
* `flush_on_change` config option was removed as their is no longer a public API for removing resampled images.
* Implemented a focus-point react component
* Switched Y-Coordinates upside down (from 1 = top, -1 = bottom to -1 = top, 1 = bottom). Existing coordinates will automatically be converted by the migration task.

#### Migration

Field values will automatically migrate from 2.x to 3.x. If you need to migrate back from 3.x to 2.x, follow these steps:

 - revert the module to the 2.x version
 - do `dev/build`
 - Run the `FocusPointMigrationTask` with "direction=down". Eg. `sake dev/tasks/FocusPointMigrationTask "direction=down"`

## 2.0

* `FocusPointField` was refactored for 2.x. If you're using the class in your own code you will need to update it.
