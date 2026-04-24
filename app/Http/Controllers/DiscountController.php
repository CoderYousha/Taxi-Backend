<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\UsedDiscount;
use App\Models\RequestModel;
use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{
    /**
     * View a list of all discounts
     */
    public function index(Request $request)
    {
        $query = Discount::query();

        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->has('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        $discounts = $query->paginate($request->get('per_page', 15));

        foreach ($discounts as $discount) {
            $discount->usage_count = UsedDiscount::usageCount($discount->id);
        }

        return response()->json([
            'success' => true,
            'data' => $discounts,
            'message' => 'Data fetched successfully'
        ]);
    }

    /**
     * Show a specific discount
     */
    public function show($id)
    {
        $discount = Discount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found'
            ], 404);
        }

        $discount->usage_count = UsedDiscount::usageCount($discount->id);

        return response()->json([
            'success' => true,
            'data' => $discount,
            'message' => 'Data fetched successfully'
        ]);
    }

    /**
     * Search for a discount using the code
     */
    public function findByCode($code)
    {
        $discount = Discount::where('code', $code)->first();

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount code not found'
            ], 404);
        }

        $discount->usage_count = UsedDiscount::usageCount($discount->id);

        return response()->json([
            'success' => true,
            'data' => $discount,
            'message' => 'Discount found successfully'
        ]);
    }

    /**
     * Add a new discount (for admins)
     */
    public function store(StoreDiscountRequest $request)
    {
        $discount = Discount::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $discount,
            'message' => 'Discount code added successfully'
        ], 201);
    }

    /**
     * Update discount data
     */
    public function update(UpdateDiscountRequest $request, $id)
    {
        $discount = Discount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found'
            ], 404);
        }
        if ($request->has('code')) {
            $discount->code = $request->code;
        }
        if ($request->has('min_amount')) {
            $discount->amount = $request->min_amount;
        }
        if ($request->has('type')) {
            $discount->type = $request->type;
        }
        $discount->save();
        return response()->json([
            'success' => true,
            'data' => $discount->fresh(),
            'message' => 'Discount code updated successfully'
        ]);
    }

    /**
     * Validate and apply discount code (for users)
     */
    public function validateAndApply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'userId' => 'required|integer|exists:users,id',
            'originalPrice' => 'required|numeric|min:0'
        ]);

        // Search for the code
        $discount = Discount::where('code', $request->code)->first();

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid discount code',
                'code' => 'INVALID_CODE'
            ], 404);
        }

        // Check if user has already used this code
        $isUsed = UsedDiscount::isUsedByUser($request->userId, $discount->id);

        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this code',
                'code' => 'ALREADY_USED'
            ], 400);
        }

        // Calculate new price after discount
        $originalPrice = $request->originalPrice;
        $discountAmount = 0;
        $newPrice = $originalPrice;

        if ($discount->type === Discount::TYPE_PERCENTAGE) {
            $discountAmount = ($originalPrice * $discount->amount) / 100;
            $newPrice = $originalPrice - $discountAmount;
        } else {
            // Fixed type
            $discountAmount = min($discount->amount, $originalPrice);
            $newPrice = $originalPrice - $discountAmount;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'discount' => $discount,
                'original_price' => $originalPrice,
                'discount_amount' => round($discountAmount, 2),
                'new_price' => round($newPrice, 2),
                'saved_amount' => round($discountAmount, 2)
            ],
            'message' => 'Discount code applied successfully'
        ]);
    }

    /**
     * Confirm discount code usage after trip completion
     */
    public function confirmUsage(Request $request)
    {
        $request->validate([
            'requestId' => 'required|integer|exists:requests,id',
            'userId' => 'required|integer|exists:users,id',
            'discountId' => 'required|integer|exists:discounts,id'
        ]);

        $existing = UsedDiscount::where('requestId', $request->requestId)->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A discount code has already been used for this trip'
            ], 400);
        }

        // Check if user has already used this code
        $isUsed = UsedDiscount::isUsedByUser($request->userId, $request->discountId);

        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this code'
            ], 400);
        }

        // Register code usage
        $usedDiscount = UsedDiscount::create([
            'requestId' => $request->requestId,
            'userId' => $request->userId,
            'discountId' => $request->discountId
        ]);

        // Update the trip record with discount
        $rideRequest = RequestModel::find($request->requestId);
        if ($rideRequest && $rideRequest->history) {
            $rideRequest->history->descountId = $request->discountId;
            $rideRequest->history->save();
        }

        return response()->json([
            'success' => true,
            'data' => $usedDiscount,
            'message' => 'Discount code successfully applied to the trip'
        ]);
    }

    /**
     * Delete a discount (Soft Delete)
     */
    public function destroy($id)
    {
        $discount = Discount::find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found'
            ], 404);
        }

        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully'
        ]);
    }

    /**
     * Restore a deleted discount
     */
    public function restore($id)
    {
        $discount = Discount::withTrashed()->find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found'
            ], 404);
        }

        if (!$discount->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Discount is not deleted'
            ], 400);
        }

        $discount->restore();

        return response()->json([
            'success' => true,
            'data' => $discount,
            'message' => 'Discount restored successfully'
        ]);
    }

    /**
     * Permanently delete a discount
     */
    public function forceDelete($id)
    {
        $discount = Discount::withTrashed()->find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found'
            ], 404);
        }

        $discount->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted permanently'
        ]);
    }

    /**
     * View only deleted discounts
     */
    public function trashed()
    {
        $discounts = Discount::onlyTrashed()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $discounts,
            'message' => 'Data fetched successfully'
        ]);
    }

    /**
     * Discount usage statistics
     */
    public function statistics()
    {
        $totalDiscounts = Discount::count();
        $totalUsed = UsedDiscount::count();

        $mostUsedDiscount = Discount::withCount('usedDiscounts')
            ->orderBy('used_discounts_count', 'desc')
            ->first();

        $totalSavedAmount = DB::table('usedDiscounts as ud')
            ->join('discounts as d', 'ud.discountId', '=', 'd.id')
            ->join('requestHistories as rh', 'ud.requestId', '=', 'rh.requestId')
            ->sum(DB::raw('CASE
                WHEN d.type = "Percentage" THEN (rh.finalCost * d.amount / 100)
                ELSE d.amount
            END'));

        return response()->json([
            'success' => true,
            'data' => [
                'total_discounts' => $totalDiscounts,
                'total_used' => $totalUsed,
                'usage_rate' => $totalDiscounts > 0 ? round(($totalUsed / $totalDiscounts) * 100, 2) : 0,
                'most_used_discount' => $mostUsedDiscount,
                'total_saved_amount' => round($totalSavedAmount, 2)
            ],
            'message' => 'Statistics fetched successfully'
        ]);
    }
}
