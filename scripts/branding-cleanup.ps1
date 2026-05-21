param(
    [switch]$Apply,
    [string[]]$IncludeFiles = @()
)

$ErrorActionPreference = "Stop"

$root = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path

$excludeFragments = @(
    "\vendor\",
    "\node_modules\",
    "\storage\framework\cache\",
    "\.git\"
)

$includeExtensions = @(".php", ".blade.php", ".sql", ".txt", ".md")

$replacements = @(
    @{ Old = "https://codecanyon.net/item/active-ecommerce-flutter-app/31466365"; New = "https://cibato.com/products/customer-app" },
    @{ Old = "https://codecanyon.net/item/active-ecommerce-seller-app/38842276"; New = "https://cibato.com/products/seller-app" },
    @{ Old = "https://codecanyon.net/item/active-ecommerce-delivery-boy-flutter-app/32173746"; New = "https://cibato.com/products/delivery-app" },
    @{ Old = "https://codecanyon.net/user/activeitzone/portfolio"; New = "https://cibato.com/addons" },
    @{ Old = "https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code"; New = "https://licensing.cibato.com/help" },
    @{ Old = "https://activeitzone.com/docs/active-ecommerce-cms/"; New = "https://cibato.com/docs" },
    @{ Old = "https://demo.activeitzone.com/"; New = "https://demo.cibato.com/" },
    @{ Old = "https://activeitzone.com/"; New = "https://cibato.com/" },
    @{ Old = "https://activation.activeitzone.com/"; New = "https://licensing.cibato.com/" },
    @{ Old = "demo.activeitzone.com"; New = "demo.cibato.com" },
    @{ Old = "support.activeitzone.com"; New = "support.cibato.com" },
    @{ Old = "www.activeitzone.com"; New = "www.cibato.com" },
    @{ Old = "Codecanyon"; New = "Cibato" },
    @{ Old = "CodeCanyon Purchase Code"; New = "Cibato License Code" },
    @{ Old = "Codecanyon purchase code"; New = "Cibato license code" },
    @{ Old = "Download latest version from codecanyon."; New = "Download latest release package from Cibato panel." }
)

function ShouldSkipFile([string]$path) {
    foreach ($frag in $excludeFragments) {
        if ($path -like "*$frag*") { return $true }
    }
    return $false
}

$files = @()
if ($IncludeFiles.Count -gt 0) {
    foreach ($inc in $IncludeFiles) {
        $fullPath = if ([System.IO.Path]::IsPathRooted($inc)) { $inc } else { Join-Path $root $inc }
        if (Test-Path $fullPath -PathType Leaf) {
            $item = Get-Item $fullPath
            if (-not (ShouldSkipFile $item.FullName)) {
                $files += $item
            }
        }
    }
}
else {
    $files = Get-ChildItem -Path $root -Recurse -File | Where-Object {
        $path = $_.FullName
        if (ShouldSkipFile $path) { return $false }
        foreach ($ext in $includeExtensions) {
            if ($path.EndsWith($ext, [System.StringComparison]::OrdinalIgnoreCase)) { return $true }
        }
        return $false
    }
}

$changes = @()

foreach ($file in $files) {
    $content = Get-Content -Path $file.FullName -Raw
    if ($null -eq $content) { continue }
    $updated = $content
    $hitCount = 0

    foreach ($rep in $replacements) {
        if ($updated.Contains($rep.Old)) {
            $hitCount++
            $updated = $updated.Replace($rep.Old, $rep.New)
        }
    }

    if ($updated -ne $content) {
        $changes += [PSCustomObject]@{
            File = $file.FullName.Substring($root.Length + 1)
            RulesMatched = $hitCount
        }
        if ($Apply) {
            Set-Content -Path $file.FullName -Value $updated -NoNewline
        }
    }
}

Write-Host "Mode: $([string]::Join('', @($(if($Apply){"APPLY"}else{"DRY-RUN"}))))"
Write-Host "Changed files: $($changes.Count)"
foreach ($c in $changes) {
    Write-Host (" - {0} (rules: {1})" -f $c.File, $c.RulesMatched)
}
