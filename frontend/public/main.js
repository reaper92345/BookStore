document.addEventListener('DOMContentLoaded', function(){
  const search = document.getElementById('searchInput');
  const grid = document.getElementById('booksGrid');
  if(!search || !grid) return;

  search.addEventListener('input', function(e){
    const q = e.target.value.trim().toLowerCase();
    const cards = grid.querySelectorAll('.card');
    cards.forEach(card => {
      const title = (card.dataset.title||'').toLowerCase();
      const author = (card.dataset.author||'').toLowerCase();
      const visible = !q || title.includes(q) || author.includes(q);
      card.style.display = visible ? '' : 'none';
    });
  });
});
