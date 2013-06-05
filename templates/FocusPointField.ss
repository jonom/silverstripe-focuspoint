<style type="text/css">
	
	.focus-point-field {
	}
	.focus-point-field ul {
		border-right: 1px solid #fff;
		border-bottom: 1px solid #fff;
		background: #eee none no-repeat center center;
	}
	
	.focus-point-field li {
		float: left;
		width: 7.6923%;
		height: 7.6923%;
		overflow: visible;
		text-align: center;
		position: relative;
		left: 0;
		top: 0;
	}
	
	.focus-point-field li label {
		position: absolute;
		left: 0;
		top: 0;
		right: -1px;
		bottom: -1px;
		text-indent: -999px;
		overflow: hidden;
		border-top: 1px solid #fff;
		border-left: 1px solid #fff;
	}
	
	.focus-point-field input {
		opacity: 0;
	}
	
	.focus-point-field input+label {
		opacity: .25;
	}
	
	.focus-point-field input:checked+label {
		background: rgb(82, 168, 236);
		background: rgba(82, 168, 236, 0.6);
		border: 1px solid #fff;
		opacity: 1;
  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(82, 168, 236, 0.6);
     -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(82, 168, 236, 0.6);
          box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(82, 168, 236, 0.6);
	}


</style>
<div class="focus-point-field">
<ul id="$ID" class="$extraClass"
<% with Image %>
	<% with SetWidth(337) %>
		style="background-image:url({$link}); 
	<% end_with %>
	<% with SetWidth(365) %>
		width:{$Width}px; height: {$Height}px;"
	<% end_with %>
<% end_with %>
>
	<% loop Options %>
		<li class="$Class col{$Modulus(13)}">
			<input id="$ID" class="radio" name="$Name" type="radio" value="$Value"<% if isChecked %> checked<% end_if %><% if isDisabled %> disabled<% end_if %> />
			<label for="$ID"><span>$Title</span></label>
		</li>
	<% end_loop %>
</ul>
</div>