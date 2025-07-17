<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Entities\StaffMember;
use Carbon\Carbon;

class StaffMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $staffMembers = [
            [
                'name' => 'Dr. Margaret Thompson',
                'slug' => 'margaret-thompson',
                'position' => 'Principal',
                'department' => 'Administration',
                'email' => 'mthompson@school.edu',
                'phone' => '(555) 123-4501',
                'bio' => 'Dr. Thompson brings over 20 years of educational leadership experience to our school. She holds a Ph.D. in Educational Administration and is passionate about creating inclusive learning environments.',
                'qualifications' => json_encode([
                    'Ph.D. in Educational Administration - Harvard University',
                    'M.Ed. in Curriculum and Instruction - Stanford University',
                    'B.A. in English Literature - Yale University',
                    'Licensed School Administrator',
                    'Certified Educational Leader'
                ]),
                'specializations' => json_encode(['Educational Leadership', 'Curriculum Development', 'School Management']),
                'years_experience' => 20,
                'photo' => '/images/staff/margaret-thompson.jpg',
                'office_location' => 'Main Office, Room 101',
                'office_hours' => 'Monday-Friday: 8:00 AM - 4:00 PM',
                'is_featured' => true,
                'is_leadership' => true,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/margaretthompson',
                    'twitter' => 'https://twitter.com/drthompson_edu'
                ]),
                'achievements' => json_encode([
                    'Principal of the Year Award 2023',
                    'Educational Innovation Award',
                    'Published researcher in educational journals'
                ]),
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr. James Rodriguez',
                'slug' => 'james-rodriguez',
                'position' => 'Vice Principal',
                'department' => 'Administration',
                'email' => 'jrodriguez@school.edu',
                'phone' => '(555) 123-4502',
                'bio' => 'Dr. Rodriguez oversees academic programs and student affairs. With expertise in special education and inclusive practices, he ensures all students receive quality education.',
                'qualifications' => json_encode([
                    'Ph.D. in Special Education - UC Berkeley',
                    'M.Ed. in Educational Psychology - UCLA',
                    'B.S. in Psychology - University of Michigan',
                    'Special Education Certification',
                    'Administrative License'
                ]),
                'specializations' => json_encode(['Special Education', 'Student Affairs', 'Inclusive Education']),
                'years_experience' => 15,
                'photo' => '/images/staff/james-rodriguez.jpg',
                'office_location' => 'Main Office, Room 103',
                'office_hours' => 'Monday-Friday: 7:30 AM - 3:30 PM',
                'is_featured' => true,
                'is_leadership' => true,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/jamesrodriguez'
                ]),
                'achievements' => json_encode([
                    'Special Education Advocate of the Year',
                    'Inclusive Practices Champion',
                    'Conference speaker on educational equity'
                ]),
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sarah Chen',
                'slug' => 'sarah-chen',
                'position' => 'Head of Mathematics Department',
                'department' => 'Mathematics',
                'email' => 'schen@school.edu',
                'phone' => '(555) 123-4510',
                'bio' => 'Ms. Chen leads our mathematics department with innovative teaching methods and a passion for making math accessible to all students.',
                'qualifications' => json_encode([
                    'M.S. in Mathematics - MIT',
                    'B.S. in Mathematics Education - Stanford University',
                    'Teaching Certification in Mathematics',
                    'Advanced Placement Calculus Certified'
                ]),
                'specializations' => json_encode(['Advanced Mathematics', 'Calculus', 'Statistics', 'STEM Education']),
                'years_experience' => 12,
                'photo' => '/images/staff/sarah-chen.jpg',
                'office_location' => 'Mathematics Building, Room 201',
                'office_hours' => 'Monday-Friday: 2:30 PM - 4:00 PM',
                'is_featured' => true,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/sarahchen'
                ]),
                'achievements' => json_encode([
                    'Excellence in Mathematics Teaching Award',
                    'AP Calculus Teacher of the Year',
                    'Mathematics competition coach'
                ]),
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Michael Johnson',
                'slug' => 'michael-johnson',
                'position' => 'Science Department Chair',
                'department' => 'Science',
                'email' => 'mjohnson@school.edu',
                'phone' => '(555) 123-4520',
                'bio' => 'Mr. Johnson brings real-world scientific research experience to the classroom, inspiring students to pursue careers in science and technology.',
                'qualifications' => json_encode([
                    'Ph.D. in Chemistry - Caltech',
                    'M.S. in Chemical Engineering - MIT',
                    'B.S. in Chemistry - Harvard University',
                    'Teaching Certification in Science',
                    'Research Laboratory Experience'
                ]),
                'specializations' => json_encode(['Chemistry', 'Physics', 'Environmental Science', 'Research Methods']),
                'years_experience' => 14,
                'photo' => '/images/staff/michael-johnson.jpg',
                'office_location' => 'Science Building, Room 105',
                'office_hours' => 'Monday-Friday: 3:00 PM - 4:30 PM',
                'is_featured' => true,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/michaeljohnson'
                ]),
                'achievements' => json_encode([
                    'Science Educator of the Year',
                    'Published researcher in chemistry journals',
                    'Science fair coordinator'
                ]),
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Emily Davis',
                'slug' => 'emily-davis',
                'position' => 'English Department Head',
                'department' => 'English',
                'email' => 'edavis@school.edu',
                'phone' => '(555) 123-4530',
                'bio' => 'Ms. Davis fosters creativity and critical thinking through literature and writing, helping students develop strong communication skills.',
                'qualifications' => json_encode([
                    'M.A. in English Literature - Columbia University',
                    'B.A. in English - Princeton University',
                    'Teaching Certification in English',
                    'Creative Writing Workshop Leader'
                ]),
                'specializations' => json_encode(['Literature', 'Creative Writing', 'Composition', 'Public Speaking']),
                'years_experience' => 10,
                'photo' => '/images/staff/emily-davis.jpg',
                'office_location' => 'Humanities Building, Room 210',
                'office_hours' => 'Monday-Friday: 2:45 PM - 4:15 PM',
                'is_featured' => true,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/emilydavis',
                    'twitter' => 'https://twitter.com/emilydavis_edu'
                ]),
                'achievements' => json_encode([
                    'Outstanding English Teacher Award',
                    'Published poet and author',
                    'Debate team coach'
                ]),
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Robert Wilson',
                'slug' => 'robert-wilson',
                'position' => 'Athletic Director',
                'department' => 'Athletics',
                'email' => 'rwilson@school.edu',
                'phone' => '(555) 123-4540',
                'bio' => 'Coach Wilson leads our athletic programs with a focus on character development, teamwork, and healthy competition.',
                'qualifications' => json_encode([
                    'M.S. in Sports Management - University of Florida',
                    'B.S. in Kinesiology - University of Texas',
                    'Certified Athletic Administrator',
                    'CPR and First Aid Certified'
                ]),
                'specializations' => json_encode(['Sports Management', 'Athletic Training', 'Team Coaching', 'Health & Fitness']),
                'years_experience' => 18,
                'photo' => '/images/staff/robert-wilson.jpg',
                'office_location' => 'Athletic Center, Room 100',
                'office_hours' => 'Monday-Friday: 7:00 AM - 4:00 PM',
                'is_featured' => true,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/robertwilson'
                ]),
                'achievements' => json_encode([
                    'Athletic Director of the Year',
                    'Championship coach in multiple sports',
                    'Youth sports development advocate'
                ]),
                'sort_order' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Lisa Martinez',
                'slug' => 'lisa-martinez',
                'position' => 'Arts Department Coordinator',
                'department' => 'Arts',
                'email' => 'lmartinez@school.edu',
                'phone' => '(555) 123-4550',
                'bio' => 'Ms. Martinez nurtures artistic talent and creativity, providing students with opportunities to express themselves through various art forms.',
                'qualifications' => json_encode([
                    'M.F.A. in Visual Arts - Rhode Island School of Design',
                    'B.A. in Art Education - California College of the Arts',
                    'Teaching Certification in Visual Arts',
                    'Museum Education Certificate'
                ]),
                'specializations' => json_encode(['Visual Arts', 'Art History', 'Digital Arts', 'Art Therapy']),
                'years_experience' => 9,
                'photo' => '/images/staff/lisa-martinez.jpg',
                'office_location' => 'Arts Building, Room 150',
                'office_hours' => 'Monday-Friday: 3:15 PM - 4:45 PM',
                'is_featured' => false,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/lisamartinez',
                    'instagram' => 'https://instagram.com/lmartinez_art'
                ]),
                'achievements' => json_encode([
                    'Art Educator Excellence Award',
                    'Professional artist with gallery exhibitions',
                    'Art therapy workshop leader'
                ]),
                'sort_order' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'David Kim',
                'slug' => 'david-kim',
                'position' => 'Technology Coordinator',
                'department' => 'Technology',
                'email' => 'dkim@school.edu',
                'phone' => '(555) 123-4560',
                'bio' => 'Mr. Kim integrates technology into education, ensuring students are prepared for the digital future while maintaining digital citizenship.',
                'qualifications' => json_encode([
                    'M.S. in Educational Technology - Georgia Tech',
                    'B.S. in Computer Science - Carnegie Mellon University',
                    'Educational Technology Certification',
                    'Network Administration Certification'
                ]),
                'specializations' => json_encode(['Educational Technology', 'Programming', 'Digital Citizenship', 'STEM Integration']),
                'years_experience' => 8,
                'photo' => '/images/staff/david-kim.jpg',
                'office_location' => 'Technology Center, Room 120',
                'office_hours' => 'Monday-Friday: 3:00 PM - 4:30 PM',
                'is_featured' => false,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/davidkim',
                    'github' => 'https://github.com/dkim-edu'
                ]),
                'achievements' => json_encode([
                    'Technology Innovation in Education Award',
                    'Coding bootcamp instructor',
                    'Digital equity advocate'
                ]),
                'sort_order' => 8,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Amanda Brown',
                'slug' => 'amanda-brown',
                'position' => 'School Counselor',
                'department' => 'Student Services',
                'email' => 'abrown@school.edu',
                'phone' => '(555) 123-4570',
                'bio' => 'Ms. Brown supports student mental health and academic planning, helping students navigate challenges and plan for their futures.',
                'qualifications' => json_encode([
                    'M.S. in School Counseling - University of Pennsylvania',
                    'B.A. in Psychology - Duke University',
                    'Licensed Professional Counselor',
                    'Crisis Intervention Certified'
                ]),
                'specializations' => json_encode(['Academic Counseling', 'Career Planning', 'Mental Health', 'Crisis Intervention']),
                'years_experience' => 11,
                'photo' => '/images/staff/amanda-brown.jpg',
                'office_location' => 'Counseling Center, Room 80',
                'office_hours' => 'Monday-Friday: 8:00 AM - 4:00 PM',
                'is_featured' => false,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/amandabrown'
                ]),
                'achievements' => json_encode([
                    'School Counselor of the Year',
                    'Mental health advocacy speaker',
                    'College prep workshop leader'
                ]),
                'sort_order' => 9,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Thomas Lee',
                'slug' => 'thomas-lee',
                'position' => 'Librarian',
                'department' => 'Library Services',
                'email' => 'tlee@school.edu',
                'phone' => '(555) 123-4580',
                'bio' => 'Mr. Lee manages our library resources and promotes literacy, research skills, and information literacy among students and staff.',
                'qualifications' => json_encode([
                    'M.L.I.S. - Library and Information Science - University of Washington',
                    'B.A. in History - Northwestern University',
                    'School Library Media Specialist Certification',
                    'Digital Archives Certification'
                ]),
                'specializations' => json_encode(['Information Literacy', 'Research Methods', 'Digital Resources', 'Literature']),
                'years_experience' => 13,
                'photo' => '/images/staff/thomas-lee.jpg',
                'office_location' => 'Library, Information Desk',
                'office_hours' => 'Monday-Friday: 7:30 AM - 4:00 PM',
                'is_featured' => false,
                'is_leadership' => false,
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/thomaslee'
                ]),
                'achievements' => json_encode([
                    'Outstanding Library Media Specialist',
                    'Reading promotion program developer',
                    'Information literacy curriculum designer'
                ]),
                'sort_order' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($staffMembers as $staffData) {
            StaffMember::create($staffData);
        }

        $this->command->info('Staff members seeded successfully!');
    }
}