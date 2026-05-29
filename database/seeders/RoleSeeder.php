<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Crear roles y permisos del sistema.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Mesas
            'mesas.ver',
            'mesas.crear',
            'mesas.editar',
            'mesas.eliminar',
            'mesas.mover',         // Drag & drop en mapa
            'mesas.ver_mapa',

            // Reservas
            'reservas.ver_todas',
            'reservas.ver_propias',
            'reservas.crear',
            'reservas.editar',
            'reservas.cancelar',
            'reservas.eliminar',
            'reservas.toggle_pago',
            'reservas.factura',

            // Dashboard
            'dashboard.ver',

            // Zonas
            'zonas.gestionar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Rol Administrador — todos los permisos
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Rol Cliente — permisos limitados
        $clienteRole = Role::firstOrCreate(['name' => 'cliente']);
        $clienteRole->givePermissionTo([
            'mesas.ver_mapa',
            'reservas.ver_propias',
            'reservas.crear',
            'reservas.cancelar',
        ]);
    }
}
