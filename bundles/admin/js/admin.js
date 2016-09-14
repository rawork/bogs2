function preSubmit(utype) {
	if (utype) {
		$('#entityForm input[name=utype]').val(utype);	
	}
	$('#entityForm').submit();
}

function preFilter(type) {
	$('#filter_type').val(type);
	$('#frmFilter').submit();
}

function startGroupDelete() {
	elements = $(".list-checker:checked");
	if (elements.length <= 0) {
		alert('Не выбраны элементы для удаления');
	} else {
		if (confirm('Уверены, что хотите удалить выделенные записи?')) {
			ids = new Array();
			elements.each(function (index, domElement) {
				id = $(domElement).val(); 
				ids.push(id);
			});
			$('#ids').val(ids.join());
            path = location.href.split('?');
			$('#frmGroupUpdate').prop('action', path[0] + '/groupdelete');
			$('#frmGroupUpdate').submit();
		} else {
			return false;
		}
	}	
}

function startDelete(id) {
	if (confirm('Уверены, что хотите удалить запись?')) {
		path = location.href.split('?');
		window.location = path[0] + '/' + id + '/delete';
	} else {
		return false;
	}
}

function startGroupUpdate(checkUpdate) {
	elements = $(".list-checker:checked");
	if (checkUpdate == true) {
		if (elements.length <= 0) {
			alert('Не выбраны элементы для редактирования');
		} else {
			ids = new Array();
			elements.each(function (index, domElement) {
				id = $(domElement).val(); 
				ids.push(id);
			});
			$('input[name="edited"]').val(0);
			$('#ids').val(ids.join());
			$('#frmGroupUpdate').submit();
		}
	} else {
		$('#frmGroupUpdate').submit();
	}	
}

function showPopup() {
	$('#modalDialog').modal('show');
}

function hidePopup() {
	$('#modalDialog').modal('hide');
}

function emptySelect(inputId) {
	$('#'+inputId).val(0);
	$('#'+inputId+'_title').html('Не выбрано');
}

function makePopupChoice(inputId) {
	value = $('#popupChoiceId').val();
	valueTitle = $('#popupChoiceTitle').html();
	type = $('#'+inputId+'_type').val();
	if (type == 'many') {
		text = '<div>'+valueTitle+' <input type="radio" name="'+inputId+'_default" value="'+value+'" class="selected-default" data-input-id="'+inputId+'"> По умолчанию <a href="#" class="selected-remove" data-input-id="'+inputId+'"><i class="glyphicon glyphicon-remove"></i></a></div>';
		if ($('input[name|="'+inputId+'_default"]').length == 0) {
			$('#'+inputId+'_title').html(text);
			$('#'+inputId).val(value);
			$('input[name|="'+inputId+'_default"]').first().prop('checked', true);
		} else {
			$('#'+inputId+'_title').append(text);
		}
		ids = new Array();
		$('input[name|="'+inputId+'_default"]').each(function (index, domElement){
			if ($(domElement).val() != $('#'+inputId).val()) {
				ids.push($(domElement).val());
			}
		});
		$('#'+inputId+'_extra').val(ids.join());
	} else {
		$('#'+inputId).val(value);
		text = '<div>'+valueTitle+' <a href="#" class="selected-remove" data-input-id="'+inputId+'"><i class="glyphicon glyphicon-remove"></i></a></div>';
		$('#'+inputId+'_title').html(text);
	}
	hidePopup();
}

function choiceList(input_id) {
    ids = new Array();
    titles = new Array();
    $("input.popup-item:checked").each(function (index, domElement) {
        id = $(domElement).val();
        title = $('#itemTitle' + id).html();
        ids.push(id);
        titles.push(title);
    });
    $('#'+input_id).val(ids.join());
    $('#'+input_id+'_title').val(titles.join(', '));
    hidePopup();
}

function changeTemplateWidget(name){
	$('#'+name+'_template').toggleClass('hidden');
	$('#'+name+'_file').toggleClass('hidden');
}

function changeTemplateState(name){
	index = $('#'+name+'_version')[0].selectedIndex;
	if (index) {
		$('#'+name+'_delete').addClass('hidden');
		$('#'+name+'_template').addClass('hidden');
		$('#'+name+'_file').addClass('hidden');
		$('#'+name+'_view').removeClass('hidden');
	} else {
		$('#'+name+'_delete').removeClass('hidden');
		$('#'+name+'_template').removeClass('hidden');
		$('#'+name+'_file').removeClass('hidden');
		$('#'+name+'_view').addClass('hidden');
	}
}

/* ajax */
function showSelectDialog(inputId, tableName, fieldName, dbId, title){
	$.post(prj_ref+"/admin/dialog/select", {input_id: inputId, table_name: tableName, field_name: fieldName, entity_id: dbId, title : title},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		$('.popup-item').on("click", function(event){
			$('#popupChoiceId').val($(this).prop('rel'));
			$('#popupChoiceTitle').html($(this).html());
		});	
		showPopup();
	}, "json");
}

