</div>
</div>
</div>

</div>

<!-- jQuery -->
<script src="<?= BASE_URL ?>aset/jquery/jquery.min.js"></script>
<!-- Bootstrap 5 JS -->
<script src="<?= BASE_URL ?>aset/bootstrap-5/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="<?= BASE_URL ?>aset/datatables/js/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>aset/datatables/js/dataTables.bootstrap5.min.js"></script>
<!-- Select2 JS -->
<script src="<?= BASE_URL ?>aset/select2/js/select2.min.js"></script>

<script>
  $(document).ready(function() {

    // 1. Sidebar toggle
    $('[data-widget="pushmenu"]').on('click', function(e) {
      e.preventDefault();
      $('body').toggleClass('sidebar-collapse');
    });

    // 2. Treeview menu
    $('.nav-sidebar .has-treeview > .nav-link').on('click', function(e) {
      e.preventDefault();
      var $parent = $(this).parent();
      var $treeview = $parent.find('> .nav-treeview');
      $parent.toggleClass('menu-open');
      //$treeview.slideSlide();
      $treeview.slideToggle(); // <--- PERBAIKI DI SINI

    });

    // 3. Inisialisasi DataTables
    if ($('.datatable').length > 0) {
      $('.datatable').DataTable({
        language: {
          lengthMenu: "Tampilkan _MENU_ data",
          zeroRecords: "Data tidak ditemukan",
          info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
          infoEmpty: "Tidak ada data",
          infoFiltered: "(dari _MAX_ total data)",
          search: "Cari:",
          paginate: {
            first: "Awal",
            last: "Akhir",
            next: "→",
            previous: "←"
          }
        },
        pageLength: 5,
        lengthMenu: [
          [5, 10, 25, 50, -1],
          [5, 10, 25, 50, "Semua"]
        ],
        ordering: true,
        responsive: true
      });
    }

    // ==================== FUNGSI KHUSUS: SELECT MOBIL ====================
    // Inisialisasi Select2 khusus untuk dropdown mobil (dengan event handler)
    function initSelectMobil($selectEl) {
      // Skip yang sudah jadi Select2
      if ($selectEl.hasClass('select2-hidden-accessible')) return;

      $selectEl.select2({
        placeholder: 'Pilih Mobil...',
        allowClear: true,
        width: '100%',
        language: {
          noResults: function() {
            return "Mobil tidak ditemukan";
          },
          searching: function() {
            return "Mencari...";
          }
        }
      });

      // Event change untuk auto-fill harga + update opsi
      $selectEl.on('change', function() {
        const selectedOption = $(this).find(':selected');
        const harga = selectedOption.data('harga') || 0;
        const $row = $(this).closest('.mobil-row');
        $row.find('.input-harga').val(harga);

        if (typeof hitungTotal === 'function') hitungTotal();
        if (typeof updateOpsiMobil === 'function') updateOpsiMobil();
      });
    }

    // ==================== INIT SAAT PAGE LOAD ====================
    // 1. Select2 biasa (Penyewa, Status, dll) — KECUALI .select-mobil
    $('.select2').not('.select-mobil').each(function() {
      if ($(this).hasClass('select2-hidden-accessible')) return;

      $(this).select2({
        placeholder: $(this).data('placeholder') || 'Pilih...',
        allowClear: true,
        width: '100%',
        language: {
          noResults: function() {
            return "Tidak ada hasil";
          },
          searching: function() {
            return "Mencari...";
          }
        }
      });
    });

    // 2. Dropdown mobil — inisialisasi dengan event handler khusus
    $('.select-mobil').each(function() {
      initSelectMobil($(this));
    });

    // ==================== EXPOSE KE GLOBAL SCOPE ====================
    window.initSelectMobil = initSelectMobil;
  });
</script>

</body>

</html>