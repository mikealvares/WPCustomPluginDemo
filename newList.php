<?php
/*
Plugin Name: Custom List Plugin
Description: Add custom post type for list 
Author: Michael Alvares
*/
// Custom Post Type
function ma_custom_post_newList() {
	$labels = array(
		'name'               => __( 'NewLists' ),
		'singular_name'      => __( 'NewList' ),
		'add_new'            => __( 'Add New List' ),
		'add_new_item'       => __( 'Add New List' ),
		'edit_item'          => __( 'Edit List' ),
		'new_item'           => __( 'New List' ),
		'all_items'          => __( 'All List' ),
		'view_item'          => __( 'View Lists' ),
		'search_items'       => __( 'Search Lists' ),
		'not_found'          => __( 'No Lists found' ),
		'not_found_in_trash' => __( 'No Lists found in trash' ),
		'featured_image'     => 'Image',
		'set_featured_image' => 'Add Image'
	);
	$args = array(
		'labels'            => $labels,
		'description'       => 'Holds List specific data',
		'public'            => true,
		'menu_position'     => 5,
		'rewrite'           => array( 'slug' => 'newlist' ),
		'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'has_archive'       => true,
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'has_archive'       => true,
		'query_var'         => 'newlist',
		'register_meta_box_cb' => 'ma_add_newList_metaboxes',
	);
	register_post_type( 'newlist', $args);
}
// Custom Meta Boxes
function ma_add_newList_metaboxes() {
	add_meta_box(
		'ma_newList_url',
		'URL',
		'ma_newList_url',
		'newlist',
		'side',
		'default'
	);
	add_meta_box(
		'ma_newList_pos',
		'List Position',
		'ma_newList_pos',
		'newList',
		'side',
		'default'
	);
}
function ma_newList_url() {
	global $post;
	wp_nonce_field( basename( __FILE__ ), 'newlist_fields' );
	$url = get_post_meta( $post->ID, 'url', true );
	echo '<input type="text" name="url" value="' . esc_textarea( $url )  . '" class="widefat">';
}
function ma_newList_pos() {
	global $post;
	wp_nonce_field( basename( __FILE__ ), 'newlist_fields' );
	$position = get_post_meta( $post->ID, 'position', true );
	$SBOX = '<select name="position" class="widefat">';
	for($i=1;$i<11;$i++){
		$SEL = "";
		if($position==$i){ $SEL=' selected="selected" ';}
		$SBOX .= '<option value="'.$i.'" '.$SEL.'>'.$i.'</option>';
	}
	$SBOX .='</select>';
	echo $SBOX;
}
// Save the meta box
function ma_save_newList_meta( $post_id, $post ) {
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	if ( ! isset( $_POST['position'] ) || ! isset( $_POST['url'] ) || ! wp_verify_nonce( $_POST['newlist_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}
	$events_meta['position'] = esc_textarea( $_POST['position'] );
	$events_meta['url'] = esc_textarea( $_POST['url'] );
	foreach ( $events_meta as $key => $value ) :
		if ( 'revision' === $post->post_type ) {
			return;
		}
		if ( get_post_meta( $post_id, $key, false ) ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			add_post_meta( $post_id, $key, $value);
		}
		if ( ! $value ) {
			delete_post_meta( $post_id, $key );
		}
	endforeach;
}
// Create a shortcode for the post type
function newList_shortcode( $atts ) {
	$layout = shortcode_atts( array('lay' => 'h'), $atts );
	$args = array(
		'post_type' => 'newlist',
		'post_status' => 'publish',
		'meta_key' => 'position',
    	'orderby' => 'meta_value_num',
    	'order' => 'ASC'
	);
	$string = '<div class="row">';
    $query = new WP_Query( $args );
	if( $query->have_posts() ){
		while( $query->have_posts() ){
			$query->the_post();
			$position 	= get_post_meta( get_the_ID(), 'position', true );
			$url 		= get_post_meta( get_the_ID(), 'url', true );
			$post_thumbnail_id = get_post_thumbnail_id( get_the_ID());
			if ( ! $post_thumbnail_id ) {$img='';}
			$img = wp_get_attachment_image_url( $post_thumbnail_id, $size );
			if($layout['lay']=='h' || $layout['lay']=='H'){
				$string .='<div class="col-md-3" style="margin-bottom:10px;">';
				$string .='<div style="background-color:green;color:#fff;padding:5px;font-weight:bold;">#'.$position.' '.get_the_title().'</div>';
				if($img !=""){
					$string .='<div align="center"><img src="'.$img.'" style="height:100px;" /></div>';
				}else{
					$string .='<div align="center" style="height:100px;">&nbsp;</div>';
				}				
				$string .='<div class="row" align="left">';
				$string .='<div class="col-md-12" align="center" style="height:100px">';
				$string .='<strong>'.get_the_content().'</strong><br />';
				$string .='</div></div><div class="row" style="height:40px;"><div class="col-md-6" align="left" style="line-height:40px;">';
				$string .='<a data-toggle="modal" data-target="#review'.get_the_ID().'">Reviews</a>';
				$string .='</div><div class="col-md-6" align="left">';
				$string .='<a href="'.$url.'" class="btn btn-warning btnBet" target="_blank" style="text-decoration:none;">Click to view</a>';
				$string .='</div>';
				$string .='</div>';
				$string .='</div>';
			}else{
				$string .='<div class="col-md-12 row" style="height:65px;border-bottom:2px solid #000;padding-top:2px;">';
				$string .='<div class="col-sm-1" style="line-height:65px;">'.$position.'</div>';
				if($img !=""){
					$string .='<div class="col-md-2"><img src="'.$img.'" style="width:100px;height:60px" /></div>';
				}else{
					$string .='<div class="col-md-2" style="width:100px;">&nbsp;</div>';
				}
				$string .='<div class="col-md-5" style="line-height:65px;"><strong>'.get_the_content().'</strong></div>';
				$string .='<div class="col-md-2" align="center"><a href="'.$url.'" class="btn btn-warning btn-sm btnBet" target="_blank" style="text-decoration:none;">Click to view</a></div>';
				$string .='</div>';
			}
			/*$string .='<div class="modal fade" id="review'.get_the_ID().'"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">';
			$string .='<h4 class="modal-title">Reviews for '.get_the_title().'</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
			$string .='<div class="modal-body">';
			$args1 = array(
				'post_type' => 'tlReviews',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => 'topListId',
						'value' => get_the_ID()
					)
				)
			);
			$query1 = new WP_Query($args1);
			if( $query1->have_posts() ){
				while( $query1->have_posts() ){
					$query1->the_post();
					$string .='<p><strong>'.get_the_title().'</strong><br />'.get_the_content().'</p>';
				}
			}else{
				$string .='<p><strong>No reviews yet.</strong></p>';
			}*/
			//$string .='</div></div></div></div>';
		}
	}
    $string .= '</div>';
    wp_reset_postdata();
    return $string;
}

add_shortcode( 'newlist', 'newList_shortcode' );
add_action( 'init', 'ma_custom_post_newList' );
add_action( 'save_post', 'ma_save_newList_meta', 1, 2 );