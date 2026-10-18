(function () {
  const manifestUrl = new URL('../../public/build/manifest.json', document.currentScript.src);

  fetch(manifestUrl)
    .then(response => response.ok ? response.json() : null)
    .then(manifest => {
      const entry = manifest && manifest['resources/js/app.js'];
      if (!entry || !entry.file) return;

      const app = document.createElement('script');
      app.type = 'module';
      app.src = new URL(entry.file, manifestUrl).href;
      document.head.appendChild(app);
    })
    .catch(() => {});
})();
