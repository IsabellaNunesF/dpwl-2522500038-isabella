<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php" class="brand">
          <div class="brand-icon">
            <img src="<?= BASE_URL ?>aset/images/logo-mjt.png" alt="Logo Usaha">
          </div>
          <div class="brand-text">
            <strong>Usaha</strong>
            <small>Rental</small>
          </div>
        </a>
        <p class="footer-desc">Penyedia layanan rental mobil premium terpercaya di Pulau Bangka.</p>
      </div>
      <div class="footer-col">
        <h4>Navigasi</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>penyewa/controller/tmobil_controller.php">Katalog</a></li>
          <li><a href="<?= BASE_URL ?>penyewa/controller/tlogin_controller.php">Masuk</a></li>
          <li><a href="<?= BASE_URL ?>penyewa/controller/tregistrasi_controller.php">Registrasi</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Layanan</h4>
        <ul>
          <li><a href="#">Rental Harian</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Kontak</h4>
        <ul class="contact-list">
          <li><i class="fa-solid fa-location-dot"></i> Jalan Senang Kode, Provinsi Kepulauan Bangka Belitung</li>
          <li><i class="fa-solid fa-location-dot"></i><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d364.6473702574873!2d106.11142778821997!3d-2.0868848851446065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e22c0395ab695df%3A0xbc21e9969aa0079!2sISB%20ATMA%20LUHUR!5e1!3m2!1sen!2sid!4v1789529306338!5m2!1sen!2sid" width="300" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></li>
          <li><i class="fa-brands fa-whatsapp"></i> 0852 6852 1825</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Usaha.</p>
    </div>
  </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollTop" class="scroll-top"><i class="fa-solid fa-arrow-up"></i></button>

<!-- jQuery -->
<script src="<?= BASE_URL ?>aset/jquery/jquery.min.js"></script>

