#!/bin/bash

# Create a log file for tracking execution
LOG_FILE="/tmp/fabric_query_$(date +%s).log"
echo "=== BLOCKCHAIN QUERY SCRIPT LOG ===" > $LOG_FILE
echo "Started: $(date)" >> $LOG_FILE
echo "Running as user: $(whoami)" >> $LOG_FILE

# Set absolute path to peer command
PEER_CMD=$(which peer)
if [ -z "$PEER_CMD" ]; then
    PEER_CMD="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/bin/peer"  # Fallback path
    echo "No peer command found in PATH, using default: $PEER_CMD" >> $LOG_FILE
else
    echo "Found peer command at: $PEER_CMD" >> $LOG_FILE
fi

# Set required environment variables
export FABRIC_CFG_PATH=/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/config
export CORE_PEER_TLS_ENABLED=true
export CORE_PEER_LOCALMSPID="Org1MSP"
export CORE_PEER_TLS_ROOTCERT_FILE=/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org1.example.com/peers/peer0.org1.example.com/tls/ca.crt
export CORE_PEER_MSPCONFIGPATH=/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org1.example.com/users/Admin@org1.example.com/msp
export CORE_PEER_ADDRESS=localhost:7051

# Get donor ID from command line argument
DONOR_ID=$1
echo "Querying for Donor ID: $DONOR_ID" >> $LOG_FILE

# Create JSON args
JSON_ARGS=$(echo "{\"Args\":[\"ReadMedicalInfo\", \"$DONOR_ID\"]}")

# Execute the command and capture output
OUTPUT=$($PEER_CMD chaincode query -C mychannel -n donor_medical_info -c "$JSON_ARGS" 2>> $LOG_FILE)
EXIT_CODE=$?

# Log the raw output
echo "Raw output:" >> $LOG_FILE
echo "$OUTPUT" >> $LOG_FILE
echo "Exit code: $EXIT_CODE" >> $LOG_FILE

# If successful, ensure we're returning valid JSON
if [ $EXIT_CODE -eq 0 ]; then
    # Try to clean the output - strip any leading/trailing whitespace or log messages
    # This regex tries to extract just the JSON part from the output
    CLEAN_OUTPUT=$(echo "$OUTPUT" | grep -o '{.*}')
    if [ -n "$CLEAN_OUTPUT" ]; then
        # If we found what looks like JSON, use that
        echo "$CLEAN_OUTPUT"
    else
        # Otherwise return the original output
        echo "$OUTPUT"
    fi
else
    # If the command failed, return an error JSON
    echo "{\"error\":\"Failed to query blockchain\",\"exit_code\":$EXIT_CODE}"
fi

exit $EXIT_CODE