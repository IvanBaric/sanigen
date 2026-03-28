<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use IvanBaric\Sanigen\Attributes\Generate;
use IvanBaric\Sanigen\Attributes\Sanitize;
use IvanBaric\Sanigen\Traits\HasGenerators;
use IvanBaric\Sanigen\Traits\HasSanitization;

#[Sanitize([
    'priority_field' => 'lower',
    'attr_only_field' => 'trim',
])]
class AttributeSanitizeModel extends Model
{
    use HasSanitization;

    protected $table = 'sanitizer_test_models';

    protected $fillable = [
        'priority_field',
        'attr_only_field',
        'config_only_field',
    ];

    protected array $sanitize = [
        'priority_field' => 'trim',
    ];
}

#[Generate([
    'slug' => 'slugify:title',
])]
class AttributeGenerateModel extends Model
{
    use HasGenerators;

    protected $table = 'test_models';

    protected $fillable = ['title', 'slug'];
}

#[Generate([
    'slug' => 'slugify:title',
])]
class GeneratePriorityModel extends Model
{
    use HasGenerators;

    protected $table = 'test_models';

    protected $fillable = ['title', 'slug'];

    protected array $generate = [
        'slug' => 'uuid',
    ];
}

test('class level sanitize attribute is supported and priority is property > attribute > config', function () {
    Config::set('sanigen.sanitize_defaults', [
        'priority_field' => 'upper',
        'config_only_field' => 'trim',
    ]);

    $model = new AttributeSanitizeModel;
    $model->priority_field = '  AbC  ';
    $model->attr_only_field = '  Value  ';
    $model->config_only_field = '  Config Value  ';
    $model->save();

    expect($model->priority_field)->toBe('AbC');
    expect($model->attr_only_field)->toBe('Value');
    expect($model->config_only_field)->toBe('Config Value');

    Config::set('sanigen.sanitize_defaults', []);
});

test('class level generate attribute is supported', function () {
    $model = AttributeGenerateModel::create([
        'title' => 'Generated from attribute',
    ]);

    expect($model->slug)->toBe('generated-from-attribute');
});

test('generate priority is property > attribute > config', function () {
    Config::set('sanigen.generate_defaults', [
        'slug' => 'slugify:title',
    ]);

    $model = GeneratePriorityModel::create([
        'title' => 'Priority Title',
    ]);

    expect($model->slug)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');

    Config::set('sanigen.generate_defaults', []);
});
