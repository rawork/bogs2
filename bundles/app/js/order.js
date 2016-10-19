(function($){

	//====== Modules ======//
    var mobMenu = require('./modules/mob-menu'),
    	modal = require('./modules/modal'),
        catalogOrder = require('./modules/catalog-order'),
        callOrder = require('./modules/call-order');

    // Close modal
    modal.closeModal();


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
	})

	//====== Submit form ======//
	$('.form__submit').on('submit', function(){
		e.preventDefault();
		console.info('Submit!');
	})
})(jQuery);