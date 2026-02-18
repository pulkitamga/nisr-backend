<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inventory Mutation Service Toggle
    |--------------------------------------------------------------------------
    |
    | When enabled, stock movement in migrated flows uses InventoryMutationService
    | as the single mutation path. Keep enabled in production after rollout.
    |
    */
    'mutation_service_enabled' => env('INVENTORY_MUTATION_SERVICE_ENABLED', true),
];

