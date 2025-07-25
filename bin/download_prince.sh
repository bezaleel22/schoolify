#!/bin/bash

# Download and install PrinceXML v16.1 for development
PRINCE_VERSION="16.1"
PRINCE_PACKAGE="prince-${PRINCE_VERSION}-linux-generic-x86_64.tar.gz"
PRINCE_URL="https://www.princexml.com/download/${PRINCE_PACKAGE}"
PRINCE_DIR="prince-${PRINCE_VERSION}-linux-generic-x86_64"

# Get the absolute path to the bin directory (where this script is located)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BIN_DIR="$SCRIPT_DIR"

echo "Downloading PrinceXML v${PRINCE_VERSION} to bin directory..."
echo "Target directory: $BIN_DIR"

# Change to bin directory to ensure everything is downloaded there
cd "$BIN_DIR"

# Download PrinceXML
curl -L -o "$PRINCE_PACKAGE" "$PRINCE_URL"

if [ $? -ne 0 ]; then
    echo "Failed to download PrinceXML"
    exit 1
fi

echo "Extracting PrinceXML..."
tar -xzf "$PRINCE_PACKAGE"

if [ $? -ne 0 ]; then
    echo "Failed to extract PrinceXML"
    exit 1
fi

# Install Prince to bin/prince-16.1-linux-generic-x86_64 directory
echo "Installing PrinceXML to $BIN_DIR directory..."
# Keep the extracted directory as-is in bin/
# The extracted directory is already named prince-16.1-linux-generic-x86_64

# Make prince binaries executable
chmod +x "${PRINCE_DIR}/bin/prince"
chmod +x "${PRINCE_DIR}/bin/princedebug"

# Create a wrapper script in bin/ directory for easy access
echo "Creating Prince wrapper script..."
cat > "$BIN_DIR/prince" <<EOF
#!/bin/sh
exec "$BIN_DIR/${PRINCE_DIR}/bin/prince" --prefix="$BIN_DIR/${PRINCE_DIR}" "\$@"
EOF

# Make wrapper script executable
chmod +x "$BIN_DIR/prince"

# Cleanup downloaded files
echo "Cleaning up temporary files..."
rm -rf "$PRINCE_PACKAGE"

echo "PrinceXML v${PRINCE_VERSION} installed successfully in $BIN_DIR/${PRINCE_DIR} directory"
echo "Binary location: $BIN_DIR/${PRINCE_DIR}/bin/prince"
echo "Wrapper script: $BIN_DIR/prince"
echo "Installation directory: $BIN_DIR/${PRINCE_DIR}/"