  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/preline@3/dist/preline.min.js"></script>
<script src="https://unpkg.com/lucide@0.477.0"></script>
<script src="<?= ADMIN_URL ?>/assets/admin-images.js"></script>
<script>
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
</script>
<?php if (!empty($extraScripts)) echo $extraScripts; ?>
</body>
</html>
