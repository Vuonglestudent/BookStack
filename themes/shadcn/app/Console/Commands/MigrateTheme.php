<?php

namespace Themes\Shadcn\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MigrateTheme extends Command
{
    protected $signature = 'theme:migrate';
    protected $description = 'Run migrations from the shadcn theme';

    public function handle()
    {
        $themeName = config('app.theme');
        $migrationsPath = base_path("themes/{$themeName}/database/migrations");
        
        $this->info("Running migrations from theme: {$themeName}");
        
        // Kiểm tra và thêm parent_id vào bảng chapters nếu chưa có
        if (!Schema::hasColumn('chapters', 'parent_id')) {
            $this->info('Adding parent_id column to chapters table...');
            Schema::table('chapters', function ($table) {
                $table->integer('parent_id')->nullable()->default(null)->after('book_id');
                $table->index('parent_id');
            });
            $this->info('Column parent_id added successfully!');
        } else {
            $this->info('Column parent_id already exists in chapters table.');
        }
        
        $this->info('Theme migration completed successfully!');
    }
}