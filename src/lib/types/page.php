<?php

/**
 * Check if the page type should be displayed or not.
 *
 * @param  string|object $page_type
 *
 * @return bool
 */
function papi_display_page_type( $page_type ) {
	$post_type = papi_get_post_type();

	if ( empty( $post_type ) ) {
		return false;
	}

	if ( is_string( $page_type ) ) {
		$page_type = papi_get_content_type_by_id( $page_type );
	}

	if ( ! is_object( $page_type ) ) {
		return false;
	}

	if ( ! in_array( $post_type, $page_type->post_type ) ) {
		return false;
	}

	$display = $page_type->display( $post_type );

	if ( ! is_bool( $display ) || $display === false ) {
		return false;
	}

	if ( preg_match( '/papi\-standard\-\w+\-type/', $page_type->get_id() ) ) {
		return true;
	}

	$parent_page_type = papi_get_page_type_by_post_id( papi_get_parent_post_id() );

	if ( papi_is_page_type( $parent_page_type ) ) {
		$child_types = $parent_page_type->get_child_types();

		if ( ! empty( $child_types ) ) {
			return in_array( $page_type, $parent_page_type->get_child_types() );
		}
	}

	// Run show page type filter.
	return papi_filter_settings_show_page_type( $post_type, $page_type );
}

/**
 * Get all page types based on a post type.
 *
 * @param  string $post_type
 *
 * @return array
 */
function papi_get_all_page_types( $post_type = '' ) {
	$content_types = papi_get_all_content_types( [
		'args'  => $post_type,
		'mode'  => 'include',
		'types' => ['attachment', 'page']
	] );

	$page_types = array_filter( $content_types, function ( $content_type ) {
		return papi_is_page_type( $content_type );
	} );

	if ( is_array( $page_types ) ) {
		usort( $page_types, function ( $a, $b ) {
			return strcmp( $a->name, $b->name );
		} );
	}

	return papi_sort_order( array_reverse( $page_types ) );
}

/**
 * Get number of how many pages uses the given page type.
 * This will also work with only page type.
 *
 * @param  string|Papi_Core_Type $page_type
 *
 * @return int
 */
function papi_get_number_of_pages( $page_type ) {
	global $wpdb;

	if ( empty( $page_type ) || ( ! is_string( $page_type ) && ( ! is_object( $page_type ) ) ) ) {
		return 0;
	}

	if ( $page_type instanceof Papi_Core_Type ) {
		$page_type = $page_type->get_id();
	}

	if ( ! is_string( $page_type ) ) {
		return 0;
	}

	$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE `meta_key` = '%s' AND `meta_value` = '%s'";
	$sql = $wpdb->prepare( $sql, papi_get_page_type_key(), $page_type );

	return intval( $wpdb->get_var( $sql ) );
}

/**
 * Get page type by id.
 *
 * @param  string $id
 *
 * @return Papi_Page_Type
 */
function papi_get_page_type_by_id( $id ) {
	return papi_get_content_type_by_id( $id );
}

/**
 * Get page type id.
 *
 * @param  int $post_id
 *
 * @return string
 */
function papi_get_page_type_id( $post_id = 0 ) {
	return papi_get_content_type_id( $post_id );
}

/**
 * Get page type from post id.
 *
 * @param  int $post_id
 *
 * @return Papi_Page_Type
 */
function papi_get_page_type_by_post_id( $post_id = 0 ) {
	if ( ! is_numeric( $post_id ) ) {
		return;
	}

	$post_id = papi_get_post_id( $post_id );

	if ( $post_id === 0 ) {
		return;
	}

	if ( $page_type = papi_get_content_type_id( $post_id ) ) {
		return papi_get_content_type_by_id( $page_type );
	}
}

/**
 * Get the page type key that is used for each post.
 *
 * @return string
 */
function papi_get_page_type_key() {
	return defined( 'PAPI_PAGE_TYPE_KEY' ) ? PAPI_PAGE_TYPE_KEY : '_papi_page_type';
}

/**
 * Get the Page type name.
 *
 * @param  int $post_id
 *
 * @return string
 */
function papi_get_page_type_name( $post_id = 0 ) {
	$post_id = papi_get_post_id( $post_id );

	if ( empty( $post_id ) ) {
		return '';
	}

	$page_type_id = papi_get_page_type_id( $post_id );

	if ( empty( $page_type_id ) ) {
		return '';
	}

	$page_type = papi_get_content_type_by_id( $page_type_id );

	if ( empty( $page_type ) ) {
		return '';
	}

	return $page_type->name;
}

/**
 * Get template file from post id.
 *
 * @param  int $post_id
 *
 * @return null|string
 */
function papi_get_page_type_template( $post_id = 0 ) {
	if ( empty( $post_id ) && ! is_numeric( $post_id ) ) {
		return;
	}

	$data = papi_get_page_type_by_post_id( $post_id );

	if ( isset( $data ) && isset( $data->template ) ) {
		$template  = $data->template;
		$extension = '.php';
		$ext_reg   = '/(' . $extension . ')+$/';

		if ( preg_match( '/\.\w+$/', $template, $matches ) && preg_match( $ext_reg, $matches[0] ) ) {
			return str_replace( '.', '/', preg_replace( '/' . $matches[0] . '$/', '', $template ) ) . $matches[0];
		} else {
			$template = str_replace( '.', '/', $template );
			return substr( $template, -strlen( $extension ) ) === $extension
				? $template : $template . $extension;
		}
	}
}

/**
 * Check if `$obj` is a instanceof `Papi_Page_Type`.
 *
 * @param  mixed $obj
 *
 * @return bool
 */
function papi_is_page_type( $obj ) {
	return $obj instanceof Papi_Page_Type;
}

/**
 * Set page type to a post.
 *
 * @param  mixed $post_id
 * @param  string $page_type
 *
 * @return bool
 */
function papi_set_page_type_id( $post_id, $page_type ) {
	return papi_content_type_exists( $page_type ) && update_post_meta(
		papi_get_post_id( $post_id ),
		papi_get_page_type_key(),
		$page_type
	);
}

/**
 * Echo the Page type name.
 *
 * @param  int $post_id
 *
 * @return string
 */
function the_papi_page_type_name( $post_id = 0 ) {
	echo papi_get_page_type_name( $post_id );
}
