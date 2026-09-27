(function () {
  const INVENTORY_KEY = 'saey_merchant_inventory_v1';
  const MOVEMENTS_KEY = 'saey_merchant_stock_movements_v1';
  const FINANCE_KEY = 'saey_merchant_finance_v1';
  const SETTLEMENTS_KEY = 'saey_merchant_settlements_v1';
  const RETURNS_KEY = 'saey_merchant_returns_v1';
  const ORDER_STATES_KEY = 'saey_merchant_order_states_v1';
  const ORDER_EVENTS_KEY = 'saey_merchant_order_events_v1';
  const LEDGER_KEY = 'saey_merchant_ledger_v1';

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

  const api = {
    inventory: () => read(INVENTORY_KEY, inventorySeed),
    movements: () => read(MOVEMENTS_KEY, movementsSeed),
    returns: () => read(RETURNS_KEY, returnsSeed),
    updateReturn(id, status, note = '') {
      const items = api.returns(), item = items.find(entry => entry.id === id);
      if (!item) return items;
      item.status = status; item.note = note; item.updatedAt = nowLabel();
      if (status === 'approved' && !item.restocked) {
        const product = api.inventory().find(entry => entry.id === item.productId);
        if (product) api.updateStock(item.productId, product.stock + 1, `مرتجع ${item.id}`);
        const finance = api.finance(), commission = Math.round(item.amount * .06);
        finance.available = Math.max(0, finance.available - (item.amount - commission));
        finance.grossSales = Math.max(0, finance.grossSales - item.amount);
        finance.commission = Math.max(0, finance.commission - commission);
        write(FINANCE_KEY, finance);
        api.addLedger({ title: `استرداد ${item.order}`, detail: item.product, amount: -item.amount, type: 'refund' });
        item.restocked = true;
      }
      return write(RETURNS_KEY, items);
    },
    orderStates: () => read(ORDER_STATES_KEY, {}),
    orderState(code, fallback = 'new') { return api.orderStates()[code] || fallback; },
    ledger: () => read(LEDGER_KEY, ledgerSeed),
    addLedger(entry) { const rows = api.ledger(); rows.unshift({ id: `LG-${Date.now()}`, date: nowLabel(), ...entry }); return write(LEDGER_KEY, rows.slice(0, 60)); },
    advanceOrder(code, nextState, order) {
      const states = api.orderStates(), events = read(ORDER_EVENTS_KEY, {});
      states[code] = nextState; write(ORDER_STATES_KEY, states);
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
      write(ORDER_EVENTS_KEY, events);
      return nextState;
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
      return { ok: true, finance, settlements };
    },
    saveBank(bank) {
      const finance = api.finance();
      finance.bank = { ...finance.bank, ...bank };
      write(FINANCE_KEY, finance);
      return finance;
    }
  };

  window.SaeyMerchant = api;
})();
