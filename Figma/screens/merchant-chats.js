(function () {
  const customers = [
    {id: 'c1', name: 'سارة أحمد', preview: 'هل المنتج متوفر باللون الأسود؟', time: '٩:٣٢ ص', unread: 2, avatar: 'س'},
    {id: 'c2', name: 'محمود علي', preview: 'شكرًا، استلمت الطلب اليوم', time: 'أمس', unread: 0, avatar: 'م'},
    {id: 'c3', name: 'نورهان حسن', preview: 'متى يصل الطلب؟', time: 'أمس', unread: 1, avatar: 'ن'},
    {id: 'c4', name: 'ريم خالد', preview: 'هل يمكن استبدال المقاس؟', time: 'الأحد', unread: 0, avatar: 'ر'},
  ];
  const list = document.getElementById('chatList');
  const search = document.getElementById('chatSearch');
  const count = document.getElementById('chatCount');
  const empty = document.getElementById('chatEmpty');
  const escapeHtml = value => String(value).replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
  })[character]);

  function render() {
    const query = search.value.trim();
    const visible = customers.filter(customer => customer.name.includes(query));
    list.innerHTML = visible.map(customer => '<button class="chat-row' + (customer.unread ? ' unread' : '') + '" type="button" data-customer="' + escapeHtml(customer.id) + '">' +
      '<span class="chat-avatar"><span class="chat-avatar-fallback" aria-hidden="true">' + escapeHtml(customer.avatar) + '</span></span>' +
      '<span class="chat-copy"><strong>' + escapeHtml(customer.name) + '</strong><span>' + escapeHtml(customer.preview) + '</span></span>' +
      '<span class="chat-meta"><time>' + escapeHtml(customer.time) + '</time>' + (customer.unread ? '<b>' + customer.unread.toLocaleString('ar-EG') + '</b>' : '') + '</span>' +
      '</button>').join('');
    count.textContent = visible.length.toLocaleString('ar-EG') + ' محادثات';
    empty.classList.toggle('visible', visible.length === 0);
    list.hidden = visible.length === 0;
    list.querySelectorAll('[data-customer]').forEach(row => row.addEventListener('click', () => {
      const customer = customers.find(item => item.id === row.dataset.customer);
      const params = new URLSearchParams({type: 'merchant', customer_id: customer.id, customer: customer.name});
      location.href = '14-chat-conversation.html?' + params.toString();
    }));
  }

  search.addEventListener('input', render);
  render();
})();
