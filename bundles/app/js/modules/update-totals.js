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