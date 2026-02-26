<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminPermissionRegistry
{
    private const GENERAL_GROUP = 'general';

    private const VERB_TOKENS = [
        'access',
        'add',
        'approve',
        'assign',
        'attach',
        'cancel',
        'close',
        'complete',
        'conduct',
        'connect',
        'convert',
        'create',
        'decline',
        'delete',
        'diagnose',
        'disable',
        'disqualify',
        'dispatch',
        'edit',
        'enable',
        'export',
        'get',
        'ignore',
        'import',
        'issue',
        'link',
        'list',
        'manage',
        'manual',
        'mark',
        'payment',
        'read',
        'receive',
        'reject',
        'remove',
        'repair',
        'reply',
        'request',
        'resolve',
        'resume',
        'review',
        'schedule',
        'screen',
        'send',
        'show',
        'start',
        'status',
        'submit',
        'sync',
        'toggle',
        'triage',
        'update',
        'upsert',
        'view',
    ];

    public static function guard(): string
    {
        return (string)config('permissions_admin.guard', 'admin');
    }

    public static function superAdminRole(): string
    {
        return (string)config('permissions_admin.super_admin_role', 'Super Admin');
    }

    public static function moduleAliases(): array
    {
        $aliases = config('permissions_admin.module_aliases', []);
        return is_array($aliases) ? $aliases : [];
    }

    public static function modules(): array
    {
        $modules = config('permissions_admin.modules', []);
        return is_array($modules) ? $modules : [];
    }

    public static function all(): array
    {
        $all = [];
        foreach (self::modules() as $module => $actions) {
            if (!is_array($actions)) {
                continue;
            }
            foreach ($actions as $action) {
                $all[] = sprintf('%s.%s', $module, $action);
            }
        }

        $extra = config('permissions_admin.extra_permissions', []);
        if (is_array($extra)) {
            foreach ($extra as $permission) {
                if (is_string($permission) && $permission !== '') {
                    $all[] = $permission;
                }
            }
        }

        $all = array_values(array_unique($all));
        sort($all);

        return $all;
    }

    public static function has(string $permission): bool
    {
        static $lookup = null;
        if ($lookup === null) {
            $lookup = array_fill_keys(self::all(), true);
        }

        return isset($lookup[$permission]);
    }

    public static function fromModuleAction(string $module, ?string $action = null): ?string
    {
        $module = trim($module);
        if ($module === '') {
            return null;
        }

        if ($action === null && str_contains($module, ',')) {
            [$module, $action] = array_pad(array_map('trim', explode(',', $module, 2)), 2, null);
        }

        $module = self::moduleAliases()[$module] ?? $module;
        $action = $action !== null ? trim($action) : 'access';
        if ($action === '') {
            $action = 'access';
        }

        $permission = sprintf('%s.%s', $module, $action);
        if (self::has($permission)) {
            return $permission;
        }

        if ($action === 'access') {
            $readFallback = sprintf('%s.read', $module);
            if (self::has($readFallback)) {
                return $readFallback;
            }
        }

        return null;
    }

    public static function groupedPermissions(): array
    {
        $grouped = [];
        foreach (self::modules() as $module => $actions) {
            if (!is_array($actions)) {
                continue;
            }
            $grouped[$module] = array_map(fn(string $action) => sprintf('%s.%s', $module, $action), $actions);
        }

        $extra = config('permissions_admin.extra_permissions', []);
        if (is_array($extra)) {
            foreach ($extra as $permission) {
                if (!is_string($permission) || $permission === '' || !str_contains($permission, '.')) {
                    continue;
                }
                [$module] = explode('.', $permission, 2);
                $grouped[$module] ??= [];
                $grouped[$module][] = $permission;
            }
        }

        foreach ($grouped as $module => $permissions) {
            $permissions = array_values(array_unique($permissions));
            sort($permissions);
            $grouped[$module] = $permissions;
        }
        ksort($grouped);

        return $grouped;
    }

    public static function groupedPermissionsBySection(): array
    {
        $modules = self::groupedPermissions();
        $sectioned = [];

        foreach ($modules as $module => $permissions) {
            $groupMap = [];
            foreach ($permissions as $permission) {
                if (!is_string($permission) || $permission === '') {
                    continue;
                }
                $action = str_contains($permission, '.') ? explode('.', $permission, 2)[1] : $permission;
                $group = self::groupKeyFromAction((string)$action);
                $groupMap[$group] ??= [];
                $groupMap[$group][] = $permission;
            }

            foreach ($groupMap as $group => $items) {
                $items = array_values(array_unique($items));
                sort($items);
                $groupMap[$group] = $items;
            }

            if (isset($groupMap[self::GENERAL_GROUP])) {
                $general = [self::GENERAL_GROUP => $groupMap[self::GENERAL_GROUP]];
                unset($groupMap[self::GENERAL_GROUP]);
                ksort($groupMap);
                $groupMap = $general + $groupMap;
            } else {
                ksort($groupMap);
            }

            $sectioned[$module] = $groupMap;
        }

        return $sectioned;
    }

    public static function moduleDisplayName(string $module): string
    {
        $module = trim($module);
        if ($module === '') {
            return '';
        }

        $module = self::moduleAliases()[$module] ?? $module;
        static $cache = [];
        $cacheKey = app()->getLocale() . '|' . $module;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $fallback = Str::headline(str_replace('_', ' ', $module));

        $cache[$cacheKey] = self::lang(
            key: sprintf('permission-hints.modules.%s', $module),
            fallback: $fallback
        );

        return $cache[$cacheKey];
    }

    public static function groupDisplayName(string $group): string
    {
        $group = trim($group);
        if ($group === '') {
            return '';
        }

        static $cache = [];
        $cacheKey = app()->getLocale() . '|' . $group;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        if ($group === self::GENERAL_GROUP) {
            $cache[$cacheKey] = self::lang('permission-hints.labels.general', fallback: Str::headline($group));
            return $cache[$cacheKey];
        }

        $cache[$cacheKey] = self::tokensToLabel(self::splitTokens($group));
        return $cache[$cacheKey];
    }

    public static function permissionDisplayName(string $permission): string
    {
        static $cache = [];
        $cacheKey = app()->getLocale() . '|' . $permission;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        [$module, $action] = self::splitPermission($permission);
        if ($action === '') {
            $cache[$cacheKey] = self::tokensToLabel(self::splitTokens($permission));
            return $cache[$cacheKey];
        }

        $directLabel = self::lang(
            key: sprintf('permission-hints.permissions.%s', $permission),
            fallback: ''
        );

        if ($directLabel !== '') {
            $cache[$cacheKey] = $directLabel;
            return $cache[$cacheKey];
        }

        // Keep action label short in cards while module context is visible in the tab header.
        $cache[$cacheKey] = self::tokensToLabel(self::splitTokens($action));
        return $cache[$cacheKey];
    }

    public static function permissionHint(string $permission): string
    {
        static $cache = [];
        $cacheKey = app()->getLocale() . '|' . $permission;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        [$module, $action] = self::splitPermission($permission);
        $moduleLabel = self::moduleDisplayName($module !== '' ? $module : $permission);
        $actionLabel = self::actionInstructionLabel($action !== '' ? $action : $permission);

        $cache[$cacheKey] = self::lang(
            key: 'permission-hints.templates.permission_hint',
            replace: ['action' => $actionLabel, 'module' => $moduleLabel],
            fallback: sprintf('Allows access to %s in %s.', $actionLabel, $moduleLabel)
        );

        return $cache[$cacheKey];
    }

    public static function moduleHint(string $module): string
    {
        $moduleLabel = self::moduleDisplayName($module);

        return self::lang(
            key: 'permission-hints.templates.module_toggle_hint',
            replace: ['module' => $moduleLabel],
            fallback: sprintf('Select or clear all permissions in %s.', $moduleLabel)
        );
    }

    public static function groupHint(string $module, string $group): string
    {
        $moduleLabel = self::moduleDisplayName($module);
        $groupLabel = self::groupDisplayName($group);

        return self::lang(
            key: 'permission-hints.templates.group_toggle_hint',
            replace: ['group' => $groupLabel, 'module' => $moduleLabel],
            fallback: sprintf('Select or clear all %s permissions in %s.', $groupLabel, $moduleLabel)
        );
    }

    private static function groupKeyFromAction(string $action): string
    {
        $action = trim($action);
        if ($action === '') {
            return self::GENERAL_GROUP;
        }

        $tokens = array_values(array_filter(explode('_', $action), static fn(string $token) => $token !== ''));
        if (count($tokens) === 0) {
            return self::GENERAL_GROUP;
        }

        $first = strtolower($tokens[0]);
        if (count($tokens) === 1) {
            return in_array($first, self::VERB_TOKENS, true) ? self::GENERAL_GROUP : $first;
        }

        $second = strtolower($tokens[1]);
        if (in_array($first, self::VERB_TOKENS, true)) {
            $groupParts = [];
            for ($i = 1; $i < count($tokens); $i++) {
                $token = strtolower($tokens[$i]);
                if ($token === 'new') {
                    continue;
                }

                $groupParts[] = $token;
                if (count($groupParts) === 3) {
                    break;
                }
            }

            if (count($groupParts) === 0) {
                return self::GENERAL_GROUP;
            }

            return implode('_', $groupParts);
        }

        $groupParts = [$first];
        if (!in_array($second, self::VERB_TOKENS, true)) {
            $groupParts[] = $second;
        }

        return implode('_', $groupParts);
    }

    private static function splitPermission(string $permission): array
    {
        $permission = trim($permission);
        if ($permission === '') {
            return ['', ''];
        }

        if (!str_contains($permission, '.')) {
            return [$permission, ''];
        }

        return array_pad(explode('.', $permission, 2), 2, '');
    }

    private static function splitTokens(string $value): array
    {
        return array_values(array_filter(
            preg_split('/[_.]+/', strtolower(trim($value))),
            static fn(string $token): bool => $token !== ''
        ));
    }

    private static function tokensToLabel(array $tokens): string
    {
        if (count($tokens) === 0) {
            return '';
        }

        $labels = [];
        foreach ($tokens as $token) {
            $labels[] = self::tokenLabel($token);
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', $labels)));
    }

    private static function actionInstructionLabel(string $action): string
    {
        $tokens = self::splitTokens($action);
        if (count($tokens) === 0) {
            return '';
        }

        $verbIndex = self::detectVerbIndex($tokens);
        if ($verbIndex === null) {
            return self::tokensToLabel($tokens);
        }

        $verbToken = $tokens[$verbIndex];
        $verbLabel = self::verbLabel($verbToken);

        unset($tokens[$verbIndex]);
        $subjectTokens = array_values($tokens);
        $subjectLabel = self::tokensToLabel($subjectTokens);

        if ($subjectLabel === '') {
            return $verbLabel;
        }

        return self::lang(
            key: 'permission-hints.templates.action_with_subject',
            replace: ['verb' => $verbLabel, 'subject' => $subjectLabel],
            fallback: trim($verbLabel . ' ' . $subjectLabel)
        );
    }

    private static function detectVerbIndex(array $tokens): ?int
    {
        foreach ($tokens as $index => $token) {
            if (in_array($token, self::VERB_TOKENS, true)) {
                return $index;
            }
        }

        if (count($tokens) > 1) {
            $lastIndex = count($tokens) - 1;
            if (in_array($tokens[$lastIndex], self::VERB_TOKENS, true)) {
                return $lastIndex;
            }
        }

        return null;
    }

    private static function verbLabel(string $verb): string
    {
        $fallback = Str::headline($verb);

        return self::lang(
            key: sprintf('permission-hints.verbs.%s', $verb),
            fallback: $fallback
        );
    }

    private static function tokenLabel(string $token): string
    {
        $fallback = Str::headline(str_replace('_', ' ', $token));

        return self::lang(
            key: sprintf('permission-hints.tokens.%s', $token),
            fallback: $fallback
        );
    }

    private static function lang(string $key, array $replace = [], string $fallback = ''): string
    {
        $value = __($key, $replace);
        if ($value === $key) {
            return $fallback;
        }

        return $value;
    }
}
