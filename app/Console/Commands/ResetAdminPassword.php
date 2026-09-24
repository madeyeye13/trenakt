<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * The "I lost admin access" recovery command. Finds a staff account by
 * email - or creates one if it doesn't exist - sets its password, and
 * makes sure it holds the super_admin role, all in one step, without
 * needing to hand-write Tinker commands while locked out.
 *
 * super_admin is both necessary and sufficient to restore access: the
 * admin console's only real gate (see EnsureUserIsAdmin and
 * Admin\LoginForm) is holding any role other than participant/business,
 * and AppServiceProvider gives super_admin a blanket Gate::before bypass -
 * so there's no need to touch permissions here, and no email-verification
 * check to satisfy either.
 *
 * Deliberately skips the Have I Been Pwned check RegisterForm's password
 * rule uses (->uncompromised()) - a recovery tool that depends on a
 * third-party API being reachable is a bad idea for exactly the moment
 * you're most likely to need it.
 *
 * Usage:
 *   php artisan admin:reset admin@example.com
 *     -> prompts for the new password (hidden input, never echoed or left
 *        in shell history)
 *   php artisan admin:reset admin@example.com "SomeStrongP@ssw0rd" --name="Jane Admin"
 *     -> non-interactive form; --name only matters when the email doesn't
 *        match an existing account, so this creates one
 */
class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset
        {email : The admin account\'s email address}
        {password? : New password - omit to be prompted for it instead}
        {--name=Admin : Display name - only used if this creates a brand new account}';

    protected $description = 'Create or recover a super_admin account by email: sets its password and makes sure it holds the super_admin role.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $password = $this->argument('password') ?: $this->secret('New password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'email'],
                'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        $isNew = ! $user;

        if ($isNew) {
            $user = User::create([
                'name' => (string) ($this->option('name') ?: 'Admin'),
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        } else {
            $user->forceFill(['password' => Hash::make($password)])->save();
        }

        // Guards against a weird or partially-seeded database (e.g. the
        // seeders never ran) rather than assuming the role is already
        // there - firstOrCreate mirrors exactly what RolesAndPermissionsSeeder
        // does for this same role.
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user->assignRole('super_admin');

        $this->info(($isNew ? 'Created' : 'Updated') . " super admin account: {$user->email}");
        $this->line('They can log in now at the admin console with the password just set.');

        return self::SUCCESS;
    }
}
