<?php
/**
 * Plugin Name: Modern Fleet Gallery
 * Description: Supports Vehicles, Auto-play and vehicle features.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Post Type & Meta (Standard)
add_action( 'init', function() {
	register_post_type(
		'fleet_vehicle', 
		[
			'labels' => [ 'name' => 'Vehicles', 'singular_name' => 'Vehicle' ],
			'public' => true,
			'menu_icon' => 'dashicons-cart',
			'supports' => [ 'title', 'thumbnail', 'excerpt' ],
			'taxonomies' => [ 'category' ],
		]
	);
});

	add_action('add_meta_boxes', function() {
		add_meta_box('fleet_details_id', 'Vehicle Details', 'fleet_details_html', 'fleet_vehicle', 'side');
	});

		function fleet_details_html($post) {
			$price = get_post_meta($post->ID, '_fleet_price', true);
			$url = get_post_meta($post->ID, '_fleet_link', true);
			echo '<p><label>Price (€):</label><input type="text" name="fleet_price_field" value="'.esc_attr($price).'" style="width:100%"></p>';
			echo '<p><label>Booking Link:</label><input type="text" name="fleet_link_field" value="'.esc_attr($url).'" style="width:100%"></p>';
		}

		add_action('save_post', function($post_id) {
			if (isset($_POST['fleet_price_field'])) update_post_meta($post_id, '_fleet_price', sanitize_text_field($_POST['fleet_price_field']));
			if (isset($_POST['fleet_link_field'])) update_post_meta($post_id, '_fleet_link', esc_url_raw($_POST['fleet_link_field']));
		});

			// 2. Styles (FIXED CENTERING)
			add_action( 'wp_enqueue_scripts', function() {
				wp_register_style( 'fleet-gallery-css', false );
				wp_add_inline_style( 'fleet-gallery-css', "
		.fleet-wrapper { display: flex; max-width: 1400px; margin: 40px auto; gap: 0; align-items: flex-start; position: relative; font-family: sans-serif; }
		.fleet-main { flex: 1; min-width: 0; position: relative; background: #fbfbfb; padding: 30px; border-radius: 16px; }

		.index-card-base { background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); z-index: 2; position: relative; }
		.index-sidebar { flex: 0 0 260px; position: sticky; top: 100px; margin-right: -20px; display: block; }
		.mobile-index-header { display: none; }

		.index-sidebar::after { content: ''; position: absolute; background: #fff; border: 1px solid #eee; width: 16px; height: 16px; transform: rotate(45deg); display: block; right: -8px; top: 40px; border-bottom: 0; border-left: 0; }

		.index-list { list-style: none; padding: 0; margin: 0 0 20px 0; border-bottom: 1px solid #f1f1f1; padding-bottom: 15px; }
		.index-list li { margin-bottom: 10px; display: flex; align-items: center; font-size: 0.9rem; font-weight: 600; color: #444; }
		.index-list li::before { content: '✓'; color: #25D366; font-weight: 900; margin-right: 10px; }

		.index-wa-support { display: flex; align-items: center; gap: 10px; text-decoration: none !important; color: #1e272e !important; font-weight: 700; font-size: 0.85rem; padding: 10px; background: #f9f9f9; border-radius: 10px; transition: 0.2s; }
		.index-wa-support:hover { background: #25D366; color: #fff !important; }
		.wa-icon { background: #25D366; color: white; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 14px; font-style: normal; }

		.fleet-row { display: flex; gap: 15px; padding: 10px 0; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
		.fleet-row::-webkit-scrollbar { display: none; }

		.vehicle-card { flex: 0 0 46%; background: #fff; border-radius: 16px; border: 1px solid #eee; overflow: hidden; position: relative; scroll-snap-align: start; }
		.price-badge { position: absolute; top: 12px; right: 12px; background: #1e272e; color: #fff; padding: 5px 12px; border-radius: 8px; font-weight: 800; font-size: 0.8rem; z-index: 5; }
		.image-box { width: 100%; height: 220px; }
		.image-box img { width: 100%; height: 100%; object-fit: cover; }

		.content { padding: 20px; flex: 1; display: flex; flex-direction: column; }
		.model-name { font-size: 1.25rem; font-weight: 800; margin: 0 0 10px 0; color: #111; }
		.specs-list { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 20px; }
		.spec-pill { background: #f1f3f5; color: #666; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }
		.cta-group { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 20px; }

		.btn-book { display: block; width: 100%; text-align: center; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; text-decoration: none !important; background: #1e272e; color: #fff !important; }

		/* FIXED: CENTERED DOTS */
		.fleet-dots { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 25px; width: 100%; }
		.dot { width: 8px; height: 8px; border-radius: 50%; background: #ccc; cursor: pointer; transition: 0.3s; border: none; padding: 0; outline: none; }
		.dot.active { background: #1e272e; width: 22px; border-radius: 10px; }

		.nav-btn { position: absolute; top: 50%; width: 40px; height: 40px; background: #fff; border-radius: 50%; border: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center; transform: translateY(-50%); }
		.nav-btn:disabled { opacity: 0.2; pointer-events: none; }
		.prev { left: 5px; } .next { right: 5px; }

		@media (max-width: 1100px) {
			.fleet-wrapper { display: block; padding: 0 15px; }
			.index-sidebar { display: none !important; }
			.mobile-index-header { display: block; margin-bottom: 20px; }
			.mobile-index-header .index-list { display: grid; grid-template-columns: 1fr 1fr; margin-bottom: 15px; }
			.fleet-main { padding: 10px 10px 70px 10px; }
			.vehicle-card { flex: 0 0 88%; }
			.nav-btn { top: auto; bottom: 20px; transform: none; }
			.prev { left: 10%; } .next { right: 10%; }
			.fleet-dots { margin-top: 20px; }
		}
	");
			});

		// 3. Shortcode
		add_shortcode( 'fleet_gallery', function( $atts ) {
			wp_enqueue_style( 'fleet-gallery-css' );
			$a = shortcode_atts( array(
				'category'    => '',
				'index_title' => 'What\'s Included',
				'index_items' => 'Full Insurance,2 Helmets,Unlimited KM,24/7 Assist,Free Delivery',
				'wa_number'   => '1234567890',
				'wa_text'     => 'Questions? WhatsApp Us',
				'autoplay'    => '5000'
			), $atts );

			$items_html = '';
			foreach(explode(',', $a['index_items']) as $i) { $items_html .= '<li>'.esc_html(trim($i)).'</li>'; }
			$clean_wa = preg_replace('/[^0-9]/', '', $a['wa_number']);
			$wa_html = '<a href="https://wa.me/'.$clean_wa.'" class="index-wa-support" target="_blank"><i class="wa-icon">w</i><span>'.esc_html($a['wa_text']).'</span></a>';

			$args = array('post_type' => 'fleet_vehicle', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC');
			if (!empty($a['category'])) { $args['category_name'] = $a['category']; }

			$query = new WP_Query($args);
			$uid = uniqid( $args['category_name'] . '_' );

			ob_start(); ?>
	<div class="fleet-wrapper" id="fleet_<?php echo $uid; ?>" data-autoplay="<?php echo esc_attr($a['autoplay']); ?>">
		<aside class="index-card-base index-sidebar">
			<h2 style="font-size: 1rem; margin-top:0;"><?php echo esc_html($a['index_title']); ?></h2>
			<ul class="index-list"><?php echo $items_html; ?></ul>
			<?php echo $wa_html; ?>
		</aside>

		<div class="fleet-main">
			<div class="index-card-base mobile-index-header">
				<h2 style="font-size: 0.95rem; margin-top:0;"><?php echo esc_html($a['index_title']); ?></h2>
				<ul class="index-list"><?php echo $items_html; ?></ul>
				<?php echo $wa_html; ?>
			</div>

			<button class="nav-btn prev" id="prev_<?php echo $uid; ?>" onclick="mfgScroll('<?php echo $uid; ?>', -1)">❮</button>

			<div class="fleet-row" id="row_<?php echo $uid; ?>" onscroll="mfgUpdate('<?php echo $uid; ?>')">
				<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); 
					$price = get_post_meta(get_the_ID(), '_fleet_price', true);
					$link = get_post_meta(get_the_ID(), '_fleet_link', true) ?: '#';
				?>
				<div class="vehicle-card">
					<?php if($price): ?><div class="price-badge">€<?php echo esc_html($price); ?></div><?php endif; ?>
					<div class="image-box"><?php the_post_thumbnail('medium_large'); ?></div>
					<div class="content">
						<h3 class="model-name"><?php the_title(); ?></h3>
						<div class="specs-list">
							<?php $specs = explode(',', get_the_excerpt()); 
							foreach($specs as $s) if(!empty(trim($s))) echo '<span class="spec-pill">'.esc_html(trim($s)).'</span>'; ?>
						</div>
						<div class="cta-group"><a href="<?php echo esc_url($link); ?>" class="btn-book">Book Online</a></div>
					</div>
				</div>
				<?php endwhile; wp_reset_postdata(); endif; ?>
			</div>

			<button class="nav-btn next" id="next_<?php echo $uid; ?>" onclick="mfgScroll('<?php echo $uid; ?>', 1)">❯</button>

			<div class="fleet-dots" id="dots_<?php echo $uid; ?>"></div>
		</div>
	</div>

	<script>
	(function() {
		const uid = '<?php echo $uid; ?>';
		const wrapper = document.getElementById('fleet_' + uid);
		const row = document.getElementById('row_' + uid);
		const dotsCont = document.getElementById('dots_' + uid);
		const speed = parseInt(wrapper.getAttribute('data-autoplay'));
		let timer;

		function createDots() {
			const cards = row.querySelectorAll('.vehicle-card');
			dotsCont.innerHTML = '';
			cards.forEach((_, i) => {
				const dot = document.createElement('button');
				dot.className = 'dot' + (i === 0 ? ' active' : '');
				dot.onclick = () => {
					row.scrollTo({ left: cards[i].offsetLeft - row.offsetLeft - 15, behavior: 'smooth' });
					resetAutoplay();
				};
				dotsCont.appendChild(dot);
			});
		}
		
		window.mfgUpdate = function(id) {
			const r = document.getElementById('row_' + id);
			const dotsWrap = document.getElementById('dots_' + id);
			if(!dotsWrap) return;
			const d = dotsWrap.querySelectorAll('.dot');
			const c = r.querySelectorAll('.vehicle-card');
			if (!c.length) return;

			document.getElementById('prev_' + id).disabled = r.scrollLeft <= 10;
			document.getElementById('next_' + id).disabled = r.scrollLeft + r.offsetWidth >= r.scrollWidth - 10;

			let index = Math.round(r.scrollLeft / (c[0].offsetWidth + 15));
			index = Math.max(0, Math.min(index, d.length - 1));
			d.forEach((dot, i) => dot.classList.toggle('active', i === index));
		};

		window.mfgScroll = function(id, dir) {
			const r = document.getElementById('row_' + id);
			const c = r.querySelector('.vehicle-card');
			if(c) {
				const isAtEnd = r.scrollLeft + r.offsetWidth >= r.scrollWidth - 15;
				if (dir === 1 && isAtEnd) {
					r.scrollTo({ left: 0, behavior: 'smooth' });
				} else {
					r.scrollBy({ left: (c.offsetWidth + 15) * dir, behavior: 'smooth' });
				}
			}
			resetAutoplay();
		};

		function startAutoplay() {
			if (speed > 0) {
				timer = setInterval(() => { window.mfgScroll(uid, 1); }, speed);
			}
		}

		function resetAutoplay() {
			clearInterval(timer);
			startAutoplay();
		}

		createDots();
		startAutoplay();
		wrapper.addEventListener('mouseenter', () => clearInterval(timer));
		wrapper.addEventListener('mouseleave', startAutoplay);
	})();
	</script>
	<?php return ob_get_clean();
});