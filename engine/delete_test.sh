#!/bin/bash

# Check if the argument is provided
if [ $# -eq 0 ]; then
    echo "Usage: $0 <folder_path>"
    exit 1
fi

# Check if the provided path is a directory
if [ ! -d "$1" ]; then
    echo "Error: $1 is not a directory"
    exit 1
fi

# Delete the folder and its contents recursively
rm -rf "$1"

# Check if deletion was successful
if [ $? -eq 0 ]; then
    echo "Folder $1 and its contents deleted successfully"
else
    echo "Error: Failed to delete folder $1 and its contents"
    exit 1
fi
