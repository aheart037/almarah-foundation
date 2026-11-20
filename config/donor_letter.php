<?php

declare(strict_types=1);

/**
 * Fixed wording and legal details for the donor receipt + thanks / tax-exemption letter.
 * Edit THIS file when the approval is renewed (new dates) or the wording changes.
 * Only the donor name (and the receipt fields) change per donor.
 */
return [
    'org_name' => 'Almarah Foundation',
    'ntn' => '4910219',
    'signatory_name' => 'Sophia Warraich',
    'signatory_title' => 'Chairperson',

    // Facts taken from the FBR approval order dated 22-Oct-2026 (section 2(36), valid tax years 2027 & 2028).
    'approval_text' => 'approved as a Non-Profit Organization under section 2(36) of the Income Tax Ordinance, 2001 (FBR approval order dated 22 October 2026)',
    'approval_valid_until' => '30 June 2028',
    'tax_years' => '2027 and 2028',

    'org_address' => 'Head Office: 3-Canal Park, Gulberg-II, Lahore',

    // Your original thank-you letter, word for word. Greeting = "My Dear {name},". Put a word in salutation (e.g. Bhai) only if you want it after every name.
    'letter' => [
        'salutation' => '',
        'p1' => 'I hope this letter finds you well. On behalf of the Almarah Foundation, I want to express our heartfelt gratitude for your contribution to the noble cause.',
        'p2' => "Your kindness means the world to us, and it's making a real difference in the lives of orphaned children. With your support, we're able to move forward with plans to build state-of-the-art children's complexes at two different locations.",
        'ask' => 'However, we still need your help to make this vision a reality. Your continued support will ensure that these children have a safe and nurturing environment to thrive in.',
        'close' => 'Thank you once again for your generosity and belief in our cause.',
        'regards' => 'Warm regards,',
    ],
];
