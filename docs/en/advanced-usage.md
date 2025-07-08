# Advanced usage

## Method chaining

Image method chaining e.g. `$Image.ScaleHeight(200).FocusCropWidth(200)` should work from Silverstripe 3.3 onwards and update the focus point accordingly. This is helpful if you want to make use of the focal point in templates e.g. for the Responsive Cropping example below.

A small catch is that you can't include regular (non FocusPoint) cropping methods in the chain, as these won't re-calculate the focus point after cropping. For example `$Image.Fill(200,200).FocusCropWidth(100)` won't work properly... Not that you'd ever want to do that!

## Front-end art direction

When images are cropped/framed on the front-end of a website, you can pass through FocusPoint data to ensure the important part of your image is preserved.

### jQuery FocusPoint

[jQuery FocusPoint ](https://github.com/jonom/jquery-focuspoint) allows you to fill a flexible container with your image, while always retaining the most important part. Check out a [demo here](http://jonom.github.io/jquery-focuspoint/demos/grid/lizard.html). Example v1 integration:

```html
<% with $SomeImage %>
	<div class="focuspoint"
		data-focus-x="$FocusPoint.LegacyX"
		data-focus-y="$FocusPoint.LegacyY"
		data-image-w="$Width"
		data-image-h="$Height">
		<img src="$Link" alt="" />
	</div>
<% end_with %>
```

For v2, just replace `LegacyX` and `LegacyY` with `OffsetX` and `OffsetY`.

### Background image with focus point preserved

Try something like this to get a full-screen background image that preserves your focus point as you resize the browser window.

```html
<body
	<% with $BGImage %>
		style="background-image: url($Link);
			background-position: {$FocusPoint.PercentageX}% {$FocusPoint.PercentageY}%;
			background-size: cover;"
	<% end_with %>
>
```

### CSS transitions - zoom from focus point

Ever made an image in a tile zoom in on roll over? You can make sure the zoom originates from the image's focus point like so:

```html
<img src="$Link" style="transform-origin: {$FocusPoint.PercentageX}% {$FocusPoint.PercentageY}%" />
```

## Make the CMS preview bigger or smaller

You can change the preview size like so:

```yml
FocusPointField:
  max_width: 500
  max_height: 300
```

## Partial cache busting

If you are caching page content that includes a FocusFill and you edit the image (i.e. by changing the focus point) but not the page, you may want to invalidate the page's cache as the updated FocusFill will have a different filename. Gordon Banderson has written a [robust extension](https://github.com/gordonbanderson/weboftalent-imageeditpartialcachebust) to help you achieve this.

## Fine-tuned cropping in individual contexts

Silverstripe FocusPoint provides an easy and automated way to get better results when forcing an image to be a different aspect ratio. I have some vague plans to offer more fine-grained control over individual crops in the future, but until then I recommend checking out Will Morgan's [Silverstripe CropperField](https://github.com/willmorgan/silverstripe-cropperfield) as an alternative.
