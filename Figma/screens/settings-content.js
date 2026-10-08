document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const savedLanguage = params.get('lang') || localStorage.getItem('souq_lang') || 'ar';
  const language = savedLanguage === 'en' ? 'en' : 'ar';
  const field = document.body.dataset.settingsField;
  const copy = language === 'en'
    ? {
        about_app: 'About Saey',
        privacy: 'Privacy Policy',
        terms_conditions: 'Terms and Conditions',
        loading: 'Loading application settings...',
        empty: 'This content is not available yet.',
        error: 'Could not load application settings.',
        retry: 'Try again',
        back: 'Back to account',
      }
    : {
        about_app: 'عن سعي',
        privacy: 'سياسة الخصوصية',
        terms_conditions: 'الشروط والأحكام',
        loading: 'جارٍ تحميل إعدادات التطبيق...',
        empty: 'هذا المحتوى غير متاح حاليًا.',
        error: 'تعذر تحميل إعدادات التطبيق.',
        retry: 'إعادة المحاولة',
        back: 'العودة إلى حسابي',
      };
  const title = copy[field];
  const content = document.getElementById('settingsContentText');
  const webview = document.getElementById('settingsWebview');
  const state = document.getElementById('settingsLoadState');
  const retry = document.getElementById('settingsRetry');
  const back = document.getElementById('settingsBackLink');

  document.documentElement.lang = language;
  document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
  document.title = `${language === 'en' ? 'Saey' : 'سعي'} — ${title}`;
  document.querySelector('.view-head h1').textContent = title;
  back.href = `15-account.html?lang=${language}${params.has('theme') ? `&theme=${encodeURIComponent(params.get('theme'))}` : ''}`;
  back.setAttribute('aria-label', copy.back);
  content.style.whiteSpace = 'pre-line';
  webview.title = title;
  webview.addEventListener('load', () => {
    if (!webview.hasAttribute('src')) return;
    webview.hidden = false;
    state.hidden = true;
  });
  webview.addEventListener('error', () => showState(copy.error, true));
  retry.textContent = copy.retry;

  function showState(message, canRetry = false) {
    state.textContent = message;
    state.hidden = false;
    retry.hidden = !canRetry;
  }

  async function loadSettings() {
    showState(copy.loading);
    retry.hidden = true;
    content.textContent = '';
    webview.hidden = true;
    webview.removeAttribute('src');

    try {
      const response = await fetch('http://127.0.0.1:8000/api/settings', {
        headers: {Accept: 'application/json', 'Accept-Language': language},
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.message || copy.error);

      const value = payload.data?.[field];
      if (typeof value !== 'string' || value.trim() === '') {
        showState(copy.empty);
        return;
      }

      const isPageUrl = /^https?:\/\//i.test(value);
      if (isPageUrl) {
        webview.src = new URL(value).href;
      } else {
        content.textContent = value;
        content.hidden = false;
        state.hidden = true;
      }
    } catch (error) {
      showState(error.message || copy.error, true);
    }
  }

  retry.addEventListener('click', loadSettings);
  window.addEventListener('message', (event) => {
    if (event.data?.type !== 'SET_LANG' || !['ar', 'en'].includes(event.data.lang)) return;
    params.set('lang', event.data.lang);
    window.location.search = params.toString();
  });
  loadSettings();
});
