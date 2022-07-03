var datatable = $('#dataTable').DataTable({ 
    initComplete: function() {
        var api = this.api();
        $('#mytable_filter input')
            .off('.DT')
            .on('input.DT', function() {
                api.search(this.value).draw();
        });
    },
    oLanguage: {
    sProcessing: "loading..."
    },
    processing: true,
    serverSide: true,
    ajax: {"url": url_base+"tes/loadTes", "type": "POST"},
    columns: [
        {"data": "nama_tes"},
        {"data": "nama_tes", render : function(data, row, iDisplayIndex){
            return `
                <a href='#uploadGambar' class='uploadGambar' data-bs-toggle='modal' data-id='`+iDisplayIndex.id_tes+`'><span class='avatar avatar-sm' style='background-image: url(`+url_base+`assets/logo/`+iDisplayIndex.id_tes+`.png?t=`+Math.random()+`)'></span></a>`
        }},
        {"data": "tgl_tes"},
        // {"data": "tgl_pengumuman"},
        {"data": "nama_soal", render : function(data, row, iDisplayIndex) {
            return data+" ("+iDisplayIndex.soal+")";
        }},
        {"data": "peserta", render : function (data, row, iDisplayIndex) {
            if(jQuery.browser.mobile == true) return data+`/`+iDisplayIndex.sertifikat
            else return "<center>"+data+"/"+iDisplayIndex.sertifikat+"</center>"
        }},
        // {"data": "sertifikat"},
        {"data": "status", render : function(data, row, iDisplayIndex){
            (data == 'Berjalan' ? status = "checked" : status = "");
            
            if(jQuery.browser.mobile == true) 
                return `<label class="form-switch">
                    <input class="form-check-input" type="checkbox" name="id_tes" value="`+iDisplayIndex.id_tes+`" `+status+`>
                </label>`
            else 
                return `<center>
                    <label class="form-switch">
                        <input class="form-check-input" type="checkbox" name="id_tes" value="`+iDisplayIndex.id_tes+`" `+status+`>
                    </label>
                </center>`
        }},
        {"data": "action", render : function (data) {
            if(jQuery.browser.mobile == true) return data
            else return "<center>"+data+"</center>"
        }},
        {"data": "password", "className": "none text-wrap"},
        {"data": "link", "className": "none text-wrap"},
        {"data": "catatan", "className": "none text-wrap"},
    ],
    order: [[2, 'desc']],
    rowCallback: function(row, data, iDisplayIndex) {
        var info = this.fnPagingInfo();
        var page = info.iPage;
        var length = info.iLength;
        $('td:eq(0)', row).html();
    },
    "columnDefs": [
    { "searchable": false, "targets": "" },  // Disable search on first and last columns
    { "targets": [4, 5, 6], "orderable": false},
    ],
    "rowReorder": {
        "selector": 'td:nth-child(0)'
    },
    "responsive": true
});