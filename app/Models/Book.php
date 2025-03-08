<?php
// app/Models/Book.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'isbn', 'description', 'cover_image', 'file_path',
        'language', 'pages', 'published_year', 'publisher', 'copies_available'
    ];

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }
    
    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }
}
