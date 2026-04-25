<?php

namespace App\Http\Controllers;
use Illuminate\Http\Response;
use App\Http\Requests\DriverActiveRequest;
use App\Http\Requests\DriverUpdateLocation;
use App\Models\Driver;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\CarType;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{

    public function active(Request $request)
    {
        $userId = $request->user()->id;
        return response()->json([
            'state' => true,
            'channel' => 'private-driver.' . Driver::where('userId', $userId)->value('id')
        ]);
    }
    /**
     * View list of all drivers     */
    public function index(Request $request)
    {
        $query = Driver::query();
        $query->with(['user', 'transType']);

        // Search by car number
        if ($request->has('carNumber')) {
            $query->where('carNumber', 'like', '%' . $request->carNumber . '%');
        }

        // Search by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by user
        if ($request->has('userId')) {
            $query->where('userId', $request->userId);
        }

        // Order
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Display deleted

        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        $drivers = $query->paginate($request->get('per_page', 15));
        $drivers->getCollection()->each(function ($driver) {
            $driver->makeHidden(['userId']);
            if ($driver->user) {
                $driver->user->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at']);
            }
            if ($driver->transType) {
                $driver->transType->makeHidden(['created_at', 'updated_at', 'deleted_at']);
            }
        });
        return response()->json([
            'success' => true,
            'data' => $drivers,
            'message' => 'Data fetched successfully'
        ]);
    }

    /**
     * View specific driver
     */
    public function show($id)
    {
        $driver = Driver::find($id);

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $driver,
            'message' => 'Data fetched successfully'
        ]);
    }

    /**
     * Add a new driver
     */

    public function store(StoreDriverRequest $request)
    {

        $carType = CarType::where('id', $request->CarTypeId)->first();
        if ($carType == null)
            return response()->json([
                'success' => false,
                'message' => 'خطأ في نوع العداد',
            ], 400);
        DB::beginTransaction();

        try {
            // 1. إضافة نوع السيارة (CarType)


            // 2. إضافة المستخدم (User)
            $user = User::create([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'number' => $request->number,
                'password' => Hash::make('Syriataxi@1'),
                'roll' => 'Driver',
                'banned' => false,
                'expireDate' => Carbon::today()->addMonth()->format('Y-m-d'),
            ]);

            $file = $request->file('image');
            $filePath = time() . $file->getClientOriginalName();
            $fileType = $file->guessExtension();
            Storage::disk('public')->put($filePath, File::get($file));
             $file1 = $request->file('IDImage');
            $filePath1 = time() . $file->getClientOriginalName();
            $fileType1 = $file->guessExtension();
            Storage::disk('public')->put($filePath1, File::get($file1));
            // 3. إضافة السائق (Driver)
            $driver = Driver::create([
                'userId' => $user->id,
                'transTypeId' => $carType->id,
                'image' => $filePath,
                'IDImage' => $filePath1,
                'carNumber' => $request->carNumber,
                'insurance' => $request->insurance,
                'mechanics' => $request->mechanics,
                'type' => $request->typeCar,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة السائق بنجاح',
                'data' => [
                    'user' => $user,
                    'carType' => $carType,
                    'driver' => $driver,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة السائق',
                'error' => $e->getMessage()
            ], 400);
        }
    }
     public  function getImage($path)
    {
        try {

                $responseFile = Storage::disk('public')->get($path);
                return (new Response($responseFile, 200))
                    ->header('Content-Type', Storage::disk('public')->
                    Storage::mimeType($path));

        } catch (Exception $e) {
            return response()->json([
                "state" => false,
                "data" => $e->getMessage()
            ]);
        }
    }
    public function updateLocation(DriverUpdateLocation $request)
    {
        $driverId = Driver::where('userId', $request->user()->id);
        $lng = $request->longitude;
        $lat = $request->latitude;
        Redis::geoadd('drivers', $lng, $lat, $driverId);
        return response()->json([
            'state' => true
        ]);
    }

    public function update(UpdateDriverRequest $request, $id)
    {
        $driver = Driver::find($id);
        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        if ($request->has('transTypeId')) {
            $carType = CarType::find($request->transTypeId);
            if (!$carType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car type not found'
                ], 404);
            }
            $driver->transTypeId = $request->transTypeId;
        }

        if ($request->has('carNumber') && $request->carNumber !== $driver->carNumber) {
            $existingDriver = Driver::where('carNumber', $request->carNumber)
                ->where('id', '!=', $id)
                ->first();

            if ($existingDriver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car number already exists'
                ], 400);
            }
            $driver->carNumber = $request->carNumber;
        }

        if ($request->has('typeCar')) {
            if ($request->typeCar !== $driver->type) {
                $driver->type = $request->typeCar;
            }
        }

        if ($request->has('image')) {
            $driver->image = $request->image;
        }

        if ($request->has('IDImage')) {
            $driver->IDImage = $request->IDImage;
        }

        if ($request->has('insurance')) {
            $driver->insurance = $request->insurance;
        }

        if ($request->has('mechanics')) {
            $driver->mechanics = $request->mechanics;
        }

        if ($request->has('userId')) {
            $driver->userId = $request->userId;
        }

        $driver->save();

        return response()->json([
            'success' => true,
            'data' => $driver->fresh(),
            'message' => 'Driver updated successfully'
        ]);
    }


    public function destroy($id)
    {
        $driver = Driver::find($id);

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        $driver->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver deleted successfully'
        ]);
    }

    public function restore($id)
    {
        $driver = Driver::withTrashed()->find($id);

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        if (!$driver->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Driver is not deleted'
            ], 400);
        }

        $driver->restore();

        return response()->json([
            'success' => true,
            'data' => $driver,
            'message' => 'Driver restored successfully'
        ]);
    }
    public function forceDelete($id)
    {
        $driver = Driver::withTrashed()->find($id);

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        $driver->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Driver deleted permanently'
        ]);
    }
    public function trashed()
    {
        $drivers = Driver::onlyTrashed()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $drivers,
            'message' => 'Data fetched successfully'
        ]);
    }
}
