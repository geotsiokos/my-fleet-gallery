<?php
/**
 * Plugin Name: Modern Fleet Gallery Pro (Refined)
 * Description: Lightened background, reduced border radius, and fixed single-index display.
 * Version: 16.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Post Type & Meta (Core Logic)
add_action( 'init', function() {
	register_post_type( 'fleet_vehicle', [
			'labels' => [ 'name' => 'Vehicles', 'singular_name' => 'Vehicle' ],
			'public' => true,
			'menu_icon' => 'dashicons-cart',
			'supports' => [ 'title', 'thumbnail', 'excerpt' ],
			'taxonomies' => [ 'category' ],
	]);
});
	
	add_action('add_meta_boxes', function() {
		add_meta_box('fleet_details_id', 'Vehicle Details', 'fleet_details_html', 'fleet_vehicle', 'side');
	});
		
		function fleet_details_html($post) {
			$price = get_post_meta($post->ID, '_fleet_price', true);
			$url = get_post_meta($post->ID, '_fleet_link', true);
			$wa = get_post_meta($post->ID, '_fleet_whatsapp', true);
			echo '<p><label>Price (€):</label><input type="text" name="fleet_price_field" value="'.esc_attr($price).'" style="width:100%"></p>';
			echo '<p><label>Booking Link:</label><input type="text" name="fleet_link_field" value="'.esc_attr($url).'" style="width:100%"></p>';
			echo '<p><label>WhatsApp Number:</label><input type="text" name="fleet_wa_field" value="'.esc_attr($wa).'" style="width:100%"></p>';
		}
		
		add_action('save_post', function($post_id) {
			if (isset($_POST['fleet_price_field'])) update_post_meta($post_id, '_fleet_price', sanitize_text_field($_POST['fleet_price_field']));
			if (isset($_POST['fleet_link_field'])) update_post_meta($post_id, '_fleet_link', esc_url_raw($_POST['fleet_link_field']));
			if (isset($_POST['fleet_wa_field'])) update_post_meta($post_id, '_fleet_whatsapp', sanitize_text_field($_POST['fleet_wa_field']));
		});
			
			// 2. Styles - Lightened & Sharpened
			add_action( 'wp_enqueue_scripts', function() {
				wp_register_style( 'fleet-gallery-css', false );
				wp_add_inline_style( 'fleet-gallery-css', "
		.fleet-wrapper { display: flex; max-width: 1400px; margin: 40px auto; gap: 0; align-items: flex-start; position: relative; font-family: sans-serif; }
						
		/* The Vehicle Stage - Lighter and Sharper */
		.fleet-main {
			flex: 1; min-width: 0; position: relative;
			background: #fbfbfb; padding: 30px; border-radius: 16px; /*border: 1px solid #eee;*/
		}
						
		/* Index Card Base Styles */
		.index-card-base {
			background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 25px;
			box-shadow: 0 5px 15px rgba(0,0,0,0.03); z-index: 10; position: relative;
		}
						
		/* Desktop Sidebar Configuration */
		.index-sidebar { flex: 0 0 260px; position: sticky; top: 100px; margin-right: -20px; display: block; }
						
		/* Hide mobile header on desktop */
		.mobile-index-header { display: none; }
						
		/* Connector Notch - Desktop */
		.index-sidebar::after {
			content: ''; position: absolute; background: #fff; border: 1px solid #eee;
			width: 16px; height: 16px; transform: rotate(45deg); display: block;
			right: -8px; top: 40px; border-bottom: 0; border-left: 0;
		}
						
		.index-list { list-style: none; padding: 0; margin: 0; }
		.index-list li { margin-bottom: 10px; display: flex; align-items: center; font-size: 0.9rem; font-weight: 600; color: #444; }
		.index-list li::before { content: '✓'; color: #25D366; font-weight: 900; margin-right: 10px; }
						
		.fleet-row { display: flex; gap: 15px; padding: 10px 0; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
		.fleet-row::-webkit-scrollbar { display: none; }
						
		.vehicle-card { flex: 0 0 46%; background: #fff; border-radius: 16px; border: 1px solid #eee; overflow: hidden; position: relative; }
		.price-badge { position: absolute; top: 12px; right: 12px; background: #1e272e; color: #fff; padding: 5px 12px; border-radius: 8px; font-weight: 800; font-size: 0.8rem; z-index: 5; }
		.image-box { width: 100%; height: 220px; }
		.image-box img { width: 100%; height: 100%; object-fit: cover; }
		.content { padding: 20px; flex: 1; display: flex; flex-direction: column; }
		.model-name { font-size: 1.25rem; font-weight: 800; margin: 0 0 10px 0; color: #111; }
		.specs-list { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 20px; }
		.spec-pill { background: #f1f3f5; color: #666; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }
						
		.cta-group { display: flex; flex-direction: column; gap: 8px; margin-top: auto; }
		.cta-btn { display: block; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; text-decoration: none !important; }
		.btn-book { background: #1e272e; color: #fff !important; }
		.btn-wa { background: #25D366; color: #fff !important; }
						
		.nav-btn { position: absolute; top: 50%; width: 40px; height: 40px; background: #fff; border-radius: 50%; border: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center; transform: translateY(-50%); }
		.nav-btn:disabled { opacity: 0.2; }
		.prev { left: 5px; } .next { right: 5px; }
						
		@media (max-width: 1100px) {
			.fleet-wrapper { display: block; padding: 0 15px; }
			.index-sidebar { display: none !important; }
						
			.mobile-index-header { display: block; margin-bottom: 20px; }
						
			/* Connector Notch - Mobile */
			.mobile-index-header::after {
				content: ''; position: absolute; background: #fff; border: 1px solid #eee;
				width: 16px; height: 16px; transform: rotate(45deg); display: block;
				bottom: -8px; left: 30px; border-top: 0; border-left: 0;
			}
						
			.mobile-index-header .index-list { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
			.fleet-main { padding: 10px 10px 50px 10px; border-radius: 16px; }
			.vehicle-card { flex: 0 0 85%; }
			.nav-btn { top: auto; bottom: 10px; transform: none; }
			.prev { left: 25%; } .next { right: 25%; }
		}
	");
			});
				
				// 3. Shortcode
				add_shortcode( 'fleet_gallery', function( $atts ) {
					wp_enqueue_style( 'fleet-gallery-css' );
					$a = shortcode_atts( array(
							'category' => '',
							'index_title' => 'What\'s Included',
							'index_items' => 'Full Insurance,2 Helmets,Unlimited KM,24/7 Assist,Free Delivery'
					), $atts );
					
					$items_html = '';
					foreach(explode(',', $a['index_items']) as $i) {
						$items_html .= '<li>'.esc_html(trim($i)).'</li>';
					}
					
					$args = array('post_type' => 'fleet_vehicle', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC');
					if (!empty($a['category'])) { $args['category_name'] = $a['category']; }
					
					$query = new WP_Query($args);
					$uid = uniqid();
					
					ob_start(); ?>
	<div class="fleet-wrapper">
		
		<aside class="index-card-base index-sidebar">
			<h2 style="font-size: 1rem; margin-top:0;"><?php echo esc_html($a['index_title']); ?></h2>
			<ul class="index-list"><?php echo $items_html; ?></ul>
		</aside>

		<div class="fleet-main" id="wrap_<?php echo $uid; ?>">
			
			<div class="index-card-base mobile-index-header">
				<h2 style="font-size: 0.95rem; margin-top:0;"><?php echo esc_html($a['index_title']); ?></h2>
				<ul class="index-list"><?php echo $items_html; ?></ul>
			</div>

			<button class="nav-btn prev" id="prev_<?php echo $uid; ?>" onclick="mfgScroll('<?php echo $uid; ?>', -1)" disabled>❮</button>
			
			<div class="fleet-row" id="row_<?php echo $uid; ?>" onscroll="mfgUpdate('<?php echo $uid; ?>')">
				<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); 
					$price = get_post_meta(get_the_ID(), '_fleet_price', true);
					$link = get_post_meta(get_the_ID(), '_fleet_link', true) ?: '#';
					$wa = get_post_meta(get_the_ID(), '_fleet_whatsapp', true);
					$specs = explode(',', get_the_excerpt());
				?>
				<div class="vehicle-card">
					<?php if($price): ?><div class="price-badge">€<?php echo esc_html($price); ?></div><?php endif; ?>
					<div class="image-box"><?php the_post_thumbnail('medium_large'); ?></div>
					<div class="content">
						<h3 class="model-name"><?php the_title(); ?></h3>
						<div class="specs-list">
							<?php foreach($specs as $s) if(!empty(trim($s))) echo '<span class="spec-pill">'.esc_html(trim($s)).'</span>'; ?>
						</div>
						<div class="cta-group">
							<a href="<?php echo esc_url($link); ?>" class="cta-btn btn-book">Book Online</a>
							<a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $wa ?: '123456789'); ?>?text=Hi, I am interested in <?php the_title_attribute(); ?>" class="cta-btn btn-wa" target="_blank">WhatsApp</a>
						</div>
					</div>
				</div>
				<?php endwhile; wp_reset_postdata(); endif; ?>
			</div>
			
			<button class="nav-btn next" id="next_<?php echo $uid; ?>" onclick="mfgScroll('<?php echo $uid; ?>', 1)">❯</button>
		</div>
	</div>

	<script>
		window.mfgUpdate = function(id) {
			const r = document.getElementById('row_' + id);
			document.getElementById('prev_' + id).disabled = r.scrollLeft <= 10;
			document.getElementById('next_' + id).disabled = r.scrollLeft + r.offsetWidth >= r.scrollWidth - 10;
		};
		window.mfgScroll = function(id, dir) {
			const r = document.getElementById('row_' + id);
			const c = r.querySelector('.vehicle-card');
			if(c) r.scrollBy({ left: (c.offsetWidth + 15) * dir, behavior: 'smooth' });
		};
	</script>
	<?php return ob_get_clean();
});