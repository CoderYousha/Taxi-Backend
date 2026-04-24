<?php

namespace App\Http\Controllers;

use App\Models\RequestModel ;
use App\Models\User;
use App\Models\RequestHistory;
use Illuminate\Http\Request ;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportController extends Controller
{
    /**
     * التقارير المالية
     */
    public function financialReport(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'driver_id' => 'nullable|exists:drivers,id',
            'format' => 'sometimes|in:json,pdf,excel'
        ]);

        $query = RequestHistory::with(['request', 'driver.user'])
            ->whereBetween('created_at', [$request->from_date, $request->to_date]);

        // فلترة حسب السائق
        if ($request->has('driver_id')) {
            $query->where('driverId', $request->driver_id);
        }

        $histories = $query->get();

        // حساب الإحصائيات
        $statistics = [
            'total_trips' => $histories->count(),
            'total_revenue' => $histories->sum('finalCost'),
            'average_cost' => $histories->avg('finalCost'),
            'total_discounts' => $histories->whereNotNull('descountId')->count(),
            'total_discount_amount' => $this->calculateDiscountAmount($histories),
            'trips_by_driver' => $histories->groupBy('driverId')->map(function($item) {
                return [
                    'count' => $item->count(),
                    'revenue' => $item->sum('finalCost')
                ];
            })
        ];

        $report = [
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date
            ],
            'statistics' => $statistics,
            'details' => $histories
        ];

        // تصدير حسب التنسيق المطلوب
        if ($request->format === 'pdf') {
            return $this->exportToPDF($report);
        }

        if ($request->format === 'excel') {
            return $this->exportToExcel($report);
        }

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'تم إنشاء التقرير المالي بنجاح'
        ]);
    }

    /**
     * التقارير التشغيلية
     */
    public function operationalReport(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        // إحصائيات الطلبات
        $requests = RequestModel::whereBetween('created_at', [$request->from_date, $request->to_date]);

        $statistics = [
            'total_requests' => $requests->count(),
            'scheduled_requests' => (clone $requests)->where('type', RequestModel::TYPE_SCHEDULE)->count(),
            'immediate_requests' => (clone $requests)->where('type', RequestModel::TYPE_IMMEDIATE)->count(),
            'pending_requests' => (clone $requests)->where('status', RequestModel::STATUS_PENDING)->count(),
            'running_requests' => (clone $requests)->where('status', RequestModel::STATUS_RUNNING)->count(),
            'finished_requests' => (clone $requests)->where('status', RequestModel::STATUS_FINISHED)->count(),
            'cancelled_requests' => (clone $requests)->where('status', RequestModel::STATUS_REMOVED)->count(),
            'completion_rate' => $this->calculateCompletionRate($requests),
            'average_response_time' => $this->calculateAverageResponseTime($request->from_date, $request->to_date),
        ];

        // أكثر المناطق طلباً
        $topLocations = $this->getTopLocations($request->from_date, $request->to_date);

        // أكثر أنواع السيارات طلباً
        $topCarTypes = $this->getTopCarTypes($request->from_date, $request->to_date);

        $report = [
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date
            ],
            'statistics' => $statistics,
            'top_locations' => $topLocations,
            'top_car_types' => $topCarTypes
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'تم إنشاء التقرير التشغيلي بنجاح'
        ]);
    }

    /**
     * تقارير الجودة
     */
    public function qualityReport(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'driver_id' => 'nullable|exists:drivers,id'
        ]);

        $query = RequestHistory::with(['request', 'driver.user'])
            ->whereBetween('created_at', [$request->from_date, $request->to_date]);

        if ($request->has('driver_id')) {
            $query->where('driverId', $request->driver_id);
        }

        $histories = $query->get();

        // حساب تقييم الجودة (إذا كان لديك جدول تقييمات)
        $statistics = [
            'total_trips' => $histories->count(),
            'on_time_percentage' => $this->calculateOnTimePercentage($histories),
            'driver_performance' => $this->calculateDriverPerformance($histories),
            'customer_satisfaction' => $this->calculateCustomerSatisfaction($histories),
        ];

        $report = [
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date
            ],
            'statistics' => $statistics,
            'details' => $histories
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'تم إنشاء تقرير الجودة بنجاح'
        ]);
    }

    /**
     * دوال مساعدة للحسابات
     */
    private function calculateDiscountAmount($histories)
    {
        // حساب قيمة الخصومات
        return 0;
    }

    private function calculateCompletionRate($requests)
    {
        $total = $requests->count();
        if ($total == 0) return 0;

        $completed = (clone $requests)->where('status', RequestModel::STATUS_FINISHED)->count();
        return round(($completed / $total) * 100, 2);
    }

    private function calculateAverageResponseTime($fromDate, $toDate)
    {
        // حساب متوسط وقت الاستجابة
        return 0;
    }

    private function getTopLocations($fromDate, $toDate)
    {
        // أكثر المناطق طلباً
        return [];
    }

    private function getTopCarTypes($fromDate, $toDate)
    {
        // أكثر أنواع السيارات طلباً
        return [];
    }

    private function calculateOnTimePercentage($histories)
    {
        // حساب نسبة الرحلات في الوقت المحدد
        return 100;
    }

    private function calculateDriverPerformance($histories)
    {
        // حساب أداء السائقين
        return [];
    }

    private function calculateCustomerSatisfaction($histories)
    {
        // حساب رضا العملاء
        return 100;
    }

    private function exportToPDF($report)
    {
        $pdf = PDF::loadView('reports.financial', $report);
        return $pdf->download('financial_report.pdf');
    }

    private function exportToExcel($report)
    {
        // تصدير إلى Excel
        return response()->json(['message' => 'Excel export coming soon']);
    }
}
