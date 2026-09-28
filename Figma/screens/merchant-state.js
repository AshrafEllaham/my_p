(function () {
  const INVENTORY_KEY = 'saey_merchant_inventory_v1';
  const MOVEMENTS_KEY = 'saey_merchant_stock_movements_v1';
  const FINANCE_KEY = 'saey_merchant_finance_v1';
  const SETTLEMENTS_KEY = 'saey_merchant_settlements_v1';
  const RETURNS_KEY = 'saey_merchant_returns_v1';
  const ORDER_STATES_KEY = 'saey_merchant_order_states_v1';
  const ORDER_EVENTS_KEY = 'saey_merchant_order_events_v1';
  const LEDGER_KEY = 'saey_merchant_ledger_v1';
  const ACTIVITY_KEY = 'saey_merchant_activity_v1';

  const inventorySeed = [
    { id: 'jacket', name: 'جاكيت جلد طبيعي', sku: 'JK-1042', stock: 18, threshold: 5, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=240&q=82' },
    { id: 'shoe', name: 'حذاء رياضي أبيض', sku: 'SH-2081', stock: 3, threshold: 5, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=240&q=82' },
    { id: 'backpack', name: 'حقيبة ظهر عملية', sku: 'BG-3014', stock: 24, threshold: 6, image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=240&q=82' },
    { id: 'watch', name: 'ساعة رياضية ذكية', sku: 'WT-4072', stock: 0, threshold: 4, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=240&q=82' }
  ];

  const financeSeed = {
    available: 18640,
    pending: 6213,
    grossSales: 24850,
    commission: 1491,
    nextSettlement: '30 سبتمبر 2026',
    bank: { bank: 'البنك الأهلي المصري', holder: 'نقطة ستور', iban: 'EG380019000500000000263180002' }
  };

  const settlementsSeed = [
    { id: 'ST-2048', date: '25 سبتمبر 2026', gross: 9200, commission: 552, net: 8648, status: 'completed' },
    { id: 'ST-2051', date: '27 سبتمبر 2026', gross: 6610, commission: 397, net: 6213, status: 'pending' }
  ];

  const movementsSeed = [
    { id: 'MV-1', productId: 'shoe', product: 'حذاء رياضي أبيض', change: -1, before: 4, after: 3, reason: 'طلب جديد #1028', time: 'اليوم، 10:42 ص' },
    { id: 'MV-2', productId: 'jacket', product: 'جاكيت جلد طبيعي', change: 8, before: 10, after: 18, reason: 'إضافة مخزون', time: 'أمس، 6:15 م' },
    { id: 'MV-3', productId: 'watch', product: 'ساعة رياضية ذكية', change: -2, before: 2, after: 0, reason: 'طلب جديد #1021', time: 'أمس، 1:20 م' }
  ];

  const returnsSeed = [
    { id: 'RT-3018', order: 'SA-10794', customer: 'ريم خالد', productId: 'shoe', product: 'حذاء رياضي أبيض', amount: 950, reason: 'المنتج لا يطابق الوصف', details: 'العميلة أرفقت صورًا وتطلب استرداد المبلغ.', date: 'اليوم، 11:20 ص', status: 'pending' },
    { id: 'RT-3014', order: 'SA-10781', customer: 'أحمد سامي', productId: 'backpack', product: 'حقيبة ظهر عملية', amount: 680, reason: 'عيب في المنتج', details: 'تمت مراجعة الصور والمنتج قابل للإرجاع.', date: 'أمس، 4:10 م', status: 'review' },
    { id: 'RT-3009', order: 'SA-10742', customer: 'مريم حسن', productId: 'jacket', product: 'جاكيت جلد طبيعي', amount: 1850, reason: 'تراجع عن الشراء', details: 'تم استلام المنتج وإعادة المبلغ.', date: '24 سبتمبر 2026', status: 'completed' }
  ];

  const ledgerSeed = [
    { id: 'LG-1028', title: 'طلب #SA-10928', detail: 'بيع جاكيت جلد طبيعي', amount: 1850, type: 'sale', date: 'اليوم، 10:32 ص' },
    { id: 'LG-C1028', title: 'عمولة سعي', detail: 'عمولة الطلب #SA-10928', amount: -111, type: 'charge', date: 'اليوم، 10:32 ص' },
    { id: 'LG-1026', title: 'طلب #SA-10886', detail: 'بيع حقيبة ظهر عملية', amount: 680, type: 'sale', date: 'أمس، 6:40 م' }
  ];

  const activitySeed = [
    { id: 'AC-1', type: 'orders', title: 'تم تأكيد الطلب #SA-10928', detail: 'انتقل الطلب إلى مرحلة التجهيز', user: 'محمد حسن', time: '10:42 ص', day: 'اليوم' },
    { id: 'AC-2', type: 'inventory', title: 'تم تحديث مخزون حذاء رياضي أبيض', detail: 'تغيرت الكمية من ٤ إلى ٣ وحدات', user: 'النظام', time: '10:42 ص', day: 'اليوم' },
    { id: 'AC-3', type: 'finance', title: 'تم إنشاء تسوية ST-2051', detail: 'قيمة التسوية ٦٬٢١٣ ج.م', user: 'النظام', time: '9:15 ص', day: 'اليوم' },
    { id: 'AC-4', type: 'team', title: 'تم تعديل صلاحيات آية محمود', detail: 'الوصول إلى الطلبات فقط', user: 'صاحب المتجر', time: '6:10 م', day: 'أمس' }
  ];

  const clone = value => JSON.parse(JSON.stringify(value));
  const read = (key, fallback) => {
    try {
      const value = JSON.parse(localStorage.getItem(key));
      return value && (Array.isArray(fallback) ? Array.isArray(value) : typeof value === 'object') ? value : clone(fallback);
    } catch (_) { return clone(fallback); }
  };
  const write = (key, value) => {
    localStorage.setItem(key, JSON.stringify(value));
    return clone(value);
  };
  const nowLabel = () => new Intl.DateTimeFormat('ar-EG', { day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' }).format(new Date());
  const sharedOrders = () => read('saey_orders_v2', []);
  const writeSharedOrders = items => write('saey_orders_v2', items);
  const syncUserOrderState = (code, state) => {
    const states = read('saey_order_states', {}); states[code] = state; write('saey_order_states', states);
    const items = sharedOrders(), item = items.find(entry => entry.code === code);
    if (item) { item.state = state; item.updatedAt = new Date().toISOString(); item.events = [...(item.events || []), { state, at: item.updatedAt }]; writeSharedOrders(items); }
    return item;
  };
  const notifyUser = item => { const notices = read('saey_user_notifications', []); notices.unshift({ id: `notice-${Date.now()}-${Math.random().toString(16).slice(2)}`, date: new Date().toISOString(), ...item }); write('saey_user_notifications', notices.slice(0, 40)); };

  const api = {
    activity: () => read(ACTIVITY_KEY, activitySeed),
    addActivity(entry) { const rows = api.activity(); rows.unshift({ id: `AC-${Date.now()}`, day: 'اليوم', time: nowLabel(), user: 'صاحب المتجر', ...entry }); return write(ACTIVITY_KEY, rows.slice(0, 80)); },
    inventory: () => read(INVENTORY_KEY, inventorySeed),
    movements: () => read(MOVEMENTS_KEY, movementsSeed),
    returns: () => read(RETURNS_KEY, returnsSeed),
    updateReturn(id, status, note = '') {
      const items = api.returns(), item = items.find(entry => entry.id === id);
      if (!item) return items;
      item.status = status; item.note = note; item.updatedAt = nowLabel();
      api.addActivity({ type: 'orders', title: `${status === 'rejected' ? 'تم رفض' : 'تم تحديث'} المرتجع ${item.id}`, detail: item.product });
      const userNotices = read('saey_user_notifications', []);
      userNotices.unshift({ id: `notice-${Date.now()}`, type: 'return', title: status === 'rejected' ? 'تم رفض طلب الاسترجاع' : status === 'approved' ? 'تم قبول طلب الاسترجاع' : 'تم تحديث طلب الاسترجاع', text: note || `تم تحديث حالة الطلب ${item.order}.`, href: `26-return-status.html?code=${item.order}`, date: new Date().toISOString() });
      write('saey_user_notifications', userNotices.slice(0, 40));
      if (status === 'completed' && !item.restocked) {
        const product = api.inventory().find(entry => entry.id === item.productId);
        if (product) api.updateStock(item.productId, product.stock + 1, `مرتجع ${item.id}`);
        if (item.resolution !== 'exchange') {
          const finance = api.finance(), commission = Math.round(item.amount * .06);
          finance.available = Math.max(0, finance.available - (item.amount - commission));
          finance.grossSales = Math.max(0, finance.grossSales - item.amount);
          finance.commission = Math.max(0, finance.commission - commission);
          write(FINANCE_KEY, finance);
          api.addLedger({ title: `استرداد ${item.order}`, detail: item.product, amount: -item.amount, type: 'refund' });
          const refunds = read('saey_order_refunds', {});
          if (!refunds[item.order]) { refunds[item.order] = { amount: item.amount, date: new Date().toISOString() }; write('saey_order_refunds', refunds); localStorage.setItem('saey_wallet_balance', String(Number(localStorage.getItem('saey_wallet_balance') || 4500) + Number(item.amount || 0))); const tx = read('saey_wallet_transactions', []); tx.unshift({ id:`TX-${Date.now()}`, type:'استرداد طلب مقبول', amount:Number(item.amount), status:'completed', date:new Date().toISOString() }); write('saey_wallet_transactions', tx.slice(0,50)); }
        }
        syncUserOrderState(item.order, item.resolution === 'exchange' ? 'exchanged' : 'refunded');
        notifyUser({ type:'return', title:item.resolution === 'exchange'?'تم تجهيز الاستبدال':'تم رد المبلغ', text:item.resolution === 'exchange'?`اكتمل استبدال الطلب ${item.order}.`:`أُعيد ${Number(item.amount).toLocaleString('ar-EG')} ج.م إلى محفظتك.`, href:`26-return-status.html?code=${item.order}` });
        item.restocked = true;
      }
      return write(RETURNS_KEY, items);
    },
    orders: sharedOrders,
    order(code) { return sharedOrders().find(item => item.code === code) || null; },
    orderStates: () => read(ORDER_STATES_KEY, {}),
    orderState(code, fallback = 'new') { return api.order(code)?.state || api.orderStates()[code] || fallback; },
    ledger: () => read(LEDGER_KEY, ledgerSeed),
    addLedger(entry) { const rows = api.ledger(); rows.unshift({ id: `LG-${Date.now()}`, date: nowLabel(), ...entry }); return write(LEDGER_KEY, rows.slice(0, 60)); },
    advanceOrder(code, nextState, order) {
      const states = api.orderStates(), events = read(ORDER_EVENTS_KEY, {});
      states[code] = nextState; write(ORDER_STATES_KEY, states); const shared = syncUserOrderState(code, nextState);
      api.addActivity({ type: 'orders', title: `تم تحديث الطلب #${code}`, detail: `الحالة الجديدة: ${nextState}` });
      if (nextState === 'preparing' && !events[`${code}:stock`]) {
        const product = api.inventory().find(item => item.id === order.productId);
        if (product) api.updateStock(order.productId, Math.max(0, product.stock - Number(order.quantity || 1)), `تأكيد الطلب #${code}`);
        events[`${code}:stock`] = new Date().toISOString();
      }
      if (nextState === 'completed' && !events[`${code}:finance`]) {
        const amount = Number(order.amount || 0), commission = Math.round(amount * .06), finance = api.finance();
        finance.available += amount - commission; finance.grossSales += amount; finance.commission += commission;
        write(FINANCE_KEY, finance);
        api.addLedger({ title: `طلب #${code}`, detail: `بيع ${order.product || 'منتج'}`, amount, type: 'sale' });
        api.addLedger({ title: 'عمولة سعي', detail: `عمولة الطلب #${code}`, amount: -commission, type: 'charge' });
        events[`${code}:finance`] = new Date().toISOString();
      }
      const messages = { preparing:'بدأ المتجر تجهيز طلبك.', ready:'طلبك جاهز، ويمكن إتمام التعامل حضوريًا أو عن بُعد.', completion_requested:'طلب المتجر تأكيد إتمام التعامل. راجع الطلب ثم أكّد أو أبلغ عن مشكلة.', completed:'اكتمل الطلب بنجاح.' };
      if (messages[nextState]) notifyUser({ type:nextState==='ready'?'pickup':'order', title:nextState==='completion_requested'?'مطلوب تأكيدك':'تحديث حالة الطلب', text:`${messages[nextState]} (${code})`, href:`21-order-details.html?code=${code}` });
      write(ORDER_EVENTS_KEY, events);
      return nextState;
    },
    rejectOrder(code, reason = 'تعذر تنفيذ الطلب') {
      const order = api.order(code); syncUserOrderState(code, 'rejected');
      if (order) {
        const refunds = read('saey_order_refunds', {});
        if (!refunds[code]) { const amount=Number(order.total || order.amount || 0); refunds[code] = { amount, date:new Date().toISOString() }; write('saey_order_refunds', refunds); localStorage.setItem('saey_wallet_balance', String(Number(localStorage.getItem('saey_wallet_balance') || 4500) + amount)); const tx=read('saey_wallet_transactions',[]);tx.unshift({id:`TX-${Date.now()}`,type:'استرداد طلب لم يُنفذ',amount,status:'completed',date:new Date().toISOString()});write('saey_wallet_transactions',tx.slice(0,50)); }
      }
      notifyUser({ type:'order', title:'تعذر تنفيذ الطلب', text:`${reason}. أُعيد المبلغ إلى محفظتك.`, href:`21-order-details.html?code=${code}` });
      api.addActivity({ type:'orders', title:`تم رفض الطلب #${code}`, detail:reason }); return true;
    },
    verifyPickupCode(code, enteredCode, order) {
      const expected = String(code || '').replace(/\D/g, '').padEnd(6, '4').slice(-6);
      if (String(enteredCode || '').replace(/\D/g, '') !== expected) return { ok:false, message:'الكود غير صحيح. اطلب من المستلم مراجعة الكود الظاهر لديه.' };
      api.advanceOrder(code, 'completed', order); return { ok:true };
    },
    markNoShow(code, order) {
      const item = api.order(code); if (item?.pickupUntil && new Date(item.pickupUntil) > new Date()) return { ok:false, message:'مدة الحجز لم تنتهِ بعد.' };
      api.rejectOrder(code, 'انتهت مدة الحجز دون إتمام التعامل'); syncUserOrderState(code, 'no_show');
      return { ok:true };
    },
    disputes: () => read('saey_order_disputes_v1', []),
    resolveDispute(id, resolution, note = '') {
      const rows=api.disputes(),item=rows.find(entry=>entry.id===id);if(!item)return null;const order=api.order(item.order);item.resolution=resolution;item.resolutionNote=note;item.resolvedAt=new Date().toISOString();item.status=resolution==='more_info'?'awaiting_customer':'resolved';
      if(resolution==='complete'&&order)api.advanceOrder(item.order,'completed',order);
      if(resolution==='refund'){
        const amount=Number(order?.total||order?.amount||0),refunds=read('saey_order_refunds',{});
        if(amount&&!refunds[item.order]){refunds[item.order]={amount,date:new Date().toISOString()};write('saey_order_refunds',refunds);localStorage.setItem('saey_wallet_balance',String(Number(localStorage.getItem('saey_wallet_balance')||4500)+amount));const tx=read('saey_wallet_transactions',[]);tx.unshift({id:`TX-${Date.now()}`,type:'رد مبلغ بعد تسوية نزاع',amount,status:'completed',date:new Date().toISOString()});write('saey_wallet_transactions',tx.slice(0,50));if(order?.productId){const product=api.inventory().find(entry=>entry.id===order.productId);if(product)api.updateStock(order.productId,product.stock+Number(order.quantity||order.qty||1),`تسوية نزاع ${item.order}`)}}syncUserOrderState(item.order,'refunded');
      }
      write('saey_order_disputes_v1',rows);notifyUser({type:'order',title:resolution==='refund'?'تم رد المبلغ':resolution==='complete'?'تم حسم النزاع وإكمال الطلب':'مطلوب معلومات إضافية',text:note||`تم تحديث النزاع الخاص بالطلب ${item.order}.`,href:`order-dispute-status.html?code=${item.order}`});api.addActivity({type:'orders',title:`تم تحديث نزاع الطلب #${item.order}`,detail:resolution});return item;
    },
    updateStock(id, nextStock, reason = 'تعديل يدوي') {
      const items = api.inventory();
      const item = items.find(entry => entry.id === id);
      if (!item) return items;
      const before = Number(item.stock) || 0;
      const after = Math.max(0, Math.floor(Number(nextStock) || 0));
      if (before === after) return items;
      item.stock = after;
      write(INVENTORY_KEY, items);
      const movements = api.movements();
      movements.unshift({ id: `MV-${Date.now()}`, productId: item.id, product: item.name, change: after - before, before, after, reason, time: nowLabel() });
      write(MOVEMENTS_KEY, movements.slice(0, 30));
      api.addActivity({ type: 'inventory', title: `تم تحديث مخزون ${item.name}`, detail: `تغيرت الكمية من ${before} إلى ${after}` });
      return items;
    },
    bulkUpdate(ids, mode, amount) {
      const items = api.inventory();
      const selected = new Set(ids);
      const value = Math.max(0, Math.floor(Number(amount) || 0));
      items.forEach(item => {
        if (!selected.has(item.id)) return;
        const before = Number(item.stock) || 0;
        const after = mode === 'add' ? before + value : mode === 'subtract' ? Math.max(0, before - value) : value;
        if (before === after) return;
        item.stock = after;
        const movements = api.movements();
        movements.unshift({ id: `MV-${Date.now()}-${item.id}`, productId: item.id, product: item.name, change: after - before, before, after, reason: 'تحديث جماعي', time: nowLabel() });
        write(MOVEMENTS_KEY, movements.slice(0, 30));
      });
      return write(INVENTORY_KEY, items);
    },
    finance: () => {
      const finance = read(FINANCE_KEY, financeSeed);
      finance.bank = { ...financeSeed.bank, ...(finance.bank || {}) };
      return finance;
    },
    settlements: () => read(SETTLEMENTS_KEY, settlementsSeed),
    requestSettlement(amount) {
      const finance = api.finance();
      const value = Math.floor(Number(amount) || 0);
      if (value < 100 || value > finance.available) return { ok: false, finance };
      finance.available -= value;
      finance.pending += value;
      write(FINANCE_KEY, finance);
      const settlements = api.settlements();
      settlements.unshift({ id: `ST-${String(Date.now()).slice(-4)}`, date: nowLabel(), gross: value, commission: 0, net: value, status: 'pending' });
      write(SETTLEMENTS_KEY, settlements);
      api.addActivity({ type: 'finance', title: 'تم إرسال طلب تسوية جديد', detail: `قيمة التسوية ${value} ج.م` });
      return { ok: true, finance, settlements };
    },
    saveBank(bank) {
      const finance = api.finance();
      finance.bank = { ...finance.bank, ...bank };
      write(FINANCE_KEY, finance);
      api.addActivity({ type: 'finance', title: 'تم تحديث حساب التحويل', detail: finance.bank.bank });
      return finance;
    }
  };

  window.SaeyMerchant = api;
})();
