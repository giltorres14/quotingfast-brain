# 🧠 BRAIN DEVELOPMENT SETUP GUIDE
## Complete Development Environment Configuration

---

## 🚀 QUICK START

### **1. Prerequisites**
- PHP 8.1+
- Composer
- Node.js 18+
- PostgreSQL (production) / SQLite (local)
- Cursor IDE with extensions

### **2. Environment Setup**
```bash
# Clone and setup
cd /Users/giltorres/Downloads/platformparcelsms-main/brain
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### **3. Local Development**
```bash
# Start local server
php artisan serve --port=8001

# Run queue worker (if needed)
php artisan queue:work
```

---

## 🔧 CURSOR IDE EXTENSIONS

### **Essential Extensions (Auto-installed)**
```json
{
    "recommendations": [
        // PHP Development Core
        "bmewburn.vscode-intelephense-client",    // PHP IntelliSense
        "xdebug.php-debug",                       // PHP Debugging
        "valeryanm.vscode-phpsab",                // PHP CodeSniffer
        
        // Laravel Framework
        "onecentlin.laravel-blade",               // Blade Templates
        "amiralizadeh9480.laravel-extra-intellisense", // Laravel IntelliSense
        "ryannaddy.laravel-artisan",              // Artisan Commands
        
        // Database Management
        "cweijan.vscode-mysql-client2",           // Database Client
        "ms-mssql.mssql",                         // SQL Server Support
        
        // Code Quality
        "esbenp.prettier-vscode",                 // Code Formatting
        "bradlc.vscode-tailwindcss",              // Tailwind CSS
        "ms-vscode.vscode-eslint",                // ESLint
        
        // Productivity
        "gruntfuggly.todo-tree",                  // TODO Tracking
        "eamodio.gitlens",                        // Git Enhancement
        "alefragnani.bookmarks",                  // Code Bookmarks
        
        // API Development
        "humao.rest-client",                      // HTTP Client
        "rangav.vscode-thunder-client",           // API Testing
        
        // UI Enhancement
        "pkief.material-icon-theme",              // Better Icons
        "aaron-bond.better-comments"              // Enhanced Comments
    ]
}
```

### **Extension Benefits**
- **PHP IntelliSense**: Auto-completion, error detection, navigation
- **Laravel Blade**: Syntax highlighting, auto-completion for Blade templates
- **Todo Tree**: Track all TODO comments across the project
- **GitLens**: Enhanced Git integration with blame, history, and more
- **Thunder Client**: Test APIs directly in Cursor
- **Database Client**: Connect and query databases visually

---

## 📁 PROJECT STRUCTURE

```
brain/
├── app/
│   ├── Http/Controllers/     # Request handlers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   │   ├── AllstateCallTransferService.php    # Allstate API integration
│   │   ├── AllstateTestingService.php         # Testing orchestration
│   │   ├── AutoQualificationService.php       # Auto-fill qualification
│   │   ├── CRMIntegrationService.php          # Multi-CRM support
│   │   └── RingBAService.php                  # RingBA integration
│   └── ...
├── resources/views/         # Blade templates
│   ├── admin/              # Admin panel views
│   ├── buyer/              # Buyer dashboard views
│   └── agent/              # Agent interface views
├── routes/
│   ├── web.php             # Web routes (main file)
│   └── api.php             # API routes
├── database/
│   ├── migrations/         # Database schema changes
│   └── seeders/           # Sample data generators
└── public/                # Static assets
    ├── css/brain-design-system.css    # Unified design system
    └── js/brain-enhancements.js       # UI enhancements
```

---

## 🔄 DEVELOPMENT WORKFLOW

### **Daily Development**
1. **Pull latest changes**: `git pull origin main`
2. **Run migrations**: `php artisan migrate`
3. **Start local server**: `php artisan serve --port=8001`
4. **Open in Cursor**: Extensions auto-load
5. **Use Todo Tree**: Track all TODO comments

### **Testing APIs**
1. **Thunder Client**: Right-click → "Thunder Client" → "New Request"
2. **Test Allstate**: Use `/admin/allstate-testing` dashboard
3. **Debug with logs**: `tail -f storage/logs/laravel.log`

### **Database Management**
1. **Local**: SQLite browser or Database Client extension
2. **Production**: Use Database Client with PostgreSQL credentials
3. **Migrations**: `php artisan make:migration create_table_name`

---

## 🚨 CRITICAL REMINDERS

### **Before Making Changes**
- [ ] Check PROJECT_MEMORY.md for current status
- [ ] Review API_CONFIGURATIONS.md for exact field mappings
- [ ] Update CHANGE_LOG.md with all modifications
- [ ] Test locally before deploying

### **API Integration Rules**
- [ ] Always use exact field names from official documentation
- [ ] Test with realistic data (Tambara Farrell lead)
- [ ] Log all API requests and responses
- [ ] Never test production APIs until live

### **Code Quality Standards**
- [ ] Use services for business logic
- [ ] Follow Laravel conventions
- [ ] Add comprehensive logging
- [ ] Document all API integrations

---

## 🔗 USEFUL COMMANDS

```bash
# Laravel Artisan
php artisan route:list                # List all routes
php artisan make:controller Name      # Create controller
php artisan make:model Name -m       # Create model with migration
php artisan make:service Name        # Create service (custom command)

# Database
php artisan migrate                   # Run migrations
php artisan migrate:rollback         # Rollback last migration
php artisan db:seed                  # Run seeders

# Cache & Optimization
php artisan config:clear             # Clear config cache
php artisan route:clear              # Clear route cache
php artisan view:clear               # Clear view cache

# Queue Management
php artisan queue:work               # Process queue jobs
php artisan queue:failed             # List failed jobs
```

---

## 📊 MONITORING & DEBUGGING

### **Log Files**
- **Laravel Logs**: `storage/logs/laravel.log`
- **Web Server**: Local terminal output
- **Database**: Check migration status

### **Debug Tools**
- **Ray**: Advanced debugging (if installed)
- **Telescope**: Laravel debugging dashboard
- **Log Viewer**: Browser-based log viewer

### **Performance**
- **Query Log**: Enable in `.env` for SQL debugging
- **Memory Usage**: Monitor with `memory_get_usage()`
- **Response Time**: Use Laravel's built-in profiling

---

## 🎯 NEXT STEPS AFTER SETUP

1. **Explore Admin Panel**: `/admin/buyer-management`
2. **Test Allstate Integration**: `/admin/allstate-testing`  
3. **Review Documentation**: `PROJECT_MEMORY.md`
4. **Check API Configs**: `API_CONFIGURATIONS.md`
5. **Start Development**: Follow TODO comments with Todo Tree extension

---

*This guide is automatically updated with the project. Keep it current!*
