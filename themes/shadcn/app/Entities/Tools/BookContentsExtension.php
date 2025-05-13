<?php

namespace Themes\Shadcn\App\Entities\Tools;

use BookStack\Entities\Tools\BookContents;
use BookStack\Entities\Models\Book;
use Illuminate\Support\Collection;

class BookContentsExtension extends BookContents
{
    protected $originalBookContents;
    
    public function __construct($originalBookContents = null)
    {
        $this->originalBookContents = $originalBookContents;
        
        // Lấy book từ originalBookContents hoặc sử dụng null check
        if ($originalBookContents && isset($originalBookContents->book)) {
            parent::__construct($originalBookContents->book);
        } else if ($originalBookContents instanceof Book) {
            parent::__construct($originalBookContents);
        } else {
            // Trường hợp khởi tạo trực tiếp không qua container
            parent::__construct(new Book());
        }
    }
    
    /**
     * Override phương thức getTree để hỗ trợ chapter lồng nhau
     */
    public function getTree(bool $showDrafts = false, bool $renderPages = false): Collection
    {
        $pages = $this->getPages($showDrafts, $renderPages);
        
        try {
            // Lấy chỉ các chapter gốc (không có parent) - truy vấn trực tiếp
            $rootChapters = $this->book->chapters()
                ->scopes('visible')
                ->whereNull('parent_id')
                ->get();
            
            // Lấy tất cả chapter để map
            $allChapters = $this->book->chapters()->scopes('visible')->get();
            $chapterMap = $allChapters->keyBy('id');
            
            // Xử lý chapter lồng nhau
            foreach ($allChapters as $chapter) {
                // Sử dụng truy vấn collection trực tiếp
                $childChapters = $allChapters->where('parent_id', $chapter->id)
                    ->sortBy('priority');
                $chapter->setAttribute('child_chapters', $childChapters);
            }
            
            // Xử lý pages trong chapters
            $lonePages = collect();
            $pages->groupBy('chapter_id')->each(function ($chapterPages, $chapter_id) use ($chapterMap, &$lonePages) {
                $chapter = $chapterMap->get($chapter_id);
                if ($chapter) {
                    $chapter->setAttribute('visible_pages', collect($chapterPages)->sortBy('priority'));
                } else {
                    $lonePages = $lonePages->concat($chapterPages);
                }
            });
            
            $sortedPages = $lonePages->sortBy('priority');
            $sortedChapters = $rootChapters->sortBy('priority');
            
            // Return a collection that can be accessed as an array
            $result = collect();
            $result->put('pages', $sortedPages);
            $result->put('chapters', $sortedChapters);
            
            return $result;
        } catch (\Exception $e) {
            // Nếu có lỗi, gọi đến phương thức gốc để có đầu ra an toàn
            if ($this->originalBookContents) {
                return $this->originalBookContents->getTree($showDrafts, $renderPages);
            }
            
            // Trả về kết quả mặc định nếu không có originalBookContents
            $result = collect();
            $result->put('pages', collect());
            $result->put('chapters', collect());
            
            return $result;
        }
    }
}