(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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
			// console.log('ajax answer');
			if(data.status == 'ok') {
				if (data.order){

					console.log(
						parseInt(data.order.cost) + parseInt(data.order.delivery_cost),
						parseFloat(data.order.cost) + parseFloat(data.order.delivery_cost));

                    ga('require', 'ecommerce');
                    ga('ecommerce:addTransaction', {
                        'id': data.order.id,                     // Transaction ID. Required.
                        'affiliation': 'Bogs-Shop.ru',   // Affiliation or store name.
                        'revenue': parseFloat(data.order.cost) + parseFloat(data.order.delivery_cost),               // Grand Total.
                        'shipping': data.order.delivery_cost,                  // Shipping.
                        'currency': 'RUR'                     // Tax.
                    });


                    data.items.forEach(function(item) {
                        ga('ecommerce:addItem', {
                            'id': data.order.id,            // Transaction ID. Required.
                            'name': item.name,    			// Product name. Required.
                            'sku': item.sku,                // SKU/code.
                            'price': item.price,            // Unit price.
                            'quantity': item.amount,        // Quantity.
                            'currency': 'RUR'
                        });
                    });

                    ga('ecommerce:send');

                    var products = [];

                    data.items.forEach(function(product){
						products.push({
                            "id": product.sku,
                            "name": product.name,
                            "price": product.price,
                            "brand": "Bogs",
                            "category": product.category,
                            "variant": product.size + " размер"
						})
					});

                    dataLayer.push({
                        "ecommerce": {
                            "purchase": {
                                "actionField": {
                                    "id" : "TRX987"
                                },
                                "products": products
                            }
                        }
                    });
				}

                setTimeout(function(){
                    window.location = data.link;
				}, 1000);
			} else {
				alert(data.error);
			}
		}, 'json');

		console.info('Submit!');
	})
})(jQuery);
},{"./modules/call-order":2,"./modules/catalog-order":3,"./modules/mob-menu":4,"./modules/modal":5}],2:[function(require,module,exports){
'use strict';
//====== Call order ======//
var modal = require('./modal');
module.exports = (function() {
    $(document).on('click', '.show-call-order', function(e){
        e.preventDefault();
        $('.modal .title').html('Заказать звонок');
        $('.modal .content').html('<form method="post"><input type="text" name="lastname" id="lastname" placeholder="Фамилия" class="flag"> <input type="text" id="name" placeholder="Ваше имя"><input type="text" id="phone" placeholder="Ваш телефон"><button class="btn call-order">Заказать</button></form>');
        modal.showModal();
        yaCounter29093585.reachGoal('show_order_call');
    });

    $(document).on('click', '.call-order', function(e){
        e.preventDefault();
        var lastname = $('input#lastname').val();
        var name = $('input#name').val();
        var phone = $('input#phone').val();

        if (name === '' || phone === '') {
            return;
        }

        $.post("/ajax/call", {lastname: lastname, name: name, phone: phone},
            function(data){
                $('.modal .content').html(data.text);
                yaCounter29093585.reachGoal('order_call');
            }, "json");
    });
})();
},{"./modal":5}],3:[function(require,module,exports){
'use strict';
//====== Catalog Order ======//
var modal = require('./modal');
module.exports = (function(){
	$(document).on('click', '.show-catalog-order', function(e){
        e.preventDefault();
        $('.modal .title').html('BOGS Оптом');
        $('.modal .content').html('<p>Оставьте ваши контактные данные и мы пришлем вам оптовый прайс лист</p><form method="post"><input type="text" id="name" placeholder="Ваше имя"><input type="text" id="email" placeholder="Ваш e-mail"><input type="text" id="phone" placeholder="Ваш телефон"><button class="btn catalog-order">Заказать</button></form>');
        modal.showModal();
        yaCounter29093585.reachGoal('show_order_pdf');
    });

    $(document).on('click', '.catalog-order', function(e){
        e.preventDefault();

        var name = $('input#name').val();
        var email = $('input#email').val();
        var phone = $('input#phone').val();

        if (name === '' || phone === '' || email === '') {
            return;
        }

        $.post("/ajax/catalog", {name: name, email: email, phone: phone},
            function(data){
                $('.modal .content').html(data.text);
                yaCounter29093585.reachGoal('order_pdf');
            }, "json");
    });
})();
},{"./modal":5}],4:[function(require,module,exports){
'use strict';
//====== Mobile menu ======//
module.exports = (function(){
    var header2 = $('#header2'),
        showCat = $('#header1 .pull-right').html(),
        menuLinks = $('.menu ul').html(),
        socialLinks = $('.social').html(),
        mmBtn = $('.mm-btn'),
        mobMenuCont = $('.mm-cont');

    mobMenuCont.html('<ul>' + menuLinks + '</ul>' + showCat + '<div class="social">' + socialLinks + '</div>');

    mmBtn.on('click', function(e){
        e.preventDefault();
        header2.toggleClass('show-menu');
    })
}());

},{}],5:[function(require,module,exports){
'use strict';
module.exports = {
    showModal: function (){
        var that = this;
        var body = $('body');
        $('.modal').removeClass('hidden');
        body.addClass('no-scroll');
        if ($('.modal .gallery-img-big img').length) { // Wait when big image loaded
            $('.modal .gallery-img-big img').load(function(){
                that.getMarginTop();
                $( '.modal .gallery-img-big img' ).unbind( 'load' );
            });
        } else {
            that.getMarginTop();
        }
    },
    getMarginTop: function(){
        var modalDialog = $('.modal-dialog'),
            winHeight = $(window).outerHeight(),
            modalDialogHeight = modalDialog.outerHeight(),
            marginTop = ( modalDialogHeight + 50 ) < winHeight ? ( winHeight - modalDialogHeight ) / 2 : 50;
        $('.modal-dialog').css({
            marginTop: marginTop    
        });
    },
    closeModal: function(){
        $(document).on('click', '.modal .close, .modal-overlay', function(e){
            e.preventDefault();
            $('body').removeClass('no-scroll');
            $(this).parents('.modal').addClass('hidden').removeClass('modal--product');
        });
    }
}
},{}]},{},[1])