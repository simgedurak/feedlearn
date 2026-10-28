<?php
// config/mistake_rules.php

// Simple static mapping for common English mistakes
// In a real app, this would be computed via AI or matched by question tags.
// For now, we fallback to generic "Grammar Rule" if no specific tag is found.

function get_mistake_explanation($questionText, $correctAnswer)
{
    // Basic Keyword Matching to simulate "Smartness"
    $lowerQ = strtolower($questionText);

    // Tense: Past Simple
    if (strpos($lowerQ, 'yesterday') !== false || strpos($lowerQ, 'last week') !== false || strpos($lowerQ, 'ago') !== false) {
        return [
            'type' => 'Past Simple',
            'explanation' => "Because the action happened at a specific time in the past (e.g., 'yesterday'), we use the Past Simple tense.",
            'rule' => "Past Simple is used for finished actions in the past. Regular verbs end in -ed.",
            'similar' => [
                ['q' => "She ______ (visit) her grandma yesterday.", 'a' => 'visited'],
                ['q' => "They ______ (go) to the park last Sunday.", 'a' => 'went']
            ]
        ];
    }

    // Tense: Present Perfect
    if (strpos($lowerQ, 'since') !== false || strpos($lowerQ, 'for') !== false || strpos($lowerQ, 'already') !== false) {
        return [
            'type' => 'Present Perfect',
            'explanation' => "We use Present Perfect to connect the past with the present, often with 'since' or 'for'.",
            'rule' => "Form: have/has + past participle. Use it for actions starting in the past and continuing to now.",
            'similar' => [
                ['q' => "I ______ (live) here for 10 years.", 'a' => 'have lived'],
                ['q' => "She ______ (just / finish) her homework.", 'a' => 'has just finished']
            ]
        ];
    }

    // Conditionals
    if (strpos($lowerQ, 'if') !== false) {
        return [
            'type' => 'Conditionals',
            'explanation' => "This looks like a conditional sentence. Check if it's real (1st) or hypothetical (2nd).",
            'rule' => "2nd Conditional: If + Past Simple, ... would + verb. (Hypothetical situations)",
            'similar' => [
                ['q' => "If I ______ (be) rich, I would buy a boat.", 'a' => 'were'],
                ['q' => "If it ______ (rain), we will stay home.", 'a' => 'rains']
            ]
        ];
    }

    // Default
    return [
        'type' => 'General Grammar',
        'explanation' => "Compare your answer with the correct one. Notice the verb form or preposition used.",
        'rule' => "Review the sentence structure carefully. " . $correctAnswer . " fits best here.",
        'similar' => [
            ['q' => "Try another: He ______ (like) coffee.", 'a' => 'likes'],
            ['q' => "Try another: She is ______ (run) now.", 'a' => 'running']
        ]
    ];
}
