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