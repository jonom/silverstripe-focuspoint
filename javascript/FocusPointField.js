(function($) {
	$.entwine('ss', function($){
	
		$('.focus-point-field .grid').entwine({
			
			getCoordField: function() {
				return this.next();
			},
			updateGrid: function() {
				
				//Get coordinates from text field
				var coordField = this.getCoordField();
				var coords = coordField.val();
				var coords = coords.split(',');
				var focusX = coords[0];
				var focusY = coords[1];
				
				//Calculate background positions
				var backgroundWH = 605; //Width and height of grid background image
				var bgOffset = Math.floor(-backgroundWH/2);
				var fieldW = $(this).width();
				var fieldH = $(this).height();
				var leftBG = bgOffset+((focusX/2 +.5)*fieldW);
				var topBG = bgOffset+((-focusY/2 +.5)*fieldH);
				
				//Line up crosshairs with click position
				$(this).css('background-position',leftBG+'px '+topBG+'px');
			},
			onclick: function(e) {
				var fieldW = $(this).width();
				var fieldH = $(this).height();
				
				//Calculate FocusPoint coordinates
				var offsetX = e.pageX - $(this).offset().left;
				var offsetY = e.pageY - $(this).offset().top;
				var focusX = (offsetX/fieldW - .5)*2;
				var focusY = (offsetY/fieldH - .5)*-2;
				//console.log('FocusX: '+focusX+' FocusY: '+focusY);
				
				//Pass coordinates to form field
				var coordField = this.getCoordField();
				coordField.val(focusX + ',' + focusY);
				
				//Update form field
				this.updateGrid();
				
			},
			onadd: function() {
				//Position focus grid on form field
				var grid = this;
				grid.updateGrid();
				//May not have worked - try again after image loads
				$(this).prev('img').load(function(){
					grid.updateGrid();
				});
			}
			
		});
	});
}(jQuery));
