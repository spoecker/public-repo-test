#!/bin/sh

# Check if the unhealthy flag file exists
if [ -f /tmp/unhealthy ]; then
  # Kill the main process (PID 1) to stop the container
  kill 1
  exit 1
fi

# If healthy, exit with status 0
exit 0