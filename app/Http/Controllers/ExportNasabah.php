<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BcasAkun;
use App\Models\BcasNasabahBO;
use App\Models\BcasNasabahDomisili;
use App\Models\BcasNasabahNpwp;
use App\Models\BcasNasabahKTP;
use App\Models\BcasPernyataan;
use App\Models\BcasDataPekerjaan;
use App\Models\BcasNasabahRekening;
use App\Models\BcaAhliWaris;
use App\Models\BcasNasabahTTD;
use App\Models\BcaInstruksiKhusus;
use App\Models\BcasDataTambahan;
use App\Models\SyncIntegration;
use App\Models\ExportLog;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class ExportNasabah extends Controller
{
    public function index(Request $request)
    {
        $bcas_akun = BcasAkun::all();
        // dd($bcas_akun);
        return view('ExportNasabah/index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');

        // Membaca file Excel
        $data = Excel::toArray(new class implements ToArray {
            public function array(array $array)
            {
                return $array; // Mengembalikan semua baris sebagai array
            }
        }, $file);

        // Mengambil data dari kolom B (indeks 2) mulai dari B2
        $results = [];
        foreach ($data[0] as $key => $row) {
            if ($key >= 1) { // Mulai dari baris ke 2
                $results[] = [
                    'email' => $row[1],
                    'nohp' => $row[2],
                    'nik' => $row[3],
                    'nama_lengkap' => $row[4],
                    'tempat_lahir' => $row[5],
                    'tanggal_lahir' => $row[6],
                    'jenis_kelamin' => $row[7],
                    'status_perkawinan' => $row[8],
                    'agama' => $row[9],
                    'alamat_ktp' => $row[10],
                    'rt_ktp' => $row[11],
                    'rw_ktp' => $row[12],
                    'kota_ktp' => $row[13],
                    'provinsi_ktp' => $row[14],
                    'kode_pos_ktp' => $row[15],
                    'nama_ibu_kandung' => $row[16],
                    'kecamatan_ktp' => $row[17],
                    'kelurahan_ktp' => $row[18],
                    'jumlah_tanggungan' => $row[19],
                    'nama_pasangan' => $row[20],
                    'no_telp_pasangan' => $row[21],
                    'negara_lahir' => $row[22],
                    'pendidikan_terakhir' => $row[23],
                    'pekerjaan' => $row[24],
                    'jabatan' => $row[25],
                    'nama_perusahaan' => $row[26],
                    'bidang_usaha' => $row[27],
                    'hubungan_kerja' => $row[28],
                    'tahun_lama_kerja' => $row[29],
                    'bulan_lama_kerja' => $row[30],
                    'alamat_office' => $row[31],
                    'kelurahan_office' => $row[32],
                    'kecamatan_office' => $row[33],
                    'kota_office' => $row[34],
                    'provinsi_office' => $row[35],
                    'rt_office' => $row[36],
                    'rw_office' => $row[37],
                    'kode_pos_office' => $row[38],
                    'tlp_office' => $row[39],
                    'status_tempat_tinggal' => $row[40],
                    'menempati_sejak' => $row[41],
                    'tujuan_investasi' => $row[42],
                    'toleransi_terhadap_resiko' => $row[43],
                    'kekayaan_bersih' => $row[44],
                    'pendapatan_per_tahun' => $row[45],
                    'nama_bank_tujuan' => $row[46],
                    'no_rekening_tujuan' => $row[47],
                    'nama_rekening_tujuan' => $row[48],
                    'tipe_membership_bca' => $row[49],
                    'nama_ahli_waris' => $row[50],
                    'hubungan_ahli_waris' => $row[51],
                    'tlp_alih_waris' => $row[52],
                    'alamat_domisili' => $row[53],
                    'rt_domisili' => $row[54],
                    'rw_domisili' => $row[55],
                    'kelurahan_domisili' => $row[56],
                    'kecamatan_domisili' => $row[57],
                    'kota_domisili' => $row[58],
                    'provinsi_domisili' => $row[59],
                    'kode_pos_domisili' => $row[60],
                    'is_npwp' => $row[61],
                    'npwp_no' => $row[62],
                ];
            }
        }

        // Menghapus elemen kosong
        $results = array_filter($results, function ($item) {
            return !is_null($item['email']) && !is_null($item['nohp']);
        });

        $this->insertData($results);
        Session::flash('statusExport', 'success');
        Session::flash('messageExport', 'Data selesai di proses');

        return redirect('/exportNasabah');
    }

    public function insertData($data)
    {
        foreach ($data as $key => $row) {
            $client_id = strtoupper(Str::random(4));
            $username = 'JKU' . $client_id;
            $uuid = (string) Str::uuid();

            if (BcasAkun::where('email', $row['email'])->exists()) {
                $this->addLogs('validasi-awal', $username, 'FAILED', 'Email '.$row['email'].' sudah terdaftar.');
            }else if (BcasAkun::where('nohp', $row['nohp'])->exists()) {
                $this->addLogs('validasi-awal', $username, 'FAILED', 'No Hp '.$row['nohp'].' sudah terdaftar.');
            }else if (BcasNasabahKTP::where('nik', $row['nik'])->exists()) {
                $this->addLogs('validasi-awal', $username, 'FAILED', 'NIK '.$row['nik'].' sudah terdaftar');
            } else {
                $result_insert_bcasAkun = false;
                $result_insert_bcasAkun = $this->insertBcasAkun($row, $key, $client_id, $username, $uuid);

                if($result_insert_bcasAkun == true){
                    $this->insertBcasNasabahDomisili($row, $key, $username, $uuid);
                    $this->insertBcasNpwp($row, $username, $uuid);
                    $this->insertBcasKTP($row, $key, $username, $uuid);
                    $this->insertDataPekerjaan($row, $username, $uuid);                   
                    $this->insertBcasPernyataan($row, $username, $uuid);
                    $this->insertBcasBO($row, $username, $uuid);
                    $this->insertNasabahRekening($row, $username, $uuid);
                    $this->insertInstruksiKhusus($row, $username, $uuid);
                    $this->insertAhliWaris($row, $username, $uuid);
                    $this->insertTTDNasabah($row, $username, $uuid);
                    $this->insertDataTambahan($row, $username, $uuid);
                    $this->insertSyncIntegration($row, $username, $uuid, $client_id);

                }
            }
        }
    }

    public function addLogs($table_name, $username, $status, $error_message)
    {
        ExportLog::create([
            'table_process' => $table_name,
            'username' => $username,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => $status,
            'error_message' => $error_message
        ]);
    }

    public function insertBcasAkun($data, $key, $client_id, $username, $uuid)
    {
        $token_best = base64_encode($data['tanggal_lahir'] . env('TOKEN_BEST'));

        try {
            BcasAkun::create([
                'id' => $uuid,
                // 'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                // 'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'deleted' => false,
                'email' => $data['email'],
                'nohp' => $data['nohp'],
                'username' => $username,
                'password' => bcrypt($data['tanggal_lahir']),
                'status' => 1,
                'verifikasi_nohp' => true,
                'verifikasi_email' => true,
                'id_device' => $uuid,
                'state' => 10,
                'sync_best' => false,
                'status_approval' => 0,
                'client_id' => $client_id,
                'token_best' => $token_best,
                'platform' => 'web',
                'source_platform' => 'Web BCAS'
            ]);
            $this->addLogs('bcas_akun', $username, 'SUCCESS', '-');
            return true;
        } catch (QueryException $e) {
            $this->addLogs('bcas_akun', $username, 'FAILED', $e->getMessage());
            return false;
        }
    }

    public function insertBcasNpwp($data, $username, $uuid)
    {

        $validate = false;
        $no_npwp = '';

        if ($data['is_npwp'] == 'Ada (Pribadi)') {
            $validate = true;
            $no_npwp = $data['nik'];
        }

        if ($data['is_npwp'] == 'Ada (Orang Lain)') {
            $no_npwp = $data['npwp_no'];
        }

        try {
            BcasNasabahNpwp::create([
                'id' => $uuid,
                'no_npwp' => $no_npwp,
                'validate' => $validate,
                'is_use_nik' => true,
                'is_file_upload_npwp' => true
            ]);
            $this->addLogs('bca_nasabah_npwp', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bca_nasabah_npwp', $username, 'FAILED', $e->getMessage());
        }
    }

    public function insertBcasPernyataan($data, $username, $uuid)
    {

        $no_pernyataan = [1, 11, 14, 16, 18, 20, 21, 100];

        foreach ($no_pernyataan as $key => $value) {
            $last_id = BcasPernyataan::max('id');
            if($key == 0) {
                $next_id = $last_id + 20;
            } else {
                $next_id = $last_id + 1;
            }
            
            try {
                BcasPernyataan::insert([
                    'id' => ($next_id),
                    'id_user' => $uuid,
                    'jawaban' => 'tidak',
                    'master_pernyataan_id' => $value
                ]);
                $this->addLogs('bcas_nasabah_pernyataan', $username, 'SUCCESS', '-');
            } catch (QueryException $e) {
                $this->addLogs('bcas_nasabah_pernyataan', $username, 'FAILED', $e->getMessage());
            }
        }
    }

    public function insertBcasBO($data, $username, $uuid)
    {
        try {
            BcasNasabahBO::create([
                'id' => $uuid,
                'deleted' => false,
                'is_nasabah_bo' => false,
                'created_at' => now()
            ]);
            $this->addLogs('bcas_nasabah_beneficiary_owner', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bcas_nasabah_beneficiary_owner', $username, 'FAILED', $e->getMessage());
        }
    }

    public function insertBcasNasabahDomisili($data, $key, $username, $uuid)
    {
        try {
            BcasNasabahDomisili::create([
                'id' => $uuid,
                'alamat' => $data['alamat_domisili'],
                'rt' => $data['rt_domisili'],
                'rw' => $data['rw_domisili'],
                'kelurahan' => $data['kelurahan_domisili'],
                'kecamatan' => $data['kecamatan_domisili'],
                'id_kota' => $data['kota_domisili'],
                'id_provinsi' => $data['provinsi_domisili'],
                'kodepos' => $data['kode_pos_domisili']
            ]);
            $this->addLogs('bca_nasabah_domisili', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bca_nasabah_domisili', $username, 'FAILED', $e->getMessage());
        }
    }

    public function insertBcasKTP($data, $key, $username, $uuid)
    {
        try {
            BcasNasabahKTP::create([
                'id' => $uuid,
                'nik' => $data['nik'],
                'nama_lengkap' => $data['nama_lengkap'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'status_perkawinan' => $data['status_perkawinan'],
                'agama' => $data['agama'],
                'alamat' => $data['alamat_ktp'],
                'rt' => $data['rt_ktp'],
                'rw' => $data['rw_ktp'],
                'id_kota' => $data['kota_ktp'],
                'id_provinsi' => $data['provinsi_ktp'],
                'kodepos' => $data['kode_pos_ktp'],
                'nama_ibu_kandung' => $data['nama_ibu_kandung'],
                'kecamatan' => $data['kecamatan_ktp'],
                'kelurahan' => $data['kelurahan_ktp'],
                'jumlah_tanggungan' => $data['jumlah_tanggungan'],
                'nama_pasangan' => $data['nama_pasangan'],
                'no_telp_pasangan' => $data['no_telp_pasangan'],
                'negara_lahir' => $data['negara_lahir'],
                'pendidikan_terakhir' => $data['pendidikan_terakhir'],
                'verified_ktp' => 'REGISTERED',
                'valid_ktp' => false,
                'valid_selfie' => false,
                'valid_ktp_bca' => false,
                'valid_ktp_disdukcapil' => false
            ]);
            $this->addLogs('bcas_nasabah_ktp', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bcas_nasabah_ktp', $username, 'FAILED', $e->getMessage());
        }
    }

    public function insertDataPekerjaan($data, $username, $uuid)
    {
        try {
            BcasDataPekerjaan::create([
                'id' => $uuid,
                'sumber_dana' => 'Gaji',
                'pekerjaan' => $data['pekerjaan'],
                'jabatan' => $data['jabatan'],
                'nama_perusahaan' => $data['nama_perusahaan'],
                'bidang_usaha' => $data['bidang_usaha'],
                'hubungan_kerja' => $data['hubungan_kerja'],
                'lama_kerja' => $data['tahun_lama_kerja'],
                'alamat_perusahaan' => $data['alamat_office'],
                'kelurahan' => $data['kelurahan_office'],
                'kecamatan' => $data['kecamatan_office'],
                'id_kota' => $data['kota_office'],
                'id_provinsi' => $data['provinsi_office'],
                'kodepos' => $data['kode_pos_office'],
                'telp_perusahaan' => $data['tlp_office'],
                'status_tempat_tinggal' => $data['status_tempat_tinggal'],
                'menempati_sejak' => $data['menempati_sejak'],
                'tujuan_investasi' => $data['tujuan_investasi'],
                'toleransi_terhadap_resiko' => $data['toleransi_terhadap_resiko'],
                'kekayaan_bersih' => $data['kekayaan_bersih'],
                'pendapatan_per_tahun' => $data['pendapatan_per_tahun'],
                'rt'=> $data['rt_office'],
                'rw' => $data['rw_office'],
                'lama_bulan_kerja' => $data['bulan_lama_kerja'],
                'id_detail_pekerjaan' => 9 // pegawai swasta
            ]);
            $this->addLogs('bcas_nasabah_data_pekerjaan', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bcas_nasabah_data_pekerjaan', $username, 'FAILED', $e->getMessage());
        }
    }

    public function insertNasabahRekening($data, $username, $uuid)
    {
        $nama_bank = $data['nama_bank_tujuan'];

        if($data['nama_bank_tujuan'] == 'blu') {
            $nama_bank = 'BCAD';
        }

        try{
            BcasNasabahRekening::create([
                'id' => $uuid,
                'kode_bank' => $nama_bank,
                'nomor_rekening' => $data['no_rekening_tujuan'],
                'is_karyawan' => false,
                'is_sharedata_blu' => false,
                'is_blucc_done' =>  false,
                'is_create_acc_blu' => false,
                'kode_hybrid' => 232 // Online
            ]);
            $this->addLogs('bcas_nasabah_rekening', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bcas_nasabah_rekening', $username, 'FAILED', $e->getMessage());
        }
    }

    public function insertInstruksiKhusus($data, $username, $uuid)
    {
        $nama_bank = $data['nama_bank_tujuan'];
        $tipe_membership;
        $id_fitur;

        if($data['nama_bank_tujuan'] == 'blu') {
            $nama_bank = 'BCAD';
        }

        if($data['nama_bank_tujuan'] == 'BCA') {
            //check membership
            if($data['tipe_membership_bca'] == 'PRIORITAS') {
                $tipe_membership = 3;
                $id_fitur = 'be9c770e-a799-42f7-865c-8b26bd8d3174';
            } else if($data['tipe_membership_bca'] == 'SOLITAIRE') {
                $tipe_membership = 4;
                $id_fitur = '197dffb9-ecf5-45d2-a55d-f89baec498b6';
            } else {
                $tipe_membership = 5;
                $id_fitur = 'd8030c2c-29f9-4e36-a203-32633ac8d4b6';
            }
        } else if($data['nama_bank_tujuan'] == 'blu') {
            $tipe_membership = 5;
            $id_fitur = 'd8030c2c-29f9-4e36-a203-32633ac8d4b6';
        }

        try{
            BcaInstruksiKhusus::create([
                'id' => $uuid,
                'suber_info' => 'Referral',
                'kode_referal' => 'BonusPTRO', // dummy sementara
                'nama_bank_tujuan' => $nama_bank,
                'no_rekeing_tujuan' => $data['no_rekening_tujuan'],
                'nama_rekeing_tujuan' => $data['nama_rekening_tujuan'],
                'email_laporan_transaksi' => $data['email'],
                'instruksi_pembayaran' => 0,
                'id_fitur_sekuritas' => $id_fitur,
                'tipe_membership' => $tipe_membership,
                'fasilitas_transaksi' => 'Online'
            ]);
            $this->addLogs('bca_nasabah_instruksi_khusus', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bca_nasabah_instruksi_khusus', $username, 'FAILED', $e->getMessage());
        } 
    }

    public function insertAhliWaris($data, $username, $uuid) {
        if($data['nama_ahli_waris'] != NULL && $data['hubungan_ahli_waris']!= NULL && $data['tlp_alih_waris']!= NULL) {
            try{
                $latestId = BcaAhliWaris::max('id');
                $newId = $latestId + 20;
    
                BcaAhliWaris::create([
                    'id' => $newId,
                    'user_id' => $uuid,
                    'nama' => $data['nama_ahli_waris'],
                    'hubungan' => $data['hubungan_ahli_waris'],
                    'phone' => $data['tlp_alih_waris']
                ]);
    
                $this->addLogs('bca_nasabah_ahli_waris', $username, 'SUCCESS', '-');
            } catch (QueryException $e) {
                $this->addLogs('bca_nasabah_ahli_waris', $username, 'FAILED', $e->getMessage());
            }
        }
        
    }

    public function insertTTDNasabah($data, $username, $uuid){
        try{
            BcasNasabahTTD::create([
                'id' => $uuid
            ]);
            $this->addLogs('bcas_nasabah_ttd', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bcas_nasabah_ttd', $username, 'FAILED', $e->getMessage());
        }
    }

    public function insertDataTambahan($data, $username, $uuid)
    {
        try{
            BcasDataTambahan::create([
                'id' => $uuid,
                'usaha_luar_negri' => false,
                'kartu_kredit_lain' => false,
                'lama_tahun' => 0
            ]);

            $this->addLogs('bcas_nasabah_data_tambahan', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bcas_nasabah_data_tambahan', $username, 'FAILED', $e->getMessage());
        }
    }
    
    public function insertSyncIntegration($data, $username, $uuid, $client_id)
    {
        try{
            SyncIntegration::create([
                'id' => $uuid,
                'deleted' => false,
                'no_cif' => $client_id,
                'nik' => $data['nik'],
                'name' => $data['nama_lengkap'],
                'sas' => false,
                'best' => false,
                'sync' => false,
                'status' => 0,
                'last_update' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $this->addLogs('synchronize_integration', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('synchronize_integration', $username, 'FAILED', $e->getMessage());
        }
    }
}
