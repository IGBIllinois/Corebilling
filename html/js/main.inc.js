function escapeRegExp(text) {
  return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
}

function searchCol(table,column,field){
	var $filter = jQuery('#'+field);
	table.column(column).search(escapeRegExp($filter.val()));
	table.draw();
}

function matchCol(table,column,field){
	var $filter = jQuery('#'+field);
	if($filter.val() == ""){
		table.column(column).search("");
	} else {
		table.column(column).search('^'+escapeRegExp($filter.val())+'$',true,false);
	}
	table.draw();
}

function confirm_group_delete() {
        return confirm("Are you sure want to delete this group?");
}

function confirm_regenerate_key() {
	return confirm("Are you sure you want to regenerate device key?  This will cause an existing device to not work till new key is input");
}

$.fn.select2.defaults.set( "theme", "bootstrap" );
$.fn.select2.defaults.set( "width", null );
