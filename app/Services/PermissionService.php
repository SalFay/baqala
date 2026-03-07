<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class PermissionService
{
    /**
     * Load all permissions from the Permissions directory
     */
    public function getAllPermissions(): array
    {
        $permissions = [];
        $permissionsPath = app_path('Permissions');

        if (!File::isDirectory($permissionsPath)) {
            return $permissions;
        }

        $files = File::allFiles($permissionsPath);

        foreach ($files as $file) {
            $permissionContent = include $file->getPathname();
            $fileName = str_replace('.php', '', $file->getFilename());

            $permissions[$fileName] = [
                'title' => $permissionContent['title'] ?? $fileName,
                'description' => $permissionContent['description'] ?? '',
                'permissions' => $permissionContent['permissions'] ?? [],
            ];
        }

        return $permissions;
    }

    /**
     * Get flat list of all permission keys
     */
    public function getAllPermissionKeys(): array
    {
        $keys = [];
        $permissions = $this->getAllPermissions();

        foreach ($permissions as $group) {
            foreach ($group['permissions'] ?? [] as $permission) {
                $keys[] = $permission['key'] ?? $permission;
            }
        }

        return $keys;
    }

    /**
     * Check if a permission key exists
     */
    public function permissionExists(string $key): bool
    {
        return in_array($key, $this->getAllPermissionKeys());
    }
}
