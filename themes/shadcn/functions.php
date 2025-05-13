<?php

/**
 * This file can be used to include any custom theme functionality.
 * This file is autoloaded if it exists.
 * Remember that this file is loaded very early in the app lifecycle.
 */

use BookStack\Facades\Theme;
use BookStack\Theming\ThemeEvents;
use Themes\Shadcn\App\ThemeServiceProvider;
use Illuminate\Support\Facades\Route;
use Themes\Shadcn\App\Entities\Controllers\ChapterControllerExtension;

// Require các class cần thiết
require_once __DIR__ . '/app/ThemeServiceProvider.php';
require_once __DIR__ . '/app/Console/Commands/MigrateTheme.php';
require_once __DIR__ . '/app/Entities/Models/ChapterRelations.php';
require_once __DIR__ . '/app/Entities/Controllers/ChapterControllerExtension.php';
require_once __DIR__ . '/app/Entities/Tools/BookContentsExtension.php';

// Đăng ký ThemeServiceProvider khi app boot
Theme::listen(ThemeEvents::APP_BOOT, function($app) {
    $provider = new Themes\Shadcn\App\ThemeServiceProvider($app);
    $provider->register();
    $provider->boot();
});

// Register theme custom routes theo cách đơn giản
Theme::listen(ThemeEvents::ROUTES_REGISTER_WEB_AUTH, function($router) {
    // Route để tạo sub-chapter
    $router->get('/books/{bookSlug}/chapter/{chapterSlug}/create-chapter', 
        [ChapterControllerExtension::class, 'create']
    )->name('chapters.sub.create');
    
    $router->post('/books/{bookSlug}/chapter/{chapterSlug}/create-chapter', 
        [ChapterControllerExtension::class, 'store']
    )->name('chapters.sub.store');
    
    // QUAN TRỌNG: Override các routes có sẵn để sử dụng controller extension
    // Route edit chapter - cần phải override
    $router->get('/books/{bookSlug}/chapter/{chapterSlug}/edit', 
        [ChapterControllerExtension::class, 'edit']
    );
    
    // Route update chapter - cần phải override
    $router->put('/books/{bookSlug}/chapter/{chapterSlug}', 
        [ChapterControllerExtension::class, 'update']
    );
});