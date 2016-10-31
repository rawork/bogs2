(function($){

	//====== Modules ======//
    var mobMenu = require('./modules/mob-menu'),
    	modal = require('./modules/modal'),
        catalogOrder = require('./modules/catalog-order'),
        callOrder = require('./modules/call-order');
	var validators = {
		'*': /^[\w\S\,\.\-\s\(\)]+$/,
		'email': /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
		'tel': /^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]\d{3}[\s.-]\d{4}$/,
		'num': /^[0-9]+$/
	};
    // Close modal
    modal.closeModal();

	$('#tel').mask("+7 (999) 999-9999");

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
		var errors = false;

		console.log('before validator', errors);
		inputs.each(function(){
			var value = $(this).val();
			var rulesString = $(this).attr('data-validator');
			if (typeof rulesString === "undefined") {
				return true;
			}
			//console.info(rulesString);
			var rulesArray = rulesString.split(',');
			for (var i in rulesArray) {
				if (rulesArray[i] != '') {
					var validator = validators[rulesArray[i]];
					if (!validator.test(value)) {
						$(this).css('border-color','#d00');
						errors = true;
						console.log(value, validator, rulesString, rulesArray);
					} else {
						$(this).css('border-color','#ddd');
					}
				}
			}
		});

		console.log('before errors', errors);

		if (errors){
			return;
		}

		console.log('after errors');

		var inputData = {
			name: $('#name').val(),
			lastname: $('#surname').val(),
			email: $('#email').val(),
			phone: $('#tel').val(),
			country: $('#country').val(),
			postindex: $('#postindex').val(),
			region: $('#region').val(),
			city: $('#city').val(),
			street: $('#street').val(),
			house: $('#house').val(),
			building: $('#building').val(),
			apartment: $('#apartment').val()
		};

		console.log(inputData);

		$.post('/api/order/save', inputData, function(data){
			console.log('ajax answer');
			if(data.status == 'ok') {
				window.location = data.link;
			} else {
				alert(data.error);
			}
		}, 'json');

		console.info('Submit!');
	})
})(jQuery);