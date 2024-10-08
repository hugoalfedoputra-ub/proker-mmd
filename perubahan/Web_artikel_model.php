<?php

/*
 *
 * File ini bagian dari:
 *
 * OpenSID
 *
 * Sistem informasi desa sumber terbuka untuk memajukan desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * @copyright Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 * @link      https://github.com/OpenSID/OpenSID
 *
 */

use App\Models\UserGrup;

defined('BASEPATH') || exit('No direct script access allowed');

class Web_artikel_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('agenda_model');
        $this->load->helper(array('form', 'url'));
    }

    public function autocomplete($cat)
    {
        $this->group_akses();

        if ($cari) {
            $this->db->like($kolom, $cari);
        }

        $data = $this->config_id_exist('artikel', 'a')
            ->distinct()
            ->select('a.judul')
            ->order_by('a.judul')
            ->where('a.id_kategori', $cat)
            ->get('artikel a')
            ->result_array();

        return autocomplete_data_ke_str($data);
    }

    private function search_sql()
    {
        $cari = $this->session->cari;

        if (isset($cari)) {
            $this->db->like('judul', $cari, 'BOTH')->or_like('isi', $cari, 'BOTH');
        }
    }

    private function filter_sql()
    {
        $status = $this->session->status;

        if (isset($status)) {
            $this->db->where('a.enabled', $status);
        }
    }

    // TODO : Gunakan $this->group_akses(); jika sudah menggunakan query builder
    private function grup_sql()
    {
        // Kontributor dan lainnya (group yg dibuat sendiri) hanya dapat melihat artikel yg dibuatnya sendiri
        if (!in_array($this->session->grup, UserGrup::getGrupSistem())) {
            $this->db->where('a.id_user', $this->session->user);
        }
    }

    public function paging($cat = 0, $p = 1, $o = 0)
    {
        $this->db->select('COUNT(a.id)');
        $this->list_data_sql($cat);
        $row      = $this->db->get()->row_array();
        $jml_data = $row['id'];

        $this->load->library('paging');
        $cfg['page']     = $p;
        $cfg['per_page'] = $_SESSION['per_page'];
        $cfg['num_rows'] = $jml_data;
        $this->paging->init($cfg);

        return $this->paging;
    }

    private function list_data_sql($cat)
    {
        $this->config_id('a')
            ->from('artikel a')
            ->join('kategori k', 'a.id_kategori = k.id', 'left');
        if ($cat > 0) {
            $this->db->where('id_kategori', $cat);
        } elseif ($cat == -1) {
            // Semua artikel dinamis (tidak termasuk artikel statis)
            $this->db->where_not_in('id_kategori', ['999', '1000', '1001']);
        } else {
            // Artikel dinamis tidak berkategori
            $this->db->where_not_in('id_kategori', ['999', '1000', '1001'])->where('k.id', null);
        }
        $this->search_sql();
        $this->filter_sql();
        $this->grup_sql();
    }

    public function list_data($cat = 0, $o = 0, $offset = 0, $limit = 500)
    {
        switch ($o) {
            case 1:
                $this->db->order_by('judul');
                break;

            case 2:
                $this->db->order_by('judul', 'DESC');
                break;

            case 3:
                $this->db->order_by('hit');
                break;

            case 4:
                $this->db->order_by('hit', 'DESC');
                break;

            case 5:
                $this->db->order_by('tgl_upload');
                break;

            case 6:
                $this->db->order_by('tgl_upload', 'DESC');
                break;

            default:
                $this->db->order_by('id', 'DESC');
        }

        $this->db->select('a.*, k.kategori AS kategori, YEAR(tgl_upload) as thn, MONTH(tgl_upload) as bln, DAY(tgl_upload) as hri');
        $this->db->limit($limit, $offset);
        $this->list_data_sql($cat);

        $data = $this->db->get()->result_array();

        $j = $offset;

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['no']         = $j + 1;
            $data[$i]['boleh_ubah'] = $this->boleh_ubah($data[$i]['id'], $this->session->user);
            $data[$i]['judul']      = e($data[$i]['judul']);
            $j++;
        }

        return $data;
    }

    // TODO: pindahkan dan gunakan web_kategori_model
    private function kategori($id)
    {
        return $this->config_id(null, true)
            ->where('parrent', $id)
            ->order_by('urut')
            ->get('kategori')
            ->result_array();
    }

    // TODO: pindahkan dan gunakan web_kategori_model
    public function list_kategori()
    {
        $data = $this->kategori(0);

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['submenu'] = $this->kategori($data[$i]['id']);
        }

        $data[] = [
            'id'       => '0',
            'kategori' => '[Tidak Berkategori]',
        ];

        return $data;
    }

    // TODO: pindahkan dan gunakan web_kategori_model
    public function get_kategori_artikel($id)
    {
        return $this->config_id()->select('id_kategori')->where('id', $id)->get('artikel')->row_array();
    }

    // TODO: pindahkan dan gunakan web_kategori_model
    public function get_kategori($cat = 0)
    {
        return $this->config_id()
            ->select('kategori')
            ->where('id', $cat)
            ->get('kategori')
            ->row_array();
    }

    // Revised function for specific purposes
    public function insert($cat = 1)
    {
        session_error_clear();
        $data = $this->input->post();
        $upload_path = FCPATH . LOKASI_UPLOAD . 'media/' . $data['folder_name'] . '/';

        if (!is_dir($upload_path)) {
            if (!mkdir($upload_path, 0777, true)) {
                $_SESSION['success'] = -1;
                $_SESSION['error_msg'] = "Direktori gagal dibuat";
                return;
            }
        }

        $config = array(
            'upload_path'   => $upload_path,
            'allowed_types' => "gif|jpg|png|jpeg",
            // 'overwrite'     => TRUE,
            'max_size'      => "65536",
            'max_height'    => "16384",
            'max_width'     => "16384"
        );

        $this->load->library('upload', $config);
        $uploaded_data = array();
        $upload_errors = array();

        foreach ($_FILES['gambar']['name'] as $key => $image) {
            // Generate a unique file name for each file
            $unique_filename = $image;

            $_FILES['uploaded_file']['name']     = $unique_filename;
            $_FILES['uploaded_file']['type']     = $_FILES['gambar']['type'][$key];
            $_FILES['uploaded_file']['tmp_name'] = $_FILES['gambar']['tmp_name'][$key];
            $_FILES['uploaded_file']['error']    = $_FILES['gambar']['error'][$key];
            $_FILES['uploaded_file']['size']     = $_FILES['gambar']['size'][$key];

            $this->upload->initialize($config);

            if ($this->upload->do_upload('uploaded_file')) {
                $uploaded_data[] = $this->upload->data();
                // log_message('error', print_r('uploading file...' . $uploaded_data, TRUE));
            } else {
                $upload_errors[] = $this->upload->display_errors();
            }
        }

        if (!empty($uploaded_data)) {
            // Takes first file that is uploaded, resizes it, then uploads it as article thumbnail
            $uploadedImage = $uploaded_data[0];
            ResizeGambar($uploadedImage['full_path'], LOKASI_FOTO_ARTIKEL . 'kecil_' . $uploadedImage['file_name'], ['width' => 440, 'height' => 440]);
            ResizeGambar($uploadedImage['full_path'], LOKASI_FOTO_ARTIKEL . 'sedang_' . $uploadedImage['file_name'], ['width' => 880, 'height' => 880]);

            // log_message('error', print_r($uploaded_data, TRUE));
            $base_url = sprintf(
                "%s://%s%s",
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                $_SERVER['SERVER_NAME'],
                '/'
            );

            // log_message('error', print_r($base_url . LOKASI_UPLOAD . 'media/' . $data['folder_name'] . '/', TRUE));
            // return;

            $result['judul'] = judul($data['judul']);
            $result['gambar'] = $uploaded_data[0]['file_name'];
            foreach ($uploaded_data as $udata) {
                $temp = $base_url . LOKASI_UPLOAD . 'media/' . $data['folder_name'] . '/' . $udata['file_name'];
                // log_message('error', print_r($temp, TRUE));
                $result['isi'] .= "<p><img src='" . $temp . "'/></p>";
            }
            // return;
            $result['isi'] .= $data['isi'];
            $result['id_kategori'] = $cat;
            $result['id_user']     = $_SESSION['user'];

            // Kontributor tidak dapat mengaktifkan artikel
            if ($_SESSION['grup'] == 4) {
                $result['enabled'] = 2;
            }

            // Upload dokumen lampiran
            $lokasi_file = $_FILES['dokumen']['tmp_name'];
            $tipe_file   = TipeFile($_FILES['dokumen']);
            $nama_file   = $_FILES['dokumen']['name'];
            $ext         = get_extension($nama_file);
            $nama_file   = str_replace(' ', '-', $nama_file); // normalkan nama file

            if ($nama_file && !empty($lokasi_file)) {
                if (!in_array($tipe_file, unserialize(MIME_TYPE_DOKUMEN), true) || !in_array($ext, unserialize(EXT_DOKUMEN))) {
                    unset($data['link_dokumen']);
                    $_SESSION['error_msg'] .= ' -> Jenis file salah: ' . $tipe_file;
                    $_SESSION['success'] = -1;
                } else {
                    $result['dokumen'] = $nama_file;
                    if ($result['link_dokumen'] == '') {
                        $result['link_dokumen'] = $result['judul'];
                    }
                    UploadDocument2($nama_file);
                }
            }

            if ($result['tgl_upload'] == '') {
                $result['tgl_upload'] = date('Y-m-d H:i:s');
            } else {
                $tempTgl            = date_create_from_format('d-m-Y H:i:s', $result['tgl_upload']);
                $result['tgl_upload'] = $tempTgl->format('Y-m-d H:i:s');
            }
            if ($result['tgl_agenda'] == '') {
                unset($result['tgl_agenda']);
            } else {
                $tempTgl            = date_create_from_format('d-m-Y H:i:s', $result['tgl_agenda']);
                $result['tgl_agenda'] = $tempTgl->format('Y-m-d H:i:s');
            }

            $result['slug']      = unique_slug('artikel', $result['judul']);
            $result['config_id'] = identitas('id');

            if ($cat == AGENDA) {
                $outp = $this->insert_agenda($result);
            } else {
                // Inserts new article to database
                $outp = $this->db->insert('artikel', $result);
            }
            status_sukses($outp);

            $_SESSION['success'] = 1;
        }

        if (!empty($upload_errors)) {
            // log_message('error', print_r($upload_errors, true));
            // log_message('error', print_r($uploaded_data, true));
            // Display error
            $_SESSION['success'] = -1;
            $_SESSION['error_msg'] .= 'Telah terjadi kegagalan';
            $_SESSION['error_msg'] .= $this->upload->display_errors();
        }

        if (!empty($upload_errors) && count($upload_errors) == count($_FILES['gambar'])) {
            // Delete the dir
            $this->load->helper('file');
            delete_files($upload_path, true);
            rmdir($upload_path);

            // Display error
            $_SESSION['success'] = -1;
            $_SESSION['error_msg'] .= 'Telah terjadi kegagalan';
            $_SESSION['error_msg'] .= $this->upload->display_errors();
        }
    }

    public function insert_old($cat = 1)
    {
        session_error_clear();
        $data = $this->input->post();
        if (empty($data['judul']) || empty($data['isi'])) {
            // $_SESSION['error_msg'] .= var_dump($data);
            $_SESSION['error_msg'] .= ' -> Data harus diisi';
            $_SESSION['success'] = -1;

            return;
        }

        $data = array(
            'status' => 'testing',
            'message' => 'Data retrieved successfully',
            'data' => $data
        );

        $json_data = json_encode($data);

        echo $json_data;

        // Batasi judul menggunakan teks polos
        $data['judul'] = judul($data['judul']);

        $fp          = time();
        $list_gambar = ['gambar', 'gambar1', 'gambar2', 'gambar3'];

        foreach ($list_gambar as $gambar) {
            $lokasi_file = $_FILES[$gambar]['tmp_name'];
            $nama_file   = $fp . '_' . $_FILES[$gambar]['name'];
            if (!empty($lokasi_file)) {
                $tipe_file = TipeFile($_FILES[$gambar]);
                $hasil     = UploadArtikel($nama_file, $gambar, $fp, $tipe_file);
                if ($hasil) {
                    $data[$gambar] = $nama_file;
                } else {
                    redirect('web');
                }
            }
        }
        $data['id_kategori'] = $cat;
        $data['id_user']     = $_SESSION['user'];

        // Kontributor tidak dapat mengaktifkan artikel
        if ($_SESSION['grup'] == 4) {
            $data['enabled'] = 2;
        }

        // Upload dokumen lampiran
        $lokasi_file = $_FILES['dokumen']['tmp_name'];
        $tipe_file   = TipeFile($_FILES['dokumen']);
        $nama_file   = $_FILES['dokumen']['name'];
        $ext         = get_extension($nama_file);
        $nama_file   = str_replace(' ', '-', $nama_file); // normalkan nama file

        if ($nama_file && !empty($lokasi_file)) {
            if (!in_array($tipe_file, unserialize(MIME_TYPE_DOKUMEN), true) || !in_array($ext, unserialize(EXT_DOKUMEN))) {
                unset($data['link_dokumen']);
                $_SESSION['error_msg'] .= ' -> Jenis file salah: ' . $tipe_file;
                $_SESSION['success'] = -1;
            } else {
                $data['dokumen'] = $nama_file;
                if ($data['link_dokumen'] == '') {
                    $data['link_dokumen'] = $data['judul'];
                }
                UploadDocument2($nama_file);
            }
        }

        foreach ($list_gambar as $gambar) {
            unset($data['old_' . $gambar]);
        }
        if ($data['tgl_upload'] == '') {
            $data['tgl_upload'] = date('Y-m-d H:i:s');
        } else {
            $tempTgl            = date_create_from_format('d-m-Y H:i:s', $data['tgl_upload']);
            $data['tgl_upload'] = $tempTgl->format('Y-m-d H:i:s');
        }
        if ($data['tgl_agenda'] == '') {
            unset($data['tgl_agenda']);
        } else {
            $tempTgl            = date_create_from_format('d-m-Y H:i:s', $data['tgl_agenda']);
            $data['tgl_agenda'] = $tempTgl->format('Y-m-d H:i:s');
        }

        $data['slug']      = unique_slug('artikel', $data['judul']);
        $data['config_id'] = identitas('id');

        if ($cat == AGENDA) {
            $outp = $this->insert_agenda($data);
        } else {
            $outp = $this->db->insert('artikel', $data);
        }
        status_sukses($outp);
    }

    private function ambil_data_agenda(&$data)
    {
        $agenda               = [];
        $agenda['tgl_agenda'] = $data['tgl_agenda'];
        unset($data['tgl_agenda']);
        $agenda['koordinator_kegiatan'] = $data['koordinator_kegiatan'];
        unset($data['koordinator_kegiatan']);
        $agenda['lokasi_kegiatan'] = $data['lokasi_kegiatan'];
        unset($data['lokasi_kegiatan']);

        return $agenda;
    }

    private function insert_agenda($data)
    {
        $agenda = $this->ambil_data_agenda($data);
        unset($data['id_agenda']);
        $outp = $this->db->insert('artikel', $data);
        if ($outp) {
            $insert_id            = $this->db->insert_id();
            $agenda['id_artikel'] = $insert_id;
            $this->agenda_model->insert($agenda);
        }

        return $outp;
    }

    public function update($cat, $id = 0)
    {
        session_error_clear();
        $data = $this->input->post();
        $upload_path = FCPATH . LOKASI_UPLOAD . 'media/' . $data['folder_name'] . '/';
        log_message('error', print_r($data, true));

        $hapus_lampiran = $data['hapus_lampiran'];
        unset($data['hapus_lampiran']);

        if (!is_dir($upload_path)) {
            if (!mkdir($upload_path, 0777, true)) {
                $_SESSION['success'] = -1;
                $_SESSION['error_msg'] = "Direktori gagal dibuat";
                return;
            }
        }

        /**
         * Hal-hal yang terjadi saat update artikel:
         * 1. Judul dan folder penyimpanan gambar tetap sama
         * 2. Tambahkan gambar baru apabila user mengunggah gambar baru
         * 3. Periksa apabila gambar sudah ada sebelumnya (untuk menghindari duplikasi)
         * 4. Tambahkan gambar yang baru itu ke isi artikel (append)
         */

        $config = array(
            'upload_path'   => $upload_path,
            'allowed_types' => "gif|jpg|png|jpeg",
            // 'overwrite'     => TRUE,
            'max_size'      => "65536",
            'max_height'    => "16384",
            'max_width'     => "16384"
        );

        $this->load->library('upload', $config);
        $uploaded_data = array();
        $upload_errors = array();

        if (!empty($data['files'])) {
            foreach ($_FILES['gambar']['name'] as $key => $image) {
                // Generate a unique file name for each file
                $unique_filename = $image;

                $_FILES['uploaded_file']['name']     = $unique_filename;
                $_FILES['uploaded_file']['type']     = $_FILES['gambar']['type'][$key];
                $_FILES['uploaded_file']['tmp_name'] = $_FILES['gambar']['tmp_name'][$key];
                $_FILES['uploaded_file']['error']    = $_FILES['gambar']['error'][$key];
                $_FILES['uploaded_file']['size']     = $_FILES['gambar']['size'][$key];

                $this->upload->initialize($config);

                if ($this->upload->do_upload('uploaded_file')) {
                    $uploaded_data[] = $this->upload->data();
                    // log_message('error', print_r('uploading file...' . $uploaded_data, TRUE));
                } else {
                    $upload_errors[] = $this->upload->display_errors();
                }
            }
        }

        // Update artikel tidak perlu membuat thumbnail lagi

        $base_url = sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            '/'
        );

        // Tidak perlu mengatur judul lagi

        // Teks sudah masuk di sini; teks akan muncul paling belakang
        $result['isi'] = $data['isi'];
        // Pengaturan upload update gambar
        // Apabila tidak ada gambar baru, maka tahap ini dilompati
        $temp_result = "";
        if (!empty($data['files']) && !empty($uploaded_data)) {
            foreach ($uploaded_data as $udata) {
                $temp = $base_url . LOKASI_UPLOAD . 'media/' . $data['folder_name'] . '/' . $udata['file_name'];
                $temp_result .= "<p><img src='" . $temp . "'/></p>";
            }
        }
        // Menambahkan gambar-gambar baru di paling atas (gambar terbaru muncul paling atas)
        $result['isi'] = $temp_result . $result['isi'];

        // log_message('error', print_r($data['isi'], true));

        // Kedua hal ini mungkin berubah
        $result['id_kategori'] = $cat;
        $result['id_user']     = $_SESSION['user'];

        // Kontributor tidak dapat mengaktifkan artikel
        if ($_SESSION['grup'] == 4) {
            $result['enabled'] = 2;
        }

        // Upload dokumen lampiran
        $lokasi_file = $_FILES['dokumen']['tmp_name'];
        $tipe_file   = TipeFile($_FILES['dokumen']);
        $nama_file   = $_FILES['dokumen']['name'];
        $ext         = get_extension($nama_file);
        $nama_file   = str_replace(' ', '-', $nama_file); // normalkan nama file

        if ($nama_file && !empty($lokasi_file)) {
            if (!in_array($tipe_file, unserialize(MIME_TYPE_DOKUMEN)) || !in_array($ext, unserialize(EXT_DOKUMEN))) {
                unset($data['link_dokumen']);
                $_SESSION['error_msg'] .= ' -> Jenis file salah: ' . $tipe_file;
                $_SESSION['success'] = -1;
            } else {
                $data['dokumen'] = $nama_file;
                if ($data['link_dokumen'] == '') {
                    $data['link_dokumen'] = $data['judul'];
                }
                UploadDocument2($nama_file);
            }
        }

        if ($data['tgl_upload'] == '') {
            $data['tgl_upload'] = date('Y-m-d H:i:s');
        } else {
            $tempTgl            = date_create_from_format('d-m-Y H:i:s', $data['tgl_upload']);
            $data['tgl_upload'] = $tempTgl->format('Y-m-d H:i:s');
        }
        if ($data['tgl_agenda'] == '') {
            unset($data['tgl_agenda']);
        } else {
            $tempTgl            = date_create_from_format('d-m-Y H:i:s', $data['tgl_agenda']);
            $data['tgl_agenda'] = $tempTgl->format('Y-m-d H:i:s');
        }

        $data['slug'] = unique_slug('artikel', $data['judul'], $id);

        $this->group_akses();

        if ($cat == AGENDA) {
            $outp = $this->update_agenda($id, $data);
        } else {
            // Updates article in database
            $outp = $this->config_id()->where('a.id', $id)->update('artikel a', $result);
        }

        if ($hapus_lampiran == 'true') {
            $this->config_id()->where('id', $id)->update('artikel', ['dokumen' => null, 'link_dokumen' => '']);
        }

        status_sukses($outp);

        if (!empty($upload_errors)) {
            // Display error
            $_SESSION['success'] = -1;
            $_SESSION['error_msg'] .= 'Telah terjadi kegagalan';
            $_SESSION['error_msg'] .= $this->upload->display_errors();
        }
    }

    public function update_old($cat, $id = 0)
    {
        session_error_clear();

        $data = $_POST;

        $hapus_lampiran = $data['hapus_lampiran'];
        unset($data['hapus_lampiran']);

        if (empty($data['judul']) || empty($data['isi'])) {
            $_SESSION['error_msg'] .= ' -> Data harus diisi';
            $_SESSION['success'] = -1;

            return;
        }

        // Batasi judul menggunakan teks polos
        $data['judul'] = judul($data['judul']);

        $fp          = time();
        $list_gambar = ['gambar', 'gambar1', 'gambar2', 'gambar3'];

        foreach ($list_gambar as $gambar) {
            $lokasi_file = $_FILES[$gambar]['tmp_name'];
            $nama_file   = $fp . '_' . $_FILES[$gambar]['name'];

            if (!empty($lokasi_file)) {
                $tipe_file = TipeFile($_FILES[$gambar]);
                $hasil     = UploadArtikel($nama_file, $gambar);
                if ($hasil) {
                    $data[$gambar] = $nama_file;
                    HapusArtikel($data['old_' . $gambar]);
                } else {
                    unset($data[$gambar]);
                }
            } else {
                unset($data[$gambar]);
            }
        }

        foreach ($list_gambar as $gambar) {
            if (isset($data[$gambar . '_hapus'])) {
                HapusArtikel($data[$gambar . '_hapus']);
                $data[$gambar] = '';
                unset($data[$gambar . '_hapus']);
            }
        }

        // Upload dokumen lampiran
        $lokasi_file = $_FILES['dokumen']['tmp_name'];
        $tipe_file   = TipeFile($_FILES['dokumen']);
        $nama_file   = $_FILES['dokumen']['name'];
        $ext         = get_extension($nama_file);
        $nama_file   = str_replace(' ', '-', $nama_file); // normalkan nama file

        if ($nama_file && !empty($lokasi_file)) {
            if (!in_array($tipe_file, unserialize(MIME_TYPE_DOKUMEN)) || !in_array($ext, unserialize(EXT_DOKUMEN))) {
                unset($data['link_dokumen']);
                $_SESSION['error_msg'] .= ' -> Jenis file salah: ' . $tipe_file;
                $_SESSION['success'] = -1;
            } else {
                $data['dokumen'] = $nama_file;
                if ($data['link_dokumen'] == '') {
                    $data['link_dokumen'] = $data['judul'];
                }
                UploadDocument2($nama_file);
            }
        }

        foreach ($list_gambar as $gambar) {
            unset($data['old_' . $gambar]);
        }
        if ($data['tgl_upload'] == '') {
            $data['tgl_upload'] = date('Y-m-d H:i:s');
        } else {
            $tempTgl            = date_create_from_format('d-m-Y H:i:s', $data['tgl_upload']);
            $data['tgl_upload'] = $tempTgl->format('Y-m-d H:i:s');
        }
        if ($data['tgl_agenda'] == '') {
            unset($data['tgl_agenda']);
        } else {
            $tempTgl            = date_create_from_format('d-m-Y H:i:s', $data['tgl_agenda']);
            $data['tgl_agenda'] = $tempTgl->format('Y-m-d H:i:s');
        }

        $data['slug'] = unique_slug('artikel', $data['judul'], $id);

        $this->group_akses();

        if ($cat == AGENDA) {
            $outp = $this->update_agenda($id, $data);
        } else {
            $outp = $this->config_id()->where('a.id', $id)->update('artikel a', $data);
        }

        if ($hapus_lampiran == 'true') {
            $this->config_id()->where('id', $id)->update('artikel', ['dokumen' => null, 'link_dokumen' => '']);
        }

        status_sukses($outp);
    }

    private function update_agenda($id_artikel, $data)
    {
        $agenda = $this->ambil_data_agenda($data);
        $id     = $data['id_agenda'];
        unset($data['id_agenda']);
        $outp = $this->config_id()->where('a.id', $id_artikel)->update('artikel a', $data);
        if ($outp) {
            if (empty($id)) {
                $agenda['id_artikel'] = $id_artikel;
                $this->agenda_model->insert($agenda);
            } else {
                $this->agenda_model->update($id, $agenda);
            }
        }

        return $outp;
    }

    public function update_kategori($id, $id_kategori)
    {
        $this->config_id()->where('id', $id)->update('artikel', ['id_kategori' => $id_kategori]);
    }

    public function delete($id = 0, $semua = false)
    {
        if (!$semua) {
            $this->session->success = 1;
        }

        $this->group_akses();

        $list_gambar = $this->config_id()
            ->select('a.gambar, a.gambar1, a.gambar2, a.gambar3')
            ->from('artikel a')
            ->where('a.id', $id)
            ->get()
            ->row_array();

        if ($list_gambar) {
            foreach ($list_gambar as $key => $gambar) {
                HapusArtikel($gambar);
            }
        }

        if (!in_array($this->session->grup, UserGrup::getGrupSistem())) {
            $this->db->where('id_user', $this->session->user);
        }

        $this->config_id()->from('artikel')->where('id', $id)->delete();
        $outp = $this->db->affected_rows();

        status_sukses($outp, $gagal_saja = true); //Tampilkan Pesan
    }

    public function delete_all()
    {
        $this->session->success = 1;

        $id_cb = $this->input->post('id_cb');

        foreach ($id_cb as $id) {
            if ($this->boleh_ubah($id, $this->session->user)) {
                $this->delete($id, true);
            }
        }
    }

    // TODO: pindahkan dan gunakan web_kategori_model
    public function hapus($id = 0, $semua = false)
    {
        if (!$semua) {
            $this->session->success = 1;
        }
        $outp = $this->config_id()->where('id', $id)->delete('kategori');

        status_sukses($outp, $gagal_saja = true); //Tampilkan Pesan
    }

    public function artikel_lock($id = 0, $val = 1)
    {
        $this->group_akses();

        $outp = $this->config_id()->where('id', $id)->update('artikel a', ['a.enabled' => $val]);

        status_sukses($outp); //Tampilkan Pesan
    }

    public function komentar_lock($id = 0, $val = 1)
    {
        $outp = $this->config_id()->where('id', $id)->update('artikel', ['boleh_komentar' => $val]);

        status_sukses($outp); //Tampilkan Pesan
    }

    public function get_artikel($id = 0)
    {
        $this->group_akses();

        $data = $this->config_id('a')
            ->select('a.*, g.*, g.id as id_agenda, u.nama AS owner')
            ->select('YEAR(tgl_upload) as thn, MONTH(tgl_upload) as bln, DAY(tgl_upload) as hri')
            ->from('artikel a')
            ->join('user u', 'a.id_user = u.id', 'LEFT')
            ->join('agenda g', 'g.id_artikel = a.id', 'LEFT')
            ->where('a.id', $id)
            ->get()
            ->row_array();

        // Jika artikel tdk ditemukan
        if (!$data) {
            return false;
        }

        $data['judul'] = e($data['judul']);

        // Digunakan untuk timepicker
        $tempTgl            = date_create_from_format('Y-m-d H:i:s', $data['tgl_upload']);
        $data['tgl_upload'] = $tempTgl->format('d-m-Y H:i:s');
        // Data artikel terkait agenda
        if (!empty($data['tgl_agenda'])) {
            $tempTgl            = date_create_from_format('Y-m-d H:i:s', $data['tgl_agenda']);
            $data['tgl_agenda'] = $tempTgl->format('d-m-Y H:i:s');
        } else {
            $data['tgl_agenda'] = date('d-m-Y H:i:s');
        }

        return $data;
    }

    public function get_headline()
    {
        $data = $this->config_id('a')
            ->select('a.*, u.nama AS owner')
            ->from('artikel a')
            ->join('user u', 'a.id_user = u.id', 'LEFT')
            ->where('headline', 1)
            ->order_by('tgl_upload', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($data)) {
            $data = null;
        } else {
            $id          = $data['id'];
            $panjang     = str_split($data['isi'], 300);
            $data['isi'] = '<label>' . $panjang[0] . "...</label><a href='" . site_url("artikel/{$id}") . "'>Baca Selengkapnya</a>";
        }

        return $data;
    }

    // TODO: pindahkan dan gunakan web_kategori_model
    public function insert_kategori()
    {
        $data['kategori']  = $_POST['kategori'];
        $data['tipe']      = '2';
        $data['config_id'] = $this->config_id;

        $outp = $this->db->insert('kategori', $data);

        status_sukses($outp); //Tampilkan Pesan
    }

    public function list_komentar($id = 0)
    {
        return $this->config_id()
            ->where('id_artikel', $id)
            ->order_by('tgl_upload', 'DESC')
            ->get('komentar')
            ->result_array();
    }

    public function headline($id = 0)
    {
        $outp = $this->config_id()->update('artikel', ['headline' => 0]);
        $outp = $this->config_id()->where('id', $id)->update('artikel', ['headline' => 1]);

        status_sukses($outp); //Tampilkan Pesan
    }

    public function slide($id = 0)
    {
        $data = $this->config_id()->get_where('artikel', ['id' => $id])->row_array();

        if ($data['slider'] == '1') {
            $slider = 0;
        } else {
            $slider = 1;
        }

        $outp = $this->config_id()->where('id', $id)->update('artikel', ['slider' => $slider]);

        status_sukses($outp); //Tampilkan Pesan
    }

    public function boleh_ubah($id, $user)
    {
        // Kontributor hanya boleh mengubah artikel yg ditulisnya sendiri
        $id_user = $this->config_id()->select('id_user')->where('id', $id)->get('artikel')->row()->id_user;

        return $user == $id_user || $this->session->grup != 4;
    }

    public function reset($cat)
    {
        // Normalkan kembali hit artikel kategori 999 (yg ditampilkan di menu) akibat robot (crawler)
        $persen    = $this->input->post('hit');
        $list_menu = $this->config_id()
            ->distinct()
            ->select('link')
            ->like('link', 'artikel/')
            ->where('enabled', 1)
            ->get('menu')
            ->result_array();

        foreach ($list_menu as $list) {
            $id      = str_replace('artikel/', '', $list['link']);
            $artikel = $this->config_id()->where('id', $id)->get('artikel')->row_array();
            $hit     = $artikel['hit'] * ($persen / 100);
            if ($artikel) {
                $this->config_id()->where('id', $id)->update('artikel', ['hit' => $hit]);
            }
        }
    }

    public function list_artikel_statis()
    {
        // '999' adalah id_kategori untuk artikel statis
        $this->group_akses();

        return $this->config_id()
            ->select('a.id, judul')
            ->where('a.id_kategori', '999')
            ->get('artikel a')
            ->result_array();
    }

    private function group_akses()
    {
        // Kontributor dan lainnya (group yg dibuat sendiri) hanya dapat melihat artikel yg dibuatnya sendiri
        if (!in_array($this->session->grup, UserGrup::getGrupSistem())) {
            $this->db->where('a.id_user', $this->session->user);
        }
    }
}
