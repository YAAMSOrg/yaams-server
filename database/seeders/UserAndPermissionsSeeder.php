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
        
        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'Pilot']);
        $role2 = Role::create(['name' => 'Manager']);
        $role2->givePermissionTo('add aircraft');
        $role3 = Role::create(['name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

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
