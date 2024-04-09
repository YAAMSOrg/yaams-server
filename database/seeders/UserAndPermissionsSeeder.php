<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'add aircraft']);
        Permission::create(['name' => 'edit aircraft']);

        // create roles and assign existing permissions

        // Normal permission for a new user
        $role1 = Role::create(['name' => 'Pilot']);

        // Airline Manager
        $role2 = Role::create(['name' => 'Manager']);
        $role2->givePermissionTo('add aircraft');
        $role2->givePermissionTo('edit aircraft');

        // Super-Admin, which will get all permissions
        // gets all permissions via Gate::before rule; see AuthServiceProvider
        $role3 = Role::create(['name' => 'Super-Admin']);
        
        // create demo users and api auth tokens for them
        $user = \App\Models\User::factory()->create([
            'name' => 'Homer Simpson',
            'email' => 'homer@test.com',
            'password' => Hash::make('start'),
            'homebase' => 'EDDF'
        ]);
        $user->assignRole($role1);
        $generatedToken = $user->createToken("homertoken")->plainTextToken;
        $this->command->info("Token for User " . $user->name . " is " . $generatedToken);

        $user = \App\Models\User::factory()->create([
            'name' => 'Max Mustermann',
            'email' => 'test@test.com',
            'password' => Hash::make('start'),
            'homebase' => 'EDDF'
        ]);
        $user->assignRole($role2);
        $generatedToken = $user->createToken("maxtoken")->plainTextToken;
        $this->command->info("Token for User " . $user->name . " is " . $generatedToken);

        $user = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('start'),
            'homebase' => 'EDDM'
        ]);
        $user->assignRole($role3);
        $generatedToken = $user->createToken("admintoken")->plainTextToken;
        $this->command->info("Token for User " . $user->name . " is " . $generatedToken);
    }
}
