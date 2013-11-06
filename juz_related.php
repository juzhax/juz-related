<?php
/*
Plugin Name: Juz Related
Plugin URI: http://www.juzhax.com/
Description: Put Custom HTML code to the specified posts by id.
Version: 1.0
Author: Justin Soo ( JuzHax )
Author URI: http://www.juzhax.com/
License: GPL2
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
//	global $wp_query;
	global $wpdb;
	
	$content = '';
	$wp_juz = array();
	$wp_juz_post_id = array (9,280,210,229,249,266,1999);
	
	foreach ($wp_juz_post_id as $post_id) {
		$wp_juz_group[$post_id] = 'icomania';
	}	
	
	
	$wp_juz_data['icomania'] = '
	<h3>Other Language for Icomania Answers:</h3>
	<ul>
	<li><a title="Icomania Answers" href="http://game-solver.com/icomania-answers/">Icomania Answers</a></li>
	<li><a title="Icomania Antwoorden Nederlands" href="http://game-solver.com/icomania-antwoorden-nederlands/">Icomania Antwoorden Nederlands</a></li>
	<li><a title="Icomania astuce soluce Français" href="http://game-solver.com/icomania-astuce-soluce-francais/">Icomania astuce soluce Français</a></li>
	<li><a title="Icomania Lösung Deutsch" href="http://game-solver.com/icomania-deutsch-losung/">Icomania Lösung Deutsch</a></li>
	<li><a title="Icomania Respuestas Soluciones Espanol" href="http://game-solver.com/icomania-respuestas-soluciones-espanol/">Icomania Respuestas Soluciones Espanol</a></li>
	<li><a title="Icomania Soluzioni Italiano" href="http://game-solver.com/icomania-soluzioni-italiano/">Icomania Soluzioni Italiano</a></li>
	<li><a title="Icomania ответы" href="http://game-solver.com/icomania-%d0%be%d1%82%d0%b2%d0%b5%d1%82%d1%8b/">Icomania ответы</a></li>
	</ul>';

	$wp_juz_post_id = array (6709,3762,2916,3414,6686,6622,8069,6637,3014,3455,3086,8032,6766,6651,3776,8055);
	foreach ($wp_juz_post_id as $post_id) {
		$wp_juz_group[$post_id] = 'higuess';
	}	

	
	$wp_juz_data['higuess'] = '
	<h3>Other Hi Guess Games Answers:</h3>
<ul>
	<li><a title="Hi Guess the Basketball Answers" href="http://game-solver.com/hi-guess-the-basketball-answers/">Hi Guess the Basketball Answers</a></li>
	<li><a title="Hi Guess the Brand Answers" href="http://game-solver.com/hi-guess-the-brand-answers/">Hi Guess the Brand Answers</a></li>
	<li><a title="Hi Guess the Character Answers" href="http://game-solver.com/hi-guess-the-character-answers/">Hi Guess the Character Answers</a></li>
	<li><a title="Hi Guess the Emoji Answers" href="http://game-solver.com/hi-guess-the-emoji-answers/">Hi Guess the Emoji Answers</a></li>
	<li><a title="Hi Guess the Food Answers" href="http://game-solver.com/hi-guess-the-food-answers/">Hi Guess the Food Answers</a></li>
	<li><a title="Hi Guess the Football Star Answers" href="http://game-solver.com/hi-guess-the-football-star-answers/">Hi Guess the Football Star Answers</a></li>
	<li><a title="Hi Guess the Games Answers" href="http://game-solver.com/hi-guess-the-games-answers/">Hi Guess the Games Answers</a></li>
	<li><a title="Hi Guess the Movie Answers" href="http://game-solver.com/hi-guess-the-movie-answers/">Hi Guess the Movie Answers</a></li>
	<li><a title="Hi Guess the Pic Answers" href="http://game-solver.com/hi-guess-the-pic-answers/">Hi Guess the Pic Answers</a></li>
	<li><a title="Hi Guess the Place Answers" href="http://game-solver.com/hi-guess-the-place-answers/">Hi Guess the Place Answers</a></li>
	<li><a title="Hi Guess the Restaurant Answers" href="http://game-solver.com/hi-guess-the-restaurant-answers/">Hi Guess the Restaurant Answers</a></li>
	<li><a title="Hi Guess the Riddle Answers" href="http://game-solver.com/hi-guess-the-riddle-answers/">Hi Guess the Riddle Answers</a></li>
	<li><a title="Hi Guess the Show Answers" href="http://game-solver.com/hi-guess-the-show-answers/">Hi Guess the Show Answers</a></li>
	<li><a title="Hi Guess the TV Show Answers" href="http://game-solver.com/hi-guess-the-tv-show-answers/">Hi Guess the TV Show Answers</a></li>
	<li><a title="Hi Guess the View Answers" href="http://game-solver.com/hi-guess-the-view-answers/">Hi Guess the View Answers</a></li>
	<li><a title="Hi Guess Who Answers" href="http://game-solver.com/hi-guess-who-answers/">Hi Guess Who Answers</a></li>
</ul>
';	

	$wp_juz_post_id = array (7722,7725,7728,7731,7734,7737,7741,7744,7747,7750,7753,7756,7759,7762,7765,7768);
	foreach ($wp_juz_post_id as $post_id) {
		$wp_juz_group[$post_id] = 'flowfree';
	}	

	$wp_juz_data['flowfree'] = '
	<h3>All others Flow Free Solutions:</h3>
	
<ul>
	<li><a title="Flow Regular Pack Solutions" href="http://game-solver.com/flow-regular-pack-solutions/">Flow Regular Pack Solutions</a></li>
	<li><a title="Flow Bonus Pack Solutions" href="http://game-solver.com/flow-bonus-pack-solutions/">Flow Bonus Pack Solutions</a></li>
	<li><a title="Flow Green Pack Solutions" href="http://game-solver.com/flow-green-pack-solutions/">Flow Green Pack Solutions</a></li>
	<li><a title="Flow Blue Pack Solutions" href="http://game-solver.com/flow-blue-pack-solutions/">Flow Blue Pack Solutions</a></li>
	<li><a title="Flow Bridge Pack Solutions" href="http://game-solver.com/flow-bridge-pack-solutions/">Flow Bridge Pack Solutions</a></li>
	<li><a title="Flow 7×7 Mania Solutions" href="http://game-solver.com/flow-7x7-mania-solutions/">Flow 7×7 Mania Solutions</a></li>
	<li><a title="Flow 8×8 Mania Solutions" href="http://game-solver.com/flow-8x8-mania-solutions/">Flow 8×8 Mania Solutions</a></li>
	<li><a title="Flow 9×9 Mania Solutions" href="http://game-solver.com/flow-9x9-mania-solutions/">Flow 9×9 Mania Solutions</a></li>
	<li><a title="Flow 10×10 Mania Solutions" href="http://game-solver.com/flow-10x10-mania-solutions/">Flow 10×10 Mania Solutions</a></li>
	<li><a title="Flow 11×11 Mania Solutions" href="http://game-solver.com/flow-11x11-mania-solutions/">Flow 11×11 Mania Solutions</a></li>
	<li><a title="Flow 12×12 Mania Solutions" href="http://game-solver.com/flow-12x12-mania-solutions/">Flow 12×12 Mania Solutions</a></li>
	<li><a title="Flow Rainbow Pack Solutions" href="http://game-solver.com/flow-rainbow-pack-solutions/">Flow Rainbow Pack Solutions</a></li>
	<li><a title="Flow Kids Pack Solutions" href="http://game-solver.com/flow-kids-pack-solutions/">Flow Kids Pack Solutions</a></li>
	<li><a title="Flow Jumbo Pack Solutions" href="http://game-solver.com/flow-jumbo-pack-solutions/">Flow Jumbo Pack (iPad) Solutions</a></li>
	<li><a title="Flow Purple Pack Solutions" href="http://game-solver.com/flow-purple-pack-solutions/">Flow Purple Pack (iPad) Solutions</a></li>
	<li><a title="Flow Pink Pack Solutions" href="http://game-solver.com/flow-pink-pack-solutions/">Flow Pink Pack (iPad) Solutions</a></li>
</ul>
';	

	
	if (is_single()) {
		$get_the_ID = get_the_ID();
		$wp_juz_data = $wpdb->get_row('SELECT post_id, `wp_juz_related_id`.item_id, item_data
					FROM `wp_juz_related_id`
					LEFT JOIN `wp_juz_related_item`
					ON `wp_juz_related_id`.item_id =`wp_juz_related_item`.item_id
					WHERE post_id = '.$get_the_ID.';',ARRAY_A);
		echo '<pre>';
		print_r($wp_juz_data);
		echo '</pre>';

		if (isset($wp_juz_group[$get_the_ID])) {
			$content .= $wp_juz_data[$wp_juz_group[$get_the_ID]];
//			$content = $get_the_ID;
//			print_r($wp_juz[$get_the_ID]);
/*
			$content = '<h3>Other Related :</h3>';
			$content .= '<ul>';
			foreach ($wp_juz[$wp_juz_group[$get_the_ID]]['data'] as $data) {
				$content .= '<li><a href="'.$data['url'].'" title="'. $data['title'].'">'. $data['title'].'</a></li>';
			}
			$content .= '</ul>';
*/			
		}
	
	} 
	return $content;
}


echo get_the_ID();
wp_juz_related();



?>