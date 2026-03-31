/**
 * Modern Fleet Gallery - JS Logic v3.2.0
 */
document.addEventListener('DOMContentLoaded', function() {
	console.log('Fleet Gallery JS Loaded'); // Check console to verify

	const fleetGrids = document.querySelectorAll('.fleet-grid');

	fleetGrids.forEach(grid => {
		const parent = grid.parentElement;
		const dotsContainer = parent.querySelector('.fleet-dots');
		const cards = grid.querySelectorAll('.vehicle-card');

		if (!dotsContainer || cards.length < 2) return;

		// Clear existing dots and create new ones
		dotsContainer.innerHTML = '';
		cards.forEach((card, index) => {
			const dot = document.createElement('span');
			dot.classList.add('dot');
			if (index === 0) dot.classList.add('active');
			dotsContainer.appendChild(dot);
		});

		const dots = dotsContainer.querySelectorAll('.dot');

		// Intersection Observer to handle "Active" dot state
		const options = {
			root: grid,
			threshold: 0.5, // Trigger when 50% of the card is visible
			active: true
		};

		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					const cardIndex = Array.from(cards).indexOf(entry.target);
					
					// Update Dots
					dots.forEach((dot, i) => {
						if (i === cardIndex) {
							dot.classList.add('active');
						} else {
							dot.classList.remove('active');
						}
					});
				}
			});
		}, options);

		cards.forEach(card => observer.observe(card));
	});
});