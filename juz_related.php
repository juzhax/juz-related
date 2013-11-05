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


/** Step 2 (from text above). */
add_action( 'admin_menu', 'wp_juz_related_menu' );

/** Step 1. */
function wp_juz_related_menu() {
	add_options_page( 'Juz Related', 'Juz Related', 'manage_options', 'juz-related', 'wp_juz_related_page' );
}

function wp_juz_related_page_menu() {
	$menu = '<a href="?page=juz-related">List Items</a> | ';

	$menu .= '<a href="?page=juz-related&action=add">Add Item</a>';
	return $menu;
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
	} else if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {

?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-juz-related' ); ?></strong></p></div>
<?php

    } else {
		wp_juz_related_list();
    
    }
}


function wp_juz_related_form($action='add') {
    // Now display the settings editing screen
    if ($action == 'add') {
		$juz_related_title = $juz_related_data = '';
	}
	
	if (isset($_POST['juz_related_title']) && $_POST['juz_related_title']) {
		$juz_related_title = $_POST['juz_related_title'];
	}

	if (isset($_POST['juz_related_data']) && $_POST['juz_related_data']) {
		$juz_related_data = $_POST['juz_related_data'];
	}
	
    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'Juz Related : Add', 'menu-juz-related' ) . "</h2>";

    // settings form
    echo wp_juz_related_page_menu();
    echo '

<form name="juz_related_form" method="post" action="?page=juz-related&action='.$action.'">
<table class="form-table">
<tbody>


<tr valign="top">
<th scope="row"><label for="juz_related_title">Item Title</label></th>
<td><input name="juz_related_title" type="text" id="juz_related_title" value="'.$juz_related_title.'" class="regular-text"></td>
</tr>

<tr valign="top"><th scope="row">Item Data</th><td>
<textarea name="juz_related_data" class="large-text" cols="50" rows="20">'.$juz_related_data.'</textarea></td></tr>


</tbody></table>
<hr />
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="Add New" />
</p>

</div>
';

$temp = '

<p>Title: <br />

<input type="text" name="title" value="" size="100">
</p>
<p>
Item data: <br />
<textarea name="juz_related_item_data" id="juz_related_item_data" class="large-text code" rows="20">Put your HTML code to here</textarea>

</p>
<hr />
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="Add New" />
</p>

</form>


<table class="form-table">
<tbody><tr valign="top">
<th scope="row">Formatting</th>
<td><fieldset><legend class="screen-reader-text"><span>Formatting</span></legend>
<label for="use_smilies">
<input name="use_smilies" type="checkbox" id="use_smilies" value="1" checked="checked">
Convert emoticons like <code>:-)</code> and <code>:-P</code> to graphics on display</label><br>
<label for="use_balanceTags"><input name="use_balanceTags" type="checkbox" id="use_balanceTags" value="1"> WordPress should correct invalidly nested XHTML automatically</label>
</fieldset></td>
</tr>
<tr valign="top">
<th scope="row"><label for="default_category">Default Post Category</label></th>
<td>
<select name="default_category" id="default_category" class="postform">
	<option class="level-0" value="2">Blog</option>
	<option class="level-0" value="11">Joke</option>
	<option class="level-0" value="10">Linux</option>
	<option class="level-0" value="1" selected="selected">Uncategorized</option>
</select>
</td>
</tr>
<tr valign="top">
<th scope="row"><label for="default_post_format">Default Post Format</label></th>
<td>
	<select name="default_post_format" id="default_post_format">
		<option value="0">Standard</option>
		<option value="aside">Aside</option>
		<option value="chat">Chat</option>
		<option value="gallery">Gallery</option>
		<option value="link">Link</option>
		<option value="image">Image</option>
		<option value="quote">Quote</option>
		<option value="status">Status</option>
		<option value="video">Video</option>
		<option value="audio">Audio</option>
	</select>
</td>
</tr>

</tbody></table>

<textarea name="ping_sites" id="ping_sites" class="large-text code" rows="3">http://rpc.pingomatic.com/</textarea>





	';
	
	}

function wp_juz_related_list() {
	global $wpdb;
	
	$myrows = $wpdb->get_results( "SELECT * FROM wp_juz_related_id;" );
//	print_r($myrows);
	
	

    echo '<div class="wrap">';
    echo "<h2>" . __( 'Juz Related : List', 'menu-juz-related' ) . "</h2>";
    echo wp_juz_related_page_menu();


	echo "<table><tr><th>";
	echo "ID</th><th>Name</th><th>Date</th><th>Action</th>";
	echo "</tr><tr>";
	echo "<td><td></td><td></td><td></td>";
	echo "</td></tr></table>";
	echo "</div>";	

}




function wp_juz_related() {
//	global $wp_query;
	
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





?>