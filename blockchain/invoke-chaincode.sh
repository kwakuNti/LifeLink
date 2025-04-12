#!/bin/bash
# invoke-chaincode.sh - Supports both CreateMedicalInfo and CreateMatch
# Create a log file for tracking execution
LOG_FILE="/tmp/fabric_invoke_$(date +%s).log"
echo "=== BLOCKCHAIN INVOKE SCRIPT LOG ===" > $LOG_FILE
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
echo "PATH: $PATH" >> $LOG_FILE

# Set required environment variables
export FABRIC_CFG_PATH="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/config"
export CORE_PEER_TLS_ENABLED=true
export CORE_PEER_LOCALMSPID="Org1MSP"
export CORE_PEER_TLS_ROOTCERT_FILE="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org1.example.com/peers/peer0.org1.example.com/tls/ca.crt"
export CORE_PEER_MSPCONFIGPATH="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org1.example.com/users/Admin@org1.example.com/msp"
export CORE_PEER_ADDRESS="localhost:7051"

# Log environment variables
echo "Environment variables set:" >> $LOG_FILE
echo "FABRIC_CFG_PATH: $FABRIC_CFG_PATH" >> $LOG_FILE
echo "CORE_PEER_TLS_ENABLED: $CORE_PEER_TLS_ENABLED" >> $LOG_FILE
echo "CORE_PEER_LOCALMSPID: $CORE_PEER_LOCALMSPID" >> $LOG_FILE
echo "CORE_PEER_TLS_ROOTCERT_FILE: $CORE_PEER_TLS_ROOTCERT_FILE" >> $LOG_FILE
echo "CORE_PEER_MSPCONFIGPATH: $CORE_PEER_MSPCONFIGPATH" >> $LOG_FILE
echo "CORE_PEER_ADDRESS: $CORE_PEER_ADDRESS" >> $LOG_FILE

# Define certificate paths
ORDERER_CA="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/ordererOrganizations/example.com/tlsca/tlsca.example.com-cert.pem"
ORG1_CA="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org1.example.com/tlsca/tlsca.org1.example.com-cert.pem"
ORG2_CA="/Users/cliffordntinkansah/go/src/github.com/kwakuNti/fabric-samples/test-network/organizations/peerOrganizations/org2.example.com/tlsca/tlsca.org2.example.com-cert.pem"

# Check if certificate files exist and are readable
echo "Checking certificate files:" >> $LOG_FILE
for cert_file in "$ORDERER_CA" "$ORG1_CA" "$ORG2_CA"; do
    if [ -f "$cert_file" ]; then
        echo "✅ File exists: $cert_file" >> $LOG_FILE
        if [ -r "$cert_file" ]; then
            echo "✅ File is readable: $cert_file" >> $LOG_FILE
        else
            echo "❌ File is NOT readable: $cert_file" >> $LOG_FILE
            chmod 644 "$cert_file" 2>> $LOG_FILE
            echo "Attempted to fix permissions" >> $LOG_FILE
        fi
    else
        echo "❌ File does NOT exist: $cert_file" >> $LOG_FILE
    fi
done

#######################
# Build JSON arguments:
#######################

# The script now supports two modes, based on the first parameter:
# If the first parameter is "CreateMatch", then shift the parameters and use those.
# Otherwise, default to CreateMedicalInfo.

FUNCTION=$1
shift

if [ "$FUNCTION" == "CreateMatch" ]; then
    # Expected parameters for CreateMatch:
    # MATCH_ID, DONOR_ID, RECIPIENT_ID, MATCH_SCORE, STATUS
    MATCH_ID=$1
    DONOR_ID=$2
    RECIPIENT_ID=$3
    MATCH_SCORE=$4
    STATUS=$5

    echo "Selected function: CreateMatch" >> $LOG_FILE
    echo "MATCH_ID: $MATCH_ID" >> $LOG_FILE
    echo "DONOR_ID: $DONOR_ID" >> $LOG_FILE
    echo "RECIPIENT_ID: $RECIPIENT_ID" >> $LOG_FILE
    echo "MATCH_SCORE: $MATCH_SCORE" >> $LOG_FILE
    echo "STATUS: $STATUS" >> $LOG_FILE

    JSON_ARGS=$(echo "{\"Args\":[\"CreateMatch\", \"$MATCH_ID\", \"$DONOR_ID\", \"$RECIPIENT_ID\", \"$MATCH_SCORE\", \"$STATUS\"]}")
