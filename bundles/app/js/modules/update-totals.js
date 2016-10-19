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
    var total = subTotal + shippingTotal;
    $('.shipping-total__val').text(shippingTotal);
    $('.sub-total__val').text(subTotal);
    $('.total__val').text(total);
};