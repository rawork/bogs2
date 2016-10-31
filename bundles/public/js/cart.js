(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';
(function($) {

    var warningMessage = $('.warning');

    //====== Initial Cart Values ======//
    var shouldSelectRegion = false,
        subTotal = 0, // products total
        shippingTotal = 0; // shipping total

    //====== Export setter ======//
    function setShoudSelectRegion(val){
        shouldSelectRegion = val;
    }
    module.exports = setShoudSelectRegion;

    //====== Modules ======//
    var autocomplete = require('./modules/autocomplete'),
        updateTotals = require('./modules/update-totals'),
        setRegion = require('./modules/set-region'),
        convert = require('./modules/price-converter'),
        mobMenu = require('./modules/mob-menu'),
        catalogOrder = require('./modules/catalog-order'),
        callOrder = require('./modules/call-order'),
        modal = require('./modules/modal');

    // Close modal
    modal.closeModal();

    //====== Get subTotal ======//
    $('.product-in-cart__price').each(function(){
        subTotal += convert.priceToNum( $(this) );
    });
    

    //====== Radio ======//
    // Initial state
    var radios = $('.shipping-list input[type="radio"]'),
        activeRadioParent; // store active item
    // Set active on load
    radios.each(function(){
        var radio = $(this);
        if ( radio.prop( 'checked' ) ) {
            shippingTotal = parseInt( radio.attr('data-shipping-price') );
            activeRadioParent = radio.closest('.shipping-payment__list__item');
            activeRadioParent.addClass('active');
        }
    });
    // On Change
    var hiddenMethod = null;
    radios.on('change', function(){
        activeRadioParent.removeClass('active');
        var radio = $(this),
            hidePayment = radio.attr('data-hide-payment'),
            radioParent = radio.closest('.shipping-payment__list__item');
        shippingTotal = parseInt( radio.attr('data-shipping-price') );
        if ( radio.attr('data-select-region') === 'true' ){
            shouldSelectRegion = true;
            shippingTotal = 0;
        } else {
            shouldSelectRegion = false;
        }
        radioParent.addClass('active');
        activeRadioParent = radioParent;
        $('.select-region__field').val('').removeClass('selected'); // Clean Region Search
        updateTotals(shippingTotal, subTotal);

        // Hide/Show payment method
        if( hidePayment ){
            hiddenMethod = $(hidePayment);
            hiddenMethod.find('input[type="radio"]').prop( 'checked', false );
            hiddenMethod.hide();
        }
        if( !hidePayment && hiddenMethod) {
            hiddenMethod.show();
        }
    });

    // Update on load
    updateTotals(shippingTotal, subTotal);


    //====== Remove Item from Cart ======//
    // Check if Cart is empty
    function checkCart(){
        if( $('.product-in-cart').length === 0 ) {
            window.location.reload();
            $('.products-list').html('<tr class="empty-cart"><td>Ваша корзина пуста</td></tr>')
        }
    };

    $('.remove').on('click', function(e){
        e.preventDefault();
        var id = parseInt($(this).attr('data-id'));
        var product = $(this).closest('.product-in-cart'),
            price = convert.priceToNum( product.find('.product-in-cart__price') );
        $.post('/api/basket/remove', {id: id}, function(data){
            if(data.status){
                $('#cart').html(data.minicart);
                product.fadeOut(300, function(){
                    product.remove();
                    subTotal = subTotal - price;
                    $('.sub-total__val').text( convert.numToPrice( subTotal ) );
                    checkCart();
                    updateTotals(shippingTotal, subTotal);
                });
            } else {
                console.log('remove sku from cart error');
            }
        }, 'json');

    });

    $(document).on("check", '.amount, .amount-dec, .amount-inc', function () {
        console.log('cart amount check');

        var product = $(this).closest('.product-in-cart');
        var amount = product.find('.amount');
        var price = product.find('.product-in-cart__price');
        var priceOld = convert.priceToNum( price );
        var priceNew = parseInt(amount.val())*parseInt(amount.attr('data-price'));
        var delta = priceNew - priceOld;

        subTotal += delta;
        if (subTotal < 0) {
            subTotal = 0;
        }

        $.post('/api/basket/amount', {id: amount.attr('data-sku'), amount: amount.val()}, function(data){
            price.html(priceNew);
            $('.sub-total__val').text( convert.numToPrice( subTotal ) );
            checkCart();
            updateTotals(shippingTotal, subTotal);
            $('#cart').html(data.minicart);
        }, "json")

    });



    //====== Autocomlete ======//
    // Click Autocomplete Item
    $(document).on('click', '.autocomplete__item', function(){
        var item = $(this),
            subjName = item.find('.autocomplete__item__name').text(),
            autocompleteList = item.parent(),
            field = autocompleteList.siblings('.select-region__field');
        shippingTotal = parseInt( item.find('.autocomplete__item__price').text() );
        setRegion(field, autocompleteList, subjName);
        shouldSelectRegion = false;
        updateTotals(shippingTotal, subTotal);
    });

    // Remove Selected Region
    $(document).on('keyup', '.select-region__field.selected', function(e){
        if( e.which <= 90 && e.which >= 48 || e.which == 8 ){
            $(this).removeClass('selected');
            shippingTotal = 0;
            updateTotals(shippingTotal, subTotal);
        }
    });

    //====== Send Order ======//
    function showMessage(text) {
        warningMessage.text(text);
        warningMessage.fadeIn();
        console.warn(text);
    }

    $('.btn-order').on('click', function(e){
        e.preventDefault();
        var $that = $(this);
        var paymentChecked = false,
            productsLength = $('.product-in-cart').length;
        $('input[name="payment-type"]').each(function() {
            if( $(this).prop( 'checked' ) ){
                paymentChecked = true;
            }
        });
        if (!productsLength) {
            showMessage('Корзина пуста(');
            return;
        }
        if ( !paymentChecked ) {
            showMessage('Выберите метод оплаты');
            return;
        }
        if ( shouldSelectRegion ) {
            showMessage('Выберите место доставки');
            return;
        }
        //console.info('Заказ отправлен!');
        var delivery_type_field = $('input[name=shipping-type]:checked');
        var delivery_type = delivery_type_field.val();
        var delivery_country = $('input.select-region__field[data-source=countries]').val();
        var delivery_region = '';
        var delivery_city = '';
        try {
            var parent = delivery_type_field.parents('.shipping-payment__list__item');
            if (parent.find('input[data-source=regions]').length) {
                delivery_region = parent.find('input[data-source=regions]').val();
            }
            if (parent.find('input[data-source=cities]').length){
                delivery_city = parent.find('input[data-source=cities]').val();
            }

        } catch (err) {
            console.log(err);
        }

        var delivery_cost = shippingTotal;
        var payment_type = $('input[name=payment-type]:checked').val();

        //console.log(delivery_country, delivery_region, delivery_city);

        $.post('/api/basket/info', {delivery_type: delivery_type, delivery_country: delivery_country, delivery_region: delivery_region, delivery_city: delivery_city, delivery_cost: delivery_cost, payment_type: payment_type}, function(data){
            if (data.status == 'ok') {
                window.location = $that.attr('href');
            }
        },"json");

    });

})(jQuery);
},{"./modules/autocomplete":3,"./modules/call-order":4,"./modules/catalog-order":5,"./modules/mob-menu":6,"./modules/modal":7,"./modules/price-converter":8,"./modules/set-region":9,"./modules/update-totals":10}],2:[function(require,module,exports){
module.exports=require(1)
},{"./modules/autocomplete":3,"./modules/call-order":4,"./modules/catalog-order":5,"./modules/mob-menu":6,"./modules/modal":7,"./modules/price-converter":8,"./modules/set-region":9,"./modules/update-totals":10}],3:[function(require,module,exports){
'use strict';
//====== Region autocomplete ======//
var updateTotals = require('./update-totals'),
    setRegion = require('./set-region');

module.exports = (function() {

    var field = $('.select-region__field'),
        regions,
        lists = {'cities': [], 'regions': [], 'countries': []};

    // Get Regions
    $.getJSON('/api/basket/regions.json', function(data){
        regions = data;
        lists.regions = data
    });

    $.getJSON('/api/basket/cities.json', function(data){
        lists.cities = data
    });

    $.getJSON('/api/basket/countries.json', function(data){
        lists.countries = data
    });

    // Search Filter Function
    function filterRegion(arr, query, autocompleteList, context) {
        var newQuery = query.toLowerCase(),
            newList = [];
        var newArr = arr.filter(function(item) {
            if ( newQuery === item.subject.toLowerCase() ) {
                //console.log(item);
                setRegion(field, autocompleteList, item.subject);
                updateTotals(item[context]);
                return;
            }
            return item.subject.toLowerCase().indexOf(newQuery) >= 0 ;
        }); 
        for (var i = 0, max = newArr.length; i < max; i++) {
            newList.push('<li class="autocomplete__item"><span class="autocomplete__item__name">' + newArr[i].subject + '</span> – <span class="autocomplete__item__price">' + newArr[i][context] + '</span>' + '\xa0руб.</li>');
        }
        autocompleteList.html( newList.join('') );
    }

    // Listen keyup
    var timer;
    field.on('keyup', function (e) {
        var context = $(this).attr('data-context');
        var source = $(this).attr('data-source');
        //console.log(context, source);
        if( e.which <= 90 && e.which >= 48 || e.which == 8 ){
            var input = $(this);
            if (timer) clearTimeout(timer); // clear timer
            timer = setTimeout( function(){
                var query = input.val(), // value of input
                    autocompleteList = input.siblings('.autocomplete'); // value of input
                if (query !== '') {
                    filterRegion(lists[source], query, autocompleteList, context);
                } else {
                    autocompleteList.html('');
                    field.removeClass('selected');
                }
            }, 200);
        }
    });

    // Clean Autocomplete if click outside
    $(document).mouseup(function(e){
        if ( !$('.autocomplete').is($(e.target)) && $('.autocomplete').has(e.target).length === 0 ){
            $('.autocomplete').html(''); // Clean Autocomplete
        }
    });
}());
},{"./set-region":9,"./update-totals":10}],4:[function(require,module,exports){
'use strict';
//====== Call order ======//
var modal = require('./modal');
module.exports = (function() {
    $(document).on('click', '.show-call-order', function(e){
        e.preventDefault();
        $('.modal .title').html('Заказать звонок');
        $('.modal .content').html('<form method="post"><input type="text" id="name" placeholder="Ваше имя"><input type="text" id="phone" placeholder="Ваш телефон"><button class="btn call-order">Заказать</button></form>');
        modal.showModal();
        yaCounter29093585.reachGoal('show_order_call');
    });

    $(document).on('click', '.call-order', function(e){
        e.preventDefault();
        var name = $('input#name').val();
        var phone = $('input#phone').val();

        if (name === '' || phone === '') {
            return;
        }

        $.post("/ajax/call", {name: name, phone: phone},
            function(data){
                $('.modal .content').html(data.text);
                yaCounter29093585.reachGoal('order_call');
            }, "json");
    });
})();
},{"./modal":7}],5:[function(require,module,exports){
'use strict';
//====== Catalog Order ======//
var modal = require('./modal');
module.exports = (function(){
	$(document).on('click', '.show-catalog-order', function(e){
        e.preventDefault();
        $('.modal .title').html('BOGS Оптом');
        $('.modal .content').html('<form method="post"><input type="text" id="name" placeholder="Ваше имя"><input type="text" id="email" placeholder="Ваш e-mail"><input type="text" id="phone" placeholder="Ваш телефон"><button class="btn catalog-order">Заказать</button></form>');
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
},{"./modal":7}],6:[function(require,module,exports){
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

},{}],7:[function(require,module,exports){
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
},{}],8:[function(require,module,exports){
'use strict';
//====== Price Converter ======//
module.exports = {
	// Price to number
    priceToNum: function(el){
        return parseInt( el[0].innerText.replace(/\s+/g, '') );
    },
    // Number to Price
    numToPrice: function(num){
        var num = num + '',
            arr = num.split(''),
            arrCopy = arr.slice(0),
            j = 0;
        for(var i = arr.length -1 ; i >= 0; i--){
            if ( j % 3 === 0 && j !== 0 ) {
                arrCopy.splice( i + 1, 0, ' ' );
            }
            j++;
        };
        return arrCopy.join('');
    }
}
},{}],9:[function(require,module,exports){
'use strict';
//====== setRegion ======//
var setShoudSelectRegion = require('../cart');
module.exports = function(field, autocompleteList, name) {
	setShoudSelectRegion(false);
    field.val(name);
    autocompleteList.html(''); // Clean Autocomplete
    field.addClass('selected');

};
},{"../cart":1}],10:[function(require,module,exports){
'use strict';
var convert = require('./price-converter');
//====== Update ======//
module.exports = function(shippingTotal, subTotal){
	console.log('Update Totals', shippingTotal);
	// if subTotal undefined
    if (!subTotal) {
    	subTotal = 0;
    	$('.product-in-cart__price').each(function(){
	        subTotal += convert.priceToNum( $(this) );
	    });
    }
    var total = 0;
    if (shippingTotal > -1) {
        total = subTotal + shippingTotal;
        $('.shipping-total__val').text(shippingTotal).removeClass('none');
        $('.total__title').text('Стоимость товаров и доставки');

    } else {
        total = subTotal;
        $('.shipping-total__val').text('Рассчитывается менеджером').addClass('none');
        $('.total__title').text('Стоимость товаров');
    }


    $('.sub-total__val').text(subTotal);
    $('.total__val').text(total);
};
},{"./price-converter":8}]},{},[2])