tinymce.init({
	mode : 'textareas',
	theme : 'modern',
	editor_selector : 'tinymce',
	language : 'ru',
	forced_root_block : '',
	file_browser_callback : 'fileBrowserCallBack',
	plugins : "advlist anchor contextmenu directionality fullscreen image layer link lists media nonbreaking noneditable pagebreak paste preview responsivefilemanager save searchreplace table template textcolor visualblocks visualchars",
	relative_urls : false,
	convert_urls : false,
	paste_use_dialog : false,
	height: '300',
	width: '100%',
	image_advtab: true ,
   
	external_filemanager_path:prj_ref+"/bundles/filemanager/",
	filemanager_title:"Файловый менеджер" ,
	external_plugins: { "filemanager" : prj_ref+"/bundles/filemanager/plugin.min.js"}
});


function controlEditor(el, elementName) {
	if (el.checked)
		tinymce.execCommand('mceAddEditor', false, elementName);
	else
		tinymce.execCommand('mceRemoveEditor', false, elementName);
}