<?php

namespace NikolayS93\Queries;

/**
 * Safe dynamic expression include.
 *
 * @param string $path relative path.
 */
function include_plugin_file( $path ) {
	if ( 0 !== strpos( $path, PLUGIN_DIR ) ) {
		$path = PLUGIN_DIR . $path;
	}

	if ( is_file( $path ) && is_readable( $path ) ) {
		return require_once $path;
	}

	return false;
}

/**
 * Sanitize option values (escape html) and native wordpress sanitize keys.
 *
 * @param Array $options list of options
 * @param boolean $sort need sorts?
 *
 * @return Array   $options results
 */
function sanitize_select_array( $options, $sort = false ) {
	$options = ( ! is_array( $options ) ) ? (array) $options : $options;

	// Clean the values (since it can be filtered by other plugins)
	$options = array_map( 'esc_html', $options );

	// Flip to clean the keys (used as <option> values in <select> field on form)
	$options = array_flip( $options );
	$options = array_map( 'sanitize_key', $options );

	// Flip back
	$options = array_flip( $options );

	if ( $sort ) {
		asort( $options );
	};

	return $options;
}

/**
 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
 * placed under a 'children' member of their parent term.
 *
 * @param Array $cats taxonomy term objects to sort
 * @param Array $into result array to put them in
 * @param integer $parentId the current parent ID to put them in
 */
function sort_terms_hierarchicaly( &$cats, &$into, $parentId = 0 ) {
	foreach ( $cats as $i => $cat ) {
		if ( $cat->parent == $parentId ) {
			$into[ $cat->term_id ] = $cat;
			unset( $cats[ $i ] );
		}
	}

	foreach ( $into as $topCat ) {
		$topCat->children = array();
		sort_terms_hierarchicaly( $cats, $topCat->children, $topCat->term_id );
	}
}
