<?php
/*
  A group of helper methods intended for use with database connections 
  in Wordpress plugins.
*/

/*
	An object representing a field in a DB table. 
 */
class TableField {
  public $name; // the name of the field
  public $sql;  // its SQL initializer
  public $unique_field; // is this field unique
  
  function __construct($namey, $sqly, $unique=false) {
    $this->name = $namey;
    $this->sql = $sqly;
    $this->unique_field = $unique;
  }
}


/* 
	Creates a table with the given name, if one doesn't exist already.
 */
function create_table($table_name, $table_params, $auto_id=false) {
	global $wpdb;
	$wpdb->show_errors();
	if(!table_exists($table_name)) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = 'CREATE TABLE ' . $table_name . ' (' . table_sql($table_params, $auto_id) . ');';
		dbDelta($sql);
	}
}

/* 
	Drops the table with the given name, if it exists.
 */
function drop_table($table_name) {
	return do_query('DROP TABLE IF EXISTS ' . $table_name . ';');
}

/*
  Creates a SQL statement for initialization of the table columns.
 */
function table_sql($table_params, $auto_id=true) {
	$sql = '';
	if($auto_id) {
		$sql .= 'id int NOT NULL AUTO_INCREMENT,\n';
	}
	foreach($table_params as $param) {
		$sql .= $param->name . ' ' . $param->sql . ',\n';
	}
	foreach($table_params as $param) {
		if($param->unique_field) {
			$sql .= 'UNIQUE  (' . $param->name . '),\n';
		}
	}
	if($auto_id) {
		$sql .= 'PRIMARY KEY  (id)';
	} else {
		$sql = substr($sql, 0, -2);
	}
	return $sql;
}

/* 
  Returns true if the given table already exists in the wordpress database.
 */
function table_exists($table_name) {
	global $wpdb;
	return $wpdb->get_var('SHOW TABLES LIKE "$table_name"') == $table_name;
}

/* 
  Adds to the table with the given parameters and data (which should be a map
  from parameter names to values).
 */
function save_table_item($table_name, $table_params, $data) {
	global $wpdb;
	$insert = '';
	$vals = '';
	foreach($table_params as $param) {
		$insert .= $param->name . ',';
		if($param->name == 'date') {
	    	$data['date'] = tec_format_date(sanitize_text_field($data['date']),'/','-');
		}
		$vals .= '"' . sanitize_text_field($data[$param->name]) . '",';
	}
	$vals = substr($vals,0,-1);
	$insert = substr($insert,0,-1);
	$query = 'INSERT INTO ' . $table_name . ' (' . $insert . ') VALUES (' . $vals . ');';
	return do_query($query);
}

/* 
  Updates the table with the given parameters and data (which should be a map
  from parameter names to values).
 */
function update_table_item($table_name, $table_params, $data) {
	$id = sanitize_text_field($data['id']);
	$vals = '';
	foreach($table_params as $param) {
		if($param->name == 'date') {
	    	$data['date'] = tec_format_date(sanitize_text_field($data['date']),'/','-');
		}
		$vals .= $param->name . '="' . sanitize_text_field($data[$param->name]) . '",';
	}
	$vals = substr($vals,0,-1);
	$query = 'UPDATE ' . $table_name . ' SET ' . $vals . ' WHERE ID=' . $id . ';';
	return do_query($query);
}

/* 
  Returns all entries in the table.
 */
function get_all($table_name) {
	return get_results('SELECT * FROM ' . $table_name . ';');
}

/* 
  Returns all entries in the table, ordered by date.
 */
function get_all_by_date($table_name) {
	return get_results('SELECT * FROM ' . $table_name . ' ORDER BY date ASC;');
}

/* 
  Returns the num_items most recent entries from the table.
 */
function get_recent_items($table_name, $num_items) {
	return get_results('SELECT * FROM ' . $table_name . ' WHERE date >= DATE_FORMAT(NOW(),"%Y-%m-%d") ORDER BY date ASC LIMIT ' . $num_items . ';');
}

/* 
  Returns the item associated with the given id.
 */
function get_item_by_id($table_name, $id) {
	return get_results('SELECT * FROM ' . $table_name . ' WHERE id=' . $id . ';')[0];
}

/* 
  Returns the items associated with the given parameter.
 */
function get_items_by_param($table_name, $param_name, $param_value) {
	return get_results('SELECT value FROM ' . $table_name . ' WHERE ' . $param_name . '="' . $param_value . '";');
}

/* 
  Returns the first item associated with the given parameter.
 */
function get_item_by_param($table_name, $param_name, $param_value) {
	return get_items_by_param($table_name, $param_name, $param_value)[0];
}

/* 
  Returns the number of items in the table.
 */
function get_table_count($table_name) {
	$result = get_results('SELECT COUNT(*) as the_count FROM ' . $table_name . ';');
	return $result[0]->the_count;
}

/* 
  Deletes from the table any entry that matches the data provided.
 */
function delete_table_item($table_name, $data) {
	$id = $post_data['id'];
	$query = 'DELETE FROM ' . $table_name . ' WHERE id=' . $id . ';';
	return do_query($query);
}

/* 
  Helper function for getting results from the DB.
 */
function get_results($query) {
	global $wpdb;
	return $wpdb->get_results($query);	
}

/* 
  Helper function for querying the DB.
 */
function do_query($query) {
	global $wpdb;
	return $wpdb->query($query);
}

/* 
  Helper function for formatting dates. 
 */
function format_date($dateString, $new_format, $format='Y-m-d') {
	$old_date = DateTime::createFromFormat($format, $dateString);
	return $old_date->format($new_format);
}

?>