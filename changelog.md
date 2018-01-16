#  Ch-ch-ch-ch-Changes

> 🎶 Turn and face the strange 🎶

## 3.0

* Injector support was removed as it was buggy. You'll have to use the FocusPoint method names for now instead of SilverStripe's built in cropping methods. :(
* `flush_on_change` config option was removed as their is no longer a public API for removing resampled images.

## 2.0

* `FocusPointField` was refactored for 2.x. If you're using the class in your own code you will need to update it.