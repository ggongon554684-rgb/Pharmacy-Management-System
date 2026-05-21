# Snapshot current (clean) project files into exam-golden/ for exam diffing.
# Run from repo root:  .\exam-tools\snapshot-golden.ps1

$ErrorActionPreference = 'Stop'
$RepoRoot = Split-Path $PSScriptRoot -Parent
$Manifest = Join-Path $PSScriptRoot 'manifest.txt'
$GoldenRoot = Join-Path $RepoRoot 'exam-golden'

if (-not (Test-Path $Manifest)) {
    Write-Error "Missing manifest: $Manifest"
}

$lines = Get-Content $Manifest | Where-Object {
    $_ -and ($_ -notmatch '^\s*#')
}

$copied = 0
foreach ($rel in $lines) {
    $rel = $rel.Trim()
    $src = Join-Path $RepoRoot $rel
    $dest = Join-Path $GoldenRoot $rel

    if (-not (Test-Path $src)) {
        Write-Warning "SKIP (missing): $rel"
        continue
    }

    $destDir = Split-Path $dest -Parent
    if (-not (Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
    }

    Copy-Item -Path $src -Destination $dest -Force
    $copied++
}

$stamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
@(
    "snapshot_at=$stamp"
    "file_count=$copied"
    "repo_root=$RepoRoot"
) | Set-Content (Join-Path $GoldenRoot 'SNAPSHOT.txt')

Write-Host "Golden snapshot done: $copied files -> $GoldenRoot" -ForegroundColor Green
Write-Host "Copy the whole 'exam-golden' folder to USB / phone / second laptop before exam."
