<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class WebsiteDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->command->info('Starting Website module database seeding...');

        // Seed in order of dependencies
        $this->call([
            BlogCategorySeeder::class,
            WebsitePageSeeder::class,
            BlogPostSeeder::class,
            EventSeeder::class,
            StaffMemberSeeder::class,
            GallerySeeder::class,
        ]);

        $this->command->info('Website module database seeding completed successfully!');
    }
}
