(function() {
  async function loadBooks() {
    try {
      const resp = await fetch('/api/books');
      if (!resp.ok) {
        document.getElementById('books').innerText = 'Failed to load books.';
        return;
      }
      const books = await resp.json();
      const ul = document.getElementById('books');
      ul.innerHTML = '';
      books.forEach(function(book) {
        const li = document.createElement('li');
        const h3 = document.createElement('h3');
        h3.textContent = book.title || 'Untitled';
        const pAuthor = document.createElement('p');
        pAuthor.textContent = book.author || '';
        const pDesc = document.createElement('p');
        pDesc.textContent = book.description || '';
        li.appendChild(h3);
        li.appendChild(pAuthor);
        li.appendChild(pDesc);
        ul.appendChild(li);
      });
    } catch (err) {
      console.error(err);
      document.getElementById('books').innerText = 'Error loading books.';
    }
  }

  // Simple client-side routing for / and /books
  function router() {
    const path = window.location.pathname;
    const bookListSection = document.getElementById('book-list');
    if (path === '/' || path === '/index.html') {
      bookListSection.style.display = 'block';
    } else if (path === '/books') {
      bookListSection.style.display = 'block';
    } else {
      bookListSection.style.display = 'block';
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    router();
    loadBooks();
  });
})();
