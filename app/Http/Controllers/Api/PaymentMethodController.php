<?php

namespace App\Http\Controllers\Api;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMethodController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $branch = $request->user()->branch;
        if (!$branch) {
            return $this->error('No branch assigned', 404);
        }

        $methods = PaymentMethod::where('branch_id', $branch->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($methods);
    }

    public function active(Request $request): JsonResponse
    {
        $branch = $request->user()->branch;
        if (!$branch) {
            return $this->error('No branch assigned', 404);
        }

        $methods = PaymentMethod::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($methods);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $branch = $request->user()->branch;
        if (!$branch) {
            return $this->error('No branch assigned', 404);
        }

        $slug = Str::slug($validated['name']);

        $exists = PaymentMethod::where('branch_id', $branch->id)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            return $this->error('A payment method with this name already exists', 422);
        }

        $maxSort = PaymentMethod::where('branch_id', $branch->id)->max('sort_order');

        $method = PaymentMethod::create([
            'branch_id' => $branch->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'is_active' => true,
            'sort_order' => ($maxSort ?? 0) + 1,
        ]);

        return $this->success($method, 'Payment method created');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $branch = $request->user()->branch;
        if (!$branch || $paymentMethod->branch_id !== $branch->id) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);

            $exists = PaymentMethod::where('branch_id', $branch->id)
                ->where('slug', $validated['slug'])
                ->where('id', '!=', $paymentMethod->id)
                ->exists();

            if ($exists) {
                return $this->error('A payment method with this name already exists', 422);
            }
        }

        $paymentMethod->update($validated);

        return $this->success($paymentMethod, 'Payment method updated');
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $branch = $request->user()->branch;
        if (!$branch || $paymentMethod->branch_id !== $branch->id) {
            return $this->error('Unauthorized', 403);
        }

        $hasPayments = \App\Models\Payment::where('payment_method', $paymentMethod->slug)
            ->where('branch_id', $branch->id)
            ->exists();

        if ($hasPayments) {
            return $this->error('Cannot delete — this method has existing payments. Deactivate it instead.', 422);
        }

        $paymentMethod->delete();

        return $this->success(null, 'Payment method deleted');
    }
}
