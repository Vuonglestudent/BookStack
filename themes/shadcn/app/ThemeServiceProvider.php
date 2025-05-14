<?php

namespace Themes\Shadcn\App;

use BookStack\Facades\Theme;
use BookStack\Theming\ThemeEvents;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Themes\Shadcn\App\Console\Commands\MigrateTheme;
use Themes\Shadcn\App\Entities\Models\ChapterRelations;
use Themes\Shadcn\App\Entities\Controllers\ChapterControllerExtension;
use Themes\Shadcn\App\Entities\Tools\BookContentsExtension;
use Themes\Shadcn\App\Entities\Services\ChapterSubService;
use Themes\Shadcn\App\Entities\Services\BookContentService;
use BookStack\Entities\Models\Chapter;
use BookStack\Entities\Models\Book;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký các services
     */
    public function register()
    {
        // Đăng ký custom commands
        Theme::registerCommand(new MigrateTheme());
        
        // Đăng ký controller extension với cách tiếp cận an toàn hơn
        $this->app->extend('BookStack\Entities\Controllers\ChapterController', 
            function($controller, $app) {
                try {
                    // Tạo instance mới với tham số từ controller ban đầu nếu có thể
                    if (method_exists($controller, 'getChapterRepo')) {
                        $chapterRepo = $controller->getChapterRepo();
                        $chapterQueries = $controller->getChapterQueries();
                        $entityQueries = $controller->getEntityQueries();
                        $referenceFetcher = $controller->getReferenceFetcher();
                        
                        return new ChapterControllerExtension(
                            $chapterRepo,
                            $chapterQueries,
                            $entityQueries,
                            $referenceFetcher
                        );
                    }
                    
                    // Nếu không thể lấy dependencies từ controller gốc
                    return new ChapterControllerExtension($controller);
                } catch (\Exception $e) {
                    // Fallback: sử dụng controller gốc nếu có lỗi
                    return $controller;
                }
            }
        );
        
        // Đăng ký BookContents extension
        $this->app->extend('BookStack\Entities\Tools\BookContents', 
            function($bookContents, $app) {
                try {
                    return new BookContentsExtension($bookContents);
                } catch (\Exception $e) {
                    // Fallback: sử dụng đối tượng ban đầu nếu có lỗi
                    return $bookContents;
                }
            }
        );

        // Đăng ký ChapterSubService
        $this->app->singleton(ChapterSubService::class, function ($app) {
            return new ChapterSubService();
        });
        
        // Đăng ký BookContentService
        $this->app->singleton(BookContentService::class, function ($app) {
            return new BookContentService();
        });
    }

    /**
     * Bootstrap các services
     */
    public function boot()
    {
        // Đăng ký model extensions
        ChapterRelations::register();
        
        // Đăng ký permission policies
        $this->registerPermissionPolicies();

        // Đăng ký routes
        $this->registerRoutes();

        // Xử lý sub-chapters cho các chức năng khác nhau
        $this->registerEntityEvents();
        
        // Xử lý sidebar và trang show của book
        $this->registerViewEvents();
    }
    
    /**
     * Đăng ký các sự kiện cho giao diện (view)
     */
    protected function registerViewEvents()
    {
        // Xử lý hiển thị book (show view)
        view()->composer('themes.shadcn.books.show', function ($view) {
            $viewData = $view->getData();
            
            if (isset($viewData['bookChildren']) && $viewData['bookChildren'] instanceof \Illuminate\Support\Collection) {
                $bookContentService = $this->app->make(BookContentService::class);
                $organizedContents = $bookContentService->organizeBookContents($viewData['bookChildren']);
                
                $view->with('bookChildren', $organizedContents);
            }
        });
        
        // Xử lý sidebar entity list (book tree navigation)
        view()->composer(['entities.book-tree', 'themes.shadcn.entities.book-tree'], function ($view) {
            if (!isset($view->getData()['sidebarTree'])) {
                return;
            }
            
            $sidebarTree = $view->getData()['sidebarTree'];
            
            // Tìm tất cả các chapter trong sidebar
            $chapters = collect($sidebarTree)->filter(function ($item) {
                return $item instanceof Chapter;
            });
            
            if (!$chapters->isEmpty()) {
                // Sử dụng lại ChapterSubService để xử lý việc thêm sub-chapters
                $chapterSubService = $this->app->make(ChapterSubService::class);
                $chapterSubService->addVisibleSubChapters($chapters);
            }
            
            // Cập nhật lại view với dữ liệu đã xử lý
            $view->with('sidebarTree', $sidebarTree);
        });
        
        // Đảm bảo chapter trong các template có thông tin về sub-chapters
        view()->composer([
            'chapters.parts.list-item', 
            'themes.shadcn.chapters.parts.list-item', 
            'chapters.parts.child-menu', 
            'themes.shadcn.chapters.parts.child-menu'
        ], function ($view) {
            $viewData = $view->getData();
            $chapter = $viewData['chapter'] ?? $viewData['bookChild'] ?? null;
            
            if ($chapter instanceof Chapter) {
                $chapterSubService = $this->app->make(ChapterSubService::class);
                $chapterSubService->addVisibleSubChapters($chapter);
            }
        });
    }
    
    /**
     * Đăng ký các sự kiện cho entity (chapter, book...)
     */
    protected function registerEntityEvents()
    {
        $chapterEvents = [
            'entity_boot', 
            'entity_show', 
            'entity_listing'
        ];
        
        // Đăng ký listener cho các sự kiện entity
        foreach ($chapterEvents as $event) {
            Theme::listen($event, function ($entity) {
                // Xử lý một entity
                if ($entity instanceof Chapter) {
                    $this->app->make(ChapterSubService::class)->addVisibleSubChapters($entity);
                }
                
                // Xử lý collection các entity
                elseif (is_iterable($entity)) {
                    $chapters = collect($entity)->filter(function ($item) {
                        return $item instanceof Chapter;
                    });
                    
                    if (!$chapters->isEmpty()) {
                        $this->app->make(ChapterSubService::class)->addVisibleSubChapters($chapters);
                    }
                }
            });
        }
    }

    /**
     * Đăng ký các route tùy chỉnh
     */
    protected function registerRoutes()
    {
        // Route để tạo sub-chapter
        Route::get('/books/{bookSlug}/chapter/{chapterSlug}/create-chapter', [
            'uses' => '\Themes\Shadcn\App\Entities\Controllers\ChapterControllerExtension@create',
            'as' => 'chapters.sub.create',
        ])->middleware(['auth']);
        
        Route::post('/books/{bookSlug}/chapter/{chapterSlug}/create-chapter', [
            'uses' => '\Themes\Shadcn\App\Entities\Controllers\ChapterControllerExtension@store',
            'as' => 'chapters.sub.store',
        ])->middleware(['auth']);
    }

    /**
     * Đăng ký permission policies cho nested chapters
     */
    protected function registerPermissionPolicies()
    {
        // Không có event ThemeEvents::AUTH_PERMISSIONS_CHECK
        // Sử dụng Theme::listen để đăng ký custom listener
        Theme::listen('auth_permissions_check', function($ability, $object) {
            // Cho phép tạo sub-chapter nếu có quyền update trên chapter cha
            if ($ability === 'chapter-create' && is_a($object, \BookStack\Entities\Models\Chapter::class)) {
                return Auth::check() && Gate::allows('update', $object);
            }
            
            // Permission handling cho subchapter
            if (is_a($object, \BookStack\Entities\Models\Chapter::class) && $object->parent_id) {
                $parent = \BookStack\Entities\Models\Chapter::find($object->parent_id);
                if ($parent && in_array($ability, ['view', 'update', 'delete'])) {
                    // Inherit permissions from parent chapter
                    return Auth::check() && Gate::allows($ability, $parent);
                }
            }
            
            return null;
        });
    }
}