# Rebuild the installable zips in dist/ (run from the repo root).
# Uses tar (built into Windows 10+) because PowerShell 5's Compress-Archive
# writes backslash paths that Linux/PHP unzip tools can't read.
$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force dist | Out-Null
Remove-Item dist\*.zip -ErrorAction SilentlyContinue
tar -a -cf dist/meridian-theme.zip -C theme meridian
tar -a -cf dist/meridian-core.zip -C plugin meridian-core
Get-ChildItem dist | Select-Object Name, Length
