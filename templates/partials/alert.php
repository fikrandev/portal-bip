<?php
/**
 * Flash Alert Messages
 * Triggers SweetAlert2 via AppNotif helper
 */

$flashSuccess = Response::flash('success');
$flashError   = Response::flash('error');
$flashWarning = Response::flash('warning');
$flashInfo    = Response::flash('info');

$validationErrors = Response::validationErrors();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($flashSuccess): ?>
        AppNotif.success('Berhasil', '<?= e($flashSuccess) ?>');
    <?php endif; ?>

    <?php if ($flashError): ?>
        AppNotif.error('Gagal', '<?= e($flashError) ?>');
    <?php endif; ?>

    <?php if ($flashWarning): ?>
        AppNotif.warning('Peringatan', '<?= e($flashWarning) ?>');
    <?php endif; ?>

    <?php if ($flashInfo): ?>
        AppNotif.info('Informasi', '<?= e($flashInfo) ?>');
    <?php endif; ?>

    <?php if (!empty($validationErrors)): ?>
        <?php
        $errorMessages = [];
        foreach ($validationErrors as $field => $errors) {
            foreach ((array) $errors as $error) {
                $errorMessages[] = e($error);
            }
        }
        $errorText = implode('<br>', $errorMessages);
        ?>
        AppNotif.error('Terdapat Kesalahan', '<?= $errorText ?>');
    <?php endif; ?>
});
</script>
