function searchCol(table,column,field){
	var $filter = jQuery('#'+field);
	table.column(column).search($filter.val());
	table.draw();
}

function matchCol(table,column,field){
	var $filter = jQuery('#'+field);
	table.column(column).search('^'+$filter.val()+'$',true,false);
	table.draw();
}

$.fn.select2.defaults.set( "theme", "bootstrap" );
$.fn.select2.defaults.set( "width", null );