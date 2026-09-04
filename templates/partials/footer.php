    <!-- jQuery (slim) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Main Application JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= asset('js/notif.js') ?>"></script>
    <script src="<?= asset('js/modal-helper.js') ?>"></script>
    <script src="<?= asset('js/searchable-select.js') ?>"></script>
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
    
    <?php include TEMPLATES_PATH . '/partials/loading_modal.php'; ?>
    
</body>
</html>
