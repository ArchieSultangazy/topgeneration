<?php

namespace App\Console\Commands;

use App\Models\Location\District;
use App\Models\Location\Locality;
use App\Models\Location\Region;
use App\Models\School;
use Illuminate\Console\Command;
use Importer;

class LocationSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:seed {model}';

    /**
     * The console command description.
     * Ex: php artisan location:seed District
     *
     * @var string
     */
    protected $description = 'This command seeds location infos to DB. ! RUN DISTRICT MODEL FIRST !';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (strtolower($this->argument('model')) == 'district') {
            $this->district();
        }
        else if (strtolower($this->argument('model')) == 'locality') {
            $this->locality();
        }
        else if (strtolower($this->argument('model')) == 'school') {
            $this->school();
        }
        else {
            $this->warn("There is no location model like this. Please try again.");
        }
    }

    protected function district() {
        $excel = Importer::make('Excel');
        $excel->load(storage_path('districts_list.xlsx'));
        $collection = $excel->getCollection()->toArray();

        for ($i = 0; $i < count($collection); $i++) {
            if (!is_null(District::where('name', $collection[$i][1])->first())) {
                continue;
            }

            $region_id = Region::where('name', $collection[$i][0])->first()->id ?? null;
            District::create([
                'region_id' => $region_id,
                'name' => $collection[$i][1],
            ]);
        }

        $this->info('Districts list inserted successfully!');
    }

    protected function locality() {
        $excel = Importer::make('Excel');
        $excel->load(storage_path('localities_list.xlsx'));
        $collection = $excel->getCollection()->toArray();

        for ($i = 0; $i < count($collection); $i++) {
            if (!is_null(Locality::where('name', $collection[$i][2])->first())) {
                continue;
            }

            $region_id = Region::where('name', $collection[$i][0])->first()->id ?? null;
            $district_id = District::where('name', $collection[$i][1])->first()->id ?? null;

            Locality::create([
                'region_id' => $region_id,
                'district_id' => $district_id,
                'name' => $collection[$i][2],
            ]);
        }

        $this->info('Localities list inserted successfully!');
    }

    protected function school() {
        $excel = Importer::make('Excel');
        $excel->load(storage_path('schools_list.xlsx'));
        $collection = $excel->getCollection()->toArray();

        for ($i = 0; $i < count($collection); $i++) {
            if (!is_null(School::where('name', $collection[$i][2])->first())) {
                continue;
            }

            if (!is_null($collection[$i][0])) {
                $region_id = Region::where('name', $collection[$i][0])->first()->id ?? null;
            } else {
                $region_id = null;
            }
            $locality_id = Locality::where('name', $collection[$i][1])->first()->id ?? null;

            School::create([
                'region_id' => $region_id,
                'locality_id' => $locality_id,
                'name' => $collection[$i][2],
            ]);
        }

        $this->info('Schools list inserted successfully!');
    }
}
