#!/bin/bash
# Deploy AgroLog: samo iz gita, samo fast-forward, staje na prvoj grešci.
# Cijela skripta je u funkciji: bash je pročita do kraja prije pokretanja, pa je merge smije
# izmijeniti dok radi (inače bash nastavi čitati izmijenjenu datoteku od krivog mjesta).
# Ako merge izmijeni ovu skriptu, ona se odmah pokrene ponovno (bez fetcha i mergea),
# pa novi koraci vrijede već za ovaj deploy, a ne tek za sljedeći.
set -euo pipefail

deploy() {
    cd ~/agrolog
    local php=/opt/cpanel/ea-php83/root/usr/bin/php

    if [ "${1:-}" != "--nakon-mergea" ]; then
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
        local prije
        prije=$(git hash-object deploy.sh)
        git fetch origin main
        git merge --ff-only origin/main
        if [ "$(git hash-object deploy.sh)" != "$prije" ]; then
            echo "deploy.sh je izmijenjen, pokrećem novu verziju…"
            exec bash deploy.sh --nakon-mergea
        fi
    fi

    # PHP paketi prema composer.lock (npr. dompdf za PDF); bez paketa za razvoj
    local composer
    composer=$(command -v composer || true)
    [ -z "$composer" ] && [ -f /opt/cpanel/composer/bin/composer ] && composer=/opt/cpanel/composer/bin/composer
    if [ -z "$composer" ]; then
        echo "STOP: composer nije pronađen. Kod je povučen, ali paketi i ostali koraci nisu napravljeni."
        exit 1
    fi
    # PHP 8.3 prvi u PATH-u: composer je phar ili skripta s "#!/usr/bin/env php", a projekt traži PHP ^8.3
    PATH="$(dirname "$php"):$PATH" "$composer" install --no-dev --no-interaction --prefer-dist --optimize-autoloader

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
