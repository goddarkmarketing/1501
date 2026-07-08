<?php

function handleImageUpload(?array $file, string $subdir = 'uploads'): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ (รหัส ' . $file['error'] . ')');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('ไฟล์ใหญ่เกิน 5 MB');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($map[$mime])) {
        throw new RuntimeException('รองรับเฉพาะ JPG, PNG, WebP, GIF');
    }

    $dir = SITE_ROOT . '/assets/img/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('สร้างโฟลเดอร์อัปโหลดไม่ได้');
    }

    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('บันทึกไฟล์ไม่สำเร็จ');
    }

    return 'assets/img/' . $subdir . '/' . $name;
}

function imagePublicUrl(string $path): string
{
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return SITE_URL . '/' . ltrim($path, '/');
}

function renderImageField(string $name, string $value, string $label, string $fileName = ''): void
{
    $fileName = $fileName ?: ($name . '_file');
    $preview = imagePublicUrl($value);
    $uid = preg_replace('/[^a-z0-9]/i', '_', $name);
    ?>
    <div class="admin-image-field" data-image-field="<?= htmlspecialchars($uid) ?>">
      <label class="admin-label"><?= htmlspecialchars($label) ?></label>
      <div class="admin-image-field__box">
        <div class="admin-image-field__preview" id="preview_<?= $uid ?>">
          <?php if ($preview): ?>
          <img src="<?= htmlspecialchars($preview) ?>" alt="">
          <?php else: ?>
          <span class="admin-image-field__placeholder">ยังไม่มีรูป</span>
          <?php endif; ?>
        </div>
        <div class="admin-image-field__controls space-y-2">
          <input type="text" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>"
                 class="admin-input admin-image-path" placeholder="assets/img/... หรือ https://..."
                 data-preview-target="preview_<?= $uid ?>">
          <div class="flex flex-wrap gap-2">
            <label class="admin-btn-outline cursor-pointer text-xs">
              เลือกรูปจากเครื่อง
              <input type="file" name="<?= htmlspecialchars($fileName) ?>" accept="image/jpeg,image/png,image/webp,image/gif"
                     class="hidden admin-image-file" data-path-input="<?= htmlspecialchars($name) ?>"
                     data-preview-target="preview_<?= $uid ?>">
            </label>
            <button type="button" class="admin-btn-outline text-xs admin-image-clear"
                    data-path-input="<?= htmlspecialchars($name) ?>"
                    data-file-input="<?= htmlspecialchars($fileName) ?>"
                    data-preview-target="preview_<?= $uid ?>">ลบรูป</button>
          </div>
          <p class="text-xs text-slate-400">JPG, PNG, WebP, GIF — สูงสุด 5 MB</p>
        </div>
      </div>
    </div>
    <?php
}
