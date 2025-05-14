<?php

namespace Themes\Shadcn\App\Entities\Tools;

use BookStack\Entities\Tools\BookContents;
use BookStack\Entities\Models\Book;
use BookStack\Entities\Models\Chapter;
use BookStack\Entities\Tools\PageContent;
use BookStack\Entities\Models\Page;
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
        try {
            // Lấy pages và chapters
            $pages = $this->getPages($showDrafts, $renderPages);
            $allChapters = $this->book->chapters()->scopes('visible')->get();
            $rootChapters = $allChapters->whereNull('parent_id')->sortBy('priority');
            
            // Map pages vào chapters và xử lý pages không thuộc chapter nào
            $lonePages = $this->mapPagesToChapters($pages, $allChapters);
            
            // Xây dựng cấu trúc cây sub-chapters
            $this->buildSubChapterTree($allChapters);
            
            // Thiết lập quan hệ với book và render nội dung nếu cần
            $this->setupBookRelations($rootChapters, $lonePages, $renderPages);
            
            // Trả về collection đã sắp xếp theo priority
            return $rootChapters->sortBy($this->bookChildSortFunc())
                ->concat($lonePages->sortBy($this->bookChildSortFunc()));
        } catch (\Exception $e) {
            return $this->originalBookContents 
                ? $this->originalBookContents->getTree($showDrafts, $renderPages) 
                : collect();
        }
    }
    
    /**
     * Map pages vào chapters tương ứng và trả về các pages không thuộc chapter nào
     */
    protected function mapPagesToChapters(Collection $pages, Collection $allChapters): Collection
    {
        $chapterMap = $allChapters->keyBy('id');
        $lonePages = collect();
        
        // Gán pages vào chapters
        $pages->groupBy('chapter_id')->each(function ($chapterPages, $chapter_id) use ($chapterMap, &$lonePages) {
            $chapter = $chapterMap->get($chapter_id);
            if ($chapter) {
                $chapter->setAttribute('visible_pages', collect($chapterPages)->sortBy('priority'));
            } else {
                $lonePages = $lonePages->concat($chapterPages);
            }
        });
        
        // Đảm bảo tất cả chapters đều có thuộc tính visible_pages
        $allChapters->whereNull('visible_pages')->each(function (Chapter $chapter) {
            $chapter->setAttribute('visible_pages', collect([]));
        });
        
        return $lonePages;
    }
    
    /**
     * Xây dựng cấu trúc cây cho các sub-chapters
     */
    protected function buildSubChapterTree(Collection $allChapters): void
    {
        foreach ($allChapters as $chapter) {
            $chapter->setAttribute('visible_sub_chapters', 
                $allChapters->where('parent_id', $chapter->id)->sortBy('priority'));
        }
    }
    
    /**
     * Thiết lập quan hệ với book và render nội dung nếu cần
     */
    protected function setupBookRelations(Collection $rootChapters, Collection $lonePages, bool $renderPages): void
    {
        $allEntities = collect()->concat($rootChapters)->concat($lonePages);
        
        $allEntities->each(function ($entity) use ($renderPages) {
            $entity->setRelation('book', $this->book);
            
            if ($renderPages && $entity instanceof Page) {
                $entity->html = (new PageContent($entity))->render();
            }
        });
    }
    
    /**
     * Helper function để lấy tất cả pages trong book
     */
    protected function getPages(bool $showDrafts = false, bool $renderPages = false): Collection
    {
        // Thử lấy pages từ originalBookContents nếu có
        if ($this->originalBookContents) {
            try {
                $reflection = new \ReflectionClass($this->originalBookContents);
                if ($reflection->hasMethod('getPages')) {
                    $method = $reflection->getMethod('getPages');
                    $method->setAccessible(true);
                    return $method->invoke($this->originalBookContents, $showDrafts, $renderPages);
                }
            } catch (\Exception $e) {
                // Bỏ qua lỗi và sử dụng phương thức fallback
            }
        }
        
        // Fallback method
        $query = $renderPages 
            ? $this->queries->pages->visibleWithContents() 
            : $this->queries->pages->visibleForList();
            
        if (!$showDrafts) {
            $query->where('draft', '=', false);
        }
        
        return $query->where('book_id', '=', $this->book->id)->get();
    }
}