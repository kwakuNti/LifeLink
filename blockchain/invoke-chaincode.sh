#!/bin/bash

# Log file
LOG_FILE="/tmp/fabric_invoke_$(date +%s).log"
echo "=== BLOCKCHAIN INVOKE SCRIPT LOG ===" > "$LOG_FILE"
echo "Started: $(date)" >> "$LOG_FILE"
echo "Running as user: $(whoami)" >> "$LOG_FILE"

# Use global peer binary
PEER_CMD=$(which peer)
echo "Using peer command at: $PEER_CMD" >> "$LOG_FILE"

# Set Fabric environment variables
export FABRIC_CFG_PATH="/opt/fabric-config"
export CORE_PEER_TLS_ENABLED=true
export CORE_PEER_LOCALMSPID="Org1MSP"
export CORE_PEER_TLS_ROOTCERT_FILE="/opt/fabric-tls/org1/peer0-org1-ca.crt"
export CORE_PEER_MSPCONFIGPATH="/opt/fabric-identities/org1/msp"
export CORE_PEER_ADDRESS="localhost:7051"

# Certificate paths
ORG1_CA="/opt/fabric-tls/org1/peer0-org1-ca.crt"
ORG2_CA="/opt/fabric-tls/org2/peer0-org2-ca.crt"
ORDERER_CA="/opt/fabric-tls/orderer/orderer-ca.crt"


# Determine function
FUNCTION=$1
shift

if [ "$FUNCTION" == "CreateMatch" ]; then
    MATCH_ID=$1
    DONOR_ID=$2
    RECIPIENT_ID=$3
    MATCH_SCORE=$4
    STATUS=$5

    echo "Running CreateMatch..." >> "$LOG_FILE"
    JSON_ARGS=$(echo "{\"Args\":[\"CreateMatch\", \"$MATCH_ID\", \"$DONOR_ID\", \"$RECIPIENT_ID\", \"$MATCH_SCORE\", \"$STATUS\"]}")
else
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

    echo "Running CreateMedicalInfo..." >> "$LOG_FILE"
    JSON_ARGS=$(echo "{\"Args\":[\"CreateMedicalInfo\", \"$USER_ID\", \"$BLOOD_TYPE\", \"$INIT_AGE\", \"$BMI_TCR\", \"$DAYSWAIT_ALLOC\", \"$KIDNEY_CLUSTER\", \"$DGN_TCR\", \"$WGT_KG_TCR\", \"$HGT_CM_TCR\", \"$GFR\", \"$ON_DIALYSIS\", \"$FILE_REF\"]}")
fi

echo "JSON_ARGS: $JSON_ARGS" >> "$LOG_FILE"

# Final peer invoke command
INVOKE_CMD="$PEER_CMD chaincode invoke -o localhost:7050 --ordererTLSHostnameOverride orderer.example.com --tls --cafile \"$ORDERER_CA\" -C mychannel -n donor_medical_info --peerAddresses localhost:7051 --tlsRootCertFiles \"$ORG1_CA\" --peerAddresses localhost:9051 --tlsRootCertFiles \"$ORG2_CA\" -c '$JSON_ARGS'"

echo "Executing: $INVOKE_CMD" >> "$LOG_FILE"
OUTPUT=$(eval $INVOKE_CMD 2>&1)
EXIT_CODE=$?

echo "Command output: $OUTPUT" >> "$LOG_FILE"
echo "$OUTPUT"
exit $EXIT_CODE
