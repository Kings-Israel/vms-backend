<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Unit;
use App\Models\User;
use App\Models\VisitorType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $buildingManager = Role::firstOrCreate(['name' => 'building_manager', 'guard_name' => 'web']);
        $tenant = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        $securityOfficer = Role::firstOrCreate(['name' => 'security_officer', 'guard_name' => 'web']);

        // Create sample building
        $building = Building::firstOrCreate(
            ['name' => 'Main Tower'],
            [
                'address' => '123 Moi Avenue',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'phone' => '+254700000000',
                'email' => 'info@maintower.co.ke',
            ]
        );

        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@vms.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'building_id' => $building->id,
                'is_active' => true,
            ]
        );
        $admin->assignRole($superAdmin);

        // Default visitor types
        $types = [
            ['name' => 'Guest', 'color' => '#3B82F6', 'description' => 'General guest visitor'],
            ['name' => 'Contractor', 'color' => '#F59E0B', 'description' => 'Contractor or vendor', 'requires_escort' => true],
            ['name' => 'Delivery', 'color' => '#10B981', 'description' => 'Delivery personnel'],
            ['name' => 'Government Official', 'color' => '#8B5CF6', 'description' => 'Government or regulatory official'],
            ['name' => 'Emergency', 'color' => '#EF4444', 'description' => 'Emergency service personnel'],
        ];

        foreach ($types as $type) {
            VisitorType::firstOrCreate(['name' => $type['name']], $type);
        }

        // Sample units
        $unitData = [
            ['name' => 'Ground Floor Lobby', 'floor' => 'G', 'unit_number' => 'G01', 'type' => 'commercial'],
            ['name' => 'Office 101', 'floor' => '1', 'unit_number' => '101', 'type' => 'office'],
            ['name' => 'Office 102', 'floor' => '1', 'unit_number' => '102', 'type' => 'office'],
            ['name' => 'Office 201', 'floor' => '2', 'unit_number' => '201', 'type' => 'office'],
        ];

        foreach ($unitData as $unit) {
            Unit::firstOrCreate(
                ['building_id' => $building->id, 'unit_number' => $unit['unit_number']],
                array_merge($unit, ['building_id' => $building->id])
            );
        }
    }
}
