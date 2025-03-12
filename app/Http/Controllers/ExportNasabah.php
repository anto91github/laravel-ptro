<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BcasAkun;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

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
                $results []= [
                    'email' => $row[1], // Ambil kolom C
                    'nohp' => $row[2],
                    'nik' => $row[3],
                    'nama_lengkap' => $row[4],
                    'tempat_lahir' => $row[5],
                    'tanggal_lahir' => $row[6],
                ];
            }
        }
        // Menghapus elemen kosong
        $results = array_filter($results, function($item) {
            return !is_null($item['email']) && !is_null($item['nohp']);
        });

        $this->insertData($results);
        Session::flash('statusExport','success');
        Session::flash('messageExport','Data selesai di proses');
    }

    public function insertData($data)
    {
        foreach($data as $key=>$row) {
            // insert into bcas_akun
            $this->insertBcasAkun($row, $key);
        }
    }

    public function insertBcasAkun($data, $key)
    {
        $client_id = 'C'.$key;
        $username = 'JKU-'.$client_id;
        $token_best = base64_encode($data['tanggal_lahir'].env('TOKEN_BEST'));


        BcasAkun::create([
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
            'id_device' => 'b8f56805-35b7-4b84-a350-d75cd35e9e9f',
            'state' => 10,
            'sync_best' => false,
            'client_id' => $client_id,
            'token_best' =>$token_best,
            'platform' => 'web',
            'source_platform' => 'Web BCAS'
        ]);
    }
}