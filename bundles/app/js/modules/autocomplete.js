'use strict';
//====== Region autocomplete ======//
var updateTotals = require('./update-totals'),
    setRegion = require('./set-region');

module.exports = (function() {

    var field = $('.select-region__field'),
        regions;

    // Get Regions
    $.getJSON('/bundles/public/js/regions.json', function(data){
        regions = data;
    });

    // Search Filter Function
    function filterRegion(arr, query, autocompleteList) {
        var newQuery = query.toLowerCase(),
            newList = [];
        var newArr = arr.filter(function(item) {
            if ( newQuery === item.subject.toLowerCase() ) {
                setRegion(field, autocompleteList, item.subject);
                updateTotals(item.price);
                return;
            }
            return item.subject.toLowerCase().indexOf(newQuery) >= 0 ;
        }); 
        for (var i = 0, max = newArr.length; i < max; i++) {
            newList.push('<li class="autocomplete__item"><span class="autocomplete__item__name">' + newArr[i].subject + '</span> – <span class="autocomplete__item__price">' + newArr[i].price + '</span>' + '\xa0руб.</li>');
        }
        autocompleteList.html( newList.join('') );
    }

    // Listen keyup
    var timer;
    field.on('keyup', function (e) {
        if( e.which <= 90 && e.which >= 48 || e.which == 8 ){
            var input = $(this);
            if (timer) clearTimeout(timer); // clear timer
            timer = setTimeout( function(){
                var query = input.val(), // value of input
                    autocompleteList = input.siblings('.autocomplete'); // value of input
                if (query !== '') {
                    filterRegion(regions, query, autocompleteList);
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