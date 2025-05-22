<?php
return [
    // Single entity names
    'book' => 'Không gian',
    'chapter' => 'Thư mục',
    'page' => 'Trang',
    'shelf' => 'Danh mục',
    
    // Plural entity names
    'books' => 'Không gian',
    'chapters' => 'Thư mục',
    'pages' => 'Trang',
    'shelves' => 'Danh mục',
    
    // Entity specific actions
    'books_create' => 'Tạo không gian mới',
    'chapters_create' => 'Tạo thư mục mới',
    'pages_create' => 'Tạo trang mới',
    'shelves_create' => 'Tạo danh mục mới',
    
    'books_new' => 'Không gian mới',
    'chapters_new' => 'Thư mục mới',
    'pages_new' => 'Trang mới',
    'shelves_new' => 'Danh mục mới',
    'shelves_new_action' => 'Danh mục mới',
    
    'books_edit' => 'Sửa không gian',
    'chapters_edit' => 'Sửa thư mục',
    'pages_edit' => 'Sửa trang',
    'shelves_edit' => 'Sửa danh mục',
    'shelves_edit_named' => 'Sửa danh mục :name',
    
    'books_delete' => 'Xóa không gian',
    'chapters_delete' => 'Xóa thư mục',
    'pages_delete' => 'Xóa trang',
    'shelves_delete' => 'Xóa danh mục',
    'shelves_delete_named' => 'Xóa danh mục :name',
    
    'books_sort' => 'Sắp xếp không gian',
    'chapters_sort' => 'Sắp xếp thư mục',
    'pages_sort' => 'Sắp xếp trang',
    
    'books_empty' => 'Không có không gian nào được tạo',
    'chapters_empty' => 'Không có thư mục nào được tạo',
    'pages_empty' => 'Không có trang nào được tạo',
    'shelves_empty' => 'Không có danh mục nào được tạo',
    
    'books_popular' => 'Những không gian phổ biến',
    'chapters_popular' => 'Những thư mục phổ biến',
    'pages_popular' => 'Các trang phổ biến',
    'shelves_popular' => 'Các danh mục phổ biến',
    
    'books_recent' => 'Những không gian gần đây',
    'chapters_recent' => 'Những thư mục gần đây',
    'pages_recent' => 'Những trang gần đây',
    
    'books_navigation' => 'Điều hướng không gian',
    'chapters_navigation' => 'Điều hướng thư mục',
    'pages_navigation' => 'Điều hướng trang',
    
    'books_empty_contents' => 'Không có trang hay thư mục nào được tạo cho không gian này.',
    'chapters_empty_contents' => 'Không có trang nào trong thư mục này.',
    'shelves_empty_contents' => 'Danh mục này không có không gian nào',
    
    'books_search_this' => 'Tìm kiếm trong không gian này',
    'chapters_search_this' => 'Tìm kiếm trong thư mục này',
    
    'books_permissions' => 'Quyền của không gian',
    'chapters_permissions' => 'Quyền của thư mục',
    'pages_permissions' => 'Quyền của trang',
    'shelves_permissions' => 'Quyền của danh mục',
    
    'books_permissions_active' => 'Đang bật các quyền hạn từ không gian',
    'chapters_permissions_active' => 'Đang bật các quyền hạn từ thư mục',
    'pages_permissions_active' => 'Đang bật các quyền hạn từ trang',
    'shelves_permissions_active' => 'Đang bật các quyền hạn từ danh mục',
    
    'recently_created_pages' => 'Những trang được tạo gần đây',
    'recently_updated_pages' => 'Những trang được cập nhật gần đây',
    'recently_created_chapters' => 'Những thư mục được tạo gần đây',
    'recently_created_books' => 'Những không gian được tạo gần đây',
    'recently_created_shelves' => 'Những danh mục được tạo gần đây',
    
    'x_books' => ':count Không gian|:count Không gian',
    'x_chapters' => ':count Thư mục|:count Thư mục',
    'x_pages' => ':count Trang|:count Trang',
    'x_shelves' => ':count Danh mục|:count Danh mục',
    
    'book_tags' => 'Thẻ không gian',
    'chapter_tags' => 'Thẻ thư mục',
    'page_tags' => 'Thẻ trang',
    'shelf_tags' => 'Thẻ danh mục',
    
    'tags_assigned_books' => 'Được gán cho không gian',
    'tags_assigned_chapters' => 'Được gán cho thư mục',
    'tags_assigned_pages' => 'Được gán cho trang',
    'tags_assigned_shelves' => 'Được gán cho danh mục',
    
    // Shelf-specific terms
    'shelves_books' => 'Không gian trong danh mục này',
    'shelves_add_books' => 'Thêm không gian vào danh mục này',
    'shelves_drag_books' => 'Kéo không gian bên dưới để thêm vào danh mục này',
    'shelves_edit_and_assign' => 'Chỉnh sửa danh mục để gán không gian',
    'shelves_delete_explain' => "Thao tác này sẽ xóa danh mục có tên ':name'. Không gian chứa trong đó sẽ không bị xóa.",
    'shelves_delete_confirmation' => 'Bạn có chắc chắn muốn xóa danh mục này?',
    'shelves_permissions_updated' => 'Quyền của danh mục đã được cập nhật',
    'shelves_permissions_cascade_warning' => 'Quyền trên danh mục không tự động áp dụng cho không gian chứa trong đó. Điều này là do một không gian có thể tồn tại trong nhiều danh mục. Tuy nhiên, quyền có thể được sao chép đến các không gian con bằng tùy chọn dưới đây.',
    'shelves_copy_permissions_to_books' => 'Sao chép quyền cho không gian',
    'shelves_copy_permissions' => 'Sao chép quyền',
    'shelves_copy_permissions_explain' => 'Thao tác này sẽ áp dụng cài đặt quyền hiện tại của danh mục này cho tất cả không gian chứa trong đó. Trước khi kích hoạt, hãy đảm bảo rằng mọi thay đổi đối với quyền của danh mục này đã được lưu.',
    'shelves_copy_permission_success' => 'Quyền của danh mục đã được sao chép cho :count không gian',
    
    // Conversion options
    'convert_to_shelf' => 'Chuyển thành Danh mục',
    'convert_to_shelf_contents_desc' => 'Bạn có thể chuyển đổi không gian này thành một danh mục mới với cùng nội dung. Thư mục trong không gian này sẽ được chuyển đổi thành không gian mới. Nếu không gian này chứa trang không thuộc thư mục nào, không gian này sẽ được đổi tên và chứa các trang đó, và không gian này sẽ trở thành một phần của danh mục mới.',
    'convert_to_shelf_permissions_desc' => 'Bất kỳ quyền nào được đặt trên không gian này sẽ được sao chép sang danh mục mới và tất cả không gian con mới không có quyền riêng. Lưu ý rằng quyền trên danh mục không tự động áp dụng cho nội dung bên trong, như chúng được áp dụng cho không gian.',
]; 