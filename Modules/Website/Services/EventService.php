<?php

namespace Modules\Website\Services;

use App\SmEvent;


class EventService
{
    public function __construct() {}

    public function singleEventDetails($id)
    {
        try {
            $data['event'] = SmEvent::with('user')->find($id);
            return view('frontEnd.theme.' . activeTheme() . '.single_event', $data);
        } catch (\Exception $e) {
            return response('error');
        }
    }
}
