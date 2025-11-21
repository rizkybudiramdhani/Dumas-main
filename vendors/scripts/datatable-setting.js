/**
 * DataTable Configuration untuk Sistem Manajemen Dokumen
 * File: datatable-init.js
 */
$(document).ready(function () {
  /**
   * Konfigurasi DataTable untuk tabel dokumen
   */
  const dataTableConfig = {
    // Layout & Display
    scrollCollapse: true,
    autoWidth: false,
    responsive: true,

    // Pagination
    pageLength: 10,
    lengthMenu: [
      [5, 10, 25, 50, -1],
      [5, 10, 25, 50, "Semua"],
    ],

    // Searching & Filtering
    searching: true,

    // Column Configuration
    columnDefs: [
      {
        // Kolom Action tidak bisa di-sort
        targets: -1,
        orderable: false,
        className: "text-center",
      },
      {
        // Kolom Status
        targets: 4,
        orderable: true,
        className: "text-center",
      },
      {
        // Kolom Tanggal - format tanggal Indonesia
        targets: 5,
        type: "date",
        render: function (data, type, row) {
          if (type === "sort" || type === "type") {
            // Konversi format dd-mm-yyyy ke timestamp untuk sorting
            const parts = data.split("-");
            if (parts.length === 3) {
              return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
            }
          }
          return data;
        },
      },
    ],

    // Default sorting: berdasarkan tanggal pengajuan terbaru
    order: [[5, "desc"]],

    // Language & Text
    language: {
      info: "Showing _START_ to _END_ of _TOTAL_ documents",
      infoEmpty: "No documents available",
      infoFiltered: "(filtered from _MAX_ total documents)",
      lengthMenu: "Show _MENU_ documents",
      search: "Search:",
      searchPlaceholder: "Search documents...",
      zeroRecords: "No matching documents found",
      emptyTable: "No documents available",
      paginate: {
        first: "First",
        last: "Last",
        next: '<i class="ion-chevron-right"></i>',
        previous: '<i class="ion-chevron-left"></i>',
      },
      loadingRecords: "Loading...",
      processing: "Processing...",
    },

    // Callback setelah tabel di-draw
    drawCallback: function (settings) {
      // Inisialisasi ulang dropdown Bootstrap
      $('[data-toggle="dropdown"]').dropdown();

      // Re-bind event handlers
      DocumentTable.bindViewButtons();
      DocumentTable.bindDeleteButtons();
    },
  };

  /**
   * Object untuk mengelola fungsi-fungsi tabel dokumen
   */
  const DocumentTable = {
    table: null,

    /**
     * Inisialisasi DataTable
     */
    init: function () {
      // Destroy existing DataTable if exists
      if ($.fn.DataTable.isDataTable(".data-table")) {
        $(".data-table").DataTable().destroy();
      }

      this.table = $(".data-table").DataTable(dataTableConfig);
      this.setupFilters();
      this.setupButtons();
      this.bindViewButtons();
      this.bindDeleteButtons();
      this.setupFormHandlers();
    },

    /**
     * Setup filter status
     */
    setupFilters: function () {
      const self = this;
      $("#statusFilter").on("change", function () {
        const selectedStatus = $(this).val();
        if (selectedStatus) {
          self.table.column(4).search(selectedStatus, true, false).draw();
        } else {
          self.table.column(4).search("").draw();
        }
      });
    },

    /**
     * Setup button handlers
     */
    setupButtons: function () {
      // Refresh Table Button
      $("#refreshTable").on("click", function () {
        if (typeof notyf !== "undefined") {
          notyf.open({
            type: "info",
            message: "Memuat ulang data...",
          });
        }
        setTimeout(() => {
          location.reload();
        }, 500);
      });

      // Export Excel Button
      $("#exportExcel").on("click", function () {
        window.location.href = "extension/export_documents.php";
      });
    },

    /**
     * Bind event handler untuk tombol View
     */
    bindViewButtons: function () {
      $(".btn-view")
        .off("click")
        .on("click", function (e) {
          e.preventDefault();
          const docId = $(this).data("id");
          DocumentTable.loadDocumentDetail(docId);
        });
    },

    /**
     * Bind event handler untuk tombol Delete
     */
    bindDeleteButtons: function () {
      $(".btn-delete")
        .off("click")
        .on("click", function (e) {
          e.preventDefault();
          const docId = $(this).data("id");
          const docNo = $(this).data("no");
          DocumentTable.deleteDocument(docId, docNo);
        });
    },

    /**
     * Load detail dokumen via AJAX
     */
    loadDocumentDetail: function (docId) {
      const modalViewContent = $("#modalViewContent");

      // Show loading
      modalViewContent.html(`
                <div class="text-center text-muted py-5">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-3">Memuat data dokumen...</p>
                </div>
            `);

      // Fetch data with archive=true to allow viewing all documents
      $.ajax({
        url: "content/doc-createget.php",
        method: "GET",
        data: { id: docId, archive: true },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            DocumentTable.renderDocumentDetail(response.data);
          } else {
            DocumentTable.showError(response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
          DocumentTable.showError("Gagal memuat data dokumen");
        },
      });
    },

    /**
     * Render detail dokumen ke modal
     */
    renderDocumentDetail: function (doc) {
      const modalViewContent = $("#modalViewContent");

      // ✅ Perbaikan parsing dan rendering lampiran
      let lampiranHtml = "";
      if (doc.lampiran_doc) {
        let lampiranArray = [];

        // Jika dari PHP masih berbentuk string JSON
        try {
          lampiranArray =
            typeof doc.lampiran_doc === "string"
              ? JSON.parse(doc.lampiran_doc)
              : doc.lampiran_doc;
        } catch (e) {
          console.error("Lampiran parse error:", e);
          lampiranArray = [];
        }

        // Pastikan hasilnya array
        if (Array.isArray(lampiranArray) && lampiranArray.length > 0) {
          lampiranHtml = `
      <label class="weight-600 m-3 h4">
        <i class="fas fa-paperclip"></i> Lampiran
      </label>
      <hr class="mt-0 mb-3 p-0" style="border-top: 1px solid #000;">
      <div class="m-3 d-flex flex-wrap gap-2">
        ${lampiranArray
          .map((file, index) => {
            // Pastikan path tidak diawali tanda kutip
            const cleanFile = file.replace(/^["']|["']$/g, "");
            return `
              <a href="${cleanFile}" target="_blank" class="btn btn-success">
                <i class="fas fa-eye"></i> Lihat Lampiran ${index + 1}
              </a>
            `;
          })
          .join("")}
      </div>
    `;
        } else {
          lampiranHtml = `
      <div class="m-3 text-muted">Tidak ada lampiran</div>
    `;
        }
      }

      const catatanHtml = doc.catatan_DH
        ? `
                <label class="weight-600 m-3 h4"><i class="fas fa-comment"></i> Catatan</label>
                <hr class="mt-0 mb-3 p-0" style="border-top: 1px solid #000;">
                <div class="m-3">
                    <div class="alert alert-info">${doc.catatan_DH}</div>
                </div>
            `
        : "";

      modalViewContent.html(`
                <div class="form-header m-3">
                    <div class="company-info m-2">
                        <table class="table pb-0 mb-0">
                            <tr><th>NO Dok</th><th>: ${doc.no_doc}</th></tr>
                            <tr><th>NO Rev</th><th>: 00</th></tr>
                            <tr><th>Tanggal</th><th>: ${doc.tgl_pengajuan_formatted}</th></tr>
                            <tr><th>Halaman</th><th>: 1 Dari 1</th></tr>
                        </table>
                    </div>

                    <label class="weight-600 m-3 h4"><i class="fas fa-file-alt"></i> Tipe Pengajuan</label>
                    <hr class="mt-0 mb-3 p-0" style="border-top: 1px solid #000;">
                    <div class="row m-1">
                        <input class="form-control m-3" value="${doc.tipe_doc}" disabled>
                    </div>

                    <label class="weight-600 m-3 h4"><i class="fas fa-user"></i> Data Pemohon</label>
                    <hr class="mt-0 mb-3 p-0" style="border-top: 1px solid #000;">
                    <div class="row m-1">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" class="form-control" value="${doc.pemohon_nama}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Departemen</label>
                                <input type="text" class="form-control" value="${doc.pemohon_dept}" disabled>    
                            </div>
                        </div>
                    </div>

                    <label class="weight-600 m-3 h4"><i class="fas fa-list"></i> Jenis Dokumen</label>
                    <hr class="mt-0 mb-3 p-0" style="border-top: 1px solid #000;">
                    <div class="row m-1">
                        <input class="form-control m-3" value="${doc.jenis_doc}" disabled>
                    </div>

                    <label class="weight-600 m-3 h4"><i class="fas fa-file-text"></i> Deskripsi</label>
                    <hr class="mt-0 mb-3 p-0" style="border-top: 1px solid #000;">
                    <div class="row m-1">
                        <textarea class="form-control m-3" rows="4" disabled>${doc.deskripsi_doc}</textarea>
                    </div>

                    ${lampiranHtml}

                    <label class="weight-600 m-3 h4"><i class="fas fa-info-circle"></i> Status</label>
                    <hr class="mt-0 mb-3 p-0" style="border-top: 1px solid #000;">
                    <div class="m-3">
                        <span class="alert alert-${doc.status_class} py-2 px-3 rounded-pill">${doc.status_text}</span>
                    </div>
                    
                    ${catatanHtml}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Tutup
                    </button>
                </div>
            `);
    },

    /**
     * Show error message di modal
     */
    showError: function (message) {
      const modalViewContent = $("#modalViewContent");
      modalViewContent.html(`
                <div class="alert alert-danger m-3">
                    <i class="fa fa-exclamation-triangle"></i> ${message}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            `);
    },

    /**
     * Delete dokumen via AJAX
     */
    deleteDocument: function (docId, docNo) {
      if (
        !confirm(
          `Apakah Anda yakin ingin menghapus dokumen ${docNo}?\n\nPeringatan: Tindakan ini tidak dapat dibatalkan!`
        )
      ) {
        return;
      }

      // Show loading notification
      if (typeof notyf !== "undefined") {
        notyf.open({
          type: "info",
          message: "Menghapus dokumen...",
        });
      }

      // Send delete request
      $.ajax({
        url: "delete_document.php",
        method: "POST",
        data: { id: docId },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            if (typeof notyf !== "undefined") {
              notyf.success(response.message);
            }
            // Refresh table after 1 second
            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            if (typeof notyf !== "undefined") {
              notyf.error(response.message);
            } else {
              alert(response.message);
            }
          }
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
          if (typeof notyf !== "undefined") {
            notyf.error("Terjadi kesalahan saat menghapus dokumen");
          } else {
            alert("Terjadi kesalahan saat menghapus dokumen");
          }
        },
      });
    },

    /**
     * Setup form handlers
     */
    setupFormHandlers: function () {
      // Toggle input "Lainnya"
      $('input[name="jenisdok"]').on("change", function () {
        const otherInput = $("#otherInput");
        const jenisLainnya = $("#jenisLainnya");

        if ($(this).val() === "lainnya") {
          otherInput.show();
          jenisLainnya.prop("required", true);
        } else {
          otherInput.hide();
          jenisLainnya.prop("required", false);
          jenisLainnya.val("");
        }
      });

      // Form validation
      const form = $("#formTambahPengajuan");
      if (form.length) {
        form.on("submit", function (e) {
          const tipedok = $('input[name="tipedok"]:checked');
          const jenisdok = $('input[name="jenisdok"]:checked');

          if (!tipedok.length) {
            e.preventDefault();
            if (typeof notyf !== "undefined") {
              notyf.error("Pilih tipe pengajuan terlebih dahulu!");
            } else {
              alert("Pilih tipe pengajuan terlebih dahulu!");
            }
            return false;
          }

          if (!jenisdok.length) {
            e.preventDefault();
            if (typeof notyf !== "undefined") {
              notyf.error("Pilih jenis dokumen terlebih dahulu!");
            } else {
              alert("Pilih jenis dokumen terlebih dahulu!");
            }
            return false;
          }

          // Validate file size
          const fileInput = $('input[name="lampiran"]')[0];
          if (fileInput && fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 2) {
              e.preventDefault();
              if (typeof notyf !== "undefined") {
                notyf.error("Ukuran file lampiran maksimal 2 MB!");
              } else {
                alert("Ukuran file lampiran maksimal 2 MB!");
              }
              return false;
            }
          }
        });
      }

      // Reset form when modal is closed
      $("#modalTambahPengajuan").on("hidden.bs.modal", function () {
        form[0].reset();
        $("#otherInput").hide();
        $("#jenisLainnya").prop("required", false);
      });

      // Set current date
      const currentDateElement = $("#currentDate");
      if (currentDateElement.length) {
        const today = new Date();
        currentDateElement.text(
          today.toLocaleDateString("id-ID", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
          })
        );
      }
    },
  };

  // Initialize DataTable
  DocumentTable.init();

  // Expose to global scope if needed
  window.DocumentTable = DocumentTable;
});

/**
 * Utility Functions
 */

// Format tanggal ke format Indonesia
function formatDateIndo(dateString) {
  const date = new Date(dateString);
  const options = { year: "numeric", month: "2-digit", day: "2-digit" };
  return date.toLocaleDateString("id-ID", options);
}

// Format file size
function formatFileSize(bytes) {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
}

// Escape HTML untuk mencegah XSS
function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text.replace(/[&<>"']/g, (m) => map[m]);
}
