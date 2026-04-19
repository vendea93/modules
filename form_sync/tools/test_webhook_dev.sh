#!/bin/bash

# Test Webhook Script for Development Environment
# This script sends a sample webhook request to test the FormSync integration

# Configuration - Update these values as needed
FORM_ID="${1:-Contact}"  # Form ID from your form configuration
BASE_URL="${2:-http://localhost:8080}"  # Your development server URL
WEBHOOK_SECRET="${3:-}"  # Optional: Webhook secret if configured

# Generate submission ID
SUBMISSION_ID="test_$(date +%s)_$(shuf -i 1000-9999 -n 1)"

# Sample form data
FORM_DATA='{
    "name": "Test User Dev",
    "email": "testuser'$(date +%s)'@example.com",
    "phone": "+1234567890",
    "message": "Test webhook submission from development environment - '$(date)'",
    "company": "Test Company Inc."
}'

# Webhook URL
WEBHOOK_URL="${BASE_URL}/form_sync/webhook/framer/${FORM_ID}"

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         FormSync Webhook Test - Development              ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo "Form ID:        ${FORM_ID}"
echo "Webhook URL:    ${WEBHOOK_URL}"
echo "Submission ID:  ${SUBMISSION_ID}"
echo "Has Secret:     $([ -z "$WEBHOOK_SECRET" ] && echo 'No' || echo 'Yes')"
echo ""
echo "Payload:"
echo "${FORM_DATA}" | jq '.' 2>/dev/null || echo "${FORM_DATA}"
echo ""

# Generate signature if secret is provided
SIGNATURE=""
if [ ! -z "$WEBHOOK_SECRET" ]; then
    # Framer signature: SHA-256 HMAC of (payload + submission_id)
    PAYLOAD_JSON=$(echo "${FORM_DATA}" | jq -c '.' 2>/dev/null || echo "${FORM_DATA}")
    HMAC_INPUT="${PAYLOAD_JSON}${SUBMISSION_ID}"
    SIGNATURE=$(echo -n "${HMAC_INPUT}" | openssl dgst -sha256 -hmac "${WEBHOOK_SECRET}" -binary | xxd -p -c 256)
    SIGNATURE="sha256=${SIGNATURE}"
    echo "Generated Signature: ${SIGNATURE}"
    echo ""
fi

# Prepare headers
HEADERS=(
    "Content-Type: application/json"
    "Framer-Webhook-Submission-Id: ${SUBMISSION_ID}"
)

if [ ! -z "$SIGNATURE" ]; then
    HEADERS+=("Framer-Signature: ${SIGNATURE}")
fi

# Build curl command
CURL_CMD="curl -X POST '${WEBHOOK_URL}'"
for header in "${HEADERS[@]}"; do
    CURL_CMD="${CURL_CMD} -H '${header}'"
done
CURL_CMD="${CURL_CMD} -d '${FORM_DATA}'"
CURL_CMD="${CURL_CMD} -w '\n\nHTTP Status: %{http_code}\n'"
CURL_CMD="${CURL_CMD} -s -S"

echo "Sending webhook request..."
echo "----------------------------------------"
echo ""

# Execute curl command
eval "${CURL_CMD}"

echo ""
echo "----------------------------------------"
echo ""
echo "✓ Webhook sent! Check your FormSync logs to see the result."
echo ""
echo "To view logs, go to: FormSync > Logs"
echo "To view pending submissions: FormSync > Pending Review"
echo ""











