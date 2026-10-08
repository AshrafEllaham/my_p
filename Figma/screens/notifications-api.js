(function () {
  const token = localStorage.getItem('saey_api_token');
  const headers = {
    Accept: 'application/json',
    Authorization: 'Bearer ' + token,
  };
  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  })[character]);
  const request = async (url, options = {}) => {
    const response = await fetch(url, {
      ...options,
      headers: {...headers, ...(options.headers || {})},
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || 'تعذر تنفيذ الطلب.');

    return result;
  };

  const initUserNotifications = () => {
    const page = document.getElementById('notificationsPage');
    if (!page) return;

    const groups = page.querySelector('.notification-groups');
    const empty = document.getElementById('emptyState');
    const clear = document.getElementById('clearAll');
    const count = page.querySelector('.notification-title span');
    let rows = [];

    const render = () => {
      groups.innerHTML = rows.length
        ? '<section><p class="notification-date"><span>الأحدث</span><i></i></p><div class="notification-list">'
          + rows.map(item => '<article class="notification-item ' + (item.is_read ? '' : 'unread') + '" data-id="' + item.id + '" data-href="' + escapeHtml(item.action_url || '') + '" role="link" tabindex="0"><span class="notification-icon success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></span><div class="notification-copy"><h2>' + escapeHtml(item.title) + '</h2><p>' + escapeHtml(item.message) + '</p><span class="notification-time">' + (item.created_at ? new Date(item.created_at).toLocaleString('ar-EG') : 'الآن') + '</span></div><i class="unread-dot"></i></article>').join('')
          + '</div></section>'
        : '';
      const unread = rows.filter(item => !item.is_read).length;
      page.classList.toggle('is-empty', !rows.length);
      empty.classList.toggle('is-visible', !rows.length);
      clear.style.visibility = rows.length ? 'visible' : 'hidden';
      count.textContent = unread ? 'لديك ' + unread.toLocaleString('ar-EG') + ' إشعارات جديدة' : 'اطلعت على كل الإشعارات';
      groups.querySelectorAll('.notification-item').forEach(item => {
        const open = async () => {
          await request('/api/notifications/' + item.dataset.id + '/read', {method: 'PATCH'});
          if (item.dataset.href) location.href = item.dataset.href;
        };
        item.onclick = open;
        item.onkeydown = event => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            open();
          }
        };
      });
    };

    const load = async () => {
      if (!token) {
        SaeyUX.state(groups, {type: 'error', title: 'انتهت الجلسة', message: 'سجل الدخول لعرض إشعاراتك.'});
        return;
      }
      SaeyUX.loading(groups);
      try {
        const result = await request('/api/notifications?per_page=100');
        rows = result.data || [];
        render();
      } catch (error) {
        SaeyUX.state(groups, {type: 'error', title: 'تعذر تحميل الإشعارات', message: error.message, action: 'إعادة المحاولة', onAction: load});
      }
    };

    clear.onclick = async () => {
      try {
        await request('/api/notifications', {method: 'DELETE'});
        rows = [];
        render();
        SaeyUX.success('تم حذف الإشعارات');
      } catch (error) {
        SaeyUX.state(groups, {type: 'error', title: 'تعذر حذف الإشعارات', message: error.message});
      }
    };
    load();
  };

  const initStoreNotifications = () => {
    const container = document.getElementById('notices');
    if (!container) return;

    let rows = [];
    let activeFilter = 'all';
    const render = () => {
      const visible = rows.filter(item => activeFilter === 'all' || (activeFilter === 'unread' && !item.is_read) || item.type === activeFilter);
      if (!visible.length) {
        SaeyUI.renderState(container, {title: 'لا توجد إشعارات جديدة', message: 'أنت مطّلع على كل تحديثات المتجر.'});
        return;
      }
      container.innerHTML = visible.map(item => '<a class="notice-card ops-card ' + (item.is_read ? '' : 'unread') + '" data-id="' + item.id + '" href="' + escapeHtml(item.action_url || '#') + '"><span class="notice-icon"><svg class="ops-svg" viewBox="0 0 24 24"><path d="M6 4h12l1 16H5L6 4ZM9 8a3 3 0 0 0 6 0"/></svg></span><div><h3>' + escapeHtml(item.title) + '</h3><p>' + escapeHtml(item.message) + '</p></div><time class="notice-time">' + (item.created_at ? new Date(item.created_at).toLocaleString('ar-EG') : 'الآن') + '</time></a>').join('');
      container.querySelectorAll('[data-id]').forEach(link => {
        link.onclick = async event => {
          event.preventDefault();
          await request('/api/notifications/' + link.dataset.id + '/read', {method: 'PATCH'});
          if (link.getAttribute('href') !== '#') location.href = link.getAttribute('href');
        };
      });
    };
    const load = async () => {
      if (!token) {
        SaeyUI.renderState(container, {title: 'انتهت الجلسة', message: 'سجل الدخول لعرض إشعارات المتجر.'});
        return;
      }
      SaeyUI.loading(container);
      try {
        const result = await request('/api/notifications?per_page=100');
        rows = result.data || [];
        render();
      } catch (error) {
        SaeyUI.renderState(container, {title: 'تعذر تحميل الإشعارات', message: error.message, action: 'إعادة المحاولة', onAction: load});
      }
    };
    document.querySelectorAll('[data-filter]').forEach(button => {
      button.onclick = () => {
        activeFilter = button.dataset.filter;
        document.querySelectorAll('[data-filter]').forEach(item => item.classList.toggle('active', item === button));
        render();
      };
    });
    document.getElementById('markAll').onclick = async () => {
      await request('/api/notifications/read-all', {method: 'PATCH'});
      rows = rows.map(item => ({...item, is_read: true}));
      render();
      SaeyUI.success('تم تحديد كل الإشعارات كمقروءة');
    };
    load();
  };

  const initPreferences = () => {
    const container = document.getElementById('preferences');
    const saveButton = document.getElementById('savePreferences');
    if (!container || !saveButton) return;

    const labels = {
      orders_enabled: ['تحديثات الطلبات', 'التأكيد والتجهيز وتغيّر الحالة'],
      pickup_enabled: ['تنبيهات الاستلام', 'جاهزية الطلب وقرب انتهاء الحجز'],
      returns_enabled: ['الاسترجاع والمبالغ', 'نتيجة الطلب وإعادة المبلغ'],
      chats_enabled: ['رسائل المتاجر', 'الرسائل الجديدة والردود'],
      offers_enabled: ['العروض والكوبونات', 'العروض المناسبة وتنبيهات انتهاء الكوبون'],
    };
    let values = {};
    let changed = {};
    const render = () => {
      container.innerHTML = Object.entries(labels).map(([key, text]) => '<div class="setting-toggle-row"><div><strong>' + text[0] + '</strong><small>' + text[1] + '</small></div><button class="view-switch ' + (values[key] ? 'active' : '') + '" data-api-key="' + key + '" aria-label="' + (values[key] ? 'إيقاف ' : 'تفعيل ') + text[0] + '"></button></div>').join('');
      container.querySelectorAll('[data-api-key]').forEach(button => {
        button.onclick = () => {
          const key = button.dataset.apiKey;
          values[key] = !values[key];
          changed[key] = values[key];
          render();
        };
      });
    };
    const load = async () => {
      if (!token) {
        SaeyUX.state(container, {type: 'error', title: 'انتهت الجلسة', message: 'سجل الدخول لتعديل الإعدادات.'});
        return;
      }
      SaeyUX.loading(container);
      try {
        const result = await request('/api/notifications/preferences');
        values = result.data;
        changed = {};
        render();
      } catch (error) {
        SaeyUX.state(container, {type: 'error', title: 'تعذر تحميل الإعدادات', message: error.message, action: 'إعادة المحاولة', onAction: load});
      }
    };
    saveButton.onclick = async () => {
      if (Object.keys(changed).length === 0) {
        SaeyUX.success('لا توجد تغييرات لحفظها');
        return;
      }
      saveButton.disabled = true;
      try {
        const result = await request('/api/notifications/preferences', {
          method: 'PATCH',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(changed),
        });
        values = result.data;
        changed = {};
        render();
        SaeyUX.success('تم حفظ إعدادات الإشعارات');
      } catch (error) {
        SaeyUX.state(container, {type: 'error', title: 'تعذر حفظ الإعدادات', message: error.message});
      } finally {
        saveButton.disabled = false;
      }
    };
    load();
  };

  initUserNotifications();
  initStoreNotifications();
  initPreferences();
})();
