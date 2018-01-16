(function($) {
	$.entwine('ss', function($){

		$('.focuspoint-field .grid').entwine({

			getCoordField: function(axis) {
				var fieldSelector = "input[name='Focus" + axis.toUpperCase() + "']";
				return this.closest('.focuspoint-fieldgroup').find(fieldSelector);
			},
			dispatchFieldChange: function(axis, value) {
				var fieldName = (axis === 'x') ? 'FocusX' : 'FocusY';
				if (window.hasOwnProperty('ss') && window.ss.hasOwnProperty('store')) {
					window.ss.store.dispatch({
						type: '@@redux-form/CHANGE',
						meta: {
						  form: 'AssetAdmin.EditForm.fileEditForm',
						  field: fieldName,
						},
						payload: value
					});
				}
			},
			updateGrid: function() {
				var grid = $(this);
				// Note: this behaviour is replicated in FocusPointImageExtension.php

				// Get coordinates from text fields
				var focusX = grid.getCoordField('x').val();
				var focusY = grid.getCoordField('y').val();

				// Calculate background positions
				var backgroundWH = 605; // Width and height of grid background image
				var bgOffset = Math.floor(-backgroundWH/2);
				var fieldW = grid.width();
				var fieldH = grid.height();
				var leftBG = bgOffset+((focusX/2 +.5)*fieldW);
				var topBG = bgOffset+((-focusY/2 +.5)*fieldH);

				// Line up crosshairs with click position
				grid.css('background-position',leftBG+'px '+topBG+'px');
			},
			onclick: function(e) {
				var grid = $(this);
				var fieldW = grid.width();
				var fieldH = grid.height();

				// Calculate FocusPoint coordinates
				var offsetX = e.pageX - grid.offset().left;
				var offsetY = e.pageY - grid.offset().top;
				var focusX = (offsetX/fieldW - .5)*2;
				var focusY = (offsetY/fieldH - .5)*-2;

				// Pass coordinates to form fields
				this.getCoordField('x').val(focusX);
				this.getCoordField('y').val(focusY);

				// Update focus point grid
				this.updateGrid();

				// Updating the inputs isn't enough for React-based asset admin
				this.dispatchFieldChange('x', focusX);
				this.dispatchFieldChange('y', focusY);
			}
		});
	});
}(jQuery));
