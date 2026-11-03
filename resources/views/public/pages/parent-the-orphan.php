<?php
/** Parent the Orphan — content migrated from almarah.org/parent-the-orphan/ */
$prog = [
    'purpose'     => 'parent-the-orphan',
    'tag'         => 'Our Flagship Programme',
    'title'       => 'Parent the Orphan',
    'lead'        => "Parent the Orphan program is the heart of Almarah's mission, grounded in the belief that every child deserves to be raised with love and security. It goes beyond basic financial aid to provide a holistic family environment.",
    'hero_images' => ['assets/images/projects/474574227_559279707092719_1092328153086759497_n.jpg', 'assets/images/projects/474205862_557144167306273_2483330261791532184_n.jpg', 'assets/images/projects/473755404_557119077308782_3061434065659056194_n.jpg'],
    'stats'       => [[275, 'Children in Our Care', '+'], [13, 'Apna Ghar Homes', ''], [24, 'Dedicated Care', '/7'], [100, 'Free of Charge', '%']],
    'intro'       => [
        'eyebrow'   => 'Apna Ghar',
        'title'     => 'Every Child Deserves a Loving Home',
        'text'      => ['At Almarah Foundation, we go beyond providing shelter—we create families. Through our Apna Ghar initiative, orphaned children are raised in a home-like environment filled with compassion, dignity, and opportunity. Every child receives the care, guidance, and support they need to grow into confident, responsible individuals.'],
        'image'     => 'assets/images/projects/WhatsApp-Image-2026-04-02-at-2.11.34-PM.jpeg',
        'cta_label' => 'Sponsor an Orphan Child Today',
    ],
    'facts'       => ['13 state-of-the-art homes in DHA and Cavalry, Lahore', '275 orphaned children in present care', '3 infant children in present care'],
    'spotlight'   => [
        'eyebrow' => 'Shaping a Brighter Future',
        'title'   => 'Raising Confident Individuals',
        'text'    => 'The goal of Parent the Orphan is not just to support children—it is to prepare them for life. By combining a nurturing home environment with emotional development and quality care, the program shapes individuals who are self-assured, capable, and ready to contribute meaningfully to society. Almarah Foundation hosts "Emotional Support Programs" where faculty and volunteers engage children in counselling, plantation activities, and indoor games. By focusing on mental health and social integration, "Parent the Orphan" aims to raise not just healthy children, but confident individuals who are emotionally equipped to navigate the world as adults.',
        'image'   => 'assets/images/projects/576552392_786752884345399_5457353216090500727_n.jpg',
    ],
    'pillars'     => [
        'title' => 'Play, Learn & Grow',
        'items' => [
            ['assets/images/projects/574562735_780123825008305_8780815025921419235_n.jpg', 'Recreational Outings', ''],
            ['assets/images/projects/574608100_786752841012070_3088578215458389128_n.jpg', 'Sports Competitions', ''],
            ['assets/images/projects/528195919_710043962016292_4721608384551703179_n.jpg', 'Creative Activities', ''],
        ],
    ],
    'gallery'     => [
        'title' => 'Almarah Apna Ghar Homes',
        'items' => array_map(static fn (string $f): array => ['assets/images/about/' . $f, 'Almarah Apna Ghar'], [
            'house-12-scaled.jpg', 'House-07.JPG-scaled.jpeg', 'House-03-scaled.jpg', 'House-09.JPG-scaled.jpeg', 'House-10.JPG-scaled.jpeg', 'House-08.JPG-scaled.jpeg',
            'House-06.JPG-scaled.jpeg', 'House-04.jpg.jpeg', 'ACH.JPG-scaled.jpeg', 'House-01.JPG-scaled.jpeg', 'house-11-scaled.jpg', 'house-5-scaled.jpg', 'House-13.jpg',
        ]),
    ],
    'cta'         => [
        'title' => 'Give a Child a Home. Give Them a Future.',
        'text'  => 'Your support helps transform houses into homes and lives into futures. Together, we can continue to provide children with the love, care, and opportunities they deserve.',
        'label' => 'Sponsor an Orphan Child',
    ],
];
require dirname(__DIR__, 2) . '/partials/program-page.php';