function showPage(divId, tableName, fieldName, entityId, page) {
	$.post(prj_ref+"/admin/dialog/selectpage", {div_id: divId, table_name: tableName, field_name: fieldName, entity_id: entityId, page: page},
	function(data){
		$('#' + divId).html(data.content);
		$('.popup-item').on("click", function(event){
			$('#popupChoiceId').val($(this).prop('rel'));
			$('#popupChoiceTitle').html($(this).html());
		});
	}, "json");
}

function showTreeDialog(inputId, tableName, fieldName, dbId, title){
	checkedId = dbId;
	$.post(prj_ref + "/admin/dialog/tree", {input_id: inputId, table_name: tableName, field_name: fieldName, entity_id: dbId, title : title},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		$("#navigation").treeview({
			persist: "location",
			collapsed: true,
			unique: true
		});
		$('.popup-item').on("click", function(event){
			$('#popupChoiceId').val($(this).prop('rel'));
			$('#popupChoiceTitle').html($(this).html());
		});
		showPopup();
	}, "json");
}

function showListDialog(inputId, table_name, field_name, value){
    $.post(prj_ref + "/admin/dialog/list", {input_id: inputId, table_name: table_name, field_name: field_name, value: value},
        function(data){
            $('#popupTitle').html(data.title);
            $('#popupButtons').html(data.button);
            $('#popupContent').html(data.content);
            showPopup();
        }, "json");
}

function showTemplateDialog(name) {
	id = $('#'+name+'_version')[0].options[$('#'+name+'_version')[0].selectedIndex].value;
	if (id) {
		$.post(prj_ref + "/admin/dialog/template", {version_id: id},
		function(data){
			$('#popupTitle').html(data.title);
			$('#popupButtons').html(data.button);
			$('#popupContent').html(data.content);
			showPopup();
		}, "json");
	} else {
		alert('Не выбрана версия!');
	}
}

function showCopyDialog(id) {
	$.post(prj_ref + "/admin/copy/"+id, {},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		showPopup();
	}, "json");
}

function startCopy(ref) {
	var quantity = parseInt($('#copyQuantity').val());
	if (quantity && (quantity < 1 || quantity > 10)) {
		$('#copyInput').addClass('error');
		$('#copyHelp').html('Введите число от 1 до 10');	
	} else if (quantity) {
		hidePopup();
		path = location.href.split('?');
		window.location = path[0] + ref + '/' + quantity + (1 in path ? '?'+ path[1] : '');
	} else {
		$('#copyInput').addClass('error');
		$('#copyHelp').html('Введите число от 1 до 10');	
	}
}

function editField(fieldId) {
	$.post(prj_ref+"/adminajax/", {method: 'editField', fieldId: fieldId},
	function(data){
		$('#popupTitle').html(data.title);
		$('#popupButtons').html(data.button);
		$('#popupContent').html(data.content);
		showPopup();
	}, "json");
}

function createBackup() {
	showBlock('waiting', 0, -100);
	$("#archive_info").addClass('closed');
	$("#archive_info").empty();
	$.post(prj_ref + "/admin/backup/create", {},
	function(data){
		hideBlock('waiting');
		window.location.reload();
	}, "json");
}

function clearCache() {
	showBlock('waiting', 0, -100);
	$("#cache_info").addClass('closed');
	$("#cache_info").empty();
	$.post(prj_ref + "/admin/cache/clear", {},
	function(data){
		$("#cache_info").html(data.content);
		$("#cache_info").removeClass('closed');
		hideBlock('waiting');
	}, "json");
}

function addGalleryInput(el) {
    $('#'+el+'_input').append('<br><input name="'+el+'[]" type="file">');
}

function deleteGalleryImage(id) {
	$.post(prj_ref+"/admin/gallery/delete", {id: id},
	function(data){
		if (data.error) {
			alert(data.error);
		} else {
            $('#file_'+id).remove();
        }
	}, "json");
}

function setRpp(sel, tableName) {
	showBlock('waiting', 0, -100);
    var rpp = sel.options[sel.selectedIndex].value;
	$.post(prj_ref+"/admin/rpp", {table: tableName, rpp: rpp},
	function(data){
		hideBlock('waiting');
		location.reload();
	}, "json");
}

/* end ajax */


function setupCalendar() {
	for (var name in calendars) {
		time = (calendars[name] ? ' ' + calendars[name] : '')
		Calendar.setup({
			inputField : name, 
			ifFormat : "%d.%m.%Y" + time, 
			showsTime : time ? true : false, 
			button : "trigger_" + name, 
			align : "Br", 
			singleClick : true,
			timeFormat : 24,
			firstDay : 1
		});
	}
}

function emptyDateSearch(name){
	$('#'+name+'_beg').val('');
	$('#'+name+'_end').val('');
	return false;
}

function setFieldType(it){
	tname = it.options[it.selectedIndex].value;
	if (tname == 'enum' || tname == 'select' || tname == 'select_list' || tname == 'select_tree') {
		$('#add_select_values').css('display', 'table-row');
		$('#add_params').css('display', 'table-row');
	} else {
		$('#add_select_values').hide();
		$('#add_params').hide();
	}
}

