<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Entities\WebsitePage;
use Carbon\Carbon;

class WebsitePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $pages = [
            [
                'title' => 'Welcome to Our School',
                'slug' => 'home',
                'content' => $this->getHomePageContent(),
                'excerpt' => 'Welcome to our modern educational institution where we nurture young minds and build future leaders.',
                'meta_title' => 'Modern School - Quality Education for Future Leaders',
                'meta_description' => 'Welcome to our modern educational institution providing quality education, innovative teaching methods, and comprehensive student development programs.',
                'meta_keywords' => 'school,education,learning,students,teachers,curriculum,academics,quality education',
                'status' => 'published',
                'featured_image' => '/images/pages/homepage-hero.jpg',
                'page_type' => 'homepage',
                'template' => 'homepage',
                'is_featured' => true,
                'show_in_menu' => true,
                'menu_order' => 1,
                'published_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'About Our School',
                'slug' => 'about',
                'content' => $this->getAboutPageContent(),
                'excerpt' => 'Learn about our history, mission, vision, and commitment to providing exceptional education.',
                'meta_title' => 'About Us - Our Mission, Vision & History',
                'meta_description' => 'Discover our school\'s rich history, educational philosophy, and commitment to academic excellence and character development.',
                'meta_keywords' => 'about,history,mission,vision,values,philosophy,excellence',
                'status' => 'published',
                'featured_image' => '/images/pages/about-us.jpg',
                'page_type' => 'standard',
                'template' => 'default',
                'is_featured' => true,
                'show_in_menu' => true,
                'menu_order' => 2,
                'published_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Academic Programs',
                'slug' => 'academics',
                'content' => $this->getAcademicsPageContent(),
                'excerpt' => 'Explore our comprehensive academic programs designed to challenge and inspire students.',
                'meta_title' => 'Academic Programs - Comprehensive Education',
                'meta_description' => 'Discover our diverse academic programs, curriculum, and educational approaches designed to prepare students for success.',
                'meta_keywords' => 'academics,programs,curriculum,subjects,education,learning',
                'status' => 'published',
                'featured_image' => '/images/pages/academics.jpg',
                'page_type' => 'standard',
                'template' => 'default',
                'is_featured' => true,
                'show_in_menu' => true,
                'menu_order' => 3,
                'published_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Admissions Information',
                'slug' => 'admissions',
                'content' => $this->getAdmissionsPageContent(),
                'excerpt' => 'Learn about our admission process, requirements, and how to join our school community.',
                'meta_title' => 'Admissions - Join Our School Community',
                'meta_description' => 'Information about our admission process, requirements, application deadlines, and how to become part of our educational community.',
                'meta_keywords' => 'admissions,enrollment,application,requirements,process,join',
                'status' => 'published',
                'featured_image' => '/images/pages/admissions.jpg',
                'page_type' => 'standard',
                'template' => 'default',
                'is_featured' => true,
                'show_in_menu' => true,
                'menu_order' => 4,
                'published_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Student Life',
                'slug' => 'student-life',
                'content' => $this->getStudentLifePageContent(),
                'excerpt' => 'Discover the vibrant student life, extracurricular activities, and community at our school.',
                'meta_title' => 'Student Life - Activities & Community',
                'meta_description' => 'Explore student life at our school including clubs, sports, activities, and the vibrant community that makes learning enjoyable.',
                'meta_keywords' => 'student life,activities,clubs,sports,community,extracurricular',
                'status' => 'published',
                'featured_image' => '/images/pages/student-life.jpg',
                'page_type' => 'standard',
                'template' => 'default',
                'is_featured' => false,
                'show_in_menu' => true,
                'menu_order' => 5,
                'published_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => $this->getPrivacyPolicyContent(),
                'excerpt' => 'Our commitment to protecting your privacy and personal information.',
                'meta_title' => 'Privacy Policy - Data Protection & Privacy',
                'meta_description' => 'Learn about our privacy policy, how we collect, use, and protect your personal information.',
                'meta_keywords' => 'privacy,policy,data protection,personal information,gdpr',
                'status' => 'published',
                'featured_image' => null,
                'page_type' => 'legal',
                'template' => 'legal',
                'is_featured' => false,
                'show_in_menu' => false,
                'menu_order' => 100,
                'published_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => $this->getTermsOfServiceContent(),
                'excerpt' => 'Terms and conditions for using our website and services.',
                'meta_title' => 'Terms of Service - Website Terms & Conditions',
                'meta_description' => 'Read our terms of service and conditions for using our website and educational services.',
                'meta_keywords' => 'terms,service,conditions,legal,website,usage',
                'status' => 'published',
                'featured_image' => null,
                'page_type' => 'legal',
                'template' => 'legal',
                'is_featured' => false,
                'show_in_menu' => false,
                'menu_order' => 101,
                'published_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($pages as $pageData) {
            WebsitePage::create($pageData);
        }

        $this->command->info('Website pages seeded successfully!');
    }

    private function getHomePageContent()
    {
        return '
            <div class="hero-section">
                <h1>Welcome to Excellence in Education</h1>
                <p class="lead">Where every student is empowered to reach their full potential through innovative learning, dedicated teachers, and a supportive community.</p>
                
                <div class="features-grid">
                    <div class="feature">
                        <h3>Academic Excellence</h3>
                        <p>Our rigorous curriculum and experienced faculty ensure students receive the highest quality education.</p>
                    </div>
                    <div class="feature">
                        <h3>Character Development</h3>
                        <p>We focus on building strong moral values and leadership skills alongside academic achievement.</p>
                    </div>
                    <div class="feature">
                        <h3>Modern Facilities</h3>
                        <p>State-of-the-art classrooms, laboratories, and technology enhance the learning experience.</p>
                    </div>
                </div>
            </div>

            <div class="stats-section">
                <div class="stat">
                    <h4>500+</h4>
                    <p>Students</p>
                </div>
                <div class="stat">
                    <h4>50+</h4>
                    <p>Teachers</p>
                </div>
                <div class="stat">
                    <h4>95%</h4>
                    <p>Graduation Rate</p>
                </div>
                <div class="stat">
                    <h4>25</h4>
                    <p>Years of Excellence</p>
                </div>
            </div>
        ';
    }

    private function getAboutPageContent()
    {
        return '
            <h2>Our Story</h2>
            <p>Founded in 1999, our school has been a cornerstone of educational excellence in the community for over two decades. We began with a simple mission: to provide quality education that prepares students for success in an ever-changing world.</p>

            <h3>Our Mission</h3>
            <p>To provide a nurturing and challenging educational environment that empowers students to achieve academic excellence, develop strong character, and become responsible global citizens.</p>

            <h3>Our Vision</h3>
            <p>To be recognized as a leading educational institution that inspires lifelong learning, critical thinking, and positive contributions to society.</p>

            <h3>Our Values</h3>
            <ul>
                <li><strong>Excellence:</strong> We strive for the highest standards in everything we do.</li>
                <li><strong>Integrity:</strong> We act with honesty, transparency, and ethical behavior.</li>
                <li><strong>Respect:</strong> We value diversity and treat everyone with dignity.</li>
                <li><strong>Innovation:</strong> We embrace new ideas and creative approaches to learning.</li>
                <li><strong>Community:</strong> We foster strong relationships and collaborative partnerships.</li>
            </ul>

            <h3>Leadership Team</h3>
            <p>Our experienced leadership team brings together decades of educational expertise, innovative thinking, and a shared commitment to student success.</p>
        ';
    }

    private function getAcademicsPageContent()
    {
        return '
            <h2>Academic Excellence</h2>
            <p>Our comprehensive academic program is designed to challenge students intellectually while providing the support they need to succeed. We offer a balanced curriculum that combines core subjects with enrichment opportunities.</p>

            <h3>Core Subjects</h3>
            <div class="subjects-grid">
                <div class="subject">
                    <h4>Mathematics</h4>
                    <p>From basic arithmetic to advanced calculus, our math program builds strong analytical and problem-solving skills.</p>
                </div>
                <div class="subject">
                    <h4>Science</h4>
                    <p>Hands-on laboratory experiences in biology, chemistry, and physics foster scientific inquiry and discovery.</p>
                </div>
                <div class="subject">
                    <h4>Language Arts</h4>
                    <p>Developing strong communication skills through reading, writing, speaking, and critical analysis.</p>
                </div>
                <div class="subject">
                    <h4>Social Studies</h4>
                    <p>Understanding history, geography, and civic responsibility to become informed global citizens.</p>
                </div>
            </div>

            <h3>Special Programs</h3>
            <ul>
                <li>Advanced Placement (AP) Courses</li>
                <li>Honor Society</li>
                <li>STEM Enrichment</li>
                <li>Arts Integration</li>
                <li>Foreign Language Study</li>
                <li>Community Service Learning</li>
            </ul>

            <h3>Assessment & Progress</h3>
            <p>We use comprehensive assessment methods to track student progress and provide personalized feedback for continuous improvement.</p>
        ';
    }

    private function getAdmissionsPageContent()
    {
        return '
            <h2>Join Our School Community</h2>
            <p>We welcome students who are eager to learn, grow, and contribute to our vibrant school community. Our admission process is designed to identify students who will thrive in our academic environment.</p>

            <h3>Application Process</h3>
            <ol>
                <li><strong>Submit Application:</strong> Complete our online application form with required documents.</li>
                <li><strong>Academic Records:</strong> Provide transcripts and standardized test scores.</li>
                <li><strong>Recommendations:</strong> Submit letters of recommendation from teachers or counselors.</li>
                <li><strong>Interview:</strong> Participate in a personal interview with our admissions team.</li>
                <li><strong>Decision:</strong> Receive admission decision within 2-3 weeks.</li>
            </ol>

            <h3>Required Documents</h3>
            <ul>
                <li>Completed application form</li>
                <li>Academic transcripts (last 2 years)</li>
                <li>Standardized test scores</li>
                <li>Two letters of recommendation</li>
                <li>Personal statement or essay</li>
                <li>Birth certificate</li>
                <li>Immunization records</li>
            </ul>

            <h3>Important Dates</h3>
            <table>
                <tr><td>Application Opens:</td><td>November 1</td></tr>
                <tr><td>Application Deadline:</td><td>March 1</td></tr>
                <tr><td>Admission Decisions:</td><td>April 15</td></tr>
                <tr><td>Enrollment Deadline:</td><td>May 1</td></tr>
            </table>

            <h3>Tuition & Financial Aid</h3>
            <p>We believe that quality education should be accessible to all qualified students. Financial aid and scholarship opportunities are available for families who demonstrate need.</p>

            <div class="contact-info">
                <h4>Admissions Office</h4>
                <p>Email: admissions@school.edu<br>
                Phone: (555) 123-4567<br>
                Office Hours: Monday-Friday, 8:00 AM - 4:00 PM</p>
            </div>
        ';
    }

    private function getStudentLifePageContent()
    {
        return '
            <h2>Vibrant Student Community</h2>
            <p>Student life at our school extends far beyond the classroom. We offer numerous opportunities for students to explore their interests, develop new skills, and build lasting friendships.</p>

            <h3>Clubs & Organizations</h3>
            <div class="clubs-grid">
                <div class="club">
                    <h4>Academic Clubs</h4>
                    <ul>
                        <li>National Honor Society</li>
                        <li>Math Club</li>
                        <li>Science Olympiad</li>
                        <li>Debate Team</li>
                        <li>Model UN</li>
                    </ul>
                </div>
                <div class="club">
                    <h4>Arts & Culture</h4>
                    <ul>
                        <li>Drama Club</li>
                        <li>Art Society</li>
                        <li>Music Ensemble</li>
                        <li>Photography Club</li>
                        <li>Creative Writing</li>
                    </ul>
                </div>
                <div class="club">
                    <h4>Service & Leadership</h4>
                    <ul>
                        <li>Student Government</li>
                        <li>Community Service Club</li>
                        <li>Environmental Club</li>
                        <li>Peer Tutoring</li>
                        <li>Leadership Academy</li>
                    </ul>
                </div>
            </div>

            <h3>Athletics</h3>
            <p>Our athletic programs promote physical fitness, teamwork, and school spirit. We offer both competitive and recreational opportunities for all skill levels.</p>
            
            <h4>Sports Offered:</h4>
            <ul>
                <li>Basketball</li>
                <li>Soccer</li>
                <li>Track & Field</li>
                <li>Tennis</li>
                <li>Swimming</li>
                <li>Volleyball</li>
            </ul>

            <h3>Special Events</h3>
            <ul>
                <li>Homecoming Week</li>
                <li>Science Fair</li>
                <li>Arts Festival</li>
                <li>Cultural Night</li>
                <li>Graduation Ceremony</li>
                <li>Awards Banquet</li>
            </ul>

            <h3>Student Support Services</h3>
            <p>We provide comprehensive support services to ensure every student can succeed academically and personally.</p>
            <ul>
                <li>Academic Counseling</li>
                <li>College & Career Guidance</li>
                <li>Tutoring Services</li>
                <li>Mental Health Support</li>
                <li>Special Needs Accommodation</li>
            </ul>
        ';
    }

    private function getPrivacyPolicyContent()
    {
        return '
            <h2>Privacy Policy</h2>
            <p><em>Last updated: ' . Carbon::now()->format('F j, Y') . '</em></p>

            <h3>Information We Collect</h3>
            <p>We collect information you provide directly to us, such as when you create an account, contact us, or use our services.</p>

            <h3>How We Use Your Information</h3>
            <p>We use the information we collect to provide, maintain, and improve our services, communicate with you, and protect our users.</p>

            <h3>Information Sharing</h3>
            <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p>

            <h3>Data Security</h3>
            <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>

            <h3>Your Rights</h3>
            <p>You have the right to access, update, or delete your personal information. Contact us if you wish to exercise these rights.</p>

            <h3>Contact Us</h3>
            <p>If you have questions about this Privacy Policy, please contact us at privacy@school.edu.</p>
        ';
    }

    private function getTermsOfServiceContent()
    {
        return '
            <h2>Terms of Service</h2>
            <p><em>Last updated: ' . Carbon::now()->format('F j, Y') . '</em></p>

            <h3>Acceptance of Terms</h3>
            <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>

            <h3>Use License</h3>
            <p>Permission is granted to temporarily access the materials on our website for personal, non-commercial transitory viewing only.</p>

            <h3>Disclaimer</h3>
            <p>The materials on our website are provided on an "as is" basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties.</p>

            <h3>Limitations</h3>
            <p>In no event shall our school or its suppliers be liable for any damages arising out of the use or inability to use the materials on our website.</p>

            <h3>Accuracy of Materials</h3>
            <p>The materials appearing on our website could include technical, typographical, or photographic errors. We do not warrant that any of the materials are accurate, complete, or current.</p>

            <h3>Modifications</h3>
            <p>We may revise these terms of service at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms.</p>

            <h3>Contact Information</h3>
            <p>Questions about the Terms of Service should be sent to us at legal@school.edu.</p>
        ';
    }
}