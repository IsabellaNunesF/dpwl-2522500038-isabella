<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🗂️ Struktur Folder Generator</title>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    /* ============================================================
           RESET & BASE
           ============================================================ */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #0f172a;
      color: #e2e8f0;
      min-height: 100vh;
      padding: 30px;
    }

    h1 {
      text-align: center;
      font-size: 1.8rem;
      margin-bottom: 30px;
      background: linear-gradient(135deg, #38bdf8, #818cf8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 380px 1fr;
      gap: 24px;
    }

    /* ============================================================
           PANEL KIRI: FORM INPUT
           ============================================================ */
    .panel {
      background: #1e293b;
      border-radius: 16px;
      padding: 24px;
      border: 1px solid #334155;
    }

    .panel-title {
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 20px;
      color: #38bdf8;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      display: block;
      font-size: 0.85rem;
      color: #94a3b8;
      margin-bottom: 6px;
      font-weight: 600;
    }

    .form-group select,
    .form-group input[type="text"] {
      width: 100%;
      padding: 10px 14px;
      background: #0f172a;
      border: 1px solid #475569;
      border-radius: 10px;
      color: #e2e8f0;
      font-size: 0.95rem;
      transition: border-color 0.2s;
    }

    .form-group select:focus,
    .form-group input[type="text"]:focus {
      outline: none;
      border-color: #38bdf8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
    }

    /* --- Tipe: Folder / File --- */
    .type-toggle {
      display: flex;
      gap: 0;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid #475569;
    }

    .type-toggle button {
      flex: 1;
      padding: 10px;
      border: none;
      background: #0f172a;
      color: #94a3b8;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .type-toggle button.active-folder {
      background: #166534;
      color: #4ade80;
    }

    .type-toggle button.active-file {
      background: #1e3a5f;
      color: #38bdf8;
    }

    .type-toggle button:hover:not(.active-folder):not(.active-file) {
      background: #1e293b;
    }

    /* --- Path preview --- */
    .path-preview {
      background: #0f172a;
      border: 1px solid #475569;
      border-radius: 10px;
      padding: 12px 14px;
      font-family: 'Courier New', monospace;
      font-size: 0.85rem;
      color: #facc15;
      min-height: 42px;
      word-break: break-all;
    }

    /* --- Tombol --- */
    .btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 10px;
      font-size: 0.95rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 4px;
    }

    .btn-add {
      background: linear-gradient(135deg, #2563eb, #7c3aed);
      color: white;
    }

    .btn-add:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
    }

    .btn-reset {
      background: #dc2626;
      color: white;
      margin-top: 10px;
    }

    .btn-reset:hover {
      background: #b91c1c;
    }

    .btn-export {
      background: linear-gradient(135deg, #059669, #0d9488);
      color: white;
      margin-top: 10px;
    }

    .btn-export:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
    }

    /* --- Notifikasi --- */
    .notif {
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 0.85rem;
      margin-top: 12px;
      display: none;
      font-weight: 600;
    }

    .notif-success {
      background: #14532d;
      color: #4ade80;
      border: 1px solid #22c55e;
    }

    .notif-error {
      background: #7f1d1d;
      color: #fca5a5;
      border: 1px solid #ef4444;
    }

    /* ============================================================
           PANEL KANAN: TREE VISUALISASI
           ============================================================ */
    .tree-output {
      background: #1e293b;
      border-radius: 16px;
      padding: 24px;
      border: 1px solid #334155;
      min-height: 400px;
    }

    .tree-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .tree-count {
      font-size: 0.8rem;
      color: #64748b;
      background: #0f172a;
      padding: 4px 12px;
      border-radius: 20px;
    }

    /* --- Node --- */
    .tree-container {
      font-family: 'Courier New', Consolas, monospace;
      font-size: 0.9rem;
      line-height: 1.4;
    }

    .tree-empty {
      text-align: center;
      color: #475569;
      padding: 60px 20px;
      font-family: 'Segoe UI', sans-serif;
    }

    .tree-empty .icon {
      font-size: 3rem;
      margin-bottom: 12px;
    }

    .tree-node {
      padding: 4px 0;
      display: flex;
      align-items: center;
      gap: 0;
      transition: background 0.15s;
      border-radius: 6px;
      padding: 3px 6px;
      cursor: pointer;
      position: relative;
    }

    .tree-node:hover {
      background: rgba(56, 189, 248, 0.08);
    }

    .tree-node .indent {
      display: inline-block;
      width: 24px;
      flex-shrink: 0;
      color: #475569;
      user-select: none;
    }

    .tree-node .connector {
      color: #475569;
    }

    .tree-node .node-icon {
      margin-right: 6px;
      flex-shrink: 0;
    }

    .tree-node .node-name {
      color: #e2e8f0;
      font-weight: 500;
    }

    .tree-node .node-name.folder-name {
      color: #fbbf24;
      font-weight: 700;
    }

    .tree-node .node-name.file-name {
      color: #60a5fa;
    }

    .tree-node .node-path {
      color: #475569;
      font-size: 0.75rem;
      margin-left: 8px;
    }

    .tree-node .btn-delete {
      margin-left: auto;
      background: none;
      border: none;
      color: #475569;
      cursor: pointer;
      font-size: 0.8rem;
      padding: 2px 6px;
      border-radius: 4px;
      opacity: 0;
      transition: all 0.15s;
    }

    .tree-node:hover .btn-delete {
      opacity: 1;
    }

    .tree-node .btn-delete:hover {
      color: #f87171;
      background: rgba(248, 113, 113, 0.15);
    }

    /* --- Selected highlight --- */
    .tree-node.selected-parent {
      background: rgba(56, 189, 248, 0.12);
      border-left: 3px solid #38bdf8;
    }

    /* ============================================================
           RESPONSIVE
           ============================================================ */
    @media (max-width: 800px) {
      .container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  <h1>🗂️ Folder & File Structure Generator</h1>

  <div class="container">

    <!-- ============================================================
         PANEL KIRI: FORM
         ============================================================ -->
    <div class="panel">
      <div class="panel-title">⚙️ Tambah Node</div>

      <!-- Pilih Parent -->
      <div class="form-group">
        <label>📌 Pilih Parent (Root / Folder)</label>
        <select id="parentSelect">
          <option value="__root__">/ (Root)</option>
        </select>
      </div>

      <!-- Nama -->
      <div class="form-group">
        <label>✏️ Nama Folder / File</label>
        <input type="text" id="nodeName" placeholder="contoh: penyewa, index.php, admin" />
      </div>

      <!-- Tipe -->
      <div class="form-group">
        <label>📁 Tipe</label>
        <div class="type-toggle">
          <button id="btnFolder" class="active-folder" data-type="folder">📁 Folder</button>
          <button id="btnFile" data-type="file">📄 File</button>
        </div>
      </div>

      <!-- Path Preview -->
      <div class="form-group">
        <label>📍 Path Preview</label>
        <div class="path-preview" id="pathPreview">/</div>
      </div>

      <!-- Tombol -->
      <button class="btn btn-add" id="btnAdd">➕ Tambahkan</button>
      <button class="btn btn-export" id="btnExport">📋 Copy Struktur ke Clipboard</button>
      <button class="btn btn-reset" id="btnReset">🗑️ Reset Semua</button>

      <!-- Notifikasi -->
      <div class="notif notif-success" id="notifSuccess"></div>
      <div class="notif notif-error" id="notifError"></div>
    </div>

    <!-- ============================================================
         PANEL KANAN: TREE OUTPUT
         ============================================================ -->
    <div class="tree-output">
      <div class="tree-header">
        <div class="panel-title">🌳 Struktur Pohon</div>
        <span class="tree-count" id="nodeCount">0 node</span>
      </div>
      <div class="tree-container" id="treeContainer">
        <div class="tree-empty">
          <div class="icon">📂</div>
          <div>Belum ada folder atau file.<br>Tambahkan dari panel kiri.</div>
        </div>
      </div>
    </div>

  </div>

  <script>
    $(function() {

      /* ================================================================
         DATA STORE
         ================================================================ */
      let nodes = [];
      let nextId = 1;
      let selectedType = 'folder';

      /* ================================================================
         HELPER: Bangun path lengkap dari root sampai node
         ================================================================ */
      function buildPath(nodeId) {
        const parts = [];
        let current = nodes.find(n => n.id === nodeId);
        while (current) {
          parts.unshift(current.name);
          current = current.parentId ?
            nodes.find(n => n.id === current.parentId) :
            null;
        }
        return '/' + parts.join('/');
      }

      /* ================================================================
         HELPER: Ambil semua folder (sebagai kandidat parent)
         ================================================================ */
      function getFolders() {
        return nodes.filter(n => n.type === 'folder');
      }

      /* ================================================================
         HELPER: Ambil children dari parentId
         ================================================================ */
      function getChildren(parentId) {
        return nodes
          .filter(n => n.parentId === parentId)
          .sort((a, b) => {
            // folder dulu, lalu file
            if (a.type !== b.type) return a.type === 'folder' ? -1 : 1;
            return a.name.localeCompare(b.name);
          });
      }

      /* ================================================================
         RENDER: Update dropdown parent
         ================================================================ */
      function renderParentSelect() {
        const $sel = $('#parentSelect');
        const currentVal = $sel.val();
        $sel.empty();
        $sel.append('<option value="__root__">/ (Root)</option>');

        const folders = getFolders();
        folders.forEach(f => {
          const path = buildPath(f.id) + '/';
          $sel.append(`<option value="${f.id}">${path}</option>`);
        });

        // restore selection if still valid
        if ($sel.find(`option[value="${currentVal}"]`).length) {
          $sel.val(currentVal);
        }

        updatePathPreview();
      }

      /* ================================================================
         RENDER: Update path preview
         ================================================================ */
      function updatePathPreview() {
        const parentId = $('#parentSelect').val();
        const name = $('#nodeName').val().trim() || 'nama_node';
        let path = '/';

        if (parentId !== '__root__') {
          path = buildPath(parseInt(parentId)) + '/';
        }
        path += name + (selectedType === 'folder' ? '/' : '');

        $('#pathPreview').text(path);
      }

      /* ================================================================
         RENDER: Tree visual (rekursif)
         ================================================================ */
      function renderTree() {
        const $container = $('#treeContainer');
        $container.empty();

        if (nodes.length === 0) {
          $container.html(`
                <div class="tree-empty">
                    <div class="icon">📂</div>
                    <div>Belum ada folder atau file.<br>Tambahkan dari panel kiri.</div>
                </div>
            `);
          $('#nodeCount').text('0 node');
          return;
        }

        const selectedParent = $('#parentSelect').val();
        renderBranch('__root__', 0, $container, selectedParent, true);
        $('#nodeCount').text(nodes.length + ' node');
      }

      function renderBranch(parentId, depth, $container, selectedParent, isRoot) {
        const children = getChildren(
          parentId === '__root__' ? null : parentId
        );

        children.forEach((node, index) => {
          const isLast = index === children.length - 1;
          const icon = node.type === 'folder' ? '📁' : '📄';
          const nameClass = node.type === 'folder' ? 'folder-name' : 'file-name';
          const path = buildPath(node.id) + (node.type === 'folder' ? '/' : '');

          // indent
          let indents = '';
          for (let i = 0; i < depth; i++) {
            indents += '<span class="indent">│&nbsp;&nbsp;</span>';
          }

          const connector = depth === 0 ?
            (isLast ? '└─ ' : '├─ ') :
            (isLast ? '└── ' : '├── ');

          const selectedClass =
            selectedParent !== '__root__' && parseInt(selectedParent) === node.id ?
            'selected-parent' :
            '';

          const html = `
                <div class="tree-node ${selectedClass}" data-id="${node.id}" data-type="${node.type}">
                    ${indents}
                    <span class="connector">${connector}</span>
                    <span class="node-icon">${icon}</span>
                    <span class="node-name ${nameClass}">${escapeHtml(node.name)}</span>
                    <span class="node-path">${escapeHtml(path)}</span>
                    <button class="btn-delete" title="Hapus">✕</button>
                </div>
            `;

          $container.append(html);

          // rekursi untuk anak folder
          if (node.type === 'folder') {
            renderBranch(node.id, depth + 1, $container, selectedParent, false);
          }
        });
      }

      /* ================================================================
         HELPER: Escape HTML
         ================================================================ */
      function escapeHtml(text) {
        const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
      }

      /* ================================================================
         HELPER: Tampilkan notifikasi
         ================================================================ */
      function showNotif(type, msg) {
        const $el = type === 'success' ? $('#notifSuccess') : $('#notifError');
        $el.text(msg).fadeIn(200);
        setTimeout(() => $el.fadeOut(400), 2500);
      }

      /* ================================================================
         EVENT: Toggle tipe (Folder / File)
         ================================================================ */
      $('#btnFolder, #btnFile').on('click', function() {
        selectedType = $(this).data('type');

        $('#btnFolder').removeClass('active-folder');
        $('#btnFile').removeClass('active-file');

        if (selectedType === 'folder') {
          $(this).addClass('active-folder');
        } else {
          $(this).addClass('active-file');
        }

        updatePathPreview();
      });

      /* ================================================================
         EVENT: Perubahan di form → update preview
         ================================================================ */
      $('#parentSelect, #nodeName').on('change input', function() {
        updatePathPreview();
      });

      /* ================================================================
         EVENT: Klik node di tree → jadikan parent
         ================================================================ */
      $(document).on('click', '.tree-node', function(e) {
        // jangan trigger jika klik tombol delete
        if ($(e.target).hasClass('btn-delete')) return;

        const nodeId = $(this).data('id');
        const nodeType = $(this).data('type');

        if (nodeType === 'file') {
          showNotif('error', '⚠️ File tidak bisa dijadikan parent!');
          return;
        }

        $('#parentSelect').val(nodeId);
        updatePathPreview();
        renderTree();
      });

      /* ================================================================
         EVENT: Tambah Node
         ================================================================ */
      $('#btnAdd').on('click', function() {
        const name = $('#nodeName').val().trim();
        const parentId = $('#parentSelect').val();

        // Validasi
        if (!name) {
          showNotif('error', '❌ Nama tidak boleh kosong!');
          $('#nodeName').focus();
          return;
        }

        if (/[/\\:*?"<>|]/.test(name)) {
          showNotif('error', '❌ Nama mengandung karakter terlarang!');
          return;
        }

        // Cek duplikat
        const realParentId = parentId === '__root__' ? null : parseInt(parentId);
        const exists = nodes.some(
          n => n.name.toLowerCase() === name.toLowerCase() && n.parentId === realParentId
        );

        if (exists) {
          showNotif('error', `❌ "${name}" sudah ada di lokasi ini!`);
          return;
        }

        // Tambah ke array
        const newNode = {
          id: nextId++,
          name: name,
          parentId: realParentId,
          type: selectedType
        };

        nodes.push(newNode);

        // Reset form
        $('#nodeName').val('');

        // Re-render
        renderParentSelect();
        renderTree();
        updatePathPreview();

        const path = buildPath(newNode.id) + (selectedType === 'folder' ? '/' : '');
        showNotif('success', `✅ Ditambahkan: ${path}`);
      });

      /* ================================================================
         EVENT: Hapus Node (+ semua children rekursif)
         ================================================================ */
      $(document).on('click', '.btn-delete', function(e) {
        e.stopPropagation();

        const nodeId = $(this).closest('.tree-node').data('id');
        const node = nodes.find(n => n.id === nodeId);

        if (!confirm(`Hapus "${node.name}" beserta semua isinya?`)) return;

        // Hapus rekursif
        function removeRecursive(id) {
          const children = nodes.filter(n => n.parentId === id);
          children.forEach(c => removeRecursive(c.id));
          nodes = nodes.filter(n => n.id !== id);
        }

        removeRecursive(nodeId);

        renderParentSelect();
        renderTree();
        showNotif('success', `🗑️ "${node.name}" berhasil dihapus.`);
      });

      /* ================================================================
         EVENT: Reset Semua
         ================================================================ */
      $('#btnReset').on('click', function() {
        if (nodes.length === 0) return;
        if (!confirm('Hapus SEMUA node?')) return;

        nodes = [];
        nextId = 1;

        renderParentSelect();
        renderTree();
        updatePathPreview();
        showNotif('success', '🗑️ Semua node telah dihapus.');
      });

      /* ================================================================
         EVENT: Export / Copy ke Clipboard
         ================================================================ */
      $('#btnExport').on('click', function() {
        if (nodes.length === 0) {
          showNotif('error', '⚠️ Tidak ada struktur untuk di-copy!');
          return;
        }

        const text = generateTextTree('__root__', 0);
        navigator.clipboard.writeText(text).then(() => {
          showNotif('success', '📋 Struktur berhasil di-copy ke clipboard!');
        }).catch(() => {
          // Fallback
          const ta = document.createElement('textarea');
          ta.value = text;
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
          showNotif('success', '📋 Struktur berhasil di-copy!');
        });
      });

      function generateTextTree(parentId, depth) {
        const children = getChildren(
          parentId === '__root__' ? null : parentId
        );

        let result = '';

        if (depth === 0) {
          // root header
          const rootName = $('#parentSelect option:selected').text() || '/';
          // tidak perlu print root jika kosong
        }

        children.forEach((node, index) => {
          const isLast = index === children.length - 1;
          const icon = node.type === 'folder' ? '📁 ' : '📄 ';
          const connector = isLast ? '└── ' : '├── ';

          let indent = '';
          for (let i = 0; i < depth; i++) {
            indent += '│   ';
          }

          result += indent + connector + icon + node.name + '\n';

          if (node.type === 'folder') {
            result += generateTextTree(node.id, depth + 1);
          }
        });

        return result;
      }

      /* ================================================================
         EVENT: Enter key pada input
         ================================================================ */
      $('#nodeName').on('keypress', function(e) {
        if (e.which === 13) {
          e.preventDefault();
          $('#btnAdd').click();
        }
      });

      /* ================================================================
         INIT
         ================================================================ */
      renderParentSelect();
      renderTree();
      updatePathPreview();

    });
  </script>

</body>

</html>