# HQMS starter (XAMPP edition) — syncs code, starts MySQL + Apache if needed.
# Run manually any time, or automatically at logon via Scheduled Task "HQMS-AutoStart".
# Rule: ALWAYS edit code in C:\Users\Parth\ml7code\hospital-queue (this script copies it to htdocs).
$SRC = "C:\Users\Parth\ml7code\hospital-queue"
$DST = "C:\xampp\htdocs\hospital-queue"
$LOG = "C:\Users\Parth\ml7code\hqms-autostart.log"

function Log($m) { Add-Content $LOG ("[{0}] {1}" -f (Get-Date -Format "yyyy-MM-dd HH:mm:ss"), $m) }
Log "=== starter check (XAMPP) ==="

# 1. Deploy latest code (one direction: workspace -> htdocs). Database lives in MySQL, never touched.
robocopy $SRC $DST /MIR /NFL /NDL /NJH /NJS /XF "hqms-autostart.log" | Out-Null
Log "code synced"

# 2. MySQL (XAMPP)
if (-not (Get-Process -Name "mysqld" -ErrorAction SilentlyContinue)) {
    Log "starting xampp mysqld..."
    Start-Process -FilePath "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini --standalone" -WindowStyle Hidden -WorkingDirectory "C:\xampp"
} else { Log "mysqld already running" }
$ready = $false
for ($i = 0; $i -lt 30; $i++) {
    Start-Sleep -Seconds 2
    & "C:\xampp\mysql\bin\mysqladmin.exe" -uroot ping 2>$null | Out-Null
    if ($LASTEXITCODE -eq 0) { $ready = $true; break }
}
Log ("mysql ready={0}" -f $ready)

# 3. Apache
$up = $false
try { $up = (Invoke-WebRequest -Uri "http://127.0.0.1/hospital-queue/index.php" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop).StatusCode -eq 200 } catch {}
if (-not $up) {
    Log "starting xampp apache..."
    Start-Process -FilePath "C:\xampp\apache\bin\httpd.exe" -WindowStyle Hidden -WorkingDirectory "C:\xampp\apache"
    Start-Sleep -Seconds 5
    try { $up = (Invoke-WebRequest -Uri "http://127.0.0.1/hospital-queue/index.php" -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop).StatusCode -eq 200 } catch {}
} else { Log "apache already running" }
Log ("site up={0} -> http://localhost/hospital-queue/index.php" -f $up)
Write-Output ("MySQL ready={0} | Site up={1} | http://localhost/hospital-queue/index.php" -f $ready, $up)
