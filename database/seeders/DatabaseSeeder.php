<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Owner',
//            'email' => 'admin@app.com',
//            'password' => password_hash('password', PASSWORD_DEFAULT),
//            'role' => 'owner',
//        ]);

        DB::raw('SET time_zone=\'+00:00\'');

        // Admin
        $this->command->warn(PHP_EOL . 'Creating admin user...');
        $user = $this->withProgressBar(1, fn () => User::factory(1)->create([
            'name' => 'Business Owner',
            'email' => 'admin@app.com',
            'role' => 'ADMIN',
        ]));
        $this->command->info('Admin user created.');


        // Customer Contact
        $this->command->warn(PHP_EOL . 'Creating customer contact...');
        $customer = $this->withProgressBar(1, fn () => Contact::factory(1)->create([
            'name' => 'Walk-In Customer',
            'email' => 'default@app.com',
            'phone' => '00000000000',
            'address' => '',
            'type' => 'customer',
        ]));
        $this->command->info('Admin user created.');

    }

    protected function withProgressBar(int $amount, Closure $createCollectionOfOne): Collection
    {
        $progressBar = new ProgressBar($this->command->getOutput(), $amount);

        $progressBar->start();

        $items = new Collection();

        foreach (range(1, $amount) as $i) {
            $items = $items->merge(
                $createCollectionOfOne()
            );
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->command->getOutput()->writeln('');

        return $items;
    }
}
