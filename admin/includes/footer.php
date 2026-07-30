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
<?php
if (!isset($feedbackLibLoaded)) {
    require_once __DIR__ . '/feedback-lib.php';
}
if (feedbackCanUseTool()):
    $fbNextId = feedbackNextId(feedbackLoadItems());
?>
<link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/feedback-tool.css">
<script>
  window.__FEEDBACK_CONFIG__ = {
    enabled: true,
    submitUrl: <?= json_encode(ADMIN_URL . '/api/feedback-submit.php') ?>,
    adminPath: <?= json_encode(ADMIN_URL) ?>,
    nextId: <?= json_encode($fbNextId) ?>
  };
</script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="<?= ADMIN_URL ?>/assets/feedback-tool.js"></script>
<?php endif; ?>
<?php if (!empty($extraScripts)) echo $extraScripts; ?>
</body>
</html>
