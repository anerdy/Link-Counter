<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Link
 * @package App\Models
 * @property int $site_id
 * @property string $url
 * @property boolean $is_parsed
 * @property string $error
 */
class Link extends Model
{
    use HasFactory;
    protected $table = 'links';

    const PARSED = 1;
    const NOT_PARSED = 0;

    protected $fillable = [
        'site_id',
        'url',
        'is_parsed',
        'error'
    ];

    protected $visible = [
        'id',
        'site_id',
        'url',
        'is_parsed',
        'error'
    ];

    protected $casts = [
        'is_parsed' => 'boolean',
    ];

    public function sourceLinks()
    {
        return $this->belongsToMany(Link::class, 'link_link', 'source_link', 'target_link');
    }

    public function targetLinks()
    {
        return $this->belongsToMany(Link::class, 'link_link', 'target_link', 'source_link');
    }

    public static function createLink($siteId, $url): self
    {
        $newLink = new self();
        $newLink->site_id = $siteId;
        $newLink->url = $url;
        $newLink->is_parsed = self::NOT_PARSED;
        $newLink->save();

        return $newLink;
    }

}
