#!/usr/bin/env bash

mkdir -p ~/.ssh
ssh-keyscan -H www.skauting.cz >> ~/.ssh/known_hosts
echo "$SSH_PRIVATE_KEY" > ~/.ssh/id_ed25519
chown www-data:www-data ~/.ssh

eval "$(ssh-agent)"
ssh-add && phing deploy
