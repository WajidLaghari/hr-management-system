<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PerformanceReview;
use App\Http\Controllers\Constants\Status;

class PerformanceReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = PerformanceReview::with('employee')->get();
        return response()->json(['review' => $reviews], Status::SUCCESS);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'review_date' => 'required|date',
            'kpi_score' => 'required|numeric',
            'feedback' => 'required|string',
        ]);

        $review = PerformanceReview::create($request->all());
        return response()->json(['message' => 'Performance Review Add Successfully'], Status::SUCCESS);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = PerformanceReview::find($id);

        if(!$review)
        {
            return response()->json(['message' => 'Review Not Found'], Status::NOT_FOUND);
        }

        return response()->json(['review' => $review], Status::SUCCESS);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $review = PerformanceReview::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $request->validate([
            'employee_id' => 'exists:employees,id',
            'review_date' => 'date',
            'kpi_score' => 'numeric',
            'feedback' => 'string',
        ]);

        $review->update($request->all());
        return response()->json(['message', 'Performance Review Update Successfully'], Status::SUCCESS);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = PerformanceReview::find($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();
        return response()->json(['message', 'Performance Review Delete Successfully'], Status::SUCCESS);
    }
}
