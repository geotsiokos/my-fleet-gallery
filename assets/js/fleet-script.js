document.addEventListener('DOMContentLoaded', function() {
    const galleries = document.querySelectorAll('.fleet-wrapper');

    galleries.forEach(wrapper => {
        const row = wrapper.querySelector('.fleet-row');
        const dotsCont = wrapper.querySelector('.fleet-dots');
        const prevBtn = wrapper.querySelector('.nav-btn.prev');
        const nextBtn = wrapper.querySelector('.nav-btn.next');
        const speed = parseInt(wrapper.getAttribute('data-autoplay')) || 5000;
        let timer;

        function updateUI() {
            const cards = row.querySelectorAll('.vehicle-card');
            const dots = dotsCont.querySelectorAll('.dot');
            if(!cards.length) return;

            prevBtn.disabled = row.scrollLeft <= 10;
            nextBtn.disabled = row.scrollLeft + row.offsetWidth >= row.scrollWidth - 15;

            let index = Math.round(row.scrollLeft / (cards[0].offsetWidth + 15));
            index = Math.max(0, Math.min(index, dots.length - 1));
            dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
        }

        function scroll(dir) {
            const card = row.querySelector('.vehicle-card');
            if(!card) return;
            const isAtEnd = row.scrollLeft + row.offsetWidth >= row.scrollWidth - 15;
            
            if (dir === 1 && isAtEnd) {
                row.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                row.scrollBy({ left: (card.offsetWidth + 15) * dir, behavior: 'smooth' });
            }
            resetAutoplay();
        }

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

        function startAutoplay() { if (speed > 0) timer = setInterval(() => scroll(1), speed); }
        function resetAutoplay() { clearInterval(timer); startAutoplay(); }

        // Init
        createDots();
        startAutoplay();
        row.addEventListener('scroll', updateUI);
        prevBtn.onclick = () => scroll(-1);
        nextBtn.onclick = () => scroll(1);
        wrapper.addEventListener('mouseenter', () => clearInterval(timer));
        wrapper.addEventListener('mouseleave', startAutoplay);
        
        // Final UI check
        setTimeout(updateUI, 100);
    });
});