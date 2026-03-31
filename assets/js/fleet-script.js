document.addEventListener('DOMContentLoaded', function() {
    const wrappers = document.querySelectorAll('.fleet-wrapper');

    wrappers.forEach(wrapper => {
        const row = wrapper.querySelector('.fleet-row');
        const cards = wrapper.querySelectorAll('.vehicle-card');
        const prevBtn = wrapper.querySelector('.prev');
        const nextBtn = wrapper.querySelector('.next');
        const dotsCont = wrapper.querySelector('.fleet-dots');
        
        let index = 0;

        // Function to check if we are on mobile
        const isMobile = () => window.innerWidth <= 768;

        // Init Dots
        cards.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.className = 'dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => { if(isMobile()) goToSlide(i); });
            dotsCont.appendChild(dot);
        });

        const dots = dotsCont.querySelectorAll('.dot');

        function updateSlider() {
            if (!isMobile()) {
                row.style.transform = 'none'; // Reset transform on Desktop
                return;
            }
            const cardWidth = cards[0].offsetWidth + 20; // 20 is gap
            row.style.transform = `translateX(${-index * cardWidth}px)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === index));
        }

        function goToSlide(n) {
            index = (n + cards.length) % cards.length;
            updateSlider();
        }

        prevBtn.addEventListener('click', () => goToSlide(index - 1));
        nextBtn.addEventListener('click', () => goToSlide(index + 1));
        
        window.addEventListener('resize', updateSlider);
        updateSlider(); // Initial check
    });
});