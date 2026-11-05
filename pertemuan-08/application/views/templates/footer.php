<footer class="main-footer">
  <div class="pull-right hidden-xs">Anything you want</div>
  <strong>Copyright &copy; 2016 <a href="#">Company</a>.</strong> All rights reserved.
</footer>
</div>

<script src="<?= htmlspecialchars($adminlte_url . 'bower_components/jquery/dist/jquery.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($adminlte_url . 'bower_components/bootstrap/dist/js/bootstrap.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($adminlte_url . 'bower_components/datatables.net/js/jquery.dataTables.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($adminlte_url . 'bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars($adminlte_url . 'dist/js/adminlte.min.js', ENT_QUOTES, 'UTF-8') ?>"></script>

<script>
  $(function() {
    $('.js-data-table').DataTable({
      'paging': true,
      'lengthChange': true,
      'searching': true,
      'ordering': true,
      'info': true,
      'autoWidth': false,
      'columnDefs': [{
        'orderable': false,
        'targets': 'no-sort'
      }]
    });
  });
</script>
</body>

</html>