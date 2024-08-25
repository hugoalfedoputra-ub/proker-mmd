<?php
$folder_name = str_replace(' ', '_', $artikel['judul']);
?>

<div class="content-wrapper">
    <section class="content-header">
        <?php
        if (!isset($artikel['judul'])) : ?>
            <h1>Tambah Artikel Baru</h1>
        <?php else : ?>
            <h1>Update Artikel</h1>
        <?php endif; ?>
        <ol class="breadcrumb">
            <li><a href="<?= site_url('beranda') ?>"><i class="fa fa-home"></i> Beranda</a></li>
            <li><a href="<?= site_url('web') ?>"> Daftar Artikel</a></li>
            <?php
            if (!isset($artikel['judul'])) : ?>
                <li class="active">Tambah Artikel Baru</li>
            <?php else : ?>
                <li class="active">Update Artikel</li>
            <?php endif; ?>
        </ol>
    </section>
    <br>
    <div class="">
        <div class="col-lg-6 col-sm-12 col-xs-12">
            <a href="<?= site_url('web') ?>" class="small-box bg-blue">
                <div class="inner">
                    <br>
                    <h3>Kembali ke Daftar Artikel</h3><br><br>
                </div>
                <div class="icon" style="margin-top:15px;margin-right:75px">
                    <i class="fa fa-arrow-left"></i>
                </div>
            </a>
        </div>
        <div class="col-lg-6 col-sm-12 col-xs-12">
            <a href="<?= site_url('Beranda') ?>" class="small-box bg-purple">
                <div class="inner">
                    <br>
                    <h3>Kembali ke Beranda</h3><br><br>
                </div>
                <div class="icon" style="margin-top:15px;margin-right:75px">
                    <i class="fa fa-home"></i>
                </div>
            </a>
        </div>
    </div>
    <section class="content" id="maincontent">
        <form id="" action="<?= $form_action ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="control-label" for="judul" style="display:flex; align-items:baseline;">
                    <h3>Judul Artikel</h3>
                    <div style="width: 8px;"></div>
                    <code>(Wajib)</code>
                </label>
                <?php
                if (!isset($artikel['judul'])) : ?>
                    <input id="judul" name="judul" class="form-control input-xlg required strip_tags judul" type="text" placeholder="Tulis judul artikel Anda di sini..." minlength="5" maxlength="200" required></input>
                <?php else : ?>
                    <input id="judul" name="judul" class="form-control input-xlg required strip_tags judul" type="text" value="<?= $artikel['judul'] ?>" readonly minlength="5" maxlength="200" required></input>
                <?php endif; ?>
                <span class="help-block"><code>Judul artikel minimal 5 karakter dan maksimal 200 karakter</code></span>
            </div>

            <label class="control-label" style="display:flex; align-items:baseline;">
                <h3>Unggah Gambar</h3>
                <div style="width: 8px;"></div>
                <code>(Wajib)</code>
            </label>
            <?php
            if (!isset($artikel['judul'])) : ?>
                <p>Anda dapat memilih <b>satu atau lebih gambar</b> (png/jpg/jpeg) dari perangkat Anda.</p>
            <?php else : ?>
                <p>Anda dapat memilih <b>satu atau lebih gambar</b> (png/jpg/jpeg) dari perangkat Anda untuk ditambahkan ke artikel ini.</p>
            <?php endif; ?>
            <div class="input-group input-group-xlg">
                <input type="text" class="form-control" id="file_path" name="files" readonly>
                <span class="input-group-btn">
                    <?php
                    if (!isset($artikel['judul'])) : ?>
                        <input type="file" style="display: none;" accept="image/png, image/jpg, image/jpeg" id="file-gambar-final" name="gambar[]" hidden multiple required>
                        <input type="file" style="display: none;" accept="image/png, image/jpg, image/jpeg" id="upload-file-gambar" name="temp[]" multiple required>
                    <?php else : ?>
                        <!-- Update artikel mungkin saja tidak menambahkan gambar(-gambar) baru -->
                        <input type="file" style="display: none;" accept="image/png, image/jpg, image/jpeg" id="file-gambar-final" name="gambar[]" hidden multiple>
                        <input type="file" style="display: none;" accept="image/png, image/jpg, image/jpeg" id="upload-file-gambar" name="temp[]" multiple>
                    <?php endif; ?>
                    <label for="upload-file-gambar" class="btn btn-info btn-flat"><i class="fa fa-search"></i> Cari dari
                        Perangkat Anda</label>
                </span>
            </div>

            <br>
            <h3>Daftar Gambar</h3>
            <p>Gambar(-gambar) yang Anda tambahkan akan muncul di bawah ini.</p>
            <div id="preview-gambar" style="width: dvw; display: flex; flex-direction: column; row-gap: 1rem; margin-top: 10px;"></div>

            <br>
            <p>Gambar(-gambar) tersebut akan disimpan di dalam folder:</p>
            <?php
            if (!isset($artikel['judul'])) : ?>
                <input id="folder-name" name="folder_name" type="text" value="" readonly required maxlength="200" class="form-control input-sm">
            <?php else : ?>
                <input id="folder-name" name="folder_name" type="text" value="<?= $folder_name ?>" readonly required maxlength="200" class="form-control input-sm">
            <?php endif; ?>
            <span class="help-block"><code>Gambar(-gambar) yang diunggah menggunakan cara di atas <b>selalu muncul
                        pertama kali pada artikel</b>. Apabila ingin menambahkan gambar sesuai posisi pada teks, silakan
                    gunakan editor teks di bawah.</code></span>

            <div class="form-group">
                <label class="control-label" for="kode_desa">
                    <h3>Isi Artikel</h3>
                </label>
                <?php
                if (isset($artikel['judul'])) : ?>
                    <p>Anda dapat menghapus gambar(-gambar) pada bagian ini dengan cara menekan gambar lalu menekan "Delete" pada keyboard.</p>
                <?php endif; ?>
                <textarea name="isi" data-filemanager='<?= json_encode(['external_filemanager_path' => base_url('assets/kelola_file/'), 'filemanager_title' => 'Responsive Filemanager', 'filemanager_access_key' => $this->session->fm_key]) ?>' class="form-control input-sm required" style="height:100px;"><?= $artikel['isi'] ?></textarea>
            </div>
            <?php if ($cat == 1000) : ?>
                <input type="hidden" name="id_agenda" value="<?= $artikel['id_agenda'] ?>">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Pengaturan Agenda Desa</h3>
                        <div class="box-tools">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label" for="tgl_agenda">Tanggal Kegiatan</label>
                                <div class="input-group input-group-sm date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar-check-o"></i>
                                    </div>
                                    <input class="form-control input-sm pull-right tgl_jam" name="tgl_agenda" type="text" value="<?= $artikel['tgl_agenda'] ?>">
                                </div>
                                <span class="help-block"><code>(Isikan Tanggal Kegiatan)</code></span>
                                <label class="control-label" for="lokasi_kegiatan">Lokasi Kegiatan</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-addon">
                                        <i class="fa fa-map-marker"></i>
                                    </div>
                                    <input class="form-control input-sm pull-right" name="lokasi_kegiatan" type="text" placeholder="Masukan lokasi tempat dilakukan kegiatan" value="<?= $artikel['lokasi_kegiatan'] ?>">
                                </div>
                                <span class="help-block"><code>(Isikan Lokasi Tempat Dilakukan Kegiatan)</code></span>
                                <label class="control-label" for="koordinator_kegiatan">Koordinator Kegiatan</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <input class="form-control input-sm pull-right" name="koordinator_kegiatan" type="text" placeholder="Masukan nama koordinator" value="<?= $artikel['koordinator_kegiatan'] ?>">
                                </div>
                                <span class="help-block"><code>(Isikan Koordinator Kegiatan)</code></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Pengaturan Lainnya</h3>
                    <div class="box-tools">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <div class="col-sm-12">
                        <?php if ($artikel['dokumen']) : ?>
                            <div class="form-group">
                                <div class="mailbox-attachment-info bg-black">
                                    <a href="<?= base_url(LOKASI_DOKUMEN . $artikel['dokumen']) ?>" class="mailbox-attachment-name"><i class="fa fa-paperclip"></i> Unduh Dokumen</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="control-label" for="dokumen">Dokumen Lampiran</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="file_path4">
                                <input type="file" class="hidden" id="file4" name="dokumen">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info btn-flat" id="file_browser4"><i class="fa fa-search"></i></button>
                                    <button type='button' class='btn btn-info btn-flat btn-danger' id="hapus_file"><i class='fa fa-stop'></i></button>
                                    <?php if ($artikel) : ?>
                                        <input type="text" hidden="" name="hapus_lampiran" value="" id="hapus_lampiran" />
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="nama_dokumen">Nama Dokumen</label>
                            <input id="link_dokumen" name="link_dokumen" class="form-control input-sm" type="text" value="<?= $artikel['link_dokumen'] ?>"></input>
                            <span class="help-block"><code>(Nantinya akan menjadi link unduh/download)</code></span>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="tgl_upload">Tanggal Posting</label>
                            <div class="input-group input-group-sm date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input class="form-control input-sm pull-right tgl_jam" name="tgl_upload" type="text" value="<?= $artikel['tgl_upload'] ?>">
                            </div>
                            <span class="help-block"><code>(Kosongkan jika ingin langsung di post, bisa digunakan untuk
                                    artikel terjadwal)</code></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class='box-footer'>
                <button type='reset' onclick='location.reload()' class='btn btn-social btn-flat btn-danger btn-xlg'><i class='fa fa-times'></i> Batal</button>
                <button type='submit' class='btn btn-social btn-flat btn-info btn-xlg pull-right'><i class='fa fa-check'></i> Simpan</button>
            </div>
        </form>
    </section>
