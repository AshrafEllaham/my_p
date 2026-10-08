document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const type = params.get('type') === 'store' ? 'store' : 'user';
  const savedLanguage = params.get('lang') || localStorage.getItem('souq_lang') || 'ar';
  const language = savedLanguage === 'en' ? 'en' : 'ar';
  const apiBaseUrl = 'http://127.0.0.1:8000/api';
  const list = document.getElementById('faqList');
  const state = document.getElementById('faqLoadState');
  const message = document.getElementById('faqStateMessage');
  const spinner = state.querySelector('.faq-spinner');
  const retry = document.getElementById('faqRetry');
  const copy = language === 'en'
      ? {
        title: 'Frequently asked questions',
        userIntro: 'Quick answers about shopping, pickup, and contacting stores.',
        storeIntro: 'Quick answers about managing your store on Saey.',
        loading: 'Loading frequently asked questions...',
        empty: 'There are no frequently asked questions for this account type yet.',
        error: 'Could not load frequently asked questions.',
        retry: 'Try again',
        back: type === 'store' ? 'Back to store account' : 'Back to account',
      }
      : {
        title: 'الأسئلة الشائعة',
        userIntro: 'إجابات سريعة عن الشراء والاستلام والتواصل مع المتاجر.',
        storeIntro: 'إجابات سريعة عن إدارة متجرك على سعي.',
        loading: 'جارٍ تحميل الأسئلة الشائعة...',
        empty: 'لا توجد أسئلة شائعة لهذا النوع من الحسابات حاليًا.',
        error: 'تعذر تحميل الأسئلة الشائعة.',
        retry: 'إعادة المحاولة',
        back: type === 'store' ? 'العودة إلى حساب المتجر' : 'العودة إلى حسابي',
      };

  document.documentElement.lang = language;
  document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
  document.title = `${language === 'en' ? 'Saey' : 'سعي'} — ${copy.title}`;
  document.querySelector('.view-head h1').textContent = copy.title;
  document.getElementById('faqIntro').textContent = type === 'store' ? copy.storeIntro : copy.userIntro;
  const backLink = document.getElementById('faqBackLink');
  const backParams = new URLSearchParams({lang: language});
  if (params.has('theme')) backParams.set('theme', params.get('theme'));
  backLink.href = `${type === 'store' ? '20-distributor-account.html' : '15-account.html'}?${backParams.toString()}`;
  backLink.setAttribute('aria-label', copy.back);
  retry.textContent = copy.retry;

  function showState(text, {loading = false, canRetry = false} = {}) {
    message.textContent = text;
    spinner.hidden = !loading;
    retry.hidden = !canRetry;
    state.hidden = false;
    list.setAttribute('aria-busy', String(loading));
  }

  async function loadFaqs() {
    showState(copy.loading, {loading: true});
    list.replaceChildren();

    try {
      const response = await fetch(`${apiBaseUrl}/faqs?type=${encodeURIComponent(type)}`, {
        headers: {Accept: 'application/json', 'Accept-Language': language},
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.message || copy.error);

      const faqs = Array.isArray(payload.data) ? payload.data : [];
      if (faqs.length === 0) {
        showState(copy.empty);
        return;
      }

      list.replaceChildren(...faqs.map((faq, index) => {
        const item = document.createElement('details');
        const question = document.createElement('summary');
        const answer = document.createElement('p');
        question.textContent = faq.question || '';
        answer.textContent = faq.answer || '';
        if (index === 0) item.open = true;
        item.append(question, answer);
        return item;
      }));
      state.hidden = true;
      list.setAttribute('aria-busy', 'false');
    } catch (error) {
      showState(error.message || copy.error, {canRetry: true});
    }
  }

  retry.addEventListener('click', loadFaqs);
  window.addEventListener('message', (event) => {
    if (event.data?.type !== 'SET_LANG' || !['ar', 'en'].includes(event.data.lang)) return;

    const nextParams = new URLSearchParams(window.location.search);
    nextParams.set('lang', event.data.lang);
    window.location.search = nextParams.toString();
  });
  loadFaqs();
});
