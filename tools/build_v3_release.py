#!/usr/bin/env python3
"""Build and verify the V3 package from this working tree; no Git staging needed."""
from __future__ import annotations

import hashlib
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DEST = ROOT / 'Almarah-Foundation-V3.zip'
PREFIX = 'Almarah-Foundation-V3/'
SKIP_DIRS = {'.git', 'node_modules', '__pycache__', '.cache', '.venv', 'build', 'dist', 'coverage', 'out', 'target'}
PRIVATE_ROOT_NAMES = {'config.php', '.env', '.env.local', '.env.production', 'Almarah-Foundation-V3.zip'}
PRIVATE_SUFFIXES = {'.log', '.zip', '.bak', '.tmp', '.swp', '.pem', '.key', '.p12', '.pfx', '.sqlite', '.db', '.gz'}
RUNTIME_PLACEHOLDERS = {'.gitkeep', '.htaccess'}
REQUIRED = {
    'VERSION', '.env.example', 'config.dist.php', '.htaccess', 'README_V3.md',
    'V3-RELEASE-NOTES.md', 'V3-FINAL-QA-REPORT.md', 'index.php', 'router.php',
    'app/Core/App.php', 'app/Services/DonationCurrencies.php', 'app/Services/GeoCurrencyService.php',
    'app/Services/GrantService.php', 'assets/js/donate.js', 'assets/css/style.css',
    'routes/admin.php', 'routes/web.php', 'database/migrations/0017_v3_reconciliation_links.sql',
    'vendor/autoload.php', 'tests/v3_final_browser_part10.cjs',
}


def allowed(path: Path) -> bool:
    parts = path.relative_to(ROOT).parts
    if any(part in SKIP_DIRS for part in parts):
        return False
    if parts[0] in {'storage', 'uploads'} and path.name not in RUNTIME_PLACEHOLDERS:
        return False
    if path.name in PRIVATE_ROOT_NAMES or path.suffix.lower() in PRIVATE_SUFFIXES:
        return False
    if path.name.startswith('.env') and path.name != '.env.example':
        return False
    if path.name.startswith('.admin-') or path.name.endswith('~'):
        return False
    return True


files = sorted((p for p in ROOT.rglob('*') if p.is_file() and allowed(p)), key=lambda p: p.relative_to(ROOT).as_posix())
paths = {p.relative_to(ROOT).as_posix() for p in files}
missing = REQUIRED - paths
if missing:
    raise SystemExit(f'Cannot build incomplete release: {sorted(missing)}')
if len(files) < 200:
    raise SystemExit('Refusing suspiciously small release archive')

# Restrict release template to placeholders, not populated credentials.
text = (ROOT / '.env.example').read_text()
for key, val in re.findall(r'^([A-Z][A-Z0-9_]*)=(.*)$', text, re.M):
    if re.search(r'PASSWORD|SECRET|TOKEN|APP_KEY|PASSPHRASE|USERNAME|MERCHANT_ID|CUSTOMER', key) and val.strip():
        raise SystemExit(f'Sensitive template variable {key} must be blank')
# config.dist.php must contain no populated credential settings.
config = (ROOT / 'config.dist.php').read_text()
for name in ('db_pass', 'fundraise_db_pass', 'backup_passphrase', 'setup_lock'):
    if not re.search(r"['\"]" + name + r"['\"]\s*=>\s*['\"]['\"]", config):
        raise SystemExit(f'Unsafe config.dist.php: {name} is not blank')

# A manifest hashes the exact bytes packaged; it is itself included in the ZIP.
manifest = ROOT / 'V3-RELEASE-MANIFEST.sha256'
files = [p for p in files if p != manifest]
manifest.write_text(''.join(f'{hashlib.sha256(p.read_bytes()).hexdigest()}  {p.relative_to(ROOT).as_posix()}\n' for p in files))
files.append(manifest)
files.sort(key=lambda p: p.relative_to(ROOT).as_posix())
with zipfile.ZipFile(DEST, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=7) as archive:
    for path in files:
        archive.write(path, PREFIX + path.relative_to(ROOT).as_posix())

with zipfile.ZipFile(DEST) as archive:
    names = archive.namelist()
    if archive.testzip() is not None:
        raise SystemExit('ZIP CRC test failed')
    if len(names) != len(set(names)) or {PREFIX + p for p in REQUIRED} - set(names):
        raise SystemExit('Missing or duplicate ZIP entry')
    if any(name.startswith(PREFIX + bad) for bad in ('.git/', 'node_modules/', 'storage/app/app-key', 'config.php', '.env.local')):
        raise SystemExit('Forbidden archive entry')
    for entry in archive.read(PREFIX + 'V3-RELEASE-MANIFEST.sha256').decode().splitlines():
        digest, rel = entry.split('  ', 1)
        if hashlib.sha256(archive.read(PREFIX + rel)).hexdigest() != digest:
            raise SystemExit(f'Manifest digest mismatch: {rel}')
    if archive.read(PREFIX + 'VERSION') != b'V3 \xe2\x80\x94 Almarah Foundation V3\n':
        raise SystemExit('Version marker mismatch')
    if b'NOT production-certified' not in archive.read(PREFIX + 'V3-RELEASE-NOTES.md'):
        raise SystemExit('Release blockers missing from notes')

print(f'Verified {DEST.name}: {len(names)} real files, {DEST.stat().st_size:,} bytes, ZIP CRC and manifest hashes pass')
print('SHA256:', hashlib.sha256(DEST.read_bytes()).hexdigest())
