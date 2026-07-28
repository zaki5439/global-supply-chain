<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Port;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class ImportPortsCommand extends Command
{
    protected $signature = 'import:ports {filepath=storage/app/ports.csv}';
    protected $description = 'Import World Port Index dataset dari file CSV ke database';

    public function handle()
    {
        $filepath = base_path($this->argument('filepath'));
        
        if (!file_exists($filepath)) {
            $this->error("File CSV tidak ditemukan di: {$filepath}");
            return;
        }

        $this->info("Memulai proses import...");
        $file = fopen($filepath, 'r');
        $header = fgetcsv($file);

        DB::beginTransaction();
        try {
            $count = 0;
            while (($row = fgetcsv($file)) !== false) {
                $data = array_combine($header, $row);
                $country = Country::where('iso3', $data['country_iso3'])->first();
                if (!$country) continue;

                Port::updateOrCreate(
                    ['unlocode' => $data['unlocode']],
                    [
                        'country_id' => $country->id,
                        'name' => $data['name'],
                        'latitude' => $data['lat'],
                        'longitude' => $data['lng']
                    ]
                );
                $count++;
            }
            DB::commit();
            $this->info("Selesai! Berhasil mengimpor {$count} pelabuhan.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi Error: " . $e->getMessage());
        }
        fclose($file);
    }
}
