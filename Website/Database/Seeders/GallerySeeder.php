<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Website\Entities\GalleryAlbum;
use Modules\Website\Entities\GalleryImage;
use Carbon\Carbon;

class GallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Create Albums
        $albums = [
            [
                'title' => 'School Campus Life',
                'slug' => 'school-campus-life',
                'description' => 'Daily life on our beautiful campus, showcasing our facilities, students, and the vibrant atmosphere of learning.',
                'featured_image' => '/images/gallery/albums/campus-life-cover.jpg',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Science Fair 2024',
                'slug' => 'science-fair-2024',
                'description' => 'Highlights from our annual Science Fair featuring innovative student projects and scientific discoveries.',
                'featured_image' => '/images/gallery/albums/science-fair-cover.jpg',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Sports Championships',
                'slug' => 'sports-championships',
                'description' => 'Action shots and victory moments from our athletic teams competing in various sports throughout the year.',
                'featured_image' => '/images/gallery/albums/sports-cover.jpg',
                'is_featured' => true,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Arts Festival 2024',
                'slug' => 'arts-festival-2024',
                'description' => 'Creative expressions through visual arts, music, drama, and dance performances at our annual Arts Festival.',
                'featured_image' => '/images/gallery/albums/arts-festival-cover.jpg',
                'is_featured' => true,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Graduation Ceremony 2024',
                'slug' => 'graduation-ceremony-2024',
                'description' => 'Celebrating our graduating class of 2024 and their achievements in this memorable ceremony.',
                'featured_image' => '/images/gallery/albums/graduation-cover.jpg',
                'is_featured' => false,
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Community Service Projects',
                'slug' => 'community-service-projects',
                'description' => 'Students and staff making a difference in the community through various volunteer and service initiatives.',
                'featured_image' => '/images/gallery/albums/community-service-cover.jpg',
                'is_featured' => false,
                'sort_order' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'International Exchange Program',
                'slug' => 'international-exchange-program',
                'description' => 'Cultural exchange experiences and international partnerships that broaden our students\' global perspective.',
                'featured_image' => '/images/gallery/albums/exchange-cover.jpg',
                'is_featured' => false,
                'sort_order' => 7,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'New STEM Laboratory',
                'slug' => 'new-stem-laboratory',
                'description' => 'Tour of our state-of-the-art STEM laboratory and students engaging with cutting-edge technology.',
                'featured_image' => '/images/gallery/albums/stem-lab-cover.jpg',
                'is_featured' => false,
                'sort_order' => 8,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        $createdAlbums = [];
        foreach ($albums as $albumData) {
            $createdAlbums[] = GalleryAlbum::create($albumData);
        }

        // Create Images for each album
        $this->createImagesForAlbum($createdAlbums[0], $this->getCampusLifeImages());
        $this->createImagesForAlbum($createdAlbums[1], $this->getScienceFairImages());
        $this->createImagesForAlbum($createdAlbums[2], $this->getSportsImages());
        $this->createImagesForAlbum($createdAlbums[3], $this->getArtsFestivalImages());
        $this->createImagesForAlbum($createdAlbums[4], $this->getGraduationImages());
        $this->createImagesForAlbum($createdAlbums[5], $this->getCommunityServiceImages());
        $this->createImagesForAlbum($createdAlbums[6], $this->getExchangeImages());
        $this->createImagesForAlbum($createdAlbums[7], $this->getSTEMLabImages());

        $this->command->info('Gallery albums and images seeded successfully!');
    }

    private function createImagesForAlbum($album, $images)
    {
        foreach ($images as $imageData) {
            $imageData['album_id'] = $album->id;
            GalleryImage::create($imageData);
        }
    }

    private function getCampusLifeImages()
    {
        return [
            [
                'title' => 'Main Campus Building',
                'description' => 'Our historic main building where administration and core classes are held.',
                'image_path' => '/images/gallery/campus/main-building.jpg',
                'alt_text' => 'Main campus building with students walking',
                'photographer' => 'School Photography Club',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Library Study Sessions',
                'description' => 'Students collaborating and studying in our modern library facility.',
                'image_path' => '/images/gallery/campus/library-study.jpg',
                'alt_text' => 'Students studying together in library',
                'photographer' => 'Thomas Lee',
                'is_featured' => false,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Cafeteria Social Time',
                'description' => 'Students enjoying lunch and socializing in our spacious cafeteria.',
                'image_path' => '/images/gallery/campus/cafeteria.jpg',
                'alt_text' => 'Students eating and socializing in cafeteria',
                'photographer' => 'School Photography Club',
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Outdoor Learning Space',
                'description' => 'Classes taking advantage of our beautiful outdoor classroom areas.',
                'image_path' => '/images/gallery/campus/outdoor-class.jpg',
                'alt_text' => 'Teacher and students in outdoor classroom',
                'photographer' => 'Emily Davis',
                'is_featured' => true,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Student Collaboration',
                'description' => 'Students working together on group projects in common areas.',
                'image_path' => '/images/gallery/campus/collaboration.jpg',
                'alt_text' => 'Students collaborating on group project',
                'photographer' => 'School Photography Club',
                'is_featured' => false,
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    private function getScienceFairImages()
    {
        return [
            [
                'title' => 'Science Fair Opening Ceremony',
                'description' => 'Opening ceremony with students presenting their innovative projects.',
                'image_path' => '/images/gallery/science-fair/opening-ceremony.jpg',
                'alt_text' => 'Science fair opening ceremony with participants',
                'photographer' => 'Michael Johnson',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Renewable Energy Project',
                'description' => 'Student demonstrating solar panel efficiency optimization project.',
                'image_path' => '/images/gallery/science-fair/renewable-energy.jpg',
                'alt_text' => 'Student explaining renewable energy project',
                'photographer' => 'David Kim',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Marine Biology Research',
                'description' => 'Presentation on microplastics impact on marine ecosystems.',
                'image_path' => '/images/gallery/science-fair/marine-biology.jpg',
                'alt_text' => 'Student presenting marine biology research',
                'photographer' => 'Michael Johnson',
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Judges Evaluation',
                'description' => 'Expert judges evaluating student projects and providing feedback.',
                'image_path' => '/images/gallery/science-fair/judges-evaluation.jpg',
                'alt_text' => 'Judges reviewing science fair projects',
                'photographer' => 'School Photography Club',
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Award Ceremony',
                'description' => 'Winners receiving awards for their outstanding scientific work.',
                'image_path' => '/images/gallery/science-fair/awards.jpg',
                'alt_text' => 'Students receiving science fair awards',
                'photographer' => 'Dr. Margaret Thompson',
                'is_featured' => true,
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    private function getSportsImages()
    {
        return [
            [
                'title' => 'Basketball Championship Victory',
                'description' => 'Team celebrating after winning the regional basketball championship.',
                'image_path' => '/images/gallery/sports/basketball-victory.jpg',
                'alt_text' => 'Basketball team celebrating championship win',
                'photographer' => 'Robert Wilson',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Soccer Team in Action',
                'description' => 'Intense soccer match showing teamwork and athletic skill.',
                'image_path' => '/images/gallery/sports/soccer-action.jpg',
                'alt_text' => 'Soccer players in action during game',
                'photographer' => 'Robert Wilson',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Track and Field Events',
                'description' => 'Athletes competing in various track and field events.',
                'image_path' => '/images/gallery/sports/track-field.jpg',
                'alt_text' => 'Athletes competing in track and field',
                'photographer' => 'School Photography Club',
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Swimming Competition',
                'description' => 'Swimmers giving their best effort in competitive races.',
                'image_path' => '/images/gallery/sports/swimming.jpg',
                'alt_text' => 'Swimmers competing in pool',
                'photographer' => 'Robert Wilson',
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Team Spirit Rally',
                'description' => 'Students showing school spirit at a pep rally supporting our teams.',
                'image_path' => '/images/gallery/sports/pep-rally.jpg',
                'alt_text' => 'Students cheering at school pep rally',
                'photographer' => 'School Photography Club',
                'is_featured' => false,
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    private function getArtsFestivalImages()
    {
        return [
            [
                'title' => 'Visual Arts Exhibition',
                'description' => 'Student artwork displayed in our main gallery during the Arts Festival.',
                'image_path' => '/images/gallery/arts/visual-arts-exhibition.jpg',
                'alt_text' => 'Student artwork displayed in gallery',
                'photographer' => 'Lisa Martinez',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Musical Performance',
                'description' => 'Student orchestra performing classical pieces for an appreciative audience.',
                'image_path' => '/images/gallery/arts/orchestra-performance.jpg',
                'alt_text' => 'Students performing in orchestra',
                'photographer' => 'School Photography Club',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Drama Production',
                'description' => 'Students performing Shakespeare\'s "A Midsummer Night\'s Dream".',
                'image_path' => '/images/gallery/arts/drama-performance.jpg',
                'alt_text' => 'Students performing in school play',
                'photographer' => 'Emily Davis',
                'is_featured' => true,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Dance Showcase',
                'description' => 'Contemporary and traditional dance performances by our dance club.',
                'image_path' => '/images/gallery/arts/dance-showcase.jpg',
                'alt_text' => 'Students performing dance routine',
                'photographer' => 'Lisa Martinez',
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Creative Workshops',
                'description' => 'Interactive art workshops where attendees create their own masterpieces.',
                'image_path' => '/images/gallery/arts/workshops.jpg',
                'alt_text' => 'Participants in art workshop creating artwork',
                'photographer' => 'Lisa Martinez',
                'is_featured' => false,
                'sort_order' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    private function getGraduationImages()
    {
        return [
            [
                'title' => 'Graduation Ceremony',
                'description' => 'The Class of 2024 celebrating their achievement at the graduation ceremony.',
                'image_path' => '/images/gallery/graduation/ceremony.jpg',
                'alt_text' => 'Graduates at commencement ceremony',
                'photographer' => 'Dr. Margaret Thompson',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Valedictorian Speech',
                'description' => 'Valedictorian delivering an inspiring speech to fellow graduates.',
                'image_path' => '/images/gallery/graduation/valedictorian.jpg',
                'alt_text' => 'Valedictorian giving graduation speech',
                'photographer' => 'School Photography Club',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Diploma Presentation',
                'description' => 'Students receiving their diplomas from the principal.',
                'image_path' => '/images/gallery/graduation/diploma.jpg',
                'alt_text' => 'Student receiving diploma from principal',
                'photographer' => 'Dr. James Rodriguez',
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Family Celebrations',
                'description' => 'Graduates celebrating with their proud families after the ceremony.',
                'image_path' => '/images/gallery/graduation/family-celebration.jpg',
                'alt_text' => 'Graduates celebrating with families',
                'photographer' => 'School Photography Club',
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    private function getCommunityServiceImages()
    {
        return [
            [
                'title' => 'Park Cleanup Initiative',
                'description' => 'Students and volunteers working together to clean up Liberty Park.',
                'image_path' => '/images/gallery/service/park-cleanup.jpg',
                'alt_text' => 'Students cleaning up park environment',
                'photographer' => 'Amanda Brown',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Food Bank Volunteer Work',
                'description' => 'Students sorting and packaging food donations for community distribution.',
                'image_path' => '/images/gallery/service/food-bank.jpg',
                'alt_text' => 'Students volunteering at food bank',
                'photographer' => 'School Photography Club',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Senior Center Visit',
                'description' => 'Students spending time with seniors, providing companionship and assistance.',
                'image_path' => '/images/gallery/service/senior-center.jpg',
                'alt_text' => 'Students visiting with senior citizens',
                'photographer' => 'Amanda Brown',
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Tree Planting Project',
                'description' => 'Environmental service project planting trees in the downtown area.',
                'image_path' => '/images/gallery/service/tree-planting.jpg',
                'alt_text' => 'Students planting trees for environmental service',
                'photographer' => 'Michael Johnson',
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    private function getExchangeImages()
    {
        return [
            [
                'title' => 'International Students Welcome',
                'description' => 'Welcoming international exchange students to our school community.',
                'image_path' => '/images/gallery/exchange/welcome.jpg',
                'alt_text' => 'International exchange students being welcomed',
                'photographer' => 'Dr. Margaret Thompson',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Cultural Presentation',
                'description' => 'Exchange students sharing their culture and traditions with local students.',
                'image_path' => '/images/gallery/exchange/cultural-presentation.jpg',
                'alt_text' => 'Students giving cultural presentation',
                'photographer' => 'Emily Davis',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Joint Learning Activities',
                'description' => 'Local and international students collaborating on educational projects.',
                'image_path' => '/images/gallery/exchange/joint-activities.jpg',
                'alt_text' => 'Local and international students working together',
                'photographer' => 'David Kim',
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    private function getSTEMLabImages()
    {
        return [
            [
                'title' => 'STEM Lab Grand Opening',
                'description' => 'Official ribbon cutting ceremony for our new STEM laboratory facility.',
                'image_path' => '/images/gallery/stem/grand-opening.jpg',
                'alt_text' => 'STEM lab grand opening ceremony',
                'photographer' => 'Dr. Margaret Thompson',
                'is_featured' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => '3D Printing Workshop',
                'description' => 'Students learning to use 3D printing technology for engineering projects.',
                'image_path' => '/images/gallery/stem/3d-printing.jpg',
                'alt_text' => 'Students using 3D printer in STEM lab',
                'photographer' => 'David Kim',
                'is_featured' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Robotics Programming',
                'description' => 'Students programming and testing robotic systems in the maker space.',
                'image_path' => '/images/gallery/stem/robotics.jpg',
                'alt_text' => 'Students programming robots in lab',
                'photographer' => 'Michael Johnson',
                'is_featured' => false,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Biotechnology Research',
                'description' => 'Advanced biology students conducting research using modern laboratory equipment.',
                'image_path' => '/images/gallery/stem/biotech-research.jpg',
                'alt_text' => 'Students conducting biotechnology research',
                'photographer' => 'Michael Johnson',
                'is_featured' => false,
                'sort_order' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }
}