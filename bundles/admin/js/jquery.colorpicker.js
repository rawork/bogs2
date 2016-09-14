/**
 * Really Simple Color Picker in jQuery
 * 
 * Copyright (c) 2008 Lakshan Perera (www.laktek.com)
 * Licensed under the MIT (MIT-LICENSE.txt)  licenses.
 * 
 */

(function($){
  $.fn.colorPicker = function(){    
    if(this.length > 0) buildSelector();
    return this.each(function(i) { buildPicker(this)}); 
  };
  
  var selectorOwner;
  var selectorShowing = false;
  
  buildPicker = function(element){
    //build color picker
    control = $("<div class='color_picker'></div>")
    control.css('background-color', $(element).val());
    
    //bind click event to color picker
    control.bind("click", toggleSelector);
    
    //add the color picker section
    $(element).before(control);
    
    //hide the input box
    $(element).hide();
  };
  
  buildSelector = function(){
     selector = $("<div id='_color_selector'></div>");
     
     //add color pallete
     $.each($.fn.colorPicker.defaultColors, function(i){
      swatch = $("<div class='_color_swatch'></div>");
      swatch.css("background-color", "#" + this);
	  swatch.bind("click", function(e){ changeColor($(this).css("background-color")) });
      swatch.bind("mouseover", function(e){ 
        $(this).css("border-color", "#598FEF"); 
        $("input#_color_value").val(toHex($(this).css("background-color")));    
        }); 
      swatch.bind("mouseout", function(e){ 
        $(this).css("border-color", "#000");
        $("input#_color_value").val(toHex($(selectorOwner).css("background-color")));
        });
      
      swatch.appendTo(selector);
     });
  
     //add HEX value field
     hex_field = $("<label for='_color_value'>Hex</label><input type='text' size='8' id='_color_value'/>");
     hex_field.bind("keydown", function(event){
      if(event.keyCode == 13) {changeColor($(this).val());}
      if(event.keyCode == 27) {toggleSelector()}
     });
     
     $("<div id='_color_custom'></div>").append(hex_field).appendTo(selector);
               
     $("body").append(selector); 
     selector.hide();
  };
  
  checkMouse = function(event){
    //check the click was on selector itself or on selectorOwner
    var selector = "div#_color_selector";
    var selectorParent = $(event.target).parents(selector).length;
    if(event.target == $(selector)[0] || event.target == selectorOwner || selectorParent > 0) return
    
    hideSelector();   
  }
  
  hideSelector = function(){
    var selector = $("div#_color_selector");
    
    $(document).unbind("mousedown", checkMouse);
    selector.hide();
    selectorShowing = false
  }
  
  showSelector = function(){
    var selector = $("div#_color_selector");
    
    selector.css({
      top: $(selectorOwner).offset().top + ($(selectorOwner).outerHeight()),
      left: $(selectorOwner).offset().left
    }); 
    hexColor = $(selectorOwner).next("input").val();
    $("input#_color_value").val(hexColor);
    selector.show();
    
    //bind close event handler
    $(document).bind("mousedown", checkMouse);
    selectorShowing = true 
   }
  
  toggleSelector = function(event){
    selectorOwner = this; 
    selectorShowing ? hideSelector() : showSelector();
  }
  
  changeColor = function(value){
    if(selectedValue = toHex(value)){
      $(selectorOwner).css("background-color", selectedValue);
      $(selectorOwner).next("input").val(selectedValue);
    
      //close the selector
      hideSelector();    
    }
  };
  
  //converts RGB string to HEX - inspired by http://code.google.com/p/jquery-color-utils
  toHex = function(color){
    //valid HEX code is entered
    if(color.match(/[0-9a-fA-F]{3}$/) || color.match(/[0-9a-fA-F]{6}$/)){
      color = (color.charAt(0) == "#") ? color : ("#" + color);
    }
    //rgb color value is entered (by selecting a swatch)
    else if(color.match(/^rgb\(([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]),[ ]{0,1}([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]),[ ]{0,1}([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])\)$/)){
      var c = ([parseInt(RegExp.$1),parseInt(RegExp.$2),parseInt(RegExp.$3)]);
      
      var pad = function(str){
            if(str.length < 2){
              for(var i = 0,len = 2 - str.length ; i<len ; i++){
                str = '0'+str;
              }
            }
            return str;
      }

      if(c.length == 3){
        var r = pad(c[0].toString(16)),g = pad(c[1].toString(16)),b= pad(c[2].toString(16));
        color = '#' + r + g + b;
      }
    }
    else color = false;
    
    return color
  }

  
  //public methods
  $.fn.colorPicker.addColors = function(colorArray){
    $.fn.colorPicker.defaultColors = $.fn.colorPicker.defaultColors.concat(colorArray);
  };
  
  $.fn.colorPicker.defaultColors = 
	[ '000000', '330000', '660000', '990000', 'cc0000', 'ff0000',
	  '000033', '330033', '660033', '990033', 'cc0033', 'ff0033',
	  '000066', '330066', '660066', '990066', 'cc0066', 'ff0066',
	  '000099', '330099', '660099', '990099', 'cc0099', 'ff0099',
	  '0000cc', '3300cc', '6600cc', '9900cc', 'cc00cc', 'ff00cc',
	  '0000ff', '3300ff', '6600ff', '9900ff', 'cc00ff', 'ff00ff',
	  '003300', '333300', '663300', '993300', 'cc3300', 'ff3300',
	  '003333', '333333', '663333', '993333', 'cc3333', 'ff3333',
	  '003366', '333366', '663366', '993366', 'cc3366', 'ff3366',
	  '003399', '333399', '663399', '993399', 'cc3399', 'ff3399',
	  '0033cc', '3333cc', '6633cc', '9933cc', 'cc33cc', 'ff33cc',
	  '0033ff', '3333ff', '6633ff', '9933ff', 'cc33ff', 'ff33ff',
	  '006600', '336600', '666600', '996600', 'cc6600', 'ff6600',
	  '006633', '336633', '666633', '996633', 'cc6633', 'ff6633',
	  '006666', '336666', '666666', '996666', 'cc6666', 'ff6666',
	  '006699', '336699', '666699', '996699', 'cc6699', 'ff6699',
	  '0066cc', '3366cc', '6666cc', '9966cc', 'cc66cc', 'ff66cc',
	  '0066ff', '3366ff', '6666ff', '9966ff', 'cc66ff', 'ff66ff',
	  '009900', '339900', '669900', '999900', 'cc9900', 'ff9900',
	  '009933', '339933', '669933', '999933', 'cc9933', 'ff9933',
	  '009966', '339966', '669966', '999966', 'cc9966', 'ff9966',
	  '009999', '339999', '669999', '999999', 'cc9999', 'ff9999',
	  '0099cc', '3399cc', '6699cc', '9999cc', 'cc99cc', 'ff99cc',
	  '0099ff', '3399ff', '6699ff', '9999ff', 'cc99ff', 'ff99ff',
	  '00cc00', '33cc00', '66cc00', '99cc00', 'cccc00', 'ffcc00',
	  '00cc33', '33cc33', '66cc33', '99cc33', 'cccc33', 'ffcc33',
	  '00cc66', '33cc66', '66cc66', '99cc66', 'cccc66', 'ffcc66',
	  '00cc99', '33cc99', '66cc99', '99cc99', 'cccc99', 'ffcc99',
	  '00cccc', '33cccc', '66cccc', '99cccc', 'cccccc', 'ffcccc',
	  '00ccff', '33ccff', '66ccff', '99ccff', 'ccccff', 'ffccff',
	  '00ff00', '33ff00', '66ff00', '99ff00', 'ccff00', 'ffff00',
	  '00ff33', '33ff33', '66ff33', '99ff33', 'ccff33', 'ffff33',
	  '00ff66', '33ff66', '66ff66', '99ff66', 'ccff66', 'ffff66',
	  '00ff99', '33ff99', '66ff99', '99ff99', 'ccff99', 'ffff99',
	  '00ffcc', '33ffcc', '66ffcc', '99ffcc', 'ccffcc', 'ffffcc',
	  '00ffff', '33ffff', '66ffff', '99ffff', 'ccffff', 'ffffff',
	];
  
})(jQuery);

