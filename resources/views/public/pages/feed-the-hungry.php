<?php
/** Feed the Hungry — content migrated from almarah.org/feed-the-hungry-2/ */
$prog = [
    'purpose'     => 'feed-the-hungry',
    'tag'         => 'Our Projects',
    'title'       => 'Feed the Hungry',
    'lead'        => 'Ensuring no one is left behind by providing food, dignity, and support to those who need it most. At Almarah Foundation, we believe that access to food is a basic human right. Through our Feed the Hungry program, we go beyond temporary relief—creating a consistent support system that nourishes individuals, uplifts families, and restores dignity within communities.',
    'hero_images' => ['assets/images/projects/DSC05009-scaled.jpg', 'assets/images/projects/hotmeal.jpg', 'assets/images/projects/DSC02236-1024x683-1.jpg'],
    'stats'       => [[1000, 'Daily Breakfasts Served', '+'], [500, 'Daily Lunches Served', '+'], [90, 'Monthly Ration Packs', '+'], [80, 'Monthly Stipends', '+']],
    'intro'       => [
        'eyebrow'   => 'Addressing hunger through sustainable and compassionate support',
        'title'     => 'More Than a Meal',
        'text'      => [
            'Feed the Hungry is a comprehensive initiative designed to combat food insecurity across vulnerable communities. Understanding that hunger is closely tied to poverty and instability, the program delivers consistent support through daily meals, monthly rations, and financial assistance.',
            'Rather than offering one-time aid, the focus is on continuity—ensuring individuals and families can depend on regular nourishment and support to rebuild stability in their lives.',
        ],
        'image'     => 'assets/images/projects/DSC05009-scaled.jpg',
        'cta_label' => 'Feed a Family Today',
    ],
    'pillars'     => [
        'title' => 'The Ways We Fight Hunger',
        'items' => [
            ['assets/images/projects/hotmeal.jpg', 'Daily Hot Meals', 'Freshly cooked breakfasts and lunches served every day to those who would otherwise go without.'],
            ['assets/images/projects/DSC02236-1024x683-1.jpg', 'Monthly Ration Packs', 'Staple food packs delivered monthly so families can count on a stocked kitchen.'],
            ['assets/images/projects/7C6A2552-1024x683-1.jpg', 'Financial Stipends', 'Monthly stipends that help widows and vulnerable households meet their essential needs.'],
        ],
    ],
    'cta'         => [
        'title' => 'Be the Reason Someone Eats Today',
        'text'  => 'Every contribution helps provide meals, support families, and restore hope. Together, we can fight hunger and create stronger, healthier communities.',
        'label' => 'Feed a Family Today',
    ],
];
require dirname(__DIR__, 2) . '/partials/program-page.php';
