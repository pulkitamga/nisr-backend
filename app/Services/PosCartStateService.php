<?php

namespace App\Services;

use App\Models\PosCartState;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PosCartStateService
{
    public function resolveActor(?string $actorType = null, ?int $actorId = null): array
    {
        if (!is_null($actorType) && !is_null($actorId)) {
            return ['actor_type' => $actorType, 'actor_id' => max(0, (int)$actorId)];
        }

        if (auth('admin')->check()) {
            return ['actor_type' => 'admin', 'actor_id' => (int)auth('admin')->id()];
        }

        if (auth('seller')->check()) {
            return ['actor_type' => 'seller', 'actor_id' => (int)auth('seller')->id()];
        }

        return ['actor_type' => 'system', 'actor_id' => 0];
    }

    public function ensureCart(
        string $cartId,
        int $branchId,
        ?string $actorType = null,
        ?int $actorId = null
    ): PosCartState {
        $context = $this->resolveActor($actorType, $actorId);
        $cartId = trim($cartId);
        $branchId = max(1, (int)$branchId);

        $state = PosCartState::query()->where('cart_id', $cartId)->first();
        if (!$state) {
            $state = PosCartState::query()->create([
                'cart_id' => $cartId,
                'actor_type' => $context['actor_type'],
                'actor_id' => $context['actor_id'],
                'branch_id' => $branchId,
                'payload' => [],
                'last_activity_at' => now(),
            ]);
            return $state;
        }

        $this->guardCartOwnership($state, $branchId, $context['actor_type'], $context['actor_id']);
        return $state;
    }

    public function assertCart(
        string $cartId,
        int $branchId,
        ?string $actorType = null,
        ?int $actorId = null
    ): PosCartState {
        $context = $this->resolveActor($actorType, $actorId);
        $state = PosCartState::query()->where('cart_id', trim($cartId))->first();
        if (!$state) {
            throw ValidationException::withMessages([
                'cart_id' => [translate('invalid_request')],
            ]);
        }

        $this->guardCartOwnership($state, max(1, (int)$branchId), $context['actor_type'], $context['actor_id']);
        return $state;
    }

    public function getPayload(
        string $cartId,
        int $branchId,
        ?string $actorType = null,
        ?int $actorId = null
    ): array {
        $state = $this->assertCart($cartId, $branchId, $actorType, $actorId);
        return is_array($state->payload) ? $state->payload : [];
    }

    public function putPayload(
        string $cartId,
        int $branchId,
        array $payload,
        ?string $actorType = null,
        ?int $actorId = null
    ): PosCartState {
        $state = $this->ensureCart($cartId, $branchId, $actorType, $actorId);
        $state->payload = $payload;
        $state->last_activity_at = Carbon::now();
        $state->save();

        return $state;
    }

    public function deleteCart(
        string $cartId,
        int $branchId,
        ?string $actorType = null,
        ?int $actorId = null
    ): void {
        $state = $this->assertCart($cartId, $branchId, $actorType, $actorId);
        $state->delete();
    }

    public function clearBranchCarts(
        int $branchId,
        ?string $actorType = null,
        ?int $actorId = null
    ): void {
        $context = $this->resolveActor($actorType, $actorId);
        PosCartState::query()
            ->where('branch_id', max(1, (int)$branchId))
            ->where('actor_type', $context['actor_type'])
            ->where('actor_id', $context['actor_id'])
            ->delete();
    }

    public function listCartIdsByBranch(
        int $branchId,
        ?string $actorType = null,
        ?int $actorId = null,
        bool $nonEmptyOnly = false
    ): array {
        $context = $this->resolveActor($actorType, $actorId);
        $query = PosCartState::query()
            ->where('branch_id', max(1, (int)$branchId))
            ->where('actor_type', $context['actor_type'])
            ->where('actor_id', $context['actor_id'])
            ->orderByDesc('updated_at');

        if (!$nonEmptyOnly) {
            return $query->pluck('cart_id')->all();
        }

        return $query->get(['cart_id', 'payload'])
            ->filter(function (PosCartState $state) {
                $payload = is_array($state->payload) ? $state->payload : [];
                foreach ($payload as $item) {
                    if (is_array($item) && isset($item['id'])) {
                        return true;
                    }
                }
                return false;
            })
            ->pluck('cart_id')
            ->values()
            ->all();
    }

    public function touch(
        string $cartId,
        int $branchId,
        ?string $actorType = null,
        ?int $actorId = null
    ): void {
        $state = $this->ensureCart($cartId, $branchId, $actorType, $actorId);
        $state->last_activity_at = now();
        $state->save();
    }

    private function guardCartOwnership(
        PosCartState $state,
        int $branchId,
        string $actorType,
        int $actorId
    ): void {
        if (
            (string)$state->actor_type !== $actorType
            || (int)$state->actor_id !== $actorId
            || (int)$state->branch_id !== $branchId
        ) {
            throw ValidationException::withMessages([
                'cart_id' => [translate('invalid_request')],
            ]);
        }
    }
}

