            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard-animations.js"></script>
    <script>
        document.querySelectorAll('form[method="post"]:not([action])').forEach(function (form) {
            form.action = window.location.pathname.split('/').pop();
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>
