const sponsoredSlideSets = {
  'ت': [
    'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=900&q=85',
    'https://images.unsplash.com/photo-1526738549149-8e07eca6c147?auto=format&fit=crop&w=900&q=85',
    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=85'
  ],
  'ن': [
    'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=900&q=85',
    'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=900&q=85',
    'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=900&q=85'
  ]
};

document.querySelectorAll('.youtube-ad').forEach(ad => {
  const image = ad.querySelector('.youtube-ad-media > img');
  const avatar = ad.querySelector('.youtube-ad-avatar')?.textContent.trim();
  const slides = ad.dataset.slides?.split('|').filter(Boolean) || sponsoredSlideSets[avatar] || [];
  const mediaLink = ad.querySelector('.youtube-ad-media');
  const ctaLink = ad.querySelector('.youtube-ad-cta');
  const chatLink = ad.querySelector('.youtube-ad-chat');
  const reelId = avatar === 'ت' ? 'electronics' : location.pathname.includes('19-store-details') ? 'jackets' : 'fashion';
  if (ctaLink) {
    const ctaLabel = ctaLink.textContent.trim();
    ctaLink.setAttribute('aria-label', ctaLabel);
    ctaLink.title = ctaLabel;
    ctaLink.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L21 8H7"/></svg>';
  }
  if (chatLink) {
    const chatLabel = chatLink.textContent.trim();
    chatLink.setAttribute('aria-label', chatLabel);
    chatLink.title = chatLabel;
    chatLink.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M8 11h8M8 15h5"/></svg>';
  }
  const openReel = event => {
    event.preventDefault();
    location.href = `25-sponsored-reel.html?ad=${reelId}`;
  };
  mediaLink?.setAttribute('aria-label', 'فتح الإعلان بملء الشاشة');
  mediaLink?.addEventListener('click', openReel);
  ad.addEventListener('click', event => {
    if (event.target.closest('.youtube-ad-actions a, button, .youtube-ad-media')) return;
    openReel(event);
  });
  if (!image || slides.length < 2) return;

  let activeSlide = 0;
  ad.classList.add('is-autoplay');

  window.setInterval(() => {
    activeSlide = (activeSlide + 1) % slides.length;
    image.classList.add('is-changing');
    window.setTimeout(() => {
      image.src = slides[activeSlide];
      image.classList.remove('is-changing');
      ad.classList.remove('restart-progress');
      void ad.offsetWidth;
      ad.classList.add('restart-progress');
    }, 180);
  }, 4200);
});
