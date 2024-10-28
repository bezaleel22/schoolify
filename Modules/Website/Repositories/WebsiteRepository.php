<?php

namespace Modules\Website\Repositories;

use App\SmNews;
use App\SmNewsPage;
use App\SmNewsCategory;
use Illuminate\Http\Request;
use App\Models\SmNewsComment;
use App\SmContactMessage;
use App\SmContactPage;
use App\SmGeneralSettings;

class WebappRepository
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'sometimes|required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);
        try {
            $contact_message = new SmContactMessage();
            $contact_message->name = $request->name;
            if ($request->phone) {
                $contact_message->phone = $request->phone;
            }
            $contact_message->email = $request->email;
            $contact_message->subject = $request->subject;
            $contact_message->message = $request->message;
            $contact_message->school_id = app('school')->id;
            $contact_message->save();

            $receiver_name = "System Admin";
            $compact['contact_name'] = $request->name;
            if ($request->phone) {
                $compact['contact_phone'] = $request->phone;
            }
            $compact['contact_email'] = $request->email;
            $compact['subject'] = $request->subject;
            $compact['contact_message'] = $request->message;
            $contact_page_email = SmContactPage::where('school_id', app('school')->id)->first();
            $setting = SmGeneralSettings::where('school_id', app('school')->id)->first();
            if ($contact_page_email->email) {
                $email = $contact_page_email->email;
            } else {
                $email = $setting->email;
            }
            @send_mail($email, $receiver_name, "frontend_contact", $compact);
            return response()->json(['success' => 'success']);
        } catch (\Exception $e) {
            return response()->json('error');
        }
    }
}