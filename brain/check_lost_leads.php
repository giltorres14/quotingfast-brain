<?php

echo "=== CHECKING FOR LOST LEADS ===\n\n";

// Check local logs
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    
    // Look for webhook attempts
    $patterns = [
        '/webhook\.php.*404/',
        '/webhook\/home.*404/',
        '/webhook\/auto.*404/',
        '/Route \[.*webhook.*\] not defined/',
        '/POST.*\/webhook\/(home|auto).*500/',
        '/Missing required contact information/',
        '/Invalid phone number/'
    ];
    
    $foundAttempts = [];
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            $foundAttempts[] = count($matches[0]) . " matches for: " . $pattern;
        }
    }
    
    if (!empty($foundAttempts)) {
        echo "Found evidence of rejected webhook attempts:\n";
        foreach ($foundAttempts as $attempt) {
            echo "  - " . $attempt . "\n";
        }
    } else {
        echo "No evidence of webhook attempts in local logs.\n";
    }
}

echo "\n=== OPTIONS TO RECOVER LOST LEADS ===\n\n";

echo "1. **CHECK WITH LEAD PROVIDER:**\n";
echo "   - Contact LeadsQuotingFast or whoever sends leads\n";
echo "   - They should have logs of failed deliveries (HTTP 404/500 errors)\n";
echo "   - Request them to resend the failed leads\n\n";

echo "2. **CHECK RENDER LOGS:**\n";
echo "   - Go to https://dashboard.render.com\n";
echo "   - Navigate to the Brain service\n";
echo "   - Check the Logs tab for the past few days\n";
echo "   - Look for POST requests to /webhook/home or /webhook/auto\n";
echo "   - The request bodies might be logged there\n\n";

echo "3. **CHECK EXTERNAL MONITORING:**\n";
echo "   - If you use services like Datadog, New Relic, or CloudWatch\n";
echo "   - They might have captured the failed requests\n\n";

echo "4. **CHECK EMAIL/ALERTS:**\n";
echo "   - Lead providers often send email alerts for failed deliveries\n";
echo "   - Check if there are any failure notifications\n\n";

echo "=== WHY THIS HAPPENED ===\n\n";
echo "The issue was TWO-FOLD:\n";
echo "1. The webhook routes were commented out (disabled)\n";
echo "   - This caused 404 errors for any incoming leads\n";
echo "2. Even when enabled, they weren't saving to database\n";
echo "   - They only logged and validated, never called Lead::create()\n\n";

echo "That's why nothing appeared in 'Stuck in Queue' - the leads never made it into the system.\n\n";

echo "=== PREVENTION GOING FORWARD ===\n\n";
echo "Now the webhooks:\n";
echo "✓ Are active and will respond to requests\n";
echo "✓ Will save leads to the database\n";
echo "✓ Will show leads in the dashboard immediately\n";
echo "✓ Will put them in 'Stuck in Queue' if Vici fails\n";
echo "✓ Will log all attempts for debugging\n";
