(() => {
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  const categoryGroup = document.querySelector('.home-filter-scroller');
  const governorateSelect = document.getElementById('homeGovernorate');
  const error = document.getElementById('homeFilterError');
  const apply = document.querySelector('.home-apply-filters');
  if (!categoryGroup || !governorateSelect || !apply) return;

  const loadChoices = async (path, onItem) => {
    const response = await fetch(`${origin}/api/${path}`, { headers: { Accept: 'application/json', 'Accept-Language': 'ar' } });
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || 'تعذر تحميل خيارات الفلترة.');
    (result.data || []).forEach(onItem);
  };
  Promise.all([
    loadChoices('main-categories', category => {
      const chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'home-filter-chip';
      chip.dataset.categoryId = String(category.id);
      chip.textContent = category.name;
      chip.addEventListener('click', () => {
        categoryGroup.querySelectorAll('.home-filter-chip').forEach(item => item.classList.remove('selected'));
        chip.classList.add('selected');
      });
      categoryGroup.append(chip);
    }),
    loadChoices('governorates', governorate => {
      const option = document.createElement('option');
      option.value = String(governorate.id);
      option.textContent = governorate.name;
      governorateSelect.append(option);
    })
  ]).catch(cause => {
    error.textContent = cause.message || 'تعذر تحميل خيارات الفلترة.';
    error.hidden = false;
  });
  apply.addEventListener('click', () => {
    const params = new URLSearchParams();
    const category = categoryGroup.querySelector('.home-filter-chip.selected')?.dataset.categoryId;
    const sort = document.querySelector('.home-sort-option.selected')?.dataset.sort;
    const search = document.getElementById('globalSearch')?.value.trim();
    if (category) params.set('main_category_id', category);
    if (governorateSelect.value) params.set('governorate_id', governorateSelect.value);
    if (sort) params.set('sort', sort);
    if (search) params.set('search', search);
    location.href = `23-search-results.html?${params}`;
  });
})();
