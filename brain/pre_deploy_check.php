#!/usr/bin/env php
<?php
/**
 * Pre-deployment Verification Script
 * Run this before every deployment to catch common issues
 */

echo "\n🔍 PRE-DEPLOYMENT CHECK STARTING...\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$errors = [];
$warnings = [];
$passed = [];

// 1. Check PHP syntax of all critical files
echo "1. Checking PHP Syntax...\n";
$phpFiles = [
    'routes/web.php',
    'import_lqf_bulk.php',
    'test_import_first_lead.php'
];

foreach ($phpFiles as $file) {
    if (!file_exists($file)) {
        $warnings[] = "File not found: $file";
        continue;
    }
    
    $output = [];
    $returnCode = 0;
    exec("php -l $file 2>&1", $output, $returnCode);
    
    if ($returnCode !== 0) {
        $errors[] = "PHP syntax error in $file: " . implode("\n", $output);
    } else {
        $passed[] = "✓ $file syntax OK";
    }
}

// 2. Check Blade template syntax
echo "\n2. Checking Blade Templates...\n";
$bladeFiles = glob('resources/views/**/*.blade.php');
foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    
    // Check for balanced Blade directives
    $ifCount = substr_count($content, '@if');
    $endifCount = substr_count($content, '@endif');
    
    if ($ifCount !== $endifCount) {
        $errors[] = "Unbalanced @if/@endif in $file (if: $ifCount, endif: $endifCount)";
    }
    
    // Check for @if inside JavaScript functions (common error)
    if (preg_match('/function\s+\w+\s*\([^)]*\)\s*\{[^}]*@if/s', $content)) {
        $warnings[] = "Found @if inside JavaScript function in $file - may cause Blade parsing issues";
    }
    
    // Check for undefined variable access patterns
    if (preg_match('/\$\w+->(?!isset|empty|has)/', $content)) {
        // This is just a warning as it might be intentional
        $warnings[] = "Direct property access without isset() check in $file";
    }
}

if (empty($errors) && empty($warnings)) {
    $passed[] = "✓ All Blade templates OK";
}

// 3. Check database connection
echo "\n3. Checking Database Connection...\n";
try {
    $pdo = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM leads");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $passed[] = "✓ Database connected ({$result['count']} leads found)";
    
    // Check for required columns
    $requiredColumns = ['id', 'external_lead_id', 'name', 'first_name', 'last_name', 'phone', 'email'];
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'leads'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $columns)) {
            $errors[] = "Missing required column: leads.$col";
        }
    }
    
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
}

// 4. Check for common route conflicts
echo "\n4. Checking Routes...\n";
if (file_exists('routes/web.php')) {
    $routeContent = file_get_contents('routes/web.php');
    
    // Check for duplicate route definitions
    preg_match_all('/Route::(get|post|put|delete|patch)\([\'"]([^\'"]+)[\'"]/i', $routeContent, $matches);
    $routes = [];
    
    for ($i = 0; $i < count($matches[0]); $i++) {
        $method = $matches[1][$i];
        $path = $matches[2][$i];
        $key = "$method:$path";
        
        if (isset($routes[$key])) {
            $warnings[] = "Duplicate route definition: $method $path";
        }
        $routes[$key] = true;
    }
    
    if (empty($warnings)) {
        $passed[] = "✓ No duplicate routes found";
    }
}

// 5. Check for test data that shouldn't be deployed
echo "\n5. Checking for Test Data...\n";
$testPatterns = [
    'TEST_',
    'BRAIN_TEST',
    'console.log',
    'var_dump',
    'dd(',
    'dump(',
    'print_r'
];

foreach ($phpFiles as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    foreach ($testPatterns as $pattern) {
        if (stripos($content, $pattern) !== false) {
            $warnings[] = "Found test/debug code '$pattern' in $file";
        }
    }
}

// 6. Check file permissions
echo "\n6. Checking File Permissions...\n";
$writableDirs = ['storage', 'bootstrap/cache'];
foreach ($writableDirs as $dir) {
    if (!is_writable($dir)) {
        $warnings[] = "Directory not writable: $dir";
    } else {
        $passed[] = "✓ $dir is writable";
    }
}

// 7. Check for hardcoded credentials (security check)
echo "\n7. Security Check...\n";
$credentialPatterns = [
    '/password[\'"\s]*[:=][\'"\s]*[\'"][^\'"\s]{8,}[\'"]/',
    '/api_key[\'"\s]*[:=][\'"\s]*[\'"][^\'"\s]{20,}[\'"]/',
    '/secret[\'"\s]*[:=][\'"\s]*[\'"][^\'"\s]{10,}[\'"]/'
];

foreach ($phpFiles as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    foreach ($credentialPatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $warnings[] = "Possible hardcoded credentials in $file";
        }
    }
}

// ========== RESULTS ==========
echo "\n" . str_repeat("=", 52) . "\n";
echo "📊 PRE-DEPLOYMENT CHECK RESULTS\n";
echo str_repeat("=", 52) . "\n\n";

if (!empty($passed)) {
    echo "✅ PASSED (" . count($passed) . "):\n";
    foreach ($passed as $p) {
        echo "   $p\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $w) {
        echo "   - $w\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $e) {
        echo "   - $e\n";
    }
    echo "\n";
}

// Final verdict
echo str_repeat("=", 52) . "\n";
if (empty($errors)) {
    echo "✅ READY TO DEPLOY - No critical errors found\n";
    if (!empty($warnings)) {
        echo "   (Review warnings above before proceeding)\n";
    }
    exit(0);
} else {
    echo "❌ DO NOT DEPLOY - Critical errors must be fixed\n";
    exit(1);
}
