<?php

namespace SpondonIt\LmsService\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\RolePermission\Entities\Role;
use Modules\Setting\Model\BusinessSetting;
use Modules\Setting\Model\GeneralSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class InitRepository
{

    public function init()
    {
        config([
            'app.item' => '30626608',
            'spondonit.module_manager_model' => \Modules\ModuleManager\Entities\InfixModuleManager::class,
            'spondonit.module_manager_table' => 'infix_module_managers',

            'spondonit.settings_model' => \Modules\Setting\Model\GeneralSetting::class,
            'spondonit.module_model' => \Nwidart\Modules\Facades\Module::class,

            'spondonit.user_model' => \App\User::class,
            'spondonit.settings_table' => 'general_settings',
            'spondonit.database_file' => 'infixlms.sql',
        ]);

    }

    public function config()
    {
        try {

            DB::connection()->getPdo();

            if (Schema::hasTable('roles')) {

                app()->singleton('permission_list', function () {
                    return Cache::rememberForever('PermissionList', function () {
                        return Role::with(['permissions' => function ($query) {
                            $query->select('route', 'module_id', 'parent_id', 'role_permission.role_id');
                        }])->get(['id', 'name']);
                    });
                });

                app()->singleton('getSetting', function () {
                    $path = Storage::path('settings.json');
                    if (!Storage::has('settings.json')) {
                        file_put_contents($path, GeneralSetting::first());
                    }
                    $data = json_decode(file_get_contents($path), true);
                    $settings = new \stdClass;
                    foreach (array_keys($data) as $property) {
                        $settings->{$property} = $data[$property];
                    }
                    $settings->site_name = $data['site_title'];
                    $settings->company_name = $data['site_title'];
                    return $settings;
                });
            }
        } catch (\Exception $exception) {
            return false;
        }
    }
}
