<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\PerformanceTestModel;

test('resanitize command handles larger batches with modern aliases', function () {
    $rows = [];

    for ($i = 0; $i < 300; $i++) {
        $rows[] = [
            'trim_field' => '  Trim me  ',
            'squish_field' => "Many     spaces   {$i}",
            'strip_scripts_field' => '<script>alert(1)</script>safe',
            'text_plain_field' => '<script>alert(1)</script>Hello   World',
            'email_field' => ' USER'.$i.'@EXAMPLE.COM ',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    DB::table('performance_test_models')->insert($rows);

    Artisan::call('sanigen:resanitize', [
        'model' => PerformanceTestModel::class,
        '--chunk' => 75,
        '--force' => true,
    ]);

    $first = PerformanceTestModel::query()->firstOrFail();

    expect($first->trim_field)->toBe('Trim me');
    expect($first->squish_field)->toBe('Many spaces 0');
    expect($first->strip_scripts_field)->toBe('safe');
    expect($first->text_plain_field)->toBe('Hello World');
    expect($first->email_field)->toBe('user0@example.com');
});
