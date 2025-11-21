// Site Arama Fonksiyonu
document.addEventListener('DOMContentLoaded', function() {
    const searchBox = document.getElementById('searchBox');
    const premiumGrid = document.getElementById('premiumGrid');
    const cards = premiumGrid ? premiumGrid.querySelectorAll('.premium-card') : [];

    if (searchBox && cards.length > 0) {
        searchBox.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();

            cards.forEach(card => {
                const siteName = card.getAttribute('data-name') || '';

                if (siteName.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
