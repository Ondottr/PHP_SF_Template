#!/usr/bin/env bash

# This script is used to run the application with Symfony CLI
# Run this script from the project root directory

# If you don't have Symfony CLI installed, use the built-in PHP web server
if ! command symfony -v &>/dev/null; then
  use_symfony_cli=false
else
  use_symfony_cli=true
  # Unable to start server with https when not using Symfony CLI
  httpsEnabled=true
fi

# You can pass the port as the first argument
port=$1

# If no port was passed, use the default one
if [ -z "$port" ]; then
  port=7000
fi

# Open the application in the browser
if [ "$httpsEnabled" = true ]; then
  gio open https://127.0.0.1:"$port"
else
  gio open http://127.0.0.1:"$port"
fi

# Start the web server
if [ "$use_symfony_cli" = true ]; then
  symfony server:start --port=$port
else
  php -S 127.0.0.1:"$port" -t public
fi
