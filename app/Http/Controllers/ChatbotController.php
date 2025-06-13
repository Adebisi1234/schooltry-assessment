<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{

    public function askQuestion(Request $request, Document $document)
    {
        // Validate the request
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        if (!Auth::user()->isStudent()) {
            return response()->json([
                'message' => 'Only students can use the AI chatbot.'
            ], 403);
        }

        $cacheKey = 'chatbot_question_' . md5($request->question);

        // Track all question keys for listing purposes
        $this->trackQuestionKey($cacheKey);

        // Increment the frequency counter for this question
        $frequency = Cache::get('question_frequency_' . $cacheKey, 0) + 1;
        Cache::put('question_frequency_' . $cacheKey, $frequency, now()->addMonths(3));

        // Check if a cached response exists in the cache
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);

            // Update the cached data with the new frequency
            $cachedData['frequency'] = $frequency;
            $cachedData['last_accessed'] = now();

            Cache::put($cacheKey, $cachedData, now()->addDays(30));

            return response()->json([
                'answer' => $cachedData['answer'],
                'cached' => true
            ]);
        }


        $documentContent = $document->content ?? 'No content available for this document.';

        // Moderate the document content
        if (!$this->moderateQuestion($request->question)) {
            return response()->json([
                'message' => 'The document content is inappropriate or flagged for moderation.'
            ], 403);
        }



        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful AI assistant for students. Answer questions based on the document provided only.'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Document content: {$documentContent}\n\nQuestion: {$request->question}"
                        ]
                    ],
                    'temperature' => 0.7,
                ]);

        $aiAnswer = $response->json()['choices'][0]['message']['content'] ?? 'Sorry, I could not process your question.';

        // Store response in cache with metadata
        $cacheData = [
            'question' => $request->question,
            'answer' => $aiAnswer,
            'document_id' => $document->id,
            'document_title' => $document->title ?? 'Unknown',
            'created_at' => now(),
            'last_accessed' => now(),
            'frequency' => $frequency
        ];

        Cache::put($cacheKey, $cacheData, now()->addDays(30));

        return response()->json([
            'answer' => $aiAnswer,
        ]);
    }

    /**
     * Track the question key in a list of all questions
     */
    private function trackQuestionKey($cacheKey)
    {
        $allKeys = Cache::get('all_chatbot_questions', []);

        if (!in_array($cacheKey, $allKeys)) {
            $allKeys[] = $cacheKey;
            Cache::put('all_chatbot_questions', $allKeys, now()->addMonths(6));
        }
    }

    /**
     * Get all cached questions and answers with ranking based on frequency
     */
    public function getAllQuestions()
    {
        if (!Auth::user()) {
            return response()->json([
                'message' => 'Authentication required.'
            ], 401);
        }

        $allKeys = Cache::get('all_chatbot_questions', []);
        $questions = [];

        foreach ($allKeys as $key) {
            $questionData = Cache::get($key);

            if ($questionData) {
                $questions[] = $questionData;
            }
        }

        // Sort by frequency (highest first)
        usort($questions, function ($a, $b) {
            return $b['frequency'] - $a['frequency'];
        });

        return response()->json([
            'questions' => $questions,
            'total' => count($questions)
        ]);
    }

    private function moderateQuestion($question)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/moderations', [
                        "model" => "omni-moderation-latest",
                        "input" => $question
                    ]);
            $result = $response->json();
            if (isset($result['results'][0]['flagged']) && $result['results'][0]['flagged']) {
                return false; // Content is flagged
            }

        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }


    private function simulateAIResponse($question, $documentContent)
    {
        // Simple logic to generate a somewhat relevant response
        $question = strtolower($question);

        if (str_contains($question, 'what') && str_contains($question, 'about')) {
            $topics = ['main idea', 'concept', 'subject', 'topic'];
            foreach ($topics as $topic) {
                if (str_contains($question, $topic)) {
                    // Find a sentence that might contain the answer
                    $sentences = explode('.', $documentContent);
                    if (count($sentences) > 0) {
                        $randomIndex = array_rand($sentences);
                        return "Based on the document, I can tell you that: " . trim($sentences[$randomIndex]) . ".";
                    }
                }
            }
        }

        if (str_contains($question, 'how') || str_contains($question, 'explain')) {
            return "The document explains that this process involves several steps. " .
                "First, you need to understand the basic concepts. " .
                "Then, apply them to the specific context mentioned in the document.";
        }

        if (str_contains($question, 'why')) {
            return "According to the document, this is important because it forms the foundation for understanding the broader subject matter. " .
                "The document emphasizes the significance of this concept in the overall framework.";
        }

        if (str_contains($question, 'when') || str_contains($question, 'where')) {
            return "The document mentions that this typically occurs in educational settings, particularly in classroom environments. " .
                "The timing can vary depending on the specific curriculum being followed.";
        }

        if (str_contains($question, 'who') || str_contains($question, 'name')) {
            return "The document references several key figures in this field, including experts and researchers who have contributed significantly to this area of study. " .
                "Their collective work has shaped our understanding of these concepts.";
        }


        return "Based on the document, I can provide the following information related to your question: " .
            "The document covers various aspects of this topic, including key concepts, applications, and implications. " .
            "For more specific information, you might want to refine your question or refer to a particular section of the document.";
    }

    /**
     * Display the chatbot interface for a document.
     */
    public function showChatInterface(Document $document)
    {
        // Only students can access the chatbot
        if (!Auth::user()->isStudent()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Only students can use the AI chatbot.');
        }

        return inertia('Documents/Chat', [
            'document' => $document,
        ]);
    }
}