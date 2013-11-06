<?php
/*
Plugin Name: Juz Related
Plugin URI: http://www.juzhax.com/
Description: Put Custom HTML code to the specified posts by id.
Version: 1.0
Author: Justin Soo ( JuzHax )
Author URI: http://www.juzhax.com/
License: GPL2

CREATE TABLE IF NOT EXISTS `wp_juz_related_id` (
  `post_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  UNIQUE KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `wp_juz_related_item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_title` varchar(255) NOT NULL,
  `item_data` text NOT NULL,
  `item_update` datetime NOT NULL,
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


*/

add_action( 'admin_menu', 'wp_juz_related_menu' );

function wp_juz_related_menu() {
	add_options_page( 'Juz Related', 'Juz Related', 'manage_options', 'juz-related', 'wp_juz_related_page' );
}

function wp_juz_related_header($notice) {
/*
	$header .= '<a href="?page=juz-related">List Items</a> | ';
	$header .= '<a href="?page=juz-related&action=add">Add Item</a>';
*/	
	$header = '<h2>Juz Related <a href="?page=juz-related&action=add" class="add-new-h2">Add New</a></h2>';
	$header .= $notice;
/*	
	$header .= '
<div>
<ul class="subsubsub">
	<li class="all"><a href="edit-comments.php?comment_status=all" class="current">All</a> |</li>
	<li class="moderated"><a href="edit-comments.php?comment_status=moderated">Pending <span class="count">(<span class="pending-count">0</span>)</span></a> |</li>
	<li class="approved"><a href="edit-comments.php?comment_status=approved">Approved</a> |</li>
	<li class="spam"><a href="edit-comments.php?comment_status=spam">Spam <span class="count">(<span class="spam-count">2</span>)</span></a> |</li>
	<li class="trash"><a href="edit-comments.php?comment_status=trash">Trash <span class="count">(<span class="trash-count">0</span>)</span></a></li>
</ul>
</div>
';
*/

	$temp = '
<h2>Posts <a href="http://juzhax.com/wp-admin/post-new.php" class="add-new-h2">Add New</a></h2>';		

	return $header;
}

/** Step 3. */
function wp_juz_related_page() {
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $opt_name = 'mt_favorite_color';
    $hidden_field_name = 'mt_submit_hidden';
    $data_field_name = 'mt_favorite_color';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
		
	if( isset($_GET['action']) && $_GET['action'] == 'edit') {
		wp_juz_related_form('edit');
	} elseif( isset($_GET['action']) && $_GET['action'] == 'add') {
		wp_juz_related_form('add');
    } else {
		wp_juz_related_list();
    
    }
}


function wp_juz_related_form($action='add') {
    // Now display the settings editing screen
    global $wpdb;
 	$wpdb->show_errors(); 

    $error = $notice = $output = '';
    $success = 0;
    if ($action == 'add') {
		$juz_related_title = $juz_related_data = $juz_related_post_id = '';
	} elseif ($action == 'edit' && isset($_GET['id']) && is_numeric($_GET['id']) && !isset($_POST['juz_related'])) {
		//$mylink = $wpdb->get_row("SELECT * FROM $wpdb->links WHERE link_id = 10");
		$wp_juz_related_item = $wpdb->get_row("SELECT * from wp_juz_related_item WHERE item_id = ".$_GET['id'], ARRAY_A);
		$juz_related_title = $wp_juz_related_item['item_title'];
		$juz_related_data = $wp_juz_related_item['item_data'];
		
		$juz_related_id = array();
		$wp_juz_related_id = $wpdb->get_results("SELECT post_id from wp_juz_related_id WHERE item_id = ".$_GET['id'], ARRAY_A);
		foreach ($wp_juz_related_id as $id) {
			$juz_related_id[] = $id['post_id'];
		}
		$juz_related_post_id = implode(',', $juz_related_id);
		
	}
	
	if (isset($_POST['juz_related_title']) && $_POST['juz_related_title'] != '') {
		$juz_related_title = stripcslashes($_POST['juz_related_title']);
	} else	if (isset($_POST['juz_related_title']) && $_POST['juz_related_title'] == '') {
		$error = 'Item title cannot be empty.';
	}

	if (isset($_POST['juz_related_post_id']) && $_POST['juz_related_post_id']) {
		$juz_related_post_id = $_POST['juz_related_post_id'];
		$post_ids = split(',',$juz_related_post_id);
		foreach ($post_ids as $post_id) {
			$post_id = trim($post_id);
			if (!is_numeric($post_id)) {
				$error .= 'Illegal Post ID found';
				break;
			}
		}
	}



	if (isset($_POST['juz_related_data']) && $_POST['juz_related_data']) {
		$juz_related_data = stripcslashes($_POST['juz_related_data']);
	}

	if ($error == '' && isset($_POST['juz_related']) && $_POST['juz_related'] == 'post') {
		$success = 1;
	}
	

    if ($error != '') {
		$notice = '<div class="error"><p><strong>'.$error.'</strong></p></div>';
    } 
    
    if ($success == 1) {
    	if ($action == 'add') {
	
			$wpdb->insert( 
				'wp_juz_related_item', 
				array( 
					'item_title' => $juz_related_title, 
					'item_data' => $juz_related_data,
					'item_update' => current_time('mysql', 1),
				), 
				array( 
					'%s', 
					'%s',
					'%s' 
				) 
			);   
			$current_id = $wpdb->insert_id;
		} elseif ($action == 'edit') {
			$current_id = $_GET['id'];
				$wpdb->update( 
				'wp_juz_related_item', 
				array( 
					'item_title' => $juz_related_title, 
					'item_data' => $juz_related_data,
					'item_update' => current_time('mysql', 1),
				), 
				array('item_id' => $current_id),
				array( 
					'%s', 
					'%s',
					'%s' 
				),
				array (
					'%d',
				) 
			);  		
		}
		foreach ($post_ids as $post_id) {
			$post_id = trim($post_id);
			$wpdb->query(
				'
				INSERT INTO wp_juz_related_id (post_id, item_id)
				VALUES ('.$post_id.', '.$current_id.')
				ON DUPLICATE KEY 
				UPDATE 
				item_id = '.$current_id.';
				'
			);
		}
		
		
		$wpdb->print_error();
		$notice = '';
		if ($action == 'add') {

			$notice = '<div class="updated"><p><strong>Added successful !</strong></p></div>';
			$notice .= '<a href="?page=juz-related">Back to List</a>';
			
		} elseif ($action == 'edit') {
			$notice = '<div class="updated"><p><strong>Updated successful !</strong></p></div>';
		
		}


		
	}
	

	
    $output .= '<div class="wrap">';
    if ($action == 'add') {
		$output .= '<h2>Juz Related : Add New</h2>';
	} elseif ($action == 'edit') {
		$output .= '<h2>Juz Related : Edit</h2>';
		
	}
	$output .= $notice;    
//    $output .= wp_juz_related_header($notice);
	
	if ($success == 0 || $action == 'edit') {
	    
	    if ($action == 'add') {
		    $output .= '<form name="juz_related_form" method="post" action="?page=juz-related&action='.$action.'">';
		} elseif ($action == 'edit') {
		    $output .= '<form name="juz_related_form" method="post" action="?page=juz-related&action='.$action.'&id='.$_GET['id'].'">';
		
		}    
	    
		$output .= '

<table class="form-table">
<tbody>


<tr valign="top">
<th scope="row"><label for="juz_related_title">Item Title</label></th>
<td><input name="juz_related_title" type="text" id="juz_related_title" value="'.$juz_related_title.'" class="regular-text" autocomplete="off"></td>
</tr>

<tr valign="top">
<th scope="row"><label for="juz_related_post_id">Post ID</label></th>
<td><input name="juz_related_post_id" type="text" id="juz_related_post_id" value="'.$juz_related_post_id.'" class="regular-text" autocomplete="off">
<p class="description">e.g.: 234,545,7676,78454,6565,654343,54656</p></td>
</tr>


<tr valign="top"><th scope="row">Item Data</th><td>
<textarea name="juz_related_data" class="large-text" cols="50" rows="20">'.$juz_related_data.'</textarea></td></tr>


</tbody></table>
<hr />
<p class="submit">
';
	if ($action == 'add') {
		$output .= '<input type="submit" name="Submit" class="button-primary" value="Add New" />';
	} elseif ($action == 'edit') {
		$output .= '<input type="submit" name="Submit" class="button-primary" value="Update" />';
		
	}
	$output .= '</p>
<input type="hidden" name="juz_related" value="post" />
</div>
</form>
';
	}

	echo $output;	
}

