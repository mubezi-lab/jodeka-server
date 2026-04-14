<?php

namespace App\Http\Controllers;

use App\Models\Livestock;
use Illuminate\Http\Request;

class LivestockController extends Controller
{
    /**
     * Display all livestock batches
     */
    public function index()
    {
        $livestocks = Livestock::latest()->get();

        return view('livestocks.index', compact('livestocks'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('livestocks.create');
    }

    /**
     * Store new livestock
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        Livestock::create($data);

        return redirect()
            ->route('livestocks.index')
            ->with('success', 'Livestock created successfully');
    }

    /**
     * Show single livestock (SMART DATA kutoka model)
     */
    public function show(Livestock $livestock)
    {
        return view('livestocks.show', [
            'livestock'      => $livestock,
            'week'           => $livestock->current_week,
            'ageDays'        => $livestock->age_in_days,
            'expectedFeed'   => $livestock->expected_feed,
            'todayFeed'      => $livestock->getDailyFeed(now()),
            'totalFeedCost'  => $livestock->totalFeedCost(),
            'costPerAlive'   => $livestock->costPerAlive(),
            'costPerDead'    => $livestock->costPerDead(),
            'totalMortality' => $livestock->totalMortality(),
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Livestock $livestock)
    {
        return view('livestocks.edit', compact('livestock'));
    }

    /**
     * Update livestock
     */
    public function update(Request $request, Livestock $livestock)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        $livestock->update($data);

        return redirect()
            ->route('livestocks.index')
            ->with('success', 'Livestock updated successfully');
    }

    /**
     * Delete livestock
     */
    public function destroy(Livestock $livestock)
    {
        $livestock->delete();

        return redirect()
            ->route('livestocks.index')
            ->with('success', 'Livestock deleted successfully');
    }
}