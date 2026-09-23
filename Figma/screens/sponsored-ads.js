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