function showBlock(it) {
	$('#'+it).css('display', 'block');
}

function hideBlock(it) {
	$('#'+it).css('display', 'none');
}

$(document).ready(function(){
	$('input.clPicker').colorPicker();

    $(document).on('click', 'a.state', function () {
        showBlock('waiting', 0, -100);
        var state = $(this).attr('data-state');
        var module = $(this).attr('data-module');

        $.post(prj_ref + "/admin/statemenu/"+ state + (module ? "/" + module : ''), {},
        function(data){
            if (data.error) {
                window.location.reload();
            } else {
                $('#moduleMenu').html(data.content);
            }
            hideBlock('waiting');
        }, "json");
    });

    $(document).on('click', 'a.module', function () {
        showBlock('waiting', 0, -100);
        var module = $(this).attr('data-module');
        tablelist = $('#table-menu-'+module);
        if (tablelist.html() == '') {
            $.post(prj_ref + "/admin/modulemenu/" + module, {},
                function(data){
                    if (data.alertText) {
                        window.location.reload();
                    } else {
                        tablelist.html(data.content);
                        tablelist.show();
                    }
                    hideBlock('waiting');
                }, "json");
        } else if (tablelist.css('display') == 'none') {
            tablelist.show();
            hideBlock('waiting');
        } else {
            tablelist.hide();
            hideBlock('waiting');
        }
    })
	
	$('#myTab a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	})
	
	$('.multi').MultiFile({ 
		accept:'jpg|gif|png|rar|zip|pdf|flv|ppt|xls|doc', 
		max:10, 
		remove:'удалить',
		file:'$file', 
		selected:'Выбраны: $file', 
		denied:'Неверный тип файла: $ext!', 
		duplicate:'Этот файл уже выбран:\n$file!' 
	});		  

	$("#waiting").ajaxStart(function(){
		$(this).show();
	})
	.ajaxComplete(function(){
		$(this).hide();
	});
	
	$('#uploadForm').ajaxForm({
		beforeSubmit: function(a,f,o) {
			o.dataType = "html";
			$('#uploadOutput').html('Отправка данных...');
		},
		success: function(data) {
			var out = $('#uploadOutput');
			out.html('');
			if (typeof data == 'object' && data.nodeType)
				data = elementToString(data.documentElement, true);
			else if (typeof data == 'object')
				data = objToString(data);
			out.append('<div>'+ data +'</div>');
			$('a.MultiFile-remove').click();
			$('#updatelistbtn').click();
		}
	});
	
	setupCalendar();
	
	$('#list-checker').on('click', function () {
		if ($(this).prop('checked')) {
			$(".list-checker").prop('checked', true);
		} else {
			$(".list-checker").prop('checked', false);
		}
	});

    $('a.filemanager-link').click(function(e){
        e.preventDefault();
        var that = $(this);
        var iframe = $('<iframe frameborder="0"></iframe>');
        iframe.attr('src', that.attr('href'));
        $('#popupContent').html(iframe[0].outerHTML);
        $('#popupTitle').html(that.html());
        $('.modal-content').css('width', '800px');
        $('.modal-body').css('padding', '0');
        $('.modal-footer').css('margin-top', '0');
        $('.modal-footer').css('padding', '0');
        $('#modalDialog').modal({show:true});

    })

    $(document).on('click', 'a.locale', function(){
        $('#formLocale input[name=locale]').val($(this).attr('data-locale'));
        $('#formLocale').submit();
    });

    $(document).on('click', '.selected-default', function(e){
//        alert('def');
        var that = $(this);
        var inputId = that.attr('data-input-id');
        if (that.prop('checked')) {
            $('#'+inputId).val(that.val());
            ids = new Array();
            $('input[name|="'+inputId+'_default"]').each(function (index, domElement){
                if ($(domElement).val() != that.val()) {
                    ids.push($(domElement).val());
                }
            });
            $('#'+inputId+'_extra').val(ids.join());
        }
    });

    $(document).on('click', 'a.selected-remove', function(e){
        e.preventDefault();
        var inputId = $(this).attr('data-input-id');
        var checked = $(this).prev().prop('checked');
        $(this).parent().remove();
        if (checked) {
            if (firstElement = $('input[name|="'+inputId+'_default"]').first()) {
                firstElement.prop('checked', true);
                $('#'+inputId).val(firstElement.val());
            }
        }
        ids = new Array();
        $('input[name|="'+inputId+'_default"]').each(function (index, domElement){
            if ($(domElement).val() != $('#'+inputId).val()) {
                ids.push($(domElement).val());
            }
        });
        if ($('input[name|="'+inputId+'_default"]').length == 0) {
            $('#'+inputId).val(0);
            $('#'+inputId+'_title').html('Не выбрано');
            $('#'+inputId+'_extra').val('');
        }
        $('#'+inputId+'_extra').val(ids.join());
    });
	
});

