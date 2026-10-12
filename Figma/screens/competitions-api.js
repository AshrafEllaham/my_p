(function () {
  const isLocalPreviewHost = ['localhost', '127.0.0.1'].includes(window.location.hostname);
  const apiOrigin = window.location.protocol === 'file:'
    ? 'http://localhost:8000'
    : isLocalPreviewHost && window.location.port !== '8000'
      ? `${window.location.protocol}//${window.location.hostname}:8000`
      : window.location.origin;
  const baseUrl = new URL(localStorage.getItem('saey_api_base') || '/api/store', apiOrigin).toString().replace(/\/$/, '');

  async function request(path, options = {}) {
    const token = localStorage.getItem('saey_api_token') || '';
    let response;
    try {
      response = await fetch(`${baseUrl}${path}`, {
        ...options,
        headers: {
          Accept: 'application/json',
          'Accept-Language': document.documentElement.lang || 'ar',
          ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
          ...(options.headers || {}),
        },
      });
    } catch (error) {
      const language = document.documentElement.lang || 'ar';
      throw new Error(language === 'ar' ? 'تعذر الاتصال بالخادم. تحقق من الاتصال ثم أعد المحاولة.' : 'Could not connect to the server. Check your connection and try again.');
    }
    const result = await response.json().catch(() => ({}));
    if (!response.ok || result.code >= 400) {
      const language = document.documentElement.lang || 'ar';
      const fallback = response.status === 401
        ? (language === 'ar' ? 'سجّل الدخول إلى حساب المتجر لعرض المسابقات.' : 'Sign in to your store account to view competitions.')
        : (language === 'ar' ? `تعذر تنفيذ الطلب (${response.status}).` : `The request failed (${response.status}).`);
      const error = new Error(Object.values(result.errors || {}).flat()[0] || result.message || fallback);
      error.status = response.status;
      throw error;
    }
    return result;
  }

  const fromApi = item => ({
    id: String(item.id),
    title: item.title,
    question: item.question,
    prize: item.prize,
    image: item.image,
    status: item.status,
    endsAt: item.ends_at,
    entriesCount: Number(item.entries_count || 0),
    correctAnswer: item.correct_answer || '',
    winnerId: item.winner_user_id ? String(item.winner_user_id) : '',
    winner: item.winner || null,
  });
  const imageUrl = value => {
    if (!value) return '';
    try {
      const url = new URL(value, window.location.href);
      return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
    } catch (error) {
      return '';
    }
  };
  const toMultipart = (item, methodOverride = false) => {
    const body = new FormData();
    body.append('title', item.title);
    body.append('question', item.question);
    body.append('prize', item.prize);
    body.append('ends_at', item.endsAt);
    if (item.image instanceof File) body.append('image', item.image);
    if (methodOverride) body.append('_method', 'PATCH');
    return body;
  };

  window.SaeyCompetitionsApi = {
    imageUrl,
    async list(status = '', page = 1) {
      const params = new URLSearchParams({ pagination: 'on', limit_per_page: '10', page: String(page) });
      if (status) params.set('status', status);
      const result = await request(`/competitions?${params}`);
      return {
        items: (result.data || []).map(fromApi),
        summary: result.summary || {},
        currentPage: Number(result.current_page || page),
        lastPage: Number(result.last_page || 1),
        total: Number(result.total || 0),
      };
    },
    async find(id) { return fromApi((await request(`/competitions/${encodeURIComponent(id)}`)).data); },
    async draw(id, correctAnswer) {
      const result = (await request(`/competitions/${encodeURIComponent(id)}/draw`, {
        method: 'POST',
        body: JSON.stringify({ correct_answer: correctAnswer }),
      })).data;
      return { ...result, competition: fromApi(result.competition) };
    },
    async create(item) { return fromApi((await request('/competitions', { method: 'POST', body: toMultipart(item) })).data); },
    async update(id, item) { return fromApi((await request(`/competitions/${encodeURIComponent(id)}`, { method: 'POST', body: toMultipart(item, true) })).data); },
    async remove(id) { await request(`/competitions/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
  };
})();
