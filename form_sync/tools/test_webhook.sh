#!/bin/bash
#
# Framer Webhook Test Script
# 
# Simple wrapper script to test Framer webhooks locally
# 
# Usage:
#   ./test_webhook.sh FORM_ID [SECRET] [URL]
#
# Examples:
#   ./test_webhook.sh demo1
#   ./test_webhook.sh demo1 my_secret_key
#   ./test_webhook.sh demo1 my_secret_key http://localhost:3000

FORM_ID=$1
SECRET=${2:-}
URL=${3:-http://localhost:8080}

if [ -z "$FORM_ID" ]; then
    echo "Usage: $0 FORM_ID [SECRET] [URL]"
    echo ""
    echo "Examples:"
    echo "  $0 demo1"
    echo "  $0 demo1 my_secret_key"
    echo "  $0 demo1 my_secret_key http://localhost:3000"
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [ -z "$SECRET" ]; then
    php "$SCRIPT_DIR/simulate_framer_webhook.php" --form-id="$FORM_ID" --url="$URL"
else
    php "$SCRIPT_DIR/simulate_framer_webhook.php" --form-id="$FORM_ID" --secret="$SECRET" --url="$URL"
fi











