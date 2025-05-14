<?php

namespace Themes\Shadcn\App\Entities\Models;

use BookStack\Entities\Models\Chapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;

class ChapterRelations
{
    /**
     * Đăng ký các quan hệ mới cho Chapter model
     */
    public static function register()
    {
        app()->resolving(Chapter::class, function ($chapter) {
            // Thêm các casts
            $chapter->mergeCasts(['parent_id' => 'integer']);
        });
    }
    
    /**
     * Lấy các chapter con của một chapter
     * Phương thức helper tĩnh - an toàn hơn so với macro
     */
    public static function getChildChapters(Chapter $chapter)
    {
        return Chapter::where('parent_id', '=', $chapter->id)
            ->orderBy('priority', 'asc')
            ->get();
    }
    
    /**
     * Lấy các ID của chapter con
     */
    public static function getChildChapterIds(Chapter $chapter)
    {
        return Chapter::where('parent_id', '=', $chapter->id)
            ->pluck('id')
            ->toArray();
    }
    
    /**
     * Lấy chapter cha của một chapter
     */
    public static function getParentChapter(Chapter $chapter)
    {
        if (empty($chapter->parent_id)) {
            return null;
        }
        return Chapter::find($chapter->parent_id);
    }
    
    /**
     * Query các chapter gốc của một book
     */
    public static function queryRootChapters($bookQuery)
    {
        return $bookQuery->whereNull('parent_id');
    }

    /**
     * Get the sub-chapters that belong to this chapter.
     */
    public static function getSubChapters(Chapter $chapter, string $dir = 'ASC')
    {
        return Chapter::where('parent_id', '=', $chapter->id)
            ->orderBy('priority', $dir)
            ->get();
    }

    /**
     * Get visible sub-chapters for a chapter
     */
    public static function getVisibleSubChapters(Chapter $chapter): Collection
    {
        return Chapter::where('parent_id', '=', $chapter->id)
            ->scopes('visible')
            ->orderBy('priority', 'asc')
            ->get();
    }
}