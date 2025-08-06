#!/bin/bash

# RingBA Parameter Creation Script (CURL Version)
# Automatically creates all missing URL parameters for Allstate integration

# Configuration
ACCOUNT_ID="RAf810ac4421a34c9cbfbbf61288a1bec2"  # Your RingBA account ID
API_URL="https://api.ringba.com/v2/${ACCOUNT_ID}/queryPathMaps"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 RingBA Parameter Creation Script${NC}"
echo "==================================="
echo ""

# Check if account ID is set
if [ "$ACCOUNT_ID" = "YOUR_ACCOUNT_ID" ]; then
    echo -e "${RED}❌ ERROR: Please update the ACCOUNT_ID variable with your actual RingBA account ID${NC}"
    echo "   Find your account ID in your RingBA dashboard URL or API documentation"
    exit 1
fi

# All missing parameters to create
parameters=(
    # Personal Information
    "first_name" "last_name" "email" "phone" "address1" "city" "state" "country"
    
    # Demographics
    "dob" "gender" "marital_status" "residence_status" "education_level" "occupation"
    
    # Insurance Status
    "currently_insured" "current_insurance_company" "policy_expiration_date" "current_premium" 
    "insurance_duration" "policy_expires"
    
    # Coverage Requirements
    "coverage_level" "deductible_preference" "coverage_type"
    
    # Financial Information
    "credit_score_range" "credit_score" "home_ownership" "home_status"
    
    # Driving Information
    "years_licensed" "accidents_violations" "dui_conviction" "sr22_required" 
    "license_age" "active_license" "dui_timeframe" "dui_sr22"
    
    # Vehicle Information
    "num_vehicles" "vehicle_year" "vehicle_make" "vehicle_model" "vehicle_trim" 
    "vin" "leased" "annual_mileage" "primary_use" "commute_days" "commute_mileage" 
    "garage_type" "alarm"
    
    # Lead Quality & Timing
    "lead_source" "lead_quality_score" "urgency_level" "best_time_to_call" 
    "motivation_level" "motivation_score" "urgency"
    
    # TCPA & Compliance
    "consent_timestamp" "opt_in_method" "tcpa_compliant"
    
    # Technical Data
    "ip_address" "user_agent" "referrer_url" "landing_page"
    
    # Agent Qualification Metadata
    "qualified_by_agent" "qualification_timestamp" "agent_notes" "call_duration"
    
    # Additional Qualification Questions
    "shopping_for_rates" "ready_to_speak" "allstate_quote"
)

echo -e "${BLUE}📊 Total parameters to create: ${#parameters[@]}${NC}"
echo -e "${BLUE}🎯 Target API: $API_URL${NC}"
echo ""

# Ask for confirmation
echo "This will create ${#parameters[@]} new URL parameters in your RingBA account."
read -p "Are you sure you want to proceed? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Operation cancelled.${NC}"
    exit 0
fi

echo ""
echo -e "${BLUE}🔄 Creating parameters...${NC}"
echo ""

success_count=0
error_count=0
errors=()

for i in "${!parameters[@]}"; do
    parameter="${parameters[$i]}"
    progress=$((i + 1))
    
    printf "[%d/%d] Creating: %-25s " "$progress" "${#parameters[@]}" "$parameter"
    
    # Create the JSON payload
    json_payload="{\"incomingQueryStringName\":\"$parameter\",\"mapToTagName\":\"$parameter\",\"mapToTagType\":\"User\"}"
    
    # Make the API call
    response=$(curl -s -w "HTTPSTATUS:%{http_code}" \
        -X POST \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "$json_payload" \
        "$API_URL" \
        --connect-timeout 30 \
        --max-time 30)
    
    # Extract HTTP status and body
    http_code=$(echo "$response" | grep -o "HTTPSTATUS:[0-9]*" | cut -d: -f2)
    body=$(echo "$response" | sed -E 's/HTTPSTATUS:[0-9]*$//')
    
    if [ -z "$http_code" ]; then
        echo -e "${RED}❌ CONNECTION ERROR${NC}"
        ((error_count++))
        errors+=("$parameter: Connection error")
    elif [ "$http_code" -ge 200 ] && [ "$http_code" -lt 300 ]; then
        echo -e "${GREEN}✅ SUCCESS${NC}"
        ((success_count++))
    else
        echo -e "${RED}❌ HTTP $http_code${NC}"
        ((error_count++))
        errors+=("$parameter: HTTP $http_code")
        
        # Show response for debugging (first 100 chars)
        if [ ! -z "$body" ]; then
            echo "   Response: ${body:0:100}"
        fi
    fi
    
    # Small delay to avoid rate limiting
    sleep 0.1
done

echo ""
echo -e "${BLUE}📈 RESULTS SUMMARY${NC}"
echo "=================="
echo -e "${GREEN}✅ Successfully created: $success_count parameters${NC}"
echo -e "${RED}❌ Failed to create: $error_count parameters${NC}"
echo -e "${BLUE}📊 Total processed: ${#parameters[@]} parameters${NC}"
echo ""

if [ ${#errors[@]} -gt 0 ]; then
    echo -e "${RED}❌ ERRORS ENCOUNTERED:${NC}"
    for error in "${errors[@]}"; do
        echo "   • $error"
    done
    echo ""
fi

if [ $success_count -gt 0 ]; then
    echo -e "${GREEN}🎉 SUCCESS! $success_count parameters have been created in your RingBA account.${NC}"
    echo "   You can now use these parameters in your RingBA campaigns and tracking."
    echo ""
fi

if [ $error_count -gt 0 ]; then
    echo -e "${YELLOW}⚠️  Some parameters failed to create. Please check:${NC}"
    echo "   1. Your RingBA account ID is correct"
    echo "   2. You have proper API authentication (add API token if required)"
    echo "   3. Your RingBA account has permission to create parameters"
    echo "   4. The parameters don't already exist (some APIs reject duplicates)"
    echo ""
fi

echo -e "${BLUE}📋 Next steps:${NC}"
echo "   1. Login to your RingBA dashboard"
echo "   2. Go to Settings > URL Parameters"
echo "   3. Verify the new parameters are listed"
echo "   4. Test with a sample campaign"
echo ""

echo -e "${BLUE}🔗 For support, refer to RingBA API documentation:${NC}"
echo "   https://developers.ringba.com/"
echo ""

echo -e "${GREEN}✅ Script completed!${NC}"
