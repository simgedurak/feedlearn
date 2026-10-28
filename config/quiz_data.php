<?php
// config/quiz_data.php

$quiz_sets = [
    1 => [
        'title' => 'Basic Grammar',
        'desc' => 'Present Simple, Continuous, and basic sentence structure.',
        'difficulty' => 'easy',
        'recommended' => true,
        'questions' => [
            ['id' => 101, 'q' => 'Choose the correct sentence:', 'o' => ['He go to school.', 'He goes to school.', 'He going to school.'], 'c' => 1, 't' => 'grammar'],
            ['id' => 102, 'q' => 'Select the correct verb:', 'o' => ['They is happy.', 'They are happy.', 'They be happy.'], 'c' => 1, 't' => 'grammar'],
            ['id' => 103, 'q' => 'Complete: "She ___ playing tennis now."', 'o' => ['is', 'are', 'does'], 'c' => 0, 't' => 'tenses'],
            ['id' => 104, 'q' => 'Choose the negative:', 'o' => ['I not like it.', 'I don’t like it.', 'I no like it.'], 'c' => 1, 't' => 'grammar'],
            ['id' => 105, 'q' => 'Select the correct order:', 'o' => ['You are where from?', 'Where are you from?', 'Where you are from?'], 'c' => 1, 't' => 'grammar'],
        ]
    ],
    2 => [
        'title' => 'Past Tenses',
        'desc' => 'Past Simple, Past Continuous, and irregular verbs.',
        'difficulty' => 'medium',
        'recommended' => true,
        'questions' => [
            ['id' => 201, 'q' => 'I ___ to the cinema yesterday.', 'o' => ['go', 'went', 'have gone'], 'c' => 1, 't' => 'past_simple'],
            ['id' => 202, 'q' => 'She ___ sleeping when I called.', 'o' => ['was', 'were', 'is'], 'c' => 0, 't' => 'past_continuous'],
            ['id' => 203, 'q' => 'They ___ visit us last week.', 'o' => ['didn’t', 'don’t', 'haven’t'], 'c' => 0, 't' => 'past_simple'],
            ['id' => 204, 'q' => 'We ___ seen that movie before.', 'o' => ['has', 'did', 'had'], 'c' => 2, 't' => 'past_perfect'],
            ['id' => 205, 'q' => 'Why ___ you late?', 'o' => ['did', 'were', 'was'], 'c' => 1, 't' => 'past_simple'],
        ]
    ],
    3 => [
        'title' => 'Prepositions & Articles',
        'desc' => 'In, on, at, a, an, the usage.',
        'difficulty' => 'medium',
        'recommended' => false,
        'questions' => [
            ['id' => 301, 'q' => 'Let’s meet ___ 5 o\'clock.', 'o' => ['in', 'on', 'at'], 'c' => 2, 't' => 'prepositions'],
            ['id' => 302, 'q' => 'The book is ___ the table.', 'o' => ['in', 'on', 'at'], 'c' => 1, 't' => 'prepositions'],
            ['id' => 303, 'q' => 'I saw ___ elephant.', 'o' => ['a', 'an', 'the'], 'c' => 1, 't' => 'articles'],
            ['id' => 304, 'q' => 'She lives ___ London.', 'o' => ['in', 'on', 'at'], 'c' => 0, 't' => 'prepositions'],
            ['id' => 305, 'q' => '___ sun is shining.', 'o' => ['A', 'An', 'The'], 'c' => 2, 't' => 'articles'],
        ]
    ],
    4 => [
        'title' => 'Vocabulary: Daily Life',
        'desc' => 'Common words for routine, home, and hobbies.',
        'difficulty' => 'easy',
        'recommended' => false,
        'questions' => [
            ['id' => 401, 'q' => 'Which is a fruit?', 'o' => ['Carrot', 'Apple', 'Potato'], 'c' => 1, 't' => 'vocab'],
            ['id' => 402, 'q' => 'We cook food in the ___.', 'o' => ['Bathroom', 'Kitchen', 'Bedroom'], 'c' => 1, 't' => 'vocab'],
            ['id' => 403, 'q' => 'Opposite of "Big":', 'o' => ['Huge', 'Small', 'Tall'], 'c' => 1, 't' => 'vocab'],
            ['id' => 404, 'q' => 'I brush my ___ every morning.', 'o' => ['hair', 'teeth', 'eyes'], 'c' => 1, 't' => 'vocab'],
            ['id' => 405, 'q' => 'You wear this on your feet:', 'o' => ['Hat', 'Gloves', 'Shoes'], 'c' => 2, 't' => 'vocab'],
        ]
    ],
    5 => [
        'title' => 'Vocabulary: Academic/Work',
        'desc' => 'Professional and formal terminology.',
        'difficulty' => 'hard',
        'recommended' => false,
        'questions' => [
            ['id' => 505, 'q' => 'To "attend" a meeting means to:', 'o' => ['Cancel it', 'Go to it', 'Forget it'], 'c' => 1, 't' => 'vocab_work'],
        ]
    ],
    6 => [
        'title' => 'Advanced Phrasal Verbs',
        'desc' => 'Tricky multi-word verbs for advanced learners.',
        'difficulty' => 'hard',
        'recommended' => false,
        'questions' => [
            ['id' => 601, 'q' => 'To "put up with" means:', 'o' => ['To build', 'To tolerate', 'To display'], 'c' => 1, 't' => 'phrasal_verbs'],
            ['id' => 602, 'q' => 'He "ran into" an old friend.', 'o' => ['Crashed into', 'Met unexpectedly', 'Jogged with'], 'c' => 1, 't' => 'phrasal_verbs'],
            ['id' => 603, 'q' => 'We need to "sort out" this problem.', 'o' => ['Resolve', 'Ignore', 'Create'], 'c' => 0, 't' => 'phrasal_verbs'],
            ['id' => 604, 'q' => 'Can you "look after" my cat?', 'o' => ['Watch continuously', 'Take care of', 'Search for'], 'c' => 1, 't' => 'phrasal_verbs'],
            ['id' => 605, 'q' => 'The plane "took off" on time.', 'o' => ['Departed', 'Arrived', 'Stopped'], 'c' => 0, 't' => 'phrasal_verbs'],
        ]
    ],
    7 => [
        'title' => 'Business Emailing',
        'desc' => 'Professional correspondence phrases.',
        'difficulty' => 'medium',
        'recommended' => true,
        'questions' => [
            ['id' => 701, 'q' => 'Best opening for a formal email:', 'o' => ['Hey!', 'Dear Mr. Smith,', 'Hi friend,'], 'c' => 1, 't' => 'emailing'],
            ['id' => 702, 'q' => '"I look forward to ___ you."', 'o' => ['hear from', 'hearing from', 'heard from'], 'c' => 1, 't' => 'emailing'],
            ['id' => 703, 'q' => 'Meaning of "ASAP":', 'o' => ['As soon as possible', 'Always stay positive', 'After some period'], 'c' => 0, 't' => 'emailing'],
            ['id' => 704, 'q' => 'To "attach" a file means:', 'o' => ['To delete it', 'To include it', 'To print it'], 'c' => 1, 't' => 'emailing'],
            ['id' => 705, 'q' => 'Formal closing:', 'o' => ['Best regards,', 'Love,', 'Later,'], 'c' => 0, 't' => 'emailing'],
        ]
    ],
    8 => [
        'title' => 'Conditional Sentences',
        'desc' => 'Zero, First, Second, and Third Conditionals.',
        'difficulty' => 'hard',
        'recommended' => true,
        'questions' => [
            ['id' => 801, 'q' => 'If it rains, we ___ home.', 'o' => ['stay', 'will stay', 'stayed'], 'c' => 1, 't' => 'first_conditional'],
            ['id' => 802, 'q' => 'If I were you, I ___ do that.', 'o' => ['won’t', 'wouldn’t', 'didn’t'], 'c' => 1, 't' => 'second_conditional'],
            ['id' => 803, 'q' => 'If you ___ water, it boils.', 'o' => ['heat', 'heated', 'will heat'], 'c' => 0, 't' => 'zero_conditional'],
            ['id' => 804, 'q' => 'I would have come if I ___ known.', 'o' => ['have', 'had', 'would'], 'c' => 1, 't' => 'third_conditional'],
            ['id' => 805, 'q' => 'Unless you ___, you will fail.', 'o' => ['study', 'don’t study', 'studied'], 'c' => 0, 't' => 'first_conditional'],
        ]
    ],
    9 => [
        'title' => 'Basic Vocabulary II',
        'desc' => 'More essential words for beginners.',
        'difficulty' => 'easy',
        'recommended' => false,
        'questions' => [
            ['id' => 901, 'q' => 'Opposite of "Hot":', 'o' => ['Warm', 'Cold', 'Spicy'], 'c' => 1, 't' => 'vocab'],
            ['id' => 902, 'q' => 'A place to read books:', 'o' => ['Library', 'Gym', 'Restaurant'], 'c' => 0, 't' => 'vocab'],
            ['id' => 903, 'q' => 'Yellow fruit:', 'o' => ['Apple', 'Banana', 'Grape'], 'c' => 1, 't' => 'vocab'],
            ['id' => 904, 'q' => 'We sleep in a:', 'o' => ['Desk', 'Bed', 'Chair'], 'c' => 1, 't' => 'vocab'],
            ['id' => 905, 'q' => 'The day after Monday is:', 'o' => ['Sunday', 'Tuesday', 'Friday'], 'c' => 1, 't' => 'vocab'],
        ]
    ],
    10 => [
        'title' => 'Colors & Shapes',
        'desc' => 'Basic visual vocabulary.',
        'difficulty' => 'easy',
        'recommended' => true,
        'questions' => [
            ['id' => 1001, 'q' => 'The sky is usually:', 'o' => ['Green', 'Blue', 'Red'], 'c' => 1, 't' => 'vocab_colors'],
            ['id' => 1002, 'q' => 'A ball is:', 'o' => ['Square', 'Round', 'Triangle'], 'c' => 1, 't' => 'vocab_shapes'],
            ['id' => 1003, 'q' => 'Grass is:', 'o' => ['Green', 'Purple', 'Orange'], 'c' => 0, 't' => 'vocab_colors'],
            ['id' => 1004, 'q' => 'A box is usually:', 'o' => ['Square/Rectangular', 'Circle', 'Line'], 'c' => 0, 't' => 'vocab_shapes'],
            ['id' => 1005, 'q' => 'Snow is:', 'o' => ['Black', 'White', 'Yellow'], 'c' => 1, 't' => 'vocab_colors'],
        ]
    ],
    11 => [
        'title' => 'Travel English',
        'desc' => 'Airports, hotels, and directions.',
        'difficulty' => 'medium',
        'recommended' => false,
        'questions' => [
            ['id' => 1101, 'q' => 'I would like to ___ a room.', 'o' => ['buy', 'book', 'sell'], 'c' => 1, 't' => 'vocab_travel'],
            ['id' => 1102, 'q' => 'Where is the baggage ___?', 'o' => ['claim', 'climb', 'calm'], 'c' => 0, 't' => 'vocab_travel'],
            ['id' => 1103, 'q' => 'Could you ___ me the way?', 'o' => ['say', 'tell', 'speak'], 'c' => 1, 't' => 'vocab_travel'],
            ['id' => 1104, 'q' => 'The flight is ___ by 2 hours.', 'o' => ['late', 'delayed', 'stopped'], 'c' => 1, 't' => 'vocab_travel'],
            ['id' => 1105, 'q' => 'I need a ___ ticket.', 'o' => ['one-way', 'one-direction', 'one-road'], 'c' => 0, 't' => 'vocab_travel'],
        ]
    ],
    12 => [
        'title' => 'Idioms & Slang',
        'desc' => 'Common expressions used by natives.',
        'difficulty' => 'hard',
        'recommended' => true,
        'questions' => [
            ['id' => 1201, 'q' => 'It cost "an arm and a ___".', 'o' => ['hand', 'leg', 'foot'], 'c' => 1, 't' => 'idioms'],
            ['id' => 1202, 'q' => '"Break a leg" means:', 'o' => ['Good luck', 'Get hurt', 'Stop working'], 'c' => 0, 't' => 'idioms'],
            ['id' => 1203, 'q' => 'It\'s "raining cats and ___".', 'o' => ['dogs', 'mice', 'birds'], 'c' => 0, 't' => 'idioms'],
            ['id' => 1204, 'q' => 'I\'m "feeling blue" means:', 'o' => ['I\'m cold', 'I\'m sad', 'I\'m happy'], 'c' => 1, 't' => 'idioms'],
            ['id' => 1205, 'q' => '"Piece of cake" means:', 'o' => ['Yummy', 'Very easy', 'Difficult'], 'c' => 1, 't' => 'idioms'],
        ]
    ],
    13 => [
        'title' => 'Family Members',
        'desc' => 'Relationships and relatives.',
        'difficulty' => 'easy',
        'recommended' => false,
        'questions' => [
            ['id' => 1301, 'q' => 'Your father\'s father is your:', 'o' => ['Uncle', 'Grandfather', 'Brother'], 'c' => 1, 't' => 'vocab_family'],
            ['id' => 1302, 'q' => 'Your sister\'s daughter is your:', 'o' => ['Niece', 'Nephew', 'Cousin'], 'c' => 0, 't' => 'vocab_family'],
            ['id' => 1303, 'q' => 'Your uncle\'s son is your:', 'o' => ['Brother', 'Cousin', 'Father'], 'c' => 1, 't' => 'vocab_family'],
            ['id' => 1304, 'q' => 'Your mother\'s sister is your:', 'o' => ['Aunt', 'Grandma', 'Niece'], 'c' => 0, 't' => 'vocab_family'],
            ['id' => 1305, 'q' => 'Siblings are:', 'o' => ['Parents', 'Brothers/Sisters', 'Friends'], 'c' => 1, 't' => 'vocab_family'],
        ]
    ],
    14 => [
        'title' => 'Modals of Obligation',
        'desc' => 'Must, have to, should, ought to.',
        'difficulty' => 'medium',
        'recommended' => true,
        'questions' => [
            ['id' => 1401, 'q' => 'You ___ stop at a red light.', 'o' => ['must', 'should', 'can'], 'c' => 0, 't' => 'modals'],
            ['id' => 1402, 'q' => 'I ___ go to the dentist yesterday.', 'o' => ['must', 'had to', 'have to'], 'c' => 1, 't' => 'modals'],
            ['id' => 1403, 'q' => 'You ___ smoke here; it\'s forbidden.', 'o' => ['mustn\'t', 'don\'t have to', 'shouldn\'t'], 'c' => 0, 't' => 'modals'],
            ['id' => 1404, 'q' => 'He ___ wear a suit to work options.', 'o' => ['has to', 'have to', 'must to'], 'c' => 0, 't' => 'modals'],
            ['id' => 1405, 'q' => 'You ___ see a doctor if you feel sick.', 'o' => ['should', 'must', 'have'], 'c' => 0, 't' => 'modals'],
        ]
    ],
    15 => [
        'title' => 'Reported Speech',
        'desc' => 'Changing Direct to Indirect speech.',
        'difficulty' => 'hard',
        'recommended' => false,
        'questions' => [
            ['id' => 1501, 'q' => '"I am happy." -> He said he ___ happy.', 'o' => ['is', 'was', 'be'], 'c' => 1, 't' => 'reported_speech'],
            ['id' => 1502, 'q' => '"I will go." -> She said that she ___ go.', 'o' => ['will', 'would', 'went'], 'c' => 1, 't' => 'reported_speech'],
            ['id' => 1503, 'q' => '"I learned output." -> He said he ___ it.', 'o' => ['learned', 'had learned', 'learns'], 'c' => 1, 't' => 'reported_speech'],
            ['id' => 1504, 'q' => '"Do you like it?" -> She asked if I ___ it.', 'o' => ['like', 'liked', 'will like'], 'c' => 1, 't' => 'reported_speech'],
            ['id' => 1505, 'q' => '"Stay here." -> He told me to ___ there.', 'o' => ['stay', 'stayed', 'staying'], 'c' => 0, 't' => 'reported_speech'],
        ]
    ],
];
?>