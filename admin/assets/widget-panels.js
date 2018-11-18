if( "undefined" == typeof jQuery )throw new Error( "widget panels JS requires jQuery" );


( function( widget_panel, $, undefined ) {

	'use strict';


	/**
	 * Closes accordion section in widget form
	 *
	 * @since 1.0.0
	 */
	widget_panel.close_accordions = function ( widget ){
		var sections = widget.find( '.widget-panel-section' );
		sections.find( '.widget-panel-settings' ).hide();
	};


	/**
	 * Invokes accordion closing when widget form is saved
	 *
	 * @since 1.0.0
	 */
	widget_panel.accordion_form_update = function( event, widget ){
		console.log( event );
		console.log( widget );
		widget_panel.close_accordions( widget );
	};


	/**
	 * Updates thumbnail preview
	 *
	 * @since 1.0.0
	 */
	widget_panel.update_thumbnail_preview = function ( widget ){

		var preview_div = widget.find( '.widget-panel-thumbnail-preview' );

		if( ! preview_div.length ) {
			return;
		}

		var thumbsize_wrap = preview_div.closest( '.widget-panel-thumbsize-wrap' );
		var preview_image = $( '.widget-panel-preview-image', preview_div );
		var width         = parseInt ( ( $.trim( $( '.widget-panel-thumb-width', thumbsize_wrap ).val() ) * 1 ) + 0 );
		var height        = parseInt ( ( $.trim( $( '.widget-panel-thumb-height', thumbsize_wrap ).val() ) * 1 ) + 0 );

		preview_div.css( {
			'height' : height + 'px',
			'width'  : width  + 'px'
		} );
		preview_image.css( { 'font-size' : height + 'px' } );

		return;
	};


	/**
	 * Invokes thumbnail update when widget form is saved
	 *
	 * @since 1.0.0
	 */
	widget_panel.thumbnail_form_update = function ( event, widget ){
		// widget-panel.update_thumbnail_preview( widget );
	};


	/**
	 * Updates excerpt preview
	 *
	 * @since 1.0.0
	 */
	widget_panel.update_excerpt_preview = function ( widget ) {

		var preview_div = widget.find( '.widget-panel-excerpt-preview' );
		var sample_excerpt = widget.find( '.widget-panel-excerpt-sample' ).text();

		if( ! preview_div.length ) {
			return;
		}

		var excerpt = $( '.widget-panel-excerpt', preview_div );
		var field   = widget.find( '.widget-panel-excerpt-length' );
		var size    = parseInt ( ( $.trim( field.val() ) * 1 ) + 0 );
		var words   = sample_excerpt.match(/\S+/g).length;
		var trimmed = '';

		if ( words > size ) {
			trimmed = sample_excerpt.split(/\s+/, size).join(" ");
		} else {
			trimmed = sample_excerpt;
		}

		excerpt.html( trimmed + "&hellip;" );

	};


	/**
	 * Invokes excerpt update when widget form is saved
	 *
	 * @since 1.0.0
	 */
	widget_panel.excerpt_form_update = function ( event, widget ){
		widget_panel.update_excerpt_preview( widget );
	};


}( window.widget_panel = window.widget_panel || {}, jQuery ) );

( function ( $ ) {

    'use strict';

	/**
	 * Accordion functions
	 *
	 * @since 1.0.0
	 */
	$( document ).on( 'widget-added widget-updated', widget_panel.accordion_form_update );

	$( '#widgets-right .widget:has( .widget-panel-widget-form )' ).each( function () {
		widget_panel.close_accordions( $( this ) );
	} );

	$( '#widgets-right, #accordion-panel-widgets' ).on( 'click', '.widget-panel-section-top', function( e ) {
		var header = $( this );
		var section = header.closest( '.widget-panel-section' );
		var fieldset_id = header.data( 'fieldset' );
		var target_fieldset = $( 'fieldset[data-fieldset-id="' + fieldset_id + '"]', section );
		var content = section.find( '.widget-panel-settings' );

		header.toggleClass( 'widget-panel-active' );
		target_fieldset.addClass( 'targeted');
		content.slideToggle( 300, function () {
			section.toggleClass( 'expanded' );
		});
	});


	/**
	 * Preview thumbnail size
	 *
	 * @since 1.0.0
	 */

	$( document ).on( 'widget-added widget-updated', widget_panel.thumbnail_form_update );

	// Change thumb size when form field changes
	$( '#customize-controls, #wpcontent' ).on( 'change', '.widget-panel-thumb-size', function ( e ) {
		var widget = $(this).closest('.widget');
		widget_panel.update_thumbnail_preview( widget );
		return;
	});

	// Change thumb size as user types
	$( '#customize-controls, #wpcontent' ).on( 'keyup', '.widget-panel-thumb-size', function ( e ) {
		var widget = $(this).closest('.widget');
		setTimeout( function(){
			widget_panel.update_thumbnail_preview( widget );
		}, 300 );
		return;
	});

	$( '#widgets-right .widget:has( .widget-panel-thumbnail-preview )' ).each( function () {
		widget_panel.update_thumbnail_preview( $(this) );
	});


	/**
	 * Preview excerpt size
	 *
	 * @since 1.0.0
	 */

	$( document ).on( 'widget-added widget-updated', widget_panel.excerpt_form_update );

	// Change excerpt size when form field changes
	$( '#customize-controls, #wpcontent' ).on( 'change', '.widget-panel-excerpt-length', function ( e ) {
		var widget = $(this).closest('.widget');
		widget_panel.update_excerpt_preview( widget );
		return;
	});

	// Change excerpt size as user types
	$( '#customize-controls, #wpcontent' ).on( 'keyup', '.widget-panel-excerpt-length', function ( e ) {
		var widget = $(this).closest('.widget');
		setTimeout( function(){
			widget_panel.update_excerpt_preview( widget );
		}, 300 );
		return;
	});

	$( '#widgets-right .widget:has( .widget-panel-excerpt-preview )' ).each( function () {
		widget_panel.update_excerpt_preview( $(this) );
	});


}( jQuery ) );