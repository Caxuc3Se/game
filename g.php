<?php
// ============================================================
//  game.php — ЖД Игра
//  Цвет компании · класс=маршруты · узлы в поле · врезка станции
//  субаренда путей и станций · подключение к чужой сети
// ============================================================
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ---------- Базовые ----------
const DB_FILE = __DIR__ . '/game.db';
const OSM_DB_FILE = __DIR__ . '/map.db';
const DAY_DURATION_SEC = 60;
const START_MONEY = 10000;
const COST_STATION = 250;
const COST_EDGE_PER_M = 1;
const COST_UPGRADE_DOUBLE_PER_M = 0.80;
const COST_UPGRADE_ELECTRIFY_PER_M = 0.50;
const MAX_CLASS = 10;
const COOKIE_SECRET = 'change-me-please-32-bytes-min-xxxxxxxx';
const MAX_EDGE_POINTS = 200;
const STATION_PRICE_GROWTH = 1.02;
const EDGE_PRICE_GROWTH = 1.01;
const UPKEEP_STATION_BASE = 2;
const UPKEEP_STATION_PER_CLASS = 2;
const UPKEEP_EDGE_INCOME_SHARE = 0.10;
const INCOME_PER_TRACK = 15;
const FORECAST_DAYS_ON_CONFISCATE = 3;
const AUCTION_DURATION_SEC = 3 * 3600;
const AUCTION_PRICE_OWNER_RATIO = 0.40;
const AUCTION_PRICE_OUTSIDER_RATIO = 0.60;
const STATION_RADIUS_BASE = 8.0;
const STATION_RADIUS_PER_CLASS = 1.5;
const EVENTS_KEEP_DAYS = 7;
const LONG_EDGE_M = 5000;
const BUILD_TIME_STATION = 1;
const BUILD_TIME_EDGE_PER_KM = 0.5;
const BUILD_TIME_UPGRADE_DOUBLE = 1;
const BUILD_TIME_UPGRADE_ELECTRIC = 1;
const BUILD_TIME_CLASS_UPGRADE = 1;
const REFUND_RATIO = 0.50;
const HEALTH_DECAY_PER_REAL_DAY = 0.1;
const REAL_DAY_SEC = 86400;
const REPAIR_COST_RATIO = 0.10;
const REPAIR_FREE_RADIUS_M = 1000;
const OSM_DISCOUNT = 0.10;
const OSM_STATION_RADIUS = 200;
const OSM_EDGE_DISCOUNT = 0.30;
const OSM_EDGE_TOLERANCE_M = 50;
const COST_CULVERT = 100;
const COST_DAM = 300;
const COST_DEMOLISH_BUILDING = 200;
const BASE_LAT = 55.75;
const BASE_LNG = 37.62;

// ---------- КЛАСС СТАНЦИИ (п.2) ----------
// Класс влияет ТОЛЬКО на число маршрутов через станцию.
const ROUTE_SLOTS_PER_CLASS = 1;      // маршрутов на станцию = класс × это
const CLASS_LIMITS_TRACKS = false;    // больше НЕ ограничивает число путей
const CLASS_LIMITS_TRAIN_SLOTS = false; // больше НЕ ограничивает нитки
const CLASS_INCOME_STEP = 0.0;        // больше НЕ множит доход

// ---------- УЗЛЫ / ВЕТКИ В ПОЛЕ (п.3, п.4) ----------
const COST_NODE = 50;                 // цена концевого узла («тупик»/стык)
const UPKEEP_NODE = 0.5;
const NODE_SNAP_M = 30.0;             // клик рядом с узлом/станцией — привязка
const MIN_EDGE_LEN_M = 20.0;
const MIN_SPLIT_DIST_M = 60.0;        // минимальный отступ врезки от концов
const NODE_NAME_DEFAULT = 'Узел';

// ---------- ЦВЕТА КОМПАНИЙ (п.1) ----------
const COMPANY_COLORS = ['#35d07f','#ff5a5a','#3d9bff','#ffb02e','#a97bff','#24d3c4','#ff5da2','#b6e83a','#ff8a3d','#7fa6d8','#e0d84a','#f06292'];

// ---------- СУБАРЕНДА (п.5, п.6) ----------
const TRACTAGE_REVENUE_SHARE = 0.30;  // 30%
const TRACTAGE_SHARE_BASIS = 'income';// 'income' | 'net'
const TRANSIT_UPKEEP_RATIO = 0.75;    // станция проездом (без остановки)

// ---------- МОСТЫ ----------
const COST_BRIDGE = 500;
const COST_OVERPASS_BASE = 400;
const BRIDGE_DEFAULT_TYPE = 'concrete_beam';
const BRIDGE_RIVER_MIN_LENGTH_M = 500;
const BRIDGE_WATER_HALF_WIDTH_M = 60.0;
const BRIDGE_MIN_SPAN_M = 40.0;
const BRIDGE_MAX_SPAN_M = 400.0;
const BRIDGE_SAMPLE_STEP_M = 5.0;
const BRIDGE_CLUSTER_RADIUS_M = 80.0;
const BRIDGE_MATCH_RADIUS_M = 60.0;
const OVERPASS_SPAN_M = 60.0;
const RAIL_CROSS_MIN_ANGLE_DEG = 30.0;
const RAIL_CROSS_STATION_SKIP_M = 80.0;
const RAIL_CROSS_SHARED_SKIP_M = 300.0;

// ---------- СКОРОСТИ ----------
const EDGE_SPEED_BASE = 60;
const EDGE_SPEED_ELECTRIC = 120;
const ROUTE_SPEED_BASE = 60;

// ---------- МАРШРУТЫ ----------
const ROUTE_INCOME_BASE = 250;
const ROUTE_DOUBLE_BONUS = 0.25;
const ROUTE_ELECTRIC_BONUS = 0.20;
const ROUTE_UPKEEP_PER_KM = 0.5;
const MAX_ROUTE_STOPS = 20;
const TRAIN_ROUTE_KM_DIVISOR = 3.0;

// ---------- ДЕПО ----------
const DEPOT_COST = 1500;
const DEPOT_PRICE_GROWTH = 1.02;
const DEPOT_MAX_STATION_DIST_M = 500;
const DEPOT_CAPACITY_BASE = 3;
const DEPOT_CAPACITY_PER_LEVEL = 2;
const DEPOT_MAX_LEVEL = 5;
const DEPOT_UPGRADE_COST = 800;
const DEPOT_UPKEEP_BASE = 4;
const DEPOT_UPKEEP_PER_LEVEL = 2;
const BUILD_TIME_DEPOT = 2;
const BUILD_TIME_DEPOT_UPGRADE = 1;

// ---------- ПОЕЗДА ----------
const TRAIN_COST = 800;
const TRAIN_UPKEEP_ACTIVE = 3.0;
const TRAIN_UPKEEP_IDLE = 1.5;
const BUILD_TIME_TRAIN = 1;
const TRAIN_REBASE_COST = 200;
const BUILD_TIME_TRAIN_REBASE = 1;

// ---------- ЗАЩИТА ----------
const CONSERVATION_RATIO = 0.50;
const GRACE_DAYS = 20;
const GRACE_UPKEEP_RATIO = 0.50;
const RESTART_GRANT = 2000;
const RESTART_GRANT_COOLDOWN_SEC = 7 * 86400;

// ============================================================
//  СЛУЖЕБНОЕ
// ============================================================
function haversineDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float {
$R = 6371000; $dLat = deg2rad($lat2 - $lat1); $dLng = deg2rad($lng2 - $lng1);
$a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2)**2;
return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}
function stationUpgradeCost(int $c): int { return (int)ceil($c * 1.25 * 50); }
function stationRadiusForClass(int $c): float { return STATION_RADIUS_BASE + (max(1, $c) - 1) * STATION_RADIUS_PER_CLASS; }
function stationClassMultiplier(int $class): float { return 1.0 + CLASS_INCOME_STEP * (max(1, $class) - 1); }
function stationRouteLimit(array $s): int { return max(1, (int)($s['class'] ?? 1)) * ROUTE_SLOTS_PER_CLASS; }
function depotCapacityForLevel(int $l): int { return DEPOT_CAPACITY_BASE + (max(1, $l) - 1) * DEPOT_CAPACITY_PER_LEVEL; }

class ApiError extends RuntimeException {
public int $httpCode;
public function __construct(string $m, int $c = 400) { parent::__construct($m); $this->httpCode = $c; }
}
function fail(string $m, int $c = 400): void { throw new ApiError($m, $c); }
set_exception_handler(function (Throwable $e) {
if ($e instanceof ApiError) sendJson(['error' => $e->getMessage()], $e->httpCode);
sendJson(['error' => 'server error: ' . $e->getMessage()], 500);
});
function sendJson($d, int $c = 200): void {
http_response_code($c); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
echo json_encode($d, JSON_UNESCAPED_UNICODE); exit;
}
function readJson(): array { $r = file_get_contents('php://input'); $j = json_decode($r === false ? '' : $r, true); return is_array($j) ? $j : []; }
function idList($v): array { if (!is_array($v)) return []; $o = []; foreach ($v as $x) if (is_string($x) || is_int($x)) $o[] = (string)$x; return array_values(array_unique($o)); }
function newId(string $p): string { return $p . (int)round(microtime(true) * 1000) . '_' . bin2hex(random_bytes(3)); }
function isLocalRequest(): bool { $ip = $_SERVER['REMOTE_ADDR'] ?? ''; return $ip === '127.0.0.1' || $ip === '::1'; }
function cacheReset(): void { $GLOBALS['__eco'] = null; $GLOBALS['__pol'] = null; $GLOBALS['__path'] = null; $GLOBALS['__col'] = null; }

// ============================================================
//  БАЗА
// ============================================================
function ensureColumn(PDO $pdo, string $t, string $c, string $def): void {
try {
foreach ($pdo->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC) as $x) if (($x['name'] ?? '') === $c) return;
$pdo->exec("ALTER TABLE $t ADD COLUMN $c $def");
} catch (Throwable $e) {}
}
function db(): PDO {
static $pdo = null;
if ($pdo !== null) return $pdo;
$pdo = new PDO('sqlite:' . DB_FILE);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA journal_mode=WAL");
$pdo->exec("PRAGMA busy_timeout=5000");
$pdo->exec("CREATE TABLE IF NOT EXISTS players (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, money INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL)");
$pdo->exec("CREATE TABLE IF NOT EXISTS world (id INTEGER PRIMARY KEY CHECK (id = 1), data TEXT NOT NULL, updated_at TEXT NOT NULL, version INTEGER NOT NULL DEFAULT 0)");
$pdo->exec("CREATE TABLE IF NOT EXISTS meta (key TEXT PRIMARY KEY, value TEXT)");
$pdo->exec("CREATE TABLE IF NOT EXISTS transactions (id INTEGER PRIMARY KEY AUTOINCREMENT, from_player INTEGER, to_player INTEGER, amount INTEGER NOT NULL, kind TEXT NOT NULL, meta TEXT, created_at TEXT NOT NULL)");
$pdo->exec("CREATE TABLE IF NOT EXISTS auctions (id INTEGER PRIMARY KEY AUTOINCREMENT, seller_id INTEGER NOT NULL, debt INTEGER NOT NULL, price INTEGER NOT NULL, price_owner INTEGER NOT NULL, replacement_cost INTEGER NOT NULL DEFAULT 0, stations TEXT NOT NULL, edges TEXT NOT NULL, created_at TEXT NOT NULL, expires_at TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'active', type TEXT NOT NULL DEFAULT 'bankrupt')");
$pdo->exec("CREATE TABLE IF NOT EXISTS events (id INTEGER PRIMARY KEY AUTOINCREMENT, kind TEXT NOT NULL, player_id INTEGER, text TEXT NOT NULL, meta TEXT, created_at TEXT NOT NULL)");
$pdo->exec("CREATE TABLE IF NOT EXISTS daily_stats (id INTEGER PRIMARY KEY AUTOINCREMENT, player_id INTEGER NOT NULL, day INTEGER NOT NULL, income INTEGER NOT NULL, upkeep INTEGER NOT NULL, net INTEGER NOT NULL)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_daily_stats_player ON daily_stats(player_id, day)");
$pdo->exec("CREATE TABLE IF NOT EXISTS company_snapshots (id INTEGER PRIMARY KEY AUTOINCREMENT, player_id INTEGER NOT NULL, day INTEGER NOT NULL, company_value INTEGER NOT NULL, stations INTEGER NOT NULL, edge_km REAL NOT NULL)");
$pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_company_snap_unique ON company_snapshots(player_id, day)");
$pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, player_id INTEGER NOT NULL, text TEXT NOT NULL, created_at TEXT NOT NULL)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_chat_created ON chat_messages(created_at)");
$pdo->exec("CREATE TABLE IF NOT EXISTS company_policies (player_id INTEGER PRIMARY KEY, allow_trackage INTEGER NOT NULL DEFAULT 0, allow_stops INTEGER NOT NULL DEFAULT 0, allow_connect INTEGER NOT NULL DEFAULT 0, updated_at TEXT NOT NULL)");
ensureColumn($pdo, 'players', 'color', "TEXT NOT NULL DEFAULT ''");
ensureColumn($pdo, 'players', 'starter_claimed', "INTEGER NOT NULL DEFAULT 0");
ensureColumn($pdo, 'players', 'grace_until_day', "INTEGER NOT NULL DEFAULT 0");
ensureColumn($pdo, 'players', 'last_grant_at', "INTEGER NOT NULL DEFAULT 0");
ensureColumn($pdo, 'auctions', 'type', "TEXT NOT NULL DEFAULT 'bankrupt'");
ensureColumn($pdo, 'auctions', 'depots', "TEXT NOT NULL DEFAULT '[]'");
ensureColumn($pdo, 'auctions', 'trains', "TEXT NOT NULL DEFAULT '[]'");
ensureColumn($pdo, 'auctions', 'nodes', "TEXT NOT NULL DEFAULT '[]'");
ensureColumn($pdo, 'company_snapshots', 'depots', "INTEGER NOT NULL DEFAULT 0");
ensureColumn($pdo, 'company_snapshots', 'trains', "INTEGER NOT NULL DEFAULT 0");
ensureColumn($pdo, 'company_snapshots', 'routes', "INTEGER NOT NULL DEFAULT 0");
ensureColumn($pdo, 'company_snapshots', 'nodes', "INTEGER NOT NULL DEFAULT 0");
if ((int)$pdo->query("SELECT COUNT(*) FROM world")->fetchColumn() === 0) {
$empty = ['stations'=>[], 'nodes'=>[], 'edges'=>[], 'routes'=>[], 'depots'=>[], 'trains'=>[], 'physical_trains'=>[]];
$pdo->prepare("INSERT INTO world (id, data, updated_at, version) VALUES (1, :d, :u, 0)")->execute([':d'=>json_encode($empty), ':u'=>date('c')]);
$pdo->exec("INSERT OR REPLACE INTO meta (key, value) VALUES ('last_tick', " . time() . ")");
$pdo->exec("INSERT OR REPLACE INTO meta (key, value) VALUES ('day_count', 1)");
$pdo->exec("INSERT OR REPLACE INTO meta (key, value) VALUES ('next_due', 0)");
$pdo->exec("INSERT OR REPLACE INTO meta (key, value) VALUES ('health_tick', " . time() . ")");
}
// цвета существующим игрокам
try {
$rows = $pdo->query("SELECT id, color FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$used = [];
foreach ($rows as $r) if (!empty($r['color'])) $used[strtolower($r['color'])] = true;
$upd = $pdo->prepare("UPDATE players SET color = :c WHERE id = :id");
foreach ($rows as $r) {
if (!empty($r['color'])) continue;
$c = null;
foreach (COMPANY_COLORS as $cand) if (!isset($used[strtolower($cand)])) { $c = $cand; break; }
if ($c === null) $c = COMPANY_COLORS[((int)$r['id'] - 1) % count(COMPANY_COLORS)];
$used[strtolower($c)] = true;
$upd->execute([':c'=>$c, ':id'=>(int)$r['id']]);
}
} catch (Throwable $e) {}
return $pdo;
}
function metaGet(string $k): ?string { $s = db()->prepare("SELECT value FROM meta WHERE key = :k"); $s->execute([':k'=>$k]); $v = $s->fetchColumn(); return $v === false ? null : (string)$v; }
function metaSet(string $k, string $v): void { db()->prepare("INSERT OR REPLACE INTO meta (key, value) VALUES (:k, :v)")->execute([':k'=>$k, ':v'=>$v]); }
function runTx(callable $fn) {
$p = db(); $p->exec('BEGIN IMMEDIATE');
try { $r = $fn(); $p->exec('COMMIT'); return $r; } catch (Throwable $e) { try { $p->exec('ROLLBACK'); } catch (Throwable $_) {} throw $e; }
}

// ============================================================
//  МИР
// ============================================================
function normAccess($v): string { $v = (string)$v; return in_array($v, ['open','closed'], true) ? $v : 'inherit'; }
function worldGet(): array {
$row = db()->query("SELECT data, version FROM world WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$data = json_decode($row['data'], true);
if (!is_array($data)) $data = [];
foreach (['stations','nodes','edges','routes','depots','trains','physical_trains'] as $k) if (!isset($data[$k]) || !is_array($data[$k])) $data[$k] = [];

foreach ($data['stations'] as &$s) {
foreach (['class'=>1,'capacity'=>4,'built_day'=>1,'is_building'=>false,'build_end'=>0,'build_type'=>null,'upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,'frozen'=>false,'health'=>100,'buildings'=>0] as $k=>$v) if (!isset($s[$k])) $s[$k] = $v;
if (!isset($s['labelOffset'])) $s['labelOffset'] = ['x'=>10,'y'=>4];
$s['access'] = normAccess($s['access'] ?? 'inherit');
$s['health'] = max(0, min(100, (int)$s['health']));
$exp = stationRadiusForClass((int)$s['class']);
if (!isset($s['radius']) || abs((float)$s['radius'] - $exp) > 0.01) $s['radius'] = $exp;
} unset($s);

foreach ($data['nodes'] as &$n) {
foreach (['built_day'=>1,'frozen'=>false,'name'=>NODE_NAME_DEFAULT] as $k=>$v) if (!isset($n[$k])) $n[$k] = $v;
if (!isset($n['labelOffset'])) $n['labelOffset'] = ['x'=>8,'y'=>4];
$n['access'] = normAccess($n['access'] ?? 'inherit');
$n['lat'] = (float)$n['lat']; $n['lng'] = (float)$n['lng'];
} unset($n);

foreach ($data['edges'] as &$e) {
foreach (['electric'=>false,'built_day'=>1,'double'=>false,'points'=>[],'is_building'=>false,'build_end'=>0,'build_type'=>null,'upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'frozen'=>false] as $k=>$v) if (!isset($e[$k])) $e[$k] = $v;
$e['access'] = normAccess($e['access'] ?? 'inherit');
if (!isset($e['bridges']) || !is_array($e['bridges'])) $e['bridges'] = [];
foreach ($e['bridges'] as &$br) {
$kind = ($br['kind'] ?? 'river') === 'rail' ? 'rail' : 'river';
$br['kind'] = $kind;
$br['type'] = normalizeBridgeType($kind, (string)($br['type'] ?? BRIDGE_DEFAULT_TYPE));
if (!isset($br['id'])) $br['id'] = substr(md5($kind.'|'.($br['lat'] ?? 0).'|'.($br['lng'] ?? 0)), 0, 10);
if (!isset($br['name'])) $br['name'] = $kind === 'rail' ? 'Путепровод' : 'Река';
if (!isset($br['span'])) $br['span'] = $kind === 'rail' ? OVERPASS_SPAN_M : BRIDGE_MIN_SPAN_M;
if (!isset($br['bearing'])) $br['bearing'] = 0;
} unset($br);
} unset($e);

foreach ($data['routes'] as &$r) {
if (!isset($r['stations']) || !is_array($r['stations'])) $r['stations'] = [];
if (!isset($r['edges']) || !is_array($r['edges'])) $r['edges'] = [];
foreach (['loop'=>false,'name'=>'Маршрут','created_day'=>1,'auto'=>false] as $k=>$v) if (!isset($r[$k])) $r[$k] = $v;
} unset($r);

foreach ($data['depots'] as &$d) {
foreach (['level'=>1,'built_day'=>1,'is_building'=>false,'build_end'=>0,'upgrading'=>false,'upgrade_end'=>0,'upgrade_level'=>0,'frozen'=>false,'health'=>100] as $k=>$v) if (!isset($d[$k])) $d[$k] = $v;
if (!isset($d['capacity'])) $d['capacity'] = depotCapacityForLevel((int)$d['level']);
if (!isset($d['labelOffset'])) $d['labelOffset'] = ['x'=>10,'y'=>4];
$d['access'] = normAccess($d['access'] ?? 'inherit');
$d['health'] = max(0, min(100, (int)$d['health']));
} unset($d);

foreach ($data['trains'] as &$t) {
foreach (['depot_id'=>null,'route_id'=>null,'is_building'=>false,'build_end'=>0,'transit_end'=>0,'bought_day'=>1,'model'=>'generic','frozen'=>false,'name'=>'Поезд'] as $k=>$v) if (!isset($t[$k])) $t[$k] = $v;
} unset($t);

return ['data'=>$data, 'version'=>(int)$row['version']];
}
function computeNextDue(array $d): int {
$min = 0; $c = function (int $t) use (&$min) { if ($t > 0 && ($min === 0 || $t < $min)) $min = $t; };
foreach (['stations','edges','depots'] as $k) foreach (($d[$k] ?? []) as $o) {
if (!empty($o['is_building'])) $c((int)($o['build_end'] ?? 0));
if (!empty($o['upgrading'])) $c((int)($o['upgrade_end'] ?? 0));
}
foreach (($d['trains'] ?? []) as $t) { $c((int)($t['build_end'] ?? 0)); $c((int)($t['transit_end'] ?? 0)); }
return $min;
}
function worldSave(array $d): int {
cacheReset();
$p = db();
$p->prepare("UPDATE world SET data = :d, updated_at = :u, version = version + 1 WHERE id = 1")->execute([':d'=>json_encode($d, JSON_UNESCAPED_UNICODE), ':u'=>date('c')]);
metaSet('next_due', (string)computeNextDue($d));
return (int)$p->query("SELECT version FROM world WHERE id = 1")->fetchColumn();
}
function idxOf(array $arr, string $id): ?int { foreach ($arr as $i => $o) if ((string)($o['id'] ?? '') === $id) return (int)$i; return null; }
function idxStation(array $d, string $id): ?int { return idxOf($d['stations'], $id); }
function idxNode(array $d, string $id): ?int { return idxOf($d['nodes'], $id); }
function idxEdge(array $d, string $id): ?int { return idxOf($d['edges'], $id); }
function idxRoute(array $d, string $id): ?int { return idxOf($d['routes'], $id); }
function idxDepot(array $d, string $id): ?int { return idxOf($d['depots'], $id); }
function idxTrain(array $d, string $id): ?int { return idxOf($d['trains'], $id); }
function stationById(array $d, string $id): ?array { $i = idxStation($d, $id); return $i === null ? null : $d['stations'][$i]; }
function nodeById(array $d, string $id): ?array { $i = idxNode($d, $id); return $i === null ? null : $d['nodes'][$i]; }
function edgeById(array $d, string $id): ?array { $i = idxEdge($d, $id); return $i === null ? null : $d['edges'][$i]; }
function routeById(array $d, string $id): ?array { $i = idxRoute($d, $id); return $i === null ? null : $d['routes'][$i]; }
function depotById(array $d, string $id): ?array { $i = idxDepot($d, $id); return $i === null ? null : $d['depots'][$i]; }
function endpointById(array $d, string $id): ?array {
$s = stationById($d, $id); if ($s) return $s + ['_kind'=>'station'];
$n = nodeById($d, $id); if ($n) return $n + ['_kind'=>'node'];
return null;
}
function endpointName(array $d, string $id): string {
$e = endpointById($d, $id);
if (!$e) return '?';
return $e['_kind'] === 'station' ? (string)$e['name'] : ('узел ' . (string)$e['name']);
}
function pruneOrphanNodes(array &$d): int {
$used = [];
foreach ($d['edges'] as $e) { $used[(string)$e['from']] = true; $used[(string)$e['to']] = true; }
$before = count($d['nodes']);
$d['nodes'] = array_values(array_filter($d['nodes'], fn($n) => isset($used[(string)$n['id']]) || !empty($n['frozen'])));
return $before - count($d['nodes']);
}

// ============================================================
//  АККАУНТЫ / ЦВЕТА / ПОЛИТИКИ
// ============================================================
function cookieSign(string $id): string { return $id . '.' . substr(hash_hmac('sha256', $id, COOKIE_SECRET), 0, 16); }
function cookieVerify(?string $raw): ?int {
if (!$raw) return null; $p = explode('.', $raw, 2); if (count($p) !== 2) return null; [$id, $sig] = $p;
if (!ctype_digit($id)) return null;
if (!hash_equals(substr(hash_hmac('sha256', $id, COOKIE_SECRET), 0, 16), $sig)) return null;
return (int)$id;
}
function currentPlayer(): ?array { $id = cookieVerify($_COOKIE['pid'] ?? null); if ($id === null) return null; $s = db()->prepare("SELECT * FROM players WHERE id = :id"); $s->execute([':id'=>$id]); return $s->fetch(PDO::FETCH_ASSOC) ?: null; }
function requirePlayer(): array { $p = currentPlayer(); if (!$p) sendJson(['error'=>'not logged in'], 401); return $p; }
function playerName(int $pid): string { $s = db()->prepare("SELECT name FROM players WHERE id = :id"); $s->execute([':id'=>$pid]); $n = $s->fetchColumn(); return $n !== false ? (string)$n : ('игрок #' . $pid); }
function colorsAll(): array {
if (!empty($GLOBALS['__col'])) return $GLOBALS['__col'];
$out = [];
foreach (db()->query("SELECT id, color FROM players")->fetchAll(PDO::FETCH_ASSOC) as $r) {
$c = (string)$r['color'];
if ($c === '') $c = COMPANY_COLORS[((int)$r['id'] - 1) % count(COMPANY_COLORS)];
$out[(int)$r['id']] = $c;
}
$GLOBALS['__col'] = $out; return $out;
}
function playerColor(int $pid): string { $c = colorsAll(); return $c[$pid] ?? COMPANY_COLORS[max(0, $pid - 1) % count(COMPANY_COLORS)]; }
function freeCompanyColor(): string {
$used = []; foreach (colorsAll() as $c) $used[strtolower($c)] = true;
foreach (COMPANY_COLORS as $c) if (!isset($used[strtolower($c)])) return $c;
return COMPANY_COLORS[0];
}
function policiesAll(): array {
if (!empty($GLOBALS['__pol'])) return $GLOBALS['__pol'];
$out = [];
foreach (db()->query("SELECT * FROM company_policies")->fetchAll(PDO::FETCH_ASSOC) as $r) {
$out[(int)$r['player_id']] = ['allow_trackage'=>(int)$r['allow_trackage'], 'allow_stops'=>(int)$r['allow_stops'], 'allow_connect'=>(int)$r['allow_connect']];
}
$GLOBALS['__pol'] = $out; return $out;
}
function policyFor(array $pol, int $pid): array { return $pol[$pid] ?? ['allow_trackage'=>0, 'allow_stops'=>0, 'allow_connect'=>0]; }
function policySave(int $pid, array $p): void {
db()->prepare("INSERT OR REPLACE INTO company_policies (player_id, allow_trackage, allow_stops, allow_connect, updated_at) VALUES (:p, :t, :s, :c, :u)")
->execute([':p'=>$pid, ':t'=>!empty($p['allow_trackage']) ? 1 : 0, ':s'=>!empty($p['allow_stops']) ? 1 : 0, ':c'=>!empty($p['allow_connect']) ? 1 : 0, ':u'=>date('c')]);
cacheReset();
}
function companiesPublic(array $data): array {
$pol = policiesAll(); $out = [];
foreach (db()->query("SELECT id, name, color FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $r) {
$pid = (int)$r['id'];
$out[] = ['id'=>$pid, 'name'=>$r['name'], 'color'=>playerColor($pid), 'policies'=>policyFor($pol, $pid),
'stations'=>playerStationsCount($data, $pid), 'nodes'=>playerNodesCount($data, $pid), 'edges'=>playerEdgesCount($data, $pid), 'routes'=>playerRoutesCount($data, $pid)];
}
return $out;
}
function logEvent(string $k, ?int $pid, string $t, ?array $m = null): void {
db()->prepare("INSERT INTO events (kind, player_id, text, meta, created_at) VALUES (:k, :p, :t, :m, :d)")
->execute([':k'=>$k, ':p'=>$pid, ':t'=>$t, ':m'=>$m ? json_encode($m, JSON_UNESCAPED_UNICODE) : null, ':d'=>date('c')]);
}
function cleanupEvents(): void { db()->prepare("DELETE FROM events WHERE created_at < :c")->execute([':c'=>date('c', time() - EVENTS_KEEP_DAYS * 86400)]); }
function addMoney(int $pid, int $amt, string $kind, ?int $from = null, ?array $meta = null): void {
if ($amt < 0) {
$s = db()->prepare("UPDATE players SET money = money + :a WHERE id = :id AND money + :a >= 0");
$s->execute([':a'=>$amt, ':id'=>$pid]);
if ($s->rowCount() === 0) throw new RuntimeException('insufficient funds');
} else db()->prepare("UPDATE players SET money = money + :a WHERE id = :id")->execute([':a'=>$amt, ':id'=>$pid]);
db()->prepare("INSERT INTO transactions (from_player, to_player, amount, kind, meta, created_at) VALUES (:f, :t, :a, :k, :m, :d)")
->execute([':f'=>$from, ':t'=>$pid, ':a'=>$amt, ':k'=>$kind, ':m'=>$meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null, ':d'=>date('c')]);
}
function chargePlayer(int $pid, int $cost, string $kind, ?int $from = null, ?array $meta = null, string $err = 'недостаточно денег'): void {
$s = db()->prepare("SELECT money FROM players WHERE id = :id"); $s->execute([':id'=>$pid]);
if ((int)$s->fetchColumn() < $cost) fail($err, 400);
addMoney($pid, -$cost, $kind, $from, $meta);
}

// ============================================================
//  ДОСТУП (п.5, п.6)
// ============================================================
function accessAllowed(array $pol, array $obj, int $consumer, string $mode): bool {
$owner = (int)($obj['owner_id'] ?? 0);
if ($owner === $consumer || $owner <= 0) return true;
$a = normAccess($obj['access'] ?? 'inherit');
if ($a === 'open') return true;
if ($a === 'closed') return false;
$p = policyFor($pol, $owner);
$key = $mode === 'stop' ? 'allow_stops' : ($mode === 'connect' ? 'allow_connect' : 'allow_trackage');
return !empty($p[$key]);
}
function accessState(array $pol, array $obj, int $consumer, string $mode): string {
$owner = (int)($obj['owner_id'] ?? 0);
if ($owner === $consumer) return 'own';
$a = normAccess($obj['access'] ?? 'inherit');
if ($a === 'open') return 'open';
if ($a === 'closed') return 'closed';
return accessAllowed($pol, $obj, $consumer, $mode) ? 'policy' : 'denied';
}

// ============================================================
//  ГЕОМЕТРИЯ
// ============================================================
function distPointToSegmentLatLng(float $plat, float $plng, array $a, array $b): float {
$lat0 = ($a[0] + $b[0]) / 2 * M_PI / 180; $kx = 111320 * cos($lat0); $ky = 111320;
$px = $plng * $kx; $py = $plat * $ky; $ax = $a[1] * $kx; $ay = $a[0] * $ky; $bx = $b[1] * $kx; $by = $b[0] * $ky;
$dx = $bx - $ax; $dy = $by - $ay; $l2 = $dx*$dx + $dy*$dy;
$t = $l2 > 0 ? max(0, min(1, (($px-$ax)*$dx + ($py-$ay)*$dy) / $l2)) : 0;
return hypot($px - ($ax + $t*$dx), $py - ($ay + $t*$dy));
}
function segIntersectionLatLng(array $p1, array $p2, array $p3, array $p4): ?array {
$lat0 = deg2rad(($p1[0]+$p2[0]+$p3[0]+$p4[0]) / 4); $kx = 111320 * cos($lat0); $ky = 111320;
$x1=$p1[1]*$kx; $y1=$p1[0]*$ky; $x2=$p2[1]*$kx; $y2=$p2[0]*$ky;
$x3=$p3[1]*$kx; $y3=$p3[0]*$ky; $x4=$p4[1]*$kx; $y4=$p4[0]*$ky;
$rx=$x2-$x1; $ry=$y2-$y1; $sx=$x4-$x3; $sy=$y4-$y3; $den=$rx*$sy-$ry*$sx;
if (abs($den) < 1e-9) return null;
$t = (($x3-$x1)*$sy - ($y3-$y1)*$sx) / $den;
$u = (($x3-$x1)*$ry - ($y3-$y1)*$rx) / $den;
if ($t < 0 || $t > 1 || $u < 0 || $u > 1) return null;
return ['lat'=>$p1[0] + $t*($p2[0]-$p1[0]), 'lng'=>$p1[1] + $t*($p2[1]-$p1[1]), 't'=>$t];
}
function segmentsIntersect($a, $b, $c, $d): bool { return segIntersectionLatLng($a, $b, $c, $d) !== null; }
function segmentBearingDeg(array $p1, array $p2): float {
$l = deg2rad(($p1[0] + $p2[0]) / 2);
return rad2deg(atan2(($p2[1]-$p1[1]) * cos($l), $p2[0]-$p1[0]));
}
function acuteAngleDeg(array $a1, array $a2, array $b1, array $b2): float {
$kx = cos(deg2rad(($a1[0]+$a2[0]+$b1[0]+$b2[0]) / 4));
$ax = ($a2[1]-$a1[1])*$kx; $ay = $a2[0]-$a1[0]; $bx = ($b2[1]-$b1[1])*$kx; $by = $b2[0]-$b1[0];
$na = hypot($ax, $ay); $nb = hypot($bx, $by);
if ($na < 1e-12 || $nb < 1e-12) return 0.0;
return rad2deg(acos(max(-1.0, min(1.0, abs($ax*$bx + $ay*$by) / ($na*$nb)))));
}
function trackBuild(array $a, array $b, array $points): array {
$pts = [[(float)$a['lat'], (float)$a['lng']]];
foreach ($points as $p) $pts[] = [(float)$p['lat'], (float)$p['lng']];
$pts[] = [(float)$b['lat'], (float)$b['lng']];
$cum = [0.0]; $tot = 0.0;
for ($i = 0; $i < count($pts)-1; $i++) { $tot += haversineDistanceMeters($pts[$i][0], $pts[$i][1], $pts[$i+1][0], $pts[$i+1][1]); $cum[] = $tot; }
return ['pts'=>$pts, 'cum'=>$cum, 'total'=>$tot];
}
function trackPointAt(array $tr, float $off): array {
$off = max(0.0, min($tr['total'], $off)); $n = count($tr['pts']);
for ($i = 0; $i < $n-1; $i++) {
$s = $tr['cum'][$i]; $e = $tr['cum'][$i+1];
if ($off <= $e || $i === $n-2) {
$t = ($e-$s) > 0 ? ($off-$s)/($e-$s) : 0.0; $t = max(0.0, min(1.0, $t));
return [$tr['pts'][$i][0] + $t*($tr['pts'][$i+1][0]-$tr['pts'][$i][0]), $tr['pts'][$i][1] + $t*($tr['pts'][$i+1][1]-$tr['pts'][$i][1])];
}
}
return $tr['pts'][$n-1];
}
function projectOnPolyline(array $pts, float $lat, float $lng): array {
$kx = 111320 * cos(deg2rad($lat)); $ky = 111320;
$px = $lng*$kx; $py = $lat*$ky;
$best = ['off'=>INF, 'dist'=>0.0, 'lat'=>$pts[0][0], 'lng'=>$pts[0][1], 'seg'=>0, 't'=>0.0];
$acc = 0.0;
for ($i = 0; $i < count($pts)-1; $i++) {
$A = $pts[$i]; $B = $pts[$i+1];
$ax=$A[1]*$kx; $ay=$A[0]*$ky; $bx=$B[1]*$kx; $by=$B[0]*$ky;
$dx=$bx-$ax; $dy=$by-$ay; $l2=$dx*$dx+$dy*$dy; $len=sqrt($l2);
$t = $l2 > 0 ? max(0, min(1, (($px-$ax)*$dx + ($py-$ay)*$dy)/$l2)) : 0;
$d = hypot($px-($ax+$t*$dx), $py-($ay+$t*$dy));
if ($d < $best['off']) $best = ['off'=>$d, 'dist'=>$acc + $t*$len, 'lat'=>$A[0]+$t*($B[0]-$A[0]), 'lng'=>$A[1]+$t*($B[1]-$A[1]), 'seg'=>$i, 't'=>$t];
$acc += $len;
}
$best['total'] = $acc;
return $best;
}
function edgePolyline(array $d, array $e): array {
$a = endpointById($d, (string)($e['from'] ?? '')); $b = endpointById($d, (string)($e['to'] ?? ''));
if (!$a || !$b) return [];
$pts = [[(float)$a['lat'], (float)$a['lng']]];
foreach (($e['points'] ?? []) as $p) $pts[] = [(float)$p['lat'], (float)$p['lng']];
$pts[] = [(float)$b['lat'], (float)$b['lng']];
return $pts;
}
function bboxOf(array $pts): array {
return ['minLat'=>min(array_column($pts,0)), 'maxLat'=>max(array_column($pts,0)), 'minLng'=>min(array_column($pts,1)), 'maxLng'=>max(array_column($pts,1))];
}
function bboxOverlap(array $a, array $b, float $pad = 0.0): bool {
return !($a['maxLat']+$pad < $b['minLat'] || $a['minLat']-$pad > $b['maxLat'] || $a['maxLng']+$pad < $b['minLng'] || $a['minLng']-$pad > $b['maxLng']);
}
function clusterizeCrossings(array $items, float $r = 100): array {
$u = [];
foreach ($items as $it) {
$near = false;
foreach ($u as $x) if (haversineDistanceMeters($it['lat'], $it['lng'], $x['lat'], $x['lng']) <= $r) { $near = true; break; }
if (!$near) $u[] = $it;
}
return $u;
}
function parsePoints($raw): array {
if ($raw === null || $raw === []) return [];
if (!is_array($raw) || count($raw) > MAX_EDGE_POINTS) fail('bad points', 400);
$o = [];
foreach ($raw as $p) {
if (!is_array($p)) fail('bad points', 400);
$lat = (float)($p['lat'] ?? 0); $lng = (float)($p['lng'] ?? 0);
if (!is_finite($lat) || !is_finite($lng)) fail('bad points', 400);
$o[] = ['lat'=>$lat, 'lng'=>$lng];
}
return $o;
}
function parsePoint($raw): ?array {
if (!is_array($raw)) return null;
$lat = (float)($raw['lat'] ?? 0); $lng = (float)($raw['lng'] ?? 0);
if (!is_finite($lat) || !is_finite($lng) || ($lat == 0 && $lng == 0)) return null;
return ['lat'=>$lat, 'lng'=>$lng];
}
function pathLength(array $a, array $b, array $points): float { return max(1.0, round(trackBuild($a, $b, $points)['total'], 2)); }

// ============================================================
//  OSM
// ============================================================
function getOsmDb(): ?PDO {
static $pdo = null; static $tried = false;
if ($tried) return $pdo;
$tried = true;
if (!file_exists(OSM_DB_FILE)) return null;
try { $pdo = new PDO('sqlite:' . OSM_DB_FILE); $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Throwable $e) { $pdo = null; }
return $pdo;
}
function parseWkb($w) {
if (!$w) return null;
$b = unpack('Corder/Ltype', substr($w, 0, 5)); $g = $b['type'] % 1000;
if ($g === 1) { $c = unpack('dlat/dlon', substr($w, 5, 16)); return ['lat'=>$c['lat'], 'lng'=>$c['lon']]; }
if ($g === 2) {
$n = unpack('L', substr($w, 5, 4))[1]; $o = [];
for ($i = 0; $i < $n; $i++) { $p = unpack('dlat/dlon', substr($w, 9 + $i*16, 16)); $o[] = [$p['lon'], $p['lat']]; }
return $o;
}
if ($g === 3) {
$n = unpack('L', substr($w, 5, 4))[1]; $rings = []; $off = 9;
for ($i = 0; $i < $n; $i++) {
$np = unpack('L', substr($w, $off, 4))[1]; $off += 4; $ring = [];
for ($j = 0; $j < $np; $j++) { $p = unpack('dlat/dlon', substr($w, $off, 16)); $ring[] = [$p['lon'], $p['lat']]; $off += 16; }
$rings[] = $ring;
}
return $rings;
}
return null;
}
function osmNameFromRow(array $r, string $fb): string {
foreach (['name','name_ru','NAME','title'] as $k) if (!empty($r[$k]) && is_string($r[$k])) return trim($r[$k]);
foreach (['other_tags','tags','osm_tags','attrs'] as $k) {
if (empty($r[$k]) || !is_string($r[$k])) continue;
if (preg_match('/"name"\s*=>\s*"([^"]+)"/u', $r[$k], $m)) return trim($m[1]);
$j = json_decode($r[$k], true);
if (is_array($j) && !empty($j['name']) && is_string($j['name'])) return trim($j['name']);
}
return $fb;
}
function getOsmStationDiscount(float $lat, float $lng): bool {
$p = getOsmDb(); if (!$p) return false;
try { $st = $p->query("SELECT GEOMETRY FROM points WHERE (other_tags LIKE '%\"railway\"=>\"station\"%' OR other_tags LIKE '%\"railway\"=>\"halt\"%')"); }
catch (Throwable $e) { return false; }
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
$pt = parseWkb($r['GEOMETRY']); if (!$pt || !isset($pt['lat'])) continue;
if (haversineDistanceMeters($lat, $lng, $pt['lat'], $pt['lng']) <= OSM_STATION_RADIUS) return true;
if (haversineDistanceMeters($lat, $lng, $pt['lng'], $pt['lat']) <= OSM_STATION_RADIUS) return true;
}
return false;
}
function countNearbyBuildings(float $lat, float $lng, float $rad = 500.0): int {
$p = getOsmDb(); if (!$p) return 0;
$dLa = $rad / 111320.0; $dLo = $rad / (111320.0 * max(0.01, cos(deg2rad($lat))));
$st = $p->prepare("SELECT coords FROM fast_buildings WHERE minLat <= :a AND maxLat >= :b AND minLng <= :c AND maxLng >= :d");
$st->execute([':a'=>$lat+$dLa, ':b'=>$lat-$dLa, ':c'=>$lng+$dLo, ':d'=>$lng-$dLo]);
$n = 0; $kx = 111320.0 * cos(deg2rad($lat)); $ky = 111320.0; $r2 = $rad*$rad;
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $b) {
$raw = json_decode($b['coords'], true); if (!$raw || !is_array($raw)) continue;
if (count($raw) === 1) { $pl = (float)$raw[0][0]; $pa = (float)$raw[0][1]; }
else { $las = array_column($raw,1); $los = array_column($raw,0); $pa = (min($las)+max($las))/2; $pl = (min($los)+max($los))/2; }
$dx = ($pl-$lng)*$kx; $dy = ($pa-$lat)*$ky;
if ($dx*$dx + $dy*$dy <= $r2) $n++;
}
return $n;
}
function getBuildingIncomePercent(int $b): int {
if ($b <= 0) return 0; if ($b <= 5) return 5; if ($b <= 10) return 10; if ($b <= 19) return 15;
if ($b <= 29) return 20; if ($b <= 39) return 25; if ($b <= 45) return 30; if ($b <= 52) return 35;
if ($b <= 75) return 40; if ($b <= 87) return 45; if ($b <= 99) return 50; if ($b <= 119) return 55;
if ($b <= 139) return 60; if ($b <= 159) return 65; if ($b <= 179) return 70; if ($b <= 199) return 75;
if ($b <= 219) return 80; if ($b <= 239) return 85; if ($b <= 259) return 90; if ($b <= 279) return 95;
return 100;
}
function getForbiddenZones(): array {
static $z = null; if ($z !== null) return $z;
$z = []; $f = __DIR__ . '/zzoness.json';
if (!file_exists($f)) return $z;
$d = json_decode((string)file_get_contents($f), true);
if (!is_array($d) || !isset($d['features'])) return $z;
foreach ($d['features'] as $ft) {
$t = $ft['geometry']['type'] ?? ''; $nm = $ft['properties']['name'] ?? 'Запретная зона';
if ($t === 'Polygon') { $c = []; foreach ($ft['geometry']['coordinates'][0] as $p) $c[] = [(float)$p[1], (float)$p[0]]; $z[] = ['name'=>$nm, 'coords'=>$c]; }
elseif ($t === 'MultiPolygon') foreach ($ft['geometry']['coordinates'] as $poly) { $c = []; foreach ($poly[0] as $p) $c[] = [(float)$p[1], (float)$p[0]]; $z[] = ['name'=>$nm, 'coords'=>$c]; }
}
return $z;
}
function isPointInForbiddenZone(float $lat, float $lng): ?array {
foreach (getForbiddenZones() as $zone) {
$c = $zone['coords']; $n = count($c); $in = false;
for ($i = 0, $j = $n-1; $i < $n; $j = $i++) {
$yi = $c[$i][0]; $xi = $c[$i][1]; $yj = $c[$j][0]; $xj = $c[$j][1];
if ((($yi > $lat) != ($yj > $lat)) && ($lng < ($xj-$xi)*($lat-$yi)/(($yj-$yi) ?: 1e-12) + $xi)) $in = !$in;
}
if ($in) return $zone;
}
return null;
}
function doesEdgeCrossForbiddenZone(array $a, array $b, array $points): ?array {
$zs = getForbiddenZones(); if (!$zs) return null;
$pts = trackBuild($a, $b, $points)['pts'];
foreach ($zs as $z) {
$c = $z['coords']; $n = count($c);
for ($i = 0; $i < count($pts)-1; $i++) {
for ($j = 0; $j < $n; $j++) if (segmentsIntersect($pts[$i], $pts[$i+1], $c[$j], $c[($j+1)%$n])) return $z;
if (isPointInForbiddenZone($pts[$i][0], $pts[$i][1])) return $z;
}
}
return null;
}
function edgeOsmCoverage(array $a, array $b, array $points): float {
$p = getOsmDb(); if (!$p) return 0.0;
$pts = trackBuild($a, $b, $points)['pts']; $bb = bboxOf($pts);
$st = $p->prepare("SELECT coords FROM fast_rails WHERE minLat <= :a AND maxLat >= :b AND minLng <= :c AND maxLng >= :d");
$st->execute([':a'=>$bb['maxLat'], ':b'=>$bb['minLat'], ':c'=>$bb['maxLng'], ':d'=>$bb['minLng']]);
$rails = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { $raw = json_decode($r['coords'], true); if ($raw) $rails[] = array_map(fn($c)=>[$c[1],$c[0]], $raw); }
$tot = 0.0; $cov = 0.0;
for ($i = 0; $i < count($pts)-1; $i++) {
$p1 = $pts[$i]; $p2 = $pts[$i+1];
$len = haversineDistanceMeters($p1[0], $p1[1], $p2[0], $p2[1]); $tot += $len;
$hit = false;
foreach ($rails as $rc) for ($j = 0; $j < count($rc)-1; $j++) {
$d = min(distPointToSegmentLatLng($p1[0], $p1[1], $rc[$j], $rc[$j+1]), distPointToSegmentLatLng($p2[0], $p2[1], $rc[$j], $rc[$j+1]));
if ($d <= OSM_EDGE_TOLERANCE_M) { $hit = true; break 2; }
}
if ($hit) $cov += $len;
}
return $tot > 0 ? $cov/$tot : 0.0;
}

// ============================================================
//  МОСТЫ
// ============================================================
function bridgeTypes(): array {
return [
'wood'            => ['label'=>'Деревянный',                'speed'=>30,  'pct'=>0.30, 'material'=>'wood',     'rail_only'=>false],
'wood_reinforced' => ['label'=>'Укреплённый деревянный',    'speed'=>45,  'pct'=>0.60, 'material'=>'wood',     'rail_only'=>false],
'concrete_beam'   => ['label'=>'Бетонный балочный',         'speed'=>60,  'pct'=>1.00, 'material'=>'concrete', 'rail_only'=>false],
'concrete_arch'   => ['label'=>'Бетонный арочный',          'speed'=>90,  'pct'=>1.50, 'material'=>'concrete', 'rail_only'=>false],
'steel_beam'      => ['label'=>'Металлический балочный',    'speed'=>120, 'pct'=>2.20, 'material'=>'steel',    'rail_only'=>false],
'steel_arch'      => ['label'=>'Металлический арочный',     'speed'=>150, 'pct'=>3.20, 'material'=>'steel',    'rail_only'=>false],
'steel_modern'    => ['label'=>'Современный металлический', 'speed'=>180, 'pct'=>4.00, 'material'=>'steel',    'rail_only'=>false],
'level'           => ['label'=>'Глухое пересечение',        'speed'=>40,  'pct'=>0.25, 'material'=>'level',    'rail_only'=>true],
];
}
function bridgeTypesPublic(): array {
$o = [];
foreach (bridgeTypes() as $k => $t) $o[] = ['key'=>$k, 'label'=>$t['label'], 'speed'=>$t['speed'], 'pct'=>$t['pct'], 'material'=>$t['material'], 'rail_only'=>$t['rail_only'], 'cost_river'=>bridgeCost('river',$k), 'cost_rail'=>bridgeCost('rail',$k)];
return $o;
}
function normalizeBridgeType(string $kind, ?string $t): string {
$all = bridgeTypes(); $t = (string)$t;
if (!isset($all[$t])) return BRIDGE_DEFAULT_TYPE;
if ($kind !== 'rail' && !empty($all[$t]['rail_only'])) return BRIDGE_DEFAULT_TYPE;
return $t;
}
function bridgeCost(string $kind, string $t): int { $a = bridgeTypes(); $x = $a[$t] ?? $a[BRIDGE_DEFAULT_TYPE]; return (int)ceil(($kind === 'rail' ? COST_OVERPASS_BASE : COST_BRIDGE) * $x['pct']); }
function bridgeSpeed(string $t): int { $a = bridgeTypes(); return (int)($a[$t]['speed'] ?? $a[BRIDGE_DEFAULT_TYPE]['speed']); }
function bridgeLabel(string $t): string { $a = bridgeTypes(); return (string)($a[$t]['label'] ?? $a[BRIDGE_DEFAULT_TYPE]['label']); }
function decorateBridge(array $br): array {
$k = ($br['kind'] ?? 'river') === 'rail' ? 'rail' : 'river';
$t = normalizeBridgeType($k, (string)($br['type'] ?? BRIDGE_DEFAULT_TYPE));
$br['kind'] = $k; $br['type'] = $t; $br['type_label'] = bridgeLabel($t); $br['speed'] = bridgeSpeed($t); $br['cost'] = bridgeCost($k, $t);
return $br;
}
function computeRiverCrossings(array $a, array $b, array $points): array {
$p = getOsmDb(); if (!$p) return [];
$tr = trackBuild($a, $b, $points); if ($tr['total'] < 1) return [];
$bb = bboxOf($tr['pts']);
$st = $p->prepare("SELECT * FROM fast_rivers WHERE minLat <= :a AND maxLat >= :b AND minLng <= :c AND maxLng >= :d");
$st->execute([':a'=>$bb['maxLat'], ':b'=>$bb['minLat'], ':c'=>$bb['maxLng'], ':d'=>$bb['minLng']]);
$hits = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $river) {
$raw = json_decode((string)($river['coords'] ?? ''), true); if (!$raw || count($raw) < 2) continue;
$rc = array_map(fn($c)=>[$c[1],$c[0]], $raw);
$len = 0.0; for ($k = 0; $k < count($rc)-1; $k++) $len += haversineDistanceMeters($rc[$k][0], $rc[$k][1], $rc[$k+1][0], $rc[$k+1][1]);
if ($len <= BRIDGE_RIVER_MIN_LENGTH_M) continue;
$name = osmNameFromRow($river, 'Река без названия');
$dTo = function (float $la, float $ln) use ($rc): float {
$d = INF; for ($j = 0; $j < count($rc)-1; $j++) { $x = distPointToSegmentLatLng($la, $ln, $rc[$j], $rc[$j+1]); if ($x < $d) $d = $x; }
return $d;
};
for ($i = 0; $i < count($tr['pts'])-1; $i++) {
$p1 = $tr['pts'][$i]; $p2 = $tr['pts'][$i+1];
for ($j = 0; $j < count($rc)-1; $j++) {
$ix = segIntersectionLatLng($p1, $p2, $rc[$j], $rc[$j+1]); if (!$ix) continue;
$off = $tr['cum'][$i] + $ix['t'] * ($tr['cum'][$i+1] - $tr['cum'][$i]);
$bear = segmentBearingDeg($p1, $p2);
$dAt = function (float $o) use ($tr, $dTo): float { [$la,$ln] = trackPointAt($tr, $o); return $dTo($la, $ln); };
$maxHalf = BRIDGE_MAX_SPAN_M / 2; $step = BRIDGE_SAMPLE_STEP_M;
$f = 0.0; while ($off+$f < $tr['total'] && $f < $maxHalf) { $f += $step; if ($dAt($off+$f) > BRIDGE_WATER_HALF_WIDTH_M) { $f -= $step; break; } }
$f = max(0.0, min($f, $tr['total']-$off, $maxHalf));
$bw = 0.0; while ($off-$bw > 0 && $bw < $maxHalf) { $bw += $step; if ($dAt($off-$bw) > BRIDGE_WATER_HALF_WIDTH_M) { $bw -= $step; break; } }
$bw = max(0.0, min($bw, $off, $maxHalf));
$span = max(BRIDGE_MIN_SPAN_M, min(BRIDGE_MAX_SPAN_M, $f + $bw));
[$clat, $clng] = trackPointAt($tr, max(0.0, min($tr['total'], $off + ($f-$bw)/2)));
$hits[] = decorateBridge(['id'=>substr(md5('river|'.round($clat,5).'|'.round($clng,5)),0,10), 'kind'=>'river','name'=>$name,'ref'=>null,
'lat'=>round($clat,7),'lng'=>round($clng,7),'bearing'=>round($bear,1),'span'=>(int)round($span),'type'=>BRIDGE_DEFAULT_TYPE]);
}
}
}
return array_slice(clusterizeCrossings($hits, BRIDGE_CLUSTER_RADIUS_M), 0, 50);
}
function computeRailCrossings(array $data, array $a, array $b, array $points, ?string $excludeEdgeId = null): array {
$tr = trackBuild($a, $b, $points); if ($tr['total'] < 2) return ['items'=>[], 'blocked'=>[]];
$bb = bboxOf($tr['pts']);
$verts = [];
foreach ($data['stations'] as $s) $verts[] = [(float)$s['lat'], (float)$s['lng']];
foreach ($data['nodes'] as $n) $verts[] = [(float)$n['lat'], (float)$n['lng']];
$near = function (float $la, float $ln, float $r) use ($verts): bool {
foreach ($verts as $v) if (haversineDistanceMeters($la, $ln, $v[0], $v[1]) <= $r) return true;
return false;
};
$items = []; $blocked = [];
$aid = (string)($a['id'] ?? ''); $bid = (string)($b['id'] ?? '');
foreach ($data['edges'] as $e) {
$eid = (string)($e['id'] ?? '');
if ($excludeEdgeId !== null && $eid === $excludeEdgeId) continue;
$pl = edgePolyline($data, $e); if (count($pl) < 2) continue;
if (!bboxOverlap($bb, bboxOf($pl), 0.001)) continue;
$otherName = endpointName($data, (string)$e['from']) . ' ↔ ' . endpointName($data, (string)$e['to']);
$shared = array_intersect([$aid, $bid], [(string)$e['from'], (string)$e['to']]);
$found = false;
for ($i = 0; $i < count($tr['pts'])-1 && !$found; $i++) {
$p1 = $tr['pts'][$i]; $p2 = $tr['pts'][$i+1];
for ($j = 0; $j < count($pl)-1; $j++) {
$ix = segIntersectionLatLng($p1, $p2, $pl[$j], $pl[$j+1]); if (!$ix) continue;
$la = (float)$ix['lat']; $ln = (float)$ix['lng'];
if ($near($la, $ln, RAIL_CROSS_STATION_SKIP_M)) continue;
$skip = false;
foreach ($shared as $sid) { if ($sid === '') continue; $v = endpointById($data, (string)$sid); if ($v && haversineDistanceMeters($la, $ln, (float)$v['lat'], (float)$v['lng']) <= RAIL_CROSS_SHARED_SKIP_M) { $skip = true; break; } }
if ($skip) continue;
$ang = acuteAngleDeg($p1, $p2, $pl[$j], $pl[$j+1]);
if ($ang <= RAIL_CROSS_MIN_ANGLE_DEG) {
$blocked[] = ['kind'=>'rail','lat'=>round($la,7),'lng'=>round($ln,7),'angle'=>round($ang,1),'ref'=>$eid,'name'=>$otherName,
'reason'=>'слишком острый угол пересечения ('.round($ang,1).'° ≤ '.RAIL_CROSS_MIN_ANGLE_DEG.'°) — сделайте съезд у станции'];
$found = true; break;
}
$items[] = decorateBridge(['id'=>substr(md5('rail|'.$eid.'|'.round($la,5).'|'.round($ln,5)),0,10),'kind'=>'rail','name'=>'Над '.$otherName,'ref'=>$eid,
'lat'=>round($la,7),'lng'=>round($ln,7),'bearing'=>round(segmentBearingDeg($p1,$p2),1),'span'=>(int)OVERPASS_SPAN_M,'angle'=>round($ang,1),'type'=>BRIDGE_DEFAULT_TYPE]);
$found = true; break;
}
}
}
for ($i = 0; $i < count($tr['pts'])-1; $i++) for ($j = $i+2; $j < count($tr['pts'])-1; $j++) {
$ix = segIntersectionLatLng($tr['pts'][$i], $tr['pts'][$i+1], $tr['pts'][$j], $tr['pts'][$j+1]); if (!$ix) continue;
$la = (float)$ix['lat']; $ln = (float)$ix['lng'];
if ($near($la, $ln, RAIL_CROSS_STATION_SKIP_M)) continue;
$ang = acuteAngleDeg($tr['pts'][$i], $tr['pts'][$i+1], $tr['pts'][$j], $tr['pts'][$j+1]);
if ($ang <= RAIL_CROSS_MIN_ANGLE_DEG) { $blocked[] = ['kind'=>'rail','lat'=>round($la,7),'lng'=>round($ln,7),'angle'=>round($ang,1),'ref'=>'self','name'=>'самопересечение','reason'=>'трасса пересекает себя под острым углом']; continue; }
$items[] = decorateBridge(['id'=>substr(md5('self|'.round($la,5).'|'.round($ln,5)),0,10),'kind'=>'rail','name'=>'Над собственной трассой','ref'=>'self',
'lat'=>round($la,7),'lng'=>round($ln,7),'bearing'=>round(segmentBearingDeg($tr['pts'][$i],$tr['pts'][$i+1]),1),'span'=>(int)OVERPASS_SPAN_M,'angle'=>round($ang,1),'type'=>BRIDGE_DEFAULT_TYPE]);
}
return ['items'=>array_slice(clusterizeCrossings($items, BRIDGE_CLUSTER_RADIUS_M), 0, 50), 'blocked'=>$blocked];
}
function computeEdgeCrossings(array $data, array $a, array $b, array $points, ?string $excl = null): array {
$rail = computeRailCrossings($data, $a, $b, $points, $excl);
return ['bridges'=>computeRiverCrossings($a, $b, $points), 'overpasses'=>$rail['items'], 'blocked'=>$rail['blocked']];
}
function applyBridgeSelection(array $items, array $sel): array {
if (!$sel) return $items;
$out = [];
foreach ($items as $it) {
$type = (string)$it['type']; $best = null; $bd = INF;
foreach ($sel as $s) {
if (!is_array($s)) continue;
$k = (string)($s['kind'] ?? ''); if ($k !== '' && $k !== $it['kind']) continue;
if (!isset($s['lat'], $s['lng'])) continue;
$d = haversineDistanceMeters((float)$it['lat'], (float)$it['lng'], (float)$s['lat'], (float)$s['lng']);
if ($d < $bd) { $bd = $d; $best = $s; }
}
if ($best !== null && $bd <= BRIDGE_MATCH_RADIUS_M) $type = normalizeBridgeType((string)$it['kind'], (string)($best['type'] ?? $type));
$it['type'] = $type; $out[] = decorateBridge($it);
}
return $out;
}
function crossingsCost(array $items): int { $s = 0; foreach ($items as $i) $s += bridgeCost((string)$i['kind'], (string)$i['type']); return $s; }

// ============================================================
//  ПРЕПЯТСТВИЯ
// ============================================================
function checkOsmObstacles(array $a, array $b, array $points): array {
$p = getOsmDb(); $empty = ['culverts'=>0,'dams'=>0,'buildings'=>0,'obstacles'=>[]];
if (!$p) return $empty;
$pts = trackBuild($a, $b, $points)['pts']; $bb = bboxOf($pts);
$par = [':a'=>$bb['maxLat'], ':b'=>$bb['minLat'], ':c'=>$bb['maxLng'], ':d'=>$bb['minLng']];
$cul = 0; $dams = 0;
$st = $p->prepare("SELECT coords FROM fast_rivers WHERE minLat <= :a AND maxLat >= :b AND minLng <= :c AND maxLng >= :d");
$st->execute($par);
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
$raw = json_decode((string)$r['coords'], true); if (!$raw || count($raw) < 2) continue;
$rc = array_map(fn($c)=>[$c[1],$c[0]], $raw);
$len = 0.0; for ($k = 0; $k < count($rc)-1; $k++) $len += haversineDistanceMeters($rc[$k][0], $rc[$k][1], $rc[$k+1][0], $rc[$k+1][1]);
if ($len > BRIDGE_RIVER_MIN_LENGTH_M) continue;
for ($i = 0; $i < count($pts)-1; $i++) {
$hit = false; for ($j = 0; $j < count($rc)-1; $j++) if (segmentsIntersect($pts[$i], $pts[$i+1], $rc[$j], $rc[$j+1])) { $hit = true; break; }
if ($hit) { $cul++; break; }
}
}
$st = $p->prepare("SELECT coords FROM fast_water_bodies WHERE minLat <= :a AND maxLat >= :b AND minLng <= :c AND maxLng >= :d");
$st->execute($par);
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $l) {
$raw = json_decode((string)$l['coords'], true); if (!$raw || count($raw) < 3) continue;
$lc = array_map(fn($c)=>[$c[1],$c[0]], $raw);
if ($lc[0] !== end($lc)) $lc[] = $lc[0];
for ($i = 0; $i < count($pts)-1; $i++) {
$hit = false; for ($j = 0; $j < count($lc)-1; $j++) if (segmentsIntersect($pts[$i], $pts[$i+1], $lc[$j], $lc[$j+1])) { $hit = true; break; }
if ($hit) { $dams++; break; }
}
}
$st = $p->prepare("SELECT coords FROM fast_buildings WHERE minLat <= :a AND maxLat >= :b AND minLng <= :c AND maxLng >= :d");
$st->execute($par);
$hits = [];
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $bld) {
$raw = json_decode((string)$bld['coords'], true); if (!$raw) continue;
if (count($raw) === 1) {
$pt = [$raw[0][1], $raw[0][0]];
for ($i = 0; $i < count($pts)-1; $i++) if (distPointToSegmentLatLng($pt[0], $pt[1], $pts[$i], $pts[$i+1]) <= 10) { $hits[] = json_encode($raw); break; }
} else {
$bc = array_map(fn($c)=>[$c[1],$c[0]], $raw);
if ($bc[0] !== end($bc)) $bc[] = $bc[0];
for ($i = 0; $i < count($pts)-1; $i++) {
$hit = false; for ($j = 0; $j < count($bc)-1; $j++) if (segmentsIntersect($pts[$i], $pts[$i+1], $bc[$j], $bc[$j+1])) { $hit = true; break; }
if ($hit) { $hits[] = json_encode($raw); break; }
}
}
}
$bl = count(array_unique($hits)); $obs = [];
if ($cul > 0) $obs[] = ['type'=>'culvert','count'=>$cul,'cost_each'=>COST_CULVERT,'total'=>$cul*COST_CULVERT,'label'=>'🚰 Водопропускная труба'];
if ($dams > 0) $obs[] = ['type'=>'dam','count'=>$dams,'cost_each'=>COST_DAM,'total'=>$dams*COST_DAM,'label'=>'🏞️ Дамба через озеро/пруд'];
if ($bl > 0) $obs[] = ['type'=>'building','count'=>$bl,'cost_each'=>COST_DEMOLISH_BUILDING,'total'=>$bl*COST_DEMOLISH_BUILDING,'label'=>'🏚️ Снос зданий'];
return ['culverts'=>$cul,'dams'=>$dams,'buildings'=>$bl,'obstacles'=>$obs];
}
function obstaclesCost(array $o): int { $s = 0; foreach ($o as $x) $s += (int)($x['total'] ?? 0); return $s; }

// ============================================================
//  СОДЕРЖАНИЕ / СТОИМОСТЬ
// ============================================================
function stationTracks(array $d, string $sid): int {
$t = 0;
foreach ($d['edges'] as $e) { $w = (!empty($e['double']) || ($e['upgrade_type'] ?? null) === 'double') ? 2 : 1; if (($e['from'] ?? '') === $sid) $t += $w; if (($e['to'] ?? '') === $sid) $t += $w; }
return $t;
}
function nodeEdgesCount(array $d, string $nid): int { $n = 0; foreach ($d['edges'] as $e) if (($e['from'] ?? '') === $nid || ($e['to'] ?? '') === $nid) $n++; return $n; }
function edgeLengthCoeff(float $l): float { return 1.0 + max(0.0, $l)/1000.0; }
function edgeElectricCoeff(array $e): float { return !empty($e['electric']) ? 1.20 : 1.0; }
function stationUpkeep(array $s): float { if (!empty($s['is_building']) || !empty($s['frozen'])) return 0.0; return UPKEEP_STATION_BASE + UPKEEP_STATION_PER_CLASS * max(1, (int)($s['class'] ?? 1)); }
function nodeUpkeep(array $n): float { return !empty($n['frozen']) ? 0.0 : UPKEEP_NODE; }
function edgeUpkeep(array $e, ?float $fb = null): float {
if (!empty($e['is_building']) || !empty($e['frozen'])) return 0.0;
$t = !empty($e['double']) ? 2 : 1; $l = isset($e['length']) ? (float)$e['length'] : (float)($fb ?? 0);
return INCOME_PER_TRACK * 2 * $t * edgeLengthCoeff($l) * edgeElectricCoeff($e) * UPKEEP_EDGE_INCOME_SHARE;
}
function depotUpkeep(array $d): float { if (!empty($d['is_building']) || !empty($d['frozen'])) return 0.0; return DEPOT_UPKEEP_BASE + DEPOT_UPKEEP_PER_LEVEL * (max(1, (int)($d['level'] ?? 1)) - 1); }
function trainUpkeep(array $t, bool $run): float { return !empty($t['frozen']) ? 0.0 : ($run ? TRAIN_UPKEEP_ACTIVE : TRAIN_UPKEEP_IDLE); }
function stationReplacementCost(array $s): int { $v = COST_STATION; for ($c = 1; $c < max(1,(int)($s['class'] ?? 1)); $c++) $v += stationUpgradeCost($c); return $v; }
function nodeReplacementCost(array $n): int { return COST_NODE; }
function edgeReplacementCost(array $e, array $a, array $b): int {
$l = (float)($e['length'] ?? haversineDistanceMeters((float)$a['lat'], (float)$a['lng'], (float)$b['lat'], (float)$b['lng']));
$c = (int)ceil($l * COST_EDGE_PER_M * (!empty($e['double']) ? 2 : 1));
if (!empty($e['double'])) $c += (int)ceil($l * COST_UPGRADE_DOUBLE_PER_M);
if (!empty($e['electric'])) $c += (int)ceil($l * COST_UPGRADE_ELECTRIFY_PER_M);
foreach (($e['bridges'] ?? []) as $br) $c += bridgeCost((string)($br['kind'] ?? 'river'), (string)($br['type'] ?? BRIDGE_DEFAULT_TYPE));
return $c;
}
function depotReplacementCost(array $d): int { $v = DEPOT_COST; for ($l = 1; $l < max(1,(int)($d['level'] ?? 1)); $l++) $v += DEPOT_UPGRADE_COST * $l; return $v; }
function trainReplacementCost(array $t): int { return TRAIN_COST; }
function edgeSpeedKmh(array $e): int {
if (!empty($e['is_building']) || !empty($e['frozen'])) return 0;
$s = !empty($e['electric']) ? EDGE_SPEED_ELECTRIC : EDGE_SPEED_BASE;
foreach (($e['bridges'] ?? []) as $br) $s = min($s, bridgeSpeed((string)($br['type'] ?? BRIDGE_DEFAULT_TYPE)));
return max(1, $s);
}
function playerStationsCount(array $d, int $p): int { $n = 0; foreach ($d['stations'] as $s) if ((int)($s['owner_id'] ?? 0) === $p) $n++; return $n; }
function playerNodesCount(array $d, int $p): int { $n = 0; foreach ($d['nodes'] as $s) if ((int)($s['owner_id'] ?? 0) === $p) $n++; return $n; }
function playerEdgesCount(array $d, int $p): int { $n = 0; foreach ($d['edges'] as $s) if ((int)($s['owner_id'] ?? 0) === $p) $n++; return $n; }
function playerDepotsCount(array $d, int $p): int { $n = 0; foreach ($d['depots'] as $s) if ((int)($s['owner_id'] ?? 0) === $p) $n++; return $n; }
function playerTrainsCount(array $d, int $p): int { $n = 0; foreach ($d['trains'] as $s) if ((int)($s['owner_id'] ?? 0) === $p) $n++; return $n; }
function playerRoutesCount(array $d, int $p): int { $n = 0; foreach ($d['routes'] as $s) if ((int)($s['owner_id'] ?? 0) === $p) $n++; return $n; }
function stationCostForPlayer(array $d, int $p): int { return (int)ceil(COST_STATION * pow(STATION_PRICE_GROWTH, playerStationsCount($d, $p))); }
function edgeCostPerMeterForPlayer(array $d, int $p): float { return COST_EDGE_PER_M * pow(EDGE_PRICE_GROWTH, playerEdgesCount($d, $p)); }
function depotCostForPlayer(array $d, int $p): int { return (int)ceil(DEPOT_COST * pow(DEPOT_PRICE_GROWTH, playerDepotsCount($d, $p))); }
function stationRoutesUsed(array $d, string $sid): int { $n = 0; foreach ($d['routes'] as $r) if (in_array($sid, array_map('strval', $r['stations'] ?? []), true)) $n++; return $n; }

// ============================================================
//  ГРАФ / МАРШРУТИЗАЦИЯ
// ============================================================
function worldIndex(array $d): array {
$st = []; foreach ($d['stations'] as $s) $st[(string)$s['id']] = $s;
$nd = []; foreach ($d['nodes'] as $n) $nd[(string)$n['id']] = $n;
$ep = $st + $nd;
$ed = []; $adj = []; $pair = [];
foreach ($ep as $id => $_) $adj[$id] = [];
foreach ($d['edges'] as $e) {
$id = (string)$e['id']; $ed[$id] = $e;
$f = (string)($e['from'] ?? ''); $t = (string)($e['to'] ?? '');
if (!isset($ep[$f]) || !isset($ep[$t])) continue;
$len = (float)($e['length'] ?? haversineDistanceMeters((float)$ep[$f]['lat'], (float)$ep[$f]['lng'], (float)$ep[$t]['lat'], (float)$ep[$t]['lng']));
$adj[$f][] = ['e'=>$id, 'v'=>$t, 'len'=>$len];
$adj[$t][] = ['e'=>$id, 'v'=>$f, 'len'=>$len];
$pair["$f|$t"] = $id; $pair["$t|$f"] = $id;
}
$dp = []; foreach ($d['depots'] as $x) $dp[(string)$x['id']] = $x;
$tr = []; foreach ($d['trains'] as $x) $tr[(string)$x['id']] = $x;
return ['st'=>$st,'nd'=>$nd,'ep'=>$ep,'ed'=>$ed,'adj'=>$adj,'pair'=>$pair,'dp'=>$dp,'tr'=>$tr];
}
function edgeUsableFor(array $pol, array $e, int $pid): bool {
if (!empty($e['is_building']) || !empty($e['frozen'])) return false;
return accessAllowed($pol, $e, $pid, 'trackage');
}
function pathBetween(array $idx, array $pol, string $a, string $b, int $pid): ?array {
if ($a === $b) return ['steps'=>[], 'len'=>0.0];
if (!isset($idx['ep'][$a]) || !isset($idx['ep'][$b])) return null;
$key = $pid . '|' . $a . '|' . $b;
if (!isset($GLOBALS['__path'])) $GLOBALS['__path'] = [];
if (array_key_exists($key, $GLOBALS['__path'])) return $GLOBALS['__path'][$key];
$usable = [];
foreach ($idx['ed'] as $eid => $e) $usable[$eid] = edgeUsableFor($pol, $e, $pid);
$dist = [$a => 0.0]; $prev = []; $settled = [];
$pq = new SplPriorityQueue(); $pq->setExtractFlags(SplPriorityQueue::EXTR_BOTH); $pq->insert($a, 0);
$found = false;
while (!$pq->isEmpty()) {
$top = $pq->extract(); $v = (string)$top['data']; $dv = -(float)$top['priority'];
if (isset($settled[$v])) continue;
if ($dv > ($dist[$v] ?? INF) + 1e-6) continue;
$settled[$v] = true;
if ($v === $b) { $found = true; break; }
foreach (($idx['adj'][$v] ?? []) as $link) {
$eid = $link['e']; if (empty($usable[$eid])) continue;
$w = (string)$link['v']; if (isset($settled[$w])) continue;
$nd = $dv + max(0.1, (float)$link['len']);
if ($nd < ($dist[$w] ?? INF) - 1e-9) { $dist[$w] = $nd; $prev[$w] = ['v'=>$v, 'e'=>$eid]; $pq->insert($w, -$nd); }
}
}
if (!$found) { $GLOBALS['__path'][$key] = null; return null; }
$steps = []; $cur = $b;
while ($cur !== $a) {
if (!isset($prev[$cur])) { $GLOBALS['__path'][$key] = null; return null; }
$steps[] = ['e'=>$prev[$cur]['e'], 'from'=>$prev[$cur]['v'], 'to'=>$cur];
$cur = $prev[$cur]['v'];
}
$steps = array_reverse($steps);
$res = ['steps'=>$steps, 'len'=>$dist[$b]];
$GLOBALS['__path'][$key] = $res;
return $res;
}
function routeCompute(array $idx, array $pol, array $stops, bool $loop, int $owner, int $assigned): array {
$out = ['ok'=>false,'reason'=>'','active'=>false,'km'=>0.0,'double_share'=>0.0,'elec_share'=>0.0,'speed'=>0,'min_class'=>1,
'building_sum'=>0.0,'recommended'=>1,'slots'=>1,'trains'=>$assigned,'train_factor'=>0.0,'income'=>0,'upkeep'=>0.0,
'fees'=>[],'fee_total'=>0,'edges'=>[],'stops'=>[],'path'=>[],'legs'=>[],'transit'=>[],'owners'=>[],'foreign_km'=>0.0];
$stops = array_values(array_unique(array_map('strval', $stops)));
$out['stops'] = $stops;
if (count($stops) < 2) { $out['reason'] = 'нужно минимум 2 станции'; return $out; }
$seq = $stops; if ($loop) $seq[] = $stops[0];
$km = 0.0; $dkm = 0.0; $ekm = 0.0; $speed = PHP_INT_MAX;
$edgeIds = []; $foreign = []; $steps = []; $legs = []; $vertices = []; $reason = '';
for ($i = 0; $i < count($seq)-1; $i++) {
$from = $seq[$i]; $to = $seq[$i+1];
$vertices[$from] = true;
$path = pathBetween($idx, $pol, $from, $to, $owner);
if ($path === null || ($from !== $to && !$path['steps'])) {
if ($reason === '') $reason = 'нет доступного пути «' . ($idx['st'][$from]['name'] ?? '?') . '» → «' . ($idx['st'][$to]['name'] ?? '?') . '»';
$legs[] = ['from'=>$from, 'to'=>$to, 'len'=>0.0, 'steps'=>[], 'ok'=>false];
continue;
}
$legLen = 0.0;
foreach ($path['steps'] as $s) {
$e = $idx['ed'][$s['e']]; $len = (float)($e['length'] ?? 0);
$km += $len; $legLen += $len;
if (!empty($e['double'])) $dkm += $len;
if (!empty($e['electric'])) $ekm += $len;
$speed = min($speed, edgeSpeedKmh($e));
$eo = (int)($e['owner_id'] ?? 0);
if ($eo > 0 && $eo !== $owner) $foreign[$eo] = ($foreign[$eo] ?? 0.0) + $len;
$edgeIds[] = $s['e']; $steps[] = $s;
$vertices[(string)$s['to']] = true;
}
$legs[] = ['from'=>$from, 'to'=>$to, 'len'=>$legLen, 'steps'=>$path['steps'], 'ok'=>true];
}
$minClass = 99; $bsum = 0.0;
foreach ($stops as $sid) {
if (!isset($idx['st'][$sid])) { if ($reason === '') $reason = 'станция удалена'; continue; }
$s = $idx['st'][$sid];
if (!empty($s['is_building']) && $reason === '') $reason = "станция «{$s['name']}» строится";
if (!empty($s['frozen']) && $reason === '') $reason = "станция «{$s['name']}» на продаже";
if (!accessAllowed($pol, $s, $owner, 'stop') && $reason === '') $reason = "нет разрешения на остановку на «{$s['name']}»";
$minClass = min($minClass, max(1, (int)$s['class']));
$h = max(0, min(100, (int)($s['health'] ?? 100)));
$bsum += (getBuildingIncomePercent((int)($s['buildings'] ?? 0)) / 100.0) * stationClassMultiplier((int)$s['class']) * ($h / 100.0);
}
if ($minClass === 99) $minClass = 1;
if ($speed === PHP_INT_MAX) $speed = EDGE_SPEED_BASE;
$ds = $km > 0 ? $dkm/$km : 0.0; $es = $km > 0 ? $ekm/$km : 0.0;
$rec = max(1, (int)ceil(($km/1000.0) / TRAIN_ROUTE_KM_DIVISOR));
$slots = max(1, (int)ceil($rec * (1 + $ds)));
if (CLASS_LIMITS_TRAIN_SLOTS) $slots = max(1, min($minClass, $slots));
$transit = [];
foreach (array_keys($vertices) as $v) if (!in_array($v, $stops, true) && isset($idx['st'][$v])) $transit[] = $v;
$out['km'] = $km; $out['double_share'] = $ds; $out['elec_share'] = $es; $out['speed'] = $speed;
$out['min_class'] = $minClass; $out['building_sum'] = $bsum; $out['recommended'] = $rec; $out['slots'] = $slots;
$out['edges'] = array_values(array_unique($edgeIds)); $out['path'] = $steps; $out['legs'] = $legs; $out['transit'] = $transit;
$fk = array_sum($foreign); $out['foreign_km'] = $fk;
$owners = []; foreach ($foreign as $p => $m) $owners[] = ['player_id'=>(int)$p, 'km'=>$m/1000.0, 'share'=>$km > 0 ? $m/$km : 0.0];
$out['owners'] = $owners;
if ($reason !== '') { $out['reason'] = $reason; return $out; }
$factor = $assigned > 0 ? min($assigned, $slots) / $rec : 0.0;
$gross = ROUTE_INCOME_BASE * $bsum * (1 + ROUTE_DOUBLE_BONUS*$ds) * (1 + ROUTE_ELECTRIC_BONUS*$es) * ($speed / ROUTE_SPEED_BASE) * $factor;
$upk = $assigned > 0 ? ($km/1000.0) * ROUTE_UPKEEP_PER_KM : 0.0;
$basis = TRACTAGE_SHARE_BASIS === 'net' ? max(0.0, $gross - $upk) : $gross;
$fees = []; $ft = 0;
if ($assigned > 0 && $km > 0) foreach ($foreign as $p => $m) {
$pay = (int)floor($basis * ($m/$km) * TRACTAGE_REVENUE_SHARE);
if ($pay > 0) { $fees[(int)$p] = $pay; $ft += $pay; }
}
$out['ok'] = true; $out['active'] = $assigned > 0; $out['train_factor'] = $factor;
$out['income'] = (int)floor($gross); $out['upkeep'] = $upk; $out['fees'] = $fees; $out['fee_total'] = $ft;
return $out;
}
function routeGeometry(array $data, array $idx, array $info): array {
$pts = []; $stopDist = []; $acc = 0.0; $first = true;
foreach (($info['legs'] ?? []) as $li => $leg) {
if ($first) { $stopDist[] = 0.0; $first = false; }
foreach (($leg['steps'] ?? []) as $s) {
$e = $idx['ed'][$s['e']] ?? null; if (!$e) continue;
$ll = edgePolyline($data, $e); if (count($ll) < 2) continue;
if ((string)$e['from'] !== (string)$s['from']) $ll = array_reverse($ll);
$start = $first ? 0 : 1;
if ($pts === []) $start = 0;
for ($i = $start; $i < count($ll); $i++) {
$p = $ll[$i];
if ($pts !== []) $acc += haversineDistanceMeters($pts[count($pts)-1][0], $pts[count($pts)-1][1], $p[0], $p[1]);
$pts[] = $p;
}
}
$stopDist[] = $acc;
}
if (count($stopDist) > count($info['stops'] ?? [])) $stopDist = array_slice($stopDist, 0, count($info['stops']));
$transit = [];
if (count($pts) > 1) foreach (($info['transit'] ?? []) as $sid) {
$s = $idx['st'][$sid] ?? null; if (!$s) continue;
$pr = projectOnPolyline($pts, (float)$s['lat'], (float)$s['lng']);
$transit[] = ['id'=>$sid, 'name'=>$s['name'], 'dist'=>$pr['dist'], 'off'=>$pr['off']];
}
return ['geometry'=>$pts, 'stops_dist'=>$stopDist, 'transit'=>$transit, 'length'=>$acc];
}

// ============================================================
//  ЭКОНОМИКА
// ============================================================
function economy(array $data): array {
if (!empty($GLOBALS['__eco'])) return $GLOBALS['__eco'];
$now = time(); $idx = worldIndex($data); $pol = policiesAll();
$ready = []; $perRoute = [];
foreach ($data['trains'] as $t) {
$ok = empty($t['is_building']) && (int)($t['build_end'] ?? 0) <= $now && (int)($t['transit_end'] ?? 0) <= $now;
$hasDp = !empty($t['depot_id']) && isset($idx['dp'][(string)$t['depot_id']]);
$rid = (string)($t['route_id'] ?? '');
if ($ok && $hasDp && $rid !== '') { $perRoute[$rid] = ($perRoute[$rid] ?? 0) + 1; $ready[(string)$t['id']] = $rid; }
}
$routes = []; $servedSt = []; $passedSt = []; $servedEd = []; $servedNd = []; $dpServed = [];
foreach ($data['routes'] as $r) {
$rid = (string)$r['id']; $own = (int)($r['owner_id'] ?? 0);
$info = routeCompute($idx, $pol, $r['stations'] ?? [], !empty($r['loop']), $own, $perRoute[$rid] ?? 0);
$info['owner_id'] = $own; $info['name'] = (string)($r['name'] ?? 'Маршрут');
$info['status'] = !$info['ok'] ? 'broken' : ($info['active'] ? 'active' : 'idle');
$routes[$rid] = $info;
if ($info['ok'] && $info['active']) {
foreach ($info['stops'] as $sid) $servedSt[$sid] = true;
foreach ($info['transit'] as $sid) $passedSt[$sid] = true;
foreach ($info['edges'] as $eid) $servedEd[$eid] = true;
foreach ($info['path'] as $s) { $servedNd[(string)$s['from']] = true; $servedNd[(string)$s['to']] = true; }
}
}
foreach ($data['trains'] as $t) {
$rid = (string)($t['route_id'] ?? '');
if ($rid !== '' && isset($routes[$rid]) && $routes[$rid]['status'] === 'active' && isset($ready[(string)$t['id']]) && !empty($t['depot_id'])) $dpServed[(string)$t['depot_id']] = true;
}
$fin = [];
$touch = function (int $p) use (&$fin) {
if ($p <= 0) return;
if (!isset($fin[$p])) $fin[$p] = ['income'=>0,'upkeep'=>0.0,'net'=>0.0,'route_income'=>0,'tractage_in'=>0,'tractage_out'=>0,
'station_upkeep'=>0.0,'node_upkeep'=>0.0,'edge_upkeep'=>0.0,'depot_upkeep'=>0.0,'train_upkeep'=>0.0,'route_upkeep'=>0.0,
'routes_active'=>0,'routes_total'=>0,'trains'=>0,'trains_running'=>0,'depots'=>0];
};
foreach ($data['stations'] as $s) {
$p = (int)($s['owner_id'] ?? 0); if ($p <= 0) continue; $touch($p);
$id = (string)$s['id'];
$k = isset($servedSt[$id]) ? 1.0 : (isset($passedSt[$id]) ? TRANSIT_UPKEEP_RATIO : CONSERVATION_RATIO);
$u = stationUpkeep($s) * $k; $fin[$p]['station_upkeep'] += $u; $fin[$p]['upkeep'] += $u;
}
foreach ($data['nodes'] as $n) {
$p = (int)($n['owner_id'] ?? 0); if ($p <= 0) continue; $touch($p);
$u = nodeUpkeep($n) * (isset($servedNd[(string)$n['id']]) ? 1.0 : CONSERVATION_RATIO);
$fin[$p]['node_upkeep'] += $u; $fin[$p]['upkeep'] += $u;
}
foreach ($data['edges'] as $e) {
$p = (int)($e['owner_id'] ?? 0); if ($p <= 0) continue; $touch($p);
$u = edgeUpkeep($e) * (isset($servedEd[(string)$e['id']]) ? 1.0 : CONSERVATION_RATIO);
$fin[$p]['edge_upkeep'] += $u; $fin[$p]['upkeep'] += $u;
}
foreach ($data['depots'] as $d) {
$p = (int)($d['owner_id'] ?? 0); if ($p <= 0) continue; $touch($p); $fin[$p]['depots']++;
$u = depotUpkeep($d) * (isset($dpServed[(string)$d['id']]) ? 1.0 : CONSERVATION_RATIO);
$fin[$p]['depot_upkeep'] += $u; $fin[$p]['upkeep'] += $u;
}
foreach ($data['trains'] as $t) {
$p = (int)($t['owner_id'] ?? 0); if ($p <= 0) continue; $touch($p); $fin[$p]['trains']++;
$rid = (string)($t['route_id'] ?? '');
$run = isset($ready[(string)$t['id']]) && isset($routes[$rid]) && $routes[$rid]['status'] === 'active';
if ($run) $fin[$p]['trains_running']++;
$u = trainUpkeep($t, $run); $fin[$p]['train_upkeep'] += $u; $fin[$p]['upkeep'] += $u;
}
foreach ($routes as $info) {
$p = (int)$info['owner_id']; if ($p <= 0) continue; $touch($p); $fin[$p]['routes_total']++;
if ($info['status'] !== 'active') continue;
$fin[$p]['routes_active']++;
$fin[$p]['route_income'] += $info['income']; $fin[$p]['income'] += $info['income'];
$fin[$p]['route_upkeep'] += $info['upkeep']; $fin[$p]['upkeep'] += $info['upkeep'];
foreach ($info['fees'] as $other => $fee) {
$fin[$p]['tractage_out'] += $fee; $fin[$p]['upkeep'] += $fee;
$touch((int)$other);
$fin[(int)$other]['tractage_in'] += $fee; $fin[(int)$other]['income'] += $fee;
}
}
foreach ($fin as &$f) $f['net'] = $f['income'] - $f['upkeep']; unset($f);
$res = ['routes'=>$routes,'players'=>$fin,'served_st'=>$servedSt,'passed_st'=>$passedSt,'served_ed'=>$servedEd,'served_nd'=>$servedNd,'depot_served'=>$dpServed,'trains_ready'=>$ready];
$GLOBALS['__eco'] = $res;
return $res;
}
function playerFinancials(array $d, int $p): array {
$e = economy($d);
return $e['players'][$p] ?? ['income'=>0,'upkeep'=>0.0,'net'=>0.0,'route_income'=>0,'tractage_in'=>0,'tractage_out'=>0,
'station_upkeep'=>0.0,'node_upkeep'=>0.0,'edge_upkeep'=>0.0,'depot_upkeep'=>0.0,'train_upkeep'=>0.0,'route_upkeep'=>0.0,
'routes_active'=>0,'routes_total'=>0,'trains'=>0,'trains_running'=>0,'depots'=>0];
}
function stationIncomePerDay(array $d, string $sid): int { return 0; }
function playerCompanyValue(array $d, int $p): array {
$sv = 0; $sc = 0; foreach ($d['stations'] as $s) if ((int)($s['owner_id'] ?? 0) === $p) { $sc++; $sv += stationReplacementCost($s); }
$nv = 0; $nc = 0; foreach ($d['nodes'] as $n) if ((int)($n['owner_id'] ?? 0) === $p) { $nc++; $nv += nodeReplacementCost($n); }
$ev = 0; $km = 0.0;
foreach ($d['edges'] as $e) if ((int)($e['owner_id'] ?? 0) === $p) {
$a = endpointById($d, (string)$e['from']); $b = endpointById($d, (string)$e['to']);
if ($a && $b) { $ev += edgeReplacementCost($e, $a, $b); $km += (float)($e['length'] ?? 0)/1000.0; }
}
$dv = 0; $dc = 0; foreach ($d['depots'] as $x) if ((int)($x['owner_id'] ?? 0) === $p) { $dc++; $dv += depotReplacementCost($x); }
$tv = 0; $tc = 0; foreach ($d['trains'] as $x) if ((int)($x['owner_id'] ?? 0) === $p) { $tc++; $tv += trainReplacementCost($x); }
return ['stations'=>$sc,'station_value'=>$sv,'nodes'=>$nc,'node_value'=>$nv,'edge_km'=>round($km,2),'edge_value'=>$ev,
'depots'=>$dc,'depot_value'=>$dv,'trains'=>$tc,'train_value'=>$tv,'assets'=>$sv+$nv+$ev+$dv+$tv];
}

// ============================================================
//  ТИК / СТРОЙКА / ИЗНОС
// ============================================================
function clearUpgrade(array &$o): void { $o['upgrading'] = false; $o['upgrade_type'] = null; $o['upgrade_end'] = 0; $o['upgrade_class'] = 0; }
function completeConstruction(array &$d, int $now): bool {
$ch = false;
foreach ($d['stations'] as &$s) {
if (!empty($s['is_building']) && (int)$s['build_end'] <= $now) { $s['is_building'] = false; $s['build_type'] = null; $ch = true; logEvent('construction_done', (int)$s['owner_id'], "Станция «{$s['name']}» достроена!", []); }
if (!empty($s['upgrading']) && (int)$s['upgrade_end'] <= $now) {
if (($s['upgrade_type'] ?? null) === 'class') {
$nc = (int)($s['upgrade_class'] ?? 0); if ($nc <= (int)$s['class']) $nc = (int)$s['class'] + 1;
$s['class'] = min(MAX_CLASS, $nc); $s['capacity'] = 4 + ($s['class']-1)*2; $s['radius'] = stationRadiusForClass((int)$s['class']);
logEvent('construction_done', (int)$s['owner_id'], "Станция «{$s['name']}» улучшена до класса {$s['class']} — доступно маршрутов: " . stationRouteLimit($s), []);
}
clearUpgrade($s); $ch = true;
}
} unset($s);
foreach ($d['edges'] as &$e) {
$p = (int)($e['owner_id'] ?? 0);
if (!empty($e['is_building']) && (int)$e['build_end'] <= $now) { $e['is_building'] = false; $e['build_type'] = null; $ch = true; logEvent('construction_done', $p, "Перегон достроен!", []); }
if (!empty($e['upgrading']) && (int)$e['upgrade_end'] <= $now) {
$t = $e['upgrade_type'] ?? null;
if ($t === 'double') { $e['double'] = true; logEvent('construction_done', $p, "Второй путь построен!", []); }
elseif ($t === 'electric') { $e['electric'] = true; logEvent('construction_done', $p, "Перегон электрифицирован!", []); }
clearUpgrade($e); $ch = true;
}
} unset($e);
foreach ($d['depots'] as &$x) {
$p = (int)($x['owner_id'] ?? 0);
if (!empty($x['is_building']) && (int)$x['build_end'] <= $now) { $x['is_building'] = false; $ch = true; logEvent('construction_done', $p, "Депо «{$x['name']}» построено!", []); }
if (!empty($x['upgrading']) && (int)$x['upgrade_end'] <= $now) {
$l = max((int)$x['level']+1, (int)($x['upgrade_level'] ?? 0));
$x['level'] = min(DEPOT_MAX_LEVEL, $l); $x['capacity'] = depotCapacityForLevel((int)$x['level']);
$x['upgrading'] = false; $x['upgrade_end'] = 0; $x['upgrade_level'] = 0; $ch = true;
logEvent('construction_done', $p, "Депо «{$x['name']}» расширено до уровня {$x['level']}!", []);
}
} unset($x);
foreach ($d['trains'] as &$t) {
$p = (int)($t['owner_id'] ?? 0);
if (!empty($t['is_building']) && (int)$t['build_end'] <= $now) { $t['is_building'] = false; $t['build_end'] = 0; $ch = true; logEvent('train_ready', $p, "Поезд «{$t['name']}» поставлен в депо!", []); }
if ((int)($t['transit_end'] ?? 0) > 0 && (int)$t['transit_end'] <= $now) { $t['transit_end'] = 0; $ch = true; logEvent('train_ready', $p, "Поезд «{$t['name']}» прибыл в новое депо", []); }
} unset($t);
if ($ch) cacheReset();
return $ch;
}
function applyHealthDecay(array &$d, int $now): bool {
$last = (int)metaGet('health_tick');
if ($last === 0) { metaSet('health_tick', (string)$now); return false; }
$el = $now - $last; if ($el < 60) return false;
$dec = HEALTH_DECAY_PER_REAL_DAY * ($el / REAL_DAY_SEC); if ($dec <= 0) return false;
$ch = false;
foreach (['stations','depots'] as $k) {
foreach ($d[$k] as &$o) {
if (!empty($o['is_building']) || !empty($o['frozen'])) continue;
$old = (int)($o['health'] ?? 100);
$pend = (float)($o['health_pending'] ?? 0) + $dec;
$lost = (int)floor($pend); $o['health_pending'] = $pend - $lost;
$new = max(0, $old - $lost);
if ($new < $old) { $o['health'] = $new; $ch = true; }
} unset($o);
}
metaSet('health_tick', (string)$now);
if ($ch) cacheReset();
return $ch;
}
function tickDue(): bool {
$now = time();
$ht = (int)metaGet('health_tick'); if ($ht === 0 || $now - $ht >= 60) return true;
if ($now - (int)metaGet('last_tick') >= DAY_DURATION_SEC) return true;
$nd = metaGet('next_due'); if ($nd === null) return true; if ((int)$nd > 0 && (int)$nd <= $now) return true;
foreach (db()->query("SELECT expires_at FROM auctions WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN) as $x) if (strtotime((string)$x) < $now) return true;
return false;
}
function tickWorld(): void {
if (!tickDue()) return;
runTx(function () {
$pdo = db(); $last = (int)metaGet('last_tick'); $day = (int)metaGet('day_count'); $now = time();
$passed = intdiv($now - $last, DAY_DURATION_SEC);
$w = worldGet(); $ch = false; cacheReset();
if (applyHealthDecay($w['data'], $now)) $ch = true;
if (completeConstruction($w['data'], $now)) $ch = true;
if (expireAuctions($pdo, $w['data'], $now)) $ch = true;
if ($passed > 0) {
if (processDays($pdo, $w, $day, $passed)) $ch = true;
cleanupEvents();
$pdo->prepare("DELETE FROM daily_stats WHERE day < :d")->execute([':d'=>$day + $passed - 400]);
metaSet('day_count', (string)($day + $passed));
metaSet('last_tick', (string)($last + $passed * DAY_DURATION_SEC));
}
if ($ch) worldSave($w['data']); else metaSet('next_due', (string)computeNextDue($w['data']));
});
}
function processDays(PDO $pdo, array &$world, int $day, int $passed): bool {
$ch = false; cacheReset();
$players = $pdo->query("SELECT id, money, grace_until_day, last_grant_at FROM players")->fetchAll(PDO::FETCH_ASSOC);
$statIns = $pdo->prepare("INSERT INTO daily_stats (player_id, day, income, upkeep, net) VALUES (:p, :d, :i, :u, :n)");
$snap = $pdo->prepare("INSERT OR REPLACE INTO company_snapshots (player_id, day, company_value, stations, edge_km, depots, trains, routes, nodes) VALUES (:p, :d, :v, :s, :k, :dp, :tr, :rt, :nd)");
$first = max($day, $day + $passed - 400); $toAuction = [];
foreach ($players as $p) {
$pid = (int)$p['id']; cacheReset();
$fin = playerFinancials($world['data'], $pid);
$grace = (int)($p['grace_until_day'] ?? 0) > $day;
$upkDay = $fin['upkeep'] * ($grace ? GRACE_UPKEEP_RATIO : 1.0);
$net = (int)round($fin['income']*$passed - $upkDay*$passed);
for ($d = $first; $d < $day + $passed; $d++) $statIns->execute([':p'=>$pid, ':d'=>$d, ':i'=>(int)$fin['income'], ':u'=>(int)ceil($upkDay), ':n'=>(int)floor($fin['income'] - $upkDay)]);
$cv = playerCompanyValue($world['data'], $pid);
$bs = $pdo->prepare("SELECT money FROM players WHERE id = :id"); $bs->execute([':id'=>$pid]); $bal = (int)$bs->fetchColumn();
$val = $cv['assets'] + $bal;
for ($d = $first; $d < $day + $passed; $d++) $snap->execute([':p'=>$pid, ':d'=>$d, ':v'=>$val, ':s'=>$cv['stations'], ':k'=>$cv['edge_km'], ':dp'=>$cv['depots'], ':tr'=>$cv['trains'], ':rt'=>playerRoutesCount($world['data'], $pid), ':nd'=>$cv['nodes']]);
if ($net >= 0) { if ($net > 0) addMoney($pid, $net, 'net_income', null, ['days'=>$passed]); }
else {
$need = -$net; $can = min($bal, $need);
if ($can > 0) $pdo->prepare("UPDATE players SET money = money - :a WHERE id = :id")->execute([':a'=>$can, ':id'=>$pid]);
$debt = $need - $can; if ($debt > 0) $toAuction[$pid] = $debt;
}
}
foreach ($toAuction as $pid => $debt) {
cacheReset();
$nm = playerName((int)$pid);
$comps = confiscateForDebt($world, (int)$pid, (float)$debt);
$pdo->prepare("UPDATE players SET money = 0 WHERE id = :id")->execute([':id'=>(int)$pid]);
$a = 0; $b = 0; $c = 0; $e = 0; $nn = 0;
foreach ($comps as $ci) { createAuctionFromComponent($pdo, (int)$pid, $ci, 'bankrupt'); $a += count($ci['stations']); $b += count($ci['edges']); $c += count($ci['depots']); $e += count($ci['trains']); $nn += count($ci['nodes']); }
if ($comps) { logEvent('bankruptcy', (int)$pid, "{$nm} обанкротился: {$a} ст., {$nn} узл., {$b} пер., {$c} депо, {$e} поездов ушли на рынок", ['debt'=>$debt]); $ch = true; }
cacheReset();
if (playerStationsCount($world['data'], (int)$pid) === 0 && playerDepotsCount($world['data'], (int)$pid) === 0) {
$s = $pdo->prepare("SELECT money, last_grant_at FROM players WHERE id = :id"); $s->execute([':id'=>(int)$pid]); $r = $s->fetch(PDO::FETCH_ASSOC);
if ($r && (int)$r['money'] < RESTART_GRANT && time() - (int)$r['last_grant_at'] > RESTART_GRANT_COOLDOWN_SEC) {
addMoney((int)$pid, RESTART_GRANT, 'restart_grant', null, []);
$pdo->prepare("UPDATE players SET last_grant_at = :t, grace_until_day = :g WHERE id = :id")->execute([':t'=>time(), ':g'=>$day + $passed + GRACE_DAYS, ':id'=>(int)$pid]);
logEvent('restart_grant', (int)$pid, "{$nm} получил грант на перезапуск: " . RESTART_GRANT . " ₽", []);
}
}
}
return $ch;
}

// ============================================================
//  КОМПОНЕНТЫ / БАНКРОТСТВО / АУКЦИОНЫ
// ============================================================
function findComponents(array $vertices, array $edges): array {
$adj = []; foreach ($vertices as $v) $adj[(string)$v['id']] = [];
foreach ($edges as $e) {
$f = (string)($e['from'] ?? ''); $t = (string)($e['to'] ?? '');
if (!isset($adj[$f]) || !isset($adj[$t])) continue;
$adj[$f][] = $t; $adj[$t][] = $f;
}
$seen = []; $out = [];
foreach ($adj as $sid => $_) {
if (isset($seen[$sid])) continue;
$q = [$sid]; $comp = [];
while ($q) { $v = array_shift($q); if (isset($seen[$v])) continue; $seen[$v] = true; $comp[] = $v; foreach ($adj[$v] as $nb) if (!isset($seen[$nb])) $q[] = $nb; }
$out[] = $comp;
}
return $out;
}
function confiscateForDebt(array &$world, int $pid, float $debt): array {
$d = &$world['data']; $eco = economy($d);
$mySt = []; $myNd = []; $vset = [];
foreach ($d['stations'] as $s) if ((int)($s['owner_id'] ?? 0) === $pid && empty($s['frozen'])) { $mySt[] = $s; $vset[(string)$s['id']] = true; }
foreach ($d['nodes'] as $n) if ((int)($n['owner_id'] ?? 0) === $pid && empty($n['frozen'])) { $myNd[] = $n; $vset[(string)$n['id']] = true; }
if (!$mySt && !$myNd) return [];
$myEd = []; foreach ($d['edges'] as $e) if ((int)($e['owner_id'] ?? 0) === $pid && empty($e['frozen'])) $myEd[] = $e;
$myDp = []; foreach ($d['depots'] as $x) if ((int)($x['owner_id'] ?? 0) === $pid && empty($x['frozen'])) $myDp[] = $x;
$myTr = []; foreach ($d['trains'] as $x) if ((int)($x['owner_id'] ?? 0) === $pid && empty($x['frozen'])) $myTr[] = $x;
$protected = []; $best = null;
foreach ($eco['routes'] as $rid => $info) {
if ((int)$info['owner_id'] !== $pid || $info['status'] !== 'active') continue;
$ok = false;
foreach ($myDp as $x) {
if (!in_array((string)$x['station_id'], $info['stops'], true)) continue;
foreach ($myTr as $t) if ((string)($t['depot_id'] ?? '') === (string)$x['id'] && (string)($t['route_id'] ?? '') === (string)$rid) { $ok = true; break 2; }
}
if (!$ok) continue;
if ($best === null || $info['income'] > $best['income']) $best = $info;
}
if ($best !== null) {
foreach ($best['stops'] as $sid) $protected[$sid] = true;
foreach ($best['path'] as $s) { $protected[(string)$s['from']] = true; $protected[(string)$s['to']] = true; }
}
$verts = array_merge($mySt, $myNd);
$adjEdges = array_values(array_filter($myEd, fn($e) => isset($vset[(string)$e['from']]) && isset($vset[(string)$e['to']])));
$comps = findComponents($verts, $adjEdges);
$groups = [];
foreach ($comps as $comp) {
$set = array_flip($comp);
$prot = false; foreach ($comp as $v) if (isset($protected[$v])) { $prot = true; break; }
$groups[] = ['set'=>$set, 'ids'=>$comp, 'protected'=>$prot, 'edges'=>[]];
}
$orphan = ['set'=>[], 'ids'=>[], 'protected'=>false, 'edges'=>[]];
foreach ($myEd as $e) {
$placed = false;
foreach ($groups as &$g) if (isset($g['set'][(string)$e['from']]) || isset($g['set'][(string)$e['to']])) { $g['edges'][] = $e; $placed = true; break; }
unset($g);
if (!$placed) $orphan['edges'][] = $e;
}
if ($orphan['edges']) $groups[] = $orphan;
$info = [];
foreach ($groups as $g) {
$set = $g['set'];
$st = array_values(array_filter($mySt, fn($s) => isset($set[(string)$s['id']])));
$nd = array_values(array_filter($myNd, fn($s) => isset($set[(string)$s['id']])));
$ed = $g['edges'];
$dp = array_values(array_filter($myDp, fn($x) => isset($set[(string)($x['station_id'] ?? '')])));
$dpIds = []; foreach ($dp as $x) $dpIds[(string)$x['id']] = true;
$tr = array_values(array_filter($myTr, fn($x) => isset($dpIds[(string)($x['depot_id'] ?? '')])));
$upk = 0.0; $inc = 0;
foreach ($st as $s) $upk += stationUpkeep($s) * (isset($eco['served_st'][$s['id']]) ? 1.0 : (isset($eco['passed_st'][$s['id']]) ? TRANSIT_UPKEEP_RATIO : CONSERVATION_RATIO));
foreach ($nd as $n) $upk += nodeUpkeep($n) * (isset($eco['served_nd'][$n['id']]) ? 1.0 : CONSERVATION_RATIO);
foreach ($ed as $e) $upk += edgeUpkeep($e) * (isset($eco['served_ed'][$e['id']]) ? 1.0 : CONSERVATION_RATIO);
foreach ($dp as $x) $upk += depotUpkeep($x) * (isset($eco['depot_served'][(string)$x['id']]) ? 1.0 : CONSERVATION_RATIO);
foreach ($tr as $x) $upk += trainUpkeep($x, isset($eco['trains_ready'][(string)$x['id']]));
$eids = []; foreach ($ed as $e) $eids[(string)$e['id']] = true;
foreach ($eco['routes'] as $r) {
if ((int)$r['owner_id'] !== $pid || $r['status'] !== 'active') continue;
$all = true; foreach ($r['edges'] as $x) if (!isset($eids[$x])) { $all = false; break; }
if ($all && $r['edges']) { $inc += $r['income']; $upk += $r['upkeep'] + $r['fee_total']; }
}
$repl = 0;
foreach ($st as $s) $repl += stationReplacementCost($s);
foreach ($nd as $n) $repl += nodeReplacementCost($n);
foreach ($ed as $e) { $a = endpointById($d, (string)$e['from']); $b = endpointById($d, (string)$e['to']); if ($a && $b) $repl += edgeReplacementCost($e, $a, $b); }
foreach ($dp as $x) $repl += depotReplacementCost($x);
foreach ($tr as $x) $repl += trainReplacementCost($x);
$info[] = ['stations'=>$st,'nodes'=>$nd,'edges'=>$ed,'depots'=>$dp,'trains'=>$tr,'station_ids'=>$g['ids'],
'upkeep'=>$upk,'income'=>$inc,'net'=>$inc-$upk,'replacement'=>$repl,'protected'=>$g['protected']];
}
usort($info, function ($a, $b) {
$an = $a['net'] < 0; $bn = $b['net'] < 0;
if ($an && !$bn) return -1; if (!$an && $bn) return 1;
if ($an) return $a['net'] <=> $b['net'];
return $b['upkeep'] <=> $a['upkeep'];
});
$remU = array_sum(array_column($info, 'upkeep')); $remI = array_sum(array_column($info, 'income'));
$removed = 0.0; $take = [];
foreach ($info as $ci) {
if (!empty($ci['protected'])) { $remU -= $ci['upkeep']; $remI -= $ci['income']; continue; }
$net = $remI - $remU; $gap = $net < 0 ? (-$net)*FORECAST_DAYS_ON_CONFISCATE : 0;
if ($removed >= $debt + $gap) break;
$take[] = $ci; $removed += $ci['upkeep']; $remU -= $ci['upkeep']; $remI -= $ci['income'];
}
$rs = []; $rn = []; $re = []; $rd = []; $rt = [];
foreach ($take as $ci) {
foreach ($ci['stations'] as $x) $rs[$x['id']] = true;
foreach ($ci['nodes'] as $x) $rn[$x['id']] = true;
foreach ($ci['edges'] as $x) $re[$x['id']] = true;
foreach ($ci['depots'] as $x) $rd[$x['id']] = true;
foreach ($ci['trains'] as $x) $rt[$x['id']] = true;
}
$d['stations'] = array_values(array_filter($d['stations'], fn($x) => !isset($rs[$x['id']])));
$d['nodes'] = array_values(array_filter($d['nodes'], fn($x) => !isset($rn[$x['id']])));
$d['edges'] = array_values(array_filter($d['edges'], fn($x) => !isset($re[$x['id']])));
$d['depots'] = array_values(array_filter($d['depots'], fn($x) => !isset($rd[$x['id']])));
$d['trains'] = array_values(array_filter($d['trains'], fn($x) => !isset($rt[$x['id']])));
pruneOrphanNodes($d);
cacheReset();
return $take;
}
function createAuctionFromComponent(PDO $pdo, int $seller, array $ci, string $type = 'bankrupt'): int {
$repl = (int)($ci['replacement'] ?? 0);
$po = max(1, (int)ceil($repl * AUCTION_PRICE_OWNER_RATIO));
$px = max(1, (int)ceil($repl * AUCTION_PRICE_OUTSIDER_RATIO));
$pdo->prepare("INSERT INTO auctions (seller_id, debt, price, price_owner, replacement_cost, stations, edges, depots, trains, nodes, created_at, expires_at, status, type) VALUES (:s, :d, :p, :po, :rc, :st, :ed, :dp, :tr, :nd, :c, :e, 'active', :t)")
->execute([':s'=>$seller, ':d'=>(int)ceil($ci['upkeep'] ?? 0), ':p'=>$px, ':po'=>$po, ':rc'=>$repl,
':st'=>json_encode($ci['stations'] ?? [], JSON_UNESCAPED_UNICODE), ':ed'=>json_encode($ci['edges'] ?? [], JSON_UNESCAPED_UNICODE),
':dp'=>json_encode($ci['depots'] ?? [], JSON_UNESCAPED_UNICODE), ':tr'=>json_encode($ci['trains'] ?? [], JSON_UNESCAPED_UNICODE),
':nd'=>json_encode($ci['nodes'] ?? [], JSON_UNESCAPED_UNICODE), ':c'=>date('c'), ':e'=>date('c', time() + AUCTION_DURATION_SEC), ':t'=>$type]);
return (int)$pdo->lastInsertId();
}
function freezeObjects(array &$d, array $sIds, array $eIds, array $dIds, array $tIds, array $nIds, int $aid): void {
foreach (['stations'=>$sIds,'edges'=>$eIds,'depots'=>$dIds,'trains'=>$tIds,'nodes'=>$nIds] as $k => $ids) {
foreach ($d[$k] as &$o) if (in_array((string)$o['id'], $ids, true)) { $o['frozen'] = true; $o['auction_id'] = $aid; } unset($o);
}
cacheReset();
}
function unfreezeAuctionObjects(array &$d, int $aid): bool {
$ch = false;
foreach (['stations','edges','depots','trains','nodes'] as $k) {
foreach ($d[$k] as &$o) if (isset($o['auction_id']) && (int)$o['auction_id'] === $aid) { $o['frozen'] = false; unset($o['auction_id']); $ch = true; } unset($o);
}
if ($ch) cacheReset();
return $ch;
}
function collectSaleSelection(array $d, int $pid, array $sIds, array $eIds, array $dIds, array $tIds, array $nIds, bool $strict): array {
$st = []; $ed = []; $dp = []; $tr = []; $nd = []; $repl = 0;
foreach ($d['stations'] as $s) {
if (!in_array((string)$s['id'], $sIds, true)) continue;
if ((int)($s['owner_id'] ?? 0) !== $pid) { if ($strict) fail('не ваша станция', 403); continue; }
if (!empty($s['is_building']) || !empty($s['upgrading'])) { if ($strict) fail("станция «{$s['name']}»: стройка/улучшение", 400); continue; }
if (!empty($s['frozen'])) { if ($strict) fail('станция уже на продаже', 409); continue; }
$st[] = $s; $repl += stationReplacementCost($s);
}
foreach ($d['nodes'] as $n) {
if (!in_array((string)$n['id'], $nIds, true)) continue;
if ((int)($n['owner_id'] ?? 0) !== $pid) { if ($strict) fail('не ваш узел', 403); continue; }
if (!empty($n['frozen'])) { if ($strict) fail('узел уже на продаже', 409); continue; }
$nd[] = $n; $repl += nodeReplacementCost($n);
}
foreach ($d['edges'] as $e) {
if (!in_array((string)$e['id'], $eIds, true)) continue;
if ((int)($e['owner_id'] ?? 0) !== $pid) { if ($strict) fail('не ваш перегон', 403); continue; }
if (!empty($e['is_building']) || !empty($e['upgrading'])) { if ($strict) fail('перегон строится/улучшается', 400); continue; }
if (!empty($e['frozen'])) { if ($strict) fail('перегон уже на продаже', 409); continue; }
$a = endpointById($d, (string)$e['from']); $b = endpointById($d, (string)$e['to']);
$ed[] = $e; $repl += ($a && $b) ? edgeReplacementCost($e, $a, $b) : (int)ceil((float)$e['length']);
}
$dpSet = [];
foreach ($d['depots'] as $x) {
if (!in_array((string)$x['id'], $dIds, true)) continue;
if ((int)($x['owner_id'] ?? 0) !== $pid) { if ($strict) fail('не ваше депо', 403); continue; }
if (!empty($x['is_building']) || !empty($x['upgrading'])) { if ($strict) fail('депо строится/расширяется', 400); continue; }
if (!empty($x['frozen'])) { if ($strict) fail('депо уже на продаже', 409); continue; }
$dp[] = $x; $dpSet[(string)$x['id']] = true; $repl += depotReplacementCost($x);
}
foreach ($d['trains'] as $t) {
$auto = isset($dpSet[(string)($t['depot_id'] ?? '')]);
if (!in_array((string)$t['id'], $tIds, true) && !$auto) continue;
if ((int)($t['owner_id'] ?? 0) !== $pid) { if ($strict && !$auto) fail('не ваш поезд', 403); continue; }
if (!empty($t['frozen'])) continue;
if (!empty($t['is_building'])) { if ($strict) fail('поезд ещё не поставлен', 400); continue; }
$tr[] = $t; $repl += trainReplacementCost($t);
}
if ($strict && (count($st) !== count($sIds) || count($ed) !== count($eIds))) fail('часть объектов не найдена', 404);
return ['stations'=>$st,'edges'=>$ed,'depots'=>$dp,'trains'=>$tr,'nodes'=>$nd,'replacement'=>$repl];
}
function expireAuctions(PDO $pdo, array &$d, int $now): bool {
$ch = false;
$rows = $pdo->query("SELECT id, seller_id, stations, type, expires_at FROM auctions WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
$u = $pdo->prepare("UPDATE auctions SET status = 'expired' WHERE id = :id");
foreach ($rows as $r) {
if (strtotime((string)$r['expires_at']) >= $now) continue;
$pid = (int)$r['seller_id']; $nm = playerName($pid);
if ($r['type'] === 'manual') { if (unfreezeAuctionObjects($d, (int)$r['id'])) $ch = true; logEvent('auction_expired', $pid, "Лот {$nm} истёк, объекты возвращены", []); }
else { $st = json_decode((string)$r['stations'], true) ?: []; logEvent('auction_expired', $pid, "Лот {$nm} истёк: " . count($st) . " ст. удалено", []); }
$u->execute([':id'=>(int)$r['id']]);
}
return $ch;
}

// ============================================================
//  ДЕКОРАЦИЯ ДЛЯ КЛИЕНТА
// ============================================================
function decorateWorld(array $d, int $viewer): array {
$eco = economy($d); $idx = worldIndex($d); $pol = policiesAll(); $now = time();
foreach ($d['stations'] as &$s) {
$id = (string)$s['id'];
$s['served'] = isset($eco['served_st'][$id]);
$s['passed'] = isset($eco['passed_st'][$id]);
$s['routes_used'] = stationRoutesUsed($d, $id);
$s['routes_limit'] = stationRouteLimit($s);
$s['tracks'] = stationTracks($d, $id);
$s['income_percent'] = getBuildingIncomePercent((int)($s['buildings'] ?? 0));
$s['color'] = playerColor((int)($s['owner_id'] ?? 0));
$s['access_state'] = accessState($pol, $s, $viewer, 'stop');
$s['can_connect'] = accessAllowed($pol, $s, $viewer, 'connect');
$s['can_stop'] = accessAllowed($pol, $s, $viewer, 'stop');
} unset($s);
foreach ($d['nodes'] as &$n) {
$id = (string)$n['id'];
$n['served'] = isset($eco['served_nd'][$id]);
$n['edges_count'] = nodeEdgesCount($d, $id);
$n['color'] = playerColor((int)($n['owner_id'] ?? 0));
$n['can_connect'] = accessAllowed($pol, $n, $viewer, 'connect');
$n['access_state'] = accessState($pol, $n, $viewer, 'connect');
} unset($n);
foreach ($d['edges'] as &$e) {
$e['served'] = isset($eco['served_ed'][(string)$e['id']]);
$e['speed'] = edgeSpeedKmh($e);
$e['color'] = playerColor((int)($e['owner_id'] ?? 0));
$e['can_use'] = accessAllowed($pol, $e, $viewer, 'trackage');
$e['access_state'] = accessState($pol, $e, $viewer, 'trackage');
$bs = []; foreach (($e['bridges'] ?? []) as $br) $bs[] = decorateBridge($br);
$e['bridges'] = $bs;
$e['from_name'] = endpointName($d, (string)$e['from']);
$e['to_name'] = endpointName($d, (string)$e['to']);
$e['from_kind'] = (endpointById($d, (string)$e['from'])['_kind'] ?? '?');
$e['to_kind'] = (endpointById($d, (string)$e['to'])['_kind'] ?? '?');
} unset($e);
foreach ($d['depots'] as &$x) {
$x['served'] = isset($eco['depot_served'][(string)$x['id']]);
$c = 0; foreach ($d['trains'] as $t) if ((string)($t['depot_id'] ?? '') === (string)$x['id']) $c++;
$x['trains_count'] = $c; $x['free_slots'] = max(0, (int)$x['capacity'] - $c);
$x['color'] = playerColor((int)($x['owner_id'] ?? 0));
} unset($x);
foreach ($d['trains'] as &$t) {
$rid = (string)($t['route_id'] ?? ''); $st = 'idle';
if (!empty($t['is_building'])) $st = 'delivering';
elseif ((int)($t['transit_end'] ?? 0) > $now) $st = 'transit';
elseif (empty($t['depot_id'])) $st = 'orphan';
elseif ($rid !== '' && isset($eco['routes'][$rid])) $st = $eco['routes'][$rid]['status'] === 'active' ? 'running' : ($eco['routes'][$rid]['status'] === 'broken' ? 'broken' : 'idle');
$t['status'] = $st;
$t['color'] = playerColor((int)($t['owner_id'] ?? 0));
} unset($t);
foreach ($d['routes'] as &$r) {
$rid = (string)$r['id']; $info = $eco['routes'][$rid] ?? null;
$r['color'] = playerColor((int)($r['owner_id'] ?? 0)); // п.1 — цвет компании
if (!$info) { $r['status'] = 'broken'; $r['income'] = 0; continue; }
$r['status'] = $info['status']; $r['reason'] = $info['reason'];
$r['km'] = round($info['km']/1000, 3); $r['income'] = $info['income'];
$r['upkeep'] = round($info['upkeep'], 2); $r['fee'] = $info['fee_total'];
$r['speed'] = $info['speed']; $r['trains'] = $info['trains']; $r['slots'] = $info['slots'];
$r['recommended'] = $info['recommended']; $r['double_share'] = round($info['double_share'], 3);
$r['elec_share'] = round($info['elec_share'], 3); $r['min_class'] = $info['min_class'];
$r['edges'] = $info['edges']; $r['path'] = $info['path']; $r['owners'] = $info['owners'];
$r['transit_ids'] = $info['transit'];
$g = routeGeometry($d, $idx, $info);
$r['geometry'] = $g['geometry']; $r['stops_dist'] = $g['stops_dist']; $r['transit'] = $g['transit'];
} unset($r);
return $d;
}

// ============================================================
//  АВТОМАРШРУТЫ
// ============================================================
function autoRouteForPlayer(array $d, array $idx, array $pol, int $pid, array $candidates): ?array {
$score = function (string $sid) use ($idx): float {
$s = $idx['st'][$sid] ?? null; if (!$s) return 0.0;
return getBuildingIncomePercent((int)($s['buildings'] ?? 0)) * stationClassMultiplier((int)$s['class']);
};
$free = [];
foreach ($candidates as $sid) {
$sid = (string)$sid; $s = $idx['st'][$sid] ?? null; if (!$s) continue;
if (!empty($s['is_building']) || !empty($s['frozen'])) continue;
if (stationRoutesUsed($d, $sid) >= stationRouteLimit($s)) continue;
$free[] = $sid;
}
if (count($free) < 2) return null;
$start = null; $bs = -1;
foreach ($free as $sid) { $v = $score($sid); if ($v > $bs) { $bs = $v; $start = $sid; } }
if ($start === null) return null;
$path = [$start]; $used = [$start => true];
while (count($path) < MAX_ROUTE_STOPS) {
$cur = $path[count($path)-1]; $next = null; $bestLen = INF;
foreach ($free as $sid) {
if (isset($used[$sid])) continue;
$p = pathBetween($idx, $pol, $cur, $sid, $pid);
if ($p === null || !$p['steps']) continue;
if ($p['len'] < $bestLen) { $bestLen = $p['len']; $next = $sid; }
}
if ($next === null) break;
$path[] = $next; $used[$next] = true;
}
return count($path) >= 2 ? $path : null;
}
function createRouteRecord(array &$d, int $pid, string $name, array $stops, bool $loop, bool $auto): array {
$idx = worldIndex($d); $pol = policiesAll();
$info = routeCompute($idx, $pol, $stops, $loop, $pid, 0);
if (!$info['ok']) fail('Маршрут невалиден: ' . $info['reason'], 400);
$sig = $info['stops']; sort($sig); $sig = implode('|', $sig);
foreach ($d['routes'] as $r) {
if ((int)$r['owner_id'] !== $pid) continue;
$s2 = array_map('strval', $r['stations'] ?? []); sort($s2);
if (implode('|', $s2) === $sig) fail('такой маршрут уже существует', 409);
}
$owns = false;
foreach ($info['stops'] as $sid) {
$s = $idx['st'][$sid];
if ((int)$s['owner_id'] === $pid) $owns = true;
$used = stationRoutesUsed($d, $sid); $lim = stationRouteLimit($s);
if ($used >= $lim) fail("станция «{$s['name']}»: исчерпаны слоты маршрутов ({$used}/{$lim}) — повысьте класс", 400);
}
if (!$owns) fail('маршрут должен включать хотя бы одну вашу станцию', 403);
$id = newId('r');
$d['routes'][] = ['id'=>$id, 'name'=>$name, 'owner_id'=>$pid, 'stations'=>$info['stops'], 'edges'=>$info['edges'],
'loop'=>$loop, 'color'=>playerColor($pid), 'created_day'=>(int)metaGet('day_count'), 'auto'=>$auto];
cacheReset();
return ['id'=>$id, 'info'=>$info];
}

// ============================================================
//  РЕЗОЛВЕР КОНЦОВ ПЕРЕГОНА (п.3, п.6)
// ============================================================
function snapEndpoint(array $d, float $lat, float $lng): ?array {
$best = null; $bd = INF;
foreach (['stations','nodes'] as $k) foreach ($d[$k] as $o) {
$dist = haversineDistanceMeters($lat, $lng, (float)$o['lat'], (float)$o['lng']);
if ($dist <= NODE_SNAP_M && $dist < $bd) { $bd = $dist; $best = $o + ['_kind'=>$k === 'stations' ? 'station' : 'node', '_snap_dist'=>$dist]; }
}
return $best;
}
function resolveEndpoint(array $d, array $pol, int $pid, string $id, ?array $point): array {
if ($id !== '') {
$o = endpointById($d, $id);
if (!$o) fail('точка привязки не найдена: ' . $id, 404);
if (!accessAllowed($pol, $o, $pid, 'connect')) fail('«' . ($o['name'] ?? 'объект') . '»: владелец не разрешает подключаться к своей сети', 403);
return ['mode'=>'existing', 'id'=>$id, 'lat'=>(float)$o['lat'], 'lng'=>(float)$o['lng'], 'obj'=>$o, 'snapped'=>false];
}
if ($point === null) fail('нужно указать id или координаты конца перегона', 400);
$snap = snapEndpoint($d, $point['lat'], $point['lng']);
if ($snap) {
if (!accessAllowed($pol, $snap, $pid, 'connect')) fail('«' . ($snap['name'] ?? 'объект') . '»: владелец не разрешает подключаться', 403);
return ['mode'=>'existing', 'id'=>(string)$snap['id'], 'lat'=>(float)$snap['lat'], 'lng'=>(float)$snap['lng'], 'obj'=>$snap, 'snapped'=>true];
}
$z = isPointInForbiddenZone($point['lat'], $point['lng']);
if ($z) fail("Здесь нельзя строить: «{$z['name']}» — запретная зона", 400);
return ['mode'=>'new', 'id'=>'', 'lat'=>$point['lat'], 'lng'=>$point['lng'], 'obj'=>null, 'snapped'=>false];
}
function endpointPreview(array $d, array $r): array {
$o = $r['obj'];
return ['mode'=>$r['mode'], 'id'=>$r['id'], 'lat'=>$r['lat'], 'lng'=>$r['lng'], 'snapped'=>!empty($r['snapped']),
'kind'=>$o ? ($o['_kind'] ?? 'station') : 'node', 'name'=>$o ? (string)($o['name'] ?? '') : '', 'owner_id'=>$o ? (int)($o['owner_id'] ?? 0) : 0];
}
function makeNode(array &$d, int $pid, float $lat, float $lng, string $name = ''): string {
$id = newId('n');
$d['nodes'][] = ['id'=>$id, 'name'=>$name !== '' ? $name : (NODE_NAME_DEFAULT . ' ' . (playerNodesCount($d, $pid) + 1)),
'lat'=>$lat, 'lng'=>$lng, 'owner_id'=>$pid, 'built_day'=>(int)metaGet('day_count'), 'frozen'=>false,
'access'=>'inherit', 'labelOffset'=>['x'=>8,'y'=>4]];
return $id;
}

// ============================================================
//  API
// ============================================================
$action = $_GET['action'] ?? '';
$isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

if ($action === 'login' && $isPost) {
$req = readJson(); $name = trim((string)($req['name'] ?? ''));
if ($name === '' || mb_strlen($name) > 40) sendJson(['error'=>'bad name'], 400);
$s = db()->prepare("SELECT * FROM players WHERE name = :n"); $s->execute([':n'=>$name]); $p = $s->fetch(PDO::FETCH_ASSOC);
if (!$p) {
$day = (int)metaGet('day_count'); $col = freeCompanyColor();
db()->prepare("INSERT INTO players (name, money, created_at, grace_until_day, color) VALUES (:n, :m, :d, :g, :c)")
->execute([':n'=>$name, ':m'=>START_MONEY, ':d'=>date('c'), ':g'=>$day + GRACE_DAYS, ':c'=>$col]);
$s = db()->prepare("SELECT * FROM players WHERE name = :n"); $s->execute([':n'=>$name]); $p = $s->fetch(PDO::FETCH_ASSOC);
policySave((int)$p['id'], ['allow_trackage'=>0,'allow_stops'=>0,'allow_connect'=>0]);
logEvent('new_player', (int)$p['id'], "Новая компания: {$name}", ['color'=>$col]);
}
setcookie('pid', cookieSign((string)$p['id']), ['expires'=>time()+86400*30, 'path'=>'/', 'httponly'=>true, 'samesite'=>'Lax']);
$p['color'] = playerColor((int)$p['id']);
sendJson(['ok'=>true, 'player'=>$p]);
}
if ($action === 'logout') { setcookie('pid', '', ['expires'=>time()-3600, 'path'=>'/']); sendJson(['ok'=>true]); }
if ($action === 'me') { $p = currentPlayer(); if ($p) $p['color'] = playerColor((int)$p['id']); sendJson(['player'=>$p]); }
if ($action === 'bridge_types') sendJson(['types'=>bridgeTypesPublic(), 'default'=>BRIDGE_DEFAULT_TYPE]);

// ---------- НАСТРОЙКИ КОМПАНИИ (п.1, п.5, п.6) ----------
if ($action === 'company_settings') {
$me = requirePlayer(); $pid = (int)$me['id']; $pol = policiesAll();
if ($isPost) {
$req = readJson(); $cur = policyFor($pol, $pid);
foreach (['allow_trackage','allow_stops','allow_connect'] as $k) if (array_key_exists($k, $req)) $cur[$k] = !empty($req[$k]) ? 1 : 0;
if (!empty($cur['allow_trackage']) && empty($cur['allow_stops'])) { /* пути без остановок — допустимо */ }
policySave($pid, $cur);
if (isset($req['color'])) {
$c = (string)$req['color'];
if (!in_array($c, COMPANY_COLORS, true)) fail('цвет должен быть из палитры', 400);
foreach (colorsAll() as $other => $oc) if ($other !== $pid && strtolower($oc) === strtolower($c)) fail('этот цвет уже занят другой компанией', 409);
db()->prepare("UPDATE players SET color = :c WHERE id = :id")->execute([':c'=>$c, ':id'=>$pid]);
cacheReset();
logEvent('company_updated', $pid, "{$me['name']} сменил цвет компании", ['color'=>$c]);
}
$w = worldGet();
logEvent('company_updated', $pid, "{$me['name']} обновил настройки доступа", policyFor(policiesAll(), $pid));
sendJson(['ok'=>true, 'policies'=>policyFor(policiesAll(), $pid), 'color'=>playerColor($pid), 'version'=>worldSave($w['data'])]);
}
sendJson(['policies'=>policyFor($pol, $pid), 'color'=>playerColor($pid), 'palette'=>COMPANY_COLORS,
'share'=>TRACTAGE_REVENUE_SHARE, 'basis'=>TRACTAGE_SHARE_BASIS,
'used_colors'=>colorsAll(), 'companies'=>companiesPublic(worldGet()['data'])]);
}
if ($action === 'set_object_access' && $isPost) {
$me = requirePlayer(); $req = readJson();
$kind = (string)($req['kind'] ?? 'station'); $id = (string)($req['id'] ?? ''); $acc = normAccess($req['access'] ?? 'inherit');
$map = ['station'=>'stations','node'=>'nodes','edge'=>'edges','depot'=>'depots'];
if (!isset($map[$kind]) || $id === '') sendJson(['error'=>'bad request'], 400);
$k = $map[$kind];
$ver = runTx(function () use ($me, $k, $id, $acc) {
$w = worldGet(); $d = $w['data']; $i = idxOf($d[$k], $id);
if ($i === null) fail('объект не найден', 404);
if ((int)$d[$k][$i]['owner_id'] !== (int)$me['id']) fail('не ваш объект', 403);
$d[$k][$i]['access'] = $acc;
return worldSave($d);
});
sendJson(['ok'=>true, 'version'=>$ver, 'access'=>$acc]);
}

if ($action === 'load') {
$me = requirePlayer(); tickWorld(); $w = worldGet(); $d = $w['data'];
$act = db()->query("SELECT id, type, stations, edges, depots, trains, nodes FROM auctions WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
$stS = []; $edS = []; $dpS = []; $trS = []; $ndS = [];
foreach ($act as $a) {
foreach (json_decode((string)$a['stations'], true) ?: [] as $x) $stS[$x['id']] = $a['type'];
foreach (json_decode((string)$a['edges'], true) ?: [] as $x) $edS[$x['id']] = $a['type'];
foreach (json_decode((string)($a['depots'] ?? '[]'), true) ?: [] as $x) $dpS[$x['id']] = $a['type'];
foreach (json_decode((string)($a['trains'] ?? '[]'), true) ?: [] as $x) $trS[$x['id']] = $a['type'];
foreach (json_decode((string)($a['nodes'] ?? '[]'), true) ?: [] as $x) $ndS[$x['id']] = $a['type'];
}
foreach ($d['stations'] as &$x) if (isset($stS[$x['id']])) $x['auction_status'] = $stS[$x['id']]; unset($x);
foreach ($d['edges'] as &$x) if (isset($edS[$x['id']])) $x['auction_status'] = $edS[$x['id']]; unset($x);
foreach ($d['depots'] as &$x) if (isset($dpS[$x['id']])) $x['auction_status'] = $dpS[$x['id']]; unset($x);
foreach ($d['trains'] as &$x) if (isset($trS[$x['id']])) $x['auction_status'] = $trS[$x['id']]; unset($x);
foreach ($d['nodes'] as &$x) if (isset($ndS[$x['id']])) $x['auction_status'] = $ndS[$x['id']]; unset($x);
$d = decorateWorld($d, (int)$me['id']);
$last = (int)metaGet('last_tick');
sendJson(['data'=>$d, 'version'=>$w['version'], 'tick_left'=>max(0, DAY_DURATION_SEC - (time() - $last)),
'day'=>(int)metaGet('day_count'), 'now'=>time(), 'bridge_types'=>bridgeTypesPublic(),
'economy'=>playerFinancials($d, (int)$me['id']), 'companies'=>companiesPublic($d),
'my_policies'=>policyFor(policiesAll(), (int)$me['id']), 'palette'=>COMPANY_COLORS,
'constants'=>['depot_cost'=>depotCostForPlayer($d, (int)$me['id']), 'train_cost'=>TRAIN_COST, 'node_cost'=>COST_NODE,
'route_income_base'=>ROUTE_INCOME_BASE, 'trackage_share'=>TRACTAGE_REVENUE_SHARE, 'route_slots_per_class'=>ROUTE_SLOTS_PER_CLASS,
'class_limits_tracks'=>CLASS_LIMITS_TRACKS, 'grace_until_day'=>(int)($me['grace_until_day'] ?? 0), 'node_snap_m'=>NODE_SNAP_M]]);
}
if ($action === 'poll') {
$me = requirePlayer(); tickWorld();
$v = (int)db()->query("SELECT version FROM world WHERE id = 1")->fetchColumn();
$last = (int)metaGet('last_tick'); $day = (int)metaGet('day_count');
$s = db()->prepare("SELECT money, name, grace_until_day FROM players WHERE id = :id"); $s->execute([':id'=>(int)$me['id']]);
$f = $s->fetch(PDO::FETCH_ASSOC) ?: ['money'=>0,'name'=>'','grace_until_day'=>0];
$w = worldGet(); $fin = playerFinancials($w['data'], (int)$me['id']);
$grace = (int)$f['grace_until_day'] > $day;
$upk = $fin['upkeep'] * ($grace ? GRACE_UPKEEP_RATIO : 1.0); $net = $fin['income'] - $upk;
$s = db()->prepare("SELECT COUNT(*) FROM auctions WHERE seller_id = :id AND status = 'active'"); $s->execute([':id'=>(int)$me['id']]);
sendJson(['version'=>$v, 'tick_left'=>max(0, DAY_DURATION_SEC - (time() - $last)), 'day'=>$day,
'money'=>(int)$f['money'], 'name'=>$f['name'], 'color'=>playerColor((int)$me['id']),
'income_per_day'=>(int)$fin['income'], 'upkeep_per_day'=>(int)ceil($upk), 'net_per_day'=>(int)floor($net),
'tractage_in'=>(int)$fin['tractage_in'], 'tractage_out'=>(int)$fin['tractage_out'],
'next_station_cost'=>stationCostForPlayer($w['data'], (int)$me['id']), 'edge_cost_per_m'=>round(edgeCostPerMeterForPlayer($w['data'], (int)$me['id']), 4),
'next_depot_cost'=>depotCostForPlayer($w['data'], (int)$me['id']), 'train_cost'=>TRAIN_COST, 'node_cost'=>COST_NODE,
'my_auctions'=>(int)$s->fetchColumn(), 'grace_active'=>$grace,
'days_to_bankrupt'=>$net < 0 ? (int)floor((int)$f['money'] / max(0.01, -$net)) : null,
'routes_active'=>$fin['routes_active'], 'trains_running'=>$fin['trains_running'], 'now'=>time()]);
}
if ($action === 'players') {
requirePlayer(); $pol = policiesAll(); $out = [];
foreach (db()->query("SELECT id, name, money FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $r) {
$r['color'] = playerColor((int)$r['id']); $r['policies'] = policyFor($pol, (int)$r['id']); $out[] = $r;
}
sendJson(['players'=>$out]);
}
if ($action === 'rating') {
requirePlayer(); $w = worldGet(); $d = $w['data']; $rows = [];
foreach (db()->query("SELECT id, name, money FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $p) {
$pid = (int)$p['id']; $fin = playerFinancials($d, $pid);
$rows[] = ['id'=>$pid,'name'=>$p['name'],'color'=>playerColor($pid),'money'=>(int)$p['money'],
'income_per_day'=>(int)$fin['income'],'upkeep_per_day'=>(int)ceil($fin['upkeep']),'net_per_day'=>(int)floor($fin['net']),
'stations'=>playerStationsCount($d,$pid),'routes'=>$fin['routes_active'],'trains'=>$fin['trains'],
'tractage_in'=>(int)$fin['tractage_in'],'tractage_out'=>(int)$fin['tractage_out']];
}
usort($rows, fn($a,$b) => $b['net_per_day'] !== $a['net_per_day'] ? $b['net_per_day'] <=> $a['net_per_day'] : $b['money'] <=> $a['money']);
sendJson(['rating'=>$rows]);
}
if ($action === 'history') {
requirePlayer(); $cd = (int)metaGet('day_count');
$pl = db()->query("SELECT id, name FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($pl as &$p) $p['color'] = playerColor((int)$p['id']); unset($p);
$s = db()->prepare("SELECT player_id, day, income, upkeep, net FROM daily_stats WHERE day >= :d ORDER BY day ASC");
$s->execute([':d'=>$cd - 365]);
sendJson(['history'=>$s->fetchAll(PDO::FETCH_ASSOC), 'players'=>$pl, 'currentDay'=>$cd]);
}
if ($action === 'company_history') {
requirePlayer(); $cd = (int)metaGet('day_count'); $w = worldGet(); $d = $w['data'];
$pl = db()->query("SELECT id, name FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$s = db()->prepare("SELECT player_id, day, company_value, stations, edge_km, depots, trains, routes, nodes FROM company_snapshots WHERE day >= :d ORDER BY day ASC");
$s->execute([':d'=>$cd - 365]);
$by = []; $days = [];
foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) { $by[(int)$r['player_id']][(int)$r['day']] = $r; $days[(int)$r['day']] = true; }
ksort($days); $days = array_keys($days); $res = [];
foreach ($pl as $p) {
$pid = (int)$p['id']; $cv = playerCompanyValue($d, $pid);
$b = db()->prepare("SELECT money FROM players WHERE id = :id"); $b->execute([':id'=>$pid]); $bal = (int)$b->fetchColumn();
$h = [];
foreach ($days as $dd) {
$r = $by[$pid][$dd] ?? null;
$h[] = $r ? ['day'=>$dd,'company_value'=>(int)$r['company_value'],'stations'=>(int)$r['stations'],'edge_km'=>(float)$r['edge_km'],
'depots'=>(int)($r['depots'] ?? 0),'trains'=>(int)($r['trains'] ?? 0),'routes'=>(int)($r['routes'] ?? 0),'nodes'=>(int)($r['nodes'] ?? 0)]
: ['day'=>$dd,'company_value'=>null,'stations'=>null,'edge_km'=>null,'depots'=>null,'trains'=>null,'routes'=>null,'nodes'=>null];
}
$res[] = ['id'=>$pid,'name'=>$p['name'],'color'=>playerColor($pid),'company_value'=>$cv['assets']+$bal,
'stations'=>$cv['stations'],'nodes'=>$cv['nodes'],'edge_km'=>$cv['edge_km'],'depots'=>$cv['depots'],'trains'=>$cv['trains'],'balance'=>$bal,'history'=>$h];
}
sendJson(['players'=>$res, 'days'=>$days, 'currentDay'=>$cd]);
}
if ($action === 'events') { requirePlayer(); sendJson(['events'=>db()->query("SELECT id, kind, player_id, text, created_at FROM events ORDER BY id DESC LIMIT 40")->fetchAll(PDO::FETCH_ASSOC)]); }
if ($action === 'chat_get') {
requirePlayer();
$s = db()->prepare("SELECT c.id, c.player_id, c.text, c.created_at, p.name AS player_name FROM chat_messages c LEFT JOIN players p ON p.id = c.player_id ORDER BY c.id DESC LIMIT 50");
$s->execute(); $rows = array_reverse($s->fetchAll(PDO::FETCH_ASSOC));
foreach ($rows as &$r) $r['color'] = playerColor((int)$r['player_id']); unset($r);
sendJson(['messages'=>$rows]);
}
if ($action === 'chat_send' && $isPost) {
$me = requirePlayer(); $t = trim((string)(readJson()['text'] ?? ''));
if ($t === '' || mb_strlen($t) > 300) sendJson(['error'=>'bad text'], 400);
db()->prepare("INSERT INTO chat_messages (player_id, text, created_at) VALUES (:p, :t, :d)")->execute([':p'=>(int)$me['id'], ':t'=>$t, ':d'=>date('c')]);
sendJson(['ok'=>true]);
}

// ---------- АУКЦИОНЫ ----------
if ($action === 'auctions') {
$me = requirePlayer(); tickWorld(); $out = [];
foreach (db()->query("SELECT * FROM auctions WHERE status = 'active' ORDER BY expires_at ASC")->fetchAll(PDO::FETCH_ASSOC) as $r) {
$st = json_decode((string)$r['stations'], true) ?: []; $ed = json_decode((string)$r['edges'], true) ?: [];
$dp = json_decode((string)($r['depots'] ?? '[]'), true) ?: []; $tr = json_decode((string)($r['trains'] ?? '[]'), true) ?: [];
$nd = json_decode((string)($r['nodes'] ?? '[]'), true) ?: [];
$mine = (int)$r['seller_id'] === (int)$me['id'];
$prev = array_map(fn($s)=>['name'=>$s['name'] ?? '?','lat'=>$s['lat'] ?? 0,'lng'=>$s['lng'] ?? 0], array_slice($st, 0, 3));
$out[] = ['id'=>(int)$r['id'],'seller_id'=>(int)$r['seller_id'],'seller_color'=>playerColor((int)$r['seller_id']),
'debt'=>(int)$r['debt'],'price'=>(int)$r['price'],'price_owner'=>(int)$r['price_owner'],'replacement_cost'=>(int)$r['replacement_cost'],
'is_mine'=>$mine,'my_price'=>$mine ? (int)$r['price_owner'] : (int)$r['price'],
'stations_count'=>count($st),'edges_count'=>count($ed),'depots_count'=>count($dp),'trains_count'=>count($tr),'nodes_count'=>count($nd),
'stations_preview'=>$prev,'type'=>$r['type'],'expires_at'=>$r['expires_at'],'seconds_left'=>max(0, strtotime((string)$r['expires_at']) - time())];
}
sendJson(['auctions'=>$out]);
}
if ($action === 'get_sale_quote') {
$me = requirePlayer(); $q = readJson(); $w = worldGet();
$sel = collectSaleSelection($w['data'], (int)$me['id'], idList($q['station_ids'] ?? []), idList($q['edge_ids'] ?? []),
idList($q['depot_ids'] ?? []), idList($q['train_ids'] ?? []), idList($q['node_ids'] ?? []), false);
$m = max(1, $sel['replacement']);
sendJson(['min_price'=>$m, 'max_price'=>$m*5]);
}
if ($action === 'list_for_sale' && $isPost) {
$me = requirePlayer(); $q = readJson();
$sI = idList($q['station_ids'] ?? []); $eI = idList($q['edge_ids'] ?? []); $dI = idList($q['depot_ids'] ?? []);
$tI = idList($q['train_ids'] ?? []); $nI = idList($q['node_ids'] ?? []); $price = (int)($q['price'] ?? 0);
if (!$sI && !$eI && !$dI && !$tI && !$nI) sendJson(['error'=>'ничего не выбрано'], 400);
$out = runTx(function () use ($me, $sI, $eI, $dI, $tI, $nI, $price) {
$pdo = db(); $w = worldGet(); $d = $w['data'];
$sel = collectSaleSelection($d, (int)$me['id'], $sI, $eI, $dI, $tI, $nI, true);
$r = $sel['replacement']; if ($r <= 0) fail('стоимость не рассчитана', 400);
if ($price < $r || $price > $r*5) fail("цена должна быть от {$r} до " . ($r*5) . " ₽", 400);
$pdo->prepare("INSERT INTO auctions (seller_id, debt, price, price_owner, replacement_cost, stations, edges, depots, trains, nodes, created_at, expires_at, status, type) VALUES (:s, 0, :p, :po, :rc, :st, :ed, :dp, :tr, :nd, :c, :e, 'active', 'manual')")
->execute([':s'=>(int)$me['id'], ':p'=>$price, ':po'=>$price, ':rc'=>$r,
':st'=>json_encode($sel['stations'], JSON_UNESCAPED_UNICODE), ':ed'=>json_encode($sel['edges'], JSON_UNESCAPED_UNICODE),
':dp'=>json_encode($sel['depots'], JSON_UNESCAPED_UNICODE), ':tr'=>json_encode($sel['trains'], JSON_UNESCAPED_UNICODE),
':nd'=>json_encode($sel['nodes'], JSON_UNESCAPED_UNICODE), ':c'=>date('c'), ':e'=>date('c', time() + AUCTION_DURATION_SEC)]);
$aid = (int)$pdo->lastInsertId();
freezeObjects($d, array_column($sel['stations'],'id'), array_column($sel['edges'],'id'), array_column($sel['depots'],'id'), array_column($sel['trains'],'id'), array_column($sel['nodes'],'id'), $aid);
worldSave($d);
return ['ok'=>true, 'auction_id'=>$aid];
});
sendJson($out);
}
if ($action === 'cancel_sale' && $isPost) {
$me = requirePlayer(); $aid = (int)(readJson()['auction_id'] ?? 0); if ($aid <= 0) sendJson(['error'=>'bad request'], 400);
runTx(function () use ($me, $aid) {
$pdo = db();
$s = $pdo->prepare("SELECT id FROM auctions WHERE id = :id AND seller_id = :s AND status = 'active' AND type = 'manual'");
$s->execute([':id'=>$aid, ':s'=>(int)$me['id']]);
if (!$s->fetchColumn()) fail('лот не найден', 404);
$pdo->prepare("UPDATE auctions SET status = 'cancelled' WHERE id = :id")->execute([':id'=>$aid]);
$w = worldGet(); $d = $w['data'];
if (unfreezeAuctionObjects($d, $aid)) worldSave($d);
logEvent('auction_cancelled', (int)$me['id'], "{$me['name']} отменил продажу лота #{$aid}", []);
});
sendJson(['ok'=>true]);
}
if ($action === 'buy_auction' && $isPost) {
$me = requirePlayer(); $aid = (int)(readJson()['auction_id'] ?? 0); if ($aid <= 0) sendJson(['error'=>'bad request'], 400); tickWorld();
$out = runTx(function () use ($me, $aid) {
$pdo = db();
$s = $pdo->prepare("SELECT * FROM auctions WHERE id = :id AND status = 'active'"); $s->execute([':id'=>$aid]); $a = $s->fetch(PDO::FETCH_ASSOC);
if (!$a) fail('лот не найден', 404);
if (strtotime((string)$a['expires_at']) < time()) fail('лот истёк', 410);
$own = (int)$a['seller_id'] === (int)$me['id']; $type = (string)$a['type'];
if ($type === 'manual' && $own) fail('это ваш лот — используйте «Отменить»', 400);
$price = $own ? (int)$a['price_owner'] : (int)$a['price'];
$seller = (int)$a['seller_id']; $buyer = (int)$me['id'];
chargePlayer($buyer, $price, 'buy_auction', $seller, ['auction_id'=>$aid]);
if (!$own) addMoney($seller, $price, 'auction_compensation', $buyer, ['auction_id'=>$aid]);
$w = worldGet(); $d = $w['data'];
$lists = ['stations'=>json_decode((string)$a['stations'], true) ?: [], 'edges'=>json_decode((string)$a['edges'], true) ?: [],
'depots'=>json_decode((string)($a['depots'] ?? '[]'), true) ?: [], 'trains'=>json_decode((string)($a['trains'] ?? '[]'), true) ?: [],
'nodes'=>json_decode((string)($a['nodes'] ?? '[]'), true) ?: []];
foreach ($lists as $k => $items) foreach ($items as $obj) {
$i = idxOf($d[$k], (string)$obj['id']);
if ($i !== null) {
$d[$k][$i]['owner_id'] = $buyer; $d[$k][$i]['frozen'] = false; unset($d[$k][$i]['auction_id']);
if ($k === 'trains') $d[$k][$i]['route_id'] = null;
} elseif ($type === 'bankrupt') {
$obj['owner_id'] = $buyer; $obj['frozen'] = false; unset($obj['auction_id']);
if (isset($obj['health'])) { $obj['health'] = 100; $obj['health_pending'] = 0.0; }
if ($k === 'trains') $obj['route_id'] = null;
$d[$k][] = $obj;
}
}
worldSave($d);
$pdo->prepare("UPDATE auctions SET status = 'sold' WHERE id = :id")->execute([':id'=>$aid]);
logEvent('auction_sold', $buyer, "{$me['name']} " . ($own ? 'выкупил свой лот' : 'купил лот') . " за {$price} ₽ (" . count($lists['stations']) . " ст., " . count($lists['nodes']) . " узл., " . count($lists['edges']) . " пер., " . count($lists['depots']) . " депо, " . count($lists['trains']) . " поездов)", ['price'=>$price]);
return ['ok'=>true, 'price'=>$price, 'is_owner'=>$own];
});
sendJson($out);
}

// ---------- OSM ----------
if ($action === 'check_osm_station') {
requirePlayer(); $q = readJson(); $lat = (float)($q['lat'] ?? 0); $lng = (float)($q['lng'] ?? 0);
if (!is_finite($lat) || !is_finite($lng)) sendJson(['error'=>'bad coords'], 400);
sendJson(['discount'=>getOsmStationDiscount($lat, $lng) ? OSM_DISCOUNT : 0]);
}
if ($action === 'check_osm_station_buildings') {
requirePlayer(); $q = readJson(); $lat = (float)($q['lat'] ?? 0); $lng = (float)($q['lng'] ?? 0);
if (!is_finite($lat) || !is_finite($lng)) sendJson(['error'=>'bad coords'], 400);
$c = countNearbyBuildings($lat, $lng, 500);
sendJson(['discount'=>getOsmStationDiscount($lat,$lng) ? OSM_DISCOUNT : 0, 'buildings'=>$c,
'income_percent'=>getBuildingIncomePercent($c), 'forbidden_zone'=>isPointInForbiddenZone($lat,$lng)['name'] ?? null]);
}
if ($action === 'check_osm_buildings') {
requirePlayer(); $q = readJson(); $lat = (float)($q['lat'] ?? 0); $lng = (float)($q['lng'] ?? 0); $r = (float)($q['radius'] ?? 500);
if (!is_finite($lat) || !is_finite($lng)) sendJson(['error'=>'bad coords'], 400);
sendJson(['count'=>countNearbyBuildings($lat,$lng,$r), 'radius'=>$r]);
}

// ---------- СТАНЦИИ ----------
if ($action === 'build_station' && $isPost) {
$me = requirePlayer(); $q = readJson();
$name = trim((string)($q['name'] ?? '')); $lat = (float)($q['lat'] ?? 0); $lng = (float)($q['lng'] ?? 0);
if ($name === '' || mb_strlen($name) > 60) sendJson(['error'=>'bad name'], 400);
if (!is_finite($lat) || !is_finite($lng)) sendJson(['error'=>'bad coords'], 400);
$z = isPointInForbiddenZone($lat, $lng); if ($z) fail("Здесь нельзя строить: «{$z['name']}»", 400);
$osm = getOsmStationDiscount($lat, $lng); $bl = countNearbyBuildings($lat, $lng, 500);
$out = runTx(function () use ($me, $name, $lat, $lng, $osm, $bl) {
$w = worldGet(); $d = $w['data'];
$cost = stationCostForPlayer($d, (int)$me['id']); if ($osm) $cost = (int)ceil($cost * (1 - OSM_DISCOUNT));
chargePlayer((int)$me['id'], $cost, 'build_station', null, ['name'=>$name]);
$id = newId('s'); $end = time() + BUILD_TIME_STATION * DAY_DURATION_SEC;
$d['stations'][] = ['id'=>$id,'name'=>$name,'lat'=>$lat,'lng'=>$lng,'radius'=>stationRadiusForClass(1),'owner_id'=>(int)$me['id'],
'labelOffset'=>['x'=>10,'y'=>4],'color'=>null,'railway'=>'','class'=>1,'capacity'=>4,'built_day'=>(int)metaGet('day_count'),
'is_building'=>true,'build_end'=>$end,'build_type'=>'station','upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,
'frozen'=>false,'health'=>100,'buildings'=>$bl,'access'=>'inherit'];
$ver = worldSave($d);
logEvent('station_built', (int)$me['id'], "Начато строительство станции «{$name}»", ['station_id'=>$id]);
if (playerStationsCount($d, (int)$me['id']) === 10) logEvent('station_10', (int)$me['id'], "{$me['name']} достиг 10 станций!", []);
return ['ok'=>true,'version'=>$ver,'cost'=>$cost,'build_end'=>$end,'osm_discount'=>$osm,'buildings'=>$bl,'income_percent'=>getBuildingIncomePercent($bl)];
});
sendJson($out);
}
if ($action === 'move_label' && $isPost) {
$me = requirePlayer(); $q = readJson();
$id = (string)($q['id'] ?? $q['station_id'] ?? ''); $kind = (string)($q['kind'] ?? 'station');
$x = (float)($q['x'] ?? 0); $y = (float)($q['y'] ?? 0);
$map = ['station'=>'stations','node'=>'nodes','depot'=>'depots'];
if ($id === '' || !isset($map[$kind]) || !is_finite($x) || !is_finite($y)) sendJson(['error'=>'bad request'], 400);
$k = $map[$kind];
$ver = runTx(function () use ($me, $k, $id, $x, $y) {
$w = worldGet(); $d = $w['data']; $i = idxOf($d[$k], $id);
if ($i === null) fail('объект не найден', 404);
if ((int)$d[$k][$i]['owner_id'] !== (int)$me['id']) fail('не ваш объект', 403);
$d[$k][$i]['labelOffset'] = ['x'=>round($x,1), 'y'=>round($y,1)];
return worldSave($d);
});
sendJson(['ok'=>true, 'version'=>$ver]);
}
if ($action === 'rename_station' && $isPost) {
$me = requirePlayer(); $q = readJson(); $sid = (string)($q['station_id'] ?? ''); $nm = trim((string)($q['name'] ?? ''));
if ($sid === '' || $nm === '' || mb_strlen($nm) > 60) sendJson(['error'=>'bad request'], 400);
$ver = runTx(function () use ($me, $sid, $nm) {
$w = worldGet(); $d = $w['data']; $i = idxStation($d, $sid); if ($i === null) fail('станция не найдена', 404);
if ((int)$d['stations'][$i]['owner_id'] !== (int)$me['id']) fail('не ваша станция', 403);
if (!empty($d['stations'][$i]['frozen'])) fail('станция на продаже', 400);
$d['stations'][$i]['name'] = $nm; return worldSave($d);
});
sendJson(['ok'=>true, 'version'=>$ver]);
}
if ($action === 'upgrade_station' && $isPost) {
$me = requirePlayer(); $sid = (string)(readJson()['station_id'] ?? ''); if ($sid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $sid) {
$w = worldGet(); $d = $w['data']; $i = idxStation($d, $sid); if ($i === null) fail('станция не найдена', 404);
$s = $d['stations'][$i];
if ((int)$s['owner_id'] !== (int)$me['id']) fail('не ваша станция', 403);
if (!empty($s['is_building'])) fail('станция ещё строится', 400);
if (!empty($s['upgrading'])) fail('станция уже улучшается', 400);
if (!empty($s['frozen'])) fail('станция на продаже', 400);
$c = (int)$s['class']; if ($c >= MAX_CLASS) fail('максимальный класс', 400);
$cost = stationUpgradeCost($c);
chargePlayer((int)$me['id'], $cost, 'upgrade_station', null, ['station_id'=>$sid]);
$end = time() + BUILD_TIME_CLASS_UPGRADE * $c * DAY_DURATION_SEC;
$d['stations'][$i]['upgrading'] = true; $d['stations'][$i]['upgrade_type'] = 'class';
$d['stations'][$i]['upgrade_class'] = $c + 1; $d['stations'][$i]['upgrade_end'] = $end;
$ver = worldSave($d);
if (in_array($c+1, [5,10], true)) logEvent('big_build', (int)$me['id'], "{$me['name']} качает «{$s['name']}» до класса " . ($c+1), []);
return ['ok'=>true,'version'=>$ver,'cost'=>$cost,'class'=>$c+1,'build_end'=>$end,'routes_limit'=>($c+1)*ROUTE_SLOTS_PER_CLASS];
});
sendJson($out);
}
if ($action === 'repair_station' && $isPost) {
$me = requirePlayer(); $q = readJson(); $sid = (string)($q['station_id'] ?? '');
$ml = isset($q['lat']) && is_numeric($q['lat']) ? (float)$q['lat'] : null;
$mg = isset($q['lng']) && is_numeric($q['lng']) ? (float)$q['lng'] : null;
if ($sid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $sid, $ml, $mg) {
$w = worldGet(); $d = $w['data']; $i = idxStation($d, $sid); if ($i === null) fail('станция не найдена', 404);
$s = $d['stations'][$i];
if ((int)$s['owner_id'] !== (int)$me['id']) fail('не ваша станция', 403);
if (!empty($s['frozen']) || !empty($s['is_building'])) fail('станция недоступна', 400);
$h = (int)($s['health'] ?? 100); if ($h >= 100) fail('станция в идеальном состоянии', 400);
$free = ($ml !== null && $mg !== null && haversineDistanceMeters($ml, $mg, (float)$s['lat'], (float)$s['lng']) <= REPAIR_FREE_RADIUS_M);
$cost = 0;
if (!$free) { $cost = (int)ceil(stationReplacementCost($s) * REPAIR_COST_RATIO); chargePlayer((int)$me['id'], $cost, 'repair_station', null, ['station_id'=>$sid]); }
$d['stations'][$i]['health'] = 100;
logEvent('station_repaired', (int)$me['id'], "{$me['name']} починил «{$s['name']}» (" . ($free ? 'бесплатно' : "{$cost} ₽") . ")", []);
worldSave($d);
return ['ok'=>true,'cost'=>$cost,'free'=>$free,'from_health'=>$h];
});
sendJson($out);
}
if ($action === 'delete_station' && $isPost) {
$me = requirePlayer(); $sid = (string)(readJson()['station_id'] ?? ''); if ($sid === '') sendJson(['error'=>'bad request'], 400);
runTx(function () use ($me, $sid) {
$w = worldGet(); $d = $w['data']; $i = idxStation($d, $sid); if ($i === null) fail('станция не найдена', 404);
$s = $d['stations'][$i];
if ((int)$s['owner_id'] !== (int)$me['id']) fail('не ваша станция', 403);
if (!empty($s['frozen'])) fail('станция на продаже', 400);
$ne = 0; foreach ($d['edges'] as $e) if ($e['from'] === $sid || $e['to'] === $sid) $ne++;
$d['edges'] = array_values(array_filter($d['edges'], fn($e) => $e['from'] !== $sid && $e['to'] !== $sid));
foreach ($d['depots'] as &$x) if ((string)$x['station_id'] === $sid) $x['station_id'] = null; unset($x);
array_splice($d['stations'], $i, 1);
$orph = pruneOrphanNodes($d);
$ref = (int)ceil(stationReplacementCost($s) * REFUND_RATIO);
if ($ref > 0) addMoney((int)$me['id'], $ref, 'delete_station_refund', null, ['station_id'=>$sid]);
logEvent('deleted', (int)$me['id'], "{$me['name']} снёс «{$s['name']}» (возврат {$ref} ₽, перегонов: {$ne}, узлов: {$orph})", []);
worldSave($d);
});
sendJson(['ok'=>true]);
}
if ($action === 'sell_station' && $isPost) {
$me = requirePlayer(); $q = readJson();
$sid = (string)($q['station_id'] ?? ''); $price = (int)($q['price'] ?? 0); $buyer = (int)($q['buyer_id'] ?? 0);
if ($sid === '' || $price <= 0 || $buyer <= 0 || $buyer === (int)$me['id']) sendJson(['error'=>'bad request'], 400);
$ver = runTx(function () use ($me, $sid, $price, $buyer) {
$w = worldGet(); $d = $w['data']; $i = idxStation($d, $sid); if ($i === null) fail('станция не найдена', 404);
$s = $d['stations'][$i];
if ((int)$s['owner_id'] !== (int)$me['id']) fail('не ваша станция', 403);
if (!empty($s['is_building']) || !empty($s['upgrading']) || !empty($s['frozen'])) fail('станцию нельзя продать сейчас', 400);
$st = db()->prepare("SELECT id FROM players WHERE id = :id"); $st->execute([':id'=>$buyer]);
if (!$st->fetchColumn()) fail('покупатель не найден', 404);
chargePlayer($buyer, $price, 'buy', (int)$me['id'], ['station_id'=>$sid], 'у покупателя нет денег');
addMoney((int)$me['id'], $price, 'sell', $buyer, ['station_id'=>$sid]);
$d['stations'][$i]['owner_id'] = $buyer;
return worldSave($d);
});
sendJson(['ok'=>true, 'version'=>$ver]);
}

// ---------- ПЕРЕГОНЫ: СМЕТА (п.3, п.6) ----------
if ($action === 'quote_edge') {
$me = requirePlayer(); $q = readJson();
$w = worldGet(); $d = $w['data']; $pol = policiesAll(); $pid = (int)$me['id'];
$A = resolveEndpoint($d, $pol, $pid, (string)($q['from'] ?? ''), parsePoint($q['from_point'] ?? null));
$B = resolveEndpoint($d, $pol, $pid, (string)($q['to'] ?? ''), parsePoint($q['to_point'] ?? null));
$points = parsePoints($q['points'] ?? []);
$sel = is_array($q['bridges'] ?? null) ? $q['bridges'] : [];
$a = ['id'=>$A['id'], 'lat'=>$A['lat'], 'lng'=>$A['lng']];
$b = ['id'=>$B['id'], 'lat'=>$B['lat'], 'lng'=>$B['lng']];
$length = pathLength($a, $b, $points);
$perM = edgeCostPerMeterForPlayer($d, $pid);
$base = (int)ceil($length * $perM);
$cov = edgeOsmCoverage($a, $b, $points);
$disc = round(OSM_EDGE_DISCOUNT * $cov, 4);
$discounted = (int)ceil($base * (1 - $disc));
$obs = checkOsmObstacles($a, $b, $points); $obsCost = obstaclesCost($obs['obstacles']);
$cross = computeEdgeCrossings($d, $a, $b, $points);
$br = applyBridgeSelection($cross['bridges'], $sel);
$ov = applyBridgeSelection($cross['overpasses'], $sel);
$brC = crossingsCost($br); $ovC = crossingsCost($ov);
$newNodes = ($A['mode'] === 'new' ? 1 : 0) + ($B['mode'] === 'new' ? 1 : 0);
$nodeC = $newNodes * COST_NODE;
$dup = ($A['mode'] === 'existing' && $B['mode'] === 'existing')
? (bool)array_filter($d['edges'], fn($e) => ((string)$e['from'] === $A['id'] && (string)$e['to'] === $B['id']) || ((string)$e['from'] === $B['id'] && (string)$e['to'] === $A['id']))
: false;
$zone = doesEdgeCrossForbiddenZone($a, $b, $points);
$spd = EDGE_SPEED_BASE; foreach (array_merge($br, $ov) as $x) $spd = min($spd, (int)$x['speed']);
$err = null;
if ($length < MIN_EDGE_LEN_M) $err = 'перегон слишком короткий (минимум ' . MIN_EDGE_LEN_M . ' м)';
elseif ($dup) $err = 'между этими точками перегон уже есть';
elseif ($zone) $err = 'запретная зона: ' . $zone['name'];
elseif (!empty($cross['blocked'])) $err = $cross['blocked'][0]['reason'];
sendJson(['length'=>$length,'cost_base'=>$base,'cost_discount'=>$discounted,'coverage'=>$cov,'discount_pct'=>$disc,
'obstacles'=>$obs['obstacles'],'obstacles_cost'=>$obsCost,'bridges'=>$br,'overpasses'=>$ov,'blocked'=>$cross['blocked'],
'bridges_cost'=>$brC,'overpasses_cost'=>$ovC,'nodes_new'=>$newNodes,'node_cost'=>$nodeC,
'endpoints'=>['from'=>endpointPreview($d, $A), 'to'=>endpointPreview($d, $B)],
'bridge_types'=>bridgeTypesPublic(),'default_bridge_type'=>BRIDGE_DEFAULT_TYPE,'speed_preview'=>$spd,
'forbidden_zone'=>$zone['name'] ?? null,'duplicate'=>$dup,'error'=>$err,
'final_cost'=>$discounted + $obsCost + $brC + $ovC + $nodeC]);
}

// ---------- ПЕРЕГОНЫ: ПОСТРОЙКА (п.2, п.3, п.6) ----------
if ($action === 'build_edge' && $isPost) {
$me = requirePlayer(); $q = readJson();
$points = parsePoints($q['points'] ?? []);
$double = !empty($q['double']);
$sel = is_array($q['bridges'] ?? null) ? $q['bridges'] : [];
$w0 = worldGet(); $d0 = $w0['data']; $pol = policiesAll(); $pid = (int)$me['id'];
$A = resolveEndpoint($d0, $pol, $pid, (string)($q['from'] ?? ''), parsePoint($q['from_point'] ?? null));
$B = resolveEndpoint($d0, $pol, $pid, (string)($q['to'] ?? ''), parsePoint($q['to_point'] ?? null));
$a = ['id'=>$A['id'], 'lat'=>$A['lat'], 'lng'=>$A['lng']];
$b = ['id'=>$B['id'], 'lat'=>$B['lat'], 'lng'=>$B['lng']];
$len0 = pathLength($a, $b, $points);
if ($len0 < MIN_EDGE_LEN_M) fail('перегон слишком короткий (минимум ' . MIN_EDGE_LEN_M . ' м)', 400);
if ($A['mode'] === 'existing' && $B['mode'] === 'existing') {
if ($A['id'] === $B['id']) fail('концы перегона совпадают', 400);
foreach ($d0['edges'] as $e) if (((string)$e['from'] === $A['id'] && (string)$e['to'] === $B['id']) || ((string)$e['from'] === $B['id'] && (string)$e['to'] === $A['id'])) fail('перегон уже существует', 409);
}
$z = doesEdgeCrossForbiddenZone($a, $b, $points); if ($z) fail("Перегон пересекает запретную зону: «{$z['name']}»", 400);
$cov = edgeOsmCoverage($a, $b, $points); $disc = round(OSM_EDGE_DISCOUNT * $cov, 4);
$obs = checkOsmObstacles($a, $b, $points); $obsCost = obstaclesCost($obs['obstacles']);
$cross = computeEdgeCrossings($d0, $a, $b, $points);
if (!empty($cross['blocked'])) fail('Строить нельзя: ' . $cross['blocked'][0]['reason'], 400);
$structures = applyBridgeSelection(array_merge($cross['bridges'], $cross['overpasses']), $sel);
$structCost = crossingsCost($structures);
$out = runTx(function () use ($me, $A, $B, $points, $double, $disc, $cov, $obsCost, $structCost, $structures) {
$w = worldGet(); $d = $w['data']; $pid = (int)$me['id']; $pol = policiesAll();
$resolveId = function (array $R) use (&$d, $pid, $pol): string {
if ($R['mode'] === 'existing') {
$o = endpointById($d, $R['id']);
if (!$o) fail('точка привязки исчезла', 409);
if (!accessAllowed($pol, $o, $pid, 'connect')) fail('владелец «' . ($o['name'] ?? '') . '» не разрешает подключение', 403);
if (!empty($o['frozen'])) fail('«' . ($o['name'] ?? '') . '» выставлен на продажу', 400);
return (string)$o['id'];
}
return makeNode($d, $pid, $R['lat'], $R['lng']);
};
$newCount = ($A['mode'] === 'new' ? 1 : 0) + ($B['mode'] === 'new' ? 1 : 0);
$fid = $resolveId($A); $tid = $resolveId($B);
if ($fid === $tid) fail('концы перегона совпали после привязки', 409);
foreach ($d['edges'] as $e) if (((string)$e['from'] === $fid && (string)$e['to'] === $tid) || ((string)$e['from'] === $tid && (string)$e['to'] === $fid)) fail('перегон уже существует', 409);
$ea = endpointById($d, $fid); $eb = endpointById($d, $tid);
$length = pathLength($ea, $eb, $points);
$perM = edgeCostPerMeterForPlayer($d, $pid) * (1 - $disc);
$cost = (int)ceil($length * $perM * ($double ? 2 : 1)) + $obsCost + $structCost + $newCount * COST_NODE;
chargePlayer($pid, $cost, 'build_edge', null, ['from'=>$fid,'to'=>$tid,'length'=>$length,'nodes'=>$newCount]);
$id = newId('e');
$bt = max(30, ($length/1000) * BUILD_TIME_EDGE_PER_KM * DAY_DURATION_SEC);
$end = time() + (int)$bt;
// п.2 — никаких проверок класса на число путей
$d['edges'][] = ['id'=>$id,'from'=>$fid,'to'=>$tid,'length'=>$length,'minutes'=>10,'double'=>$double,'electric'=>false,
'built_day'=>(int)metaGet('day_count'),'points'=>$points,'is_building'=>true,'build_end'=>$end,'build_type'=>'edge',
'upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'frozen'=>false,'owner_id'=>$pid,'access'=>'inherit',
'bridges'=>$structures,'bridges_v'=>1];
$ver = worldSave($d);
$nmA = endpointName($d, $fid); $nmB = endpointName($d, $tid);
if ($length >= LONG_EDGE_M) logEvent('long_edge', $pid, "{$me['name']} начал перегон {$length} м: {$nmA} → {$nmB}", []);
$nb = 0; $no = 0; foreach ($structures as $s) { if ($s['kind'] === 'rail') $no++; else $nb++; }
if ($nb + $no > 0) logEvent('bridges_built', $pid, "{$me['name']} строит {$nb} мост(ов) и {$no} путепровод(ов)", ['edge_id'=>$id]);
if ($newCount > 0) logEvent('branch_built', $pid, "{$me['name']} продлевает ветку в поле ({$newCount} нов. узл.)", ['edge_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'cost'=>$cost,'length'=>$length,'build_end'=>$end,'from'=>$fid,'to'=>$tid,
'nodes_new'=>$newCount,'bridges'=>$nb,'overpasses'=>$no,'structures'=>$structures];
});
sendJson($out);
}

// ---------- ВРЕЗКА СТАНЦИИ В ПЕРЕГОН (п.4) ----------
if ($action === 'split_edge' && $isPost) {
$me = requirePlayer(); $q = readJson();
$eid = (string)($q['edge_id'] ?? ''); $name = trim((string)($q['name'] ?? ''));
$lat = (float)($q['lat'] ?? 0); $lng = (float)($q['lng'] ?? 0);
if ($eid === '' || !is_finite($lat) || !is_finite($lng)) sendJson(['error'=>'bad request'], 400);
$w0 = worldGet(); $e0 = edgeById($w0['data'], $eid);
if (!$e0) sendJson(['error'=>'перегон не найден'], 404);
$pl0 = edgePolyline($w0['data'], $e0);
if (count($pl0) < 2) fail('геометрия перегона недоступна', 400);
$pr = projectOnPolyline($pl0, $lat, $lng);
if ($pr['off'] > 150) fail('клик слишком далеко от перегона (' . round($pr['off']) . ' м)', 400);
if ($pr['dist'] < MIN_SPLIT_DIST_M || $pr['total'] - $pr['dist'] < MIN_SPLIT_DIST_M) fail('врезка слишком близко к концу перегона (минимум ' . MIN_SPLIT_DIST_M . ' м)', 400);
$z = isPointInForbiddenZone($pr['lat'], $pr['lng']); if ($z) fail("Здесь нельзя строить: «{$z['name']}»", 400);
$osm = getOsmStationDiscount($pr['lat'], $pr['lng']); $bl = countNearbyBuildings($pr['lat'], $pr['lng'], 500);
$out = runTx(function () use ($me, $eid, $name, $pr, $osm, $bl) {
$w = worldGet(); $d = $w['data']; $pol = policiesAll(); $pid = (int)$me['id'];
$i = idxEdge($d, $eid); if ($i === null) fail('перегон не найден', 404);
$e = $d['edges'][$i];
if (!empty($e['is_building'])) fail('перегон ещё строится', 400);
if (!empty($e['upgrading'])) fail('перегон улучшается', 400);
if (!empty($e['frozen'])) fail('перегон на продаже', 400);
if ((int)$e['owner_id'] !== $pid && !accessAllowed($pol, $e, $pid, 'connect')) fail('владелец перегона не разрешает врезку станции', 403);
$ea = endpointById($d, (string)$e['from']); $eb = endpointById($d, (string)$e['to']);
if (!$ea || !$eb) fail('концы перегона не найдены', 404);
$pts = edgePolyline($d, $e); $n = count($pts);
$seg = (int)$pr['seg'];
$S = ['lat'=>round((float)$pr['lat'], 7), 'lng'=>round((float)$pr['lng'], 7)];
$near = snapEndpoint($d, $S['lat'], $S['lng']);
if ($near) fail('рядом уже есть «' . ($near['name'] ?? 'объект') . '» — стройте перегон к нему', 409);
$cost = stationCostForPlayer($d, $pid); if ($osm) $cost = (int)ceil($cost * (1 - OSM_DISCOUNT));
chargePlayer($pid, $cost, 'split_edge_station', null, ['edge_id'=>$eid]);
$sid = newId('s'); $end = time() + BUILD_TIME_STATION * DAY_DURATION_SEC;
$nm = $name !== '' ? $name : ('Станция ' . (playerStationsCount($d, $pid) + 1));
$d['stations'][] = ['id'=>$sid,'name'=>$nm,'lat'=>$S['lat'],'lng'=>$S['lng'],'radius'=>stationRadiusForClass(1),'owner_id'=>$pid,
'labelOffset'=>['x'=>10,'y'=>4],'color'=>null,'railway'=>'','class'=>1,'capacity'=>4,'built_day'=>(int)metaGet('day_count'),
'is_building'=>true,'build_end'=>$end,'build_type'=>'station','upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,
'frozen'=>false,'health'=>100,'buildings'=>$bl,'access'=>'inherit'];
$ptsA = array_slice($pts, 0, $seg + 1); $ptsA[] = [$S['lat'], $S['lng']];
$ptsB = [[$S['lat'], $S['lng']]]; for ($k = $seg + 1; $k < $n; $k++) $ptsB[] = $pts[$k];
$intA = array_slice($pts, 1, max(0, $seg));
$intB = array_slice($pts, $seg + 1, max(0, $n - $seg - 2));
$mk = function (array $from, array $to, array $interior) use ($e, $pid): array {
$a = ['lat'=>$from[0], 'lng'=>$from[1]]; $b = ['lat'=>$to[0], 'lng'=>$to[1]];
$pp = []; foreach ($interior as $x) $pp[] = ['lat'=>$x[0], 'lng'=>$x[1]];
return ['id'=>newId('e'),'from'=>'','to'=>'','length'=>pathLength($a, $b, $pp),'minutes'=>10,
'double'=>!empty($e['double']),'electric'=>!empty($e['electric']),'built_day'=>(int)($e['built_day'] ?? 1),
'points'=>$pp,'is_building'=>false,'build_end'=>0,'build_type'=>null,'upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,
'frozen'=>false,'owner_id'=>(int)($e['owner_id'] ?? $pid),'access'=>normAccess($e['access'] ?? 'inherit'),'bridges'=>[],'bridges_v'=>1];
};
$eA = $mk($ptsA[0], $ptsA[count($ptsA)-1], $intA);
$eB = $mk($ptsB[0], $ptsB[count($ptsB)-1], $intB);
$eA['from'] = (string)$e['from']; $eA['to'] = $sid;
$eB['from'] = $sid; $eB['to'] = (string)$e['to'];
$tr = trackBuild(['lat'=>$pts[0][0],'lng'=>$pts[0][1]], ['lat'=>$pts[$n-1][0],'lng'=>$pts[$n-1][1]], array_map(fn($x)=>['lat'=>$x[0],'lng'=>$x[1]], array_slice($pts, 1, $n-2)));
foreach (($e['bridges'] ?? []) as $br) {
$p2 = projectOnPolyline($pts, (float)$br['lat'], (float)$br['lng']);
if ($p2['dist'] <= $pr['dist']) $eA['bridges'][] = $br; else $eB['bridges'][] = $br;
}
array_splice($d['edges'], $i, 1);
$d['edges'][] = $eA; $d['edges'][] = $eB;
$ver = worldSave($d);
logEvent('station_inserted', $pid, "{$me['name']} врезает станцию «{$nm}» в перегон {$e['from']}→{$e['to']}", ['station_id'=>$sid,'edge_a'=>$eA['id'],'edge_b'=>$eB['id']]);
return ['ok'=>true,'version'=>$ver,'station_id'=>$sid,'edge_a'=>$eA['id'],'edge_b'=>$eB['id'],
'cost'=>$cost,'build_end'=>$end,'lat'=>$S['lat'],'lng'=>$S['lng'],'buildings'=>$bl,
'income_percent'=>getBuildingIncomePercent($bl),'len_a'=>$eA['length'],'len_b'=>$eB['length']];
});
sendJson($out);
}
function loadEdgeForUpgrade(array $d, string $eid, array $me): array {
$i = idxEdge($d, $eid); if ($i === null) fail('перегон не найден', 404);
$e = $d['edges'][$i];
if (!empty($e['is_building'])) fail('перегон ещё строится', 400);
if (!empty($e['upgrading'])) fail('перегон уже улучшается', 400);
if (!empty($e['frozen'])) fail('перегон на продаже', 400);
if ((int)($e['owner_id'] ?? 0) !== (int)$me['id']) fail('не ваш перегон', 403);
$a = endpointById($d, (string)$e['from']); $b = endpointById($d, (string)$e['to']);
if (!$a || !$b) fail('концы не найдены', 404);
return [$i, $e, $a, $b];
}
if ($action === 'upgrade_edge_double' && $isPost) {
$me = requirePlayer(); $eid = (string)(readJson()['edge_id'] ?? ''); if ($eid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $eid) {
$w = worldGet(); $d = $w['data']; [$i, $e, $a, $b] = loadEdgeForUpgrade($d, $eid, $me);
if (!empty($e['double'])) fail('уже двухпутный', 409);
// п.2 — класс станции больше не ограничивает пути
$len = (float)$e['length']; $cost = (int)ceil($len * COST_UPGRADE_DOUBLE_PER_M);
chargePlayer((int)$me['id'], $cost, 'upgrade_edge_double', null, ['edge_id'=>$eid]);
$end = time() + BUILD_TIME_UPGRADE_DOUBLE * DAY_DURATION_SEC;
$d['edges'][$i]['upgrading'] = true; $d['edges'][$i]['upgrade_type'] = 'double'; $d['edges'][$i]['upgrade_end'] = $end;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'build_end'=>$end];
});
sendJson($out);
}
if ($action === 'upgrade_edge_electric' && $isPost) {
$me = requirePlayer(); $eid = (string)(readJson()['edge_id'] ?? ''); if ($eid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $eid) {
$w = worldGet(); $d = $w['data']; [$i, $e, $a, $b] = loadEdgeForUpgrade($d, $eid, $me);
if (!empty($e['electric'])) fail('уже электрифицирован', 409);
$len = (float)$e['length']; $cost = (int)ceil($len * COST_UPGRADE_ELECTRIFY_PER_M);
chargePlayer((int)$me['id'], $cost, 'upgrade_edge_electric', null, ['edge_id'=>$eid]);
$end = time() + BUILD_TIME_UPGRADE_ELECTRIC * DAY_DURATION_SEC;
$d['edges'][$i]['upgrading'] = true; $d['edges'][$i]['upgrade_type'] = 'electric'; $d['edges'][$i]['upgrade_end'] = $end;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'build_end'=>$end];
});
sendJson($out);
}
if ($action === 'upgrade_bridge' && $isPost) {
$me = requirePlayer(); $q = readJson();
$eid = (string)($q['edge_id'] ?? ''); $bid = (string)($q['bridge_id'] ?? ''); $type = (string)($q['type'] ?? '');
if ($eid === '' || $bid === '' || $type === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $eid, $bid, $type) {
$w = worldGet(); $d = $w['data']; [$i, $e, $a, $b] = loadEdgeForUpgrade($d, $eid, $me);
$bi = null; foreach (($e['bridges'] ?? []) as $k => $br) if ((string)$br['id'] === $bid) { $bi = $k; break; }
if ($bi === null) fail('сооружение не найдено', 404);
$br = $e['bridges'][$bi]; $kind = (string)$br['kind'];
$nt = normalizeBridgeType($kind, $type);
if (bridgeSpeed($nt) <= bridgeSpeed((string)$br['type'])) fail('можно только повысить класс', 400);
$diff = max(0, bridgeCost($kind, $nt) - bridgeCost($kind, (string)$br['type']));
chargePlayer((int)$me['id'], $diff, 'upgrade_bridge', null, ['edge_id'=>$eid,'bridge_id'=>$bid]);
$d['edges'][$i]['bridges'][$bi]['type'] = $nt;
$ver = worldSave($d);
logEvent('bridge_upgraded', (int)$me['id'], "{$me['name']} заменил «{$br['name']}»: " . bridgeLabel($nt) . " (" . bridgeSpeed($nt) . " км/ч)", []);
return ['ok'=>true,'version'=>$ver,'cost'=>$diff,'type'=>$nt,'speed'=>bridgeSpeed($nt)];
});
sendJson($out);
}
if ($action === 'delete_edge' && $isPost) {
$me = requirePlayer(); $eid = (string)(readJson()['edge_id'] ?? ''); if ($eid === '') sendJson(['error'=>'bad request'], 400);
runTx(function () use ($me, $eid) {
$w = worldGet(); $d = $w['data']; $i = idxEdge($d, $eid); if ($i === null) fail('перегон не найден', 404);
$e = $d['edges'][$i];
if ((int)($e['owner_id'] ?? 0) !== (int)$me['id']) fail('не ваш перегон', 403);
if (!empty($e['frozen'])) fail('перегон на продаже', 400);
$a = endpointById($d, (string)$e['from']); $b = endpointById($d, (string)$e['to']);
$ref = (int)ceil((($a && $b) ? edgeReplacementCost($e, $a, $b) : (int)$e['length']) * REFUND_RATIO);
array_splice($d['edges'], $i, 1);
$orph = pruneOrphanNodes($d);
if ($ref > 0) addMoney((int)$me['id'], $ref, 'delete_edge_refund', null, ['edge_id'=>$eid]);
logEvent('deleted', (int)$me['id'], "{$me['name']} снёс перегон (возврат {$ref} ₽, узлов убрано: {$orph})", []);
worldSave($d);
});
sendJson(['ok'=>true]);
}

// ---------- УЗЛЫ (п.3) ----------
if ($action === 'rename_node' && $isPost) {
$me = requirePlayer(); $q = readJson(); $nid = (string)($q['node_id'] ?? ''); $nm = trim((string)($q['name'] ?? ''));
if ($nid === '' || $nm === '' || mb_strlen($nm) > 40) sendJson(['error'=>'bad request'], 400);
$ver = runTx(function () use ($me, $nid, $nm) {
$w = worldGet(); $d = $w['data']; $i = idxNode($d, $nid); if ($i === null) fail('узел не найден', 404);
if ((int)$d['nodes'][$i]['owner_id'] !== (int)$me['id']) fail('не ваш узел', 403);
$d['nodes'][$i]['name'] = $nm; return worldSave($d);
});
sendJson(['ok'=>true, 'version'=>$ver]);
}
if ($action === 'delete_node' && $isPost) {
$me = requirePlayer(); $nid = (string)(readJson()['node_id'] ?? ''); if ($nid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $nid) {
$w = worldGet(); $d = $w['data']; $i = idxNode($d, $nid); if ($i === null) fail('узел не найден', 404);
$n = $d['nodes'][$i];
if ((int)$n['owner_id'] !== (int)$me['id']) fail('не ваш узел', 403);
if (!empty($n['frozen'])) fail('узел на продаже', 400);
$ne = 0; foreach ($d['edges'] as $e) if ($e['from'] === $nid || $e['to'] === $nid) $ne++;
$d['edges'] = array_values(array_filter($d['edges'], fn($e) => $e['from'] !== $nid && $e['to'] !== $nid));
array_splice($d['nodes'], $i, 1);
pruneOrphanNodes($d);
$ref = (int)ceil(COST_NODE * REFUND_RATIO);
if ($ref > 0) addMoney((int)$me['id'], $ref, 'delete_node_refund', null, ['node_id'=>$nid]);
logEvent('deleted', (int)$me['id'], "{$me['name']} убрал узел «{$n['name']}» (перегонов: {$ne})", []);
return ['ok'=>true,'version'=>worldSave($d),'refund'=>$ref,'edges_deleted'=>$ne];
});
sendJson($out);
}
if ($action === 'convert_node' && $isPost) {
$me = requirePlayer(); $q = readJson();
$nid = (string)($q['node_id'] ?? ''); $nm = trim((string)($q['name'] ?? ''));
if ($nid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $nid, $nm) {
$w = worldGet(); $d = $w['data']; $i = idxNode($d, $nid); if ($i === null) fail('узел не найден', 404);
$n = $d['nodes'][$i]; $pid = (int)$me['id'];
if ((int)$n['owner_id'] !== $pid) fail('не ваш узел', 403);
if (!empty($n['frozen'])) fail('узел на продаже', 400);
$lat = (float)$n['lat']; $lng = (float)$n['lng'];
$z = isPointInForbiddenZone($lat, $lng); if ($z) fail("Здесь нельзя строить: «{$z['name']}»", 400);
$osm = getOsmStationDiscount($lat, $lng); $bl = countNearbyBuildings($lat, $lng, 500);
$cost = stationCostForPlayer($d, $pid); if ($osm) $cost = (int)ceil($cost * (1 - OSM_DISCOUNT));
chargePlayer($pid, $cost, 'convert_node', null, ['node_id'=>$nid]);
$sid = newId('s'); $end = time() + BUILD_TIME_STATION * DAY_DURATION_SEC;
$name = $nm !== '' ? $nm : ('Станция ' . (playerStationsCount($d, $pid) + 1));
$d['stations'][] = ['id'=>$sid,'name'=>$name,'lat'=>$lat,'lng'=>$lng,'radius'=>stationRadiusForClass(1),'owner_id'=>$pid,
'labelOffset'=>$n['labelOffset'] ?? ['x'=>10,'y'=>4],'color'=>null,'railway'=>'','class'=>1,'capacity'=>4,'built_day'=>(int)metaGet('day_count'),
'is_building'=>true,'build_end'=>$end,'build_type'=>'station','upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,
'frozen'=>false,'health'=>100,'buildings'=>$bl,'access'=>normAccess($n['access'] ?? 'inherit')];
foreach ($d['edges'] as &$e) { if ((string)$e['from'] === $nid) $e['from'] = $sid; if ((string)$e['to'] === $nid) $e['to'] = $sid; } unset($e);
array_splice($d['nodes'], $i, 1);
$ver = worldSave($d);
logEvent('station_inserted', $pid, "{$me['name']} превратил узел в станцию «{$name}»", ['station_id'=>$sid]);
return ['ok'=>true,'version'=>$ver,'station_id'=>$sid,'cost'=>$cost,'build_end'=>$end,'buildings'=>$bl,'income_percent'=>getBuildingIncomePercent($bl)];
});
sendJson($out);
}

// ---------- МАРШРУТЫ ----------
if ($action === 'quote_route') {
$me = requirePlayer(); $q = readJson();
$stops = idList($q['stations'] ?? []); $loop = !empty($q['loop']); $tr = max(0, (int)($q['trains'] ?? 0));
$w = worldGet(); $d = $w['data']; $idx = worldIndex($d); $pol = policiesAll(); $pid = (int)$me['id'];
$info = routeCompute($idx, $pol, $stops, $loop, $pid, $tr);
$prev = [];
for ($n = 0; $n <= max(1, $info['slots']); $n++) {
$p = routeCompute($idx, $pol, $stops, $loop, $pid, $n);
$prev[] = ['trains'=>$n,'income'=>$p['income'],'upkeep'=>round($p['upkeep'],2),'fee'=>$p['fee_total']];
}
$names = []; $warn = null;
foreach ($info['stops'] as $sid) {
$s = $idx['st'][$sid] ?? null;
$used = stationRoutesUsed($d, $sid); $lim = $s ? stationRouteLimit($s) : 1;
$names[] = ['id'=>$sid,'name'=>$s['name'] ?? '?','buildings'=>(int)($s['buildings'] ?? 0),
'percent'=>getBuildingIncomePercent((int)($s['buildings'] ?? 0)),'class'=>(int)($s['class'] ?? 1),
'owner_id'=>(int)($s['owner_id'] ?? 0),'color'=>playerColor((int)($s['owner_id'] ?? 0)),
'routes_used'=>$used,'routes_limit'=>$lim,'can_stop'=>$s ? accessAllowed($pol, $s, $pid, 'stop') : false];
if ($s && $used >= $lim && $warn === null) $warn = "станция «{$s['name']}»: слоты маршрутов исчерпаны ({$used}/{$lim})";
}
$g = routeGeometry($d, $idx, $info);
sendJson(['ok'=>$info['ok'],'reason'=>$info['reason'],'info'=>$info,'stops'=>$names,'income_by_trains'=>$prev,
'geometry'=>$g['geometry'],'stops_dist'=>$g['stops_dist'],'transit'=>$g['transit'],'slot_warning'=>$warn,
'owners'=>$info['owners'],'trackage_share'=>TRACTAGE_REVENUE_SHARE]);
}
if ($action === 'create_route' && $isPost) {
$me = requirePlayer(); $q = readJson();
$nm = trim((string)($q['name'] ?? '')); $stops = idList($q['stations'] ?? []); $loop = !empty($q['loop']);
if (count($stops) < 2) sendJson(['error'=>'нужно минимум 2 станции'], 400);
if (count($stops) > MAX_ROUTE_STOPS) sendJson(['error'=>'слишком много остановок (макс ' . MAX_ROUTE_STOPS . ')'], 400);
if (mb_strlen($nm) > 60) sendJson(['error'=>'слишком длинное название'], 400);
$out = runTx(function () use ($me, $nm, $stops, $loop) {
$w = worldGet(); $d = $w['data']; $pid = (int)$me['id'];
if ($nm === '') $nm = 'Маршрут ' . (playerRoutesCount($d, $pid) + 1);
$res = createRouteRecord($d, $pid, $nm, $stops, $loop, false);
$ver = worldSave($d);
logEvent('route_created', $pid, "{$me['name']} открыл маршрут «{$nm}» (" . count($res['info']['stops']) . " ост., " . round($res['info']['km']/1000, 2) . " км)", ['route_id'=>$res['id']]);
return ['ok'=>true,'version'=>$ver,'route_id'=>$res['id'],'info'=>$res['info'],'color'=>playerColor($pid)];
});
sendJson($out);
}
if ($action === 'rename_route' && $isPost) {
$me = requirePlayer(); $q = readJson(); $rid = (string)($q['route_id'] ?? ''); $nm = trim((string)($q['name'] ?? ''));
if ($rid === '' || $nm === '' || mb_strlen($nm) > 60) sendJson(['error'=>'bad request'], 400);
$ver = runTx(function () use ($me, $rid, $nm) {
$w = worldGet(); $d = $w['data']; $i = idxRoute($d, $rid); if ($i === null) fail('маршрут не найден', 404);
if ((int)$d['routes'][$i]['owner_id'] !== (int)$me['id']) fail('не ваш маршрут', 403);
$d['routes'][$i]['name'] = $nm; return worldSave($d);
});
sendJson(['ok'=>true, 'version'=>$ver]);
}
if ($action === 'delete_route' && $isPost) {
$me = requirePlayer(); $rid = (string)(readJson()['route_id'] ?? ''); if ($rid === '') sendJson(['error'=>'bad request'], 400);
runTx(function () use ($me, $rid) {
$w = worldGet(); $d = $w['data']; $i = idxRoute($d, $rid); if ($i === null) fail('маршрут не найден', 404);
$r = $d['routes'][$i];
if ((int)$r['owner_id'] !== (int)$me['id']) fail('не ваш маршрут', 403);
foreach ($d['trains'] as &$t) if ((string)($t['route_id'] ?? '') === $rid) $t['route_id'] = null; unset($t);
array_splice($d['routes'], $i, 1);
logEvent('route_closed', (int)$me['id'], "{$me['name']} закрыл маршрут «{$r['name']}»", []);
worldSave($d);
});
sendJson(['ok'=>true]);
}
if ($action === 'auto_routes' && $isPost) {
$me = requirePlayer();
$out = runTx(function () use ($me) {
$w = worldGet(); $d = $w['data']; $pid = (int)$me['id'];
$idx = worldIndex($d); $pol = policiesAll();
$mine = []; foreach ($d['stations'] as $s) if ((int)$s['owner_id'] === $pid) $mine[] = (string)$s['id'];
$created = 0; $guard = 0;
while ($guard++ < 20) {
$path = autoRouteForPlayer($d, $idx, $pol, $pid, $mine);
if (!$path) break;
try { createRouteRecord($d, $pid, 'Авто ' . ($created + 1), $path, false, true); $created++; }
catch (Throwable $e) { break; }
$idx = worldIndex($d);
}
if ($created > 0) { worldSave($d); logEvent('route_created', $pid, "{$me['name']} открыл {$created} автомаршрут(ов)", []); }
return ['ok'=>true,'created'=>$created];
});
sendJson($out);
}

// ---------- ДЕПО ----------
if ($action === 'build_depot' && $isPost) {
$me = requirePlayer(); $q = readJson();
$nm = trim((string)($q['name'] ?? '')); $lat = (float)($q['lat'] ?? 0); $lng = (float)($q['lng'] ?? 0); $sid = (string)($q['station_id'] ?? '');
if (!is_finite($lat) || !is_finite($lng)) sendJson(['error'=>'bad coords'], 400);
$z = isPointInForbiddenZone($lat, $lng); if ($z) fail("Здесь нельзя строить: «{$z['name']}»", 400);
$out = runTx(function () use ($me, $nm, $lat, $lng, $sid) {
$w = worldGet(); $d = $w['data']; $pid = (int)$me['id']; $pol = policiesAll();
$s = null;
if ($sid !== '') { $s = stationById($d, $sid); if (!$s) fail('станция не найдена', 404); }
else {
$bd = INF;
foreach ($d['stations'] as $x) {
if (!empty($x['is_building'])) continue;
if ((int)$x['owner_id'] !== $pid && !accessAllowed($pol, $x, $pid, 'connect')) continue;
$dd = haversineDistanceMeters($lat, $lng, (float)$x['lat'], (float)$x['lng']);
if ($dd < $bd) { $bd = $dd; $s = $x; }
}
}
if (!$s) fail('рядом нет доступной станции', 400);
if ((int)$s['owner_id'] !== $pid && !accessAllowed($pol, $s, $pid, 'connect')) fail('владелец станции не разрешает подключение', 403);
$dist = haversineDistanceMeters($lat, $lng, (float)$s['lat'], (float)$s['lng']);
if ($dist > DEPOT_MAX_STATION_DIST_M) fail('депо должно быть не дальше ' . DEPOT_MAX_STATION_DIST_M . ' м (сейчас ' . round($dist) . ' м)', 400);
$cost = depotCostForPlayer($d, $pid);
chargePlayer($pid, $cost, 'build_depot', null, ['name'=>$nm]);
$id = newId('d'); $end = time() + BUILD_TIME_DEPOT * DAY_DURATION_SEC;
if ($nm === '') $nm = 'Депо ' . (playerDepotsCount($d, $pid) + 1);
$d['depots'][] = ['id'=>$id,'name'=>$nm,'lat'=>$lat,'lng'=>$lng,'station_id'=>(string)$s['id'],'owner_id'=>$pid,
'level'=>1,'capacity'=>depotCapacityForLevel(1),'built_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,
'upgrading'=>false,'upgrade_end'=>0,'upgrade_level'=>0,'frozen'=>false,'health'=>100,'access'=>'inherit','labelOffset'=>['x'=>10,'y'=>4]];
$ver = worldSave($d);
logEvent('depot_built', $pid, "{$me['name']} строит депо «{$nm}» у «{$s['name']}»", ['depot_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'depot_id'=>$id,'cost'=>$cost,'build_end'=>$end,'station'=>$s['name'],'distance'=>round($dist)];
});
sendJson($out);
}
if ($action === 'upgrade_depot' && $isPost) {
$me = requirePlayer(); $did = (string)(readJson()['depot_id'] ?? ''); if ($did === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $did) {
$w = worldGet(); $d = $w['data']; $i = idxDepot($d, $did); if ($i === null) fail('депо не найдено', 404);
$x = $d['depots'][$i];
if ((int)$x['owner_id'] !== (int)$me['id']) fail('не ваше депо', 403);
if (!empty($x['is_building'])) fail('депо ещё строится', 400);
if (!empty($x['upgrading'])) fail('депо уже расширяется', 400);
if (!empty($x['frozen'])) fail('депо на продаже', 400);
$l = (int)$x['level']; if ($l >= DEPOT_MAX_LEVEL) fail('максимальный уровень', 400);
$cost = DEPOT_UPGRADE_COST * $l;
chargePlayer((int)$me['id'], $cost, 'upgrade_depot', null, ['depot_id'=>$did]);
$end = time() + BUILD_TIME_DEPOT_UPGRADE * DAY_DURATION_SEC;
$d['depots'][$i]['upgrading'] = true; $d['depots'][$i]['upgrade_end'] = $end; $d['depots'][$i]['upgrade_level'] = $l + 1;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'level'=>$l+1,'capacity'=>depotCapacityForLevel($l+1),'build_end'=>$end];
});
sendJson($out);
}
if ($action === 'repair_depot' && $isPost) {
$me = requirePlayer(); $q = readJson(); $did = (string)($q['depot_id'] ?? '');
$ml = isset($q['lat']) && is_numeric($q['lat']) ? (float)$q['lat'] : null;
$mg = isset($q['lng']) && is_numeric($q['lng']) ? (float)$q['lng'] : null;
if ($did === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $did, $ml, $mg) {
$w = worldGet(); $d = $w['data']; $i = idxDepot($d, $did); if ($i === null) fail('депо не найдено', 404);
$x = $d['depots'][$i];
if ((int)$x['owner_id'] !== (int)$me['id']) fail('не ваше депо', 403);
if (!empty($x['is_building']) || !empty($x['frozen'])) fail('депо недоступно', 400);
$h = (int)$x['health']; if ($h >= 100) fail('депо в идеальном состоянии', 400);
$free = ($ml !== null && $mg !== null && haversineDistanceMeters($ml, $mg, (float)$x['lat'], (float)$x['lng']) <= REPAIR_FREE_RADIUS_M);
$cost = 0;
if (!$free) { $cost = (int)ceil(depotReplacementCost($x) * REPAIR_COST_RATIO); chargePlayer((int)$me['id'], $cost, 'repair_depot', null, ['depot_id'=>$did]); }
$d['depots'][$i]['health'] = 100;
worldSave($d);
return ['ok'=>true,'cost'=>$cost,'free'=>$free];
});
sendJson($out);
}
if ($action === 'delete_depot' && $isPost) {
$me = requirePlayer(); $did = (string)(readJson()['depot_id'] ?? ''); if ($did === '') sendJson(['error'=>'bad request'], 400);
runTx(function () use ($me, $did) {
$w = worldGet(); $d = $w['data']; $i = idxDepot($d, $did); if ($i === null) fail('депо не найдено', 404);
$x = $d['depots'][$i];
if ((int)$x['owner_id'] !== (int)$me['id']) fail('не ваше депо', 403);
if (!empty($x['frozen'])) fail('депо на продаже', 400);
$orph = 0;
foreach ($d['trains'] as &$t) if ((string)($t['depot_id'] ?? '') === $did) { $t['depot_id'] = null; $t['route_id'] = null; $orph++; } unset($t);
$ref = (int)ceil(depotReplacementCost($x) * REFUND_RATIO);
array_splice($d['depots'], $i, 1);
if ($ref > 0) addMoney((int)$me['id'], $ref, 'delete_depot_refund', null, ['depot_id'=>$did]);
logEvent('deleted', (int)$me['id'], "{$me['name']} снёс депо «{$x['name']}» (возврат {$ref} ₽, поездов без депо: {$orph})", []);
worldSave($d);
});
sendJson(['ok'=>true]);
}

// ---------- ПОЕЗДА ----------
if ($action === 'buy_train' && $isPost) {
$me = requirePlayer(); $q = readJson(); $did = (string)($q['depot_id'] ?? ''); $nm = trim((string)($q['name'] ?? ''));
if ($did === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $did, $nm) {
$w = worldGet(); $d = $w['data']; $i = idxDepot($d, $did); if ($i === null) fail('депо не найдено', 404);
$x = $d['depots'][$i];
if ((int)$x['owner_id'] !== (int)$me['id']) fail('не ваше депо', 403);
if (!empty($x['is_building'])) fail('депо ещё строится', 400);
if (!empty($x['frozen'])) fail('депо на продаже', 400);
$c = 0; foreach ($d['trains'] as $t) if ((string)($t['depot_id'] ?? '') === $did) $c++;
if ($c >= (int)$x['capacity']) fail("в депо нет мест ({$c}/{$x['capacity']})", 400);
chargePlayer((int)$me['id'], TRAIN_COST, 'buy_train', null, ['depot_id'=>$did]);
$id = newId('t'); $end = time() + BUILD_TIME_TRAIN * DAY_DURATION_SEC;
if ($nm === '') $nm = 'Поезд ' . (playerTrainsCount($d, (int)$me['id']) + 1);
$d['trains'][] = ['id'=>$id,'name'=>$nm,'owner_id'=>(int)$me['id'],'depot_id'=>$did,'route_id'=>null,'model'=>'generic',
'bought_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'transit_end'=>0,'frozen'=>false];
$ver = worldSave($d);
logEvent('train_bought', (int)$me['id'], "{$me['name']} заказал поезд «{$nm}»", ['train_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'train_id'=>$id,'cost'=>TRAIN_COST,'build_end'=>$end];
});
sendJson($out);
}
if ($action === 'rename_train' && $isPost) {
$me = requirePlayer(); $q = readJson(); $tid = (string)($q['train_id'] ?? ''); $nm = trim((string)($q['name'] ?? ''));
if ($tid === '' || $nm === '' || mb_strlen($nm) > 40) sendJson(['error'=>'bad request'], 400);
$ver = runTx(function () use ($me, $tid, $nm) {
$w = worldGet(); $d = $w['data']; $i = idxTrain($d, $tid); if ($i === null) fail('поезд не найден', 404);
if ((int)$d['trains'][$i]['owner_id'] !== (int)$me['id']) fail('не ваш поезд', 403);
$d['trains'][$i]['name'] = $nm; return worldSave($d);
});
sendJson(['ok'=>true, 'version'=>$ver]);
}
if ($action === 'sell_train' && $isPost) {
$me = requirePlayer(); $tid = (string)(readJson()['train_id'] ?? ''); if ($tid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $tid) {
$w = worldGet(); $d = $w['data']; $i = idxTrain($d, $tid); if ($i === null) fail('поезд не найден', 404);
$t = $d['trains'][$i];
if ((int)$t['owner_id'] !== (int)$me['id']) fail('не ваш поезд', 403);
if (!empty($t['frozen'])) fail('поезд на продаже', 400);
$ref = (int)ceil(TRAIN_COST * REFUND_RATIO);
array_splice($d['trains'], $i, 1);
addMoney((int)$me['id'], $ref, 'sell_train', null, ['train_id'=>$tid]);
logEvent('deleted', (int)$me['id'], "{$me['name']} списал поезд «{$t['name']}» (возврат {$ref} ₽)", []);
return ['ok'=>true,'version'=>worldSave($d),'refund'=>$ref];
});
sendJson($out);
}
if ($action === 'move_train' && $isPost) {
$me = requirePlayer(); $q = readJson(); $tid = (string)($q['train_id'] ?? ''); $did = (string)($q['depot_id'] ?? '');
if ($tid === '' || $did === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $tid, $did) {
$w = worldGet(); $d = $w['data'];
$i = idxTrain($d, $tid); if ($i === null) fail('поезд не найден', 404);
$t = $d['trains'][$i];
if ((int)$t['owner_id'] !== (int)$me['id']) fail('не ваш поезд', 403);
if (!empty($t['is_building'])) fail('поезд ещё не поставлен', 400);
if (!empty($t['frozen'])) fail('поезд на продаже', 400);
if ((string)($t['depot_id'] ?? '') === $did) fail('поезд уже в этом депо', 400);
$di = idxDepot($d, $did); if ($di === null) fail('депо не найдено', 404);
$x = $d['depots'][$di];
if ((int)$x['owner_id'] !== (int)$me['id']) fail('не ваше депо', 403);
if (!empty($x['is_building']) || !empty($x['frozen'])) fail('депо недоступно', 400);
$c = 0; foreach ($d['trains'] as $y) if ((string)($y['depot_id'] ?? '') === $did) $c++;
if ($c >= (int)$x['capacity']) fail('в депо нет мест', 400);
$cost = empty($t['depot_id']) ? 0 : TRAIN_REBASE_COST;
if ($cost > 0) chargePlayer((int)$me['id'], $cost, 'move_train', null, ['train_id'=>$tid]);
$d['trains'][$i]['depot_id'] = $did; $d['trains'][$i]['route_id'] = null;
$d['trains'][$i]['transit_end'] = time() + BUILD_TIME_TRAIN_REBASE * DAY_DURATION_SEC;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'transit_end'=>$d['trains'][$i]['transit_end']];
});
sendJson($out);
}
if ($action === 'assign_train' && $isPost) {
$me = requirePlayer(); $q = readJson(); $tid = (string)($q['train_id'] ?? ''); $rid = (string)($q['route_id'] ?? '');
if ($tid === '') sendJson(['error'=>'bad request'], 400);
$out = runTx(function () use ($me, $tid, $rid) {
$w = worldGet(); $d = $w['data']; $pid = (int)$me['id'];
$i = idxTrain($d, $tid); if ($i === null) fail('поезд не найден', 404);
$t = $d['trains'][$i];
if ((int)$t['owner_id'] !== $pid) fail('не ваш поезд', 403);
if (!empty($t['frozen'])) fail('поезд на продаже', 400);
if ($rid === '') { $d['trains'][$i]['route_id'] = null; return ['ok'=>true,'version'=>worldSave($d),'assigned'=>false]; }
if (!empty($t['is_building'])) fail('поезд ещё не поставлен', 400);
if ((int)($t['transit_end'] ?? 0) > time()) fail('поезд в пути между депо', 400);
if (empty($t['depot_id'])) fail('поезд без депо', 400);
$r = routeById($d, $rid); if (!$r) fail('маршрут не найден', 404);
if ((int)$r['owner_id'] !== $pid) fail('не ваш маршрут', 403);
$dp = depotById($d, (string)$t['depot_id']); if (!$dp) fail('депо не найдено', 404);
$stops = array_map('strval', $r['stations'] ?? []);
if (!in_array((string)($dp['station_id'] ?? ''), $stops, true)) fail('депо поезда должно быть у одной из станций маршрута', 400);
$idx = worldIndex($d); $pol = policiesAll();
$now = 0; foreach ($d['trains'] as $x) if ((string)($x['route_id'] ?? '') === $rid && (string)$x['id'] !== $tid) $now++;
$info = routeCompute($idx, $pol, $stops, !empty($r['loop']), $pid, $now + 1);
if (!$info['ok']) fail('маршрут разорван: ' . $info['reason'], 400);
if ($now + 1 > $info['slots']) fail("нет свободных ниток ({$now}/{$info['slots']}) — нужен второй путь", 400);
$d['trains'][$i]['route_id'] = $rid;
$ver = worldSave($d);
logEvent('train_assigned', $pid, "{$me['name']} назначил «{$t['name']}» на «{$r['name']}»" . ($info['fee_total'] > 0 ? " (субаренда {$info['fee_total']} ₽/д)" : ''), ['train_id'=>$tid,'route_id'=>$rid]);
return ['ok'=>true,'version'=>$ver,'assigned'=>true,'income'=>$info['income'],'slots'=>$info['slots'],'fee'=>$info['fee_total'],'owners'=>$info['owners']];
});
sendJson($out);
}

// ---------- GPS ----------
if ($action === 'gps_repair_nearby' && $isPost) {
$me = requirePlayer(); $q = readJson();
$lat = isset($q['lat']) && is_numeric($q['lat']) ? (float)$q['lat'] : null;
$lng = isset($q['lng']) && is_numeric($q['lng']) ? (float)$q['lng'] : null;
if ($lat === null || $lng === null || !is_finite($lat) || !is_finite($lng)) fail('bad coords', 400);
$out = runTx(function () use ($me, $lat, $lng) {
$w = worldGet(); $d = $w['data']; $fixed = [];
foreach (['stations','depots'] as $k) {
foreach ($d[$k] as &$o) {
if ((int)$o['owner_id'] !== (int)$me['id']) continue;
if (!empty($o['is_building']) || !empty($o['frozen'])) continue;
if ((int)($o['health'] ?? 100) >= 100) continue;
if (haversineDistanceMeters($lat, $lng, (float)$o['lat'], (float)$o['lng']) <= REPAIR_FREE_RADIUS_M) { $o['health'] = 100; $fixed[] = $o['name']; }
} unset($o);
}
if ($fixed) { logEvent('station_repaired', (int)$me['id'], "{$me['name']} бесплатно починил " . count($fixed) . " объект(ов) рядом", []); worldSave($d); }
return ['ok'=>true,'fixed'=>$fixed];
});
sendJson($out);
}

// ---------- СТАРТОВЫЙ ПАКЕТ ----------
if ($action === 'claim_starter_pack' && $isPost) {
$me = requirePlayer();
if ((int)($me['starter_claimed'] ?? 0) === 1) fail('стартовый пакет уже получен', 409);
$out = runTx(function () use ($me) {
$pid = (int)$me['id']; $w = worldGet(); $d = $w['data'];
$idx = worldIndex($d); $pol = policiesAll();
$mine = []; foreach ($d['stations'] as $s) if ((int)$s['owner_id'] === $pid && empty($s['is_building'])) $mine[] = (string)$s['id'];
if (count($mine) < 2) fail('нужно минимум 2 построенные станции', 400);
$path = autoRouteForPlayer($d, $idx, $pol, $pid, $mine);
if (!$path) fail('не удалось построить автомаршрут — проверьте связность и слоты', 400);
$res = createRouteRecord($d, $pid, 'Стартовый маршрут', $path, false, true);
$bestSid = $path[0]; $bv = -1;
foreach ($path as $sid) { $s = stationById($d, (string)$sid); $v = getBuildingIncomePercent((int)$s['buildings']); if ($v > $bv) { $bv = $v; $bestSid = $sid; } }
$st = stationById($d, (string)$bestSid);
$did = newId('d');
$d['depots'][] = ['id'=>$did,'name'=>'Депо «' . $st['name'] . '»','lat'=>(float)$st['lat'] + 0.0012,'lng'=>(float)$st['lng'] + 0.0016,
'station_id'=>(string)$st['id'],'owner_id'=>$pid,'level'=>1,'capacity'=>depotCapacityForLevel(1),'built_day'=>(int)metaGet('day_count'),
'is_building'=>false,'build_end'=>0,'upgrading'=>false,'upgrade_end'=>0,'upgrade_level'=>0,'frozen'=>false,'health'=>100,
'access'=>'inherit','labelOffset'=>['x'=>10,'y'=>4]];
$tid = newId('t');
$d['trains'][] = ['id'=>$tid,'name'=>'Поезд 1','owner_id'=>$pid,'depot_id'=>$did,'route_id'=>$res['id'],'model'=>'generic',
'bought_day'=>(int)metaGet('day_count'),'is_building'=>false,'build_end'=>0,'transit_end'=>0,'frozen'=>false];
$ver = worldSave($d);
db()->prepare("UPDATE players SET starter_claimed = 1 WHERE id = :id")->execute([':id'=>$pid]);
logEvent('starter_pack', $pid, "{$me['name']} получил стартовый пакет: депо, поезд и маршрут", []);
return ['ok'=>true,'version'=>$ver,'route_id'=>$res['id'],'depot_id'=>$did,'train_id'=>$tid];
});
sendJson($out);
}

// ---------- DEV ----------
if ($action === 'dev_bankrupt' && $isPost) {
$me = requirePlayer(); if (!isLocalRequest()) sendJson(['error'=>'dev only'], 403);
$out = runTx(function () use ($me) {
$pdo = db(); $pdo->prepare("UPDATE players SET money = 0 WHERE id = :id")->execute([':id'=>(int)$me['id']]);
$w = worldGet(); $fin = playerFinancials($w['data'], (int)$me['id']);
$debt = max(1.0, $fin['upkeep']);
$comps = confiscateForDebt($w, (int)$me['id'], $debt);
$a=0;$b=0;$c=0;$e=0;$n=0;
foreach ($comps as $ci) { createAuctionFromComponent($pdo, (int)$me['id'], $ci, 'bankrupt'); $a+=count($ci['stations']); $b+=count($ci['edges']); $c+=count($ci['depots']); $e+=count($ci['trains']); $n+=count($ci['nodes']); }
worldSave($w['data']);
if ($comps) logEvent('bankruptcy', (int)$me['id'], "{$me['name']} обанкротился (dev)", []);
return ['ok'=>true,'debt'=>$debt,'components'=>count($comps),'stations'=>$a,'edges'=>$b,'depots'=>$c,'trains'=>$e,'nodes'=>$n];
});
sendJson($out);
}
if ($action === 'dev_give_money' && $isPost) {
$me = requirePlayer(); if (!isLocalRequest()) sendJson(['error'=>'dev only'], 403);
$a = (int)(readJson()['amount'] ?? 10000);
if ($a <= 0 || $a > 1000000) fail('bad amount', 400);
addMoney((int)$me['id'], $a, 'dev_grant', null, []);
sendJson(['ok'=>true, 'amount'=>$a]);
}

if ($action !== '') sendJson(['error'=>'unknown action'], 404);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#070c14">
<title>ЖД ИГРА · Диспетчерская</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ==========================================================
   ДИСПЕТЧЕРСКАЯ · ночной пульт управления сетью
   ========================================================== */
:root{
  --bg:#070c14; --panel:#0f1826; --panel2:#16233a; --raise:#1d2f4c; --raise2:#254066;
  --line:#22344c; --line2:#33507a;
  --ink:#e8f0fa; --ink2:#9db0c9; --mut:#61789a;
  --green:#35d07f; --green-d:#1f9c5c; --green-t:#7bf0b3;
  --blue:#3d9bff; --blue-t:#9ccaff;
  --amber:#ffb02e; --amber-t:#ffd88a;
  --red:#ff5a5a; --red-d:#c9302f; --red-t:#ffa3a3;
  --violet:#a97bff; --teal:#24d3c4; --pink:#ff5da2; --lime:#b6e83a;
  --wood:#c07a3c; --concrete:#8ea6c6; --steel:#4a7fc1; --water:#3f7fbf;
  --scrim:rgba(3,7,13,.74);
  --chrome-h:52px;
  --f-body:'Manrope',system-ui,-apple-system,sans-serif;
  --f-disp:'Unbounded','Manrope',system-ui,sans-serif;
  --f-mono:'JetBrains Mono',ui-monospace,'SF Mono',monospace;
}
*{box-sizing:border-box}
html,body{height:100%;margin:0;padding:0;overflow:hidden}
body{font-family:var(--f-body);background:var(--bg);color:var(--ink);display:flex;flex-direction:column;font-size:14px;-webkit-font-smoothing:antialiased}
::selection{background:rgba(53,208,127,.32)}
:focus-visible{outline:2px solid var(--green);outline-offset:2px;border-radius:6px}
*{scrollbar-width:thin;scrollbar-color:#33507a rgba(7,12,20,.6)}
::-webkit-scrollbar{width:11px;height:11px}
::-webkit-scrollbar-track{background:rgba(7,12,20,.6)}
::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#33507a,#22344c);border-radius:9px;border:2px solid var(--panel)}
::-webkit-scrollbar-thumb:hover{background:linear-gradient(180deg,var(--green),var(--green-d))}
::-webkit-scrollbar-corner{background:transparent}

/* ================= ВЕРХНЯЯ ПАНЕЛЬ ================= */
#topbar{display:flex;align-items:center;gap:8px;padding:8px 13px;background:linear-gradient(180deg,#15213a,#0b1220);border-bottom:1px solid var(--line);font-size:13px;z-index:1000;position:relative;flex-shrink:0;overflow-x:auto;white-space:nowrap;animation:dropIn .5s ease backwards}
#topbar::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;background:linear-gradient(90deg,var(--green),var(--blue),var(--violet),var(--amber),var(--green));background-size:300% 100%;animation:stripSlide 11s linear infinite}
.brand{font-family:var(--f-disp);font-weight:700;font-size:13.5px;letter-spacing:.6px;display:flex;align-items:center;gap:8px;flex-shrink:0}
.lamp{width:9px;height:9px;border-radius:50%;background:var(--mut);flex-shrink:0}
.lamp.ok{background:var(--green);box-shadow:0 0 9px var(--green);animation:lampPulse 2.4s ease infinite}
.lamp.warn{background:var(--amber);box-shadow:0 0 9px var(--amber)}
.lamp.err{background:var(--red);box-shadow:0 0 9px var(--red)}
#meChip{width:12px;height:12px;border-radius:4px;background:var(--mut);border:1px solid rgba(255,255,255,.35);flex-shrink:0;box-shadow:0 0 8px rgba(0,0,0,.4)}
#meName{color:var(--ink2);font-weight:700;max-width:130px;overflow:hidden;text-overflow:ellipsis}
#topbar .balance{margin-left:auto;color:var(--green-t);font-weight:700;font-family:var(--f-mono);font-size:14px;padding:2px 7px;border-radius:8px;flex-shrink:0}
.balance.up{animation:flashUp 1s ease}
.balance.down{animation:flashDown 1s ease}
.badge{font-family:var(--f-mono);font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px;border:1px solid;flex-shrink:0}
.badge.inc{background:rgba(53,208,127,.1);color:var(--green-t);border-color:rgba(53,208,127,.35)}
.badge.upk{background:rgba(255,90,90,.1);color:var(--red-t);border-color:rgba(255,90,90,.35)}
.badge.net{background:rgba(61,155,255,.1);color:var(--blue-t);border-color:rgba(61,155,255,.35)}
.badge.grace{background:rgba(169,123,255,.12);color:#d3bcff;border-color:rgba(169,123,255,.4)}
.badge.danger{background:rgba(255,90,90,.16);color:var(--red-t);border-color:rgba(255,90,90,.5);animation:lampPulse 1.6s ease infinite}
#tickTimer{position:relative;overflow:hidden;background:rgba(61,155,255,.08);color:var(--ink2);border:1px solid rgba(61,155,255,.3);padding:3px 10px 5px;border-radius:999px;font-size:11px;display:flex;align-items:center;gap:5px;font-family:var(--f-mono);flex-shrink:0}
#tickTimer b{color:var(--amber-t);font-size:12px}
.tickbar{position:absolute;left:0;bottom:0;height:2px;width:0;background:linear-gradient(90deg,var(--blue),var(--green));transition:width 1s linear}
.ibtn{width:33px;height:33px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;background:var(--panel2);border:1px solid var(--line);border-radius:10px;font-size:15px;cursor:pointer;color:var(--ink2);transition:all .15s ease;padding:0;position:relative}
.ibtn:hover{border-color:var(--green);color:var(--ink);transform:translateY(-1px);box-shadow:0 4px 14px rgba(53,208,127,.2)}
.ibtn.on{border-color:var(--green);color:var(--green-t);background:rgba(53,208,127,.12)}
.ibtn .dotmark{position:absolute;top:-3px;right:-3px;min-width:15px;height:15px;padding:0 3px;border-radius:8px;background:var(--red);color:#fff;font-size:9px;font-family:var(--f-mono);font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid var(--panel)}
#logoutBtn{width:auto;padding:0 12px;font-size:12px;font-family:var(--f-body);font-weight:700;color:var(--red-t);border-color:rgba(255,90,90,.35)}
#logoutBtn:hover{border-color:var(--red);box-shadow:0 4px 14px rgba(255,90,90,.2)}

/* ================= БЕГУЩАЯ СТРОКА ================= */
#ticker{display:none;background:linear-gradient(90deg,#0f3324,#0d2b3a);color:#8bf3bd;border-bottom:1px solid rgba(53,208,127,.3);overflow:hidden;height:29px;align-items:center;flex-shrink:0;z-index:999;font-weight:600;font-size:12.5px}
.ticker-in{display:inline-block;white-space:nowrap;padding-left:100vw;animation:marquee 18s linear infinite}
@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}

/* ================= КАРКАС ================= */
#mainRow{flex:1;display:flex;overflow:hidden;height:100%}
#mapWrap{flex:1;position:relative;min-width:0}
#map{position:absolute;inset:0;background:#dde5ee;z-index:1}
#map.m-station,#map.m-edge,#map.m-depot,#map.m-route{cursor:crosshair!important}
#mapWrap::after{content:'';position:absolute;inset:0;pointer-events:none;z-index:400;box-shadow:inset 0 0 60px rgba(7,12,20,.35)}
#board{width:376px;flex-shrink:0;border-left:1px solid var(--line);padding:13px;overflow-y:auto;font-size:13px;z-index:500;height:100%;
  background:radial-gradient(640px 340px at 0% 0%,rgba(53,208,127,.07),transparent 62%),radial-gradient(560px 420px at 100% 100%,rgba(61,155,255,.06),transparent 62%),var(--panel)}

/* ================= КАРТОЧКИ ================= */
.card{background:linear-gradient(180deg,rgba(29,47,76,.5),rgba(15,24,38,.88));border:1px solid var(--line);border-radius:14px;padding:12px;margin-bottom:11px;animation:cardIn .5s ease backwards}
.card:nth-child(1){animation-delay:.04s}.card:nth-child(2){animation-delay:.08s}.card:nth-child(3){animation-delay:.12s}
.card:nth-child(4){animation-delay:.16s}.card:nth-child(5){animation-delay:.2s}.card:nth-child(6){animation-delay:.24s}
.card:nth-child(7){animation-delay:.28s}.card:nth-child(8){animation-delay:.32s}.card:nth-child(9){animation-delay:.36s}
.card h2{font-family:var(--f-disp);font-weight:500;font-size:10px;text-transform:uppercase;letter-spacing:1.6px;color:var(--ink2);margin:0 0 10px;display:flex;align-items:center;gap:8px}
.dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot.g{background:var(--green);box-shadow:0 0 6px var(--green)}.dot.b{background:var(--blue);box-shadow:0 0 6px var(--blue)}
.dot.a{background:var(--amber);box-shadow:0 0 6px var(--amber)}.dot.v{background:var(--violet);box-shadow:0 0 6px var(--violet)}
.dot.t{background:var(--teal);box-shadow:0 0 6px var(--teal)}.dot.r{background:var(--red);box-shadow:0 0 6px var(--red)}
.chip-count{margin-left:auto;font-family:var(--f-mono);font-size:10px;color:var(--mut);background:var(--panel);border:1px solid var(--line);padding:1px 7px;border-radius:999px}

/* ================= КНОПКИ / ПОЛЯ ================= */
.btn{display:flex;align-items:center;gap:7px;width:100%;padding:9px 11px;margin:5px 0;border-radius:10px;border:1px solid var(--line2);background:var(--panel2);color:var(--ink);font-family:var(--f-body);font-weight:700;font-size:13px;cursor:pointer;transition:all .15s ease;text-align:left}
.btn:hover:not(:disabled){transform:translateY(-1px);border-color:var(--green);box-shadow:0 5px 16px rgba(53,208,127,.16)}
.btn:disabled{opacity:.42;cursor:not-allowed}
.btn.active{border-color:var(--green);background:rgba(53,208,127,.14);animation:pulseOutline 1.8s ease infinite}
.btn .k{margin-left:auto;font-family:var(--f-mono);font-size:10px;color:var(--mut)}
.btn-price{font-family:var(--f-mono);font-size:11px;color:var(--amber-t);background:rgba(255,176,46,.1);border:1px solid rgba(255,176,46,.3);padding:1px 7px;border-radius:999px}
.btn-primary{background:linear-gradient(135deg,var(--green),var(--teal));color:#04241a;border:none;justify-content:center}
.btn-primary:hover:not(:disabled){box-shadow:0 6px 20px rgba(53,208,127,.38)}
.btn-violet{background:linear-gradient(135deg,var(--violet),#6f5bff);color:#fff;border:none;justify-content:center}
.btn-violet:hover:not(:disabled){box-shadow:0 6px 20px rgba(169,123,255,.38)}
.btn-danger{background:rgba(255,90,90,.1);color:var(--red-t);border-color:rgba(255,90,90,.4)}
.btn-danger:hover:not(:disabled){border-color:var(--red);box-shadow:0 5px 16px rgba(255,90,90,.2)}
.btn-sm{padding:5px 10px;font-size:11.5px;width:auto;display:inline-flex;margin:0}
.input,.select{width:100%;padding:8px 10px;font-size:13px;font-family:var(--f-body);background:#0a1120;border:1px solid var(--line2);border-radius:10px;color:var(--ink);transition:border .15s}
.input:focus,.select:focus{border-color:var(--green);outline:none;box-shadow:0 0 0 3px rgba(53,208,127,.14)}
.select{cursor:pointer}
input[type=checkbox],input[type=radio]{accent-color:var(--green);width:15px;height:15px;cursor:pointer;flex-shrink:0}
input[type=range]{accent-color:var(--green);width:100%;cursor:pointer;height:22px}
.vrow{display:flex;align-items:center;gap:8px;margin:7px 0;font-size:12.5px;color:var(--ink2)}
.vrow label{display:flex;align-items:center;gap:8px;cursor:pointer;flex:1}
.vcap{font-size:10.5px;color:var(--mut);text-transform:uppercase;letter-spacing:.9px;font-weight:800;margin:11px 0 3px;display:flex;align-items:center;gap:6px}
.vcap b{font-family:var(--f-mono);color:var(--amber-t);font-size:11px;letter-spacing:0}
.vcap .sp{margin-left:auto;font-family:var(--f-mono);color:var(--mut);font-size:9.5px;letter-spacing:0;text-transform:none}
.hint{color:var(--mut);font-size:11.5px;padding:3px 0;line-height:1.45}

/* --- переключатель компании --- */
.swrow{display:flex;align-items:center;gap:9px;padding:7px 0;border-top:1px dashed var(--line)}
.swrow:first-of-type{border-top:0}
.swrow .swl{flex:1;font-size:12.5px;color:var(--ink);font-weight:600}
.swrow .swl small{display:block;color:var(--mut);font-weight:500;font-size:10.5px;margin-top:1px}
.switch{position:relative;width:38px;height:21px;flex-shrink:0}
.switch input{opacity:0;width:0;height:0;position:absolute}
.switch i{position:absolute;inset:0;background:var(--raise);border:1px solid var(--line2);border-radius:999px;transition:.18s;cursor:pointer}
.switch i::before{content:'';position:absolute;left:2px;top:2px;width:15px;height:15px;border-radius:50%;background:var(--mut);transition:.18s}
.switch input:checked + i{background:rgba(53,208,127,.25);border-color:var(--green)}
.switch input:checked + i::before{transform:translateX(17px);background:var(--green);box-shadow:0 0 8px rgba(53,208,127,.6)}
.cc-head{display:flex;align-items:center;gap:9px;margin-bottom:6px}
.cc-swatch{width:26px;height:26px;border-radius:8px;border:2px solid rgba(255,255,255,.4);cursor:pointer;flex-shrink:0;box-shadow:0 2px 10px rgba(0,0,0,.4);transition:transform .15s}
.cc-swatch:hover{transform:scale(1.1)}
.cc-name{font-weight:800;font-size:13px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cc-sums{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;font-family:var(--f-mono);font-size:10.5px}
.cc-sum{background:var(--panel2);border:1px solid var(--line);border-radius:7px;padding:2px 8px;color:var(--ink2)}
.cc-sum b{font-weight:700}
.pal-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin:10px 0}
.pal-sw{aspect-ratio:1;border-radius:9px;border:2px solid rgba(255,255,255,.25);cursor:pointer;position:relative;transition:transform .13s}
.pal-sw:hover{transform:scale(1.12)}
.pal-sw.sel{border-color:#fff;box-shadow:0 0 0 3px rgba(255,255,255,.25),0 0 14px currentColor}
.pal-sw.used{opacity:.35;cursor:not-allowed}
.pal-sw.used::after{content:'✕';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;text-shadow:0 1px 3px #000}

/* ================= МИССИЯ ================= */
.mission-head{display:flex;align-items:center;gap:9px;margin-bottom:9px}
.mission-title{font-family:var(--f-disp);font-size:12px;font-weight:500;color:var(--ink)}
.mission-bar{height:5px;background:#0a1120;border-radius:3px;overflow:hidden;margin-bottom:10px}
.mission-bar i{display:block;height:100%;background:linear-gradient(90deg,var(--green),var(--teal));border-radius:3px;transition:width .5s cubic-bezier(.2,.8,.2,1)}
.step{display:flex;gap:9px;align-items:flex-start;padding:5px 0;font-size:12.5px;color:var(--ink2)}
.step .box{width:17px;height:17px;border-radius:5px;border:1.5px solid var(--line2);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:10px;margin-top:1px}
.step.done .box{background:var(--green);border-color:var(--green);color:#04241a}
.step.done{color:var(--mut)}
.step.done .lbl{text-decoration:line-through}
.step .lbl b{color:var(--ink);font-weight:700}
.step.done .lbl b{color:var(--mut)}
.step .sub{display:block;font-size:10.5px;color:var(--mut);margin-top:1px}

/* ================= СПИСКИ ================= */
.row-line{padding:6px 2px;border-top:1px dashed var(--line);display:flex;gap:7px;align-items:center;font-size:12.5px}
.row-line:first-child{border-top:0}
.rank{color:var(--mut);width:19px;font-family:var(--f-mono);font-size:11px}
.cdot{width:10px;height:10px;border-radius:3px;flex-shrink:0;border:1px solid rgba(255,255,255,.3)}
.tag{font-size:9px;font-weight:800;background:rgba(53,208,127,.15);color:var(--green-t);border:1px solid rgba(53,208,127,.35);padding:1px 6px;border-radius:999px;text-transform:uppercase;letter-spacing:.5px}
.mini{font-family:var(--f-mono);font-size:10px;color:var(--ink2);background:var(--raise);border:1px solid var(--line2);padding:0 6px;border-radius:999px;white-space:nowrap}
.item{padding:8px 9px;border:1px solid var(--line);border-radius:11px;margin:6px 0;cursor:pointer;background:rgba(10,17,32,.55);transition:all .15s;position:relative;overflow:hidden}
.item:hover{border-color:var(--green);transform:translateX(2px)}
.item::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--rc,var(--line2))}
.item-top{display:flex;align-items:center;gap:6px}
.item-name{font-weight:700;font-size:12.5px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.item-sub{font-size:10.5px;color:var(--mut);margin-top:3px;display:flex;gap:7px;flex-wrap:wrap;font-family:var(--f-mono)}
.item-val{font-family:var(--f-mono);font-size:11px;font-weight:700}
.item-live{font-size:10.5px;font-family:var(--f-mono);color:var(--blue-t);margin-top:4px;display:flex;gap:6px;align-items:center}
.item-live .sd{width:6px;height:6px;border-radius:50%;background:var(--blue);flex-shrink:0}
.item-live.stop{color:var(--amber-t)}.item-live.stop .sd{background:var(--amber);animation:lampPulse 1s ease infinite}
.item-live.pass{color:var(--ink2)}.item-live.pass .sd{background:var(--ink2)}
.pos{color:var(--green-t)}.neg{color:var(--red-t)}.warnv{color:var(--amber-t)}
.bar{height:4px;background:#0a1120;border-radius:2px;overflow:hidden;margin-top:5px}
.bar i{display:block;height:100%;border-radius:2px}
.st-active{border-color:rgba(53,208,127,.4)}
.st-idle{border-color:rgba(255,176,46,.35)}
.st-broken{border-color:rgba(255,90,90,.45);background:rgba(255,90,90,.05)}
.pill{font-size:9px;font-weight:800;padding:1px 6px;border-radius:999px;text-transform:uppercase;letter-spacing:.4px;border:1px solid;white-space:nowrap}
.pill.ok{background:rgba(53,208,127,.14);color:var(--green-t);border-color:rgba(53,208,127,.4)}
.pill.idle{background:rgba(255,176,46,.14);color:var(--amber-t);border-color:rgba(255,176,46,.4)}
.pill.bad{background:rgba(255,90,90,.14);color:var(--red-t);border-color:rgba(255,90,90,.45)}
.pill.info{background:rgba(61,155,255,.14);color:var(--blue-t);border-color:rgba(61,155,255,.4)}
.pill.vio{background:rgba(169,123,255,.14);color:#d3bcff;border-color:rgba(169,123,255,.4)}
.tools{display:flex;gap:6px;margin-bottom:8px}
.tools .input{flex:1;padding:6px 9px;font-size:12px}
.tools .select{width:122px;padding:6px 7px;font-size:11.5px}

/* ================= АУКЦИОНЫ ================= */
.lot{background:linear-gradient(180deg,rgba(255,176,46,.09),rgba(255,176,46,.03));border:1px solid rgba(255,176,46,.35);border-radius:12px;padding:9px 11px;margin:7px 0;font-size:12px;cursor:pointer;transition:all .15s}
.lot:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(255,176,46,.16);border-color:var(--amber)}
.lot.bank{border-color:rgba(255,90,90,.35);background:linear-gradient(180deg,rgba(255,90,90,.08),rgba(255,90,90,.02))}
.lot.bank:hover{border-color:var(--red)}
.lot .head{display:flex;justify-content:space-between;gap:8px;font-weight:700;align-items:center}
.lot .head span{color:var(--mut);font-size:11px;font-family:var(--f-mono);font-weight:500}
.lot .names{color:var(--ink2);font-size:11px;margin-top:3px}
.lot .bottom{display:flex;justify-content:space-between;align-items:center;margin-top:6px;gap:8px}
.lot-price{color:var(--amber-t);font-weight:700;font-family:var(--f-mono)}
.lot-price small{color:var(--mut);font-size:10px;font-weight:500}
.lot-btn{padding:4px 12px;font-size:11.5px;font-weight:800;border-radius:8px;border:none;background:linear-gradient(135deg,var(--amber),#ff8a3d);color:#3a2400;cursor:pointer;font-family:var(--f-body)}
.lot-btn:hover{box-shadow:0 4px 12px rgba(255,176,46,.35)}
.lot-btn.cancel{background:var(--raise);color:var(--ink2);border:1px solid var(--line2)}

/* ================= ДОК НА КАРТЕ ================= */
#modeBadge{position:absolute;left:12px;top:12px;z-index:450;display:none;align-items:center;gap:8px;background:rgba(11,18,32,.92);border:1px solid var(--line2);border-radius:999px;padding:6px 14px;font-size:12px;font-weight:700;color:var(--ink);box-shadow:0 8px 24px rgba(0,0,0,.4);backdrop-filter:blur(8px)}
#modeBadge.show{display:flex}
#modeBadge .ic{font-size:14px}
#dock{position:absolute;left:50%;transform:translateX(-50%) translateY(14px);bottom:16px;z-index:460;display:none;flex-direction:column;gap:9px;background:rgba(11,18,32,.95);border:1px solid var(--line2);border-radius:16px;padding:12px 14px;min-width:min(580px,92%);max-width:min(700px,94%);box-shadow:0 20px 50px rgba(0,0,0,.55);backdrop-filter:blur(10px);opacity:0;transition:opacity .2s ease,transform .2s ease}
#dock.show{display:flex;opacity:1;transform:translateX(-50%) translateY(0)}
.dock-head{display:flex;align-items:center;gap:9px;font-size:12.5px;font-weight:800;color:var(--ink);flex-wrap:wrap}
.dock-head .ic{font-size:15px}
.dock-head .sp{margin-left:auto;display:flex;gap:6px}
.dock-stats{display:flex;gap:8px;flex-wrap:wrap;font-family:var(--f-mono);font-size:11px}
.dstat{background:var(--panel2);border:1px solid var(--line);border-radius:8px;padding:3px 9px;color:var(--ink2)}
.dstat b{color:var(--ink);font-weight:700}
.dstat.good b{color:var(--green-t)}.dstat.bad b{color:var(--red-t)}.dstat.warn b{color:var(--amber-t)}
.dock-chips{display:flex;gap:6px;flex-wrap:wrap;max-height:96px;overflow-y:auto}
.schip{display:inline-flex;align-items:center;gap:6px;background:var(--raise);border:1px solid var(--line2);border-radius:999px;padding:3px 5px 3px 10px;font-size:11.5px;font-weight:700}
.schip i{font-style:normal;font-family:var(--f-mono);font-size:9.5px;color:var(--mut)}
.schip button{background:none;border:none;color:var(--mut);cursor:pointer;font-size:13px;line-height:1;padding:0 3px}
.schip button:hover{color:var(--red)}
.dock-actions{display:flex;gap:8px;flex-wrap:wrap}
.dock-actions .btn{margin:0;flex:1;min-width:130px;justify-content:center}
.dock-note{font-size:11px;color:var(--mut);line-height:1.45}
.dock-note.bad{color:var(--red-t)}

/* ================= КОНТЕКСТНОЕ МЕНЮ ================= */
#ctx{position:fixed;background:linear-gradient(180deg,#1d2f4c,#111c2e);border:1px solid var(--line2);border-radius:14px;box-shadow:0 22px 60px rgba(0,0,0,.6);padding:12px;min-width:292px;max-width:400px;max-height:82vh;overflow-y:auto;font-size:13px;z-index:2500;display:none;animation:popIn .17s ease}
#ctx.open{display:block}
#ctx .title{font-family:var(--f-disp);font-weight:500;font-size:12px;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid var(--line);padding-right:20px;display:flex;align-items:center;gap:7px}
#ctx .row{display:flex;justify-content:space-between;gap:10px;margin:4px 0}
#ctx .row .k{color:var(--mut);font-size:11.5px}
#ctx .row .v{color:var(--ink);font-weight:600;text-align:right;font-family:var(--f-mono);font-size:11px}
#ctx .sec{margin-top:9px;padding-top:8px;border-top:1px dashed var(--line);font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--mut);font-weight:800}
#ctx .actions{margin-top:10px;border-top:1px solid var(--line);padding-top:10px;display:flex;flex-direction:column;gap:5px}
#ctx .actions button{padding:7px 11px;font-size:12.5px;font-weight:700;cursor:pointer;width:100%;border:1px solid var(--line2);border-radius:9px;background:var(--panel2);color:var(--ink);font-family:var(--f-body);text-align:left;transition:all .13s}
#ctx .actions button:hover:not(:disabled){border-color:var(--green);background:rgba(53,208,127,.1);transform:translateX(2px)}
#ctx .actions button:disabled{opacity:.42;cursor:not-allowed}
#ctx .actions button.dngr:hover:not(:disabled){border-color:var(--red);background:rgba(255,90,90,.1)}
#ctx .close{position:absolute;right:9px;top:7px;cursor:pointer;color:var(--mut);font-size:17px;line-height:1}
#ctx .close:hover{color:var(--red)}
.acc-row{display:flex;gap:5px;margin-top:8px}
.acc-row button{flex:1;padding:5px 4px;font-size:10.5px;font-weight:800;border-radius:7px;border:1px solid var(--line2);background:var(--panel2);color:var(--ink2);cursor:pointer;font-family:var(--f-body)}
.acc-row button.sel{border-color:var(--green);color:var(--green-t);background:rgba(53,208,127,.12)}
.hbar{height:6px;background:#0a1120;border-radius:3px;overflow:hidden;margin-top:6px}
.hbar i{display:block;height:100%;border-radius:3px}

/* ================= МОДАЛКИ ================= */
.modal{position:fixed;inset:0;background:var(--scrim);display:none;align-items:center;justify-content:center;backdrop-filter:blur(7px);z-index:3000;padding:14px}
.modal.open{display:flex}
.dialog{background:linear-gradient(180deg,#1a2740,#101a2b);border:1px solid var(--line2);border-radius:18px;padding:21px;width:min(94vw,430px);box-shadow:0 30px 90px rgba(0,0,0,.65);animation:popIn .22s ease;max-height:90vh;display:flex;flex-direction:column}
.dialog.wide{width:min(96vw,620px)}
.dialog.xwide{width:min(96vw,1000px)}
.dialog h2,.dialog h3{font-family:var(--f-disp);font-weight:500;font-size:14px;margin:0 0 14px;display:flex;justify-content:space-between;align-items:center;gap:10px}
.close-x{cursor:pointer;font-size:22px;color:var(--mut);line-height:1;background:none;border:none;padding:0}
.close-x:hover{color:var(--red)}
.mbody{overflow-y:auto;flex:1;font-size:13px;color:var(--ink2);line-height:1.5}
.btn-row{display:flex;gap:9px;margin-top:16px}
.btn-row button{flex:1;padding:10px;font-size:13.5px;font-weight:800;cursor:pointer;border-radius:10px;font-family:var(--f-body);transition:all .15s}
.b-cancel{background:var(--raise);color:var(--ink2);border:1px solid var(--line2)}
.b-cancel:hover{border-color:var(--mut)}
.b-ok{background:linear-gradient(135deg,var(--green),var(--teal));color:#04241a;border:none}
.b-ok:hover:not(:disabled){box-shadow:0 6px 20px rgba(53,208,127,.35);transform:translateY(-1px)}
.b-ok.danger{background:linear-gradient(135deg,var(--red),var(--red-d));color:#fff}
.b-ok:disabled{opacity:.42;cursor:not-allowed}
#actionModal{z-index:5000}
#loginModal{z-index:6000}
#loginModal .dialog{width:min(94vw,400px);text-align:center;padding:28px 26px}
.login-title{font-family:var(--f-disp);font-weight:700;font-size:25px;margin:2px 0 6px;background:linear-gradient(90deg,var(--green-t),var(--blue),var(--amber-t));-webkit-background-clip:text;background-clip:text;color:transparent}
.login-sub{color:var(--ink2);font-size:12.5px;margin:0 0 14px;line-height:1.55}
.login-chips{display:flex;gap:6px;justify-content:center;flex-wrap:wrap;margin-bottom:16px}
.lchip{font-size:10.5px;font-weight:700;color:var(--ink2);background:var(--panel2);border:1px solid var(--line2);padding:3px 9px;border-radius:999px}
#loginName{text-align:center;font-size:15px;font-weight:600}
.login-hint{font-size:11px;color:var(--mut);margin:11px 0 0}

/* --- смета --- */
.price-old{text-decoration:line-through;color:var(--mut);font-size:.9em;margin-right:8px;font-family:var(--f-mono)}
.price-new{color:var(--green-t);font-weight:700;font-size:1.15em;font-family:var(--f-mono)}
.disc{background:rgba(53,208,127,.12);color:var(--green-t);padding:3px 9px;border-radius:8px;font-size:11px;font-weight:800;display:inline-block;margin-bottom:8px;border:1px solid rgba(53,208,127,.35)}
.total{font-size:17px;font-weight:800;margin-top:12px;border-top:2px solid var(--green-d);padding-top:9px;text-align:right;font-family:var(--f-mono);color:var(--ink)}
.alert{margin-top:8px;padding:7px 11px;background:rgba(255,90,90,.1);border:1px solid rgba(255,90,90,.4);border-radius:9px;color:var(--red-t);font-size:12px;font-weight:700}
.note{margin-top:8px;padding:7px 11px;background:rgba(61,155,255,.1);border:1px solid rgba(61,155,255,.35);border-radius:9px;color:var(--blue-t);font-size:12px;font-weight:700}
.box{margin-top:9px;border:1px solid var(--line2);border-radius:11px;overflow:hidden}
.box-head{background:var(--panel2);padding:8px 12px;cursor:pointer;font-size:12.5px;font-weight:800;display:flex;justify-content:space-between;align-items:center;color:var(--ink);gap:8px}
.box-head:hover{background:var(--raise)}
.box-head .sum{margin-left:auto;font-family:var(--f-mono);font-size:11.5px;color:var(--amber-t)}
.box-body{display:none;padding:8px 12px;background:rgba(10,17,32,.6)}
.box-body.open{display:block}
.obs-row{display:flex;justify-content:space-between;padding:5px 0;font-size:12px;border-bottom:1px dashed var(--line);gap:10px}
.obs-row:last-child{border-bottom:0}
.obs-row b{color:var(--amber-t);font-family:var(--f-mono)}
.br-item{padding:8px 0;border-bottom:1px dashed var(--line)}
.br-item:last-child{border-bottom:0}
.br-top{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:var(--ink)}
.br-top .sw{width:11px;height:11px;border-radius:3px;flex-shrink:0;border:1px solid rgba(255,255,255,.25)}
.br-meta{font-size:10.5px;color:var(--mut);font-family:var(--f-mono);margin:3px 0 5px}
.br-item .select{padding:5px 8px;font-size:12px}
.tr-table{width:100%;border-collapse:collapse;font-size:12px;margin-top:6px}
.tr-table th{text-align:left;color:var(--mut);font-size:10px;text-transform:uppercase;letter-spacing:.7px;padding:4px 6px;border-bottom:1px solid var(--line)}
.tr-table td{padding:5px 6px;border-bottom:1px dashed var(--line);font-family:var(--f-mono)}
.tr-table tr.hl td{background:rgba(53,208,127,.08);color:var(--green-t)}
.choice{display:flex;gap:9px;align-items:flex-start;padding:8px 9px;border:1px solid var(--line);border-radius:10px;margin:5px 0;cursor:pointer;background:rgba(10,17,32,.5);transition:all .14s}
.choice:hover{border-color:var(--green)}
.choice.dis{opacity:.45;cursor:not-allowed}
.choice .cn{font-weight:700;font-size:12.5px;color:var(--ink)}
.choice .cs{font-size:10.5px;color:var(--mut);font-family:var(--f-mono);margin-top:2px}

/* --- продажа --- */
.sell-lists{flex:1;overflow-y:auto;margin-bottom:12px;border:1px solid var(--line);padding:10px;border-radius:12px;background:rgba(10,17,32,.5);max-height:44vh}
.sell-lists h4{margin:8px 0 6px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--mut);font-family:var(--f-disp);font-weight:500}
.sell-lists h4:first-child{margin-top:0}
.sell-row{display:flex;align-items:center;gap:8px;padding:5px 6px;border-radius:8px;font-size:12.5px;cursor:pointer;color:var(--ink)}
.sell-row:hover{background:var(--panel2)}

/* --- графики --- */
#chartModal .dialog{height:86vh}
.tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}
.tab{padding:6px 12px;font-size:11.5px;font-weight:800;border:1px solid var(--line2);background:var(--panel2);border-radius:999px;cursor:pointer;color:var(--ink2);font-family:var(--f-body);transition:all .15s}
.tab:hover{border-color:var(--green);color:var(--ink)}
.tab.active{background:linear-gradient(135deg,var(--green),var(--teal));color:#04241a;border-color:transparent}
#chartBox{flex:1;position:relative;width:100%;min-height:260px;overflow-y:auto}
.bd-sum{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:13px}
.bd-chip{font-size:12.5px;padding:7px 12px;border-radius:10px;font-family:var(--f-mono);font-weight:700;border:1px solid}
.bd-chip.i{background:rgba(53,208,127,.1);color:var(--green-t);border-color:rgba(53,208,127,.35)}
.bd-chip.u{background:rgba(255,90,90,.1);color:var(--red-t);border-color:rgba(255,90,90,.35)}
.bd-chip.n{background:rgba(61,155,255,.1);color:var(--blue-t);border-color:rgba(61,155,255,.35)}
.bd-h{margin:14px 0 8px;font-size:10.5px;font-family:var(--f-disp);font-weight:500;text-transform:uppercase;letter-spacing:1.2px}
.bd-h.g{color:var(--green-t)}.bd-h.r{color:var(--red-t)}.bd-h.v{color:#d3bcff}
.bd-card{border:1px solid var(--line);border-radius:11px;padding:8px 12px;margin-bottom:7px;background:rgba(10,17,32,.5)}
.bd-row{display:flex;justify-content:space-between;font-weight:700;font-size:12.5px;color:var(--ink);gap:10px}
.bd-row small{color:var(--mut);font-weight:500}
.bd-sub{display:flex;justify-content:space-between;font-size:11px;color:var(--ink2);margin-top:3px;padding-left:13px;gap:10px}
.bd-line{display:flex;justify-content:space-between;font-size:12px;padding:5px 8px;border-bottom:1px dashed var(--line);color:var(--ink);gap:10px}
.bd-line small{color:var(--mut)}

/* --- ремонт / новости / справка --- */
.rep-table{width:100%;border-collapse:collapse;font-size:12px}
.rep-table td{padding:6px 2px;border-bottom:1px dashed var(--line)}
.rep-table .rh{color:var(--red-t);text-align:right;font-family:var(--f-mono)}
.rep-table .rc{text-align:right;font-family:var(--f-mono);color:var(--ink)}
.free{color:var(--green-t);font-weight:800}
.ev{padding:7px 2px;border-bottom:1px dashed var(--line);display:flex;gap:9px;align-items:flex-start;font-size:12.5px}
.ev:last-child{border-bottom:0}
.ev .ei{flex-shrink:0}
.ev .et{color:var(--ink);line-height:1.4}
.ev .ed{color:var(--mut);font-size:10px;font-family:var(--f-mono);margin-left:auto;flex-shrink:0}
.htable{width:100%;border-collapse:collapse}
.htable td{padding:7px 2px;border-bottom:1px dashed var(--line);font-size:12.5px;color:var(--ink2)}
.htable td:first-child{width:118px}
kbd{display:inline-block;padding:2px 8px;font-size:11px;font-family:var(--f-mono);color:var(--ink);background:var(--raise);border:1px solid var(--line2);border-radius:6px;box-shadow:0 2px 0 var(--line)}
.lg{display:flex;align-items:center;gap:9px;font-size:12px;color:var(--ink2);padding:4px 0}
.lg .sw{width:13px;height:13px;border-radius:50%;border:2px solid #fff;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,.4)}
.lg-sec{margin-top:14px;padding-top:12px;border-top:1px solid var(--line)}
.lg-sec h4{margin:0 0 8px;font-size:10.5px;font-family:var(--f-disp);font-weight:500;text-transform:uppercase;letter-spacing:1.2px;color:var(--mut)}
.demo-track{background:rgba(10,17,32,.6);border:1px solid var(--line);border-radius:11px;padding:10px 12px;margin-top:8px}
.demo-rail{position:relative;height:26px;margin:6px 0 4px}
.demo-rail::before{content:'';position:absolute;left:0;right:0;top:12px;height:2px;background:linear-gradient(90deg,#33507a,#7fa6d8,#33507a)}
.demo-st{position:absolute;top:6px;width:14px;height:14px;border-radius:50%;background:#1f9c5c;border:2px solid #fff;transform:translateX(-50%)}
.demo-st.pass{background:#98a4b4;width:10px;height:10px;top:8px}
.demo-tr{position:absolute;top:9px;left:0;width:18px;height:8px;border-radius:2px;background:var(--amber);border:1px solid #07101c;animation:demoRun 7s cubic-bezier(.4,0,.6,1) infinite}
@keyframes demoRun{0%{left:2%}12%{left:22%}22%{left:31%}30%,44%{left:33%}56%{left:64%}66%{left:73%}74%,88%{left:75%}100%{left:96%}}

/* ================= ЧАТ ================= */
#chat{position:fixed;right:0;top:var(--chrome-h);bottom:0;width:292px;background:linear-gradient(180deg,#141f36,#0c1424);border-left:1px solid var(--line2);display:flex;flex-direction:column;z-index:1500;box-shadow:-8px 0 30px rgba(0,0,0,.45);transition:transform .22s ease}
#chat.hide{transform:translateX(292px)}
#chatHead{padding:10px 12px;border-bottom:1px solid var(--line);font-weight:800;font-size:12.5px;display:flex;align-items:center;gap:8px;color:var(--ink)}
#chatHead button{margin-left:auto;background:none;border:none;color:var(--mut);cursor:pointer;font-size:17px;line-height:1}
#chatHead button:hover{color:var(--red)}
#chatMsgs{flex:1;overflow-y:auto;padding:10px 12px;font-size:12px}
.cmsg{margin-bottom:8px;line-height:1.42;word-wrap:break-word;color:var(--ink)}
.cmsg .a{font-weight:800}
.cmsg .t{color:var(--mut);font-size:9.5px;margin-left:5px;font-family:var(--f-mono)}
#chatRow{display:flex;border-top:1px solid var(--line)}
#chatInput{flex:1;border:none;padding:11px 12px;font-size:13px;outline:none;background:#0a1120;color:var(--ink);font-family:var(--f-body)}
#chatSend{border:none;background:linear-gradient(135deg,var(--green),var(--teal));color:#04241a;padding:0 16px;cursor:pointer;font-size:16px;font-weight:800}

/* ================= ЗАГРУЗКА ================= */
#loading{position:fixed;inset:0;background:var(--scrim);display:none;align-items:center;justify-content:center;z-index:99999;backdrop-filter:blur(4px)}
.lbox{background:linear-gradient(180deg,#1a2740,#101a2b);border:1px solid var(--line2);border-radius:16px;padding:24px 34px;box-shadow:0 20px 60px rgba(0,0,0,.55);display:flex;flex-direction:column;align-items:center;gap:14px;min-width:250px}
.spin{width:44px;height:44px;border:3px solid var(--line2);border-top-color:var(--green);border-right-color:var(--teal);border-radius:50%;animation:spin .8s linear infinite}
#loadText{font-size:13.5px;font-weight:700;color:var(--ink2)}

/* ================= КАРТА: МЕТКИ / УЗЛЫ / ПОЕЗДА ================= */
.lbl{background:#fff;border:1px solid #c6d2e2;border-radius:7px;padding:2px 7px;font-size:12px;font-family:var(--f-body);font-weight:600;color:#1e2b3a;white-space:nowrap;user-select:none;box-shadow:0 2px 6px rgba(16,30,54,.2);touch-action:none}
.lbl.mine{border-color:var(--oc,#1f9c5c);color:#14713f}
.lbl.drag{cursor:grab}
.lbl.dragging{background:#eaf4ff;border-color:#3d9bff;cursor:grabbing;box-shadow:0 6px 16px rgba(61,155,255,.45)}
.lbl.build{background:#fff6df;border-color:#e0bf5c;color:#8a6a00;animation:buildPulse 1.9s ease infinite}
.lbl.sale{background:#fff3d0;border-color:#e0ac33;color:#8a6a00}
.lbl.bank{background:#eceff4;border-color:#96a2b3;color:#5b6c85}
.lbl.dead{background:#f3f5f8;border-color:#b8c2d0;color:#7b8798;font-style:italic}
.dlbl{background:#0f1826;border:1px solid var(--teal);border-radius:7px;padding:2px 8px;font-size:11.5px;font-weight:700;color:#8ff0e6;white-space:nowrap;user-select:none;box-shadow:0 2px 8px rgba(0,0,0,.35);touch-action:none;font-family:var(--f-body)}
.dlbl.drag{cursor:grab}
.dlbl.dragging{border-color:var(--green);box-shadow:0 6px 16px rgba(36,211,196,.45)}
.dlbl.build{border-color:var(--amber);color:var(--amber-t);animation:buildPulse 1.9s ease infinite}
.depot-ic{filter:drop-shadow(0 2px 4px rgba(0,0,0,.4))}
.nodeWrap{position:relative;width:14px;height:14px}
.nodeSq{position:absolute;left:2px;top:2px;width:10px;height:10px;transform:rotate(45deg);border:1.6px solid #07101c;box-shadow:0 1px 4px rgba(0,0,0,.45);border-radius:2px}
.nodeSq.own{border-color:#fff}
.nodeSq.dead{opacity:.55}
.nodeLbl{position:absolute;left:16px;top:-1px;font:600 10px var(--f-body);color:#3c4c60;background:rgba(255,255,255,.85);border-radius:4px;padding:0 4px;white-space:nowrap;pointer-events:none}
.tw{position:relative;pointer-events:none}
.tb{position:absolute;left:50%;top:50%;border-radius:2px;border:1px solid rgba(6,12,22,.85);box-shadow:0 1px 4px rgba(0,0,0,.45);will-change:transform}
.tb.dw{animation:dwPulse 1.1s ease infinite}
@keyframes dwPulse{0%,100%{box-shadow:0 0 0 2px rgba(255,255,255,.45),0 1px 6px rgba(0,0,0,.5)}50%{box-shadow:0 0 0 4px rgba(255,255,255,.12),0 1px 6px rgba(0,0,0,.5)}}
.tl{position:absolute;left:50%;top:100%;transform:translate(-50%,3px);font:700 9.5px var(--f-mono);color:#e8f0fa;background:rgba(9,15,26,.86);border:1px solid #33507a;border-radius:5px;padding:0 4px;white-space:nowrap}
.fx{position:relative}
.fx i{position:absolute;left:50%;top:50%;width:30px;height:30px;margin:-15px 0 0 -15px;border-radius:50%;border:2px solid currentColor;animation:fxRing .72s ease-out forwards}
.fx.sm i{width:20px;height:20px;margin:-10px 0 0 -10px;border-width:1.5px;animation-duration:.5s}
@keyframes fxRing{0%{transform:scale(.35);opacity:.95}100%{transform:scale(1.5);opacity:0}}
.tip{background:rgba(9,15,26,.92)!important;color:#e8f0fa!important;border:1px solid #33507a!important;border-radius:8px!important;padding:4px 9px!important;font-size:11.5px!important;font-family:var(--f-mono)!important;box-shadow:0 4px 14px rgba(0,0,0,.35)!important;line-height:1.45!important}
.tip::before{display:none!important}
.leaflet-container{touch-action:none;font-family:var(--f-body);background:#dde5ee}
.leaflet-control-scale-line{background:rgba(9,15,26,.8)!important;color:#cfe0f5!important;border-color:#33507a!important;font-family:var(--f-mono)!important;font-size:10px!important;border-radius:4px}
.leaflet-bar a{background:#16233a!important;color:#cfe0f5!important;border-color:#22344c!important}
.leaflet-bar a:hover{background:#1d2f4c!important}
.leaflet-control-attribution{background:rgba(9,15,26,.72)!important;color:#8ea3bd!important;font-size:9.5px!important}
.leaflet-control-attribution a{color:#9ccaff!important}

/* ================= АНИМАЦИИ ================= */
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes lampPulse{0%,100%{opacity:1}50%{opacity:.42}}
@keyframes stripSlide{0%{background-position:0 0}100%{background-position:300% 0}}
@keyframes cardIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
@keyframes dropIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}
@keyframes popIn{from{opacity:0;transform:scale(.95) translateY(8px)}to{opacity:1;transform:none}}
@keyframes flashUp{0%{background:rgba(53,208,127,.42);color:#fff}100%{background:transparent}}
@keyframes flashDown{0%{background:rgba(255,90,90,.42);color:#fff}100%{background:transparent}}
@keyframes pulseOutline{0%,100%{box-shadow:0 0 0 0 rgba(53,208,127,.35)}50%{box-shadow:0 0 0 5px rgba(53,208,127,0)}}
@keyframes buildPulse{0%,100%{box-shadow:0 2px 6px rgba(224,191,92,.3)}50%{box-shadow:0 2px 15px rgba(224,191,92,.8)}}

/* ================= АДАПТИВ ================= */
@media (max-width:860px){
  #mainRow{flex-direction:column}
  #mapWrap{min-height:54vh;flex:none;height:54vh}
  #board{width:100%;height:auto;flex:1;border-left:none;border-top:1px solid var(--line);padding-bottom:16px}
  #topbar{flex-wrap:wrap;white-space:normal;gap:6px}
  #topbar .balance{margin-left:0}
  #ctx{max-width:94vw}
  #dock{min-width:94%;bottom:10px;padding:10px}
  #chat{width:100%;top:auto;bottom:0;height:46vh}
  #chat.hide{transform:translateY(100%)}
  .dialog.xwide{height:88vh}
}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}}
</style>
</head>
<body>
<noscript><div style="padding:24px;color:#ffa3a3;font-weight:700">Для игры необходим JavaScript.</div></noscript>

<div id="topbar">
  <span class="brand"><span class="lamp" id="lamp"></span>ЖД ИГРА</span>
  <span id="meChip" title="Цвет вашей компании"></span>
  <span id="meName">—</span>
  <div id="tickTimer" title="1 игровой день = 60 секунд">День <b id="dayNum">1</b> · расчёт через <b id="tickSec">--</b>с<span class="tickbar" id="tickBar"></span></div>
  <span class="balance" id="balance">0 ₽</span>
  <span id="bInc" class="badge inc" title="Доход за игровой день">+0 ₽</span>
  <span id="bUpk" class="badge upk" title="Содержание за игровой день">−0 ₽</span>
  <span id="bNet" class="badge net" title="Чистая прибыль за день">=0 ₽/д</span>
  <span id="bGrace" class="badge grace" style="display:none" title="Каникулы: содержание ×0.5">🛡 каникулы</span>
  <span id="bRisk" class="badge danger" style="display:none"></span>
  <button id="btnSound" class="ibtn on" title="Звук">🔊</button>
  <button id="btnChart" class="ibtn" title="Статистика и графики">📈</button>
  <button id="btnRepair" class="ibtn" title="Ведомость ремонта">🔧</button>
  <button id="btnNews" class="ibtn" title="Новости">📰<span class="dotmark" id="newsMark" style="display:none">0</span></button>
  <button id="btnGps" class="ibtn" title="GPS: бесплатный ремонт рядом">📍</button>
  <button id="btnChat" class="ibtn" title="Чат диспетчеров">💬</button>
  <button id="btnCompany" class="ibtn" title="Настройки компании: цвет и доступ">🏢</button>
  <button id="btnSettings" class="ibtn" title="Настройки карты">⚙️</button>
  <button id="btnHelp" class="ibtn" title="Справка и легенда">❓</button>
  <button id="logoutBtn" class="ibtn" style="display:none">Выйти</button>
</div>

<div id="ticker"><div id="tickerIn" class="ticker-in"></div></div>

<div id="mainRow">
  <div id="mapWrap">
    <div id="map"></div>
    <div id="modeBadge"><span class="ic" id="mbIc">🚉</span><span id="mbTx">Режим</span></div>
    <div id="dock"></div>
  </div>

  <div id="board">
    <div class="card">
      <h2><span class="dot g"></span>Пульт запуска</h2>
      <div id="missionBody"></div>
    </div>

    <div class="card">
      <h2><span class="dot b"></span>Инструменты</h2>
      <button id="mStation" class="btn">🚉 Станция <span class="btn-price"><span id="pStation">250</span> ₽</span><span class="k">1</span></button>
      <button id="mEdge" class="btn">🛤️ Перегон / ветка <span class="btn-price"><span id="pEdge">1.00</span> ₽/м</span><span class="k">2</span></button>
      <button id="mDepot" class="btn">🏭 Депо <span class="btn-price"><span id="pDepot">1500</span> ₽</span><span class="k">3</span></button>
      <button id="mRoute" class="btn">🧭 Маршрут <span class="btn-price">бесплатно</span><span class="k">4</span></button>
      <button id="btnAuto" class="btn">✨ Автомаршруты по сети</button>
      <button id="btnStarter" class="btn btn-violet">🎁 Стартовый пакет</button>
      <div class="hint">В режиме <b style="color:var(--ink)">2</b> клик по существующему перегону врезает станцию; клик в пустом поле тянет ветку через узлы (50 ₽).</div>
    </div>

    <div class="card">
      <h2><span class="dot t"></span>Компания и доступ</h2>
      <div class="cc-head">
        <span class="cc-swatch" id="ccColor" title="Сменить цвет компании"></span>
        <span class="cc-name" id="ccName">—</span>
        <button class="btn btn-sm" id="btnPalette">🎨 Цвет</button>
      </div>
      <div class="swrow"><span class="swl">🛤 Субаренда путей<small>Чужие поезда едут по вашим перегонам · вы получаете 30 % их дохода пропорционально км</small></span><label class="switch"><input type="checkbox" id="ccTrackage"><i></i></label></div>
      <div class="swrow"><span class="swl">🚉 Остановки на станциях<small>Чужие маршруты могут останавливаться на ваших станциях</small></span><label class="switch"><input type="checkbox" id="ccStops"><i></i></label></div>
      <div class="swrow"><span class="swl">🔌 Подключение к сети<small>К вашим станциям и узлам можно строить свои перегоны и врезать станции</small></span><label class="switch"><input type="checkbox" id="ccConnect"><i></i></label></div>
      <div class="cc-sums"><span class="cc-sum">аренда вам <b class="pos" id="ccIn">+0 ₽</b></span><span class="cc-sum">вы платите <b class="neg" id="ccOut">−0 ₽</b></span></div>
      <div class="hint" style="margin-top:6px">Точечно доступ переопределяется в меню объекта: «как в компании / открыт / закрыт».</div>
    </div>

    <div class="card">
      <h2><span class="dot v"></span>Маршруты <span class="chip-count" id="cntRoutes">0</span></h2>
      <div id="routesList"></div>
    </div>

    <div class="card">
      <h2><span class="dot t"></span>Депо и поезда <span class="chip-count" id="cntFleet">0</span></h2>
      <div id="fleetList"></div>
    </div>

    <div class="card">
      <h2><span class="dot a"></span>Движение поездов</h2>
      <div class="vcap">Скорость симуляции <b>×<span id="moSpeedVal">8</span></b><span class="sp">0 = пауза</span></div>
      <input type="range" id="moSpeed" min="0" max="30" step="1" value="8">
      <div class="vcap">Стоянка на станции <b><span id="moDwellVal">3.5</span> с</b></div>
      <input type="range" id="moDwell" min="0" max="12" step="0.5" value="3.5">
      <div class="vcap">Разгон / торможение <b><span id="moAccelVal">1.6</span> с</b><span class="sp">до полной скорости</span></div>
      <input type="range" id="moAccel" min="0.3" max="5" step="0.1" value="1.6">
      <div class="vcap">Длина вагона <b><span id="moLenVal">18</span> px</b></div>
      <input type="range" id="moLen" min="10" max="34" step="1" value="18">
      <div class="vrow"><label><input type="checkbox" id="moExpress"> ⏩ Экспресс: только конечные</label></div>
      <div class="vrow"><label><input type="checkbox" id="moLabels"> 🏷 Подписи поездов</label></div>
      <div class="vrow"><label><input type="checkbox" id="moPause"> ⏸ Пауза движения</label></div>
      <button id="moReset" class="btn btn-sm" style="margin-top:6px">↺ Сбросить движение</button>
      <div class="hint" style="margin-top:6px">Поезд разгоняется от станции, тормозит перед остановкой и стоит заданное время. Станции не из маршрута проезжает без замедления.</div>
    </div>

    <div class="card">
      <h2><span class="dot r"></span>Ремонт</h2>
      <div id="repairPanel"></div>
      <button id="btnRepair2" class="btn btn-sm" style="margin-top:8px">🔧 Открыть ведомость</button>
    </div>

    <div class="card">
      <h2><span class="dot b"></span>Вид карты</h2>
      <div class="vcap">Подложка</div>
      <select id="mapStyle" class="select">
        <option value="voyager" selected>Carto Voyager</option>
        <option value="positron">Carto Positron (светлая)</option>
        <option value="osm">Стандартная OSM</option>
      </select>
      <div class="vcap">Яркость подложки <b><span id="opVal">100</span>%</b></div>
      <input type="range" id="mapOpacity" min="20" max="100" value="100">
      <div class="vrow"><label><input type="checkbox" id="tgRoutes" checked> 🧭 Линии маршрутов</label></div>
      <div class="vrow"><label><input type="checkbox" id="tgTrains" checked> 🚆 Поезда в движении</label></div>
      <div class="vrow"><label><input type="checkbox" id="tgBridges" checked> 🌉 Мосты и путепроводы</label></div>
      <div class="vrow"><label><input type="checkbox" id="tgNodes" checked> 🔩 Узлы веток</label></div>
      <div class="vrow"><label><input type="checkbox" id="tgLabels" checked> 🏷 Подписи станций</label></div>
      <div class="vrow"><label><input type="checkbox" id="tgForeign" checked> 🌐 Чужие маршруты</label></div>
      <div class="vrow"><label><input type="checkbox" id="tgFade"> 🌫 Полупрозрачные перегоны</label></div>
      <div class="vrow"><label><input type="checkbox" id="tgAfford"> 💰 Подсвечивать доступные лоты</label></div>
      <button id="btnFit" class="btn btn-sm" style="margin-top:8px">🎯 Вписать всю сеть</button>
    </div>

    <div class="card">
      <h2><span class="dot a"></span>Рынок <span class="chip-count" id="cntLots">0 моих</span></h2>
      <div id="lotsList"></div>
    </div>

    <div class="card">
      <h2><span class="dot g"></span>Рейтинг · ₽/день</h2>
      <div id="ratingList"></div>
    </div>

    <div class="card">
      <h2><span class="dot b"></span>Компании</h2>
      <div id="playersList"></div>
    </div>

    <div class="card">
      <h2><span class="dot t"></span>Мои станции <span class="chip-count" id="cntSt">0</span></h2>
      <div class="tools">
        <input type="text" id="stSearch" class="input" placeholder="Поиск…" autocomplete="off">
        <select id="stSort" class="select">
          <option value="routes">по маршрутам</option>
          <option value="name">по имени</option>
          <option value="class">по классу</option>
          <option value="health">по износу</option>
          <option value="bonus">по бонусу</option>
        </select>
      </div>
      <div id="myStations"></div>
    </div>

    <div id="devCard" class="card" style="display:none">
      <h2><span class="dot r"></span>Dev</h2>
      <button id="devMoney" class="btn">💵 +10 000 ₽</button>
      <button id="devBank" class="btn btn-danger">💥 Обанкротить себя</button>
    </div>
  </div>
</div>

<!-- НАСТРОЙКИ КАРТЫ -->
<div id="settingsModal" class="modal"><div class="dialog">
  <h2>⚙️ Настройки карты <button class="close-x" data-close="settingsModal">&times;</button></h2>
  <div class="mbody">
    <div class="vrow"><label><input type="checkbox" id="gridOn"> Показывать метровую сетку</label></div>
    <div class="vrow"><label><input type="checkbox" id="gridSnap"> Привязка кликов к сетке</label></div>
    <div class="vcap">Шаг сетки</div>
    <select id="gridStep" class="select">
      <option value="10">10 м</option><option value="50" selected>50 м</option><option value="100">100 м</option>
      <option value="250">250 м</option><option value="500">500 м</option><option value="1000">1000 м</option>
    </select>
    <p class="hint" style="margin-top:12px">Клик по полотну пути открывает меню перегона, клик по цветной линии сбоку — меню маршрута. Подписи своих станций и депо перетаскиваются.</p>
    <button id="resetSettings" class="btn btn-danger" style="margin-top:14px">↺ Сбросить настройки</button>
  </div>
</div></div>

<!-- ЦВЕТ КОМПАНИИ -->
<div id="companyModal" class="modal"><div class="dialog">
  <h2>🎨 Цвет компании <button class="close-x" data-close="companyModal">&times;</button></h2>
  <div class="mbody">
    <p class="hint">Один цвет на всю компанию: маршруты, поезда, станции и узлы. Занятые цвета недоступны.</p>
    <div class="pal-grid" id="palGrid"></div>
    <div class="note" id="cmInfo">Субаренда: оператор платит владельцу путей 30 % дохода маршрута пропорционально километражу.</div>
  </div>
  <div class="btn-row"><button class="b-cancel" data-close="companyModal">Закрыть</button><button id="cmSave" class="b-ok">Применить цвет</button></div>
</div></div>

<!-- ПРОДАЖА -->
<div id="sellModal" class="modal"><div class="dialog wide">
  <h3>🏷 Выставить лот на рынок</h3>
  <div class="sell-lists">
    <div id="sellStations"></div><div id="sellEdges"></div><div id="sellNodes"></div><div id="sellDepots"></div><div id="sellTrains"></div>
  </div>
  <div style="font-size:12px;margin-bottom:6px;color:var(--ink2)">Цена: от <b id="minPrice" style="color:var(--amber-t);font-family:var(--f-mono)">0</b> до <b id="maxPrice" style="color:var(--amber-t);font-family:var(--f-mono)">0</b> ₽</div>
  <input type="number" id="sellPrice" class="input" style="font-family:var(--f-mono);font-weight:700;font-size:15px">
  <div class="btn-row"><button class="b-cancel" data-close="sellModal">Отмена</button><button id="sellOk" class="b-ok">Выставить</button></div>
</div></div>

<!-- КОНТЕКСТНОЕ МЕНЮ -->
<div id="ctx"><span class="close" id="ctxClose">×</span><div class="title" id="ctxTitle">—</div><div id="ctxBody"></div><div class="actions" id="ctxActions"></div></div>

<!-- ГРАФИКИ -->
<div id="chartModal" class="modal"><div class="dialog xwide">
  <h2>📈 Статистика за игровой год <button class="close-x" data-close="chartModal">&times;</button></h2>
  <div class="tabs" id="chartTabs">
    <button class="tab active" data-m="net">💰 Прибыль</button>
    <button class="tab" data-m="company_value">🏢 Компания</button>
    <button class="tab" data-m="stations">🏙 Станции</button>
    <button class="tab" data-m="edge_km">🛤 Км путей</button>
    <button class="tab" data-m="routes">🧭 Маршруты</button>
    <button class="tab" data-m="trains">🚆 Поезда</button>
    <button class="tab" data-m="depots">🏭 Депо</button>
    <button class="tab" data-m="breakdown">📊 Детализация</button>
  </div>
  <div id="chartBox"><canvas id="hChart"></canvas></div>
</div></div>

<!-- ВХОД -->
<div id="loginModal" class="modal"><div class="dialog">
  <svg viewBox="0 0 320 46" style="width:100%;height:46px;margin-bottom:4px" fill="none" aria-hidden="true">
    <path d="M6 34 C 70 34, 90 12, 160 12 S 250 34, 314 34" stroke="#22344c" stroke-width="3" stroke-dasharray="7 6"/>
    <path d="M6 34 C 70 34, 90 12, 160 12 S 250 34, 314 34" stroke="#35d07f" stroke-width="3" stroke-dasharray="34 256" stroke-linecap="round">
      <animate attributeName="stroke-dashoffset" from="290" to="0" dur="3.4s" repeatCount="indefinite"/>
    </path>
    <circle cx="6" cy="34" r="6" fill="#35d07f" stroke="#070c14" stroke-width="2"/>
    <circle cx="160" cy="12" r="6" fill="#ffb02e" stroke="#070c14" stroke-width="2"/>
    <circle cx="314" cy="34" r="6" fill="#3d9bff" stroke="#070c14" stroke-width="2"/>
    <path d="M148 6 l6 -5 M172 6 l-6 -5 M148 18 l6 5 M172 18 l-6 5" stroke="#7fa6d8" stroke-width="2"/>
  </svg>
  <h3 class="login-title">ЖД ИГРА</h3>
  <p class="login-sub">Стройте станции и ветки в поле, врезайте станции в перегоны, наводите мосты, открывайте маршруты и сдавайте пути в субаренду другим компаниям.</p>
  <div class="login-chips"><span class="lchip">🎨 Цвет компании</span><span class="lchip">🔩 Ветки в поле</span><span class="lchip">🤝 Субаренда 30 %</span><span class="lchip">🚆 Живое движение</span></div>
  <input type="text" id="loginName" class="input" placeholder="Название компании" autocomplete="off" maxlength="40">
  <button id="loginBtn" class="btn btn-primary" style="margin-top:10px">Войти в диспетчерскую</button>
  <p class="login-hint">Новое имя — новая компания: 10 000 ₽, свой цвет и 20 дней каникул.</p>
</div></div>

<!-- НОВОСТИ -->
<div id="newsModal" class="modal"><div class="dialog wide">
  <h2>📰 Лента событий <button class="close-x" data-close="newsModal">&times;</button></h2>
  <div id="eventsList" class="mbody"></div>
</div></div>

<!-- СПРАВКА -->
<div id="helpModal" class="modal"><div class="dialog wide">
  <h2>❓ Управление и легенда <button class="close-x" data-close="helpModal">&times;</button></h2>
  <div class="mbody">
    <table class="htable">
      <tr><td><kbd>1</kbd></td><td>Станция — клик по карте</td></tr>
      <tr><td><kbd>2</kbd></td><td>Перегон: клик по станции/узлу или в поле — начало; клики в поле — изгибы; клик по станции/узлу — конец; <b>клик по перегону — врезка станции</b></td></tr>
      <tr><td><kbd>Enter</kbd></td><td>Завершить ветку в точке курсора (режим 2) / подтвердить диалог</td></tr>
      <tr><td><kbd>3</kbd></td><td>Депо — в радиусе 500 м от доступной станции</td></tr>
      <tr><td><kbd>4</kbd></td><td>Маршрут — станции по порядку; клик по первой замыкает кольцо; путь через узлы находится автоматически</td></tr>
      <tr><td><kbd>Пробел</kbd></td><td>Точка изгиба под курсором / пауза поездов вне режимов</td></tr>
      <tr><td><kbd>Backspace</kbd></td><td>Убрать последний изгиб / остановку</td></tr>
      <tr><td><kbd>Esc</kbd></td><td>Отмена режима, закрытие окон</td></tr>
      <tr><td><kbd>Клик / ПКМ</kbd></td><td>Меню объекта; полотно → перегон, цветная линия → маршрут</td></tr>
    </table>
    <div class="lg-sec">
      <h4>Класс станции</h4>
      <p class="hint">Класс влияет <b style="color:var(--ink)">только на число маршрутов</b> через станцию (1 маршрут за класс). Пути и нитки больше не ограничены классом: два двухпутных перегона можно примкнуть к станции 1 класса.</p>
    </div>
    <div class="lg-sec">
      <h4>Экономика маршрута</h4>
      <p class="hint">Доход = <b style="color:var(--ink)">35 × Σ(бонус зданий × состояние) × (1 + 0.25·двухпутка) × (1 + 0.20·электрификация) × (скорость / 60) × поездной фактор</b>. Скорость — минимум по перегонам, перегон ограничен самым медленным мостом. Чужие пути в маршруте оплачиваются владельцу: <b style="color:var(--ink)">30 % дохода × доля его км</b>.</p>
    </div>
    <div class="lg-sec">
      <h4>Движение поездов</h4>
      <div class="demo-track">
        <div class="demo-rail">
          <span class="demo-st" style="left:3%"></span><span class="demo-st pass" style="left:33%"></span>
          <span class="demo-st" style="left:75%"></span><span class="demo-st" style="left:97%"></span>
          <span class="demo-tr"></span>
        </div>
        <div class="hint">Разгон → крейсер → торможение → стоянка (белая обводка). Серая точка — станция не из маршрута: проезд без замедления.</div>
      </div>
    </div>
    <div class="lg-sec">
      <h4>Объекты</h4>
      <div class="lg"><span class="sw" style="background:#35d07f"></span>Станция вашей компании (цвет компании)</div>
      <div class="lg"><span class="sw" style="background:#ff5a5a"></span>Станция другой компании (её цвет)</div>
      <div class="lg"><span class="sw" style="background:#7fa6d8;border-radius:2px;transform:rotate(45deg)"></span>Узел ветки — стык/тупик в поле, превращается в станцию</div>
      <div class="lg"><span class="sw" style="background:#ffd98a;border-color:#e0bf5c"></span>Строится</div>
      <div class="lg"><span class="sw" style="background:#98a4b4;border-color:#5b6c85"></span>Не обслуживается (содержание ×0.5)</div>
      <div class="lg"><span class="sw" style="background:#24d3c4;border-radius:3px"></span>Депо</div>
      <div class="lg">🔒 Закрытый чужой объект — подключение/проезд запрещены владельцем</div>
    </div>
    <div class="lg-sec">
      <h4>Мосты</h4>
      <div class="lg"><span class="sw" style="background:#c07a3c;border-radius:3px"></span>Дерево — 30 / 45 км/ч</div>
      <div class="lg"><span class="sw" style="background:#8ea6c6;border-radius:3px"></span>Бетон — 60 / 90 км/ч</div>
      <div class="lg"><span class="sw" style="background:#4a7fc1;border-radius:3px"></span>Металл — 120 / 150 / 180 км/ч</div>
      <div class="lg"><span class="sw" style="background:#d9a441;border-radius:3px"></span>Глухое пересечение — 40 км/ч (над путями)</div>
    </div>
  </div>
</div></div>

<!-- РЕМОНТ -->
<div id="repairModal" class="modal"><div class="dialog wide">
  <h2>🔧 Ведомость ремонта <button class="close-x" data-close="repairModal">&times;</button></h2>
  <div id="repairBody" class="mbody"></div>
  <div class="btn-row"><button class="b-cancel" data-close="repairModal">Отмена</button><button id="repairNear" class="b-cancel" title="Бесплатно починить всё в радиусе 1 км от вас">📍 Рядом</button><button id="repairOk" class="b-ok">Починить всё</button></div>
</div></div>

<!-- ЧАТ -->
<div id="chat" class="hide">
  <div id="chatHead"><span>💬 Чат диспетчеров</span><button id="chatClose">×</button></div>
  <div id="chatMsgs"></div>
  <div id="chatRow"><input id="chatInput" type="text" maxlength="300" placeholder="Сообщение…" autocomplete="off"><button id="chatSend">➤</button></div>
</div>

<div id="loading"><div class="lbox"><div class="spin"></div><div id="loadText">Проверяем местность…</div></div></div>

<div id="actionModal" class="modal"><div class="dialog" id="actionDialog">
  <h3 id="actionTitle">Заголовок</h3>
  <div id="actionBody" class="mbody"></div>
  <div class="btn-row"><button id="actionCancel" class="b-cancel">Отмена</button><button id="actionOk" class="b-ok">ОК</button></div>
</div></div>

<script>
if (typeof L === 'undefined') {
  document.getElementById('map').innerHTML = '<div style="padding:22px;color:#c9302f;font-weight:700;background:#fee">Критическая ошибка: Leaflet не загрузилась.</div>';
  throw new Error('Leaflet missing');
}
"use strict";
/* ============================================================
   КОНСТАНТЫ
   ============================================================ */
const API='game.php';
const DAY=60, POLL_MS=2000;
const COST_STATION=250, COST_EDGE_M=1, COST_DOUBLE_M=.80, COST_ELEC_M=.50, COST_NODE=50;
const MAX_CLASS=10;
const UPK_ST_BASE=2, UPK_ST_CLASS=2, UPK_EDGE_SHARE=.10, INCOME_TRACK=15, UPK_NODE=.5;
const UPK_DEPOT_BASE=4, UPK_DEPOT_LVL=2, DEPOT_CAP_BASE=3, DEPOT_CAP_LVL=2, DEPOT_MAX_LVL=5;
const DEPOT_UPG_COST=800, DEPOT_RADIUS=500, TRAIN_COST=800, TRAIN_UPK_RUN=3, TRAIN_UPK_IDLE=1.5;
const REBASE_COST=200, REPAIR_RATIO=.10, REPAIR_FREE_M=1000, REFUND=.50;
const ST_R_BASE=8, ST_R_CLASS=1.5, HEALTH_CRIT=20, CONSERV=.50, TRANSIT_UPK=.75;
const BRIDGE_MIN_PX=26, BRIDGE_OFF_PX=6, BRIDGE_WING_PX=7, BRIDGE_WING_A=35*Math.PI/180;
const ROUTE_OFFSET_PX=12, EDGE_HIT_PX=16, ROUTE_HIT_PX=8, MAX_ANIM_TRAINS=80;
const BASE_LAT=55.75, BASE_LNG=37.62;
const CARTO_KEY='cb1_4pcc_1_c40db698b0c1ad6e7d443599';
const PALETTE=['#35d07f','#ff5a5a','#3d9bff','#ffb02e','#a97bff','#24d3c4','#ff5da2','#b6e83a','#ff8a3d','#7fa6d8','#e0d84a','#f06292'];
const MAT_COLOR={wood:'#c07a3c',concrete:'#8ea6c6',steel:'#4a7fc1',level:'#d9a441'};
const EV_ICON={new_player:'👋',bankruptcy:'💥',auction_sold:'💰',auction_expired:'⌛',auction_cancelled:'↩️',big_build:'⭐',long_edge:'🛤️',station_10:'🎯',construction_done:'🏁',deleted:'🗑️',station_repaired:'🔧',bridges_built:'🌉',bridge_upgraded:'⬆️',route_created:'🧭',route_closed:'🚫',depot_built:'🏭',train_bought:'🚆',train_ready:'✅',train_assigned:'🎫',starter_pack:'🎁',restart_grant:'🛟',branch_built:'🔩',station_inserted:'📍',company_updated:'🏢'};
const PUBLIC_EV=new Set(Object.keys(EV_ICON));
const FALLBACK_TYPES=[
 {key:'wood',label:'Деревянный',speed:30,pct:.30,material:'wood',rail_only:false,cost_river:150,cost_rail:120},
 {key:'wood_reinforced',label:'Укреплённый деревянный',speed:45,pct:.60,material:'wood',rail_only:false,cost_river:300,cost_rail:240},
 {key:'concrete_beam',label:'Бетонный балочный',speed:60,pct:1.00,material:'concrete',rail_only:false,cost_river:500,cost_rail:400},
 {key:'concrete_arch',label:'Бетонный арочный',speed:90,pct:1.50,material:'concrete',rail_only:false,cost_river:750,cost_rail:600},
 {key:'steel_beam',label:'Металлический балочный',speed:120,pct:2.20,material:'steel',rail_only:false,cost_river:1100,cost_rail:880},
 {key:'steel_arch',label:'Металлический арочный',speed:150,pct:3.20,material:'steel',rail_only:false,cost_river:1600,cost_rail:1280},
 {key:'steel_modern',label:'Современный металлический',speed:180,pct:4.00,material:'steel',rail_only:false,cost_river:2000,cost_rail:1600},
 {key:'level',label:'Глухое пересечение',speed:40,pct:.25,material:'level',rail_only:true,cost_river:125,cost_rail:100}
];
const el=id=>document.getElementById(id);
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmt=n=>Number(n||0).toLocaleString('ru-RU');
const money=n=>fmt(Math.round(Number(n)||0))+' ₽';
const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));

/* ============================================================
   СОСТОЯНИЕ
   ============================================================ */
let me=null;
let world={stations:[],nodes:[],edges:[],routes:[],depots:[],trains:[]};
let companies=[], palette=PALETTE.slice(), myPolicies={allow_trackage:0,allow_stops:0,allow_connect:0};
let constants={node_snap_m:30,trackage_share:.30};
let bridgeTypes=FALLBACK_TYPES.slice(), btMap={}, defaultBridge='concrete_beam';
let pricing={station:COST_STATION,edge:COST_EDGE_M,depot:1500,train:TRAIN_COST,node:COST_NODE};
let lastEco=null, lastPoll=null;
let lastVersion=-1, polling=false, pollTimer=null, srvOffset=0, tickLeft=0, curDay=1, lastSrvAt=0;
let mode='idle';
let edgeDraft={start:null,points:[]};
let routeDraft={stops:[],loop:false};
let mouseLL=null, snapHit=null, ghost=null, tempLine=null, tempTip=null, osmTip=null, osmTimer=null, routeQuoteTimer=null;
let edgeQuote=null, routeQuote=null, pendingSplit=null;
let map=null, baseTiles=null, gridLayer=null, zoneLayer=null, edgeLayer=null, edgeHitLayer=null, routeLayer=null, routeHitLayer=null,
    bridgeLayer=null, stationLayer=null, nodeLayer=null, depotLayer=null, labelLayer=null, draftLayer=null, previewLayer=null, hintLayer=null, fxLayer=null;
let trainMarkers=new Map(), animRunning=false, lastFrame=0, statusT=null;
let idxSt=new Map(), idxNd=new Map(), idxEp=new Map(), idxEd=new Map(), idxDp=new Map(), idxRt=new Map();
let lots=[], sellState={stations:[],edges:[],nodes:[],depots:[],trains:[],min:0,max:0};
let myPos=null, gpsWatch=null, gpsCircle=null;
let chatTimer=null, chatLast=0, newsSeen=0, newsUnread=0;
let chart=null, chartMetric='net', chartCache={h:null,c:null};
let tickerQ=[], tickerT=null;
let lastCtx={x:120,y:120,ll:null};
let palSel=null;
const UI_DEF={grid:false,snap:false,step:50,routes:true,trains:true,bridges:true,nodes:true,labels:true,foreign:true,fade:false,afford:false,sound:true,
  tspeed:8,dwell:3.5,accel:1.6,tlen:18,express:false,tlabels:false,paused:false,style:'voyager',opacity:100};
let ui={...UI_DEF};

/* ============================================================
   ЗВУК
   ============================================================ */
const SFX={ctx:null,master:null,ready:false,
 unlock(){ if(this.ready)return; try{const AC=window.AudioContext||window.webkitAudioContext; if(!AC)return; this.ctx=new AC(); this.master=this.ctx.createGain(); this.master.gain.value=.12; this.master.connect(this.ctx.destination); this.ready=true;}catch(e){} },
 tone(f,t0,d,type='sine',v=1,slide=0){ if(!this.ctx)return; const o=this.ctx.createOscillator(),g=this.ctx.createGain(); o.type=type; const t=this.ctx.currentTime+t0; o.frequency.setValueAtTime(f,t); if(slide)o.frequency.exponentialRampToValueAtTime(Math.max(30,f+slide),t+d); g.gain.setValueAtTime(.0001,t); g.gain.exponentialRampToValueAtTime(Math.max(.0002,v),t+.014); g.gain.exponentialRampToValueAtTime(.0001,t+d); o.connect(g); g.connect(this.master); o.start(t); o.stop(t+d+.05); },
 play(n){ if(!ui.sound||!this.ready||!this.ctx)return; if(this.ctx.state==='suspended')this.ctx.resume();
  switch(n){
   case 'click': this.tone(1350,0,.05,'triangle',.22); break;
   case 'snap': this.tone(1950,0,.03,'triangle',.18); break;
   case 'build': this.tone(210,0,.09,'square',.38); this.tone(320,.09,.12,'square',.32); this.tone(640,.2,.16,'triangle',.28); break;
   case 'cash': this.tone(880,0,.08,'sine',.42); this.tone(1320,.08,.14,'sine',.36); break;
   case 'error': this.tone(165,0,.19,'sawtooth',.32,-60); break;
   case 'chat': this.tone(1040,0,.07,'sine',.24); this.tone(1560,.07,.09,'sine',.18); break;
   case 'day': this.tone(520,0,.1,'triangle',.26); this.tone(780,.1,.14,'triangle',.22); break;
   case 'whistle': this.tone(720,0,.28,'sawtooth',.16,90); this.tone(960,.03,.3,'sine',.14,60); break;
   case 'route': this.tone(440,0,.08,'triangle',.3); this.tone(660,.08,.1,'triangle',.28); this.tone(880,.18,.16,'sine',.24); break;
   case 'stop': this.tone(430,0,.1,'sine',.13); this.tone(300,.09,.16,'sine',.11); break;
   case 'pass': this.tone(880,0,.13,'sine',.07,-140); break;
  } }
};
document.addEventListener('pointerdown',()=>SFX.unlock(),{once:true});
document.addEventListener('keydown',()=>SFX.unlock(),{once:true});
function soundIcon(){ const b=el('btnSound'); b.textContent=ui.sound?'🔊':'🔇'; b.classList.toggle('on',ui.sound); b.title=ui.sound?'Звук включён':'Звук выключен'; }
el('btnSound').onclick=()=>{ ui.sound=!ui.sound; soundIcon(); saveUI(); if(ui.sound){SFX.unlock();SFX.play('click');} };

/* ============================================================
   ЛОКАЛЬНЫЕ ЗЕРКАЛА ЭКОНОМИКИ
   ============================================================ */
function bonusPct(b){b=b||0;const t=[[0,0],[5,5],[10,10],[19,15],[29,20],[39,25],[45,30],[52,35],[75,40],[87,45],[99,50],[119,55],[139,60],[159,65],[179,70],[199,75],[219,80],[239,85],[259,90],[279,95]];for(const [n,p] of t) if(b<=n) return p; return 100;}
function stUpgCost(c){return Math.ceil(c*1.25*50);}
function stRadius(c){return ST_R_BASE+(Math.max(1,c)-1)*ST_R_CLASS;}
function stReplace(s){let v=COST_STATION;for(let c=1;c<(s.class||1);c++)v+=stUpgCost(c);return v;}
function dpReplace(d){let v=1500;for(let l=1;l<(d.level||1);l++)v+=DEPOT_UPG_COST*l;return v;}
function hav(a,b,c,d){const R=6371000,dLa=(c-a)*Math.PI/180,dLo=(d-b)*Math.PI/180;const x=Math.sin(dLa/2)**2+Math.cos(a*Math.PI/180)*Math.cos(c*Math.PI/180)*Math.sin(dLo/2)**2;return R*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x));}
function nearMe(o){return myPos&&o?hav(myPos.lat,myPos.lng,o.lat,o.lng)<=REPAIR_FREE_M:false;}
function stTracks(sid){let t=0;world.edges.forEach(e=>{const w=(e.double||e.upgrade_type==='double')?2:1;if(e.from===sid)t+=w;if(e.to===sid)t+=w;});return t;}
function stUpkeep(s){if(s.is_building||s.frozen)return 0;return (UPK_ST_BASE+UPK_ST_CLASS*Math.max(1,s.class||1))*(s.served?1:s.passed?TRANSIT_UPK:CONSERV);}
function ndUpkeep(n){if(n.frozen)return 0;return UPK_NODE*(n.served?1:CONSERV);}
function edUpkeep(e){if(e.is_building||e.frozen)return 0;const tr=e.double?2:1,len=Number(e.length||0);return INCOME_TRACK*2*tr*(1+len/1000)*(e.electric?1.2:1)*UPK_EDGE_SHARE*(e.served?1:CONSERV);}
function dpUpkeep(d){if(d.is_building||d.frozen)return 0;return (UPK_DEPOT_BASE+UPK_DEPOT_LVL*((d.level||1)-1))*(d.served?1:CONSERV);}
function trUpkeep(t){if(t.frozen)return 0;return t.status==='running'?TRAIN_UPK_RUN:TRAIN_UPK_IDLE;}
function bridgeCost(kind,type){const t=btMap[type]||btMap[defaultBridge];if(!t)return 500;return kind==='rail'?t.cost_rail:t.cost_river;}
function bridgeSpeed(type){const t=btMap[type]||btMap[defaultBridge];return t?t.speed:60;}
function bridgeMat(type){const t=btMap[type]||btMap[defaultBridge];return t?t.material:'concrete';}
function bridgeLabel(type){const t=btMap[type]||btMap[defaultBridge];return t?t.label:'—';}
function setTypes(arr){bridgeTypes=(arr&&arr.length?arr:FALLBACK_TYPES).slice();btMap={};bridgeTypes.forEach(t=>btMap[t.key]=t);if(!btMap[defaultBridge])defaultBridge='concrete_beam';}
function srvNow(){return Date.now()/1000+srvOffset;}
function left(end){return Math.max(0,Math.ceil(Number(end||0)-srvNow()));}
function dur(s){s=Math.max(0,Math.round(s));if(s<60)return s+'с';const m=Math.floor(s/60);if(m<60)return m+'м '+(s%60?(s%60)+'с':'');const h=Math.floor(m/60);return h+'ч '+(m%60)+'м';}
function companyColor(pid){const c=companies.find(x=>x.id===pid);return c?c.color:(me&&me.id===pid?me.color:PALETTE[(Math.max(1,pid)-1)%PALETTE.length]);}
function companyName(pid){const c=companies.find(x=>x.id===pid);return c?c.name:(me&&me.id===pid?me.name:'игрок #'+pid);}
function accessLabel(a){return a==='open'?'открыт всем':a==='closed'?'закрыт':'как в компании';}
function accessStateLabel(st){return{own:'ваш',open:'открыт',policy:'по политике',closed:'закрыт',denied:'запрещён'}[st]||st;}

/* ============================================================
   API / ТОСТЫ / МОДАЛКИ
   ============================================================ */
async function api(action,method='GET',body=null){
  const o={method,headers:{}};
  if(body){o.headers['Content-Type']='application/json';o.body=JSON.stringify(body);}
  const r=await fetch(API+'?action='+action,o);
  const txt=await r.text();
  let j; try{j=JSON.parse(txt);}catch(e){throw new Error(txt.replace(/<[^>]*>/g,'').trim().slice(0,180)||('HTTP '+r.status));}
  if(!r.ok) throw new Error(j.error||('HTTP '+r.status));
  return j;
}
function toast(t){ if(/^Ошибка|⛔|Нельзя/.test(t))SFX.play('error'); tickerQ.push(t); if(!tickerT)nextToast(); }
function nextToast(){
  if(!tickerQ.length){el('ticker').style.display='none';tickerT=null;chromeH();return;}
  el('ticker').style.display='flex'; chromeH();
  const t=tickerQ.shift(), c=el('tickerIn');
  c.textContent='📢 '+t; c.style.animation='none'; void c.offsetHeight; c.style.animation='marquee 18s linear infinite';
  tickerT=setTimeout(nextToast,Math.max(9000,t.length*180));
}
function chromeH(){ const h=el('topbar').offsetHeight+(el('ticker').style.display==='flex'?el('ticker').offsetHeight:0); document.documentElement.style.setProperty('--chrome-h',h+'px'); }
function loading(t='Загрузка…'){el('loadText').textContent=t;el('loading').style.display='flex';if(map){map.dragging.disable();map.doubleClickZoom.disable();}}
function unloading(){el('loading').style.display='none';if(map){map.dragging.enable();if(mode!=='edge')map.doubleClickZoom.enable();}}
function modal({title,body,input=null,ok='ОК',cancel='Отмена',showCancel=true,danger=false,disabled=false,wide=false,onOk,onClose=null,onMount=null}){
  const dlg=el('actionDialog'); dlg.classList.toggle('wide',!!wide);
  el('actionTitle').textContent=title;
  const b=el('actionBody'); b.innerHTML=body;
  const inp=input!==null?el('modalInput'):null; if(inp)inp.value=input;
  const bo=el('actionOk'), bc=el('actionCancel');
  bo.textContent=ok; bo.classList.toggle('danger',!!danger); bo.disabled=!!disabled;
  bc.textContent=cancel; bc.style.display=showCancel?'':'none';
  const close=()=>{el('actionModal').classList.remove('open');bo.onclick=null;bc.onclick=null;bo.disabled=false;if(onClose)onClose();};
  bo.onclick=()=>{ if(inp){const v=inp.value.trim(); if(!v){toast('Поле не может быть пустым');return;} close(); onOk&&onOk(v);} else {close(); onOk&&onOk();} };
  bc.onclick=close;
  el('actionModal').classList.add('open');
  if(onMount)onMount(b);
  if(inp)setTimeout(()=>{inp.focus();inp.select();},60);
}
function choiceModal({title,items,ok='Выбрать',empty='Нет доступных вариантов',onOk}){
  if(!items.length){toast(empty);return;}
  const first=items.findIndex(x=>!x.disabled);
  const body=items.map((it,i)=>`<label class="choice${it.disabled?' dis':''}"><input type="radio" name="ch" value="${esc(it.value)}" ${it.disabled?'disabled':''} ${i===first?'checked':''}><span><span class="cn">${esc(it.label)}</span><span class="cs">${esc(it.sub||'')}</span></span></label>`).join('');
  modal({title,body,ok,onOk:()=>{const s=el('actionBody').querySelector('input[name=ch]:checked');if(!s){toast('Ничего не выбрано');return;}onOk(s.value);}});
}
document.querySelectorAll('[data-close]').forEach(b=>b.onclick=()=>{
  const id=b.dataset.close; el(id).classList.remove('open');
  if(id==='chartModal'&&chart){chart.destroy();chart=null;chartCache={h:null,c:null};}
});
document.querySelectorAll('.modal').forEach(m=>m.addEventListener('mousedown',e=>{
  if(e.target!==m)return;
  if(m.id==='loginModal'||m.id==='actionModal')return;
  m.classList.remove('open');
  if(m.id==='chartModal'&&chart){chart.destroy();chart=null;chartCache={h:null,c:null};}
}));

/* ============================================================
   НАСТРОЙКИ UI
   ============================================================ */
function loadUI(){
  try{const s=JSON.parse(localStorage.getItem('railUI2')||'{}');Object.keys(UI_DEF).forEach(k=>{if(s[k]!==undefined)ui[k]=s[k];});}catch(e){}
  ui.tspeed=clamp(Number(ui.tspeed)||0,0,30); ui.dwell=clamp(Number(ui.dwell)||0,0,12);
  ui.accel=clamp(Number(ui.accel)||1.6,.3,5); ui.tlen=clamp(Number(ui.tlen)||18,10,34);
  syncMotionUI();
  el('gridOn').checked=ui.grid; el('gridSnap').checked=ui.snap; el('gridStep').value=ui.step;
  el('tgRoutes').checked=ui.routes; el('tgTrains').checked=ui.trains; el('tgBridges').checked=ui.bridges;
  el('tgNodes').checked=ui.nodes; el('tgLabels').checked=ui.labels; el('tgForeign').checked=ui.foreign;
  el('tgFade').checked=ui.fade; el('tgAfford').checked=ui.afford;
  el('mapStyle').value=ui.style; el('mapOpacity').value=ui.opacity; el('opVal').textContent=ui.opacity;
  soundIcon();
}
function syncMotionUI(){
  el('moSpeed').value=ui.tspeed; el('moSpeedVal').textContent=ui.tspeed;
  el('moDwell').value=ui.dwell; el('moDwellVal').textContent=Number(ui.dwell).toFixed(1);
  el('moAccel').value=ui.accel; el('moAccelVal').textContent=Number(ui.accel).toFixed(1);
  el('moLen').value=ui.tlen; el('moLenVal').textContent=ui.tlen;
  el('moExpress').checked=!!ui.express; el('moLabels').checked=!!ui.tlabels; el('moPause').checked=!!ui.paused;
}
function saveUI(){ try{localStorage.setItem('railUI2',JSON.stringify(ui));}catch(e){} }
el('resetSettings').onclick=()=>{ui={...UI_DEF};loadUI();saveUI();if(map){setBase(ui.style);setOpacity(ui.opacity);drawGrid();render();}toast('Настройки сброшены');};

/* ============================================================
   КАРТА
   ============================================================ */
function initMap(){
  map=L.map('map',{center:[BASE_LAT,BASE_LNG],zoom:12,zoomControl:true,attributionControl:true});
  L.control.scale({metric:true,imperial:false,position:'bottomleft'}).addTo(map);
  loadUI(); setBase(ui.style); setOpacity(ui.opacity);
  const pane=(n,z,pe)=>{const p=map.createPane(n);p.style.zIndex=z;if(pe)p.style.pointerEvents=pe;return p;};
  pane('gridP',230,'none'); pane('zoneP',235,'none'); pane('hintP',245,'none');
  pane('routeP',415,'none'); pane('waterP',425,'none'); pane('bridgeP',430,'none'); pane('prevP',440,'none');
  pane('edgeHitP',445); pane('routeHitP',450); pane('bridgeHitP',455);
  pane('fxP',470,'none'); pane('stP',500); pane('nodeP',510); pane('lblP',650); pane('trainP',660,'none');
  gridLayer=L.layerGroup().addTo(map); zoneLayer=L.layerGroup().addTo(map); hintLayer=L.layerGroup().addTo(map);
  edgeLayer=L.layerGroup().addTo(map); edgeHitLayer=L.layerGroup().addTo(map);
  routeLayer=L.layerGroup().addTo(map); routeHitLayer=L.layerGroup().addTo(map);
  bridgeLayer=L.layerGroup().addTo(map);
  stationLayer=L.layerGroup().addTo(map); nodeLayer=L.layerGroup().addTo(map); depotLayer=L.layerGroup().addTo(map); labelLayer=L.layerGroup().addTo(map);
  draftLayer=L.layerGroup().addTo(map); previewLayer=L.layerGroup().addTo(map); fxLayer=L.layerGroup().addTo(map);
  loadZones();
  map.on('click',onMapClick);
  map.on('mousemove',onMapMove);
  map.on('mouseout',()=>{mouseLL=null;snapHit=null;clearGhost();});
  map.on('zoomend',()=>{drawGrid();renderRoutes();renderBridges();renderPreview();});
  map.on('moveend',drawGrid);
  drawGrid(); startAnim();
  setTimeout(()=>map&&map.invalidateSize(),120);
  setTimeout(()=>map&&map.invalidateSize(true),520);
  el('mapStyle').onchange=e=>{ui.style=e.target.value;setBase(ui.style);saveUI();};
  el('mapOpacity').oninput=e=>{ui.opacity=+e.target.value;el('opVal').textContent=ui.opacity;setOpacity(ui.opacity);saveUI();};
  el('gridOn').onchange=e=>{ui.grid=e.target.checked;drawGrid();saveUI();};
  el('gridSnap').onchange=e=>{ui.snap=e.target.checked;saveUI();};
  el('gridStep').onchange=e=>{ui.step=+e.target.value;drawGrid();saveUI();};
  el('tgRoutes').onchange=e=>{ui.routes=e.target.checked;renderRoutes();saveUI();};
  el('tgTrains').onchange=e=>{ui.trains=e.target.checked;syncTrains();saveUI();};
  el('tgBridges').onchange=e=>{ui.bridges=e.target.checked;renderBridges();saveUI();};
  el('tgNodes').onchange=e=>{ui.nodes=e.target.checked;renderNodes();saveUI();};
  el('tgLabels').onchange=e=>{ui.labels=e.target.checked;renderStations();renderNodes();renderDepots();saveUI();};
  el('tgForeign').onchange=e=>{ui.foreign=e.target.checked;renderRoutes();saveUI();};
  el('tgFade').onchange=e=>{ui.fade=e.target.checked;renderEdges();saveUI();};
  el('tgAfford').onchange=e=>{ui.afford=e.target.checked;render();saveUI();};
  el('btnFit').onclick=fitAll;
  bindMotion();
}
function bindMotion(){
  el('moSpeed').oninput=e=>{ui.tspeed=+e.target.value;el('moSpeedVal').textContent=ui.tspeed;saveUI();};
  el('moDwell').oninput=e=>{ui.dwell=+e.target.value;el('moDwellVal').textContent=ui.dwell.toFixed(1);saveUI();};
  el('moAccel').oninput=e=>{ui.accel=+e.target.value;el('moAccelVal').textContent=ui.accel.toFixed(1);saveUI();};
  el('moLen').oninput=e=>{ui.tlen=+e.target.value;el('moLenVal').textContent=ui.tlen;refreshTrainIcons();saveUI();};
  el('moExpress').onchange=e=>{ui.express=e.target.checked;saveUI();refreshTrainPlans();renderRoutes();toast(ui.express?'⏩ Экспресс: остановки только на конечных':'🚉 Остановки на всех станциях маршрута');};
  el('moLabels').onchange=e=>{ui.tlabels=e.target.checked;refreshTrainIcons();saveUI();};
  el('moPause').onchange=e=>{ui.paused=e.target.checked;saveUI();updateTrainStatuses();};
  el('moReset').onclick=()=>{Object.assign(ui,{tspeed:UI_DEF.tspeed,dwell:UI_DEF.dwell,accel:UI_DEF.accel,tlen:UI_DEF.tlen,express:false,tlabels:false,paused:false});
    syncMotionUI();saveUI();refreshTrainIcons();refreshTrainPlans();renderRoutes();SFX.play('click');toast('↺ Параметры движения сброшены');};
}
function setBase(style){
  if(baseTiles)map.removeLayer(baseTiles);
  let url,attr='© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
  if(style==='osm'){url='https://tile.openstreetmap.org/{z}/{x}/{y}.png';}
  else if(style==='positron'){url=`https://basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png?key=${CARTO_KEY}`;attr+=' © <a href="https://carto.com/attributions">CARTO</a>';}
  else{url=`https://basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=${CARTO_KEY}`;attr+=' © <a href="https://carto.com/attributions">CARTO</a>';}
  baseTiles=L.tileLayer(url,{attribution:attr,subdomains:'abcd',maxZoom:20}).addTo(map);
  setOpacity(ui.opacity);
}
function setOpacity(p){ if(baseTiles)baseTiles.setOpacity(p/100); }
function drawGrid(){
  if(!map||!gridLayer)return; gridLayer.clearLayers(); if(!ui.grid)return;
  const b=map.getBounds(),cl=map.getCenter().lat;
  let step=ui.step,la=step/111320,lo=step/(111320*Math.cos(cl*Math.PI/180)),g=0;
  while(((b.getNorth()-b.getSouth())/la>110||(b.getEast()-b.getWest())/lo>110)&&g<12){step*=2;la=step/111320;lo=step/(111320*Math.cos(cl*Math.PI/180));g++;}
  const st={pane:'gridP',color:'#5b7ba6',weight:1,opacity:.28,interactive:false};
  for(let y=Math.ceil(b.getSouth()/la)*la;y<=b.getNorth();y+=la)L.polyline([[y,b.getWest()],[y,b.getEast()]],st).addTo(gridLayer);
  for(let x=Math.ceil(b.getWest()/lo)*lo;x<=b.getEast();x+=lo)L.polyline([[b.getSouth(),x],[b.getNorth(),x]],st).addTo(gridLayer);
}
function snap(ll){
  if(!ui.snap)return ll;
  const la=ui.step/111320,lo=ui.step/(111320*Math.cos(ll.lat*Math.PI/180));
  return L.latLng(Math.round(ll.lat/la)*la,Math.round(ll.lng/lo)*lo);
}
async function loadZones(){
  try{
    const r=await fetch('zzoness.json'); if(!r.ok)return; const d=await r.json(); if(!d.features)return;
    const st={pane:'zoneP',color:'#c9302f',weight:2,fillColor:'#ff5a5a',fillOpacity:.1,dashArray:'6 5',interactive:false};
    d.features.forEach(f=>{
      const add=c=>L.polygon(c.map(x=>[x[1],x[0]]),st).addTo(zoneLayer);
      if(f.geometry.type==='Polygon')add(f.geometry.coordinates[0]);
      else if(f.geometry.type==='MultiPolygon')f.geometry.coordinates.forEach(p=>add(p[0]));
    });
  }catch(e){}
}
function fitAll(){
  if(!map)return;
  const pts=[...world.stations,...world.nodes,...world.depots];
  if(!pts.length){map.setView([BASE_LAT,BASE_LNG],12);return;}
  let a=Infinity,b=Infinity,c=-Infinity,d=-Infinity;
  pts.forEach(s=>{a=Math.min(a,s.lat);b=Math.min(b,s.lng);c=Math.max(c,s.lat);d=Math.max(d,s.lng);});
  const pa=Math.max(.02,(c-a)*.22),pb=Math.max(.02,(d-b)*.22);
  map.fitBounds([[c+pa,b-pb],[a-pa,d+pb]],{padding:[26,26]});
}
function fxPulse(lat,lng,color,small){
  if(!map||!fxLayer)return;
  const size=small?22:32;
  const m=L.marker([lat,lng],{pane:'fxP',interactive:false,keyboard:false,
    icon:L.divIcon({html:`<div class="fx${small?' sm':''}" style="color:${color}"><i></i></div>`,className:'',iconSize:[size,size],iconAnchor:[size/2,size/2]})}).addTo(fxLayer);
  setTimeout(()=>{if(fxLayer)fxLayer.removeLayer(m);},small?540:760);
}

/* ============================================================
   ИНДЕКСЫ / ГЕОМЕТРИЯ
   ============================================================ */
function reindex(){
  idxSt=new Map();idxNd=new Map();idxEp=new Map();idxEd=new Map();idxDp=new Map();idxRt=new Map();
  world.stations.forEach(s=>{idxSt.set(s.id,s);idxEp.set(s.id,{...s,kind:'station'});});
  world.nodes.forEach(n=>{idxNd.set(n.id,n);idxEp.set(n.id,{...n,kind:'node'});});
  world.edges.forEach(e=>idxEd.set(e.id,e));
  world.depots.forEach(d=>idxDp.set(d.id,d));
  world.routes.forEach(r=>idxRt.set(r.id,r));
}
const st=id=>idxSt.get(id)||null;
const ep=id=>idxEp.get(id)||null;
function epName(id){const o=ep(id);return o?(o.kind==='node'?'узел '+o.name:o.name):'?';}
function edgeLL(e){const a=ep(e.from),b=ep(e.to);if(!a||!b)return null;const p=[[a.lat,a.lng]];(e.points||[]).forEach(x=>p.push([x.lat,x.lng]));p.push([b.lat,b.lng]);return p;}
function pathOf(pts){let tot=0;const cum=[0];for(let i=0;i<pts.length-1;i++){tot+=hav(pts[i][0],pts[i][1],pts[i+1][0],pts[i+1][1]);cum.push(tot);}return{pts,cum,total:tot};}
function pointAt(g,d){
  d=clamp(d,0,g.total);
  for(let i=0;i<g.pts.length-1;i++){
    if(d<=g.cum[i+1]||i===g.pts.length-2){
      const s=g.cum[i],e=g.cum[i+1],t=e>s?(d-s)/(e-s):0;
      const A=g.pts[i],B=g.pts[i+1];
      return{lat:A[0]+(B[0]-A[0])*t,lng:A[1]+(B[1]-A[1])*t,bearing:bearing(A,B)};
    }
  }
  const P=g.pts[g.pts.length-1];return{lat:P[0],lng:P[1],bearing:0};
}
function bearing(A,B){const l=Math.cos((A[0]+B[0])/2*Math.PI/180);return Math.atan2((B[1]-A[1])*l,B[0]-A[0])*180/Math.PI;}
function mpp(lat){return 40075016.686*Math.cos(lat*Math.PI/180)/(256*Math.pow(2,map.getZoom()));}
function offsetLL(lat,lng,dx,dy){return[lat+dy/111320,lng+dx/(111320*Math.cos(lat*Math.PI/180))];}
function offsetPath(pts,px){
  if(!px)return pts;
  const m=mpp(pts[0][0])*px;
  return pts.map((p,i)=>{
    const a=pts[Math.max(0,i-1)],b=pts[Math.min(pts.length-1,i+1)];
    const l=Math.cos(p[0]*Math.PI/180);
    let dx=(b[1]-a[1])*l,dy=b[0]-a[0];const n=Math.hypot(dx,dy)||1;dx/=n;dy/=n;
    return offsetLL(p[0],p[1],dy*m,-dx*m);
  });
}
function nearestEndpoint(lat,lng,radius){
  let best=null,bd=Infinity;
  idxEp.forEach(o=>{const d=hav(lat,lng,o.lat,o.lng);if(d<=radius&&d<bd){bd=d;best=o;}});
  return best?{...o0(best),dist:bd}:null;
  function o0(x){return x;}
}
function routesOnEdge(eid){return world.routes.filter(r=>(r.path||[]).some(s=>s.e===eid)||(r.edges||[]).includes(eid));}

/* ============================================================
   ОТРИСОВКА: ПЕРЕГОНЫ
   ============================================================ */
function edgeTip(e){
  let tip=`<b>${esc(e.from_name||epName(e.from))} ↔ ${esc(e.to_name||epName(e.to))}</b><br>${Math.round(e.length)} м · ${e.double?'2 пути':'1 путь'}${e.electric?' · ⚡':''} · ${e.speed||0} км/ч`;
  tip+=`<br><span style="color:${e.color}">■</span> ${esc(companyName(e.owner_id))}`;
  if(me&&e.owner_id!==me.id)tip+=` · доступ: ${accessStateLabel(e.access_state)}${e.can_use?'':' 🔒'}`;
  if(e.is_building)tip+=`<br>🏗 строится · <span data-cd="${e.build_end}">${dur(left(e.build_end))}</span>`;
  else if(!e.served)tip+='<br>💤 не обслуживается';
  const rs=routesOnEdge(e.id);
  if(rs.length)tip+=`<br>🧭 маршрутов: ${rs.length}`;
  if((e.bridges||[]).length)tip+=`<br>🌉 сооружений: ${e.bridges.length}`;
  tip+='<br><span style="color:#9db0c9">клик — меню · режим 2 — врезка станции</span>';
  return tip;
}
function renderEdges(){
  if(!map)return; edgeLayer.clearLayers(); edgeHitLayer.clearLayers();
  const op=ui.fade?.45:.85, live=(mode==='idle'||mode==='edge');
  world.edges.forEach(e=>{
    const ll=edgeLL(e); if(!ll)return;
    // окантовка цвета владельца
    L.polyline(ll,{color:e.color||'#7d8aa0',weight:(e.double?4:2.4)+3,opacity:.32,interactive:false,smoothFactor:1}).addTo(edgeLayer);
    let color=e.double&&e.electric?'#d08010':e.double?'#1f9c5c':e.electric?'#e8a020':'#7d8aa0';
    let dash=null,opacity=op;
    if(e.is_building){color='#b9c3d2';dash='6 5';}
    else if(e.auction_status==='bankrupt'){color='#98a4b4';dash='2 5';}
    else if(e.auction_status==='manual'){color='#f5c518';dash='2 5';opacity=lotAfford(e)?1:.5;}
    else if(!e.served){opacity=op*.55;}
    if(me&&e.owner_id!==me.id&&!e.can_use&&!e.is_building){opacity=Math.min(opacity,.4);dash=dash||'3 4';}
    L.polyline(ll,{color,weight:e.double?4:2.4,opacity,dashArray:dash,smoothFactor:1,interactive:false}).addTo(edgeLayer);
    if(!live)return;
    const hit=L.polyline(ll,{pane:'edgeHitP',color:'#fff',weight:EDGE_HIT_PX,opacity:0,interactive:true,smoothFactor:1}).addTo(edgeHitLayer);
    hit.bindTooltip(edgeTip(e),{sticky:true,className:'tip'});
    hit.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxEdge(ev.originalEvent,e,ev.latlng);});
    hit.on('click',ev=>{
      L.DomEvent.stopPropagation(ev);
      if(mode==='edge'&&!edgeDraft.start){openSplitFlow(e,ev.latlng);return;}
      if(mode==='edge'&&edgeDraft.start){edgeDraft.points.push({lat:ev.latlng.lat,lng:ev.latlng.lng});SFX.play('snap');renderDraft();updateDock();return;}
      if(mode==='idle')ctxEdge(ev.originalEvent,e,ev.latlng);
    });
  });
}
function lotAfford(obj){
  if(!ui.afford||!me)return true;
  const l=lots.find(x=>x.type==='manual'&&(x.stations_preview||[]).some(s=>s.name===obj.name||(s.lat===obj.lat&&s.lng===obj.lng)));
  return !l||me.money>=l.my_price;
}

/* ============================================================
   ОТРИСОВКА: МАРШРУТЫ (цвет компании)
   ============================================================ */
function renderRoutes(){
  if(!map)return; routeLayer.clearLayers(); routeHitLayer.clearLayers(); if(!ui.routes)return;
  const usage=new Map(), live=(mode==='idle');
  world.routes.forEach(r=>{
    if(me&&r.owner_id!==me.id&&!ui.foreign)return;
    const broken=r.status==='broken', idle=r.status==='idle';
    const col=r.color||companyColor(r.owner_id);
    const steps=r.path||[];
    steps.forEach(s=>{
      const e=idxEd.get(s.e); if(!e)return;
      let ll=edgeLL(e); if(!ll)return;
      if(e.from!==s.from)ll=ll.slice().reverse();
      const n=usage.get(e.id)||0; usage.set(e.id,n+1);
      const path=offsetPath(ll,n*ROUTE_OFFSET_PX);
      L.polyline(path,{pane:'routeP',color:broken?'#ff5a5a':col,weight:idle?2.6:3.4,opacity:broken?.6:(idle?.5:.92),dashArray:broken?'5 5':(idle?'9 6':null),lineCap:'round',interactive:false}).addTo(routeLayer);
      if(!live||n===0)return;
      const hit=L.polyline(path,{pane:'routeHitP',color:col,weight:ROUTE_HIT_PX,opacity:0,interactive:true}).addTo(routeHitLayer);
      hit.bindTooltip(`<b>${esc(r.name)}</b> · <span style="color:${col}">■</span> ${esc(companyName(r.owner_id))}<br>${broken?'⛔ разорван':idle?'💤 без поездов':'🚆 '+r.trains+' поезд(ов)'} · ${r.km} км · ${r.speed||0} км/ч<br>${money(r.income)}/д${r.fee?` · аренда ${money(r.fee)}`:''}<br><span style="color:#9db0c9">клик — меню маршрута</span>`,{sticky:true,className:'tip'});
      hit.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);ctxRoute(ev.originalEvent,r);});
      hit.on('click',ev=>{L.DomEvent.stopPropagation(ev);ctxRoute(ev.originalEvent,r);});
    });
  });
}

/* ============================================================
   ОТРИСОВКА: МОСТЫ
   ============================================================ */
function bridgeSymbol(br,edge,layer,preview=false){
  if(!map||!layer||!br||!isFinite(br.lat)||!isFinite(br.lng))return;
  const building=!!(edge&&edge.is_building);
  const m=mpp(br.lat), span=Number(br.span)||60;
  const half=Math.max(span/2,BRIDGE_MIN_PX/2*m);
  const offPx=BRIDGE_OFF_PX+(edge&&edge.double?1.5:0), off=offPx*m, wing=BRIDGE_WING_PX*m;
  const b=(Number(br.bearing)||0)*Math.PI/180;
  const ux=Math.sin(b),uy=Math.cos(b),px=Math.cos(b),py=-Math.sin(b);
  const P=(al,si)=>offsetLL(br.lat,br.lng,ux*al+px*si,uy*al+py*si);
  const W=(es,ss)=>{const base=P(half*es,off*ss);
    const dx=(ux*es*Math.sin(BRIDGE_WING_A)+px*ss*Math.cos(BRIDGE_WING_A))*wing;
    const dy=(uy*es*Math.sin(BRIDGE_WING_A)+py*ss*Math.cos(BRIDGE_WING_A))*wing;
    return[base,offsetLL(base[0],base[1],dx,dy)];};
  const segs=[[P(-half,off),P(half,off)],[P(-half,-off),P(half,-off)],W(1,1),W(1,-1),W(-1,1),W(-1,-1)];
  const axis=[P(-half,0),P(half,0)];
  const col=preview?'#ffb02e':(MAT_COLOR[bridgeMat(br.type)]||'#4a7fc1');
  const isRail=br.kind==='rail';
  L.polyline(axis,{pane:'waterP',color:isRail?'#7d8aa0':'#3f7fbf',weight:Math.max(4,offPx*2-2),opacity:preview?.2:(building?.14:.3),lineCap:'butt',interactive:false}).addTo(layer);
  L.polyline(segs,{pane:'bridgeP',color:'#fff',weight:4.6,opacity:preview?.5:.9,lineCap:'round',interactive:false}).addTo(layer);
  L.polyline(segs,{pane:'bridgeP',color:col,weight:2.6,opacity:building?.55:1,dashArray:(building||preview)?'4 3':null,lineCap:'round',interactive:false}).addTo(layer);
  if(preview||mode!=='idle')return;
  const hit=L.polyline(axis,{pane:'bridgeHitP',weight:offPx*2+11,opacity:0,interactive:true}).addTo(layer);
  hit.bindTooltip(`<b>${isRail?'🌉 Путепровод':'🌉 Мост'}</b> · ${esc(br.name||'')}<br>${esc(bridgeLabel(br.type))} · ${bridgeSpeed(br.type)} км/ч<br>пролёт ${Math.round(span)} м${br.angle?` · угол ${br.angle}°`:''}${building?'<br>🏗 строится':''}`,{sticky:true,className:'tip'});
  if(edge){
    hit.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);ctxEdge(ev.originalEvent,edge,ev.latlng);});
    hit.on('click',ev=>{L.DomEvent.stopPropagation(ev);ctxEdge(ev.originalEvent,edge,ev.latlng);});
  }
}
function renderBridges(){
  if(!map)return; bridgeLayer.clearLayers(); if(!ui.bridges)return;
  world.edges.forEach(e=>(e.bridges||[]).forEach(br=>bridgeSymbol(br,e,bridgeLayer)));
}
function renderPreview(){
  if(!map)return; previewLayer.clearLayers();
  if(edgeQuote)[...(edgeQuote.bridges||[]),...(edgeQuote.overpasses||[])].forEach(br=>bridgeSymbol(br,null,previewLayer,true));
  if(pendingSplit)L.circleMarker([pendingSplit.lat,pendingSplit.lng],{pane:'prevP',radius:12,color:'#a97bff',weight:3,fillColor:'#a97bff',fillOpacity:.2,interactive:false}).addTo(previewLayer);
}

/* ============================================================
   ОТРИСОВКА: СТАНЦИИ / УЗЛЫ / ДЕПО
   ============================================================ */
function labelIcon(o,kind){
  const mine=me&&o.owner_id===me.id;
  const d=document.createElement('div');
  if(kind==='depot'){
    d.className='dlbl'+(mine?' drag':'')+(o.is_building?' build':'');
    d.textContent='🏭 '+o.name+(o.is_building?' (стройка)':'');
  }else{
    let c='lbl';
    if(mine){c+=' mine drag';d.style.setProperty('--oc',o.color||'#1f9c5c');d.style.borderColor=o.color||'#1f9c5c';d.style.color=o.color||'#14713f';}
    if(o.is_building)c+=' build';
    else if(o.auction_status==='manual')c+=' sale';
    else if(o.auction_status==='bankrupt')c+=' bank';
    else if(!o.served&&!o.passed)c+=' dead';
    d.className=c;
    d.textContent=o.name+(o.is_building?' 🏗':(mine&&!o.served&&!o.passed?' 💤':''));
  }
  const off=o.labelOffset||{x:10,y:4};
  return L.divIcon({html:d,className:'',iconSize:null,iconAnchor:[-(off.x??10),-(off.y??4)]});
}
function depotIcon(d){
  const c=d.is_building?'#ffb02e':(d.auction_status?'#f5c518':(d.color||'#24d3c4'));
  const html=`<svg class="depot-ic" width="26" height="26" viewBox="0 0 26 26">
    <path d="M3 12 L13 4 L23 12 V22 H3 Z" fill="${c}" stroke="#07101c" stroke-width="1.6" stroke-linejoin="round"/>
    <rect x="9" y="14" width="8" height="8" fill="#07101c" opacity=".72" rx="1"/>
    <path d="M9 17h8M9 20h8" stroke="${c}" stroke-width="1"/></svg>`;
  return L.divIcon({html,className:'',iconSize:[26,26],iconAnchor:[13,13]});
}
function nodeIcon(n){
  const own=me&&n.owner_id===me.id;
  const lbl=ui.labels?`<span class="nodeLbl">${esc(n.name)}</span>`:'';
  return L.divIcon({html:`<div class="nodeWrap"><i class="nodeSq${own?' own':''}${n.served?'':' dead'}" style="background:${n.color||'#7fa6d8'}"></i>${lbl}</div>`,className:'',iconSize:[14,14],iconAnchor:[7,7]});
}
function attachDrag(marker,o,kind){
  const root=marker.getElement&&marker.getElement(); const div=root&&root.firstElementChild; if(!div)return;
  div.addEventListener('click',ev=>ev.stopPropagation());
  div.addEventListener('pointerdown',e=>{
    if(!me||o.owner_id!==me.id||e.button!==0)return;
    e.stopPropagation();e.preventDefault();
    const sx=e.clientX,sy=e.clientY,base={x:(o.labelOffset||{}).x??10,y:(o.labelOffset||{}).y??4};
    let dx=0,dy=0; map.dragging.disable(); div.classList.add('dragging');
    try{div.setPointerCapture(e.pointerId);}catch(_){}
    const mv=ev=>{dx=ev.clientX-sx;dy=ev.clientY-sy;div.style.transform=`translate(${dx}px,${dy}px)`;};
    const up=()=>{
      div.removeEventListener('pointermove',mv);div.removeEventListener('pointerup',up);div.removeEventListener('pointercancel',up);
      map.dragging.enable();div.classList.remove('dragging');div.style.transform='';
      if(Math.abs(dx)+Math.abs(dy)>2){
        o.labelOffset={x:Math.round(base.x+dx),y:Math.round(base.y+dy)};
        marker.setIcon(labelIcon(o,kind)); attachDrag(marker,o,kind);
        api('move_label','POST',{id:o.id,kind,x:o.labelOffset.x,y:o.labelOffset.y}).catch(()=>{});
        SFX.play('snap');
      }
    };
    div.addEventListener('pointermove',mv);div.addEventListener('pointerup',up);div.addEventListener('pointercancel',up);
  });
}
function renderStations(){
  if(!map)return; stationLayer.clearLayers(); labelLayer.clearLayers();
  world.stations.forEach(s=>{
    const mine=me&&s.owner_id===me.id, ll=[s.lat,s.lng], r=Number(s.radius)||stRadius(s.class||1);
    const h=s.health??100;
    let fill=s.color||companyColor(s.owner_id),stroke=mine?'#fff':'#0b1220',dash=null,fo=1;
    if(s.is_building){fill='#ffd98a';stroke='#e0bf5c';dash='3 3';}
    else if(s.auction_status==='bankrupt'){fill='#98a4b4';stroke='#5b6c85';}
    else if(s.auction_status==='manual'){const ok=lotAfford(s);fill=ok?'#f5c518':'rgba(245,197,24,.45)';stroke='#c79f00';}
    else if(h<HEALTH_CRIT){fill='#8a94a2';}
    else if(h<50){fill='#d4a017';}
    else if(!s.served&&!s.passed){fo=.62;stroke='#c3ccd8';dash='2 3';}
    if(s.upgrading&&!s.is_building&&!s.auction_status){stroke='#ffb02e';dash='3 3';}
    const c=L.circleMarker(ll,{pane:'stP',radius:Math.max(4,r),fillColor:fill,color:stroke,weight:mine?2.5:2,fillOpacity:fo,dashArray:dash}).addTo(stationLayer);
    let tip=`<b>${esc(s.name)}</b> · <span style="color:${s.color}">■</span> ${esc(companyName(s.owner_id))}<br>класс ${s.class||1} · маршрутов ${s.routes_used||0}/${s.routes_limit||s.class||1} · путей ${s.tracks??stTracks(s.id)}<br>🏢 ${s.buildings??0} → ${bonusPct(s.buildings)}% · состояние ${h}%`;
    if(me&&!mine)tip+=`<br>остановки: ${s.can_stop?'разрешены':'🔒 запрещены'} · подключение: ${s.can_connect?'разрешено':'🔒 запрещено'}`;
    if(s.is_building)tip+=`<br>🏗 <span data-cd="${s.build_end}">${dur(left(s.build_end))}</span>`;
    else if(s.upgrading)tip+=`<br>⬆️ класс ${s.upgrade_class} · <span data-cd="${s.upgrade_end}">${dur(left(s.upgrade_end))}</span>`;
    else if(s.passed&&!s.served)tip+='<br>⏩ проездом без остановки';
    else if(!s.served)tip+='<br>💤 нет маршрутов — доход 0';
    c.bindTooltip(tip,{direction:'top',offset:[0,-r-2],className:'tip'});
    c.on('click',ev=>onStationClick(ev,s));
    c.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxStation(ev.originalEvent,s);});
    if(ui.labels){
      const m=L.marker(ll,{pane:'lblP',icon:labelIcon(s,'station'),keyboard:false}).addTo(labelLayer);
      m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxStation(ev.originalEvent,s);});
      attachDrag(m,s,'station');
    }
  });
}
function renderNodes(){
  if(!map)return; nodeLayer.clearLayers(); if(!ui.nodes)return;
  world.nodes.forEach(n=>{
    const m=L.marker([n.lat,n.lng],{pane:'nodeP',icon:nodeIcon(n),keyboard:false}).addTo(nodeLayer);
    let tip=`<b>🔩 ${esc(n.name)}</b> · <span style="color:${n.color}">■</span> ${esc(companyName(n.owner_id))}<br>перегонов: ${n.edges_count??0} · ${n.served?'в сети':'💤 простой'}`;
    if(me&&n.owner_id!==me.id)tip+=`<br>подключение: ${n.can_connect?'разрешено':'🔒 запрещено'}`;
    m.bindTooltip(tip,{direction:'top',offset:[0,-8],className:'tip'});
    m.on('click',ev=>onNodeClick(ev,n));
    m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxNode(ev.originalEvent,n);});
  });
}
function renderDepots(){
  if(!map)return; depotLayer.clearLayers();
  world.depots.forEach(d=>{
    const ll=[d.lat,d.lng];
    const m=L.marker(ll,{icon:depotIcon(d),keyboard:false}).addTo(depotLayer);
    const s=st(d.station_id);
    let tip=`<b>🏭 ${esc(d.name)}</b> · <span style="color:${d.color}">■</span> ${esc(companyName(d.owner_id))}<br>уровень ${d.level||1} · мест ${d.trains_count||0}/${d.capacity}<br>у «${esc(s?s.name:'—')}» · ${d.health??100}%`;
    if(d.is_building)tip+=`<br>🏗 <span data-cd="${d.build_end}">${dur(left(d.build_end))}</span>`;
    else if(d.upgrading)tip+=`<br>⬆️ расширение · <span data-cd="${d.upgrade_end}">${dur(left(d.upgrade_end))}</span>`;
    m.bindTooltip(tip,{direction:'top',offset:[0,-14],className:'tip'});
    m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxDepot(ev.originalEvent,d);});
    m.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxDepot(ev.originalEvent,d);});
    if(ui.labels){
      const lm=L.marker(ll,{pane:'lblP',icon:labelIcon(d,'depot'),keyboard:false}).addTo(depotLayer);
      lm.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxDepot(ev.originalEvent,d);});
      attachDrag(lm,d,'depot');
    }
  });
}

/* ============================================================
   ПОЕЗДА: ПРЯМОУГОЛЬНИКИ + ФИЗИКА
   ============================================================ */
function trainSize(){const len=clamp(ui.tlen,10,34);return{len,wid:clamp(Math.round(len*.4),4,12),box:len+8};}
function trainIcon(color){
  const s=trainSize();
  const lab=ui.tlabels?'<span class="tl"></span>':'';
  return L.divIcon({html:`<div class="tw" style="width:${s.box}px;height:${s.box}px"><i class="tb" style="width:${s.len}px;height:${s.wid}px;background:${color}"></i>${lab}</div>`,className:'',iconSize:[s.box,s.box],iconAnchor:[s.box/2,s.box/2]});
}
function refreshTrainIcons(){trainMarkers.forEach(o=>{o.marker.setIcon(trainIcon(o.color));o.body=null;o.label=null;o.rot=null;applyTrainTransform(o,true);});updateTrainStatuses();}
function bindTrainDom(o){const root=o.marker.getElement&&o.marker.getElement();o.body=root?root.querySelector('.tb'):null;o.label=root?root.querySelector('.tl'):null;}
function applyTrainTransform(o,force){
  if(!o.body||!o.body.isConnected)bindTrainDom(o);
  if(!o.body)return;
  const rot=Math.round(o.angle*10)/10;
  if(force||rot!==o.rot){o.rot=rot;o.body.style.transform=`translate(-50%,-50%) rotate(${rot}deg)`;}
  const dw=o.phase==='dwell';
  if(dw!==o.dwClass){o.dwClass=dw;o.body.classList.toggle('dw',dw);}
}
function planOf(r){
  const pts=r.geometry||[]; if(pts.length<2)return null;
  const g=pathOf(pts); if(!(g.total>1))return null;
  let marks=(r.stops_dist||[]).map((d,i)=>({dist:clamp(Number(d)||0,0,g.total),sid:(r.stations||[])[i]})).filter(m=>m.sid);
  if(marks.length<2)return null;
  if(r.loop&&marks[marks.length-1].dist<g.total-1)marks.push({dist:g.total,sid:r.stations[0]});
  let stopList=marks, transit=(r.transit||[]).map(t=>({dist:clamp(Number(t.dist)||0,0,g.total),sid:t.id}));
  if(ui.express&&marks.length>2){
    stopList=[marks[0],marks[marks.length-1]];
    marks.slice(1,-1).forEach(m=>transit.push({dist:m.dist,sid:m.sid}));
  }
  transit.sort((a,b)=>a.dist-b.dist);
  return {g,stopList,transit};
}
function computeTarget(o){
  const total=o.g.total,list=o.stopList;
  if(!list||list.length<2){o.targetDist=total;o.targetSid=null;return;}
  if(o.loop){
    o.dir=1;
    for(const s of list){if(s.dist>o.dist+.5){o.targetDist=s.dist;o.targetSid=s.sid;return;}}
    o.targetDist=list[0].dist+total;o.targetSid=list[0].sid;return;
  }
  if(o.dir>0){
    for(const s of list){if(s.dist>o.dist+.5){o.targetDist=s.dist;o.targetSid=s.sid;return;}}
    o.dir=-1;return computeTarget(o);
  }
  for(let i=list.length-1;i>=0;i--){if(list[i].dist<o.dist-.5){o.targetDist=list[i].dist;o.targetSid=list[i].sid;return;}}
  o.dir=1;return computeTarget(o);
}
function trainArrive(o){
  const total=o.g.total;
  o.dist=o.loop?((o.targetDist%total)+total)%total:clamp(o.targetDist,0,total);
  o.v=0;o.phase='dwell';o.dwellLeft=ui.dwell;o.atSid=o.targetSid;
  const s=st(o.targetSid);
  if(s){fxPulse(s.lat,s.lng,o.color,false);if(trainMarkers.size<=8)SFX.play('stop');}
  applyTrainTransform(o,true);
}
function trainDepart(o){
  const total=o.g.total;
  if(!o.loop){if(o.dist>=total-.5)o.dir=-1;else if(o.dist<=.5)o.dir=1;}
  computeTarget(o);o.phase='run';o.atSid=null;
}
function crossedMark(prev,cur,dir,m){
  if(dir>0)return cur>=prev?(m>prev&&m<=cur):(m>prev||m<=cur);
  return cur<=prev?(m<prev&&m>=cur):(m<prev||m>=cur);
}
function syncTrains(){
  if(!map)return;
  const want=new Map();
  if(ui.trains){
    let n=0;
    world.trains.forEach(t=>{
      if(t.status!=='running'||!t.route_id)return;
      const r=idxRt.get(t.route_id); if(!r||r.status!=='active')return;
      const plan=planOf(r); if(!plan)return;
      if(n++>=MAX_ANIM_TRAINS)return;
      want.set(t.id,{t,r,plan,color:r.color||t.color||'#a97bff'});
    });
  }
  trainMarkers.forEach((o,id)=>{if(!want.has(id)){map.removeLayer(o.marker);trainMarkers.delete(id);}});
  let i=0;
  want.forEach((v,id)=>{
    const ex=trainMarkers.get(id);
    if(ex){
      const changed=Math.abs(ex.g.total-v.plan.g.total)>1;
      ex.g=v.plan.g;ex.r=v.r;ex.t=v.t;ex.stopList=v.plan.stopList;ex.transit=v.plan.transit;ex.loop=!!v.r.loop;ex.color=v.color;
      ex.dist=clamp(ex.dist,0,ex.g.total);
      if(changed||ex.phase!=='dwell')computeTarget(ex);
      if(ex.body&&!ex.body.isConnected)bindTrainDom(ex);
      return;
    }
    const g=v.plan.g, dist=(i/Math.max(1,want.size))*g.total, p=pointAt(g,dist);
    const m=L.marker([p.lat,p.lng],{pane:'trainP',icon:trainIcon(v.color),keyboard:false,interactive:false}).addTo(map);
    const o={marker:m,g,r:v.r,t:v.t,stopList:v.plan.stopList,transit:v.plan.transit,loop:!!v.r.loop,color:v.color,
      dist,dir:1,v:0,phase:'run',dwellLeft:0,angle:p.bearing-90,rot:null,body:null,label:null,dwClass:false,atSid:null,lastPass:0,passSid:null,passAt:0};
    computeTarget(o);
    o.v=((v.r.speed||60)/3.6)*ui.tspeed*.5;
    trainMarkers.set(id,o);bindTrainDom(o);applyTrainTransform(o,true);
    i++;
  });
  updateTrainStatuses();
}
function refreshTrainPlans(){
  trainMarkers.forEach(o=>{
    const plan=planOf(o.r); if(!plan)return;
    o.g=plan.g;o.stopList=plan.stopList;o.transit=plan.transit;o.dist=clamp(o.dist,0,o.g.total);
    if(o.phase!=='dwell')computeTarget(o);
  });
  updateTrainStatuses();
}
function startAnim(){if(animRunning)return;animRunning=true;lastFrame=performance.now();requestAnimationFrame(frame);}
function frame(now){
  requestAnimationFrame(frame);
  const dt=Math.min(.1,(now-lastFrame)/1000); lastFrame=now;
  if(!map||document.hidden||!ui.trains||!trainMarkers.size)return;
  const mul=ui.paused?0:ui.tspeed;
  trainMarkers.forEach(o=>{
    const g=o.g; if(!g||!(g.total>1))return;
    if(mul>0){
      const vmax=((o.r.speed||60)/3.6)*mul, a=vmax/Math.max(.2,ui.accel);
      if(o.phase==='dwell'){o.dwellLeft-=dt;if(o.dwellLeft<=0)trainDepart(o);}
      else{
        const rem=Math.abs(o.targetDist-o.dist);
        const vT=Math.min(vmax,Math.sqrt(2*a*Math.max(0,rem)));
        o.v=Math.min(o.v+a*dt,Math.max(vT,Math.min(vmax*.06,vmax)));
        const step=o.v*dt;
        if(step>=rem-.05)trainArrive(o);
        else{
          const prev=o.dist;
          o.dist+=o.dir*step;
          if(o.loop)o.dist=((o.dist%g.total)+g.total)%g.total;else o.dist=clamp(o.dist,0,g.total);
          for(const tr of (o.transit||[])){
            if(!crossedMark(prev,o.dist,o.dir,tr.dist))continue;
            const s=st(tr.sid); if(!s)continue;
            fxPulse(s.lat,s.lng,'#cfe0f5',true);
            o.passSid=tr.sid;o.passAt=now;
            if(trainMarkers.size<=6&&now-o.lastPass>6000){o.lastPass=now;SFX.play('pass');}
          }
        }
      }
    }
    const p=pointAt(g,o.dist);
    o.marker.setLatLng([p.lat,p.lng]);
    const target=p.bearing-90;
    let d=((target-o.angle+90)%180+180)%180-90;
    o.angle+=d*Math.min(1,dt*9);
    applyTrainTransform(o,false);
  });
}
document.addEventListener('visibilitychange',()=>{lastFrame=performance.now();});
function trainLive(o){
  if(!o)return null;
  const mul=ui.paused?0:ui.tspeed;
  const kmh=mul>0?Math.round((o.v/mul)*3.6):0;
  const nextName=(st(o.targetSid)||{}).name||'—';
  if(mul<=0)return{cls:'pass',text:`⏸ пауза · далее «${nextName}»`};
  if(o.phase==='dwell')return{cls:'stop',text:`🛑 стоянка «${(st(o.atSid)||{}).name||'—'}» · ${Math.max(0,o.dwellLeft).toFixed(1)} с`};
  const vmax=((o.r.speed||60)/3.6)*mul;
  const rem=Math.abs(o.targetDist-o.dist);
  const braking=Math.sqrt(2*(vmax/Math.max(.2,ui.accel))*Math.max(0,rem))<vmax*.97;
  if(o.passSid&&performance.now()-o.passAt<2200)return{cls:'pass',text:`⏩ проездом «${(st(o.passSid)||{}).name||'—'}» · ${kmh} км/ч`};
  if(braking)return{cls:'stop',text:`◀ торможение · ${kmh} → «${nextName}»`};
  if(o.v<vmax*.97)return{cls:'',text:`▶ разгон · ${kmh} → «${nextName}»`};
  return{cls:'',text:`⏩ ${kmh} км/ч → «${nextName}»`};
}
function updateTrainStatuses(){
  document.querySelectorAll('[data-tstatus]').forEach(n=>{
    const live=trainLive(trainMarkers.get(n.dataset.tstatus));
    const wrap=n.closest('.item-live');
    if(!live){n.textContent='нет анимации';if(wrap)wrap.className='item-live pass';return;}
    n.textContent=live.text; if(wrap)wrap.className='item-live '+live.cls;
  });
  if(ui.tlabels)trainMarkers.forEach(o=>{
    if(!o.label||!o.label.isConnected)bindTrainDom(o);
    if(!o.label)return;
    const mul=ui.paused?0:ui.tspeed, kmh=mul>0?Math.round((o.v/mul)*3.6):0;
    o.label.textContent=o.phase==='dwell'?`${o.t.name} · стоянка ${Math.max(0,o.dwellLeft).toFixed(0)}с`:`${o.t.name} · ${kmh}`;
  });
}
function startStatus(){if(statusT)return;statusT=setInterval(updateTrainStatuses,350);}
function stopStatus(){clearInterval(statusT);statusT=null;}

/* ============================================================
   ЧЕРНОВИКИ
   ============================================================ */
function clearGhost(){
  if(ghost){map.removeLayer(ghost);ghost=null;}
  if(tempLine){map.removeLayer(tempLine);tempLine=null;}
  if(tempTip){map.removeLayer(tempTip);tempTip=null;}
  if(osmTip){map.removeLayer(osmTip);osmTip=null;}
  clearTimeout(osmTimer);
}
function renderDraft(){
  if(!map)return; draftLayer.clearLayers(); hintLayer.clearLayers();
  if(mode==='edge'&&edgeDraft.start){
    const a=edgeDraft.start;
    L.circleMarker([a.lat,a.lng],{pane:'prevP',radius:7,color:'#ff5a5a',fillColor:'#fff',fillOpacity:1,weight:2,interactive:false}).addTo(draftLayer);
    edgeDraft.points.forEach((p,i)=>{
      L.circleMarker([p.lat,p.lng],{pane:'prevP',radius:5,color:'#ff5a5a',fillColor:'#fff',fillOpacity:1,weight:2,interactive:false}).addTo(draftLayer);
      L.marker([p.lat,p.lng],{pane:'prevP',interactive:false,icon:L.divIcon({html:`<div style="font:700 10px var(--f-mono);color:#ff5a5a;background:rgba(9,15,26,.85);border-radius:5px;padding:0 4px">${i+1}</div>`,className:'',iconSize:null,iconAnchor:[-6,6]})}).addTo(draftLayer);
    });
    const ll=[[a.lat,a.lng],...edgeDraft.points.map(p=>[p.lat,p.lng])];
    if(ll.length>1)L.polyline(ll,{pane:'prevP',color:'#ff5a5a',weight:2.5,dashArray:'6 5',interactive:false}).addTo(draftLayer);
  }
  if(mode==='route'){
    const q=routeQuote;
    if(q&&q.ok&&q.geometry&&q.geometry.length>1)
      L.polyline(q.geometry,{pane:'prevP',color:'#a97bff',weight:4,opacity:.6,interactive:false}).addTo(draftLayer);
    const seq=routeDraft.loop&&routeDraft.stops.length>2?[...routeDraft.stops,routeDraft.stops[0]]:routeDraft.stops;
    seq.forEach((sid,i)=>{
      const s=st(sid); if(!s)return;
      L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:13,color:'#a97bff',weight:3,fillColor:'#a97bff',fillOpacity:.18,interactive:false}).addTo(draftLayer);
      L.marker([s.lat,s.lng],{pane:'prevP',interactive:false,icon:L.divIcon({html:`<div style="font:800 11px var(--f-mono);color:#fff;background:#a97bff;border:2px solid #07101c;border-radius:999px;width:19px;height:19px;display:flex;align-items:center;justify-content:center">${i+1}</div>`,className:'',iconSize:[19,19],iconAnchor:[9,-8]})}).addTo(draftLayer);
    });
  }
  if(mode==='depot'&&me){
    world.stations.filter(s=>!s.is_building&&(s.owner_id===me.id||s.can_connect)).forEach(s=>{
      L.circle([s.lat,s.lng],{pane:'hintP',radius:DEPOT_RADIUS,color:'#24d3c4',weight:1.5,fillColor:'#24d3c4',fillOpacity:.06,dashArray:'5 5',interactive:false}).addTo(hintLayer);
    });
  }
}

/* ============================================================
   ДОК
   ============================================================ */
function dock(html){
  const d=el('dock');
  if(!html){d.classList.remove('show');setTimeout(()=>{if(!d.classList.contains('show'))d.innerHTML='';},220);return;}
  d.innerHTML=html; d.classList.add('show');
}
function updateDock(){
  if(mode==='idle'){dock(null);el('modeBadge').classList.remove('show');return;}
  const badge=(ic,tx)=>{el('mbIc').textContent=ic;el('mbTx').textContent=tx;el('modeBadge').classList.add('show');};
  if(mode==='station'){
    badge('🚉','Постановка станции');
    dock(`<div class="dock-head"><span class="ic">🚉</span>Кликните по карте<span class="sp"><button class="btn btn-sm" id="dkCancel">Отмена · Esc</button></span></div>
      <div class="dock-stats"><span class="dstat">цена <b>${money(pricing.station)}</b></span><span class="dstat">класс 1 → <b>1 маршрут</b></span><span class="dstat">стройка <b>1 день</b></span></div>
      <div class="dock-note">Класс ограничивает только число маршрутов. Доход даёт маршрут с поездом, а не сама станция.</div>`);
    el('dkCancel').onclick=()=>setMode('idle');
  }
  else if(mode==='edge'){
    badge('🛤️','Перегон / ветка');
    const a=edgeDraft.start;
    let len=0;
    if(a){let p=[a.lat,a.lng];edgeDraft.points.forEach(q=>{len+=hav(p[0],p[1],q.lat,q.lng);p=[q.lat,q.lng];});
      if(mouseLL){const s=snap(mouseLL);len+=hav(p[0],p[1],s.lat,s.lng);}}
    const startName=a?(a.id?esc(a.name||'узел'):'точка в поле'):'—';
    dock(`<div class="dock-head"><span class="ic">🛤️</span>${a?'От «'+startName+'»':'Кликните станцию, узел или точку в поле — начало'}
      <span class="sp"><button class="btn btn-sm" id="dkCancel">Отмена · Esc</button></span></div>
      <div class="dock-stats"><span class="dstat">изгибов <b>${edgeDraft.points.length}</b></span>${a?`<span class="dstat">≈ <b>${Math.round(len)} м</b></span><span class="dstat">≈ <b>${money(Math.ceil(len*pricing.edge))}</b></span>`:''}${snapHit?`<span class="dstat good">привязка: <b>${esc(snapHit.name||'узел')}</b></span>`:''}</div>
      <div class="dock-note">Клик по станции/узлу — завершить. Клики в поле — изгибы. <b style="color:var(--ink)">Enter</b> или кнопка — конец в поле (узел ${money(pricing.node)}). Клик по перегону без начала — врезка станции.</div>
      ${a?`<div class="dock-actions"><button class="btn" id="dkUndo" ${edgeDraft.points.length?'':'disabled'}>⌫ Убрать изгиб</button><button class="btn btn-primary" id="dkFinish">⏹ Завершить в поле · Enter</button></div>`:''}`);
    el('dkCancel').onclick=()=>setMode('idle');
    if(a){
      el('dkUndo').onclick=()=>{edgeDraft.points.pop();renderDraft();updateDock();};
      el('dkFinish').onclick=finishEdgeInField;
    }
  }
  else if(mode==='depot'){
    badge('🏭','Размещение депо');
    let info='';
    if(mouseLL&&me){
      const ll=snap(mouseLL); let best=null,bd=Infinity;
      world.stations.filter(s=>!s.is_building&&(s.owner_id===me.id||s.can_connect)).forEach(s=>{const d=hav(ll.lat,ll.lng,s.lat,s.lng);if(d<bd){bd=d;best=s;}});
      info=best?`<span class="dstat ${bd<=DEPOT_RADIUS?'good':'bad'}">«${esc(best.name)}» <b>${Math.round(bd)} м</b></span>`:'<span class="dstat bad">нет доступных станций</span>';
    }
    dock(`<div class="dock-head"><span class="ic">🏭</span>Кликните в бирюзовом круге (≤ ${DEPOT_RADIUS} м)<span class="sp"><button class="btn btn-sm" id="dkCancel">Отмена</button></span></div>
      <div class="dock-stats"><span class="dstat">цена <b>${money(pricing.depot)}</b></span><span class="dstat">мест <b>${DEPOT_CAP_BASE}</b></span><span class="dstat">стройка <b>2 дня</b></span>${info}</div>
      <div class="dock-note">Можно ставить у чужой станции, если владелец разрешил подключение.</div>`);
    el('dkCancel').onclick=()=>setMode('idle');
  }
  else if(mode==='route'){
    badge('🧭','Сборка маршрута');
    const q=routeQuote;
    const chips=routeDraft.stops.map((sid,i)=>{const s=st(sid);return `<span class="schip"><i>${i+1}</i>${esc(s?s.name:'?')}<button data-rm="${i}">×</button></span>`;}).join('');
    let stats='',note='Кликайте станции по порядку — путь через узлы найдётся сам. Клик по первой замыкает кольцо.',bad=false;
    if(routeDraft.stops.length>=2&&q){
      if(q.ok){
        const inf=q.info;
        const one=q.income_by_trains&&q.income_by_trains[1]?q.income_by_trains[1].income:0;
        stats=`<span class="dstat good"><b>${(inf.km/1000).toFixed(2)} км</b></span><span class="dstat">${inf.speed} км/ч</span><span class="dstat">ниток <b>${inf.slots}</b></span><span class="dstat good">1 поезд → <b>${money(one)}/д</b></span>${inf.fee_total?`<span class="dstat warn">субаренда <b>${money(inf.fee_total)}/д</b></span>`:''}`;
        if(q.slot_warning){note='⚠ '+q.slot_warning;}
      }else{bad=true;note='⛔ '+q.reason;}
    }
    dock(`<div class="dock-head"><span class="ic">🧭</span>Остановок ${routeDraft.stops.length}${routeDraft.loop?' · кольцо':''}
      <span class="sp"><button class="btn btn-sm" id="dkLoop">${routeDraft.loop?'⟲ Разомкнуть':'⟳ Замкнуть'}</button><button class="btn btn-sm" id="dkCancel">Отмена</button></span></div>
      <div class="dock-chips">${chips||'<span class="dock-note">Пока пусто</span>'}</div>
      ${stats?`<div class="dock-stats">${stats}</div>`:''}
      <div class="dock-note${bad?' bad':''}">${esc(note)}</div>
      <div class="dock-actions"><button class="btn btn-violet" id="dkCreate" ${(!q||!q.ok)?'disabled':''}>🧭 Открыть маршрут</button></div>`);
    el('dkCancel').onclick=()=>setMode('idle');
    el('dkLoop').onclick=()=>{if(routeDraft.stops.length<3){toast('Для кольца нужно ≥3 станции');return;}routeDraft.loop=!routeDraft.loop;SFX.play('click');renderDraft();quoteRoute();};
    el('dkCreate').onclick=createRouteFlow;
    el('dock').querySelectorAll('[data-rm]').forEach(b=>b.onclick=()=>{routeDraft.stops.splice(+b.dataset.rm,1);if(routeDraft.stops.length<3)routeDraft.loop=false;SFX.play('snap');renderDraft();quoteRoute();});
  }
}

/* ============================================================
   МЫШЬ / КЛИКИ
   ============================================================ */
function findSnap(ll){
  const r=constants.node_snap_m||30;
  let best=null,bd=Infinity;
  idxEp.forEach(o=>{const d=hav(ll.lat,ll.lng,o.lat,o.lng);if(d<=r&&d<bd){bd=d;best=o;}});
  return best;
}
function onMapMove(e){
  mouseLL=e.latlng;
  if(mode==='edge'){
    snapHit=findSnap(e.latlng);
    const ll=snapHit?L.latLng(snapHit.lat,snapHit.lng):snap(e.latlng);
    if(ghost)map.removeLayer(ghost);
    ghost=L.circleMarker(ll,{pane:'prevP',radius:snapHit?11:6,color:snapHit?'#35d07f':'#ff5a5a',weight:2,fillColor:snapHit?'rgba(53,208,127,.25)':'#fff',fillOpacity:.8,dashArray:snapHit?null:'3 3',interactive:false}).addTo(map);
    if(edgeDraft.start){
      const a=edgeDraft.start;
      const pts=[[a.lat,a.lng],...edgeDraft.points.map(p=>[p.lat,p.lng]),[ll.lat,ll.lng]];
      if(tempLine)map.removeLayer(tempLine);
      tempLine=L.polyline(pts,{pane:'prevP',color:'#ff5a5a',weight:2,dashArray:'5 5',interactive:false}).addTo(map);
      let len=0;for(let i=0;i<pts.length-1;i++)len+=hav(pts[i][0],pts[i][1],pts[i+1][0],pts[i+1][1]);
      if(tempTip)map.removeLayer(tempTip);
      tempTip=L.tooltip({permanent:true,direction:'top',className:'tip',offset:[0,-8]}).setLatLng(ll).setContent(`${Math.round(len)} м · ${money(Math.ceil(len*pricing.edge))}${snapHit?' · 🔗 '+esc(snapHit.name||'узел'):''}`).addTo(map);
    }
    updateDock();
  }else if(mode==='station'){
    const ll=snap(e.latlng);
    if(ghost)map.removeLayer(ghost);
    ghost=L.circleMarker(ll,{pane:'prevP',radius:stRadius(1),fillColor:'rgba(53,208,127,.35)',color:'#35d07f',weight:1.5,dashArray:'3 3',fillOpacity:.55,interactive:false}).addTo(map);
    clearTimeout(osmTimer);
    osmTimer=setTimeout(async()=>{
      try{
        const r=await api('check_osm_station_buildings','POST',{lat:ll.lat,lng:ll.lng});
        if(osmTip){map.removeLayer(osmTip);osmTip=null;}
        const parts=[];
        if(r.discount>0)parts.push('🌟 скидка OSM 10%');
        parts.push(`🏢 ${r.buildings} → ${r.income_percent}%`);
        if(r.forbidden_zone)parts.push('⛔ '+r.forbidden_zone);
        osmTip=L.tooltip({permanent:true,direction:'top',className:'tip',offset:[0,-10]}).setLatLng(ll).setContent(parts.join(' · ')).addTo(map);
      }catch(err){}
    },420);
  }else if(mode==='depot'){
    const ll=snap(e.latlng);
    if(ghost)map.removeLayer(ghost);
    let best=null,bd=Infinity;
    if(me)world.stations.filter(s=>!s.is_building&&(s.owner_id===me.id||s.can_connect)).forEach(s=>{const d=hav(ll.lat,ll.lng,s.lat,s.lng);if(d<bd){bd=d;best=s;}});
    const ok=best&&bd<=DEPOT_RADIUS;
    ghost=L.marker(ll,{pane:'prevP',interactive:false,icon:L.divIcon({html:`<svg width="26" height="26" viewBox="0 0 26 26" opacity="${ok?.95:.45}"><path d="M3 12 L13 4 L23 12 V22 H3 Z" fill="${ok?'#24d3c4':'#ff5a5a'}" stroke="#07101c" stroke-width="1.6" stroke-linejoin="round"/><rect x="9" y="14" width="8" height="8" fill="#07101c" opacity=".7" rx="1"/></svg>`,className:'',iconSize:[26,26],iconAnchor:[13,13]})}).addTo(map);
    updateDock();
  }
}
function onMapClick(e){
  if(mode==='station')buildStationFlow(snap(e.latlng));
  else if(mode==='depot')buildDepotFlow(snap(e.latlng));
  else if(mode==='edge'){
    const s=findSnap(e.latlng);
    if(s){pickEdgeEndpoint(s);return;}
    const ll=snap(e.latlng);
    if(!edgeDraft.start){
      edgeDraft.start={id:'',lat:ll.lat,lng:ll.lng,name:'точка в поле',kind:'field'};
      SFX.play('click');
    }else{
      edgeDraft.points.push({lat:ll.lat,lng:ll.lng});SFX.play('snap');
    }
    renderDraft();updateDock();
  }
  else if(mode==='idle')closeCtx();
}
function pickEdgeEndpoint(o){
  if(!edgeDraft.start){
    if(me&&o.owner_id!==me.id&&!o.can_connect){toast(`🔒 «${o.name}»: владелец не разрешает подключение`);return;}
    edgeDraft.start={id:o.id,lat:o.lat,lng:o.lng,name:o.name,kind:o.kind};
    edgeDraft.points=[];SFX.play('click');renderDraft();updateDock();return;
  }
  if(edgeDraft.start.id&&edgeDraft.start.id===o.id){edgeDraft.start=null;edgeDraft.points=[];renderDraft();updateDock();return;}
  if(me&&o.owner_id!==me.id&&!o.can_connect){toast(`🔒 «${o.name}»: подключение запрещено`);return;}
  quoteAndBuildEdge({to:o.id},{lat:o.lat,lng:o.lng});
}
function finishEdgeInField(){
  if(!edgeDraft.start){toast('Сначала выберите начало');return;}
  let ll=null;
  if(edgeDraft.points.length){ll=edgeDraft.points.pop();}
  else if(mouseLL){const s=snap(mouseLL);ll={lat:s.lat,lng:s.lng};}
  if(!ll){toast('Нет точки для завершения');renderDraft();updateDock();return;}
  if(hav(edgeDraft.start.lat,edgeDraft.start.lng,ll.lat,ll.lng)<20){toast('Слишком короткий перегон (минимум 20 м)');return;}
  quoteAndBuildEdge({to_point:ll},ll);
}
function onStationClick(ev,s){
  L.DomEvent.stopPropagation(ev);
  if(mode==='route'){routePick(s);return;}
  if(mode==='edge'){pickEdgeEndpoint({...s,kind:'station'});return;}
  if(mode==='depot'){buildDepotFlow(L.latLng(s.lat,s.lng));return;}
  if(mode==='idle')ctxStation(ev.originalEvent,s);
}
function onNodeClick(ev,n){
  L.DomEvent.stopPropagation(ev);
  if(mode==='edge'){pickEdgeEndpoint({...n,kind:'node'});return;}
  if(mode==='route'){toast('Узел — не остановка; путь пройдёт через него автоматически');return;}
  if(mode==='idle')ctxNode(ev.originalEvent,n);
}

/* ============================================================
   СТАНЦИЯ: ПОСТРОЙКА
   ============================================================ */
function buildStationFlow(ll){
  if(osmTip){map.removeLayer(osmTip);osmTip=null;}
  modal({
    title:'🚉 Новая станция',
    body:`<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60">`,
    input:'Станция '+(world.stations.filter(s=>me&&s.owner_id===me.id).length+1),
    ok:'Продолжить',
    onOk:async name=>{
      loading('Проверяем местность…');
      try{
        const r=await api('check_osm_station_buildings','POST',{lat:ll.lat,lng:ll.lng});
        unloading();
        if(r.forbidden_zone){toast('⛔ Здесь нельзя строить: '+r.forbidden_zone);return;}
        let cost=pricing.station, html='';
        if(r.discount>0){const fc=Math.ceil(cost*.9);html=`<div class="disc">🌟 Реальная станция OSM в 200 м</div><div style="margin-bottom:10px"><span class="price-old">${money(cost)}</span><span class="price-new">${money(fc)}</span></div>`;cost=fc;}
        else html=`<div style="margin-bottom:10px;font:800 17px var(--f-mono)">${money(cost)}</div>`;
        html+=`<div style="font-size:13px">🏢 Зданий в 500 м: <b style="color:var(--ink)">${r.buildings}</b> → бонус <b style="color:var(--amber-t)">${r.income_percent}%</b></div>`;
        html+=`<div class="note">Класс 1 = 1 маршрут. Пути не ограничены классом.</div>`;
        const poor=me.money<cost;
        if(poor)html+=`<div class="alert">💸 Не хватает: на балансе ${money(me.money)}</div>`;
        modal({title:'Подтверждение',body:`<p style="margin:0 0 10px;color:var(--ink)">Построить «${esc(name)}»?</p>${html}`,ok:'Построить',disabled:poor,
          onOk:async()=>{
            loading('Строим станцию…');
            try{await api('build_station','POST',{name,lat:ll.lat,lng:ll.lng});unloading();SFX.play('build');toast(`🚉 «${name}» строится`);setMode('idle');await loadWorld();await refreshAll();}
            catch(err){unloading();toast('Ошибка: '+err.message);}
          }});
      }catch(err){unloading();toast('Ошибка: '+err.message);}
    }
  });
}

/* ============================================================
   ПЕРЕГОН: СМЕТА / ПОСТРОЙКА / ВРЕЗКА
   ============================================================ */
function quoteAndBuildEdge(toPart,ll){
  const a=edgeDraft.start;
  const body={points:edgeDraft.points};
  if(a.id)body.from=a.id;else body.from_point={lat:a.lat,lng:a.lng};
  Object.assign(body,toPart);
  loading('Ищем реки, пересечения и покрытие OSM…');
  api('quote_edge','POST',body)
    .then(q=>{unloading();edgeQuote=q;setTypes(q.bridge_types);renderPreview();edgeModal(body,q);})
    .catch(err=>{unloading();toast('Ошибка проверки: '+err.message);});
}
function structRow(br,i){
  const isRail=br.kind==='rail';
  const opts=bridgeTypes.filter(t=>!t.rail_only||isRail).map(t=>{
    const c=isRail?t.cost_rail:t.cost_river;
    return `<option value="${t.key}" ${t.key===br.type?'selected':''}>${esc(t.label)} · ${t.speed} км/ч · ${fmt(c)} ₽</option>`;
  }).join('');
  return `<div class="br-item">
    <div class="br-top"><span class="sw" data-sw="${i}" style="background:${MAT_COLOR[bridgeMat(br.type)]}"></span>${isRail?'🌉':'🏞'} ${esc(br.name||'')}</div>
    <div class="br-meta">пролёт ${Math.round(br.span)} м${br.angle?` · угол ${br.angle}°`:''}</div>
    <select class="select" data-br="${i}">${opts}</select>
  </div>`;
}
function edgeModal(reqBody,q){
  const epNameOf=p=>p.mode==='existing'?(p.kind==='node'?'узел '+(p.name||''):p.name||'объект'):'новый узел в поле';
  const all=[...(q.bridges||[]),...(q.overpasses||[])];
  let head='';
  if(q.coverage>.01)head=`<div class="disc">🛰 Покрытие реальной ЖД: ${Math.round(q.coverage*100)}% · скидка ${Math.round(q.discount_pct*100)}%</div><div><span class="price-old">${money(q.cost_base)}</span><span class="price-new">${money(q.cost_discount)}</span></div>`;
  else head=`<div style="font:800 17px var(--f-mono)">${money(q.cost_base)}</div>`;
  let obs='';
  if((q.obstacles||[]).length){
    obs=`<div class="box"><div class="box-head" data-box="obs"><span>🚧 Препятствия · ${q.obstacles.reduce((s,o)=>s+o.count,0)}</span><span class="sum">${money(q.obstacles_cost)}</span><span data-arrow="obs">▶</span></div><div class="box-body" id="box-obs">`+
      q.obstacles.map(o=>`<div class="obs-row"><span>${esc(o.label)} × ${o.count}</span><span><b>${money(o.total)}</b></span></div>`).join('')+`</div></div>`;
  }
  let rivers='',overs='';
  if((q.bridges||[]).length)rivers=`<div class="box"><div class="box-head" data-box="riv"><span>🌉 Мосты через реки · ${q.bridges.length}</span><span class="sum" id="sumRiv">${money(q.bridges_cost)}</span><span data-arrow="riv">▼</span></div><div class="box-body open" id="box-riv">`+q.bridges.map((br,i)=>structRow(br,i)).join('')+`</div></div>`;
  if((q.overpasses||[]).length)overs=`<div class="box"><div class="box-head" data-box="ovr"><span>🚧 Путепроводы · ${q.overpasses.length}</span><span class="sum" id="sumOvr">${money(q.overpasses_cost)}</span><span data-arrow="ovr">▼</span></div><div class="box-body open" id="box-ovr">`+q.overpasses.map((br,i)=>structRow(br,i+q.bridges.length)).join('')+`</div></div>`;
  let nodesLine='';
  if(q.nodes_new>0)nodesLine=`<div class="obs-row" style="margin-top:8px"><span>🔩 Новые узлы в поле × ${q.nodes_new}</span><span><b>${money(q.node_cost)}</b></span></div>`;
  let blocked='';
  if(q.error)blocked=`<div class="alert">⛔ ${esc(q.error)}</div>`;
  const body=`<p style="margin:0 0 6px;color:var(--ink);font-weight:700">${esc(epNameOf(q.endpoints.from))} → ${esc(epNameOf(q.endpoints.to))}</p>
    <div style="font:11px var(--f-mono);color:var(--mut);margin-bottom:10px">${q.length.toFixed(1)} м · изгибов ${reqBody.points.length} · скорость ≈ <span id="spdPrev">${q.speed_preview}</span> км/ч</div>
    ${head}${rivers}${overs}${obs}${nodesLine}${blocked}
    <div class="total">Итого: <span id="edgeTotal">${money(q.final_cost)}</span></div><div id="edgePoor"></div>`;
  const canBuild=!q.error;
  modal({
    title:'🛤️ Смета перегона',body,wide:true,ok:'Построить',disabled:!canBuild||me.money<q.final_cost,
    onClose:()=>{edgeQuote=null;renderPreview();},
    onMount:root=>{
      root.querySelectorAll('[data-box]').forEach(h=>h.onclick=()=>{
        const b=el('box-'+h.dataset.box),ar=root.querySelector(`[data-arrow="${h.dataset.box}"]`);
        b.classList.toggle('open');ar.textContent=b.classList.contains('open')?'▼':'▶';SFX.play('click');
      });
      const recalc=()=>{
        let rc=0,oc=0,spd=60;
        (q.bridges||[]).forEach(br=>{rc+=bridgeCost('river',br.type);spd=Math.min(spd,bridgeSpeed(br.type));});
        (q.overpasses||[]).forEach(br=>{oc+=bridgeCost('rail',br.type);spd=Math.min(spd,bridgeSpeed(br.type));});
        q.final_cost=q.cost_discount+q.obstacles_cost+rc+oc+q.node_cost;
        const sr=el('sumRiv'),so=el('sumOvr');
        if(sr)sr.textContent=money(rc);if(so)so.textContent=money(oc);
        el('edgeTotal').textContent=money(q.final_cost);
        el('spdPrev').textContent=spd;
        const poor=me.money<q.final_cost;
        el('edgePoor').innerHTML=poor?`<div class="alert">💸 Не хватает: ${money(me.money)}</div>`:'';
        el('actionOk').disabled=!canBuild||poor;
        renderPreview();
      };
      root.querySelectorAll('[data-br]').forEach(sel=>sel.onchange=()=>{
        const br=all[+sel.dataset.br];if(!br)return;
        br.type=sel.value;
        const sw=root.querySelector(`[data-sw="${sel.dataset.br}"]`);if(sw)sw.style.background=MAT_COLOR[bridgeMat(br.type)];
        SFX.play('snap');recalc();
      });
      recalc();
    },
    onOk:async()=>{
      loading('Строим перегон и сооружения…');
      try{
        const sel=all.map(br=>({kind:br.kind,lat:br.lat,lng:br.lng,type:br.type}));
        const r=await api('build_edge','POST',{...reqBody,double:false,bridges:sel});
        unloading();SFX.play('build');
        let msg=`🛤️ Перегон строится · ${money(r.cost)}`;
        if(r.nodes_new)msg+=` · 🔩 узлов ${r.nodes_new}`;
        if(r.bridges)msg+=` · 🌉 ${r.bridges}`;
        if(r.overpasses)msg+=` · 🚧 ${r.overpasses}`;
        toast(msg);
        await loadWorld();await refreshAll();
        // цепочка: продолжаем от нового конца
        const cont=ep(r.to);
        if(cont){edgeDraft.start={id:cont.id,lat:cont.lat,lng:cont.lng,name:cont.name,kind:cont.kind};edgeDraft.points=[];renderDraft();updateDock();}
        else setMode('idle');
      }catch(err){unloading();toast('Ошибка: '+err.message);}
    }
  });
}
function openSplitFlow(e,ll){
  if(e.is_building){toast('Перегон ещё строится');return;}
  if(e.upgrading){toast('Перегон улучшается');return;}
  if(e.frozen){toast('Перегон на продаже');return;}
  if(me&&e.owner_id!==me.id&&!e.can_use){toast('🔒 Владелец не разрешает врезку на этом перегоне');return;}
  pendingSplit={lat:ll.lat,lng:ll.lng};renderPreview();
  const cost=pricing.station, poor=me.money<cost;
  modal({
    title:'📍 Врезка станции в перегон',
    body:`<p style="margin:0 0 10px">${esc(e.from_name)} ↔ ${esc(e.to_name)}</p>
      <p style="margin:0 0 10px">Название станции:</p><input type="text" id="modalInput" class="input" maxlength="60">
      <div class="note">Станция встанет на полотно в точке клика, перегон разрежется на два с сохранением путей, электрификации и мостов. Отступ от концов ≥ 60 м.</div>
      <div style="margin-top:10px;font:800 17px var(--f-mono)">${money(cost)} <span style="font-size:11px;color:var(--mut)">возможна скидка OSM 10 %</span></div>
      ${poor?`<div class="alert">💸 Не хватает денег</div>`:''}`,
    input:'Станция '+(world.stations.filter(s=>me&&s.owner_id===me.id).length+1),
    ok:'Врезать',disabled:poor,
    onClose:()=>{pendingSplit=null;renderPreview();},
    onOk:async name=>{
      loading('Врезаем станцию…');
      try{
        const r=await api('split_edge','POST',{edge_id:e.id,lat:ll.lat,lng:ll.lng,name});
        unloading();SFX.play('build');
        toast(`📍 «${name}» врезана · ${Math.round(r.len_a)} м + ${Math.round(r.len_b)} м · 🏢 ${r.buildings} → ${r.income_percent}%`);
        setMode('idle');await loadWorld();await refreshAll();
        const s=st(r.station_id);if(s)map.panTo([s.lat,s.lng]);
      }catch(err){unloading();toast('Ошибка: '+err.message);}
    }
  });
}

/* ============================================================
   ДЕПО / ПОЕЗДА
   ============================================================ */
function buildDepotFlow(ll){
  if(!me)return;
  let best=null,bd=Infinity;
  world.stations.filter(s=>!s.is_building&&(s.owner_id===me.id||s.can_connect)).forEach(s=>{const d=hav(ll.lat,ll.lng,s.lat,s.lng);if(d<bd){bd=d;best=s;}});
  if(!best){toast('Рядом нет доступной станции');return;}
  if(bd>DEPOT_RADIUS){toast(`Далеко от «${best.name}»: ${Math.round(bd)} м (макс ${DEPOT_RADIUS})`);return;}
  const cost=pricing.depot, poor=me.money<cost;
  modal({
    title:'🏭 Новое депо',
    body:`<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60">
      <div class="dock-stats" style="margin-top:12px"><span class="dstat">станция <b>${esc(best.name)}</b></span><span class="dstat good">${Math.round(bd)} м</span><span class="dstat">мест <b>${DEPOT_CAP_BASE}</b></span><span class="dstat">2 дня</span></div>
      <div style="margin-top:10px;font:800 17px var(--f-mono)">${money(cost)}</div>
      ${best.owner_id!==me.id?`<div class="note">Чужая станция — подключение разрешено владельцем.</div>`:''}
      ${poor?`<div class="alert">💸 Не хватает денег</div>`:''}`,
    input:'Депо «'+best.name+'»',ok:'Построить',disabled:poor,
    onOk:async name=>{
      loading('Строим депо…');
      try{const r=await api('build_depot','POST',{name,lat:ll.lat,lng:ll.lng,station_id:best.id});unloading();SFX.play('build');toast(`🏭 «${name}» строится у «${r.station}»`);setMode('idle');await loadWorld();await refreshAll();}
      catch(err){unloading();toast('Ошибка: '+err.message);}
    }
  });
}
function buyTrain(depotId){
  const d=idxDp.get(depotId);if(!d)return;
  const poor=me.money<pricing.train;
  modal({title:'🚆 Заказ поезда',
    body:`<p style="margin:0 0 10px">«${esc(d.name)}» · свободно ${d.free_slots}/${d.capacity}</p>
      <p style="margin:0 0 10px">Название состава:</p><input type="text" id="modalInput" class="input" maxlength="40">
      <div style="margin-top:10px;font:800 17px var(--f-mono)">${money(pricing.train)}</div>
      <div class="note">Поставка 1 день. Содержание: ${TRAIN_UPK_RUN} ₽/д на маршруте, ${TRAIN_UPK_IDLE} ₽/д в отстое.</div>
      ${poor?`<div class="alert">💸 Не хватает денег</div>`:''}`,
    input:'Поезд '+(world.trains.filter(t=>me&&t.owner_id===me.id).length+1),
    ok:'Заказать',disabled:poor||d.free_slots<=0,
    onOk:async name=>{
      loading('Заказываем…');
      try{await api('buy_train','POST',{depot_id:depotId,name});unloading();SFX.play('build');toast(`🚆 «${name}» заказан`);await loadWorld();await refreshAll();}
      catch(e){unloading();toast('Ошибка: '+e.message);}
    }});
}
function assignTrainFlow(t){
  const d=idxDp.get(t.depot_id);
  if(!d){toast('Поезд без депо — сначала припишите');return;}
  const items=world.routes.filter(r=>me&&r.owner_id===me.id).map(r=>{
    const onRoute=(r.stations||[]).includes(d.station_id);
    const free=(r.slots||0)-(r.trains||0);
    const dis=!onRoute||r.status==='broken'||free<=0;
    const why=!onRoute?'депо не на маршруте':(r.status==='broken'?'разорван':(free<=0?'нет ниток':`${r.trains}/${r.slots} ниток · ${r.km} км · ${money(r.income)}/д`));
    return{value:r.id,label:r.name,sub:why,disabled:dis};
  });
  if(t.route_id)items.unshift({value:'',label:'⏹ Снять с маршрута',sub:'отстой, содержание ×0.5'});
  choiceModal({title:`🎫 «${t.name}»`,items,ok:'Назначить',empty:'Нет маршрутов через станцию депо',
    onOk:async rid=>{
      loading('Назначаем…');
      try{const r=await api('assign_train','POST',{train_id:t.id,route_id:rid});unloading();
        if(rid){SFX.play('whistle');toast(`🎫 На маршруте · доход ${money(r.income)}/д${r.fee?` · субаренда ${money(r.fee)}/д`:''}`);}
        else{SFX.play('click');toast('Поезд снят с маршрута');}
        await loadWorld();await refreshAll();
      }catch(e){unloading();toast('Ошибка: '+e.message);}
    }});
}
function moveTrainFlow(t){
  const items=world.depots.filter(d=>me&&d.owner_id===me.id&&d.id!==t.depot_id&&!d.is_building&&!d.frozen)
    .map(d=>({value:d.id,label:d.name,sub:`${d.trains_count}/${d.capacity} · у «${(st(d.station_id)||{}).name||'—'}»`,disabled:d.free_slots<=0}));
  choiceModal({title:`🔀 «${t.name}» в другое депо`,items,ok:'Перевести',empty:'Нет других депо со свободными местами',
    onOk:async did=>{
      loading('Переводим…');
      try{const r=await api('move_train','POST',{train_id:t.id,depot_id:did});unloading();SFX.play('click');toast(`🔀 В пути${r.cost?` · ${money(r.cost)}`:''}`);await loadWorld();await refreshAll();}
      catch(e){unloading();toast('Ошибка: '+e.message);}
    }});
}

/* ============================================================
   МАРШРУТЫ
   ============================================================ */
function routePick(s){
  if(s.is_building){toast('Станция строится');return;}
  if(s.auction_status){toast('Станция на аукционе');return;}
  if(me&&s.owner_id!==me.id&&!s.can_stop){toast(`🔒 «${s.name}»: остановки запрещены владельцем`);return;}
  const d=routeDraft;
  if(d.stops.length&&d.stops[0]===s.id&&d.stops.length>=3){d.loop=true;SFX.play('route');renderDraft();quoteRoute();return;}
  if(d.stops.includes(s.id)){toast('Станция уже в маршруте');return;}
  if(d.stops.length>=20){toast('Максимум 20 остановок');return;}
  d.stops.push(s.id);SFX.play('snap');renderDraft();quoteRoute();
}
function quoteRoute(){
  clearTimeout(routeQuoteTimer);
  if(routeDraft.stops.length<2){routeQuote=null;renderDraft();updateDock();return;}
  routeQuoteTimer=setTimeout(async()=>{
    try{routeQuote=await api('quote_route','POST',{stations:routeDraft.stops,loop:routeDraft.loop,trains:1});}
    catch(e){routeQuote={ok:false,reason:e.message};}
    renderDraft();updateDock();
  },220);
  updateDock();
}
function createRouteFlow(){
  const q=routeQuote;
  if(!q||!q.ok){toast('Маршрут невалиден');return;}
  const rows=(q.income_by_trains||[]).map(r=>`<tr class="${r.trains===1?'hl':''}"><td>${r.trains}</td><td class="pos">+${fmt(r.income)}</td><td class="neg">−${fmt(Math.round(r.upkeep+r.fee))}</td><td>${fmt(Math.round(r.income-r.upkeep-r.fee))}</td></tr>`).join('');
  const stops=(q.stops||[]).map(s=>`<div class="obs-row"><span><span style="color:${s.color}">■</span> ${esc(s.name)} <small style="color:var(--mut)">кл.${s.class} · маршрутов ${s.routes_used}/${s.routes_limit}</small></span><span>🏢 <b>${s.percent}%</b></span></div>`).join('');
  const owners=(q.owners||[]).map(o=>`<div class="obs-row"><span><span style="color:${companyColor(o.player_id)}">■</span> ${esc(companyName(o.player_id))}</span><span>${o.km.toFixed(2)} км · ${Math.round(o.share*100)} % → <b>${money(Math.round(q.info.income*o.share*(q.trackage_share||.3)))}/д</b></span></div>`).join('');
  modal({
    title:'🧭 Открытие маршрута',wide:true,ok:'Открыть',
    input:'Маршрут '+(world.routes.filter(r=>me&&r.owner_id===me.id).length+1),
    body:`<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60">
      <div class="dock-stats" style="margin:12px 0"><span class="dstat good">${(q.info.km/1000).toFixed(2)} км</span><span class="dstat">${q.info.speed} км/ч</span><span class="dstat">ниток ${q.info.slots}</span><span class="dstat">2-путка ${Math.round(q.info.double_share*100)}%</span><span class="dstat">эл. ${Math.round(q.info.elec_share*100)}%</span></div>
      <div class="box"><div class="box-head" data-box="stops"><span>🚉 Остановки · ${q.stops.length}</span><span data-arrow="stops">▼</span></div><div class="box-body open" id="box-stops">${stops}</div></div>
      ${owners?`<div class="box"><div class="box-head" data-box="own"><span>🤝 Субаренда чужих путей</span><span data-arrow="own">▼</span></div><div class="box-body open" id="box-own">${owners}</div></div>`:''}
      <table class="tr-table"><thead><tr><th>Поездов</th><th>Доход/д</th><th>Расход/д</th><th>Чистыми</th></tr></thead><tbody>${rows}</tbody></table>
      ${q.slot_warning?`<div class="alert">⚠ ${esc(q.slot_warning)}</div>`:''}
      <div class="note">Доход пойдёт после назначения поездов из депо.</div>`,
    onMount:root=>{root.querySelectorAll('[data-box]').forEach(h=>h.onclick=()=>{const b=el('box-'+h.dataset.box);b.classList.toggle('open');root.querySelector(`[data-arrow="${h.dataset.box}"]`).textContent=b.classList.contains('open')?'▼':'▶';});},
    onOk:async name=>{
      loading('Открываем маршрут…');
      try{await api('create_route','POST',{name,stations:routeDraft.stops,loop:routeDraft.loop});unloading();SFX.play('route');toast(`🧭 «${name}» открыт`);setMode('idle');await loadWorld();await refreshAll();}
      catch(err){unloading();toast('Ошибка: '+err.message);}
    }
  });
}
async function autoRoutes(){
  loading('Прокладываем автомаршруты…');
  try{const r=await api('auto_routes','POST',{});unloading();await loadWorld();await refreshAll();
    if(r.created){SFX.play('route');toast(`✨ Автомаршрутов: ${r.created}. Назначьте поезда.`);}else toast('Новых автомаршрутов нет — слоты заняты или сеть несвязна.');
  }catch(e){unloading();toast('Ошибка: '+e.message);}
}
async function starterPack(){
  loading('Выдаём стартовый пакет…');
  try{const r=await api('claim_starter_pack','POST',{});unloading();SFX.play('whistle');toast('🎁 Депо, поезд и маршрут — доход пошёл!');await loadWorld();await refreshAll();
    const rt=idxRt.get(r.route_id);if(rt)zoomRoute(rt);
  }catch(e){unloading();toast('Ошибка: '+e.message);}
}
function zoomRoute(r){
  const pts=r.geometry||[];if(!pts.length||!map)return;
  map.fitBounds(L.latLngBounds(pts).pad(.25),{padding:[30,30]});
}
function showRouteStops(r){
  zoomRoute(r);
  const stopIds=new Set(r.stations||[]);
  const marks=[];
  (r.stations||[]).forEach(sid=>{const s=st(sid);if(!s)return;
    marks.push(L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:16,color:r.color,weight:3,fillColor:r.color,fillOpacity:.15,interactive:false}).addTo(previewLayer));});
  (r.transit||[]).forEach(t=>{const s=st(t.id);if(!s||stopIds.has(t.id))return;
    marks.push(L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:11,color:'#cfe0f5',weight:2,dashArray:'3 3',fillColor:'#cfe0f5',fillOpacity:.1,interactive:false}).addTo(previewLayer));});
  setTimeout(()=>marks.forEach(m=>previewLayer.removeLayer(m)),4200);
  toast(`🚉 остановок ${stopIds.size} · проездом ${(r.transit||[]).filter(t=>!stopIds.has(t.id)).length}`);
}

/* ============================================================
   ДЕЙСТВИЯ-ОБЁРТКИ
   ============================================================ */
async function act(action,body,msg,snd='build'){
  loading(msg||'Выполняем…');
  try{const r=await api(action,'POST',body);unloading();SFX.play(snd);await loadWorld();await refreshAll();return r;}
  catch(e){unloading();toast('Ошибка: '+e.message);return null;}
}
function renameFlow(title,action,body,val){
  modal({title,body:'<input type="text" id="modalInput" class="input" maxlength="60">',input:val,ok:'Сохранить',
    onOk:async n=>{try{await api(action,'POST',{...body,name:n});await loadWorld();await refreshAll();toast('Переименовано');}catch(e){toast('Ошибка: '+e.message);}}});
}
function deleteFlow(title,text,action,body){
  modal({title,danger:true,ok:'Снести',body:`<p style="margin:0;color:var(--red-t)">${text}</p>`,
    onOk:()=>act(action,body,'Сносим…').then(r=>{if(r)toast('Снесено');})});
}
function setAccess(kind,id,acc){
  act('set_object_access',{kind,id,access:acc},'Меняем доступ…','click').then(r=>{if(r)toast('Доступ: '+accessLabel(acc));});
}
function convertNodeFlow(n){
  const cost=pricing.station,poor=me.money<cost;
  modal({title:'🚉 Узел → станция',
    body:`<p style="margin:0 0 10px">«${esc(n.name)}» станет станцией; все перегоны сохранятся.</p>
      <p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60">
      <div style="margin-top:8px;font:800 17px var(--f-mono)">${money(cost)}</div>
      ${poor?`<div class="alert">💸 Не хватает денег</div>`:''}`,
    input:'Станция '+(world.stations.filter(s=>me&&s.owner_id===me.id).length+1),
    ok:'Превратить',disabled:poor,
    onOk:async name=>{
      const r=await act('convert_node',{node_id:n.id,name},'Строим станцию…');
      if(r)toast(`🚉 «${name}» · 🏢 ${r.buildings} → ${r.income_percent}%`);
    }});
}
function upgradeBridgeFlow(e,br){
  const isRail=br.kind==='rail',cur=bridgeSpeed(br.type);
  const items=bridgeTypes.filter(t=>(!t.rail_only||isRail)&&t.speed>cur).map(t=>({value:t.key,label:`${t.label} · ${t.speed} км/ч`,sub:`доплата ${money(bridgeCost(br.kind,t.key)-bridgeCost(br.kind,br.type))}`}));
  choiceModal({title:`⬆️ ${br.name}`,items,ok:'Заменить',empty:'Уже самый быстрый тип',
    onOk:async type=>{const r=await act('upgrade_bridge',{edge_id:e.id,bridge_id:br.id,type},'Меняем пролёт…');if(r)toast(`🌉 ${bridgeLabel(type)} · ${r.speed} км/ч`);}});
}

/* ============================================================
   КОНТЕКСТНЫЕ МЕНЮ
   ============================================================ */
const ctx=el('ctx');
function closeCtx(){ctx.classList.remove('open');ctx.style.left='';ctx.style.top='';}
el('ctxClose').onclick=closeCtx;
window.addEventListener('click',e=>{if(!ctx.contains(e.target))closeCtx();});
function accessWidget(kind,o){
  if(!me||o.owner_id!==me.id)return '';
  const cur=o.access||'inherit';
  return `<div class="sec">Доступ для других компаний</div><div class="acc-row">`+
    [['inherit','Как в компании'],['open','Открыт'],['closed','Закрыт']].map(([v,l])=>`<button data-acc="${v}" class="${cur===v?'sel':''}">${l}</button>`).join('')+`</div>`;
}
function showCtx(x,y,title,rows,actions,extra,accKind,accObj){
  lastCtx={x,y,ll:null};
  el('ctxTitle').innerHTML=title;
  const b=el('ctxBody');b.innerHTML=extra||'';
  rows.forEach(r=>{
    if(r&&r.sec){const d=document.createElement('div');d.className='sec';d.textContent=r.sec;b.appendChild(d);return;}
    const d=document.createElement('div');d.className='row';
    d.innerHTML=`<span class="k">${esc(r[0])}</span><span class="v">${r[2]?r[1]:esc(r[1])}</span>`;
    b.appendChild(d);
  });
  if(accKind&&accObj){
    b.insertAdjacentHTML('beforeend',accessWidget(accKind,accObj));
    b.querySelectorAll('[data-acc]').forEach(btn=>btn.onclick=()=>{closeCtx();setAccess(accKind,accObj.id,btn.dataset.acc);});
  }
  const a=el('ctxActions');a.innerHTML='';
  actions.forEach(it=>{
    const btn=document.createElement('button');btn.textContent=it.label;
    if(it.disabled)btn.disabled=true;if(it.danger)btn.className='dngr';
    btn.onclick=()=>{closeCtx();it.fn&&it.fn();};a.appendChild(btn);
  });
  a.style.display=actions.length?'':'none';
  ctx.classList.add('open');
  const r=ctx.getBoundingClientRect();
  ctx.style.left=clamp(x,8,Math.max(8,window.innerWidth-r.width-8))+'px';
  ctx.style.top=clamp(y,8,Math.max(8,window.innerHeight-r.height-8))+'px';
}
function hbar(h){const c=h<20?'var(--red)':h<50?'var(--amber)':'var(--green)';return `<div class="hbar"><i style="width:${h}%;background:${c}"></i></div>`;}
function ownerRow(o){return ['Владелец',`<span style="color:${o.color}">■</span> ${esc(companyName(o.owner_id))}`,true];}

function ctxStation(ev,s){
  ev.preventDefault();ev.stopPropagation();
  const mine=me&&s.owner_id===me.id,h=s.health??100,cls=s.class||1;
  const serving=world.routes.filter(r=>(r.stations||[]).includes(s.id));
  const rows=[ownerRow(s),
    ['Класс',`${cls}/${MAX_CLASS} → маршрутов ${s.routes_limit||cls}`],
    ['Маршрутов',`${s.routes_used||0}/${s.routes_limit||cls}`],
    ['Путей',`${s.tracks??stTracks(s.id)} (без лимита)`],
    ['Зданий (500 м)',`${s.buildings??0} → ${bonusPct(s.buildings)}%`],
    ['Состояние',h+'%'],
    ['Статус',s.served?'🚉 в сети':s.passed?'⏩ проездом':'💤 простой ×0.5'],
    ['Содержание',money(Math.ceil(stUpkeep(s)))+'/д']];
  if(!mine)rows.push(['Остановки',s.can_stop?'разрешены':'🔒 запрещены'],['Подключение',s.can_connect?'разрешено':'🔒 запрещено']);
  if(s.is_building)rows.push(['🏗 Строится',`<span data-cd="${s.build_end}">${dur(left(s.build_end))}</span>`,true]);
  if(s.upgrading)rows.push(['⬆️ До класса '+s.upgrade_class,`<span data-cd="${s.upgrade_end}">${dur(left(s.upgrade_end))}</span>`,true]);
  if(s.auction_status)rows.push(['Лот',s.auction_status==='manual'?'на продаже':'аукцион банкрота']);
  if(serving.length){rows.push({sec:'Маршруты'});serving.forEach(r=>rows.push([r.name,`${r.status==='active'?'🚆':'💤'} ${money(r.income)}/д`]));}
  const acts=[];
  if(mine&&!s.is_building&&!s.auction_status){
    if(h<100){const near=nearMe(s),cost=near?0:Math.ceil(stReplace(s)*REPAIR_RATIO);
      acts.push({label:near?'🔧 Починить (бесплатно)':`🔧 Починить (${money(cost)})`,disabled:!near&&me.money<cost,fn:()=>act('repair_station',{station_id:s.id,...(myPos?{lat:myPos.lat,lng:myPos.lng}:{})},'Ремонт…').then(r=>{if(r)toast(r.free?'🔧 Бесплатно':'🔧 '+money(r.cost));})});}
    if(cls<MAX_CLASS)acts.push({label:`⬆️ Класс ${cls+1} → ${cls+1} маршрут(а) (${money(stUpgCost(cls))})`,disabled:me.money<stUpgCost(cls)||s.upgrading,fn:()=>act('upgrade_station',{station_id:s.id},'Улучшаем…').then(r=>{if(r)toast(`⬆️ Класс ${r.class} · маршрутов ${r.routes_limit}`);})});
    acts.push({label:'🧭 Маршрут отсюда',fn:()=>{setMode('route');routeDraft.stops=[s.id];renderDraft();quoteRoute();}});
    acts.push({label:'🛤️ Ветка отсюда',fn:()=>{setMode('edge');edgeDraft.start={id:s.id,lat:s.lat,lng:s.lng,name:s.name,kind:'station'};renderDraft();updateDock();}});
    acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Станция','rename_station',{station_id:s.id},s.name)});
    acts.push({label:'🏷 На продажу',disabled:!!s.upgrading,fn:()=>openSell([s.id],[],[],[],[])});
    acts.push({label:'🗑 Снести',danger:true,fn:()=>deleteFlow('🗑 Снос станции',`Снести «${esc(s.name)}»? Примыкающие перегоны удалятся. Возврат ${money(Math.ceil(stReplace(s)*REFUND))}.`,'delete_station',{station_id:s.id})});
  }
  serving.forEach(r=>acts.push({label:`🧭 ${r.name}`,fn:()=>openRouteMenu(r,lastCtx.x,lastCtx.y)}));
  showCtx(ev.clientX,ev.clientY,`<span class="cdot" style="background:${s.color}"></span> ${esc(s.name)}`,rows,acts,hbar(h),'station',s);
}
function ctxNode(ev,n){
  ev.preventDefault();ev.stopPropagation();
  const mine=me&&n.owner_id===me.id;
  const rows=[ownerRow(n),['Перегонов',String(n.edges_count??0)],['Статус',n.served?'в сети':'💤 простой'],['Содержание',money(ndUpkeep(n))+'/д']];
  if(!mine)rows.push(['Подключение',n.can_connect?'разрешено':'🔒 запрещено']);
  const acts=[];
  if(mine&&!n.frozen){
    acts.push({label:`🚉 Превратить в станцию (${money(pricing.station)})`,disabled:me.money<pricing.station,fn:()=>convertNodeFlow(n)});
    acts.push({label:'🛤️ Тянуть ветку отсюда',fn:()=>{setMode('edge');edgeDraft.start={id:n.id,lat:n.lat,lng:n.lng,name:n.name,kind:'node'};renderDraft();updateDock();}});
    acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Узел','rename_node',{node_id:n.id},n.name)});
    acts.push({label:'🗑 Убрать узел',danger:true,fn:()=>deleteFlow('🗑 Узел',`Убрать «${esc(n.name)}»? Его перегоны удалятся.`,'delete_node',{node_id:n.id})});
  }
  showCtx(ev.clientX,ev.clientY,`🔩 ${esc(n.name)}`,rows,acts,'','node',n);
}
function ctxEdge(ev,e,ll){
  ev.preventDefault();ev.stopPropagation();
  const mine=me&&e.owner_id===me.id,len=Number(e.length||0);
  const rs=routesOnEdge(e.id);
  const rows=[ownerRow(e),
    ['Участок',`${e.from_name||epName(e.from)} ↔ ${e.to_name||epName(e.to)}`],
    ['Длина',len.toFixed(0)+' м'],['Путей',e.double?'2':'1'],['Электрификация',e.electric?'⚡ есть':'нет'],
    ['Скорость',(e.speed||0)+' км/ч'],['Статус',e.served?'в сети':'💤 ×0.5'],['Содержание',money(Math.ceil(edUpkeep(e)))+'/д']];
  if(!mine)rows.push(['Проезд',e.can_use?'разрешён (субаренда 30 %)':'🔒 запрещён']);
  if(e.is_building)rows.push(['🏗 Строится',`<span data-cd="${e.build_end}">${dur(left(e.build_end))}</span>`,true]);
  if(e.upgrading)rows.push(['⬆️ '+(e.upgrade_type==='double'?'второй путь':'электрификация'),`<span data-cd="${e.upgrade_end}">${dur(left(e.upgrade_end))}</span>`,true]);
  const brs=e.bridges||[];
  if(brs.length){rows.push({sec:`Сооружения · ${brs.length}`});brs.forEach(br=>rows.push([(br.kind==='rail'?'🚧 ':'🌉 ')+br.name,`${bridgeSpeed(br.type)} км/ч`]));}
  if(rs.length){rows.push({sec:`Маршруты · ${rs.length}`});rs.forEach(r=>rows.push([r.name,r.status==='active'?`🚆 ${money(r.income)}/д`:'💤']));}
  const acts=[];
  if(!e.is_building&&!e.frozen&&(mine||e.can_use))
    acts.push({label:`📍 Врезать станцию здесь (${money(pricing.station)})`,disabled:me.money<pricing.station||!!e.upgrading,fn:()=>openSplitFlow(e,ll||L.latLng((ep(e.from)||{}).lat,(ep(e.from)||{}).lng))});
  if(mine&&!e.is_building&&!e.auction_status){
    if(!e.double&&!e.upgrading){const c=Math.ceil(len*COST_DOUBLE_M);acts.push({label:`🛤 Второй путь (${money(c)})`,disabled:me.money<c,fn:()=>act('upgrade_edge_double',{edge_id:e.id},'Второй путь…').then(r=>{if(r)toast('🛤 Строится');})});}
    if(!e.electric&&!e.upgrading){const c=Math.ceil(len*COST_ELEC_M);acts.push({label:`⚡ Электрифицировать (${money(c)})`,disabled:me.money<c,fn:()=>act('upgrade_edge_electric',{edge_id:e.id},'Электрификация…').then(r=>{if(r)toast('⚡ Начата');})});}
    brs.forEach(br=>{const cur=bridgeSpeed(br.type);
      if(bridgeTypes.some(t=>(!t.rail_only||br.kind==='rail')&&t.speed>cur))acts.push({label:`⬆️ ${br.name}: быстрее ${cur}`,fn:()=>upgradeBridgeFlow(e,br)});});
    acts.push({label:'🏷 На продажу',disabled:!!e.upgrading,fn:()=>openSell([],[e.id],[],[],[])});
    acts.push({label:'🗑 Снести',danger:true,fn:()=>deleteFlow('🗑 Перегон','Снести перегон? Маршруты через него разорвутся. Возврат 50 %.','delete_edge',{edge_id:e.id})});
  }
  rs.forEach(r=>acts.push({label:`🧭 ${r.name}`,fn:()=>openRouteMenu(r,lastCtx.x,lastCtx.y)}));
  showCtx(ev.clientX,ev.clientY,'🛤 Перегон',rows,acts,'','edge',e);
}
function ctxDepot(ev,d){
  ev.preventDefault();ev.stopPropagation();
  const mine=me&&d.owner_id===me.id,s=st(d.station_id),h=d.health??100;
  const trains=world.trains.filter(t=>t.depot_id===d.id);
  const rows=[ownerRow(d),['Станция',s?s.name:'— потеряна'],['Уровень',`${d.level||1}/${DEPOT_MAX_LVL}`],
    ['Мест',`${d.trains_count||0}/${d.capacity}`],['Состояние',h+'%'],['Содержание',money(dpUpkeep(d))+'/д']];
  if(d.is_building)rows.push(['🏗 Строится',`<span data-cd="${d.build_end}">${dur(left(d.build_end))}</span>`,true]);
  if(d.upgrading)rows.push(['⬆️ Расширение',`<span data-cd="${d.upgrade_end}">${dur(left(d.upgrade_end))}</span>`,true]);
  if(trains.length){rows.push({sec:'Поезда'});trains.forEach(t=>rows.push([t.name,statusRu(t.status)]));}
  const acts=[];
  if(mine&&!d.auction_status){
    if(!d.is_building){
      acts.push({label:`🚆 Купить поезд (${money(pricing.train)})`,disabled:d.free_slots<=0||me.money<pricing.train,fn:()=>buyTrain(d.id)});
      if((d.level||1)<DEPOT_MAX_LVL){const c=DEPOT_UPG_COST*(d.level||1);acts.push({label:`⬆️ Расширить (${money(c)})`,disabled:me.money<c||d.upgrading,fn:()=>act('upgrade_depot',{depot_id:d.id},'Расширяем…').then(r=>{if(r)toast(`⬆️ Ур. ${r.level} · мест ${r.capacity}`);})});}
      if(h<100){const near=nearMe(d),cost=near?0:Math.ceil(dpReplace(d)*REPAIR_RATIO);acts.push({label:near?'🔧 Починить (бесплатно)':`🔧 Починить (${money(cost)})`,disabled:!near&&me.money<cost,fn:()=>act('repair_depot',{depot_id:d.id,...(myPos?{lat:myPos.lat,lng:myPos.lng}:{})},'Ремонт…')});}
      acts.push({label:'🏷 На продажу (с поездами)',disabled:!!d.upgrading,fn:()=>openSell([],[],[],[d.id],[])});
    }
    acts.push({label:'🗑 Снести депо',danger:true,fn:()=>deleteFlow('🗑 Депо',`Снести «${esc(d.name)}»? Поезда останутся без депо. Возврат ${money(Math.ceil(dpReplace(d)*REFUND))}.`,'delete_depot',{depot_id:d.id})});
  }
  trains.forEach(t=>acts.push({label:`🚆 ${t.name}`,fn:()=>openTrainMenu(t,lastCtx.x,lastCtx.y)}));
  showCtx(ev.clientX,ev.clientY,`🏭 ${esc(d.name)}`,rows,acts,hbar(h),'depot',d);
}
function statusRu(s){return{running:'🚆 на маршруте',idle:'💤 отстой',delivering:'🏗 поставка',transit:'🔀 в пути',orphan:'⚠️ без депо',broken:'⛔ разорван'}[s]||s;}
function ctxTrain(ev,t){ev.preventDefault();ev.stopPropagation();openTrainMenu(t,ev.clientX,ev.clientY);}
function openTrainMenu(t,x,y){
  const mine=me&&t.owner_id===me.id,d=idxDp.get(t.depot_id),r=idxRt.get(t.route_id);
  const live=trainLive(trainMarkers.get(t.id));
  const rows=[ownerRow(t),['Статус',statusRu(t.status)],['Депо',d?d.name:'— нет'],['Маршрут',r?r.name:'— не назначен'],['Содержание',money(trUpkeep(t))+'/д']];
  if(live)rows.push(['Движение',live.text]);
  if(t.is_building)rows.push(['🏗 Поставка',`<span data-cd="${t.build_end}">${dur(left(t.build_end))}</span>`,true]);
  if(t.transit_end>srvNow())rows.push(['🔀 В пути',`<span data-cd="${t.transit_end}">${dur(left(t.transit_end))}</span>`,true]);
  const acts=[];
  if(mine&&!t.frozen){
    if(!t.is_building)acts.push({label:t.route_id?'🎫 Переназначить / снять':'🎫 Назначить на маршрут',disabled:!t.depot_id,fn:()=>assignTrainFlow(t)});
    acts.push({label:`🔀 В другое депо${t.depot_id?' ('+money(REBASE_COST)+')':' (бесплатно)'}`,disabled:!!t.is_building,fn:()=>moveTrainFlow(t)});
    if(r)acts.push({label:`🧭 ${r.name}`,fn:()=>openRouteMenu(r,x,y)});
    acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Поезд','rename_train',{train_id:t.id},t.name)});
    acts.push({label:'🏷 На продажу',fn:()=>openSell([],[],[],[],[t.id])});
    acts.push({label:'🗑 Списать (50 %)',danger:true,fn:()=>deleteFlow('🗑 Списание',`Списать «${esc(t.name)}»? Возврат ${money(Math.ceil(TRAIN_COST*REFUND))}.`,'sell_train',{train_id:t.id})});
  }
  showCtx(x,y,`🚆 ${esc(t.name)}`,rows,acts);
}
function ctxRoute(ev,r){ev.preventDefault();ev.stopPropagation();openRouteMenu(r,ev.clientX,ev.clientY);}
function openRouteMenu(r,x,y){
  const mine=me&&r.owner_id===me.id;
  const trains=world.trains.filter(t=>t.route_id===r.id);
  const rows=[ownerRow(r),
    ['Статус',r.status==='active'?'🚆 работает':r.status==='idle'?'💤 без поездов':'⛔ разорван'],
    ['Длина',(r.km||0)+' км'],['Скорость',(r.speed||0)+' км/ч'],
    ['Поездов',`${r.trains||0}/${r.slots||0} (рек. ${r.recommended||1})`],
    ['Двухпутка',Math.round((r.double_share||0)*100)+'%'],['Электрификация',Math.round((r.elec_share||0)*100)+'%'],
    ['Доход',money(r.income)+'/д'],['Содержание',money(Math.ceil((r.upkeep||0)+(r.fee||0)))+'/д'],['Кольцо',r.loop?'да':'нет']];
  if(r.reason)rows.push(['Причина',r.reason]);
  if((r.owners||[]).length){rows.push({sec:'Субаренда путей'});r.owners.forEach(o=>rows.push([companyName(o.player_id),`${o.km.toFixed(2)} км · ${Math.round(o.share*100)} %`]));rows.push(['Платёж/д',money(r.fee)]);}
  rows.push({sec:'Остановки'});
  (r.stations||[]).forEach((sid,i)=>{const s=st(sid);rows.push([`${i+1}. ${s?s.name:'?'}`,s?`${bonusPct(s.buildings)}% · кл.${s.class}`:'—']);});
  if(trains.length){rows.push({sec:'Поезда'});trains.forEach(t=>{const live=trainLive(trainMarkers.get(t.id));rows.push([t.name,live?live.text:statusRu(t.status)]);});}
  const acts=[];
  if(mine){
    const free=world.trains.filter(t=>t.owner_id===me.id&&!t.route_id&&t.status==='idle'&&t.depot_id&&(r.stations||[]).includes((idxDp.get(t.depot_id)||{}).station_id));
    acts.push({label:`🎫 Назначить поезд${free.length?` (${free.length})`:''}`,disabled:!free.length||(r.trains||0)>=(r.slots||0)||r.status==='broken',
      fn:()=>choiceModal({title:`🎫 На «${r.name}»`,items:free.map(t=>({value:t.id,label:t.name,sub:'депо «'+((idxDp.get(t.depot_id)||{}).name||'—')+'»'})),ok:'Назначить',
        onOk:async tid=>{const z=await act('assign_train',{train_id:tid,route_id:r.id},'Назначаем…','whistle');if(z)toast(`🎫 Доход ${money(z.income)}/д${z.fee?` · аренда ${money(z.fee)}`:''}`);}})});
    acts.push({label:'🚉 Остановки и проезды',disabled:r.status==='broken',fn:()=>showRouteStops(r)});
    acts.push({label:'🔍 Показать на карте',fn:()=>zoomRoute(r)});
    acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Маршрут','rename_route',{route_id:r.id},r.name)});
    acts.push({label:'🚫 Закрыть маршрут',danger:true,fn:()=>deleteFlow('🚫 Закрытие',`Закрыть «${esc(r.name)}»? Поезда уйдут в отстой.`,'delete_route',{route_id:r.id})});
  }else{
    acts.push({label:'🚉 Остановки и проезды',disabled:r.status==='broken',fn:()=>showRouteStops(r)});
    acts.push({label:'🔍 Показать на карте',fn:()=>zoomRoute(r)});
  }
  trains.forEach(t=>acts.push({label:`🚆 ${t.name}`,fn:()=>openTrainMenu(t,x,y)}));
  showCtx(x,y,`<span class="cdot" style="background:${r.color}"></span> ${esc(r.name)}`,rows,acts);
}

/* ============================================================
   ПАНЕЛИ
   ============================================================ */
function mineArr(a){return me?a.filter(x=>x.owner_id===me.id):[];}
function renderBoard(){renderMission();renderCompanyCard();renderRoutesList();renderFleet();renderMyStations();renderRepairPanel();updatePrices();}
function updatePrices(){el('pStation').textContent=fmt(pricing.station);el('pEdge').textContent=Number(pricing.edge).toFixed(2);el('pDepot').textContent=fmt(pricing.depot);}
function renderMission(){
  const box=el('missionBody');
  if(!me){box.innerHTML='<div class="hint">Войдите, чтобы начать.</div>';return;}
  const ms=mineArr(world.stations),me2=mineArr(world.edges),mr=mineArr(world.routes),md=mineArr(world.depots),mt=mineArr(world.trains);
  const built=ms.filter(s=>!s.is_building).length,edB=me2.filter(e=>!e.is_building).length;
  const running=mt.filter(t=>t.status==='running').length;
  const steps=[
    {d:built>=2,t:'Постройте <b>2 станции</b>',s:'Клавиша 1 · или тяните ветку в поле (2) и превратите узел'},
    {d:edB>=1,t:'Соедините <b>перегоном</b>',s:'Клавиша 2 · клик по перегону врезает станцию'},
    {d:mr.length>=1,t:'Откройте <b>маршрут</b>',s:'Клавиша 4 · путь через узлы найдётся сам'},
    {d:md.length>=1&&mt.length>=1,t:'<b>Депо и поезд</b>',s:'Клавиша 3 · поезд заказывается в депо'},
    {d:running>=1,t:'<b>Назначьте поезд</b>',s:'Клик по поезду → «Назначить»'}
  ];
  const done=steps.filter(s=>s.d).length;
  const canClaim=!me.starter_claimed&&built>=2&&edB>=1;
  let html=`<div class="mission-head"><span class="mission-title">${done===steps.length?'✅ Сеть работает':'Запуск сети'}</span><span class="mini" style="margin-left:auto">${done}/${steps.length}</span></div>
    <div class="mission-bar"><i style="width:${done/steps.length*100}%"></i></div>`;
  html+=steps.map(s=>`<div class="step${s.d?' done':''}"><span class="box">${s.d?'✓':''}</span><span class="lbl">${s.t}<span class="sub">${s.s}</span></span></div>`).join('');
  if(canClaim)html+=`<button class="btn btn-violet" id="mClaim" style="margin-top:10px">🎁 Депо + поезд + маршрут бесплатно</button>`;
  if(done===steps.length)html+=`<div class="note" style="margin-top:10px">Чистыми ${money(lastPoll?lastPoll.net_per_day:0)}/д. Класс станции = слоты маршрутов; двухпутка = нитки; быстрые мосты = скорость; чужие пути = 30 % дохода владельцу.</div>`;
  box.innerHTML=html;
  const c=el('mClaim');if(c)c.onclick=starterPack;
  el('btnStarter').style.display=me.starter_claimed?'none':'';
  el('btnStarter').disabled=!canClaim;
}
function renderCompanyCard(){
  if(!me)return;
  el('ccColor').style.background=me.color||'#35d07f';
  el('ccName').textContent=me.name;
  el('ccTrackage').checked=!!myPolicies.allow_trackage;
  el('ccStops').checked=!!myPolicies.allow_stops;
  el('ccConnect').checked=!!myPolicies.allow_connect;
  el('ccIn').textContent='+'+fmt(lastPoll?lastPoll.tractage_in:0)+' ₽';
  el('ccOut').textContent='−'+fmt(lastPoll?lastPoll.tractage_out:0)+' ₽';
}
async function savePolicy(key,val){
  try{
    const r=await api('company_settings','POST',{[key]:val?1:0});
    myPolicies=r.policies;SFX.play('click');
    toast((val?'🔓 ':'🔒 ')+({allow_trackage:'Субаренда путей',allow_stops:'Остановки на станциях',allow_connect:'Подключение к сети'}[key])+(val?' разрешены':' запрещены'));
    await loadWorld();await refreshAll();
  }catch(e){toast('Ошибка: '+e.message);renderCompanyCard();}
}
el('ccTrackage').onchange=e=>savePolicy('allow_trackage',e.target.checked);
el('ccStops').onchange=e=>savePolicy('allow_stops',e.target.checked);
el('ccConnect').onchange=e=>savePolicy('allow_connect',e.target.checked);
function openPalette(){
  palSel=me.color;
  const used=new Set(companies.filter(c=>c.id!==me.id).map(c=>(c.color||'').toLowerCase()));
  el('palGrid').innerHTML=palette.map(c=>`<span class="pal-sw${used.has(c.toLowerCase())?' used':''}${c===palSel?' sel':''}" data-c="${c}" style="background:${c};color:${c}" title="${c}"></span>`).join('');
  el('palGrid').querySelectorAll('.pal-sw:not(.used)').forEach(sw=>sw.onclick=()=>{
    palSel=sw.dataset.c;SFX.play('snap');
    el('palGrid').querySelectorAll('.pal-sw').forEach(x=>x.classList.toggle('sel',x.dataset.c===palSel));
  });
  el('cmInfo').textContent=`Субаренда: оператор платит владельцу путей ${Math.round((constants.trackage_share||.3)*100)} % дохода маршрута пропорционально км. Цвет перекрасит маршруты, поезда, станции и узлы.`;
  el('companyModal').classList.add('open');
}
el('btnPalette').onclick=openPalette;
el('ccColor').onclick=openPalette;
el('btnCompany').onclick=()=>{el('board').scrollTop=0;openPalette();};
el('cmSave').onclick=async()=>{
  if(!palSel||palSel===me.color){el('companyModal').classList.remove('open');return;}
  try{
    const r=await api('company_settings','POST',{color:palSel});
    me.color=r.color;el('meChip').style.background=r.color;
    el('companyModal').classList.remove('open');SFX.play('cash');
    toast('🎨 Цвет компании обновлён');
    await loadWorld();await refreshAll();
  }catch(e){toast('Ошибка: '+e.message);}
};
function renderRoutesList(){
  const list=el('routesList'),rs=mineArr(world.routes);
  el('cntRoutes').textContent=rs.length;
  if(!rs.length){list.innerHTML='<div class="hint">Маршрутов нет. Клавиша <b>4</b> — станции по порядку, или автомаршруты.</div>';return;}
  list.innerHTML='';
  rs.forEach(r=>{
    const cls=r.status==='active'?'st-active':r.status==='idle'?'st-idle':'st-broken';
    const pill=r.status==='active'?'<span class="pill ok">работает</span>':r.status==='idle'?'<span class="pill idle">без поездов</span>':'<span class="pill bad">разорван</span>';
    const d=document.createElement('div');d.className='item '+cls;d.style.setProperty('--rc',r.color);
    d.innerHTML=`<div class="item-top"><span class="item-name">${esc(r.name)}</span>${pill}<span class="item-val ${r.income>0?'pos':''}">${r.income>0?'+'+fmt(r.income)+' ₽':'0 ₽'}</span></div>
      <div class="item-sub"><span>${r.km} км</span><span>${r.speed||0} км/ч</span><span>🚆 ${r.trains||0}/${r.slots||0}</span>${r.loop?'<span>⟳</span>':''}${r.fee?`<span class="warnv">🤝 ${fmt(r.fee)} ₽</span>`:''}</div>`;
    d.title=r.reason||'Клик — меню';
    d.onclick=ev=>ctxRoute(ev,r);d.oncontextmenu=ev=>ctxRoute(ev,r);
    list.appendChild(d);
  });
}
function renderFleet(){
  const list=el('fleetList'),ds=mineArr(world.depots),ts=mineArr(world.trains);
  el('cntFleet').textContent=`${ds.length} / ${ts.length}`;
  if(!ds.length&&!ts.length){list.innerHTML='<div class="hint">Нет депо. Клавиша <b>3</b> — в радиусе 500 м от доступной станции.</div>';return;}
  list.innerHTML='';
  ds.forEach(d=>{
    const s=st(d.station_id);
    const pill=d.is_building?'<span class="pill idle">стройка</span>':d.upgrading?'<span class="pill info">расширение</span>':d.served?'<span class="pill ok">работает</span>':'<span class="pill idle">простой</span>';
    const n=document.createElement('div');n.className='item';n.style.setProperty('--rc',d.color||'#24d3c4');
    n.innerHTML=`<div class="item-top"><span class="item-name">🏭 ${esc(d.name)}</span>${pill}<span class="mini">${d.trains_count||0}/${d.capacity}</span></div>
      <div class="item-sub"><span>ур.${d.level||1}</span><span>${esc(s?s.name:'—')}</span><span>${d.health??100}%</span>${d.is_building?`<span data-cd="${d.build_end}">${dur(left(d.build_end))}</span>`:''}</div>`;
    n.onclick=ev=>ctxDepot(ev,d);n.oncontextmenu=ev=>ctxDepot(ev,d);
    list.appendChild(n);
  });
  ts.forEach(t=>{
    const r=idxRt.get(t.route_id),d=idxDp.get(t.depot_id);
    const pill=t.status==='running'?'<span class="pill ok">на маршруте</span>':t.status==='delivering'?'<span class="pill idle">поставка</span>':t.status==='transit'?'<span class="pill info">в пути</span>':t.status==='orphan'?'<span class="pill bad">без депо</span>':t.status==='broken'?'<span class="pill bad">разорван</span>':'<span class="pill idle">отстой</span>';
    const n=document.createElement('div');n.className='item';n.style.setProperty('--rc',r?r.color:'#61789a');
    n.innerHTML=`<div class="item-top"><span class="item-name">🚆 ${esc(t.name)}</span>${pill}</div>
      <div class="item-sub"><span>${esc(d?d.name:'без депо')}</span><span>${r?esc(r.name):'не назначен'}</span>${t.is_building?`<span data-cd="${t.build_end}">${dur(left(t.build_end))}</span>`:''}${t.transit_end>srvNow()?`<span data-cd="${t.transit_end}">${dur(left(t.transit_end))}</span>`:''}</div>
      ${t.status==='running'?`<div class="item-live"><span class="sd"></span><span data-tstatus="${esc(t.id)}">…</span></div>`:''}`;
    n.onclick=ev=>ctxTrain(ev,t);n.oncontextmenu=ev=>ctxTrain(ev,t);
    list.appendChild(n);
  });
  updateTrainStatuses();
}
function renderMyStations(){
  const list=el('myStations');
  if(!me){list.innerHTML='<div class="hint">Войдите в игру.</div>';el('cntSt').textContent='0';return;}
  let ms=mineArr(world.stations);el('cntSt').textContent=ms.length;
  const q=(el('stSearch').value||'').toLowerCase();
  ms=ms.filter(s=>s.name.toLowerCase().includes(q));
  const sort=el('stSort').value;
  ms.sort((a,b)=>sort==='name'?a.name.localeCompare(b.name,'ru'):sort==='class'?(b.class||1)-(a.class||1):sort==='health'?(a.health??100)-(b.health??100):sort==='bonus'?(b.buildings||0)-(a.buildings||0):(b.routes_used||0)-(a.routes_used||0));
  if(!ms.length){list.innerHTML='<div class="hint">'+(q?'Ничего не найдено.':'Постройте первую станцию.')+'</div>';return;}
  list.innerHTML='';
  ms.forEach(s=>{
    const h=s.health??100,hc=h<HEALTH_CRIT?'var(--red)':h<50?'var(--amber)':'var(--green)';
    const pill=s.is_building?'<span class="pill idle">стройка</span>':s.auction_status?'<span class="pill idle">лот</span>':s.served?'<span class="pill ok">в сети</span>':s.passed?'<span class="pill info">проезд</span>':'<span class="pill bad">простой</span>';
    const d=document.createElement('div');d.className='item';d.style.setProperty('--rc',s.served||s.passed?(s.color||'#35d07f'):'#61789a');
    d.innerHTML=`<div class="item-top"><span class="item-name">${esc(s.name)}</span>${pill}<span class="mini">кл.${s.class||1}</span></div>
      <div class="item-sub"><span>🧭 ${s.routes_used||0}/${s.routes_limit||s.class||1}</span><span>🛤 ${s.tracks??stTracks(s.id)}</span><span>🏢 ${bonusPct(s.buildings)}%</span><span>${h}%</span></div>
      <div class="bar"><i style="width:${h}%;background:${hc}"></i></div>`;
    d.onclick=()=>{map.flyTo([s.lat,s.lng],Math.max(map.getZoom(),15),{duration:.6});SFX.play('click');
      const ring=L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:20,color:s.color||'#35d07f',weight:3,fillColor:s.color||'#35d07f',fillOpacity:.12,interactive:false}).addTo(map);
      setTimeout(()=>map.removeLayer(ring),1500);};
    d.oncontextmenu=ev=>ctxStation(ev,s);
    list.appendChild(d);
  });
}
el('stSearch').addEventListener('input',renderMyStations);
el('stSort').addEventListener('change',renderMyStations);

/* ============================================================
   РЕМОНТ
   ============================================================ */
function repairTargets(){
  if(!me)return[];
  const s=mineArr(world.stations).filter(x=>!x.is_building&&!x.frozen&&(x.health??100)<100).map(x=>({o:x,kind:'station',cost:nearMe(x)?0:Math.ceil(stReplace(x)*REPAIR_RATIO)}));
  const d=mineArr(world.depots).filter(x=>!x.is_building&&!x.frozen&&(x.health??100)<100).map(x=>({o:x,kind:'depot',cost:nearMe(x)?0:Math.ceil(dpReplace(x)*REPAIR_RATIO)}));
  return[...s,...d];
}
function renderRepairPanel(){
  const p=el('repairPanel');if(!me){p.innerHTML='';return;}
  const all=[...mineArr(world.stations),...mineArr(world.depots)].filter(x=>!x.is_building);
  if(!all.length){p.innerHTML='<div class="hint">Нет объектов.</div>';return;}
  const need=repairTargets();
  const avg=all.reduce((a,x)=>a+(x.health??100),0)/all.length;
  if(!need.length){p.innerHTML='<div style="color:var(--green-t);font-size:12px;font-weight:700">✓ Вся инфраструктура в идеале</div>';return;}
  p.innerHTML=`<div style="font-size:12px;color:var(--ink2)">Среднее <b style="color:var(--ink);font-family:var(--f-mono)">${avg.toFixed(1)}%</b> · к ремонту <b style="color:var(--amber-t);font-family:var(--f-mono)">${need.length}</b> · ${money(need.reduce((a,x)=>a+x.cost,0))}</div>`;
}
function openRepair(){
  const body=el('repairBody'),ok=el('repairOk');
  if(!me){body.innerHTML='<div class="hint">Войдите.</div>';ok.disabled=true;return;}
  const need=repairTargets();
  el('repairNear').disabled=!myPos;
  if(!need.length){body.innerHTML='<div style="color:var(--green-t);font-weight:700">✓ Всё в идеальном состоянии.</div>';ok.disabled=true;el('repairModal').classList.add('open');return;}
  const total=need.reduce((a,x)=>a+x.cost,0),freeN=need.filter(x=>x.cost===0).length;
  let html=`<div style="margin-bottom:10px">Объектов: <b style="color:var(--ink)">${need.length}</b> · <b style="color:var(--amber-t);font-family:var(--f-mono)">${money(total)}</b>${myPos?` · рядом ${freeN} бесплатно`:''}</div><table class="rep-table">`;
  need.forEach(x=>{html+=`<tr><td>${x.kind==='depot'?'🏭':'🚉'} ${esc(x.o.name)}</td><td class="rh">${x.o.health}%</td><td class="rc">${x.cost===0?'<span class="free">бесплатно</span>':money(x.cost)}</td></tr>`;});
  html+='</table>';
  body.innerHTML=html;ok.disabled=me.money<total;
  el('repairModal').classList.add('open');
}
el('btnRepair').onclick=openRepair;el('btnRepair2').onclick=openRepair;
el('repairOk').onclick=async()=>{
  const need=repairTargets();if(!need.length)return;
  loading('Ремонтируем…');
  try{
    for(const x of need){
      const b=myPos?{lat:myPos.lat,lng:myPos.lng}:{};
      await api(x.kind==='station'?'repair_station':'repair_depot','POST',{[x.kind==='station'?'station_id':'depot_id']:x.o.id,...b});
    }
    unloading();SFX.play('build');el('repairModal').classList.remove('open');await loadWorld();await refreshAll();toast('🔧 Ремонт завершён');
  }catch(e){unloading();toast('Ошибка: '+e.message);}
};
el('repairNear').onclick=async()=>{
  if(!myPos){toast('Включите GPS (📍)');return;}
  const r=await act('gps_repair_nearby',{lat:myPos.lat,lng:myPos.lng},'Чиним рядом…');
  if(r){toast(r.fixed.length?`🔧 Бесплатно починено: ${r.fixed.length}`:'Рядом нечего чинить');openRepair();}
};

/* ============================================================
   ПРОДАЖА
   ============================================================ */
async function openSell(sIds=[],eIds=[],nIds=[],dIds=[],tIds=[]){
  if(!me)return;
  sellState={stations:[...sIds],edges:[...eIds],nodes:[...nIds],depots:[...dIds],trains:[...tIds],min:0,max:0};
  el('sellPrice').value='';
  const ms=mineArr(world.stations).filter(s=>!s.auction_status&&!s.is_building&&!s.upgrading);
  const me2=mineArr(world.edges).filter(e=>!e.auction_status&&!e.is_building&&!e.upgrading);
  const mn=mineArr(world.nodes).filter(n=>!n.frozen&&!n.auction_status);
  const md=mineArr(world.depots).filter(d=>!d.auction_status&&!d.is_building&&!d.upgrading);
  const mt=mineArr(world.trains).filter(t=>!t.auction_status&&!t.is_building);
  const fill=(boxId,title,arr,ids,key,label)=>{
    const box=el(boxId);box.innerHTML=`<h4>${title}</h4>`;
    if(!arr.length){box.innerHTML+='<div class="hint">Нет доступных</div>';return;}
    arr.forEach(o=>{
      const l=document.createElement('label');l.className='sell-row';
      l.innerHTML=`<input type="checkbox" ${ids.includes(o.id)?'checked':''}> ${esc(label(o))}`;
      l.querySelector('input').onchange=ev=>{
        if(ev.target.checked){if(!sellState[key].includes(o.id))sellState[key].push(o.id);}
        else sellState[key]=sellState[key].filter(x=>x!==o.id);
        sellQuote();
      };
      box.appendChild(l);
    });
  };
  fill('sellStations','Станции',ms,sIds,'stations',s=>`${s.name} · кл.${s.class}`);
  fill('sellEdges','Перегоны',me2,eIds,'edges',e=>`${e.from_name} ↔ ${e.to_name} · ${Math.round(e.length)} м`);
  fill('sellNodes','Узлы веток',mn,nIds,'nodes',n=>`${n.name} · ${n.edges_count||0} пер.`);
  fill('sellDepots','Депо (поезда уходят вместе)',md,dIds,'depots',d=>`${d.name} · ур.${d.level} · ${d.trains_count} 🚆`);
  fill('sellTrains','Поезда отдельно',mt,tIds,'trains',t=>`${t.name} · ${statusRu(t.status)}`);
  await sellQuote();
  el('sellModal').classList.add('open');
}
async function sellQuote(){
  const s=sellState;
  if(!s.stations.length&&!s.edges.length&&!s.nodes.length&&!s.depots.length&&!s.trains.length){el('minPrice').textContent='0';el('maxPrice').textContent='0';s.min=0;s.max=0;return;}
  try{
    const j=await api('get_sale_quote','POST',{station_ids:s.stations,edge_ids:s.edges,node_ids:s.nodes,depot_ids:s.depots,train_ids:s.trains});
    s.min=j.min_price;s.max=j.max_price;
    el('minPrice').textContent=fmt(s.min);el('maxPrice').textContent=fmt(s.max);
    const i=el('sellPrice');if(!i.value||+i.value<s.min)i.value=s.min;
  }catch(e){}
}
el('sellOk').onclick=async()=>{
  const p=+el('sellPrice').value,s=sellState;
  if(!s.stations.length&&!s.edges.length&&!s.nodes.length&&!s.depots.length&&!s.trains.length){toast('Выберите объект');return;}
  if(!(p>=s.min&&p<=s.max)){toast(`Цена: ${fmt(s.min)}–${fmt(s.max)} ₽`);return;}
  try{
    await api('list_for_sale','POST',{station_ids:s.stations,edge_ids:s.edges,node_ids:s.nodes,depot_ids:s.depots,train_ids:s.trains,price:p});
    el('sellModal').classList.remove('open');SFX.play('cash');await loadWorld();await refreshAll();toast('🏷 Лот на рынке');
  }catch(e){toast('Ошибка: '+e.message);}
};

/* ============================================================
   РЫНОК / РЕЙТИНГ / КОМПАНИИ / НОВОСТИ
   ============================================================ */
async function refreshLots(){
  try{
    const j=await api('auctions');lots=j.auctions;
    const list=el('lotsList');list.innerHTML='';
    el('cntLots').textContent=lots.filter(a=>a.is_mine).length+' моих';
    if(!lots.length){list.innerHTML='<div class="hint">Пусто. Лоты появляются при банкротстве или продаже.</div>';return;}
    lots.forEach(a=>{
      const d=document.createElement('div');d.className='lot'+(a.type==='bankrupt'?' bank':'');
      const names=(a.stations_preview||[]).map(s=>s.name).join(', ');
      const more=a.stations_count>(a.stations_preview||[]).length?`… +${a.stations_count-a.stations_preview.length}`:'';
      const parts=[`${a.stations_count} ст.`,`${a.edges_count} пер.`];
      if(a.nodes_count)parts.push(`${a.nodes_count} узл.`);
      if(a.depots_count)parts.push(`${a.depots_count} депо`);
      if(a.trains_count)parts.push(`${a.trains_count} 🚆`);
      const btn=a.is_mine?(a.type==='manual'?`<button class="lot-btn cancel" data-cancel="${a.id}">Отменить</button>`:`<button class="lot-btn" data-buy="${a.id}">Выкупить</button>`):`<button class="lot-btn" data-buy="${a.id}">Купить</button>`;
      d.innerHTML=`<div class="head"><b><span class="cdot" style="background:${a.seller_color};display:inline-block;vertical-align:-1px"></span> ${parts.join(' · ')}</b><span>${dur(a.seconds_left)}</span></div>
        <div class="names">${esc(names)}${more}</div>
        <div class="bottom"><span class="lot-price">${fmt(a.my_price)} ₽ <small>(восст. ${fmt(a.replacement_cost)})</small></span>${btn}</div>
        <div style="font-size:10px;color:var(--mut);margin-top:4px">${a.type==='bankrupt'?'💥 Банкрот':'🏷 Продажа'}</div>`;
      d.onclick=ev=>{if(ev.target.closest('button'))return;zoomLot(a);};
      list.appendChild(d);
    });
    list.querySelectorAll('[data-buy]').forEach(b=>b.onclick=ev=>{ev.stopPropagation();buyLot(+b.dataset.buy);});
    list.querySelectorAll('[data-cancel]').forEach(b=>b.onclick=ev=>{ev.stopPropagation();cancelLot(+b.dataset.cancel);});
  }catch(e){}
}
function zoomLot(a){
  const pts=(a.stations_preview||[]).filter(s=>s.lat&&s.lng);if(!pts.length)return;
  map.fitBounds(L.latLngBounds(pts.map(s=>[s.lat,s.lng])).pad(.6),{padding:[30,30]});
  pts.forEach(s=>{const c=L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:14,color:'#ffb02e',weight:3,fillColor:'#ffb02e',fillOpacity:.2,interactive:false}).addTo(map);setTimeout(()=>map.removeLayer(c),2600);});
}
function buyLot(id){modal({title:'💰 Покупка лота',ok:'Купить',body:'<p style="margin:0">Объекты перейдут вам, деньги — продавцу. Чужие маршруты через них разорвутся.</p>',
  onOk:async()=>{const r=await act('buy_auction',{auction_id:id},'Сделка…','cash');if(r)toast(`💰 Куплено за ${money(r.price)}`);}});}
function cancelLot(id){modal({title:'↩️ Отмена продажи',ok:'Вернуть',body:'<p style="margin:0">Снять лот и вернуть объекты?</p>',
  onOk:async()=>{const r=await act('cancel_sale',{auction_id:id},'Отменяем…','click');if(r)toast('Лот снят');}});}
async function refreshPlayers(){
  try{
    const j=await api('players'),list=el('playersList');list.innerHTML='';
    companies=j.players;
    j.players.forEach(p=>{
      const pol=p.policies||{};
      const icons=(pol.allow_trackage?'🛤':'')+(pol.allow_stops?'🚉':'')+(pol.allow_connect?'🔌':'');
      const d=document.createElement('div');d.className='row-line';
      d.innerHTML=`<span class="cdot" style="background:${p.color}"></span><b>${esc(p.name)}</b><span style="font-size:10px" title="Открыто: пути / остановки / подключение">${icons||'🔒'}</span><span style="margin-left:auto;color:var(--ink2);font-family:var(--f-mono);font-size:11px">${fmt(p.money)} ₽</span>${me&&p.id===me.id?' <span class="tag">вы</span>':''}`;
      list.appendChild(d);
    });
  }catch(e){}
}
async function refreshRating(){
  try{
    const j=await api('rating'),list=el('ratingList');list.innerHTML='';
    j.rating.forEach((p,i)=>{
      const d=document.createElement('div');d.className='row-line';
      d.innerHTML=`<span class="rank">${i+1}.</span><span class="cdot" style="background:${p.color}"></span><b>${esc(p.name)}</b><span style="margin-left:auto;color:${p.net_per_day>=0?'var(--green-t)':'var(--red-t)'};font-weight:700;font-family:var(--f-mono);font-size:11.5px">${fmt(p.net_per_day)} ₽/д</span>${me&&p.id===me.id?' <span class="tag">вы</span>':''}`;
      d.title=`Доход ${fmt(p.income_per_day)} · содержание ${fmt(p.upkeep_per_day)} · аренда +${fmt(p.tractage_in)}/−${fmt(p.tractage_out)} · ст.${p.stations} · 🧭${p.routes} · 🚆${p.trains}`;
      list.appendChild(d);
    });
    if(!j.rating.length)list.innerHTML='<div class="hint">Пока никого.</div>';
  }catch(e){}
}
async function refreshEvents(){
  try{
    const j=await api('events'),list=el('eventsList');list.innerHTML='';
    const vis=j.events.filter(e=>PUBLIC_EV.has(e.kind));
    if(!vis.length){list.innerHTML='<div class="hint">Пока тихо.</div>';return;}
    vis.forEach(e=>{
      const d=document.createElement('div');d.className='ev';
      d.innerHTML=`<span class="ei">${EV_ICON[e.kind]||'•'}</span><span class="et">${esc(e.text)}</span><span class="ed">${new Date(e.created_at).toLocaleTimeString('ru-RU',{hour:'2-digit',minute:'2-digit'})}</span>`;
      list.appendChild(d);
    });
    const top=j.events.length?j.events[0].id:0;
    if(newsSeen&&top>newsSeen&&!el('newsModal').classList.contains('open')){
      newsUnread+=j.events.filter(e=>e.id>newsSeen&&PUBLIC_EV.has(e.kind)).length;
      const m=el('newsMark');if(newsUnread>0){m.style.display='';m.textContent=newsUnread>9?'9+':newsUnread;}
    }
    newsSeen=top;
  }catch(e){}
}
el('btnNews').onclick=()=>{el('newsModal').classList.add('open');newsUnread=0;el('newsMark').style.display='none';refreshEvents();};

/* ============================================================
   ГРАФИКИ
   ============================================================ */
if(typeof Chart!=='undefined'){Chart.defaults.color='#9db0c9';Chart.defaults.borderColor='rgba(157,176,201,.13)';Chart.defaults.font.family="'Manrope',system-ui,sans-serif";Chart.defaults.font.size=11;}
const TT={backgroundColor:'#1a2740',borderColor:'#33507a',borderWidth:1,titleColor:'#e8f0fa',bodyColor:'#9db0c9',padding:10,cornerRadius:10,boxPadding:4};
document.querySelectorAll('#chartTabs .tab').forEach(b=>b.onclick=()=>{
  document.querySelectorAll('#chartTabs .tab').forEach(x=>x.classList.remove('active'));
  b.classList.add('active');chartMetric=b.dataset.m;SFX.play('click');
  if(chartMetric==='net')netChart();else if(chartMetric==='breakdown')breakdown();else companyChart();
});
el('btnChart').onclick=async()=>{
  el('chartModal').classList.add('open');chartCache={h:null,c:null};chartMetric='net';
  document.querySelectorAll('#chartTabs .tab').forEach(b=>b.classList.toggle('active',b.dataset.m==='net'));
  await netChart();
};
function chartReady(box){if(typeof Chart==='undefined'){box.innerHTML='<div style="padding:20px;color:var(--red-t)">Chart.js не загрузился.</div>';return false;}return true;}
async function netChart(){
  const box=el('chartBox');box.innerHTML='<div style="padding:20px;color:var(--mut)">Загрузка…</div>';
  if(!chartReady(box))return;
  try{
    if(!chartCache.h)chartCache.h=await api('history');
    const j=chartCache.h,days=[...new Set(j.history.map(r=>r.day))].sort((a,b)=>a-b);
    if(!days.length){box.innerHTML='<div style="padding:20px;color:var(--mut)">Нет данных — переждите день.</div>';return;}
    const ds=[];
    j.players.forEach(p=>{
      const m=new Map(j.history.filter(h=>h.player_id===p.id).map(h=>[h.day,h.net]));
      if(!m.size)return;
      ds.push({label:p.name,data:days.map(d=>m.has(d)?m.get(d):null),borderColor:p.color||'#35d07f',backgroundColor:(p.color||'#35d07f')+'22',tension:.3,spanGaps:true,borderWidth:2,pointRadius:0,pointHitRadius:10});
    });
    box.innerHTML='<canvas id="hChart"></canvas>';if(chart)chart.destroy();
    chart=new Chart(el('hChart').getContext('2d'),{type:'line',data:{labels:days,datasets:ds},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
      plugins:{legend:{position:'top',labels:{usePointStyle:true,boxWidth:8}},tooltip:{...TT,callbacks:{title:c=>'День '+c[0].label,label:c=>`${c.dataset.label}: ${fmt(c.parsed.y)} ₽/д`}}},
      scales:{x:{title:{display:true,text:'Игровой день'},ticks:{maxTicksLimit:20}},y:{title:{display:true,text:'Прибыль ₽/день'}}}}});
  }catch(e){box.innerHTML=`<div style="padding:20px;color:var(--red-t)">Ошибка: ${esc(e.message)}</div>`;}
}
async function companyChart(){
  const box=el('chartBox');box.innerHTML='<div style="padding:20px;color:var(--mut)">Загрузка…</div>';
  if(!chartReady(box))return;
  try{
    if(!chartCache.c)chartCache.c=await api('company_history');
    const j=chartCache.c,days=j.days,metric=chartMetric;
    if(!days.length){box.innerHTML='<div style="padding:20px;color:var(--mut)">Нет данных.</div>';return;}
    const titles={company_value:'Стоимость компании ₽',stations:'Станций',edge_km:'Км путей',routes:'Маршрутов',trains:'Поездов',depots:'Депо'};
    const ds=[];
    j.players.forEach(p=>{
      const m=new Map(p.history.map(h=>[h.day,h[metric]]));
      const data=days.map(d=>m.has(d)?m.get(d):null);
      if(data.every(v=>v===null))return;
      ds.push({label:p.name,data,borderColor:p.color||'#35d07f',backgroundColor:(p.color||'#35d07f')+'22',tension:.3,spanGaps:true,borderWidth:2,pointRadius:0,pointHitRadius:10});
    });
    box.innerHTML='<canvas id="hChart"></canvas>';if(chart)chart.destroy();
    const isM=metric==='company_value';
    chart=new Chart(el('hChart').getContext('2d'),{type:'line',data:{labels:days,datasets:ds},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
      plugins:{legend:{position:'top',labels:{usePointStyle:true,boxWidth:8}},tooltip:{...TT,callbacks:{title:c=>'День '+c[0].label,label:c=>`${c.dataset.label}: ${c.parsed.y===null?'—':(isM?fmt(c.parsed.y)+' ₽':c.parsed.y)}`}}},
      scales:{x:{title:{display:true,text:'Игровой день'},ticks:{maxTicksLimit:20}},y:{title:{display:true,text:titles[metric]||''},beginAtZero:!isM}}}});
  }catch(e){box.innerHTML=`<div style="padding:20px;color:var(--red-t)">Ошибка: ${esc(e.message)}</div>`;}
}
function breakdown(){
  const box=el('chartBox');
  if(!me){box.innerHTML='<div style="padding:20px;color:var(--mut)">Войдите.</div>';return;}
  const rs=mineArr(world.routes),ms=mineArr(world.stations),ns=mineArr(world.nodes),es=mineArr(world.edges),ds=mineArr(world.depots),ts=mineArr(world.trains);
  const e=lastEco||{income:0,upkeep:0,net:0,tractage_in:0,tractage_out:0};
  let html=`<div style="padding:4px 2px"><div class="bd-sum"><div class="bd-chip i">Доход: +${money(e.income)}/д</div><div class="bd-chip u">Расход: −${money(Math.ceil(e.upkeep))}/д</div><div class="bd-chip n">Чистыми: ${money(Math.floor(e.net))}/д</div></div>
    <div class="bd-h v">🧭 Доход по маршрутам</div>`;
  if(!rs.length)html+='<div class="hint">Маршрутов нет — дохода нет.</div>';
  rs.forEach(r=>{
    html+=`<div class="bd-card"><div class="bd-row"><span>${esc(r.name)} <small>${r.status==='active'?'работает':r.status==='idle'?'без поездов':'разорван'} · ${r.km} км · ${r.speed||0} км/ч · 🚆 ${r.trains||0}/${r.slots||0}</small></span><span class="${r.income>0?'pos':''}">${r.income>0?'+':''}${money(r.income)}</span></div>`;
    html+=`<div class="bd-sub"><span>2-путка ${Math.round((r.double_share||0)*100)}% · эл. ${Math.round((r.elec_share||0)*100)}%</span><span>×${(1+.25*(r.double_share||0)).toFixed(2)} · ×${(1+.2*(r.elec_share||0)).toFixed(2)} · ×${((r.speed||0)/60).toFixed(2)}</span></div>`;
    (r.stations||[]).forEach(sid=>{const s=st(sid);if(!s)return;
      html+=`<div class="bd-sub"><span>🚉 ${esc(s.name)} <small>${s.health}%</small></span><span>🏢 ${s.buildings} → ${bonusPct(s.buildings)}%</span></div>`;});
    (r.owners||[]).forEach(o=>{html+=`<div class="bd-sub"><span>🤝 ${esc(companyName(o.player_id))} · ${o.km.toFixed(2)} км</span><span class="neg">−${money(Math.round(r.income*o.share*(constants.trackage_share||.3)))}</span></div>`;});
    html+='</div>';
  });
  if(e.tractage_in)html+=`<div class="bd-line"><span>🤝 Субаренда ваших путей другими</span><span class="pos">+${money(e.tractage_in)}</span></div>`;
  html+='<div class="bd-h r">🔧 Содержание</div>';
  const grp=(title,arr,fn,note)=>{
    if(!arr.length)return'';
    let s=`<div class="bd-h r" style="font-size:10px;margin-top:10px">${title}</div>`;
    arr.forEach(o=>{s+=`<div class="bd-line"><span>${esc(o.name||'')} <small>${note(o)}</small></span><span class="neg">−${money(Math.ceil(fn(o)))}</span></div>`;});
    return s;
  };
  html+=grp('Станции',ms,stUpkeep,s=>`кл.${s.class}${s.served?'':s.passed?' · проезд ×0.75':' ×0.5'}`);
  html+=grp('Узлы',ns,ndUpkeep,n=>n.served?'в сети':'×0.5');
  html+=grp('Перегоны',es,edUpkeep,x=>`${Math.round(x.length)} м · ${x.double?'2п':'1п'}${x.electric?' ⚡':''}${x.served?'':' ×0.5'}`);
  html+=grp('Депо',ds,dpUpkeep,d=>`ур.${d.level}${d.served?'':' ×0.5'}`);
  html+=grp('Поезда',ts,trUpkeep,t=>statusRu(t.status));
  const rtU=rs.reduce((a,r)=>a+(r.upkeep||0)+(r.fee||0),0);
  if(rtU)html+=`<div class="bd-line"><span>🧭 Маршруты + субаренда</span><span class="neg">−${money(Math.ceil(rtU))}</span></div>`;
  html+='</div>';
  box.innerHTML=html;
}

/* ============================================================
   GPS / DEV / ПРОЧЕЕ
   ============================================================ */
el('btnGps').onclick=()=>{
  if(!navigator.geolocation){toast('GPS не поддерживается');return;}
  if(gpsWatch!==null){
    navigator.geolocation.clearWatch(gpsWatch);gpsWatch=null;myPos=null;
    if(gpsCircle&&map){map.removeLayer(gpsCircle);gpsCircle=null;}
    el('btnGps').classList.remove('on');toast('GPS выключен');renderRepairPanel();return;
  }
  gpsWatch=navigator.geolocation.watchPosition(p=>{
    myPos={lat:p.coords.latitude,lng:p.coords.longitude,acc:p.coords.accuracy};
    el('btnGps').classList.add('on');
    if(!map)return;
    if(gpsCircle)map.removeLayer(gpsCircle);
    gpsCircle=L.circle([myPos.lat,myPos.lng],{radius:Math.max(30,myPos.acc),color:'#3d9bff',fillColor:'#3d9bff',fillOpacity:.14,weight:2}).addTo(map);
    renderRepairPanel();
  },err=>toast('GPS: '+err.message),{enableHighAccuracy:true,maximumAge:10000,timeout:15000});
  toast('GPS включён — ремонт рядом бесплатный');
};
el('devMoney').onclick=async()=>{try{await api('dev_give_money','POST',{amount:10000});SFX.play('cash');await loadWorld();await refreshAll();toast('💵 +10 000 ₽');}catch(e){toast('Ошибка: '+e.message);}};
el('devBank').onclick=()=>modal({title:'💥 Банкротство',danger:true,ok:'Обанкротиться',body:'<p style="margin:0;color:var(--red-t)">Объекты уйдут на аукцион (кроме защищённого минимума).</p>',
  onOk:async()=>{const r=await act('dev_bankrupt',{},'Банкротим…','error');if(r)toast(`💥 ${r.stations} ст., ${r.nodes} узл., ${r.edges} пер.`);}});
el('btnSettings').onclick=()=>el('settingsModal').classList.add('open');
el('btnHelp').onclick=()=>el('helpModal').classList.add('open');
el('btnAuto').onclick=autoRoutes;
el('btnStarter').onclick=starterPack;

/* ============================================================
   РЕЖИМЫ
   ============================================================ */
function setMode(m){
  mode=m;edgeDraft={start:null,points:[]};routeDraft={stops:[],loop:false};routeQuote=null;edgeQuote=null;pendingSplit=null;
  closeCtx();clearGhost();clearTimeout(routeQuoteTimer);
  ['mStation','mEdge','mDepot','mRoute'].forEach((id,i)=>el(id).classList.toggle('active',['station','edge','depot','route'][i]===m));
  const mp=el('map');['station','edge','depot','route'].forEach(x=>mp.classList.toggle('m-'+x,m===x));
  if(map){if(m==='edge')map.doubleClickZoom.disable();else map.doubleClickZoom.enable();}
  renderEdges();renderRoutes();renderBridges();renderDraft();renderPreview();updateDock();
}
el('mStation').onclick=()=>{if(!me)return;if(me.money<pricing.station){toast('Нужно '+money(pricing.station));return;}SFX.play('click');setMode(mode==='station'?'idle':'station');};
el('mEdge').onclick=()=>{if(!me)return;SFX.play('click');setMode(mode==='edge'?'idle':'edge');};
el('mDepot').onclick=()=>{if(!me)return;if(me.money<pricing.depot){toast('Нужно '+money(pricing.depot));return;}SFX.play('click');setMode(mode==='depot'?'idle':'depot');};
el('mRoute').onclick=()=>{if(!me)return;SFX.play('click');setMode(mode==='route'?'idle':'route');};

/* ============================================================
   ВХОД / ОПРОС
   ============================================================ */
async function tryLogin(){
  try{const j=await api('me');if(j.player)logged(j.player);else el('loginModal').classList.add('open');}
  catch(e){el('loginModal').classList.add('open');}
}
function logged(p){
  me=p;
  el('meName').textContent=me.name;
  el('meChip').style.background=me.color||'#35d07f';
  el('balance').textContent=money(me.money);
  el('logoutBtn').style.display='';
  el('loginModal').classList.remove('open');
  el('lamp').className='lamp ok';
  el('devCard').style.display=['localhost','127.0.0.1',''].includes(location.hostname)?'':'none';
  if(!map)initMap();
  chromeH();
  setTimeout(()=>map&&map.invalidateSize(),160);
  loadWorld().then(()=>{refreshAll();startPoll();startLive();startStatus();fitAll();startChat();});
}
el('loginBtn').onclick=async()=>{
  const n=el('loginName').value.trim();if(!n)return;
  try{const j=await api('login','POST',{name:n});SFX.play('click');logged(j.player);}catch(e){toast('Ошибка входа: '+e.message);}
};
el('loginName').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();el('loginBtn').click();}});
el('logoutBtn').onclick=async()=>{
  try{await api('logout');}catch(e){}
  me=null;stopPoll();stopChat();stopStatus();closeCtx();setMode('idle');
  if(gpsWatch!==null){navigator.geolocation.clearWatch(gpsWatch);gpsWatch=null;myPos=null;if(gpsCircle&&map){map.removeLayer(gpsCircle);gpsCircle=null;}el('btnGps').classList.remove('on');}
  trainMarkers.forEach(v=>map.removeLayer(v.marker));trainMarkers.clear();
  if(tickerT){clearTimeout(tickerT);tickerT=null;}tickerQ=[];nextToast();
  world={stations:[],nodes:[],edges:[],routes:[],depots:[],trains:[]};companies=[];reindex();render();
  el('meName').textContent='—';el('meChip').style.background='var(--mut)';el('balance').textContent='0 ₽';
  el('bInc').textContent='+0 ₽';el('bUpk').textContent='−0 ₽';el('bNet').textContent='=0 ₽/д';
  el('bGrace').style.display='none';el('bRisk').style.display='none';
  el('logoutBtn').style.display='none';el('devCard').style.display='none';
  el('lamp').className='lamp err';
  el('loginModal').classList.add('open');
};
async function loadWorld(){
  try{
    const j=await api('load');
    world=j.data||{};
    ['stations','nodes','edges','routes','depots','trains'].forEach(k=>{if(!Array.isArray(world[k]))world[k]=[];});
    lastVersion=j.version;lastEco=j.economy||null;
    companies=j.companies||companies;palette=j.palette||palette;myPolicies=j.my_policies||myPolicies;constants=j.constants||constants;
    setTypes(j.bridge_types);
    if(j.constants){pricing.depot=j.constants.depot_cost;pricing.train=j.constants.train_cost;pricing.node=j.constants.node_cost;}
    if(j.tick_left!==undefined){tickLeft=j.tick_left;lastSrvAt=Date.now();}
    if(j.day!==undefined){curDay=j.day;el('dayNum').textContent=curDay;}
    if(j.now!==undefined)srvOffset=j.now-Date.now()/1000;
    if(me){const c=companies.find(x=>x.id===me.id);if(c){me.color=c.color;el('meChip').style.background=c.color;}}
    render();
  }catch(e){}
}
function render(){
  if(!map)return;
  reindex();
  renderEdges();renderRoutes();renderBridges();renderStations();renderNodes();renderDepots();renderDraft();renderPreview();syncTrains();
  renderBoard();
}
function startPoll(){if(polling)return;polling=true;poll();}
function stopPoll(){polling=false;clearTimeout(pollTimer);}
async function poll(){
  if(!polling)return;
  try{
    const j=await api('poll');lastPoll=j;
    el('lamp').className='lamp ok';
    if(j.tick_left!==undefined){tickLeft=j.tick_left;lastSrvAt=Date.now();}
    if(j.day!==undefined&&curDay!==j.day){curDay=j.day;el('dayNum').textContent=curDay;SFX.play('day');}
    if(j.money!==undefined&&me){
      const prev=me.money;me.money=j.money;
      const b=el('balance');b.textContent=money(me.money);
      if(j.money>prev){b.classList.remove('up');void b.offsetWidth;b.classList.add('up');SFX.play('cash');}
      else if(j.money<prev){b.classList.remove('down');void b.offsetWidth;b.classList.add('down');}
    }
    if(j.color&&me){me.color=j.color;el('meChip').style.background=j.color;}
    el('bInc').textContent='+'+fmt(j.income_per_day)+' ₽';
    el('bUpk').textContent='−'+fmt(j.upkeep_per_day)+' ₽';
    el('bNet').textContent='='+fmt(j.net_per_day)+' ₽/д';
    el('bGrace').style.display=j.grace_active?'':'none';
    if(j.days_to_bankrupt!==null&&j.days_to_bankrupt!==undefined&&j.days_to_bankrupt<60){
      el('bRisk').style.display='';el('bRisk').textContent='⚠ банкрот через '+j.days_to_bankrupt+' д';
    }else el('bRisk').style.display='none';
    if(j.now!==undefined)srvOffset=j.now-Date.now()/1000;
    pricing.station=j.next_station_cost;pricing.edge=Number(j.edge_cost_per_m);
    if(j.next_depot_cost)pricing.depot=j.next_depot_cost;
    if(j.train_cost)pricing.train=j.train_cost;
    if(j.node_cost)pricing.node=j.node_cost;
    updatePrices();renderCompanyCard();
    if(j.version!==lastVersion){await loadWorld();await refreshAll();}
  }catch(e){
    el('lamp').className='lamp warn';
    if(e.message&&e.message.includes('not logged in')){stopPoll();el('lamp').className='lamp err';el('loginModal').classList.add('open');return;}
  }
  pollTimer=setTimeout(poll,POLL_MS);
}
async function refreshAll(){await Promise.all([refreshPlayers(),refreshRating(),refreshLots(),refreshEvents()]);renderBoard();}
let liveT=null;
function startLive(){
  if(liveT)return;
  liveT=setInterval(()=>{
    if(lastSrvAt>0){
      const l=Math.max(0,tickLeft-Math.floor((Date.now()-lastSrvAt)/1000));
      el('tickSec').textContent=l;
      el('tickBar').style.width=clamp((1-l/DAY)*100,0,100)+'%';
    }
    document.querySelectorAll('[data-cd]').forEach(n=>{const s=left(+n.dataset.cd);n.textContent=s>0?dur(s):'готово';});
  },1000);
}

/* ============================================================
   ЧАТ
   ============================================================ */
async function refreshChat(){
  if(!me)return;
  try{
    const j=await api('chat_get'),box=el('chatMsgs'),msgs=j.messages||[];
    const last=msgs.length?msgs[msgs.length-1].id:0;
    if(last===chatLast&&box.dataset.r==='1')return;
    const newOther=box.dataset.r==='1'&&msgs.some(m=>m.id>chatLast&&m.player_id!==me.id);
    chatLast=last;box.innerHTML='';
    msgs.forEach(m=>{
      const d=document.createElement('div');d.className='cmsg';
      const col=m.color||companyColor(m.player_id);
      d.innerHTML=`<span class="a" style="color:${col}">${esc(m.player_name||'игрок')}:</span> ${esc(m.text)} <span class="t">${new Date(m.created_at).toLocaleTimeString('ru-RU',{hour:'2-digit',minute:'2-digit'})}</span>`;
      box.appendChild(d);
    });
    box.dataset.r='1';box.scrollTop=box.scrollHeight;
    if(newOther&&!el('chat').classList.contains('hide'))SFX.play('chat');
  }catch(e){}
}
function startChat(){if(chatTimer)return;refreshChat();chatTimer=setInterval(refreshChat,3000);}
function stopChat(){clearInterval(chatTimer);chatTimer=null;}
el('btnChat').onclick=()=>{const c=el('chat');c.classList.toggle('hide');el('btnChat').classList.toggle('on',!c.classList.contains('hide'));if(!c.classList.contains('hide'))refreshChat();};
el('chatClose').onclick=()=>{el('chat').classList.add('hide');el('btnChat').classList.remove('on');};
async function sendChat(){
  const i=el('chatInput'),t=i.value.trim();if(!t)return;i.value='';
  try{await api('chat_send','POST',{text:t});SFX.play('click');await refreshChat();}catch(e){toast('Ошибка чата: '+e.message);}
}
el('chatSend').onclick=sendChat;
el('chatInput').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();sendChat();}});

/* ============================================================
   КЛАВИАТУРА
   ============================================================ */
document.addEventListener('keydown',e=>{
  const tag=e.target.tagName;
  const typing=tag==='INPUT'||tag==='TEXTAREA'||tag==='SELECT';
  if(e.key==='Enter'&&el('actionModal').classList.contains('open')){
    const b=el('actionOk');if(b&&!b.disabled){e.preventDefault();b.click();}return;
  }
  if(e.key==='Enter'&&el('sellModal').classList.contains('open')&&document.activeElement===el('sellPrice')){e.preventDefault();el('sellOk').click();return;}
  if(typing){
    if(e.key==='Escape'&&el('actionModal').classList.contains('open')){e.preventDefault();el('actionCancel').click();}
    return;
  }
  if(el('actionModal').classList.contains('open')){if(e.key==='Escape'){e.preventDefault();el('actionCancel').click();}return;}
  if(e.key==='1'&&me)setMode(mode==='station'?'idle':'station');
  else if(e.key==='2'&&me)setMode(mode==='edge'?'idle':'edge');
  else if(e.key==='3'&&me)setMode(mode==='depot'?'idle':'depot');
  else if(e.key==='4'&&me)setMode(mode==='route'?'idle':'route');
  else if(e.key==='Enter'&&mode==='edge'&&edgeDraft.start){e.preventDefault();finishEdgeInField();}
  else if(e.key===' '){
    if(mode==='edge'&&edgeDraft.start&&mouseLL){e.preventDefault();const s=findSnap(mouseLL);const ll=s?L.latLng(s.lat,s.lng):snap(mouseLL);edgeDraft.points.push({lat:ll.lat,lng:ll.lng});SFX.play('snap');renderDraft();updateDock();}
    else if(mode==='idle'){e.preventDefault();ui.paused=!ui.paused;el('moPause').checked=ui.paused;saveUI();updateTrainStatuses();toast(ui.paused?'⏸ Пауза':'▶ Движение');}
  }
  else if(e.key==='Backspace'){
    if(mode==='edge'&&edgeDraft.points.length){e.preventDefault();edgeDraft.points.pop();renderDraft();updateDock();}
    else if(mode==='route'&&routeDraft.stops.length){e.preventDefault();routeDraft.stops.pop();if(routeDraft.stops.length<3)routeDraft.loop=false;renderDraft();quoteRoute();}
  }
  else if(e.key==='Escape'){
    for(const id of ['sellModal','settingsModal','companyModal','newsModal','helpModal','repairModal','chartModal']){
      if(el(id).classList.contains('open')){el(id).classList.remove('open');if(id==='chartModal'&&chart){chart.destroy();chart=null;chartCache={h:null,c:null};}return;}
    }
    if(ctx.classList.contains('open')){closeCtx();return;}
    setMode('idle');
  }
});

/* ============================================================
   RESIZE / СТАРТ
   ============================================================ */
let rzT=null;
window.addEventListener('resize',()=>{clearTimeout(rzT);rzT=setTimeout(()=>{chromeH();if(map){map.invalidateSize();drawGrid();renderRoutes();renderBridges();}},160);});
setTypes(FALLBACK_TYPES);
loadUI();chromeH();
tryLogin();
</script>
</body>
</html>
