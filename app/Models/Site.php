<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Site
 * @package App\Models
 * @property int $id
 * @property string $domain
 */
class Site extends Model
{
    use HasFactory;
    protected $table = 'sites';

    protected $fillable = [
        'domain',
    ];

    protected $visible = [
        'id',
        'domain',
    ];

    public function links()
    {
        return $this->hasMany(Link::class);
    }

}
