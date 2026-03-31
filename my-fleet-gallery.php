<?php
/**
 * Plugin Name: Modern Fleet Gallery
 * Description: Viewport-Constrained Hybrid Layout (Grid Desktop / Carousel Mobile)
 * Version: 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'fleet-gallery-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '2.4.0' );
	wp_enqueue_script( 'fleet-gallery-script', plugin_dir_url( __FILE__ ) . 'assets/js/fleet-script.js', array(), '2.4.0', true );
});
	
	add_shortcode( 'fleet_gallery', function( $atts ) {
		$a = shortcode_atts( array(
				'category'    => '',
				'index_title' => 'What\'s Included',
				'index_items' => 'Full Insurance,2 Helmets,Unlimited KM,24/7 Assist,Free Delivery',
				'wa_number'   => '1234567890',
				'wa_text'     => 'Questions? WhatsApp Us'
		), $atts );
		
		$items_html = '';
		foreach( explode( ',', $a['index_items'] ) as $i ) {
			$items_html .= '<li>' . esc_html( trim( $i ) ) . '</li>';
		}
		$clean_wa = preg_replace( '/[^0-9]/', '', $a['wa_number'] );
		$query = new WP_Query(['post_type' => 'fleet_vehicle', 'posts_per_page' => -1, 'category_name' => $a['category'], 'orderby' => 'menu_order', 'order' => 'ASC']);
		$uid = uniqid( 'fleet_' );
		
		$o = '<div class="fleet-wrapper" id="' . esc_attr( $uid ) . '">';
		
		// Desktop Sidebar
		$o .= '<aside class="index-card-base index-sidebar"><h2 style="font-size:1rem;margin:0 0 15px 0;">' . esc_html($a['index_title']) . '</h2><ul class="index-list">' . $items_html . '</ul><a href="https://wa.me/'.$clean_wa.'" class="index-wa-support" target="_blank"><i class="wa-icon">w</i><span>'.$a['wa_text'].'</span></a></aside>';
		
		$o .= '<div class="fleet-main">';
		// Mobile Header
		$o .= '<div class="index-card-base mobile-index-header"><h2 style="font-size:0.95rem;margin:0 0 15px 0;">' . esc_html($a['index_title']) . '</h2><ul class="index-list">' . $items_html . '</ul><a href="https://wa.me/'.$clean_wa.'" class="index-wa-support" target="_blank"><i class="wa-icon">w</i><span>'.$a['wa_text'].'</span></a></div>';
		
		// The Scissors (Viewport)
		$o .= '<div class="fleet-viewport">';
		$o .= '<div class="fleet-row">';
		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
		$price = get_post_meta( get_the_ID(), '_fleet_price', true );
		$link = get_post_meta( get_the_ID(), '_fleet_link', true ) ?: '#';
		$o .= '<article class="vehicle-card">';
		if($price) $o .= '<div class="price-badge">€' . esc_html($price) . '</div>';
		$o .= '<div class="image-box">' . get_the_post_thumbnail( get_the_ID(), 'medium_large' ) . '</div>';
		$o .= '<div class="content"><h3 class="model-name">' . get_the_title() . '</h3><div class="specs-list">';
		foreach(explode(',', get_the_excerpt()) as $s) if(!empty(trim($s))) $o .= '<span class="spec-pill">'.esc_html(trim($s)).'</span>';
		$o .= '</div><div class="cta-group"><a href="'.esc_url($link).'" class="btn-book">Book Online</a></div></div></article>';
		endwhile; wp_reset_postdata(); endif;
		$o .= '</div>'; // close fleet-row
		$o .= '</div>'; // close fleet-viewport
		
		$o .= '<button class="nav-btn prev" aria-label="Prev">❮</button>';
		$o .= '<button class="nav-btn next" aria-label="Next">❯</button>';
		$o .= '<div class="fleet-dots"></div>';
		$o .= '</div></div>';
		
		return $o;
	});