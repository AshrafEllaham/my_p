document.addEventListener('DOMContentLoaded', () => {
  const language = new URLSearchParams(window.location.search).get('lang') === 'en' ? 'en' : 'ar';
  const apiBaseUrl = 'http://127.0.0.1:8000/api';
  const copy = language === 'en'
    ? {
        loading: 'Loading offers...',
        empty: 'No banners are available for this account type right now.',
        imageError: 'Could not load the banner image.',
        loadError: 'Could not load offers.',
        retry: 'Try again',
        banner: (index) => `Banner ${index + 1} from Saey`,
        showBanner: (index) => `Show banner ${index + 1}`,
        userLabel: 'Today’s offers',
        storeLabel: 'Store offers',
        pagerLabel: 'Offers pagination',
      }
    : {
        loading: 'جارٍ تحميل العروض...',
        empty: 'لا توجد بانرات لهذا النوع من الحسابات حاليًا.',
        imageError: 'تعذر تحميل صورة البانر.',
        loadError: 'تعذر تحميل العروض.',
        retry: 'إعادة المحاولة',
        banner: (index) => `بانر ${index + 1} من سعي`,
        showBanner: (index) => `عرض البانر ${index + 1}`,
        userLabel: 'عروض اليوم',
        storeLabel: 'عروض المتجر',
        pagerLabel: 'مؤشر العروض',
      };

  document.querySelectorAll('[data-banner-carousel]').forEach((hero) => {
    const type = hero.dataset.bannerType;
    const image = hero.querySelector('[data-banner-image]');
    const pager = hero.querySelector('[data-banner-pager]');
    const state = document.querySelector(`[data-banner-state-for="${type}"]`);
    if (!['user', 'store'].includes(type) || !image || !pager || !state) return;

    hero.setAttribute('aria-label', type === 'store' ? copy.storeLabel : copy.userLabel);
    pager.setAttribute('aria-label', copy.pagerLabel);
    let banners = [];
    let activeIndex = 0;
    let rotationTimer = null;

    const showState = (message, {loading = false, retry = false} = {}) => {
      if (rotationTimer) {
        window.clearInterval(rotationTimer);
        rotationTimer = null;
      }

      state.replaceChildren();
      if (loading) {
        const spinner = document.createElement('span');
        spinner.className = 'home-banner-state__spinner';
        spinner.setAttribute('aria-hidden', 'true');
        state.append(spinner);
      }

      const text = document.createElement('p');
      text.textContent = message;
      state.append(text);

      if (retry) {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = copy.retry;
        button.addEventListener('click', loadBanners, {once: true});
        state.append(button);
      }

      state.hidden = false;
      hero.hidden = true;
    };

    const renderBanner = (index) => {
      activeIndex = index;
      image.alt = copy.banner(index);
      pager.querySelectorAll('button').forEach((button, buttonIndex) => {
        button.classList.toggle('active', buttonIndex === index);
        button.setAttribute('aria-current', buttonIndex === index ? 'true' : 'false');
      });
      image.src = banners[index].image;
    };

    image.addEventListener('load', () => {
      hero.hidden = false;
      state.hidden = true;
    });

    image.addEventListener('error', () => {
      showState(copy.imageError, {retry: true});
    });

    async function loadBanners() {
      if (rotationTimer) window.clearInterval(rotationTimer);
      showState(copy.loading, {loading: true});

      try {
        const response = await fetch(`${apiBaseUrl}/banners?type=${encodeURIComponent(type)}`, {
          headers: {
            Accept: 'application/json',
            'Accept-Language': language,
          },
        });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || copy.loadError);

        banners = Array.isArray(payload.data)
          ? payload.data.filter((banner) => typeof banner.image === 'string' && banner.image.trim() !== '')
          : [];

        if (!banners.length) {
          showState(copy.empty);
          return;
        }

        pager.replaceChildren(...banners.map((banner, index) => {
          const dot = document.createElement('button');
          dot.type = 'button';
          dot.setAttribute('aria-label', copy.showBanner(index));
          dot.addEventListener('click', () => renderBanner(index));
          return dot;
        }));

        renderBanner(0);
        if (banners.length > 1) {
          rotationTimer = window.setInterval(() => {
            renderBanner((activeIndex + 1) % banners.length);
          }, 5000);
        }
      } catch (error) {
        showState(error.message || copy.loadError, {retry: true});
      }
    }

    loadBanners();
  });
});
