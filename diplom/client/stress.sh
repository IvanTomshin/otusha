#!/bin/bash

COUNT=10000
NUM_CLIENTS=200
IP=127.0.0.1

for ((i=1; i<=NUM_CLIENTS; i++)); do
    ./tcp_client "$IP" 9000 "$COUNT" &
    sleep 0.01
done
