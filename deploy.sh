#!/bin/bash
# ============================================================
# deploy.sh — Deploy aman ke Hostinger (adicell-pos)
#
# Alur: backup tar (file lama di server) → scp → md5 verify
#       → php -l → view:cache → HTTP check
#       → AUTO-ROLLBACK kalau ada langkah yang gagal
#
# Pakai:
#   ./deploy.sh              → deploy semua file .php/.js/.css yang berubah (git status)
#   ./deploy.sh file1 file2  → deploy file tertentu
#   ./deploy.sh --dry-run    → lihat rencana tanpa eksekusi
#
# Catatan: .env* TIDAK pernah ikut ter-deploy (ada filter eksplisit).
# ============================================================
set -euo pipefail

HOST="adicell-pos"
REMOTE="/home/u564540896/domains/red-anteater-980940.hostingersite.com/public_html/pos"
URL="https://red-anteater-980940.hostingersite.com/pos/login"
STAMP=$(date +%Y%m%d_%H%M%S)
BACKUP="/home/u564540896/backups/files/pos_deploy_${STAMP}.tgz"
DRY=0

if [ "${1:-}" = "--dry-run" ]; then DRY=1; shift; fi

cd "$(dirname "$0")"

rollback() {
  echo "!! AUTO-ROLLBACK: restore dari $BACKUP"
  if [ "$DRY" = 1 ]; then echo "   (dry-run) skip"; return; fi
  ssh "$HOST" "tar xzf $BACKUP -C $REMOTE 2>/dev/null; cd $REMOTE && /usr/bin/php artisan view:cache >/dev/null 2>&1" || true
  echo "!! Server kembali ke kondisi sebelum deploy."
}

# --- [1/6] Kumpulkan file ---
if [ $# -gt 0 ]; then
  FILES=("$@")
else
  FILES=($(git status --porcelain 2>/dev/null | awk '{print $2}' | grep -E '\.(php|js|css)$' | grep -v '^\.env' || true))
fi

if [ ${#FILES[@]} -eq 0 ]; then
  echo "Tidak ada file berubah — selesai."
  exit 0
fi

echo "== [1/6] File yang akan di-deploy (${#FILES[@]}):"
printf '   %s\n' "${FILES[@]}"

# --- [2/6] Backup kondisi server saat ini ---
echo "== [2/6] Backup file lama di server → $BACKUP"
if [ "$DRY" = 1 ]; then
  echo "   (dry-run)"
else
  ssh "$HOST" "mkdir -p /home/u564540896/backups/files && cd $REMOTE && tar czf $BACKUP ${FILES[*]} 2>/dev/null || true"
fi

# --- [3/6] Upload ---
echo "== [3/6] Upload via scp"
for f in "${FILES[@]}"; do
  if [ "$DRY" = 1 ]; then echo "   (dry-run) scp $f"; continue; fi
  if ! scp -q "$f" "$HOST:$REMOTE/$f"; then echo "!! Gagal upload $f"; rollback; exit 1; fi
done

# --- [4/6] Verifikasi md5 lokal = server ---
echo "== [4/6] Verifikasi md5"
for f in "${FILES[@]}"; do
  if [ "$DRY" = 1 ]; then continue; fi
  L=$(md5sum "$f" | awk '{print $1}')
  R=$(ssh "$HOST" "md5sum $REMOTE/$f" | awk '{print $1}')
  if [ "$L" != "$R" ]; then
    echo "!! MISMATCH $f → rollback"; rollback; exit 1
  fi
  echo "   OK $f"
done

# --- [5/6] Lint + cache di server ---
echo "== [5/6] php -l + view:cache"
if [ "$DRY" = 1 ]; then
  echo "   (dry-run)"
else
  if ! ssh "$HOST" "cd $REMOTE && for f in ${FILES[*]}; do /usr/bin/php -l \"\$f\" >/dev/null 2>&1 || { echo LINT-FAIL \$f; exit 1; }; done && /usr/bin/php artisan view:cache >/dev/null 2>&1"; then
    echo "!! Lint/cache gagal → rollback"; rollback; exit 1
  fi
  echo "   Lint OK + view cache rebuilt"
fi

# --- [6/6] HTTP check ---
echo "== [6/6] HTTP check $URL"
if [ "$DRY" = 1 ]; then echo "   (dry-run) selesai"; exit 0; fi
CODE=$(curl -s -o /dev/null -w "%{http_code}" "$URL" || true)
if [ "$CODE" != "200" ]; then
  echo "!! HTTP $CODE → rollback"; rollback; exit 1
fi
echo "   HTTP $CODE OK"

echo ""
echo "== DEPLOY SELESAI ✅ (backup: $BACKUP)"
echo "   Rollback manual kalau perlu: ssh $HOST \"tar xzf $BACKUP -C $REMOTE\""
