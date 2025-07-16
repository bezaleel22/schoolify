<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Entities\BlogCategory;
use Carbon\Carbon;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $categories = [
            [
                'name' => 'School News',
                'slug' => 'school-news',
                'description' => 'Latest news and announcements from our school community, including updates on policies, achievements, and important events.',
                'color' => '#1976d2',
                'icon' => 'fas fa-newspaper',
                'is_featured' => true,
                'meta_title' => 'School News - Latest Updates & Announcements',
                'meta_description' => 'Stay updated with the latest school news, announcements, and important information from our educational community.',
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Academic Excellence',
                'slug' => 'academic-excellence',
                'description' => 'Stories of academic achievements, curriculum updates, teaching methodologies, and educational innovations at our school.',
                'color' => '#388e3c',
                'icon' => 'fas fa-graduation-cap',
                'is_featured' => true,
                'meta_title' => 'Academic Excellence - Educational Achievements & Updates',
                'meta_description' => 'Discover stories of academic success, innovative teaching methods, and educational excellence at our school.',
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Student Life',
                'slug' => 'student-life',
                'description' => 'Highlights from student activities, clubs, sports, events, and the vibrant community life at our school.',
                'color' => '#f57c00',
                'icon' => 'fas fa-users',
                'is_featured' => true,
                'meta_title' => 'Student Life - Activities, Events & Community',
                'meta_description' => 'Explore the vibrant student life at our school including activities, events, clubs, and community involvement.',
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sports & Athletics',
                'slug' => 'sports-athletics',
                'description' => 'Coverage of our athletic programs, sports achievements, team updates, and upcoming competitions.',
                'color' => '#d32f2f',
                'icon' => 'fas fa-trophy',
                'is_featured' => true,
                'meta_title' => 'Sports & Athletics - Teams, Achievements & Updates',
                'meta_description' => 'Follow our athletic teams, sports achievements, and competitive events in our comprehensive sports program.',
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Arts & Culture',
                'slug' => 'arts-culture',
                'description' => 'Showcasing our arts programs, cultural events, student artwork, music performances, and creative achievements.',
                'color' => '#7b1fa2',
                'icon' => 'fas fa-palette',
                'is_featured' => true,
                'meta_title' => 'Arts & Culture - Creative Programs & Performances',
                'meta_description' => 'Explore our vibrant arts and culture programs including visual arts, music, drama, and creative performances.',
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Technology & Innovation',
                'slug' => 'technology-innovation',
                'description' => 'Updates on educational technology, STEM programs, digital learning initiatives, and innovative teaching tools.',
                'color' => '#0288d1',
                'icon' => 'fas fa-laptop-code',
                'is_featured' => false,
                'meta_title' => 'Technology & Innovation - STEM & Digital Learning',
                'meta_description' => 'Discover our technology integration, STEM programs, and innovative approaches to modern education.',
                'sort_order' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Community Outreach',
                'slug' => 'community-outreach',
                'description' => 'Stories of community service, partnerships, volunteer work, and our commitment to social responsibility.',
                'color' => '#689f38',
                'icon' => 'fas fa-hands-helping',
                'is_featured' => false,
                'meta_title' => 'Community Outreach - Service & Partnerships',
                'meta_description' => 'Learn about our community service initiatives, partnerships, and commitment to social responsibility.',
                'sort_order' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Parent Resources',
                'slug' => 'parent-resources',
                'description' => 'Information and resources for parents including tips, guides, policies, and ways to support student success.',
                'color' => '#5d4037',
                'icon' => 'fas fa-family',
                'is_featured' => false,
                'meta_title' => 'Parent Resources - Guides, Tips & Support',
                'meta_description' => 'Access valuable resources for parents including educational tips, school policies, and ways to support your child.',
                'sort_order' => 8,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Alumni Updates',
                'slug' => 'alumni-updates',
                'description' => 'News and updates from our alumni community, success stories, and ways to stay connected with our school.',
                'color' => '#455a64',
                'icon' => 'fas fa-user-graduate',
                'is_featured' => false,
                'meta_title' => 'Alumni Updates - Success Stories & Community',
                'meta_description' => 'Stay connected with our alumni community, read success stories, and learn about alumni achievements.',
                'sort_order' => 9,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Information about student health, wellness programs, safety protocols, and promoting healthy lifestyles.',
                'color' => '#c2185b',
                'icon' => 'fas fa-heartbeat',
                'is_featured' => false,
                'meta_title' => 'Health & Wellness - Student Safety & Well-being',
                'meta_description' => 'Learn about our health and wellness programs, safety protocols, and initiatives for student well-being.',
                'sort_order' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Environmental Initiatives',
                'slug' => 'environmental-initiatives',
                'description' => 'Our commitment to environmental sustainability, green programs, and eco-friendly initiatives at school.',
                'color' => '#2e7d32',
                'icon' => 'fas fa-leaf',
                'is_featured' => false,
                'meta_title' => 'Environmental Initiatives - Sustainability & Green Programs',
                'meta_description' => 'Discover our environmental programs, sustainability initiatives, and commitment to eco-friendly practices.',
                'sort_order' => 11,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'International Programs',
                'slug' => 'international-programs',
                'description' => 'Updates on exchange programs, international partnerships, cultural exchanges, and global learning opportunities.',
                'color' => '#303f9f',
                'icon' => 'fas fa-globe',
                'is_featured' => false,
                'meta_title' => 'International Programs - Global Learning & Exchanges',
                'meta_description' => 'Explore our international programs, cultural exchanges, and global learning opportunities for students.',
                'sort_order' => 12,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($categories as $categoryData) {
            BlogCategory::create($categoryData);
        }

        $this->command->info('Blog categories seeded successfully!');
    }
}