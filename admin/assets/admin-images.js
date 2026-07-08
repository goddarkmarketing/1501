(function () {
  function setPreview(targetId, src) {
    const box = document.getElementById(targetId);
    if (!box) return;
    if (src) {
      box.innerHTML = '<img src="' + src.replace(/"/g, '&quot;') + '" alt="">';
    } else {
      box.innerHTML = '<span class="admin-image-field__placeholder">ยังไม่มีรูป</span>';
    }
  }

  function resolveUrl(path) {
    if (!path) return '';
    if (/^https?:\/\//i.test(path)) return path;
    const base = document.querySelector('meta[name="site-url"]')?.content || '/1501';
    return base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
  }

  document.addEventListener('change', function (e) {
    const input = e.target;
    if (!input.classList.contains('admin-image-file')) return;
    const file = input.files && input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function () {
      setPreview(input.dataset.previewTarget, reader.result);
    };
    reader.readAsDataURL(file);
  });

  document.addEventListener('input', function (e) {
    const input = e.target;
    if (!input.classList.contains('admin-image-path')) return;
    setPreview(input.dataset.previewTarget, resolveUrl(input.value.trim()));
  });

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.admin-image-clear');
    if (!btn) return;
    const pathInput = document.querySelector('[name="' + btn.dataset.pathInput + '"]');
    if (pathInput) pathInput.value = '';
    const fileName = btn.dataset.fileInput || (btn.dataset.pathInput + '_file');
    const fileInput = document.querySelector('[name="' + fileName + '"]');
    if (fileInput) fileInput.value = '';
    setPreview(btn.dataset.previewTarget, '');
  });
})();
