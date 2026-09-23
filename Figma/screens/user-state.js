(function () {
  const read = (key, fallback) => {
    try {
      const value = JSON.parse(localStorage.getItem(key));
      return value == null ? fallback : value;
    } catch (error) {
      return fallback;
    }
  };
  const write = (key, value) => localStorage.setItem(key, JSON.stringify(value));
  window.SaeyState = {
    read, write,
    orderStates() { return read('saey_order_states', {}); },
    orderState(code, fallback = 'ready') { return this.orderStates()[code] || fallback; },
    setOrderState(code, state) { const states = this.orderStates(); states[code] = state; write('saey_order_states', states); return state; },
    ratings() { return read('saey_order_ratings', {}); },
    isRated(code) { return Boolean(this.ratings()[code]); },
    saveRating(code, rating) { const ratings = this.ratings(); ratings[code] = { ...rating, date: new Date().toISOString() }; write('saey_order_ratings', ratings); localStorage.setItem('saey_rated_order', code); return ratings[code]; },
    favorites() { return read('saey_favorites', []); },
    isFavorite(id) { return this.favorites().some(item => item.id === id); },
    toggleFavorite(product) { const items = this.favorites(), index = items.findIndex(item => item.id === product.id); if (index >= 0) items.splice(index, 1); else items.unshift(product); write('saey_favorites', items); return index < 0; },
    favoriteStores() { return read('saey_favorite_stores', []); },
    isFavoriteStore(id) { return this.favoriteStores().some(item => item.id === id); },
    toggleFavoriteStore(store) { const items = this.favoriteStores(), index = items.findIndex(item => item.id === store.id); if (index >= 0) items.splice(index, 1); else items.unshift(store); write('saey_favorite_stores', items); return index < 0; },
    walletBalance() { return Number(localStorage.getItem('saey_wallet_balance') || 4500); },
    setWalletBalance(value) { localStorage.setItem('saey_wallet_balance', String(value)); return value; },
    addTransaction(type, amount, status = 'completed') { const transactions = read('saey_wallet_transactions', []); transactions.unshift({ id: `TX-${Date.now()}`, type, amount, status, date: new Date().toISOString() }); write('saey_wallet_transactions', transactions.slice(0, 50)); },
    refundOnce(code, amount, type = 'استرداد طلب ملغي') { const refunds = read('saey_order_refunds', {}); if (refunds[code] || !amount) return false; refunds[code] = { amount, date: new Date().toISOString() }; write('saey_order_refunds', refunds); this.setWalletBalance(this.walletBalance() + amount); this.addTransaction(type, amount); return true; },
    notificationState() { return read('saey_notification_state', { read: [], cleared: [] }); },
    markNotificationRead(id) { const state = this.notificationState(); if (!state.read.includes(id)) state.read.push(id); write('saey_notification_state', state); },
    clearNotifications(ids) { const state = this.notificationState(); state.cleared = [...new Set([...state.cleared, ...ids])]; write('saey_notification_state', state); }
  };
})();
