<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Display Members
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $members = Member::with('payments', 'events')
            ->get()

            ->filter(function ($member) {
                return $member->total_paid >= 210000;
            })

            ->sort(function ($a, $b) {

                if ($a->total_paid == $b->total_paid) {
                    return strcmp(
                        $a->full_name,
                        $b->full_name
                    );
                }

                return $b->total_paid <=> $a->total_paid;
            })

            ->values();

        return view(
            'bagambakamo.members.index',
            compact('members')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Member
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(
                    'bagambakamo_members',
                    'phone'
                ),
            ],
        ]);

        Member::create([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'status' => 'active',
            'join_date' => now(),
        ]);

        return back()->with(
            'success',
            'Member added successfully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Single Member
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $member = Member::with(
            'payments',
            'events'
        )->findOrFail($id);

        return view(
            'bagambakamo.members.show',
            compact('member')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Member
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        Member::findOrFail($id)->delete();

        return back()->with(
            'success',
            'Member deleted successfully'
        );
    }
}