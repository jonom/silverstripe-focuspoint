<%-- Example of how to set up SS template to produce output to work with jQuery FocusPoint --%>

<% with Image %>
	<div class="focuspoint $BasicFocusArea"
	data-focus-x="$FocusX"
	data-focus-y="$FocusY"
	data-image-w="$Width"
	data-image-h="$Height">
		<img src="img/lizard.jpg" alt="" />
	</div>
<% end_with %>