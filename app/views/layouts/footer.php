            </div> </main> <footer class="text-center py-4 border-t print:hidden">
            <?php
                // Hitung waktu muat halaman
                $loadTime = microtime(true) - APP_START_TIME;
            ?>
            <p class="text-sm text-gray-600">
                &copy;                       <?php echo date('Y') ?> Product by <strong>Team IT UPT Perparkiran</strong>.
                <span class="mx-2 text-gray-300">|</span>
                Load page:                           <?php echo number_format($loadTime, 4) ?> detik.
            </p>
        </footer>
        </div> </div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
    };

    <?php if (isset($_SESSION['flash'])): ?>
        toastr['<?php echo $_SESSION['flash']['type'] ?>']('<?php echo $_SESSION['flash']['message'] ?>');
        <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

// Cek apakah elemen logout ada di halaman ini
    const logoutButton = document.getElementById('logout-link');
    if (logoutButton) {
        logoutButton.addEventListener('click', function(event) {
            // Mencegah link langsung dieksekusi
            event.preventDefault();

            const logoutUrl = this.href; // Simpan URL logout

            Swal.fire({
                title: 'Konfirmasi Logout',
                text: "Apakah Anda yakin ingin keluar dari sesi ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika dikonfirmasi, arahkan ke URL logout
                    window.location.href = logoutUrl;
                }
            });
        });
    }
</script>
</body>
</html>