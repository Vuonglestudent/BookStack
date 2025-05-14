<?php

namespace Themes\Shadcn\App\Entities\Services;

use BookStack\Entities\Models\Book;
use BookStack\Entities\Models\Chapter;
use BookStack\Entities\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BookContentService
{
    /**
     * Nhóm các sub-chapters vào chapter cha tương ứng từ một danh sách bookChildren
     *
     * @param Collection $bookChildren
     * @return Collection
     */
    public function organizeBookContents(Collection $bookChildren): Collection
    {
        // Tách chapters và pages
        $allChapters = $bookChildren->filter(function ($item) {
            return $item instanceof Chapter;
        });
        
        $pages = $bookChildren->filter(function ($item) {
            return $item instanceof Page && (!isset($item->chapter_id) || $item->chapter_id === 0);
        });
        
        // Tìm các root chapters (không có parent_id)
        $rootChapters = $allChapters->filter(function (Chapter $chapter) {
            return !isset($chapter->parent_id) || empty($chapter->parent_id);
        });
        
        // Tìm các sub-chapters (có parent_id)
        $subChapters = $allChapters->filter(function (Chapter $chapter) {
            return isset($chapter->parent_id) && !empty($chapter->parent_id);
        });
        
        // Nhóm sub-chapters theo parent_id
        $groupedSubChapters = $subChapters->groupBy('parent_id');
        
        // Gán sub-chapters vào chapter cha tương ứng
        foreach ($rootChapters as $chapter) {
            $chapterSubChapters = $groupedSubChapters->get($chapter->id, collect([]));
            $chapter->setAttribute('visible_sub_chapters', $chapterSubChapters);
            
            // Log thông tin để debug
            Log::debug("Added {$chapterSubChapters->count()} visible_sub_chapters to Chapter #{$chapter->id} [{$chapter->name}]");
        }
        
        // Trả về collection đã được sắp xếp lại, chỉ bao gồm root chapters và pages
        return $rootChapters->concat($pages)->sortBy('priority');
    }
} 