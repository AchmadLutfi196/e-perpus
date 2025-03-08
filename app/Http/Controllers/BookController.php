<?php
// app/Http/Controllers/BookController.php
namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['authors', 'categories']);
        
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('authors', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        
        if ($request->has('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('id', $request->category);
            });
        }
        
        $books = $query->paginate(12);
        
        return response()->json($books);
    }
    
    public function show(Book $book)
    {
        $book->load(['authors', 'categories', 'reviews.user']);
        return response()->json($book);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'file' => 'nullable|file|mimes:pdf,epub|max:20480',
            'language' => 'nullable|string',
            'pages' => 'nullable|integer',
            'published_year' => 'nullable|integer',
            'publisher' => 'nullable|string',
            'copies_available' => 'nullable|integer',
            'authors' => 'nullable|array',
            'categories' => 'nullable|array',
        ]);
        
        $book = new Book($request->except(['cover_image', 'file', 'authors', 'categories']));
        
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            // Resize and save image
            $img = Image::make($image->getRealPath());
            $img->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            $path = 'covers/' . $filename;
            Storage::disk('public')->put($path, (string) $img->encode());
            $book->cover_image = $path;
        }
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('books', $filename, 'public');
            $book->file_path = $path;
        }
        
        $book->save();
        
        if ($request->has('authors')) {
            $book->authors()->sync($request->authors);
        }
        
        if ($request->has('categories')) {
            $book->categories()->sync($request->categories);
        }
        
        return response()->json($book, 201);
    }
    
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|unique:books,isbn,' . $book->id,
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'file' => 'nullable|file|mimes:pdf,epub|max:20480',
            'language' => 'nullable|string',
            'pages' => 'nullable|integer',
            'published_year' => 'nullable|integer',
            'publisher' => 'nullable|string',
            'copies_available' => 'nullable|integer',
            'authors' => 'nullable|array',
            'categories' => 'nullable|array',
        ]);
        
        $book->fill($request->except(['cover_image', 'file', 'authors', 'categories']));
        
        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            
            $image = $request->file('cover_image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            $img = Image::make($image->getRealPath());
            $img->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            $path = 'covers/' . $filename;
            Storage::disk('public')->put($path, (string) $img->encode());
            $book->cover_image = $path;
        }
        
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($book->file_path) {
                Storage::disk('public')->delete($book->file_path);
            }
            
            $file = $request->file('file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('books', $filename, 'public');
            $book->file_path = $path;
        }
        
        $book->save();
        
        if ($request->has('authors')) {
            $book->authors()->sync($request->authors);
        }
        
        if ($request->has('categories')) {
            $book->categories()->sync($request->categories);
        }
        
        return response()->json($book);
    }
    
    public function destroy(Book $book)
    {
        // Delete related files
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        
        if ($book->file_path) {
            Storage::disk('public')->delete($book->file_path);
        }
        
        $book->delete();
        
        return response()->json(null, 204);
    }
}