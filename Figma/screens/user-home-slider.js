/* Demo content for the home sliders in the interactive preview. */
document.addEventListener('DOMContentLoaded', () => {
  const heart = '<i class="product-favorite" aria-label="أضف للمفضلة"><svg viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.6a5.5 5.5 0 0 0-.1-7.8Z"/></svg></i>';
  const extras = [
    [
      { image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=500&q=85', alt: 'حذاء رياضي', badge: 'الأكثر طلبًا', name: 'حذاء رياضي يومي مريح', price: '٩٥٠ ج.م', category: 'أحذية', rating: '4.7' },
      { image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=500&q=85', alt: 'ساعة ذكية', badge: 'خصم ١٥٪', name: 'ساعة ذكية للمتابعة اليومية', price: '١,٨٥٠ ج.م', category: 'إلكترونيات', rating: '4.9' }
    ],
    [
      { image: 'https://images.unsplash.com/photo-1523381294911-8d3cead13475?auto=format&fit=crop&w=500&q=85', alt: 'حقيبة يد', name: 'حقيبة يد عملية وأنيقة', price: '١,١٩٠ ج.م', category: 'إكسسوارات', rating: '4.8' }
    ],
    [
      { image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500&q=85', alt: 'سماعة لاسلكية', name: 'سماعة لاسلكية بعزل الضوضاء', price: '٢,٣٥٠ ج.م', category: 'إلكترونيات', rating: '4.9' }
    ]
  ];

  const makeCard = (item) => `<a class="market-product" href="12-product-details.html"><span class="market-product-image"><img src="${item.image}" alt="${item.alt}">${item.badge ? `<em>${item.badge}</em>` : ''}${heart}</span><strong>${item.name}</strong><span class="market-price"><b>${item.price}</b></span><span class="product-meta-row"><span class="market-product-category">${item.category}</span><small class="product-rating-only">${item.rating} <i>★</i></small></span></a>`;

  document.querySelectorAll('.market-product-grid').forEach((grid, index) => {
    (extras[index] || []).forEach(item => grid.insertAdjacentHTML('beforeend', makeCard(item)));
  });
});
