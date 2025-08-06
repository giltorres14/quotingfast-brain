/**
 * QuotingFast Brain - UI Enhancements
 * Adds modern interactions and animations to the Brain interface
 */

class BrainUI {
    constructor() {
        this.init();
    }

    init() {
        this.setupAnimations();
        this.setupInteractions();
        this.setupFormEnhancements();
        this.setupTooltips();
        this.setupNotifications();
        this.setupLoadingStates();
    }

    // Smooth animations for page elements
    setupAnimations() {
        // Animate cards on page load
        const cards = document.querySelectorAll('.qf-card, .lead-card, .stat-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animate stats counters
        this.animateCounters();
    }

    // Enhanced interactions
    setupInteractions() {
        // Button ripple effect
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('qf-btn') || e.target.closest('.qf-btn')) {
                this.createRipple(e);
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Enhanced dropdown animations
        this.setupDropdowns();
    }

    // Form enhancements
    setupFormEnhancements() {
        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            });
        });

        // Input focus animations
        document.querySelectorAll('.qf-input, input, textarea').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('focused');
            });
        });

        // Real-time form validation
        this.setupFormValidation();
    }

    // Tooltip system
    setupTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    // Notification system
    setupNotifications() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
    }

    // Loading states
    setupLoadingStates() {
        // Auto-add loading states to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    this.setLoadingState(submitBtn, true);
                }
            });
        });
    }

    // Helper Methods
    createRipple(e) {
        const button = e.target.closest('.qf-btn') || e.target;
        const rect = button.getBoundingClientRect();
        const ripple = document.createElement('span');
        
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;
        
        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }

    animateCounters() {
        document.querySelectorAll('[data-count]').forEach(counter => {
            const target = parseInt(counter.dataset.count);
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = Math.floor(current).toLocaleString();
            }, 16);
        });
    }

    setupDropdowns() {
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const dropdown = toggle.closest('.dropdown');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                if (menu) {
                    const isOpen = menu.classList.contains('show');
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                        m.classList.remove('show');
                    });
                    
                    if (!isOpen) {
                        menu.classList.add('show');
                        menu.style.animation = 'slideDown 0.3s ease-out';
                    }
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }

    setupFormValidation() {
        document.querySelectorAll('input[required], textarea[required]').forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    this.validateField(input);
                }
            });
        });
    }

    validateField(field) {
        const isValid = field.checkValidity();
        field.classList.toggle('error', !isValid);
        
        // Remove existing error message
        const existingError = field.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        if (!isValid) {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.textContent = field.validationMessage;
            errorMsg.style.cssText = `
                color: var(--qf-error);
                font-size: 0.75rem;
                margin-top: 4px;
                animation: slideDown 0.2s ease-out;
            `;
            field.parentElement.appendChild(errorMsg);
        }
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'qf-tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: var(--qf-gray-800);
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.2s ease;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.bottom + 8 + 'px';
        
        requestAnimationFrame(() => {
            tooltip.style.opacity = '1';
            tooltip.style.transform = 'translateY(0)';
        });
    }

    hideTooltip() {
        document.querySelectorAll('.qf-tooltip').forEach(tooltip => {
            tooltip.remove();
        });
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `qf-notification qf-notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        notification.style.cssText = `
            background: white;
            border: 1px solid var(--qf-gray-200);
            border-left: 4px solid var(--qf-${type});
            border-radius: var(--qf-radius-md);
            box-shadow: var(--qf-shadow-lg);
            padding: 16px;
            margin-bottom: 10px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        document.getElementById('notification-container').appendChild(notification);
        
        requestAnimationFrame(() => {
            notification.style.transform = 'translateX(0)';
        });
        
        if (duration > 0) {
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
    }

    setLoadingState(button, loading) {
        if (loading) {
            button.classList.add('loading');
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.innerHTML = `
                <span class="spinner"></span>
                ${button.dataset.loadingText || 'Loading...'}
            `;
        } else {
            button.classList.remove('loading');
            button.disabled = false;
            button.textContent = button.dataset.originalText;
        }
    }
}

// CSS for animations and components
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to { transform: scale(2); opacity: 0; }
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .spinner {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 8px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .error {
        border-color: var(--qf-error) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
    
    .focused {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    .dropdown-menu {
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }
    
    .dropdown-menu.show {
        opacity: 1;
        transform: translateY(0);
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => new BrainUI());
} else {
    new BrainUI();
}

// Global utility functions
window.BrainUI = {
    showNotification: (message, type, duration) => {
        const ui = new BrainUI();
        ui.showNotification(message, type, duration);
    },
    
    setLoadingState: (button, loading) => {
        const ui = new BrainUI();
        ui.setLoadingState(button, loading);
    }
};