<?php
header("Content-Type: application/json");

// Get and decode JSON input
$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

if (!isset($input["description"])) {
    echo json_encode(["error" => "Missing input data"]);
    exit;
}

$description = htmlspecialchars($input["description"]);
$max_tokens = isset($input["max_tokens"]) ? max(50, min(600, intval($input["max_tokens"]))) : 150; // Ensure within range

// Adjust announcement length based on max_tokens
$length_instruction = ($max_tokens <= 150) 
    ? "Write a short, professional announcement (2 to 3 sentences)" 
    : "Write a detailed, professional announcement (4 to 8 sentences)";

// Construct the prompt
$messages = [
    ["role" => "system", "content" => "You are an AI assistant that generates professional and concise announcements."],
    ["role" => "user", "content" => "{$length_instruction} based on the following details:\n\n{$description}. Ensure clarity, professionalism, and correct structure."]
];


// 🔒 Use an environment variable or config file for API key
$api_key = "tgp_v1_p1o_acPtY0BvSEs4P-fxhlrwDj9RDWlJGWmujS4T2sg";
$model = "mistralai/Mistral-7B-Instruct-v0.2";

$data = json_encode([
    "model" => $model,
    "messages" => $messages,
    "max_tokens" =>$max_tokens, 
    "temperature" => 0.5,  
    "top_p" => 0.8
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.together.xyz/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle API errors
if ($http_code !== 200 || !$response) {
    echo json_encode([
        "error" => "API call failed. Please check your API key, cURL settings, or Hostinger firewall.",
        "http_code" => $http_code,
        "response" => json_decode($response, true) ?? "No response",
        "curl_error" => $error ?: "None"
    ]);
    exit;
}

// Process API Response Safely
$response_data = json_decode($response, true);
$generated_text = $response_data["choices"][0]["message"]["content"] ?? null;

if (!$generated_text) {
    echo json_encode(["error" => "Invalid API response", "raw_response" => $response]);
    exit;
}

// Clean and format the output
$cleaned_text = trim(preg_replace('/\n{2,}/', "\n\n", $generated_text));
$cleaned_text = str_replace("**", "", $cleaned_text); // Remove markdown asterisks

echo json_encode(["generated_text" => $cleaned_text]);
?>
