<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BcasAkun;
use App\Models\BcasNasabahDomisili;
use App\Models\BcasNasabahNpwp;
use App\Models\BcasNasabahKTP;
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
            // insert into bcas_akun
            $client_id = strtoupper(Str::random(4));
            $username = 'JKU' . $client_id;
            $uuid = (string) Str::uuid();

            if (BcasAkun::where('email', $row['email'])->exists() || BcasAkun::where('nohp', $row['nohp'])->exists()) {
                $this->addLogs('validasi-awal', $username, 'FAILED', 'Email / Nohp sudah terdaftar.');
            } else if (BcasNasabahKTP::where('nik', $row['nik'])->exists()) {
                $this->addLogs('validasi-awal', $username, 'FAILED', 'NIK sudah terdaftar');
            } else {
                $this->insertBcasAkun($row, $key, $client_id, $username, $uuid);
                $this->insertBcasNasabahDomisili($row, $key, $username, $uuid);
                $this->insertBcasNpwp($row, $username, $uuid);
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
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
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
                'client_id' => $client_id,
                'token_best' => $token_best,
                'platform' => 'web',
                'source_platform' => 'Web BCAS'
            ]);
            $this->addLogs('bcas_akun', $username, 'SUCCESS', '-');
        } catch (QueryException $e) {
            $this->addLogs('bcas_akun', $username, 'FAILED', $e->getMessage());
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
}
