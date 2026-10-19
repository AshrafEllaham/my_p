(() => {
  const api = window.SaeyAccountApi;
  const form = document.getElementById('profileForm');
  if (!api || !form) return;

  const message = document.getElementById('profileMessage');
  const save = document.getElementById('saveProfile');
  const photo = document.getElementById('profilePhoto');
  const photoFile = document.getElementById('profilePhotoFile');
  const showMessage = (text, success = false) => {
    message.textContent = text;
    message.className = `view-message ${success ? 'success' : 'error'}`;
    message.hidden = false;
  };

  api.request('/user/profile', 'GET').then(result => {
    const profile = result.data || {};
    document.getElementById('profileName').value = profile.name || '';
    document.getElementById('profilePhone').value = [profile.phone_code, profile.phone].filter(Boolean).join(' ');
    document.getElementById('profileEmail').value = profile.email || '';
    if (profile.image) photo.src = profile.image;
    window.saeyUserProfile = profile;
  }).catch(error => showMessage(error.message));

  photoFile.addEventListener('change', () => {
    const file = photoFile.files?.[0];
    if (file) photo.src = URL.createObjectURL(file);
  });

  form.addEventListener('submit', async event => {
    event.preventDefault();
    save.disabled = true;
    message.hidden = true;
    const body = new FormData();
    body.append('name', document.getElementById('profileName').value.trim());
    if (photoFile.files?.[0]) body.append('image', photoFile.files[0]);
    try {
      const result = await api.request('/user/profile', 'POST', body);
      const profile = result.data || {};
      if (profile.image) photo.src = profile.image;
      showMessage(result.message || 'تم حفظ التغييرات.', true);
    } catch (error) {
      showMessage(error.message || 'تعذر حفظ الملف الشخصي.');
    } finally {
      save.disabled = false;
    }
  });
})();
