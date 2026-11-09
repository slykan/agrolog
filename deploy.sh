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

    # Datoteke koje git prati ne smiju biti ručno mijenjane na serveru (npr. deploy mimo gita):
    # tada se ne radi ništa dok se ne srede. Datoteke kojih nema u gitu (error_log, .well-known,
    # slike…) smiju postojati; ako bi ih merge prepisao, merge sam odbije i skripta stane.
    if ! git diff --quiet || ! git diff --cached --quiet; then
        echo "STOP: na serveru su ručno izmijenjene datoteke iz gita. Ništa nije promijenjeno."
        git status --short --untracked-files=no
        echo "Te izmjene prvo treba komitati u repo (lokalno, pa push) ili vratiti na serveru (git checkout -- <datoteka>)."
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
