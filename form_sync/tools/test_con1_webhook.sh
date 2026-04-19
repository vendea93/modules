#!/bin/bash
#
# Quick test script for CON1 form webhook
# 
# Usage: ./test_con1_webhook.sh

php modules/form_sync/tools/simulate_framer_webhook.php \
    --form-id=CON1 \
    --url=http://localhost:8080 \
    --secret=nlKtP7hERWluToi4OBNYAWaDM4k2b8Bd \
    --data='{"name":"Test User","email":"test@example.com","phone":"+1234567890","message":"Test message from webhook simulator"}'











