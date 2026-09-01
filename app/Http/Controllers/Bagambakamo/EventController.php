<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => [
                'required',
                Rule::exists('bagambakamo_members', 'id'),
            ],
            'type' => [
                'required',
                'string',
                'max:255',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'contribution_per_member' => [
                'required',
                'numeric',
                'min:0',
            ],
            'event_date' => [
                'required',
                'date',
            ],
        ]);

        Event::create([
            'member_id' => $request->member_id,
            'type' => $request->type,
            'amount' => $request->amount,
            'contribution_per_member' => $request->contribution_per_member,
            'event_date' => $request->event_date,
        ]);

        return back()->with(
            'success',
            'Event added successfully'
        );
    }
}