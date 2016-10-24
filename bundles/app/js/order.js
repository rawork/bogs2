(function($){

	//====== Modules ======//
    var mobMenu = require('./modules/mob-menu'),
    	modal = require('./modules/modal'),
        catalogOrder = require('./modules/catalog-order'),
        callOrder = require('./modules/call-order');
	var validators = {
		'*': /^(\w+\S+)/,
		'email': /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
		'tel': /^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]\d{3}[\s.-]\d{4}$/,
		'num': /^[0-9]+$/
	};
    // Close modal
    modal.closeModal();

	$('#tel').mask("+7 (999)999-9999");

    //====== Slide Fade Toggle ======//
	$.fn.slideFadeToggle = function(speed, easing, callback){
		return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);
	};

	$('.order-prop__toggle').on('click', function(e){
		e.preventDefault();
		var toggle = $(this);
		toggle.parent().siblings('.order-prop__hidden-area').slideFadeToggle(300);
		if ( !toggle.hasClass('opened') ) {
			toggle.addClass('opened').text('Скрыть список');
		} else {
			toggle.removeClass('opened').text('Показать список товаров');
		}
	});

	//====== Submit form ======//
	$('.form__submit').on('click', function(e){
		e.preventDefault();
		var that = $(this);

		var form = that.parents(form);
		var inputs = form.find('input');
		var errors = [];
		inputs.each(function(){
			var value = $(this).val();
			var rulesString = $(this).attr('data-validator');
			if (typeof rulesString === "undefined") {
				return true;
			}
			console.info(rulesString);
			var rulesArray = rulesString.split(',');
			for (var i in rulesArray) {
				if (rulesArray[i] != '') {
					var validator = validators[rulesArray[i]];
					if (!validator.test(value)) {
						$(this).css('border-color','#d00');
					} else {
						$(this).css('border-color','#ddd');
					}
				}
			}
		});

		console.info('Submit!');
	})
})(jQuery);