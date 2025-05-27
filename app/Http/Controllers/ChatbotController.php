<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    /**
     * Handle the user's question about a document.
     */
    public function askQuestion(Request $request, Document $document)
    {
        // Validate the request
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        // Check if the user is a student
        if (!Auth::user()->isStudent()) {
            return response()->json([
                'message' => 'Only students can use the AI chatbot.'
            ], 403);
        }

        // In a real application, you would call an AI API service like OpenAI
        // For this example, we'll simulate a response
        
        // Fetch document content
        $documentContent = $document->content ?? 'No content available for this document.';
        
        // In a real implementation, you would send the question and document content to the AI API
        // For example, using OpenAI's API:
        /*
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful AI assistant for students. Answer questions based on the document provided.'
                ],
                [
                    'role' => 'user',
                    'content' => "Document content: {$documentContent}\n\nQuestion: {$request->question}"
                ]
            ],
            'temperature' => 0.7,
        ]);
        
        $aiAnswer = $response->json()['choices'][0]['message']['content'] ?? 'Sorry, I could not process your question.';
        */
        
        // Simulate AI response for demonstration
        $aiAnswer = $this->simulateAIResponse($request->question, $documentContent);
        
        return response()->json([
            'answer' => $aiAnswer,
        ]);
    }
    
    /**
     * Simulate an AI response for demonstration purposes.
     * In a real application, this would be replaced with an actual AI API call.
     */
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
        
        // Default response
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