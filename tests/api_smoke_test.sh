#!/usr/bin/env bash

set -e

BASE_URL="https://legislative-bill-information-portal.ddev.site"

echo "Running Legislative Bill Information Portal API tests..."
echo

echo "1. Testing GET /api/bills"
RESPONSE=$(curl -ks "$BASE_URL/api/bills")

echo "$RESPONSE" | grep -q '"count"'
echo "$RESPONSE" | grep -q '"data"'

echo "PASS"
echo

echo "2. Testing GET /api/bills/AB2306"
RESPONSE=$(curl -ks "$BASE_URL/api/bills/AB2306")

echo "$RESPONSE" | grep -q '"bill_number":"AB2306"'

echo "PASS"
echo

echo "3. Testing chamber filtering"
RESPONSE=$(curl -ks "$BASE_URL/api/bills?chamber=Senate")

echo "$RESPONSE" | grep -q '"chamber":"Senate"'

echo "PASS"
echo

echo "4. Testing keyword search"
RESPONSE=$(curl -ks "$BASE_URL/api/bills?search=digital")

echo "$RESPONSE" | grep -qi 'digital'

echo "PASS"
echo

echo "5. Testing invalid bill returns HTTP 404"
STATUS=$(curl -ks -o /dev/null -w "%{http_code}" "$BASE_URL/api/bills/INVALID")

if [ "$STATUS" != "404" ]; then
  echo "FAIL: Expected HTTP 404 but received $STATUS"
  exit 1
fi

echo "PASS"
echo

echo "All API smoke tests passed."
