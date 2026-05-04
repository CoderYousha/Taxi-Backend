<?php

namespace App\Http\Controllers;

use App\Models\CarType;
use App\Http\Requests\StoreCarTypeRequest;
use App\Http\Requests\UpdateCarTypeRequest;
use App\Models\Driver;
use Illuminate\Http\Request;
use Laravel\Reverb\Loggers\Log;

class CarTypeController extends Controller
{
    /**
     * View a list of all car types     */
    public function index(Request $request)
    {
        $query = CarType::query();

        if ($request->has('name')) {
            $search = $request->name;
            $query->where('name', 'like', "%$search%");
        }


        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        //$carTypes = $query->paginate($request->get('per_page', 15));
        $carTypes = $query->get();

        return response()->json([
            'success' => true,
            'carTypes' => $carTypes,
            'message' => 'Data fetched successfully'
        ]);
    }



    public function show($id)
    {
        $carType = CarType::find($id);

        if (!$carType) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $carType,
            'message' => 'Data fetched successfully'
        ]);
    }



    /**
     *Add a new car type
     */
    public function store(StoreCarTypeRequest $request)
    {
        $carType = CarType::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $carType,
            'message' => 'Data added successfully'
        ]);
    }

    public function update(Request $request)
    {

        $carType = CarType::find($request->id);
        if (!$carType) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }

        if ($request->has('name')) {
            $carType->name = $request->name;
        }

        if ($request->has('timePrice')) {
            $carType->timePrice = $request->timePrice;
        }

        if ($request->has('KMPrice')) {
            $carType->KMPrice = $request->KMPrice;
        }

        if ($request->has('openPrice')) {
            $carType->openPrice = $request->openPrice;
        }

        $carType->save();

        return response()->json([
            'success' => true,
            'data' => $carType->fresh(),
            'message' => 'Data updated successfully'
        ]);
    }


    public function destroy($id)
    {
        $carType = CarType::find($id);

        if (!$carType) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }


        $carType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully'
        ]);
    }


    public function restore($id)
    {
        $carType = CarType::withTrashed()->find($id);

        if (!$carType) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }

        if (!$carType->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Data is not deleted'
            ], 400);
        }

        $carType->restore();

        return response()->json([
            'success' => true,
            'data' => $carType,
            'message' => 'Data restored successfully'
        ]);
    }


    public function forceDelete($id)
    {
        $carType = CarType::withTrashed()->find($id);

        if (!$carType) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }

        $checkDriver = Driver::where('transTypeId', $id)->first();
        if ($checkDriver != null) {
            return response()->json([
                'state' => false,
                'message' => 'لا يمكن حذف فئة يوجد سائقين بها'
            ], 400);
        }
        $carType->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Data deleted permanently'
        ]);
    }

    /**
     * Display only deleted car models     */
    public function trashed()
    {
        $carTypes = CarType::onlyTrashed()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $carTypes,
            'message' => 'Data fetched successfully'
        ]);
    }
}
