@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1></h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ $title }}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Tanggal Awal</label>
                                <input type="text" class="form-control datepicker" id="tanggal1" name="tanggal_awal" value="{{ $tanggal1 }}" >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Tanggal Akhir</label>
                                <input type="text" class="form-control datepicker" id="tanggal2" name="tanggal_akhir" value="{{ $tanggal2 }}" >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Aksi</label><br>
                                <button class="btn btn-info btn-sm" onclick="filtertglonly()">Filter</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert" style="background-color: #3D6AA2 !important;color: white">Update <?php echo date('d F Y')?></div>
                                <table class="table table-bordered">
                                    <thead>
                                            <tr>
                                                <th rowspan="3" style="text-align: center;vertical-align: middle;">No</th>
                                                <th rowspan="3" style="text-align: center;vertical-align: middle;">Nama PO</th>
                                                <th colspan="3" style="text-align: center;">Kirim CMT</th>
                                                <th colspan="3" style="text-align: center;vertical-align: middle;">Setor CMT</th>
                                            </tr>
                                            <tr style="text-align: center;vertical-align: bottom;">
                                                <th>Jml PO</th>
                                                <th>Dz</th>
                                                <th>Pcs</th>
                                                <th>Jml PO</th>
                                                <th>Dz</th>
                                                <th>Pcs</th>
                                            </tr>
                                        </thead>
                                    <tbody>
                                        <?php $adjkirim=0;$adjdz=0;$adjpcs=0;$spo=0;$sdz=0;$spcs=0; ?>
                                        <?php $nom=1;$jmlpo1=0;$jmlpo2=0;$dz1=0;$dz2=0;$pcs1=0;$pcs2=0; ?>
                                        <?php foreach($adjustment as $r){?>
                                            <tr style="text-align: center">
                                                <td><?php echo $nom++?></td>
                                                <td><?php echo $r['nama']?></td>
                                                <td><?php echo number_format($r['kirim_po'])?></td>
                                                <td><?php echo number_format($r['kirim_dz'])?></td>
                                                <td><?php echo number_format($r['kirim_pcs'])?></td>
                                                <td><?php echo number_format($r['setor_po'])?></td>
                                                <td><?php echo number_format($r['setor_dz'])?></td>
                                                <td><?php echo number_format($r['setor_pcs'])?></td>
                                            </tr>
                                            <?php

                                                $adjkirim+=($r['kirim_po']);
                                                $adjdz+=($r['kirim_dz']);
                                                $adjpcs+=($r['kirim_pcs']);
                                                $spo+=($r['setor_po']);
                                                $sdz+=($r['setor_dz']);
                                                $spcs+=($r['setor_pcs']);
                                            ?>
                                        <?php } ?>

                                        <?php foreach($rekap as $r){?>
                                        <tr style="text-align: center">
                                            <td><?php echo $nom++?></td>
                                            <td><?php echo $r['type']?></td>
                                            <td><?php echo number_format($r['countkirim'])?></td>
                                            <td><?php echo number_format($r['qtykirimdz'])?></td>
                                            <td><?php echo number_format($r['qtykirimpcs'])?></td>
                                            <td><?php echo number_format($r['countsetor'])?></td>
                                            <td><?php echo number_format($r['qtysetordz'])?></td>
                                            <td><?php echo number_format($r['qtysetorpcs'])?></td>
                                        </tr>
                                        <?php
                                            $jmlpo1+=($r['countkirim']);
                                            $jmlpo2+=($r['countsetor']);
                                            $dz1+=($r['qtykirimdz']);
                                            $dz2+=($r['qtysetordz']);
                                            $pcs1+=($r['qtykirimpcs']);
                                            $pcs2+=($r['qtysetorpcs']);
                                        ?>
                                        <?php } ?>
                                        <tr style="text-align: center">
                                            <td colspan="2"><b>Total</b></td>
                                            <td><b><?php echo number_format($jmlpo1+$adjkirim)?></b></td>
                                            <td><b><?php echo number_format($dz1+$adjdz)?></b></td>
                                            <td><b><?php echo number_format($pcs1+$adjpcs)?></b></td>
                                            <td><b><?php echo number_format($jmlpo2+$spo)?></b></td>
                                            <td><b><?php echo number_format($dz2+$sdz)?></b></td>
                                            <td><b><?php echo number_format($pcs2+$spcs)?></b></td>
                                        </tr>
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    
    <script>
        $(document).ready(function () {
            // Inisialisasi Datepicker
            $('.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                todayHighlight: true
            });
        });
    </script>
@stop
<script>
     function filtertglonly(){
        var url='?';
        var tanggal1 =$("#tanggal1").val();
        var tanggal2 =$("#tanggal2").val();
        if(tanggal1){
        url+='&tanggal1='+tanggal1;
        }
        if(tanggal2){
        url+='&tanggal2='+tanggal2;
        }
        location =url;
    }

</script>