<?php

declare(strict_types=1);

/**
 * Donation verification stages (read as donation_review.stages).
 * Part 1: staff record or donors submit; a single finance stage verifies or rejects.
 * Add stages here (in order) to introduce multi-level review later; the last stage is final.
 */
return [
    'stages' => [
        'final_verification' => ['label' => 'Finance verification', 'roles' => ['finance_manager', 'super_admin']],
    ],
];
