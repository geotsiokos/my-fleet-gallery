<?php
/**
 * Plugin Name: Modern Fleet Gallery
 * Description: Hybrid Layout with Manual Sort Order (menu_order)
 * Version: 2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Enqueue Assets
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'fleet-gallery-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '2.5.0' );
	wp_enqueue_script( 'fleet-gallery-script', plugin_dir_url( __FILE__ ) . 'assets/js/fleet-script.js', array(), '2.5.0', true );
});
	
	// 2. Register Post Type with Sort Support
	add_action( 'init', function() {
		register_post_type('fleet_vehicle', [
				'labels' => [ 'name' => 'Vehicles', 'singular_name' => 'Vehicle' ],
				'public' => true,
				'menu_icon' => 'dashicons-cart',
				'supports' => [ 'title', 'thumbnail', 'excerpt', 'page-attributes' ], // Key: page-attributes enables menu_order
				'taxonomies' => [ 'category' ],
		]);
	});
		
		// 3. Meta Box for Vehicle Details + Sort Order
		add_action('add_meta_boxes', function() {
			add_meta_box('fleet_details_id', 'Vehicle Details', 'fleet_details_html', 'fleet_vehicle', 'side');
		});
			
			function fleet_details_html($post) {
				$price = get_post_meta($post->ID, '_fleet_price', true);
				$url = get_post_meta($post->ID, '_fleet_link', true);
				$order = $post->menu_order; // Fetch existing order
				
				echo '<p><label>Price (€):</label><input type="text" name="fleet_price_field" value="'.esc_attr($price).'" style="width:100%"></p>';
				echo '<p><label>Booking Link:</label><input type="text" name="fleet_link_field" value="'.esc_attr($url).'" style="width:100%"></p>';
				echo '<p><label><b>Sort Order:</b></label><input type="number" name="fleet_order_field" value="'.esc_attr($order).'" style="width:100%"><br><small>0 is first, 1 is second...</small></p>';
			}
			
			// 4. Save Logic for Meta and Sort Order
			add_action('save_post', function($post_id) {
				if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
				if ( !current_user_can('edit_post', $post_id) ) return;
				
				if ( isset( $_POST['fleet_price_field'] ) ) update_post_meta( $post_id, '_fleet_price', sanitize_text_field( $_POST['fleet_price_field'] ) );
				if ( isset( $_POST['fleet_link_field'] ) ) update_post_meta( $post_id, '_fleet_link', esc_url_raw( $_POST['fleet_link_field'] ) );
				
				if ( isset( $_POST['fleet_order_field'] ) ) {
					// Temporary unhook to avoid infinite loop on update
					remove_action('save_post', 'fleet_save_logic_wrapper');
					wp_update_post([
							'ID' => $post_id,
							'menu_order' => intval($_POST['fleet_order_field'])
					]);
				}
			});
				
				// 5. Shortcode with Sort Query
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
					
					// Query using menu_order
					$query = new WP_Query([
							'post_type'      => 'fleet_vehicle',
							'posts_per_page' => -1,
							'category_name'  => $a['category'],
							'orderby'        => 'menu_order',
							'order'          => 'ASC'
					]);
					
					$uid = uniqid( 'fleet_' );
					$o = '<div class="fleet-wrapper" id="' . esc_attr( $uid ) . '">';
					
					// Sidebar (Desktop)
					$o .= '<aside class="index-card-base index-sidebar"><h2 style="font-size:1rem;margin:0 0 15px 0;">' . esc_html($a['index_title']) . '</h2><ul class="index-list">' . $items_html . '</ul><a href="https://wa.me/'.$clean_wa.'" class="index-wa-support" target="_blank"><i class="wa-icon">w</i><span>'.$a['wa_text'].'</span></a></aside>';
					
					$o .= '<div class="fleet-main">';
					// Mobile Header
					$o .= '<div class="index-card-base mobile-index-header"><h2 style="font-size:0.95rem;margin:0 0 15px 0;">' . esc_html($a['index_title']) . '</h2><ul class="index-list">' . $items_html . '</ul><a href="https://wa.me/'.$clean_wa.'" class="index-wa-support" target="_blank"><i class="wa-icon">w</i><span>'.$a['wa_text'].'</span></a></div>';
					
					// Viewport & Row
					$o .= '<div class="fleet-viewport"><div class="fleet-row">';
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
					$o .= '</div></div>';
					
					$o .= '<button class="nav-btn prev">❮</button>';
					$o .= '<button class="nav-btn next">❯</button>';
					$o .= '<div class="fleet-dots"></div>';
					$o .= '</div></div>';
					
					return $o;
				});