(function () {
  const apiBaseUrl = (localStorage.getItem('saey_api_base') || 'http://127.0.0.1:8000/api').replace(/\/$/, '');
  const token = () => localStorage.getItem('saey_api_token');
  const escapeHtml = value => String(value == null ? '' : value).replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
  })[character]);
  const request = async (path, options) => {
    const accessToken = token();
    if (!accessToken) {
      const error = new Error('سجل الدخول لعرض محادثاتك.');
      error.status = 401;
      throw error;
    }

    let response;
    try {
      response = await fetch(apiBaseUrl + path, Object.assign({}, options || {}, {
        headers: Object.assign({
          Accept: 'application/json',
          'Accept-Language': document.documentElement.lang || 'ar',
          Authorization: 'Bearer ' + accessToken,
        }, options && options.headers ? options.headers : {}),
      }));
    } catch (cause) {
      const error = new Error('تعذر الاتصال بالخادم. تحقق من تشغيل واجهة API ثم أعد المحاولة.');
      error.cause = cause;
      throw error;
    }

    const result = await response.json().catch(() => ({}));
    if (!response.ok) {
      const error = new Error(result.message || 'تعذر تنفيذ طلب المحادثة.');
      error.status = response.status;
      throw error;
    }
    return result;
  };
  const formatTime = value => value
    ? new Date(value).toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit'})
    : '';
  const currentUserId = () => {
    const parts = (token() || '').split('.');
    if (parts.length < 2) return null;
    try {
      let encoded = parts[1].replace(/-/g, '+').replace(/_/g, '/');
      encoded += '='.repeat((4 - encoded.length % 4) % 4);
      const payload = JSON.parse(atob(encoded));
      const userId = Number(payload.sub);
      return Number.isInteger(userId) && userId > 0 ? userId : null;
    } catch (_) {
      return null;
    }
  };
  const watchInbox = (onMessage, onSubscribed) => {
    const userId = currentUserId();
    if (!userId) return () => {};

    const channelName = 'chat.inboxes.' + userId;
    let channel = null;
    let subscribed = false;
    const handleSubscribed = () => {
      if (subscribed) return;
      subscribed = true;
      onSubscribed();
    };
    const subscribe = () => {
      if (channel || !window.Echo) return;
      try {
        channel = window.Echo.private(channelName);
        channel.listen('.chat.message.sent', onMessage);
        if (typeof channel.subscribed === 'function') channel.subscribed(handleSubscribed);
        else handleSubscribed();
      } catch (_) {
        channel = null;
      }
    };

    subscribe();
    window.addEventListener('saey:echo-ready', subscribe);
    return () => {
      window.removeEventListener('saey:echo-ready', subscribe);
      if (channel && window.Echo) window.Echo.leave(channelName);
    };
  };

  const initRoomList = () => {
    const list = document.getElementById('chatList');
    if (!list) return;

    const search = document.getElementById('chatSearch');
    const empty = document.getElementById('chatEmpty');
    const loading = document.getElementById('chatLoading');
    const errorState = document.getElementById('chatError');
    const errorText = errorState.querySelector('p');
    const count = document.getElementById('chatCount');
    const loadMore = document.getElementById('loadMoreChats');
    let page = 1;
    let lastPage = 1;
    let totalRooms = 0;
    let rows = [];
    let timer;
    let refreshInterval;
    let realtimeConnected = false;
    let requestVersion = 0;

    const render = () => {
      list.innerHTML = rows.map(room => {
        const store = room.store || {};
        const lastMessage = room.last_message || {};
        const name = store.name || 'المتجر';
        const preview = lastMessage.body || 'ابدأ المحادثة مع المتجر';
        const unread = Number(room.unread_messages_count || 0);
        const avatar = store.avatar
          ? '<img src="' + escapeHtml(store.avatar) + '" alt="' + escapeHtml(name) + '" loading="lazy">'
          : '<span class="chat-avatar-fallback" aria-hidden="true">' + escapeHtml(name.slice(0, 1)) + '</span>';
        return '<button class="chat-row' + (unread ? ' unread' : '') + '" type="button" data-room="' + escapeHtml(room.id) + '" data-store="' + escapeHtml(store.id || '') + '" data-name="' + escapeHtml(name) + '">' +
          '<span class="chat-avatar">' + avatar + '</span>' +
          '<span class="chat-copy"><strong>' + escapeHtml(name) + '</strong><span>' + escapeHtml(preview) + '</span></span>' +
          '<span class="chat-meta"><time>' + escapeHtml(formatTime(room.last_message_at)) + '</time>' + (unread ? '<b>' + unread.toLocaleString('ar-EG') + '</b>' : '') + '</span>' +
          '</button>';
      }).join('');
      count.textContent = totalRooms ? totalRooms.toLocaleString('ar-EG') + ' محادثة' : '';
      empty.classList.toggle('visible', rows.length === 0);
      list.hidden = rows.length === 0;
      loadMore.hidden = rows.length === 0 || page >= lastPage;
      loadMore.disabled = false;
      loadMore.textContent = 'تحميل المزيد';
      list.querySelectorAll('.chat-row').forEach(row => {
        row.addEventListener('click', () => {
          const params = new URLSearchParams({
            room: row.dataset.room,
            store_id: row.dataset.store,
            store: row.dataset.name,
          });
          location.href = '14-chat-conversation.html?' + params.toString();
        });
      });
    };

    const load = async (nextPage, append) => {
      const version = ++requestVersion;
      errorState.hidden = true;
      empty.classList.remove('visible');
      if (append) {
        loadMore.disabled = true;
        loadMore.textContent = 'جارٍ التحميل…';
      } else {
        loading.hidden = false;
        list.hidden = true;
        loadMore.hidden = true;
        rows = [];
      }

      const query = new URLSearchParams({
        page: String(nextPage),
        limit_per_page: '20',
      });
      if (search.value.trim()) query.set('search', search.value.trim());

      try {
        const result = await request('/chat/rooms?' + query.toString());
        if (version !== requestVersion) return;
        page = Number(result.current_page || (result.meta && result.meta.current_page) || nextPage);
        lastPage = Number(result.last_page || (result.meta && result.meta.last_page) || 1);
        totalRooms = Number(result.total || (result.meta && result.meta.total) || 0);
        rows = append ? rows.concat(result.data || []) : (result.data || []);
        loading.hidden = true;
        errorState.hidden = true;
        render();
      } catch (error) {
        if (version !== requestVersion) return;
        loading.hidden = true;
        list.hidden = true;
        empty.classList.remove('visible');
        errorText.textContent = error.message;
        errorState.hidden = false;
      }
    };

    search.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => load(1, false), 280);
    });
    loadMore.addEventListener('click', () => {
      if (page < lastPage) load(page + 1, true);
    });
    document.getElementById('retryChats').addEventListener('click', () => load(1, false));
    watchInbox(() => {
      if (!document.hidden) load(1, false);
    }, () => {
      realtimeConnected = true;
      window.clearInterval(refreshInterval);
    });
    load(1, false);
    if (!realtimeConnected) {
      refreshInterval = window.setInterval(() => {
        if (!document.hidden && page === 1 && document.activeElement !== search) load(1, false);
      }, 12000);
    }
  };

  const initRoom = () => {
    const pageElement = document.querySelector('.conversation-page');
    if (!pageElement) return;

    const params = new URLSearchParams(location.search);
    const composer = document.getElementById('messageComposer');
    const input = document.getElementById('messageInput');
    const messages = document.getElementById('messages');
    const loading = document.getElementById('messagesLoading');
    const errorPanel = document.getElementById('messagesError');
    const errorText = document.getElementById('messagesErrorText');
    const status = document.getElementById('conversationStatus');
    const loadOlder = document.getElementById('loadOlderMessages');
    const messageSearch = document.getElementById('messageSearch');
    const storeName = document.getElementById('storeName');
    const storeAvatar = document.getElementById('storeAvatar');
    const storeAvatarFallback = document.getElementById('storeAvatarFallback');
    const actions = document.getElementById('conversationActions');
    const reportDialog = document.getElementById('reportDialog');
    const reportError = document.getElementById('reportError');
    let roomId = params.get('room');
    let page = 1;
    let lastPage = 1;
    let rows = [];
    let searchTimer;
    let refreshing = false;
    let pendingLoad = null;
    let roomBlocked = false;
    let realtimeConnected = false;
    let refreshInterval;

    const showStatus = message => {
      status.textContent = message;
      status.hidden = !message;
    };
    const showError = message => {
      errorText.textContent = message;
      errorPanel.hidden = false;
    };
    const showStore = store => {
      const name = store && store.name ? store.name : params.get('store') || 'المتجر';
      storeName.textContent = name;
      if (store && store.avatar) {
        storeAvatar.src = store.avatar;
        storeAvatar.alt = name;
        storeAvatar.hidden = false;
        storeAvatarFallback.hidden = true;
      } else {
        storeAvatar.hidden = true;
        storeAvatarFallback.textContent = name.slice(0, 1) || 'م';
        storeAvatarFallback.hidden = false;
      }
    };
    const renderMessages = () => {
      messages.innerHTML = rows.map(message => {
        const outgoing = Boolean(message.is_mine);
        const readMark = outgoing ? (message.read_at ? '✓✓' : '✓') : '';
        return '<div class="message ' + (outgoing ? 'outgoing' : 'incoming') + '">' +
          '<p>' + escapeHtml(message.body) + '</p>' +
          '<time>' + escapeHtml(formatTime(message.created_at)) + (readMark ? ' <b>' + readMark + '</b>' : '') + '</time>' +
          '</div>';
      }).join('');
      loadOlder.hidden = page >= lastPage || !rows.length;
    };
    const loadMessages = async (nextPage, reset) => {
      if (!roomId) return;
      if (refreshing) {
        pendingLoad = {page: nextPage, reset: reset};
        return;
      }
      refreshing = true;
      errorPanel.hidden = true;
      if (reset) loading.hidden = false;
      const oldHeight = pageElement.scrollHeight;
      const oldScrollTop = pageElement.scrollTop;
      const query = new URLSearchParams({page: String(nextPage), limit_per_page: '30'});
      if (messageSearch.value.trim()) query.set('search', messageSearch.value.trim());

      try {
        const result = await request('/chat/rooms/' + encodeURIComponent(roomId) + '/messages?' + query.toString());
        page = Number(result.current_page || (result.meta && result.meta.current_page) || nextPage);
        lastPage = Number(result.last_page || (result.meta && result.meta.last_page) || 1);
        const chronological = (result.data || []).slice().reverse();
        rows = reset ? chronological : chronological.concat(rows);
        renderMessages();
        pageElement.scrollTop = reset
          ? pageElement.scrollHeight
          : oldScrollTop + pageElement.scrollHeight - oldHeight;
        loading.hidden = true;
      } catch (error) {
        loading.hidden = true;
        showError(error.message);
        if (error.status === 403) {
          roomBlocked = true;
          composer.querySelectorAll('input,button').forEach(element => { element.disabled = true; });
          showStatus(error.message);
        }
      } finally {
        refreshing = false;
        if (pendingLoad) {
          const next = pendingLoad;
          pendingLoad = null;
          loadMessages(next.page, next.reset);
        }
      }
    };
    const openRoom = async () => {
      if (roomId) {
        showStore({name: params.get('store')});
        input.disabled = false;
        composer.querySelector('button').disabled = false;
        await loadMessages(1, true);
        return;
      }

      const storeId = params.get('store_id');
      if (!storeId) {
        showError('اختر متجرًا من صفحة المتجر لبدء المحادثة.');
        return;
      }
      loading.hidden = false;
      try {
        const result = await request('/chat/rooms', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({store_id: Number(storeId)}),
        });
        roomId = result.data.id;
        showStore(result.data.store);
        input.disabled = false;
        composer.querySelector('button').disabled = false;
        params.set('room', roomId);
        history.replaceState(null, '', location.pathname + '?' + params.toString());
        await loadMessages(1, true);
      } catch (error) {
        loading.hidden = true;
        showError(error.message);
      }
    };

    composer.addEventListener('submit', async event => {
      event.preventDefault();
      const body = input.value.trim();
      if (!body || !roomId || input.disabled || roomBlocked) return;
      input.disabled = true;
      showStatus('جارٍ إرسال الرسالة...');
      try {
        await request('/chat/rooms/' + encodeURIComponent(roomId) + '/messages', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({body: body}),
        });
        input.value = '';
        showStatus('');
        await loadMessages(1, true);
      } catch (error) {
        showStatus(error.message);
        if (error.status === 403) {
          roomBlocked = true;
          composer.querySelectorAll('input,button').forEach(element => { element.disabled = true; });
        }
      } finally {
        input.disabled = roomBlocked;
      }
    });

    loadOlder.addEventListener('click', () => {
      if (page < lastPage) loadMessages(page + 1, false);
    });
    messageSearch.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        page = 1;
        lastPage = 1;
        loadMessages(1, true);
      }, 280);
    });
    document.getElementById('retryMessages').addEventListener('click', () => loadMessages(1, true));
    document.getElementById('conversationMenu').addEventListener('click', () => actions.showModal());
    document.getElementById('blockConversation').addEventListener('click', async event => {
      event.preventDefault();
      actions.close();
      if (!roomId) return;
      try {
        await request('/chat/rooms/' + encodeURIComponent(roomId) + '/blocks', {method: 'POST'});
        roomBlocked = true;
        composer.querySelectorAll('input,button').forEach(element => { element.disabled = true; });
        showStatus('تم حظر هذه المحادثة.');
      } catch (error) {
        showStatus(error.message);
      }
    });
    document.getElementById('reportConversation').addEventListener('click', event => {
      event.preventDefault();
      actions.close();
      reportError.hidden = true;
      reportDialog.showModal();
    });
    document.getElementById('cancelReport').addEventListener('click', () => reportDialog.close());
    document.getElementById('reportForm').addEventListener('submit', async event => {
      event.preventDefault();
      if (!roomId) return;
      const reason = document.getElementById('reportReason').value.trim();
      const details = document.getElementById('reportDetails').value.trim();
      reportError.hidden = true;
      try {
        await request('/chat/rooms/' + encodeURIComponent(roomId) + '/reports', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({reason: reason, details: details || null}),
        });
        reportDialog.close();
        showStatus('تم إرسال البلاغ.');
      } catch (error) {
        reportError.textContent = error.message;
        reportError.hidden = false;
      }
    });

    watchInbox(event => {
      if (event.room_id === roomId && !rows.some(message => String(message.id) === String(event.message_id))) {
        loadMessages(1, true);
      }
    }, () => {
      realtimeConnected = true;
      window.clearInterval(refreshInterval);
    });
    openRoom();
    if (!realtimeConnected) {
      refreshInterval = window.setInterval(() => {
        if (roomId && page === 1 && !document.hidden) loadMessages(1, true);
      }, 12000);
    }
  };

  initRoomList();
  initRoom();
})();
