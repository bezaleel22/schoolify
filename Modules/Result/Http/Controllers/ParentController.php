<?php

namespace Modules\Result\Http\Controllers;

use App\SmStudent;
use App\SmVehicle;
use Carbon\Carbon;
use App\SmExamType;
use App\SmMarksGrade;
use App\SmBankAccount;
use App\SmExamSchedule;
use App\SmLeaveRequest;
use App\SmPaymentMethhod;
use App\SmStudentDocument;
use App\SmStudentTimeline;
use App\Models\FeesInvoice;
use App\SmStudentAttendance;
use App\SmSubjectAttendance;
use App\Models\StudentRecord;
use App\SmClassOptionalSubject;
use App\SmOptionalSubjectAssign;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;

class ParentController extends Controller
{
    public function myChildren($id)
    {
        try {
            $parent_info = Auth::user()->parent;
            $student_detail = SmStudent::where('id', $id)->where('parent_id', $parent_info->id)->with('studentRecords.directFeesInstallments.payments', 'studentAttendances', 'studentRecords.directFeesInstallments.installment', 'feesAssign', 'feesAssignDiscount', 'academicYear', 'defaultClass.class', 'category', 'religion')->first();
            $records = $student_detail->studentRecords;
            if ($student_detail) {
                $driver = SmVehicle::where('sm_vehicles.id', $student_detail->vechile_id)
                    ->join('sm_staffs', 'sm_vehicles.driver_id', '=', 'sm_staffs.id')
                    ->where('sm_staffs.school_id', Auth::user()->school_id)
                    ->first();

                $optional_subject_setup = SmClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();
                $student_optional_subject = SmOptionalSubjectAssign::where('student_id', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                $fees_assigneds = $student_detail->feesAssign;
                $invoice_settings = FeesInvoice::where('school_id', Auth::user()->school_id)->first();
                $fees_discounts = $student_detail->feesAssignDiscount;

                $documents = SmStudentDocument::where('student_staff_id', $student_detail->id)
                    ->where('type', 'stu')
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $timelines = SmStudentTimeline::where('staff_student_id', $student_detail->id)
                    ->where('academic_id', getAcademicId())
                    ->where('visible_to_student', '>', 0)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $exams = SmExamSchedule::where('class_id', $student_detail->class_id)
                    ->where('section_id', $student_detail->section_id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $maxgpa = $grades->max('gpa');

                $failgpa = $grades->min('gpa');

                $failgpaname = $grades->where('gpa', $failgpa)
                    ->first();

                $academic_year = $student_detail->academicYear;

                $exam_terms = SmExamType::where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();
                $custom_field_data = $student_detail->custom_field;

                if (!is_null($custom_field_data)) {
                    $custom_field_values = json_decode($custom_field_data);
                } else {
                    $custom_field_values = null;
                }

                $paymentMethods = SmPaymentMethhod::whereNotIn('method', ["Cash", "Wallet"])
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $bankAccounts = SmBankAccount::where('active_status', 1)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                if (moduleStatusCheck('Wallet')) {
                    $walletAmounts = WalletTransaction::where('user_id', Auth::user()->id)
                        ->where('school_id', Auth::user()->school_id)
                        ->get();
                } else {
                    $walletAmounts = null;
                }

                $custom_field_data = $student_detail->custom_field;

                if (!is_null($custom_field_data)) {
                    $custom_field_values = json_decode($custom_field_data);
                } else {
                    $custom_field_values = null;
                }

                $data['bank_info'] = SmPaymentMethhod::where('method', 'Bank')->where('school_id', Auth::user()->school_id)->first();
                $data['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')->where('school_id', Auth::user()->school_id)->first();

                $leave_details = SmLeaveRequest::where('staff_id', $student_detail->user_id)->where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $payment_gateway = SmPaymentMethhod::first();
                $student = SmStudent::where('id', $id)->where('parent_id', $parent_info->id)->first();

                $now = Carbon::now();
                $year = $now->year;
                $month  = $now->month;
                $days = cal_days_in_month(CAL_GREGORIAN, $now->month, $now->year);

                $studentRecord = StudentRecord::where('student_id', $student_detail->id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $student_detail->school_id)
                    ->get();


                $attendance = SmStudentAttendance::where('student_id', $student_detail->id)
                    ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                    ->whereIn('student_record_id', $studentRecord->pluck('id'))
                    ->get();

                $subjectAttendance = SmSubjectAttendance::with('student')
                    ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                    ->whereIn('student_record_id', $studentRecord->pluck('id'))
                    ->where('school_id', $student_detail->school_id)
                    ->get();

                $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
                $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();

                if (moduleStatusCheck('University')) {
                    $student_id = $student_detail->id;
                    $studentDetails = SmStudent::find($student_id);
                    $studentRecordDetails = StudentRecord::where('student_id', $student_id);
                    $studentRecords = $studentRecordDetails->distinct('un_academic_id')->get();
                    $print = 1;

                    return view('backEnd.parentPanel.my_children', compact('student_detail', 'fees_assigneds', 'driver', 'fees_discounts', 'exams', 'documents', 'timelines', 'grades', 'exam_terms', 'academic_year', 'leave_details', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'failgpaname', 'custom_field_values', 'walletAmounts', 'bankAccounts', 'paymentMethods', 'records', 'studentDetails', 'studentRecordDetails', 'studentRecords', 'print', 'payment_gateway', 'student', 'data', 'invoice_settings', 'studentBehaviourRecords', 'behaviourRecordSetting'));
                } else {
                    return view('backEnd.parentPanel.my_children', compact('student_detail', 'fees_assigneds', 'driver', 'fees_discounts', 'exams', 'documents', 'timelines', 'grades', 'exam_terms', 'academic_year', 'leave_details', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'failgpaname', 'custom_field_values', 'walletAmounts', 'bankAccounts', 'paymentMethods', 'records', 'payment_gateway', 'student', 'data', 'invoice_settings', 'attendance', 'subjectAttendance', 'days', 'year', 'month', 'studentBehaviourRecords', 'behaviourRecordSetting'));
                }
            } else {
                Toastr::warning('Invalid Student ID', 'Invalid');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
