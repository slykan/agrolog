$winscp = "C:\Program Files (x86)\WinSCP\WinSCP.com"

Write-Host "Deploying agrolog na vps.on-click.hr..." -ForegroundColor Cyan

& $winscp /command `
    "open agrolog@vps.on-click.hr" `
    "call bash ~/agrolog/deploy.sh" `
    "exit"

if ($LASTEXITCODE -eq 0) {
    Write-Host "Deploy gotov!" -ForegroundColor Green
} else {
    Write-Host "Deploy FAILED (exit code $LASTEXITCODE)" -ForegroundColor Red
}
