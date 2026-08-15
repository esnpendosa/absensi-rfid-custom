<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table): void {
            $table->id();
            $table->string('jenis', 20)->index();
            $table->string('nomor_surat', 100);
            $table->date('tanggal_surat')->index();
            $table->date('tanggal_diterima')->nullable()->index();
            $table->date('tanggal_dikirim')->nullable()->index();
            $table->string('asal_surat')->nullable();
            $table->string('tujuan_surat')->nullable();
            $table->string('perihal');
            $table->text('ringkasan')->nullable();
            $table->string('status', 30)->default('aktif')->index();
            $table->string('lampiran_path')->nullable();
            $table->string('lampiran_nama')->nullable();
            $table->string('lampiran_mime', 120)->nullable();
            $table->unsignedBigInteger('lampiran_size')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis', 'status', 'tanggal_surat'], 'surat_jenis_status_tanggal_idx');
        });

        $this->syncPermissions();
    }

    public function down(): void
    {
        $this->removePermissions();
        Schema::dropIfExists('surat');
    }

    private function syncPermissions(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        $guard = (string) config('auth.defaults.guard', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $viewPermission = Permission::findOrCreate('persuratan.view', $guard);
        $managePermission = Permission::findOrCreate('persuratan.manage', $guard);

        foreach (['super-admin', 'admin'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', $guard)
                ->first();

            if ($role) {
                $role->givePermissionTo([$viewPermission, $managePermission]);
            }
        }

        foreach (['kepsek', 'wakasek'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', $guard)
                ->first();

            if ($role) {
                $role->givePermissionTo($viewPermission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function removePermissions(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        $guard = (string) config('auth.defaults.guard', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['persuratan.manage', 'persuratan.view'] as $permissionName) {
            $permission = Permission::query()
                ->where('name', $permissionName)
                ->where('guard_name', $guard)
                ->first();

            if (!$permission) {
                continue;
            }

            foreach ($permission->roles as $role) {
                if ($role->guard_name === $guard && $role->hasPermissionTo($permission)) {
                    $role->revokePermissionTo($permission);
                }
            }

            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
