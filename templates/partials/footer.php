    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Main Application JS -->
    <script src="<?= asset('js/app.js') ?>?v=<?= file_exists(PUBLIC_PATH . '/js/app.js') ? filemtime(PUBLIC_PATH . '/js/app.js') : '1.1' ?>"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= asset('js/modal-helper.js') ?>?v=<?= file_exists(PUBLIC_PATH . '/js/modal-helper.js') ? filemtime(PUBLIC_PATH . '/js/modal-helper.js') : '1.1' ?>"></script>
    <script src="<?= asset('js/notif.js') ?>?v=<?= file_exists(PUBLIC_PATH . '/js/notif.js') ? filemtime(PUBLIC_PATH . '/js/notif.js') : '1.1' ?>"></script>
    <script src="<?= asset('js/searchable-select.js') ?>?v=<?= file_exists(PUBLIC_PATH . '/js/searchable-select.js') ? filemtime(PUBLIC_PATH . '/js/searchable-select.js') : '1.1' ?>"></script>
    <script src="<?= asset('js/portal-pjax.js') ?>?v=<?= file_exists(PUBLIC_PATH . '/js/portal-pjax.js') ? filemtime(PUBLIC_PATH . '/js/portal-pjax.js') : '1.0' ?>"></script>
    <div id="portal-extra-js" style="display:none;">
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
    </div>
    
    <?php include TEMPLATES_PATH . '/partials/loading_modal.php'; ?>
    
</body>
</html>
