window.addEventListener('click', function(e) {
    // 1. Find the closest wrapper to the click
    const wrapper = e.target.closest('.fleet-wrapper');
    if (!wrapper) return;

    const isMobile = window.innerWidth <= 768;
    if (!isMobile) return;

    const row = wrapper.querySelector('.fleet-row');
    const cards = wrapper.querySelectorAll('.vehicle-card');
    const dots = wrapper.querySelectorAll('.dot');
    
    // Calculate movement
    //const gap = 15;
	const card = cards[0];
	const style = window.getComputedStyle(card);
	const marginRight = parseFloat(style.marginRight);
	const cardWidth = card.offsetWidth + marginRight;

    // Get current index from a data attribute (or default to 0)
    let index = parseInt(wrapper.getAttribute('data-current-index')) || 0;

    // Next Button
    if (e.target.closest('.next')) {
        e.preventDefault();
        index = (index + 1 < cards.length) ? index + 1 : 0;
    } 
    // Prev Button
    else if (e.target.closest('.prev')) {
        e.preventDefault();
        index = (index - 1 >= 0) ? index - 1 : cards.length - 1;
    } 
    // Dot Click
    else if (e.target.classList.contains('dot')) {
        index = Array.from(dots).indexOf(e.target);
    } 
    else {
        return; // Clicked something else
    }

    // Apply movement
    row.style.transform = `translateX(${-index * cardWidth}px)`;
    
    // Update Dots
    dots.forEach((d, i) => d.classList.toggle('active', i === index));
    
    // Save state back to wrapper
    wrapper.setAttribute('data-current-index', index);
});

// Reset on resize
window.addEventListener('resize', function() {
    document.querySelectorAll('.fleet-wrapper').forEach(w => {
        w.setAttribute('data-current-index', 0);
        const row = w.querySelector('.fleet-row');
        if (row) row.style.transform = 'none';
    });
});