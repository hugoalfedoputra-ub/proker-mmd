@extends('admin.layouts.index')

@push('css')
    <style>
        .catatan-scroll {
            height: 400px;
            overflow-y: scroll;
        }

        @media (max-width: 576px) {
            .komunikasi-opendk {
                display: none !important;
            }
        }
    </style>
@endpush

@section('title')
    <h1>
        Tentang <?= ucwords(setting('sebutan_desa') . ' ' . $desa['nama_desa']) ?>
    </h1>
@endsection

@section('breadcrumb')
    <li class="active">Tentang <?= ucwords(setting('sebutan_desa') . ' ' . $desa['nama_desa']) ?></li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')

    @include('admin.home.saas')

    @include('admin.home.premium')

    @include('admin.home.rilis')

    @include('admin.home.bantuan')

    <div class="row">
        @if (can('b', 'admin-web'))
            <div class="col-lg-6 col-sm-12 col-xs-12">
                <a href="{{ route('web.form') }}" class="small-box bg-green">
                    <div class="inner" style="height:125px">
                        <br>
                        <h3>Tambah Artikel<br></h3><br><br>
                    </div>
                    <div class="icon">
                        <i class="ion-ios-paper"></i>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-sm-12 col-xs-12">
                <a href="#" class="small-box bg-blue">
                    <div class="inner" style="height:125px">
                        <br>
                        <h3>Panduan Video untuk<br>Menambah Artikel</h3><br>
                    </div>
                    <div class="icon">
                        <i class="ion-ios-play"></i>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-sm-12 col-xs-12">
                <a href="{{ route('komentar') }}" class="small-box bg-yellow">
                    <div class="inner" style="height:125px">
                        <br>
                        <h3>Pengaturan Komentar<br></h3><br><br>
                    </div>
                    <div class="icon" style="padding-right:75px">
                        <i class="fa fa-comment"></i>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-sm-12 col-xs-12">
                <a href="#" class="small-box bg-blue">
                    <div class="inner" style="height:125px">
                        <br>
                        <h3>Panduan Video Tentang<br>Pengaturan Komentar</h3><br>
                    </div>
                    <div class="icon">
                        <i class="ion-ios-play"></i>
                    </div>
                </a>
            </div>
        @endif

        @php
            // Satuan berupa '' berarti tidak memiliki satuan
            $satuan = ['', 'ha', 'jiwa', 'jiwa/sqkm', 'pria/100 wanita'];
            $luas_wilayah = 41283.59;
            $penduduk = 5296;
            $dusun = 4;
            $rt = 28;
            $rw = 8;
            $kk = 1818;
            $density = floatval($penduduk) / (floatval($luas_wilayah) / 100.0);
            $laki = 2631;
            $puan = 2665;
            $ratio_l_p = (floatval($laki) / floatval($puan)) * 100.0;
        @endphp

        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($luas_wilayah, 2, ',', '.') }}</h3>
                        <p>{{ $satuan[1] }}</p>
                    </span>
                    <p>Luas Wilayah Desa</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($penduduk, 0, ',', '.') }}</h3>
                        <p>{{ $satuan[2] }}</p>
                    </span>
                    <p>Penduduk</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($dusun, 0, ',', '.') }}</h3>
                        <p>{{ $satuan[0] }}</p>
                    </span>
                    <p>Dusun</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($rw, 0, ',', '.') }}</h3>
                        <p>{{ $satuan[0] }}</p>
                    </span>
                    <p>RW</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($rt, 0, ',', '.') }}</h3>
                        <p>{{ $satuan[0] }}</p>
                    </span>
                    <p>RT</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($kk, 0, ',', '.') }}</h3>
                        <p>{{ $satuan[0] }}</p>
                    </span>
                    <p>KK</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($density, 2, ',', '.') }}</h3>
                        <p>{{ $satuan[3] }}</p>
                    </span>
                    <p>Kepadatan Penduduk</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 col-xs-6">
            <div class="small-box bg-purple">
                <div class="inner" style="height:90px">
                    <span style="display:flex; flex-direction:row; gap:1rem; align-items:baseline;">
                        <h3>{{ number_format($ratio_l_p, 2, ',', '.') }}</h3>
                        <p>{{ $satuan[4] }}</p>
                    </span>
                    <p>Rasio Pria Wanita</p>
                </div>
            </div>
        </div>

        {{-- Template konten lama --}}
        {{-- @if (can('b', 'mandiri'))
            <div class="col-lg-3 col-sm-6 col-xs-6">
                <div class="small-box" style="background-color: #39CCCC;">
                    <div class="inner">
                        <h3>{{ $pendaftaran }}</h3>
                        <p>Verifikasi Layanan Mandiri</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person"></i>
                    </div>
                    <a href="{{ route('mandiri') }}" class="small-box-footer">Lihat Detail <i
                            class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        @endif --}}
    </div>
@endsection
