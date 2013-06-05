<%-- Example of how to set up SS template to produce output as seen in index.html --%>

<% with Image %>
	<div class="focuspoint $BasicFocusArea"
	data-focus-x="$FocusX"
	data-focus-y="$FocusY"
	data-image-w="$Width"
	data-image-h="$Height">
		<div class="focuspoint-inner">
			<img src="img/lizard.jpg" alt="" />
		</div>
	</div>
<% end_with %>