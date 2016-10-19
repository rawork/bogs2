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
