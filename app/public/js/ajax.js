// Fichier central pour la logique AJAX globale du site
// (c) Kevin - projet_perso

// --- AJOUT AU PANIER ---
// (plus besoin de DOMContentLoaded car le script est chargé avec defer)
// Délégation sur tous les boutons "ajouter" (panier)
document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('home-latest-add-btn') || e.target.classList.contains('home-popular-btn')) {
        e.preventDefault();
        const article = e.target.closest('article, .home-popular-card');
        const productId = article?.dataset?.id || article?.getAttribute('data-id') || 1; // à adapter avec l'id réel
        fetch(`/add-to-cart/${productId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ quantity: 1 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartBadge(data.cartCount);
                showFlash('Article ajouté au panier !', 'success');
            } else {
                showFlash(data.error || 'Erreur lors de l\'ajout au panier', 'error');
            }
        })
        .catch(() => showFlash('Erreur réseau lors de l\'ajout au panier', 'error'));
    }

    // --- SUPPRESSION DANS LE PANIER ---
    if (e.target.classList.contains('cart-remove-btn')) {
        e.preventDefault();
        const btn = e.target;
        const productId = btn.dataset.productId;
        fetch(`/cart/remove/${productId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Supprime la ligne du tableau
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                if (row) row.remove();
                // Met à jour le total du panier
                const total = document.querySelector('#cart-total');
                if (total) total.textContent = data.total.toLocaleString('fr-FR', {minimumFractionDigits: 2});
                // Met à jour le badge du panier
                updateCartBadge(data.cartCount);
                // Si le panier est vide, affiche le message
                if (data.cartCount === 0) {
                    const container = document.querySelector('.cart-main-container');
                    if (container) container.innerHTML = '<h1 class="cart-title">Votre panier</h1><p class="cart-empty">Votre panier est vide.</p>';
                }
                showFlash('Article retiré du panier.', 'success');
            } else {
                showFlash(data.error || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(() => showFlash('Erreur réseau lors de la suppression', 'error'));
    }

    // --- FAVORIS (like/unlike) ---
    if (e.target.classList.contains('like-button')) {
        e.preventDefault();
        const btn = e.target;
        const productId = btn.dataset.productId || btn.getAttribute('data-product-id');
        fetch(`/favorite/toggle/${productId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Toggle l'état du bouton
                btn.classList.toggle('active', data.isLiked);
                // Met à jour le compteur de likes si présent
                const countSpan = btn.querySelector('.likes-count');
                if (countSpan) countSpan.textContent = data.likesCount;
                showFlash(data.isLiked ? 'Ajouté aux favoris.' : 'Retiré des favoris.', 'success');
            } else {
                showFlash(data.error || 'Erreur favoris', 'error');
            }
        })
        .catch(() => showFlash('Erreur réseau favoris', 'error'));
    }
});

// --- Changement d'affichage de la grille Derniers ajouts ---
document.querySelectorAll('.home-latest-display-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        // Retire la classe active des autres boutons
        document.querySelectorAll('.home-latest-display-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        // Change la classe de la grille
        const cols = this.dataset.cols;
        const grid = document.querySelector('.home-latest-grid');
        if (grid) {
            grid.classList.remove('home-latest-grid-2', 'home-latest-grid-3', 'home-latest-grid-4');
            grid.classList.add('home-latest-grid-' + cols);
        }
    });
});


// Fonction utilitaire pour afficher un message flash
function showFlash(message, type = 'success') {
    let flash = document.createElement('div');
    flash.className = 'alert alert-' + type;
    flash.textContent = message;
    document.body.appendChild(flash);
    setTimeout(() => flash.remove(), 2500);
}

// --- Utilitaires réutilisables ---
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge');
    if (badge) badge.textContent = count;
}

// --- Placeholders pour d'autres actions AJAX (à compléter) ---
// Ex : suppression panier, favoris, etc.