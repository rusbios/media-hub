<?php

namespace MediaHub;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/database/migrations/2020_04_28_185232_create_files_table.php',
            __DIR__ . '/database/migrations/2020_04_28_185241_create_albums_table.php',
            __DIR__ . '/database/migrations/2020_04_28_190806_create_storage_table.php',
            __DIR__ . '/database/migrations/2020_04_28_190830_create_album_has_users_table.php',
            __DIR__ . '/database/migrations/2020_04_28_190851_create_album_has_fies_table.php',
        ]);

        $this->loadRoutesFrom(__DIR__ . '/router.php');
    }
}
