<?php
/**
 * Plugin Name: Modern Fleet Gallery
 * Description: Supports Vehicles, Auto-play and vehicle features.
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Enqueue Assets
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'fleet-gallery-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '2.0.0' );
	wp_enqueue_script( 'fleet-gallery-script', plugin_dir_url( __FILE__ ) . 'assets/js/fleet-script.js', array(), '2.0.0', true );
});

// 2. Post Type & Meta
add_action( 'init', function() {
	register_post_type( 'fleet_vehicle', [
		'labels'     => [ 'name' => 'Vehicles', 'singular_name' => 'Vehicle' ],
		'public'     => true,
		'menu_icon'  => 'dashicons-cart',
		'supports'   => [ 'title', 'thumbnail', 'excerpt' ],
		'taxonomies' => [ 'category' ],
	]);
});

add_action('add_meta_boxes', function() {
	add_meta_box( 'fleet_details_id', 'Vehicle Details', 'fleet_details_html', 'fleet_vehicle', 'side' );
});

function fleet_details_html( $post ) {
	$price = get_post_meta( $post->ID, '_fleet_price', true );
	$url = get_post_meta( $post->ID, '_fleet_link', true );
	echo '<p><label>Price (€):</label><input type="text" name="fleet_price_field" value="'. esc_attr( $price ) .'" style="width:100%"></p>';
	echo '<p><label>Booking Link:</label><input type="text" name="fleet_link_field" value="'. esc_attr( $url ) .'" style="width:100%"></p>';
}

add_action('save_post', function($post_id) {
	if ( isset( $_POST['fleet_price_field'] ) ) {
		update_post_meta( $post_id, '_fleet_price', sanitize_text_field( $_POST['fleet_price_field'] ) );
	}
	if ( isset( $_POST['fleet_link_field'] ) ) {
		update_post_meta( $post_id, '_fleet_link', esc_url_raw( $_POST['fleet_link_field'] ) );
	}
});

// 3. Shortcode
add_shortcode( 'fleet_gallery', function( $atts ) {
	$a = shortcode_atts( array(
		'category'    => '',
		'index_title' => 'What\'s Included',
		'index_items' => 'Full Insurance,2 Helmets,Unlimited KM,24/7 Assist,Free Delivery',
		'wa_number'   => '1234567890',
		'wa_text'     => 'Questions? WhatsApp Us',
		'autoplay'    => '5000'
	), $atts );

	$items_html = '';
	foreach ( explode( ',', $a['index_items'] ) as $i ) {
		$items_html .= '<li>' . esc_html( trim( $i ) ) . '</li>';
	}
	$clean_wa = preg_replace( '/[^0-9]/', '', $a['wa_number'] );

	$args = array(
			'post_type'      => 'fleet_vehicle',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
	);
	if ( !empty( $a['category'] ) ) {
		$args['category_name'] = $a['category'];
	}

	$query = new WP_Query( $args );
	$uid = uniqid( 'cat_' . $a['category'] . '_' );

	$o = '<div class="fleet-wrapper" id="' . esc_attr( $uid ) . '" data-autoplay="' . esc_attr( $a['autoplay'] ) . '">';
	$o .= '<aside class="index-card-base index-sidebar">';
	$o .= '<h2 style="font-size: 1rem; margin-top:0;">' . esc_html( $a['index_title'] ) . '</h2>';
	$o .= '<ul class="index-list">' . $items_html . '</ul>';
	$o .= '<a href="https://wa.me/' . $clean_wa . '" class="index-wa-support" target="_blank">';
	$o .= '<i class="wa-icon">w</i><span>' . esc_html( $a['wa_text'] ) . '</span>';
	$o .= '</a></aside>';

	$o .= '<div class="fleet-main">';
	$o .= '<div class="index-card-base mobile-index-header">';
	$o .= '<h2 style="font-size: 0.95rem; margin-top:0;">' . esc_html( $a['index_title'] ) . '</h2>';
	$o .= '<ul class="index-list">' . $items_html . '</ul>';
	$o .= '<a href="https://wa.me/' . $clean_wa . '" class="index-wa-support" target="_blank">';
	$o .= '<i class="wa-icon">w</i><span>' . esc_html( $a['wa_text'] ) . '</span>';
	$o .= '</a></div>';

	$o .= '<button class="nav-btn prev" aria-label="Previous">❮</button>';
	$o .= '<div class="fleet-row">';

	if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
		$price = get_post_meta( get_the_ID(), '_fleet_price', true );
		$link = get_post_meta( get_the_ID(), '_fleet_link', true ) ?: '#';
		$img_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		$title = get_the_title();

		$o .= '<script type="application/ld+json">';
		$o .= '{';
		$o .= '"@context": "https://schema.org/",';
		$o .= '"@type": "Product",';
		$o .= '"name": "' . esc_js( $title ) . '",';
		$o .= '"description": "' . esc_js( wp_strip_all_tags( get_the_excerpt() ) ) . '",';
		$o .= '"image": "' . esc_url( $img_url ) . '",';
		$o .= '"offers": {';
		$o .= '"@type": "Offer",';
		$o .= '"priceCurrency": "EUR",';
		$o .= '"price": "' . esc_attr( $price ) . '",';
		$o .= '"availability": "https://schema.org/InStock",';
		$o .= '"url": "' . get_permalink() . '"';
		$o .= '}';
		$o .= '}';
		$o .= '</script>';

		$o .= '<article class="vehicle-card">';
		if( $price ) {
			$o .= '<div class="price-badge">€' . esc_html( $price ) . '</div>';
		}
		$o .= '<div class="image-box">' . get_the_post_thumbnail( get_the_ID(), 'medium_large', ['alt' => $title, 'loading' => 'lazy'] ) . '</div>';
		$o .= '<div class="content">';
		$o .= '<h3 class="model-name">' . esc_html( $title ) . '</h3>';
		$o .= '<div class="specs-list">';
		$specs = explode( ',', get_the_excerpt() );
		foreach ( $specs as $s ) {
			if ( !empty( trim( $s ) ) ) {
				$o .= '<span class="spec-pill">' . esc_html( trim( $s ) ) . '</span>';
			}
		}
		$o .= '</div>';
		$o .= '<div class="cta-group"><a href="' . esc_url( $link ) . '" class="btn-book">Book Online</a></div>';
		$o .= '</div></article>';

	endwhile;
	wp_reset_postdata();
	endif;

	$o .= '</div>'; // close fleet-row
	$o .= '<button class="nav-btn next" aria-label="Next">❯</button>';
	$o .= '<div class="fleet-dots"></div>';
	$o .= '</div></div>'; // close fleet-main and fleet-wrapper

	return $o;
});