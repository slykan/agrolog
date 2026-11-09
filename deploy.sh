#!/bin/bash
# Deploy AgroLog: samo iz gita, samo fast-forward, staje na prvoj grešci.
# Cijela skripta je u funkciji: bash je pročita do kraja prije pokretanja, pa je merge smije
# izmijeniti dok radi (inače bash nastavi čitati izmijenjenu datoteku od krivog mjesta).
set -euo pipefail

deploy() {
    cd ~/agrolog
    local php=/opt/cpanel/ea-php83/root/usr/bin/php

    # public/build se uvijek gradi iznova (npm run build ispod), pa se prvo vraća na stanje iz gita
    git checkout -- public/build
    git clean -fdq -- public/build

    # Server mora biti čist. Ručno kopirane ili izmijenjene datoteke (npr. deploy mimo gita)
    # bi merge prepisao ili zbog njih pao, pa se tada ne radi ništa dok se ne srede.
    if ! git diff --quiet || ! git diff --cached --quiet || [ -n "$(git ls-files --others --exclude-standard)" ]; then
        echo "STOP: na serveru ima izmjena mimo gita. Ništa nije promijenjeno."
        git status --short
        echo "Te izmjene prvo treba komitati u repo (lokalno, pa push) ili maknuti sa servera."
        exit 1
    fi

    # Prvo fetch, pa samo fast-forward: nikad reset na zastarjeli origin/main.
    git fetch origin main
    git merge --ff-only origin/main

    npm run build
    "$php" artisan migrate --force
    # registar sredstava iz FIS-a (osvježava se i tjedno preko schedulera); pad FIS-a ne zaustavlja deploy
    "$php" artisan szb:registar || echo "FIS registar nije osvježen"
    "$php" artisan cache:clear
    "$php" artisan view:clear
    "$php" artisan config:cache
    "$php" artisan route:cache
    "$php" artisan view:cache
    echo "Deploy gotov! $(git log --oneline -1)"
}

deploy "$@"
exit
