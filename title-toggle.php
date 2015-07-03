<?php
/*
Plugin Name: Title Toggle
Plugin URI: http://github.com/helgatheviking/title-toggle
Description: A plugin that will allow to remove page titles per page.
Version: 0.0.1
Author: Kathy Darling
Author URI: http://www.kathyisawesome.com
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}



/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function title_toggle_load_textdomain() {
  load_plugin_textdomain( 'title-toggle', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'title_toggle_load_textdomain' );

/* = Admin meta functions
-------------------------------------------------------------- */

/**
 * Add a checkbox to the post actions metabox
 *
 * @since 1.0.0
 */
function title_toggle_post_title_actions() { 
	global $post; 

	$post_type = get_post_type_object( $post->post_type );
	$hide_title = get_post_meta( $post->ID, '_hide_title', true ); 

	?>
	<div class="misc-pub-section columns-prefs">

		<?php wp_nonce_field( 'title_toggle_post_title', 'title_toggle_post_title_nonce' ); ?>
					
		<label class="columns-prefs-1">
			<?php printf( __( 'Hide %s title:', 'title-toggle' ), strtolower($post_type->labels->singular_name ));?> <input type="checkbox" name="hide_title" value="1" <?php checked( $hide_title, '1' );?>>
		</label>

	</div>
		<?php 
}
add_action( 'post_submitbox_misc_actions', 'title_toggle_post_title_actions' );

/**
 * Save post metadata when a post is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 * @since 1.0.0
 */
function title_toggle_save_post_title_display( $post_id, $post, $update ) {

	// validate nonce
	if ( !isset( $_POST['title_toggle_post_title_nonce'] ) || !wp_verify_nonce( $_POST['title_toggle_post_title_nonce'], 'title_toggle_post_title' ) ) {
		return $post_id;
	}

	// check user priveledges
	if ( 'page' == get_post_type( $post_id ) ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	}

	// - Update the post's metadata.
	if ( isset( $_POST['hide_title'] ) ) {
		update_post_meta( $post_id, '_hide_title', true );
	} else {
		delete_post_meta( $post_id, '_hide_title' );
	}

	return $post_id;
}
add_action( 'save_post', 'title_toggle_save_post_title_display', 10, 3 );


/* = Quick Edit
-------------------------------------------------------------- */

/**
 * Add New Custom Columns
 *
 * @param array $columns
 * @return array
 * @since 1.0
 */
function title_toggle_column_header( $columns ) {
	$columns['title_toggle'] = __( 'Show Title', 'title-toggle' );
	return $columns;
}
add_action( 'manage_posts_columns', 'title_toggle_column_header' );
add_action( 'manage_pages_columns', 'title_toggle_column_header' );


/**
 * New Custom Column content
 *
 * @param string $column
 * @param int $post_id
 * @return print HTML for column
 * @since 1.0
 */
function title_toggle_column_value( $column, $post_id ) {
	global $post;

	switch ( $column ) {
		case "title_toggle":

			$nonce = wp_create_nonce( 'title-toggle' );

			if( get_post_meta( $post_id, '_hide_title', true ) == true ){
				$class = 'dashicons-no-alt';
				$value = 'hide';
			} else {
				$class = 'dashicons-yes';
				$value = 'show';
			}

			printf( '<a class="title-toggle dashicons %s" id="%s" href="#" data-nonce="%s" data-post_id="%s" title="%s">%s</a>',
				$class,
				'title_toggle_' . $post_id,
				esc_attr( $nonce ),
				esc_attr( $post_id ),
				esc_attr( 'Toggle title display', 'title-toggle' ),
				'<span class="screen-reader-text">' . __( 'Toggle title display', 'title-toggle' ) . '</span>'
			);

		break;
	}

}
add_filter( 'manage_posts_custom_column', 'title_toggle_column_value', 10, 2 );
add_filter( 'manage_pages_custom_column', 'title_toggle_column_value', 10, 2 );


/**
 * Add Quick Edit Form
 * @param string $column_name
 * @return  string
*/
function title_toggle_quick_edit_custom_box( $column_name ) {
	if( $column_name == 'title_toggle' ) { ?>

	<fieldset class="inline-edit-col-left" style="clear:left;">

	<div class="inline-edit-col">

		<div class="inline-edit-group">						

			<label class="title-toggle alignleft">
				<input type="checkbox" class="title-toggle-input" name="hide_title" value="1 />
				<span class="checkbox-title"><?php _e( 'Hide title', 'title-toggle'   ) ?></span>
				<?php wp_nonce_field( 'title_toggle_post_title', 'title_toggle_post_title_nonce' ); ?>
			</label>

		</div>
	</div>

	</fieldset>

<?php   }
}
add_action( 'quick_edit_custom_box', 'title_toggle_quick_edit_custom_box', 10 );

/**
 * Load script for quickedit
 *
 * @since 1.0.0
 */
function title_toggle_admin_script(){

	$screen = get_current_screen();

    if ( $screen->base != "edit" ){
    	return;
    }
    
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
 
	wp_enqueue_script( 'title-toggle', plugins_url( 'js/title-toggle' . $suffix . '.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

}
add_action( 'admin_enqueue_scripts', 'title_toggle_admin_script' );


/**
 * Toggles title display from quickedit via ajax
 * @return void
 * Props to WooTheme's WooCommerce
 */
function title_toggle_ajax_callback() {

	if ( ! is_admin() ){
		die();
	}

	if ( ! check_admin_referer( 'title-toggle' )) {
		die('not nonced');
	}

	// get the post ID
	$post_id = isset( $_GET['post_id'] ) && (int) $_GET['post_id'] ? (int) $_GET['post_id'] : '';

	if ( ! $post_id ){
		die();
	}

	// Check permissions
	if ( ( 'page' == get_post_type( $post_id ) && ! current_user_can( 'edit_page', $post_id ) ) || ! current_user_can( 'edit_post', $post_id ) ) {
		die();
	}

	// since it is 'toggle' get the featured status and set to opposite
	$hide = get_post_meta( $post_id, '_hide_title', true );

	if ( $hide == true ){
		delete_post_meta( $post_id, '_hide_title' );
		$msg = 'show';
	} else {
		update_post_meta( $post_id, '_hide_title', true );
		$msg = 'hide';
	}

	die($msg);
}
add_action( 'wp_ajax_title_toggle_quickedit', 'title_toggle_ajax_callback' );
	

/* = Display Functions
-------------------------------------------------------------- */

/**
 * Add a special class for hiding the post title
 *
 * @param array $classes post classes
 * @return array
 * @since 1.0.0
 */
function title_toggle_post_class( $classes ) {
	global $post;
	if( is_singular() && get_post_meta( $post->ID, '_hide_title', true ) == 1 ){
		$classes[] = 'no-title';
	}
	return $classes;
}
add_filter( 'post_class', 'title_toggle_post_class' );

/**
 * Return a null string in place of the title
 *
 * @param array $classes post classes
 * @return array
 * @since 1.0.0
 */
function title_toggle_hide_post_title( $title, $id = '' ){	

	// works only on single pages in the loop
	if( ! is_admin() && is_singular() && in_the_loop() && get_post_meta( $id, '_hide_title', true ) == 1 ){
		$title = '';	
	}
	
	return $title;
}
add_filter( 'the_title', 'title_toggle_hide_post_title', 10, 2 );

