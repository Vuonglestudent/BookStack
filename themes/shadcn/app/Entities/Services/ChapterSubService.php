<?php

namespace Themes\Shadcn\App\Entities\Services;

use BookStack\Entities\Models\Chapter;
use BookStack\Entities\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChapterSubService
{
    /**
     * Thêm thông tin về sub-chapters vào các đối tượng chapter
     *
     * @param Chapter|Collection $chapters
     * @return void
     */
    public function addVisibleSubChapters($chapters)
    {
        if ($chapters instanceof Chapter) {
            $this->addVisibleSubChaptersToChapter($chapters);
            return;
        }

        foreach ($chapters as $chapter) {
            $this->addVisibleSubChaptersToChapter($chapter);
        }
    }

    /**
     * Thêm visible sub-chapters vào một chapter
     *
     * @param Chapter $chapter
     * @return void
     */
    protected function addVisibleSubChaptersToChapter(Chapter $chapter)
    {
        // Kiểm tra nếu thuộc tính visible_sub_chapters đã tồn tại
        if ($chapter->getAttribute('visible_sub_chapters') !== null) {
            return;
        }

        try {
            // Lấy tất cả các sub-chapter hiển thị của chapter này
            $visibleSubChapters = Chapter::where('parent_id', '=', $chapter->id)
                ->scopes('visible')
                ->orderBy('priority', 'asc')
                ->get();
            
            // Quan trọng: Đảm bảo mỗi sub-chapter cũng có visible_pages để tránh lỗi null
            foreach ($visibleSubChapters as $subChapter) {
                // Thêm thuộc tính visible_pages nếu chưa có
                if ($subChapter->getAttribute('visible_pages') === null) {
                    $visiblePages = Page::where('chapter_id', '=', $subChapter->id)
                        ->scopes('visible')
                        ->orderBy('draft', 'desc')
                        ->orderBy('priority', 'asc')
                        ->get();
                    
                    $subChapter->setAttribute('visible_pages', $visiblePages);
                }
            }

            // Debug log để theo dõi
            Log::debug("Added visible_sub_chapters to Chapter #{$chapter->id} [{$chapter->name}]: " . $visibleSubChapters->count() . " sub-chapters found");

            // Gán thuộc tính visible_sub_chapters cho chapter (theo quy ước đặt tên của BookStack)
            $chapter->setAttribute('visible_sub_chapters', $visibleSubChapters);
        } catch (\Exception $e) {
            // Nếu có lỗi, gán một collection rỗng để tránh lỗi khi template kiểm tra
            $chapter->setAttribute('visible_sub_chapters', collect([]));
            Log::error("Error adding visible_sub_chapters to Chapter #{$chapter->id}: " . $e->getMessage());
        }
    }
} 