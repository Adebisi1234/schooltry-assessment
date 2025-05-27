<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents.
     */
    public function index()
    {
        $user = Auth::user();
        $documents = Document::query();

        // Filter documents based on user role
        if ($user->isTeacher()) {
            // Teachers see only their uploaded documents
            $documents = $documents->where('user_id', $user->id);
        }
        // Students see all documents

        $documents = $documents->latest()->paginate(10);
        
        return inertia('Documents/Index', [
            'documents' => $documents
        ]);
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        if (!Auth::user()->isTeacher()) {
            return redirect()->route('documents.index')
                ->with('error', 'Only teachers can upload documents.');
        }

        return inertia('Documents/Create');
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isTeacher()) {
            return redirect()->route('documents.index')
                ->with('error', 'Only teachers can upload documents.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB limit
        ]);

        $file = $request->file('document');
        $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('documents', $fileName, 'public');
        
        // Extract content for text files or PDFs for AI processing
        $content = null;
        $fileType = $file->getClientOriginalExtension();
        
        if ($fileType === 'txt') {
            $content = file_get_contents($file->getRealPath());
        } elseif ($fileType === 'pdf') {
            // I'll use a library like pdftotext or a service like AWS Textract in a real application
            // For simplicity, I'll just note that PDF content would be extracted here
            $content = "PDF content would be extracted here using a PDF parsing library";
        } elseif ($fileType === 'doc' || $fileType === 'docx') {

            $content = "Word document content would be extracted here using a Word parsing library";
        }

        $document = Document::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'content' => $content,
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        // Load the user relationship
        
        $document->load('user');
        return inertia('Documents/Show', [
            'document' => $document,
            'filePath' => Storage::url($document->file_path),
        ]);
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(Document $document)
    {
        if (Auth::id() !== $document->user_id) {
            return redirect()->route('documents.index')
                ->with('error', 'You can only edit your own documents.');
        }

        return inertia('Documents/Edit', [
            'document' => $document
        ]);
    }

    /**
     * Update the specified document in storage.
     */
    public function update(Request $request, Document $document)
    {
        if (Auth::id() !== $document->user_id) {
            return redirect()->route('documents.index')
                ->with('error', 'You can only update your own documents.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB limit
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
        ];

        // If a new file is uploaded
        if ($request->hasFile('document')) {
            // Delete the old file
            Storage::disk('public')->delete($document->file_path);
            
            // Store the new file
            $file = $request->file('document');
            $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents', $fileName, 'public');
            
            // Extract content for text files or PDFs for AI processing
            $content = null;
            $fileType = $file->getClientOriginalExtension();
            
            if ($fileType === 'txt') {
                $content = file_get_contents($file->getRealPath());
            } elseif ($fileType === 'pdf') {
                // In a real app, you'd use a library like pdftotext or a service like AWS Textract
                $content = "PDF content would be extracted here using a PDF parsing library";
            }

            $data['file_path'] = $filePath;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
            $data['content'] = $content;
        }

        $document->update($data);

        return redirect()->route('documents.index')
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document)
    {
        if (Auth::id() !== $document->user_id && !Auth::user()->isAdmin()) {
            return redirect()->route('documents.index')
                ->with('error', 'You can only delete your own documents.');
        }

        // Delete the file from storage
        Storage::disk('public')->delete($document->file_path);
        
        // Delete the document record
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}
