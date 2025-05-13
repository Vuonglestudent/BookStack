# BookStack Shadcn Theme Architecture

Tài liệu này định nghĩa cấu trúc kiến trúc và quy tắc cho theme Shadcn trong BookStack. Mọi bổ sung và thay đổi trong tương lai phải tuân theo các quy tắc được nêu trong tài liệu này để đảm bảo tính nhất quán và bảo trì.

## 1. Cấu trúc thư mục
themes/shadcn/
│
├── app/ # Mã nguồn PHP logic
│ ├── Entities/ # Các lớp liên quan đến entities
│ │ ├── Models/ # Mở rộng model
│ │ ├── Controllers/ # Override controller
│ │ └── Tools/ # Công cụ hỗ trợ
│ ├── Console/ # Lệnh CLI
│ │ └── Commands/ # Chứa các lệnh tùy chỉnh
│ └── ThemeServiceProvider.php # Class đăng ký các extension
│
├── database/ # Database migrations
│ └── migrations/ # Chứa các file migrations
│
├── chapters/ # Override UI templates
│ └── parts/ # Các template con
│
├── entities/ # Override entity templates
│
├── icons/ # SVG icons tùy chỉnh
│
├── lang/ # Bản dịch
│ ├── en/ # Tiếng Anh
│ └── vi/ # Tiếng Việt
│
├── public/ # Assets công khai
│ ├── css/ # Stylesheet
│ └── js/ # JavaScript
│
├── functions.php # Entry point của theme
├── structure-theme-system.md # Tài liệu kiến trúc (file này)
├── visual-theme-system.md # Tài liệu về hệ thống theme visual
└── logical-theme-system.md # Tài liệu về hệ thống theme logical

## 2. Quy tắc đặt tên và tổ chức

### 2.1 PHP Files

* **PascalCase** cho tên lớp: `ChapterRelations.php`, `NestedBookContents.php`
* **snake_case** cho tên file không phải lớp: `functions.php`
* Các lớp mở rộng phải có tên mô tả chức năng rõ ràng, ví dụ: `ChapterExtension`
* Các lớp phục vụ một mục đích cụ thể nên được đặt trong thư mục tương ứng
* **Namespace**: `Themes\Shadcn\App\*` cho mã PHP trong thư mục `app/`

### 2.2 Blade Templates

* **kebab-case** cho tên file blade: `book-tree.blade.php`
* Giữ nguyên cấu trúc đường dẫn tương đối như trong `resources/views/`
* Không thêm tiền tố hoặc hậu tố vào tên file template gốc khi override

### 2.3 Assets

* **kebab-case** cho tên file CSS và JavaScript
* Sử dụng cấu trúc thư mục rõ ràng theo loại asset (`css/`, `js/`)
* Asset tùy chỉnh phải được đặt trong thư mục `public/`

## 3. Nguyên tắc mở rộng mã nguồn

### 3.1 Quy tắc chung

* **KHÔNG** sửa đổi trực tiếp mã nguồn gốc của BookStack
* Sử dụng theme system để override views và extend logic
* Giữ mã nguồn của theme tách biệt hoàn toàn với core
* Mỗi lần cập nhật BookStack phải kiểm tra lại tính tương thích của theme

### 3.2 Bootstrapping

* File `functions.php` chỉ nên chứa code để bootstrap theme
* Sử dụng `ThemeServiceProvider` làm điểm trung tâm để đăng ký các mở rộng

```php
// functions.php
<?php

use BookStack\Facades\Theme;
use BookStack\Theming\ThemeEvents;
use Themes\Shadcn\App\ThemeServiceProvider;

Theme::listen(ThemeEvents::APP_BOOT, function($app) {
    // Load các class thủ công
    require_once __DIR__ . '/app/ThemeServiceProvider.php';
    
    // Khởi tạo ServiceProvider
    $provider = new ThemeServiceProvider($app);
    $provider->register();
    $provider->boot();
});
```

### 3.3 Mở rộng model

* Sử dụng `resolving()` để hook vào việc khởi tạo model
* Thêm relationships, casts và thuộc tính mới mà không sửa đổi class gốc

