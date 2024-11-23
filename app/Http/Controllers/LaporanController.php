<?php

namespace App\Http\Controllers;

use App\Models\KelolaPoKirimSetorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function ks()
    {
        //
        $data=[];
		$data['title']='Monitoring Kirim Setor CMT ';
		$get=request();
		if(isset($get['tanggal1'])){
			$tanggal1=$get['tanggal1'];
		}else{
			// $tanggal1=date('Y-m-d',strtotime("monday this week"));
            $tanggal1='2024-05-01';
		}
		if(isset($get['tanggal2'])){
			$tanggal2=$get['tanggal2'];
		}else{
			$tanggal2=date('Y-m-d',strtotime('last day of this month'));
		}
		$data['tanggal1']=$tanggal1;
		$data['tanggal2']=$tanggal2;
    	$j=1;
		$pdz=0;
		$ppcs=0;
		$jmlpo=0;
		$arpo=array(
			array('type'=>'Kemeja','id'=>1),
			array('type'=>'Kaos','id'=>2),
			array('type'=>'Celana','id'=>3),
		);
		
		$i=1;
		$qty=0;
		$qtysetor=0;
		$ckirim=0;
		$csetor=0;
        $data['rekap']=[];
        $data['adjustment']=[];
        foreach($arpo as $arp){
            $qty=$this->rpdashkirim($arp['id'],$tanggal1,$tanggal2);
            $qtysetor=$this->rpdashsetor($arp['id'],$tanggal1,$tanggal2);
			$ckirim=$this->countdashkirim($arp['id'],$tanggal1,$tanggal2);
			$csetor=$this->countdashsetor($arp['id'],$tanggal1,$tanggal2);
			$data['rekap'][]=array(
				'no'=>$i,
				'id'=>$arp['id'],
				'type'=>$arp['type'],
				'countkirim'=>$ckirim,
				'qtykirimdz'=>($qty/12),
				'qtykirimpcs'=>($qty),
				'countsetor'=>$csetor,
				'qtysetordz'=>($qtysetor/12),
				'qtysetorpcs'=>($qtysetor),
				'keterangan'=>'PO Beredar : '.($ckirim-$csetor),
			);
			$i++;
		}

        return view('lap_ks',$data);
    }

    public function rpdashkirim($jenis, $tanggal1, $tanggal2)
{
    $hasil=[];
    $query = DB::connection('mysql2')->table('kelolapo_kirim_setor as kbp')
        ->join('produksi_po as p', 'p.id_produksi_po', '=', 'kbp.idpo')
        ->leftJoin('master_jenis_po as mjp', 'mjp.nama_jenis_po', '=', 'p.nama_po')
        ->select(DB::raw('SUM(kbp.qty_tot_pcs) as total'))
        ->where('mjp.idjenis', '=', $jenis)
        ->where('mjp.tampil', '=', 1)
        ->where('kbp.kategori_cmt', '=', 'JAHIT')
        ->where('kbp.progress', '=', 'KIRIM')
        ->where('kbp.hapus', '=', 0);

    if (!empty($tanggal1)) {
        $query->whereBetween(DB::raw('DATE(kbp.create_date)'), [$tanggal1, $tanggal2]);
    }

    $hasil = $query->first();

    return $hasil && $hasil->total > 0 ? $hasil->total : 0;
}

public function rpdashsetor($jenis, $tanggal1, $tanggal2)
{
    // Variabel untuk hasil total
    $hasil = 0;
    $bangkenya=0;

    // Mulai membangun query pertama
    $query = DB::connection('mysql2')->table('kelolapo_kirim_setor as kbp')
                ->selectRaw('SUM(kbp.qty_tot_pcs) as total')
                ->join('produksi_po as p', 'p.id_produksi_po', '=', 'kbp.idpo')
                ->leftJoin('master_jenis_po as mjp', 'mjp.nama_jenis_po', '=', 'p.nama_po')
                ->where('mjp.idjenis', $jenis)
                ->where('mjp.tampil', 1)
                ->where('kbp.kategori_cmt', 'JAHIT')
                ->where('kbp.progress', 'SETOR')
                ->where('kbp.hapus', 0);

    // Menambahkan kondisi tanggal jika ada
    if (!empty($tanggal1) && !empty($tanggal2)) {
        $query->whereBetween(DB::raw('DATE(kbp.create_date)'), [$tanggal1, $tanggal2]);
    }

    // Eksekusi query dan ambil hasilnya
    $row = $query->first();

    // Menyimpan hasil total
    $hasil = $row ? $row->total : 0;

    // Query kedua untuk menghitung bangke
    $bangkeQuery = DB::connection('mysql2')->table('kelolapo_rincian_setor_cmt as rpo')
                      ->selectRaw('COALESCE(SUM(rpo.jml_setor_qty - rpo.bangke_qty), 0) as total')
                      ->leftJoin('kelolapo_kirim_setor as kbp', 'kbp.kode_po', '=', 'rpo.kode_po')
                      ->leftJoin('produksi_po as p', 'p.id_produksi_po', '=', 'kbp.idpo')
                      ->leftJoin('master_jenis_po as mjp', 'mjp.nama_jenis_po', '=', 'p.nama_po')
                      ->where('mjp.idjenis', $jenis)
                      ->where('mjp.tampil', 1)
                      ->where('kbp.kategori_cmt', 'JAHIT')
                      ->where('kbp.progress', 'SETOR')
                      ->where('kbp.hapus', 0);

    // Menambahkan kondisi tanggal jika ada
    if (!empty($tanggal1) && !empty($tanggal2)) {
        $bangkeQuery->whereBetween(DB::raw('DATE(kbp.create_date)'), [$tanggal1, $tanggal2]);
    }

    // Eksekusi query kedua dan ambil hasilnya
    $dbangke = $bangkeQuery->first();
    $bangkenya = $dbangke ? $dbangke->total : 0;

    // Kembalikan hasil
    if ($hasil > 0) {
        return $hasil - $bangkenya;
    } else {
        return 0; // Jika tidak ada hasil
    }
}

