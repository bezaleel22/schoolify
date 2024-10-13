<?php

namespace Modules\Result\Services;

use Modules\Result\Repositories\ResultRepository;

class ResultService
{
    public function __construct() {}

    public function getStudentResult($id, $exam_type)
    {
        try {

            $result_data = ResultRepository::getResultData($id, $exam_type->id);
            if (empty($result_data)) {
                throw new \Exception('No records found for the given student and exam.');
            }

            foreach ($result_data->records as $record) {
                $total_marks = $record->results->sum('total_marks');
                $record['avarage'] = floor($total_marks / $record->results->count());
            }

            $ids = $result_data->records->pluck('student_id', 'avarage');
            $min = $result_data->records->min('avarage');
            $min_average = (object)[
                'student_id' => $ids[$min] ?? null,
                'value' => $min,
            ];

            $max = $result_data->records->max('avarage');
            $max_average = (object)[
                'student_id' => $ids[$max] ?? null,
                'value' => $max,
            ];

            $student_data = $result_data->student_data ?? null;
            if (!$student_data) {
                throw new \Exception('Student data not found.');
            }

            $academic = $student_data['academic'] ?? null;
            if (!$academic) {
                throw new \Exception('Academic data not found.');
            }

            $custom_field = $student_data['custom_field'] ?? [];
            $school_data = $student_data['school'] ?? null;
            $result = $student_data['result'] ?? [];

            // Validate necessary fields in student data
            if (empty($student_data->full_name) || empty($school_data->school_name)) {
                throw new \Exception('Incomplete student or school information.');
            }

            $student = (object) [
                'id' => $student_data->id,
                'full_name' => $student_data->full_name,
                'term' => $this->removeDate($custom_field['exam_type'] ?? ''),
                'exam_type_id' => $exam_type->id,
                'title' => $exam_type->title,
                'type' => 'GRADERS',
                'class_name' => $student_data->class_name ?? 'N/A',
                'section_name' => $student_data->section_name ?? 'N/A',
                'admin_no' => $student_data->admission_no ?? 'N/A',
                'session_year' => $academic->title ?? 'N/A',
                'opened' => $custom_field['days_school_opened'] ?? 0,
                'absent' => $custom_field['days_absent'] ?? 0,
                'present' => $custom_field['days_present'] ?? 0,
                'student_photo' => $student_data->student_photo ?? '', // Placeholder for the photo path
            ];

            $address = $this->parseAddress($school_data->address ?? '');
            $school = (object) [
                'name' => $school_data->school_name ?? 'N/A',
                'city' => $address->city ?? 'N/A',
                'state' => $address->state ?? 'N/A',
                'title' => explode(' ', $custom_field['exam_type'] ?? '')[4] ?? 'N/A',
                'vacation_date' => 'December 25, 2024',
            ];

            $rateMapping = [
                '5' => ['remark' => 'Excellent', 'color' => 'range-success'],
                '4' => ['remark' => 'Good', 'color' => 'range-error'],
                '3' => ['remark' => 'Average', 'color' => 'range-info'],
                '2' => ['remark' => 'Below Average', 'color' => 'range-accent'],
                '1' => ['remark' => 'Poor', 'color' => 'range-warning'],
            ];

            $ratingData = array_filter($custom_field, function ($key) {
                return in_array($key, [
                    "adherent_and_independent",
                    "self_control_and_interaction",
                    "flexibility_and_creativity",
                    "meticulous",
                    "neatness",
                    "overall_progress"
                ]);
            }, ARRAY_FILTER_USE_KEY);

            $ratings = [];
            foreach ($ratingData as $key => $value) {
                if (isset($rateMapping[$value])) {
                    $mappedRate = $rateMapping[$value];
                    $ratings[] = (object) [
                        'attribute' => ucfirst(str_replace('_', ' ', $key)),
                        'rate' => $value / 5 * 100,
                        'color' => $mappedRate['color'],
                        'remark' => $mappedRate['remark'],
                    ];
                }
            }

            $records = [];
            $remark = '';
            $over_all = 0;
            foreach ($result as $subject_id => $marks_data) {
                if ($marks_data->isNotEmpty()) {
                    if ($subject_id == 20) {
                        $remark = $marks_data[0]->teacher_remarks ?? '';
                    }

                    $sum = $marks_data->sum('total_marks');
                    $over_all += $sum;
                    $marks = $marks_data->pluck('total_marks')->toArray();
                    $grade = $this->getGrade($sum, 'GRADERS');

                    $records[] = [
                        'subject' => $marks_data[0]->subject_name ?? 'N/A',
                        'marks' => $marks,
                        'total_score' => $sum,
                        'grade' => $grade->grade,
                        'color' => $grade->color
                    ];
                }
            }

            $score = (object) [
                'total' => $over_all,
                'average' => $records ? floor($over_all / count($records)) : 0,
                'min_average' => $min_average ?? null,
                'max_average' => $max_average ?? null,
            ];

            $remark = (object) [
                'name' => 'Teachers Remark',
                'comment' => $remark,
            ];

            return [
                'school' => $school,
                'student' => $student,
                'records' => $records,
                'score' => $score,
                'ratings' => $ratings,
                'remark' => $remark,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function removeDate($string)
    {
        $pattern = '/\s*-\s*[A-Za-z]{3}\/\d{4}/';
        return preg_replace($pattern, '', $string);
    }

    public function getGrade($score, $arm)
    {
        $eyfs = [
            ['min' => 0, 'max' => 80, 'grade' => 'EMERGING', 'color' => 'bg-purple-200'],
            ['min' => 81, 'max' => 90, 'grade' => 'EXPECTED', 'color' => 'bg-blue-200'],
            ['min' => 91, 'max' => 100, 'grade' => 'EXCEEDING', 'color' => 'bg-red-200'],
        ];

        $graders = [
            ['min' => 0, 'max' => 69, 'grade' => 'E', 'color' => 'bg-red-200'],
            ['min' => 70, 'max' => 76, 'grade' => 'D', 'color' => 'bg-orange-200'],
            ['min' => 77, 'max' => 85, 'grade' => 'C', 'color' => 'bg-yellow-200'],
            ['min' => 86, 'max' => 93, 'grade' => 'B', 'color' => 'bg-blue-200'],
            ['min' => 94, 'max' => 100, 'grade' => 'A', 'color' => 'bg-purple-200'],
        ];

        $grades = $arm === "GRADERS" ? $graders : $eyfs;
        foreach ($grades as $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return (object) ['grade' => $range['grade'], 'color' => $range['color']];
            }
        }

        return ["Outstanding", "bg-red-200"];
    }

    private function parseAddress($address)
    {
        $addressComponents = [
            'street_number' => null,
            'street_name' => null,
            'city' => null,
            'state' => null,
        ];

        $parts = array_map('trim', explode(',', $address));
        $addressComponents['state'] = array_pop($parts);
        $addressComponents['city'] = array_pop($parts);

        $streetAddress = implode(', ', $parts);

        $regex = '/No\.\s*(\d+)\s*(.+)/i';
        $matches = [];

        if (preg_match($regex, $streetAddress, $matches)) {
            $addressComponents['street_number'] = $matches[1]; // First capture group (street number)
            $addressComponents['street_name'] = $matches[2];   // Second capture group (street name)
        } else {
            $addressComponents['street_name'] = $streetAddress;
        }

        return (object) $addressComponents;
    }
}
