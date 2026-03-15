<?php
/**
 * Plugin Name: Modern Fleet Gallery
 * Description: A showcasing gallery for vehicles, with scroll and navigation buttons.
 * Version: 11.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Post Type & Taxonomy
add_action( 'init', function() {
	register_post_type( 'fleet_vehicle', [
		'labels' => [ 'name' => 'Vehicles', 'singular_name' => 'Vehicle' ],
		'public' => true,
		'menu_icon' => 'dashicons-cart',
		'supports' => [ 'title', 'thumbnail', 'excerpt' ],
		'taxonomies' => [ 'category' ], // Enables standard WP Categories
	]);
});

// Meta Boxes (Price, Link, WhatsApp)
add_action('add_meta_boxes', function() {
	add_meta_box('fleet_details_id', 'Vehicle Details', 'fleet_details_html', 'fleet_vehicle', 'side');
});

function fleet_details_html($post) {
	$price = get_post_meta($post->ID, '_fleet_price', true);
	$url = get_post_meta($post->ID, '_fleet_link', true);
	$wa = get_post_meta($post->ID, '_fleet_whatsapp', true);
	echo '<p><label>Price (€/Day):</label><input type="text" name="fleet_price_field" value="'.esc_attr($price).'" placeholder="from 25" style="width:100%"></p>';
	echo '<p><label>Booking Link:</label><input type="text" name="fleet_link_field" value="'.esc_attr($url).'" style="width:100%"></p>';
	echo '<p><label>WhatsApp Number:</label><input type="text" name="fleet_wa_field" value="'.esc_attr($wa).'" placeholder="346000000" style="width:100%"></p>';
}

add_action('save_post', function($post_id) {
	if (isset($_POST['fleet_price_field'])) update_post_meta($post_id, '_fleet_price', sanitize_text_field($_POST['fleet_price_field']));
	if (isset($_POST['fleet_link_field'])) update_post_meta($post_id, '_fleet_link', esc_url_raw($_POST['fleet_link_field']));
	if (isset($_POST['fleet_wa_field'])) update_post_meta($post_id, '_fleet_whatsapp', sanitize_text_field($_POST['fleet_wa_field']));
});

// 2. Styles
add_action( 'wp_enqueue_scripts', function() {
	wp_register_style( 'fleet-gallery-css', false );
	wp_add_inline_style( 'fleet-gallery-css', "
		.fleet-wrapper { display: flex; max-width: 1400px; margin: 40px auto; gap: 30px; align-items: flex-start; position: relative; font-family: sans-serif; }
		.index-sidebar { flex: 0 0 280px; background: #f8f9fa; border-radius: 24px; padding: 30px; border: 1px solid #eee; position: sticky; top: 100px; margin-top: 10px; }
		.index-sidebar h2 { color: #1e272e; margin: 0 0 15px 0; font-size: 1.3rem; }
		
		.index-list { list-style: none; padding: 0; margin: 0; }
		.index-list li { margin-bottom: 10px; display: flex; align-items: center; font-size: 0.95rem; font-weight: 500; color: #444; }
		.index-list li::before { content: '✓'; color: #25D366; font-weight: bold; margin-right: 10px; }

		.fleet-main { flex: 1; min-width: 0; position: relative; }
		.fleet-row { display: flex; gap: 20px; padding: 10px 0 40px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none; }
		.fleet-row::-webkit-scrollbar { display: none; }
		
		.vehicle-card { flex: 0 0 45%; background: #fff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); scroll-snap-align: start; display: flex; flex-direction: column; border: 1px solid #eee; overflow: hidden; position: relative; }
		
		/* Price Tag Overlay */
		.price-badge { position: absolute; top: 15px; right: 15px; background: #fff; color: #1e272e; padding: 6px 12px; border-radius: 12px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 5; border: 1px solid #eee; }

		.image-box { width: 100%; height: 260px; background: #f8f8f8; }
		.image-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
		
		.content { padding: 22px; flex: 1; display: flex; flex-direction: column; }
		.model-name { font-size: 1.4rem; font-weight: 700; margin: 0 0 12px 0; color: #1e272e; }
		
		.specs-list { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px; }
		.spec-pill { background: #f1f3f5; color: #495057; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }

		.cta-group { display: flex; flex-direction: column; gap: 8px; margin-top: auto; }
		.cta-btn { display: block; text-align: center; padding: 12px; border-radius: 14px; font-weight: 700; font-size: 0.95rem; text-decoration: none !important; transition: 0.3s; }
		.btn-book { background: #1e272e; color: #fff !important; }
		.btn-wa { background: #25D366; color: #fff !important; }

		.nav-btn { position: absolute; top: 40%; width: 44px; height: 44px; background: #fff; border-radius: 50%; border: 1px solid #eee; box-shadow: 0 4px 12px rgba(0,0,0,0.1); cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; }
		.nav-btn:disabled { opacity: 0.2; }
		.prev { left: -22px; } .next { right: -22px; }

		.swipe-hint { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: #1e272e; margin-top: 30px; padding-top: 15px; border-top: 1px dashed #ccc; transition: opacity 0.4s; }
		.swipe-hand { display: inline-block; animation: swipeMove 1.5s infinite ease-in-out; }
		@keyframes swipeMove { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(10px); } }

		@media (max-width: 1100px) {
			.index-sidebar { display: none; }
			.fleet-wrapper { display: block; padding: 0 15px; }
			.mobile-index-card { display: flex; flex: 0 0 65%; background: #f8f9fa; border-radius: 24px; padding: 30px; scroll-snap-align: start; flex-direction: column; justify-content: center; border: 1px solid #eee; }
			.vehicle-card { flex: 0 0 82%; }
			.nav-btn { display: flex; width: 38px; height: 38px; top: auto; bottom: -15px; transform: none; }
			.prev { left: 30%; } .next { right: 30%; }
		}
	");
});

// 3. Shortcode
add_shortcode( 'fleet_gallery', function( $atts ) {
	wp_enqueue_style( 'fleet-gallery-css' );
	$a = shortcode_atts( array(
		'category' => '', // This is the category slug (e.g., 'scooters')
		'index_title' => 'Rental Standard',
		'index_items' => 'Full Insurance,2 Helmets,Unlimited KM,24/7 Assist,Free Delivery'
	), $atts );

	$items = explode(',', $a['index_items']);
	$items_html = ''; foreach($items as $i) $items_html .= '<li>'.esc_html(trim($i)).'</li>';

	// Query Setup with Category Filtering
	$args = array(
		'post_type' => 'fleet_vehicle',
		'posts_per_page' => -1,
		'orderby' => 'menu_order',
		'order' => 'ASC'
	);

	if ( !empty($a['category']) ) {
		$args['category_name'] = $a['category'];
	}

	$query = new WP_Query($args);
	$uid = uniqid();

	ob_start(); ?>
	<div class="fleet-wrapper">
		<aside class="index-sidebar">
			<h2><?php echo esc_html($a['index_title']); ?></h2>
			<ul class="index-list"><?php echo $items_html; ?></ul>
		</aside>

		<div class="fleet-main" id="wrap_<?php echo $uid; ?>">
			<button class="nav-btn prev" id="prev_<?php echo $uid; ?>" onclick="mfgScroll('<?php echo $uid; ?>', -1)" disabled>❮</button>
			
			<div class="fleet-row" id="row_<?php echo $uid; ?>" onscroll="mfgUpdate('<?php echo $uid; ?>')">
				<div class="mobile-index-card">
					<h2><?php echo esc_html($a['index_title']); ?></h2>
					<ul class="index-list"><?php echo $items_html; ?></ul>
					<div class="swipe-hint" id="hint_<?php echo $uid; ?>">
						<span class="swipe-hand">👉</span> <span>Swipe to explore</span>
					</div>
				</div>

				<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); 
					$price = get_post_meta(get_the_ID(), '_fleet_price', true);
					$link = get_post_meta(get_the_ID(), '_fleet_link', true) ?: '#';
					$wa = get_post_meta(get_the_ID(), '_fleet_whatsapp', true);
					$specs = explode(',', get_the_excerpt());
				?>
				<div class="vehicle-card">
					<?php if($price): ?>
						<div class="price-badge">€<?php echo esc_html($price); ?></div>
					<?php endif; ?>
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
			const h = document.getElementById('hint_' + id);
			document.getElementById('prev_' + id).disabled = r.scrollLeft <= 10;
			document.getElementById('next_' + id).disabled = r.scrollLeft + r.offsetWidth >= r.scrollWidth - 10;
			if(h && r.scrollLeft > 20) { h.style.opacity = '0'; }
		};
		window.mfgScroll = function(id, dir) {
			const r = document.getElementById('row_' + id);
			const c = r.querySelector('.vehicle-card') || r.querySelector('.mobile-index-card');
			r.scrollBy({ left: (c.offsetWidth + 20) * dir, behavior: 'smooth' });
		};
	</script>
	<?php return ob_get_clean();
});