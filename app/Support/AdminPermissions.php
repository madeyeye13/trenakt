<?php

namespace App\Support;

/**
 * The single source of truth for every permission the admin panel checks.
 * Nothing about WHO holds a permission lives here - that's entirely
 * database-driven via roles a super admin creates on the Roles page. This
 * class only names the fixed set of actions the app actually gates (one
 * entry per admin section/route) and how to label/group them in the Roles
 * & Permissions UI, mirroring the admin sidebar's own grouping.
 *
 * Adding a new gated admin section later means adding one line here (which
 * both the seeder and the Roles page read from) rather than hardcoding a
 * role or permission name anywhere else in the app.
 *
 * The dashboard itself isn't in this list - it's every staff member's
 * landing page after login (gated only by the 'admin' middleware, i.e. by
 * holding any staff role at all), not something a role can be denied.
 */
class AdminPermissions
{
    /**
     * @return array<string, array{label: string, group: string}>
     */
    public static function catalog(): array
    {
        return [
            'manage-campaigns' => ['label' => 'Review & manage campaigns', 'group' => 'Campaigns'],
            'manage-categories' => ['label' => 'Manage categories', 'group' => 'Campaigns'],

            'verify-submissions' => ['label' => 'Verify task submissions', 'group' => 'Earning'],
            'manage-rejection-reasons' => ['label' => 'Manage rejection reasons', 'group' => 'Earning'],
            'manage-withdrawals' => ['label' => 'Manage withdrawals', 'group' => 'Earning'],
            'manage-activation-payments' => ['label' => 'Manage activation payments', 'group' => 'Earning'],
            'manage-wallet-fundings' => ['label' => 'View business wallet fundings', 'group' => 'Earning'],

            'manage-countries' => ['label' => 'Manage countries', 'group' => 'Platform'],
            'manage-settings' => ['label' => 'Manage platform settings', 'group' => 'Platform'],

            'manage-users' => ['label' => 'View & remove registered users', 'group' => 'Administration'],
            'manage-staff' => ['label' => 'Create & manage staff', 'group' => 'Administration'],
            'manage-roles' => ['label' => 'Create & manage roles', 'group' => 'Administration'],
        ];
    }

    /**
     * @return array<string, array<string, array{label: string, group: string}>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (static::catalog() as $name => $meta) {
            $grouped[$meta['group']][$name] = $meta;
        }

        return $grouped;
    }

    public static function names(): array
    {
        return array_keys(static::catalog());
    }

    public static function label(string $name): string
    {
        return static::catalog()[$name]['label'] ?? $name;
    }
}
