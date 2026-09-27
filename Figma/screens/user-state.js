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
    coupons() {
      return read('saey_merchant_coupons', [
        { id: 'coupon-saey20', code: 'SAEY20', type: 'percent', value: 20, minOrder: 500, expiresAt: '2027-12-31', enabled: true, uses: 24 },
        { id: 'coupon-welcome100', code: 'WELCOME100', type: 'fixed', value: 100, minOrder: 1000, expiresAt: '2027-06-30', enabled: true, uses: 11 },
        { id: 'coupon-old15', code: 'OLD15', type: 'percent', value: 15, minOrder: 300, expiresAt: '2025-12-31', enabled: false, uses: 38 }
      ]);
    },
    saveCoupons(items) { write('saey_merchant_coupons', items); return items; },
    saveCoupon(coupon) {
      const items = this.coupons(), index = items.findIndex(item => item.id === coupon.id);
      if (index >= 0) items[index] = coupon; else items.unshift(coupon);
      return this.saveCoupons(items);
    },
    deleteCoupon(id) { return this.saveCoupons(this.coupons().filter(item => item.id !== id)); },
    findCoupon(code) { return this.coupons().find(item => item.code === String(code || '').trim().toUpperCase()); },
    validateCoupon(code, subtotal) {
      const coupon = this.findCoupon(code), amount = Number(subtotal) || 0;
      if (!coupon) return { valid: false, message: 'كود الخصم غير صحيح.' };
      if (!coupon.enabled) return { valid: false, message: 'هذا الكوبون غير متاح حاليًا.' };
      const expires = new Date(`${coupon.expiresAt}T23:59:59`);
      if (Number.isNaN(expires.getTime()) || expires < new Date()) return { valid: false, message: 'انتهت صلاحية هذا الكوبون.' };
      if (amount < Number(coupon.minOrder || 0)) return { valid: false, message: `الحد الأدنى لاستخدام الكوبون ${Number(coupon.minOrder).toLocaleString('ar-EG')} ج.م.` };
      const rawDiscount = coupon.type === 'percent' ? amount * Number(coupon.value) / 100 : Number(coupon.value);
      return { valid: true, coupon, discount: Math.min(amount, Math.max(0, Math.round(rawDiscount))) };
    },
    recordCouponUse(id) {
      const items = this.coupons(), coupon = items.find(item => item.id === id);
      if (coupon) coupon.uses = Number(coupon.uses || 0) + 1;
      this.saveCoupons(items);
    },
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
