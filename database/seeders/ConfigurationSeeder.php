<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dt = Carbon::now();
        $dateNow = $dt->toDateTimeString();

        $configurations = [
            [
                'label' => 'Sandbox',
                'code' => 'is_sandbox',
                'value' => 'true',
                'group' => 'General',
                'is_protected' => true
            ],
            [
                'label' => 'Notifications Email',
                'code' => 'notifications_email',
                'value' => 'roberto.guzman@leancommerce.mx',
                'group' => 'General',
                'is_protected' => true
            ],
            [
                'label' => 'Token Sandbox',
                'code' => 'token_sandbox',
                'value' => 'MToyWnEySTFqV0JSOVhBWmpoYVZkSk9Rd2g4aEt0RWMxSFVUTUVyWGR3',
                'group' => 'General',
                'is_protected' => true
            ],
            [
                'label' => 'Token',
                'code' => 'token',
                'value' => 'MTpTMjBGQTJnTGVYdURoVll1d3AwSTFJM3NpTFp5ZGlTdlR0UFFDdzdy',
                'group' => 'General',
                'is_protected' => true
            ],
            [
                'label' => 'BIP Endpoint Sandbox',
                'code' => 'bip_endpoint_sandbox',
                'value' => 'https://api-sandbox.igou.mx/v1',
                'group' => 'BIP',
                'is_protected' => true
            ],
            [
                'label' => 'BIP Endpoint',
                'code' => 'bip_endpoint',
                'value' => 'https://api.igou.mx/v1',
                'group' => 'BIP',
                'is_protected' => true
            ],
        ];

        foreach ($configurations as $configuration) {
            DB::table('configurations')->insert([
                'label' => $configuration['label'],
                'code' => $configuration['code'],
                'value' => $configuration['value'],
                'group' => $configuration['group'],
                'is_protected' => $configuration['is_protected'],
                'created_at' => $dateNow,
                'updated_at' => $dateNow
            ]);
        }
    }
}
