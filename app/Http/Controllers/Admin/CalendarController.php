<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CalendarEvent;
use App\Models\WeeklySchedule;
 // Make sure this model exists

class CalendarController extends Controller
{
    public function index()
    {
        $events = CalendarEvent::where('user_id', auth()->id())->orderBy('date')->get();
        return view('admin.calendar', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'type' => 'required|in:unavailable,class,task',
        ]);

        CalendarEvent::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'type' => $request->type,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Event added successfully.');
    }

    public function destroy($id)
    {
        $event = CalendarEvent::find($id);

        if (!$event) {
            return redirect()->back()->with('error', 'Event not found.');
        }

        if ((int)$event->user_id !== (int)auth()->id()) {
            abort(403, 'Unauthorized to delete this event.');
        }

        $event->delete();
        return redirect()->back()->with('success', 'Event deleted successfully.');
    }

    public function storeRecurring(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
        'start_time' => 'required',
        'end_time' => 'required',
        'type' => 'required|in:class,task',
    ]);

    WeeklySchedule::create([
        'user_id' => auth()->id(),
        'title' => $request->title,
        'day_of_week' => $request->day_of_week,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'type' => $request->type,
    ]);

    return redirect()->back()->with('success', 'Recurring task added.');
}

public function destroyRecurring($id)
{
    $task = WeeklySchedule::find($id);

    if (!$task || $task->user_id !== auth()->id()) {
        return redirect()->back()->with('error', 'Task not found or unauthorized.');
    }

    $task->delete();
    return redirect()->back()->with('success', 'Recurring task deleted.');
}

}
