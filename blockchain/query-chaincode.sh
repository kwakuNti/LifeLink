#!/bin/bash
# query-chaincode.sh - Query blockchain for donor medical info or match info.
# Create a log file for tracking execution
LOG_FILE="/tmp/fabric_query_$(date +%s).log"
echo "=== BLOCKCHAIN QUERY SCRIPT LOG ===" > "$LOG_FILE"
echo "Started: $(date)" >> "$LOG_FILE"
echo "Running as user: $(whoami)" >> "$LOG_FILE"

# Set absolute path to peer command
PEER_CMD=$(which peer)
if [ -z "$PEER_CMD" ]; then
    PEER_CMD="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/bin/peer"  # Fallback path
    echo "No peer command found in PATH, using default: $PEER_CMD" >> "$LOG_FILE"
else
    echo "Found peer command at: $PEER_CMD" >> "$LOG_FILE"
fi
echo "PATH: $PATH" >> "$LOG_FILE"

# Set required environment variables
export FABRIC_CFG_PATH="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/config"
export CORE_PEER_TLS_ENABLED=true
export CORE_PEER_LOCALMSPID="Org1MSP"
export CORE_PEER_TLS_ROOTCERT_FILE="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org1.example.com/peers/peer0.org1.example.com/tls/ca.crt"
export CORE_PEER_MSPCONFIGPATH="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org1.example.com/users/Admin@org1.example.com/msp"
export CORE_PEER_ADDRESS="localhost:7051"

# Log environment variables
echo "Environment variables set:" >> "$LOG_FILE"
echo "FABRIC_CFG_PATH: $FABRIC_CFG_PATH" >> "$LOG_FILE"
echo "CORE_PEER_TLS_ENABLED: $CORE_PEER_TLS_ENABLED" >> "$LOG_FILE"
echo "CORE_PEER_LOCALMSPID: $CORE_PEER_LOCALMSPID" >> "$LOG_FILE"
echo "CORE_PEER_TLS_ROOTCERT_FILE: $CORE_PEER_TLS_ROOTCERT_FILE" >> "$LOG_FILE"
echo "CORE_PEER_MSPCONFIGPATH: $CORE_PEER_MSPCONFIGPATH" >> "$LOG_FILE"
echo "CORE_PEER_ADDRESS: $CORE_PEER_ADDRESS" >> "$LOG_FILE"

# Determine which function to query:
# If the first parameter is "ReadMatch", then we expect the next argument to be the match ID.
# Otherwise, default to querying donor medical info.
FUNCTION=$1
shift
if [ "$FUNCTION" == "ReadMatch" ]; then
    MATCH_ID=$1
    echo "Querying for Match ID: $MATCH_ID" >> "$LOG_FILE"
    JSON_ARGS=$(echo "{\"Args\":[\"ReadMatch\", \"$MATCH_ID\"]}")
else
    # In default mode, treat the first argument as donor ID.
    DONOR_ID=$FUNCTION
    echo "Querying for Donor ID: $DONOR_ID" >> "$LOG_FILE"
    JSON_ARGS=$(echo "{\"Args\":[\"ReadMedicalInfo\", \"$DONOR_ID\"]}")
fi
echo "JSON Arguments: $JSON_ARGS" >> "$LOG_FILE"

# Execute the peer chaincode query command and capture output
OUTPUT=$($PEER_CMD chaincode query -C mychannel -n donor_medical_info -c "$JSON_ARGS" 2>> "$LOG_FILE")
EXIT_CODE=$?

# Log the raw output and exit code
echo "Raw output:" >> "$LOG_FILE"
echo "$OUTPUT" >> "$LOG_FILE"
echo "Exit code: $EXIT_CODE" >> "$LOG_FILE"

# If successful, try to extract valid JSON from the output
if [ $EXIT_CODE -eq 0 ]; then
    CLEAN_OUTPUT=$(echo "$OUTPUT" | grep -o '{.*}')
    if [ -n "$CLEAN_OUTPUT" ]; then
        echo "$CLEAN_OUTPUT"
    else
        echo "$OUTPUT"
    fi
else
    echo "{\"error\":\"Failed to query blockchain\",\"exit_code\":$EXIT_CODE}"
fi

exit $EXIT_CODE
