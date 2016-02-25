<?php

/**
 * Delete property value in the database.
 *
 * @param  int    $term_id
 * @param  string $slug
 *
 * @return bool
 */
function papi_delete_term_field( $term_id, $slug = '' ) {
	if ( ! is_numeric( $term_id ) && is_string( $term_id ) ) {
		$slug    = $term_id;
		$term_id = null;
	}

	if ( ! is_string( $slug ) || empty( $slug ) ) {
		return false;
	}

	return papi_delete_field( $term_id, $slug, Papi_Term_Store::TYPE );
}

/**
 * Get property value from the database.
 *
 * @param  int    $term_id
 * @param  string $slug
 * @param  mixed  $default
 *
 * @return mixed
 */
function papi_get_term_field( $term_id, $slug, $default = null ) {
	if ( ! is_numeric( $term_id ) && is_string( $term_id ) ) {
		$slug    = $term_id;
		$default = $slug;
		$term_id = null;
	}

	if ( ! is_string( $slug ) || empty( $slug ) ) {
		return $default;
	}

	$term_id = papi_get_term_id( $term_id );

	if ( empty( $term_id ) ) {
		return $default;
	}

	return papi_get_field( $term_id, $slug, $default, Papi_Term_Store::TYPE );
}

/**
 * Shortcode for `papi_get_term_field` function.
 *
 * [papi_taxonomy id=1 slug="property_slug" default="Default value"][/papi_taxonomy]
 *
 * @param  array $atts
 *
 * @return mixed
 */
function papi_taxonomy_shortcode( $atts ) {
	$atts['id'] = isset( $atts['id'] ) ? $atts['id'] : 0;
	$atts['id'] = papi_get_term_id( $atts['id'] );
	$default    = isset( $atts['default'] ) ? $atts['default'] : '';

	if ( empty( $atts['id'] ) || empty( $atts['slug'] ) ) {
		$value = $default;
	} else {
		$value = papi_get_term_field( $atts['id'], $atts['slug'], $default );
	}

	if ( is_array( $value ) ) {
		$value = implode( ', ', $value );
	}

	return $value;
}

add_shortcode( 'papi_taxonomy', 'papi_taxonomy_shortcode' );

/**
 * Update property with new value. The old value will be deleted.
 *
 * @param  int    $term_id
 * @param  string $slug
 * @param  mixed  $value
 *
 * @return bool
 */
function papi_update_term_field( $term_id, $slug, $value = null ) {
	if ( ! is_numeric( $term_id ) && is_string( $term_id ) ) {
		$slug    = $term_id;
		$value   = $slug;
		$term_id = null;
	}

	if ( ! is_string( $slug ) || empty( $slug ) ) {
		return false;
	}

	if ( papi_is_empty( $value ) ) {
		return papi_delete_term_field( $term_id, $slug );
	}

	$term_id = papi_get_term_id( $term_id );

	if ( empty( $term_id ) ) {
		return $default;
	}

	return papi_update_field( $term_id, $slug, $value, Papi_Term_Store::TYPE );
}

/**
 * Echo the value for property on a page.
 *
 * @param int    $term_id
 * @param string $slug
 * @param mixed  $default
 */
function the_papi_term_meta( $term_id = null, $slug = null, $default = null ) {
	$value = papi_get_term_field( $term_id, $slug, $default );

	if ( is_array( $value ) ) {
		$value = implode( ', ', $value );
	}

	if ( is_object( $value ) ) {
		// @codingStandardsIgnoreLine
		$value = print_r( $value, true );
	}

	echo $value;
}
