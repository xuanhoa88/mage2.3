;(function (window, document, undefined ) {
	"use strict";

	define([
			'jquery',
			'jquery/ui',
	], function($){

	$.PM = $.PM || {};

	// ======================================================
	// Init
	// ------------------------------------------------------
	$.PM.Init = function() {
		$( '.pin__type' ).on( 'click', function() {
			$( this ).toggleClass( 'pin__opened' );
			$( this ).siblings().removeClass( 'pin__opened' );
		});
		$( '.pin__image' ).on( 'click', function() {
			$( this ).siblings().removeClass( 'pin__opened' );
		});
        $( '.pin__close' ).on( 'click', function() {
            $( this ).siblings().removeClass( 'pin__opened' );
        });

		$( '.pin__type--area' ).hover( function() {
			$( this ).siblings( '.pin__image' ).toggleClass( 'pm-mask' );
		});
	};

	$( document ).ready( function() {
		$.PM.Init();
	} );
	});
})(window, document );