public function countdashkirim($jenis, $tanggal1, $tanggal2)
{
    $hasil = 0;  // Default jika tidak ada hasil
    // $hasil = KelolaPoKirimSetorModel::
    // join('produksi_po as p', 'p.id_produksi_po', '=', 'kelolapo_kirim_setor.idpo')
    // ->leftJoin('master_jenis_po as mjp', 'mjp.nama_jenis_po', '=', 'p.nama_po')
    // ->where('kategori_cmt','JAHIT',)
    // ->where('progress','KIRIM')
    // ->where('mjp.idjenis', $jenis)
    // ->where('mjp.tampil', 1)
    // ->where('kelolapo_kirim_setor.hapus', 0)
    // ->whereNotIn('kelolapo_kirim_setor.id_master_cmt', [63]);
    // if (!empty($tanggal1) && !empty($tanggal2)) {
    //     $hasil->whereBetween(DB::raw('DATE(kelolapo_kirim_setor.create_date)'), [$tanggal1, $tanggal2]);
    // }

    // $hasil = $hasil->count();
    // Mulai membangun query
    $query = DB::connection('mysql2')->table('kelolapo_kirim_setor as kbp')
                ->selectRaw('count(DISTINCT kbp.kode_po) as total, mjp.nama_jenis_po, mjp.perkalian')
                ->join('produksi_po as p', 'p.id_produksi_po', '=', 'kbp.idpo')
                ->leftJoin('master_jenis_po as mjp', 'mjp.nama_jenis_po', '=', 'p.nama_po')
                ->where('mjp.idjenis', $jenis)
                ->where('kbp.kategori_cmt', 'JAHIT')
                ->where('kbp.progress', 'KIRIM')
                ->where('kbp.hapus', 0)
                ->where('mjp.tampil', 1)
                ->whereNotIn('kbp.id_master_cmt', [63]);

    // Menambahkan kondisi tanggal jika ada
    if (!empty($tanggal1) && !empty($tanggal2)) {
        $query->whereBetween(DB::raw('DATE(kbp.create_date)'), [$tanggal1, $tanggal2]);
    }

    // Eksekusi query dan ambil hasilnya
    $query->groupBy('mjp.nama_jenis_po');
    $query->groupBy('mjp.perkalian');
    $row = $query->first(); // Mengambil satu baris hasil query

    // Menentukan hasil
    
    if ($row && $row->total > 0) {
        $hasil = $row->total;
        if ($row->nama_jenis_po == "SKF" || strtoupper($row->nama_jenis_po) == "SIMULASI SKF") {
            $hasil = round($row->total * $row->perkalian);
        }
    }

    return $hasil;
}

public function countdashsetor($jenis, $tanggal1, $tanggal2)
{
    $hasil = 0;  // Default hasil jika tidak ada data
    $bangkenya=0;
    // // Mulai membangun query
    // $query = DB::connection('mysql2')->table('kelolapo_kirim_setor as kbp')
    //             ->selectRaw('count(DISTINCT p.kode_po) as total, mjp.nama_jenis_po, mjp.perkalian')
    //             ->join('produksi_po as p', 'p.id_produksi_po', '=', 'kbp.idpo')
    //             ->leftJoin('master_jenis_po as mjp', 'mjp.nama_jenis_po', '=', 'p.nama_po')
    //             ->where('mjp.idjenis', $jenis)
    //             ->where('kbp.kategori_cmt', 'JAHIT')
    //             ->where('kbp.progress', 'SETOR')
    //             ->where('kbp.hapus', 0)
    //             ->where('mjp.tampil', 1)
    //             ->whereNotIn('kbp.id_master_cmt', [63])
    //             ->groupBy('mjp.nama_jenis_po');

    // // Menambahkan kondisi tanggal jika ada
    // if (!empty($tanggal1) && !empty($tanggal2)) {
    //     $query->whereBetween(DB::raw('DATE(kbp.create_date)'), [$tanggal1, $tanggal2]);
    // }

    // // Eksekusi query dan ambil hasilnya
    // $rows = $query->get();

    // Proses hasil
    // if ($rows->isNotEmpty()) {
    //     foreach ($rows as $row) {
    //         $hasil += round($row->total * $row->perkalian);
    //     }
    // }

    return $hasil;
}






    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
