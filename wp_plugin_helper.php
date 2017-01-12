<?php
function add_rewrite($param) {
  add_rewrite_rule(
  	'^' . $param . '/([0-9]+)?',
  	'index.php?' . $param . '_id=$matches[1]',
    'top' 
  );
}

function add_query_vars($query_vars, $param) {
  $query_vars[] = $param . '_id';
  return $query_vars;
}

function direct_to_template($param, $url, $path) {
  $val = get_query_var($param);
  if($val) {
    //return $url . '?' . $param . '=' . $val;
    return $url;
  }
  return $path;
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