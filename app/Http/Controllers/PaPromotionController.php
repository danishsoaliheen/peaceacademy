<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PaEnrollment;
use App\Models\PaClass;
use App\Models\PaSession;

class PaPromotionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Promotion Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $sessions = PaSession::where('is_active', 1)
            ->orderBy('id')
            ->get();

        $classes = PaClass::where('is_active', 1)
            ->orderBy('class_order')
            ->get();

        $fromSession = $request->from_session_id;

        $classId = $request->class_id;

        $students = PaEnrollment::with([
                'student',
                'class',
                'session'
            ])
            ->when($fromSession, function ($query) use ($fromSession) {

                $query->where('session_id', $fromSession);

            })
            ->when($classId, function ($query) use ($classId) {

                $query->where('class_id', $classId);

            })
            ->where('is_active', 1)
            ->orderBy('class_id')
            ->paginate(50);

        return view(
            'promotion.preview',
            compact(
                'sessions',
                'classes',
                'students',
                'fromSession',
                'classId'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Execute Promotion
    |--------------------------------------------------------------------------
    */

    public function promote(Request $request)
    {
        $request->validate([

            'from_session_id' => 'required',

            'to_session_id' => 'required',

        ]);

        if(
            $request->from_session_id
            ==
            $request->to_session_id
        ){
            return back()->with(
                'error',
                'From and To session cannot be same.'
            );
        }

        $enrollments = PaEnrollment::with('class')
            ->where('session_id', $request->from_session_id)
            ->where('is_active', 1)
            ->get();

        $promotedCount = 0;

        foreach ($enrollments as $enrollment)
        {
            /*
            |--------------------------------------------------------------------------
            | Find Next Class
            |--------------------------------------------------------------------------
            */

            $nextClass = PaClass::where(
                    'class_order',
                    '>',
                    $enrollment->class->class_order
                )
                ->orderBy('class_order')
                ->first();

            if(!$nextClass){
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Promotion
            |--------------------------------------------------------------------------
            */

            $alreadyExists = PaEnrollment::where(
                    'student_id',
                    $enrollment->student_id
                )
                ->where(
                    'session_id',
                    $request->to_session_id
                )
                ->exists();

            if($alreadyExists){
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Create New Enrollment
            |--------------------------------------------------------------------------
            */

            PaEnrollment::create([

                'student_id' => $enrollment->student_id,

                'class_id' => $nextClass->id,

                'session_id' => $request->to_session_id,

                'roll_no' => $enrollment->roll_no,

                'enrollment_date' => now(),

                'is_active' => 1

            ]);

            /*
            |--------------------------------------------------------------------------
            | Deactivate Old Enrollment
            |--------------------------------------------------------------------------
            */

            $enrollment->update([
                'is_active' => 0
            ]);

            $promotedCount++;
        }

        return back()->with(
            'success',
            $promotedCount . ' students promoted successfully.'
        );
    }
}