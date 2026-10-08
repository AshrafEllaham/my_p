document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const language = (params.get('lang') || localStorage.getItem('souq_lang') || 'ar') === 'en' ? 'en' : 'ar';
  const copy = language === 'en'
    ? {
        title: 'Contact us', intro: 'Official contact details and Saey social links.',
        info: 'Contact information', socials: 'Follow Saey', whatsapp: 'WhatsApp',
        phone: 'Phone', otherPhone: 'Additional phone', email: 'Email',
        loading: 'Loading contact information...', empty: 'No contact information is available yet.',
        socialsEmpty: 'No social links are available yet.', error: 'Could not load contact information.',
        retry: 'Try again', back: 'Back to account',
        formTitle: 'Send a message', name: 'Name', subject: 'Subject', message: 'Message',
        messagePlaceholder: 'Describe your request or question', send: 'Send message',
        messageTooShort: 'Please add more details to your message.', messageSent: 'Your message was saved. We will contact you soon.',
      }
    : {
        title: 'تواصل معنا', intro: 'بيانات التواصل الرسمية وروابط حسابات سعي.',
        info: 'معلومات التواصل', socials: 'تابع سعي', whatsapp: 'واتساب',
        phone: 'الهاتف', otherPhone: 'هاتف إضافي', email: 'البريد الإلكتروني',
        loading: 'جارٍ تحميل بيانات التواصل...', empty: 'لا توجد معلومات تواصل متاحة حاليًا.',
        socialsEmpty: 'لا توجد روابط اجتماعية متاحة حاليًا.', error: 'تعذر تحميل بيانات التواصل.',
        retry: 'إعادة المحاولة', back: 'العودة إلى حسابي',
        formTitle: 'أرسل رسالة', name: 'الاسم', subject: 'عنوان الرسالة', message: 'الرسالة',
        messagePlaceholder: 'اكتب تفاصيل طلبك أو استفسارك', send: 'إرسال الرسالة',
        messageTooShort: 'اكتب تفاصيل أكثر عن طلبك.', messageSent: 'تم حفظ رسالتك، وسنتواصل معك قريبًا.',
      };
  const byId = (id) => document.getElementById(id);
  const contactState = byId('contactLoadState');
  const stateText = byId('contactStateText');
  const spinner = byId('contactSpinner');
  const retry = byId('contactRetry');
  const socialsEmpty = byId('socialsEmpty');
  const contactRows = ['contactWhatsapp', 'contactPhone', 'contactOtherPhone', 'contactEmail'];
  const socialLinks = ['instagramLink', 'facebookLink'];

  document.documentElement.lang = language;
  document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
  document.title = `${language === 'en' ? 'Saey' : 'سعي'} — ${copy.title}`;
  byId('contactTitle').textContent = copy.title;
  document.querySelector('.view-intro').textContent = copy.intro;
  byId('contactInfoTitle').textContent = copy.info;
  byId('socialsTitle').textContent = copy.socials;
  byId('whatsappLabel').textContent = copy.whatsapp;
  byId('phoneLabel').textContent = copy.phone;
  byId('otherPhoneLabel').textContent = copy.otherPhone;
  byId('emailLabel').textContent = copy.email;
  byId('contactBackLink').href = `15-account.html?lang=${language}${params.has('theme') ? `&theme=${encodeURIComponent(params.get('theme'))}` : ''}`;
  byId('contactBackLink').setAttribute('aria-label', copy.back);
  byId('contactRetry').textContent = copy.retry;
  byId('socialsEmpty').textContent = copy.socialsEmpty;
  byId('contactFormTitle').textContent = copy.formTitle;
  byId('contactNameLabel').textContent = copy.name;
  byId('contactEmailLabel').textContent = copy.email;
  byId('contactSubjectLabel').textContent = copy.subject;
  byId('contactMessageLabel').textContent = copy.message;
  byId('contactMessage').placeholder = copy.messagePlaceholder;
  byId('contactSubmit').textContent = copy.send;

  function settingText(value) {
    return typeof value === 'string' || typeof value === 'number' ? String(value).trim() : '';
  }

  byId('contactForm').addEventListener('submit', (event) => {
    event.preventDefault();
    const message = byId('contactMessage').value.trim();
    const status = byId('contactStatus');
    if (message.length < 10) {
      status.textContent = copy.messageTooShort;
      status.className = 'view-message error';
      status.hidden = false;
      return;
    }

    localStorage.setItem('saey_last_support_message', JSON.stringify({
      name: byId('contactName').value.trim(),
      email: byId('contactFormEmail').value.trim(),
      subject: byId('contactSubject').value.trim(),
      message,
      date: new Date().toISOString(),
    }));
    status.textContent = copy.messageSent;
    status.className = 'view-message success';
    status.hidden = false;
    event.currentTarget.reset();
  });

  function setContactValue(rowId, valueId, value, href) {
    const normalized = settingText(value);
    if (!normalized) return false;
    const row = byId(rowId);
    row.href = href;
    byId(valueId).textContent = normalized;
    row.hidden = false;
    return true;
  }

  function setSocialLink(id, value) {
    const normalized = typeof value === 'string' ? value.trim() : '';
    if (!normalized) return false;
    try {
      const url = new URL(/^https?:\/\//i.test(normalized) ? normalized : `https://${normalized}`);
      if (!['http:', 'https:'].includes(url.protocol)) return false;
      const link = byId(id);
      link.href = url.href;
      link.hidden = false;
      return true;
    } catch (_) {
      return false;
    }
  }

  async function loadSettings() {
    contactRows.forEach((id) => { byId(id).hidden = true; });
    socialLinks.forEach((id) => { byId(id).hidden = true; });
    socialsEmpty.hidden = true;
    contactState.hidden = false;
    spinner.hidden = false;
    retry.hidden = true;
    stateText.textContent = copy.loading;

    try {
      const response = await fetch('http://127.0.0.1:8000/api/settings', {
        headers: {Accept: 'application/json', 'Accept-Language': language},
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.message || copy.error);
      const settings = payload.data || {};

      const whatsappNumber = settingText(settings.whatsapp);
      const whatsappDigits = whatsappNumber.replace(/\D/g, '');
      const hasWhatsapp = whatsappDigits.length > 0 && setContactValue(
        'contactWhatsapp', 'whatsappValue', whatsappNumber, `https://wa.me/${whatsappDigits}`,
      );
      const phone = settingText(settings.phone);
      const otherPhone = settingText(settings.other_phone ?? settings.otherPhone);
      const email = settingText(settings.email);
      const hasPhone = setContactValue('contactPhone', 'phoneValue', phone, `tel:${phone.replace(/[^\d+]/g, '')}`);
      const hasOtherPhone = setContactValue('contactOtherPhone', 'otherPhoneValue', otherPhone, `tel:${otherPhone.replace(/[^\d+]/g, '')}`);
      const hasEmail = setContactValue('contactEmail', 'emailValue', email, `mailto:${email}`);
      const hasInstagram = setSocialLink('instagramLink', settings.instagram);
      const hasFacebook = setSocialLink('facebookLink', settings.facebook);

      contactState.hidden = true;
      socialsEmpty.hidden = hasInstagram || hasFacebook;
      if (!(hasWhatsapp || hasPhone || hasOtherPhone || hasEmail)) {
        contactState.hidden = false;
        spinner.hidden = true;
        stateText.textContent = copy.empty;
      }
    } catch (error) {
      spinner.hidden = true;
      stateText.textContent = error.message || copy.error;
      retry.hidden = false;
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
