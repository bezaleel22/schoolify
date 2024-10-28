<?php

namespace Modules\Website\Http\Controllers;

use App\SmEvent;
use App\SmNews;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebsiteController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    { {
            try {
                $home_data = [
                    'blogs' => SmNews::where('school_id', app('school')->id)->orderBy('order', 'asc')->limit(3)->get(['id', 'news_title', 'image', 'image_thumb', 'view_count']),
                    'events' => SmEvent::where('school_id', app('school')->id)->get(),
                ];

                return response()->json(['success' => true, 'data' => $home_data]);
            } catch (\Exception $e) {
                return redirect()->back();
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('website::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('website::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('website::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
