<?php 

/*
  A group of helper methods intended for use with displaying database 
  information (and other miscellaneous objects) in Wordpress plugins.
*/

// include database helper functions
include('wp_sql_helper.php');

/*
  Helper function that returns a string containing an HTML table holding
  all entries in the given table. The "basic" table includes all fields 
  in the DB table.
*/
function get_basic_table($table_name, $table_params, $html_id) {
	return get_table($table_params, get_all($table_name), $html_id);
}

/*
  Helper function that returns a string containing an HTML table holding
  all entries in the given table, plus an optional parameter that can be made
  a link to an edit page for the entry. By default, includes a column that allows
  entries to be deleted from the table. 
*/
function get_admin_table($table_name, $table_params, $html_id, $edit_param='', $edit_page='') {
	return get_table($table_params, get_all($table_name), $html_id, true, $edit_param, $edit_page);
}

/*
  Helper function that returns a string containing an HTML table holding
  all entries in the given table, plus an optional parameter that can be made
  a link to an edit page for the entry but does not include the option to 
  delete entries (if the table contains plugin settings, even the admin shouldn't
  be able to just delete settings).
*/
function get_settings_table($table_name, $table_params, $html_id, $edit_param='', $edit_page='') {
  return get_table($table_params, get_all($table_name), $html_id, false, $edit_param, $edit_page);
}

/*
  Helper function that returns a string containing an HTML table holding
  all entries in the given table, configurable via the following parameters:
  -$table_params: the table fields to include in the HTML table
  -$items: the table items to display
  -$html_id: the ID to place on the HTML table
  -$delete_col: (optional) whether or not to include a column allowing the user to delete an entry
  -$edit_param: (optional) which parameter to add an "edit" link to
  -$edit_page: (optional) the page to link to in order to edit the entry
  
  Uses Bootstrap classes for styling.
*/
function get_table($table_params, $items, $html_id, $delete_col=false, $edit_param='', $edit_page='') {
	$table = '<table id="' . $html_id . '" class="table table-responsive table-hover">' . get_table_header($table_params, $delete_col) . '<tbody>';
	foreach($items as $item) {
		$table .= '<tr>';
		$item = get_object_vars($item);
		foreach($table_params as $param) {
			$table .= '<td>';
			if($param->name == 'date') {
				$table .= format_date($item[$param->name],'m/d/Y');
			} else if($param->name == $edit_param && !empty($edit_page)) {
				$table .= get_edit_link($item[$param->name], $item['id'], $edit_page);
			} else {
				$table .= $item[$param->name];
			}
			$table .= '</td>';
		}
		if($delete_col) {
			$table .= '<td id="' . $item['id'] . '" class="delete"></td>';
		}
		$table .= '</tr>';
	}
	$table .= '</tbody></table>';
	return $table;
}

/*
  Wraps the given name with a link to an admin edit page for a table item.
*/
function get_edit_link($name, $id, $edit_page) {
	$rel_path = 'admin.php?page=' . $edit_page . '&id=' . $id;
	return '<a href="' . admin_url($rel_path) . '">' . $name . '</a>';
}

/*
  Creates a <thead> header string for an HTML table.
*/
function get_table_header($table_params, $delete) {
  $header = '<thead>';
	foreach($table_params as $param) {
		$header .= '<th>' . $param->name . '</th>';
	}
	if($delete) {
		$header .= '<th>Delete?</th>';
	}
	$header .= '</thead>';
	return $header;
}

/*
  Returns a basic form (styled with Bootstrap) for adding or updating a
  table item. 
*/
function get_basic_form($params, $form_id, $edit=false, $item=NULL) {
	$form = '<form id="' . $form_id . '">';
	foreach($params as $param) {
		$form .= '<div class="form-group">';
		$form .= '<label for="' . $param->name . '">' . $param->name . '</label>';
		$value = '';
		if($edit) {
			if($param->name == 'date') {
				$value .= format_date($item[$param->name],'m/d/Y');
			} else {
				$value .= $item[$param->name];
			} 
		}
		if($param->name == 'description') {
			$form .= wrap_with_textarea($param->name, $value);
		} else {
			$form .= wrap_with_input($param->name, $value);
		}
		$form .= '</div>';
	}
	$form .= '<button type="submit" name="' . $item->id . '" id="submit_form" class="btn">Submit</button></form>';
	return $form;
}

function wrap_with_textarea($id, $value, $cols=100, $rows=5) {
	return '<textarea type="text" class="form-control" id="' . $id . '" COLS=' . $cols . ' ROWS=' . $rows .'>' . $value . '</textarea>';
}

function wrap_with_input($id, $value) {
	return '<input type="text" class="form-control" id="' . $id . '" value="' . $value . '">';
}

function create_page($page_title, $page_content) {
	$page = get_page_by_title($page_title);
	if(!$page) {
	  $page_id = create_content($page_title, $page_content, 'page');
	} else {
		$page_id = $page->ID;
	  $page->post_status = 'publish';
	  $page_id = wp_update_post($page);
	}
	return $page_id;
}

function create_content($title, $content, $type = 'post') {
	$_p = array();
	$_p['post_title'] = $title;
	$_p['post_content'] = $content;
	$_p['post_status'] = 'publish';
	$_p['post_type'] = $type;
	$_p['comment_status'] = 'closed';
	$_p['ping_status'] = 'closed';
	$page_id = wp_insert_post($_p);
	return $page_id;
}

?>