<?php
/** Educate Pakistan — content migrated from almarah.org/educate-pakistan/ */
$prog = [
    'purpose'     => 'educate-pakistan',
    'tag'         => 'Our Projects',
    'title'       => 'Educate Pakistan',
    'lead'        => 'Providing accessible, high-quality education in a safe and supportive environment, enabling underprivileged children to break the cycle of poverty and build a brighter, more independent future. At Almarah Foundation, we believe education is the foundation of lasting change. Through our Educate Pakistan program, we provide children from underserved communities with access to high-quality learning in a safe, nurturing, and inclusive environment—equipping them with the knowledge and confidence to succeed in life.',
    'hero_images' => ['assets/images/projects/DSC05761-scaled.jpg', 'assets/images/projects/497903312_122208465626104528_6036840965113577816_n.jpg', 'assets/images/projects/528195919_710043962016292_4721608384551703179_n.jpg'],
    'stats'       => [[300, 'Students Free Education', '+'], [23, 'Loving Teachers', ''], [1, 'School', ''], [1, 'Children Complex', '']],
    'intro'       => [
        'eyebrow'   => 'A nurturing environment for academic excellence and personal growth',
        'title'     => 'Almarah Grammar School',
        'text'      => ['At the heart of Educate Pakistan is Almarah Grammar School, a purpose-driven institution committed to delivering quality education from early years through higher levels.'],
        'image'     => 'assets/images/projects/DSC05761-scaled.jpg',
        'cta_label' => 'Sponsor a Student',
    ],
    'facts'       => ['A structured and modern academic curriculum', 'Safe and inclusive classrooms', 'Focus on character building and life skills', "Individual attention to support each child's growth"],
    'spotlight'   => [
        'eyebrow'    => 'Breaking the Cycle of Poverty',
        'title'      => 'Education as a pathway to long-term change',
        'text'       => 'Access to education transforms not just individuals, but entire families and communities. By equipping children with knowledge and skills, the program helps break the cycle of poverty and creates opportunities for upward mobility.',
        'image'      => 'assets/images/projects/497903312_122208465626104528_6036840965113577816_n.jpg',
        'link_url'   => 'https://almarah.edu.pk/',
        'link_label' => 'Explore Almarah Grammar School',
    ],
    'pillars'     => [
        'title' => 'How We Educate',
        'items' => [
            ['assets/images/projects/DSC05761-scaled.jpg', 'Quality Schooling', 'Free, high-quality schooling for children who would otherwise be left out of the classroom.'],
            ['assets/images/about/Forman-Christian-College.jpeg', 'Institutional Synergy', 'Partnerships with leading colleges and universities open doors to higher education.'],
            ['assets/images/projects/528195919_710043962016292_4721608384551703179_n.jpg', 'Skills Development', 'Life skills, creativity and confidence-building alongside academics.'],
        ],
    ],
    'cta'         => [
        'title' => 'Educate a Child. Empower a Future.',
        'text'  => 'By supporting Educate Pakistan, you help provide children with the tools they need to succeed. Together, we can empower the next generation through education.',
        'label' => 'Sponsor a Student',
    ],
];
require dirname(__DIR__, 2) . '/partials/program-page.php';
