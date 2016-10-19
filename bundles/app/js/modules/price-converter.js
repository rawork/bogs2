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