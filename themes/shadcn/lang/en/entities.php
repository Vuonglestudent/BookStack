<?php
return [
    // Single entity names
    'book' => 'Space',
    'chapter' => 'Folder',
    'page' => 'Page',
    'shelf' => 'Category',
    
    // Plural entity names
    'books' => 'Spaces',
    'chapters' => 'Folders',
    'pages' => 'Pages',
    'shelves' => 'Categories',
    
    // Entity specific actions
    'books_create' => 'Create Space',
    'chapters_create' => 'Create Folder',
    'pages_create' => 'Create Page',
    'shelves_create' => 'Create New Category',
    
    'books_new' => 'New Space',
    'chapters_new' => 'New Folder',
    'pages_new' => 'New Page',
    'shelves_new' => 'New Categories',
    'shelves_new_action' => 'New Category',
    
    'books_edit' => 'Edit Space',
    'chapters_edit' => 'Edit Folder',
    'pages_edit' => 'Edit Page',
    'shelves_edit' => 'Edit Category',
    'shelves_edit_named' => 'Edit Category :name',
    
    'books_delete' => 'Delete Space',
    'chapters_delete' => 'Delete Folder',
    'pages_delete' => 'Delete Page',
    'shelves_delete' => 'Delete Category',
    'shelves_delete_named' => 'Delete Category :name',
    
    'books_sort' => 'Sort Spaces',
    'chapters_sort' => 'Sort Folders',
    'pages_sort' => 'Sort Pages',
    
    'books_empty' => 'No spaces have been created',
    'chapters_empty' => 'No folders have been created',
    'pages_empty' => 'No pages have been created',
    'shelves_empty' => 'No categories have been created',
    
    'books_popular' => 'Popular Spaces',
    'chapters_popular' => 'Popular Folders',
    'pages_popular' => 'Popular Pages',
    'shelves_popular' => 'Popular Categories',
    
    'books_recent' => 'Recent Spaces',
    'chapters_recent' => 'Recent Folders',
    'pages_recent' => 'Recent Pages',
    
    'books_navigation' => 'Space Navigation',
    'chapters_navigation' => 'Folder Navigation',
    'pages_navigation' => 'Page Navigation',
    
    'books_empty_contents' => 'No pages or folders have been created for this space.',
    'chapters_empty_contents' => 'No pages have been created for this folder.',
    'shelves_empty_contents' => 'This category has no spaces assigned to it',
    
    'books_search_this' => 'Search this space',
    'chapters_search_this' => 'Search this folder',
    
    'books_permissions' => 'Space Permissions',
    'chapters_permissions' => 'Folder Permissions',
    'pages_permissions' => 'Page Permissions',
    'shelves_permissions' => 'Category Permissions',
    
    'books_permissions_active' => 'Space Permissions Active',
    'chapters_permissions_active' => 'Folder Permissions Active',
    'pages_permissions_active' => 'Page Permissions Active',
    'shelves_permissions_active' => 'Category Permissions Active',
    
    'recently_created_pages' => 'Recently Created Pages',
    'recently_updated_pages' => 'Recently Updated Pages',
    'recently_created_chapters' => 'Recently Created Folders',
    'recently_created_books' => 'Recently Created Spaces',
    'recently_created_shelves' => 'Recently Created Categories',
    
    'x_books' => ':count Space|:count Spaces',
    'x_chapters' => ':count Folder|:count Folders',
    'x_pages' => ':count Page|:count Pages',
    'x_shelves' => ':count Category|:count Categories',
    
    'book_tags' => 'Space Tags',
    'chapter_tags' => 'Folder Tags',
    'page_tags' => 'Page Tags',
    'shelf_tags' => 'Category Tags',
    
    'tags_assigned_books' => 'Assigned to Spaces',
    'tags_assigned_chapters' => 'Assigned to Folders',
    'tags_assigned_pages' => 'Assigned to Pages',
    'tags_assigned_shelves' => 'Assigned to Categories',
    
    // Shelf-specific terms
    'shelves_books' => 'Spaces in this category',
    'shelves_add_books' => 'Add spaces to this category',
    'shelves_drag_books' => 'Drag spaces below to add them to this category',
    'shelves_edit_and_assign' => 'Edit category to assign spaces',
    'shelves_delete_explain' => "This will delete the category with the name ':name'. Contained spaces will not be deleted.",
    'shelves_delete_confirmation' => 'Are you sure you want to delete this category?',
    'shelves_permissions_updated' => 'Category Permissions Updated',
    'shelves_permissions_cascade_warning' => 'Permissions on categories do not automatically cascade to contained spaces. This is because a space can exist in multiple categories. Permissions can however be copied down to child spaces using the option found below.',
    'shelves_copy_permissions_to_books' => 'Copy Permissions to Spaces',
    'shelves_copy_permissions' => 'Copy Permissions',
    'shelves_copy_permissions_explain' => 'This will apply the current permission settings of this category to all spaces contained within. Before activating, ensure any changes to the permissions of this category have been saved.',
    'shelves_copy_permission_success' => 'Category permissions copied to :count spaces',
    
    // Conversion options
    'convert_to_shelf' => 'Convert to Category',
    'convert_to_shelf_contents_desc' => 'You can convert this space to a new category with the same contents. Folders contained within this space will be converted to new spaces. If this space contains any pages, that are not in a folder, this space will be renamed and contain such pages, and this space will become part of the new category.',
    'convert_to_shelf_permissions_desc' => 'Any permissions set on this space will be copied to the new category and to all new child spaces that don\'t have their own permissions enforced. Note that permissions on categories do not auto-cascade to content within, as they do for spaces.',
]; 