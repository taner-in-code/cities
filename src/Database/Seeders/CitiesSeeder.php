<?php

namespace TanerInCode\Cities\Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class CitiesSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = $this->readXlsxData();
        if (!empty($data) && $data->count()) {

            $values = $this->dataInteraction($data);
            $cityModel = config('cities.models.city');
            $countyModel = config('cities.models.county');
            $districtModel = config('cities.models.district');
            $neighborhoodModel = config('cities.models.neighborhood');

            // turn the data rows..
            foreach ($values as $cityName => $counties) {
                $cityName = rtrim($cityName, " ");
                $city = $cityModel::firstOrNew(array('name' => $cityName));
                $city->name = $cityName;
                $city->save();

                // turn the countries rows !
                foreach ($counties as $countyName => $districts) {
                    $countyName = rtrim($countyName, " ");
                    $county = $countyModel::firstOrNew(array('name' => $countyName, 'city_id' => $city->id));
                    $county->name = $countyName;
                    $county->city_id = $city->id;
                    $county->save();

                    // turn the districts rows !
                    foreach ($districts as $districtName => $neighborhoods) {
                        $districtName = rtrim($districtName, " ");
                        $district = $districtModel::firstOrNew(array('name' => $districtName, 'county_id' => $county->id));
                        $district->name = $districtName;
                        $district->county_id = $county->id;
                        $district->save();

                        foreach ($neighborhoods as $neighborhoodData) {
                            $neighborhoodName = rtrim($neighborhoodData['name'], " ");
                            $neighborhoodPk = rtrim($neighborhoodData['pk'], " ");
                            $neighborhood = $neighborhoodModel::firstOrNew(array('name' => $neighborhoodName, 'pk' => $neighborhoodPk, 'district_id' => $district->id));
                            $neighborhood->name = $neighborhoodName;
                            $neighborhood->pk = $neighborhoodPk;
                            $neighborhood->district_id = $district->id;
                            $neighborhood->save();
                        }
                    }
                }
            }

        }

    }

    /**
     * @return mixed
     */
    private function readXlsxData()
    {
        try {
            $data = \PhpOffice\PhpSpreadsheet\IOFactory::load(config('cities.data_path'));
            return $data->get();
        } catch (\Exception $exception) {
            \Log::info($exception->getMessage());
        }
    }
    public function dataInteraction($data)
    {
        $insert = array();
        foreach ($data as $key => $value) {
            $insert[$value->il][$value->ilce][$value->semt_bucak_belde][] = ['name' => $value->mahalle, 'pk' => $value->pk];
        }
        return $insert;
    }
}