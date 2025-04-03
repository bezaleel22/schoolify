#!/bin/bash

# Change to the repository root directory
cd "$(git rev-parse --show-toplevel)" || { echo "Failed to navigate to git root directory"; exit 1; }

echo "Checking for Git lock files..."

# Check for index.lock
if [ -f .git/index.lock ]; then
    echo "Found .git/index.lock file."
    echo "Checking if any git processes are still running..."
    
    # Check for running git processes
    git_processes=$(pgrep -l git)
    
    if [ -n "$git_processes" ]; then
        echo "Warning: Git processes are still running:"
        echo "$git_processes"
        echo "You might want to wait for them to finish or terminate them manually."
        read -p "Do you want to continue removing the lock file anyway? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo "Operation cancelled."
            exit 1
        fi
    else
        echo "No Git processes found running."
    fi
    
    # Remove the lock file
    rm -f .git/index.lock
    echo "Removed .git/index.lock file."
else
    echo "No .git/index.lock file found."
fi

# Check for other potential lock files
for lock_file in .git/*.lock; do
    if [ -f "$lock_file" ] && [ "$lock_file" != ".git/index.lock" ]; then
        echo "Found additional lock file: $lock_file"
        read -p "Do you want to remove this lock file? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm -f "$lock_file"
            echo "Removed $lock_file."
        else
            echo "Skipped removing $lock_file."
        fi
    fi
done

echo "Git lock file check complete."
echo "You can now try your Git operation again."
