'use strict';
//====== setRegion ======//
var setShoudSelectRegion = require('../cart');
module.exports = function(field, autocompleteList, name) {
	setShoudSelectRegion(false);
    field.val(name);
    autocompleteList.html(''); // Clean Autocomplete
    field.addClass('selected');

};