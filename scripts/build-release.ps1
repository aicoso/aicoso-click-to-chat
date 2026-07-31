[CmdletBinding()]
param(
	[string] $OutputPath,
	[switch] $Flat
)

$ErrorActionPreference = 'Stop'

$pluginRoot = Split-Path -Parent $PSScriptRoot
$pluginSlug = Split-Path -Leaf $pluginRoot
$versionLine = Select-String -LiteralPath (Join-Path $pluginRoot 'aicoso-click-to-chat.php') -Pattern '^\s*\*\s*Version:\s*(\S+)' | Select-Object -First 1

if ( -not $versionLine ) {
	throw 'Could not determine the plugin version.'
}

$version = $versionLine.Matches[0].Groups[1].Value

if ( -not $OutputPath ) {
	$archiveName = if ( $Flat ) { "$pluginSlug.zip" } else { "$pluginSlug-$version.zip" }
	$OutputPath = Join-Path $pluginRoot "build\$archiveName"
}

$outputFullPath = [System.IO.Path]::GetFullPath($OutputPath)
$buildRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("$pluginSlug-release-" + [guid]::NewGuid().ToString('N'))
$stagingPlugin = Join-Path $buildRoot $pluginSlug

$excludedDirectories = @(
	'.agents',
	'.codex',
	'.git',
	'.github',
	'build',
	'docs',
	'node_modules',
	'scripts',
	'specs',
	'stories',
	'tests',
	'vendor'
)

$excludedFiles = @(
	'.distignore',
	'.gitignore',
	'AGENTS.md',
	'composer.json',
	'composer.lock',
	'Gruntfile.js',
	'package.json',
	'package-lock.json',
	'phpcs.xml',
	'readme.md'
)

try {
	New-Item -ItemType Directory -Path $stagingPlugin -Force | Out-Null

	Get-ChildItem -LiteralPath $pluginRoot -Recurse -File | ForEach-Object {
		$relativePath = $_.FullName.Substring($pluginRoot.Length).TrimStart([System.IO.Path]::DirectorySeparatorChar, [System.IO.Path]::AltDirectorySeparatorChar)
		$segments = $relativePath -split '[\\/]'

		if ( $segments | Where-Object { $_.StartsWith('.') } ) {
			return
		}

		if ( $segments[0] -in $excludedDirectories ) {
			return
		}

		if ( $relativePath -in $excludedFiles -or $_.Name -eq '.DS_Store' -or $_.Name -eq 'README.md' -or $_.Extension -eq '.csv' ) {
			return
		}

		$destination = Join-Path $stagingPlugin $relativePath
		$destinationDirectory = Split-Path -Parent $destination
		New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null
		Copy-Item -LiteralPath $_.FullName -Destination $destination
	}

	$outputDirectory = Split-Path -Parent $outputFullPath
	New-Item -ItemType Directory -Path $outputDirectory -Force | Out-Null

	if ( Test-Path -LiteralPath $outputFullPath ) {
		Remove-Item -LiteralPath $outputFullPath
	}

	Add-Type -AssemblyName System.IO.Compression
	Add-Type -AssemblyName System.IO.Compression.FileSystem

	$archive = [System.IO.Compression.ZipFile]::Open(
		$outputFullPath,
		[System.IO.Compression.ZipArchiveMode]::Create
	)

	try {
		Get-ChildItem -LiteralPath $stagingPlugin -Recurse -File | ForEach-Object {
			$relativePath = $_.FullName.Substring($stagingPlugin.Length).TrimStart([System.IO.Path]::DirectorySeparatorChar, [System.IO.Path]::AltDirectorySeparatorChar)
			$entryPath = $relativePath.Replace([char] 92, [char] 47)

			if ( -not $Flat ) {
				$entryPath = "$pluginSlug/$entryPath"
			}

			[System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
				$archive,
				$_.FullName,
				$entryPath,
				[System.IO.Compression.CompressionLevel]::Optimal
			) | Out-Null
		}
	} finally {
		$archive.Dispose()
	}

	Write-Output $outputFullPath
} finally {
	if ( Test-Path -LiteralPath $buildRoot ) {
		Remove-Item -LiteralPath $buildRoot -Recurse -Force
	}
}