</div>
<script type="text/javascript">
    const elJudul = document.getElementById('judul')
    const fileInput = document.getElementById("upload-file-gambar");
    const fileResult = document.getElementById("file-gambar-final");
    const filePathInput = document.getElementById("file_path");
    const imagePreview = document.getElementById('preview-gambar')
    const fileMap = new Map()
    var folderName = ""
    var fileList = []

    elJudul.addEventListener('input', () => {
        folderName = elJudul.value.toLowerCase().replaceAll(" ", "_")
        document.getElementById('folder-name').value = folderName
    })

    fileInput.addEventListener('change', (event) => {
        const eventFile = event.target.files;
        // console.log('debug', eventFile[0].name);
        // console.log('debug', fileList.indexOf(eventFile[0].name));
        for (let i = 0; i < eventFile.length; i++) {
            if (fileList.length == 0) {
                fileList.push(eventFile[i])
            }

            var idxExist = 0
            for (let j = 0; j < fileList.length; j++) {
                if (eventFile[i].name == fileList[j].name) {
                    idxExist = 1
                    break;
                } else {
                    idxExist = -1
                }
            }

            // console.log('loop', eventFile[i].name, idxExist);
            if (idxExist == -1) {
                fileList.push(eventFile[i])
            }
        }
        // console.log('add', fileList);
        // console.log(fileInput.files);

        for (let i = 0; i < fileList.length; i++) {
            let fileName = fileList[i].name

            if (!fileMap.has(fileName)) {
                fileMap.set(fileName, fileList[i])
                const wrapper = document.createElement('div')
                const imgWrapper = document.createElement('div')
                const actionDiv = document.createElement('div')
                const actionButton = document.createElement('button')
                const icon = document.createElement('i')
                const img = document.createElement('img')
                const imgName = document.createElement('p')

                wrapper.style.display = 'flex'
                wrapper.style.flexDirection = 'row'
                wrapper.style.width = '100%'

                imgWrapper.style.backgroundColor = '#FFF'
                imgWrapper.style.width = '100%'

                actionDiv.style.width = 'fit-content'
                actionDiv.style.backgroundColor = '#FFF'

                actionButton.type = 'button'
                actionButton.classList.add('btn', 'btn-social', 'btn-flat', 'btn-danger', 'btn-xlg')
                actionButton.textContent = 'Hapus Gambar'
                actionButton.setAttribute('onclick', 'removePreviewImage(\'' + fileName + '\',' + i + ')')

                icon.classList.add('fa', 'fa-times')

                img.src = URL.createObjectURL(fileList[i])
                img.style.width = '50%'

                imgName.textContent = fileName

                actionButton.appendChild(icon)
                actionDiv.appendChild(actionButton)
                imgWrapper.appendChild(imgName)
                imgWrapper.appendChild(img)
                wrapper.appendChild(imgWrapper)
                wrapper.appendChild(actionDiv)
                imagePreview.appendChild(wrapper)
            }
        }

        // This displays the readonly file paths input
        filePathInput.value = '';
        let filePaths = []
        fileMap.forEach((value, key) => {
            // console.log(key, value);
            filePaths.push(key)
        })
        filePathInput.value = filePaths.join(", ");

        // This reindexes the onclick function param
        const theParent = document.getElementById('preview-gambar')
        var counter = 0
        theParent.childNodes.forEach((child) => {
            // console.log(child.childNodes[1].childNodes[0]);
            child.childNodes[1].childNodes[0].setAttribute('onclick', 'removePreviewImage(\'' +
                filePaths[counter] + '\',' + counter + ')')
            counter++
        })

        rebuildFileList(fileList)
        // console.log('add to final', fileResult.files);
    });

    const removePreviewImage = (filename, idx) => {
        // These remove the image file from image input array
        fileMap.delete(filename)
        fileList.splice(idx, 1);
        rebuildFileList(fileList)
        // console.log('delete', fileList);
        // console.log('update to final', fileResult.files);

        // This removes the temporary readonly filename input
        filePathInput.value = '';
        let filePaths = []
        fileMap.forEach((value, key) => {
            filePaths.push(key)
        })
        filePathInput.value = filePaths.join(", ");

        // This removes it from the image preview list
        const theParent = document.getElementById('preview-gambar')
        const toBeRemoved = theParent.childNodes[idx]
        theParent.removeChild(toBeRemoved)

        // This reindexes the onclick function param
        var counter = 0
        theParent.childNodes.forEach((child) => {
            // console.log(child.childNodes[1].childNodes[0]);
            child.childNodes[1].childNodes[0].setAttribute('onclick', 'removePreviewImage(\'' + filePaths[
                counter] + '\',' + counter + ')')
            counter++
        })
    }

    const rebuildFileList = (fileList) => {
        const dt = new DataTransfer();

        for (let i = 0; i < fileList.length; i++) {
            dt.items.add(fileList[i]);
        }

        // Assign the updated FileList to the fileResult element to be posted
        fileResult.files = dt.files;

        // Empty fileList and fileInput (temp[])
        while (fileList.length > 0) {
            fileList.pop();
        }
        fileInput.files = [];
    }
