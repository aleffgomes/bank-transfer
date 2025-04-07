#!/bin/bash

set -e

host="$1"
user="$2"
pass="$3"
cmd="$4"

until curl -s -u "$user:$pass" "http://$host:15672/api/aliveness-test/%2F" | grep -q "alive"; do
  >&2 echo "RabbitMQ não está pronto - aguardando..."
  sleep 2
done

>&2 echo "RabbitMQ está pronto!"

exec $cmd
