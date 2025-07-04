#!/bin/bash
# query-chaincode.sh - Query blockchain for donor medical info or match info.

LOG_FILE="/tmp/fabric_query_$(date +%s).log"
echo "=== BLOCKCHAIN QUERY SCRIPT LOG ===" > "$LOG_FILE"
echo "Started: $(date)" >> "$LOG_FILE"
echo "Running as user: $(whoami)" >> "$LOG_FILE"

# Use globally available peer binary
PEER_CMD=$(which peer)
echo "Using peer command at: $PEER_CMD" >> "$LOG_FILE"

# Set environment variables
export FABRIC_CFG_PATH="/opt/fabric-config"
export CORE_PEER_TLS_ENABLED=true
export CORE_PEER_LOCALMSPID="Org1MSP"
export CORE_PEER_TLS_ROOTCERT_FILE="/opt/fabric-tls/org1/peer0-org1-ca.crt"
export CORE_PEER_MSPCONFIGPATH="/opt/fabric-identities/org1/msp"
export CORE_PEER_ADDRESS="localhost:7051"

# Log environment details
echo "FABRIC_CFG_PATH: $FABRIC_CFG_PATH" >> "$LOG_FILE"
echo "CORE_PEER_TLS_ENABLED: $CORE_PEER_TLS_ENABLED" >> "$LOG_FILE"
echo "CORE_PEER_LOCALMSPID: $CORE_PEER_LOCALMSPID" >> "$LOG_FILE"
echo "CORE_PEER_TLS_ROOTCERT_FILE: $CORE_PEER_TLS_ROOTCERT_FILE" >> "$LOG_FILE"
echo "CORE_PEER_MSPCONFIGPATH: $CORE_PEER_MSPCONFIGPATH" >> "$LOG_FILE"
echo "CORE_PEER_ADDRESS: $CORE_PEER_ADDRESS" >> "$LOG_FILE"

# Parse function argument
FUNCTION=$1
shift

if [ "$FUNCTION" == "ReadMatch" ]; then
    MATCH_ID=$1
    echo "Querying for Match ID: $MATCH_ID" >> "$LOG_FILE"
    JSON_ARGS=$(echo "{\"Args\":[\"ReadMatch\", \"$MATCH_ID\"]}")
else
    DONOR_ID=$FUNCTION
    echo "Querying for Donor ID: $DONOR_ID" >> "$LOG_FILE"
    JSON_ARGS=$(echo "{\"Args\":[\"ReadMedicalInfo\", \"$DONOR_ID\"]}")
fi

echo "JSON_ARGS: $JSON_ARGS" >> "$LOG_FILE"

# Execute peer query command
OUTPUT=$($PEER_CMD chaincode query -C mychannel -n donor_medical_info -c "$JSON_ARGS" 2>> "$LOG_FILE")
EXIT_CODE=$?

# Log and return output
echo "Raw output:" >> "$LOG_FILE"
echo "$OUTPUT" >> "$LOG_FILE"
echo "Exit code: $EXIT_CODE" >> "$LOG_FILE"

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
