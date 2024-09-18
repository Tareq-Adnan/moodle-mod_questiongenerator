<?php
// API endpoint
$url = 'https://api-inference.huggingface.co/models/mistralai/Mistral-Nemo-Instruct-2407/v1/chat/completions';
 
// API key
$apiKey = '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'; // Replace with your actual Hugging Face API key
 
// Initialize cURL
$ch = curl_init($url);
$large_text = "ম্যাজিস্ট্রেসি ক্ষমতায় সেনা সদস্যদের কাজ শুরু
ম্যাজিস্ট্রেসি ক্ষমতায় বুধবার থেকে কাজ শুরু করেছেন সেনা সদস্যরা।

কোটা সংস্কার আন্দোলন ঘিরে দুই মাস ধরে মাঠে থাকলেও এই প্রথম সেনাবাহিনীকে বিচারিক ক্ষমতা দিয়েছে অন্তর্বর্তীকালীন সরকার।

এর ফলে সেনাবাহিনীর কমিশন্ড কর্মকর্তারা নির্বাহী ম্যাজিস্ট্রেটের ক্ষমতা প্রাপ্ত হলেন। ফলে অপরাধ সংঘটিত হলে তাঁরা অপরাধীকে গ্রেপ্তার করতে বা গ্রেপ্তারের নির্দেশ দিতে পারবেন।

মঙ্গলবার রাতে এই প্রজ্ঞাপন জারির পর আজ থেকে ম্যাজিস্ট্রেসি ক্ষমতায় সেনা সদস্যরা কাজ করতে শুরু করেছেন।";
 
// Set the cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json"
]);
 
// JSON pattern example
$json_pattern = '{
    "question": "What is CTE?",
    "options": [
        "A type of cancer",
        "A viral infection",
        "A brain tumor",
        "A degenerative brain disorder"
    ],
    "correct_answer": "A brain tumor"
}';
 
// JSON data to be sent
$data = [
    "messages" => [
        [
            "role" => "user",
            "content" => "Only json response, no other texts.
            ONLY generate 5 multiple-choice QUESTIONS from the following text in a JSON format. Each question object should have exactly 4 options and a 'correct_answer' field and Don't add any extra text. Strictly follow this format: " . $json_pattern . " Text: \n\n" . $large_text
        ]
    ],
    "max_tokens" => 500,
    "stream" => false
];
 
// Encode data to JSON
$jsonData = json_encode($data);
 
// Set POST data
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
 
// Execute the request
$response = curl_exec($ch);
 
// Check for errors
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}
 
// Decode the API response
$response_data = json_decode($response, true);
 
// Extract content from response
$content = $response_data['choices'][0]['message']['content'] ?? '';
 
// Check if content is valid JSON
$isCorrectFormat = false;
if (!empty($content)) {
    if (json_last_error() === JSON_ERROR_NONE) {
        $isCorrectFormat = is_array($content) &&
                           isset($content[0]['question']) &&
                           isset($content[0]['options']) &&
                           isset($content[0]['correct_answer']);
    }
}
 
// Output the response if it matches the expected format
if ($isCorrectFormat) {
    echo '<pre>';
    echo htmlspecialchars($content, JSON_PRETTY_PRINT); // Use htmlspecialchars to display the raw JSON content safely
    echo '</pre>';
} else {
  // echo '<pre>';
  // echo htmlspecialchars($content, JSON_PRETTY_PRINT); // Use htmlspecialchars to display the raw JSON content safely
  // echo '</pre>';
    // Prepare correction prompt
    $correction_prompt = 'The response format is incorrect. Please adjust the response to match the following expected format: ' .
                         json_encode([
                             [
                                 "question" => "Example question?",
                                 "options" => [
                                     "Option 1",
                                     "Option 2",
                                     "Option 3",
                                     "Option 4"
                                 ],
                                 "correct_answer" => "Option 1"
                             ]
                         ], JSON_PRETTY_PRINT) .
                         ' Here is the actual response received: ' . htmlspecialchars($content);
 
    // Create a new request to guide the API with the corrected format
    $correction_data = [
        "messages" => [
            [
                "role" => "user",
                "content" => "Modify the response to fit the following format: " . $correction_prompt
            ]
        ],
        "max_tokens" => 500,
        "stream" => false
    ];
 
    $correction_jsonData = json_encode($correction_data);
 
    // Set POST data for correction request
    curl_setopt($ch, CURLOPT_POSTFIELDS, $correction_jsonData);
 
    // Execute the correction request
    $correction_response = curl_exec($ch);
 
    // Check for errors
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    } else {
        // Decode and display the correction response
        $correction_response_data = json_decode($correction_response, true);
        $correction_content = $correction_response_data['choices'][0]['message']['content'] ?? '';
 
        echo '<pre>';
        echo htmlspecialchars($correction_content, JSON_PRETTY_PRINT); // Use htmlspecialchars to display the raw JSON content safely
        echo '</pre>';
    }
}
 
// Close cURL session
curl_close($ch);