if(!class_exists('WP_List_Table')) :
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
endif;

class Juz_Related_List_Table extends WP_List_Table {
	var $juz_related_items;
	

	function get_columns(){
		$columns = array(
			'juz_related_title' => 'Title',
			'juz_related_post_ids'    => 'Post IDs',
			'juz_related_update'      => 'Update'
		);
		return $columns;
	}

	function prepare_items() {
	
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->juz_related_items;
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'juz_related_title':
				
				return '<a href="?page=juz-related&action=edit&id='.$item[ 'item_id' ].'">'.$item[ 'item_title' ].'</a>';
			case 'juz_related_post_ids':
				$post_ids = wp_juz_related_get_post_ids($item[ 'item_id' ]);
				$return_ids = array();
				foreach ($post_ids as $post_id) {
					$post_info = get_post($post_id['post_id'],ARRAY_A);
					if (isset($post_info['post_type']) && $post_info['post_type'] == 'post') {
						$return_ids[] = '<a href="/?p='.$post_id['post_id'].'">'.$post_id['post_id'].'</a>';
					} else {
						$return_ids[] = $post_id['post_id'];
					}
				}
				$return_ids = implode(',', $return_ids);
				return $return_ids;
			case 'juz_related_update':
				return $item[ 'item_update' ];
		default:
			// return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
}

function wp_juz_related_get_post_ids($item_id) {
	global $wpdb;
	if (is_numeric($item_id)) {
		return $wpdb->get_results( "SELECT * FROM wp_juz_related_id WHERE item_id = $item_id;", ARRAY_A );
	}
}

function wp_juz_related_list() {
	global $wpdb;

	$JuzRelatedTable = new Juz_Related_List_Table();	

    echo '<div class="wrap">';
    echo wp_juz_related_header();
    $JuzRelatedTable->juz_related_items = $wpdb->get_results( "SELECT * FROM wp_juz_related_item ORDER BY item_update DESC;", ARRAY_A );
	$JuzRelatedTable->prepare_items(); 
	$JuzRelatedTable->display(); 
	echo '</div>'; 
}

function wp_juz_related() {
	global $wpdb;
	$content = '';
	if (is_single()) {
		$get_the_ID = get_the_ID();
		$wp_juz_data = $wpdb->get_row('SELECT post_id, `wp_juz_related_id`.item_id, item_data
					FROM `wp_juz_related_id`
					LEFT JOIN `wp_juz_related_item`
					ON `wp_juz_related_id`.item_id =`wp_juz_related_item`.item_id
					WHERE post_id = '.$get_the_ID.';',ARRAY_A);

		if (isset($wp_juz_data['item_data'])) {
			$content = $wp_juz_data['item_data'];
		}
	} 
	return $content;
}
?>