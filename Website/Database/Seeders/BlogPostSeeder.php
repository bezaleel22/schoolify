<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\BlogCategory;
use Carbon\Carbon;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Get category IDs for random assignment
        $categories = BlogCategory::pluck('id')->toArray();

        $posts = [
            [
                'title' => 'Welcome to Our New School Website',
                'slug' => 'welcome-new-school-website',
                'excerpt' => 'We are excited to launch our new website with enhanced features, better navigation, and more resources for students, parents, and staff.',
                'content' => $this->getWelcomePostContent(),
                'featured_image' => '/images/blog/new-website-launch.jpg',
                'author_id' => 1,
                'category_id' => $categories[0] ?? 1, // School News
                'status' => 'published',
                'is_featured' => true,
                'tags' => json_encode(['website', 'technology', 'announcement', 'digital']),
                'meta_title' => 'New School Website Launch - Enhanced Digital Experience',
                'meta_description' => 'Discover our new school website with improved features, better navigation, and comprehensive resources for our community.',
                'reading_time' => 3,
                'views_count' => 245,
                'likes_count' => 18,
                'published_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Outstanding Academic Results for 2024',
                'slug' => 'outstanding-academic-results-2024',
                'excerpt' => 'Our students have achieved exceptional results this year, with 95% graduation rate and numerous academic honors and scholarships.',
                'content' => $this->getAcademicResultsContent(),
                'featured_image' => '/images/blog/academic-results-2024.jpg',
                'author_id' => 1,
                'category_id' => $categories[1] ?? 2, // Academic Excellence
                'status' => 'published',
                'is_featured' => true,
                'tags' => json_encode(['academics', 'results', 'achievement', 'graduation', 'success']),
                'meta_title' => '2024 Academic Results - Outstanding Student Achievement',
                'meta_description' => 'Celebrating our students\' exceptional academic achievements and graduation success in 2024.',
                'reading_time' => 4,
                'views_count' => 312,
                'likes_count' => 25,
                'published_at' => Carbon::now()->subDays(5),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Annual Science Fair Winners Announced',
                'slug' => 'annual-science-fair-winners-2024',
                'excerpt' => 'Congratulations to all participants and winners of our 2024 Science Fair, showcasing innovative projects and scientific creativity.',
                'content' => $this->getScienceFairContent(),
                'featured_image' => '/images/blog/science-fair-2024.jpg',
                'author_id' => 1,
                'category_id' => $categories[5] ?? 6, // Technology & Innovation
                'status' => 'published',
                'is_featured' => true,
                'tags' => json_encode(['science', 'fair', 'innovation', 'students', 'STEM', 'competition']),
                'meta_title' => 'Science Fair 2024 Winners - Innovation & Discovery',
                'meta_description' => 'Celebrate the winners and participants of our annual Science Fair showcasing student innovation and scientific discovery.',
                'reading_time' => 5,
                'views_count' => 198,
                'likes_count' => 22,
                'published_at' => Carbon::now()->subDays(7),
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'title' => 'Basketball Team Wins Regional Championship',
                'slug' => 'basketball-team-regional-championship',
                'excerpt' => 'Our varsity basketball team has brought home the regional championship trophy after an incredible season of hard work and determination.',
                'content' => $this->getBasketballChampionshipContent(),
                'featured_image' => '/images/blog/basketball-championship.jpg',
                'author_id' => 1,
                'category_id' => $categories[3] ?? 4, // Sports & Athletics
                'status' => 'published',
                'is_featured' => false,
                'tags' => json_encode(['basketball', 'championship', 'sports', 'team', 'victory', 'athletics']),
                'meta_title' => 'Basketball Regional Championship Victory 2024',
                'meta_description' => 'Our varsity basketball team claims regional championship title with outstanding teamwork and dedication.',
                'reading_time' => 3,
                'views_count' => 287,
                'likes_count' => 31,
                'published_at' => Carbon::now()->subDays(10),
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'Spring Arts Festival Showcase',
                'slug' => 'spring-arts-festival-showcase-2024',
                'excerpt' => 'Join us for our annual Spring Arts Festival featuring student artwork, musical performances, and theatrical productions.',
                'content' => $this->getArtsFestivalContent(),
                'featured_image' => '/images/blog/arts-festival-2024.jpg',
                'author_id' => 1,
                'category_id' => $categories[4] ?? 5, // Arts & Culture
                'status' => 'published',
                'is_featured' => false,
                'tags' => json_encode(['arts', 'festival', 'music', 'theater', 'creativity', 'performance']),
                'meta_title' => 'Spring Arts Festival 2024 - Creative Showcase',
                'meta_description' => 'Experience the creativity of our students at the annual Spring Arts Festival featuring diverse artistic performances.',
                'reading_time' => 4,
                'views_count' => 156,
                'likes_count' => 19,
                'published_at' => Carbon::now()->subDays(12),
                'created_at' => Carbon::now()->subDays(12),
                'updated_at' => Carbon::now()->subDays(12),
            ],
            [
                'title' => 'New STEM Laboratory Opens',
                'slug' => 'new-stem-laboratory-opens',
                'excerpt' => 'We are proud to unveil our state-of-the-art STEM laboratory, equipped with cutting-edge technology and research facilities.',
                'content' => $this->getSTEMLabContent(),
                'featured_image' => '/images/blog/stem-lab-opening.jpg',
                'author_id' => 1,
                'category_id' => $categories[5] ?? 6, // Technology & Innovation
                'status' => 'published',
                'is_featured' => true,
                'tags' => json_encode(['STEM', 'laboratory', 'technology', 'science', 'innovation', 'facilities']),
                'meta_title' => 'New STEM Laboratory - Advanced Learning Facility',
                'meta_description' => 'Explore our new state-of-the-art STEM laboratory designed to enhance science and technology education.',
                'reading_time' => 4,
                'views_count' => 201,
                'likes_count' => 16,
                'published_at' => Carbon::now()->subDays(15),
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'title' => 'Community Service Week Success',
                'slug' => 'community-service-week-success',
                'excerpt' => 'Our students and staff came together for Community Service Week, making a positive impact through various volunteer initiatives.',
                'content' => $this->getCommunityServiceContent(),
                'featured_image' => '/images/blog/community-service-week.jpg',
                'author_id' => 1,
                'category_id' => $categories[6] ?? 7, // Community Outreach
                'status' => 'published',
                'is_featured' => false,
                'tags' => json_encode(['community', 'service', 'volunteer', 'outreach', 'giving back', 'social responsibility']),
                'meta_title' => 'Community Service Week - Making a Difference Together',
                'meta_description' => 'Learn about our successful Community Service Week and how our school community made a positive impact.',
                'reading_time' => 3,
                'views_count' => 143,
                'likes_count' => 14,
                'published_at' => Carbon::now()->subDays(18),
                'created_at' => Carbon::now()->subDays(18),
                'updated_at' => Carbon::now()->subDays(18),
            ],
            [
                'title' => 'Parent-Teacher Conference Guidelines',
                'slug' => 'parent-teacher-conference-guidelines',
                'excerpt' => 'Important information for parents about upcoming parent-teacher conferences, scheduling, and how to prepare for productive meetings.',
                'content' => $this->getParentTeacherContent(),
                'featured_image' => '/images/blog/parent-teacher-conferences.jpg',
                'author_id' => 1,
                'category_id' => $categories[7] ?? 8, // Parent Resources
                'status' => 'published',
                'is_featured' => false,
                'tags' => json_encode(['parents', 'teachers', 'conferences', 'communication', 'education', 'meetings']),
                'meta_title' => 'Parent-Teacher Conferences - Guidelines & Preparation',
                'meta_description' => 'Everything parents need to know about upcoming parent-teacher conferences and how to prepare.',
                'reading_time' => 5,
                'views_count' => 89,
                'likes_count' => 7,
                'published_at' => Carbon::now()->subDays(20),
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'title' => 'Alumni Success Stories: Class of 2019',
                'slug' => 'alumni-success-stories-class-2019',
                'excerpt' => 'Celebrating the achievements of our 2019 graduates as they excel in colleges, careers, and making positive impacts in their communities.',
                'content' => $this->getAlumniSuccessContent(),
                'featured_image' => '/images/blog/alumni-success-2019.jpg',
                'author_id' => 1,
                'category_id' => $categories[8] ?? 9, // Alumni Updates
                'status' => 'published',
                'is_featured' => false,
                'tags' => json_encode(['alumni', 'success', 'graduates', 'achievements', 'college', 'career']),
                'meta_title' => 'Alumni Success Stories - Class of 2019 Achievements',
                'meta_description' => 'Discover the impressive achievements and success stories of our 2019 graduates in college and career.',
                'reading_time' => 6,
                'views_count' => 176,
                'likes_count' => 21,
                'published_at' => Carbon::now()->subDays(25),
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(25),
            ],
            [
                'title' => 'Health and Wellness Month Activities',
                'slug' => 'health-wellness-month-activities',
                'excerpt' => 'Join us for Health and Wellness Month with various activities focused on physical fitness, mental health, and healthy lifestyle choices.',
                'content' => $this->getHealthWellnessContent(),
                'featured_image' => '/images/blog/health-wellness-month.jpg',
                'author_id' => 1,
                'category_id' => $categories[9] ?? 10, // Health & Wellness
                'status' => 'published',
                'is_featured' => false,
                'tags' => json_encode(['health', 'wellness', 'fitness', 'mental health', 'lifestyle', 'activities']),
                'meta_title' => 'Health & Wellness Month - Promoting Healthy Living',
                'meta_description' => 'Participate in Health and Wellness Month activities designed to promote physical and mental well-being.',
                'reading_time' => 4,
                'views_count' => 92,
                'likes_count' => 12,
                'published_at' => Carbon::now()->subDays(28),
                'created_at' => Carbon::now()->subDays(28),
                'updated_at' => Carbon::now()->subDays(28),
            ],
        ];

        foreach ($posts as $postData) {
            BlogPost::create($postData);
        }

        $this->command->info('Blog posts seeded successfully!');
    }

    private function getWelcomePostContent()
    {
        return '
            <p>We are thrilled to announce the launch of our brand new school website! After months of planning, development, and testing, we are excited to share this enhanced digital platform with our entire school community.</p>

            <h3>What\'s New?</h3>
            <ul>
                <li><strong>Modern Design:</strong> A fresh, responsive design that works seamlessly across all devices</li>
                <li><strong>Improved Navigation:</strong> Easier access to information with intuitive menu structure</li>
                <li><strong>Enhanced Features:</strong> Better search functionality, interactive calendars, and multimedia galleries</li>
                <li><strong>Parent Portal:</strong> Secure access to student information and communication tools</li>
                <li><strong>Mobile Optimization:</strong> Full functionality on smartphones and tablets</li>
            </ul>

            <h3>Key Features</h3>
            <p>Our new website includes several exciting features designed to improve communication and engagement:</p>

            <h4>Real-time Updates</h4>
            <p>Stay informed with instant notifications about school news, events, and important announcements through our integrated notification system.</p>

            <h4>Interactive Calendar</h4>
            <p>View upcoming events, important dates, and school activities in our dynamic calendar that syncs with your personal devices.</p>

            <h4>Resource Library</h4>
            <p>Access educational resources, forms, policies, and documents through our comprehensive digital library.</p>

            <h3>Getting Started</h3>
            <p>We encourage all community members to explore the new features and create accounts to access personalized content. If you need assistance navigating the new site, please don\'t hesitate to contact our support team.</p>

            <p>Thank you for your patience during the transition, and we look forward to serving our community better through this improved digital platform.</p>
        ';
    }

    private function getAcademicResultsContent()
    {
        return '
            <p>We are incredibly proud to announce the outstanding academic achievements of our students for the 2024 academic year. These results reflect the hard work, dedication, and excellence that define our school community.</p>

            <h3>Key Achievements</h3>
            <ul>
                <li><strong>95% Graduation Rate:</strong> The highest in our school\'s history</li>
                <li><strong>$2.5 Million in Scholarships:</strong> Awarded to our graduating seniors</li>
                <li><strong>Advanced Placement Success:</strong> 89% pass rate on AP examinations</li>
                <li><strong>College Acceptance:</strong> 98% of graduates accepted to their top-choice colleges</li>
                <li><strong>National Merit Scholars:</strong> 12 students recognized for exceptional achievement</li>
            </ul>

            <h3>Academic Highlights</h3>
            
            <h4>STEM Excellence</h4>
            <p>Our STEM programs continue to excel with students earning recognition in national science competitions and mathematics olympiads. The robotics team placed second in the state championship, while our environmental science students won the regional sustainability challenge.</p>

            <h4>Humanities and Arts</h4>
            <p>Students in our humanities programs demonstrated exceptional writing and critical thinking skills, with several works published in national student journals. Our debate team secured first place in the state tournament.</p>

            <h4>Language Programs</h4>
            <p>Our world language students achieved remarkable proficiency levels, with 78% earning advanced or superior ratings on standardized assessments.</p>

            <h3>College Destinations</h3>
            <p>Our graduates have been accepted to prestigious institutions including Harvard, MIT, Stanford, Yale, and many other top-tier universities. We are particularly proud that 45% of our graduates received merit-based scholarships.</p>

            <h3>Looking Forward</h3>
            <p>These achievements motivate us to continue our commitment to academic excellence. We thank our dedicated faculty, supportive families, and hardworking students who make these successes possible.</p>
        ';
    }

    private function getScienceFairContent()
    {
        return '
            <p>Our annual Science Fair has once again showcased the incredible talent, creativity, and scientific curiosity of our students. This year\'s event featured over 150 projects spanning various scientific disciplines.</p>

            <h3>Winners by Category</h3>

            <h4>Physical Sciences</h4>
            <ul>
                <li><strong>1st Place:</strong> "Renewable Energy Storage Solutions" by Sarah Johnson (Grade 11)</li>
                <li><strong>2nd Place:</strong> "Quantum Computing Applications" by Michael Chen (Grade 12)</li>
                <li><strong>3rd Place:</strong> "Solar Panel Efficiency Optimization" by Emma Rodriguez (Grade 10)</li>
            </ul>

            <h4>Life Sciences</h4>
            <ul>
                <li><strong>1st Place:</strong> "Microplastics Impact on Marine Ecosystems" by David Kim (Grade 12)</li>
                <li><strong>2nd Place:</strong> "Plant-Based Water Purification Systems" by Lisa Thompson (Grade 11)</li>
                <li><strong>3rd Place:</strong> "Antibiotic Resistance in Soil Bacteria" by James Wilson (Grade 10)</li>
            </ul>

            <h4>Environmental Science</h4>
            <ul>
                <li><strong>1st Place:</strong> "Urban Air Quality Monitoring Network" by Maria Garcia (Grade 12)</li>
                <li><strong>2nd Place:</strong> "Sustainable Agriculture Techniques" by Robert Lee (Grade 11)</li>
                <li><strong>3rd Place:</strong> "Climate Change Impact on Local Wildlife" by Amanda Brown (Grade 9)</li>
            </ul>

            <h3>Special Recognition</h3>
            <p>This year, we introduced special awards recognizing exceptional aspects of scientific research:</p>

            <ul>
                <li><strong>Innovation Award:</strong> Alex Martinez for "AI-Powered Recycling Sorter"</li>
                <li><strong>Community Impact Award:</strong> Rachel Davis for "Accessible Technology for Disabilities"</li>
                <li><strong>Collaboration Award:</strong> The Grade 9 team project on "Sustainable School Gardens"</li>
            </ul>

            <h3>Judge Panel</h3>
            <p>We were honored to have distinguished scientists and researchers from local universities and research institutions serve as judges, providing valuable feedback and mentorship to our students.</p>

            <h3>Next Steps</h3>
            <p>Several projects will advance to regional and national competitions, and we encourage all participants to continue pursuing their scientific interests. Our STEM department will provide ongoing support for research continuation.</p>
        ';
    }

    private function getBasketballChampionshipContent()
    {
        return '
            <p>In a thrilling championship game that will be remembered for years to come, our varsity basketball team claimed the regional championship title with a decisive 78-65 victory over Central High School.</p>

            <h3>Championship Game Highlights</h3>
            <p>The game was a showcase of skill, teamwork, and determination. Our team maintained a strong defense throughout the game while executing precise offensive plays that secured the victory.</p>

            <h4>Outstanding Performances</h4>
            <ul>
                <li><strong>MVP: Jason Rivers (Senior)</strong> - 24 points, 8 rebounds, 6 assists</li>
                <li><strong>Marcus Thompson (Junior)</strong> - 18 points, 12 rebounds</li>
                <li><strong>Tyler Johnson (Senior)</strong> - 16 points, 5 three-pointers</li>
                <li><strong>Carlos Martinez (Sophomore)</strong> - 12 points, 7 assists</li>
            </ul>

            <h3>Season Statistics</h3>
            <p>The championship caps off an exceptional season with a 26-4 record:</p>
            <ul>
                <li>Regular Season: 20-2 (Conference Champions)</li>
                <li>Regional Tournament: 6-2</li>
                <li>Average margin of victory: 18 points</li>
                <li>Team scoring average: 82.3 points per game</li>
            </ul>

            <h3>Coach\'s Comments</h3>
            <blockquote>
                <p>"This championship is the result of incredible hard work, dedication, and team chemistry. These young men have shown what can be achieved when talent meets determination. I couldn\'t be prouder of their accomplishment."</p>
                <cite>- Coach David Mitchell</cite>
            </blockquote>

            <h3>Road to State Championship</h3>
            <p>With the regional title secured, our team now advances to the state championship tournament. The first state tournament game is scheduled for next Friday at the state arena.</p>

            <h3>Community Support</h3>
            <p>We want to thank our incredible fans, parents, and school community for their unwavering support throughout the season. The energy and enthusiasm at every game made a real difference.</p>

            <p>Join us in congratulating our champions and supporting them as they continue their journey toward the state title!</p>
        ';
    }

    private function getArtsFestivalContent()
    {
        return '
            <p>Mark your calendars for our spectacular Spring Arts Festival, a celebration of creativity, talent, and artistic expression featuring our incredibly gifted students across all grade levels.</p>

            <h3>Event Schedule</h3>

            <h4>Friday, April 12th - Opening Night</h4>
            <ul>
                <li><strong>6:00 PM:</strong> Visual Arts Exhibition Opening</li>
                <li><strong>7:00 PM:</strong> Chamber Music Concert</li>
                <li><strong>8:30 PM:</strong> Drama Club: "A Midsummer Night\'s Dream" (Act I)</li>
            </ul>

            <h4>Saturday, April 13th - Main Festival Day</h4>
            <ul>
                <li><strong>10:00 AM:</strong> Student Art Workshops</li>
                <li><strong>12:00 PM:</strong> Jazz Ensemble Performance</li>
                <li><strong>1:30 PM:</strong> Poetry Reading & Creative Writing</li>
                <li><strong>3:00 PM:</strong> Dance Performance Showcase</li>
                <li><strong>4:30 PM:</strong> Complete Drama Production</li>
                <li><strong>7:00 PM:</strong> Grand Finale Concert</li>
            </ul>

            <h3>Featured Exhibitions</h3>

            <h4>Visual Arts Gallery</h4>
            <p>Our main gallery will showcase over 200 pieces of student artwork including:</p>
            <ul>
                <li>Paintings and drawings</li>
                <li>Sculpture and ceramics</li>
                <li>Digital art and photography</li>
                <li>Mixed media installations</li>
            </ul>

            <h4>Musical Performances</h4>
            <p>Enjoy performances by our award-winning music ensembles:</p>
            <ul>
                <li>Concert Band and Orchestra</li>
                <li>Chamber Music Groups</li>
                <li>Vocal Ensemble and Choir</li>
                <li>Jazz Band</li>
                <li>Student soloists</li>
            </ul>

            <h4>Theatrical Productions</h4>
            <p>Our drama department presents Shakespeare\'s "A Midsummer Night\'s Dream" with original staging and costume design by our students.</p>

            <h3>Interactive Activities</h3>
            <p>Festival attendees can participate in hands-on workshops led by our art teachers and student mentors:</p>
            <ul>
                <li>Pottery throwing demonstrations</li>
                <li>Watercolor painting sessions</li>
                <li>Creative writing workshops</li>
                <li>Music composition basics</li>
            </ul>

            <h3>Admission & Tickets</h3>
            <p>All events are free and open to the public. Reserved seating tickets for the main performances are available at the school office or online through our website.</p>

            <p>Join us in celebrating the incredible artistic talents of our students and the vibrant creative culture that makes our school special!</p>
        ';
    }

    private function getSTEMLabContent()
    {
        return '
            <p>We are excited to officially open our state-of-the-art STEM laboratory, a 3,000 square foot facility designed to inspire innovation and hands-on learning in science, technology, engineering, and mathematics.</p>

            <h3>Facility Features</h3>

            <h4>Advanced Equipment</h4>
            <ul>
                <li>3D printers and laser cutting equipment</li>
                <li>Robotics and automation systems</li>
                <li>High-powered microscopes and imaging systems</li>
                <li>Environmental monitoring stations</li>
                <li>Computer-aided design (CAD) workstations</li>
                <li>Virtual reality learning stations</li>
            </ul>

            <h4>Specialized Areas</h4>
            <ul>
                <li><strong>Maker Space:</strong> For prototyping and engineering projects</li>
                <li><strong>Biotechnology Lab:</strong> For advanced biology and chemistry research</li>
                <li><strong>Computer Science Hub:</strong> For programming and software development</li>
                <li><strong>Research Station:</strong> For independent student research projects</li>
                <li><strong>Collaboration Zone:</strong> For team projects and presentations</li>
            </ul>

            <h3>Educational Programs</h3>

            <h4>New Course Offerings</h4>
            <p>The laboratory enables us to expand our STEM curriculum with exciting new courses:</p>
            <ul>
                <li>Advanced Robotics and AI</li>
                <li>Biomedical Engineering</li>
                <li>Environmental Science Research</li>
                <li>App Development and Programming</li>
                <li>Engineering Design and Innovation</li>
            </ul>

            <h4>Research Opportunities</h4>
            <p>Students can now pursue independent research projects with mentorship from faculty and industry professionals. Topics include renewable energy, medical devices, and sustainable technology solutions.</p>

            <h3>Community Partnerships</h3>
            <p>Our STEM lab has been made possible through generous partnerships with:</p>
            <ul>
                <li>Local tech companies providing equipment and expertise</li>
                <li>University research departments offering mentorship</li>
                <li>Professional engineers and scientists serving as advisors</li>
                <li>Industry sponsors supporting ongoing programs</li>
            </ul>

            <h3>Student Impact</h3>
            <p>The laboratory will serve over 400 students annually and provide opportunities for:</p>
            <ul>
                <li>Hands-on experimentation and discovery</li>
                <li>Real-world problem solving</li>
                <li>Preparation for STEM careers</li>
                <li>Collaboration with industry professionals</li>
                <li>Competition in national STEM challenges</li>
            </ul>

            <h3>Grand Opening Event</h3>
            <p>Join us for the official grand opening ceremony on March 15th at 2:00 PM. The event will feature demonstrations, student presentations, and tours of the facility. Light refreshments will be provided.</p>

            <p>This investment in STEM education represents our commitment to preparing students for the challenges and opportunities of the 21st century!</p>
        ';
    }

    private function getCommunityServiceContent()
    {
        return '
            <p>Community Service Week was an overwhelming success, with over 300 students, faculty, and staff participating in meaningful volunteer activities throughout our local community. This annual tradition reinforces our commitment to social responsibility and civic engagement.</p>

            <h3>Service Projects</h3>

            <h4>Environmental Initiatives</h4>
            <ul>
                <li><strong>Park Cleanup:</strong> 75 volunteers cleaned Liberty Park, collecting 500 pounds of litter</li>
                <li><strong>Tree Planting:</strong> Planted 50 native trees in the downtown area</li>
                <li><strong>River Restoration:</strong> Helped restore habitats along Meadow Creek</li>
                <li><strong>Community Garden:</strong> Established vegetable gardens at two senior centers</li>
            </ul>

            <h4>Social Services</h4>
            <ul>
                <li><strong>Food Bank Support:</strong> Sorted and packaged 2,000 pounds of food donations</li>
                <li><strong>Homeless Shelter:</strong> Prepared and served 400 meals</li>
                <li><strong>Senior Center Visits:</strong> Provided companionship and technology assistance</li>
                <li><strong>Children\'s Hospital:</strong> Created art projects and reading materials</li>
            </ul>

            <h4>Educational Outreach</h4>
            <ul>
                <li><strong>Elementary School Tutoring:</strong> 40 high school students provided reading support</li>
                <li><strong>Library Programs:</strong> Organized storytelling and STEM activities</li>
                <li><strong>Adult Education:</strong> Assisted with computer literacy classes</li>
                <li><strong>Community Center:</strong> Led after-school programs for younger children</li>
            </ul>

            <h3>Student Reflections</h3>
            <blockquote>
                <p>"Working at the food bank opened my eyes to the needs in our community. It felt amazing to know that our work directly helped families in need."</p>
                <cite>- Sarah, Grade 11</cite>
            </blockquote>

            <blockquote>
                <p>"Teaching younger kids about science was so rewarding. They were so curious and excited to learn!"</p>
                <cite>- Marcus, Grade 12</cite>
            </blockquote>

            <h3>Community Impact</h3>
            <p>Our collective efforts resulted in significant community impact:</p>
            <ul>
                <li>1,200 hours of volunteer service</li>
                <li>15 different community organizations supported</li>
                <li>Direct assistance to over 500 community members</li>
                <li>$15,000 estimated value of services provided</li>
            </ul>

            <h3>Partnership Recognition</h3>
            <p>We extend our gratitude to our community partners who made these service opportunities possible:</p>
            <ul>
                <li>City Parks and Recreation Department</li>
                <li>Community Food Bank</li>
                <li>Sunshine Senior Center</li>
                <li>Children\'s Medical Center</li>
                <li>Public Library System</li>
            </ul>

            <h3>Year-Round Service</h3>
            <p>While Community Service Week is our annual flagship event, we encourage ongoing service throughout the year. Students can log volunteer hours for graduation requirements and scholarship applications.</p>

            <p>Thank you to everyone who participated and made this week a tremendous success. Your dedication to serving others exemplifies the values that make our school community special!</p>
        ';
    }

    private function getParentTeacherContent()
    {
        return '
            <p>Parent-teacher conferences are valuable opportunities for collaboration between families and educators to support student success. Here\'s everything you need to know about our upcoming conferences scheduled for October 15-17.</p>

            <h3>Conference Schedule</h3>
            <ul>
                <li><strong>Monday, October 15:</strong> 3:00 PM - 8:00 PM</li>
                <li><strong>Tuesday, October 16:</strong> 3:00 PM - 8:00 PM</li>
                <li><strong>Wednesday, October 17:</strong> 1:00 PM - 6:00 PM (Early release day)</li>
            </ul>

            <h3>Scheduling Your Conference</h3>

            <h4>Online Scheduling</h4>
            <p>Use our convenient online scheduling system available on the parent portal:</p>
            <ol>
                <li>Log into your parent portal account</li>
                <li>Select "Conference Scheduling" from the menu</li>
                <li>Choose available time slots with your child\'s teachers</li>
                <li>Confirm your appointments via email</li>
            </ol>

            <h4>Phone Scheduling</h4>
            <p>If you prefer to schedule by phone, call the main office at (555) 123-4567 between 8:00 AM and 4:00 PM.</p>

            <h3>Preparing for Your Conference</h3>

            <h4>Questions to Consider</h4>
            <ul>
                <li>How is my child progressing academically?</li>
                <li>What are my child\'s strengths and areas for improvement?</li>
                <li>How can I support learning at home?</li>
                <li>Are there any behavioral or social concerns?</li>
                <li>What resources are available for additional support?</li>
            </ul>

            <h4>What to Bring</h4>
            <ul>
                <li>A list of questions or concerns</li>
                <li>Your child\'s recent work samples (if applicable)</li>
                <li>Notebook for taking notes</li>
                <li>Your calendar for scheduling follow-up meetings</li>
            </ul>

            <h3>Conference Format</h3>

            <h4>Individual Meetings</h4>
            <p>Each conference is scheduled for 15 minutes with individual teachers. For students with multiple teachers, plan for separate meetings with each educator.</p>

            <h4>Team Conferences</h4>
            <p>Special education students and those with academic concerns may have team conferences involving multiple teachers and support staff.</p>

            <h3>Topics for Discussion</h3>

            <h4>Academic Progress</h4>
            <ul>
                <li>Current grades and assessment results</li>
                <li>Progress toward learning objectives</li>
                <li>Homework completion and study habits</li>
                <li>Test-taking strategies and skills</li>
            </ul>

            <h4>Social and Emotional Development</h4>
            <ul>
                <li>Peer relationships and social skills</li>
                <li>Classroom behavior and participation</li>
                <li>Self-confidence and motivation</li>
                <li>Stress management and coping strategies</li>
            </ul>

            <h3>Follow-Up Actions</h3>
            <p>After your conference, consider:</p>
            <ul>
                <li>Discussing the meeting with your child</li>
                <li>Implementing suggested strategies at home</li>
                <li>Scheduling follow-up meetings if needed</li>
                <li>Monitoring progress on discussed goals</li>
            </ul>

            <h3>Virtual Conference Option</h3>
            <p>For families unable to attend in person, virtual conferences are available via video call. Please indicate your preference when scheduling.</p>

            <h3>Contact Information</h3>
            <p>For questions about conferences or scheduling assistance:</p>
            <ul>
                <li><strong>Main Office:</strong> (555) 123-4567</li>
                <li><strong>Email:</strong> conferences@school.edu</li>
                <li><strong>Guidance Department:</strong> (555) 123-4570</li>
            </ul>

            <p>We look forward to productive conversations about your child\'s education and continued success!</p>
        ';
    }

    private function getAlumniSuccessContent()
    {
        return '
            <p>Five years after graduation, the Class of 2019 continues to make us proud with their remarkable achievements in higher education, career advancement, and community service. Here are some inspiring success stories from our graduates.</p>

            <h3>Higher Education Excellence</h3>

            <h4>Academic Achievements</h4>
            <ul>
                <li><strong>Jennifer Martinez</strong> - Graduated summa cum laude from MIT with a degree in Aerospace Engineering, now pursuing a PhD at Stanford</li>
                <li><strong>David Chen</strong> - Earned a full scholarship to Harvard Medical School, specializing in pediatric cardiology</li>
                <li><strong>Sarah Johnson</strong> - Completed her Master\'s in Environmental Science at UC Berkeley, leading research on climate change solutions</li>
                <li><strong>Michael Rodriguez</strong> - Graduated from Yale Law School, now working as a public defender advocating for social justice</li>
            </ul>

            <h3>Career Highlights</h3>

            <h4>Technology and Innovation</h4>
            <ul>
                <li><strong>Alex Kim</strong> - Software engineer at Google, leading development of accessibility features</li>
                <li><strong>Emma Thompson</strong> - Founded a successful startup developing educational apps for children with learning disabilities</li>
                <li><strong>James Wilson</strong> - Data scientist at a leading healthcare company, working on AI-driven medical diagnosis</li>
            </ul>

            <h4>Public Service and Education</h4>
            <ul>
                <li><strong>Lisa Garcia</strong> - Elementary school teacher in an underserved community, named Teacher of the Year</li>
                <li><strong>Robert Lee</strong> - Peace Corps volunteer in Guatemala, establishing sustainable farming programs</li>
                <li><strong>Amanda Brown</strong> - Social worker specializing in family services, pursuing her MSW degree</li>
            </ul>

            <h4>Arts and Media</h4>
            <ul>
                <li><strong>Tyler Davis</strong> - Professional musician touring with a nationally recognized orchestra</li>
                <li><strong>Rachel Martinez</strong> - Graphic designer at a major advertising agency, winning multiple design awards</li>
                <li><strong>Carlos Johnson</strong> - Documentary filmmaker whose work has been featured at international film festivals</li>
            </ul>

            <h3>Entrepreneurship and Business</h3>
            <ul>
                <li><strong>Maria Rodriguez</strong> - Started a sustainable fashion company that has grown to 50 employees</li>
                <li><strong>Kevin Thompson</strong> - Co-founded a renewable energy consulting firm serving municipal clients</li>
                <li><strong>Nicole Wilson</strong> - Opened a community-focused coffee shop that supports local artists and musicians</li>
            </ul>

            <h3>Community Impact</h3>
            <p>Beyond their professional achievements, our Class of 2019 graduates continue to give back:</p>
            <ul>
                <li>Established the "Future Leaders Scholarship" providing financial aid to current students</li>
                <li>Volunteer as mentors for current students exploring career paths</li>
                <li>Organize annual alumni networking events for recent graduates</li>
                <li>Participate in career day presentations and guest lectures</li>
            </ul>

            <h3>Alumni Spotlight Interview</h3>
            <blockquote>
                <p>"The foundation I received at our school was invaluable. The teachers pushed me to think critically, the diverse student body broadened my perspective, and the emphasis on service taught me the importance of giving back. I carry those lessons with me every day."</p>
                <cite>- Jennifer Martinez, Aerospace Engineer</cite>
            </blockquote>

            <h3>Recent Recognition</h3>
            <p>Several Class of 2019 graduates have received notable recognition:</p>
            <ul>
                <li>David Chen was featured in Medical Student Research Journal</li>
                <li>Emma Thompson\'s startup was recognized by Forbes "30 Under 30"</li>
                <li>Lisa Garcia received the State Excellence in Teaching Award</li>
                <li>Tyler Davis performed at Carnegie Hall with the National Youth Orchestra</li>
            </ul>

            <h3>Stay Connected</h3>
            <p>We love hearing about alumni achievements! If you\'re a graduate or know of alumni success stories, please share them with us through our alumni portal or by emailing alumni@school.edu.</p>

            <p>The Class of 2019 exemplifies the values and excellence that define our school community. We are incredibly proud of their continued success and positive impact in the world!</p>
        ';
    }

    private function getHealthWellnessContent()
    {
        return '
            <p>This month, we\'re focusing on the health and wellness of our entire school community with a variety of activities, workshops, and initiatives designed to promote physical fitness, mental well-being, and healthy lifestyle choices.</p>

            <h3>Weekly Theme Schedule</h3>

            <h4>Week 1: Physical Fitness</h4>
            <ul>
                <li><strong>Monday:</strong> Fitness assessments and goal setting</li>
                <li><strong>Tuesday:</strong> Yoga and mindfulness sessions</li>
                <li><strong>Wednesday:</strong> Nutrition workshop - "Fueling Your Body"</li>
                <li><strong>Thursday:</strong> Team sports and recreational activities</li>
                <li><strong>Friday:</strong> Dance fitness and movement celebration</li>
            </ul>

            <h4>Week 2: Mental Health Awareness</h4>
            <ul>
                <li><strong>Monday:</strong> Stress management techniques workshop</li>
                <li><strong>Tuesday:</strong> Peer support groups and discussion circles</li>
                <li><strong>Wednesday:</strong> Mental health resources fair</li>
                <li><strong>Thursday:</strong> Art therapy and creative expression</li>
                <li><strong>Friday:</strong> Mindfulness and meditation session</li>
            </ul>

            <h4>Week 3: Healthy Relationships</h4>
            <ul>
                <li><strong>Monday:</strong> Communication skills workshop</li>
                <li><strong>Tuesday:</strong> Conflict resolution and peer mediation</li>
                <li><strong>Wednesday:</strong> Digital citizenship and online safety</li>
                <li><strong>Thursday:</strong> Building positive friendships</li>
                <li><strong>Friday:</strong> Community service project</li>
            </ul>

            <h4>Week 4: Lifestyle Choices</h4>
            <ul>
                <li><strong>Monday:</strong> Sleep hygiene and healthy habits</li>
                <li><strong>Tuesday:</strong> Substance abuse prevention education</li>
                <li><strong>Wednesday:</strong> Environmental health and sustainability</li>
                <li><strong>Thursday:</strong> Financial wellness for students</li>
                <li><strong>Friday:</strong> Celebration and commitment ceremony</li>
            </ul>

            <h3>Special Programs</h3>

            <h4>Wellness Fair</h4>
            <p>Join us for our comprehensive Wellness Fair featuring:</p>
            <ul>
                <li>Health screenings (vision, hearing, blood pressure)</li>
                <li>Nutrition counseling and meal planning</li>
                <li>Fitness demonstrations and equipment trials</li>
                <li>Mental health resources and counseling information</li>
                <li>Local healthcare provider booths</li>
            </ul>

            <h4>Guest Speakers</h4>
            <p>We\'re excited to welcome several expert speakers:</p>
            <ul>
                <li><strong>Dr. Sarah Mitchell</strong> - Adolescent psychiatrist discussing teenage mental health</li>
                <li><strong>Coach Maria Gonzalez</strong> - Olympic athlete sharing fitness motivation</li>
                <li><strong>Chef Anthony Williams</strong> - Nutritionist demonstrating healthy cooking</li>
                <li><strong>Ms. Jennifer Park</strong> - Social worker discussing healthy relationships</li>
            </ul>

            <h3>Student-Led Initiatives</h3>

            <h4>Peer Wellness Ambassadors</h4>
            <p>Our trained student ambassadors will:</p>
            <ul>
                <li>Lead discussion groups on wellness topics</li>
                <li>Provide peer support and resources</li>
                <li>Organize lunchtime wellness activities</li>
                <li>Create wellness content for social media</li>
            </ul>

            <h4>Wellness Challenge</h4>
            <p>Students can participate in our month-long wellness challenge tracking:</p>
            <ul>
                <li>Daily physical activity (30 minutes minimum)</li>
                <li>Healthy meal choices and water intake</li>
                <li>Adequate sleep (8+ hours for teenagers)</li>
                <li>Stress management techniques practiced</li>
                <li>Acts of kindness toward others</li>
            </ul>

            <h3>Resources for Families</h3>

            <h4>Parent Information Sessions</h4>
            <ul>
                <li>"Supporting Teen Mental Health at Home"</li>
                <li>"Nutrition for Growing Minds and Bodies"</li>
                <li>"Recognizing Signs of Stress in Teenagers"</li>
                <li>"Creating Healthy Technology Boundaries"</li>
            </ul>

            <h4>Take-Home Resources</h4>
            <ul>
                <li>Healthy recipe collections</li>
                <li>Family fitness activity guides</li>
                <li>Mental health resource directories</li>
                <li>Communication tip sheets</li>
            </ul>

            <h3>Long-Term Impact</h3>
            <p>Our goal is to establish lasting healthy habits that extend beyond this month. We\'re implementing:</p>
            <ul>
                <li>Ongoing wellness clubs and support groups</li>
                <li>Improved healthy food options in cafeteria</li>
                <li>Regular fitness and wellness activities</li>
                <li>Enhanced counseling and mental health services</li>
                <li>Continued education on wellness topics</li>
            </ul>

            <h3>Get Involved</h3>
            <p>There are many ways to participate in Health and Wellness Month:</p>
            <ul>
                <li>Attend workshops and information sessions</li>
                <li>Join the wellness challenge</li>
                <li>Volunteer for community service projects</li>
                <li>Share your wellness journey on social media (#SchoolWellness)</li>
                <li>Support friends and classmates in their wellness goals</li>
            </ul>

            <p>Let\'s work together to build a healthier, happier school community where everyone can thrive physically, mentally, and emotionally!</p>
        ';
    }
}