else
    # Default function is CreateMedicalInfo.
    # Expected parameters: USER_ID, BLOOD_TYPE, INIT_AGE, BMI_TCR, DAYSWAIT_ALLOC, KIDNEY_CLUSTER, DGN_TCR, WGT_KG_TCR, HGT_CM_TCR, GFR, ON_DIALYSIS, FILE_REF
    USER_ID=$1
    BLOOD_TYPE=$2
    INIT_AGE=$3
    BMI_TCR=$4
    DAYSWAIT_ALLOC=$5
    KIDNEY_CLUSTER=$6
    DGN_TCR=$7
    WGT_KG_TCR=$8
    HGT_CM_TCR=$9
    GFR=${10}
    ON_DIALYSIS=${11}
    FILE_REF=${12}

    echo "Selected function: CreateMedicalInfo" >> $LOG_FILE
    echo "USER_ID: $USER_ID" >> $LOG_FILE
    echo "BLOOD_TYPE: $BLOOD_TYPE" >> $LOG_FILE
    echo "INIT_AGE: $INIT_AGE" >> $LOG_FILE
    echo "BMI_TCR: $BMI_TCR" >> $LOG_FILE
    echo "DAYSWAIT_ALLOC: $DAYSWAIT_ALLOC" >> $LOG_FILE
    echo "KIDNEY_CLUSTER: $KIDNEY_CLUSTER" >> $LOG_FILE
    echo "DGN_TCR: $DGN_TCR" >> $LOG_FILE
    echo "WGT_KG_TCR: $WGT_KG_TCR" >> $LOG_FILE
    echo "HGT_CM_TCR: $HGT_CM_TCR" >> $LOG_FILE
    echo "GFR: $GFR" >> $LOG_FILE
    echo "ON_DIALYSIS: $ON_DIALYSIS" >> $LOG_FILE
    echo "FILE_REF: $FILE_REF" >> $LOG_FILE

    JSON_ARGS=$(echo "{\"Args\":[\"CreateMedicalInfo\", \"$USER_ID\", \"$BLOOD_TYPE\", \"$INIT_AGE\", \"$BMI_TCR\", \"$DAYSWAIT_ALLOC\", \"$KIDNEY_CLUSTER\", \"$DGN_TCR\", \"$WGT_KG_TCR\", \"$HGT_CM_TCR\", \"$GFR\", \"$ON_DIALYSIS\", \"$FILE_REF\"]}")
fi

echo "JSON Arguments: $JSON_ARGS" >> $LOG_FILE

# Construct full command
INVOKE_CMD="$PEER_CMD chaincode invoke -o localhost:7050 --ordererTLSHostnameOverride orderer.example.com --tls --cafile \"$ORDERER_CA\" -C mychannel -n donor_medical_info --peerAddresses localhost:7051 --tlsRootCertFiles \"$ORG1_CA\" --peerAddresses localhost:9051 --tlsRootCertFiles \"$ORG2_CA\" -c '$JSON_ARGS'"

echo "Executing command: $INVOKE_CMD" >> $LOG_FILE

# Execute the command and capture output
OUTPUT=$(eval $INVOKE_CMD 2>&1)
EXIT_CODE=$?

# Log the output and exit code
echo "Command output: $OUTPUT" >> $LOG_FILE
echo "Exit code: $EXIT_CODE" >> $LOG_FILE

if [ $EXIT_CODE -ne 0 ]; then
    echo "Command failed. Attempting diagnostics..." >> $LOG_FILE
    if [ ! -f "$PEER_CMD" ]; then
        echo "❌ peer binary not found at $PEER_CMD" >> $LOG_FILE
    elif [ ! -x "$PEER_CMD" ]; then
        echo "❌ peer binary exists but is not executable" >> $LOG_FILE
    else
        echo "✅ peer binary exists and is executable" >> $LOG_FILE
    fi
    echo "Testing network connectivity:" >> $LOG_FILE
    nc -zv localhost 7050 >> $LOG_FILE 2>&1 || echo "❌ Cannot connect to orderer at localhost:7050" >> $LOG_FILE
    nc -zv localhost 7051 >> $LOG_FILE 2>&1 || echo "❌ Cannot connect to peer0.org1 at localhost:7051" >> $LOG_FILE
    nc -zv localhost 9051 >> $LOG_FILE 2>&1 || echo "❌ Cannot connect to peer0.org2 at localhost:9051" >> $LOG_FILE
    echo "Testing basic peer command:" >> $LOG_FILE
    $PEER_CMD channel list >> $LOG_FILE 2>&1
fi

echo "Log file created at: $LOG_FILE" >&2
echo "$OUTPUT"
exit $EXIT_CODE
