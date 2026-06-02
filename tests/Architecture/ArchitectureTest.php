<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

arch('controllers have Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('models extend Model')
    ->expect('App\Models')
    ->toExtend(Model::class)
    ->ignoring('App\Models\Concerns')
    ->ignoring('App\Models\Scopes')
    ->ignoring('App\Models\Permission');

arch('no debugging functions in production code')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);

arch('form requests extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend(FormRequest::class);

arch('policies have Policy suffix')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

arch('enums are backed')
    ->expect('App\Enums')
    ->toBeEnums();

arch('services are not used in models')
    ->expect('App\Services')
    ->not->toBeUsedIn('App\Models');
