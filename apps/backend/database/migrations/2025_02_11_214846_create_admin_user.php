<?php

use App\Enums\ClusterType;
use App\Enums\Constants;
use App\Models\Auth\Role;
use App\Models\User;
use App\Services\ClusterService;
use Illuminate\Database\Migrations\Migration;
use PHPUnit\TextUI\Configuration\Constant;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = Role::firstOrCreate([
            'name' => Constants::ROLE_OWNER,
            'guard_name' => 'api',
        ]);

        $type = app(ClusterService::class)->type();
        if ($type == ClusterType::MASTER) {
            $user = User::Create([
                'name' => 'admin',
                'username' => 'admin',
                "password" => Hash::make("123456"),
            ]);

            $user->assignRole($role);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where("username", "admin")->delete();
        Role::where("name", Constants::ROLE_OWNER)->delete();
    }
};
