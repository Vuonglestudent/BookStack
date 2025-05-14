/**
 * BookStack Theme JavaScript Entry Point
 * This file serves as the central module for importing and initializing all theme JavaScript functionality
 */

// Khai báo các animation functions trực tiếp thay vì import
// Để tránh vấn đề với module imports
const animateStylesCleanupMap = new WeakMap();

function animateStyles(element, styles, animTime = 400, onComplete = null) {
    const styleNames = Object.keys(styles);
    for (const style of styleNames) {
        element.style.setProperty(style, styles[style][0]);
    }

    const cleanup = () => {
        for (const style of styleNames) {
            element.style.removeProperty(style);
        }
        element.style.removeProperty('transition');
        element.removeEventListener('transitionend', cleanup);
        animateStylesCleanupMap.delete(element);
        if (onComplete) onComplete();
    };

    setTimeout(() => {
        element.style.transition = `all ease-in-out ${animTime}ms`;
        for (const style of styleNames) {
            element.style.setProperty(style, styles[style][1]);
        }

        element.addEventListener('transitionend', cleanup);
        animateStylesCleanupMap.set(element, cleanup);
    }, 15);
}

function cleanupExistingElementAnimation(element) {
    if (animateStylesCleanupMap.has(element)) {
        const oldCleanup = animateStylesCleanupMap.get(element);
        oldCleanup();
    }
}

function slideUp(element, animTime = 400) {
    cleanupExistingElementAnimation(element);
    const currentHeight = element.getBoundingClientRect().height;
    const computedStyles = getComputedStyle(element);
    const currentPaddingTop = computedStyles.getPropertyValue('padding-top');
    const currentPaddingBottom = computedStyles.getPropertyValue('padding-bottom');
    const animStyles = {
        'max-height': [`${currentHeight}px`, '0px'],
        'overflow': ['hidden', 'hidden'],
        'padding-top': [currentPaddingTop, '0px'],
        'padding-bottom': [currentPaddingBottom, '0px'],
    };

    animateStyles(element, animStyles, animTime, () => {
        element.style.display = 'none';
    });
}

function slideDown(element, animTime = 400) {
    cleanupExistingElementAnimation(element);
    element.style.display = 'block';
    const targetHeight = element.getBoundingClientRect().height;
    const computedStyles = getComputedStyle(element);
    const targetPaddingTop = computedStyles.getPropertyValue('padding-top');
    const targetPaddingBottom = computedStyles.getPropertyValue('padding-bottom');
    const animStyles = {
        'max-height': ['0px', `${targetHeight}px`],
        'overflow': ['hidden', 'hidden'],
        'padding-top': ['0px', targetPaddingTop],
        'padding-bottom': ['0px', targetPaddingBottom],
    };

    animateStyles(element, animStyles, animTime);
}

// Định nghĩa class ChapterContentsExtended trực tiếp ở đây
class ChapterContentsExtended {
    constructor(element) {
        this.el = element;
        this.setup();
    }

    setup() {
        // Find all toggle buttons within this component
        this.toggleSections = new Map();
        
        // Tìm tất cả các nút toggle trong component
        const toggles = this.el.querySelectorAll('[data-toggle="chapter-contents"]');
        
        // Skip if no qualifying toggle elements found
        if (toggles.length === 0) return;
        
        // For each toggle button, find its corresponding list
        toggles.forEach(toggle => {
            const sectionId = toggle.getAttribute('data-section-id');
            if (!sectionId) return;
            
            // Find matching list element
            const listSelector = `[data-list="chapter-contents"][data-section-id="${sectionId}"]`;
            const list = this.el.querySelector(listSelector);
            
            if (toggle && list) {
                const isOpen = toggle.classList.contains('open');
                
                // Store the section info
                this.toggleSections.set(sectionId, {
                    toggle,
                    list,
                    isOpen
                });
                
                // Đảm bảo chỉ có một event handler bằng cách clone và thay thế
                const newToggle = toggle.cloneNode(true);
                toggle.parentNode.replaceChild(newToggle, toggle);
                
                // Lưu lại tham chiếu mới
                this.toggleSections.get(sectionId).toggle = newToggle;
                
                // Thêm event handler mới
                newToggle.addEventListener('click', this.handleClick.bind(this, sectionId));
            }
        });
    }

    open(sectionId) {
        const section = this.toggleSections.get(sectionId);
        if (!section) return;
        
        // Đánh dấu trạng thái trước khi thực hiện animation
        section.isOpen = true;
        section.toggle.classList.add('open');
        section.toggle.setAttribute('aria-expanded', 'true');
        
        // Hiển thị list và xác nhận bằng console
        section.list.style.display = 'block';
        console.log(`Opening section: ${sectionId}`);
        slideDown(section.list, 180);
    }

    close(sectionId) {
        const section = this.toggleSections.get(sectionId);
        if (!section) return;
        
        // Đánh dấu trạng thái trước khi thực hiện animation
        section.isOpen = false;
        section.toggle.classList.remove('open');
        section.toggle.setAttribute('aria-expanded', 'false');
        
        // Ẩn list và xác nhận bằng console
        console.log(`Closing section: ${sectionId}`);
        slideUp(section.list, 180);
    }

    handleClick(sectionId, event) {
        // Ngăn chặn hành vi mặc định và lan truyền sự kiện
        event.preventDefault();
        event.stopPropagation();
        
        console.log(`Click event on section: ${sectionId}`);
        
        const section = this.toggleSections.get(sectionId);
        if (!section) return;
        
        // Kiểm tra trạng thái hiện tại dựa trên thuộc tính isOpen
        const currentlyOpen = section.isOpen;
        console.log(`Current state: ${currentlyOpen ? 'open' : 'closed'}`);
        
        if (currentlyOpen) {
            // Nếu đang mở, đóng lại
            this.close(sectionId);
        } else {
            // Nếu đang đóng, mở ra - không đóng các phần khác
            this.open(sectionId);
        }
    }
}

// Initialize all components when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.clear(); // Xóa console trước để dễ theo dõi
    console.log('Initializing BookStack Theme JS...');
    
    // Khởi tạo component của chúng ta
    const chapterContentsElements = document.querySelectorAll('[data-component="chapter-contents"]');
    console.log(`Found ${chapterContentsElements.length} chapter content components`);
    
    chapterContentsElements.forEach((element, index) => {
        console.log(`Initializing component ${index + 1}`);
        new ChapterContentsExtended(element);
    });
    
    console.log('BookStack Theme: Chapter contents extensions initialized');
});
 