```php
app()->resolving(Chapter::class, function ($chapter) {
    // Thêm các relationships, casts, macros vào instance
    $chapter->mergeCasts(['parent_id' => 'integer']);
    
    $chapter::resolveRelationUsing('parent', function ($chapterModel) {
        return $chapterModel->belongsTo(Chapter::class, 'parent_id');
    });
});
```

### 3.4 Mở rộng controller

* Sử dụng dependency injection container để thay thế controllers
* Extend controller gốc để giữ tất cả hành vi mặc định

```php
$app->extend('BookStack\Entities\Controllers\ChapterController', 
    function($controller, $app) {
        return new \Themes\Shadcn\App\Entities\Controllers\NestedChapterController($controller);
    }
);
```

### 3.5 UI Templates

* Đặt template tại cùng đường dẫn tương đối như trong `resources/views/`
* Sao chép template gốc và thêm các chỉnh sửa cần thiết
* Không thay đổi cấu trúc cơ bản của template để đảm bảo tương thích

## 4. Tính năng chương lồng nhau (Nested Chapters)

### 4.1 Cơ sở dữ liệu

* Sử dụng trường `parent_id` trong bảng `chapters`
* Migration cần kiểm tra sự tồn tại của trường trước khi thêm

```php
if (!Schema::hasColumn('chapters', 'parent_id')) {
    Schema::table('chapters', function (Blueprint $table) {
        $table->integer('parent_id')->nullable()->default(null)->after('book_id');
        $table->index('parent_id');
    });
}
```

### 4.2 Model Relationships

* **parent**: `belongsTo(Chapter::class, 'parent_id')`
* **childChapters**: `hasMany(Chapter::class, 'parent_id')`

### 4.3 BookContents Extension

* Cần mở rộng `BookContents` để xử lý cấu trúc phân cấp trong sidebar
* Tách các chapter gốc và chapter con khi xây dựng cây nội dung

## 5. Quy tắc cập nhật

### 5.1 Migration

* Tạo migration có kiểm tra điều kiện để tránh lỗi khi chạy nhiều lần
* Sử dụng `theme:migrate` command để áp dụng migration

### 5.2 Bản địa hóa

* Thêm các khóa dịch mới trong `lang/en/entities.php` và các ngôn ngữ khác
* Đảm bảo tất cả text hiển thị cho người dùng đều được bản địa hóa

### 5.3 Kiểm tra tương thích

* Kiểm tra tất cả tính năng sau mỗi lần cập nhật BookStack
* Đặc biệt chú ý đến các trang template đã override
* Kiểm tra các route cụ thể khi BookStack thay đổi cấu trúc controller

## 6. Các lưu ý quan trọng

### 6.1 Hiệu suất

* Cẩn thận với các truy vấn lồng nhau sâu
* Giới hạn số lượng cấp của chapter lồng nhau nếu cần
* Sử dụng eager loading khi truy vấn nhiều cấp chapters

### 6.2 Phân quyền

* Chapter con phải kế thừa phân quyền từ chapter cha
* Kiểm tra các middleware phân quyền khi làm việc với nested resources

### 6.3 URLs và Routing

* URLs cho chapter con phải đảm bảo tính nhất quán với cấu trúc hiện có
* Xử lý đúng cách redirects và referred URLs

## 7. Quy trình phát triển

### 7.1 Cập nhật code

1. Fork từ nhánh chính
2. Tạo nhánh tính năng mới
3. Phát triển và kiểm thử
4. Gửi pull request với mô tả chi tiết

### 7.2 Testing

* Kiểm tra tất cả tính năng đã được thêm hoặc sửa đổi
* Đảm bảo tính tương thích với phiên bản BookStack hiện tại
* Kiểm tra cả trên desktop và mobile

### 7.3 Tài liệu

* Cập nhật tài liệu này khi thêm tính năng mới
* Mô tả rõ ràng mọi thay đổi đối với kiến trúc hoặc quy ước

---

## Tài liệu tham khảo

* [BookStack Visual Theme System](./visual-theme-system.md)
* [BookStack Logical Theme System](./logical-theme-system.md)
* [Laravel Documentation](https://laravel.com/docs)