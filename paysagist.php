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
	static $t, $theme, $themes;

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

		/**
		 * Taste the waters
		 *
		 * Populate static variables once
		 */
		if( empty( $theme ) ) {
			$theme = K::get_var( 'theme', $_GET, $vars );
			$themes = array_keys( wp_get_themes() );
			$t = wp_get_theme( $theme );
		}

		// Handle cheating requests like ?theme=cheating
		if( ! in_array( $theme, $themes ) ) {
			return $vars;
		}

		// Return the right parent when using a child theme
		if( $t->template !== $t->stylesheet ) {
			switch ( $hook ) {
			case 'template' :
			case 'option_template' :
				$theme = $t->template;
			}
		}

		// Choose theme choice
		return $theme;
	}

	if( 'paysagist' === K::get_var( 'p', $vars ) ) {

		global $wp;
		$protocol = $s = is_ssl() ? 'https' : 'http';

		?>
		<!DOCTYPE html><html>
			<head>
				<meta charset="utf-8" />
				<link rel="stylesheet" href="<?php echo $s ?>://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/css/bootstrap.css" />
				<script src="<?php echo $s ?>://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js" ></script>
				<style>
					body {
						background: radial-gradient(ellipse at center,rgba(240,183,161,1) 0%,rgba(140,51,16,1) 50%,rgba(117,34,1,1) 51%,rgba(191,110,78,1) 100%);
						width: 270%;
						padding: 2em 2em 0;
					}
					iframe {

					}
				</style>
				<script>
					jQuery( document ).ready( function( $ ) {
						$( window ).scroll( function() {
							$( 'iframe' ).css( 'height', 1.2 * $( window ).height() );
						} ).scroll();
					} );
				</script>
			</head>
			<body>
				<div class="container-fluid"><?php
					// Build URL for iframes
					$url = K::get_var( 'url', $_GET, get_home_url() );

					foreach ( $themes as $theme ) {
						$src = sprintf( '%s%s%s'
							, $url 
							, ( parse_url( $url, PHP_URL_QUERY ) == NULL ) ? '?' : '&'
							, "theme=$theme"
						);
						?>
						<div class="col-xs-3">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title"><?php echo $theme; ?></h3>
								</div>
								<div class="panel-body">
									<iframe src="<?php echo $src; ?>" width="100%%" ></iframe>
								</div>
							</div>
						</div>
						<?php
					}
				?>
				</div>
			</body>
		</html><?php

		// We're done here
		exit;
	}
}
