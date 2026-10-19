(() => {
  const api = window.SaeyAccountApi;
  const download = document.getElementById('downloadData');
  const message = document.getElementById('dataMessage');
  if (!api || !download || !message) return;

  const fetchAll = async (path, perPage = 100) => {
    const rows = [];
    let page = 1;
    let lastPage = 1;
    do {
      const separator = path.includes('?') ? '&' : '?';
      const result = await api.request(`${path}${separator}limit_per_page=${perPage}&page=${page}`, 'GET');
      rows.push(...(result.data || []));
      lastPage = Number(result.last_page || result.meta?.last_page || 1);
      page += 1;
    } while (page <= lastPage && page <= 100);
    return rows;
  };

  download.addEventListener('click', async () => {
    download.disabled = true;
    message.hidden = true;
    try {
      const [profile, orders, wallet, favorites, rooms] = await Promise.all([
        api.request('/user/profile', 'GET'),
        fetchAll('/user/orders'),
        fetchAll('/user/wallet'),
        fetchAll('/user/favorites'),
        fetchAll('/chat/rooms', 50)
      ]);
      const conversations = await Promise.all(rooms.map(async room => ({
        ...room,
        messages: await fetchAll(`/chat/rooms/${encodeURIComponent(room.id)}/messages`)
      })));
      const exportData = {
        generated_at: new Date().toISOString(),
        profile: profile.data || null,
        orders,
        wallet_transactions: wallet,
        favorites,
        conversations
      };
      const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `saey-account-data-${new Date().toISOString().slice(0, 10)}.json`;
      link.click();
      URL.revokeObjectURL(url);
      message.textContent = 'تم تنزيل نسخة البيانات المتاحة في حسابك.';
      message.className = 'view-message success';
      message.hidden = false;
    } catch (error) {
      message.textContent = error.message || 'تعذر تجهيز بيانات الحساب. حاول مرة أخرى.';
      message.className = 'view-message error';
      message.hidden = false;
    } finally {
      download.disabled = false;
    }
  });
})();
