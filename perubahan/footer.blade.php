<footer class="main-footer">
    <div class="pull-right hidden-xs" style="margin-left:25px">
        <b>Versi</b> {{ AmbilVersi() }}.mmd
        {{-- <b>Versi</b> 2406.mmd --}}
    </div>
    <strong>
        Aplikasi <a href="https://github.com/OpenSID/OpenSID" target="_blank">
            <?= config_item('nama_aplikasi') ?></a>, dikembangkan oleh <a href="https://www.facebook.com/groups/OpenSID/"
            target="_blank">Komunitas <?= config_item('nama_aplikasi') ?></a>. Pembaruan sistem oleh <a
            href="<?= route('profil_mmd') ?>">Tim MMD</a> dari <a href="https://filkom.ub.ac.id/" target="_blank"> FILKOM
            UB</a> tahun 2024.
    </strong>
</footer>
