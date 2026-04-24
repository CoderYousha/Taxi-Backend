<?php

namespace App\Http\Controllers;

use App\Models\RequestModel;
use App\Models\RequestHistory;
use App\Models\Driver;
use App\Models\User;
use App\Models\Discount;
use App\Http\Requests\StoreRequestRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RequestController extends Controller
{
    /**
     * Create a new request (scheduled or immediate)
     */
    public function store(StoreRequestRequest $request)
    {
        $validated = $request->validated();

        $validated['status'] = RequestModel::STATUS_PENDING;

        $newRequest = RequestModel::create($validated);

        // If immediate request, find a driver immediately
        if ($request->type === RequestModel::TYPE_IMMEDIATE) {
            $this->findImmediateDriver($newRequest);
        } else {
            // If scheduled request, send notification to drivers
            $this->notifyDriversForScheduledRequest($newRequest);
        }

        

        return response()->json([
            'success' => true,
            'data' => $newRequest,
            'message' => 'Request created successfully'
        ], 201);
    }

    /**
     * Find a driver for immediate request
     */
    private function findImmediateDriver($request)
    {
        // Find available drivers
        $availableDrivers = Driver::where('status', 'available')
            ->where('type', $request->carType->type ?? 'car')
            ->get();

        foreach ($availableDrivers as $driver) {
            // Send notification to driver
            $this->sendNotificationToDriver($driver, $request);
        }

        // If no driver found within 30 seconds, convert request to scheduled
        // (This part can be implemented using a Job)
    }

    /**
     * Send notification to drivers about scheduled request
     */
    private function notifyDriversForScheduledRequest($request)
    {
        $drivers = Driver::all();

        foreach ($drivers as $driver) {
            // Send notification via Firebase or WebSocket
            // event(new NewScheduledRequest($driver, $request));
        }
    }

    /**
     * Send notification to a driver
     */
    private function sendNotificationToDriver($driver, $request)
    {
        // Implement notification sending
    }

    /**
     * Accept booking by driver
     */
    public function acceptBooking(HttpRequest $request, $requestId)
    {
        $requestData = RequestModel::find($requestId);

        if (!$requestData) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found'
            ], 404);
        }

        if ($requestData->status !== RequestModel::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot accept this request at this time'
            ], 400);
        }

        $driverId = $request->driverId;
        $driver = Driver::find($driverId);

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Update request status to Reserved
            $requestData->status = 'Reserved';
            $requestData->save();

            // Create trip record
            $history = RequestHistory::create([
                'requestId' => $requestData->id,
                'driverId' => $driverId,
                'finalCost' => $requestData->predectedCost ?? 0,
                'descountId' => $request->discountId ?? null
            ]);

            DB::commit();

            // Send confirmation to passenger
            $this->sendConfirmationToPassenger($requestData, $driver);

            return response()->json([
                'success' => true,
                'data' => [
                    'request' => $requestData,
                    'history' => $history,
                    'driver' => $driver
                ],
                'message' => 'Booking accepted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send confirmation to passenger
     */
    private function sendConfirmationToPassenger($request, $driver)
    {
        // Send notification to passenger that a driver has been assigned
        // event(new DriverAssigned($request, $driver));
    }

    /**
     * Start trip (when driver arrives)
     */
    public function startTrip($requestId)
    {
        $request = RequestModel::find($requestId);

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found'
            ], 404);
        }

        if ($request->status !== RequestModel::STATUS_RESERVED ) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot start trip at this status'
            ], 400);
        }

        $request->status = RequestModel::STATUS_RUNNING;
        $request->save();

        // Guide driver to passenger location
        // And update location in real-time

        return response()->json([
            'success' => true,
            'data' => $request,
            'message' => 'Trip started successfully'
        ]);
    }

    /**
     * Finish trip
     */
    public function finishTrip($requestId, HttpRequest $request)
    {
        $requestData = RequestModel::find($requestId);

        if (!$requestData) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $finalCost = $request->finalCost;

            // Apply discount if exists
            if ($request->discountCode) {
                $discount = Discount::where('code', $request->discountCode)->first();
                if ($discount) {
                    $finalCost = $discount->calculateDiscount($finalCost);

                    // Update trip record with discount
                    $history = RequestHistory::where('requestId', $requestId)->first();
                    if ($history) {
                        $history->descountId = $discount->id;
                        $history->finalCost = $finalCost;
                        $history->save();
                    }
                }
            }

            $requestData->status = RequestModel::STATUS_FINISHED;
            $requestData->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $requestData,
                'finalCost' => $finalCost,
                'message' => 'Trip finished successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available bookings for drivers
     */
    public function getAvailableBookings()
    {
        $bookings = RequestModel::where('type', RequestModel::TYPE_SCHEDULE)
            ->where('status', RequestModel::STATUS_PENDING)
            ->where('requestDate', '>', now())
            ->with(['user', 'startLocation', 'destLocation', 'carType'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'message' => 'Available bookings retrieved successfully'
        ]);
    }

    /**
     * Get passenger requests
     */
    public function getUserRequests($userId)
    {
        $requests = RequestModel::where('userId', $userId)
            ->with(['history.driver.user', 'carType', 'startLocation', 'destLocation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Requests retrieved successfully'
        ]);
    }

    /**
     * Get driver trips
     */
    public function getDriverTrips($driverId)
    {
        $trips = RequestHistory::where('driverId', $driverId)
            ->with(['request' => function($q) {
                $q->with(['user', 'startLocation', 'destLocation']);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trips,
            'message' => 'Trips retrieved successfully'
        ]);
    }

    /**
     * Send reminder notification to driver before booking time
     */
    public function remindDriver($requestId)
    {
        $request = RequestModel::find($requestId);

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found'
            ], 404);
        }

        $history = RequestHistory::where('requestId', $requestId)->first();

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'No driver associated with this booking'
            ], 404);
        }

        // Send reminder notification to driver
        // Notification::send($history->driver, new TripReminder($request));

        return response()->json([
            'success' => true,
            'message' => 'Reminder sent to driver successfully'
        ]);
    }
}
