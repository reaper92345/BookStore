document.addEventListener('DOMContentLoaded', function () {
  console.log('Main.js loaded v2');
  const search = document.getElementById('searchInput');
  const grid = document.getElementById('booksGrid');

  if (search && grid) {
    search.addEventListener('input', function (e) {
      const q = e.target.value.trim().toLowerCase();
      const cards = grid.querySelectorAll('.card');
      cards.forEach(card => {
        const title = (card.dataset.title || '').toLowerCase();
        const author = (card.dataset.author || '').toLowerCase();
        const visible = !q || title.includes(q) || author.includes(q);
        card.style.display = visible ? '' : 'none';
      });
    });

    // Add-to-cart handler (delegated)
    grid.addEventListener('click', async function (e) {
      const btn = e.target.closest('.btn-buy');
      if (!btn) return;

      const card = btn.closest('.card');
      const bookId = btn.dataset.id || (card && card.dataset.id);
      if (!bookId) {
        console.error('Add to cart: missing book id');
        return;
      }

      // Prevent double clicks
      if (btn.disabled) return;
      btn.disabled = true;
      const originalText = btn.textContent;
      btn.textContent = 'Adding...';

      // Get or create cart_id
      let cartId = localStorage.getItem('cart_id');
      if (!cartId) {
        cartId = 'cart_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
        localStorage.setItem('cart_id', cartId);
      }

      const payload = {
        book_id: parseInt(bookId, 10),
        quantity: 1,
        cart_id: cartId
      };
      const url = '/api/cart/';

      let success = false;
      try {
        const resp = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        if (resp.ok) {
          success = true;
        } else {
          console.warn('Add to cart failed at', url, 'status', resp.status);
        }
      } catch (err) {
        console.warn('Add to cart request failed to', url, err.message || err);
      }

      if (success) {
        btn.textContent = 'Added';
        btn.classList.add('added');
        setTimeout(() => {
          btn.textContent = originalText;
          btn.disabled = false;
          btn.classList.remove('added');
        }, 1500);
      } else {
        btn.textContent = 'Failed';
        btn.classList.add('failed');
        setTimeout(() => {
          btn.textContent = originalText;
          btn.disabled = false;
          btn.classList.remove('failed');
        }, 2000);
      }
    });
  }
  // Cart Page Logic
  const cartItemsList = document.getElementById('cartItemsList');
  const cartSummaryCard = document.getElementById('cartSummaryCard');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const paymentModal = document.getElementById('paymentModal');

  if (cartItemsList) {
    loadCart();
  }

  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      if (paymentModal) paymentModal.classList.add('active');
    });
  }

  window.closeModal = function () {
    if (paymentModal) paymentModal.classList.remove('active');
  }

  window.selectPayment = function (provider) {
    window.location.href = `/progress.php?provider=${encodeURIComponent(provider)}`;
  }

  async function loadCart() {
    const cartId = localStorage.getItem('cart_id');
    console.log('Loading cart for ID:', cartId);

    if (!cartId) {
      renderEmptyCart();
      return;
    }

    try {
      console.log('Fetching cart items...');
      const resp = await fetch(`/api/cart/?cart_id=${cartId}`);
      if (!resp.ok) throw new Error(`Failed to fetch cart: ${resp.status}`);
      const items = await resp.json();
      console.log('Cart items:', items);

      if (!Array.isArray(items) || items.length === 0) {
        renderEmptyCart();
        return;
      }

      console.log('Fetching books...');
      const booksResp = await fetch('/api/books/');
      if (!booksResp.ok) throw new Error(`Failed to fetch books: ${booksResp.status}`);
      const books = await booksResp.json();

      if (!Array.isArray(books)) throw new Error('Invalid books response');

      const booksMap = {};
      books.forEach(b => booksMap[b.id] = b);

      let html = '';
      let total = 0;

      items.forEach(item => {
        const book = booksMap[item.book_id] || { title: 'Unknown Book', price: 0, author: '', id: 0 };
        const itemTotal = book.price * item.quantity;
        total += itemTotal;

        // Use a placeholder if no image (backend doesn't provide one yet)
        const imageSrc = book.thumbnail_path || '/images/book-placeholder.png';

        html += `
          <div class="cart-item" data-id="${item.id}">
            <img src="${imageSrc}" alt="${book.title}" class="cart-item-image">
            <div class="cart-item-details">
              <h3>${book.title}</h3>
              <p>${book.author}</p>
              <div class="cart-item-controls" style="margin-top:8px">
                  <div class="quantity-controls">
                      <button class="quantity-btn" onclick="updateCartItem(${item.id}, ${item.quantity - 1})">-</button>
                      <span class="quantity-display">${item.quantity}</span>
                      <button class="quantity-btn" onclick="updateCartItem(${item.id}, ${item.quantity + 1})">+</button>
                  </div>
                  <button class="remove-btn" onclick="removeCartItem(${item.id})" aria-label="Remove item">
                      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  </button>
              </div>
            </div>
            <div class="cart-item-price">
              <span class="unit-price">NPR ${book.price}</span>
              <span class="total-price">NPR ${itemTotal.toFixed(2)}</span>
            </div>
          </div>
        `;
      });

      cartItemsList.innerHTML = html;
      document.getElementById('cartSubtotal').textContent = total.toFixed(2);
      document.getElementById('cartTotal').textContent = total.toFixed(2);
      cartSummaryCard.style.display = 'block';

    } catch (err) {
      console.error('Load cart error:', err);
      cartItemsList.innerHTML = `<div class="empty">Failed to load cart: ${err.message}</div>`;
    }
  }

  window.updateCartItem = async function (itemId, newQuantity) {
    if (newQuantity < 1) return;
    try {
      const resp = await fetch(`/api/cart/items/${itemId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ quantity: newQuantity })
      });
      if (resp.ok) {
        loadCart(); // Reload to update totals
      }
    } catch (err) {
      console.error('Update item failed', err);
    }
  }

  window.removeCartItem = async function (itemId) {
    if (!confirm('Remove this item?')) return;
    try {
      const resp = await fetch(`/api/cart/items/${itemId}`, {
        method: 'DELETE'
      });
      if (resp.ok) {
        loadCart();
      }
    } catch (err) {
      console.error('Remove item failed', err);
    }
  }

  function renderEmptyCart() {
    const grid = document.getElementById('cartGrid');
    if (grid) {
      grid.innerHTML = `
            <div class="cart-empty-state">
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any books yet.</p>
                <a href="/books.php" class="btn btn-primary">Browse Books</a>
            </div>
        `;
    }
  }
});