<!-- Semua Script digabung di sini (menggantikan script.js) -->
<script>
  (function($) {
    'use strict';

    /* =========================================================
    UTILITAS: Toast Notification
    ========================================================= */
    function showToast(message, icon = 'fa-circle-check') {
      const $toast = $(`
            <div class="toast">
                <i class="fa-solid ${icon}"></i>
                <span>${message}</span>
            </div>
        `).appendTo('body');

      setTimeout(() => $toast.addClass('show'), 50);
      setTimeout(() => {
        $toast.removeClass('show');
        setTimeout(() => $toast.remove(), 400);
      }, 2500);
    }

    /* =========================================================
    KERANJANG (AJAX - Update badge dari database)
    ========================================================= */
    function addToCart(noPlat, namaMobil, harga) {
      const $btn = $(`.btn-add-cart[data-id="${noPlat}"]`);

      $.ajax({
        url: '<?= BASE_URL ?>penyewa/controller/keranjang_controller.php',
        method: 'POST',
        data: {
          action: 'add',
          no_plat: noPlat
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            // PERBAIKAN: Update badge dengan jumlah dari database
            $('#cartCount').text(response.count);

            if (response.action === 'added') {
              $btn.addClass('added').html('<i class="fa-solid fa-check"></i> Ditambahkan');
              showToast(`${namaMobil} ditambahkan ke keranjang!`, 'fa-cart-plus');
            } else {
              $btn.removeClass('added').html('<i class="fa-solid fa-cart-plus"></i> Keranjang');
              showToast(`${namaMobil} dihapus dari keranjang`, 'fa-trash');
            }
          }
        },
        error: function() {
          showToast('Gagal memproses keranjang', 'fa-exclamation-triangle');
        }
      });
    }

    // Fungsi untuk refresh keranjang dari database
    function refreshKeranjangCount() {
      $.ajax({
        url: '<?= BASE_URL ?>penyewa/controller/keranjang_controller.php',
        method: 'GET',
        data: {
          action: 'count'
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            $('#cartCount').text(response.count);
          }
        }
      });
    }

    /* =========================================================
    SLIDER GALERI DETAIL (Hanya aktif di halaman detail)
    ========================================================= */
    function initDetailSlider() {
      const track = document.getElementById('sliderTrack');
      if (!track) return; // Keluar jika tidak ada slider

      const thumbs = document.querySelectorAll('.thumb-item');
      const btnPrev = document.getElementById('btnPrev');
      const btnNext = document.getElementById('btnNext');
      const counter = document.getElementById('currentSlide');
      const total = track.children.length;
      let current = 0;

      function goTo(index) {
        if (index < 0) index = total - 1;
        if (index >= total) index = 0;
        current = index;
        track.style.transform = `translateX(-${current * 100}%)`;
        if (counter) counter.textContent = current + 1;
        thumbs.forEach((t, i) => t.classList.toggle('active', i === current));
      }

      if (btnPrev) btnPrev.addEventListener('click', () => goTo(current - 1));
      if (btnNext) btnNext.addEventListener('click', () => goTo(current + 1));
      thumbs.forEach(t => t.addEventListener('click', () => goTo(parseInt(t.dataset.index))));

      // Navigasi keyboard
      document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') goTo(current - 1);
        if (e.key === 'ArrowRight') goTo(current + 1);
      });

      // Swipe support untuk mobile
      let startX = 0;
      track.addEventListener('touchstart', e => startX = e.touches[0].clientX, {
        passive: true
      });
      track.addEventListener('touchend', e => {
        const diff = e.changedTouches[0].clientX - startX;
        if (Math.abs(diff) > 50) goTo(current + (diff < 0 ? 1 : -1));
      }, {
        passive: true
      });
    }

    /* =========================================================
    EVENT BINDINGS
    ========================================================= */
    function bindEvents() {
      // Dropdown Profil User
      $(document).on('click', '#userToggle', function(e) {
        e.stopPropagation();
        $('#userMenu').toggleClass('show');
      });

      $(document).on('click', function(e) {
        if (!$(e.target).closest('.user-dropdown').length) {
          $('#userMenu').removeClass('show');
        }
      });

      // Slider harga
      $('#priceRange').on('input', function() {
        const val = parseInt($(this).val());
        $('#priceMaxLabel').text(val.toLocaleString('id-ID'));
      });

      // Tombol preset harga
      $('.preset-btn').on('click', function() {
        const max = parseInt($(this).data('max'));
        $('#priceRange').val(max).trigger('input');
        $('.preset-btn').removeClass('active');
        $(this).addClass('active');
      });

      // Klik tombol Add to Cart
      // $(document).on('click', '.btn-add-cart', function(e) {
      //   e.preventDefault();
      //   const noPlat = $(this).data('id');
      //   const nama = $(this).data('nama');
      //   const harga = $(this).data('harga');
      //   addToCart(noPlat, nama, harga);
      // });

      // Tombol favorit
      $(document).on('click', '.car-fav', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
      });

      // Scroll to top & Header effect
      $(window).on('scroll', function() {
        const sc = $(this).scrollTop();
        $('#scrollTop').toggleClass('visible', sc > 400);
        $('#siteHeader').toggleClass('scrolled', sc > 50);
      });

      $('#scrollTop').on('click', function() {
        $('html, body').animate({
          scrollTop: 0
        }, 700);
      });

      // Mobile menu toggle
      $('#menuToggle').on('click', function() {
        $('.main-nav').slideToggle(300);
      });

      // Profil Tab Switching
      $(document).on('click', '.profil-menu .menu-item', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        $('.profil-menu .menu-item').removeClass('active');
        $(this).addClass('active');
        $('.profil-section').removeClass('active');
        $('#' + target).addClass('active');
      });

      // Auto-hide notifikasi login setelah 5 detik
      setTimeout(() => {
        const $notif = $('#notifBar');
        if ($notif.length) {
          $notif.css({
            transition: 'all 0.5s ease',
            transform: 'translateY(-100%)',
            opacity: 0
          });
          setTimeout(() => $notif.remove(), 600);
        }
      }, 5000);
    }

    /* =========================================================
    LOADING OVERLAY
    ========================================================= */
    function hideLoader() {
      setTimeout(() => {
        $('#loader').addClass('hidden');
        setTimeout(() => $('#loader').remove(), 600);
      }, 800);
    }

    /* =========================================================
    INISIALISASI
    ========================================================= */
    $(document).ready(function() {
      bindEvents();
      initDetailSlider(); // Inisialisasi slider detail
      hideLoader();

      // PERBAIKAN: Refresh keranjang saat halaman dimuat
      refreshKeranjangCount();
    });

  })(jQuery);
</script>
</body>

</html>