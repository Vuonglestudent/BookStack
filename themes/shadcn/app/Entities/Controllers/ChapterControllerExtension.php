<?php

namespace Themes\Shadcn\App\Entities\Controllers;

use BookStack\Entities\Controllers\ChapterController;
use BookStack\Entities\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Themes\Shadcn\App\Entities\Models\ChapterRelations;

class ChapterControllerExtension extends ChapterController
{
    protected $originalController;
    
    public function __construct($originalController = null)
    {
        $this->originalController = $originalController;
        
        // Khởi tạo các dependencies như controller gốc
        parent::__construct(
            app()->make('BookStack\Entities\Repos\ChapterRepo'),
            app()->make('BookStack\Entities\Queries\ChapterQueries'),
            app()->make('BookStack\Entities\Queries\EntityQueries'),
            app()->make('BookStack\References\ReferenceFetcher')
        );
    }
    
    /**
     * Override phương thức create để hỗ trợ parent chapter
     */
    public function create(string $bookSlug, string $chapterSlug = null)
    {
        $book = $this->entityQueries->books->findVisibleBySlugOrFail($bookSlug);
        
        // Sửa permission check
        $parentChapter = null;
        if ($chapterSlug) {
            $parentChapter = $this->queries->findVisibleBySlugsOrFail($bookSlug, $chapterSlug);
            // Kiểm tra permission dựa trên chapter cha
            $this->checkOwnablePermission('update', $parentChapter);
        } else {
            // Kiểm tra permission dựa trên book
            $this->checkOwnablePermission('chapter-create', $book);
        }
        
        // Lấy danh sách chapter không phải con của chapter hiện tại
        // để tránh tạo vòng lặp phân cấp
        if ($chapterSlug) {
            // Get all chapters except those that are descendants of the current parent
            $childChapterIds = ChapterRelations::getChildChapterIds($parentChapter);
            $availableParentChapters = $book->chapters()
                ->whereNotIn('id', $childChapterIds)
                ->get();
        } else {
            $availableParentChapters = $book->chapters()->get();
        }
        
        // Create a new chapter instance with relations pre-set
        $chapter = new Chapter();
        $chapter->book_id = $book->id;
        // Explicitly set parent_id to match parentChapter if available
        $chapter->parent_id = $parentChapter ? $parentChapter->id : null;
        $chapter->book_slug = $book->slug;
        // Make sure book is available via relationship
        $chapter->setRelation('book', $book);
        
        $this->setPageTitle(trans('entities.chapters_create'));
        
        // Debug information to assist in troubleshooting
        $debug = [
            'parentChapterId' => $parentChapter ? $parentChapter->id : null,
            'chapterParentId' => $chapter->parent_id,
        ];
        
        return view('chapters.create', [
            'book' => $book,
            'current' => $book,
            'parentChapter' => $parentChapter,
            'chapter' => $chapter,
            'availableParentChapters' => $availableParentChapters,
            // Ensure these are available for any template that needs them
            'bookSlug' => $book->slug,
            'chapterSlug' => $chapterSlug,
            'debug' => $debug,
        ]);
    }
    
    /**
     * Override phương thức store để hỗ trợ parent_id
     */
    public function store(Request $request, string $bookSlug, string $chapterSlug = null)
    {
        $book = $this->entityQueries->books->findVisibleBySlugOrFail($bookSlug);
        
        // Sửa permission check
        $parentChapter = null;
        if ($chapterSlug) {
            $parentChapter = $this->queries->findVisibleBySlugsOrFail($bookSlug, $chapterSlug);
            $this->checkOwnablePermission('update', $parentChapter);
        } else {
            $this->checkOwnablePermission('chapter-create', $book);
        }
        
        $validated = $this->validate($request, [
            'name'                => ['required', 'string', 'max:255'],
            'description_html'    => ['string', 'max:2000'],
            'tags'                => ['array'],
            'default_template_id' => ['nullable', 'integer'],
            'parent_id'           => ['nullable', 'integer', 'exists:chapters,id'],
        ]);
        
        // Create chapter using the regular repo method
        $chapter = $this->chapterRepo->create($validated, $book);
        
        // Now handle the parent_id separately since it's not in the $fillable array
        $parentId = null;
        
        // Get parent_id from request if available
        if (isset($validated['parent_id']) && !empty($validated['parent_id'])) {
            $parentId = $validated['parent_id'];
        } 
        // Or use the parent chapter from URL if available and no parent_id in request
        elseif ($chapterSlug && $parentChapter) {
            $parentId = $parentChapter->id;
        }
        
        // Update the parent_id directly in the database if we have one
        if ($parentId) {
            \Illuminate\Support\Facades\DB::table('chapters')
                ->where('id', $chapter->id)
                ->update(['parent_id' => $parentId]);
            
            // Refresh the chapter to get the updated data
            $chapter->refresh();
        }
        
        return redirect($chapter->getUrl());
    }
    
