<?php 

include('wp_sql_helper.php');

function get_basic_table($table_name, $table_params, $id) {
	return get_table($table_params, get_all($table_name), $id);
}

function get_admin_table($table_name, $table_params, $id, $edit_param='', $edit_page='') {
	return get_table($table_params, get_all($table_name), $id, true, $edit_param, $edit_page);
}

function get_settings_table($table_name, $table_params, $id, $edit_param='', $edit_page='') {
  return get_table($table_params, get_all($table_name), $id, false, $edit_param, $edit_page);
}

function get_table($table_params, $items, $id, $delete_col=false, $edit_param='', $edit_page='') {
	$table = '<table id="' . $id . '" class="table table-responsive table-hover">' . get_table_header($table_params, $delete_col) . '<tbody>';
	foreach($items as $item) {
		$table .= '<tr>';
		foreach($table_params as $param) {
			$table .= '<td>';
			if($param->name == 'date') {
				$table .= format_date($event->date,'-','/');
			} else if($param->name == $edit_param && !empty($edit_page)) {
				$path = 'admin.php?page=' . $edit_page . '&id=' . $item->id;
				$table .= '<a href="' . admin_url($path) . '">' . get_object_vars($item)[$param->name] . '</a>';
			} else {
				$table .= get_object_vars($item)[$param->name];
			}
			$table .= '</td>';
		}
		if($delete_col) {
			$table .= '<td id="' . $item->id . '" class="delete"></td>';
		}
		$table .= '</tr>';
	}
	$table .= '</tbody></table>';
	return $table;
}

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

function get_basic_form($params, $id, $edit=false, $item=NULL) {
	$form = '<form id="' . $id . '">';
	foreach($params as $param) {
		$form .= '<div class="form-group">';
		$form .= '<label for="' . $param->name . '">' . $param->name . '</label>';
		if($param->name == 'description') {
			$form .= '<textarea type="text" class="form-control" id="' . $param->name . '" COLS=100 ROWS=5>';
			if($edit) {
				$form .= get_object_vars($item)[$param->name];
			}
			$form .= '</textarea>';
		} else {
			$form .= '<input type="text" class="form-control" id="' . $param->name . '"';
			if($edit) {
				$form .= ' value="' . get_object_vars($item)[$param->name] . '"';
			}
			$form .= '>';	
		}
		$form .= '</div>';
	}
	$form .= '<button type="submit" name="' . $item->id . '" id="submit_form" class="btn">Submit</button></form>';
	return $form;
}
?>