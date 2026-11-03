#!/usr/bin/env bash
# Exact-size screenshots with headless Chrome. Profile and output stay on D:.
# Usage: ./shoot.sh <url> <name> [width] [height] [full]
#   full = capture the whole page height (taller window), otherwise one screen.
set -euo pipefail
cd "$(dirname "$0")"
url=$1; name=$2; w=${3:-1440}; h=${4:-900}; full=${5:-}
mkdir -p .hallmark/shots .hallmark/chrome-profile
[ -n "$full" ] && h=$full
"/c/Program Files/Google/Chrome/Application/chrome.exe" --headless=new --disable-gpu --hide-scrollbars \
  --user-data-dir="D:/csfr-lms/.hallmark/chrome-profile" --window-size="$w,$h" --virtual-time-budget=3000 \
  --screenshot="D:/csfr-lms/.hallmark/shots/$name.png" "$url" >/dev/null 2>&1
echo "D:/csfr-lms/.hallmark/shots/$name.png"
