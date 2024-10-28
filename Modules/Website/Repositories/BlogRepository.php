<?php

namespace Modules\Website\Repositories;

use App\SmNews;
use App\SmNewsPage;
use App\SmNewsCategory;
use Illuminate\Http\Request;
use App\Models\SmNewsComment;


class BlogRepository
{
    public function getBlogs($skip = 0)
    {
        try {

            $blog = SmNews::where('school_id', app('school')->id)->take(5);
            $count = SmNews::count();
            $data['total_items'] = $count;
            if ($skip) {
                $data['skip'] = (int)$skip;
                $data['limit'] = $count - $skip;
                $data['blogs'] = $blog->skip($skip);
            }

            $data['blogs'] = $blog->get();
            $data['categories'] = SmNewsCategory::where('school_id', app('school')->id)->get();
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getBlog($id)
    {
        try {
            $data['news'] = SmNews::with(['newsComments.onlyChildrenFrontend'])->where('school_id', app('school')->id)->findOrFail($id);
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function addComment(Request $request, $id)
    {
        try {
            $newsDeyails = SmNews::find($id);
            $store = new SmNewsComment();
            $store->message = $request->message;
            $store->news_id = $request->news_id;
            $store->user_id = $request->user_id;
            $store->parent_id = $request->parent_id ?? NULL;
            if ($newsDeyails->is_global == 1 && generalSetting()->auto_approve == 1) {
                $store->status = 1;
            } elseif ($newsDeyails->is_global == 0 && $newsDeyails->auto_approve == 1) {
                $store->status = 1;
            } else {
                $store->status = 0;
            }
            $store->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getComments(Request $request, $id)
    {
        try {
            if ($request->skip) {
                $data['skip'] = $request->skip;
                $data['limit'] = $data['count'] - $data['skip'];
            }

            $data['comments'] = SmNewsComment::skip($data['skip'])
                ->where('news_id', $id)
                ->where('school_id', app('school')->id)->take(5)
                ->get();

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getCategory(Request $request)
    {
        try {
            $data['category'] = SmNewsCategory::find($request->id);
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteComment($id)
    {
        try {
            $data['category'] = SmNewsCategory::find($id);
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
