<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Entities\Event;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $events = [
            [
                'title' => 'Spring Open House',
                'slug' => 'spring-open-house-2024',
                'description' => 'Join us for our annual Spring Open House to learn about our academic programs, meet our faculty, and tour our facilities.',
                'content' => $this->getOpenHouseContent(),
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(15)->addHours(4),
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'event_type' => 'open_house',
                'location' => 'Main Campus - Auditorium and Various Classrooms',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 200,
                'registration_required' => true,
                'registration_deadline' => Carbon::now()->addDays(10),
                'price' => 0.00,
                'featured_image' => '/images/events/spring-open-house.jpg',
                'gallery_images' => json_encode([
                    '/images/events/open-house-1.jpg',
                    '/images/events/open-house-2.jpg',
                    '/images/events/open-house-3.jpg'
                ]),
                'contact_person' => 'Admissions Office',
                'contact_email' => 'admissions@school.edu',
                'contact_phone' => '(555) 123-4567',
                'is_featured' => true,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'Spring Open House 2024 - Discover Our School',
                'meta_description' => 'Attend our Spring Open House to explore our campus, meet teachers, and learn about our exceptional educational programs.',
                'tags' => json_encode(['open house', 'admissions', 'tour', 'information', 'prospective students']),
                'external_url' => null,
                'registration_count' => 45,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Annual Science Fair',
                'slug' => 'annual-science-fair-2024',
                'description' => 'Students showcase their innovative science projects in our annual Science Fair competition with prizes and recognition.',
                'content' => $this->getScienceFairContent(),
                'start_date' => Carbon::now()->addDays(25),
                'end_date' => Carbon::now()->addDays(25)->addHours(6),
                'start_time' => '08:00:00',
                'end_time' => '14:00:00',
                'event_type' => 'academic',
                'location' => 'School Gymnasium and Science Wing',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 500,
                'registration_required' => false,
                'registration_deadline' => null,
                'price' => 0.00,
                'featured_image' => '/images/events/science-fair.jpg',
                'gallery_images' => json_encode([
                    '/images/events/science-fair-1.jpg',
                    '/images/events/science-fair-2.jpg',
                    '/images/events/science-fair-3.jpg'
                ]),
                'contact_person' => 'Dr. Sarah Johnson',
                'contact_email' => 's.johnson@school.edu',
                'contact_phone' => '(555) 123-4570',
                'is_featured' => true,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'Annual Science Fair 2024 - Student Innovation Showcase',
                'meta_description' => 'Experience student innovation at our Annual Science Fair featuring creative projects and scientific discoveries.',
                'tags' => json_encode(['science fair', 'STEM', 'innovation', 'competition', 'students', 'projects']),
                'external_url' => null,
                'registration_count' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Spring Arts Festival',
                'slug' => 'spring-arts-festival-2024',
                'description' => 'A celebration of creativity featuring student artwork, musical performances, theatrical productions, and interactive workshops.',
                'content' => $this->getArtsFestivalContent(),
                'start_date' => Carbon::now()->addDays(32),
                'end_date' => Carbon::now()->addDays(33)->addHours(8),
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'event_type' => 'arts',
                'location' => 'Arts Center and Theater',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 300,
                'registration_required' => true,
                'registration_deadline' => Carbon::now()->addDays(28),
                'price' => 5.00,
                'featured_image' => '/images/events/arts-festival.jpg',
                'gallery_images' => json_encode([
                    '/images/events/arts-festival-1.jpg',
                    '/images/events/arts-festival-2.jpg',
                    '/images/events/arts-festival-3.jpg'
                ]),
                'contact_person' => 'Ms. Emily Rodriguez',
                'contact_email' => 'e.rodriguez@school.edu',
                'contact_phone' => '(555) 123-4571',
                'is_featured' => true,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'Spring Arts Festival 2024 - Creative Celebration',
                'meta_description' => 'Join our Spring Arts Festival for an evening of student creativity including art, music, and theatrical performances.',
                'tags' => json_encode(['arts festival', 'creativity', 'music', 'theater', 'visual arts', 'performance']),
                'external_url' => null,
                'registration_count' => 127,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Parent-Teacher Conference Week',
                'slug' => 'parent-teacher-conference-week',
                'description' => 'Scheduled meetings between parents and teachers to discuss student progress, goals, and support strategies.',
                'content' => $this->getParentTeacherContent(),
                'start_date' => Carbon::now()->addDays(40),
                'end_date' => Carbon::now()->addDays(44)->addHours(8),
                'start_time' => '15:00:00',
                'end_time' => '20:00:00',
                'event_type' => 'conference',
                'location' => 'Individual Classrooms',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 1000,
                'registration_required' => true,
                'registration_deadline' => Carbon::now()->addDays(35),
                'price' => 0.00,
                'featured_image' => '/images/events/parent-teacher-conference.jpg',
                'gallery_images' => null,
                'contact_person' => 'Main Office',
                'contact_email' => 'office@school.edu',
                'contact_phone' => '(555) 123-4567',
                'is_featured' => false,
                'is_public' => false,
                'status' => 'published',
                'meta_title' => 'Parent-Teacher Conference Week - Academic Collaboration',
                'meta_description' => 'Schedule your parent-teacher conference to discuss your child\'s academic progress and development.',
                'tags' => json_encode(['parent conference', 'academic progress', 'meetings', 'education', 'collaboration']),
                'external_url' => null,
                'registration_count' => 234,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Regional Basketball Championship',
                'slug' => 'regional-basketball-championship',
                'description' => 'Cheer on our varsity basketball team as they compete in the regional championship game.',
                'content' => $this->getBasketballChampionshipContent(),
                'start_date' => Carbon::now()->addDays(18),
                'end_date' => Carbon::now()->addDays(18)->addHours(3),
                'start_time' => '19:00:00',
                'end_time' => '22:00:00',
                'event_type' => 'sports',
                'location' => 'Regional Sports Complex',
                'address' => '456 Sports Avenue, Athletic City, AC 54321',
                'capacity' => 2000,
                'registration_required' => false,
                'registration_deadline' => null,
                'price' => 8.00,
                'featured_image' => '/images/events/basketball-championship.jpg',
                'gallery_images' => json_encode([
                    '/images/events/basketball-1.jpg',
                    '/images/events/basketball-2.jpg'
                ]),
                'contact_person' => 'Coach David Mitchell',
                'contact_email' => 'd.mitchell@school.edu',
                'contact_phone' => '(555) 123-4572',
                'is_featured' => true,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'Regional Basketball Championship - Support Our Team',
                'meta_description' => 'Join us to support our varsity basketball team in the exciting regional championship game.',
                'tags' => json_encode(['basketball', 'championship', 'sports', 'athletics', 'competition', 'school spirit']),
                'external_url' => null,
                'registration_count' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'STEM Career Fair',
                'slug' => 'stem-career-fair-2024',
                'description' => 'Explore STEM career opportunities with professionals from technology, engineering, medicine, and research fields.',
                'content' => $this->getSTEMCareerFairContent(),
                'start_date' => Carbon::now()->addDays(28),
                'end_date' => Carbon::now()->addDays(28)->addHours(4),
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
                'event_type' => 'career',
                'location' => 'School Auditorium and Library',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 400,
                'registration_required' => true,
                'registration_deadline' => Carbon::now()->addDays(21),
                'price' => 0.00,
                'featured_image' => '/images/events/stem-career-fair.jpg',
                'gallery_images' => json_encode([
                    '/images/events/career-fair-1.jpg',
                    '/images/events/career-fair-2.jpg'
                ]),
                'contact_person' => 'Career Counseling Office',
                'contact_email' => 'careers@school.edu',
                'contact_phone' => '(555) 123-4573',
                'is_featured' => false,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'STEM Career Fair 2024 - Explore Future Opportunities',
                'meta_description' => 'Discover exciting STEM career paths at our career fair featuring industry professionals and interactive demonstrations.',
                'tags' => json_encode(['STEM careers', 'career fair', 'technology', 'engineering', 'science', 'future planning']),
                'external_url' => null,
                'registration_count' => 78,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Graduation Ceremony 2024',
                'slug' => 'graduation-ceremony-2024',
                'description' => 'Celebrate the achievements of our graduating class as they embark on their next chapter.',
                'content' => $this->getGraduationContent(),
                'start_date' => Carbon::now()->addDays(60),
                'end_date' => Carbon::now()->addDays(60)->addHours(3),
                'start_time' => '10:00:00',
                'end_time' => '13:00:00',
                'event_type' => 'ceremony',
                'location' => 'Football Stadium',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 3000,
                'registration_required' => true,
                'registration_deadline' => Carbon::now()->addDays(45),
                'price' => 0.00,
                'featured_image' => '/images/events/graduation-ceremony.jpg',
                'gallery_images' => json_encode([
                    '/images/events/graduation-1.jpg',
                    '/images/events/graduation-2.jpg',
                    '/images/events/graduation-3.jpg'
                ]),
                'contact_person' => 'Registrar Office',
                'contact_email' => 'registrar@school.edu',
                'contact_phone' => '(555) 123-4574',
                'is_featured' => true,
                'is_public' => false,
                'status' => 'published',
                'meta_title' => 'Graduation Ceremony 2024 - Celebrating Achievement',
                'meta_description' => 'Join us for the graduation ceremony celebrating our students\' achievements and future success.',
                'tags' => json_encode(['graduation', 'ceremony', 'achievement', 'celebration', 'commencement', 'seniors']),
                'external_url' => null,
                'registration_count' => 456,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Community Service Day',
                'slug' => 'community-service-day-2024',
                'description' => 'Join students, staff, and families for a day of community service projects making a positive impact in our local area.',
                'content' => $this->getCommunityServiceContent(),
                'start_date' => Carbon::now()->addDays(35),
                'end_date' => Carbon::now()->addDays(35)->addHours(8),
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'event_type' => 'service',
                'location' => 'Various Community Locations',
                'address' => 'Meet at School Main Parking Lot',
                'capacity' => 300,
                'registration_required' => true,
                'registration_deadline' => Carbon::now()->addDays(28),
                'price' => 0.00,
                'featured_image' => '/images/events/community-service-day.jpg',
                'gallery_images' => json_encode([
                    '/images/events/service-1.jpg',
                    '/images/events/service-2.jpg'
                ]),
                'contact_person' => 'Service Learning Coordinator',
                'contact_email' => 'service@school.edu',
                'contact_phone' => '(555) 123-4575',
                'is_featured' => false,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'Community Service Day 2024 - Making a Difference',
                'meta_description' => 'Participate in Community Service Day and help make a positive impact in our local community.',
                'tags' => json_encode(['community service', 'volunteer', 'giving back', 'community impact', 'service learning']),
                'external_url' => null,
                'registration_count' => 89,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Music Department Spring Concert',
                'slug' => 'music-spring-concert-2024',
                'description' => 'An evening of musical performances featuring our talented student musicians in band, orchestra, and choir.',
                'content' => $this->getMusicConcertContent(),
                'start_date' => Carbon::now()->addDays(42),
                'end_date' => Carbon::now()->addDays(42)->addHours(2.5),
                'start_time' => '19:30:00',
                'end_time' => '22:00:00',
                'event_type' => 'performance',
                'location' => 'School Auditorium',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 500,
                'registration_required' => false,
                'registration_deadline' => null,
                'price' => 3.00,
                'featured_image' => '/images/events/spring-concert.jpg',
                'gallery_images' => json_encode([
                    '/images/events/concert-1.jpg',
                    '/images/events/concert-2.jpg'
                ]),
                'contact_person' => 'Mr. James Anderson',
                'contact_email' => 'j.anderson@school.edu',
                'contact_phone' => '(555) 123-4576',
                'is_featured' => false,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'Spring Concert 2024 - Musical Excellence',
                'meta_description' => 'Enjoy an evening of beautiful music performed by our talented student musicians.',
                'tags' => json_encode(['music concert', 'performance', 'band', 'orchestra', 'choir', 'student talent']),
                'external_url' => null,
                'registration_count' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'International Cultural Night',
                'slug' => 'international-cultural-night-2024',
                'description' => 'Celebrate diversity with an evening of international food, music, dance, and cultural presentations from around the world.',
                'content' => $this->getCulturalNightContent(),
                'start_date' => Carbon::now()->addDays(50),
                'end_date' => Carbon::now()->addDays(50)->addHours(4),
                'start_time' => '17:00:00',
                'end_time' => '21:00:00',
                'event_type' => 'cultural',
                'location' => 'School Cafeteria and Gymnasium',
                'address' => '123 Education Street, Learning City, LC 12345',
                'capacity' => 400,
                'registration_required' => true,
                'registration_deadline' => Carbon::now()->addDays(43),
                'price' => 10.00,
                'featured_image' => '/images/events/cultural-night.jpg',
                'gallery_images' => json_encode([
                    '/images/events/cultural-1.jpg',
                    '/images/events/cultural-2.jpg',
                    '/images/events/cultural-3.jpg'
                ]),
                'contact_person' => 'International Student Services',
                'contact_email' => 'international@school.edu',
                'contact_phone' => '(555) 123-4577',
                'is_featured' => true,
                'is_public' => true,
                'status' => 'published',
                'meta_title' => 'International Cultural Night 2024 - Celebrating Diversity',
                'meta_description' => 'Experience cultures from around the world at our International Cultural Night featuring food, music, and performances.',
                'tags' => json_encode(['cultural night', 'diversity', 'international', 'food', 'music', 'dance', 'celebration']),
                'external_url' => null,
                'registration_count' => 156,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        $this->command->info('Events seeded successfully!');
    }

    private function getOpenHouseContent()
    {
        return '
            <h3>Welcome to Our Spring Open House</h3>
            <p>We invite prospective students and their families to discover what makes our school special. This comprehensive event will give you the opportunity to explore our campus, meet our dedicated faculty, and learn about our exceptional academic programs.</p>

            <h4>Event Schedule</h4>
            <ul>
                <li><strong>9:00 AM - 9:30 AM:</strong> Registration and Welcome Coffee</li>
                <li><strong>9:30 AM - 10:15 AM:</strong> Principal\'s Welcome Presentation</li>
                <li><strong>10:30 AM - 11:30 AM:</strong> Campus Tours (Groups A & B)</li>
                <li><strong>11:45 AM - 12:30 PM:</strong> Academic Department Showcases</li>
                <li><strong>12:30 PM - 1:00 PM:</strong> Q&A Session and Closing</li>
            </ul>

            <h4>What You\'ll Experience</h4>
            <ul>
                <li>Guided tours of our modern facilities</li>
                <li>Meet our experienced teachers and staff</li>
                <li>Learn about our curriculum and programs</li>
                <li>Discover extracurricular opportunities</li>
                <li>Understand our admission process</li>
                <li>Connect with current students and parents</li>
            </ul>

            <h4>Academic Highlights</h4>
            <p>During the open house, you\'ll have the chance to visit our:</p>
            <ul>
                <li>State-of-the-art STEM laboratories</li>
                <li>Well-equipped art and music studios</li>
                <li>Modern library and media center</li>
                <li>Athletic facilities and sports programs</li>
                <li>Technology-integrated classrooms</li>
            </ul>

            <h4>Admission Information</h4>
            <p>Our admissions team will be available to discuss:</p>
            <ul>
                <li>Application requirements and deadlines</li>
                <li>Financial aid and scholarship opportunities</li>
                <li>Placement testing information</li>
                <li>Transportation options</li>
                <li>School policies and procedures</li>
            </ul>

            <h4>Registration Required</h4>
            <p>Please register by <strong>' . Carbon::now()->addDays(10)->format('F j, Y') . '</strong> to ensure we can accommodate your visit. Registration includes:</p>
            <ul>
                <li>Reserved seating for presentations</li>
                <li>Welcome packet with school information</li>
                <li>Light refreshments</li>
                <li>Priority scheduling for individual meetings</li>
            </ul>

            <p>We look forward to welcoming you to our school community and showing you why our students thrive here!</p>
        ';
    }

    private function getScienceFairContent()
    {
        return '
            <h3>Annual Science Fair 2024</h3>
            <p>Join us for our most exciting science fair yet, featuring innovative projects from students across all grade levels. This year\'s theme is "Science for a Sustainable Future," highlighting research and discoveries that can help build a better world.</p>

            <h4>Event Schedule</h4>
            <ul>
                <li><strong>8:00 AM - 9:00 AM:</strong> Setup and Final Preparations</li>
                <li><strong>9:00 AM - 11:00 AM:</strong> Judging Period (Closed to Public)</li>
                <li><strong>11:00 AM - 1:00 PM:</strong> Public Viewing and Student Presentations</li>
                <li><strong>1:00 PM - 1:30 PM:</strong> Awards Ceremony</li>
                <li><strong>1:30 PM - 2:00 PM:</strong> Reception and Congratulations</li>
            </ul>

            <h4>Project Categories</h4>
            <ul>
                <li><strong>Physical Sciences:</strong> Physics, chemistry, engineering</li>
                <li><strong>Life Sciences:</strong> Biology, medicine, environmental science</li>
                <li><strong>Earth & Space Sciences:</strong> Geology, astronomy, meteorology</li>
                <li><strong>Mathematics & Computer Science:</strong> Algorithms, data analysis, modeling</li>
                <li><strong>Environmental Sciences:</strong> Sustainability, conservation, renewable energy</li>
            </ul>

            <h4>Special Features</h4>
            <ul>
                <li>Interactive demonstrations throughout the day</li>
                <li>Guest judges from local universities and research institutions</li>
                <li>Special recognition awards for innovation and creativity</li>
                <li>Science activity stations for younger visitors</li>
                <li>Poster sessions with student researchers</li>
            </ul>

            <h4>Awards and Recognition</h4>
            <p>Winners will receive:</p>
            <ul>
                <li>First, second, and third place awards in each category</li>
                <li>Special recognition for outstanding achievement</li>
                <li>Opportunity to advance to regional competitions</li>
                <li>Science department scholarships for seniors</li>
                <li>Recognition in school publications</li>
            </ul>

            <h4>Community Engagement</h4>
            <p>This event showcases our students\' dedication to scientific inquiry and their potential to contribute to solving real-world problems. We encourage community members to attend and support our young scientists.</p>

            <p>Free admission - All are welcome to attend and celebrate scientific discovery!</p>
        ';
    }

    private function getArtsFestivalContent()
    {
        return '
            <h3>Spring Arts Festival 2024</h3>
            <p>Immerse yourself in creativity at our annual Spring Arts Festival, a two-day celebration of student artistic achievement across visual arts, performing arts, and digital media.</p>

            <h4>Friday Evening - Opening Night</h4>
            <ul>
                <li><strong>6:00 PM:</strong> Gallery Opening Reception</li>
                <li><strong>7:00 PM:</strong> Chamber Music Concert</li>
                <li><strong>8:00 PM:</strong> Drama Club Preview Performance</li>
                <li><strong>9:00 PM:</strong> Artist Meet & Greet</li>
            </ul>

            <h4>Saturday - Main Festival</h4>
            <ul>
                <li><strong>2:00 PM:</strong> Interactive Art Workshops</li>
                <li><strong>3:30 PM:</strong> Student Art Awards Ceremony</li>
                <li><strong>4:00 PM:</strong> Jazz Ensemble Performance</li>
                <li><strong>5:00 PM:</strong> Poetry and Creative Writing Reading</li>
                <li><strong>6:00 PM:</strong> Dance Performance Showcase</li>
                <li><strong>7:30 PM:</strong> Main Stage Theater Production</li>
            </ul>

            <h4>Featured Exhibitions</h4>
            <ul>
                <li><strong>Visual Arts Gallery:</strong> Paintings, sculptures, and mixed media</li>
                <li><strong>Photography Exhibition:</strong> Student photography from around the world</li>
                <li><strong>Digital Arts Showcase:</strong> Animation, graphic design, and digital art</li>
                <li><strong>Fashion Design Display:</strong> Student-created clothing and accessories</li>
            </ul>

            <h4>Interactive Workshops</h4>
            <p>Hands-on activities for all ages:</p>
            <ul>
                <li>Pottery and ceramics demonstrations</li>
                <li>Watercolor painting sessions</li>
                <li>Creative writing workshops</li>
                <li>Basic music composition</li>
                <li>Digital art tutorials</li>
            </ul>

            <h4>Performances</h4>
            <ul>
                <li><strong>Theater:</strong> "A Midsummer Night\'s Dream" by our Drama Club</li>
                <li><strong>Music:</strong> Concert band, jazz ensemble, and vocal performances</li>
                <li><strong>Dance:</strong> Modern, classical, and cultural dance presentations</li>
                <li><strong>Poetry:</strong> Original student works and classical pieces</li>
            </ul>

            <h4>Awards and Recognition</h4>
            <p>Outstanding student artists will be recognized with:</p>
            <ul>
                <li>Best in Show awards for each artistic medium</li>
                <li>People\'s Choice Award voted by festival attendees</li>
                <li>Scholarship opportunities for graduating seniors</li>
                <li>Featured display in the school\'s permanent collection</li>
            </ul>

            <h4>Food and Vendors</h4>
            <p>Support the arts while enjoying:</p>
            <ul>
                <li>Student-run café with homemade treats</li>
                <li>Local artist vendor booths</li>
                <li>Art supplies and craft materials for sale</li>
                <li>Silent auction featuring student artwork</li>
            </ul>

            <p><strong>Tickets:</strong> $5 general admission includes all performances and exhibitions. Children under 12 free with adult admission.</p>
        ';
    }

    private function getParentTeacherContent()
    {
        return '
            <h3>Parent-Teacher Conference Week</h3>
            <p>We invite all parents and guardians to participate in our comprehensive conference week, designed to foster communication between home and school for the benefit of every student.</p>

            <h4>Conference Schedule</h4>
            <ul>
                <li><strong>Monday, ' . Carbon::now()->addDays(40)->format('F j') . ':</strong> Grades 9-10 (3:00 PM - 8:00 PM)</li>
                <li><strong>Tuesday, ' . Carbon::now()->addDays(41)->format('F j') . ':</strong> Grades 11-12 (3:00 PM - 8:00 PM)</li>
                <li><strong>Wednesday, ' . Carbon::now()->addDays(42)->format('F j') . ':</strong> All Grades - Make-up Day (1:00 PM - 6:00 PM)</li>
                <li><strong>Thursday, ' . Carbon::now()->addDays(43)->format('F j') . ':</strong> Special Education IEP Meetings (3:00 PM - 8:00 PM)</li>
                <li><strong>Friday, ' . Carbon::now()->addDays(44)->format('F j') . ':</strong> Virtual Conferences Only (3:00 PM - 8:00 PM)</li>
            </ul>

            <h4>How to Schedule</h4>
            <ol>
                <li>Log into the parent portal at portal.school.edu</li>
                <li>Click on "Conference Scheduling"</li>
                <li>Select available time slots with your child\'s teachers</li>
                <li>Confirm appointments via email notification</li>
                <li>Add appointments to your calendar</li>
            </ol>

            <h4>Conference Format</h4>
            <ul>
                <li><strong>Individual Meetings:</strong> 15-minute sessions with each teacher</li>
                <li><strong>Team Conferences:</strong> 30-minute sessions for students needing coordinated support</li>
                <li><strong>Virtual Options:</strong> Video conferences for families unable to attend in person</li>
                <li><strong>Translation Services:</strong> Available upon request in Spanish, French, and Mandarin</li>
            </ul>

            <h4>What to Expect</h4>
            <p>During your conference, teachers will discuss:</p>
            <ul>
                <li>Your child\'s academic progress and current grades</li>
                <li>Strengths and areas for improvement</li>
                <li>Study habits and homework completion</li>
                <li>Social and emotional development</li>
                <li>Extracurricular participation and interests</li>
                <li>Goals for the remainder of the school year</li>
            </ul>

            <h4>Preparing for Your Conference</h4>
            <p>To make the most of your time:</p>
            <ul>
                <li>Review your child\'s recent report card and assignments</li>
                <li>Prepare specific questions about academic progress</li>
                <li>Discuss any concerns about homework or studying</li>
                <li>Share information about your child\'s interests and challenges</li>
                <li>Bring a notebook to record important information</li>
            </ul>

            <h4>Additional Resources</h4>
            <p>During conference week, the following will also be available:</p>
            <ul>
                <li>College counseling information sessions</li>
                <li>Special education resource consultations</li>
                <li>Tutoring program enrollment</li>
                <li>Mental health and wellness resources</li>
                <li>Technology support for online learning tools</li>
            </ul>

            <h4>Childcare Services</h4>
            <p>Free childcare will be provided in the school library for children ages 3-12 during conference hours. Registration required when scheduling conferences.</p>

            <p>We look forward to partnering with you in your child\'s educational journey!</p>
        ';
    }

    private function getBasketballChampionshipContent()
    {
        return '
            <h3>Regional Basketball Championship</h3>
            <p>Our Eagles are heading to the regional championship! Join us as our varsity basketball team takes on the Central High Wildcats in what promises to be an exciting and competitive game.</p>

            <h4>Game Information</h4>
            <ul>
                <li><strong>Date:</strong> ' . Carbon::now()->addDays(18)->format('l, F j, Y') . '</li>
                <li><strong>Time:</strong> 7:00 PM tip-off</li>
                <li><strong>Location:</strong> Regional Sports Complex</li>
                <li><strong>Address:</strong> 456 Sports Avenue, Athletic City</li>
                <li><strong>Parking:</strong> Free parking available on-site</li>
            </ul>

            <h4>Team Statistics</h4>
            <p>Our Eagles enter the championship with an impressive season record:</p>
            <ul>
                <li><strong>Season Record:</strong> 24-4 overall, 16-0 in conference</li>
                <li><strong>Average Points Per Game:</strong> 78.3</li>
                <li><strong>Team Leaders:</strong> Jason Rivers (18.5 ppg), Marcus Thompson (12.2 rpg)</li>
                <li><strong>Coach:</strong> David Mitchell (15 years experience)</li>
            </ul>

            <h4>Road to the Championship</h4>
            <p>Our team\'s path through the regional tournament:</p>
            <ul>
                <li><strong>Quarterfinals:</strong> Eagles 82, Riverside 65</li>
                <li><strong>Semifinals:</strong> Eagles 74, Mountain View 68</li>
                <li><strong>Championship:</strong> Eagles vs. Central High</li>
            </ul>

            <h4>Team Roster Highlights</h4>
            <ul>
                <li><strong>Jason Rivers (Senior):</strong> Team captain, 4-year starter, committed to State University</li>
                <li><strong>Marcus Thompson (Junior):</strong> Leading rebounder, defensive specialist</li>
                <li><strong>Tyler Johnson (Senior):</strong> Sharp-shooting guard, team\'s 3-point leader</li>
                <li><strong>Carlos Martinez (Sophomore):</strong> Rising star, excellent court vision</li>
                <li><strong>David Williams (Senior):</strong> Versatile forward, strong work ethic</li>
            </ul>

            <h4>Ticket Information</h4>
            <ul>
                <li><strong>Adults:</strong> $8.00</li>
                <li><strong>Students/Seniors:</strong> $5.00</li>
                <li><strong>Children Under 6:</strong> Free</li>
                <li><strong>Group Rates:</strong> Available for 10+ people</li>
            </ul>

            <h4>Fan Support</h4>
            <p>Show your Eagle pride! We encourage all fans to:</p>
            <ul>
                <li>Wear school colors (blue and gold)</li>
                <li>Arrive early for the best seats</li>
                <li>Join in traditional team chants</li>
                <li>Support our players with positive energy</li>
                <li>Stay for the entire game</li>
            </ul>

            <h4>Concessions and Merchandise</h4>
            <p>Available at the venue:</p>
            <ul>
                <li>Traditional concession stand items</li>
                <li>Team merchandise and spirit gear</li>
                <li>Championship t-shirts and memorabilia</li>
                <li>50/50 raffle supporting the athletics program</li>
            </ul>

            <h4>Transportation</h4>
            <p>For fans without transportation:</p>
            <ul>
                <li>Charter bus available from school parking lot</li>
                <li>Departure: 5:30 PM, Return: 10:30 PM</li>
                <li>Cost: $15 per person (includes game ticket)</li>
                <li>Register at the athletic office</li>
            </ul>

            <p>Let\'s pack the stands and cheer our Eagles to victory! Go Eagles!</p>
        ';
    }

    private function getSTEMCareerFairContent()
    {
        return '
            <h3>STEM Career Fair 2024</h3>
            <p>Discover your future in Science, Technology, Engineering, and Mathematics! Our comprehensive career fair brings together industry professionals, university representatives, and career experts to help students explore exciting STEM opportunities.</p>

            <h4>Participating Organizations</h4>
            <ul>
                <li><strong>Technology:</strong> Google, Microsoft, Apple, Tesla, SpaceX</li>
                <li><strong>Healthcare:</strong> Mayo Clinic, Johnson & Johnson, Pfizer</li>
                <li><strong>Engineering:</strong> Boeing, General Electric, Lockheed Martin</li>
                <li><strong>Research:</strong> National Institutes of Health, NASA, CDC</li>
                <li><strong>Universities:</strong> MIT, Stanford, Cal Tech, State University</li>
            </ul>

            <h4>Career Exploration Areas</h4>
            <ul>
                <li><strong>Computer Science & IT:</strong> Software development, cybersecurity, data science</li>
                <li><strong>Engineering:</strong> Mechanical, electrical, civil, aerospace, biomedical</li>
                <li><strong>Life Sciences:</strong> Biology, medicine, pharmacy, veterinary science</li>
                <li><strong>Physical Sciences:</strong> Physics, chemistry, materials science</li>
                <li><strong>Mathematics:</strong> Statistics, actuarial science, financial modeling</li>
                <li><strong>Environmental Science:</strong> Conservation, renewable energy, climate research</li>
            </ul>

            <h4>Interactive Demonstrations</h4>
            <p>Hands-on experiences throughout the day:</p>
            <ul>
                <li>Virtual reality engineering simulations</li>
                <li>Robotics programming challenges</li>
                <li>Medical equipment demonstrations</li>
                <li>3D printing and prototyping</li>
                <li>Environmental testing laboratories</li>
                <li>Coding workshops for beginners</li>
            </ul>

            <h4>Presentations and Workshops</h4>
            <ul>
                <li><strong>10:30 AM:</strong> "Breaking into Tech" - Panel Discussion</li>
                <li><strong>11:15 AM:</strong> "Women in STEM" - Success Stories</li>
                <li><strong>12:00 PM:</strong> "Engineering the Future" - Innovation Showcase</li>
                <li><strong>12:45 PM:</strong> "Medical Careers" - Healthcare Professionals Panel</li>
                <li><strong>1:30 PM:</strong> "Research Opportunities" - Graduate School Information</li>
            </ul>

            <h4>College and University Information</h4>
            <p>Representatives from top STEM programs will discuss:</p>
            <ul>
                <li>Admission requirements and application processes</li>
                <li>Scholarship and financial aid opportunities</li>
                <li>Research opportunities for undergraduates</li>
                <li>Internship and co-op programs</li>
                <li>Graduate school pathways</li>
            </ul>

            <h4>Career Planning Resources</h4>
            <ul>
                <li>One-on-one career counseling sessions</li>
                <li>Resume review and improvement tips</li>
                <li>Interview skills workshops</li>
                <li>Salary and job market information</li>
                <li>Professional networking guidance</li>
            </ul>

            <h4>Special Features</h4>
            <ul>
                <li><strong>STEM Skills Assessment:</strong> Discover your strengths and interests</li>
                <li><strong>Job Shadow Opportunities:</strong> Sign up for workplace visits</li>
                <li><strong>Internship Fair:</strong> Summer opportunities for high school students</li>
                <li><strong>Scholarship Information:</strong> STEM-specific funding opportunities</li>
            </ul>

            <h4>Who Should Attend</h4>
            <ul>
                <li>High school students interested in STEM careers</li>
                <li>Parents seeking information about STEM education paths</li>
                <li>Middle school students exploring future options</li>
                <li>Current college students considering career changes</li>
                <li>Anyone curious about STEM opportunities</li>
            </ul>

            <h4>Registration Benefits</h4>
            <p>Pre-registered attendees receive:</p>
            <ul>
                <li>Welcome packet with career information</li>
                <li>Priority seating for presentations</li>
                <li>Free lunch voucher</li>
                <li>Access to exclusive networking session</li>
                <li>Digital resource library access</li>
            </ul>

            <p>Register today to secure your spot and take the first step toward an exciting STEM career!</p>
        ';
    }

    private function getGraduationContent()
    {
        return '
            <h3>Graduation Ceremony 2024</h3>
            <p>Join us in celebrating the achievements of our graduating class as they embark on their next chapter. This momentous occasion recognizes their hard work, dedication, and the bright futures that await them.</p>

            <h4>Ceremony Schedule</h4>
            <ul>
                <li><strong>9:00 AM:</strong> Graduates arrive for final preparations</li>
                <li><strong>9:30 AM:</strong> Guest seating begins</li>
                <li><strong>10:00 AM:</strong> Processional and ceremony begins</li>
                <li><strong>11:00 AM:</strong> Diploma presentation</li>
                <li><strong>12:00 PM:</strong> Recessional</li>
                <li><strong>12:30 PM:</strong> Reception and photo opportunities</li>
            </ul>

            <h4>Class of 2024 Achievements</h4>
            <ul>
                <li><strong>Graduating Class Size:</strong> 156 students</li>
                <li><strong>Graduation Rate:</strong> 98%</li>
                <li><strong>College Acceptance:</strong> 95% of graduates accepted to college</li>
                <li><strong>Scholarships Awarded:</strong> $2.8 million in total scholarships</li>
                <li><strong>National Merit Scholars:</strong> 8 students</li>
                <li><strong>Advanced Placement Scholars:</strong> 23 students</li>
            </ul>

            <h4>Special Recognition</h4>
            <ul>
                <li><strong>Valedictorian:</strong> Sarah Johnson (4.0 GPA, Harvard University)</li>
                <li><strong>Salutatorian:</strong> Michael Chen (3.98 GPA, MIT)</li>
                <li><strong>Class President:</strong> Jennifer Martinez</li>
                <li><strong>Outstanding Service Award:</strong> David Rodriguez</li>
                <li><strong>Athletic Excellence:</strong> Lisa Thompson</li>
                <li><strong>Arts Achievement:</strong> James Wilson</li>
            </ul>

            <h4>Ceremony Speakers</h4>
            <ul>
                <li><strong>Welcome:</strong> Principal Dr. Robert Adams</li>
                <li><strong>Keynote Speaker:</strong> Dr. Angela Foster, NASA Scientist and School Alumna</li>
                <li><strong>Student Speaker:</strong> Sarah Johnson, Class Valedictorian</li>
                <li><strong>Board of Education:</strong> Chairperson Maria Rodriguez</li>
            </ul>

            <h4>Post-Graduation Plans</h4>
            <p>Our graduates are heading to outstanding institutions and opportunities:</p>
            <ul>
                <li><strong>Four-Year Universities:</strong> 85% (including Ivy League schools)</li>
                <li><strong>Community Colleges:</strong> 10%</li>
                <li><strong>Military Service:</strong> 3%</li>
                <li><strong>Gap Year/Work:</strong> 2%</li>
            </ul>

            <h4>Venue Information</h4>
            <ul>
                <li><strong>Location:</strong> Football Stadium (Indoor backup: Gymnasium)</li>
                <li><strong>Seating:</strong> General admission, first-come first-served</li>
                <li><strong>Parking:</strong> Free parking in designated lots</li>
                <li><strong>Accessibility:</strong> Wheelchair accessible seating available</li>
                <li><strong>Weather Policy:</strong> Ceremony will proceed rain or shine</li>
            </ul>

            <h4>Guest Guidelines</h4>
            <ul>
                <li>Arrive early for best seating (gates open at 9:30 AM)</li>
                <li>Bring sunscreen and water for outdoor ceremony</li>
                <li>Photography encouraged during processional and recessional</li>
                <li>Please remain seated during diploma presentation</li>
                <li>Save major celebrations for after the ceremony</li>
            </ul>

            <h4>Live Streaming</h4>
            <p>For those unable to attend in person:</p>
            <ul>
                <li>Live stream available on school website</li>
                <li>Professional recording will be available after ceremony</li>
                <li>Social media updates throughout the event</li>
                <li>Photos will be posted on school galleries</li>
            </ul>

            <h4>Reception Details</h4>
            <p>Immediately following the ceremony:</p>
            <ul>
                <li>Light refreshments in the school cafeteria</li>
                <li>Photo opportunities with graduates</li>
                <li>Yearbook signing station</li>
                <li>Alumni association registration</li>
                <li>Memory wall for family photos and messages</li>
            </ul>

            <h4>Graduation Requirements Completed</h4>
            <p>All graduates have successfully completed:</p>
            <ul>
                <li>Required coursework and credit hours</li>
                <li>State testing requirements</li>
                <li>Community service obligations (40 hours minimum)</li>
                <li>Senior project presentations</li>
                <li>Financial literacy certification</li>
            </ul>

            <p>We are incredibly proud of the Class of 2024 and excited to celebrate their achievements with our entire school community!</p>
        ';
    }

    private function getCommunityServiceContent()
    {
        return '
            <h3>Community Service Day 2024</h3>
            <p>Join students, staff, families, and community members for our annual day of service dedicated to making a positive impact in our local area. This is an opportunity to give back, build connections, and strengthen our community bonds.</p>

            <h4>Service Project Locations</h4>
            <ul>
                <li><strong>Liberty Park Cleanup:</strong> Litter removal, trail maintenance, garden planting</li>
                <li><strong>Senior Center Support:</strong> Technology assistance, meal service, activity leadership</li>
                <li><strong>Food Bank Operations:</strong> Sorting donations, packaging meals, inventory management</li>
                <li><strong>Elementary School Beautification:</strong> Painting, landscaping, playground maintenance</li>
                <li><strong>Community Garden:</strong> Planting, weeding, harvest preparation</li>
            </ul>

            <h4>Schedule</h4>
            <p><strong>Date:</strong> Saturday, April 20, 2024<br>
            <strong>Time:</strong> 8:00 AM - 4:00 PM<br>
            <strong>Check-in:</strong> School Main Lobby at 7:30 AM</p>

            <h4>What to Bring</h4>
            <ul>
                <li>Work clothes and closed-toe shoes</li>
                <li>Water bottle and snacks</li>
                <li>Positive attitude and willingness to help</li>
                <li>Sun protection (hat, sunscreen)</li>
            </ul>

            <h4>Registration</h4>
            <p>Sign up by April 15th through the school portal or at the main office. Community service hours will be officially recorded for student transcripts.</p>

            <p>For questions, contact the Community Service Coordinator at community@school.edu or call (555) 123-4567.</p>
        ';
}

private function getMusicConcertContent()
{
    return '
        <h3>Spring Music Concert 2024</h3>
        <p>Join us for an evening of beautiful music performed by our talented students from various music programs. This concert will showcase the hard work and dedication of our school bands, choirs, and orchestra.</p>

        <h4>Featured Performances</h4>
        <ul>
            <li><strong>Concert Band:</strong> Classical and contemporary pieces</li>
            <li><strong>Jazz Ensemble:</strong> Modern jazz standards and original compositions</li>
            <li><strong>School Choir:</strong> Choral arrangements from various genres</li>
            <li><strong>String Orchestra:</strong> Classical masterpieces and film scores</li>
            <li><strong>Solo Performances:</strong> Individual student showcases</li>
        </ul>

        <h4>Event Details</h4>
        <p><strong>Date:</strong> Friday, May 17, 2024<br>
        <strong>Time:</strong> 7:00 PM - 9:00 PM<br>
        <strong>Location:</strong> School Auditorium<br>
        <strong>Admission:</strong> Free (donations welcome)</p>

        <h4>Special Recognition</h4>
        <p>We will also be recognizing our graduating seniors from the music program and presenting awards for outstanding musical achievement.</p>

        <p>Light refreshments will be served during intermission. Come support our young musicians!</p>
    ';
}

private function getCulturalNightContent()
{
    return '
        <h3>Cultural Heritage Night 2024</h3>
        <p>Celebrate the rich diversity of our school community at our annual Cultural Heritage Night. This multicultural event brings together families and students to share traditions, food, music, and art from around the world.</p>

        <h4>Cultural Presentations</h4>
        <ul>
            <li><strong>Traditional Dance Performances:</strong> Folk dances from various cultures</li>
            <li><strong>Musical Performances:</strong> Traditional instruments and songs</li>
            <li><strong>Art Exhibitions:</strong> Student artwork inspired by cultural heritage</li>
            <li><strong>Storytelling Corner:</strong> Traditional stories and legends</li>
            <li><strong>Fashion Show:</strong> Traditional clothing from different cultures</li>
        </ul>

        <h4>International Food Fair</h4>
        <p>Experience flavors from around the world with authentic dishes prepared by our diverse families. Food tickets will be available at the event.</p>

        <h4>Interactive Activities</h4>
        <ul>
            <li>Cultural craft stations</li>
            <li>Henna and face painting</li>
            <li>Language learning activities</li>
            <li>Traditional games from different countries</li>
        </ul>

        <h4>Event Information</h4>
        <p><strong>Date:</strong> Saturday, March 16, 2024<br>
        <strong>Time:</strong> 5:00 PM - 8:00 PM<br>
        <strong>Location:</strong> School Gymnasium and Cafeteria<br>
        <strong>Admission:</strong> Free for families, $5 for general public</p>

        <p>Volunteers needed! Contact the main office if you would like to help with setup or cultural presentations.</p>
    ';
}
}
