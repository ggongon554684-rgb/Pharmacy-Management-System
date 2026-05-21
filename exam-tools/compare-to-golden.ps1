# Diff project files against exam-golden/. Shows exactly what the prof changed.
# Run from repo root:  .\exam-tools\compare-to-golden.ps1

$ErrorActionPreference = 'Stop'
$RepoRoot = Split-Path $PSScriptRoot -Parent
$Manifest = Join-Path $PSScriptRoot 'manifest.txt'
$GoldenRoot = Join-Path $RepoRoot 'exam-golden'

if (-not (Test-Path $GoldenRoot)) {
    Write-Error "No exam-golden folder. Run snapshot-golden.ps1 on your CLEAN copy first, or copy exam-golden from USB next to the broken project."
}

$lines = Get-Content $Manifest | Where-Object {
    $_ -and ($_ -notmatch '^\s*#')
}

$changed = @()
$missing = @()
$onlyGolden = @()

foreach ($rel in $lines) {
    $rel = $rel.Trim()
    $current = Join-Path $RepoRoot $rel
    $golden = Join-Path $GoldenRoot $rel

    if (-not (Test-Path $golden)) {
        $onlyGolden += $rel
        continue
    }
    if (-not (Test-Path $current)) {
        $missing += $rel
        continue
    }

    $a = Get-FileHash $current -Algorithm SHA256
    $b = Get-FileHash $golden -Algorithm SHA256
    if ($a.Hash -ne $b.Hash) {
        $changed += $rel
    }
}

Write-Host "`n=== CHANGED (fix these first) ===" -ForegroundColor Yellow
if ($changed.Count -eq 0) {
    Write-Host "(none in manifest — bug may be in .env, DB, or a file not listed)"
} else {
    $changed | ForEach-Object { Write-Host "  $_" }
}

if ($missing.Count -gt 0) {
    Write-Host "`n=== MISSING in project ===" -ForegroundColor Red
    $missing | ForEach-Object { Write-Host "  $_" }
}

Write-Host "`n--- Open side-by-side in VS Code (first changed file) ---"
if ($changed.Count -gt 0) {
    $first = Join-Path $RepoRoot $changed[0]
    $g = Join-Path $GoldenRoot $changed[0]
    Write-Host "code --diff `"$g`" `"$first`""
}

Write-Host "`n--- Full text diff (optional) ---"
foreach ($rel in $changed) {
    Write-Host "fc /n `"$(Join-Path $GoldenRoot $rel)`" `"$(Join-Path $RepoRoot $rel)`""
}
