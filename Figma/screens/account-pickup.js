(() => {
  const api = window.SaeyAccountApi;
  if (!api) return;
  const country = document.getElementById('pickupCountry');
  const governorate = document.getElementById('pickupGovernorate');
  const city = document.getElementById('pickupCity');
  const address = document.getElementById('pickupAddress');
  const summary = document.getElementById('pickupSummary');
  const message = document.getElementById('pickupMessage');
  const save = document.getElementById('savePickup');
  let profile = {};
  let coordinates = null;

  const showMessage = (text, success = false) => {
    message.textContent = text;
    message.className = `view-message ${success ? 'success' : 'error'}`;
    message.hidden = false;
  };
  const addOptions = (select, rows, selectedId, placeholder) => {
    select.replaceChildren(new Option(placeholder, ''));
    (rows || []).forEach(row => select.add(new Option(row.name, String(row.id), false, String(row.id) === String(selectedId || ''))));
  };
  const lookup = async path => {
    const response = await fetch(`${api.origin}/api/${path}`, { headers: { Accept: 'application/json', 'Accept-Language': 'ar' } });
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || 'تعذر تحميل الموقع.');
    return result.data || [];
  };
  const loadGovernorates = async selected => {
    governorate.disabled = true;
    city.disabled = true;
    const rows = await lookup(`governorates?country_id=${encodeURIComponent(country.value)}&pagination=off`);
    addOptions(governorate, rows, selected, 'اختر المحافظة');
    governorate.disabled = false;
    if (governorate.value) await loadCities(profile.city_id);
  };
  const loadCities = async selected => {
    city.disabled = true;
    if (!governorate.value) {
      addOptions(city, [], null, 'اختر المدينة');
      return;
    }
    const rows = await lookup(`cities?governorate_id=${encodeURIComponent(governorate.value)}&pagination=off`);
    addOptions(city, rows, selected, 'اختر المدينة');
    city.disabled = false;
  };
  const updateSummary = () => {
    const names = [governorate.selectedOptions[0]?.textContent, city.selectedOptions[0]?.textContent, address.value.trim()].filter(Boolean);
    summary.textContent = names.join('، ') || 'لم يتم تحديد موقع الاستلام';
  };

  Promise.all([api.request('/user/profile', 'GET'), lookup('countries?pagination=off')]).then(async ([result, countries]) => {
    profile = result.data || {};
    addOptions(country, countries, profile.country_id || countries[0]?.id, 'اختر الدولة');
    if (!country.value && countries[0]) country.value = String(countries[0].id);
    address.value = profile.address || '';
    const lat = Number(profile.pickup_latitude);
    const long = Number(profile.pickup_longitude);
    if (Number.isFinite(lat) && Number.isFinite(long)) coordinates = { lat, long };
    await loadGovernorates(profile.governorate_id);
    updateSummary();
  }).catch(error => showMessage(error.message));

  country.addEventListener('change', () => { coordinates = null; profile.city_id = null; loadGovernorates(null).catch(error => showMessage(error.message)); });
  governorate.addEventListener('change', () => { coordinates = null; profile.city_id = null; loadCities(null).catch(error => showMessage(error.message)); });
  city.addEventListener('change', () => { coordinates = null; updateSummary(); });
  address.addEventListener('input', () => { coordinates = null; updateSummary(); });
  document.getElementById('detectLocation').addEventListener('click', event => {
    const button = event.currentTarget;
    if (!navigator.geolocation) return showMessage('تحديد الموقع غير متاح على هذا الجهاز.');
    button.disabled = true;
    button.textContent = 'جارٍ تحديد الموقع…';
    navigator.geolocation.getCurrentPosition(position => {
      coordinates = { lat: position.coords.latitude, long: position.coords.longitude };
      address.value = address.value || 'موقعي الحالي';
      updateSummary();
      button.disabled = false;
      button.textContent = 'تم تحديد الموقع';
    }, () => {
      button.disabled = false;
      button.textContent = 'استخدام موقعي الحالي';
      showMessage('تعذر الوصول إلى موقعك. تحقق من صلاحية الموقع وحاول مجددًا.');
    });
  });

  save.addEventListener('click', async () => {
    if (address.value.trim().length < 5) return showMessage('اكتب عنوانًا تفصيليًا صحيحًا.');
    if (!governorate.value || !city.value) return showMessage('اختر المحافظة والمدينة.');
    save.disabled = true;
    message.hidden = true;
    try {
      const body = new FormData();
      body.append('name', profile.name || '');
      body.append('country_id', country.value);
      body.append('governorate_id', governorate.value);
      body.append('city_id', city.value);
      body.append('address', address.value.trim());
      body.append('pickup_latitude', coordinates ? String(coordinates.lat) : '');
      body.append('pickup_longitude', coordinates ? String(coordinates.long) : '');
      const result = await api.request('/user/profile', 'POST', body);
      profile = { ...profile, ...(result.data || {}), address: address.value.trim(), city_id: city.value, governorate_id: governorate.value };
      updateSummary();
      showMessage(result.message || 'تم حفظ موقع الاستلام.', true);
    } catch (error) {
      showMessage(error.message || 'تعذر حفظ موقع الاستلام.');
    } finally {
      save.disabled = false;
    }
  });
})();
