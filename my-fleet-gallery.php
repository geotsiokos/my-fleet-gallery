<?php
/**
 * Plugin Name: Modern Fleet Gallery
 * Description: Premium vehicle gallery with "Peek" logic for all screen sizes to encourage scrolling.
 * Version: 4.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Register Post Type & Taxonomy
add_action( 'init', function() {
	register_post_type( 'fleet_vehicle', [
			'labels' => [
					'name' => 'Vehicles',
					'singular_name' => 'Vehicle',
					'add_new_item' => 'Add New Vehicle'
			],
			'public' => true,
			'menu_icon' => 'dashicons-cart',
			'supports' => [ 'title', 'thumbnail', 'excerpt', 'page-attributes' ],
			'taxonomies' => [ 'category' ],
	]);
});
	
	// 2. Meta Boxes
	add_action('add_meta_boxes', function() {
		add_meta_box('fleet_details_id', 'Vehicle Details', 'fleet_details_html', 'fleet_vehicle', 'side');
	});
		
		function fleet_details_html($post) {
			$price = get_post_meta($post->ID, '_fleet_price', true);
			$url = get_post_meta($post->ID, '_fleet_link', true);
			echo '<p><label>Price (€):</label><input type="text" name="fleet_price_field" value="'.esc_attr($price).'" style="width:100%"></p>';
			echo '<p><label>CTA Link:</label><input type="text" name="fleet_link_field" value="'.esc_attr($url).'" style="width:100%"></p>';
		}
		
		add_action('save_post', function($post_id) {
			if (isset($_POST['fleet_price_field'])) update_post_meta($post_id, '_fleet_price', $_POST['fleet_price_field']);
			if (isset($_POST['fleet_link_field'])) update_post_meta($post_id, '_fleet_link', $_POST['fleet_link_field']);
		});
			
			// 3. Optimized Styles with "Peek-a-Boo" Logic
			add_action( 'wp_enqueue_scripts', function() {
				wp_register_style( 'fleet-gallery-css', false );
				wp_add_inline_style( 'fleet-gallery-css', "
		.fleet-container { max-width: 1400px; margin: 40px auto; position: relative; font-family: -apple-system, system-ui, sans-serif; }
		.fleet-row {
			display: flex;
			gap: 25px;
			padding: 20px 10px 40px;
			overflow-x: auto;
			scroll-snap-type: x mandatory;
			scroll-behavior: smooth;
			scrollbar-width: none;
			-ms-overflow-style: none;
		}
		.fleet-row::-webkit-scrollbar { display: none; }

		.vehicle-card {
			flex: 0 0 31%;
			background: #fff;
			border-radius: 24px;
			box-shadow: 0 12px 40px rgba(0,0,0,0.06);
			scroll-snap-align: start;
			overflow: hidden;
			position: relative;
			display: flex;
			flex-direction: column;
			border: 1px solid #eee;
			transition: transform 0.3s ease;
		}
		.vehicle-card:hover { transform: translateY(-8px); }

		.price-tag { position: absolute; top: 15px; right: 15px; background: #fff; padding: 6px 14px; border-radius: 12px; font-weight: bold; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 5; }

		.image-box { width: 100%; height: 320px; background: #f8f8f8; }
		.image-box img { width: 100%; height: 100%; object-fit: cover; display: block; }

		.content { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
		.category-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; color: #a67c00; margin-bottom: 10px; display: block; }
		.model-name { font-size: 1.5rem; font-weight: 700; margin: 0 0 15px 0; color: #1e272e; line-height: 1.2; }

		.specs-list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 25px; }
		.spec-pill { background: #f1f3f5; color: #495057; padding: 6px 14px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; border: 1px solid #e9ecef; }

		.cta-btn { display: block; text-align: center; background: #1e272e; color: #fff !important; text-decoration: none !important; padding: 14px; border-radius: 14px; font-weight: bold; font-size: 1rem; transition: 0.3s; margin-top: auto; }
		.cta-btn:hover { background: #25D366; }

		.nav-btn { position: absolute; top: 45%; transform: translateY(-50%); width: 50px; height: 50px; background: #fff; border: none; border-radius: 50%; box-shadow: 0 6px 20px rgba(0,0,0,0.12); cursor: pointer; z-index: 10; font-size: 20px; display: flex; align-items: center; justify-content: center; }
		.prev { left: -10px; } .next { right: -10px; }

		@media (max-width: 1100px) {
			.vehicle-card { flex: 0 0 46%; }
			.image-box { height: 280px; }
		}

		@media (max-width: 768px) {
			.nav-btn { display: none; }
			.fleet-row { gap: 15px; padding-left: 15px; }
			.vehicle-card { flex: 0 0 82%; }
			.image-box { height: 240px; }
		}
	");
			});
				
				// 4. Shortcode Logic
				add_shortcode( 'fleet_gallery', function( $atts ) {
					wp_enqueue_style( 'fleet-gallery-css' );
					$atts = shortcode_atts( array('category' => ''), $atts );
					$args = array('post_type' => 'fleet_vehicle', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC');
					if ( !empty( $atts['category'] ) ) { $args['category_name'] = $atts['category']; }

					$query = new WP_Query( $args );
					$unique_id = 'fleet_' . uniqid();

					ob_start(); ?>
	<div class="fleet-container" id="<?php echo $unique_id; ?>">
		<button class="nav-btn prev" onclick="scrollFleet('<?php echo $unique_id; ?>', -1)">❮</button>
		<div class="fleet-row" id="row_<?php echo $unique_id; ?>">
			<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); 
				$price = get_post_meta(get_the_ID(), '_fleet_price', true);
				$link = get_post_meta(get_the_ID(), '_fleet_link', true);
				$specs = explode(',', get_the_excerpt());
				$cats = get_the_category();
			?>
			<div class="vehicle-card">
				<?php if($price): ?><div class="price-tag">€<?php echo esc_html($price); ?></div><?php endif; ?>
				<div class="image-box">
					<?php if ( has_post_thumbnail() ) : the_post_thumbnail('large'); else : ?>
						<img src="https://via.placeholder.com/600x400?text=No+Image" alt="">
					<?php endif; ?>
				</div>
				<div class="content">
					<?php if ( !empty($cats) ) : ?>
						<span class="category-label"><?php echo esc_html($cats[0]->name); ?></span>
					<?php endif; ?>
					<h3 class="model-name"><?php the_title(); ?></h3>
					<div class="specs-list">
						<?php foreach ($specs as $spec): if(!empty(trim($spec))): ?>
							<span class="spec-pill"><?php echo esc_html(trim($spec)); ?></span>
						<?php endif; endforeach; ?>
					</div>
					<?php if($link): ?>
						<a href="<?php echo esc_url($link); ?>" class="cta-btn" target="_blank">Book Now</a>
					<?php endif; ?>
				</div>
			</div>
			<?php endwhile; wp_reset_postdata(); endif; ?>
		</div>
		<button class="nav-btn next" onclick="scrollFleet('<?php echo $unique_id; ?>', 1)">❯</button>
	</div>

	<script>
		if (typeof scrollFleet !== 'function') {
			window.scrollFleet = function(id, dir) {
				const r = document.getElementById('row_' + id);
				const c = r.querySelector('.vehicle-card');
				if (c) {
					const gap = parseInt(window.getComputedStyle(r).gap) || 25;
					r.scrollBy({ left: (c.offsetWidth + gap) * dir, behavior: 'smooth' });
				}
			};
		}
	</script>
	<?php
	return ob_get_clean();
});