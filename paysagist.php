<?php
/**
 * @package Paysagist
 * @version 0.1
 */

/*
	Plugin Name: Paysagist
	Plugin URI: http://wordpress.org/plugins/paysagist/
	Description: Preview all you installed themes at once
	Author: Nabil Kadimi
	Version: 0.1
	Author URI: http://kadimi.com/
*/

// Plugin starts here;
paysagist( 'enter' );
// Plugin ends here;

/**
 * The Boss
 *
 * Does everything
 * @param  mixed $vars (optional) Could be nothing or anything
 * @return mixed                  Could be nothing or anything
 */
function paysagist( $vars = '' ) {

	if ( 'enter' === $vars ) {
	
		// Include the Skelet framework used for options pages
		// $skelet_use_sample_data = true;
		require 'skelet/skelet.php';

		/**
		 * Add JavaScript file containing TinyMCE plugins
		 */
		add_action( 'init',              'paysagist' );
		add_filter( 'request',           'paysagist' );
		add_filter( 'template',          'paysagist' );
		add_filter( 'option_template',   'paysagist' );
		add_filter( 'option_stylesheet', 'paysagist' );

		return;
	}

	$hook = current_filter();
	static $theme, $themes;

	if ( 'init' === $hook ) {

		// Add endpoint
		add_rewrite_endpoint( 'paysagist', EP_ROOT );

		// Rewrite some URLs
		add_rewrite_rule( '^paysagist$', 'index.php?p=paysagist', 'top' );
		add_rewrite_rule( '^mezzanine$', 'index.php?p=mezzanine', 'top' );
		add_rewrite_rule( '^ground$',    'index.php?p=ground',    'top' );
		return;
	}

	if ( 'request' === $hook ) {

		// Handle request
		if( ! in_array( K::get_var( 'p', $vars ), explode( ',', 'paysagist,mezzanine,ground' ) ) ) {
			return $vars;
		}
	}

	if ( in_array( $hook, explode( ',', 'option_template,template,option_stylesheet' ) ) ) {

		// Taste the waters
		$theme = K::get_var( 'theme', $_GET, $vars );
		$themes = array_keys( wp_get_themes() );

		// Choose theme
		return in_array( $theme, $themes ) ? $theme : $vars;
	}

	if( 'paysagist' === K::get_var( 'p', $vars ) ) {

		global $wp;
		$protocol = $s = is_ssl() ? 'https' : 'http';

		?><!DOCTYPE html><html>
			<head>
				<meta charset="utf-8" />
				<link rel="stylesheet" href="<?php echo $s ?>://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/css/bootstrap.css" />
				<script src="<?php echo $s ?>://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js" ></script>
				<style>
					body {
						background: radial-gradient(ellipse at center,rgba(240,183,161,1) 0%,rgba(140,51,16,1) 50%,rgba(117,34,1,1) 51%,rgba(191,110,78,1) 100%);
						width: 270%;
						padding: 2em;
					}
					iframe {

					}
				</style>
				<script>
					jQuery( document ).ready( function( $ ) {
						$( window ).scroll( function() {
							$( 'iframe' ).css( 'height', 0.9 * $( window ).height() );
						} ).scroll();
					} );
				</script>
			</head>
			<body>
				<div class="container-fluid"><?php
					foreach ( $themes as $theme ) {
						$url = get_home_url(
							null
							, add_query_arg(
								array( 'theme' => $theme )
								, $wp->request
							)
						);
						printf( "\n" . '<div class="col-xs-4"><iframe src="%s" width="100%%" ></iframe></div>' , $url );
					}
				?></div>
			</body>
		</html><?php

		// We're done here
		exit;
	}
}