    /**
     * Override phương thức edit để hỗ trợ parent chapter
     */
    public function edit(string $bookSlug, string $chapterSlug)
    {
        $chapter = $this->queries->findVisibleBySlugsOrFail($bookSlug, $chapterSlug);
        $this->checkOwnablePermission('chapter-update', $chapter);
        
        // Sử dụng helper function từ ChapterRelations thay vì truy vấn trực tiếp
        $childChapterIds = ChapterRelations::getChildChapterIds($chapter);
        
        $availableParentChapters = $chapter->book->chapters()
            ->where('id', '!=', $chapter->id)
            ->whereNotIn('id', $childChapterIds)
            ->get();
        
        $this->setPageTitle(trans('entities.chapters_edit'));
        
        return view('chapters.edit', [
            'book' => $chapter->book,
            'chapter' => $chapter,
            'current' => $chapter,
            'availableParentChapters' => $availableParentChapters,
        ]);
    }
    
    /**
     * Override phương thức update để hỗ trợ parent_id
     */
    public function update(Request $request, string $bookSlug, string $chapterSlug)
    {
        $chapter = $this->queries->findVisibleBySlugsOrFail($bookSlug, $chapterSlug);
        $this->checkOwnablePermission('chapter-update', $chapter);
        
        $validated = $this->validate($request, [
            'name'                => ['required', 'string', 'max:255'],
            'description_html'    => ['string', 'max:2000'],
            'tags'                => ['array'],
            'default_template_id' => ['nullable', 'integer'],
            'parent_id'           => ['nullable', 'integer', 'exists:chapters,id'],
        ]);
        
        // Store the parent_id separately before updating
        $parentId = $validated['parent_id'] ?? null;
        
        // Kiểm tra không cho phép chapter làm cha của chính nó
        if ($parentId && $parentId == $chapter->id) {
            $parentId = null;
            unset($validated['parent_id']);
        }
        
        // Sử dụng helper function từ ChapterRelations thay vì truy vấn trực tiếp
        $childChapterIds = ChapterRelations::getChildChapterIds($chapter);
            
        if ($parentId && in_array($parentId, $childChapterIds)) {
            $parentId = null;
            unset($validated['parent_id']);
        }
        
        // Remove parent_id from validated data since it's not in the fillable array
        if (isset($validated['parent_id'])) {
            unset($validated['parent_id']);
        }
        
        // Update the chapter using the regular repo method
        $chapter = $this->chapterRepo->update($chapter, $validated);
        
        // Update the parent_id directly in the database if needed
        if ($parentId !== null) {
            \Illuminate\Support\Facades\DB::table('chapters')
                ->where('id', $chapter->id)
                ->update(['parent_id' => $parentId]);
            
            // Refresh the chapter to get the updated data
            $chapter->refresh();
        }
        
        return redirect($chapter->getUrl());
    }
    
    public function getChapterRepo()
    {
        return $this->chapterRepo;
    }
    
    public function getChapterQueries()
    {
        return $this->queries;
    }
    
    public function getEntityQueries()
    {
        return $this->entityQueries;
    }
    
    public function getReferenceFetcher()
    {
        return $this->referenceFetcher;
    }
}