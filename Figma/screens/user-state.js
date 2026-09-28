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
        { id: 'coupon-saey20', code: 'SAEY20', type: 'percent', value: 20, minOrder: 500, expiresAt: '2027-12-31', enabled: true, uses: 24, maxUses: 100, perUserLimit: 1 },
        { id: 'coupon-welcome100', code: 'WELCOME100', type: 'fixed', value: 100, minOrder: 1000, expiresAt: '2027-06-30', enabled: true, uses: 11, maxUses: 50, perUserLimit: 1 },
        { id: 'coupon-old15', code: 'OLD15', type: 'percent', value: 15, minOrder: 300, expiresAt: '2025-12-31', enabled: false, uses: 38, maxUses: 40, perUserLimit: 1 }
      ]);
    },
    saveCoupons(items) { write('saey_merchant_coupons', items); return items; },
    saveCoupon(coupon) {
      const items = this.coupons(), index = items.findIndex(item => item.id === coupon.id);
      if (index >= 0) items[index] = coupon; else items.unshift(coupon);
      return this.saveCoupons(items);
    },
    deleteCoupon(id) { return this.saveCoupons(this.coupons().filter(item => item.id !== id)); },
    competitions() {
      const seeded = [
        {
          id: 'contest-tech-01',
          title: 'اكسب سماعة لاسلكية',
          question: 'ما الميزة الأهم بالنسبة لك عند اختيار سماعة لاسلكية؟',
          prize: 'سماعة لاسلكية بعزل الضوضاء',
          merchant: 'نقطة ستور',
          image: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=720&q=85',
          endsAt: '2026-10-03T22:00',
          status: 'active',
          createdAt: '2026-09-27T12:00:00.000Z',
          winnerId: '',
          responses: [
            { id: 'answer-1', user: 'سارة أحمد', answer: 'عزل الضوضاء وجودة الصوت', createdAt: '2026-09-28T08:15:00.000Z' },
            { id: 'answer-2', user: 'محمود علي', answer: 'عمر البطارية الطويل', createdAt: '2026-09-28T09:40:00.000Z' },
            { id: 'answer-3', user: 'نور خالد', answer: 'الراحة أثناء الاستخدام', createdAt: '2026-09-28T10:05:00.000Z' },
            { id: 'answer-4', user: 'أحمد سمير', answer: 'وضوح المكالمات', createdAt: '2026-09-28T10:42:00.000Z' }
          ]
        },
        {
          id: 'contest-fashion-02',
          title: 'اكسب قسيمة تسوق ١٬٠٠٠ ج.م',
          question: 'إيه قطعة الملابس الأساسية في دولابك؟',
          prize: 'قسيمة شراء بقيمة ١٬٠٠٠ ج.م',
          merchant: 'أزياء الشرق',
          image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=720&q=85',
          endsAt: '2026-10-06T21:00',
          status: 'active',
          createdAt: '2026-09-28T09:00:00.000Z',
          winnerId: '',
          responses: [
            { id: 'fashion-answer-1', user: 'منى عادل', answer: 'الجاكيت العملي', createdAt: '2026-09-28T11:10:00.000Z' },
            { id: 'fashion-answer-2', user: 'ريم حسن', answer: 'القميص الأبيض', createdAt: '2026-09-28T12:05:00.000Z' }
          ]
        },
        {
          id: 'contest-home-03',
          title: 'اكسب قسيمة لتجديد بيتك',
          question: 'إيه أول ركن تحب تجدده في بيتك؟',
          prize: 'قسيمة مشتريات منزلية بقيمة ٧٥٠ ج.م',
          merchant: 'بيتك أجمل',
          image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=720&q=85',
          endsAt: '2026-10-08T20:00',
          status: 'active',
          createdAt: '2026-09-28T10:00:00.000Z',
          winnerId: '',
          responses: []
        }
      ];
      const saved = read('saey_competitions_v1', []);
      const deleted = read('saey_deleted_competitions_v1', []);
      const visibleSaved = saved.filter(item => !deleted.includes(item.id));
      const visibleSeeded = seeded.filter(item => !deleted.includes(item.id));
      if (!visibleSaved.length) return visibleSeeded;
      return [...visibleSaved, ...visibleSeeded.filter(seed => !visibleSaved.some(item => item.id === seed.id))];
    },
    saveCompetitions(items) { write('saey_competitions_v1', items); return items; },
    saveCompetition(competition) {
      const deleted = read('saey_deleted_competitions_v1', []).filter(id => id !== competition.id);
      write('saey_deleted_competitions_v1', deleted);
      const items = this.competitions(), index = items.findIndex(item => item.id === competition.id);
      if (index >= 0) items[index] = competition; else items.unshift(competition);
      return this.saveCompetitions(items);
    },
    deleteCompetition(id) {
      const deleted = read('saey_deleted_competitions_v1', []);
      if (!deleted.includes(id)) deleted.push(id);
      write('saey_deleted_competitions_v1', deleted);
      return this.saveCompetitions(this.competitions().filter(item => item.id !== id));
    },
    competition(id) { return this.competitions().find(item => item.id === id) || this.competitions()[0]; },
    answerCompetition(id, answer) {
      const items = this.competitions(), item = items.find(entry => entry.id === id);
      if (!item || item.status !== 'active' || item.winnerId) return null;
      item.responses = Array.isArray(item.responses) ? item.responses : [];
      const existing = item.responses.find(entry => entry.id === 'answer-current-user');
      const value = { id: 'answer-current-user', user: 'محمد أحمد', answer: String(answer || '').trim(), createdAt: new Date().toISOString() };
      if (existing) Object.assign(existing, value); else item.responses.push(value);
      this.saveCompetitions(items);
      return value;
    },
    competitionAnswerKey(answer) {
      return String(answer || '')
        .trim()
        .replace(/[\u064B-\u065F\u0670]/g, '')
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ى/g, 'ي')
        .replace(/ؤ/g, 'و')
        .replace(/ئ/g, 'ي')
        .replace(/\s+/g, ' ')
        .toLowerCase();
    },
    pickCompetitionWinner(id, correctAnswer) {
      const items = this.competitions(), item = items.find(entry => entry.id === id), responses = item?.responses || [];
      const answer = String(correctAnswer || '').trim(), answerKey = this.competitionAnswerKey(answer);
      if (!item || item.winnerId || !responses.length || !answerKey) return null;
      const eligible = responses.filter(response => this.competitionAnswerKey(response.answer) === answerKey);
      if (!eligible.length) return null;
      const winner = eligible[Math.floor(Math.random() * eligible.length)];
      item.correctAnswer = answer;
      item.eligibleResponses = eligible.length;
      item.winnerId = winner.id; item.status = 'completed'; item.winnerPickedAt = new Date().toISOString();
      this.saveCompetitions(items);
      return winner;
    },
    findCoupon(code) { return this.coupons().find(item => item.code === String(code || '').trim().toUpperCase()); },
    validateCoupon(code, subtotal) {
      const coupon = this.findCoupon(code), amount = Number(subtotal) || 0;
      if (!coupon) return { valid: false, message: 'كود الخصم غير صحيح.' };
      if (!coupon.enabled) return { valid: false, message: 'هذا الكوبون غير متاح حاليًا.' };
      const expires = new Date(`${coupon.expiresAt}T23:59:59`);
      if (Number.isNaN(expires.getTime()) || expires < new Date()) return { valid: false, message: 'انتهت صلاحية هذا الكوبون.' };
      if (Number(coupon.maxUses || 0) > 0 && Number(coupon.uses || 0) >= Number(coupon.maxUses)) return { valid: false, message: 'تم استنفاد الحد الإجمالي لاستخدام هذا الكوبون.' };
      const userUses=read('saey_coupon_user_uses_v1',{}),currentUserUses=Number(userUses[coupon.id]||0);
      if (Number(coupon.perUserLimit || 0) > 0 && currentUserUses >= Number(coupon.perUserLimit)) return { valid: false, message: 'استخدمت هذا الكوبون الحد الأقصى المسموح لحسابك.' };
      if (amount < Number(coupon.minOrder || 0)) return { valid: false, message: `الحد الأدنى لاستخدام الكوبون ${Number(coupon.minOrder).toLocaleString('ar-EG')} ج.م.` };
      const rawDiscount = coupon.type === 'percent' ? amount * Number(coupon.value) / 100 : Number(coupon.value);
      return { valid: true, coupon, discount: Math.min(amount, Math.max(0, Math.round(rawDiscount))) };
    },
    recordCouponUse(id) {
      const items = this.coupons(), coupon = items.find(item => item.id === id);
      if (coupon) { coupon.uses = Number(coupon.uses || 0) + 1; const userUses=read('saey_coupon_user_uses_v1',{});userUses[id]=Number(userUses[id]||0)+1;write('saey_coupon_user_uses_v1',userUses); }
      this.saveCoupons(items);
    },
    orders() { return read('saey_orders_v2', []); },
    order(code) { return this.orders().find(item => item.code === code) || null; },
    createOrder(order) {
      const items = this.orders().filter(item => item.code !== order.code);
      const value = { customer: 'محمد أحمد', customerPhone: '+20 100 000 0000', productId: 'jacket', state: 'new', handover: 'customer_confirmation', events: [{ state: 'new', at: new Date().toISOString() }], ...order };
      items.unshift(value); write('saey_orders_v2', items.slice(0, 80));
      this.setOrderState(value.code, value.state);
      this.addMerchantNotification({ type:'orders', title:'طلب جديد يحتاج تأكيدك', text:`${value.customer} طلب ${value.product}.`, href:`21-distributor-order-details.html?order=${value.code}` });
      return value;
    },
    updateOrder(code, changes) {
      const items = this.orders(), item = items.find(entry => entry.code === code);
      if (!item) return null;
      const previousState = item.state; Object.assign(item, changes, { updatedAt: new Date().toISOString() });
      if (changes.state && changes.state !== previousState) item.events = [...(item.events || []), { state: changes.state, at: item.updatedAt }];
      write('saey_orders_v2', items); this.setOrderState(code, item.state); return item;
    },
    orderStates() {
      const states = read('saey_order_states', {});
      this.orders().forEach(item => { states[item.code] = item.state; });
      return states;
    },
    orderState(code, fallback = 'ready') { return this.order(code)?.state || this.orderStates()[code] || fallback; },
    setOrderState(code, state) {
      const states = read('saey_order_states', {}); states[code] = state; write('saey_order_states', states);
      const items = this.orders(), item = items.find(entry => entry.code === code);
      if (item && item.state !== state) { item.state = state; item.updatedAt = new Date().toISOString(); item.events = [...(item.events || []), { state, at: item.updatedAt }]; write('saey_orders_v2', items); }
      return state;
    },
    confirmCompletion(code) {
      const item = this.updateOrder(code, { state: 'completed', completedAt: new Date().toISOString(), completedBy: 'customer' });
      if (item) {
        const events = read('saey_merchant_order_events_v1', {});
        if (!events[`${code}:finance`]) {
          const amount=Number(item.total||item.amount||0),commission=Math.round(amount*.06),finance=read('saey_merchant_finance_v1',{available:0,pending:0,grossSales:0,commission:0});
          finance.available=Number(finance.available||0)+amount-commission;finance.grossSales=Number(finance.grossSales||0)+amount;finance.commission=Number(finance.commission||0)+commission;write('saey_merchant_finance_v1',finance);
          const ledger=read('saey_merchant_ledger_v1',[]);ledger.unshift({id:`LG-${Date.now()}`,title:`طلب #${code}`,detail:`بيع ${item.product}`,amount,type:'sale',date:new Date().toLocaleString('ar-EG')},{id:`LG-${Date.now()}-C`,title:'عمولة سعي',detail:`عمولة الطلب #${code}`,amount:-commission,type:'charge',date:new Date().toLocaleString('ar-EG')});write('saey_merchant_ledger_v1',ledger.slice(0,60));events[`${code}:finance`]=new Date().toISOString();write('saey_merchant_order_events_v1',events);
        }
        this.addUserNotification({ type: 'order', title: 'اكتمل الطلب', text: `تم تأكيد إتمام الطلب ${code}.`, href: `21-order-details.html?code=${code}` });
      }
      return item;
    },
    reportOrderIssue(code, note = '') {
      const item = this.updateOrder(code, { state: 'disputed', disputeNote: note || 'أبلغ المستخدم عن مشكلة قبل إتمام الطلب.', disputedAt: new Date().toISOString() });
      const reports = read('saey_order_disputes_v1', []); reports.unshift({ id: `DSP-${Date.now()}`, order: code, note: item?.disputeNote, status: 'open', date: new Date().toISOString() }); write('saey_order_disputes_v1', reports.slice(0, 40));
      if (item) this.addUserNotification({ type: 'order', title: 'تم إيقاف إتمام الطلب', text: `سجلنا المشكلة على الطلب ${code} وسيظل المبلغ محميًا لحين المراجعة.`, href: `21-order-details.html?code=${code}` });
      this.addMerchantNotification({ type:'orders', title:'مشكلة تمنع إتمام الطلب', text:`أوقف المستخدم إتمام الطلب ${code}: ${item?.disputeNote||note}`, href:`21-distributor-order-details.html?order=${code}` });
      return item;
    },
    disputes() { return read('saey_order_disputes_v1', []); },
    dispute(code) { return this.disputes().find(item => item.order === code) || null; },
    respondToDispute(code, message) { const rows=this.disputes(),item=rows.find(entry=>entry.order===code);if(!item)return null;item.customerResponse=String(message||'').trim();item.status='under_review';item.updatedAt=new Date().toISOString();write('saey_order_disputes_v1',rows);this.addMerchantNotification({type:'orders',title:'رد جديد على النزاع',text:`أضاف المستخدم تفاصيل جديدة للطلب ${code}.`,href:'23-distributor-disputes.html'});return item; },
    cancelOrder(code, reason = 'ألغاه المستخدم قبل بدء التجهيز', fallbackAmount = 0) {
      const item=this.updateOrder(code,{state:'cancelled',cancelledBy:'customer',cancelReason:reason,cancelledAt:new Date().toISOString()});
      if(!item)this.setOrderState(code,'cancelled');this.refundOnce(code,Number(item?.total||item?.amount||fallbackAmount||0));this.addMerchantNotification({type:'orders',title:'ألغى المستخدم الطلب',text:`تم إلغاء الطلب ${code} قبل بدء التجهيز.`,href:`21-distributor-order-details.html?order=${code}`});
      return item;
    },
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
    storePolicies() { return read('saey_merchant_policies_v1', { returnDays: '14', returnTerms: 'يجب أن يكون المنتج بحالته الأصلية وغير مستخدم، مع الاحتفاظ بالتغليف.', exchange: true, pickupHours: '48', pickupTerms: 'يرجى إظهار رقم الطلب عند الوصول إلى المتجر.' }); },
    pickupCode(code) { const digits = String(code || '').replace(/\D/g, '').padEnd(6, '4').slice(-6); return digits.replace(/(.{3})/, '$1 '); },
    returnRequests() { return read('saey_user_return_requests', []); },
    returnRequest(code) { const own = this.returnRequests().find(item => item.order === code), merchant = read('saey_merchant_returns_v1', []).find(item => item.order === code); return own ? { ...own, ...(merchant ? { status: merchant.status, note: merchant.note } : {}) } : merchant; },
    createReturn(request) { const rows = this.returnRequests(), existing = rows.findIndex(item => item.order === request.order), value = { id: `RT-${String(Date.now()).slice(-5)}`, status: 'pending', resolution: 'refund', createdAt: new Date().toISOString(), ...request }; if (existing >= 0) rows[existing] = value; else rows.unshift(value); write('saey_user_return_requests', rows); const merchantRows=read('saey_merchant_returns_v1',[]),merchantIndex=merchantRows.findIndex(item=>item.order===request.order),merchantValue={id:value.id,order:request.order,customer:'محمد أحمد',productId:this.order(request.order)?.productId||'jacket',product:request.product,amount:request.amount,reason:request.reason,details:request.details,date:'اليوم',status:'pending',resolution:value.resolution,photoCount:request.photoCount};if(merchantIndex>=0)merchantRows[merchantIndex]=merchantValue;else merchantRows.unshift(merchantValue);write('saey_merchant_returns_v1',merchantRows); this.setOrderState(request.order, value.resolution==='exchange'?'exchange_requested':'refund_requested'); this.addTransaction(value.resolution==='exchange'?'طلب استبدال قيد المراجعة':'طلب استرداد قيد المراجعة', 0, 'pending'); this.addUserNotification({ type:'return', title:value.resolution==='exchange'?'تم إرسال طلب الاستبدال':'تم إرسال طلب الاسترجاع', text:`طلب ${request.order} قيد المراجعة الآن.`, href:`26-return-status.html?code=${request.order}` }); this.addMerchantNotification({type:'orders',title:value.resolution==='exchange'?'طلب استبدال جديد':'طلب استرجاع جديد',text:`الطلب ${request.order} يحتاج مراجعتك.`,href:'23-distributor-returns.html'}); return value; },
    merchantNotifications() { return read('saey_merchant_notifications_v1', []); },
    addMerchantNotification(item) { const rows=this.merchantNotifications();rows.unshift({id:`merchant-notice-${Date.now()}-${Math.random().toString(16).slice(2)}`,date:new Date().toISOString(),...item});write('saey_merchant_notifications_v1',rows.slice(0,50));return rows; },
    userNotifications() { return read('saey_user_notifications', []); },
    addUserNotification(item) { const rows = this.userNotifications(), map={order:'orders',pickup:'pickup',return:'returns',chat:'chats',offer:'offers'},key=map[item.type];if(key&&this.notificationPreferences()[key]===false)return rows; rows.unshift({ id: `notice-${Date.now()}`, date: new Date().toISOString(), ...item }); write('saey_user_notifications', rows.slice(0, 40)); return rows; },
    notificationPreferences() { return read('saey_notification_preferences', { orders: true, pickup: true, returns: true, chats: true, offers: false }); },
    saveNotificationPreferences(value) { write('saey_notification_preferences', value); return value; },
    notificationState() { return read('saey_notification_state', { read: [], cleared: [] }); },
    markNotificationRead(id) { const state = this.notificationState(); if (!state.read.includes(id)) state.read.push(id); write('saey_notification_state', state); },
    clearNotifications(ids) { const state = this.notificationState(); state.cleared = [...new Set([...state.cleared, ...ids])]; write('saey_notification_state', state); }
  };
})();
