function scrollMoods(direction) {
            const container = document.getElementById('moodChips');
            const scrollAmount = 200;
            
            if (direction === 'left') {
                container.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            } else {
                container.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            }
            
            // Update scroll button visibility
            setTimeout(updateScrollButtons, 300);
        }

        function updateScrollButtons() {
            const container = document.getElementById('moodChips');
            const leftBtn = document.getElementById('scrollLeft');
            const rightBtn = document.getElementById('scrollRight');
            
            // Hide left button if at start
            if (container.scrollLeft <= 0) {
                leftBtn.style.opacity = '0.3';
                leftBtn.style.pointerEvents = 'none';
            } else {
                leftBtn.style.opacity = '1';
                leftBtn.style.pointerEvents = 'auto';
            }
            
            // Hide right button if at end
            if (container.scrollLeft >= container.scrollWidth - container.clientWidth) {
                rightBtn.style.opacity = '0.3';
                rightBtn.style.pointerEvents = 'none';
            } else {
                rightBtn.style.opacity = '1';
                rightBtn.style.pointerEvents = 'auto';
            }
        }

        // Clear Filter Functions
        function clearFilter(filterType) {
            const params = new URLSearchParams(window.location.search);
            
            switch(filterType) {
                case 'search':
                    params.delete('search');
                    break;
                case 'mood':
                    params.delete('mood');
                    break;
                case 'date':
                    params.delete('month');
                    params.delete('year');
                    break;
            }
            
            window.location.search = params.toString();
        }

        // Sort Update Function
        function updateSort(sortValue) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sortValue);
            window.location.search = params.toString();
        }

        // Auto-submit on month/year change
        document.getElementById('monthSelect').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        document.getElementById('yearSelect').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        // Enhanced search with debouncing
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Optional: Auto-submit after typing stops
                // document.getElementById('filterForm').submit();
            }, 1000);
        });

        // Initialize scroll buttons on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateScrollButtons();
            
            // Update scroll buttons on container scroll
            document.getElementById('moodChips').addEventListener('scroll', updateScrollButtons);
            
            // Update scroll buttons on window resize
            window.addEventListener('resize', updateScrollButtons);
            
            // Auto-scroll to active mood chip
            const activeChip = document.querySelector('.mood-chip.active');
            if (activeChip && activeChip !== document.querySelector('.mood-chip')) {
                activeChip.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        });

        // Touch/swipe support for mobile
        let startX = 0;
        let scrollLeft = 0;

        document.getElementById('moodChips').addEventListener('touchstart', function(e) {
            startX = e.touches[0].pageX - this.offsetLeft;
            scrollLeft = this.scrollLeft;
        });

        document.getElementById('moodChips').addEventListener('touchmove', function(e) {
            if (!startX) return;
            
            e.preventDefault();
            const x = e.touches[0].pageX - this.offsetLeft;
            const walk = (x - startX) * 2;
            this.scrollLeft = scrollLeft - walk;
        });

        document.getElementById('moodChips').addEventListener('touchend', function() {
            startX = 0;
            updateScrollButtons();
        });