<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'category_id'
    ];
     

    public function getPaginateByLimit(int $limit_count = 5)
    {
       return $this::with('category')->orderBy('updated_at', 'DESC')->paginate($limit_count);
    }

    public function getByLimit(int $limit_count = 3)
    {
       //update_atで降順に並べた後、limitで件数制限をかける
       return $this->orderBy('updated_at', 'DESC')->limit($limit_count)->get();
    }

    // Categoryに対するリレーション

//「1対多」の関係なので単数系に
public function category()
{
    return $this->belongsTo(Category::class);
}






}
