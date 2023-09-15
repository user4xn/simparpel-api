<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('app_settings')->insert([
            [
                'id' => 1,
                'label' => 'device',
                'type' => 'number',
                'name' => 'refresh_interval',
                'value' => '60',
                'default_value' => '60',
                'description' => 'Berapa lama interval perangkat akan mengirim data ke server (dalam detik)',
                'additional_data' => '',
                'created_at' => '2023-09-06 17:41:28',
                'updated_at' => '2023-09-13 23:47:17',
            ],
            [
                'id' => 2,
                'label' => 'device',
                'type' => 'number',
                'name' => 'distance_offset',
                'value' => '20',
                'default_value' => '20',
                'description' => 'Berapa jarak antara koordinat sebelumnya sebelum perangkat mengirim data ke server (dalam meter)',
                'additional_data' => '',
                'created_at' => '2023-09-06 17:42:58',
                'updated_at' => '2023-09-13 23:47:17',
            ],
            [
                'id' => 3,
                'label' => 'device',
                'type' => 'select',
                'name' => 'api_send_method',
                'value' => 'all',
                'default_value' => 'all',
                'description' => 'Bagaimana metode yang digunakan aplikasi dalam mengirimkan data',
                'additional_data' => '[{"text":"All","value":"all"},{"text":"By refresh_interval","value":"interval"},{"text":"By distance_offset","value":"distance"}]',
                'created_at' => '2023-09-06 17:42:58',
                'updated_at' => '2023-09-13 23:47:17',
            ],
            [
                'id' => 4,
                'label' => 'device',
                'type' => 'text',
                'name' => 'apk_min_version',
                'value' => '1.0',
                'default_value' => '1.0',
                'description' => 'Minimal versi apk yang dapat menjalankan aplikasi',
                'additional_data' => '',
                'created_at' => '2023-09-06 17:42:58',
                'updated_at' => '2023-09-13 23:47:17',
            ],
            [
                'id' => 5,
                'label' => 'device',
                'type' => 'text',
                'name' => 'apk_download_link',
                'value' => 'http://google.com',
                'default_value' => 'http://google.com',
                'description' => 'Link download apk terbaru jika ada pembaruan',
                'additional_data' => '',
                'created_at' => '2023-09-06 17:42:58',
                'updated_at' => '2023-09-13 23:47:17',
            ],
        ]);
    }
}