</script>
<script type="text/javascript" src="<?= asset('js/tinymce-651/tinymce.min.js') ?>"></script>
<script type="text/javascript">
    tinymce.init({
        selector: 'textarea',
        height: 700,
        promotion: false,
        theme: 'silver',
        formats: {
            menjorok: {
                block: 'p',
                styles: {
                    'text-indent': '30px'
                }
            }
        },
        block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3; Header 4=h4; Header 5=h5; Header 6=h6; Div=div; Preformatted=pre; Blockquote=blockquote; Menjorok=menjorok',
        style_formats_merge: true,
        plugins: [
            'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'print', 'preview', 'hr', 'anchor',
            'pagebreak',
            'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'insertdatetime', 'media',
            'nonbreaking',
            'table', 'contextmenu', 'directionality', 'emoticons', 'paste', 'textcolor',
            'responsivefilemanager', 'code', 'laporan_keuangan', 'penerima_bantuan', 'sotk'
        ],
        toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blocks",
        toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor | print preview code | fontfamily fontsizeinput",
        toolbar3: "| laporan_keuangan | penerima_bantuan | sotk",
        image_advtab: true,
        external_plugins: {
            "filemanager": "<?= asset('kelola_file/plugin.min.js') ?>"
        },
        templates: [{
                title: 'Test template 1',
                content: 'Test 1'
            },
            {
                title: 'Test template 2',
                content: 'Test 2'
            }
        ],
        content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            '//www.tinymce.com/css/codepen.min.css'
        ],
        skin: 'tinymce-5',
        relative_urls: false,
        remove_script_host: false
    });
</script>