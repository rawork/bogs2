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