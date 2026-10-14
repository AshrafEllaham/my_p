(() => {
  const params = new URLSearchParams(location.search);
  const competitionId = params.get('id');
  const state = document.getElementById('userContestState');
  const details = document.getElementById('userContestDetails');
  const questionCard = document.getElementById('competitionQuestionCard');
  const question = document.getElementById('competitionQuestionTitle');
  const questionNote = document.getElementById('competitionQuestionNote');
  const answerCard = document.getElementById('answerCard');
  const token = () => localStorage.getItem('saey_api_token') || '';
  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const icons = {
    gift: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="8" width="18" height="13" rx="2"/><path d="M12 8v13M3 12h18M12 8H7.5a2.5 2.5 0 1 1 2.2-3.7L12 8Zm0 0h4.5a2.5 2.5 0 1 0-2.2-3.7L12 8Z"/></svg>',
    clock: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    edit: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20ZM14 7l3 3"/></svg>',
    check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>',
  };
  let competition = null;

  function setPageState(message, actions = []) {
    state.replaceChildren();
    const title = document.createElement('h3');
    title.textContent = message;
    state.append(title);
    actions.forEach(({ label, href, onClick }) => {
      const button = href ? document.createElement('a') : document.createElement('button');
      button.className = 'contest-state-action';
      button.textContent = label;
      if (href) button.href = href;
      else {
        button.type = 'button';
        button.addEventListener('click', onClick);
      }
      state.append(button);
    });
    state.hidden = false;
    details.hidden = true;
    questionCard.hidden = true;
    answerCard.hidden = true;
  }

  async function request(url, options = {}) {
    const response = await fetch(url, {
      ...options,
      headers: {
        Accept: 'application/json',
        'Accept-Language': document.documentElement.lang || 'ar',
        ...(token() ? { Authorization: `Bearer ${token()}` } : {}),
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...options.headers
      }
    });
    const payload = await response.json().catch(() => ({}));
    if (response.status === 401) {
      const error = new Error('سجّل الدخول لعرض تفاصيل المسابقة والمشاركة فيها.');
      error.status = response.status;
      throw error;
    }
    if (!response.ok || payload.code >= 400) {
      const error = new Error(payload.message || 'تعذر إكمال الطلب. حاول مرة أخرى.');
      error.status = response.status;
      error.errors = payload.errors || {};
      throw error;
    }
    return payload.data;
  }

  function dateLabel(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value || '';
    return date.toLocaleString('ar-EG', {
      day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: '2-digit'
    });
  }

  function appendText(parent, tagName, className, text) {
    const element = document.createElement(tagName);
    if (className) element.className = className;
    element.textContent = text || '';
    parent.append(element);
    return element;
  }

  function renderDetails() {
    const store = competition.store || {};
    const open = competition.status === 'available_now';
    details.replaceChildren();

    const cover = document.createElement('div');
    cover.className = 'user-contest-cover';
    if (competition.image || store.image) {
      const image = document.createElement('img');
      image.src = competition.image || store.image;
      image.alt = store.name ? `مسابقة من متجر ${store.name}` : 'صورة المسابقة';
      image.loading = 'eager';
      cover.append(image);
    } else {
      cover.classList.add('contest-image-placeholder');
      cover.innerHTML = icons.gift;
    }
    const shade = document.createElement('span');
    shade.className = 'user-contest-cover-shade';
    cover.append(shade);
    const status = appendText(cover, 'span', `contest-status user-contest-status${open ? '' : ' completed'}`, open ? 'متاحة الآن' : 'انتهت');
    status.setAttribute('aria-label', open ? 'حالة المسابقة: متاحة الآن' : 'حالة المسابقة: انتهت');

    const body = document.createElement('div');
    body.className = 'user-contest-hero-body';
    const storeRow = document.createElement('div');
    storeRow.className = 'detail-store';
    const storeMark = appendText(storeRow, 'span', 'detail-store-logo', (store.name || 'سعي').trim().charAt(0));
    storeMark.setAttribute('aria-hidden', 'true');
    const storeCopy = document.createElement('span');
    appendText(storeCopy, 'strong', '', store.name || 'متجر سعي');
    const storeLabel = document.createElement('small');
    storeLabel.innerHTML = `${icons.check} متجر مشارك في سعي`;
    storeCopy.append(storeLabel);
    storeRow.append(storeCopy);
    body.append(storeRow);

    const title = document.createElement('div');
    title.className = 'user-contest-title';
    appendText(title, 'small', '', 'اسم المسابقة');
    appendText(title, 'h2', '', competition.title);
    body.append(title);

    const prize = document.createElement('div');
    prize.className = 'user-contest-prize';
    const prizeIcon = document.createElement('span');
    prizeIcon.className = 'user-contest-prize-icon';
    prizeIcon.innerHTML = icons.gift;
    const prizeCopy = document.createElement('span');
    appendText(prizeCopy, 'small', '', 'الجائزة');
    appendText(prizeCopy, 'strong', '', competition.prize);
    prize.append(prizeIcon, prizeCopy);
    body.append(prize);

    const deadline = document.createElement('div');
    deadline.className = 'contest-deadline';
    deadline.innerHTML = icons.clock;
    deadline.append(document.createTextNode(open
      ? `ينتهي التقديم ${dateLabel(competition.ends_at)}`
      : competition.winner ? 'تم إعلان الفائز' : 'انتهت المشاركة'));
    body.append(deadline);
    details.append(cover, body);
    details.hidden = false;

    question.textContent = competition.question;
    questionNote.textContent = open
      ? 'اكتب إجابتك لتأكيد مشاركتك. يمكنك تعديلها قبل انتهاء المسابقة.'
      : 'انتهت فترة المشاركة في هذه المسابقة.';
    questionCard.hidden = false;
  }

  function showSavedEntry(entry, canEdit) {
    answerCard.replaceChildren();
    const heading = document.createElement('div');
    heading.className = 'answer-card-heading';
    const icon = document.createElement('span');
    icon.className = 'success-icon';
    icon.innerHTML = icons.check;
    const copy = document.createElement('div');
    appendText(copy, 'h3', '', canEdit ? 'تم تسجيل إجابتك' : 'إجابتك المسجلة');
    appendText(copy, 'p', '', canEdit ? 'أنت الآن ضمن المشاركين، ويمكنك تعديل الإجابة قبل انتهاء المسابقة.' : 'شكرًا لمشاركتك في المسابقة.');
    heading.append(icon, copy);
    answerCard.append(heading);

    const saved = document.createElement('div');
    saved.className = 'saved-answer';
    appendText(saved, 'small', '', 'إجابتك');
    appendText(saved, 'strong', '', entry.answer);
    answerCard.append(saved);
    if (canEdit) {
      const edit = document.createElement('button');
      edit.type = 'button';
      edit.textContent = 'تعديل الإجابة';
      edit.addEventListener('click', () => renderAnswerForm(entry.answer, true));
      answerCard.append(edit);
    }
    answerCard.hidden = false;
  }

  function renderAnswerForm(value = '', focusInput = false) {
    answerCard.replaceChildren();
    const heading = document.createElement('div');
    heading.className = 'answer-card-heading';
    const icon = document.createElement('span');
    icon.innerHTML = icons.edit;
    const copy = document.createElement('div');
    appendText(copy, 'h3', '', value ? 'تعديل إجابتك' : 'اكتب إجابتك');
    appendText(copy, 'p', '', value ? 'احفظ التعديل قبل انتهاء المسابقة.' : 'إجابة واحدة فقط، ويمكنك تعديلها قبل انتهاء المسابقة.');
    heading.append(icon, copy);

    const label = document.createElement('label');
    label.className = 'answer-field';
    label.htmlFor = 'contestAnswer';
    appendText(label, 'span', '', 'إجابتك على السؤال');
    const input = document.createElement('textarea');
    input.id = 'contestAnswer';
    input.maxLength = 180;
    input.placeholder = 'اكتب إجابتك هنا...';
    input.value = value;
    input.setAttribute('aria-describedby', 'answerLength answerError');
    const counter = document.createElement('small');
    const count = document.createElement('b');
    count.id = 'answerLength';
    count.textContent = String(value.length).replace(/[0-9]/g, digit => '٠١٢٣٤٥٦٧٨٩'[digit]);
    counter.append(count, document.createTextNode(' / ١٨٠ حرفًا'));
    label.append(input, counter);

    const error = document.createElement('p');
    error.className = 'contest-form-error';
    error.id = 'answerError';
    error.setAttribute('role', 'alert');
    error.hidden = true;
    const submit = document.createElement('button');
    submit.id = 'submitAnswer';
    submit.type = 'button';
    submit.textContent = value ? 'حفظ التعديل' : 'تأكيد المشاركة';
    submit.addEventListener('click', () => saveAnswer(input, error, submit));
    input.addEventListener('input', () => {
      count.textContent = String(input.value.length).replace(/[0-9]/g, digit => '٠١٢٣٤٥٦٧٨٩'[digit]);
      error.hidden = true;
    });

    answerCard.append(heading, label, error, submit);
    answerCard.hidden = false;
    if (focusInput) input.focus();
  }

  async function saveAnswer(input, error, submit) {
    const answer = input.value.trim();
    if (answer.length < 3 || !/[^\s\u064B-\u065F\u0670]/u.test(answer)) {
      error.textContent = answer.length < 3 ? 'اكتب إجابة من ٣ أحرف على الأقل.' : 'لا يمكن أن تتكوّن الإجابة من مسافات أو تشكيل فقط.';
      error.hidden = false;
      input.focus();
      return;
    }

    submit.disabled = true;
    submit.textContent = 'جارٍ حفظ الإجابة...';
    error.hidden = true;
    try {
      const entry = await request(`${apiOrigin}/api/user/competitions/${encodeURIComponent(competitionId)}/entry`, {
        method: 'PUT',
        body: JSON.stringify({ answer })
      });
      competition.my_entry = entry;
      showSavedEntry(entry, true);
    } catch (requestError) {
      if (requestError.status === 401) {
        setPageState(requestError.message, [{ label: 'تسجيل الدخول', href: '01-login.html' }]);
        return;
      }

      const validationMessage = requestError.errors?.answer?.[0];
      error.textContent = validationMessage || requestError.message || 'تعذر حفظ الإجابة. حاول مرة أخرى.';
      error.hidden = false;
      submit.disabled = false;
      submit.textContent = competition.my_entry ? 'حفظ التعديل' : 'تأكيد المشاركة';
    }
  }

  function renderAnswerCard() {
    const entry = competition.my_entry;
    if (competition.winner) {
      answerCard.replaceChildren();
      const result = document.createElement('div');
      result.className = 'answer-success';
      result.innerHTML = icons.check;
      const copy = document.createElement('span');
      appendText(copy, 'strong', '', 'انتهت المسابقة');
      appendText(copy, 'small', '', `الفائز: ${competition.winner.name}`);
      result.append(copy);
      if (entry) {
        showSavedEntry(entry, false);
        answerCard.prepend(result);
      } else {
        answerCard.append(result);
      }
      answerCard.hidden = false;
      return;
    }

    if (entry) {
      showSavedEntry(entry, competition.status === 'available_now');
      return;
    }

    if (competition.status === 'available_now') {
      renderAnswerForm();
      return;
    }

    answerCard.replaceChildren();
    appendText(answerCard, 'h3', '', 'انتهت المشاركة');
    appendText(answerCard, 'p', '', 'لا توجد إجابة مسجلة لك في هذه المسابقة.');
    answerCard.hidden = false;
  }

  async function loadCompetition() {
    setPageState('جارٍ تحميل بيانات المسابقة...');
    try {
      competition = await request(`${apiOrigin}/api/user/competitions/${encodeURIComponent(competitionId)}`);
      state.hidden = true;
      renderDetails();
      renderAnswerCard();
    } catch (error) {
      const actions = error.status === 401
        ? [{ label: 'تسجيل الدخول', href: '01-login.html' }]
        : [{ label: 'إعادة المحاولة', onClick: loadCompetition }, { label: 'العودة للمسابقات', href: '27-competitions.html' }];
      setPageState(error.message || 'تعذر تحميل تفاصيل المسابقة.', actions);
    }
  }

  if (!/^\d+$/.test(competitionId || '')) {
    setPageState('رابط المسابقة غير صحيح.', [{ label: 'العودة للمسابقات', href: '27-competitions.html' }]);
    return;
  }

  loadCompetition();
})();
