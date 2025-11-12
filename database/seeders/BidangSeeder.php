<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bidang;

class BidangSeeder extends Seeder
{
    public function run()
    {
         {
        Bidang::create(['nama_bidang' => 'Sekretariat', 'kepala_bidang' => 'Yusrini Rahayu, SH., MM', 'nip_kepala' => '198002192005022004']);
        Bidang::create(['nama_bidang' => 'Perencanaan Anggaran Daerah', 'kepala_bidang' => 'Andi Hardianzah, SE., M.Si', 'nip_kepala' => '198003032009021002']);
        Bidang::create(['nama_bidang' => 'Aset Daerah', 'kepala_bidang' => 'Masran, SE', 'nip_kepala' => '197809232005021002']);
        Bidang::create(['nama_bidang' => 'Perbendaharaan Daerah', 'kepala_bidang' => 'Seni Karyawati, S.Sos', 'nip_kepala' => '197607232000032005']);
        Bidang::create(['nama_bidang' => 'Akuntansi dan Pelaporan Daerah', 'kepala_bidang' => 'Hj. Nuralam, S.Sos', 'nip_kepala' => '196906081993092003']);
        }
    }
}
