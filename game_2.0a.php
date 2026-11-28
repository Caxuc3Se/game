<?php
declare(strict_types=1);
ini_set('display_errors','1'); error_reporting(E_ALL);
/* ---------- PERF ---------- */
const PERF_LOG=__DIR__.'/perf.log'; const PERF_SLOW_MS=300; const PERF_SAMPLE_EVERY=50;
$GLOBALS['__perf']=[];
function perfAdd(string $k,float $t0):void{ $GLOBALS['__perf'][$k]=($GLOBALS['__perf'][$k]??0)+(microtime(true)-$t0)*1000; }
function perfCount(string $k):void{ $GLOBALS['__perf'][$k.'_n']=($GLOBALS['__perf'][$k.'_n']??0)+1; }
function perfWrite(int $code,int $bytes,float $encMs,?string $err=null):void{
static $done=false; if($done)return; $done=true;
$T0=$_SERVER['REQUEST_TIME_FLOAT']??microtime(true); $ms=(microtime(true)-$T0)*1000;
$slow=$ms>=PERF_SLOW_MS||$code>=400||$err!==null;
if(!$slow&&mt_rand(1,PERF_SAMPLE_EVERY)!==1)return;
$p=$GLOBALS['__perf'];
$row=['ts'=>date('c'),'action'=>(string)($_GET['action']??''),'pid'=>(int)explode('.',$_COOKIE['pid']??'0')[0],
'ms'=>round($ms,1),'code'=>$code,'kb'=>round($bytes/1024,1),'enc'=>round($encMs,1),
'osm_st'=>round($p['osm_st']??0,1),'osm_st_idx'=>round($p['osm_st_idx']??0,1),
'tick'=>round($p['tick']??0,1),'eco'=>round($p['eco']??0,1),'eco_n'=>$p['eco_n']??0,'rc_n'=>$p['rc_n']??0,
'world'=>round($p['world']??0,1),'deco'=>round($p['deco']??0,1),'lock'=>round($p['lock']??0,1),
'eco_hit'=>round($p['eco_hit']??0,1),'osm_bld'=>round($p['osm_bld']??0,1),'osm_cov'=>round($p['osm_cov']??0,1),'osm_riv'=>round($p['osm_riv']??0,1),
'obs'=>round($p['obs']??0,1),'obs_riv'=>round($p['obs_riv']??0,1),'obs_wat'=>round($p['obs_wat']??0,1),'obs_bld'=>round($p['obs_bld']??0,1),
'mem'=>round(memory_get_peak_usage(true)/1048576,1)];
if($err)$row['err']=mb_substr($err,0,200);
if(@filesize(PERF_LOG)>5e6)@rename(PERF_LOG,PERF_LOG.'.1');
@file_put_contents(PERF_LOG,json_encode($row,JSON_UNESCAPED_UNICODE)."\n",FILE_APPEND|LOCK_EX); }
register_shutdown_function(function(){ perfWrite(http_response_code()?:500,0,0,(error_get_last()['message']??null)); });
/* ---------- константы ---------- */
const DB_FILE=__DIR__.'/game.db'; const OSM_DB_FILE=__DIR__.'/map.db';
const DAY_DURATION_SEC=60; const START_MONEY=10000; const COST_STATION=250; const COST_EDGE_PER_M=1;
const COST_UPGRADE_DOUBLE_PER_M=0.80; const COST_UPGRADE_ELECTRIFY_PER_M=0.50; const MAX_CLASS=10;
const COOKIE_SECRET='change-me-please-32-bytes-min-xxxxxxxx'; const MAX_EDGE_POINTS=200;
const STATION_PRICE_GROWTH=1.02; const EDGE_PRICE_GROWTH=1.01; const UPKEEP_STATION_BASE=2; const UPKEEP_STATION_PER_CLASS=2;
const UPKEEP_EDGE_INCOME_SHARE=0.10; const INCOME_PER_TRACK=15; const FORECAST_DAYS_ON_CONFISCATE=3;
const AUCTION_DURATION_SEC=10800; const AUCTION_PRICE_OWNER_RATIO=0.40; const AUCTION_PRICE_OUTSIDER_RATIO=0.60;
const STATION_RADIUS_BASE=8.0; const STATION_RADIUS_PER_CLASS=1.5; const EVENTS_KEEP_DAYS=7; const LONG_EDGE_M=5000;
const BUILD_TIME_STATION=1; const BUILD_TIME_EDGE_PER_KM=0.5; const BUILD_TIME_UPGRADE_DOUBLE=1; const BUILD_TIME_UPGRADE_ELECTRIC=1; const BUILD_TIME_CLASS_UPGRADE=1;
const REFUND_RATIO=0.50; const HEALTH_DECAY_PER_REAL_DAY=0.1; const REAL_DAY_SEC=86400; const REPAIR_COST_RATIO=0.10; const REPAIR_FREE_RADIUS_M=1000;
const OSM_DISCOUNT=0.10; const OSM_STATION_RADIUS=200; const OSM_EDGE_DISCOUNT=0.30; const OSM_EDGE_TOLERANCE_M=50;
const COST_CULVERT=100; const COST_DAM=300; const COST_DEMOLISH_BUILDING=200; const BASE_LAT=55.75; const BASE_LNG=37.62;
const ROUTE_SLOTS_PER_CLASS=1; const WAGONS_PER_CLASS=2; const CLASS_LIMITS_TRACKS=false; const CLASS_LIMITS_TRAIN_SLOTS=false; const CLASS_INCOME_STEP=0.0;
const COST_NODE=50; const UPKEEP_NODE=0.5; const NODE_SNAP_M=30.0; const MIN_EDGE_LEN_M=20.0; const MAX_EDGE_LEN_M=20000; const MIN_SPLIT_DIST_M=60.0; const NODE_NAME_DEFAULT='Узел';
const BLOCK_LEN_DEFAULT=750; const BLOCK_LEN_MIN=250; const BLOCK_LEN_MAX=5000; const BLOCK_LEN_STEP=50; const COST_BLOCK=10;
const STATION_BLOCK_MAX_CLASS=3; const BLOCK_DEADLOCK_SEC=28;
const COMPANY_COLORS=['#35d07f','#ff5a5a','#3d9bff','#ffb02e','#a97bff','#24d3c4','#ff5da2','#b6e83a','#ff8a3d','#7fa6d8','#e0d84a','#f06292','#0f766e','#06b6d4','#7dd3fc','#1d4ed8','#4f46e5','#c084fc','#7e22ce','#d946ef','#f9a8d4','#be123c','#d97706','#eab308','#65a30d','#92400e','#d6b48a','#9ca3af','#475569','#e5e7eb','#1f2937','#0ea5e9'];
const TRACTAGE_REVENUE_SHARE=0.30; const TRACTAGE_SHARE_BASIS='income'; const TRANSIT_UPKEEP_RATIO=0.75;
const COST_BRIDGE=500; const COST_OVERPASS_BASE=400; const BRIDGE_DEFAULT_TYPE='concrete_beam'; const BRIDGE_RIVER_MIN_LENGTH_M=500;
const BRIDGE_WATER_HALF_WIDTH_M=60.0; const BRIDGE_MIN_SPAN_M=40.0; const BRIDGE_MAX_SPAN_M=400.0; const BRIDGE_SAMPLE_STEP_M=5.0;
const BRIDGE_CLUSTER_RADIUS_M=80.0; const BRIDGE_MATCH_RADIUS_M=60.0; const OVERPASS_SPAN_M=60.0; const RAIL_CROSS_MIN_ANGLE_DEG=30.0;
const RAIL_CROSS_STATION_SKIP_M=80.0; const RAIL_CROSS_SHARED_SKIP_M=300.0;
const EDGE_SPEED_BASE=120; const EDGE_SPEED_ELECTRIC=180; const ROUTE_SPEED_BASE=120;
const ROUTE_INCOME_BASE=250; const ROUTE_DOUBLE_BONUS=0.25; const ROUTE_ELECTRIC_BONUS=0.20; const ROUTE_UPKEEP_PER_KM=0.5;
const MAX_ROUTE_STOPS=20; const TRAIN_ROUTE_KM_DIVISOR=3.0; const FREIGHT_BREAK_EVEN_KM=10.0;
const DEPOT_COST=1500; const DEPOT_PRICE_GROWTH=1.02; const DEPOT_MAX_STATION_DIST_M=500; const DEPOT_CAPACITY_BASE=3; const DEPOT_CAPACITY_PER_LEVEL=2;
const DEPOT_MAX_LEVEL=5; const DEPOT_UPGRADE_COST=800; const DEPOT_UPKEEP_BASE=4; const DEPOT_UPKEEP_PER_LEVEL=2; const BUILD_TIME_DEPOT=2; const BUILD_TIME_DEPOT_UPGRADE=1;
const SUBSTATION_COST=2500; const SUBSTATION_PRICE_GROWTH=1.02; const SUBSTATION_UPKEEP=4; const BUILD_TIME_SUBSTATION=2; const SUBSTATION_MAX_STATION_DIST_M=500; const SUBSTATION_RANGE_M=8000;
const TRACK_MAINT_COST=2000; const TRACK_MAINT_PRICE_GROWTH=1.02; const TRACK_MAINT_UPKEEP=3; const TRACK_MAINT_UPKEEP_HALF=1.5;
const BUILD_TIME_TRACK_MAINT=2; const TRACK_MAINT_MAX_STATION_DIST_M=500; const TRACK_MAINT_RANGE_M=10000;
const TRAIN_UPKEEP_ACTIVE=3.0; const TRAIN_UPKEEP_IDLE=1.5; const BUILD_TIME_TRAIN=1; const TRAIN_REBASE_COST=200; const BUILD_TIME_TRAIN_REBASE=1;
const TRAIN_WEAR_PER_REAL_DAY=10.0; const TRAIN_VISIBLE_SCALE_M=2000; const PRESETS_MAX=10; const MAX_WAGONS_PER_TRAIN=20;
const CONSERVATION_RATIO=0.50; const GRACE_DAYS=20; const GRACE_UPKEEP_RATIO=0.50; const RESTART_GRANT=2000; const RESTART_GRANT_COOLDOWN_SEC=604800;
/* ---------- модели / вагоны ---------- */
function trainModels(): array { return [
'diesel_shunter'=>['label'=>'Маневровый тепловоз','traction'=>'diesel','max_speed'=>120,'cost'=>500,'power'=>0.85,'accel'=>0.55,'img'=>null],
'diesel_line'=>['label'=>'Магистральный тепловоз','traction'=>'diesel','max_speed'=>120,'cost'=>800,'power'=>1.00,'accel'=>0.80,'img'=>null],
'diesel_express'=>['label'=>'Скоростной тепловоз','traction'=>'diesel','max_speed'=>120,'cost'=>1300,'power'=>1.10,'accel'=>1.10,'img'=>null],
'electric_line'=>['label'=>'Обычная электричка','traction'=>'electric','max_speed'=>180,'cost'=>1200,'power'=>1.00,'accel'=>0.90,'img'=>null],
'electric_express'=>['label'=>'Скоростная электричка','traction'=>'electric','max_speed'=>180,'cost'=>1800,'power'=>1.10,'accel'=>1.20,'img'=>null],
'electric_high'=>['label'=>'Высокоскоростной электропоезд','traction'=>'electric','max_speed'=>180,'cost'=>2600,'power'=>1.20,'accel'=>1.50,'img'=>null]]; }
function normTrainModel(?string $m): string { $m=(string)$m; $lg=['generic'=>'diesel_line','diesel'=>'diesel_line','electric'=>'electric_line']; if(isset($lg[$m]))$m=$lg[$m]; return isset(trainModels()[$m])?$m:'diesel_line'; }
function trainModelImg(?string $m): ?string { $k=normTrainModel($m); $c=trainModels()[$k]['img']??null; if($c)return (string)$c; $rel='img/trains/'.$k.'.png'; return file_exists(__DIR__.'/'.$rel)?$rel:null; }
function trainModelsPublic(): array { $o=[]; foreach(trainModels() as $k=>$m){ $m['img']=trainModelImg($k); $o[]=['key'=>$k]+$m; } return $o; }
function trainModelSpeed(?string $m): int { return (int)trainModels()[normTrainModel($m)]['max_speed']; }
function trainModelCost(?string $m): int { return (int)trainModels()[normTrainModel($m)]['cost']; }
function trainModelLabel(?string $m): string { return (string)trainModels()[normTrainModel($m)]['label']; }
function trainModelPower(?string $m): float { return (float)trainModels()[normTrainModel($m)]['power']; }
function trainModelTraction(?string $m): string { return (string)trainModels()[normTrainModel($m)]['traction']; }
function trainModelAccel(?string $m): float { return (float)trainModels()[normTrainModel($m)]['accel']; }
function wagonTypes(): array { return [
'passenger'=>['label'=>'Пассажирский','cap'=>1,'freight'=>false,'cost'=>300,'upkeep_active'=>0.5,'upkeep_idle'=>0.25,'hatch'=>false],
'baggage'=>['label'=>'Багажный','cap'=>2,'freight'=>true,'cost'=>500,'upkeep_active'=>0.8,'upkeep_idle'=>0.40,'hatch'=>true],
'mail'=>['label'=>'Почтовый','cap'=>2,'freight'=>true,'cost'=>600,'upkeep_active'=>0.8,'upkeep_idle'=>0.40,'hatch'=>true]]; }
function wagonTypesPublic(): array { $o=[]; foreach(wagonTypes() as $k=>$w)$o[]=['key'=>$k]+$w; return $o; }
function normWagonType(?string $w): ?string { $w=(string)$w; return isset(wagonTypes()[$w])?$w:null; }
function wagonCap(string $w): int { return (int)wagonTypes()[$w]['cap']; }
function wagonIsFreight(string $w): bool { return (bool)wagonTypes()[$w]['freight']; }
function wagonCost(string $w): int { return (int)wagonTypes()[$w]['cost']; }
function wagonUpkeep(string $w,bool $run): float { return $run?(float)wagonTypes()[$w]['upkeep_active']:(float)wagonTypes()[$w]['upkeep_idle']; }
function wagonLabel(string $w): string { return (string)wagonTypes()[$w]['label']; }
/* ---------- служебное ---------- */
function haversineDistanceMeters(float $a,float $b,float $c,float $d): float { $R=6371000; $dLa=deg2rad($c-$a); $dLo=deg2rad($d-$b); $x=sin($dLa/2)**2+cos(deg2rad($a))*cos(deg2rad($c))*sin($dLo/2)**2; return $R*2*atan2(sqrt($x),sqrt(1-$x)); }
function stationUpgradeCost(int $c): int { return (int)ceil($c*1.25*50); }
function stationRadiusForClass(int $c): float { return STATION_RADIUS_BASE+(max(1,$c)-1)*STATION_RADIUS_PER_CLASS; }
function stationClassMultiplier(int $c): float { return 1.0+CLASS_INCOME_STEP*(max(1,$c)-1); }
function stationRouteLimit(array $s): int { return max(1,(int)($s['class']??1))*ROUTE_SLOTS_PER_CLASS; }
function stationWagonLimit(array $s): int { return max(0,(int)($s['class']??1))*WAGONS_PER_CLASS; }
function depotCapacityForLevel(int $l): int { return DEPOT_CAPACITY_BASE+(max(1,$l)-1)*DEPOT_CAPACITY_PER_LEVEL; }
function normBlockLen($v): int { $n=(int)$v; if($n<=0)$n=BLOCK_LEN_DEFAULT; $n=(int)(round($n/BLOCK_LEN_STEP)*BLOCK_LEN_STEP); return max(BLOCK_LEN_MIN,min(BLOCK_LEN_MAX,$n)); }
function edgeBlockLen(array $e): int { return normBlockLen($e['block_len']??BLOCK_LEN_DEFAULT); }
function edgeBlocksN(array $e): int { if(empty($e['double']))return 1; $len=(float)($e['length']??0); return max(1,(int)ceil($len/max(1,edgeBlockLen($e)))); }
function blocksCostFor(array $e): int { return edgeBlocksN($e)*COST_BLOCK; }
function blocksNFor(float $len,bool $double,int $blen): int { return $double?max(1,(int)ceil($len/max(1,$blen))):1; }
function stationBlocksEntry(array $s): bool { return max(1,(int)($s['class']??1))<=STATION_BLOCK_MAX_CLASS; }
class ApiError extends RuntimeException { public int $httpCode; public function __construct(string $m,int $c=400){ parent::__construct($m); $this->httpCode=$c; } }
function fail(string $m,int $c=400): void { throw new ApiError($m,$c); }
function sendJson($d,int $c=200): void {
$tEnc=microtime(true); $json=json_encode($d,JSON_UNESCAPED_UNICODE); $encMs=(microtime(true)-$tEnc)*1000;
http_response_code($c); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
perfWrite($c,strlen($json),$encMs);
if(extension_loaded('zlib')&&!ini_get('zlib.output_compression')) ob_start('ob_gzhandler');
echo $json; exit; }
set_exception_handler(function(Throwable $e){ if($e instanceof ApiError) sendJson(['error'=>$e->getMessage()],$e->httpCode); sendJson(['error'=>'server error: '.$e->getMessage()],500); });
function readJson(): array { $r=file_get_contents('php://input'); $j=json_decode($r===false?'':$r,true); return is_array($j)?$j:[]; }
function idList($v): array { if(!is_array($v))return []; $o=[]; foreach($v as $x) if(is_string($x)||is_int($x))$o[]=(string)$x; return array_values(array_unique($o)); }
function newId(string $p): string { return $p.(int)round(microtime(true)*1000).'_'.bin2hex(random_bytes(3)); }
function isLocalRequest(): bool { $ip=$_SERVER['REMOTE_ADDR']??''; return $ip==='127.0.0.1'||$ip==='::1'; }
function cacheReset(): void { foreach(['__eco','__pol','__path','__col','__pow','__tm','__cm','__esz'] as $k)$GLOBALS[$k]=null; }
function ensureColumn(PDO $pdo,string $t,string $c,string $def): void { try{ foreach($pdo->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC) as $x) if(($x['name']??'')===$c)return; $pdo->exec("ALTER TABLE $t ADD COLUMN $c $def"); }catch(Throwable $e){} }
function db(): PDO {
static $pdo=null; if($pdo!==null)return $pdo;
$pdo=new PDO('sqlite:'.DB_FILE); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA journal_mode=WAL"); $pdo->exec("PRAGMA busy_timeout=5000");
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
$pdo->exec("CREATE TABLE IF NOT EXISTS eco_cache (version INTEGER PRIMARY KEY, data TEXT NOT NULL, created_at TEXT NOT NULL)");
$pdo->exec("CREATE TABLE IF NOT EXISTS fast_rail_stations (lat REAL NOT NULL, lng REAL NOT NULL)");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_frs_lat ON fast_rail_stations(lat)");
foreach([['players','color',"TEXT NOT NULL DEFAULT ''"],['players','starter_claimed',"INTEGER NOT NULL DEFAULT 0"],['players','grace_until_day',"INTEGER NOT NULL DEFAULT 0"],['players','last_grant_at',"INTEGER NOT NULL DEFAULT 0"],['players','company_name',"TEXT NOT NULL DEFAULT ''"],['players','founder',"TEXT NOT NULL DEFAULT ''"],['auctions','type',"TEXT NOT NULL DEFAULT 'bankrupt'"],['auctions','depots',"TEXT NOT NULL DEFAULT '[]'"],['auctions','trains',"TEXT NOT NULL DEFAULT '[]'"],['auctions','nodes',"TEXT NOT NULL DEFAULT '[]'"],['auctions','substations',"TEXT NOT NULL DEFAULT '[]'"],['auctions','track_maint',"TEXT NOT NULL DEFAULT '[]'"],['company_snapshots','depots',"INTEGER NOT NULL DEFAULT 0"],['company_snapshots','trains',"INTEGER NOT NULL DEFAULT 0"],['company_snapshots','routes',"INTEGER NOT NULL DEFAULT 0"],['company_snapshots','nodes',"INTEGER NOT NULL DEFAULT 0"],['company_snapshots','substations',"INTEGER NOT NULL DEFAULT 0"],['company_snapshots','track_maint',"INTEGER NOT NULL DEFAULT 0"]] as $c) ensureColumn($pdo,$c[0],$c[1],$c[2]);
if((int)$pdo->query("SELECT COUNT(*) FROM world")->fetchColumn()===0){
$empty=['stations'=>[],'nodes'=>[],'edges'=>[],'routes'=>[],'depots'=>[],'trains'=>[],'substations'=>[],'track_maint'=>[],'hqs'=>[],'presets'=>[]];
$pdo->prepare("INSERT INTO world (id,data,updated_at,version) VALUES (1,:d,:u,0)")->execute([':d'=>json_encode($empty),':u'=>date('c')]);
foreach(['last_tick'=>(string)time(),'day_count'=>'1','next_due'=>'0','health_tick'=>(string)time(),'eco_ver'=>'1'] as $k=>$v) $pdo->exec("INSERT OR REPLACE INTO meta (key,value) VALUES ('$k','$v')");
}
try{ $rows=$pdo->query("SELECT id,color FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC); $used=[];
foreach($rows as $r) if(!empty($r['color']))$used[strtolower($r['color'])]=true;
$upd=$pdo->prepare("UPDATE players SET color=:c WHERE id=:id");
foreach($rows as $r){ if(!empty($r['color']))continue; $c=null; foreach(COMPANY_COLORS as $cand) if(!isset($used[strtolower($cand)])){ $c=$cand; break; } if($c===null)$c=COMPANY_COLORS[((int)$r['id']-1)%count(COMPANY_COLORS)]; $used[strtolower($c)]=true; $upd->execute([':c'=>$c,':id'=>(int)$r['id']]); }
}catch(Throwable $e){}
return $pdo; }
function metaGet(string $k): ?string { $s=db()->prepare("SELECT value FROM meta WHERE key=:k"); $s->execute([':k'=>$k]); $v=$s->fetchColumn(); return $v===false?null:(string)$v; }
function metaSet(string $k,string $v): void { db()->prepare("INSERT OR REPLACE INTO meta (key,value) VALUES (:k,:v)")->execute([':k'=>$k,':v'=>$v]); }
function runTx(callable $fn) { $p=db(); $__t=microtime(true); $p->exec('BEGIN IMMEDIATE'); perfAdd('lock',$__t); try{ $r=$fn(); $p->exec('COMMIT'); return $r; }catch(Throwable $e){ try{$p->exec('ROLLBACK');}catch(Throwable $_){} throw $e; } }
function normAccess($v): string { $v=(string)$v; return in_array($v,['open','closed'],true)?$v:'inherit'; }
/* ---------- мир ---------- */
function worldGet(): array { $__t=microtime(true);
$row=db()->query("SELECT data,version FROM world WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$d=json_decode($row['data'],true); if(!is_array($d))$d=[];
foreach(['stations','nodes','edges','routes','depots','trains','substations','track_maint','hqs','presets'] as $k) if(!isset($d[$k])||!is_array($d[$k]))$d[$k]=[];
foreach($d['stations'] as &$s){
foreach(['class'=>1,'capacity'=>4,'built_day'=>1,'is_building'=>false,'build_end'=>0,'build_type'=>null,'upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,'frozen'=>false,'health'=>100,'buildings'=>0] as $k=>$v) if(!isset($s[$k]))$s[$k]=$v;
if(!isset($s['labelOffset']))$s['labelOffset']=['x'=>10,'y'=>4];
$s['access']=normAccess($s['access']??'inherit'); $s['health']=max(0,min(100,(int)$s['health'])); $s['lat']=(float)$s['lat']; $s['lng']=(float)$s['lng'];
$exp=stationRadiusForClass((int)$s['class']); if(!isset($s['radius'])||abs((float)$s['radius']-$exp)>0.01)$s['radius']=$exp;
} unset($s);
foreach($d['nodes'] as &$n){ foreach(['built_day'=>1,'frozen'=>false,'name'=>NODE_NAME_DEFAULT] as $k=>$v) if(!isset($n[$k]))$n[$k]=$v; if(!isset($n['labelOffset']))$n['labelOffset']=['x'=>8,'y'=>4]; $n['access']=normAccess($n['access']??'inherit'); $n['lat']=(float)$n['lat']; $n['lng']=(float)$n['lng']; } unset($n);
foreach($d['edges'] as &$e){
foreach(['electric'=>false,'built_day'=>1,'double'=>false,'points'=>[],'is_building'=>false,'build_end'=>0,'build_type'=>null,'upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'frozen'=>false,'block_len'=>BLOCK_LEN_DEFAULT] as $k=>$v) if(!isset($e[$k]))$e[$k]=$v;
$e['access']=normAccess($e['access']??'inherit'); $e['block_len']=normBlockLen($e['block_len']);
if(!empty($e['electric'])&&!isset($e['electric_legacy']))$e['electric_legacy']=true; if(!isset($e['electric_legacy']))$e['electric_legacy']=false;
if(!empty($e['double'])&&!isset($e['double_legacy']))$e['double_legacy']=true; if(!isset($e['double_legacy']))$e['double_legacy']=false;
if(!isset($e['bridges'])||!is_array($e['bridges']))$e['bridges']=[];
foreach($e['bridges'] as &$br){ $kind=($br['kind']??'river')==='rail'?'rail':'river'; $br['kind']=$kind; $br['type']=normalizeBridgeType($kind,(string)($br['type']??BRIDGE_DEFAULT_TYPE));
if(!isset($br['id']))$br['id']=substr(md5($kind.'|'.($br['lat']??0).'|'.($br['lng']??0)),0,10);
if(!isset($br['name']))$br['name']=$kind==='rail'?'Путепровод':'Река';
if(!isset($br['span']))$br['span']=$kind==='rail'?OVERPASS_SPAN_M:BRIDGE_MIN_SPAN_M;
if(!isset($br['bearing']))$br['bearing']=0; $br['lat']=(float)$br['lat']; $br['lng']=(float)$br['lng']; } unset($br);
} unset($e);
$before=count($d['edges']);
$d['edges']=array_values(array_filter($d['edges'],function($e) use ($d): bool { return endpointById($d,(string)($e['from']??''))!==null && endpointById($d,(string)($e['to']??''))!==null; }));
if(count($d['edges'])!==$before) pruneOrphanNodes($d);
foreach($d['edges'] as &$e2){ $e2['bridges']=array_values(array_filter($e2['bridges'],function($b){ $la=(float)($b['lat']??0); $ln=(float)($b['lng']??0); return is_finite($la)&&is_finite($ln)&&!($la==0.0&&$ln==0.0); })); } unset($e2);
foreach($d['routes'] as &$r){ if(!isset($r['stations'])||!is_array($r['stations']))$r['stations']=[]; if(!isset($r['edges'])||!is_array($r['edges']))$r['edges']=[]; foreach(['loop'=>false,'name'=>'Маршрут','created_day'=>1,'auto'=>false] as $k=>$v) if(!isset($r[$k]))$r[$k]=$v; } unset($r);
foreach($d['depots'] as &$x){ foreach(['level'=>1,'built_day'=>1,'is_building'=>false,'build_end'=>0,'upgrading'=>false,'upgrade_end'=>0,'upgrade_level'=>0,'frozen'=>false,'health'=>100] as $k=>$v) if(!isset($x[$k]))$x[$k]=$v;
if(!isset($x['capacity']))$x['capacity']=depotCapacityForLevel((int)$x['level']); if(!isset($x['labelOffset']))$x['labelOffset']=['x'=>10,'y'=>4];
$x['access']=normAccess($x['access']??'inherit'); $x['health']=max(0,min(100,(int)$x['health'])); $x['lat']=(float)$x['lat']; $x['lng']=(float)$x['lng']; } unset($x);
foreach($d['trains'] as &$t){ foreach(['depot_id'=>null,'route_id'=>null,'is_building'=>false,'build_end'=>0,'transit_end'=>0,'bought_day'=>1,'frozen'=>false,'name'=>'Поезд','health'=>100] as $k=>$v) if(!isset($t[$k]))$t[$k]=$v;
$t['model']=normTrainModel($t['model']??'diesel_line');
if(!isset($t['wagons'])||is_int($t['wagons'])){ $n=is_int($t['wagons']??null)?max(0,(int)$t['wagons']):0; $t['wagons']=array_fill(0,min(MAX_WAGONS_PER_TRAIN,$n),'passenger'); }
else{ $l=[]; foreach((array)$t['wagons'] as $w){ $nw=normWagonType(is_string($w)?$w:null); if($nw!==null)$l[]=$nw; if(count($l)>=MAX_WAGONS_PER_TRAIN)break; } $t['wagons']=$l; }
$t['health']=max(0,min(100,(int)$t['health'])); } unset($t);
foreach($d['substations'] as &$x){ foreach(['name'=>'Подстанция','built_day'=>1,'is_building'=>false,'build_end'=>0,'frozen'=>false,'health'=>100] as $k=>$v) if(!isset($x[$k]))$x[$k]=$v;
if(!isset($x['labelOffset']))$x['labelOffset']=['x'=>10,'y'=>4]; $x['access']=normAccess($x['access']??'inherit'); $x['health']=max(0,min(100,(int)$x['health'])); $x['lat']=(float)$x['lat']; $x['lng']=(float)$x['lng']; } unset($x);
foreach($d['track_maint'] as &$x){ foreach(['name'=>'Путевая часть','built_day'=>1,'is_building'=>false,'build_end'=>0,'frozen'=>false,'range_m'=>TRACK_MAINT_RANGE_M] as $k=>$v) if(!isset($x[$k]))$x[$k]=$v;
if(!isset($x['labelOffset']))$x['labelOffset']=['x'=>10,'y'=>4]; $x['access']=normAccess($x['access']??'inherit'); $x['lat']=(float)$x['lat']; $x['lng']=(float)$x['lng']; $x['range_m']=(int)$x['range_m']; } unset($x);
foreach($d['hqs'] as &$h){ if(!isset($h['labelOffset']))$h['labelOffset']=['x'=>12,'y'=>4]; $h['lat']=(float)$h['lat']; $h['lng']=(float)$h['lng']; $h['owner_id']=(int)($h['owner_id']??0); } unset($h);
foreach($d['presets'] as &$p){ $p['model']=normTrainModel($p['model']??'diesel_line'); $p['owner_id']=(int)($p['owner_id']??0); $p['name']=(string)($p['name']??'Пресет'); $w=[]; foreach(['passenger','baggage','mail'] as $k)$w[$k]=max(0,(int)($p['wagons'][$k]??0)); $p['wagons']=$w; } unset($p);
perfAdd('world',$__t); return ['data'=>$d,'version'=>(int)$row['version']]; }
function ensureHqs(array &$d): bool { if(!isset($d['hqs'])||!is_array($d['hqs']))$d['hqs']=[]; $have=[]; foreach($d['hqs'] as $h)$have[(int)($h['owner_id']??0)]=true; $ch=false;
foreach(db()->query("SELECT id,created_at FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $p){ $pid=(int)$p['id']; if(isset($have[$pid]))continue; $best=null; $bd=PHP_INT_MAX;
foreach($d['stations'] as $s){ if((int)($s['owner_id']??0)!==$pid)continue; $k=(int)($s['built_day']??1); if($k<$bd){$bd=$k;$best=$s;} }
$d['hqs'][]=['id'=>newId('h'),'owner_id'=>$pid,'lat'=>$best?(float)$best['lat']+0.0012:BASE_LAT,'lng'=>$best?(float)$best['lng']+0.0016:BASE_LNG,'labelOffset'=>['x'=>12,'y'=>4],'created_at'=>$p['created_at']]; $ch=true; }
return $ch; }
function companyMeta(): array { if(!empty($GLOBALS['__cm']))return $GLOBALS['__cm']; $o=[];
foreach(db()->query("SELECT id,name,company_name,founder,created_at FROM players")->fetchAll(PDO::FETCH_ASSOC) as $r) $o[(int)$r['id']]=['name'=>(string)$r['name'],'company_name'=>(string)($r['company_name']??''),'founder'=>(string)($r['founder']??''),'created_at'=>(string)($r['created_at']??'')];
$GLOBALS['__cm']=$o; return $o; }
function companyDisplayName(int $pid): string { $m=companyMeta()[$pid]??null; if(!$m)return 'игрок #'.$pid; return $m['company_name']!==''?$m['company_name']:$m['name']; }
function augmentPlayer(array $p): array { $m=companyMeta()[(int)$p['id']]??null; $p['company_name']=$m['company_name']??''; $p['founder']=$m['founder']??''; if(!isset($p['created_at']))$p['created_at']=$m['created_at']??''; $p['display_name']=($m['company_name']??'')!==''?$m['company_name']:($p['name']??''); return $p; }
function computeNextDue(array $d): int { $now=time(); $min=0; $c=function(int $t) use (&$min,$now){ if($t>$now&&($min===0||$t<$min))$min=$t; };
foreach(['stations','edges','depots','substations','track_maint'] as $k) foreach(($d[$k]??[]) as $o){ if(!empty($o['is_building']))$c((int)($o['build_end']??0)); if(!empty($o['upgrading']))$c((int)($o['upgrade_end']??0)); }
foreach(($d['trains']??[]) as $t){ if(!empty($t['is_building']))$c((int)($t['build_end']??0)); $te=(int)($t['transit_end']??0); if($te>$now)$c($te); }
return $min; }
function worldSave(array $d,bool $eco=true): int { cacheReset(); $p=db(); $p->prepare("UPDATE world SET data=:d, updated_at=:u, version=version+1 WHERE id=1")->execute([':d'=>json_encode($d,JSON_UNESCAPED_UNICODE),':u'=>date('c')]); metaSet('next_due',(string)computeNextDue($d)); if($eco) metaSet('eco_ver',(string)((int)metaGet('eco_ver')+1)); return (int)$p->query("SELECT version FROM world WHERE id=1")->fetchColumn(); }
function idxOf(array $a,string $id): ?int { foreach($a as $i=>$o) if((string)($o['id']??'')===$id)return (int)$i; return null; }
function idxStation(array $d,string $id):?int{ return idxOf($d['stations'],$id); } function idxNode(array $d,string $id):?int{ return idxOf($d['nodes'],$id); }
function idxEdge(array $d,string $id):?int{ return idxOf($d['edges'],$id); } function idxRoute(array $d,string $id):?int{ return idxOf($d['routes'],$id); }
function idxDepot(array $d,string $id):?int{ return idxOf($d['depots'],$id); } function idxTrain(array $d,string $id):?int{ return idxOf($d['trains'],$id); }
function idxSubstation(array $d,string $id):?int{ return idxOf($d['substations']??[],$id); } function idxTrackMaint(array $d,string $id):?int{ return idxOf($d['track_maint']??[],$id); }
function idxHq(array $d,string $id):?int{ return idxOf($d['hqs']??[],$id); } function idxPreset(array $d,string $id):?int{ return idxOf($d['presets']??[],$id); }
function stationById(array $d,string $id):?array{ $i=idxStation($d,$id); return $i===null?null:$d['stations'][$i]; }
function nodeById(array $d,string $id):?array{ $i=idxNode($d,$id); return $i===null?null:$d['nodes'][$i]; }
function edgeById(array $d,string $id):?array{ $i=idxEdge($d,$id); return $i===null?null:$d['edges'][$i]; }
function routeById(array $d,string $id):?array{ $i=idxRoute($d,$id); return $i===null?null:$d['routes'][$i]; }
function depotById(array $d,string $id):?array{ $i=idxDepot($d,$id); return $i===null?null:$d['depots'][$i]; }
function presetById(array $d,string $id):?array{ $i=idxPreset($d,$id); return $i===null?null:($d['presets'][$i]??null); }
function hqByOwner(array $d,int $pid):?array{ foreach(($d['hqs']??[]) as $h) if((int)($h['owner_id']??0)===$pid)return $h; return null; }
function endpointById(array $d,string $id):?array{ $s=stationById($d,$id); if($s)return $s+['_kind'=>'station']; $n=nodeById($d,$id); if($n)return $n+['_kind'=>'node']; return null; }
function endpointName(array $d,string $id):string{ $e=endpointById($d,$id); if(!$e)return '?'; return $e['_kind']==='station'?(string)$e['name']:('узел '.(string)$e['name']); }
function pruneOrphanNodes(array &$d): int { $u=[]; foreach($d['edges'] as $e){ $u[(string)$e['from']]=true; $u[(string)$e['to']]=true; } $b=count($d['nodes']); $d['nodes']=array_values(array_filter($d['nodes'],fn($n)=>isset($u[(string)$n['id']])||!empty($n['frozen']))); return $b-count($d['nodes']); }
function presetWagonList(array $p): array { $o=[]; foreach(['passenger','baggage','mail'] as $k){ $n=max(0,(int)($p['wagons'][$k]??0)); for($i=0;$i<$n;$i++)$o[]=$k; } return array_slice($o,0,MAX_WAGONS_PER_TRAIN); }
/* ---------- аккаунты ---------- */
function cookieSign(string $id):string{ return $id.'.'.substr(hash_hmac('sha256',$id,COOKIE_SECRET),0,16); }
function cookieVerify(?string $raw):?int{ if(!$raw)return null; $p=explode('.',$raw,2); if(count($p)!==2)return null; [$id,$sig]=$p; if(!ctype_digit($id))return null; if(!hash_equals(substr(hash_hmac('sha256',$id,COOKIE_SECRET),0,16),$sig))return null; return (int)$id; }
function currentPlayer():?array{ $id=cookieVerify($_COOKIE['pid']??null); if($id===null)return null; $s=db()->prepare("SELECT * FROM players WHERE id=:id"); $s->execute([':id'=>$id]); return $s->fetch(PDO::FETCH_ASSOC)?:null; }
function requirePlayer():array{ $p=currentPlayer(); if(!$p)sendJson(['error'=>'not logged in'],401); return $p; }
function playerName(int $pid):string{ $s=db()->prepare("SELECT name FROM players WHERE id=:id"); $s->execute([':id'=>$pid]); $n=$s->fetchColumn(); return $n!==false?(string)$n:('игрок #'.$pid); }
function colorsAll():array{ if(!empty($GLOBALS['__col']))return $GLOBALS['__col']; $o=[]; foreach(db()->query("SELECT id,color FROM players")->fetchAll(PDO::FETCH_ASSOC) as $r){ $c=(string)$r['color']; if($c==='')$c=COMPANY_COLORS[((int)$r['id']-1)%count(COMPANY_COLORS)]; $o[(int)$r['id']]=$c; } $GLOBALS['__col']=$o; return $o; }
function playerColor(int $pid):string{ $c=colorsAll(); return $c[$pid]??COMPANY_COLORS[max(0,$pid-1)%count(COMPANY_COLORS)]; }
function freeCompanyColor():string{ $u=[]; foreach(colorsAll() as $c)$u[strtolower($c)]=true; foreach(COMPANY_COLORS as $c) if(!isset($u[strtolower($c)]))return $c; return COMPANY_COLORS[0]; }
function policiesAll():array{ if(!empty($GLOBALS['__pol']))return $GLOBALS['__pol']; $o=[]; foreach(db()->query("SELECT * FROM company_policies")->fetchAll(PDO::FETCH_ASSOC) as $r)$o[(int)$r['player_id']]=['allow_trackage'=>(int)$r['allow_trackage'],'allow_stops'=>(int)$r['allow_stops'],'allow_connect'=>(int)$r['allow_connect']]; $GLOBALS['__pol']=$o; return $o; }
function policyFor(array $pol,int $pid):array{ return $pol[$pid]??['allow_trackage'=>0,'allow_stops'=>0,'allow_connect'=>0]; }
function policySave(int $pid,array $p):void{ db()->prepare("INSERT OR REPLACE INTO company_policies (player_id,allow_trackage,allow_stops,allow_connect,updated_at) VALUES (:p,:t,:s,:c,:u)")->execute([':p'=>$pid,':t'=>!empty($p['allow_trackage'])?1:0,':s'=>!empty($p['allow_stops'])?1:0,':c'=>!empty($p['allow_connect'])?1:0,':u'=>date('c')]); cacheReset(); }
function companiesPublic(array $data):array{ $pol=policiesAll(); $cm=companyMeta(); $o=[];
foreach(db()->query("SELECT id,name,created_at FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $r){ $pid=(int)$r['id']; $m=$cm[$pid]??['name'=>$r['name'],'company_name'=>'','founder'=>'','created_at'=>$r['created_at']];
$o[]=['id'=>$pid,'name'=>$r['name'],'color'=>playerColor($pid),'policies'=>policyFor($pol,$pid),'company_name'=>$m['company_name'],'display_name'=>companyDisplayName($pid),'founder'=>$m['founder'],'created_at'=>$m['created_at'],
'stations'=>playerStationsCount($data,$pid),'nodes'=>playerNodesCount($data,$pid),'edges'=>playerEdgesCount($data,$pid),'routes'=>playerRoutesCount($data,$pid),'depots'=>playerDepotsCount($data,$pid),'trains'=>playerTrainsCount($data,$pid),'substations'=>playerSubstationsCount($data,$pid),'track_maint'=>playerTrackMaintCount($data,$pid)]; }
return $o; }
function logEvent(string $k,?int $pid,string $t,?array $m=null):void{ db()->prepare("INSERT INTO events (kind,player_id,text,meta,created_at) VALUES (:k,:p,:t,:m,:d)")->execute([':k'=>$k,':p'=>$pid,':t'=>$t,':m'=>$m?json_encode($m,JSON_UNESCAPED_UNICODE):null,':d'=>date('c')]); }
function cleanupEvents():void{ db()->prepare("DELETE FROM events WHERE created_at < :c")->execute([':c'=>date('c',time()-EVENTS_KEEP_DAYS*86400)]); }
function addMoney(int $pid,int $amt,string $kind,?int $from=null,?array $meta=null):void{
if($amt<0){ $s=db()->prepare("UPDATE players SET money=money+:a WHERE id=:id AND money+:a>=0"); $s->execute([':a'=>$amt,':id'=>$pid]); if($s->rowCount()===0)throw new RuntimeException('insufficient funds'); }
else db()->prepare("UPDATE players SET money=money+:a WHERE id=:id")->execute([':a'=>$amt,':id'=>$pid]);
db()->prepare("INSERT INTO transactions (from_player,to_player,amount,kind,meta,created_at) VALUES (:f,:t,:a,:k,:m,:d)")->execute([':f'=>$from,':t'=>$pid,':a'=>$amt,':k'=>$kind,':m'=>$meta?json_encode($meta,JSON_UNESCAPED_UNICODE):null,':d'=>date('c')]); }
function chargePlayer(int $pid,int $cost,string $kind,?int $from=null,?array $meta=null,string $err='недостаточно денег'):void{ $s=db()->prepare("SELECT money FROM players WHERE id=:id"); $s->execute([':id'=>$pid]); if((int)$s->fetchColumn()<$cost)fail($err,400); addMoney($pid,-$cost,$kind,$from,$meta); }
function accessAllowed(array $pol,array $obj,int $consumer,string $mode):bool{ $ow=(int)($obj['owner_id']??0); if($ow===$consumer||$ow<=0)return true; $a=normAccess($obj['access']??'inherit'); if($a==='open')return true; if($a==='closed')return false; $p=policyFor($pol,$ow); $key=$mode==='stop'?'allow_stops':($mode==='connect'?'allow_connect':'allow_trackage'); return !empty($p[$key]); }
function accessState(array $pol,array $obj,int $consumer,string $mode):string{ $ow=(int)($obj['owner_id']??0); if($ow===$consumer)return 'own'; $a=normAccess($obj['access']??'inherit'); if($a==='open')return 'open'; if($a==='closed')return 'closed'; return accessAllowed($pol,$obj,$consumer,$mode)?'policy':'denied'; }
/* ---------- геометрия ---------- */
function distPointToSegmentLatLng(float $plat,float $plng,array $a,array $b):float{ $lat0=($a[0]+$b[0])/2*M_PI/180; $kx=111320*cos($lat0); $ky=111320; $px=$plng*$kx; $py=$plat*$ky; $ax=$a[1]*$kx; $ay=$a[0]*$ky; $bx=$b[1]*$kx; $by=$b[0]*$ky; $dx=$bx-$ax; $dy=$by-$ay; $l2=$dx*$dx+$dy*$dy; $t=$l2>0?max(0,min(1,(($px-$ax)*$dx+($py-$ay)*$dy)/$l2)):0; return hypot($px-($ax+$t*$dx),$py-($ay+$t*$dy)); }
function segIntersectionLatLng(array $p1,array $p2,array $p3,array $p4):?array{ $lat0=deg2rad(($p1[0]+$p2[0]+$p3[0]+$p4[0])/4); $kx=111320*cos($lat0); $ky=111320;
$x1=$p1[1]*$kx;$y1=$p1[0]*$ky;$x2=$p2[1]*$kx;$y2=$p2[0]*$ky;$x3=$p3[1]*$kx;$y3=$p3[0]*$ky;$x4=$p4[1]*$kx;$y4=$p4[0]*$ky;
$rx=$x2-$x1;$ry=$y2-$y1;$sx=$x4-$x3;$sy=$y4-$y3;$den=$rx*$sy-$ry*$sx; if(abs($den)<1e-9)return null;
$t=(($x3-$x1)*$sy-($y3-$y1)*$sx)/$den; $u=(($x3-$x1)*$ry-($y3-$y1)*$rx)/$den; if($t<0||$t>1||$u<0||$u>1)return null;
return ['lat'=>$p1[0]+$t*($p2[0]-$p1[0]),'lng'=>$p1[1]+$t*($p2[1]-$p1[1]),'t'=>$t]; }
function segmentsIntersect($a,$b,$c,$d):bool{ return segIntersectionLatLng($a,$b,$c,$d)!==null; }
function segmentBearingDeg(array $p1,array $p2):float{ $l=deg2rad(($p1[0]+$p2[0])/2); return rad2deg(atan2(($p2[1]-$p1[1])*cos($l),$p2[0]-$p1[0])); }
function acuteAngleDeg(array $a1,array $a2,array $b1,array $b2):float{ $kx=cos(deg2rad(($a1[0]+$a2[0]+$b1[0]+$b2[0])/4)); $ax=($a2[1]-$a1[1])*$kx;$ay=$a2[0]-$a1[0];$bx=($b2[1]-$b1[1])*$kx;$by=$b2[0]-$b1[0]; $na=hypot($ax,$ay);$nb=hypot($bx,$by); if($na<1e-12||$nb<1e-12)return 0.0; return rad2deg(acos(max(-1.0,min(1.0,abs($ax*$bx+$ay*$by)/($na*$nb))))); }
function trackBuild(array $a,array $b,array $points):array{ $pts=[[(float)$a['lat'],(float)$a['lng']]]; foreach($points as $p)$pts[]=[(float)$p['lat'],(float)$p['lng']]; $pts[]=[(float)$b['lat'],(float)$b['lng']]; $cum=[0.0]; $tot=0.0; for($i=0;$i<count($pts)-1;$i++){ $tot+=haversineDistanceMeters($pts[$i][0],$pts[$i][1],$pts[$i+1][0],$pts[$i+1][1]); $cum[]=$tot; } return ['pts'=>$pts,'cum'=>$cum,'total'=>$tot]; }
function trackPointAt(array $tr,float $off):array{ $off=max(0.0,min($tr['total'],$off)); $n=count($tr['pts']); for($i=0;$i<$n-1;$i++){ $s=$tr['cum'][$i]; $e=$tr['cum'][$i+1]; if($off<=$e||$i===$n-2){ $t=($e-$s)>0?($off-$s)/($e-$s):0.0; $t=max(0.0,min(1.0,$t)); return [$tr['pts'][$i][0]+$t*($tr['pts'][$i+1][0]-$tr['pts'][$i][0]),$tr['pts'][$i][1]+$t*($tr['pts'][$i+1][1]-$tr['pts'][$i][1])]; } } return $tr['pts'][$n-1]; }
function projectOnPolyline(array $pts,float $lat,float $lng):array{ $kx=111320*cos(deg2rad($lat)); $ky=111320; $px=$lng*$kx; $py=$lat*$ky; $best=['off'=>INF,'dist'=>0.0,'lat'=>$pts[0][0],'lng'=>$pts[0][1],'seg'=>0,'t'=>0.0]; $acc=0.0;
for($i=0;$i<count($pts)-1;$i++){ $A=$pts[$i];$B=$pts[$i+1]; $ax=$A[1]*$kx;$ay=$A[0]*$ky;$bx=$B[1]*$kx;$by=$B[0]*$ky; $dx=$bx-$ax;$dy=$by-$ay;$l2=$dx*$dx+$dy*$dy;$len=sqrt($l2); $t=$l2>0?max(0,min(1,(($px-$ax)*$dx+($py-$ay)*$dy)/$l2)):0; $dd=hypot($px-($ax+$t*$dx),$py-($ay+$t*$dy)); if($dd<$best['off'])$best=['off'=>$dd,'dist'=>$acc+$t*$len,'lat'=>$A[0]+$t*($B[0]-$A[0]),'lng'=>$A[1]+$t*($B[1]-$A[1]),'seg'=>$i,'t'=>$t]; $acc+=$len; }
$best['total']=$acc; return $best; }
function minDistPointToPolyline(float $lat,float $lng,array $pts):float{ $d=INF; for($i=0;$i<count($pts)-1;$i++){ $x=distPointToSegmentLatLng($lat,$lng,$pts[$i],$pts[$i+1]); if($x<$d)$d=$x; } return $d; }
function edgePolyline(array $d,array $e):array{ $a=endpointById($d,(string)($e['from']??'')); $b=endpointById($d,(string)($e['to']??'')); if(!$a||!$b)return []; $pts=[[(float)$a['lat'],(float)$a['lng']]]; foreach(($e['points']??[]) as $p)$pts[]=[(float)$p['lat'],(float)$p['lng']]; $pts[]=[(float)$b['lat'],(float)$b['lng']]; return $pts; }
function edgePolylineIdx(array $idx,array $e):array{ $a=$idx['ep'][(string)($e['from']??'')]??null; $b=$idx['ep'][(string)($e['to']??'')]??null; if(!$a||!$b)return []; $pts=[[(float)$a['lat'],(float)$a['lng']]]; foreach(($e['points']??[]) as $p)$pts[]=[(float)$p['lat'],(float)$p['lng']]; $pts[]=[(float)$b['lat'],(float)$b['lng']]; return $pts; }
function bboxOf(array $pts):array{ return ['minLat'=>min(array_column($pts,0)),'maxLat'=>max(array_column($pts,0)),'minLng'=>min(array_column($pts,1)),'maxLng'=>max(array_column($pts,1))]; }
function bboxOverlap(array $a,array $b,float $pad=0.0):bool{ return !($a['maxLat']+$pad<$b['minLat']||$a['minLat']-$pad>$b['maxLat']||$a['maxLng']+$pad<$b['minLng']||$a['minLng']-$pad>$b['maxLng']); }
function clusterizeCrossings(array $items,float $r=100):array{ $u=[]; foreach($items as $it){ $near=false; foreach($u as $x) if(haversineDistanceMeters($it['lat'],$it['lng'],$x['lat'],$x['lng'])<=$r){$near=true;break;} if(!$near)$u[]=$it; } return $u; }
function parsePoints($raw):array{ if($raw===null||$raw===[])return []; if(!is_array($raw)||count($raw)>MAX_EDGE_POINTS)fail('bad points',400); $o=[]; foreach($raw as $p){ if(!is_array($p))fail('bad points',400); $la=(float)($p['lat']??0); $ln=(float)($p['lng']??0); if(!is_finite($la)||!is_finite($ln))fail('bad points',400); $o[]=['lat'=>$la,'lng'=>$ln]; } return $o; }
function parsePoint($raw):?array{ if(!is_array($raw))return null; $la=(float)($raw['lat']??0); $ln=(float)($raw['lng']??0); if(!is_finite($la)||!is_finite($ln)||($la==0&&$ln==0))return null; return ['lat'=>$la,'lng'=>$ln]; }
function pathLength(array $a,array $b,array $points):float{ return max(1.0,round(trackBuild($a,$b,$points)['total'],2)); }
/* ---------- OSM ---------- */
function getOsmDb():?PDO{ static $pdo=null; static $tried=false; if($tried)return $pdo; $tried=true; if(!file_exists(OSM_DB_FILE))return null; try{ $pdo=new PDO('sqlite:'.OSM_DB_FILE); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); }catch(Throwable $e){ $pdo=null; } return $pdo; }
function osmBBoxRows(PDO $p,string $table,array $par,bool $all=false):array{
static $meta=[];
if(!isset($meta[$table])){
$cols=[]; try{ foreach($p->query("PRAGMA table_info({$table})") as $c)$cols[strtolower($c['name'])]=true; }catch(Throwable $e){}
$hasCoords=isset($cols['coords']); $hasId=isset($cols['id']);
$rt=false;
if($hasCoords&&$hasId){ try{
$ex=(int)$p->query("SELECT COUNT(*) FROM sqlite_master WHERE name='{$table}_rtree'")->fetchColumn();
if($ex>0){ $mx=$p->query("SELECT MAX(id) FROM {$table}")->fetchColumn();
if($mx===null){ $rt=true; }
else{ $st2=$p->prepare("SELECT COUNT(*) FROM {$table}_rtree WHERE id=:mx"); $st2->execute([':mx'=>$mx]); $rt=((int)$st2->fetchColumn()>0); } }
}catch(Throwable $e){ $rt=false; } }
$meta[$table]=['coords'=>$hasCoords,'id'=>$hasId,'rt'=>$rt];
}
$m=$meta[$table];
if(!$m['coords']) return [];
$key=$m['id']?'id':'rowid';
$sel=$all?'b.*':'b.coords AS coords';
if($m['rt']){
try{ $st=$p->prepare("SELECT {$sel}, b.{$key} AS rid FROM {$table}_rtree r JOIN {$table} b ON b.{$key}=r.id WHERE r.minLat<=:a+0.0002 AND r.maxLat>=:b-0.0002 AND r.minLng<=:c+0.0002 AND r.maxLng>=:d-0.0002");
$st->execute($par); return $st->fetchAll(PDO::FETCH_ASSOC); }catch(Throwable $e){ $meta[$table]['rt']=false; }
}
$sel2=$all?'*':'coords';
$st=$p->prepare("SELECT {$sel2}, {$key} AS rid FROM {$table} WHERE minLat<=:a AND maxLat>=:b AND minLng<=:c AND maxLng>=:d");
$st->execute($par); return $st->fetchAll(PDO::FETCH_ASSOC);
}
function parseWkb($w){ if(!$w)return null; $b=unpack('Corder/Ltype',substr($w,0,5)); $g=$b['type']%1000;
if($g===1){ $c=unpack('dlat/dlon',substr($w,5,16)); return ['lat'=>$c['lat'],'lng'=>$c['lon']]; }
if($g===2){ $n=unpack('L',substr($w,5,4))[1]; $o=[]; for($i=0;$i<$n;$i++){ $p=unpack('dlat/dlon',substr($w,9+$i*16,16)); $o[]=[$p['lon'],$p['lat']]; } return $o; }
if($g===3){ $n=unpack('L',substr($w,5,4))[1]; $rings=[]; $off=9; for($i=0;$i<$n;$i++){ $np=unpack('L',substr($w,$off,4))[1]; $off+=4; $ring=[]; for($j=0;$j<$np;$j++){ $p=unpack('dlat/dlon',substr($w,$off,16)); $ring[]=[$p['lon'],$p['lat']]; $off+=16; } $rings[]=$ring; } return $rings; }
return null; }
function osmNameFromRow(array $r,string $fb):string{ foreach(['name','name_ru','NAME','title'] as $k) if(!empty($r[$k])&&is_string($r[$k]))return trim($r[$k]);
foreach(['other_tags','tags','osm_tags','attrs'] as $k){ if(empty($r[$k])||!is_string($r[$k]))continue; if(preg_match('/"name"\s*=>\s*"([^"]+)"/u',$r[$k],$m))return trim($m[1]); $j=json_decode($r[$k],true); if(is_array($j)&&!empty($j['name'])&&is_string($j['name']))return trim($j['name']); } return $fb; }
function ensureRailStations():void{
if(metaGet('rail_st_done')==='1')return;
if((int)metaGet('rail_st_busy')>time())return;
metaSet('rail_st_busy',(string)(time()+900));
$__t=microtime(true);
$p=getOsmDb();
if(!$p){ metaSet('rail_st_done','1'); metaSet('rail_st_busy','0'); return; }
$pairs=[]; $ok=false;
try{
$st=$p->query("SELECT GEOMETRY FROM points WHERE other_tags LIKE '%\"railway\"=>\"station\"%' OR other_tags LIKE '%\"railway\"=>\"halt\"%'");
while($r=$st->fetch(PDO::FETCH_ASSOC)){
$pt=parseWkb($r['GEOMETRY']??null); if(!$pt||!isset($pt['lat']))continue;
$pairs[]=[(float)$pt['lat'],(float)$pt['lng']];
$pairs[]=[(float)$pt['lng'],(float)$pt['lat']]; }
$st->closeCursor(); $ok=true;
}catch(Throwable $e){ $ok=false; }
if($ok){
$g=db();
try{
$g->exec('BEGIN IMMEDIATE');
$g->exec('DELETE FROM fast_rail_stations');
$ins=$g->prepare("INSERT INTO fast_rail_stations (lat,lng) VALUES (:a,:b)");
foreach($pairs as $pr)$ins->execute([':a'=>$pr[0],':b'=>$pr[1]]);
$g->exec('COMMIT');
metaSet('rail_st_done','1'); metaSet('rail_st_n',(string)count($pairs));
}catch(Throwable $e){ try{$g->exec('ROLLBACK');}catch(Throwable $_){} }
}
metaSet('rail_st_busy','0');
perfAdd('osm_st_idx',$__t);
}
function getOsmStationDiscount(float $lat,float $lng):bool{
$__t=microtime(true); $res=false;
$p=getOsmDb();
if($p){
ensureRailStations();
$dLa=OSM_STATION_RADIUS/111320.0; $dLo=OSM_STATION_RADIUS/(111320.0*max(0.01,cos(deg2rad($lat))));
$st=db()->prepare("SELECT lat,lng FROM fast_rail_stations WHERE lat BETWEEN :a AND :b AND lng BETWEEN :c AND :d");
$st->execute([':a'=>$lat-$dLa,':b'=>$lat+$dLa,':c'=>$lng-$dLo,':d'=>$lng+$dLo]);
foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r) if(haversineDistanceMeters($lat,$lng,(float)$r['lat'],(float)$r['lng'])<=OSM_STATION_RADIUS){$res=true;break;}
}
perfAdd('osm_st',$__t); return $res; }
function countNearbyBuildings(float $lat,float $lng,float $rad=500.0):int{
$p=getOsmDb(); if(!$p)return 0; $__t=microtime(true);
$dLa=$rad/111320.0; $dLo=$rad/(111320.0*max(0.01,cos(deg2rad($lat))));
$par=[':a'=>$lat+$dLa,':b'=>$lat-$dLa,':c'=>$lng+$dLo,':d'=>$lng-$dLo];
$n=0; $kx=111320.0*cos(deg2rad($lat)); $ky=111320.0; $r2=$rad*$rad;
foreach(osmBBoxRows($p,'fast_buildings',$par) as $b){
$raw=json_decode((string)$b['coords'],true); if(!$raw||!is_array($raw))continue;
if(count($raw)===1){ $pl=(float)$raw[0][0]; $pa=(float)$raw[0][1]; }
else{ $las=array_column($raw,1); $los=array_column($raw,0); $pa=(min($las)+max($las))/2; $pl=(min($los)+max($los))/2; }
$dx=($pl-$lng)*$kx; $dy=($pa-$lat)*$ky; if($dx*$dx+$dy*$dy<=$r2)$n++; }
perfAdd('osm_bld',$__t); return $n; }
function getBuildingIncomePercent(int $b):int{ $t=[[0,0],[5,5],[10,10],[19,15],[29,20],[39,25],[45,30],[52,35],[75,40],[87,45],[99,50],[119,55],[139,60],[159,65],[179,70],[199,75],[219,80],[239,85],[259,90],[279,95]]; foreach($t as $x) if($b<=$x[0])return $x[1]; return 100; }
function getForbiddenZones():array{ static $z=null; if($z!==null)return $z; $z=[]; $f=__DIR__.'/zzoness.json'; if(!file_exists($f))return $z; $d=json_decode((string)file_get_contents($f),true); if(!is_array($d)||!isset($d['features']))return $z;
foreach($d['features'] as $ft){ $t=$ft['geometry']['type']??''; $nm=$ft['properties']['name']??'Запретная зона';
if($t==='Polygon'){ $c=[]; foreach($ft['geometry']['coordinates'][0] as $p)$c[]=[(float)$p[1],(float)$p[0]]; $z[]=['name'=>$nm,'coords'=>$c]; }
elseif($t==='MultiPolygon') foreach($ft['geometry']['coordinates'] as $poly){ $c=[]; foreach($poly[0] as $p)$c[]=[(float)$p[1],(float)$p[0]]; $z[]=['name'=>$nm,'coords'=>$c]; } }
return $z; }
function isPointInForbiddenZone(float $lat,float $lng):?array{ foreach(getForbiddenZones() as $zone){ $c=$zone['coords']; $n=count($c); $in=false;
for($i=0,$j=$n-1;$i<$n;$j=$i++){ $yi=$c[$i][0];$xi=$c[$i][1];$yj=$c[$j][0];$xj=$c[$j][1]; if((($yi>$lat)!=($yj>$lat))&&($lng<($xj-$xi)*($lat-$yi)/(($yj-$yi)?:1e-12)+$xi))$in=!$in; } if($in)return $zone; } return null; }
function doesEdgeCrossForbiddenZone(array $a,array $b,array $points):?array{ $zs=getForbiddenZones(); if(!$zs)return null; $pts=trackBuild($a,$b,$points)['pts'];
foreach($zs as $z){ $c=$z['coords']; $n=count($c); for($i=0;$i<count($pts)-1;$i++){ for($j=0;$j<$n;$j++) if(segmentsIntersect($pts[$i],$pts[$i+1],$c[$j],$c[($j+1)%$n]))return $z; if(isPointInForbiddenZone($pts[$i][0],$pts[$i][1]))return $z; } } return null; }
function edgeOsmCoverage(array $a,array $b,array $points):float{
$p=getOsmDb(); if(!$p)return 0.0; $__t=microtime(true);
$pts=trackBuild($a,$b,$points)['pts']; $bb=bboxOf($pts);
$par=[':a'=>$bb['maxLat'],':b'=>$bb['minLat'],':c'=>$bb['maxLng'],':d'=>$bb['minLng']];
$segs=[];
foreach(osmBBoxRows($p,'fast_rails',$par) as $r){ $raw=json_decode((string)$r['coords'],true); if(!$raw)continue;
for($j=0;$j<count($raw)-1;$j++){ $A=$raw[$j]; $B=$raw[$j+1];
$la1=(float)$A[1]; $lo1=(float)$A[0]; $la2=(float)$B[1]; $lo2=(float)$B[0];
$mLa=min($la1,$la2); $MLa=max($la1,$la2); $mLo=min($lo1,$lo2); $MLo=max($lo1,$lo2);
if($mLa>$bb['maxLat']||$MLa<$bb['minLat']||$mLo>$bb['maxLng']||$MLo<$bb['minLng'])continue;
$segs[]=[$la1,$lo1,$la2,$lo2,$mLa,$MLa,$mLo,$MLo]; } }
$tot=0.0; $cov=0.0; $padLa=OSM_EDGE_TOLERANCE_M/111320.0;
for($i=0;$i<count($pts)-1;$i++){ $p1=$pts[$i]; $p2=$pts[$i+1];
$len=haversineDistanceMeters($p1[0],$p1[1],$p2[0],$p2[1]); $tot+=$len;
$padLo=OSM_EDGE_TOLERANCE_M/(111320.0*max(0.01,cos(deg2rad(($p1[0]+$p2[0])/2))));
$la0=min($p1[0],$p2[0])-$padLa; $la1=max($p1[0],$p2[0])+$padLa;
$lo0=min($p1[1],$p2[1])-$padLo; $lo1=max($p1[1],$p2[1])+$padLo;
$hit=false;
foreach($segs as $s){ if($s[4]>$la1||$s[5]<$la0||$s[6]>$lo1||$s[7]<$lo0)continue;
$A=[$s[0],$s[1]]; $B=[$s[2],$s[3]];
$dd=min(distPointToSegmentLatLng($p1[0],$p1[1],$A,$B),distPointToSegmentLatLng($p2[0],$p2[1],$A,$B));
if($dd<=OSM_EDGE_TOLERANCE_M){$hit=true;break;} }
if($hit)$cov+=$len; }
perfAdd('osm_cov',$__t); return $tot>0?$cov/$tot:0.0; }
/* ---------- мосты ---------- */
function bridgeTypes():array{ return [
'wood'=>['label'=>'Деревянный','speed'=>30,'pct'=>0.30,'material'=>'wood','rail_only'=>false],
'wood_reinforced'=>['label'=>'Укреплённый деревянный','speed'=>45,'pct'=>0.60,'material'=>'wood','rail_only'=>false],
'concrete_beam'=>['label'=>'Бетонный балочный','speed'=>60,'pct'=>1.00,'material'=>'concrete','rail_only'=>false],
'concrete_arch'=>['label'=>'Бетонный арочный','speed'=>90,'pct'=>1.50,'material'=>'concrete','rail_only'=>false],
'steel_beam'=>['label'=>'Металлический балочный','speed'=>120,'pct'=>2.20,'material'=>'steel','rail_only'=>false],
'steel_arch'=>['label'=>'Металлический арочный','speed'=>150,'pct'=>3.20,'material'=>'steel','rail_only'=>false],
'steel_modern'=>['label'=>'Современный металлический','speed'=>180,'pct'=>4.00,'material'=>'steel','rail_only'=>false],
'level'=>['label'=>'Глухое пересечение','speed'=>40,'pct'=>0.25,'material'=>'level','rail_only'=>true]]; }
function bridgeCost(string $kind,string $t):int{ $a=bridgeTypes(); $x=$a[$t]??$a[BRIDGE_DEFAULT_TYPE]; return (int)ceil(($kind==='rail'?COST_OVERPASS_BASE:COST_BRIDGE)*$x['pct']); }
function bridgeSpeed(string $t):int{ $a=bridgeTypes(); return (int)($a[$t]['speed']??$a[BRIDGE_DEFAULT_TYPE]['speed']); }
function bridgeLabel(string $t):string{ $a=bridgeTypes(); return (string)($a[$t]['label']??$a[BRIDGE_DEFAULT_TYPE]['label']); }
function bridgeTypesPublic():array{ $o=[]; foreach(bridgeTypes() as $k=>$t)$o[]=['key'=>$k,'label'=>$t['label'],'speed'=>$t['speed'],'pct'=>$t['pct'],'material'=>$t['material'],'rail_only'=>$t['rail_only'],'cost_river'=>bridgeCost('river',$k),'cost_rail'=>bridgeCost('rail',$k)]; return $o; }
function normalizeBridgeType(string $kind,?string $t):string{ $all=bridgeTypes(); $t=(string)$t; if(!isset($all[$t]))return BRIDGE_DEFAULT_TYPE; if($kind!=='rail'&&!empty($all[$t]['rail_only']))return BRIDGE_DEFAULT_TYPE; return $t; }
function decorateBridge(array $br):array{ $k=($br['kind']??'river')==='rail'?'rail':'river'; $t=normalizeBridgeType($k,(string)($br['type']??BRIDGE_DEFAULT_TYPE)); $br['kind']=$k; $br['type']=$t; $br['type_label']=bridgeLabel($t); $br['speed']=bridgeSpeed($t); $br['cost']=bridgeCost($k,$t); return $br; }
function computeRiverCrossings(array $a,array $b,array $points):array{
$p=getOsmDb(); if(!$p)return []; $__t=microtime(true);
$tr=trackBuild($a,$b,$points); if($tr['total']<1)return [];
$bb=bboxOf($tr['pts']); $par=[':a'=>$bb['maxLat'],':b'=>$bb['minLat'],':c'=>$bb['maxLng'],':d'=>$bb['minLng']];
$T=[]; for($i=0;$i<count($tr['pts'])-1;$i++){ $A=$tr['pts'][$i]; $B=$tr['pts'][$i+1];
$T[]=[$A,$B,min($A[0],$B[0]),max($A[0],$B[0]),min($A[1],$B[1]),max($A[1],$B[1]),$tr['cum'][$i],$tr['cum'][$i+1]-$tr['cum'][$i]]; }
$hits=[];
foreach(osmBBoxRows($p,'fast_rivers',$par,true) as $river){
$raw=json_decode((string)($river['coords']??''),true); if(!$raw||count($raw)<2)continue;
$rc=array_map(fn($c)=>[(float)$c[1],(float)$c[0]],$raw);
$S=[]; for($j=0;$j<count($rc)-1;$j++){ $A=$rc[$j]; $B=$rc[$j+1];
$mLa=min($A[0],$B[0]); $MLa=max($A[0],$B[0]); $mLo=min($A[1],$B[1]); $MLo=max($A[1],$B[1]);
if($mLa>$bb['maxLat']||$MLa<$bb['minLat']||$mLo>$bb['maxLng']||$MLo<$bb['minLng'])continue;
$S[]=[$A,$B,$mLa,$MLa,$mLo,$MLo]; }
if(!$S)continue;
$len=0.0; for($k=0;$k<count($rc)-1;$k++)$len+=haversineDistanceMeters($rc[$k][0],$rc[$k][1],$rc[$k+1][0],$rc[$k+1][1]);
if($len<=BRIDGE_RIVER_MIN_LENGTH_M)continue;
$name=osmNameFromRow($river,'Река без названия');
$dTo=function(float $la,float $ln) use ($rc):float{ $dd=INF; for($j=0;$j<count($rc)-1;$j++){ $x=distPointToSegmentLatLng($la,$ln,$rc[$j],$rc[$j+1]); if($x<$dd)$dd=$x; } return $dd; };
foreach($T as $t){ $p1=$t[0]; $p2=$t[1];
foreach($S as $s){ if($s[2]>$t[3]||$s[3]<$t[2]||$s[4]>$t[5]||$s[5]<$t[4])continue;
$ix=segIntersectionLatLng($p1,$p2,$s[0],$s[1]); if(!$ix)continue;
$off=$t[6]+$ix['t']*$t[7]; $bear=segmentBearingDeg($p1,$p2);
$dAt=function(float $o) use ($tr,$dTo):float{ [$la,$ln]=trackPointAt($tr,$o); return $dTo($la,$ln); };
$maxHalf=BRIDGE_MAX_SPAN_M/2; $step=BRIDGE_SAMPLE_STEP_M;
$f=0.0; while($off+$f<$tr['total']&&$f<$maxHalf){ $f+=$step; if($dAt($off+$f)>BRIDGE_WATER_HALF_WIDTH_M){ $f-=$step; break; } } $f=max(0.0,min($f,$tr['total']-$off,$maxHalf));
$bw=0.0; while($off-$bw>0&&$bw<$maxHalf){ $bw+=$step; if($dAt($off-$bw)>BRIDGE_WATER_HALF_WIDTH_M){ $bw-=$step; break; } } $bw=max(0.0,min($bw,$off,$maxHalf));
$span=max(BRIDGE_MIN_SPAN_M,min(BRIDGE_MAX_SPAN_M,$f+$bw)); [$clat,$clng]=trackPointAt($tr,max(0.0,min($tr['total'],$off+($f-$bw)/2)));
$hits[]=decorateBridge(['id'=>substr(md5('river|'.round($clat,5).'|'.round($clng,5)),0,10),'kind'=>'river','name'=>$name,'ref'=>null,'lat'=>round($clat,7),'lng'=>round($clng,7),'bearing'=>round($bear,1),'span'=>(int)round($span),'type'=>BRIDGE_DEFAULT_TYPE]); } } }
perfAdd('osm_riv',$__t);
return array_slice(clusterizeCrossings($hits,BRIDGE_CLUSTER_RADIUS_M),0,50); }
function computeRailCrossings(array $data,array $a,array $b,array $points,?string $excl=null):array{ $tr=trackBuild($a,$b,$points); if($tr['total']<2)return ['items'=>[],'blocked'=>[]]; $bb=bboxOf($tr['pts']);
$T=[]; for($i=0;$i<count($tr['pts'])-1;$i++){ $A=$tr['pts'][$i]; $B=$tr['pts'][$i+1]; $T[]=[$A,$B,min($A[0],$B[0]),max($A[0],$B[0]),min($A[1],$B[1]),max($A[1],$B[1])]; }
$verts=[]; foreach($data['stations'] as $s)$verts[]=[(float)$s['lat'],(float)$s['lng']]; foreach($data['nodes'] as $n)$verts[]=[(float)$n['lat'],(float)$n['lng']];
$near=function(float $la,float $ln,float $r) use ($verts):bool{ foreach($verts as $v) if(haversineDistanceMeters($la,$ln,$v[0],$v[1])<=$r)return true; return false; };
$items=[]; $blocked=[]; $aid=(string)($a['id']??''); $bid=(string)($b['id']??'');
foreach($data['edges'] as $e){ $eid=(string)($e['id']??''); if($excl!==null&&$eid===$excl)continue; $pl=edgePolyline($data,$e); if(count($pl)<2)continue; if(!bboxOverlap($bb,bboxOf($pl),0.001))continue;
$otherName=endpointName($data,(string)$e['from']).' ↔ '.endpointName($data,(string)$e['to']); $shared=array_intersect([$aid,$bid],[(string)$e['from'],(string)$e['to']]); $found=false;
foreach($T as $t){ if($found)break; $p1=$t[0]; $p2=$t[1];
for($j=0;$j<count($pl)-1;$j++){ $q1=$pl[$j]; $q2=$pl[$j+1];
if(min($q1[0],$q2[0])>$t[3]||max($q1[0],$q2[0])<$t[2]||min($q1[1],$q2[1])>$t[5]||max($q1[1],$q2[1])<$t[4])continue;
$ix=segIntersectionLatLng($p1,$p2,$q1,$q2); if(!$ix)continue; $la=(float)$ix['lat']; $ln=(float)$ix['lng'];
if($near($la,$ln,RAIL_CROSS_STATION_SKIP_M))continue; $skip=false;
foreach($shared as $sid){ if($sid==='')continue; $v=endpointById($data,(string)$sid); if($v&&haversineDistanceMeters($la,$ln,(float)$v['lat'],(float)$v['lng'])<=RAIL_CROSS_SHARED_SKIP_M){$skip=true;break;} } if($skip)continue;
$ang=acuteAngleDeg($p1,$p2,$q1,$q2);
if($ang<=RAIL_CROSS_MIN_ANGLE_DEG){ $blocked[]=['kind'=>'rail','lat'=>round($la,7),'lng'=>round($ln,7),'angle'=>round($ang,1),'ref'=>$eid,'name'=>$otherName,'reason'=>'слишком острый угол пересечения ('.round($ang,1).'° ≤ '.RAIL_CROSS_MIN_ANGLE_DEG.'°) — сделайте съезд у станции']; $found=true; break; }
$items[]=decorateBridge(['id'=>substr(md5('rail|'.$eid.'|'.round($la,5).'|'.round($ln,5)),0,10),'kind'=>'rail','name'=>'Над '.$otherName,'ref'=>$eid,'lat'=>round($la,7),'lng'=>round($ln,7),'bearing'=>round(segmentBearingDeg($p1,$p2),1),'span'=>(int)OVERPASS_SPAN_M,'angle'=>round($ang,1),'type'=>BRIDGE_DEFAULT_TYPE]); $found=true; break; } } }
for($i=0;$i<count($tr['pts'])-1;$i++) for($j=$i+2;$j<count($tr['pts'])-1;$j++){ $ix=segIntersectionLatLng($tr['pts'][$i],$tr['pts'][$i+1],$tr['pts'][$j],$tr['pts'][$j+1]); if(!$ix)continue; $la=(float)$ix['lat']; $ln=(float)$ix['lng']; if($near($la,$ln,RAIL_CROSS_STATION_SKIP_M))continue;
$ang=acuteAngleDeg($tr['pts'][$i],$tr['pts'][$i+1],$tr['pts'][$j],$tr['pts'][$j+1]);
if($ang<=RAIL_CROSS_MIN_ANGLE_DEG){ $blocked[]=['kind'=>'rail','lat'=>round($la,7),'lng'=>round($ln,7),'angle'=>round($ang,1),'ref'=>'self','name'=>'самопересечение','reason'=>'трасса пересекает себя под острым углом']; continue; }
$items[]=decorateBridge(['id'=>substr(md5('self|'.round($la,5).'|'.round($ln,5)),0,10),'kind'=>'rail','name'=>'Над собственной трассой','ref'=>'self','lat'=>round($la,7),'lng'=>round($ln,7),'bearing'=>round(segmentBearingDeg($tr['pts'][$i],$tr['pts'][$i+1]),1),'span'=>(int)OVERPASS_SPAN_M,'angle'=>round($ang,1),'type'=>BRIDGE_DEFAULT_TYPE]); }
return ['items'=>array_slice(clusterizeCrossings($items,BRIDGE_CLUSTER_RADIUS_M),0,50),'blocked'=>$blocked]; }
function computeEdgeCrossings(array $data,array $a,array $b,array $points,?string $excl=null):array{ $rail=computeRailCrossings($data,$a,$b,$points,$excl); return ['bridges'=>computeRiverCrossings($a,$b,$points),'overpasses'=>$rail['items'],'blocked'=>$rail['blocked']]; }
function applyBridgeSelection(array $items,array $sel):array{ if(!$sel)return $items; $out=[];
foreach($items as $it){ $type=(string)$it['type']; $best=null; $bd=INF;
foreach($sel as $s){ if(!is_array($s))continue; $k=(string)($s['kind']??''); if($k!==''&&$k!==$it['kind'])continue; if(!isset($s['lat'],$s['lng']))continue; $dd=haversineDistanceMeters((float)$it['lat'],(float)$it['lng'],(float)$s['lat'],(float)$s['lng']); if($dd<$bd){$bd=$dd;$best=$s;} }
if($best!==null&&$bd<=BRIDGE_MATCH_RADIUS_M)$type=normalizeBridgeType((string)$it['kind'],(string)($best['type']??$type)); $it['type']=$type; $out[]=decorateBridge($it); } return $out; }
function crossingsCost(array $items):int{ $s=0; foreach($items as $i)$s+=bridgeCost((string)$i['kind'],(string)$i['type']); return $s; }
function checkOsmObstacles(array $a,array $b,array $points):array{
$p=getOsmDb(); $empty=['culverts'=>0,'dams'=>0,'buildings'=>0,'obstacles'=>[]]; if(!$p)return $empty;
$__t=microtime(true);
$pts=trackBuild($a,$b,$points)['pts']; $nT=count($pts)-1; if($nT<1)return $empty;
$bb=bboxOf($pts); $par=[':a'=>$bb['maxLat'],':b'=>$bb['minLat'],':c'=>$bb['maxLng'],':d'=>$bb['minLng']];
$T=[];
for($i=0;$i<$nT;$i++){ $A=$pts[$i]; $B=$pts[$i+1];
$la1=(float)$A[0]; $lo1=(float)$A[1]; $la2=(float)$B[0]; $lo2=(float)$B[1];
$T[]=[$la1,$lo1,$la2,$lo2,min($la1,$la2),max($la1,$la2),min($lo1,$lo2),max($lo1,$lo2)]; }
$mkSegs=function(array $ll,bool $close) use ($bb):array{
$n=count($ll); if($n<2)return []; $S=[]; $lim=$close?$n:$n-1;
for($k=0;$k<$lim;$k++){ $A=$ll[$k]; $B=$ll[($k+1)%$n];
$la1=(float)$A[1]; $lo1=(float)$A[0]; $la2=(float)$B[1]; $lo2=(float)$B[0];
$mLa=min($la1,$la2); $MLa=max($la1,$la2); $mLo=min($lo1,$lo2); $MLo=max($lo1,$lo2);
if($mLa>$bb['maxLat']||$MLa<$bb['minLat']||$mLo>$bb['maxLng']||$MLo<$bb['minLng'])continue;
$S[]=[$la1,$lo1,$la2,$lo2,$mLa,$MLa,$mLo,$MLo]; }
return $S; };
$cross=function(array $S) use ($T):bool{
foreach($T as $t){ foreach($S as $s){
if($s[4]>$t[5]||$s[5]<$t[4]||$s[6]>$t[7]||$s[7]<$t[6])continue;
if(segIntersectionLatLng([$t[0],$t[1]],[$t[2],$t[3]],[$s[0],$s[1]],[$s[2],$s[3]])!==null)return true; } }
return false; };
$cul=0; $dams=0; $bIds=[];
$__r=microtime(true);
foreach(osmBBoxRows($p,'fast_rivers',$par) as $r){
$raw=json_decode((string)$r['coords'],true); if(!$raw||count($raw)<2)continue;
$S=$mkSegs($raw,false); if(!$S)continue;
$len=0.0; for($k=0;$k<count($raw)-1;$k++)$len+=haversineDistanceMeters((float)$raw[$k][1],(float)$raw[$k][0],(float)$raw[$k+1][1],(float)$raw[$k+1][0]);
if($len>BRIDGE_RIVER_MIN_LENGTH_M)continue;
if($cross($S))$cul++; }
perfAdd('obs_riv',$__r);
$__w=microtime(true);
foreach(osmBBoxRows($p,'fast_water_bodies',$par) as $l){
$raw=json_decode((string)$l['coords'],true); if(!$raw||count($raw)<3)continue;
$S=$mkSegs($raw,true); if(!$S)continue;
if($cross($S))$dams++; }
perfAdd('obs_wat',$__w);
$__b=microtime(true); $padLa=12.0/111320.0;
foreach(osmBBoxRows($p,'fast_buildings',$par) as $bd){
$raw=json_decode((string)$bd['coords'],true); if(!$raw)continue; $rid=(int)$bd['rid'];
if(count($raw)===1){
$pla=(float)$raw[0][1]; $plo=(float)$raw[0][0];
$padLo=12.0/(111320.0*max(0.01,cos(deg2rad($pla))));
foreach($T as $t){ if($pla<$t[4]-$padLa||$pla>$t[5]+$padLa||$plo<$t[6]-$padLo||$plo>$t[7]+$padLo)continue;
if(distPointToSegmentLatLng($pla,$plo,[$t[0],$t[1]],[$t[2],$t[3]])<=10.0){ $bIds[$rid]=true; break; } }
continue; }
$S=$mkSegs($raw,true); if(!$S)continue;
if($cross($S))$bIds[$rid]=true; }
perfAdd('obs_bld',$__b);
$bl=count($bIds); $obs=[];
if($cul>0)$obs[]=['type'=>'culvert','count'=>$cul,'cost_each'=>COST_CULVERT,'total'=>$cul*COST_CULVERT,'label'=>'🚰 Водопропускная труба'];
if($dams>0)$obs[]=['type'=>'dam','count'=>$dams,'cost_each'=>COST_DAM,'total'=>$dams*COST_DAM,'label'=>'🏞️ Дамба через озеро/пруд'];
if($bl>0)$obs[]=['type'=>'building','count'=>$bl,'cost_each'=>COST_DEMOLISH_BUILDING,'total'=>$bl*COST_DEMOLISH_BUILDING,'label'=>'🏚️ Снос зданий'];
perfAdd('obs',$__t);
return ['culverts'=>$cul,'dams'=>$dams,'buildings'=>$bl,'obstacles'=>$obs]; }
function obstaclesCost(array $o):int{ $s=0; foreach($o as $x)$s+=(int)($x['total']??0); return $s; }
/* ---------- питание / ПЧ ---------- */
function poweredInfo(array $d):array{ if(!empty($GLOBALS['__pow']))return $GLOBALS['__pow']; $subs=[];
foreach(($d['substations']??[]) as $x){ if(!empty($x['is_building'])||!empty($x['frozen']))continue; $subs[]=$x; } $powered=[]; $powering=[];
foreach(($d['edges']??[]) as $e){ $eid=(string)($e['id']??''); if(empty($e['electric']))continue; if(!empty($e['electric_legacy'])){$powered[$eid]=true;continue;}
$owner=(int)($e['owner_id']??0); $pl=edgePolyline($d,$e); if(count($pl)<2)continue;
foreach($subs as $x){ if((int)$x['owner_id']!==$owner)continue; if(minDistPointToPolyline((float)$x['lat'],(float)$x['lng'],$pl)<=SUBSTATION_RANGE_M){ $powered[$eid]=true; $powering[(string)$x['id']]=true; break; } } }
$GLOBALS['__pow']=['edges'=>$powered,'subs'=>$powering]; return $GLOBALS['__pow']; }
function nearestSubstationFor(array $d,array $e):array{ $pl=edgePolyline($d,$e); if(count($pl)<2)return ['dist'=>null,'name'=>null]; $owner=(int)($e['owner_id']??0); $bd=INF; $bn=null;
foreach(($d['substations']??[]) as $x){ if((int)$x['owner_id']!==$owner)continue; if(!empty($x['is_building'])||!empty($x['frozen']))continue; $dd=minDistPointToPolyline((float)$x['lat'],(float)$x['lng'],$pl); if($dd<$bd){$bd=$dd;$bn=(string)$x['name'];} }
return ['dist'=>$bd===INF?null:$bd,'name'=>$bn]; }
function trackMaintServesInfo(array $d):array{ if(!empty($GLOBALS['__tm']))return $GLOBALS['__tm']; $maint=[];
foreach(($d['track_maint']??[]) as $x){ if(!empty($x['is_building'])||!empty($x['frozen']))continue; $maint[]=$x; } $serving=[]; $served=[];
foreach(($d['edges']??[]) as $e){ $eid=(string)($e['id']??''); if(empty($e['double'])||!empty($e['double_legacy']))continue; $owner=(int)($e['owner_id']??0); $pl=edgePolyline($d,$e); if(count($pl)<2)continue;
foreach($maint as $x){ if((int)$x['owner_id']!==$owner)continue; if(minDistPointToPolyline((float)$x['lat'],(float)$x['lng'],$pl)<=(int)($x['range_m']??TRACK_MAINT_RANGE_M)){ $served[$eid]=true; $serving[(string)$x['id']]=true; break; } } }
$GLOBALS['__tm']=['edges'=>$served,'maint'=>$serving]; return $GLOBALS['__tm']; }
function nearestTrackMaintFor(array $d,array $e):array{ $pl=edgePolyline($d,$e); if(count($pl)<2)return ['dist'=>null,'name'=>null]; $owner=(int)($e['owner_id']??0); $bd=INF; $bn=null;
foreach(($d['track_maint']??[]) as $x){ if((int)$x['owner_id']!==$owner)continue; if(!empty($x['is_building'])||!empty($x['frozen']))continue; $dd=minDistPointToPolyline((float)$x['lat'],(float)$x['lng'],$pl); if($dd<$bd){$bd=$dd;$bn=(string)$x['name'];} }
return ['dist'=>$bd===INF?null:$bd,'name'=>$bn]; }
/* ---------- экономика объектов ---------- */
function stationTracks(array $d,string $sid):int{ $t=0; foreach($d['edges'] as $e){ $w=(!empty($e['double'])||($e['upgrade_type']??null)==='double')?2:1; if(($e['from']??'')===$sid)$t+=$w; if(($e['to']??'')===$sid)$t+=$w; } return $t; }
function nodeEdgesCount(array $d,string $nid):int{ $n=0; foreach($d['edges'] as $e) if(($e['from']??'')===$nid||($e['to']??'')===$nid)$n++; return $n; }
function stationUpkeep(array $s):float{ if(!empty($s['is_building'])||!empty($s['frozen']))return 0.0; return UPKEEP_STATION_BASE+UPKEEP_STATION_PER_CLASS*max(1,(int)($s['class']??1)); }
function nodeUpkeep(array $n):float{ return !empty($n['frozen'])?0.0:UPKEEP_NODE; }
function substationUpkeep(array $x):float{ if(!empty($x['is_building'])||!empty($x['frozen']))return 0.0; return SUBSTATION_UPKEEP; }
function trackMaintUpkeep(array $x):float{ if(!empty($x['is_building'])||!empty($x['frozen']))return 0.0; return !empty($x['serves'])?TRACK_MAINT_UPKEEP:TRACK_MAINT_UPKEEP_HALF; }
function edgeUpkeep(array $e,?float $fb=null):float{ if(!empty($e['is_building'])||!empty($e['frozen']))return 0.0; $t=!empty($e['double'])?2:1; $l=isset($e['length'])?(float)$e['length']:(float)($fb??0); return INCOME_PER_TRACK*2*$t*(1.0+max(0.0,$l)/1000.0)*(!empty($e['electric'])?1.20:1.0)*UPKEEP_EDGE_INCOME_SHARE; }
function depotUpkeep(array $x):float{ if(!empty($x['is_building'])||!empty($x['frozen']))return 0.0; return DEPOT_UPKEEP_BASE+DEPOT_UPKEEP_PER_LEVEL*(max(1,(int)($x['level']??1))-1); }
function trainUpkeep(array $t,bool $run):float{ if(!empty($t['frozen']))return 0.0; $u=$run?TRAIN_UPKEEP_ACTIVE:TRAIN_UPKEEP_IDLE; foreach(($t['wagons']??[]) as $w){ $nw=normWagonType($w); if($nw)$u+=wagonUpkeep($nw,$run); } return $u; }
function stationReplacementCost(array $s):int{ $v=COST_STATION; for($c=1;$c<max(1,(int)($s['class']??1));$c++)$v+=stationUpgradeCost($c); return $v; }
function nodeReplacementCost(array $n):int{ return COST_NODE; } function substationReplacementCost(array $x):int{ return SUBSTATION_COST; } function trackMaintReplacementCost(array $x):int{ return TRACK_MAINT_COST; }
function edgeReplacementCost(array $e,array $a,array $b):int{ $l=(float)($e['length']??haversineDistanceMeters((float)$a['lat'],(float)$a['lng'],(float)$b['lat'],(float)$b['lng'])); $c=(int)ceil($l*COST_EDGE_PER_M*(!empty($e['double'])?2:1)); if(!empty($e['double']))$c+=(int)ceil($l*COST_UPGRADE_DOUBLE_PER_M); if(!empty($e['electric']))$c+=(int)ceil($l*COST_UPGRADE_ELECTRIFY_PER_M); foreach(($e['bridges']??[]) as $br)$c+=bridgeCost((string)($br['kind']??'river'),(string)($br['type']??BRIDGE_DEFAULT_TYPE)); return $c+blocksCostFor($e); }
function depotReplacementCost(array $x):int{ $v=DEPOT_COST; for($l=1;$l<max(1,(int)($x['level']??1));$l++)$v+=DEPOT_UPGRADE_COST*$l; return $v; }
function trainReplacementCost(array $t):int{ $v=trainModelCost($t['model']??'diesel_line'); foreach(($t['wagons']??[]) as $w){ $nw=normWagonType($w); if($nw)$v+=wagonCost($nw); } return $v; }
function trainCapacityUnits(array $t):array{ $p=1.0; $g=0.0; foreach(($t['wagons']??[]) as $w){ $nw=normWagonType($w); if(!$nw)continue; if(wagonIsFreight($nw))$g+=wagonCap($nw); else $p+=wagonCap($nw); } return [$p,$g]; }
function trainCapacityTotal(array $t):float{ [$p,$g]=trainCapacityUnits($t); return $p+$g; }
function trainWagonCount(array $t):int{ return count($t['wagons']??[]); }
function edgeSpeedKmh(array $e,array $pow=[]):int{ if(!empty($e['is_building'])||!empty($e['frozen']))return 0; $powered=!empty($e['electric_legacy'])||isset($pow[(string)($e['id']??'')]); return (!empty($e['electric'])&&$powered)?EDGE_SPEED_ELECTRIC:EDGE_SPEED_BASE; }
function playerStationsCount(array $d,int $p):int{ $n=0; foreach($d['stations'] as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerNodesCount(array $d,int $p):int{ $n=0; foreach($d['nodes'] as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerEdgesCount(array $d,int $p):int{ $n=0; foreach($d['edges'] as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerDepotsCount(array $d,int $p):int{ $n=0; foreach($d['depots'] as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerTrainsCount(array $d,int $p):int{ $n=0; foreach($d['trains'] as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerRoutesCount(array $d,int $p):int{ $n=0; foreach($d['routes'] as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerSubstationsCount(array $d,int $p):int{ $n=0; foreach(($d['substations']??[]) as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerTrackMaintCount(array $d,int $p):int{ $n=0; foreach(($d['track_maint']??[]) as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function playerPresetsCount(array $d,int $p):int{ $n=0; foreach(($d['presets']??[]) as $s) if((int)($s['owner_id']??0)===$p)$n++; return $n; }
function stationCostForPlayer(array $d,int $p):int{ return (int)ceil(COST_STATION*pow(STATION_PRICE_GROWTH,playerStationsCount($d,$p))); }
function edgeCostPerMeterForPlayer(array $d,int $p):float{ return COST_EDGE_PER_M*pow(EDGE_PRICE_GROWTH,playerEdgesCount($d,$p)); }
function depotCostForPlayer(array $d,int $p):int{ return (int)ceil(DEPOT_COST*pow(DEPOT_PRICE_GROWTH,playerDepotsCount($d,$p))); }
function substationCostForPlayer(array $d,int $p):int{ return (int)ceil(SUBSTATION_COST*pow(SUBSTATION_PRICE_GROWTH,playerSubstationsCount($d,$p))); }
function trackMaintCostForPlayer(array $d,int $p):int{ return (int)ceil(TRACK_MAINT_COST*pow(TRACK_MAINT_PRICE_GROWTH,playerTrackMaintCount($d,$p))); }
function stationRoutesUsed(array $d,string $sid):int{ $n=0; foreach($d['routes'] as $r) if(in_array($sid,array_map('strval',$r['stations']??[]),true))$n++; return $n; }
/* ---------- граф / профиль / спаны ---------- */
function worldIndex(array $d):array{ $st=[]; foreach($d['stations'] as $s)$st[(string)$s['id']]=$s; $nd=[]; foreach($d['nodes'] as $n)$nd[(string)$n['id']]=$n; $ep=$st+$nd; $ed=[]; $adj=[]; $pair=[];
foreach($ep as $id=>$_)$adj[$id]=[];
foreach($d['edges'] as $e){ $id=(string)$e['id']; $ed[$id]=$e; $f=(string)($e['from']??''); $t=(string)($e['to']??''); if(!isset($ep[$f])||!isset($ep[$t]))continue;
$len=(float)($e['length']??haversineDistanceMeters((float)$ep[$f]['lat'],(float)$ep[$f]['lng'],(float)$ep[$t]['lat'],(float)$ep[$t]['lng']));
$adj[$f][]=['e'=>$id,'v'=>$t,'len'=>$len]; $adj[$t][]=['e'=>$id,'v'=>$f,'len'=>$len]; $pair["$f|$t"]=$id; $pair["$t|$f"]=$id; }
$dp=[]; foreach($d['depots'] as $x)$dp[(string)$x['id']]=$x; $tr=[]; foreach($d['trains'] as $x)$tr[(string)$x['id']]=$x;
$sb=[]; foreach(($d['substations']??[]) as $x)$sb[(string)$x['id']]=$x; $tm=[]; foreach(($d['track_maint']??[]) as $x)$tm[(string)$x['id']]=$x;
return ['st'=>$st,'nd'=>$nd,'ep'=>$ep,'ed'=>$ed,'adj'=>$adj,'pair'=>$pair,'dp'=>$dp,'tr'=>$tr,'sb'=>$sb,'tm'=>$tm]; }
function edgeUsableFor(array $pol,array $e,int $pid):bool{ if(!empty($e['is_building'])||!empty($e['frozen']))return false; return accessAllowed($pol,$e,$pid,'trackage'); }
function pathBetween(array $idx,array $pol,string $a,string $b,int $pid):?array{
if($a===$b)return ['steps'=>[],'len'=>0.0]; if(!isset($idx['ep'][$a])||!isset($idx['ep'][$b]))return null;
$key=$pid.'|'.$a.'|'.$b; if(!isset($GLOBALS['__path']))$GLOBALS['__path']=[]; if(array_key_exists($key,$GLOBALS['__path']))return $GLOBALS['__path'][$key];
$usable=[]; foreach($idx['ed'] as $eid=>$e)$usable[$eid]=edgeUsableFor($pol,$e,$pid);
$dist=[$a=>0.0]; $prev=[]; $settled=[]; $pq=new SplPriorityQueue(); $pq->setExtractFlags(SplPriorityQueue::EXTR_BOTH); $pq->insert($a,0); $found=false;
while(!$pq->isEmpty()){ $top=$pq->extract(); $v=(string)$top['data']; $dv=-(float)$top['priority']; if(isset($settled[$v]))continue; if($dv>($dist[$v]??INF)+1e-6)continue; $settled[$v]=true; if($v===$b){$found=true;break;}
foreach(($idx['adj'][$v]??[]) as $link){ $eid=$link['e']; if(empty($usable[$eid]))continue; $w=(string)$link['v']; if(isset($settled[$w]))continue; $nd=$dv+max(0.1,(float)$link['len']); if($nd<($dist[$w]??INF)-1e-9){ $dist[$w]=$nd; $prev[$w]=['v'=>$v,'e'=>$eid]; $pq->insert($w,-$nd); } } }
if(!$found){ $GLOBALS['__path'][$key]=null; return null; }
$steps=[]; $cur=$b;
while($cur!==$a){ if(!isset($prev[$cur])){ $GLOBALS['__path'][$key]=null; return null; } $steps[]=['e'=>$prev[$cur]['e'],'from'=>$prev[$cur]['v'],'to'=>$cur]; $cur=$prev[$cur]['v']; }
$steps=array_reverse($steps); $res=['steps'=>$steps,'len'=>$dist[$b]]; $GLOBALS['__path'][$key]=$res; return $res; }
function edgeSpeedZones(array $idx,array $e,float $len):array{
$k=(string)($e['id']??'').'|'.(int)round($len);
if(isset($GLOBALS['__esz'][$k]))return $GLOBALS['__esz'][$k];
$zones=[]; $pts=null;
foreach(($e['bridges']??[]) as $br){ $la=(float)($br['lat']??0); $ln=(float)($br['lng']??0); if(!is_finite($la)||!is_finite($ln)||($la==0.0&&$ln==0.0))continue;
if($pts===null){ $pts=edgePolylineIdx($idx,$e); if(count($pts)<2){ $GLOBALS['__esz'][$k]=[]; return []; } }
$pr=projectOnPolyline($pts,$la,$ln); $span=(float)($br['span']??OVERPASS_SPAN_M); $z0=max(0.0,$pr['dist']-$span/2); $z1=min($len,$pr['dist']+$span/2); if($z1-$z0<1.0)continue;
$zones[]=['from'=>$z0,'to'=>$z1,'speed'=>bridgeSpeed((string)($br['type']??BRIDGE_DEFAULT_TYPE)),'name'=>(string)($br['name']??''),'type'=>(string)($br['type']??'')]; }
if(!$zones){ $GLOBALS['__esz'][$k]=[]; return []; }
usort($zones,fn($x,$y)=>$x['from']<=>$y['from']); $m=[];
foreach($zones as $z){ $n=count($m); if($n&&$z['from']<$m[$n-1]['to']){ if($z['speed']<$m[$n-1]['speed']){ $m[$n-1]['speed']=$z['speed']; $m[$n-1]['name']=$z['name']; $m[$n-1]['type']=$z['type']; } $m[$n-1]['to']=max($m[$n-1]['to'],$z['to']); } else $m[]=$z; }
$GLOBALS['__esz'][$k]=$m; return $m; }
function routeCompute(array $idx,array $pol,array $stops,bool $loop,int $owner,array $fleet=[]):array{
perfCount('rc');
$pow=poweredInfo(['stations'=>array_values($idx['st']),'nodes'=>array_values($idx['nd']),'edges'=>$idx['ed'],'substations'=>array_values($idx['sb']??[])])['edges']??[];
$out=['ok'=>false,'reason'=>'','active'=>false,'km'=>0.0,'double_share'=>0.0,'elec_share'=>0.0,'speed'=>0,'line_speed'=>EDGE_SPEED_BASE,'speed_profile'=>[[0,EDGE_SPEED_BASE]],'bottlenecks'=>[],'edge_spans'=>[],
'min_class'=>1,'building_sum'=>0.0,'recommended'=>1,'slots'=>1,'capacity'=>0.0,'train_factor'=>0.0,'income'=>0,'upkeep'=>0.0,'fees'=>[],'fee_total'=>0,'edges'=>[],'stops'=>[],'path'=>[],'legs'=>[],'transit'=>[],'owners'=>[],'foreign_km'=>0.0,'wagon_limit'=>0,'fully_powered'=>false,'electric_km'=>0.0,'powered_electric_km'=>0.0,'dist_mult'=>0.0,'freight_break_even_km'=>FREIGHT_BREAK_EVEN_KM];
$stops=array_values(array_unique(array_map('strval',$stops))); $out['stops']=$stops;
if(count($stops)<2){ $out['reason']='нужно минимум 2 станции'; return $out; }
$seq=$stops; if($loop)$seq[]=$stops[0];
$km=0.0;$dkm=0.0;$ekm=0.0;$pekm=0.0;$lineSpeed=PHP_INT_MAX;$profTime=0.0;$profLen=0.0;$profile=[];$bns=[];$spans=[];
$edgeIds=[];$foreign=[];$steps=[];$legs=[];$verts=[];$reason='';
for($i=0;$i<count($seq)-1;$i++){ $from=$seq[$i]; $to=$seq[$i+1]; $verts[$from]=true; $path=pathBetween($idx,$pol,$from,$to,$owner);
if($path===null||($from!==$to&&!$path['steps'])){ if($reason==='')$reason='нет доступного пути «'.($idx['st'][$from]['name']??'?').'» → «'.($idx['st'][$to]['name']??'?').'»'; $legs[]=['from'=>$from,'to'=>$to,'len'=>0.0,'steps'=>[],'ok'=>false]; continue; }
$legLen=0.0;
foreach($path['steps'] as $s){ $e=$idx['ed'][$s['e']]; $len=(float)($e['length']??0); $spanStart=$profLen; $km+=$len; $legLen+=$len; if(!empty($e['double']))$dkm+=$len;
$poweredEdge=!empty($e['electric_legacy'])||isset($pow[$s['e']]);
if(!empty($e['electric'])){ $ekm+=$len; if($poweredEdge)$pekm+=$len; }
$base=(!empty($e['electric'])&&$poweredEdge)?EDGE_SPEED_ELECTRIC:EDGE_SPEED_BASE; $lineSpeed=min($lineSpeed,$base);
$zones=edgeSpeedZones($idx,$e,$len); $pos=0.0; $cum=$profLen;
foreach($zones as $z){ if($z['from']>$pos){ $profile[]=[$cum+$pos,$base]; $profTime+=($z['from']-$pos)/max(1,$base); }
$profile[]=[$cum+$z['from'],$z['speed']]; $profTime+=($z['to']-$z['from'])/max(1,$z['speed']);
if($z['speed']<$base)$bns[]=['name'=>$z['name'],'type_label'=>bridgeLabel($z['type']),'speed'=>$z['speed'],'from_km'=>round(($cum+$z['from'])/1000,2),'to_km'=>round(($cum+$z['to'])/1000,2)];
$pos=$z['to']; }
if($pos<$len){ $profile[]=[$cum+$pos,$base]; $profTime+=($len-$pos)/max(1,$base); }
$profLen+=$len;
$spans[]=['e'=>$s['e'],'from_m'=>(int)round($spanStart),'to_m'=>(int)round($profLen),'f'=>((string)$s['from']===(string)$e['from']),'double'=>!empty($e['double']),'block_len'=>edgeBlockLen($e),'blocks_n'=>edgeBlocksN($e)];
$eo=(int)($e['owner_id']??0); if($eo>0&&$eo!==$owner)$foreign[$eo]=($foreign[$eo]??0.0)+$len;
$edgeIds[]=$s['e']; $steps[]=$s; $verts[(string)$s['to']]=true; }
$legs[]=['from'=>$from,'to'=>$to,'len'=>$legLen,'steps'=>$path['steps'],'ok'=>true]; }
$minClass=99; $bsum=0.0;
foreach($stops as $sid){ if(!isset($idx['st'][$sid])){ if($reason==='')$reason='станция удалена'; continue; } $s=$idx['st'][$sid];
if(!empty($s['is_building'])&&$reason==='')$reason="станция «{$s['name']}» строится";
if(!empty($s['frozen'])&&$reason==='')$reason="станция «{$s['name']}» на продаже";
if(!accessAllowed($pol,$s,$owner,'stop')&&$reason==='')$reason="нет разрешения на остановку на «{$s['name']}»";
$minClass=min($minClass,max(1,(int)$s['class'])); $h=max(0,min(100,(int)($s['health']??100)));
$bsum+=(getBuildingIncomePercent((int)($s['buildings']??0))/100.0)*stationClassMultiplier((int)$s['class'])*($h/100.0); }
if($minClass===99)$minClass=1; if($lineSpeed===PHP_INT_MAX)$lineSpeed=EDGE_SPEED_BASE;
$eff=($profLen>0&&$profTime>0)?$profLen/$profTime:$lineSpeed; $speed=(int)round($eff);
$stepsOut=[]; foreach($profile as $iv){ if(!$stepsOut||end($stepsOut)[1]!==$iv[1])$stepsOut[]=[(int)round($iv[0]),(int)$iv[1]]; } if(!$stepsOut)$stepsOut=[[0,$lineSpeed]];
$ds=$km>0?$dkm/$km:0.0; $pes=$km>0?$pekm/$km:0.0; $fully=$km>0&&$pekm>=$km-1.0;
$wagonLimit=$minClass*WAGONS_PER_CLASS; $rec=max(1,(int)ceil(($km/1000.0)/TRAIN_ROUTE_KM_DIVISOR)); $slots=max(1,(int)ceil($rec*(1+$ds)));
if(CLASS_LIMITS_TRAIN_SLOTS)$slots=max(1,min($minClass,$slots));
$transit=[]; foreach(array_keys($verts) as $v) if(!in_array($v,$stops,true)&&isset($idx['st'][$v]))$transit[]=$v;
$out['km']=$km; $out['double_share']=$ds; $out['elec_share']=$pes; $out['speed']=$speed; $out['line_speed']=$lineSpeed; $out['speed_profile']=$stepsOut; $out['bottlenecks']=$bns; $out['edge_spans']=$spans;
$out['min_class']=$minClass; $out['building_sum']=$bsum; $out['recommended']=$rec; $out['slots']=$slots;
$out['edges']=array_values(array_unique($edgeIds)); $out['path']=$steps; $out['legs']=$legs; $out['transit']=$transit;
$out['wagon_limit']=$wagonLimit; $out['fully_powered']=$fully; $out['electric_km']=$ekm; $out['powered_electric_km']=$pekm;
$fk=array_sum($foreign); $out['foreign_km']=$fk; $owners=[]; foreach($foreign as $p=>$m)$owners[]=['player_id'=>(int)$p,'km'=>$m/1000.0,'share'=>$km>0?$m/$km:0.0]; $out['owners']=$owners;
if($reason!==''){ $out['reason']=$reason; return $out; }
$cap=max(0.0,(float)($fleet['cap']??0)); $pU=max(0.0,(float)($fleet['p']??0)); $gU=max(0.0,(float)($fleet['g']??0)); $pw=(float)($fleet['power']??1.0); $hw=(float)($fleet['health']??1.0);
$factor=$cap>0?min($cap,$slots)/$rec:0.0; $kmKm=$km/1000.0; $distMult=$cap>0?($pU+$gU*($kmKm/FREIGHT_BREAK_EVEN_KM))/$cap:0.0;
$gross=ROUTE_INCOME_BASE*$bsum*$factor*$distMult*(1+ROUTE_DOUBLE_BONUS*$ds)*(1+ROUTE_ELECTRIC_BONUS*$pes)*($speed/ROUTE_SPEED_BASE)*max(0.0,$pw)*max(0.0,$hw);
$upk=$cap>0?$kmKm*ROUTE_UPKEEP_PER_KM:0.0; $basis=TRACTAGE_SHARE_BASIS==='net'?max(0.0,$gross-$upk):$gross; $fees=[]; $ft=0;
if($cap>0&&$km>0) foreach($foreign as $p=>$m){ $pay=(int)floor($basis*($m/$km)*TRACTAGE_REVENUE_SHARE); if($pay>0){ $fees[(int)$p]=$pay; $ft+=$pay; } }
$out['ok']=true; $out['active']=$cap>0; $out['train_factor']=$factor; $out['capacity']=$cap; $out['dist_mult']=$distMult;
$out['income']=(int)floor($gross); $out['upkeep']=$upk; $out['fees']=$fees; $out['fee_total']=$ft;
if($cap>0&&$profLen>1){ $pts=[]; foreach($steps as $s){ $e=$idx['ed'][$s['e']]; $ll=edgePolylineIdx($idx,$e); if(count($ll)<2)continue; if((string)$e['from']!==(string)$s['from'])$ll=array_reverse($ll); $st0=$pts?1:0; for($i=$st0;$i<count($ll);$i++)$pts[]=$ll[$i]; }
if(count($pts)>1){ $stopSet=array_flip($stops); $tset=array_flip($transit);
$bbT=bboxOf($pts); $pLa=60/111320.0; $pLo=60/(111320.0*max(0.01,cos(deg2rad(($bbT['minLat']+$bbT['maxLat'])/2))));
foreach($idx['st'] as $sid=>$s){ if(isset($stopSet[$sid])||isset($tset[$sid]))continue; if(!empty($s['is_building'])||!empty($s['frozen']))continue;
$sla=(float)$s['lat']; $sln=(float)$s['lng'];
if($sla<$bbT['minLat']-$pLa||$sla>$bbT['maxLat']+$pLa||$sln<$bbT['minLng']-$pLo||$sln>$bbT['maxLng']+$pLo)continue;
$pr=projectOnPolyline($pts,$sla,$sln); if($pr['off']<=30.0&&$pr['dist']>1.0&&$pr['dist']<$profLen-1.0){$transit[]=$sid;$tset[$sid]=true;} }
$out['transit']=$transit; } }
return $out; }
function routeGeometry(array $data,array $idx,array $info):array{ $pts=[]; $stopDist=[]; $acc=0.0; $first=true;
foreach(($info['legs']??[]) as $leg){ if($first){ $stopDist[]=0.0; $first=false; }
foreach(($leg['steps']??[]) as $s){ $e=$idx['ed'][$s['e']]??null; if(!$e)continue; $ll=edgePolyline($data,$e); if(count($ll)<2)continue; if((string)$e['from']!==(string)$s['from'])$ll=array_reverse($ll);
$start=$first?0:1; if($pts===[])$start=0;
for($i=$start;$i<count($ll);$i++){ $p=$ll[$i]; if($pts!==[])$acc+=haversineDistanceMeters($pts[count($pts)-1][0],$pts[count($pts)-1][1],$p[0],$p[1]); $pts[]=$p; } }
$stopDist[]=$acc; }
if(count($stopDist)>count($info['stops']??[]))$stopDist=array_slice($stopDist,0,count($info['stops']));
$transit=[]; if(count($pts)>1) foreach(($info['transit']??[]) as $sid){ $s=$idx['st'][$sid]??null; if(!$s)continue; $pr=projectOnPolyline($pts,(float)$s['lat'],(float)$s['lng']); $transit[]=['id'=>$sid,'name'=>$s['name'],'dist'=>$pr['dist'],'off'=>$pr['off']]; }
return ['geometry'=>$pts,'stops_dist'=>$stopDist,'transit'=>$transit,'length'=>$acc]; }
function fleetForRoute(array $data,array $eco,string $rid,bool $fully,?string $excl=null,?array $incl=null):array{ $cap=0.0;$p=0.0;$g=0.0;$pw=0.0;$hw=0.0;
$add=function(array $t) use (&$cap,&$p,&$g,&$pw,&$hw){ $c=trainCapacityTotal($t); [$pu,$gu]=trainCapacityUnits($t); $cap+=$c; $p+=$pu; $g+=$gu; $pw+=$c*trainModelPower($t['model']??'diesel_line'); $hw+=$c*max(0,(int)($t['health']??100))/100.0; };
foreach($data['trains'] as $t){ if((string)($t['route_id']??'')!==$rid)continue; if($excl!==null&&(string)$t['id']===$excl)continue; if(!isset($eco['trains_ready'][(string)$t['id']]))continue; if(trainModelTraction($t['model']??'diesel_line')==='electric'&&!$fully)continue; $add($t); }
if($incl!==null)$add($incl);
return ['cap'=>$cap,'p'=>$p,'g'=>$g,'power'=>$cap>0?$pw/$cap:1.0,'health'=>$cap>0?$hw/$cap:1.0]; }
function economy(array $data):array{ if(!empty($GLOBALS['__eco']))return $GLOBALS['__eco'];
$__t=microtime(true);
if(empty($GLOBALS['__eco_bypass'])){
try{ $v=(int)metaGet('eco_ver');
$s=db()->prepare("SELECT data FROM eco_cache WHERE version=:v"); $s->execute([':v'=>$v]); $raw=$s->fetchColumn();
if($raw!==false){ $res=json_decode((string)$raw,true); if(is_array($res)&&isset($res['routes'])){ $GLOBALS['__eco']=$res; perfAdd('eco_hit',$__t); return $res; } }
}catch(Throwable $e){}
}
$now=time(); $idx=worldIndex($data); $pol=policiesAll(); $pow=poweredInfo($data); $tm=trackMaintServesInfo($data);
$ready=[]; foreach($data['trains'] as $t){ $ok=empty($t['is_building'])&&(int)($t['build_end']??0)<=$now&&(int)($t['transit_end']??0)<=$now; $hasDp=!empty($t['depot_id'])&&isset($idx['dp'][(string)$t['depot_id']]); $rid=(string)($t['route_id']??''); if($ok&&$hasDp&&$rid!=='')$ready[(string)$t['id']]=$rid; }
$routes=[];$servedSt=[];$passedSt=[];$servedEd=[];$servedNd=[];$dpServed=[];
foreach($data['routes'] as $r){ $rid=(string)$r['id']; $own=(int)($r['owner_id']??0);
$probe=routeCompute($idx,$pol,$r['stations']??[],!empty($r['loop']),$own,[]);
$fleet=fleetForRoute($data,['trains_ready'=>$ready],$rid,$probe['fully_powered']);
$info=routeCompute($idx,$pol,$r['stations']??[],!empty($r['loop']),$own,$fleet);
$info['owner_id']=$own; $info['name']=(string)($r['name']??'Маршрут'); $info['status']=!$info['ok']?'broken':($info['active']?'active':'idle'); $routes[$rid]=$info;
if($info['ok']&&$info['active']){ foreach($info['stops'] as $sid)$servedSt[$sid]=true; foreach($info['transit'] as $sid)$passedSt[$sid]=true; foreach($info['edges'] as $eid)$servedEd[$eid]=true; foreach($info['path'] as $s){ $servedNd[(string)$s['from']]=true; $servedNd[(string)$s['to']]=true; } } }
foreach($data['trains'] as $t){ $rid=(string)($t['route_id']??''); if($rid!==''&&isset($routes[$rid])&&$routes[$rid]['status']==='active'&&isset($ready[(string)$t['id']])&&!empty($t['depot_id'])){ if(trainModelTraction($t['model']??'diesel_line')==='electric'&&!$routes[$rid]['fully_powered'])continue; $dpServed[(string)$t['depot_id']]=true; } }
$fin=[]; $touch=function(int $p) use (&$fin){ if($p<=0)return; if(!isset($fin[$p]))$fin[$p]=['income'=>0,'upkeep'=>0.0,'net'=>0.0,'route_income'=>0,'tractage_in'=>0,'tractage_out'=>0,'station_upkeep'=>0.0,'node_upkeep'=>0.0,'edge_upkeep'=>0.0,'depot_upkeep'=>0.0,'train_upkeep'=>0.0,'route_upkeep'=>0.0,'substation_upkeep'=>0.0,'track_maint_upkeep'=>0.0,'routes_active'=>0,'routes_total'=>0,'trains'=>0,'trains_running'=>0,'depots'=>0,'substations'=>0,'track_maint'=>0]; };
foreach($data['stations'] as $s){ $p=(int)($s['owner_id']??0); if($p<=0)continue; $touch($p); $id=(string)$s['id']; $k=isset($servedSt[$id])?1.0:(isset($passedSt[$id])?TRANSIT_UPKEEP_RATIO:CONSERVATION_RATIO); $u=stationUpkeep($s)*$k; $fin[$p]['station_upkeep']+=$u; $fin[$p]['upkeep']+=$u; }
foreach($data['nodes'] as $n){ $p=(int)($n['owner_id']??0); if($p<=0)continue; $touch($p); $u=nodeUpkeep($n)*(isset($servedNd[(string)$n['id']])?1.0:CONSERVATION_RATIO); $fin[$p]['node_upkeep']+=$u; $fin[$p]['upkeep']+=$u; }
foreach($data['edges'] as $e){ $p=(int)($e['owner_id']??0); if($p<=0)continue; $touch($p); $u=edgeUpkeep($e)*(isset($servedEd[(string)$e['id']])?1.0:CONSERVATION_RATIO); $fin[$p]['edge_upkeep']+=$u; $fin[$p]['upkeep']+=$u; }
foreach($data['depots'] as $x){ $p=(int)($x['owner_id']??0); if($p<=0)continue; $touch($p); $fin[$p]['depots']++; $u=depotUpkeep($x)*(isset($dpServed[(string)$x['id']])?1.0:CONSERVATION_RATIO); $fin[$p]['depot_upkeep']+=$u; $fin[$p]['upkeep']+=$u; }
foreach(($data['substations']??[]) as $x){ $p=(int)($x['owner_id']??0); if($p<=0)continue; $touch($p); $fin[$p]['substations']++; $u=substationUpkeep($x)*(isset($pow['subs'][(string)$x['id']])?1.0:CONSERVATION_RATIO); $fin[$p]['substation_upkeep']+=$u; $fin[$p]['upkeep']+=$u; }
foreach(($data['track_maint']??[]) as $x){ $p=(int)($x['owner_id']??0); if($p<=0)continue; $touch($p); $fin[$p]['track_maint']++; $u=trackMaintUpkeep($x)*(isset($tm['maint'][(string)$x['id']])?1.0:CONSERVATION_RATIO); $fin[$p]['track_maint_upkeep']+=$u; $fin[$p]['upkeep']+=$u; }
foreach($data['trains'] as $t){ $p=(int)($t['owner_id']??0); if($p<=0)continue; $touch($p); $fin[$p]['trains']++; $rid=(string)($t['route_id']??'');
$run=isset($ready[(string)$t['id']])&&isset($routes[$rid])&&$routes[$rid]['status']==='active';
if($run&&trainModelTraction($t['model']??'diesel_line')==='electric'&&!$routes[$rid]['fully_powered'])$run=false;
if($run)$fin[$p]['trains_running']++; $u=trainUpkeep($t,$run); $fin[$p]['train_upkeep']+=$u; $fin[$p]['upkeep']+=$u; }
foreach($routes as $info){ $p=(int)$info['owner_id']; if($p<=0)continue; $touch($p); $fin[$p]['routes_total']++; if($info['status']!=='active')continue; $fin[$p]['routes_active']++;
$fin[$p]['route_income']+=$info['income']; $fin[$p]['income']+=$info['income']; $fin[$p]['route_upkeep']+=$info['upkeep']; $fin[$p]['upkeep']+=$info['upkeep'];
foreach($info['fees'] as $other=>$fee){ $fin[$p]['tractage_out']+=$fee; $fin[$p]['upkeep']+=$fee; $touch((int)$other); $fin[(int)$other]['tractage_in']+=$fee; $fin[(int)$other]['income']+=$fee; } }
foreach($fin as &$f)$f['net']=$f['income']-$f['upkeep']; unset($f);
$res=['routes'=>$routes,'players'=>$fin,'served_st'=>$servedSt,'passed_st'=>$passedSt,'served_ed'=>$servedEd,'served_nd'=>$servedNd,'depot_served'=>$dpServed,'trains_ready'=>$ready,'powered_edges'=>$pow['edges'],'powering_subs'=>$pow['subs'],'tm_served_edges'=>$tm['edges'],'tm_serving_maint'=>$tm['maint']];
$GLOBALS['__eco']=$res;
perfAdd('eco',$__t); perfCount('eco');
if(empty($GLOBALS['__eco_bypass'])){ try{ $v=(int)metaGet('eco_ver');
db()->prepare("INSERT OR REPLACE INTO eco_cache (version,data,created_at) VALUES (:v,:d,:c)")->execute([':v'=>$v,':d'=>json_encode($res,JSON_UNESCAPED_UNICODE),':c'=>date('c')]);
db()->exec("DELETE FROM eco_cache WHERE version < ".max(0,$v-2)); }catch(Throwable $e){} }
return $res; }
function playerFinancials(array $d,int $p):array{ $e=economy($d); return $e['players'][$p]??['income'=>0,'upkeep'=>0.0,'net'=>0.0,'route_income'=>0,'tractage_in'=>0,'tractage_out'=>0,'station_upkeep'=>0.0,'node_upkeep'=>0.0,'edge_upkeep'=>0.0,'depot_upkeep'=>0.0,'train_upkeep'=>0.0,'route_upkeep'=>0.0,'substation_upkeep'=>0.0,'track_maint_upkeep'=>0.0,'routes_active'=>0,'routes_total'=>0,'trains'=>0,'trains_running'=>0,'depots'=>0,'substations'=>0,'track_maint'=>0]; }
function playerCompanyValue(array $d,int $p):array{ $sv=0;$sc=0; foreach($d['stations'] as $s) if((int)($s['owner_id']??0)===$p){$sc++;$sv+=stationReplacementCost($s);}
$nv=0;$nc=0; foreach($d['nodes'] as $n) if((int)($n['owner_id']??0)===$p){$nc++;$nv+=nodeReplacementCost($n);}
$ev=0;$km=0.0; foreach($d['edges'] as $e) if((int)($e['owner_id']??0)===$p){ $a=endpointById($d,(string)$e['from']); $b=endpointById($d,(string)$e['to']); if($a&&$b){ $ev+=edgeReplacementCost($e,$a,$b); $km+=(float)($e['length']??0)/1000.0; } }
$dv=0;$dc=0; foreach($d['depots'] as $x) if((int)($x['owner_id']??0)===$p){$dc++;$dv+=depotReplacementCost($x);}
$tv=0;$tc=0; foreach($d['trains'] as $x) if((int)($x['owner_id']??0)===$p){$tc++;$tv+=trainReplacementCost($x);}
$bv=0;$bc=0; foreach(($d['substations']??[]) as $x) if((int)($x['owner_id']??0)===$p){$bc++;$bv+=substationReplacementCost($x);}
$mv=0;$mc=0; foreach(($d['track_maint']??[]) as $x) if((int)($x['owner_id']??0)===$p){$mc++;$mv+=trackMaintReplacementCost($x);}
return ['stations'=>$sc,'nodes'=>$nc,'edge_km'=>round($km,2),'depots'=>$dc,'trains'=>$tc,'substations'=>$bc,'track_maint'=>$mc,'assets'=>$sv+$nv+$ev+$dv+$tv+$bv+$mv]; }
/* ---------- тик ---------- */
function clearUpgrade(array &$o):void{ $o['upgrading']=false; $o['upgrade_type']=null; $o['upgrade_end']=0; $o['upgrade_class']=0; }
function completeConstruction(array &$d,int $now):bool{ $ch=false; $due=$now+2;
foreach(['stations','edges','depots','substations','track_maint','trains'] as $k) if(!isset($d[$k])||!is_array($d[$k]))$d[$k]=[];
foreach($d['stations'] as &$s){ if(!empty($s['is_building'])&&(int)$s['build_end']<=$due){ $s['is_building']=false; $s['build_type']=null; $ch=true; logEvent('construction_done',(int)$s['owner_id'],"Станция «{$s['name']}» достроена!",[]); }
if(!empty($s['upgrading'])&&(int)$s['upgrade_end']<=$due){ if(($s['upgrade_type']??null)==='class'){ $nc=(int)($s['upgrade_class']??0); if($nc<=(int)$s['class'])$nc=(int)$s['class']+1; $s['class']=min(MAX_CLASS,$nc); $s['capacity']=4+($s['class']-1)*2; $s['radius']=stationRadiusForClass((int)$s['class']); logEvent('construction_done',(int)$s['owner_id'],"Станция «{$s['name']}» улучшена до класса {$s['class']} — маршрутов: ".stationRouteLimit($s).", вагонов: ".stationWagonLimit($s).(stationBlocksEntry($s)?'':' · путевое развитие: поезда могут разъезжаться'),[]); } clearUpgrade($s); $ch=true; } } unset($s);
foreach($d['edges'] as &$e){ $p=(int)($e['owner_id']??0);
if(!empty($e['is_building'])&&(int)$e['build_end']<=$due){ $e['is_building']=false; $e['build_type']=null; $ch=true; logEvent('construction_done',$p,"Перегон достроен! 🚦 блок-участков: ".edgeBlocksN($e),[]); }
if(!empty($e['upgrading'])&&(int)$e['upgrade_end']<=$due){ $t=$e['upgrade_type']??null; if($t==='double'){ $e['double']=true; $e['double_legacy']=false; logEvent('construction_done',$p,"Второй путь построен! 🚦 блок-участков: ".edgeBlocksN($e)." по ".edgeBlockLen($e).' м',[]); } elseif($t==='electric'){ $e['electric']=true; $e['electric_legacy']=false; logEvent('construction_done',$p,"Перегон электрифицирован!",[]); } clearUpgrade($e); $ch=true; } } unset($e);
foreach($d['depots'] as &$x){ $p=(int)($x['owner_id']??0);
if(!empty($x['is_building'])&&(int)$x['build_end']<=$due){ $x['is_building']=false; $x['build_end']=0; $ch=true; logEvent('construction_done',$p,"Депо «{$x['name']}» построено!",[]); }
if(!empty($x['upgrading'])&&(int)$x['upgrade_end']<=$due){ $l=max((int)$x['level']+1,(int)($x['upgrade_level']??0)); $x['level']=min(DEPOT_MAX_LEVEL,$l); $x['capacity']=depotCapacityForLevel((int)$x['level']); $x['upgrading']=false; $x['upgrade_end']=0; $x['upgrade_level']=0; $ch=true; logEvent('construction_done',$p,"Депо «{$x['name']}» расширено до уровня {$x['level']}!",[]); } } unset($x);
foreach($d['substations'] as &$x){ if(!empty($x['is_building'])&&(int)$x['build_end']<=$due){ $x['is_building']=false; $x['build_end']=0; $ch=true; logEvent('construction_done',(int)($x['owner_id']??0),"Подстанция «{$x['name']}» запущена — сеть в радиусе 8 км запитана",[]); } } unset($x);
foreach($d['track_maint'] as &$x){ if(!empty($x['is_building'])&&(int)$x['build_end']<=$due){ $x['is_building']=false; $x['build_end']=0; $ch=true; logEvent('construction_done',(int)($x['owner_id']??0),"Путевая часть «{$x['name']}» запущена — радиус 10 км",[]); } } unset($x);
foreach($d['trains'] as &$t){ $p=(int)($t['owner_id']??0);
if(!empty($t['is_building'])&&(int)$t['build_end']<=$due){ $t['is_building']=false; $t['build_end']=0; $ch=true; logEvent('train_ready',$p,"Поезд «{$t['name']}» (".trainModelLabel($t['model']??'diesel_line').") поставлен в депо!",[]); }
if((int)($t['transit_end']??0)>0&&(int)$t['transit_end']<=$due){ $t['transit_end']=0; $ch=true; logEvent('train_ready',$p,"Поезд «{$t['name']}» прибыл в новое депо",[]); } } unset($t);
if($ch)cacheReset(); return $ch; }
function applyHealthDecay(array &$d,int $now):bool{ $last=(int)metaGet('health_tick'); if($last===0){ metaSet('health_tick',(string)$now); return false; }
$el=$now-$last; if($el<60)return false; $dec=HEALTH_DECAY_PER_REAL_DAY*($el/REAL_DAY_SEC); $decT=TRAIN_WEAR_PER_REAL_DAY*($el/REAL_DAY_SEC); if($dec<=0)return false; $ch=false;
foreach(['stations','depots','substations'] as $k){ if(!isset($d[$k]))continue;
foreach($d[$k] as &$o){ if(!empty($o['is_building'])||!empty($o['frozen']))continue; $old=(int)($o['health']??100); $pend=(float)($o['health_pending']??0)+$dec; $lost=(int)floor($pend); $o['health_pending']=$pend-$lost; $new=max(0,$old-$lost); if($new<$old){ $o['health']=$new; $ch=true; } } unset($o); }
if($decT>0){ $eco=economy($d);
foreach($d['trains'] as &$t){ if(!empty($t['is_building'])||!empty($t['frozen']))continue; $rid=(string)($t['route_id']??'');
$run=isset($eco['trains_ready'][(string)$t['id']])&&isset($eco['routes'][$rid])&&$eco['routes'][$rid]['status']==='active';
if($run&&trainModelTraction($t['model']??'diesel_line')==='electric'&&empty($eco['routes'][$rid]['fully_powered']))$run=false; if(!$run)continue;
$old=(int)($t['health']??100); $pend=(float)($t['health_pending']??0)+$decT; $lost=(int)floor($pend); $t['health_pending']=$pend-$lost; $new=max(0,$old-$lost); if($new<$old){ $t['health']=$new; $ch=true; } } unset($t); }
metaSet('health_tick',(string)$now); if($ch)cacheReset(); return $ch; }
function tickDue():bool{ $now=time(); $ht=(int)metaGet('health_tick'); if($ht===0||$now-$ht>=60)return true; if($now-(int)metaGet('last_tick')>=DAY_DURATION_SEC)return true;
$nd=metaGet('next_due'); if($nd===null)return true; if((int)$nd>0&&(int)$nd<=$now+5)return true;
foreach(db()->query("SELECT expires_at FROM auctions WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN) as $x) if(strtotime((string)$x)<$now)return true; return false; }
function tickWorld():void{ if(!tickDue())return;
$__t=microtime(true);
$GLOBALS['__eco_bypass']=true;
try{ runTx(function(){ $pdo=db(); $last=(int)metaGet('last_tick'); $day=(int)metaGet('day_count'); $now=time(); $passed=intdiv($now-$last,DAY_DURATION_SEC);
$w=worldGet(); $ch=false; cacheReset();
$GLOBALS['__eco_bypass']=false;
if(applyHealthDecay($w['data'],$now))$ch=true;
$GLOBALS['__eco_bypass']=true;
if(completeConstruction($w['data'],$now))$ch=true;
if(expireAuctions($pdo,$w['data'],$now))$ch=true;
if($passed>0){ if(processDays($pdo,$w,$day,$passed))$ch=true; cleanupEvents();
$pdo->prepare("DELETE FROM daily_stats WHERE day < :d")->execute([':d'=>$day+$passed-400]);
metaSet('day_count',(string)($day+$passed)); metaSet('last_tick',(string)($last+$passed*DAY_DURATION_SEC)); }
if($ch){ $ecoFinal=$GLOBALS['__eco']??null; worldSave($w['data']);
if($passed>0&&$ecoFinal){ try{ db()->prepare("INSERT OR REPLACE INTO eco_cache (version,data,created_at) VALUES (:v,:d,:c)")->execute([':v'=>(int)metaGet('eco_ver'),':d'=>json_encode($ecoFinal,JSON_UNESCAPED_UNICODE),':c'=>date('c')]); }catch(Throwable $e){} } }
else metaSet('next_due',(string)computeNextDue($w['data'])); }); }
finally{ $GLOBALS['__eco_bypass']=false; }
perfAdd('tick',$__t); }
function processDays(PDO $pdo,array &$world,int $day,int $passed):bool{ $ch=false; cacheReset();
$players=$pdo->query("SELECT id,money,grace_until_day,last_grant_at FROM players")->fetchAll(PDO::FETCH_ASSOC);
$statIns=$pdo->prepare("INSERT INTO daily_stats (player_id,day,income,upkeep,net) VALUES (:p,:d,:i,:u,:n)");
$snap=$pdo->prepare("INSERT OR REPLACE INTO company_snapshots (player_id,day,company_value,stations,edge_km,depots,trains,routes,nodes,substations,track_maint) VALUES (:p,:d,:v,:s,:k,:dp,:tr,:rt,:nd,:sb,:tm)");
$first=max($day,$day+$passed-400); $toAuction=[];
foreach($players as $p){ $pid=(int)$p['id']; $fin=playerFinancials($world['data'],$pid); $grace=(int)($p['grace_until_day']??0)>$day;
$upkDay=$fin['upkeep']*($grace?GRACE_UPKEEP_RATIO:1.0); $net=(int)round($fin['income']*$passed-$upkDay*$passed);
for($dd=$first;$dd<$day+$passed;$dd++)$statIns->execute([':p'=>$pid,':d'=>$dd,':i'=>(int)$fin['income'],':u'=>(int)ceil($upkDay),':n'=>(int)floor($fin['income']-$upkDay)]);
$cv=playerCompanyValue($world['data'],$pid); $bs=$pdo->prepare("SELECT money FROM players WHERE id=:id"); $bs->execute([':id'=>$pid]); $bal=(int)$bs->fetchColumn(); $val=$cv['assets']+$bal;
for($dd=$first;$dd<$day+$passed;$dd++)$snap->execute([':p'=>$pid,':d'=>$dd,':v'=>$val,':s'=>$cv['stations'],':k'=>$cv['edge_km'],':dp'=>$cv['depots'],':tr'=>$cv['trains'],':rt'=>playerRoutesCount($world['data'],$pid),':nd'=>$cv['nodes'],':sb'=>$cv['substations'],':tm'=>$cv['track_maint']]);
if($net>=0){ if($net>0)addMoney($pid,$net,'net_income',null,['days'=>$passed]); }
else{ $need=-$net; $can=min($bal,$need); if($can>0)$pdo->prepare("UPDATE players SET money=money-:a WHERE id=:id")->execute([':a'=>$can,':id'=>$pid]); $debt=$need-$can; if($debt>0)$toAuction[$pid]=$debt; } }
foreach($toAuction as $pid=>$debt){ cacheReset(); $nm=companyDisplayName((int)$pid);
$comps=confiscateForDebt($world,(int)$pid,(float)$debt);
$pdo->prepare("UPDATE players SET money=0 WHERE id=:id")->execute([':id'=>(int)$pid]);
$a=0;$b=0;$c=0;$e=0;$nn=0;$sb=0;$tm=0;
foreach($comps as $ci){ createAuctionFromComponent($pdo,(int)$pid,$ci,'bankrupt'); $a+=count($ci['stations']); $b+=count($ci['edges']); $c+=count($ci['depots']); $e+=count($ci['trains']); $nn+=count($ci['nodes']); $sb+=count($ci['substations']); $tm+=count($ci['track_maint']??[]); }
if($comps){ logEvent('bankruptcy',(int)$pid,"{$nm} обанкротился: {$a} ст., {$nn} узл., {$b} пер., {$c} депо, {$e} поездов, {$sb} подстанций, {$tm} ПЧ ушли на рынок",['debt'=>$debt]); $ch=true; }
cacheReset();
if(playerStationsCount($world['data'],(int)$pid)===0&&playerDepotsCount($world['data'],(int)$pid)===0){
$s=$pdo->prepare("SELECT money,last_grant_at FROM players WHERE id=:id"); $s->execute([':id'=>(int)$pid]); $r=$s->fetch(PDO::FETCH_ASSOC);
if($r&&(int)$r['money']<RESTART_GRANT&&time()-(int)$r['last_grant_at']>RESTART_GRANT_COOLDOWN_SEC){ addMoney((int)$pid,RESTART_GRANT,'restart_grant',null,[]);
$pdo->prepare("UPDATE players SET last_grant_at=:t, grace_until_day=:g WHERE id=:id")->execute([':t'=>time(),':g'=>$day+$passed+GRACE_DAYS,':id'=>(int)$pid]);
logEvent('restart_grant',(int)$pid,"{$nm} получил грант на перезапуск: ".RESTART_GRANT." ₽",[]); } } }
return $ch; }
/* ---------- банкротство / аукционы ---------- */
function findComponents(array $vertices,array $edges):array{ $adj=[]; foreach($vertices as $v)$adj[(string)$v['id']]=[];
foreach($edges as $e){ $f=(string)($e['from']??''); $t=(string)($e['to']??''); if(!isset($adj[$f])||!isset($adj[$t]))continue; $adj[$f][]=$t; $adj[$t][]=$f; }
$seen=[]; $out=[]; foreach($adj as $sid=>$_){ if(isset($seen[$sid]))continue; $q=[$sid]; $comp=[];
while($q){ $v=array_shift($q); if(isset($seen[$v]))continue; $seen[$v]=true; $comp[]=$v; foreach($adj[$v] as $nb) if(!isset($seen[$nb]))$q[]=$nb; } $out[]=$comp; } return $out; }
function confiscateForDebt(array &$world,int $pid,float $debt):array{ $d=&$world['data']; $eco=economy($d);
$mySt=[];$myNd=[];$vset=[];
foreach($d['stations'] as $s) if((int)($s['owner_id']??0)===$pid&&empty($s['frozen'])){ $mySt[]=$s; $vset[(string)$s['id']]=true; }
foreach($d['nodes'] as $n) if((int)($n['owner_id']??0)===$pid&&empty($n['frozen'])){ $myNd[]=$n; $vset[(string)$n['id']]=true; }
if(!$mySt&&!$myNd)return [];
$myEd=[]; foreach($d['edges'] as $e) if((int)($e['owner_id']??0)===$pid&&empty($e['frozen']))$myEd[]=$e;
$myDp=[]; foreach($d['depots'] as $x) if((int)($x['owner_id']??0)===$pid&&empty($x['frozen']))$myDp[]=$x;
$mySb=[]; foreach(($d['substations']??[]) as $x) if((int)($x['owner_id']??0)===$pid&&empty($x['frozen']))$mySb[]=$x;
$myTm=[]; foreach(($d['track_maint']??[]) as $x) if((int)($x['owner_id']??0)===$pid&&empty($x['frozen']))$myTm[]=$x;
$myTr=[]; foreach($d['trains'] as $x) if((int)($x['owner_id']??0)===$pid&&empty($x['frozen']))$myTr[]=$x;
$protected=[]; $best=null;
foreach($eco['routes'] as $rid=>$info){ if((int)$info['owner_id']!==$pid||$info['status']!=='active')continue; $ok=false;
foreach($myDp as $x){ if(!in_array((string)$x['station_id'],$info['stops'],true))continue; foreach($myTr as $t) if((string)($t['depot_id']??'')===(string)$x['id']&&(string)($t['route_id']??'')===(string)$rid){ $ok=true; break 2; } }
if(!$ok)continue; if($best===null||$info['income']>$best['income'])$best=$info; }
if($best!==null){ foreach($best['stops'] as $sid)$protected[$sid]=true; foreach($best['path'] as $s){ $protected[(string)$s['from']]=true; $protected[(string)$s['to']]=true; } }
$verts=array_merge($mySt,$myNd); $adjEdges=array_values(array_filter($myEd,fn($e)=>isset($vset[(string)$e['from']])&&isset($vset[(string)$e['to']])));
$comps=findComponents($verts,$adjEdges); $groups=[];
foreach($comps as $comp){ $set=array_flip($comp); $prot=false; foreach($comp as $v) if(isset($protected[$v])){ $prot=true; break; } $groups[]=['set'=>$set,'ids'=>$comp,'protected'=>$prot,'edges'=>[]]; }
$orphan=['set'=>[],'ids'=>[],'protected'=>false,'edges'=>[]];
foreach($myEd as $e){ $placed=false; foreach($groups as &$g) if(isset($g['set'][(string)$e['from']])||isset($g['set'][(string)$e['to']])){ $g['edges'][]=$e; $placed=true; break; } unset($g); if(!$placed)$orphan['edges'][]=$e; }
if($orphan['edges'])$groups[]=$orphan;
$info=[];
foreach($groups as $g){ $set=$g['set'];
$st=array_values(array_filter($mySt,fn($s)=>isset($set[(string)$s['id']]))); $nd=array_values(array_filter($myNd,fn($s)=>isset($set[(string)$s['id']]))); $ed=$g['edges'];
$dp=array_values(array_filter($myDp,fn($x)=>isset($set[(string)($x['station_id']??'')])));
$sb=array_values(array_filter($mySb,fn($x)=>isset($set[(string)($x['station_id']??'')])));
$tm=array_values(array_filter($myTm,fn($x)=>isset($set[(string)($x['station_id']??'')])));
$dpIds=[]; foreach($dp as $x)$dpIds[(string)$x['id']]=true;
$tr=array_values(array_filter($myTr,fn($x)=>isset($dpIds[(string)($x['depot_id']??'')])));
$upk=0.0;$inc=0;
foreach($st as $s)$upk+=stationUpkeep($s)*(isset($eco['served_st'][$s['id']])?1.0:(isset($eco['passed_st'][$s['id']])?TRANSIT_UPKEEP_RATIO:CONSERVATION_RATIO));
foreach($nd as $n)$upk+=nodeUpkeep($n)*(isset($eco['served_nd'][$n['id']])?1.0:CONSERVATION_RATIO);
foreach($ed as $e)$upk+=edgeUpkeep($e)*(isset($eco['served_ed'][$e['id']])?1.0:CONSERVATION_RATIO);
foreach($dp as $x)$upk+=depotUpkeep($x)*(isset($eco['depot_served'][(string)$x['id']])?1.0:CONSERVATION_RATIO);
foreach($sb as $x)$upk+=substationUpkeep($x)*(isset($eco['powering_subs'][(string)$x['id']])?1.0:CONSERVATION_RATIO);
foreach($tm as $x)$upk+=trackMaintUpkeep($x)*(isset($eco['tm_serving_maint'][(string)$x['id']])?1.0:CONSERVATION_RATIO);
foreach($tr as $x)$upk+=trainUpkeep($x,isset($eco['trains_ready'][(string)$x['id']]));
$eids=[]; foreach($ed as $e)$eids[(string)$e['id']]=true;
foreach($eco['routes'] as $r){ if((int)$r['owner_id']!==$pid||$r['status']!=='active')continue; $all=true; foreach($r['edges'] as $x) if(!isset($eids[$x])){ $all=false; break; } if($all&&$r['edges']){ $inc+=$r['income']; $upk+=$r['upkeep']+$r['fee_total']; } }
$repl=0; foreach($st as $s)$repl+=stationReplacementCost($s); foreach($nd as $n)$repl+=nodeReplacementCost($n);
foreach($ed as $e){ $a=endpointById($d,(string)$e['from']); $b=endpointById($d,(string)$e['to']); if($a&&$b)$repl+=edgeReplacementCost($e,$a,$b); }
foreach($dp as $x)$repl+=depotReplacementCost($x); foreach($sb as $x)$repl+=substationReplacementCost($x); foreach($tm as $x)$repl+=trackMaintReplacementCost($x); foreach($tr as $x)$repl+=trainReplacementCost($x);
$info[]=['stations'=>$st,'nodes'=>$nd,'edges'=>$ed,'depots'=>$dp,'trains'=>$tr,'substations'=>$sb,'track_maint'=>$tm,'upkeep'=>$upk,'income'=>$inc,'net'=>$inc-$upk,'replacement'=>$repl,'protected'=>$g['protected']]; }
usort($info,function($a,$b){ $an=$a['net']<0; $bn=$b['net']<0; if($an&&!$bn)return -1; if(!$an&&$bn)return 1; if($an)return $a['net']<=>$b['net']; return $b['upkeep']<=>$a['upkeep']; });
$remU=array_sum(array_column($info,'upkeep')); $remI=array_sum(array_column($info,'income')); $removed=0.0; $take=[];
foreach($info as $ci){ if(!empty($ci['protected'])){ $remU-=$ci['upkeep']; $remI-=$ci['income']; continue; } $net=$remI-$remU; $gap=$net<0?(-$net)*FORECAST_DAYS_ON_CONFISCATE:0; if($removed>=$debt+$gap)break; $take[]=$ci; $removed+=$ci['upkeep']; $remU-=$ci['upkeep']; $remI-=$ci['income']; }
$rs=[];$rn=[];$re=[];$rd=[];$rt=[];$rb=[];$rm=[];
foreach($take as $ci){ foreach($ci['stations'] as $x)$rs[$x['id']]=true; foreach($ci['nodes'] as $x)$rn[$x['id']]=true; foreach($ci['edges'] as $x)$re[$x['id']]=true; foreach($ci['depots'] as $x)$rd[$x['id']]=true; foreach($ci['trains'] as $x)$rt[$x['id']]=true; foreach($ci['substations'] as $x)$rb[$x['id']]=true; foreach(($ci['track_maint']??[]) as $x)$rm[$x['id']]=true; }
$d['stations']=array_values(array_filter($d['stations'],fn($x)=>!isset($rs[$x['id']])));
$d['nodes']=array_values(array_filter($d['nodes'],fn($x)=>!isset($rn[$x['id']])));
$d['edges']=array_values(array_filter($d['edges'],fn($x)=>!isset($re[$x['id']])));
$d['depots']=array_values(array_filter($d['depots'],fn($x)=>!isset($rd[$x['id']])));
$d['trains']=array_values(array_filter($d['trains'],fn($x)=>!isset($rt[$x['id']])));
$d['substations']=array_values(array_filter(($d['substations']??[]),fn($x)=>!isset($rb[$x['id']])));
$d['track_maint']=array_values(array_filter(($d['track_maint']??[]),fn($x)=>!isset($rm[$x['id']])));
pruneOrphanNodes($d); cacheReset(); return $take; }
function createAuctionFromComponent(PDO $pdo,int $seller,array $ci,string $type='bankrupt'):int{ $repl=(int)($ci['replacement']??0); $po=max(1,(int)ceil($repl*AUCTION_PRICE_OWNER_RATIO)); $px=max(1,(int)ceil($repl*AUCTION_PRICE_OUTSIDER_RATIO));
$pdo->prepare("INSERT INTO auctions (seller_id,debt,price,price_owner,replacement_cost,stations,edges,depots,trains,nodes,substations,track_maint,created_at,expires_at,status,type) VALUES (:s,:d,:p,:po,:rc,:st,:ed,:dp,:tr,:nd,:sb,:tm,:c,:e,'active',:t)")
->execute([':s'=>$seller,':d'=>(int)ceil($ci['upkeep']??0),':p'=>$px,':po'=>$po,':rc'=>$repl,':st'=>json_encode($ci['stations']??[],JSON_UNESCAPED_UNICODE),':ed'=>json_encode($ci['edges']??[],JSON_UNESCAPED_UNICODE),':dp'=>json_encode($ci['depots']??[],JSON_UNESCAPED_UNICODE),':tr'=>json_encode($ci['trains']??[],JSON_UNESCAPED_UNICODE),':nd'=>json_encode($ci['nodes']??[],JSON_UNESCAPED_UNICODE),':sb'=>json_encode($ci['substations']??[],JSON_UNESCAPED_UNICODE),':tm'=>json_encode($ci['track_maint']??[],JSON_UNESCAPED_UNICODE),':c'=>date('c'),':e'=>date('c',time()+AUCTION_DURATION_SEC),':t'=>$type]);
return (int)$pdo->lastInsertId(); }
function freezeObjects(array &$d,array $sIds,array $eIds,array $dIds,array $tIds,array $nIds,array $bIds,array $mIds,int $aid):void{
foreach(['stations'=>$sIds,'edges'=>$eIds,'depots'=>$dIds,'trains'=>$tIds,'nodes'=>$nIds,'substations'=>$bIds,'track_maint'=>$mIds] as $k=>$ids){ if(!isset($d[$k]))continue; foreach($d[$k] as &$o) if(in_array((string)$o['id'],$ids,true)){ $o['frozen']=true; $o['auction_id']=$aid; } unset($o); } cacheReset(); }
function unfreezeAuctionObjects(array &$d,int $aid):bool{ $ch=false;
foreach(['stations','edges','depots','trains','nodes','substations','track_maint'] as $k){ if(!isset($d[$k]))continue; foreach($d[$k] as &$o) if(isset($o['auction_id'])&&(int)$o['auction_id']===$aid){ $o['frozen']=false; unset($o['auction_id']); $ch=true; } unset($o); } if($ch)cacheReset(); return $ch; }
function collectSaleSelection(array $d,int $pid,array $sIds,array $eIds,array $dIds,array $tIds,array $nIds,array $bIds,array $mIds,bool $strict):array{
$st=[];$ed=[];$dp=[];$tr=[];$nd=[];$sb=[];$tm=[];$repl=0;
foreach($d['stations'] as $s){ if(!in_array((string)$s['id'],$sIds,true))continue; if((int)($s['owner_id']??0)!==$pid){ if($strict)fail('не ваша станция',403); continue; }
if(!empty($s['is_building'])||!empty($s['upgrading'])){ if($strict)fail("станция «{$s['name']}»: стройка/улучшение",400); continue; }
if(!empty($s['frozen'])){ if($strict)fail('станция уже на продаже',409); continue; } $st[]=$s; $repl+=stationReplacementCost($s); }
foreach($d['nodes'] as $n){ if(!in_array((string)$n['id'],$nIds,true))continue; if((int)($n['owner_id']??0)!==$pid){ if($strict)fail('не ваш узел',403); continue; } if(!empty($n['frozen'])){ if($strict)fail('узел уже на продаже',409); continue; } $nd[]=$n; $repl+=nodeReplacementCost($n); }
foreach($d['edges'] as $e){ if(!in_array((string)$e['id'],$eIds,true))continue; if((int)($e['owner_id']??0)!==$pid){ if($strict)fail('не ваш перегон',403); continue; }
if(!empty($e['is_building'])||!empty($e['upgrading'])){ if($strict)fail('перегон строится/улучшается',400); continue; } if(!empty($e['frozen'])){ if($strict)fail('перегон уже на продаже',409); continue; }
$a=endpointById($d,(string)$e['from']); $b=endpointById($d,(string)$e['to']); $ed[]=$e; $repl+=($a&&$b)?edgeReplacementCost($e,$a,$b):(int)ceil((float)$e['length']); }
$dpSet=[];
foreach($d['depots'] as $x){ if(!in_array((string)$x['id'],$dIds,true))continue; if((int)($x['owner_id']??0)!==$pid){ if($strict)fail('не ваше депо',403); continue; }
if(!empty($x['is_building'])||!empty($x['upgrading'])){ if($strict)fail('депо строится/расширяется',400); continue; } if(!empty($x['frozen'])){ if($strict)fail('депо уже на продаже',409); continue; } $dp[]=$x; $dpSet[(string)$x['id']]=true; $repl+=depotReplacementCost($x); }
foreach($d['trains'] as $t){ $auto=isset($dpSet[(string)($t['depot_id']??'')]); if(!in_array((string)$t['id'],$tIds,true)&&!$auto)continue; if((int)($t['owner_id']??0)!==$pid){ if($strict&&!$auto)fail('не ваш поезд',403); continue; } if(!empty($t['frozen']))continue; if(!empty($t['is_building'])){ if($strict)fail('поезд ещё не поставлен',400); continue; } $tr[]=$t; $repl+=trainReplacementCost($t); }
foreach(($d['substations']??[]) as $x){ if(!in_array((string)$x['id'],$bIds,true))continue; if((int)($x['owner_id']??0)!==$pid){ if($strict)fail('не ваша подстанция',403); continue; } if(!empty($x['is_building'])){ if($strict)fail('подстанция ещё строится',400); continue; } if(!empty($x['frozen'])){ if($strict)fail('подстанция уже на продаже',409); continue; } $sb[]=$x; $repl+=substationReplacementCost($x); }
foreach(($d['track_maint']??[]) as $x){ if(!in_array((string)$x['id'],$mIds,true))continue; if((int)($x['owner_id']??0)!==$pid){ if($strict)fail('не ваша путевая часть',403); continue; } if(!empty($x['is_building'])){ if($strict)fail('ПЧ ещё строится',400); continue; } if(!empty($x['frozen'])){ if($strict)fail('ПЧ уже на продаже',409); continue; } $tm[]=$x; $repl+=trackMaintReplacementCost($x); }
if($strict&&(count($st)!==count($sIds)||count($ed)!==count($eIds)))fail('часть объектов не найдена',404);
return ['stations'=>$st,'edges'=>$ed,'depots'=>$dp,'trains'=>$tr,'nodes'=>$nd,'substations'=>$sb,'track_maint'=>$tm,'replacement'=>$repl]; }
function expireAuctions(PDO $pdo,array &$d,int $now):bool{ $ch=false;
$rows=$pdo->query("SELECT id,seller_id,stations,type,expires_at FROM auctions WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC); $u=$pdo->prepare("UPDATE auctions SET status='expired' WHERE id=:id");
foreach($rows as $r){ if(strtotime((string)$r['expires_at'])>=$now)continue; $pid=(int)$r['seller_id']; $nm=companyDisplayName($pid);
if($r['type']==='manual'){ if(unfreezeAuctionObjects($d,(int)$r['id']))$ch=true; logEvent('auction_expired',$pid,"Лот {$nm} истёк, объекты возвращены",[]); }
else{ $st=json_decode((string)$r['stations'],true)?:[]; logEvent('auction_expired',$pid,"Лот {$nm} истёк: ".count($st)." ст. удалено",[]); }
$u->execute([':id'=>(int)$r['id']]); } return $ch; }
/* ---------- декорация ---------- */
function decorateWorld(array $d,int $viewer):array{ $__t=microtime(true); $eco=economy($d); $idx=worldIndex($d); $pol=policiesAll(); $now=time();
$pe=$eco['powered_edges']; $ps=$eco['powering_subs']; $te=$eco['tm_served_edges']; $tmm=$eco['tm_serving_maint'];
$eAct=[];$eTot=[]; foreach($eco['routes'] as $ri){ foreach(($ri['edges']??[]) as $eid){ $eTot[$eid]=($eTot[$eid]??0)+1; if(($ri['status']??'')==='active')$eAct[$eid]=($eAct[$eid]??0)+1; } }
foreach($d['stations'] as &$s){ $id=(string)$s['id']; $s['served']=isset($eco['served_st'][$id]); $s['passed']=isset($eco['passed_st'][$id]);
$s['routes_used']=stationRoutesUsed($d,$id); $s['routes_limit']=stationRouteLimit($s); $s['wagon_limit']=stationWagonLimit($s); $s['tracks']=stationTracks($d,$id);
$s['income_percent']=getBuildingIncomePercent((int)($s['buildings']??0)); $s['color']=playerColor((int)($s['owner_id']??0));
$s['access_state']=accessState($pol,$s,$viewer,'stop'); $s['can_connect']=accessAllowed($pol,$s,$viewer,'connect'); $s['can_stop']=accessAllowed($pol,$s,$viewer,'stop');
$s['blocks_entry']=stationBlocksEntry($s); } unset($s);
foreach($d['nodes'] as &$n){ $id=(string)$n['id']; $n['served']=isset($eco['served_nd'][$id]); $n['edges_count']=nodeEdgesCount($d,$id); $n['color']=playerColor((int)($n['owner_id']??0)); $n['can_connect']=accessAllowed($pol,$n,$viewer,'connect'); $n['access_state']=accessState($pol,$n,$viewer,'connect'); } unset($n);
foreach($d['edges'] as &$e){ $eid=(string)$e['id']; $e['served']=isset($eco['served_ed'][$eid]);
$e['routes_active']=$eAct[$eid]??0; $e['routes_total']=$eTot[$eid]??0;
$e['powered']=!empty($e['electric'])&&(!empty($e['electric_legacy'])||isset($pe[$eid])); $e['electric_legacy']=!empty($e['electric_legacy']); $e['double_legacy']=!empty($e['double_legacy']);
$e['tm_served']=(!empty($e['double'])&&!empty($e['double_legacy']))||isset($te[$eid]); $e['speed']=edgeSpeedKmh($e,$pe);
$e['electric_label']=empty($e['electric'])?'не электрифицировано':(!empty($e['electric_legacy'])?'электрифицировано':($e['powered']?'электрифицировано · запитано':'электрифицировано · обесточено'));
$e['double_label']=empty($e['double'])?'1 путь':(!empty($e['double_legacy'])?'2 пути':($e['tm_served']?'2 пути · ПЧ':'2 пути · без ПЧ'));
$e['block_len']=edgeBlockLen($e); $e['blocks_n']=edgeBlocksN($e); $e['blocks_cost']=blocksCostFor($e);
$e['blocks_label']=empty($e['double'])?'1 блок (однопутка)':($e['blocks_n'].' бл. × '.$e['block_len'].' м');
$e['color']=playerColor((int)($e['owner_id']??0));
$e['can_use']=accessAllowed($pol,$e,$viewer,'trackage'); $e['access_state']=accessState($pol,$e,$viewer,'trackage');
$bs=[]; foreach(($e['bridges']??[]) as $br)$bs[]=decorateBridge($br); $e['bridges']=$bs;
$e['from_name']=endpointName($d,(string)$e['from']); $e['to_name']=endpointName($d,(string)$e['to']);
$e['from_kind']=(endpointById($d,(string)$e['from'])['_kind']??'?'); $e['to_kind']=(endpointById($d,(string)$e['to'])['_kind']??'?'); } unset($e);
foreach($d['depots'] as &$x){ $x['served']=isset($eco['depot_served'][(string)$x['id']]); $c=0; foreach($d['trains'] as $t) if((string)($t['depot_id']??'')===(string)$x['id'])$c++; $x['trains_count']=$c; $x['free_slots']=max(0,(int)$x['capacity']-$c); $x['color']=playerColor((int)($x['owner_id']??0)); } unset($x);
foreach($d['substations'] as &$x){ $x['powering']=isset($ps[(string)$x['id']]); $x['range_m']=SUBSTATION_RANGE_M; $x['color']=playerColor((int)($x['owner_id']??0)); $x['access_state']=accessState($pol,$x,$viewer,'connect'); $x['idle']=empty($x['powering'])&&empty($x['is_building']); } unset($x);
foreach($d['track_maint'] as &$x){ $x['serves']=isset($tmm[(string)$x['id']]); $x['range_m']=(int)($x['range_m']??TRACK_MAINT_RANGE_M); $x['color']=playerColor((int)($x['owner_id']??0)); $x['access_state']=accessState($pol,$x,$viewer,'connect'); $x['idle']=empty($x['serves'])&&empty($x['is_building']); } unset($x);
foreach($d['trains'] as &$t){ $rid=(string)($t['route_id']??''); $stt='idle';
if(!empty($t['is_building']))$stt='delivering'; elseif((int)($t['transit_end']??0)>$now)$stt='transit'; elseif(empty($t['depot_id']))$stt='orphan';
elseif($rid!==''&&isset($eco['routes'][$rid]))$stt=$eco['routes'][$rid]['status']==='active'?'running':($eco['routes'][$rid]['status']==='broken'?'broken':'idle');
if($stt==='running'&&trainModelTraction($t['model']??'diesel_line')==='electric'&&isset($eco['routes'][$rid])&&empty($eco['routes'][$rid]['fully_powered']))$stt='no_power';
$t['status']=$stt; $t['color']=playerColor((int)($t['owner_id']??0)); $m=normTrainModel($t['model']??'diesel_line'); $mm=trainModels()[$m];
[$pu,$gu]=trainCapacityUnits($t); $counts=[]; foreach(($t['wagons']??[]) as $w)$counts[$w]=($counts[$w]??0)+1;
$t['model']=$m; $t['model_label']=$mm['label']; $t['max_speed']=(int)$mm['max_speed']; $t['power']=(float)$mm['power']; $t['traction']=$mm['traction']; $t['accel']=(float)$mm['accel']; $t['img']=trainModelImg($m);
$t['capacity']=(int)($pu+$gu); $t['capacity_p']=(int)$pu; $t['capacity_g']=(int)$gu; $t['wagon_count']=trainWagonCount($t); $t['wagon_counts']=$counts; $t['health']=(int)($t['health']??100); } unset($t);
foreach($d['routes'] as &$r){ $rid=(string)$r['id']; $info=$eco['routes'][$rid]??null; $r['color']=playerColor((int)($r['owner_id']??0));
if(!$info){ $r['status']='broken'; $r['income']=0; continue; }
$r['status']=$info['status']; $r['reason']=$info['reason']; $r['km']=round($info['km']/1000,3); $r['income']=$info['income']; $r['upkeep']=round($info['upkeep'],2); $r['fee']=$info['fee_total'];
$r['speed']=$info['speed']; $r['line_speed']=$info['line_speed']; $r['speed_profile']=$info['speed_profile']; $r['bottlenecks']=$info['bottlenecks']; $r['edge_spans']=$info['edge_spans'];
$r['capacity']=$info['capacity']; $r['slots']=$info['slots']; $r['recommended']=$info['recommended']; $r['double_share']=round($info['double_share'],3); $r['elec_share']=round($info['elec_share'],3);
$r['min_class']=$info['min_class']; $r['edges']=$info['edges']; $r['path']=$info['path']; $r['owners']=$info['owners']; $r['transit_ids']=$info['transit'];
$r['wagon_limit']=$info['wagon_limit']; $r['fully_powered']=$info['fully_powered']; $r['dist_mult']=round($info['dist_mult'],3); $r['train_count']=0;
foreach($d['trains'] as $t) if((string)($t['route_id']??'')===$rid)$r['train_count']++;
$g=routeGeometry($d,$idx,$info); $r['geometry']=$g['geometry']; $r['stops_dist']=$g['stops_dist']; $r['transit']=$g['transit']; } unset($r);
$cm=companyMeta();
foreach($d['hqs'] as &$h){ $pid=(int)($h['owner_id']??0); $h['color']=playerColor($pid); $m=$cm[$pid]??null;
$h['display_name']=$m?(($m['company_name']!==''?$m['company_name']:$m['name'])):('игрок #'.$pid); $h['founder']=$m['founder']??''; $h['created_at']=$m['created_at']??''; $h['is_mine']=$pid===$viewer; } unset($h);
perfAdd('deco',$__t); return $d; }
function autoRouteForPlayer(array $d,array $idx,array $pol,int $pid,array $cand):?array{
$score=function(string $sid) use ($idx):float{ $s=$idx['st'][$sid]??null; if(!$s)return 0.0; return getBuildingIncomePercent((int)($s['buildings']??0))*stationClassMultiplier((int)$s['class']); };
$free=[]; foreach($cand as $sid){ $sid=(string)$sid; $s=$idx['st'][$sid]??null; if(!$s)continue; if(!empty($s['is_building'])||!empty($s['frozen']))continue; if(stationRoutesUsed($d,$sid)>=stationRouteLimit($s))continue; $free[]=$sid; }
if(count($free)<2)return null; $start=null; $bs=-1; foreach($free as $sid){ $v=$score($sid); if($v>$bs){$bs=$v;$start=$sid;} } if($start===null)return null;
$path=[$start]; $used=[$start=>true];
while(count($path)<MAX_ROUTE_STOPS){ $cur=$path[count($path)-1]; $next=null; $bl=INF;
foreach($free as $sid){ if(isset($used[$sid]))continue; $p=pathBetween($idx,$pol,$cur,$sid,$pid); if($p===null||!$p['steps'])continue; if($p['len']<$bl){$bl=$p['len'];$next=$sid;} }
if($next===null)break; $path[]=$next; $used[$next]=true; }
return count($path)>=2?$path:null; }
function createRouteRecord(array &$d,int $pid,string $name,array $stops,bool $loop,bool $auto):array{ $idx=worldIndex($d); $pol=policiesAll();
$info=routeCompute($idx,$pol,$stops,$loop,$pid,[]); if(!$info['ok'])fail('Маршрут невалиден: '.$info['reason'],400);
$sig=$info['stops']; sort($sig); $sig=implode('|',$sig);
foreach($d['routes'] as $r){ if((int)$r['owner_id']!==$pid)continue; $s2=array_map('strval',$r['stations']??[]); sort($s2); if(implode('|',$s2)===$sig)fail('такой маршрут уже существует',409); }
$owns=false;
foreach($info['stops'] as $sid){ $s=$idx['st'][$sid]; if((int)$s['owner_id']===$pid)$owns=true; $used=stationRoutesUsed($d,$sid); $lim=stationRouteLimit($s); if($used>=$lim)fail("станция «{$s['name']}»: исчерпаны слоты маршрутов ({$used}/{$lim}) — повысьте класс",400); }
if(!$owns)fail('маршрут должен включать хотя бы одну вашу станцию',403);
$id=newId('r'); $d['routes'][]=['id'=>$id,'name'=>$name,'owner_id'=>$pid,'stations'=>$info['stops'],'edges'=>$info['edges'],'loop'=>$loop,'color'=>playerColor($pid),'created_day'=>(int)metaGet('day_count'),'auto'=>$auto];
cacheReset(); return ['id'=>$id,'info'=>$info]; }
function snapEndpoint(array $d,float $lat,float $lng):?array{ $best=null; $bd=INF;
foreach(['stations','nodes'] as $k) foreach($d[$k] as $o){ $dd=haversineDistanceMeters($lat,$lng,(float)$o['lat'],(float)$o['lng']); if($dd<=NODE_SNAP_M&&$dd<$bd){ $bd=$dd; $best=$o+['_kind'=>$k==='stations'?'station':'node']; } } return $best; }
function resolveEndpoint(array $d,array $pol,int $pid,string $id,?array $point):array{
if($id!==''){ $o=endpointById($d,$id); if(!$o)fail('точка привязки не найдена: '.$id,404); if(!accessAllowed($pol,$o,$pid,'connect'))fail('«'.($o['name']??'объект').'»: владелец не разрешает подключаться',403); return ['mode'=>'existing','id'=>$id,'lat'=>(float)$o['lat'],'lng'=>(float)$o['lng'],'obj'=>$o,'snapped'=>false]; }
if($point===null)fail('нужно указать id или координаты конца перегона',400);
$snap=snapEndpoint($d,$point['lat'],$point['lng']);
if($snap){ if(!accessAllowed($pol,$snap,$pid,'connect'))fail('«'.($snap['name']??'объект').'»: владелец не разрешает подключаться',403); return ['mode'=>'existing','id'=>(string)$snap['id'],'lat'=>(float)$snap['lat'],'lng'=>(float)$snap['lng'],'obj'=>$snap,'snapped'=>true]; }
$z=isPointInForbiddenZone($point['lat'],$point['lng']); if($z)fail("Здесь нельзя строить: «{$z['name']}» — запретная зона",400);
return ['mode'=>'new','id'=>'','lat'=>$point['lat'],'lng'=>$point['lng'],'obj'=>null,'snapped'=>false]; }
function endpointPreview(array $d,array $r):array{ $o=$r['obj']; return ['mode'=>$r['mode'],'id'=>$r['id'],'lat'=>$r['lat'],'lng'=>$r['lng'],'snapped'=>!empty($r['snapped']),'kind'=>$o?($o['_kind']??'station'):'node','name'=>$o?(string)($o['name']??''):'','owner_id'=>$o?(int)($o['owner_id']??0):0]; }
function makeNode(array &$d,int $pid,float $lat,float $lng,string $name=''):string{ $id=newId('n'); $d['nodes'][]=['id'=>$id,'name'=>$name!==''?$name:(NODE_NAME_DEFAULT.' '.(playerNodesCount($d,$pid)+1)),'lat'=>$lat,'lng'=>$lng,'owner_id'=>$pid,'built_day'=>(int)metaGet('day_count'),'frozen'=>false,'access'=>'inherit','labelOffset'=>['x'=>8,'y'=>4]]; return $id; }
function loadEdgeForUpgrade(array $d,string $eid,array $me):array{ $i=idxEdge($d,$eid); if($i===null)fail('перегон не найден',404); $e=$d['edges'][$i];
if(!empty($e['is_building']))fail('перегон ещё строится',400); if(!empty($e['upgrading']))fail('перегон уже улучшается',400); if(!empty($e['frozen']))fail('перегон на продаже',400);
if((int)($e['owner_id']??0)!==(int)$me['id'])fail('не ваш перегон',403); $a=endpointById($d,(string)$e['from']); $b=endpointById($d,(string)$e['to']); if(!$a||!$b)fail('концы не найдены',404); return [$i,$e,$a,$b]; }
/* ================= API ================= */
$action=$_GET['action']??''; $isPost=($_SERVER['REQUEST_METHOD']??'')==='POST';
if($action==='login'&&$isPost){ $req=readJson(); $name=trim((string)($req['name']??'')); if($name===''||mb_strlen($name)>40)sendJson(['error'=>'bad name'],400);
$s=db()->prepare("SELECT * FROM players WHERE name=:n"); $s->execute([':n'=>$name]); $p=$s->fetch(PDO::FETCH_ASSOC);
if(!$p){ $day=(int)metaGet('day_count'); $col=freeCompanyColor();
db()->prepare("INSERT INTO players (name,money,created_at,grace_until_day,color) VALUES (:n,:m,:d,:g,:c)")->execute([':n'=>$name,':m'=>START_MONEY,':d'=>date('c'),':g'=>$day+GRACE_DAYS,':c'=>$col]);
$s=db()->prepare("SELECT * FROM players WHERE name=:n"); $s->execute([':n'=>$name]); $p=$s->fetch(PDO::FETCH_ASSOC);
policySave((int)$p['id'],['allow_trackage'=>0,'allow_stops'=>0,'allow_connect'=>0]); cacheReset(); logEvent('new_player',(int)$p['id'],"Новая компания: {$name}",['color'=>$col]); }
setcookie('pid',cookieSign((string)$p['id']),['expires'=>time()+86400*30,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
$p['color']=playerColor((int)$p['id']); sendJson(['ok'=>true,'player'=>augmentPlayer($p)]); }
if($action==='logout'){ setcookie('pid','',['expires'=>time()-3600,'path'=>'/']); sendJson(['ok'=>true]); }
if($action==='me'){ $p=currentPlayer(); if($p){ $p['color']=playerColor((int)$p['id']); $p=augmentPlayer($p); } sendJson(['player'=>$p]); }
if($action==='bridge_types')sendJson(['types'=>bridgeTypesPublic(),'default'=>BRIDGE_DEFAULT_TYPE]);
if($action==='train_models')sendJson(['models'=>trainModelsPublic(),'wagon_types'=>wagonTypesPublic(),'default'=>'diesel_line']);
if($action==='company_settings'){ $me=requirePlayer(); $pid=(int)$me['id']; $pol=policiesAll();
if($isPost){ $req=readJson(); $cur=policyFor($pol,$pid); foreach(['allow_trackage','allow_stops','allow_connect'] as $k) if(array_key_exists($k,$req))$cur[$k]=!empty($req[$k])?1:0; policySave($pid,$cur);
if(isset($req['color'])){ $c=(string)$req['color']; if(!in_array($c,COMPANY_COLORS,true))fail('цвет должен быть из палитры',400);
foreach(colorsAll() as $other=>$oc) if($other!==$pid&&strtolower($oc)===strtolower($c))fail('этот цвет уже занят другой компанией',409);
db()->prepare("UPDATE players SET color=:c WHERE id=:id")->execute([':c'=>$c,':id'=>$pid]); cacheReset(); logEvent('company_updated',$pid,companyDisplayName($pid).' сменил цвет компании',['color'=>$c]); }
$w=worldGet(); logEvent('company_updated',$pid,companyDisplayName($pid).' обновил настройки доступа',policyFor(policiesAll(),$pid));
sendJson(['ok'=>true,'policies'=>policyFor(policiesAll(),$pid),'color'=>playerColor($pid),'version'=>worldSave($w['data'])]); }
sendJson(['policies'=>policyFor($pol,$pid),'color'=>playerColor($pid),'palette'=>COMPANY_COLORS,'share'=>TRACTAGE_REVENUE_SHARE,'basis'=>TRACTAGE_SHARE_BASIS,'used_colors'=>colorsAll(),'companies'=>companiesPublic(worldGet()['data'])]); }
if($action==='update_company'&&$isPost){ $me=requirePlayer(); $q=readJson(); $cn=trim((string)($q['company_name']??'')); $fo=trim((string)($q['founder']??''));
if(mb_strlen($cn)>40||mb_strlen($fo)>40)sendJson(['error'=>'bad request'],400);
db()->prepare("UPDATE players SET company_name=:c, founder=:f WHERE id=:id")->execute([':c'=>$cn,':f'=>$fo,':id'=>(int)$me['id']]); cacheReset();
logEvent('company_updated',(int)$me['id'],companyDisplayName((int)$me['id']).' обновил карточку компании',['company_name'=>$cn,'founder'=>$fo]);
sendJson(['ok'=>true,'display_name'=>$cn!==''?$cn:(string)$me['name'],'company_name'=>$cn,'founder'=>$fo]); }
if($action==='move_hq'&&$isPost){ $me=requirePlayer(); $q=readJson(); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0);
if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400); $z=isPointInForbiddenZone($lat,$lng); if($z)fail("Здесь нельзя ставить штаб: «{$z['name']}»",400);
sendJson(runTx(function()use($me,$lat,$lng){ $w=worldGet(); $d=$w['data']; ensureHqs($d); $h=hqByOwner($d,(int)$me['id']); if(!$h)fail('штаб не найден — обновите страницу',404);
$i=idxHq($d,(string)$h['id']); $d['hqs'][$i]['lat']=$lat; $d['hqs'][$i]['lng']=$lng; $ver=worldSave($d,false);
logEvent('hq_moved',(int)$me['id'],companyDisplayName((int)$me['id']).' перенёс штаб',['lat'=>$lat,'lng'=>$lng]); return ['ok'=>true,'version'=>$ver]; })); }
if($action==='set_object_access'&&$isPost){ $me=requirePlayer(); $req=readJson(); $kind=(string)($req['kind']??'station'); $id=(string)($req['id']??''); $acc=normAccess($req['access']??'inherit');
$map=['station'=>'stations','node'=>'nodes','edge'=>'edges','depot'=>'depots','substation'=>'substations','track_maint'=>'track_maint'];
if(!isset($map[$kind])||$id==='')sendJson(['error'=>'bad request'],400); $k=$map[$kind];
$ver=runTx(function()use($me,$k,$id,$acc){ $w=worldGet(); $d=$w['data']; if(!isset($d[$k]))fail('объект не найден',404); $i=idxOf($d[$k],$id); if($i===null)fail('объект не найден',404);
if((int)$d[$k][$i]['owner_id']!==(int)$me['id'])fail('не ваш объект',403); $d[$k][$i]['access']=$acc; return worldSave($d); });
sendJson(['ok'=>true,'version'=>$ver,'access'=>$acc]); }
if($action==='load'){ $me=requirePlayer(); tickWorld(); $w=worldGet(); $d=$w['data']; if(ensureHqs($d)){ worldSave($d,false); $w=worldGet(); $d=$w['data']; }
$act=db()->query("SELECT id,type,stations,edges,depots,trains,nodes,substations,track_maint FROM auctions WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC);
$maps=['stations'=>[],'edges'=>[],'depots'=>[],'trains'=>[],'nodes'=>[],'substations'=>[],'track_maint'=>[]];
foreach($act as $a) foreach($maps as $k=>$_){ $items=json_decode((string)($a[$k]??'[]'),true)?:[]; foreach($items as $x) if(isset($x['id']))$maps[$k][$x['id']]=$a['type']; }
foreach($maps as $k=>$m){ if(!isset($d[$k]))continue; foreach($d[$k] as &$x) if(isset($m[$x['id']]))$x['auction_status']=$m[$x['id']]; unset($x); }
$d=decorateWorld($d,(int)$me['id']); $last=(int)metaGet('last_tick');
sendJson(['data'=>$d,'version'=>$w['version'],'tick_left'=>max(0,DAY_DURATION_SEC-(time()-$last)),'day'=>(int)metaGet('day_count'),'now'=>time(),
'bridge_types'=>bridgeTypesPublic(),'train_models'=>trainModelsPublic(),'wagon_types'=>wagonTypesPublic(),'economy'=>playerFinancials($d,(int)$me['id']),
'companies'=>companiesPublic($d),'my_policies'=>policyFor(policiesAll(),(int)$me['id']),'palette'=>COMPANY_COLORS,
'constants'=>['depot_cost'=>depotCostForPlayer($d,(int)$me['id']),'node_cost'=>COST_NODE,'substation_cost'=>substationCostForPlayer($d,(int)$me['id']),'substation_range_m'=>SUBSTATION_RANGE_M,
'track_maint_cost'=>trackMaintCostForPlayer($d,(int)$me['id']),'track_maint_range_m'=>TRACK_MAINT_RANGE_M,'wagons_per_class'=>WAGONS_PER_CLASS,'max_wagons'=>MAX_WAGONS_PER_TRAIN,'presets_max'=>PRESETS_MAX,
'route_income_base'=>ROUTE_INCOME_BASE,'trackage_share'=>TRACTAGE_REVENUE_SHARE,'route_slots_per_class'=>ROUTE_SLOTS_PER_CLASS,'class_limits_tracks'=>CLASS_LIMITS_TRACKS,
'grace_until_day'=>(int)($me['grace_until_day']??0),'node_snap_m'=>NODE_SNAP_M,'edge_speed_base'=>EDGE_SPEED_BASE,'edge_speed_electric'=>EDGE_SPEED_ELECTRIC,
'freight_break_even_km'=>FREIGHT_BREAK_EVEN_KM,'train_wear_per_day'=>TRAIN_WEAR_PER_REAL_DAY,'train_wear_real'=>true,'train_visible_scale_m'=>TRAIN_VISIBLE_SCALE_M,
'max_edge_len_m'=>MAX_EDGE_LEN_M,
'block_len_default'=>BLOCK_LEN_DEFAULT,'block_len_min'=>BLOCK_LEN_MIN,'block_len_max'=>BLOCK_LEN_MAX,'block_len_step'=>BLOCK_LEN_STEP,'cost_block'=>COST_BLOCK,
'station_block_max_class'=>STATION_BLOCK_MAX_CLASS,'block_deadlock_sec'=>BLOCK_DEADLOCK_SEC]]); }
if($action==='poll'){ $me=requirePlayer(); tickWorld(); $v=(int)db()->query("SELECT version FROM world WHERE id=1")->fetchColumn();
$last=(int)metaGet('last_tick'); $day=(int)metaGet('day_count');
$s=db()->prepare("SELECT money,name,grace_until_day,company_name FROM players WHERE id=:id"); $s->execute([':id'=>(int)$me['id']]); $f=$s->fetch(PDO::FETCH_ASSOC)?:['money'=>0,'name'=>'','grace_until_day'=>0,'company_name'=>''];
$w=worldGet(); $fin=playerFinancials($w['data'],(int)$me['id']); $grace=(int)$f['grace_until_day']>$day; $upk=$fin['upkeep']*($grace?GRACE_UPKEEP_RATIO:1.0); $net=$fin['income']-$upk;
$s=db()->prepare("SELECT COUNT(*) FROM auctions WHERE seller_id=:id AND status='active'"); $s->execute([':id'=>(int)$me['id']]);
$dn=(string)($f['company_name']??'')!==''?(string)$f['company_name']:(string)$f['name'];
sendJson(['version'=>$v,'tick_left'=>max(0,DAY_DURATION_SEC-(time()-$last)),'day'=>$day,'money'=>(int)$f['money'],'name'=>$f['name'],'display_name'=>$dn,'color'=>playerColor((int)$me['id']),
'income_per_day'=>(int)$fin['income'],'upkeep_per_day'=>(int)ceil($upk),'net_per_day'=>(int)floor($net),'tractage_in'=>(int)$fin['tractage_in'],'tractage_out'=>(int)$fin['tractage_out'],
'next_station_cost'=>stationCostForPlayer($w['data'],(int)$me['id']),'edge_cost_per_m'=>round(edgeCostPerMeterForPlayer($w['data'],(int)$me['id']),4),
'next_depot_cost'=>depotCostForPlayer($w['data'],(int)$me['id']),'next_substation_cost'=>substationCostForPlayer($w['data'],(int)$me['id']),'next_track_maint_cost'=>trackMaintCostForPlayer($w['data'],(int)$me['id']),'node_cost'=>COST_NODE,
'my_auctions'=>(int)$s->fetchColumn(),'grace_active'=>$grace,'days_to_bankrupt'=>$net<0?(int)floor((int)$f['money']/max(0.01,-$net)):null,'routes_active'=>$fin['routes_active'],'trains_running'=>$fin['trains_running'],'now'=>time()]); }
if($action==='players'){ requirePlayer(); $pol=policiesAll(); $cm=companyMeta(); $out=[];
foreach(db()->query("SELECT id,name,money,created_at FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $r){ $pid=(int)$r['id']; $m=$cm[$pid]??null;
$r['color']=playerColor($pid); $r['policies']=policyFor($pol,$pid); $r['company_name']=$m['company_name']??''; $r['founder']=$m['founder']??''; $r['display_name']=($m['company_name']??'')!==''?$m['company_name']:$r['name']; $out[]=$r; }
sendJson(['players'=>$out]); }
if($action==='rating'){ requirePlayer(); $w=worldGet(); $d=$w['data']; $rows=[];
foreach(db()->query("SELECT id,name,money FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC) as $p){ $pid=(int)$p['id']; $fin=playerFinancials($d,$pid);
$rows[]=['id'=>$pid,'name'=>$p['name'],'display_name'=>companyDisplayName($pid),'color'=>playerColor($pid),'money'=>(int)$p['money'],'income_per_day'=>(int)$fin['income'],'upkeep_per_day'=>(int)ceil($fin['upkeep']),'net_per_day'=>(int)floor($fin['net']),'stations'=>playerStationsCount($d,$pid),'routes'=>$fin['routes_active'],'trains'=>$fin['trains'],'tractage_in'=>(int)$fin['tractage_in'],'tractage_out'=>(int)$fin['tractage_out']]; }
usort($rows,fn($a,$b)=>$b['net_per_day']!==$a['net_per_day']?$b['net_per_day']<=>$a['net_per_day']:$b['money']<=>$a['money']); sendJson(['rating'=>$rows]); }
if($action==='history'){ requirePlayer(); $cd=(int)metaGet('day_count'); $pl=db()->query("SELECT id,name FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach($pl as &$p){ $p['color']=playerColor((int)$p['id']); $p['display_name']=companyDisplayName((int)$p['id']); } unset($p);
$s=db()->prepare("SELECT player_id,day,income,upkeep,net FROM daily_stats WHERE day>=:d ORDER BY day ASC"); $s->execute([':d'=>$cd-365]);
sendJson(['history'=>$s->fetchAll(PDO::FETCH_ASSOC),'players'=>$pl,'currentDay'=>$cd]); }
if($action==='company_history'){ requirePlayer(); $cd=(int)metaGet('day_count'); $w=worldGet(); $d=$w['data']; $pl=db()->query("SELECT id,name FROM players ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$s=db()->prepare("SELECT player_id,day,company_value,stations,edge_km,depots,trains,routes,nodes,substations,track_maint FROM company_snapshots WHERE day>=:d ORDER BY day ASC"); $s->execute([':d'=>$cd-365]);
$by=[]; $days=[]; foreach($s->fetchAll(PDO::FETCH_ASSOC) as $r){ $by[(int)$r['player_id']][(int)$r['day']]=$r; $days[(int)$r['day']]=true; } ksort($days); $days=array_keys($days); $res=[];
foreach($pl as $p){ $pid=(int)$p['id']; $cv=playerCompanyValue($d,$pid); $b=db()->prepare("SELECT money FROM players WHERE id=:id"); $b->execute([':id'=>$pid]); $bal=(int)$b->fetchColumn(); $h=[];
foreach($days as $dd){ $r=$by[$pid][$dd]??null;
$h[]=$r?['day'=>$dd,'company_value'=>(int)$r['company_value'],'stations'=>(int)$r['stations'],'edge_km'=>(float)$r['edge_km'],'depots'=>(int)($r['depots']??0),'trains'=>(int)($r['trains']??0),'routes'=>(int)($r['routes']??0),'nodes'=>(int)($r['nodes']??0),'substations'=>(int)($r['substations']??0),'track_maint'=>(int)($r['track_maint']??0)]
:['day'=>$dd,'company_value'=>null,'stations'=>null,'edge_km'=>null,'depots'=>null,'trains'=>null,'routes'=>null,'nodes'=>null,'substations'=>null,'track_maint'=>null]; }
$res[]=['id'=>$pid,'name'=>$p['name'],'display_name'=>companyDisplayName($pid),'color'=>playerColor($pid),'company_value'=>$cv['assets']+$bal,'stations'=>$cv['stations'],'nodes'=>$cv['nodes'],'edge_km'=>$cv['edge_km'],'depots'=>$cv['depots'],'trains'=>$cv['trains'],'substations'=>$cv['substations'],'track_maint'=>$cv['track_maint'],'balance'=>$bal,'history'=>$h]; }
sendJson(['players'=>$res,'days'=>$days,'currentDay'=>$cd]); }
if($action==='events'){ requirePlayer(); sendJson(['events'=>db()->query("SELECT id,kind,player_id,text,created_at FROM events ORDER BY id DESC LIMIT 40")->fetchAll(PDO::FETCH_ASSOC)]); }
if($action==='chat_get'){ requirePlayer(); $s=db()->prepare("SELECT c.id,c.player_id,c.text,c.created_at,p.name AS player_name FROM chat_messages c LEFT JOIN players p ON p.id=c.player_id ORDER BY c.id DESC LIMIT 50"); $s->execute(); $rows=array_reverse($s->fetchAll(PDO::FETCH_ASSOC));
foreach($rows as &$r){ $r['color']=playerColor((int)$r['player_id']); $r['player_name']=companyDisplayName((int)$r['player_id']); } unset($r); sendJson(['messages'=>$rows]); }
if($action==='chat_send'&&$isPost){ $me=requirePlayer(); $t=trim((string)(readJson()['text']??'')); if($t===''||mb_strlen($t)>300)sendJson(['error'=>'bad text'],400);
db()->prepare("INSERT INTO chat_messages (player_id,text,created_at) VALUES (:p,:t,:d)")->execute([':p'=>(int)$me['id'],':t'=>$t,':d'=>date('c')]); sendJson(['ok'=>true]); }
if($action==='auctions'){ $me=requirePlayer(); tickWorld(); $out=[];
foreach(db()->query("SELECT * FROM auctions WHERE status='active' ORDER BY expires_at ASC")->fetchAll(PDO::FETCH_ASSOC) as $r){
$st=json_decode((string)$r['stations'],true)?:[]; $ed=json_decode((string)$r['edges'],true)?:[]; $dp=json_decode((string)($r['depots']??'[]'),true)?:[]; $tr=json_decode((string)($r['trains']??'[]'),true)?:[];
$nd=json_decode((string)($r['nodes']??'[]'),true)?:[]; $sb=json_decode((string)($r['substations']??'[]'),true)?:[]; $tm=json_decode((string)($r['track_maint']??'[]'),true)?:[];
$mine=(int)$r['seller_id']===(int)$me['id']; $prev=array_map(fn($s)=>['name'=>$s['name']??'?','lat'=>$s['lat']??0,'lng'=>$s['lng']??0],array_slice($st,0,3));
$out[]=['id'=>(int)$r['id'],'seller_id'=>(int)$r['seller_id'],'seller_color'=>playerColor((int)$r['seller_id']),'debt'=>(int)$r['debt'],'price'=>(int)$r['price'],'price_owner'=>(int)$r['price_owner'],'replacement_cost'=>(int)$r['replacement_cost'],
'is_mine'=>$mine,'my_price'=>$mine?(int)$r['price_owner']:(int)$r['price'],'stations_count'=>count($st),'edges_count'=>count($ed),'depots_count'=>count($dp),'trains_count'=>count($tr),'nodes_count'=>count($nd),'substations_count'=>count($sb),'track_maint_count'=>count($tm),
'stations_preview'=>$prev,'type'=>$r['type'],'expires_at'=>$r['expires_at'],'seconds_left'=>max(0,strtotime((string)$r['expires_at'])-time())]; }
sendJson(['auctions'=>$out]); }
if($action==='get_sale_quote'){ $me=requirePlayer(); $q=readJson(); $w=worldGet();
$sel=collectSaleSelection($w['data'],(int)$me['id'],idList($q['station_ids']??[]),idList($q['edge_ids']??[]),idList($q['depot_ids']??[]),idList($q['train_ids']??[]),idList($q['node_ids']??[]),idList($q['substation_ids']??[]),idList($q['track_maint_ids']??[]),false);
$m=max(1,$sel['replacement']); sendJson(['min_price'=>$m,'max_price'=>$m*5]); }
if($action==='list_for_sale'&&$isPost){ $me=requirePlayer(); $q=readJson();
$sI=idList($q['station_ids']??[]);$eI=idList($q['edge_ids']??[]);$dI=idList($q['depot_ids']??[]);$tI=idList($q['train_ids']??[]);$nI=idList($q['node_ids']??[]);$bI=idList($q['substation_ids']??[]);$mI=idList($q['track_maint_ids']??[]); $price=(int)($q['price']??0);
if(!$sI&&!$eI&&!$dI&&!$tI&&!$nI&&!$bI&&!$mI)sendJson(['error'=>'ничего не выбрано'],400);
sendJson(runTx(function()use($me,$sI,$eI,$dI,$tI,$nI,$bI,$mI,$price){ $pdo=db(); $w=worldGet(); $d=$w['data'];
$sel=collectSaleSelection($d,(int)$me['id'],$sI,$eI,$dI,$tI,$nI,$bI,$mI,true); $r=$sel['replacement']; if($r<=0)fail('стоимость не рассчитана',400);
if($price<$r||$price>$r*5)fail("цена должна быть от {$r} до ".($r*5)." ₽",400);
$pdo->prepare("INSERT INTO auctions (seller_id,debt,price,price_owner,replacement_cost,stations,edges,depots,trains,nodes,substations,track_maint,created_at,expires_at,status,type) VALUES (:s,0,:p,:po,:rc,:st,:ed,:dp,:tr,:nd,:sb,:tm,:c,:e,'active','manual')")
->execute([':s'=>(int)$me['id'],':p'=>$price,':po'=>$price,':rc'=>$r,':st'=>json_encode($sel['stations'],JSON_UNESCAPED_UNICODE),':ed'=>json_encode($sel['edges'],JSON_UNESCAPED_UNICODE),':dp'=>json_encode($sel['depots'],JSON_UNESCAPED_UNICODE),':tr'=>json_encode($sel['trains'],JSON_UNESCAPED_UNICODE),':nd'=>json_encode($sel['nodes'],JSON_UNESCAPED_UNICODE),':sb'=>json_encode($sel['substations'],JSON_UNESCAPED_UNICODE),':tm'=>json_encode($sel['track_maint'],JSON_UNESCAPED_UNICODE),':c'=>date('c'),':e'=>date('c',time()+AUCTION_DURATION_SEC)]);
$aid=(int)$pdo->lastInsertId();
freezeObjects($d,array_column($sel['stations'],'id'),array_column($sel['edges'],'id'),array_column($sel['depots'],'id'),array_column($sel['trains'],'id'),array_column($sel['nodes'],'id'),array_column($sel['substations'],'id'),array_column($sel['track_maint'],'id'),$aid);
worldSave($d); return ['ok'=>true,'auction_id'=>$aid]; })); }
if($action==='cancel_sale'&&$isPost){ $me=requirePlayer(); $aid=(int)(readJson()['auction_id']??0); if($aid<=0)sendJson(['error'=>'bad request'],400);
runTx(function()use($me,$aid){ $pdo=db(); $s=$pdo->prepare("SELECT id FROM auctions WHERE id=:id AND seller_id=:s AND status='active' AND type='manual'"); $s->execute([':id'=>$aid,':s'=>(int)$me['id']]); if(!$s->fetchColumn())fail('лот не найден',404);
$pdo->prepare("UPDATE auctions SET status='cancelled' WHERE id=:id")->execute([':id'=>$aid]); $w=worldGet(); $d=$w['data']; if(unfreezeAuctionObjects($d,$aid))worldSave($d);
logEvent('auction_cancelled',(int)$me['id'],companyDisplayName((int)$me['id'])." отменил продажу лота #{$aid}",[]); }); sendJson(['ok'=>true]); }
if($action==='buy_auction'&&$isPost){ $me=requirePlayer(); $aid=(int)(readJson()['auction_id']??0); if($aid<=0)sendJson(['error'=>'bad request'],400); tickWorld();
sendJson(runTx(function()use($me,$aid){ $pdo=db(); $s=$pdo->prepare("SELECT * FROM auctions WHERE id=:id AND status='active'"); $s->execute([':id'=>$aid]); $a=$s->fetch(PDO::FETCH_ASSOC); if(!$a)fail('лот не найден',404);
if(strtotime((string)$a['expires_at'])<time())fail('лот истёк',410); $own=(int)$a['seller_id']===(int)$me['id']; $type=(string)$a['type']; if($type==='manual'&&$own)fail('это ваш лот — используйте «Отменить»',400);
$price=$own?(int)$a['price_owner']:(int)$a['price']; $seller=(int)$a['seller_id']; $buyer=(int)$me['id'];
chargePlayer($buyer,$price,'buy_auction',$seller,['auction_id'=>$aid]); if(!$own)addMoney($seller,$price,'auction_compensation',$buyer,['auction_id'=>$aid]);
$w=worldGet(); $d=$w['data'];
foreach(['stations','edges','depots','trains','nodes','substations','track_maint'] as $k){ $items=json_decode((string)($a[$k]??'[]'),true)?:[]; if(!isset($d[$k]))$d[$k]=[];
foreach($items as $obj){ $i=idxOf($d[$k],(string)$obj['id']);
if($i!==null){ $d[$k][$i]['owner_id']=$buyer; $d[$k][$i]['frozen']=false; unset($d[$k][$i]['auction_id']); if($k==='trains'){ $d[$k][$i]['route_id']=null; $d[$k][$i]['model']=normTrainModel($d[$k][$i]['model']??'diesel_line'); } }
elseif($type==='bankrupt'){ $obj['owner_id']=$buyer; $obj['frozen']=false; unset($obj['auction_id']); if(isset($obj['health'])){ $obj['health']=100; $obj['health_pending']=0.0; } if($k==='trains'){ $obj['route_id']=null; $obj['model']=normTrainModel($obj['model']??'diesel_line'); } $d[$k][]=$obj; } } }
worldSave($d); $pdo->prepare("UPDATE auctions SET status='sold' WHERE id=:id")->execute([':id'=>$aid]);
logEvent('auction_sold',$buyer,companyDisplayName($buyer).' '.($own?'выкупил свой лот':'купил лот')." за {$price} ₽",['price'=>$price]);
return ['ok'=>true,'price'=>$price,'is_owner'=>$own]; })); }
if($action==='check_osm_station'){ requirePlayer(); $q=readJson(); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0); if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400); sendJson(['discount'=>getOsmStationDiscount($lat,$lng)?OSM_DISCOUNT:0]); }
if($action==='check_osm_station_buildings'){ requirePlayer(); $q=readJson(); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0); if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400);
$c=countNearbyBuildings($lat,$lng,500); sendJson(['discount'=>getOsmStationDiscount($lat,$lng)?OSM_DISCOUNT:0,'buildings'=>$c,'income_percent'=>getBuildingIncomePercent($c),'forbidden_zone'=>isPointInForbiddenZone($lat,$lng)['name']??null]); }
if($action==='check_osm_buildings'){ requirePlayer(); $q=readJson(); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0); $r=(float)($q['radius']??500); if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400); sendJson(['count'=>countNearbyBuildings($lat,$lng,$r),'radius'=>$r]); }
if($action==='build_station'&&$isPost){ $me=requirePlayer(); $q=readJson(); $name=trim((string)($q['name']??'')); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0);
if($name===''||mb_strlen($name)>60)sendJson(['error'=>'bad name'],400); if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400);
$z=isPointInForbiddenZone($lat,$lng); if($z)fail("Здесь нельзя строить: «{$z['name']}»",400); $osm=getOsmStationDiscount($lat,$lng); $bl=countNearbyBuildings($lat,$lng,500);
sendJson(runTx(function()use($me,$name,$lat,$lng,$osm,$bl){ $w=worldGet(); $d=$w['data']; $cost=stationCostForPlayer($d,(int)$me['id']); if($osm)$cost=(int)ceil($cost*(1-OSM_DISCOUNT));
chargePlayer((int)$me['id'],$cost,'build_station',null,['name'=>$name]); $id=newId('s'); $end=time()+BUILD_TIME_STATION*DAY_DURATION_SEC;
$d['stations'][]=['id'=>$id,'name'=>$name,'lat'=>$lat,'lng'=>$lng,'radius'=>stationRadiusForClass(1),'owner_id'=>(int)$me['id'],'labelOffset'=>['x'=>10,'y'=>4],'color'=>null,'railway'=>'','class'=>1,'capacity'=>4,'built_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'build_type'=>'station','upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,'frozen'=>false,'health'=>100,'buildings'=>$bl,'access'=>'inherit'];
$ver=worldSave($d); logEvent('station_built',(int)$me['id'],"Начато строительство станции «{$name}»",['station_id'=>$id]);
if(playerStationsCount($d,(int)$me['id'])===10)logEvent('station_10',(int)$me['id'],companyDisplayName((int)$me['id']).' достиг 10 станций!',[]);
return ['ok'=>true,'version'=>$ver,'cost'=>$cost,'build_end'=>$end,'osm_discount'=>$osm,'buildings'=>$bl,'income_percent'=>getBuildingIncomePercent($bl)]; })); }
if($action==='move_label'&&$isPost){ $me=requirePlayer(); $q=readJson(); $id=(string)($q['id']??$q['station_id']??''); $kind=(string)($q['kind']??'station'); $x=(float)($q['x']??0); $y=(float)($q['y']??0);
$map=['station'=>'stations','node'=>'nodes','depot'=>'depots','substation'=>'substations','track_maint'=>'track_maint','hq'=>'hqs'];
if($id===''||!isset($map[$kind])||!is_finite($x)||!is_finite($y))sendJson(['error'=>'bad request'],400); $k=$map[$kind];
$ver=runTx(function()use($me,$k,$id,$x,$y){ $w=worldGet(); $d=$w['data']; if(!isset($d[$k]))fail('объект не найден',404); $i=idxOf($d[$k],$id); if($i===null)fail('объект не найден',404);
if((int)$d[$k][$i]['owner_id']!==(int)$me['id'])fail('не ваш объект',403); $d[$k][$i]['labelOffset']=['x'=>round($x,1),'y'=>round($y,1)]; return worldSave($d,false); });
sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='rename_station'&&$isPost){ $me=requirePlayer(); $q=readJson(); $sid=(string)($q['station_id']??''); $nm=trim((string)($q['name']??'')); if($sid===''||$nm===''||mb_strlen($nm)>60)sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$sid,$nm){ $w=worldGet(); $d=$w['data']; $i=idxStation($d,$sid); if($i===null)fail('станция не найдена',404); if((int)$d['stations'][$i]['owner_id']!==(int)$me['id'])fail('не ваша станция',403); if(!empty($d['stations'][$i]['frozen']))fail('станция на продаже',400); $d['stations'][$i]['name']=$nm; return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='upgrade_station'&&$isPost){ $me=requirePlayer(); $sid=(string)(readJson()['station_id']??''); if($sid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$sid){ $w=worldGet(); $d=$w['data']; $i=idxStation($d,$sid); if($i===null)fail('станция не найдена',404); $s=$d['stations'][$i];
if((int)$s['owner_id']!==(int)$me['id'])fail('не ваша станция',403); if(!empty($s['is_building']))fail('станция ещё строится',400); if(!empty($s['upgrading']))fail('станция уже улучшается',400); if(!empty($s['frozen']))fail('станция на продаже',400);
$c=(int)$s['class']; if($c>=MAX_CLASS)fail('максимальный класс',400); $cost=stationUpgradeCost($c); chargePlayer((int)$me['id'],$cost,'upgrade_station',null,['station_id'=>$sid]);
$end=time()+BUILD_TIME_CLASS_UPGRADE*$c*DAY_DURATION_SEC; $d['stations'][$i]['upgrading']=true; $d['stations'][$i]['upgrade_type']='class'; $d['stations'][$i]['upgrade_class']=$c+1; $d['stations'][$i]['upgrade_end']=$end; $ver=worldSave($d);
if(in_array($c+1,[5,10],true))logEvent('big_build',(int)$me['id'],companyDisplayName((int)$me['id'])." качает «{$s['name']}» до класса ".($c+1),[]);
return ['ok'=>true,'version'=>$ver,'cost'=>$cost,'class'=>$c+1,'build_end'=>$end,'routes_limit'=>($c+1)*ROUTE_SLOTS_PER_CLASS,'wagon_limit'=>($c+1)*WAGONS_PER_CLASS,'blocks_entry'=>($c+1)<=STATION_BLOCK_MAX_CLASS]; })); }
if($action==='repair_station'&&$isPost){ $me=requirePlayer(); $q=readJson(); $sid=(string)($q['station_id']??''); $ml=isset($q['lat'])&&is_numeric($q['lat'])?(float)$q['lat']:null; $mg=isset($q['lng'])&&is_numeric($q['lng'])?(float)$q['lng']:null; if($sid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$sid,$ml,$mg){ $w=worldGet(); $d=$w['data']; $i=idxStation($d,$sid); if($i===null)fail('станция не найдена',404); $s=$d['stations'][$i];
if((int)$s['owner_id']!==(int)$me['id'])fail('не ваша станция',403); if(!empty($s['frozen'])||!empty($s['is_building']))fail('станция недоступна',400);
$h=(int)($s['health']??100); if($h>=100)fail('станция в идеальном состоянии',400);
$free=($ml!==null&&$mg!==null&&haversineDistanceMeters($ml,$mg,(float)$s['lat'],(float)$s['lng'])<=REPAIR_FREE_RADIUS_M); $cost=0;
if(!$free){ $cost=(int)ceil(stationReplacementCost($s)*REPAIR_COST_RATIO); chargePlayer((int)$me['id'],$cost,'repair_station',null,['station_id'=>$sid]); }
$d['stations'][$i]['health']=100; $d['stations'][$i]['health_pending']=0.0;
logEvent('station_repaired',(int)$me['id'],companyDisplayName((int)$me['id'])." починил «{$s['name']}» (".($free?'бесплатно':"{$cost} ₽").")",[]); worldSave($d);
return ['ok'=>true,'cost'=>$cost,'free'=>$free,'from_health'=>$h]; })); }
if($action==='delete_station'&&$isPost){ $me=requirePlayer(); $sid=(string)(readJson()['station_id']??''); if($sid==='')sendJson(['error'=>'bad request'],400);
runTx(function()use($me,$sid){ $w=worldGet(); $d=$w['data']; $i=idxStation($d,$sid); if($i===null)fail('станция не найдена',404); $s=$d['stations'][$i];
if((int)$s['owner_id']!==(int)$me['id'])fail('не ваша станция',403); if(!empty($s['frozen']))fail('станция на продаже',400);
$ne=0; foreach($d['edges'] as $e) if($e['from']===$sid||$e['to']===$sid)$ne++;
$d['edges']=array_values(array_filter($d['edges'],fn($e)=>$e['from']!==$sid&&$e['to']!==$sid));
foreach($d['depots'] as &$x) if((string)$x['station_id']===$sid)$x['station_id']=null; unset($x);
foreach($d['substations'] as &$x) if((string)($x['station_id']??'')===$sid)$x['station_id']=null; unset($x);
foreach($d['track_maint'] as &$x) if((string)($x['station_id']??'')===$sid)$x['station_id']=null; unset($x);
array_splice($d['stations'],$i,1); $orph=pruneOrphanNodes($d); $ref=(int)ceil(stationReplacementCost($s)*REFUND_RATIO);
if($ref>0)addMoney((int)$me['id'],$ref,'delete_station_refund',null,['station_id'=>$sid]);
logEvent('deleted',(int)$me['id'],companyDisplayName((int)$me['id'])." снёс «{$s['name']}» (возврат {$ref} ₽, перегонов: {$ne}, узлов: {$orph})",[]); worldSave($d); }); sendJson(['ok'=>true]); }
if($action==='sell_station'&&$isPost){ $me=requirePlayer(); $q=readJson(); $sid=(string)($q['station_id']??''); $price=(int)($q['price']??0); $buyer=(int)($q['buyer_id']??0);
if($sid===''||$price<=0||$buyer<=0||$buyer===(int)$me['id'])sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$sid,$price,$buyer){ $w=worldGet(); $d=$w['data']; $i=idxStation($d,$sid); if($i===null)fail('станция не найдена',404); $s=$d['stations'][$i];
if((int)$s['owner_id']!==(int)$me['id'])fail('не ваша станция',403); if(!empty($s['is_building'])||!empty($s['upgrading'])||!empty($s['frozen']))fail('станцию нельзя продать сейчас',400);
$st=db()->prepare("SELECT id FROM players WHERE id=:id"); $st->execute([':id'=>$buyer]); if(!$st->fetchColumn())fail('покупатель не найден',404);
chargePlayer($buyer,$price,'buy',(int)$me['id'],['station_id'=>$sid],'у покупателя нет денег'); addMoney((int)$me['id'],$price,'sell',$buyer,['station_id'=>$sid]);
$d['stations'][$i]['owner_id']=$buyer; return worldSave($d); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='quote_edge'){ $me=requirePlayer(); $q=readJson(); $w=worldGet(); $d=$w['data']; $pol=policiesAll(); $pid=(int)$me['id'];
$A=resolveEndpoint($d,$pol,$pid,(string)($q['from']??''),parsePoint($q['from_point']??null)); $B=resolveEndpoint($d,$pol,$pid,(string)($q['to']??''),parsePoint($q['to_point']??null));
$points=parsePoints($q['points']??[]); $sel=is_array($q['bridges']??null)?$q['bridges']:[]; $double=!empty($q['double']); $blen=normBlockLen($q['block_len']??BLOCK_LEN_DEFAULT);
$a=['id'=>$A['id'],'lat'=>$A['lat'],'lng'=>$A['lng']]; $b=['id'=>$B['id'],'lat'=>$B['lat'],'lng'=>$B['lng']]; $length=pathLength($a,$b,$points);
$base=(int)ceil($length*edgeCostPerMeterForPlayer($d,$pid)*($double?2:1)); $cov=edgeOsmCoverage($a,$b,$points); $disc=round(OSM_EDGE_DISCOUNT*$cov,4); $discounted=(int)ceil($base*(1-$disc));
$obs=checkOsmObstacles($a,$b,$points); $obsCost=obstaclesCost($obs['obstacles']); $cross=computeEdgeCrossings($d,$a,$b,$points);
$br=applyBridgeSelection($cross['bridges'],$sel); $ov=applyBridgeSelection($cross['overpasses'],$sel); $brC=crossingsCost($br); $ovC=crossingsCost($ov);
$newNodes=($A['mode']==='new'?1:0)+($B['mode']==='new'?1:0); $nodeC=$newNodes*COST_NODE;
$bn=blocksNFor($length,$double,$blen); $blockC=$bn*COST_BLOCK;
$dup=($A['mode']==='existing'&&$B['mode']==='existing')?(bool)array_filter($d['edges'],fn($e)=>((string)$e['from']===$A['id']&&(string)$e['to']===$B['id'])||((string)$e['from']===$B['id']&&(string)$e['to']===$A['id'])):false;
$zone=doesEdgeCrossForbiddenZone($a,$b,$points); $err=null;
if($length<MIN_EDGE_LEN_M)$err='перегон слишком короткий (минимум '.MIN_EDGE_LEN_M.' м)';
elseif($length>MAX_EDGE_LEN_M)$err='перегон слишком длинный (максимум 20 км) — поставьте промежуточный узел или станцию';
elseif($dup)$err='между этими точками перегон уже есть'; elseif($zone)$err='запретная зона: '.$zone['name']; elseif(!empty($cross['blocked']))$err=$cross['blocked'][0]['reason'];
sendJson(['length'=>$length,'cost_base'=>$base,'cost_discount'=>$discounted,'coverage'=>$cov,'discount_pct'=>$disc,'obstacles'=>$obs['obstacles'],'obstacles_cost'=>$obsCost,'bridges'=>$br,'overpasses'=>$ov,'blocked'=>$cross['blocked'],
'bridges_cost'=>$brC,'overpasses_cost'=>$ovC,'nodes_new'=>$newNodes,'node_cost'=>$nodeC,'block_len'=>$blen,'blocks_n'=>$bn,'blocks_cost'=>$blockC,'cost_block'=>COST_BLOCK,
'endpoints'=>['from'=>endpointPreview($d,$A),'to'=>endpointPreview($d,$B)],
'bridge_types'=>bridgeTypesPublic(),'default_bridge_type'=>BRIDGE_DEFAULT_TYPE,'speed_preview'=>EDGE_SPEED_BASE,'forbidden_zone'=>$zone['name']??null,'duplicate'=>$dup,'error'=>$err,'final_cost'=>$discounted+$obsCost+$brC+$ovC+$nodeC+$blockC]); }
if($action==='build_edge'&&$isPost){ $me=requirePlayer(); $q=readJson(); $points=parsePoints($q['points']??[]); $double=!empty($q['double']); $sel=is_array($q['bridges']??null)?$q['bridges']:[]; $blen=normBlockLen($q['block_len']??BLOCK_LEN_DEFAULT);
$w0=worldGet(); $d0=$w0['data']; $pol=policiesAll(); $pid=(int)$me['id'];
$A=resolveEndpoint($d0,$pol,$pid,(string)($q['from']??''),parsePoint($q['from_point']??null)); $B=resolveEndpoint($d0,$pol,$pid,(string)($q['to']??''),parsePoint($q['to_point']??null));
$a=['id'=>$A['id'],'lat'=>$A['lat'],'lng'=>$A['lng']]; $b=['id'=>$B['id'],'lat'=>$B['lat'],'lng'=>$B['lng']]; $len0=pathLength($a,$b,$points);
if($len0<MIN_EDGE_LEN_M)fail('перегон слишком короткий (минимум '.MIN_EDGE_LEN_M.' м)',400);
if($len0>MAX_EDGE_LEN_M)fail('перегон слишком длинный (максимум 20 км) — разбейте его промежуточными узлами или станциями',400);
if($A['mode']==='existing'&&$B['mode']==='existing'){ if($A['id']===$B['id'])fail('концы перегона совпадают',400);
foreach($d0['edges'] as $e) if(((string)$e['from']===$A['id']&&(string)$e['to']===$B['id'])||((string)$e['from']===$B['id']&&(string)$e['to']===$A['id']))fail('перегон уже существует',409); }
$z=doesEdgeCrossForbiddenZone($a,$b,$points); if($z)fail("Перегон пересекает запретную зону: «{$z['name']}»",400);
$cov=edgeOsmCoverage($a,$b,$points); $disc=round(OSM_EDGE_DISCOUNT*$cov,4); $obs=checkOsmObstacles($a,$b,$points); $obsCost=obstaclesCost($obs['obstacles']);
$cross=computeEdgeCrossings($d0,$a,$b,$points); if(!empty($cross['blocked']))fail('Строить нельзя: '.$cross['blocked'][0]['reason'],400);
$structures=applyBridgeSelection(array_merge($cross['bridges'],$cross['overpasses']),$sel); $structCost=crossingsCost($structures);
sendJson(runTx(function()use($me,$A,$B,$points,$double,$disc,$obsCost,$structCost,$structures,$blen){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; $pol=policiesAll();
$resolveId=function(array $R) use (&$d,$pid,$pol):string{ if($R['mode']==='existing'){ $o=endpointById($d,$R['id']); if(!$o)fail('точка привязки исчезла',409);
if(!accessAllowed($pol,$o,$pid,'connect'))fail('владелец «'.($o['name']??'').'» не разрешает подключение',403); if(!empty($o['frozen']))fail('«'.($o['name']??'').'» выставлен на продажу',400); return (string)$o['id']; } return makeNode($d,$pid,$R['lat'],$R['lng']); };
$newCount=($A['mode']==='new'?1:0)+($B['mode']==='new'?1:0); $fid=$resolveId($A); $tid=$resolveId($B); if($fid===$tid)fail('концы перегона совпали после привязки',409);
foreach($d['edges'] as $e) if(((string)$e['from']===$fid&&(string)$e['to']===$tid)||((string)$e['from']===$tid&&(string)$e['to']===$fid))fail('перегон уже существует',409);
$ea=endpointById($d,$fid); $eb=endpointById($d,$tid); $length=pathLength($ea,$eb,$points);
if($length>MAX_EDGE_LEN_M)fail('перегон слишком длинный (максимум 20 км) — разбейте его промежуточными узлами или станциями',400);
$perM=edgeCostPerMeterForPlayer($d,$pid)*(1-$disc);
$bn=blocksNFor($length,$double,$blen); $blockC=$bn*COST_BLOCK;
$cost=(int)ceil($length*$perM*($double?2:1))+$obsCost+$structCost+$newCount*COST_NODE+$blockC;
chargePlayer($pid,$cost,'build_edge',null,['from'=>$fid,'to'=>$tid,'length'=>$length,'nodes'=>$newCount,'blocks'=>$bn]);
$id=newId('e'); $end=time()+(int)max(30,($length/1000)*BUILD_TIME_EDGE_PER_KM*DAY_DURATION_SEC);
$d['edges'][]=['id'=>$id,'from'=>$fid,'to'=>$tid,'length'=>$length,'minutes'=>10,'double'=>$double,'electric'=>false,'electric_legacy'=>false,'double_legacy'=>false,'built_day'=>(int)metaGet('day_count'),'points'=>$points,'is_building'=>true,'build_end'=>$end,'build_type'=>'edge','upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'frozen'=>false,'owner_id'=>$pid,'access'=>'inherit','bridges'=>$structures,'bridges_v'=>1,'block_len'=>$blen];
$ver=worldSave($d); $nmA=endpointName($d,$fid); $nmB=endpointName($d,$tid);
if($length>=LONG_EDGE_M)logEvent('long_edge',$pid,companyDisplayName($pid)." начал перегон {$length} м: {$nmA} → {$nmB}",[]);
$nb=0;$no=0; foreach($structures as $s){ if($s['kind']==='rail')$no++; else $nb++; }
if($nb+$no>0)logEvent('bridges_built',$pid,companyDisplayName($pid)." строит {$nb} мост(ов) и {$no} путепровод(ов)",['edge_id'=>$id]);
if($newCount>0)logEvent('branch_built',$pid,companyDisplayName($pid)." продлевает ветку в поле ({$newCount} нов. узл.)",['edge_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'cost'=>$cost,'length'=>$length,'build_end'=>$end,'from'=>$fid,'to'=>$tid,'nodes_new'=>$newCount,'bridges'=>$nb,'overpasses'=>$no,'structures'=>$structures,'block_len'=>$blen,'blocks_n'=>$bn,'blocks_cost'=>$blockC]; })); }
if($action==='set_block_len'&&$isPost){ $me=requirePlayer(); $q=readJson(); $eid=(string)($q['edge_id']??''); if($eid==='')sendJson(['error'=>'bad request'],400);
$nl=normBlockLen($q['block_len']??0);
sendJson(runTx(function()use($me,$eid,$nl){ $w=worldGet(); $d=$w['data']; [$i,$e,$a,$b]=loadEdgeForUpgrade($d,$eid,$me);
if(empty($e['double']))fail('на однопутке один блок-участок на весь перегон — сначала постройте второй путь',400);
$len=(float)$e['length']; $old=edgeBlocksN($e); $new=max(1,(int)ceil($len/$nl)); $delta=$new-$old; $cost=$delta>0?$delta*COST_BLOCK:0;
if($cost>0)chargePlayer((int)$me['id'],$cost,'set_block_len',null,['edge_id'=>$eid,'blocks_delta'=>$delta]);
$d['edges'][$i]['block_len']=$nl; $ver=worldSave($d);
logEvent('construction_done',(int)$me['id'],companyDisplayName((int)$me['id']).' изменил блок-участки: '.endpointName($d,(string)$e['from']).' ↔ '.endpointName($d,(string)$e['to'])." — {$new} × {$nl} м".($cost>0?" ({$cost} ₽)":' (бесплатно)'),['edge_id'=>$eid]);
return ['ok'=>true,'version'=>$ver,'block_len'=>$nl,'blocks_n'=>$new,'blocks_delta'=>$delta,'cost'=>$cost,'refund'=>0]; })); }
if($action==='split_edge'&&$isPost){ $me=requirePlayer(); $q=readJson(); $eid=(string)($q['edge_id']??''); $name=trim((string)($q['name']??'')); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0);
if($eid===''||!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad request'],400); $w0=worldGet(); $e0=edgeById($w0['data'],$eid); if(!$e0)sendJson(['error'=>'перегон не найден'],404);
$pl0=edgePolyline($w0['data'],$e0); if(count($pl0)<2)fail('геометрия перегона недоступна',400); $pr=projectOnPolyline($pl0,$lat,$lng);
if($pr['off']>150)fail('клик слишком далеко от перегона ('.round($pr['off']).' м)',400);
if($pr['dist']<MIN_SPLIT_DIST_M||$pr['total']-$pr['dist']<MIN_SPLIT_DIST_M)fail('врезка слишком близко к концу перегона (минимум '.MIN_SPLIT_DIST_M.' м)',400);
$z=isPointInForbiddenZone($pr['lat'],$pr['lng']); if($z)fail("Здесь нельзя строить: «{$z['name']}»",400);
$osm=getOsmStationDiscount($pr['lat'],$pr['lng']); $bl=countNearbyBuildings($pr['lat'],$pr['lng'],500);
sendJson(runTx(function()use($me,$eid,$name,$pr,$osm,$bl){ $w=worldGet(); $d=$w['data']; $pol=policiesAll(); $pid=(int)$me['id']; $i=idxEdge($d,$eid); if($i===null)fail('перегон не найден',404); $e=$d['edges'][$i];
if(!empty($e['is_building']))fail('перегон ещё строится',400); if(!empty($e['upgrading']))fail('перегон улучшается',400); if(!empty($e['frozen']))fail('перегон на продаже',400);
if((int)$e['owner_id']!==$pid&&!accessAllowed($pol,$e,$pid,'connect'))fail('владелец перегона не разрешает врезку станции',403);
$pts=edgePolyline($d,$e); $n=count($pts); $seg=(int)$pr['seg']; $S=['lat'=>round((float)$pr['lat'],7),'lng'=>round((float)$pr['lng'],7)];
$near=snapEndpoint($d,$S['lat'],$S['lng']); if($near)fail('рядом уже есть «'.($near['name']??'объект').'» — стройте перегон к нему',409);
$cost=stationCostForPlayer($d,$pid); if($osm)$cost=(int)ceil($cost*(1-OSM_DISCOUNT)); chargePlayer($pid,$cost,'split_edge_station',null,['edge_id'=>$eid]);
$sid=newId('s'); $end=time()+BUILD_TIME_STATION*DAY_DURATION_SEC; $nm=$name!==''?$name:('Станция '.(playerStationsCount($d,$pid)+1));
$d['stations'][]=['id'=>$sid,'name'=>$nm,'lat'=>$S['lat'],'lng'=>$S['lng'],'radius'=>stationRadiusForClass(1),'owner_id'=>$pid,'labelOffset'=>['x'=>10,'y'=>4],'color'=>null,'railway'=>'','class'=>1,'capacity'=>4,'built_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'build_type'=>'station','upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,'frozen'=>false,'health'=>100,'buildings'=>$bl,'access'=>'inherit'];
$ptsA=array_slice($pts,0,$seg+1); $ptsA[]=[$S['lat'],$S['lng']]; $ptsB=[[$S['lat'],$S['lng']]]; for($k=$seg+1;$k<$n;$k++)$ptsB[]=$pts[$k];
$intA=array_slice($pts,1,max(0,$seg)); $intB=array_slice($pts,$seg+1,max(0,$n-$seg-2));
$mk=function(array $from,array $to,array $interior) use ($e,$pid):array{ $a=['lat'=>$from[0],'lng'=>$from[1]]; $b=['lat'=>$to[0],'lng'=>$to[1]]; $pp=[]; foreach($interior as $x)$pp[]=['lat'=>$x[0],'lng'=>$x[1]];
return ['id'=>newId('e'),'from'=>'','to'=>'','length'=>pathLength($a,$b,$pp),'minutes'=>10,'double'=>!empty($e['double']),'electric'=>!empty($e['electric']),'electric_legacy'=>!empty($e['electric_legacy']),'double_legacy'=>!empty($e['double_legacy']),'built_day'=>(int)($e['built_day']??1),'points'=>$pp,'is_building'=>false,'build_end'=>0,'build_type'=>null,'upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'frozen'=>false,'owner_id'=>(int)($e['owner_id']??$pid),'access'=>normAccess($e['access']??'inherit'),'bridges'=>[],'bridges_v'=>1,'block_len'=>edgeBlockLen($e)]; };
$eA=$mk($ptsA[0],$ptsA[count($ptsA)-1],$intA); $eB=$mk($ptsB[0],$ptsB[count($ptsB)-1],$intB); $eA['from']=(string)$e['from']; $eA['to']=$sid; $eB['from']=$sid; $eB['to']=(string)$e['to'];
foreach(($e['bridges']??[]) as $br){ $p2=projectOnPolyline($pts,(float)$br['lat'],(float)$br['lng']); if($p2['dist']<=$pr['dist'])$eA['bridges'][]=$br; else $eB['bridges'][]=$br; }
array_splice($d['edges'],$i,1); $d['edges'][]=$eA; $d['edges'][]=$eB; $ver=worldSave($d);
logEvent('station_inserted',$pid,companyDisplayName($pid)." врезает станцию «{$nm}» в перегон",['station_id'=>$sid,'edge_a'=>$eA['id'],'edge_b'=>$eB['id']]);
return ['ok'=>true,'version'=>$ver,'station_id'=>$sid,'edge_a'=>$eA['id'],'edge_b'=>$eB['id'],'cost'=>$cost,'build_end'=>$end,'lat'=>$S['lat'],'lng'=>$S['lng'],'buildings'=>$bl,'income_percent'=>getBuildingIncomePercent($bl),'len_a'=>$eA['length'],'len_b'=>$eB['length']]; })); }
if($action==='upgrade_edge_double'&&$isPost){ $me=requirePlayer(); $eid=(string)(readJson()['edge_id']??''); if($eid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$eid){ $w=worldGet(); $d=$w['data']; [$i,$e,$a,$b]=loadEdgeForUpgrade($d,$eid,$me); if(!empty($e['double']))fail('уже двухпутный',409); $nr=['name'=>null];
if(empty($e['double_legacy'])){ $nr=nearestTrackMaintFor($d,$e); if($nr['dist']===null||$nr['dist']>TRACK_MAINT_RANGE_M){ $txt=$nr['dist']===null?'путевых частей нет вообще':'ближайшая — «'.$nr['name'].'» в '.round($nr['dist']/1000,1).' км'; fail('нет ПЧ: нужна путевая часть в '.(TRACK_MAINT_RANGE_M/1000).' км от перегона ('.$txt.')',400); } }
$len=(float)$e['length']; $blen=edgeBlockLen($e); $newN=max(1,(int)ceil($len/$blen)); $blockC=max(0,$newN-1)*COST_BLOCK;
$cost=(int)ceil($len*COST_UPGRADE_DOUBLE_PER_M)+$blockC; chargePlayer((int)$me['id'],$cost,'upgrade_edge_double',null,['edge_id'=>$eid,'blocks_cost'=>$blockC]);
$end=time()+BUILD_TIME_UPGRADE_DOUBLE*DAY_DURATION_SEC; $d['edges'][$i]['upgrading']=true; $d['edges'][$i]['upgrade_type']='double'; $d['edges'][$i]['upgrade_end']=$end;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'build_end'=>$end,'served_by'=>$nr['name']??null,'blocks_n'=>$newN,'blocks_cost'=>$blockC,'block_len'=>$blen]; })); }
if($action==='upgrade_edge_electric'&&$isPost){ $me=requirePlayer(); $eid=(string)(readJson()['edge_id']??''); if($eid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$eid){ $w=worldGet(); $d=$w['data']; [$i,$e,$a,$b]=loadEdgeForUpgrade($d,$eid,$me); if(!empty($e['electric']))fail('уже электрифицирован',409);
$nr=nearestSubstationFor($d,$e); if($nr['dist']===null||$nr['dist']>SUBSTATION_RANGE_M){ $txt=$nr['dist']===null?'подстанций нет вообще':'ближайшая — «'.$nr['name'].'» в '.round($nr['dist']/1000,1).' км'; fail('нет питания: нужна своя подстанция в '.(SUBSTATION_RANGE_M/1000).' км от перегона ('.$txt.')',400); }
$len=(float)$e['length']; $cost=(int)ceil($len*COST_UPGRADE_ELECTRIFY_PER_M); chargePlayer((int)$me['id'],$cost,'upgrade_edge_electric',null,['edge_id'=>$eid]);
$end=time()+BUILD_TIME_UPGRADE_ELECTRIC*DAY_DURATION_SEC; $d['edges'][$i]['upgrading']=true; $d['edges'][$i]['upgrade_type']='electric'; $d['edges'][$i]['upgrade_end']=$end;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'build_end'=>$end,'max_speed'=>EDGE_SPEED_ELECTRIC,'powered_by'=>$nr['name']]; })); }
if($action==='upgrade_bridge'&&$isPost){ $me=requirePlayer(); $q=readJson(); $eid=(string)($q['edge_id']??''); $bid=(string)($q['bridge_id']??''); $type=(string)($q['type']??'');
if($eid===''||$bid===''||$type==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$eid,$bid,$type){ $w=worldGet(); $d=$w['data']; [$i,$e,$a,$b]=loadEdgeForUpgrade($d,$eid,$me); $bi=null;
foreach(($e['bridges']??[]) as $k=>$br) if((string)$br['id']===$bid){ $bi=$k; break; } if($bi===null)fail('сооружение не найдено',404);
$br=$e['bridges'][$bi]; $kind=(string)$br['kind']; $nt=normalizeBridgeType($kind,$type); if(bridgeSpeed($nt)<=bridgeSpeed((string)$br['type']))fail('можно только повысить класс',400);
$diff=max(0,bridgeCost($kind,$nt)-bridgeCost($kind,(string)$br['type'])); chargePlayer((int)$me['id'],$diff,'upgrade_bridge',null,['edge_id'=>$eid,'bridge_id'=>$bid]);
$d['edges'][$i]['bridges'][$bi]['type']=$nt; $ver=worldSave($d);
logEvent('bridge_upgraded',(int)$me['id'],companyDisplayName((int)$me['id'])." заменил «{$br['name']}»: ".bridgeLabel($nt).' ('.bridgeSpeed($nt).' км/ч)',[]);
return ['ok'=>true,'version'=>$ver,'cost'=>$diff,'type'=>$nt,'speed'=>bridgeSpeed($nt)]; })); }
if($action==='delete_edge'&&$isPost){ $me=requirePlayer(); $eid=(string)(readJson()['edge_id']??''); if($eid==='')sendJson(['error'=>'bad request'],400);
runTx(function()use($me,$eid){ $w=worldGet(); $d=$w['data']; $i=idxEdge($d,$eid); if($i===null)fail('перегон не найден',404); $e=$d['edges'][$i];
if((int)($e['owner_id']??0)!==(int)$me['id'])fail('не ваш перегон',403); if(!empty($e['frozen']))fail('перегон на продаже',400);
$a=endpointById($d,(string)$e['from']); $b=endpointById($d,(string)$e['to']); $ref=(int)ceil((($a&&$b)?edgeReplacementCost($e,$a,$b):(int)$e['length'])*REFUND_RATIO);
array_splice($d['edges'],$i,1); $orph=pruneOrphanNodes($d); if($ref>0)addMoney((int)$me['id'],$ref,'delete_edge_refund',null,['edge_id'=>$eid]);
logEvent('deleted',(int)$me['id'],companyDisplayName((int)$me['id'])." снёс перегон (возврат {$ref} ₽, узлов убрано: {$orph})",[]); worldSave($d); }); sendJson(['ok'=>true]); }
if($action==='rename_node'&&$isPost){ $me=requirePlayer(); $q=readJson(); $nid=(string)($q['node_id']??''); $nm=trim((string)($q['name']??'')); if($nid===''||$nm===''||mb_strlen($nm)>40)sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$nid,$nm){ $w=worldGet(); $d=$w['data']; $i=idxNode($d,$nid); if($i===null)fail('узел не найден',404); if((int)$d['nodes'][$i]['owner_id']!==(int)$me['id'])fail('не ваш узел',403); $d['nodes'][$i]['name']=$nm; return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='delete_node'&&$isPost){ $me=requirePlayer(); $nid=(string)(readJson()['node_id']??''); if($nid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$nid){ $w=worldGet(); $d=$w['data']; $i=idxNode($d,$nid); if($i===null)fail('узел не найден',404); $n=$d['nodes'][$i];
if((int)$n['owner_id']!==(int)$me['id'])fail('не ваш узел',403); if(!empty($n['frozen']))fail('узел на продаже',400);
$ne=0; foreach($d['edges'] as $e) if($e['from']===$nid||$e['to']===$nid)$ne++;
$d['edges']=array_values(array_filter($d['edges'],fn($e)=>$e['from']!==$nid&&$e['to']!==$nid)); array_splice($d['nodes'],$i,1); pruneOrphanNodes($d);
$ref=(int)ceil(COST_NODE*REFUND_RATIO); if($ref>0)addMoney((int)$me['id'],$ref,'delete_node_refund',null,['node_id'=>$nid]);
logEvent('deleted',(int)$me['id'],companyDisplayName((int)$me['id'])." убрал узел «{$n['name']}» (перегонов: {$ne})",[]);
return ['ok'=>true,'version'=>worldSave($d),'refund'=>$ref,'edges_deleted'=>$ne]; })); }
if($action==='convert_node'&&$isPost){ $me=requirePlayer(); $q=readJson(); $nid=(string)($q['node_id']??''); $nm=trim((string)($q['name']??'')); if($nid==='')sendJson(['error'=>'bad request'],400);
$w0=worldGet(); $n0=nodeById($w0['data'],$nid); if(!$n0)fail('узел не найден',404);
if((int)$n0['owner_id']!==(int)$me['id'])fail('не ваш узел',403);
if(!empty($n0['frozen']))fail('узел на продаже',400);
$lat0=(float)$n0['lat']; $lng0=(float)$n0['lng'];
$z=isPointInForbiddenZone($lat0,$lng0); if($z)fail("Здесь нельзя строить: «{$z['name']}»",400);
$osm=getOsmStationDiscount($lat0,$lng0); $bl=countNearbyBuildings($lat0,$lng0,500);
sendJson(runTx(function()use($me,$nid,$nm,$osm,$bl){ $w=worldGet(); $d=$w['data']; $i=idxNode($d,$nid); if($i===null)fail('узел не найден',404); $n=$d['nodes'][$i]; $pid=(int)$me['id'];
if((int)$n['owner_id']!==$pid)fail('не ваш узел',403); if(!empty($n['frozen']))fail('узел на продаже',400); $lat=(float)$n['lat']; $lng=(float)$n['lng'];
$cost=stationCostForPlayer($d,$pid); if($osm)$cost=(int)ceil($cost*(1-OSM_DISCOUNT));
chargePlayer($pid,$cost,'convert_node',null,['node_id'=>$nid]); $sid=newId('s'); $end=time()+BUILD_TIME_STATION*DAY_DURATION_SEC; $name=$nm!==''?$nm:('Станция '.(playerStationsCount($d,$pid)+1));
$d['stations'][]=['id'=>$sid,'name'=>$name,'lat'=>$lat,'lng'=>$lng,'radius'=>stationRadiusForClass(1),'owner_id'=>$pid,'labelOffset'=>$n['labelOffset']??['x'=>10,'y'=>4],'color'=>null,'railway'=>'','class'=>1,'capacity'=>4,'built_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'build_type'=>'station','upgrading'=>false,'upgrade_end'=>0,'upgrade_type'=>null,'upgrade_class'=>0,'frozen'=>false,'health'=>100,'buildings'=>$bl,'access'=>normAccess($n['access']??'inherit')];
foreach($d['edges'] as &$e){ if((string)$e['from']===$nid)$e['from']=$sid; if((string)$e['to']===$nid)$e['to']=$sid; } unset($e);
array_splice($d['nodes'],$i,1); $ver=worldSave($d); logEvent('station_inserted',$pid,companyDisplayName($pid)." превратил узел в станцию «{$name}»",['station_id'=>$sid]);
return ['ok'=>true,'version'=>$ver,'station_id'=>$sid,'cost'=>$cost,'build_end'=>$end,'buildings'=>$bl,'income_percent'=>getBuildingIncomePercent($bl)]; })); }
if($action==='build_substation'&&$isPost){ $me=requirePlayer(); $q=readJson(); $nm=trim((string)($q['name']??'')); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0); $sid=(string)($q['station_id']??'');
if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400); $z=isPointInForbiddenZone($lat,$lng); if($z)fail("Здесь нельзя строить: «{$z['name']}»",400);
sendJson(runTx(function()use($me,$nm,$lat,$lng,$sid){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; $pol=policiesAll(); $s=null;
if($sid!==''){ $s=stationById($d,$sid); if(!$s)fail('станция не найдена',404); }
else{ $bd=INF; foreach($d['stations'] as $x){ if(!empty($x['is_building']))continue; if((int)$x['owner_id']!==$pid&&!accessAllowed($pol,$x,$pid,'connect'))continue; $dd=haversineDistanceMeters($lat,$lng,(float)$x['lat'],(float)$x['lng']); if($dd<$bd){$bd=$dd;$s=$x;} } }
if(!$s)fail('рядом нет доступной станции',400); if((int)$s['owner_id']!==$pid&&!accessAllowed($pol,$s,$pid,'connect'))fail('владелец станции не разрешает подключение',403);
$dist=haversineDistanceMeters($lat,$lng,(float)$s['lat'],(float)$s['lng']); if($dist>SUBSTATION_MAX_STATION_DIST_M)fail('подстанция должна быть не дальше '.SUBSTATION_MAX_STATION_DIST_M.' м от станции (сейчас '.round($dist).' м)',400);
$cost=substationCostForPlayer($d,$pid); chargePlayer($pid,$cost,'build_substation',null,['name'=>$nm]); $id=newId('b'); $end=time()+BUILD_TIME_SUBSTATION*DAY_DURATION_SEC;
if($nm==='')$nm='Подстанция '.(playerSubstationsCount($d,$pid)+1);
$d['substations'][]=['id'=>$id,'name'=>$nm,'lat'=>$lat,'lng'=>$lng,'station_id'=>(string)$s['id'],'owner_id'=>$pid,'built_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'frozen'=>false,'health'=>100,'access'=>'inherit','labelOffset'=>['x'=>10,'y'=>4]];
$ver=worldSave($d); logEvent('substation_built',$pid,companyDisplayName($pid)." строит подстанцию «{$nm}» у «{$s['name']}» — радиус ".(SUBSTATION_RANGE_M/1000).' км',['substation_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'substation_id'=>$id,'cost'=>$cost,'build_end'=>$end,'station'=>$s['name'],'distance'=>round($dist),'range_m'=>SUBSTATION_RANGE_M]; })); }
if($action==='rename_substation'&&$isPost){ $me=requirePlayer(); $q=readJson(); $bid=(string)($q['substation_id']??''); $nm=trim((string)($q['name']??'')); if($bid===''||$nm===''||mb_strlen($nm)>60)sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$bid,$nm){ $w=worldGet(); $d=$w['data']; $i=idxSubstation($d,$bid); if($i===null)fail('подстанция не найдена',404); if((int)$d['substations'][$i]['owner_id']!==(int)$me['id'])fail('не ваша подстанция',403); $d['substations'][$i]['name']=$nm; return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='repair_substation'&&$isPost){ $me=requirePlayer(); $q=readJson(); $bid=(string)($q['substation_id']??''); $ml=isset($q['lat'])&&is_numeric($q['lat'])?(float)$q['lat']:null; $mg=isset($q['lng'])&&is_numeric($q['lng'])?(float)$q['lng']:null; if($bid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$bid,$ml,$mg){ $w=worldGet(); $d=$w['data']; $i=idxSubstation($d,$bid); if($i===null)fail('подстанция не найдена',404); $x=$d['substations'][$i];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваша подстанция',403); if(!empty($x['is_building'])||!empty($x['frozen']))fail('подстанция недоступна',400);
$h=(int)$x['health']; if($h>=100)fail('подстанция в идеальном состоянии',400);
$free=($ml!==null&&$mg!==null&&haversineDistanceMeters($ml,$mg,(float)$x['lat'],(float)$x['lng'])<=REPAIR_FREE_RADIUS_M); $cost=0;
if(!$free){ $cost=(int)ceil(substationReplacementCost($x)*REPAIR_COST_RATIO); chargePlayer((int)$me['id'],$cost,'repair_substation',null,['substation_id'=>$bid]); }
$d['substations'][$i]['health']=100; $d['substations'][$i]['health_pending']=0.0; worldSave($d); return ['ok'=>true,'cost'=>$cost,'free'=>$free]; })); }
if($action==='delete_substation'&&$isPost){ $me=requirePlayer(); $bid=(string)(readJson()['substation_id']??''); if($bid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$bid){ $w=worldGet(); $d=$w['data']; $i=idxSubstation($d,$bid); if($i===null)fail('подстанция не найдена',404); $x=$d['substations'][$i];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваша подстанция',403); if(!empty($x['frozen']))fail('подстанция на продаже',400);
$pid=(int)$me['id']; $name=(string)$x['name']; $ref=(int)ceil(substationReplacementCost($x)*REFUND_RATIO); array_splice($d['substations'],$i,1);
$pw=poweredInfo($d); $lost=0; foreach($d['edges'] as $e){ if((int)($e['owner_id']??0)!==$pid)continue; if(empty($e['electric'])||!empty($e['electric_legacy']))continue; if(isset($pw['edges'][(string)$e['id']]))continue; $lost++; }
if($ref>0)addMoney($pid,$ref,'delete_substation_refund',null,['substation_id'=>$bid]);
logEvent('substation_deleted',$pid,companyDisplayName($pid)." снёс подстанцию «{$name}»".($lost>0?" — обесточено перегонов: {$lost}":''),['lost_edges'=>$lost]); worldSave($d);
return ['ok'=>true,'refund'=>$ref,'lost_edges'=>$lost]; })); }
if($action==='build_track_maint'&&$isPost){ $me=requirePlayer(); $q=readJson(); $nm=trim((string)($q['name']??'')); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0); $sid=(string)($q['station_id']??'');
if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400); $z=isPointInForbiddenZone($lat,$lng); if($z)fail("Здесь нельзя строить: «{$z['name']}»",400);
sendJson(runTx(function()use($me,$nm,$lat,$lng,$sid){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; $pol=policiesAll(); $s=null;
if($sid!==''){ $s=stationById($d,$sid); if(!$s)fail('станция не найдена',404); }
else{ $bd=INF; foreach($d['stations'] as $x){ if(!empty($x['is_building']))continue; if((int)$x['owner_id']!==$pid&&!accessAllowed($pol,$x,$pid,'connect'))continue; $dd=haversineDistanceMeters($lat,$lng,(float)$x['lat'],(float)$x['lng']); if($dd<$bd){$bd=$dd;$s=$x;} } }
if(!$s)fail('рядом нет доступной станции',400); if((int)$s['owner_id']!==$pid&&!accessAllowed($pol,$s,$pid,'connect'))fail('владелец станции не разрешает подключение',403);
$dist=haversineDistanceMeters($lat,$lng,(float)$s['lat'],(float)$s['lng']); if($dist>TRACK_MAINT_MAX_STATION_DIST_M)fail('ПЧ должна быть не дальше '.TRACK_MAINT_MAX_STATION_DIST_M.' м от станции (сейчас '.round($dist).' м)',400);
$cost=trackMaintCostForPlayer($d,$pid); chargePlayer($pid,$cost,'build_track_maint',null,['name'=>$nm]); $id=newId('m'); $end=time()+BUILD_TIME_TRACK_MAINT*DAY_DURATION_SEC;
if($nm==='')$nm='Путевая часть '.(playerTrackMaintCount($d,$pid)+1);
$d['track_maint'][]=['id'=>$id,'name'=>$nm,'lat'=>$lat,'lng'=>$lng,'station_id'=>(string)$s['id'],'owner_id'=>$pid,'built_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'frozen'=>false,'range_m'=>TRACK_MAINT_RANGE_M,'access'=>'inherit','labelOffset'=>['x'=>10,'y'=>4]];
$ver=worldSave($d); logEvent('track_maint_built',$pid,companyDisplayName($pid)." строит путевую часть «{$nm}» у «{$s['name']}» — радиус ".(TRACK_MAINT_RANGE_M/1000).' км',['track_maint_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'track_maint_id'=>$id,'cost'=>$cost,'build_end'=>$end,'station'=>$s['name'],'distance'=>round($dist),'range_m'=>TRACK_MAINT_RANGE_M]; })); }
if($action==='rename_track_maint'&&$isPost){ $me=requirePlayer(); $q=readJson(); $mid=(string)($q['track_maint_id']??''); $nm=trim((string)($q['name']??'')); if($mid===''||$nm===''||mb_strlen($nm)>60)sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$mid,$nm){ $w=worldGet(); $d=$w['data']; $i=idxTrackMaint($d,$mid); if($i===null)fail('ПЧ не найдена',404); if((int)$d['track_maint'][$i]['owner_id']!==(int)$me['id'])fail('не ваша путевая часть',403); $d['track_maint'][$i]['name']=$nm; return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='delete_track_maint'&&$isPost){ $me=requirePlayer(); $mid=(string)(readJson()['track_maint_id']??''); if($mid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$mid){ $w=worldGet(); $d=$w['data']; $i=idxTrackMaint($d,$mid); if($i===null)fail('ПЧ не найдена',404); $x=$d['track_maint'][$i];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваша путевая часть',403); if(!empty($x['frozen']))fail('ПЧ на продаже',400);
$pid=(int)$me['id']; $name=(string)$x['name']; $ref=(int)ceil(trackMaintReplacementCost($x)*REFUND_RATIO); array_splice($d['track_maint'],$i,1);
if($ref>0)addMoney($pid,$ref,'delete_track_maint_refund',null,['track_maint_id'=>$mid]);
logEvent('track_maint_deleted',$pid,companyDisplayName($pid)." ликвидировал путевую часть «{$name}» — новые двухпутки в радиусе 10 км потребуют другую ПЧ",[]); worldSave($d);
return ['ok'=>true,'refund'=>$ref]; })); }
if($action==='quote_route'){ $me=requirePlayer(); $q=readJson(); $stops=idList($q['stations']??[]); $loop=!empty($q['loop']);
$w=worldGet(); $d=$w['data']; $idx=worldIndex($d); $pol=policiesAll(); $pid=(int)$me['id']; $info=routeCompute($idx,$pol,$stops,$loop,$pid,[]);
$prev=[]; for($n=0;$n<=max(1,$info['slots']);$n++){ $p=routeCompute($idx,$pol,$stops,$loop,$pid,['cap'=>(float)$n,'p'=>(float)$n,'g'=>0.0,'power'=>1.0,'health'=>1.0]); $prev[]=['capacity'=>$n,'income'=>$p['income'],'upkeep'=>round($p['upkeep'],2),'fee'=>$p['fee_total']]; }
$prevF=[]; for($n=2;$n<=max(2,$info['slots']);$n+=2){ $p=routeCompute($idx,$pol,$stops,$loop,$pid,['cap'=>(float)$n,'p'=>0.0,'g'=>(float)$n,'power'=>1.0,'health'=>1.0]); $prevF[]=['capacity'=>$n,'income'=>$p['income'],'upkeep'=>round($p['upkeep'],2),'fee'=>$p['fee_total']]; }
$names=[]; $warn=null; $issues=[];
foreach($info['stops'] as $sid){ $s=$idx['st'][$sid]??null; $used=stationRoutesUsed($d,$sid); $lim=$s?stationRouteLimit($s):1; $cls=$s?(int)($s['class']??1):1;
$names[]=['id'=>$sid,'name'=>$s['name']??'?','buildings'=>(int)($s['buildings']??0),'percent'=>getBuildingIncomePercent((int)($s['buildings']??0)),'class'=>$cls,'owner_id'=>(int)($s['owner_id']??0),'color'=>playerColor((int)($s['owner_id']??0)),'routes_used'=>$used,'routes_limit'=>$lim,'wagon_limit'=>$s?stationWagonLimit($s):0,'can_stop'=>$s?accessAllowed($pol,$s,$pid,'stop'):false,'is_building'=>!empty($s['is_building']),'build_end'=>(int)($s['build_end']??0),'upgrading'=>!empty($s['upgrading']),'upgrade_class'=>(int)($s['upgrade_class']??0),'upgrade_end'=>(int)($s['upgrade_end']??0),'blocks_entry'=>$s?stationBlocksEntry($s):true];
if($s&&$used>=$lim){ if($warn===null)$warn="станция «{$s['name']}»: слоты маршрутов исчерпаны ({$used}/{$lim})";
$issues[]=['station_id'=>$sid,'name'=>(string)$s['name'],'class'=>$cls,'used'=>$used,'limit'=>$lim,'next_class'=>$cls<MAX_CLASS?$cls+1:null,'cost'=>$cls<MAX_CLASS?stationUpgradeCost($cls):0,'build_days'=>$cls<MAX_CLASS?BUILD_TIME_CLASS_UPGRADE*$cls:0,'upgrading'=>!empty($s['upgrading']),'upgrade_end'=>(int)($s['upgrade_end']??0),'upgrade_class'=>(int)($s['upgrade_class']??0),'maxed'=>$cls>=MAX_CLASS,'frozen'=>!empty($s['frozen'])]; } }
$g=routeGeometry($d,$idx,$info);
sendJson(['ok'=>$info['ok'],'reason'=>$info['reason'],'info'=>$info,'stops'=>$names,'income_by_capacity'=>$prev,'income_by_capacity_freight'=>$prevF,'geometry'=>$g['geometry'],'stops_dist'=>$g['stops_dist'],'transit'=>$g['transit'],'slot_warning'=>$warn,'slot_issues'=>$issues,'owners'=>$info['owners'],'trackage_share'=>TRACTAGE_REVENUE_SHARE,'wagon_types'=>wagonTypesPublic(),'freight_break_even_km'=>FREIGHT_BREAK_EVEN_KM]); }
if($action==='create_route'&&$isPost){ $me=requirePlayer(); $q=readJson(); $nm=trim((string)($q['name']??'')); $stops=idList($q['stations']??[]); $loop=!empty($q['loop']);
if(count($stops)<2)sendJson(['error'=>'нужно минимум 2 станции'],400); if(count($stops)>MAX_ROUTE_STOPS)sendJson(['error'=>'слишком много остановок'],400); if(mb_strlen($nm)>60)sendJson(['error'=>'слишком длинное название'],400);
sendJson(runTx(function()use($me,$nm,$stops,$loop){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; if($nm==='')$nm='Маршрут '.(playerRoutesCount($d,$pid)+1);
$res=createRouteRecord($d,$pid,$nm,$stops,$loop,false); $ver=worldSave($d);
logEvent('route_created',$pid,companyDisplayName($pid)." открыл маршрут «{$nm}» (".count($res['info']['stops']).' ост., '.round($res['info']['km']/1000,2).' км)',['route_id'=>$res['id']]);
return ['ok'=>true,'version'=>$ver,'route_id'=>$res['id'],'info'=>$res['info'],'color'=>playerColor($pid)]; })); }
if($action==='rename_route'&&$isPost){ $me=requirePlayer(); $q=readJson(); $rid=(string)($q['route_id']??''); $nm=trim((string)($q['name']??'')); if($rid===''||$nm===''||mb_strlen($nm)>60)sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$rid,$nm){ $w=worldGet(); $d=$w['data']; $i=idxRoute($d,$rid); if($i===null)fail('маршрут не найден',404); if((int)$d['routes'][$i]['owner_id']!==(int)$me['id'])fail('не ваш маршрут',403); $d['routes'][$i]['name']=$nm; return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='delete_route'&&$isPost){ $me=requirePlayer(); $rid=(string)(readJson()['route_id']??''); if($rid==='')sendJson(['error'=>'bad request'],400);
runTx(function()use($me,$rid){ $w=worldGet(); $d=$w['data']; $i=idxRoute($d,$rid); if($i===null)fail('маршрут не найден',404); $r=$d['routes'][$i];
if((int)$r['owner_id']!==(int)$me['id'])fail('не ваш маршрут',403); foreach($d['trains'] as &$t) if((string)($t['route_id']??'')===$rid)$t['route_id']=null; unset($t);
array_splice($d['routes'],$i,1); logEvent('route_closed',(int)$me['id'],companyDisplayName((int)$me['id'])." закрыл маршрут «{$r['name']}»",[]); worldSave($d); }); sendJson(['ok'=>true]); }
if($action==='auto_routes'&&$isPost){ $me=requirePlayer();
sendJson(runTx(function()use($me){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; $idx=worldIndex($d); $pol=policiesAll();
$mine=[]; foreach($d['stations'] as $s) if((int)$s['owner_id']===$pid)$mine[]=(string)$s['id']; $created=0; $guard=0;
while($guard++<20){ $path=autoRouteForPlayer($d,$idx,$pol,$pid,$mine); if(!$path)break; try{ createRouteRecord($d,$pid,'Авто '.($created+1),$path,false,true); $created++; }catch(Throwable $e){ break; } $idx=worldIndex($d); }
if($created>0){ worldSave($d); logEvent('route_created',$pid,companyDisplayName($pid)." открыл {$created} автомаршрут(ов)",[]); } return ['ok'=>true,'created'=>$created]; })); }
if($action==='build_depot'&&$isPost){ $me=requirePlayer(); $q=readJson(); $nm=trim((string)($q['name']??'')); $lat=(float)($q['lat']??0); $lng=(float)($q['lng']??0); $sid=(string)($q['station_id']??'');
if(!is_finite($lat)||!is_finite($lng))sendJson(['error'=>'bad coords'],400); $z=isPointInForbiddenZone($lat,$lng); if($z)fail("Здесь нельзя строить: «{$z['name']}»",400);
sendJson(runTx(function()use($me,$nm,$lat,$lng,$sid){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; $pol=policiesAll(); $s=null;
if($sid!==''){ $s=stationById($d,$sid); if(!$s)fail('станция не найдена',404); }
else{ $bd=INF; foreach($d['stations'] as $x){ if(!empty($x['is_building']))continue; if((int)$x['owner_id']!==$pid&&!accessAllowed($pol,$x,$pid,'connect'))continue; $dd=haversineDistanceMeters($lat,$lng,(float)$x['lat'],(float)$x['lng']); if($dd<$bd){$bd=$dd;$s=$x;} } }
if(!$s)fail('рядом нет доступной станции',400); if((int)$s['owner_id']!==$pid&&!accessAllowed($pol,$s,$pid,'connect'))fail('владелец станции не разрешает подключение',403);
$dist=haversineDistanceMeters($lat,$lng,(float)$s['lat'],(float)$s['lng']); if($dist>DEPOT_MAX_STATION_DIST_M)fail('депо должно быть не дальше '.DEPOT_MAX_STATION_DIST_M.' м (сейчас '.round($dist).' м)',400);
$cost=depotCostForPlayer($d,$pid); chargePlayer($pid,$cost,'build_depot',null,['name'=>$nm]); $id=newId('d'); $end=time()+BUILD_TIME_DEPOT*DAY_DURATION_SEC; if($nm==='')$nm='Депо '.(playerDepotsCount($d,$pid)+1);
$d['depots'][]=['id'=>$id,'name'=>$nm,'lat'=>$lat,'lng'=>$lng,'station_id'=>(string)$s['id'],'owner_id'=>$pid,'level'=>1,'capacity'=>depotCapacityForLevel(1),'built_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'upgrading'=>false,'upgrade_end'=>0,'upgrade_level'=>0,'frozen'=>false,'health'=>100,'access'=>'inherit','labelOffset'=>['x'=>10,'y'=>4]];
$ver=worldSave($d); logEvent('depot_built',$pid,companyDisplayName($pid)." строит депо «{$nm}» у «{$s['name']}»",['depot_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'depot_id'=>$id,'cost'=>$cost,'build_end'=>$end,'station'=>$s['name'],'distance'=>round($dist)]; })); }
if($action==='upgrade_depot'&&$isPost){ $me=requirePlayer(); $did=(string)(readJson()['depot_id']??''); if($did==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$did){ $w=worldGet(); $d=$w['data']; $i=idxDepot($d,$did); if($i===null)fail('депо не найдено',404); $x=$d['depots'][$i];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваше депо',403); if(!empty($x['is_building']))fail('депо ещё строится',400); if(!empty($x['upgrading']))fail('депо уже расширяется',400); if(!empty($x['frozen']))fail('депо на продаже',400);
$l=(int)$x['level']; if($l>=DEPOT_MAX_LEVEL)fail('максимальный уровень',400); $cost=DEPOT_UPGRADE_COST*$l; chargePlayer((int)$me['id'],$cost,'upgrade_depot',null,['depot_id'=>$did]);
$end=time()+BUILD_TIME_DEPOT_UPGRADE*DAY_DURATION_SEC; $d['depots'][$i]['upgrading']=true; $d['depots'][$i]['upgrade_end']=$end; $d['depots'][$i]['upgrade_level']=$l+1;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'level'=>$l+1,'capacity'=>depotCapacityForLevel($l+1),'build_end'=>$end]; })); }
if($action==='repair_depot'&&$isPost){ $me=requirePlayer(); $q=readJson(); $did=(string)($q['depot_id']??''); $ml=isset($q['lat'])&&is_numeric($q['lat'])?(float)$q['lat']:null; $mg=isset($q['lng'])&&is_numeric($q['lng'])?(float)$q['lng']:null; if($did==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$did,$ml,$mg){ $w=worldGet(); $d=$w['data']; $i=idxDepot($d,$did); if($i===null)fail('депо не найдено',404); $x=$d['depots'][$i];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваше депо',403); if(!empty($x['is_building'])||!empty($x['frozen']))fail('депо недоступно',400); $h=(int)$x['health']; if($h>=100)fail('депо в идеальном состоянии',400);
$free=($ml!==null&&$mg!==null&&haversineDistanceMeters($ml,$mg,(float)$x['lat'],(float)$x['lng'])<=REPAIR_FREE_RADIUS_M); $cost=0;
if(!$free){ $cost=(int)ceil(depotReplacementCost($x)*REPAIR_COST_RATIO); chargePlayer((int)$me['id'],$cost,'repair_depot',null,['depot_id'=>$did]); }
$d['depots'][$i]['health']=100; $d['depots'][$i]['health_pending']=0.0; worldSave($d); return ['ok'=>true,'cost'=>$cost,'free'=>$free]; })); }
if($action==='delete_depot'&&$isPost){ $me=requirePlayer(); $did=(string)(readJson()['depot_id']??''); if($did==='')sendJson(['error'=>'bad request'],400);
runTx(function()use($me,$did){ $w=worldGet(); $d=$w['data']; $i=idxDepot($d,$did); if($i===null)fail('депо не найдено',404); $x=$d['depots'][$i];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваше депо',403); if(!empty($x['frozen']))fail('депо на продаже',400); $orph=0;
foreach($d['trains'] as &$t) if((string)($t['depot_id']??'')===$did){ $t['depot_id']=null; $t['route_id']=null; $orph++; } unset($t);
$ref=(int)ceil(depotReplacementCost($x)*REFUND_RATIO); array_splice($d['depots'],$i,1); if($ref>0)addMoney((int)$me['id'],$ref,'delete_depot_refund',null,['depot_id'=>$did]);
logEvent('deleted',(int)$me['id'],companyDisplayName((int)$me['id'])." снёс депо «{$x['name']}» (возврат {$ref} ₽, поездов без депо: {$orph})",[]); worldSave($d); }); sendJson(['ok'=>true]); }
if($action==='buy_train'&&$isPost){ $me=requirePlayer(); $q=readJson(); $did=(string)($q['depot_id']??''); $nm=trim((string)($q['name']??'')); $model=normTrainModel($q['model']??'diesel_line'); $presetId=(string)($q['preset_id']??'');
if($did==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$did,$nm,$model,$presetId){ $w=worldGet(); $d=$w['data']; $i=idxDepot($d,$did); if($i===null)fail('депо не найдено',404); $x=$d['depots'][$i];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваше депо',403); if(!empty($x['is_building']))fail('депо ещё строится',400); if(!empty($x['frozen']))fail('депо на продаже',400);
$c=0; foreach($d['trains'] as $t) if((string)($t['depot_id']??'')===$did)$c++; if($c>=(int)$x['capacity'])fail("в депо нет мест ({$c}/{$x['capacity']})",400);
$wagons=[]; $pn=null;
if($presetId!==''){ $pr=presetById($d,$presetId); if(!$pr)fail('пресет не найден',404); if((int)$pr['owner_id']!==(int)$me['id'])fail('не ваш пресет',403); $model=normTrainModel($pr['model']); $wagons=presetWagonList($pr); $pn=(string)$pr['name']; }
$cost=trainModelCost($model); foreach($wagons as $wg)$cost+=wagonCost($wg);
chargePlayer((int)$me['id'],$cost,'buy_train',null,['depot_id'=>$did,'model'=>$model,'preset'=>$pn,'wagons'=>count($wagons)]);
$id=newId('t'); $end=time()+BUILD_TIME_TRAIN*DAY_DURATION_SEC; if($nm==='')$nm='Поезд '.(playerTrainsCount($d,(int)$me['id'])+1);
$d['trains'][]=['id'=>$id,'name'=>$nm,'owner_id'=>(int)$me['id'],'depot_id'=>$did,'route_id'=>null,'model'=>$model,'wagons'=>$wagons,'health'=>100,'bought_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'transit_end'=>0,'frozen'=>false];
$ver=worldSave($d); logEvent('train_bought',(int)$me['id'],companyDisplayName((int)$me['id'])." заказал поезд «{$nm}» (".trainModelLabel($model).", {$cost} ₽".($pn?", пресет «{$pn}»":'').', вагонов '.count($wagons).')',['train_id'=>$id,'model'=>$model]);
return ['ok'=>true,'version'=>$ver,'train_id'=>$id,'cost'=>$cost,'model'=>$model,'model_label'=>trainModelLabel($model),'max_speed'=>trainModelSpeed($model),'power'=>trainModelPower($model),'wagons'=>count($wagons),'build_end'=>$end]; })); }
if($action==='save_preset'&&$isPost){ $me=requirePlayer(); $q=readJson(); $nm=trim((string)($q['name']??'')); if($nm===''||mb_strlen($nm)>40)sendJson(['error'=>'bad name'],400);
sendJson(runTx(function()use($me,$q,$nm){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; if(playerPresetsCount($d,$pid)>=PRESETS_MAX)fail('максимум '.PRESETS_MAX.' пресетов на компанию',400);
$counts=['passenger'=>0,'baggage'=>0,'mail'=>0]; $tid=(string)($q['from_train_id']??'');
if($tid!==''){ $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i]; if((int)$t['owner_id']!==$pid)fail('не ваш поезд',403);
$model=normTrainModel($t['model']); foreach(($t['wagons']??[]) as $wg){ $nw=normWagonType($wg); if($nw)$counts[$nw]++; } }
else{ $model=normTrainModel($q['model']??'diesel_line'); foreach($counts as $k=>$_)$counts[$k]=max(0,(int)($q['wagons'][$k]??0)); }
$total=array_sum($counts); if($total>MAX_WAGONS_PER_TRAIN)fail('слишком длинный состав (макс '.MAX_WAGONS_PER_TRAIN.' вагонов)',400);
$id=newId('p'); $d['presets'][]=['id'=>$id,'owner_id'=>$pid,'name'=>$nm,'model'=>$model,'wagons'=>$counts]; $ver=worldSave($d,false);
logEvent('train_bought',$pid,companyDisplayName($pid)." сохранил пресет «{$nm}» (".trainModelLabel($model).", вагонов {$total})",['preset_id'=>$id]);
return ['ok'=>true,'version'=>$ver,'preset_id'=>$id,'model'=>$model,'wagons'=>$counts,'total'=>$total]; })); }
if($action==='rename_preset'&&$isPost){ $me=requirePlayer(); $q=readJson(); $pid0=(string)($q['preset_id']??''); $nm=trim((string)($q['name']??'')); if($pid0===''||$nm===''||mb_strlen($nm)>40)sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$pid0,$nm){ $w=worldGet(); $d=$w['data']; $i=idxPreset($d,$pid0); if($i===null)fail('пресет не найден',404); if((int)$d['presets'][$i]['owner_id']!==(int)$me['id'])fail('не ваш пресет',403); $d['presets'][$i]['name']=$nm; return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='delete_preset'&&$isPost){ $me=requirePlayer(); $pid0=(string)(readJson()['preset_id']??''); if($pid0==='')sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$pid0){ $w=worldGet(); $d=$w['data']; $i=idxPreset($d,$pid0); if($i===null)fail('пресет не найден',404); if((int)$d['presets'][$i]['owner_id']!==(int)$me['id'])fail('не ваш пресет',403); array_splice($d['presets'],$i,1); return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='apply_preset'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $pid0=(string)($q['preset_id']??''); if($tid===''||$pid0==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid,$pid0){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==$pid)fail('не ваш поезд',403); if(!empty($t['frozen']))fail('поезд на продаже',400); if(!empty($t['is_building']))fail('поезд ещё не поставлен',400);
$pr=presetById($d,$pid0); if(!$pr)fail('пресет не найден',404); if((int)$pr['owner_id']!==$pid)fail('не ваш пресет',403); $new=presetWagonList($pr); $rid=(string)($t['route_id']??'');
if($rid!==''){ $r=routeById($d,$rid); if($r){ $idx=worldIndex($d); $pol=policiesAll(); $probe=routeCompute($idx,$pol,$r['stations']??[],!empty($r['loop']),(int)$r['owner_id'],[]);
if($probe['ok']){ if(count($new)>$probe['wagon_limit'])fail("станции маршрута принимают максимум {$probe['wagon_limit']} вагон(ов) — пресет длиннее",400);
$others=0.0; foreach($d['trains'] as $y){ if((string)($y['route_id']??'')!==$rid||(string)$y['id']===$tid)continue; $others+=trainCapacityTotal($y); }
$newCap=1.0; foreach($new as $wg)$newCap+=wagonCap($wg); if($others+$newCap>$probe['slots'])fail('ёмкость маршрута не вмещает состав из пресета — нужен второй путь',400); } } }
$refund=0; foreach(($t['wagons']??[]) as $wg)$refund+=wagonCost($wg); $refund=(int)ceil($refund*REFUND_RATIO);
$charge=0; foreach($new as $wg)$charge+=wagonCost($wg); $delta=$charge-$refund;
if($delta>0)chargePlayer($pid,$delta,'apply_preset',null,['train_id'=>$tid,'preset'=>$pr['name']]); elseif($delta<0)addMoney($pid,-$delta,'apply_preset_refund',null,['train_id'=>$tid,'preset'=>$pr['name']]);
$d['trains'][$i]['wagons']=$new; $ver=worldSave($d); $cap=1; foreach($new as $wg)$cap+=wagonCap($wg);
logEvent('train_bought',$pid,companyDisplayName($pid)." пересобрал «{$t['name']}» по пресету «{$pr['name']}»",['delta'=>$delta]);
return ['ok'=>true,'version'=>$ver,'refund'=>$refund,'charge'=>$charge,'net'=>$delta,'wagons'=>count($new),'capacity'=>$cap]; })); }
if($action==='duplicate_train'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $nm=trim((string)($q['name']??'')); if($tid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid,$nm){ $w=worldGet(); $d=$w['data']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==(int)$me['id'])fail('не ваш поезд',403); if(!empty($t['frozen']))fail('поезд на продаже',400);
$did=(string)($t['depot_id']??''); if($did==='')fail('поезд без депо — сначала припишите',400); $di=idxDepot($d,$did); if($di===null)fail('депо не найдено',404); $x=$d['depots'][$di];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваше депо',403); if(!empty($x['is_building'])||!empty($x['frozen']))fail('депо недоступно',400);
$c=0; foreach($d['trains'] as $y) if((string)($y['depot_id']??'')===$did)$c++; if($c>=(int)$x['capacity'])fail("в депо нет мест ({$c}/{$x['capacity']})",400);
$model=normTrainModel($t['model']); $wagons=[]; foreach(($t['wagons']??[]) as $wg){ $nw=normWagonType($wg); if($nw)$wagons[]=$nw; } $wagons=array_slice($wagons,0,MAX_WAGONS_PER_TRAIN);
$cost=trainModelCost($model); foreach($wagons as $wg)$cost+=wagonCost($wg);
chargePlayer((int)$me['id'],$cost,'duplicate_train',null,['from'=>$tid,'model'=>$model,'wagons'=>count($wagons)]);
$id=newId('t'); $end=time()+BUILD_TIME_TRAIN*DAY_DURATION_SEC; if($nm==='')$nm=(string)$t['name'].' (копия)'; if(mb_strlen($nm)>40)$nm=mb_substr($nm,0,40);
$d['trains'][]=['id'=>$id,'name'=>$nm,'owner_id'=>(int)$me['id'],'depot_id'=>$did,'route_id'=>null,'model'=>$model,'wagons'=>$wagons,'health'=>100,'bought_day'=>(int)metaGet('day_count'),'is_building'=>true,'build_end'=>$end,'transit_end'=>0,'frozen'=>false];
$ver=worldSave($d); $cap=1; foreach($wagons as $wg)$cap+=wagonCap($wg);
logEvent('train_bought',(int)$me['id'],companyDisplayName((int)$me['id'])." продублировал «{$t['name']}» → «{$nm}» ({$cost} ₽)",['train_id'=>$id,'from'=>$tid]);
return ['ok'=>true,'version'=>$ver,'train_id'=>$id,'name'=>$nm,'cost'=>$cost,'model'=>$model,'wagons'=>count($wagons),'capacity'=>$cap,'build_end'=>$end]; })); }
if($action==='attach_wagon'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $type=normWagonType($q['type']??'passenger')??'passenger'; $cnt=max(1,(int)($q['count']??1)); if($tid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid,$type,$cnt){ $w=worldGet(); $d=$w['data']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==(int)$me['id'])fail('не ваш поезд',403); if(!empty($t['frozen']))fail('поезд на продаже',400); if(!empty($t['is_building']))fail('поезд ещё не поставлен',400);
$cur=trainWagonCount($t); if($cur+$cnt>MAX_WAGONS_PER_TRAIN)fail('максимум '.MAX_WAGONS_PER_TRAIN.' вагонов на состав',400); $rid=(string)($t['route_id']??'');
if($rid!==''){ $r=routeById($d,$rid); if($r){ $idx=worldIndex($d); $pol=policiesAll(); $probe=routeCompute($idx,$pol,$r['stations']??[],!empty($r['loop']),(int)$r['owner_id'],[]);
if($probe['ok']&&($cur+$cnt)>$probe['wagon_limit'])fail("станции маршрута принимают максимум {$probe['wagon_limit']} вагон(ов) — прокачайте класс станций",400); } }
$cost=$cnt*wagonCost($type); chargePlayer((int)$me['id'],$cost,'attach_wagon',null,['train_id'=>$tid,'type'=>$type,'count'=>$cnt]);
for($k=0;$k<$cnt;$k++)$d['trains'][$i]['wagons'][]=$type; $ver=worldSave($d);
return ['ok'=>true,'version'=>$ver,'cost'=>$cost,'wagons'=>trainWagonCount($d['trains'][$i]),'capacity'=>(int)trainCapacityTotal($d['trains'][$i])]; })); }
if($action==='detach_wagon'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $type=normWagonType($q['type']??'passenger')??'passenger'; $cnt=max(1,(int)($q['count']??1)); if($tid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid,$type,$cnt){ $w=worldGet(); $d=$w['data']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==(int)$me['id'])fail('не ваш поезд',403); if(!empty($t['frozen']))fail('поезд на продаже',400); $list=$t['wagons']??[]; $removed=0;
for($k=count($list)-1;$k>=0&&$removed<$cnt;$k--){ if($list[$k]===$type){ array_splice($list,$k,1); $removed++; } }
if($removed<$cnt)fail('таких вагонов в составе только '.$removed,400); $d['trains'][$i]['wagons']=$list;
$refund=(int)ceil($removed*wagonCost($type)*REFUND_RATIO); if($refund>0)addMoney((int)$me['id'],$refund,'detach_wagon_refund',null,['train_id'=>$tid,'type'=>$type,'count'=>$removed]);
$ver=worldSave($d); return ['ok'=>true,'version'=>$ver,'refund'=>$refund,'removed'=>$removed,'wagons'=>trainWagonCount($d['trains'][$i]),'capacity'=>(int)trainCapacityTotal($d['trains'][$i])]; })); }
if($action==='repair_train'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $ml=isset($q['lat'])&&is_numeric($q['lat'])?(float)$q['lat']:null; $mg=isset($q['lng'])&&is_numeric($q['lng'])?(float)$q['lng']:null; if($tid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid,$ml,$mg){ $w=worldGet(); $d=$w['data']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==(int)$me['id'])fail('не ваш поезд',403); if(!empty($t['frozen'])||!empty($t['is_building']))fail('поезд недоступен',400);
$h=(int)($t['health']??100); if($h>=100)fail('состав в идеальном состоянии',400); $free=false; $dp=depotById($d,(string)($t['depot_id']??''));
if($ml!==null&&$mg!==null&&$dp&&haversineDistanceMeters($ml,$mg,(float)$dp['lat'],(float)$dp['lng'])<=REPAIR_FREE_RADIUS_M)$free=true; $cost=0;
if(!$free){ $cost=(int)ceil(trainReplacementCost($t)*REPAIR_COST_RATIO*(100-$h)/100); if($cost>0)chargePlayer((int)$me['id'],$cost,'repair_train',null,['train_id'=>$tid]); }
$d['trains'][$i]['health']=100; $d['trains'][$i]['health_pending']=0.0;
logEvent('train_repaired',(int)$me['id'],companyDisplayName((int)$me['id'])." отремонтировал «{$t['name']}» (".($free?'бесплатно':"{$cost} ₽").')',[]); worldSave($d);
return ['ok'=>true,'cost'=>$cost,'free'=>$free,'from_health'=>$h]; })); }
if($action==='rename_train'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $nm=trim((string)($q['name']??'')); if($tid===''||$nm===''||mb_strlen($nm)>40)sendJson(['error'=>'bad request'],400);
$ver=runTx(function()use($me,$tid,$nm){ $w=worldGet(); $d=$w['data']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); if((int)$d['trains'][$i]['owner_id']!==(int)$me['id'])fail('не ваш поезд',403); $d['trains'][$i]['name']=$nm; return worldSave($d,false); }); sendJson(['ok'=>true,'version'=>$ver]); }
if($action==='sell_train'&&$isPost){ $me=requirePlayer(); $tid=(string)(readJson()['train_id']??''); if($tid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid){ $w=worldGet(); $d=$w['data']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==(int)$me['id'])fail('не ваш поезд',403); if(!empty($t['frozen']))fail('поезд на продаже',400);
$ref=(int)ceil(trainReplacementCost($t)*REFUND_RATIO); array_splice($d['trains'],$i,1); addMoney((int)$me['id'],$ref,'sell_train',null,['train_id'=>$tid]);
logEvent('deleted',(int)$me['id'],companyDisplayName((int)$me['id'])." списал поезд «{$t['name']}» (возврат {$ref} ₽)",[]);
return ['ok'=>true,'version'=>worldSave($d),'refund'=>$ref]; })); }
if($action==='move_train'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $did=(string)($q['depot_id']??''); if($tid===''||$did==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid,$did){ $w=worldGet(); $d=$w['data']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==(int)$me['id'])fail('не ваш поезд',403); if(!empty($t['is_building']))fail('поезд ещё не поставлен',400); if(!empty($t['frozen']))fail('поезд на продаже',400);
if((string)($t['depot_id']??'')===$did)fail('поезд уже в этом депо',400); $di=idxDepot($d,$did); if($di===null)fail('депо не найдено',404); $x=$d['depots'][$di];
if((int)$x['owner_id']!==(int)$me['id'])fail('не ваше депо',403); if(!empty($x['is_building'])||!empty($x['frozen']))fail('депо недоступно',400);
$c=0; foreach($d['trains'] as $y) if((string)($y['depot_id']??'')===$did)$c++; if($c>=(int)$x['capacity'])fail('в депо нет мест',400);
$cost=empty($t['depot_id'])?0:TRAIN_REBASE_COST; if($cost>0)chargePlayer((int)$me['id'],$cost,'move_train',null,['train_id'=>$tid]);
$d['trains'][$i]['depot_id']=$did; $d['trains'][$i]['route_id']=null; $d['trains'][$i]['transit_end']=time()+BUILD_TIME_TRAIN_REBASE*DAY_DURATION_SEC;
return ['ok'=>true,'version'=>worldSave($d),'cost'=>$cost,'transit_end'=>$d['trains'][$i]['transit_end']]; })); }
if($action==='assign_train'&&$isPost){ $me=requirePlayer(); $q=readJson(); $tid=(string)($q['train_id']??''); $rid=(string)($q['route_id']??''); if($tid==='')sendJson(['error'=>'bad request'],400);
sendJson(runTx(function()use($me,$tid,$rid){ $w=worldGet(); $d=$w['data']; $pid=(int)$me['id']; $i=idxTrain($d,$tid); if($i===null)fail('поезд не найден',404); $t=$d['trains'][$i];
if((int)$t['owner_id']!==$pid)fail('не ваш поезд',403); if(!empty($t['frozen']))fail('поезд на продаже',400);
if($rid===''){ $d['trains'][$i]['route_id']=null; return ['ok'=>true,'version'=>worldSave($d),'assigned'=>false]; }
if(!empty($t['is_building']))fail('поезд ещё не поставлен',400); if((int)($t['transit_end']??0)>time())fail('поезд в пути между депо',400); if(empty($t['depot_id']))fail('поезд без депо',400);
$r=routeById($d,$rid); if(!$r)fail('маршрут не найден',404); if((int)$r['owner_id']!==$pid)fail('не ваш маршрут',403);
$dp=depotById($d,(string)$t['depot_id']); if(!$dp)fail('депо не найдено',404); $stops=array_map('strval',$r['stations']??[]);
if(!in_array((string)($dp['station_id']??''),$stops,true))fail('депо поезда должно быть у одной из станций маршрута',400);
$idx=worldIndex($d); $pol=policiesAll(); $probe=routeCompute($idx,$pol,$stops,!empty($r['loop']),$pid,[]); if(!$probe['ok'])fail('маршрут разорван: '.$probe['reason'],400);
$wag=trainWagonCount($t); if($wag>$probe['wagon_limit'])fail("станции маршрута принимают максимум {$probe['wagon_limit']} вагон(ов) (класс {$probe['min_class']})",400);
if(trainModelTraction($t['model'])==='electric'&&!$probe['fully_powered'])fail('электропоезд требует полностью электрифицированного и запитанного маршрута',400);
$eco=economy($d); $fleet=fleetForRoute($d,$eco,$rid,$probe['fully_powered'],$tid,$t); $info=routeCompute($idx,$pol,$stops,!empty($r['loop']),$pid,$fleet);
if(!$info['ok'])fail('маршрут разорван: '.$info['reason'],400); if($fleet['cap']>$info['slots'])fail("ёмкость маршрута исчерпана ({$fleet['cap']}/{$info['slots']}) — нужен второй путь",400);
$d['trains'][$i]['route_id']=$rid; $ver=worldSave($d);
logEvent('train_assigned',$pid,companyDisplayName($pid)." назначил «{$t['name']}» на «{$r['name']}»".($info['fee_total']>0?" (субаренда {$info['fee_total']} ₽/д)":''),['train_id'=>$tid,'route_id'=>$rid]);
return ['ok'=>true,'version'=>$ver,'assigned'=>true,'income'=>$info['income'],'slots'=>$info['slots'],'capacity'=>$fleet['cap'],'fee'=>$info['fee_total'],'owners'=>$info['owners'],'speed'=>$info['speed'],'line_speed'=>$info['line_speed'],'bottlenecks'=>$info['bottlenecks'],'edge_spans'=>$info['edge_spans']]; })); }
if($action==='gps_repair_nearby'&&$isPost){ $me=requirePlayer(); $q=readJson(); $lat=isset($q['lat'])&&is_numeric($q['lat'])?(float)$q['lat']:null; $lng=isset($q['lng'])&&is_numeric($q['lng'])?(float)$q['lng']:null;
if($lat===null||$lng===null||!is_finite($lat)||!is_finite($lng))fail('bad coords',400);
sendJson(runTx(function()use($me,$lat,$lng){ $w=worldGet(); $d=$w['data']; $fixed=[];
foreach(['stations','depots','substations'] as $k){ if(!isset($d[$k]))continue;
foreach($d[$k] as &$o){ if((int)$o['owner_id']!==(int)$me['id'])continue; if(!empty($o['is_building'])||!empty($o['frozen']))continue; if((int)($o['health']??100)>=100)continue;
if(haversineDistanceMeters($lat,$lng,(float)$o['lat'],(float)$o['lng'])<=REPAIR_FREE_RADIUS_M){ $o['health']=100; $o['health_pending']=0.0; $fixed[]=$o['name']; } } unset($o); }
foreach($d['trains'] as &$t){ if((int)$t['owner_id']!==(int)$me['id'])continue; if(!empty($t['is_building'])||!empty($t['frozen']))continue; if((int)($t['health']??100)>=100)continue;
$dp=depotById($d,(string)($t['depot_id']??'')); if($dp&&haversineDistanceMeters($lat,$lng,(float)$dp['lat'],(float)$dp['lng'])<=REPAIR_FREE_RADIUS_M){ $t['health']=100; $t['health_pending']=0.0; $fixed[]=$t['name']; } } unset($t);
if($fixed){ logEvent('station_repaired',(int)$me['id'],companyDisplayName((int)$me['id']).' бесплатно починил '.count($fixed).' объект(ов) рядом',[]); worldSave($d); }
return ['ok'=>true,'fixed'=>$fixed]; })); }
if($action==='claim_starter_pack'&&$isPost){ $me=requirePlayer(); if((int)($me['starter_claimed']??0)===1)fail('стартовый пакет уже получен',409);
sendJson(runTx(function()use($me){ $pid=(int)$me['id']; $w=worldGet(); $d=$w['data']; $idx=worldIndex($d); $pol=policiesAll();
$mine=[]; foreach($d['stations'] as $s) if((int)$s['owner_id']===$pid&&empty($s['is_building']))$mine[]=(string)$s['id'];
if(count($mine)<2)fail('нужно минимум 2 построенные станции',400); $path=autoRouteForPlayer($d,$idx,$pol,$pid,$mine); if(!$path)fail('не удалось построить автомаршрут — проверьте связность и слоты',400);
$res=createRouteRecord($d,$pid,'Стартовый маршрут',$path,false,true); $bestSid=$path[0]; $bv=-1;
foreach($path as $sid){ $s=stationById($d,(string)$sid); $v=getBuildingIncomePercent((int)$s['buildings']); if($v>$bv){$bv=$v;$bestSid=$sid;} }
$st=stationById($d,(string)$bestSid); $did=newId('d');
$d['depots'][]=['id'=>$did,'name'=>'Депо «'.$st['name'].'»','lat'=>(float)$st['lat']+0.0012,'lng'=>(float)$st['lng']+0.0016,'station_id'=>(string)$st['id'],'owner_id'=>$pid,'level'=>1,'capacity'=>depotCapacityForLevel(1),'built_day'=>(int)metaGet('day_count'),'is_building'=>false,'build_end'=>0,'upgrading'=>false,'upgrade_end'=>0,'upgrade_level'=>0,'frozen'=>false,'health'=>100,'access'=>'inherit','labelOffset'=>['x'=>10,'y'=>4]];
$tid=newId('t'); $d['trains'][]=['id'=>$tid,'name'=>'Поезд 1','owner_id'=>$pid,'depot_id'=>$did,'route_id'=>$res['id'],'model'=>'diesel_line','wagons'=>[],'health'=>100,'bought_day'=>(int)metaGet('day_count'),'is_building'=>false,'build_end'=>0,'transit_end'=>0,'frozen'=>false];
ensureHqs($d); $ver=worldSave($d); db()->prepare("UPDATE players SET starter_claimed=1 WHERE id=:id")->execute([':id'=>$pid]);
logEvent('starter_pack',$pid,companyDisplayName($pid).' получил стартовый пакет: депо, поезд и маршрут',[]);
return ['ok'=>true,'version'=>$ver,'route_id'=>$res['id'],'depot_id'=>$did,'train_id'=>$tid]; })); }
if($action==='dev_bankrupt'&&$isPost){ $me=requirePlayer(); if(!isLocalRequest())sendJson(['error'=>'dev only'],403);
sendJson(runTx(function()use($me){ $pdo=db(); $pdo->prepare("UPDATE players SET money=0 WHERE id=:id")->execute([':id'=>(int)$me['id']]);
$w=worldGet(); $fin=playerFinancials($w['data'],(int)$me['id']); $debt=max(1.0,$fin['upkeep']); $comps=confiscateForDebt($w,(int)$me['id'],$debt);
$a=0;$b=0;$c=0;$e=0;$n=0;$sb=0;$tm=0;
foreach($comps as $ci){ createAuctionFromComponent($pdo,(int)$me['id'],$ci,'bankrupt'); $a+=count($ci['stations']); $b+=count($ci['edges']); $c+=count($ci['depots']); $e+=count($ci['trains']); $n+=count($ci['nodes']); $sb+=count($ci['substations']); $tm+=count($ci['track_maint']??[]); }
worldSave($w['data']); if($comps)logEvent('bankruptcy',(int)$me['id'],companyDisplayName((int)$me['id']).' обанкротился (dev)',[]);
return ['ok'=>true,'debt'=>$debt,'components'=>count($comps),'stations'=>$a,'edges'=>$b,'depots'=>$c,'trains'=>$e,'nodes'=>$n,'substations'=>$sb,'track_maint'=>$tm]; })); }
if($action==='dev_give_money'&&$isPost){ $me=requirePlayer(); if(!isLocalRequest())sendJson(['error'=>'dev only'],403); $a=(int)(readJson()['amount']??10000); if($a<=0||$a>1000000)fail('bad amount',400); addMoney((int)$me['id'],$a,'dev_grant',null,[]); sendJson(['ok'=>true,'amount'=>$a]); }
if($action!=='')sendJson(['error'=>'unknown action'],404);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0b1220">
<title>ЖД ИГРА · Диспетчерская</title>
<link rel="manifest" href="manifest.webmanifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="ЖД Игра">
<link rel="icon" type="image/png" sizes="48x48" href="img/icons/icon-48.png">
<link rel="icon" type="image/png" sizes="192x192" href="img/icons/icon-192.png">
<link rel="apple-touch-icon" href="img/icons/apple-touch-icon.png">
<meta property="og:title" content="ЖД ИГРА · Диспетчерская">
<meta property="og:description" content="Мультиплеерная ЖД-стратегия на реальной карте города: станции, перегоны, подстанции, путевые части и составы из вагонов.">
<meta property="og:image" content="img/icons/icon-512.png">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
:root{
--bg:#070c14; --panel:#0f1826; --panel2:#16233a; --raise:#1d2f4c; --raise2:#254066;
--line:#22344c; --line2:#33507a;
--ink:#e8f0fa; --ink2:#9db0c9; --mut:#6b82a4;
--green:#35d07f; --green-d:#1f9c5c; --green-t:#7bf0b3;
--blue:#3d9bff; --blue-t:#9ccaff;
--amber:#ffb02e; --amber-t:#ffd88a;
--red:#ff5a5a; --red-d:#c9302f; --red-t:#ffa3a3;
--violet:#a97bff; --teal:#24d3c4; --pink:#ff5da2; --lime:#b6e83a;
--wood:#c07a3c; --concrete:#8ea6c6; --steel:#4a7fc1; --water:#3f7fbf;
--scrim:rgba(3,7,13,.74);
--chrome-h:52px; --plate-h:80px; --nav-h:58px;
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
#topbar{display:flex;align-items:center;gap:7px;padding:8px 12px;background:linear-gradient(180deg,#15213a,#0b1220);border-bottom:1px solid var(--line);font-size:13px;z-index:2000;position:relative;flex-shrink:0;overflow-x:auto;white-space:nowrap;animation:dropIn .5s ease backwards}
#topbar::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;background:linear-gradient(90deg,var(--green),var(--blue),var(--violet),var(--amber),var(--green));background-size:300% 100%;animation:stripSlide 11s linear infinite}
.brand{font-family:var(--f-disp);font-weight:700;font-size:13.5px;letter-spacing:.6px;display:flex;align-items:center;gap:8px;flex-shrink:0}
.lamp{width:9px;height:9px;border-radius:50%;background:var(--mut);flex-shrink:0}
.lamp.ok{background:var(--green);box-shadow:0 0 9px var(--green);animation:lampPulse 2.4s ease infinite}
.lamp.warn{background:var(--amber);box-shadow:0 0 9px var(--amber)}
.lamp.err{background:var(--red);box-shadow:0 0 9px var(--red)}
#meChip{width:12px;height:12px;border-radius:4px;background:var(--mut);border:1px solid rgba(255,255,255,.35);flex-shrink:0;box-shadow:0 0 8px rgba(0,0,0,.4)}
#meName{color:var(--ink2);font-weight:700;max-width:150px;overflow:hidden;text-overflow:ellipsis}
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
.ibtn .dotmark.amber{background:var(--amber);color:#3a2400}
#logoutBtn{width:auto;padding:0 12px;font-size:12px;font-family:var(--f-body);font-weight:700;color:var(--red-t);border-color:rgba(255,90,90,.35)}
#logoutBtn:hover{border-color:var(--red);box-shadow:0 4px 14px rgba(255,90,90,.2)}
#ticker{display:none;background:linear-gradient(90deg,#0f3324,#0d2b3a);color:#8bf3bd;border-bottom:1px solid rgba(53,208,127,.3);overflow:hidden;height:29px;align-items:center;flex-shrink:0;z-index:1999;font-weight:600;font-size:12.5px}
.ticker-in{display:inline-block;white-space:nowrap;padding-left:100vw;animation:marquee 18s linear infinite}
@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}
#mainRow{flex:1;display:flex;overflow:hidden;height:100%;position:relative}
#mapWrap{flex:1;position:relative;min-width:0}
#map{position:absolute;inset:0;background:#0b1220;z-index:1}
#map.m-station,#map.m-edge,#map.m-depot,#map.m-route,#map.m-substation,#map.m-trackmaint,#map.m-hq{cursor:crosshair!important}
#mapWrap::after{content:'';position:absolute;inset:0;pointer-events:none;z-index:400;box-shadow:inset 0 0 60px rgba(7,12,20,.3)}
#buildbar{position:absolute;left:12px;top:12px;z-index:600;display:flex;flex-direction:column;gap:6px;background:rgba(11,18,32,.92);border:1px solid var(--line2);border-radius:16px;padding:8px;box-shadow:0 12px 34px rgba(0,0,0,.5);backdrop-filter:blur(9px);animation:dropIn .5s ease backwards}
.btool{position:relative;width:52px;display:flex;flex-direction:column;align-items:center;gap:2px;padding:7px 4px 5px;border-radius:12px;border:1px solid var(--line);background:var(--panel2);color:var(--ink);cursor:pointer;font-family:var(--f-body);transition:all .15s ease}
.btool:hover{border-color:var(--green);transform:translateX(2px);box-shadow:0 4px 14px rgba(53,208,127,.2)}
.btool.active{border-color:var(--green);background:rgba(53,208,127,.16);animation:pulseOutline 1.8s ease infinite}
.btool .bi{font-size:19px;line-height:1}
.btool .bp{font-family:var(--f-mono);font-size:9px;color:var(--amber-t);white-space:nowrap}
.btool .bk{position:absolute;top:-5px;right:-5px;width:16px;height:16px;border-radius:6px;background:var(--raise2);border:1px solid var(--line2);color:var(--ink2);font:700 9px var(--f-mono);display:flex;align-items:center;justify-content:center}
.btool.active .bk{background:var(--green);color:#04241a;border-color:var(--green)}
.bsep{height:1px;background:var(--line);margin:2px 4px}
.btool.util{width:52px;padding:6px 4px}
.btool.util .bi{font-size:16px}
#missionCard{position:absolute;right:12px;top:12px;z-index:590;width:300px;max-width:calc(100vw - 100px);background:rgba(11,18,32,.94);border:1px solid var(--line2);border-radius:16px;padding:12px;box-shadow:0 12px 34px rgba(0,0,0,.5);backdrop-filter:blur(9px);transition:all .3s ease;animation:cardIn .5s ease backwards}
#missionCard.mini{width:auto;padding:0;border-radius:999px}
#missionCard.mini #missionBody{display:none}
#missionCard.gone{opacity:0;transform:translateY(-14px) scale(.94);pointer-events:none}
#msToggle{position:absolute;top:8px;right:8px;width:24px;height:24px;border-radius:8px;border:1px solid var(--line2);background:var(--panel2);color:var(--ink2);cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;z-index:2}
#missionCard.mini #msToggle{position:static;width:40px;height:40px;border-radius:999px;font-size:17px;border-color:var(--green);color:var(--green-t);background:rgba(53,208,127,.12)}
.mission-head{display:flex;align-items:center;gap:9px;margin-bottom:8px;padding-right:26px}
.mission-title{font-family:var(--f-disp);font-size:11.5px;font-weight:500;color:var(--ink)}
.mission-bar{height:5px;background:#0a1120;border-radius:3px;overflow:hidden;margin-bottom:8px}
.mission-bar i{display:block;height:100%;background:linear-gradient(90deg,var(--green),var(--teal));border-radius:3px;transition:width .5s cubic-bezier(.2,.8,.2,1)}
.step{display:flex;gap:8px;align-items:flex-start;padding:4px 0;font-size:12px;color:var(--ink2)}
.step .box{width:16px;height:16px;border-radius:5px;border:1.5px solid var(--line2);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:9px;margin-top:1px}
.step.done .box{background:var(--green);border-color:var(--green);color:#04241a}
.step.done{color:var(--mut)}
.step.done .lbl{text-decoration:line-through}
.step .lbl b{color:var(--ink);font-weight:700}
.step.done .lbl b{color:var(--mut)}
.step .sub{display:block;font-size:10px;color:var(--mut);margin-top:1px}
#scaleHint{position:absolute;left:12px;bottom:34px;z-index:560;display:none;align-items:center;gap:7px;background:rgba(11,18,32,.92);border:1px solid rgba(255,176,46,.4);border-radius:999px;padding:5px 13px;font-size:11.5px;font-weight:700;color:var(--amber-t);box-shadow:0 8px 22px rgba(0,0,0,.45);backdrop-filter:blur(8px)}
#scaleHint.show{display:flex}
#drawer{position:fixed;right:0;top:var(--chrome-h);bottom:0;width:384px;max-width:94vw;background:linear-gradient(180deg,#141f36,#0c1424);border-left:1px solid var(--line2);display:flex;flex-direction:column;z-index:1500;box-shadow:-10px 0 36px rgba(0,0,0,.5);transform:translateX(105%);transition:transform .24s cubic-bezier(.2,.8,.2,1)}
#drawer.open{transform:translateX(0)}
#drawerHead{padding:11px 14px;border-bottom:1px solid var(--line);font-family:var(--f-disp);font-weight:500;font-size:12px;display:flex;align-items:center;gap:9px;color:var(--ink);flex-shrink:0}
#drawerHead .dico{font-size:16px}
#drawerClose{margin-left:auto;background:none;border:none;color:var(--mut);cursor:pointer;font-size:19px;line-height:1;padding:2px 4px}
#drawerClose:hover{color:var(--red)}
#drawerBody{flex:1;overflow-y:auto;padding:13px;font-size:13px;
background:radial-gradient(560px 300px at 0% 0%,rgba(53,208,127,.06),transparent 62%),radial-gradient(520px 380px at 100% 100%,rgba(61,155,255,.05),transparent 62%)}
.dsec{display:none}
.dsec.on{display:block}
.dgroup{background:linear-gradient(180deg,rgba(29,47,76,.5),rgba(15,24,38,.88));border:1px solid var(--line);border-radius:14px;padding:12px;margin-bottom:11px}
.dgroup h3{font-family:var(--f-disp);font-weight:500;font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--ink2);margin:0 0 9px;display:flex;align-items:center;gap:8px}
.dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot.g{background:var(--green);box-shadow:0 0 6px var(--green)}.dot.b{background:var(--blue);box-shadow:0 0 6px var(--blue)}
.dot.a{background:var(--amber);box-shadow:0 0 6px var(--amber)}.dot.v{background:var(--violet);box-shadow:0 0 6px var(--violet)}
.dot.t{background:var(--teal);box-shadow:0 0 6px var(--teal)}.dot.r{background:var(--red);box-shadow:0 0 6px var(--red)}
.chip-count{margin-left:auto;font-family:var(--f-mono);font-size:10px;color:var(--mut);background:var(--panel);border:1px solid var(--line);padding:1px 7px;border-radius:999px}
.btn{display:flex;align-items:center;gap:7px;width:100%;padding:9px 11px;margin:5px 0;border-radius:10px;border:1px solid var(--line2);background:var(--panel2);color:var(--ink);font-family:var(--f-body);font-weight:700;font-size:13px;cursor:pointer;transition:all .15s ease;text-align:left}
.btn:hover:not(:disabled){transform:translateY(-1px);border-color:var(--green);box-shadow:0 5px 16px rgba(53,208,127,.16)}
.btn:disabled{opacity:.42;cursor:not-allowed}
.btn .k{margin-left:auto;font-family:var(--f-mono);font-size:10px;color:var(--mut)}
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
.hint.warn{color:var(--amber-t)}
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
.cc-head{display:flex;align-items:center;gap:9px;margin-bottom:4px}
.cc-swatch{width:28px;height:28px;border-radius:8px;border:2px solid rgba(255,255,255,.4);cursor:pointer;flex-shrink:0;box-shadow:0 2px 10px rgba(0,0,0,.4);transition:transform .15s}
.cc-swatch:hover{transform:scale(1.1)}
.cc-name{font-weight:800;font-size:13.5px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cc-founder{font-size:11px;color:var(--mut);margin-bottom:6px}
.cc-sums{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;font-family:var(--f-mono);font-size:10.5px}
.cc-sum{background:var(--panel2);border:1px solid var(--line);border-radius:7px;padding:2px 8px;color:var(--ink2)}
.cc-sum b{font-weight:700}
.pal-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:7px;margin:10px 0}
.pal-sw{aspect-ratio:1;border-radius:8px;border:2px solid rgba(255,255,255,.25);cursor:pointer;position:relative;transition:transform .13s}
.pal-sw:hover{transform:scale(1.12)}
.pal-sw.sel{border-color:#fff;box-shadow:0 0 0 3px rgba(255,255,255,.25),0 0 14px currentColor}
.pal-sw.used{opacity:.35;cursor:not-allowed}
.pal-sw.used::after{content:'✕';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;text-shadow:0 1px 3px #000}
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
.timg{width:40px;height:20px;object-fit:contain;image-rendering:pixelated;flex-shrink:0;border-radius:4px;background:rgba(10,17,32,.6);border:1px solid var(--line)}
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
.pill.el{background:rgba(255,176,46,.16);color:var(--amber-t);border-color:rgba(255,176,46,.45)}
.pill.di{background:rgba(127,166,216,.14);color:#b9d2f0;border-color:rgba(127,166,216,.4)}
.tools{display:flex;gap:6px;margin-bottom:8px}
.tools .input{flex:1;padding:6px 9px;font-size:12px}
.tools .select{width:122px;padding:6px 7px;font-size:11.5px}
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
#modeBadge{position:absolute;left:86px;top:12px;z-index:580;display:none;align-items:center;gap:8px;background:rgba(11,18,32,.92);border:1px solid var(--line2);border-radius:999px;padding:6px 14px;font-size:12px;font-weight:700;color:var(--ink);box-shadow:0 8px 24px rgba(0,0,0,.4);backdrop-filter:blur(8px)}
#modeBadge.show{display:flex}
#modeBadge .ic{font-size:14px}
#dock{position:absolute;left:50%;transform:translateX(-50%) translateY(14px);bottom:16px;z-index:620;display:none;flex-direction:column;gap:9px;background:rgba(11,18,32,.95);border:1px solid var(--line2);border-radius:16px;padding:12px 14px;min-width:min(580px,92%);max-width:min(700px,94%);box-shadow:0 20px 50px rgba(0,0,0,.55);backdrop-filter:blur(10px);opacity:0;transition:opacity .2s ease,transform .2s ease}
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
.schip i.up{color:var(--blue-t)}
.schip button{background:none;border:none;color:var(--mut);cursor:pointer;font-size:13px;line-height:1;padding:0 3px}
.schip button:hover{color:var(--red)}
.dock-actions{display:flex;gap:8px;flex-wrap:wrap}
.dock-actions .btn{margin:0;flex:1;min-width:130px;justify-content:center}
.dock-note{font-size:11px;color:var(--mut);line-height:1.45}
.dock-note.bad{color:var(--red-t)}
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
.ctx-sprite{display:flex;justify-content:center;margin:2px 0 6px}
.ctx-sprite img{width:132px;height:66px;object-fit:contain;image-rendering:pixelated;border:1px solid var(--line2);border-radius:9px;background:rgba(10,17,32,.6);padding:4px}
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
#depotModal{z-index:2900}
#depotModal .dep-train{display:flex;flex-direction:column;gap:6px}
#depotModal .dep-train .dock-actions .btn{min-width:0;flex:0 0 auto}
#actionModal{z-index:5000}
#loginModal{z-index:6000}
#loginModal .dialog{width:min(94vw,400px);text-align:center;padding:28px 26px}
.login-title{font-family:var(--f-disp);font-weight:700;font-size:25px;margin:2px 0 6px;background:linear-gradient(90deg,var(--green-t),var(--blue),var(--amber-t));-webkit-background-clip:text;background-clip:text;color:transparent}
.login-sub{color:var(--ink2);font-size:12.5px;margin:0 0 14px;line-height:1.55}
.login-chips{display:flex;gap:6px;justify-content:center;flex-wrap:wrap;margin-bottom:16px}
.lchip{font-size:10.5px;font-weight:700;color:var(--ink2);background:var(--panel2);border:1px solid var(--line2);padding:3px 9px;border-radius:999px}
#loginName{text-align:center;font-size:15px;font-weight:600}
.login-hint{font-size:11px;color:var(--mut);margin:11px 0 0}
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
.obs-row b{color:var(--amber-t)}
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
.choice .cs{font-size:10.5px;color:var(--mut);font-family:var(--f-mono);margin-top:2px;display:block}
.mimg{width:72px;height:36px;flex-shrink:0;image-rendering:pixelated;border:1px solid var(--line2);border-radius:6px;background:rgba(10,17,32,.7);display:flex;align-items:center;justify-content:center;font-size:17px}
.choice .mrow{display:flex;gap:10px;align-items:center;flex:1}
.sell-lists{flex:1;overflow-y:auto;margin-bottom:12px;border:1px solid var(--line);padding:10px;border-radius:12px;background:rgba(10,17,32,.5);max-height:44vh}
.sell-lists h4{margin:8px 0 6px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--mut);font-family:var(--f-disp);font-weight:500}
.sell-lists h4:first-child{margin-top:0}
.sell-row{display:flex;align-items:center;gap:8px;padding:5px 6px;border-radius:8px;font-size:12.5px;cursor:pointer;color:var(--ink)}
.sell-row:hover{background:var(--panel2)}
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
.lg .sw.sq{border-radius:3px}
.lg .sw.hatch{border-radius:3px;background-image:repeating-linear-gradient(45deg,rgba(0,0,0,.55) 0 2px,transparent 2px 5px)!important}
.lg-sec{margin-top:14px;padding-top:12px;border-top:1px solid var(--line)}
.lg-sec h4{margin:0 0 8px;font-size:10.5px;font-family:var(--f-disp);font-weight:500;text-transform:uppercase;letter-spacing:1.2px;color:var(--mut)}
.demo-track{background:rgba(10,17,32,.6);border:1px solid var(--line);border-radius:11px;padding:10px 12px;margin-top:8px}
.demo-rail{position:relative;height:26px;margin:6px 0 4px}
.demo-rail::before{content:'';position:absolute;left:0;right:0;top:12px;height:2px;background:linear-gradient(90deg,#33507a,#7fa6d8,#33507a)}
.demo-st{position:absolute;top:6px;width:14px;height:14px;border-radius:50%;background:#1f9c5c;border:2px solid #fff;transform:translateX(-50%)}
.demo-st.pass{background:#98a4b4;width:10px;height:10px;top:8px}
.demo-tr{position:absolute;top:9px;left:2%;width:18px;height:8px;border-radius:2px;background:var(--amber);border:1px solid #07101c;animation:demoRun 8s cubic-bezier(.4,0,.6,1) infinite}
.demo-tr::after{content:'';position:absolute;top:1px;left:-11px;width:8px;height:6px;border-radius:1px;background-color:var(--amber);opacity:.8;border:1px solid #07101c;background-image:repeating-linear-gradient(45deg,rgba(0,0,0,.5) 0 1px,transparent 1px 3px)}
.demo-tr::before{content:'';position:absolute;top:1px;left:-22px;width:8px;height:6px;border-radius:1px;background-color:var(--amber);opacity:.8;border:1px solid #07101c;background-image:repeating-linear-gradient(-45deg,rgba(0,0,0,.5) 0 1px,transparent 1px 3px)}
@keyframes demoRun{0%{left:4%}12%{left:24%}22%{left:33%}30%,44%{left:35%}56%{left:66%}66%{left:75%}74%,88%{left:77%}100%{left:96%}}
#chat{position:fixed;right:0;top:var(--chrome-h);bottom:0;width:292px;background:linear-gradient(180deg,#141f36,#0c1424);border-left:1px solid var(--line2);display:flex;flex-direction:column;z-index:1500;box-shadow:-8px 0 30px rgba(0,0,0,.45);transition:transform .22s ease;transform:translateX(105%)}
#chat.show{transform:translateX(0)}
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
#loading{position:fixed;inset:0;background:var(--scrim);display:none;align-items:center;justify-content:center;z-index:99999;backdrop-filter:blur(4px)}
.lbox{background:linear-gradient(180deg,#1a2740,#101a2b);border:1px solid var(--line2);border-radius:16px;padding:24px 34px;box-shadow:0 20px 60px rgba(0,0,0,.55);display:flex;flex-direction:column;align-items:center;gap:14px;min-width:250px}
.spin{width:44px;height:44px;border:3px solid var(--line2);border-top-color:var(--green);border-right-color:var(--teal);border-radius:50%;animation:spin .8s linear infinite}
#loadText{font-size:13.5px;font-weight:700;color:var(--ink2)}
.lbl{background:#fff;border:1px solid #c6d2e2;border-radius:7px;padding:2px 7px;font-size:12px;font-family:var(--f-body);font-weight:600;color:#1e2b3a;white-space:nowrap;user-select:none;box-shadow:0 2px 6px rgba(16,30,54,.2);touch-action:none}
.lbl.mine{border-color:var(--oc,#1f9c5c)}
.lbl.drag{cursor:grab}
.lbl.dragging{background:#eaf4ff;border-color:#3d9bff;cursor:grabbing;box-shadow:0 6px 16px rgba(61,155,255,.45)}
.lbl.build{background:#fff6df;border-color:#e0bf5c;color:#8a6a00;animation:buildPulse 1.9s ease infinite}
.lbl.sale{background:#fff3d0;border-color:#e0ac33;color:#8a6a00}
.lbl.bank{background:#eceff4;border-color:#96a2b3;color:#5b6c85}
.lbl.dead{background:#f3f5f8;border-color:#b8c2d0;color:#7b8798;font-style:italic}
.lbl.hq{font-weight:800;border-width:2px}
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
.sbWrap{position:relative;width:24px;height:24px}
.objRing{position:absolute;inset:-3px;border-radius:10px;border:1.5px solid;opacity:.9;pointer-events:none}
.sbBox{position:absolute;inset:0;border-radius:7px;border:2px solid #07101c;display:flex;align-items:center;justify-content:center;font-size:13px;box-shadow:0 2px 7px rgba(0,0,0,.5);background:#ffb02e}
.sbBox.own{border-color:#fff}
.sbBox.build{animation:buildPulse 1.9s ease infinite}
.sbBox.dead{filter:grayscale(.55) brightness(.85)}
.tmWrap{position:relative;width:24px;height:24px}
.tmBox{position:absolute;inset:0;border-radius:7px;border:2px solid #07101c;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 7px rgba(0,0,0,.5);background:#d9a441}
.tmBox.own{border-color:#fff}
.tmBox.build{animation:buildPulse 1.9s ease infinite}
.tmBox.dead{filter:grayscale(.55) brightness(.85)}
.hqWrap{position:relative;width:30px;height:30px}
.hqBox{position:absolute;inset:0;border-radius:9px;border:2.5px solid #07101c;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 3px 10px rgba(0,0,0,.55),0 0 16px rgba(255,255,255,.12);background:#a97bff}
.hqBox.own{border-color:#fff}
.nbad{width:18px;height:18px;border-radius:6px;border:1.5px solid #07101c;color:#fff;font:800 10px var(--f-mono);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.5);white-space:nowrap}
.tw{position:relative;width:1px;height:1px;overflow:visible;pointer-events:none}
.tb{position:absolute;left:0;top:0;border-radius:2px;border:1px solid rgba(6,12,22,.85);box-shadow:0 1px 4px rgba(0,0,0,.45);will-change:transform}
.tb.wg{border-radius:1.5px}
.tb.wg::before{content:'';position:absolute;left:calc(-1 * var(--cp,2px));top:50%;width:var(--cp,2px);height:1px;background:rgba(6,12,22,.8);transform:translateY(-50%)}
.tb.wg.ps{opacity:.85}
.tb.wg.bg{opacity:.95;background-image:repeating-linear-gradient(45deg,rgba(0,0,0,.5) 0 2px,transparent 2px 5px)}
.tb.wg.ml{opacity:.95;background-image:repeating-linear-gradient(-45deg,rgba(0,0,0,.5) 0 2px,transparent 2px 5px)}
.tb.el::after{content:'';position:absolute;left:50%;top:-4px;width:1px;height:4px;background:rgba(255,255,255,.85);transform:translateX(-50%)}
.tb.loco.dw{animation:dwPulse 1.1s ease infinite}
.tb.loco.bk{animation:bkPulse 1s ease infinite}
@keyframes dwPulse{0%,100%{box-shadow:0 0 0 2px rgba(255,255,255,.45),0 1px 6px rgba(0,0,0,.5)}50%{box-shadow:0 0 0 4px rgba(255,255,255,.12),0 1px 6px rgba(0,0,0,.5)}}
@keyframes bkPulse{0%,100%{box-shadow:0 0 0 2px rgba(255,176,46,.75),0 1px 6px rgba(0,0,0,.5)}50%{box-shadow:0 0 0 5px rgba(255,176,46,.12),0 1px 6px rgba(0,0,0,.5)}}
.tl{position:absolute;left:0;top:0;transform:translate(-50%,10px);font:700 9.5px var(--f-mono);color:#e8f0fa;background:rgba(9,15,26,.86);border:1px solid #33507a;border-radius:5px;padding:0 4px;white-space:nowrap}
.fx{position:relative}
.fx i{position:absolute;left:50%;top:50%;width:30px;height:30px;margin:-15px 0 0 -15px;border-radius:50%;border:2px solid currentColor;animation:fxRing .72s ease-out forwards}
.fx.sm i{width:20px;height:20px;margin:-10px 0 0 -10px;border-width:1.5px;animation-duration:.5s}
@keyframes fxRing{0%{transform:scale(.35);opacity:.95}100%{transform:scale(1.5);opacity:0}}
.tip{background:rgba(9,15,26,.92)!important;color:#e8f0fa!important;border:1px solid #33507a!important;border-radius:8px!important;padding:4px 9px!important;font-size:11.5px!important;font-family:var(--f-mono)!important;box-shadow:0 4px 14px rgba(0,0,0,.35)!important;line-height:1.45!important}
.tip::before{display:none!important}
.leaflet-container{touch-action:none;font-family:var(--f-body);background:#0b1220}
.leaflet-control-scale-line{background:rgba(9,15,26,.8)!important;color:#cfe0f5!important;border-color:#33507a!important;font-family:var(--f-mono)!important;font-size:10px!important;border-radius:4px}
.leaflet-bar a{background:#16233a!important;color:#cfe0f5!important;border-color:#22344c!important}
.leaflet-bar a:hover{background:#1d2f4c!important}
.leaflet-control-attribution{background:rgba(9,15,26,.72)!important;color:#8ea3bd!important;font-size:9.5px!important}
.leaflet-control-attribution a{color:#9ccaff!important}
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
/* ---------- МОБИЛЬНЫЙ ХРОМ: плашка в 3 строки + нижняя навигация ---------- */
#mPlate{display:none;position:fixed;top:0;left:0;right:0;z-index:2100;flex-direction:column;gap:2px;padding:calc(6px + env(safe-area-inset-top)) 12px 7px;background:linear-gradient(180deg,#15213a,#0b1220);border-bottom:1px solid var(--line2);cursor:pointer}
#mPlate .mp-r1{display:flex;align-items:center;gap:7px;min-height:18px}
#mPlate .mp-chip{width:12px;height:12px;border-radius:4px;border:1px solid rgba(255,255,255,.4);flex-shrink:0}
#mPlate .mp-name{font-weight:800;font-size:12.5px;color:var(--ink2);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#mPlate .mp-snd{width:26px;height:22px;border-radius:7px;border:1px solid var(--line);background:var(--panel2);color:var(--ink2);font-size:12px;cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:0}
#mPlate .mp-r2{font:800 21px/1.15 var(--f-mono);color:var(--green-t);letter-spacing:.3px}
#mPlate .mp-r3{font:10px/1.3 var(--f-mono);color:var(--mut)}
#mPlate .mp-r3 b{color:var(--blue-t);font-weight:700}
#mPlate .mp-bar{position:absolute;left:0;bottom:0;height:2px;width:0;background:linear-gradient(90deg,var(--blue),var(--green));transition:width 1s linear}
#mNav{display:none;position:fixed;left:0;right:0;bottom:0;z-index:2100;background:linear-gradient(0deg,#15213a,#0b1220);border-top:1px solid var(--line2);padding:6px 8px calc(6px + env(safe-area-inset-bottom));overflow-x:auto;white-space:nowrap;scrollbar-width:none}
#mNav::-webkit-scrollbar{display:none}
#mNav button{position:relative;width:44px;height:40px;margin-right:6px;border-radius:12px;border:1px solid var(--line);background:var(--panel2);color:var(--ink2);font-size:17px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
#mNav button.on{border-color:var(--green);color:var(--green-t);background:rgba(53,208,127,.12)}
#mNav .dotmark{position:absolute;top:-4px;right:-4px;min-width:15px;height:15px;padding:0 3px;border-radius:8px;background:var(--red);color:#fff;font:700 9px var(--f-mono);display:flex;align-items:center;justify-content:center;border:2px solid #0b1220}
#mNav .dotmark.amber{background:var(--amber);color:#3a2400}
@media (max-width:760px){
#topbar{display:none!important}
#mPlate{display:flex}
#mNav{display:block}
body{padding-top:calc(var(--plate-h) + env(safe-area-inset-top))}
.leaflet-control-zoom{display:none!important}
#buildbar{left:8px;top:8px;bottom:auto;padding:6px;gap:5px}
.btool{width:44px;padding:6px 3px 4px}.btool .bp{display:none}.btool .bi{font-size:18px}
#modeBadge{left:66px;top:8px;font-size:11px;padding:5px 10px}
#missionCard{right:8px;top:8px;width:min(212px,calc(100vw - 84px))}
#scaleHint{left:8px;bottom:calc(var(--nav-h) + 10px + env(safe-area-inset-bottom));font-size:10.5px}
#dock{bottom:calc(var(--nav-h) + 10px + env(safe-area-inset-bottom));min-width:94%;padding:10px}
#drawer,#chat{top:auto;bottom:calc(var(--nav-h) + env(safe-area-inset-bottom));left:0;right:0;width:100%;max-width:100%;max-height:calc(100vh - var(--nav-h) - var(--plate-h) - 24px);border-left:none;border-top:1px solid var(--line2);border-radius:18px 18px 0 0;transform:translateY(105%);box-shadow:0 -14px 40px rgba(0,0,0,.55)}
#drawer.open,#chat.show{transform:translateY(0)}
#ctx{left:8px!important;right:8px!important;top:auto!important;bottom:calc(var(--nav-h) + 8px + env(safe-area-inset-bottom))!important;min-width:0;max-width:none;max-height:56vh}
.modal{align-items:flex-end;padding:10px 10px calc(var(--nav-h) + 10px + env(safe-area-inset-bottom))}
.dialog{width:100%;max-height:calc(100vh - var(--nav-h) - var(--plate-h) - 20px)}
.dialog.wide{width:100%}
.dialog.xwide{width:100%;height:calc(100vh - var(--nav-h) - var(--plate-h) - 20px)}
#loginModal{align-items:center;padding-bottom:14px}
.pal-grid{grid-template-columns:repeat(6,1fr)}
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
<button id="btnSound" class="ibtn on" title="Звук включён" aria-label="Звук">🔊</button>
<button id="btnRoutes" class="ibtn" title="Маршруты" aria-label="Маршруты">🧭</button>
<button id="btnInfra" class="ibtn" title="Инфраструктура: станции, депо, поезда, подстанции, ПЧ" aria-label="Инфраструктура">🏗</button>
<button id="btnMarket" class="ibtn" title="Рынок: аукционы и продажи · клавиша /" aria-label="Рынок">🏷<span class="dotmark amber" id="lotsMark" style="display:none">0</span></button>
<button id="btnCompanies" class="ibtn" title="Компании и рейтинг" aria-label="Компании">👥</button>
<button id="btnRepairTop" class="ibtn" title="Ведомость ремонта" aria-label="Ремонт">🔧<span class="dotmark" id="repairMark" style="display:none">0</span></button>
<button id="btnChart" class="ibtn" title="Статистика и графики" aria-label="Статистика">📈</button>
<button id="btnNews" class="ibtn" title="Новости" aria-label="Новости">📰<span class="dotmark" id="newsMark" style="display:none">0</span></button>
<button id="btnGps" class="ibtn" title="GPS: бесплатный ремонт рядом" aria-label="GPS">📍</button>
<button id="btnChat" class="ibtn" title="Чат диспетчеров · клавиша C" aria-label="Чат">💬</button>
<button id="btnCompany" class="ibtn" title="Компания: цвет, доступ, вывеска" aria-label="Компания">🏢</button>
<button id="btnSettings" class="ibtn" title="Настройки карты · клавиша O" aria-label="Настройки">⚙️</button>
<button id="btnHelp" class="ibtn" title="Справка и легенда · клавиша H" aria-label="Справка">❓</button>
<button id="logoutBtn" class="ibtn" style="display:none" aria-label="Выйти">Выйти</button>
</div>
<div id="ticker"><div id="tickerIn" class="ticker-in"></div></div>
<div id="mainRow">
<div id="mapWrap">
<div id="map"></div>
<div id="buildbar">
<button class="btool" id="mStation" title="Станция · клавиша 1" aria-label="Станция"><span class="bi">🚉</span><span class="bp"><span id="pStation">250</span> ₽</span><span class="bk">1</span></button>
<button class="btool" id="mEdge" title="Перегон / ветка · клавиша 2 · макс 20 км" aria-label="Перегон"><span class="bi">🛤</span><span class="bp"><span id="pEdge">1.00</span> ₽/м</span><span class="bk">2</span></button>
<button class="btool" id="mDepot" title="Депо · клавиша 3" aria-label="Депо"><span class="bi">🏭</span><span class="bp"><span id="pDepot">1500</span> ₽</span><span class="bk">3</span></button>
<button class="btool" id="mRoute" title="Маршрут · клавиша 4" aria-label="Маршрут"><span class="bi">🧭</span><span class="bp">0 ₽</span><span class="bk">4</span></button>
<button class="btool" id="mSub" title="Подстанция · питание 8 км · клавиша 5" aria-label="Подстанция"><span class="bi">⚡</span><span class="bp"><span id="pSubstation">2500</span> ₽</span><span class="bk">5</span></button>
<button class="btool" id="mTrackMaint" title="Путевая часть · двухпутка 10 км · клавиша 6" aria-label="Путевая часть"><span class="bi">⚒</span><span class="bp"><span id="pTrackMaint">2000</span> ₽</span><span class="bk">6</span></button>
<div class="bsep"></div>
<button class="btool util" id="btnFitBar" title="Вписать всю сеть в экран" aria-label="Вписать сеть"><span class="bi">🎯</span></button>
</div>
<div id="modeBadge"><span class="ic" id="mbIc">🚉</span><span id="mbTx">Режим</span></div>
<div id="missionCard">
<button id="msToggle" title="Свернуть / развернуть" aria-label="Свернуть миссию">▾</button>
<div id="missionBody"></div>
</div>
<div id="scaleHint">🔍 Обзор: полотно, точки станций и штабы — приблизьте до шкалы 2 км для деталей</div>
<div id="dock"></div>
</div>
</div>
<div id="drawer">
<div id="drawerHead"><span class="dico" id="drawerIco">🧭</span><span id="drawerTitle">Панель</span><button id="drawerClose" title="Закрыть · Esc" aria-label="Закрыть панель">×</button></div>
<div id="drawerBody">
<div class="dsec" id="dRoutes">
<div class="dgroup">
<h3><span class="dot v"></span>Маршруты <span class="chip-count" id="cntRoutes">0</span></h3>
<button class="btn btn-violet" id="btnNewRoute">🧭 Новый маршрут <span class="k">4</span></button>
<button class="btn" id="btnAuto">✨ Автомаршруты по сети</button>
<div id="routesList"></div>
<div class="hint">Клик по маршруту — меню: назначить поезд, остановки, переименовать, закрыть. Линии маршрутов на карте не рисуются — только составы.</div>
</div>
</div>
<div class="dsec" id="dInfra">
<div class="dgroup">
<h3><span class="dot t"></span>Мои станции <span class="chip-count" id="cntSt">0</span></h3>
<div class="tools">
<input type="text" id="stSearch" class="input" placeholder="Поиск…" autocomplete="off" aria-label="Поиск станций">
<select id="stSort" class="select" aria-label="Сортировка станций">
<option value="routes">по маршрутам</option>
<option value="name">по имени</option>
<option value="class">по классу</option>
<option value="health">по износу</option>
<option value="bonus">по бонусу</option>
</select>
</div>
<div id="myStations"></div>
</div>
<div class="dgroup">
<h3><span class="dot t"></span>Депо <span class="chip-count" id="cntDepots">0</span></h3>
<div id="depotList"></div>
<div class="hint">Клик по депо на карте или здесь — модалка со списком поездов и всеми действиями.</div>
</div>
<div class="dgroup">
<h3><span class="dot a"></span>Поезда <span class="chip-count" id="cntTrains">0</span></h3>
<button class="btn btn-sm" id="btnPresets">📋 Пресеты составов</button>
<div id="trainList"></div>
<div class="hint">Ёмкость = локомотив (1) + вагоны: пассажирский +1, багажный/почтовый +2 (штриховка). Грузы выгодны дальше 10 км. Состав можно <b style="color:var(--ink)">дублировать</b> или сохранить как <b style="color:var(--ink)">пресет</b>.</div>
</div>
<div class="dgroup">
<h3><span class="dot a"></span>Подстанции <span class="chip-count" id="cntSubs">0</span></h3>
<div id="subList"></div>
</div>
<div class="dgroup">
<h3><span class="dot a"></span>Путевые части <span class="chip-count" id="cntTm">0</span></h3>
<div id="tmList"></div>
</div>
</div>
<div class="dsec" id="dMarket">
<div class="dgroup">
<h3><span class="dot a"></span>Рынок <span class="chip-count" id="cntLots">0 моих</span></h3>
<div id="lotsList"></div>
<div class="hint">Лоты появляются при банкротстве или продаже. Продать объект — через его меню на карте «🏷 На продажу».</div>
</div>
</div>
<div class="dsec" id="dCompanies">
<div class="dgroup">
<h3><span class="dot g"></span>Рейтинг · ₽/день</h3>
<div id="ratingList"></div>
</div>
<div class="dgroup">
<h3><span class="dot b"></span>Компании</h3>
<div id="playersList"></div>
<div class="hint">Клик по 🏛 штабу компании на карте — полная карточка: основатель, инфраструктура, парк, финансы, графики.</div>
</div>
</div>
<div class="dsec" id="dSettings">
<div class="dgroup">
<h3><span class="dot b"></span>Вид карты</h3>
<div class="vcap">Подложка <span class="sp">M — циклически</span></div>
<select id="mapStyle" class="select" aria-label="Подложка карты">
<option value="voyager" selected>Carto Voyager</option>
<option value="positron">Carto Positron (светлая)</option>
<option value="osm">Стандартная OSM</option>
<option value="sat">🛰 Спутник (Google)</option>
<option value="hybrid">🛰 Спутник + подписи (Google)</option>
<option value="esri">🛰 Спутник (Esri, запасной)</option>
</select>
<div class="vcap">Яркость подложки <b><span id="opVal">100</span>%</b></div>
<input type="range" id="mapOpacity" min="20" max="100" value="100" aria-label="Яркость подложки">
<div class="vrow"><label><input type="checkbox" id="tgTrains" checked> 🚆 Поезда в движении <span class="k" style="margin-left:auto;font:700 10px var(--f-mono);color:var(--mut)">Z</span></label></div>
<div class="vrow"><label><input type="checkbox" id="tgTrainLabels"> 🏷 Подписи поездов</label></div>
<div class="vrow"><label><input type="checkbox" id="tgBlocks" checked> 🚦 Блок-участки (черточки) <span class="k" style="margin-left:auto;font:700 10px var(--f-mono);color:var(--mut)">&lt;</span></label></div>
<div class="vrow"><label><input type="checkbox" id="tgBlockOcc" checked> 🟠 Подсветка занятости <span class="k" style="margin-left:auto;font:700 10px var(--f-mono);color:var(--mut)">&gt;</span></label></div>
<div class="vrow"><label><input type="checkbox" id="tgBridges" checked> 🌉 Мосты и путепроводы</label></div>
<div class="vrow"><label><input type="checkbox" id="tgNodes" checked> 🔩 Узлы веток</label></div>
<div class="vrow"><label><input type="checkbox" id="tgSubCircles"> ⚡ Радиусы подстанций (8 км) <span class="k" style="margin-left:auto;font:700 10px var(--f-mono);color:var(--mut)">B</span></label></div>
<div class="vrow"><label><input type="checkbox" id="tgTmCircles"> ⚒ Радиусы ПЧ (10 км) <span class="k" style="margin-left:auto;font:700 10px var(--f-mono);color:var(--mut)">N</span></label></div>
<div class="vrow"><label><input type="checkbox" id="tgLabels" checked> 🏷 Подписи объектов <span class="k" style="margin-left:auto;font:700 10px var(--f-mono);color:var(--mut)">V</span></label></div>
<div class="vrow"><label><input type="checkbox" id="tgFade"> 🌫 Полупрозрачные перегоны <span class="k" style="margin-left:auto;font:700 10px var(--f-mono);color:var(--mut)">X</span></label></div>
<div class="vrow"><label><input type="checkbox" id="tgAfford"> 💰 Подсвечивать доступные лоты</label></div>
<div class="vrow"><label><input type="checkbox" id="tgAllInfra"> 🗺 Вся инфраструктура на обзоре</label></div>
<button id="btnFit" class="btn btn-sm" style="margin-top:8px">🎯 Вписать всю сеть</button>
<div class="hint" style="margin-top:6px">Обзор (шкала мельче 2 км): полотно, точки станций и штабы — карта лёгкая. Детали (2 км и крупнее): всё остальное. Скорость симуляции фиксирована (×8); пауза — Пробел. Спутник — для визуального планирования трасс; если Google не отвечает, игра сама предложит Esri.</div>
</div>
<div class="dgroup">
<h3><span class="dot b"></span>Метровая сетка</h3>
<div class="vrow"><label><input type="checkbox" id="gridOn"> Показывать сетку</label></div>
<div class="vrow"><label><input type="checkbox" id="gridSnap"> Привязка кликов к сетке</label></div>
<div class="vcap">Шаг сетки</div>
<select id="gridStep" class="select" aria-label="Шаг сетки">
<option value="10">10 м</option><option value="50" selected>50 м</option><option value="100">100 м</option>
<option value="250">250 м</option><option value="500">500 м</option><option value="1000">1000 м</option>
</select>
</div>
<div class="dgroup">
<button id="resetSettings" class="btn btn-danger">↺ Сбросить все настройки вида</button>
</div>
<div class="dgroup" id="devCard" style="display:none">
<h3><span class="dot r"></span>Dev</h3>
<button id="devMoney" class="btn">💵 +10 000 ₽</button>
<button id="devBank" class="btn btn-danger">💥 Обанкротить себя</button>
</div>
</div>
<div class="dsec" id="dCompany">
<div class="dgroup">
<h3><span class="dot t"></span>Компания</h3>
<div class="cc-head">
<span class="cc-swatch" id="ccColor" title="Цвет компании"></span>
<span class="cc-name" id="ccName">—</span>
</div>
<div class="cc-founder" id="ccFounder">Основатель: не указан</div>
<button class="btn btn-sm" id="btnEditCompany">✏️ Название и основатель</button>
<button class="btn btn-sm" id="btnGoHq" style="margin-left:6px">🏛 К штабу</button>
<div class="vcap" style="margin-top:12px">Цвет компании · 32 оттенка</div>
<div class="pal-grid" id="palGrid"></div>
<button id="cmSave" class="btn btn-primary btn-sm">🎨 Применить цвет</button>
<div class="hint" id="cmInfo" style="margin-top:6px">Один цвет на всё: перегоны, поезда, станции, узлы, подстанции, ПЧ и штаб.</div>
</div>
<div class="dgroup">
<h3><span class="dot t"></span>Доступ для других компаний</h3>
<div class="swrow"><span class="swl">🛤 Субаренда путей<small>Чужие поезда едут по вашим перегонам · вы получаете 30 % их дохода пропорционально км</small></span><label class="switch"><input type="checkbox" id="ccTrackage"><i></i></label></div>
<div class="swrow"><span class="swl">🚉 Остановки на станциях<small>Чужие маршруты могут останавливаться на ваших станциях</small></span><label class="switch"><input type="checkbox" id="ccStops"><i></i></label></div>
<div class="swrow"><span class="swl">🔌 Подключение к сети<small>К вашим станциям и узлам можно строить свои перегоны и врезать станции</small></span><label class="switch"><input type="checkbox" id="ccConnect"><i></i></label></div>
<div class="cc-sums"><span class="cc-sum">аренда вам <b class="pos" id="ccIn">+0 ₽</b></span><span class="cc-sum">вы платите <b class="neg" id="ccOut">−0 ₽</b></span></div>
<div class="hint" style="margin-top:6px">Точечно доступ переопределяется в меню объекта: «как в компании / открыт / закрыт».</div>
</div>
</div>
</div>
</div>
<div id="depotModal" class="modal"><div class="dialog wide">
<h2><span id="depotTitle">🏭 Депо</span><button class="close-x" data-close="depotModal" aria-label="Закрыть">&times;</button></h2>
<div id="depotBody" class="mbody"></div>
</div></div>
<div id="sellModal" class="modal"><div class="dialog wide">
<h3>🏷 Выставить лот на рынок</h3>
<div class="sell-lists">
<div id="sellStations"></div><div id="sellEdges"></div><div id="sellNodes"></div><div id="sellDepots"></div><div id="sellTrains"></div><div id="sellSubs"></div><div id="sellTm"></div>
</div>
<div style="font-size:12px;margin-bottom:6px;color:var(--ink2)">Цена: от <b id="minPrice" style="color:var(--amber-t);font-family:var(--f-mono)">0</b> до <b id="maxPrice" style="color:var(--amber-t);font-family:var(--f-mono)">0</b> ₽</div>
<input type="number" id="sellPrice" class="input" style="font-family:var(--f-mono);font-weight:700;font-size:15px" aria-label="Цена лота">
<div class="btn-row"><button class="b-cancel" data-close="sellModal">Отмена</button><button id="sellOk" class="b-ok">Выставить</button></div>
</div></div>
<div id="ctx"><span class="close" id="ctxClose" aria-label="Закрыть меню">×</span><div class="title" id="ctxTitle">—</div><div id="ctxBody"></div><div class="actions" id="ctxActions"></div></div>
<div id="chartModal" class="modal"><div class="dialog xwide">
<h2>📈 Статистика за игровой год <button class="close-x" data-close="chartModal" aria-label="Закрыть">&times;</button></h2>
<div class="tabs" id="chartTabs">
<button class="tab active" data-m="net">💰 Прибыль</button>
<button class="tab" data-m="company_value">🏢 Компания</button>
<button class="tab" data-m="stations">🏙 Станции</button>
<button class="tab" data-m="edge_km">🛤 Км путей</button>
<button class="tab" data-m="routes">🧭 Маршруты</button>
<button class="tab" data-m="trains">🚆 Поезда</button>
<button class="tab" data-m="depots">🏭 Депо</button>
<button class="tab" data-m="substations">⚡ Подстанции</button>
<button class="tab" data-m="track_maint">⚒ ПЧ</button>
<button class="tab" data-m="breakdown">📊 Детализация</button>
</div>
<div id="chartBox"><canvas id="hChart"></canvas></div>
</div></div>
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
<p class="login-sub">Карта на весь экран. Стройте сеть, ставьте подстанции и путевые части, собирайте составы из вагонов, сохраняйте пресеты и управляйте компанией из штаба 🏛.</p>
<div class="login-chips"><span class="lchip">🏛 Штаб</span><span class="lchip">🚂 6 моделей</span><span class="lchip">🚃 3 типа вагонов</span><span class="lchip">📋 пресеты</span><span class="lchip">⚡ 8 км</span><span class="lchip">⚒ 10 км</span><span class="lchip">🛰 спутник</span></div>
<input type="text" id="loginName" class="input" placeholder="Название компании (логин)" autocomplete="off" maxlength="40" aria-label="Название компании">
<button id="loginBtn" class="btn btn-primary" style="margin-top:10px">Войти в диспетчерскую</button>
<p class="login-hint">Новое имя — новая компания: 10 000 ₽, свой цвет из 32, штаб и 20 дней каникул.</p>
</div></div>
<div id="newsModal" class="modal"><div class="dialog wide">
<h2>📰 Лента событий <button class="close-x" data-close="newsModal" aria-label="Закрыть">&times;</button></h2>
<div id="eventsList" class="mbody"></div>
</div></div>
<div id="helpModal" class="modal"><div class="dialog wide">
<h2>❓ Управление и легенда <button class="close-x" data-close="helpModal" aria-label="Закрыть">&times;</button></h2>
<div class="mbody">
<table class="htable">
<tr><td><kbd>1</kbd></td><td>Станция — клик по карте</td></tr>
<tr><td><kbd>2</kbd></td><td>Перегон: клик по станции/узлу или в поле — начало; клики в поле — изгибы; станция/узел — конец; <b>клик по перегону — врезка станции</b>. Максимум 20 км.</td></tr>
<tr><td><kbd>3</kbd></td><td>Депо — в радиусе 500 м от доступной станции</td></tr>
<tr><td><kbd>4</kbd></td><td>Маршрут — станции по порядку; клик по первой замыкает кольцо; при нехватке слотов — кнопка «⬆ Улучшить» прямо в доке</td></tr>
<tr><td><kbd>5</kbd></td><td>⚡ Подстанция — питает электрификацию в 8 км</td></tr>
<tr><td><kbd>6</kbd></td><td>⚒ Путевая часть — разрешает двухпутку в 10 км</td></tr>
<tr><td><kbd>X / Ч</kbd></td><td>Полупрозрачные перегоны</td></tr>
<tr><td><kbd>M / Ь</kbd></td><td>Подложка по кругу: Voyager → Positron → OSM → спутники</td></tr>
<tr><td><kbd>Z / Я</kbd></td><td>Поезда в движении</td></tr>
<tr><td><kbd>C / С</kbd></td><td>Чат диспетчеров</td></tr>
<tr><td><kbd>V / М</kbd></td><td>Подписи объектов</td></tr>
<tr><td><kbd>B / И</kbd></td><td>Радиусы подстанций</td></tr>
<tr><td><kbd>N / Т</kbd></td><td>Радиусы путевых частей</td></tr>
<tr><td><kbd>&lt; / Б</kbd></td><td>Чёрточки блок-участков</td></tr>
<tr><td><kbd>&gt; / Ю</kbd></td><td>Подсветка занятости пути</td></tr>
<tr><td><kbd>. / ?</kbd></td><td>Рынок и лоты</td></tr>
<tr><td><kbd>H / Р</kbd></td><td>Эта справка</td></tr>
<tr><td><kbd>O / Щ</kbd></td><td>Настройки карты</td></tr>
<tr><td><kbd>Enter</kbd></td><td>Завершить ветку в точке курсора / подтвердить диалог</td></tr>
<tr><td><kbd>Пробел</kbd></td><td>Точка изгиба под курсором / пауза поездов вне режимов</td></tr>
<tr><td><kbd>Backspace</kbd></td><td>Убрать последний изгиб / остановку</td></tr>
<tr><td><kbd>Esc</kbd></td><td>Отмена режима, закрытие панелей и окон</td></tr>
<tr><td><kbd>Клик / ПКМ</kbd></td><td>Депо: клик — модалка с поездами, ПКМ — быстрое меню. Остальные объекты: меню по клику и ПКМ.</td></tr>
</table>
<div class="lg-sec">
<h4>Обзор и детали · спутник</h4>
<p class="hint">При шкале мельче <b style="color:var(--ink)">2 км</b> карта переходит в обзор: остаются полотно перегонов, крошечные точки станций (без подписей) и штабы 🏛 — так карта не тормозит при многих игроках. Депо, подстанции, ПЧ, узлы, мосты и поезда появляются с 2 км и крупнее. Тумблер «вся инфраструктура на обзоре» отключает отсечение. Подложки «🛰 Спутник» (Google и Esri) — для визуального планирования трасс; если Google не отвечает, игра предложит Esri автоматически.</p>
</div>
<div class="lg-sec">
<h4>Скорость: линия, средняя и узкие места</h4>
<p class="hint">Потолок перегона = 120 км/ч (180 на запитанной электрификации). Мосты и путепроводы режут скорость <b style="color:var(--ink)">только в своём пролёте</b>: поезд заранее тормозит, проходит мост медленно и разгоняется после. Доход маршрута считает <b style="color:var(--ink)">среднюю эффективную</b> скорость по профилю пути, а не худший мост. В меню маршрута и поезда: «линия X · средняя Y» + список узких мест с позициями в км.</p>
</div>
<div class="lg-sec">
<h4>Блок-участки и занятость</h4>
<p class="hint">Однопутка = один блок на весь перегон: встречные и попутные ждут у входа, очередь честно по порядку прибытия. Двухпутка дробится на блоки заданной длины (250–5000 м): по одному составу на блок в каждом направлении. Станции классов 1–3 держат один состав на направление, класс 4+ — путевое развитие. Клинчи разрушаются автоматически через 28 с. Чёрточки на полотне — границы блоков; янтарная подсветка — занятые блоки.</p>
</div>
<div class="lg-sec">
<h4>Пресеты составов 📋</h4>
<p class="hint">Пресет = модель + количества вагонов (пасс/багаж/почта). Личный инструмент компании (до 10 штук): не продаётся и не изымается. Создать — из меню поезда «💾 Состав в пресет» или конструктором в «📋 Пресеты составов». Применить — при покупке поезда в любое депо или «📋 Пересобрать» существующий: старые вагоны отцепляются с возвратом 50 %, новые прицепляются по полной цене, дельта показывается до подтверждения.</p>
</div>
<div class="lg-sec">
<h4>Штаб компании 🏛</h4>
<p class="hint">Один на компанию, создаётся автоматически у старейшей станции. Клик — карточка: вывеска, основатель, дата, инфраструктура, парк, финансы и переход к графикам с выделением компании. Владелец редактирует название и основателя (логин не меняется) и переносит штаб бесплатно куда угодно. Штаб не продаётся и не изымается при банкротстве.</p>
</div>
<div class="lg-sec">
<h4>Подвижной состав (6 моделей)</h4>
<div class="lg"><span class="sw" style="background:#7fa6d8"></span>🚂 Маневровый тепловоз · 500 ₽ · ×0.85 · 120 км/ч</div>
<div class="lg"><span class="sw" style="background:#7fa6d8"></span>🚂 Магистральный тепловоз · 800 ₽ · ×1.00 · 120 км/ч</div>
<div class="lg"><span class="sw" style="background:#7fa6d8"></span>🚂 Скоростной тепловоз · 1300 ₽ · ×1.10 · 120 км/ч</div>
<div class="lg"><span class="sw" style="background:#ffb02e"></span>⚡ Обычная электричка · 1200 ₽ · ×1.00 · 180 км/ч (нужна запитанная сеть)</div>
<div class="lg"><span class="sw" style="background:#ffb02e"></span>⚡ Скоростная электричка · 1800 ₽ · ×1.10 · 180 км/ч</div>
<div class="lg"><span class="sw" style="background:#ffb02e"></span>⚡ Высокоскоростной · 2600 ₽ · ×1.20 · 180 км/ч</div>
<p class="hint">Мощность — множитель дохода и разгон. Спрайты PNG из <span style="font-family:var(--f-mono)">img/trains/</span> подхватываются автоматически; состояния «в пути / погрузка / отстой» — файлы <span style="font-family:var(--f-mono)">_run / _load / _idle</span>.</p>
</div>
<div class="lg-sec">
<h4>Вагоны и грузы</h4>
<div class="lg"><span class="sw sq" style="background:#9db0c9"></span>Пассажирский · 300 ₽ · +1 ёмкость · доход от станций</div>
<div class="lg"><span class="sw sq hatch" style="background:#9db0c9"></span>Багажный · 500 ₽ · +2 ёмкости · штриховка / · доход от длины маршрута</div>
<div class="lg"><span class="sw sq hatch" style="background:#9db0c9;background-image:repeating-linear-gradient(-45deg,rgba(0,0,0,.55) 0 2px,transparent 2px 5px)!important"></span>Почтовый · 600 ₽ · +2 ёмкости · штриховка \ · доход от длины маршрута</div>
<p class="hint">Грузовая ёмкость умножается на <b style="color:var(--ink)">км маршрута / 10</b>: до 10 км выгоднее пассажиры, дальше — грузы. Лимит вагонов на состав = 2 × минимальный класс остановок. В модалке прицепа можно указать сразу несколько вагонов. В анимации рисуется до 10 вагонов на состав — ради производительности телефона.</p>
</div>
<div class="lg-sec">
<h4>Износ составов</h4>
<p class="hint">Работающий поезд изнашивается на <b style="color:var(--ink)">10 % за реальный день</b>; отстой, поставка и «нет питания» не изнашивают. Доход маршрута умножается на среднее здоровье составов. Ремонт — в меню поезда или ведомости 🔧: 10 % стоимости состава пропорционально ущербу; бесплатно — GPS рядом с депо приписки. Инфраструктура изнашивается медленно (0.1 %/реал. день), ПЧ не изнашивается.</p>
</div>
<div class="lg-sec">
<h4>Электрификация, ПЧ, доступ</h4>
<p class="hint">Электрифицировать перегон можно только при своей подстанции в <b style="color:var(--ink)">8 км</b>. Снос подстанции обесточивает перегоны: 120 км/ч, бонус +20 % пропадает, электропоезда встают. Двухпутка — при своей ПЧ в <b style="color:var(--ink)">10 км</b>; построенные двухпутки сохраняются при сносе ПЧ. Доступ чужим компаниям управляется политикой компании и точечно на каждом объекте.</p>
</div>
<div class="lg-sec">
<h4>Движение и видимость</h4>
<p class="hint">Каждый вагон идёт по пути на своей дистанции и доворачивает по кривой отдельно. Интервалы между поездами одного маршрута выравниваются мягким регулятором. Размер составов зависит от масштаба: на 2 км — эталон, ближе — компактнее, чтобы не закрывать полотно.</p>
<div class="demo-track">
<div class="demo-rail">
<span class="demo-st" style="left:3%"></span><span class="demo-st pass" style="left:33%"></span>
<span class="demo-st" style="left:75%"></span><span class="demo-st" style="left:97%"></span>
<span class="demo-tr"></span>
</div>
<div class="hint">Локомотив тянет вагоны: сплошной — пассажирский, штриховка / — багажный, \ — почтовый. Стоянка — белая обводка локомотива, ожидание блока — янтарный пульс.</div>
</div>
</div>
<div class="lg-sec">
<h4>Экономика маршрута</h4>
<p class="hint">Доход = <b style="color:var(--ink)">K × Σбонус × fill × [(P + G·км/10)/ёмкость] × (1+0.25·2п) × (1+0.20·⚡) × (средняя/120) × мощность × здоровье</b>. Чужие пути: 30 % дохода владельцу пропорционально км. Содержание: работающий объект ×1, станция проездом ×0.75, простой ×0.5; каникулы новичка ×0.5 на 20 дней.</p>
</div>
<div class="lg-sec">
<h4>Объекты</h4>
<div class="lg"><span class="sw" style="background:#a97bff;border-radius:5px"></span>🏛 Штаб компании</div>
<div class="lg"><span class="sw" style="background:#35d07f"></span>Объект вашей компании (цвет компании)</div>
<div class="lg"><span class="sw" style="background:#ff5a5a"></span>Объект другой компании (её цвет)</div>
<div class="lg"><span class="sw" style="background:#7fa6d8;border-radius:2px;transform:rotate(45deg)"></span>Узел ветки — стык/тупик в поле</div>
<div class="lg"><span class="sw" style="background:#ffb02e;border-radius:4px"></span>⚡ Подстанция (питание 8 км)</div>
<div class="lg"><span class="sw" style="background:#d9a441;border-radius:4px"></span>⚒ Путевая часть (обслуживание 10 км)</div>
<div class="lg"><span class="sw" style="background:#ffd98a;border-color:#e0bf5c"></span>Строится / улучшается</div>
<div class="lg"><span class="sw" style="background:#98a4b4;border-color:#5b6c85"></span>Не обслуживается (содержание ×0.5)</div>
<div class="lg">🔒 Закрытый чужой объект — подключение/проезд запрещены владельцем</div>
</div>
</div>
</div></div>
<div id="repairModal" class="modal"><div class="dialog wide">
<h2>🔧 Ведомость ремонта <button class="close-x" data-close="repairModal" aria-label="Закрыть">&times;</button></h2>
<div id="repairBody" class="mbody"></div>
<div class="btn-row"><button class="b-cancel" data-close="repairModal">Отмена</button><button id="repairNear" class="b-cancel" title="Бесплатно починить всё в радиусе 1 км от вас (составы — у их депо)">📍 Рядом</button><button id="repairOk" class="b-ok">Починить всё</button></div>
</div></div>
<div id="chat">
<div id="chatHead"><span>💬 Чат диспетчеров</span><button id="chatClose" aria-label="Закрыть чат">×</button></div>
<div id="chatMsgs"></div>
<div id="chatRow"><input id="chatInput" type="text" maxlength="300" placeholder="Сообщение…" autocomplete="off" aria-label="Сообщение чата"><button id="chatSend" aria-label="Отправить">➤</button></div>
</div>
<div id="loading"><div class="lbox"><div class="spin"></div><div id="loadText">Проверяем местность…</div></div></div>
<div id="actionModal" class="modal"><div class="dialog" id="actionDialog">
<h3 id="actionTitle">Заголовок</h3>
<div id="actionBody" class="mbody"></div>
<div class="btn-row"><button id="actionCancel" class="b-cancel">Отмена</button><button id="actionOk" class="b-ok">ОК</button></div>
</div></div>
<div id="mPlate" title="Финансы компании">
<div class="mp-r1"><span class="mp-chip" id="mpChip"></span><span class="mp-name" id="mpName">—</span><button class="mp-snd" id="mpSnd" aria-label="Звук">🔊</button></div>
<div class="mp-r2" id="mpBal">0 ₽</div>
<div class="mp-r3">День <b id="mpDay">1</b> · <b id="mpNet">=0 ₽/д</b> · <span id="mpTick">--с</span></div>
<span class="mp-bar" id="mpBar"></span>
</div>
<nav id="mNav" aria-label="Навигация">
<button data-mact="routes" title="Маршруты">🧭</button>
<button data-mact="infra" title="Инфраструктура">🏗</button>
<button data-mact="market" title="Рынок">🏷<span class="dotmark amber" id="mnLots" style="display:none">0</span></button>
<button data-mact="repair" title="Ремонт">🔧<span class="dotmark" id="mnRepair" style="display:none">0</span></button>
<button data-mact="companies" title="Компании">👥</button>
<button data-mact="charts" title="Графики">📈</button>
<button data-mact="news" title="Новости">📰<span class="dotmark" id="mnNews" style="display:none">0</span></button>
<button data-mact="chat" title="Чат">💬</button>
<button data-mact="company" title="Компания">🏢</button>
<button data-mact="settings" title="Настройки">⚙️</button>
<button data-mact="help" title="Справка">❓</button>
<button data-mact="logout" title="Выйти">🚪</button>
</nav>
<script>
"use strict";
if(typeof L==='undefined'){document.getElementById('map').innerHTML='<div style="padding:22px;color:#c9302f;font-weight:700;background:#fee">Leaflet не загрузилась.</div>';throw new Error('Leaflet missing');}
const API='game.php',DAY=60,POLL_MS=2000;
const COST_STATION=250,COST_EDGE_M=1,COST_DOUBLE_M=.8,COST_ELEC_M=.5,COST_NODE=50,MAX_CLASS=10,MAX_EDGE_LEN_M=20000;
const UPK_ST_BASE=2,UPK_ST_CLASS=2,UPK_EDGE_SHARE=.1,INCOME_TRACK=15,UPK_NODE=.5;
const UPK_DEPOT_BASE=4,UPK_DEPOT_LVL=2,DEPOT_CAP_BASE=3,DEPOT_MAX_LVL=5,DEPOT_UPG_COST=800,DEPOT_RADIUS=500;
const TRAIN_UPK_RUN=3,TRAIN_UPK_IDLE=1.5,REBASE_COST=200,REPAIR_RATIO=.1,REPAIR_FREE_M=1000,REFUND=.5;
const ST_R_BASE=8,ST_R_CLASS=1.5,HEALTH_CRIT=20,CONSERV=.5,TRANSIT_UPK=.75;
const EDGE_SPEED_BASE=120,EDGE_SPEED_ELECTRIC=180,ROUTE_SPEED_BASE=120;
const SUBSTATION_COST=2500,SUBSTATION_UPK=4,SUBSTATION_RANGE_M=8000,SUBSTATION_RADIUS=500;
const TRACK_MAINT_COST=2000,TRACK_MAINT_UPK=3,TRACK_MAINT_UPK_HALF=1.5,TRACK_MAINT_RANGE_M=10000,TRACK_MAINT_RADIUS=500;
const MAX_WAGONS=20,WAGONS_PER_CLASS=2,FREIGHT_BREAK_EVEN_KM=10,TRAIN_VISIBLE_SCALE_M=2000;
const MAX_DRAW_WAGONS=10,BRIDGE_WATER_HALF_M=60,EDGE_HIT_PX=16;
const SIM={speed:8,dwell:3.5,accel:1.6,tlen:18};
const BLOCK_MARGIN_M=25,BLOCK_GAP_M=30,ST_HOLD_M=35,APPROACH_M=600;
const BASE_LAT=55.75,BASE_LNG=37.62,CARTO_KEY='cb1_4pcc_1_c40db698b0c1ad6e7d443599';
const PALETTE=['#35d07f','#ff5a5a','#3d9bff','#ffb02e','#a97bff','#24d3c4','#ff5da2','#b6e83a','#ff8a3d','#7fa6d8','#e0d84a','#f06292','#0f766e','#06b6d4','#7dd3fc','#1d4ed8','#4f46e5','#c084fc','#7e22ce','#d946ef','#f9a8d4','#be123c','#d97706','#eab308','#65a30d','#92400e','#d6b48a','#9ca3af','#475569','#e5e7eb','#1f2937','#0ea5e9'];
const MAT_COLOR={wood:'#c07a3c',concrete:'#8ea6c6',steel:'#4a7fc1',level:'#d9a441'};
const EV_ICON={new_player:'👋',bankruptcy:'💥',auction_sold:'💰',auction_expired:'⌛',auction_cancelled:'↩️',big_build:'⭐',long_edge:'🛤️',station_10:'🎯',construction_done:'🏁',deleted:'🗑️',station_repaired:'🔧',bridges_built:'🌉',bridge_upgraded:'⬆️',route_created:'🧭',route_closed:'🚫',depot_built:'🏭',train_bought:'🚆',train_ready:'✅',train_assigned:'🎫',starter_pack:'🎁',restart_grant:'🛟',branch_built:'🔩',station_inserted:'📍',company_updated:'🏢',substation_built:'⚡',substation_deleted:'🔌',track_maint_built:'⚒',track_maint_deleted:'⚒',train_repaired:'🔧',hq_moved:'🏛',station_built:'🚉'};
const FALLBACK_TYPES=[{key:'wood',label:'Деревянный',speed:30,pct:.3,material:'wood',rail_only:false,cost_river:150,cost_rail:120},{key:'wood_reinforced',label:'Укреплённый деревянный',speed:45,pct:.6,material:'wood',rail_only:false,cost_river:300,cost_rail:240},{key:'concrete_beam',label:'Бетонный балочный',speed:60,pct:1,material:'concrete',rail_only:false,cost_river:500,cost_rail:400},{key:'concrete_arch',label:'Бетонный арочный',speed:90,pct:1.5,material:'concrete',rail_only:false,cost_river:750,cost_rail:600},{key:'steel_beam',label:'Металлический балочный',speed:120,pct:2.2,material:'steel',rail_only:false,cost_river:1100,cost_rail:880},{key:'steel_arch',label:'Металлический арочный',speed:150,pct:3.2,material:'steel',rail_only:false,cost_river:1600,cost_rail:1280},{key:'steel_modern',label:'Современный металлический',speed:180,pct:4,material:'steel',rail_only:false,cost_river:2000,cost_rail:1600},{key:'level',label:'Глухое пересечение',speed:40,pct:.25,material:'level',rail_only:true,cost_river:125,cost_rail:100}];
const FALLBACK_MODELS=[{key:'diesel_shunter',label:'Маневровый тепловоз',traction:'diesel',max_speed:120,cost:500,power:.85,accel:.55,img:null},{key:'diesel_line',label:'Магистральный тепловоз',traction:'diesel',max_speed:120,cost:800,power:1,accel:.8,img:null},{key:'diesel_express',label:'Скоростной тепловоз',traction:'diesel',max_speed:120,cost:1300,power:1.1,accel:1.1,img:null},{key:'electric_line',label:'Обычная электричка',traction:'electric',max_speed:180,cost:1200,power:1,accel:.9,img:null},{key:'electric_express',label:'Скоростная электричка',traction:'electric',max_speed:180,cost:1800,power:1.1,accel:1.2,img:null},{key:'electric_high',label:'Высокоскоростной электропоезд',traction:'electric',max_speed:180,cost:2600,power:1.2,accel:1.5,img:null}];
const FALLBACK_WAGONS=[{key:'passenger',label:'Пассажирский',cap:1,freight:false,cost:300,upkeep_active:.5,upkeep_idle:.25,hatch:false},{key:'baggage',label:'Багажный',cap:2,freight:true,cost:500,upkeep_active:.8,upkeep_idle:.4,hatch:true},{key:'mail',label:'Почтовый',cap:2,freight:true,cost:600,upkeep_active:.8,upkeep_idle:.4,hatch:true}];
const el=id=>document.getElementById(id);
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmt=n=>Number(n||0).toLocaleString('ru-RU');
const money=n=>fmt(Math.round(Number(n)||0))+' ₽';
const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));
let me=null,world={stations:[],nodes:[],edges:[],routes:[],depots:[],trains:[],substations:[],track_maint:[],hqs:[],presets:[]};
let companies=[],palette=PALETTE.slice(),myPolicies={allow_trackage:0,allow_stops:0,allow_connect:0};
let constants={node_snap_m:30,trackage_share:.3,substation_range_m:SUBSTATION_RANGE_M,track_maint_range_m:TRACK_MAINT_RANGE_M,max_wagons:MAX_WAGONS,wagons_per_class:WAGONS_PER_CLASS,freight_break_even_km:FREIGHT_BREAK_EVEN_KM,train_visible_scale_m:TRAIN_VISIBLE_SCALE_M,presets_max:10,max_edge_len_m:MAX_EDGE_LEN_M,block_len_default:750,block_len_min:250,block_len_max:5000,block_len_step:50,cost_block:10,station_block_max_class:3,block_deadlock_sec:28};
let bridgeTypes=FALLBACK_TYPES.slice(),btMap={},trainModels=FALLBACK_MODELS.slice(),tmMap={},wagonTypes=FALLBACK_WAGONS.slice(),wtMap={};
let pricing={station:COST_STATION,edge:COST_EDGE_M,depot:1500,node:COST_NODE,substation:SUBSTATION_COST,track_maint:TRACK_MAINT_COST};
let lastEco=null,lastPoll=null,ratingCache=[],lastVersion=-1,polling=false,pollTimer=null,srvOffset=0,tickLeft=0,curDay=1,lastSrvAt=0;
let mode='idle',edgeDraft={start:null,points:[]},routeDraft={stops:[],loop:false},routeGeom=null;
let mouseLL=null,snapHit=null,ghost=null,ghostBadge=null,tempLine=null,tempTip=null,osmTip=null,osmTimer=null,routeQuoteTimer=null,edgeQuote=null,routeQuote=null,pendingSplit=null;
let map=null,baseTiles=null,gridLayer=null,zoneLayer=null,edgeLayer=null,edgeHitLayer=null,bridgeLayer=null,blockTickLayer=null,blockOccLayer=null,stationLayer=null,nodeLayer=null,subLayer=null,tmLayer=null,hqLayer=null,depotLayer=null,labelLayer=null,draftLayer=null,previewLayer=null,hintLayer=null,fxLayer=null;
let trainMarkers=new Map(),animRunning=false,lastFrame=0,frameNo=0,statusT=null;
let idxSt=new Map(),idxNd=new Map(),idxEp=new Map(),idxEd=new Map(),idxDp=new Map(),idxRt=new Map(),idxSb=new Map(),idxTm=new Map(),idxHq=new Map();
let edgeGeom=new Map(),blockReg=new Map(),stationOcc=new Map(),singleReq=new Map();
let lots=[],sellState={stations:[],edges:[],nodes:[],depots:[],trains:[],substations:[],track_maint:[],min:0,max:0};
let myPos=null,gpsWatch=null,gpsCircle=null,chatTimer=null,chatLast=0,newsSeen=0,newsUnread=0;
let chart=null,chartMetric='net',chartCache={h:null,c:null},chartFocusPid=null,chartLoading=null;
let tickerQ=[],tickerT=null,lastCtx={x:120,y:120},palSel=null,drawerName=null,satFail=0,satOffered=false,depotModalId=null;
const UI_DEF={grid:false,snap:false,step:50,trains:true,tlabels:false,blocks:true,blockOcc:true,bridges:true,nodes:true,subCircles:false,tmCircles:false,labels:true,fade:false,afford:false,allInfra:false,sound:true,paused:false,style:'voyager',opacity:100};
let ui={...UI_DEF};
const SFX={ctx:null,master:null,ready:false,
unlock(){if(this.ready)return;try{const AC=window.AudioContext||window.webkitAudioContext;if(!AC)return;this.ctx=new AC();this.master=this.ctx.createGain();this.master.gain.value=.12;this.master.connect(this.ctx.destination);this.ready=true;}catch(e){}},
tone(f,t0,d,type,v,slide){if(!this.ctx)return;const o=this.ctx.createOscillator(),g=this.ctx.createGain();o.type=type||'sine';const t=this.ctx.currentTime+t0;o.frequency.setValueAtTime(f,t);if(slide)o.frequency.exponentialRampToValueAtTime(Math.max(30,f+slide),t+d);g.gain.setValueAtTime(.0001,t);g.gain.exponentialRampToValueAtTime(Math.max(.0002,v||1),t+.014);g.gain.exponentialRampToValueAtTime(.0001,t+d);o.connect(g);g.connect(this.master);o.start(t);o.stop(t+d+.05);},
play(n){if(!ui.sound||!this.ready||!this.ctx)return;if(this.ctx.state==='suspended')this.ctx.resume();
if(n==='click')this.tone(1350,0,.05,'triangle',.22);
else if(n==='snap')this.tone(1950,0,.03,'triangle',.18);
else if(n==='build'){this.tone(210,0,.09,'square',.38);this.tone(320,.09,.12,'square',.32);this.tone(640,.2,.16,'triangle',.28);}
else if(n==='cash'){this.tone(880,0,.08,'sine',.42);this.tone(1320,.08,.14,'sine',.36);}
else if(n==='error')this.tone(165,0,.19,'sawtooth',.32,-60);
else if(n==='chat'){this.tone(1040,0,.07,'sine',.24);this.tone(1560,.07,.09,'sine',.18);}
else if(n==='day'){this.tone(520,0,.1,'triangle',.26);this.tone(780,.1,.14,'triangle',.22);}
else if(n==='whistle'){this.tone(720,0,.28,'sawtooth',.16,90);this.tone(960,.03,.3,'sine',.14,60);}
else if(n==='route'){this.tone(440,0,.08,'triangle',.3);this.tone(660,.08,.1,'triangle',.28);this.tone(880,.18,.16,'sine',.24);}
else if(n==='stop'){this.tone(430,0,.1,'sine',.13);this.tone(300,.09,.16,'sine',.11);}
else if(n==='pass')this.tone(880,0,.13,'sine',.07,-140);
else if(n==='zap'){this.tone(220,0,.12,'sawtooth',.2,660);this.tone(1200,.1,.1,'sine',.15);}}};
document.addEventListener('pointerdown',()=>SFX.unlock(),{once:true});
document.addEventListener('keydown',()=>SFX.unlock(),{once:true});
function soundIcon(){const b=el('btnSound');b.textContent=ui.sound?'🔊':'';b.classList.toggle('on',ui.sound);const m=el('mpSnd');if(m)m.textContent=ui.sound?'🔊':'🔇';}
el('btnSound').onclick=()=>{ui.sound=!ui.sound;soundIcon();saveUI();if(ui.sound){SFX.unlock();SFX.play('click');}};
function setTrainModels(a){trainModels=(Array.isArray(a)&&a.length?a:FALLBACK_MODELS).map(m=>({key:String(m.key),label:String(m.label||m.key),traction:m.traction==='electric'?'electric':'diesel',max_speed:Number(m.max_speed)||120,cost:Number(m.cost)||0,power:Number(m.power)||1,accel:Number(m.accel)||1,img:m.img||null}));tmMap={};trainModels.forEach(m=>tmMap[m.key]=m);}
function tModel(k){return tmMap[k]||tmMap.diesel_line||trainModels[0];}
function tLabel(k){return tModel(k).label;}function tSpeed(k){return tModel(k).max_speed;}function tCost(k){return tModel(k).cost;}function tPower(k){return tModel(k).power;}function tTraction(k){return tModel(k).traction;}function tIcon(k){return tTraction(k)==='electric'?'⚡':'🚂';}function tImg(k){return tModel(k).img;}
function minTrainCost(){return trainModels.reduce((a,m)=>Math.min(a,m.cost),Infinity);}
function spriteImg(key,status,cls){const base=tImg(key);if(!base)return '';const mp={running:'_run',idle:'_idle',no_power:'_idle',orphan:'_idle',broken:'_idle',delivering:'_move',transit:'_move'};const suf=mp[status]||'';const src=suf?base.replace(/\.png$/i,suf+'.png'):base;return '<img class="'+cls+'" src="'+esc(src)+'" alt="" onerror="this.onerror=null;this.src=\''+esc(base)+'\'">';}
function setWagonTypes(a){wagonTypes=(Array.isArray(a)&&a.length?a:FALLBACK_WAGONS).map(w=>({key:String(w.key),label:String(w.label||w.key),cap:Number(w.cap)||1,freight:!!w.freight,cost:Number(w.cost)||300,upkeep_active:Number(w.upkeep_active)||.5,upkeep_idle:Number(w.upkeep_idle)||.25,hatch:!!w.hatch}));wtMap={};wagonTypes.forEach(w=>wtMap[w.key]=w);}
function wType(k){return wtMap[k]||wtMap.passenger||wagonTypes[0];}function wCap(k){return wType(k).cap;}function wCost(k){return wType(k).cost;}function wLabel(k){return wType(k).label;}function wFreight(k){return wType(k).freight;}
function bonusPct(b){b=b||0;const t=[[0,0],[5,5],[10,10],[19,15],[29,20],[39,25],[45,30],[52,35],[75,40],[87,45],[99,50],[119,55],[139,60],[159,65],[179,70],[199,75],[219,80],[239,85],[259,90],[279,95]];for(const[n,p]of t)if(b<=n)return p;return 100;}
function stUpgCost(c){return Math.ceil(c*1.25*50);}function stRadius(c){return ST_R_BASE+(Math.max(1,c)-1)*ST_R_CLASS;}
function stReplace(s){let v=COST_STATION;for(let c=1;c<(s.class||1);c++)v+=stUpgCost(c);return v;}
function dpReplace(d){let v=1500;for(let l=1;l<(d.level||1);l++)v+=DEPOT_UPG_COST*l;return v;}
function hav(a,b,c,d){const R=6371000,dLa=(c-a)*Math.PI/180,dLo=(d-b)*Math.PI/180;const x=Math.sin(dLa/2)**2+Math.cos(a*Math.PI/180)*Math.cos(c*Math.PI/180)*Math.sin(dLo/2)**2;return R*2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x));}
function nearMe(o){return myPos&&o?hav(myPos.lat,myPos.lng,o.lat,o.lng)<=REPAIR_FREE_M:false;}
function stTracks(sid){let t=0;world.edges.forEach(e=>{const w=(e.double||e.upgrade_type==='double')?2:1;if(e.from===sid)t+=w;if(e.to===sid)t+=w;});return t;}
function stUpkeep(s){if(s.is_building||s.frozen)return 0;return(UPK_ST_BASE+UPK_ST_CLASS*Math.max(1,s.class||1))*(s.served?1:s.passed?TRANSIT_UPK:CONSERV);}
function ndUpkeep(n){return n.frozen?0:UPK_NODE*(n.served?1:CONSERV);}
function sbUpkeep(x){if(x.is_building||x.frozen)return 0;return SUBSTATION_UPK*(x.powering?1:CONSERV);}
function tmUpkeep(x){if(x.is_building||x.frozen)return 0;return x.serves?TRACK_MAINT_UPK:TRACK_MAINT_UPK_HALF;}
function edUpkeep(e){if(e.is_building||e.frozen)return 0;const tr=e.double?2:1,len=Number(e.length||0);return INCOME_TRACK*2*tr*(1+len/1000)*(e.electric?1.2:1)*UPK_EDGE_SHARE*(e.served?1:CONSERV);}
function dpUpkeep(d){if(d.is_building||d.frozen)return 0;return(UPK_DEPOT_BASE+UPK_DEPOT_LVL*((d.level||1)-1))*(d.served?1:CONSERV);}
function trUpkeep(t){if(t.frozen)return 0;const run=t.status==='running';let u=run?TRAIN_UPK_RUN:TRAIN_UPK_IDLE;(t.wagons||[]).forEach(w=>{const wt=wType(w);u+=run?wt.upkeep_active:wt.upkeep_idle;});return u;}
function bridgeCost(kind,type){const t=btMap[type]||btMap.concrete_beam;if(!t)return 500;return kind==='rail'?t.cost_rail:t.cost_river;}
function bridgeSpeed(type){const t=btMap[type]||btMap.concrete_beam;return t?t.speed:60;}
function bridgeMat(type){const t=btMap[type]||btMap.concrete_beam;return t?t.material:'concrete';}
function bridgeLabel(type){const t=btMap[type]||btMap.concrete_beam;return t?t.label:'—';}
function setTypes(a){bridgeTypes=(a&&a.length?a:FALLBACK_TYPES).slice();btMap={};bridgeTypes.forEach(t=>btMap[t.key]=t);}
function srvNow(){return Date.now()/1000+srvOffset;}function left(e){return Math.max(0,Math.ceil(Number(e||0)-srvNow()));}
function dur(s){s=Math.max(0,Math.round(s));if(s<60)return s+'с';const m=Math.floor(s/60);if(m<60)return m+'м '+(s%60?(s%60)+'с':'');const h=Math.floor(m/60);return h+'ч '+(m%60)+'м';}
function companyColor(pid){const c=companies.find(x=>x.id===pid);return c?c.color:(me&&me.id===pid?me.color:PALETTE[(Math.max(1,pid)-1)%PALETTE.length]);}
function companyName(pid){const c=companies.find(x=>x.id===pid);return c?(c.display_name||c.name):(me&&me.id===pid?(me.display_name||me.name):'игрок #'+pid);}
function accessLabel(a){return a==='open'?'открыт всем':a==='closed'?'закрыт':'как в компании';}
function accessStateLabel(s){return{own:'ваш',open:'открыт',policy:'по политике',closed:'закрыт',denied:'запрещён'}[s]||s;}
function maxWagons(){return constants.max_wagons||MAX_WAGONS;}function wagonLimitByClass(c){return Math.max(1,c)*(constants.wagons_per_class||WAGONS_PER_CLASS);}function breakEvenKm(){return constants.freight_break_even_km||FREIGHT_BREAK_EVEN_KM;}function presetsMax(){return constants.presets_max||10;}
function maxEdgeLen(){return constants.max_edge_len_m||MAX_EDGE_LEN_M;}
function trainWagList(t){return Array.isArray(t.wagons)?t.wagons:[];}function trainWagCount(t){return t.wagon_count??trainWagList(t).length;}
function trainCapacity(t){if(t.capacity!=null)return Number(t.capacity);let c=1;trainWagList(t).forEach(w=>c+=wCap(w));return c;}
function trainCapParts(t){let p=1,g=0;trainWagList(t).forEach(w=>{if(wFreight(w))g+=wCap(w);else p+=wCap(w);});return{p,g};}
function trainReplaceCost(t){let v=tCost(t.model);trainWagList(t).forEach(w=>v+=wCost(w));return v;}
function trainRepairCost(t){const h=Number(t.health??100);return Math.ceil(trainReplaceCost(t)*REPAIR_RATIO*(100-h)/100);}
function consistShort(t){const cnt={};trainWagList(t).forEach(w=>cnt[w]=(cnt[w]||0)+1);const parts=[];wagonTypes.forEach(w=>{if(cnt[w.key])parts.push((w.key==='passenger'?'🚃':w.key==='baggage'?'🧳':'✉️')+cnt[w.key]);});return parts.join(' ')||'🚃0';}
function routeCapNow(r){let c=0;world.trains.forEach(t=>{if(t.route_id===r.id&&t.status!=='delivering'&&t.status!=='transit')c+=trainCapacity(t);});return c;}
function routeQueue(r){let n=0;trainMarkers.forEach(o=>{if(o.r&&o.r.id===r.id&&o.phase==='blocked')n++;});return n;}
function activeRoutesOnEdge(eid){return world.routes.filter(r=>r.status==='active'&&((r.path||[]).some(s=>s.e===eid)||(r.edges||[]).includes(eid)));}
function elecLabel(e){if(e.electric_label)return e.electric_label;if(!e.electric)return 'не электрифицировано';return (e.powered||e.electric_legacy)?'электрифицировано · запитано':'электрифицировано · обесточено';}
function dblLabel(e){if(e.double_label)return e.double_label;if(!e.double)return '1 путь';return (e.tm_served||e.double_legacy)?'2 пути':'2 пути · без ПЧ';}
function blocksLabel(e){if(e.blocks_label)return e.blocks_label;return e.double?((e.blocks_n||1)+' бл. × '+(e.block_len||750)+' м'):'1 блок (однопутка)';}
function distPointSegM(plat,plng,a,b){const kx=111320*Math.cos(plat*Math.PI/180),ky=111320;const px=plng*kx,py=plat*ky,ax=a[1]*kx,ay=a[0]*ky,bx=b[1]*kx,by=b[0]*ky;const dx=bx-ax,dy=by-ay,l2=dx*dx+dy*dy;const t=l2>0?clamp(((px-ax)*dx+(py-ay)*dy)/l2,0,1):0;return Math.hypot(px-(ax+t*dx),py-(ay+t*dy));}
function distToPolylineM(lat,lng,ll){let b=Infinity;for(let i=0;i<ll.length-1;i++)b=Math.min(b,distPointSegM(lat,lng,ll[i],ll[i+1]));return b;}
function nearestOwnSubstationToEdge(e){const ll=edgeLL(e);if(!ll||!me)return null;let b=null;world.substations.forEach(x=>{if(x.owner_id!==me.id||x.is_building||x.frozen)return;const d=distToPolylineM(x.lat,x.lng,ll);if(!b||d<b.d)b={d,name:x.name};});return b;}
function nearestOwnTmToEdge(e){const ll=edgeLL(e);if(!ll||!me)return null;let b=null;world.track_maint.forEach(x=>{if(x.owner_id!==me.id||x.is_building||x.frozen)return;const d=distToPolylineM(x.lat,x.lng,ll);if(!b||d<b.d)b={d,name:x.name};});return b;}
function myPresets(){return me?(world.presets||[]).filter(p=>p.owner_id===me.id):[];}
function presetConsist(p){const w=p.wagons||{};const parts=[];if(w.passenger)parts.push('🚃'+w.passenger);if(w.baggage)parts.push('🧳'+w.baggage);if(w.mail)parts.push('✉️'+w.mail);return parts.join(' ')||'без вагонов';}
function presetPrice(p){let v=tCost(p.model);const w=p.wagons||{};wagonTypes.forEach(t=>{v+=(w[t.key]||0)*t.cost;});return v;}
async function api(action,method,body){const o={method:method||'GET',headers:{}};if(body){o.headers['Content-Type']='application/json';o.body=JSON.stringify(body);}const r=await fetch(API+'?action='+action,o);const txt=await r.text();let j;try{j=JSON.parse(txt);}catch(e){throw new Error(txt.replace(/<[^>]*>/g,'').trim().slice(0,220)||('HTTP '+r.status));}if(!r.ok)throw new Error(j.error||('HTTP '+r.status));return j;}
function toast(t){if(/^Ошибка|⛔|Нельзя/.test(t))SFX.play('error');tickerQ.push(t);if(!tickerT)nextToast();}
function nextToast(){if(!tickerQ.length){el('ticker').style.display='none';tickerT=null;chromeH();return;}el('ticker').style.display='flex';chromeH();const t=tickerQ.shift(),c=el('tickerIn');c.textContent='📢 '+t;c.style.animation='none';void c.offsetHeight;c.style.animation='marquee 18s linear infinite';tickerT=setTimeout(nextToast,Math.max(9000,t.length*180));}
function chromeH(){const h=el('topbar').offsetHeight+(el('ticker').style.display==='flex'?el('ticker').offsetHeight:0);document.documentElement.style.setProperty('--chrome-h',h+'px');}
function loading(t){el('loadText').textContent=t||'Загрузка…';el('loading').style.display='flex';if(map){map.dragging.disable();map.doubleClickZoom.disable();}}
function unloading(){el('loading').style.display='none';if(map){map.dragging.enable();if(mode!=='edge')map.doubleClickZoom.enable();}}
function closeActionModal(){el('actionModal').classList.remove('open');}
function modal(o){const dlg=el('actionDialog');dlg.classList.toggle('wide',!!o.wide);el('actionTitle').textContent=o.title;const b=el('actionBody');b.innerHTML=o.body;const inp=o.input!=null?el('modalInput'):null;if(inp)inp.value=o.input;const bo=el('actionOk'),bc=el('actionCancel');bo.textContent=o.ok||'ОК';bo.classList.toggle('danger',!!o.danger);bo.disabled=!!o.disabled;bc.textContent=o.cancel||'Отмена';bc.style.display=o.showCancel===false?'none':'';
const close=()=>{closeActionModal();bo.onclick=null;bc.onclick=null;bo.disabled=false;if(o.onClose)o.onClose();};
bo.onclick=()=>{if(inp){const v=inp.value.trim();if(!v){toast('Поле не может быть пустым');return;}close();o.onOk&&o.onOk(v);}else{close();o.onOk&&o.onOk();}};
bc.onclick=close;el('actionModal').classList.add('open');if(o.onMount)o.onMount(b);if(inp)setTimeout(()=>{inp.focus();inp.select();},60);}
function choiceModal(o){if(!o.items.length){toast(o.empty||'Нет доступных вариантов');return;}const first=o.items.findIndex(x=>!x.disabled);
const body=o.items.map((it,i)=>'<label class="choice'+(it.disabled?' dis':'')+'"><input type="radio" name="ch" value="'+esc(it.value)+'" '+(it.disabled?'disabled':'')+' '+(i===first?'checked':'')+'><span><span class="cn">'+esc(it.label)+'</span><span class="cs">'+esc(it.sub||'')+'</span></span></label>').join('');
modal({title:o.title,body,ok:o.ok||'Выбрать',onOk:()=>{const s=el('actionBody').querySelector('input[name=ch]:checked');if(!s){toast('Ничего не выбрано');return;}o.onOk(s.value);}});}
document.querySelectorAll('[data-close]').forEach(b=>b.onclick=()=>{const id=b.dataset.close;el(id).classList.remove('open');if(id==='depotModal')depotModalId=null;if(id==='chartModal'&&chart){chart.destroy();chart=null;chartCache={h:null,c:null};chartFocusPid=null;}});
document.querySelectorAll('.modal').forEach(m=>m.addEventListener('mousedown',e=>{if(e.target!==m)return;if(m.id==='loginModal'||m.id==='actionModal')return;m.classList.remove('open');if(m.id==='depotModal')depotModalId=null;if(m.id==='chartModal'&&chart){chart.destroy();chart=null;chartCache={h:null,c:null};chartFocusPid=null;}}));
function loadUI(){try{const s=JSON.parse(localStorage.getItem('railUI13')||'{}');Object.keys(UI_DEF).forEach(k=>{if(s[k]!==undefined)ui[k]=s[k];});}catch(e){}
el('gridOn').checked=ui.grid;el('gridSnap').checked=ui.snap;el('gridStep').value=ui.step;
el('tgTrains').checked=ui.trains;el('tgTrainLabels').checked=ui.tlabels;el('tgBlocks').checked=ui.blocks;el('tgBlockOcc').checked=ui.blockOcc;el('tgBridges').checked=ui.bridges;el('tgNodes').checked=ui.nodes;el('tgSubCircles').checked=ui.subCircles;el('tgTmCircles').checked=ui.tmCircles;el('tgLabels').checked=ui.labels;el('tgFade').checked=ui.fade;el('tgAfford').checked=ui.afford;el('tgAllInfra').checked=ui.allInfra;
el('mapStyle').value=ui.style;el('mapOpacity').value=ui.opacity;el('opVal').textContent=ui.opacity;soundIcon();}
function saveUI(){try{localStorage.setItem('railUI13',JSON.stringify(ui));}catch(e){}}
el('resetSettings').onclick=()=>{ui={...UI_DEF};loadUI();saveUI();if(map){setBase(ui.style);setOpacity(ui.opacity);drawGrid();render();refreshTrainIcons();}toast('Настройки сброшены');};
function initMap(){map=L.map('map',{center:[BASE_LAT,BASE_LNG],zoom:13,zoomControl:true,attributionControl:true});
L.control.scale({metric:true,imperial:false,position:'bottomleft'}).addTo(map);
loadUI();setBase(ui.style);setOpacity(ui.opacity);
const pane=(n,z,pe)=>{const p=map.createPane(n);p.style.zIndex=z;if(pe)p.style.pointerEvents=pe;return p;};
pane('gridP',230,'none');pane('zoneP',235,'none');pane('hintP',245,'none');pane('blockOccP',420,'none');pane('waterP',425,'none');pane('bridgeP',430,'none');pane('blockP',436,'none');pane('prevP',440,'none');pane('edgeHitP',445);pane('bridgeHitP',455);pane('fxP',470,'none');pane('stP',500);pane('nodeP',510);pane('lblP',650);pane('trainP',660,'none');
gridLayer=L.layerGroup().addTo(map);zoneLayer=L.layerGroup().addTo(map);hintLayer=L.layerGroup().addTo(map);
edgeLayer=L.layerGroup().addTo(map);edgeHitLayer=L.layerGroup().addTo(map);bridgeLayer=L.layerGroup().addTo(map);
blockTickLayer=L.layerGroup().addTo(map);blockOccLayer=L.layerGroup().addTo(map);
stationLayer=L.layerGroup().addTo(map);nodeLayer=L.layerGroup().addTo(map);subLayer=L.layerGroup().addTo(map);tmLayer=L.layerGroup().addTo(map);hqLayer=L.layerGroup().addTo(map);depotLayer=L.layerGroup().addTo(map);labelLayer=L.layerGroup().addTo(map);
draftLayer=L.layerGroup().addTo(map);previewLayer=L.layerGroup().addTo(map);fxLayer=L.layerGroup().addTo(map);
loadZones();
map.on('click',onMapClick);map.on('mousemove',onMapMove);map.on('mouseout',()=>{mouseLL=null;snapHit=null;clearGhost();});
map.on('zoomend',()=>{drawGrid();renderEdges();renderDetailLayers();renderDraft();renderPreview();renderBlockTicks();});
map.on('moveend',()=>{drawGrid();renderBlockTicks();});
drawGrid();startAnim();
setTimeout(()=>map&&map.invalidateSize(),120);setTimeout(()=>map&&map.invalidateSize(true),520);
el('mapStyle').onchange=e=>{ui.style=e.target.value;setBase(ui.style);saveUI();};
el('mapOpacity').oninput=e=>{ui.opacity=+e.target.value;el('opVal').textContent=ui.opacity;setOpacity(ui.opacity);saveUI();};
el('gridOn').onchange=e=>{ui.grid=e.target.checked;drawGrid();saveUI();};
el('gridSnap').onchange=e=>{ui.snap=e.target.checked;saveUI();};
el('gridStep').onchange=e=>{ui.step=+e.target.value;drawGrid();saveUI();};
el('tgTrains').onchange=e=>{ui.trains=e.target.checked;syncTrains();saveUI();};
el('tgTrainLabels').onchange=e=>{ui.tlabels=e.target.checked;refreshTrainIcons();saveUI();};
el('tgBlocks').onchange=e=>{ui.blocks=e.target.checked;renderBlockTicks();saveUI();};
el('tgBlockOcc').onchange=e=>{ui.blockOcc=e.target.checked;renderBlockOcc();saveUI();};
el('tgBridges').onchange=e=>{ui.bridges=e.target.checked;renderBridges();saveUI();};
el('tgNodes').onchange=e=>{ui.nodes=e.target.checked;renderNodes();saveUI();};
el('tgSubCircles').onchange=e=>{ui.subCircles=e.target.checked;renderSubstations();saveUI();};
el('tgTmCircles').onchange=e=>{ui.tmCircles=e.target.checked;renderTrackMaint();saveUI();};
el('tgLabels').onchange=e=>{ui.labels=e.target.checked;renderDetailLayers();renderHqs();saveUI();};
el('tgFade').onchange=e=>{ui.fade=e.target.checked;renderEdges();saveUI();};
el('tgAfford').onchange=e=>{ui.afford=e.target.checked;render();saveUI();};
el('tgAllInfra').onchange=e=>{ui.allInfra=e.target.checked;renderDetailLayers();saveUI();toast(ui.allInfra?'🗺 Вся инфраструктура на обзоре':'🗺 Обзор облегчённый');};
el('btnFit').onclick=fitAll;el('btnFitBar').onclick=fitAll;}
function setBase(style){if(baseTiles){baseTiles.off('tileerror');map.removeLayer(baseTiles);}
let url,attr='© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',subs='abcd',mn=19;
if(style==='osm')url='https://tile.openstreetmap.org/{z}/{x}/{y}.png';
else if(style==='positron'){url='https://basemaps.cartocdn.com/rastertiles/light_all/{z}/{x}/{y}.png?key='+CARTO_KEY;attr+=' © <a href="https://carto.com/attributions">CARTO</a>';}
else if(style==='sat'){url='https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}';subs=['mt0','mt1','mt2','mt3'];attr+=' © <a href="https://www.google.com/maps">Google</a>';mn=20;}
else if(style==='hybrid'){url='https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}';subs=['mt0','mt1','mt2','mt3'];attr+=' © <a href="https://www.google.com/maps">Google</a>';mn=20;}
else if(style==='esri'){url='https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';attr+=' © <a href="https://www.esri.com/">Esri</a>';}
else{url='https://basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key='+CARTO_KEY;attr+=' © <a href="https://carto.com/attributions">CARTO</a>';}
baseTiles=L.tileLayer(url,{attribution:attr,subdomains:subs,maxZoom:20,maxNativeZoom:mn}).addTo(map);
if(style==='sat'||style==='hybrid'){satFail=0;baseTiles.on('tileerror',()=>{satFail++;if(satFail>=6&&!satOffered){satOffered=true;toast('🛰 Google не отвечает — переключаюсь на Esri');ui.style='esri';el('mapStyle').value='esri';saveUI();setBase('esri');}});}
setOpacity(ui.opacity);renderBlockTicks();}
function setOpacity(p){if(baseTiles)baseTiles.setOpacity(p/100);}
function drawGrid(){if(!map||!gridLayer)return;gridLayer.clearLayers();if(!ui.grid)return;const b=map.getBounds(),cl=map.getCenter().lat;let step=ui.step,la=step/111320,lo=step/(111320*Math.cos(cl*Math.PI/180)),g=0;
while(((b.getNorth()-b.getSouth())/la>110||(b.getEast()-b.getWest())/lo>110)&&g<12){step*=2;la=step/111320;lo=step/(111320*Math.cos(cl*Math.PI/180));g++;}
const st={pane:'gridP',color:'#5b7ba6',weight:1,opacity:.28,interactive:false};
for(let y=Math.ceil(b.getSouth()/la)*la;y<=b.getNorth();y+=la)L.polyline([[y,b.getWest()],[y,b.getEast()]],st).addTo(gridLayer);
for(let x=Math.ceil(b.getWest()/lo)*lo;x<=b.getEast();x+=lo)L.polyline([[b.getSouth(),x],[b.getNorth(),x]],st).addTo(gridLayer);}
function snap(ll){if(!ui.snap)return ll;const la=ui.step/111320,lo=ui.step/(111320*Math.cos(ll.lat*Math.PI/180));return L.latLng(Math.round(ll.lat/la)*la,Math.round(ll.lng/lo)*lo);}
async function loadZones(){try{const r=await fetch('zzoness.json');if(!r.ok)return;const d=await r.json();if(!d.features)return;const st={pane:'zoneP',color:'#c9302f',weight:2,fillColor:'#ff5a5a',fillOpacity:.1,dashArray:'6 5',interactive:false};
d.features.forEach(f=>{const add=c=>L.polygon(c.map(x=>[x[1],x[0]]),st).addTo(zoneLayer);if(f.geometry.type==='Polygon')add(f.geometry.coordinates[0]);else if(f.geometry.type==='MultiPolygon')f.geometry.coordinates.forEach(p=>add(p[0]));});}catch(e){}}
function fitAll(){if(!map)return;const pts=[...world.stations,...world.nodes,...world.depots,...world.substations,...world.track_maint,...world.hqs];if(!pts.length){map.setView([BASE_LAT,BASE_LNG],13);return;}
let a=Infinity,b=Infinity,c=-Infinity,d=-Infinity;pts.forEach(s=>{a=Math.min(a,s.lat);b=Math.min(b,s.lng);c=Math.max(c,s.lat);d=Math.max(d,s.lng);});
const pa=Math.max(.02,(c-a)*.22),pb=Math.max(.02,(d-b)*.22);map.fitBounds([[c+pa,b-pb],[a-pa,d+pb]],{padding:[26,26]});}
function flyToDetail(lat,lng){if(map)map.flyTo([lat,lng],Math.max(map.getZoom(),14),{duration:.6});}
function fxPulse(lat,lng,color,small){if(!map||!fxLayer)return;const size=small?22:32;const m=L.marker([lat,lng],{pane:'fxP',interactive:false,keyboard:false,icon:L.divIcon({html:'<div class="fx'+(small?' sm':'')+'" style="color:'+color+'"><i></i></div>',className:'',iconSize:[size,size],iconAnchor:[size/2,size/2]})}).addTo(fxLayer);setTimeout(()=>{if(fxLayer)fxLayer.removeLayer(m);},small?540:760);}
function numBadge(txt,color,op){return L.divIcon({html:'<div class="nbad" style="background:'+color+';opacity:'+(op==null?1:op)+'">'+esc(txt)+'</div>',className:'',iconSize:[18,18],iconAnchor:[-9,17]});}
function reindex(){idxSt=new Map();idxNd=new Map();idxEp=new Map();idxEd=new Map();idxDp=new Map();idxRt=new Map();idxSb=new Map();idxTm=new Map();idxHq=new Map();
world.stations.forEach(s=>{idxSt.set(s.id,s);idxEp.set(s.id,{...s,kind:'station'});});
world.nodes.forEach(n=>{idxNd.set(n.id,n);idxEp.set(n.id,{...n,kind:'node'});});
world.edges.forEach(e=>idxEd.set(e.id,e));world.depots.forEach(d=>idxDp.set(d.id,d));world.routes.forEach(r=>idxRt.set(r.id,r));
world.substations.forEach(b=>idxSb.set(b.id,b));world.track_maint.forEach(x=>idxTm.set(x.id,x));world.hqs.forEach(h=>idxHq.set(h.id,h));
edgeGeom=new Map();world.edges.forEach(e=>{const ll=edgeLL(e);if(ll&&ll.length>1)edgeGeom.set(e.id,pathOf(ll));});}
const st=id=>idxSt.get(id)||null;const ep=id=>idxEp.get(id)||null;
function epName(id){const o=ep(id);return o?(o.kind==='node'?'узел '+o.name:o.name):'?';}
function edgeLL(e){const a=ep(e.from),b=ep(e.to);if(!a||!b)return null;const p=[[a.lat,a.lng]];(e.points||[]).forEach(x=>p.push([x.lat,x.lng]));p.push([b.lat,b.lng]);return p;}
function pathOf(pts){let tot=0;const cum=[0];for(let i=0;i<pts.length-1;i++){tot+=hav(pts[i][0],pts[i][1],pts[i+1][0],pts[i+1][1]);cum.push(tot);}return{pts,cum,total:tot};}
function bearing(A,B){const l=Math.cos((A[0]+B[0])/2*Math.PI/180);return Math.atan2((B[1]-A[1])*l,B[0]-A[0])*180/Math.PI;}
function mpp(lat){return 40075016.686*Math.cos(lat*Math.PI/180)/(256*Math.pow(2,map.getZoom()));}
function segAt(g,d,hint){let i=clamp(hint|0,0,g.pts.length-2);while(i<g.pts.length-2&&d>g.cum[i+1])i++;while(i>0&&d<g.cum[i])i--;return i;}
function pointAtSeg(g,d,i){const s=g.cum[i],e=g.cum[i+1],t=e>s?clamp((d-s)/(e-s),0,1):0;const A=g.pts[i],B=g.pts[i+1];return{lat:A[0]+(B[0]-A[0])*t,lng:A[1]+(B[1]-A[1])*t,bearing:bearing(A,B),seg:i};}
function pointAt(g,d){return pointAtSeg(g,d,segAt(g,d,0));}
function subPolyline(g,a,b){const pts=[];const pa=pointAt(g,a);pts.push([pa.lat,pa.lng]);for(let i=0;i<g.pts.length;i++){if(g.cum[i]>a&&g.cum[i]<b)pts.push(g.pts[i]);}const pb=pointAt(g,b);pts.push([pb.lat,pb.lng]);return pts;}
function offsetLL(lat,lng,dx,dy){return[lat+dy/111320,lng+dx/(111320*Math.cos(lat*Math.PI/180))];}
function routesOnEdge(eid){return world.routes.filter(r=>(r.path||[]).some(s=>s.e===eid)||(r.edges||[]).includes(eid));}
function scaleRoundNum(n){if(!(n>0))return 0;const pow=Math.pow(10,String(Math.floor(n)).length-1);let d=n/pow;d=d>=10?10:d>=5?5:d>=3?3:d>=2?2:1;return pow*d;}
function trainsScaleOk(){if(!map)return true;const p1=map.containerPointToLatLng([0,0]),p2=map.containerPointToLatLng([100,0]);return scaleRoundNum(map.distance(p1,p2))<=(constants.train_visible_scale_m||TRAIN_VISIBLE_SCALE_M);}
function detailOk(){return ui.allInfra||trainsScaleOk();}
function updateScaleHint(){const ov=!detailOk();const sh=el('scaleHint');if(sh)sh.classList.toggle('show',ov&&mode==='idle');}
function routePhase(id){let h=0;const s=String(id||'r');for(let i=0;i<s.length;i++)h=(h*31+s.charCodeAt(i))>>>0;return(h%1000)/1000;}
function largestGapDist(dists,total,loop,phase){if(!dists.length)return(((phase%1)+1)%1)*total;const s=[...dists].sort((a,b)=>a-b);let bg=-1,bm=0;
if(loop){for(let i=0;i<s.length;i++){const a=s[i],b=(i+1<s.length)?s[i+1]:s[0]+total;const g=b-a;if(g>bg){bg=g;bm=((a+b)/2)%total;}}}
else{const pts=[0,...s,total];for(let i=0;i<pts.length-1;i++){const g=pts[i+1]-pts[i];if(g>bg){bg=g;bm=(pts[i]+pts[i+1])/2;}}}
return clamp(bm,0,total);}
function dwellReal(){const mul=Math.max(1,SIM.speed);return clamp(SIM.dwell*8/mul,.4,SIM.dwell);}
function edgeTip(e){const cap=e.speed||EDGE_SPEED_BASE;const act=(e.routes_active!=null)?e.routes_active:activeRoutesOnEdge(e.id).length;
let tip='<b>'+esc(e.from_name||epName(e.from))+' ↔ '+esc(e.to_name||epName(e.to))+'</b><br>'+Math.round(e.length)+' м · '+dblLabel(e)+'<br>потолок <b>'+cap+'</b> км/ч · '+elecLabel(e)+'<br>🚦 '+blocksLabel(e);
(e.bridges||[]).forEach(br=>{tip+='<br>🌉 '+esc(br.name||'')+': '+br.speed+' км/ч <span style="color:#9db0c9">только в пролёте</span>';});
tip+='<br><span style="color:'+e.color+'">■</span> '+esc(companyName(e.owner_id));
if(me&&e.owner_id!==me.id)tip+='<br>доступ: '+accessStateLabel(e.access_state)+(e.can_use?'':' 🔒');
if(e.is_building)tip+='<br>🏗 <span data-cd="'+e.build_end+'">'+dur(left(e.build_end))+'</span>';
else if(!e.served)tip+='<br>💤 не обслуживается — нет активных маршрутов';
if(act>0)tip+='<br>🧭 активных маршрутов: '+act;
tip+='<br><span style="color:#9db0c9">клик — меню · режим 2 — врезка</span>';return tip;}
function renderEdges(){if(!map)return;edgeLayer.clearLayers();edgeHitLayer.clearLayers();const op=ui.fade?.45:.85,live=(mode==='idle'||mode==='edge');
world.edges.forEach(e=>{const ll=edgeLL(e);if(!ll)return;const mine=me&&e.owner_id===me.id;let color=e.color||companyColor(e.owner_id)||'#7d8aa0',dash=null,opacity=op;const weight=e.double?4.5:2.4;
if(e.is_building){dash='6 5';opacity=.55;}else if(e.auction_status==='bankrupt'){color='#98a4b4';dash='2 5';}else if(e.auction_status==='manual'){color=lotAfford(e)?'#f5c518':'rgba(245,197,24,.45)';dash='2 5';}else if(!e.served)opacity=op*.6;
if(!mine&&me&&!e.can_use&&!e.is_building){opacity=Math.min(opacity,.35);dash=dash||'3 4';}
L.polyline(ll,{color:'#0a1120',weight:weight+2.5,opacity:.3,interactive:false,smoothFactor:1}).addTo(edgeLayer);
L.polyline(ll,{color,weight,opacity,dashArray:dash,smoothFactor:1,interactive:false}).addTo(edgeLayer);
if(e.electric&&!e.is_building)L.polyline(ll,{color:(e.powered||e.electric_legacy)?'#ffffff':'#ff5a5a',weight:1.3,opacity:Math.min(.9,opacity+.25),dashArray:'2 6',interactive:false,smoothFactor:1}).addTo(edgeLayer);
if(!live)return;
const hit=L.polyline(ll,{pane:'edgeHitP',color:'#fff',weight:EDGE_HIT_PX,opacity:0,interactive:true,smoothFactor:1}).addTo(edgeHitLayer);
hit.bindTooltip(edgeTip(e),{sticky:true,className:'tip'});
hit.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxEdge(ev.originalEvent,e,ev.latlng);});
hit.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='edge'&&!edgeDraft.start){openSplitFlow(e,ev.latlng);return;}if(mode==='edge'&&edgeDraft.start){edgeDraft.points.push({lat:ev.latlng.lat,lng:ev.latlng.lng});SFX.play('snap');renderDraft();updateDock();return;}if(mode==='idle')ctxEdge(ev.originalEvent,e,ev.latlng);});});}
function lotAfford(o){if(!ui.afford||!me)return true;const l=lots.find(x=>x.type==='manual'&&(x.stations_preview||[]).some(s=>s.name===o.name||(s.lat===o.lat&&s.lng===o.lng)));return!l||me.money>=l.my_price;}
function bridgeSymbol(br,edge,layer,preview){if(!map||!layer||!br||!isFinite(br.lat)||!isFinite(br.lng))return;
const building=!!(edge&&edge.is_building);const m=mpp(br.lat);const span=Number(br.span)||60;
const halfPx=clamp(Math.max(span/2/m,12),12,80);const offPx=6+(edge&&edge.double?1.5:0);const wingPx=7;
const half=halfPx*m,off=offPx*m,wing=wingPx*m;const b=(Number(br.bearing)||0)*Math.PI/180;
const ux=Math.sin(b),uy=Math.cos(b),px=Math.cos(b),py=-Math.sin(b);
const P=(al,si)=>offsetLL(br.lat,br.lng,ux*al+px*si,uy*al+py*si);
const W=(es,ss)=>{const base=P(half*es,off*ss);const dx=(ux*es*Math.sin(.61)+px*ss*Math.cos(.61))*wing;const dy=(uy*es*Math.sin(.61)+py*ss*Math.cos(.61))*wing;return[base,offsetLL(base[0],base[1],dx,dy)];};
const segs=[[P(-half,off),P(half,off)],[P(-half,-off),P(half,-off)],W(1,1),W(1,-1),W(-1,1),W(-1,-1)];
const axis=[P(-half,0),P(half,0)];const col=preview?'#ffb02e':(MAT_COLOR[bridgeMat(br.type)]||'#4a7fc1');const isRail=br.kind==='rail';
const waterW=clamp(BRIDGE_WATER_HALF_M*2/m,2,10);
L.polyline(axis,{pane:'waterP',color:isRail?'#7d8aa0':'#3f7fbf',weight:waterW,opacity:preview?.2:(building?.14:.3),lineCap:'butt',interactive:false}).addTo(layer);
L.polyline(segs,{pane:'bridgeP',color:'#fff',weight:4.6,opacity:preview?.5:.9,lineCap:'round',interactive:false}).addTo(layer);
L.polyline(segs,{pane:'bridgeP',color:col,weight:2.6,opacity:building?.55:1,dashArray:(building||preview)?'4 3':null,lineCap:'round',interactive:false}).addTo(layer);
if(preview||mode!=='idle')return;
const hit=L.polyline(axis,{pane:'bridgeHitP',weight:offPx*2+11,opacity:0,interactive:true}).addTo(layer);
hit.bindTooltip('<b>'+(isRail?'🌉 Путепровод':'🌉 Мост')+'</b> · '+esc(br.name||'')+'<br>'+esc(bridgeLabel(br.type))+' · '+br.speed+' км/ч <span style="color:#9db0c9">только в пролёте</span><br>пролёт '+Math.round(span)+' м'+(building?'<br>🏗 строится':''),{sticky:true,className:'tip'});
if(edge){hit.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);ctxEdge(ev.originalEvent,edge,ev.latlng);});hit.on('click',ev=>{L.DomEvent.stopPropagation(ev);ctxEdge(ev.originalEvent,edge,ev.latlng);});}}
function renderBridges(){if(!map)return;bridgeLayer.clearLayers();if(!ui.bridges||!detailOk())return;
world.edges.forEach(e=>{const ll=edgeLL(e);if(!ll||ll.length<2)return;(e.bridges||[]).forEach(br=>bridgeSymbol(br,e,bridgeLayer,false));});}
function renderBlockTicks(){if(!map||!blockTickLayer)return;blockTickLayer.clearLayers();if(!ui.blocks||!detailOk())return;
const dark=!['sat','hybrid','esri'].includes(ui.style);
const main=dark?'#0b1220':'#f2f6fc',under=dark?'#f2f6fc':'#0b1220';
const b=map.getBounds().pad(.25);
world.edges.forEach(e=>{if(!e.double||e.is_building)return;const g=edgeGeom.get(e.id);if(!g)return;
let inView=false;for(const p of g.pts){if(b.contains([p[0],p[1]])){inView=true;break;}}if(!inView)return;
const bl=e.block_len||constants.block_len_default||750;const n=e.blocks_n||Math.max(1,Math.ceil(g.total/bl));
const segs=[];for(let k=1;k<n;k++){const d=k*bl;if(d>=g.total-1)break;const p=pointAt(g,d);const m2=mpp(p.lat);const half=4.5*m2;
const pb=(p.bearing+90)*Math.PI/180;const dx=Math.sin(pb)*half,dy=Math.cos(pb)*half;
segs.push([offsetLL(p.lat,p.lng,dx,dy),offsetLL(p.lat,p.lng,-dx,-dy)]);}
if(!segs.length)return;
L.polyline(segs,{pane:'blockP',color:under,weight:3.4,opacity:.5,interactive:false}).addTo(blockTickLayer);
L.polyline(segs,{pane:'blockP',color:main,weight:1.8,opacity:.9,interactive:false}).addTo(blockTickLayer);});}
function renderBlockOcc(){if(!map||!blockOccLayer)return;blockOccLayer.clearLayers();if(!ui.blockOcc||!detailOk())return;
const seen=new Set();
trainMarkers.forEach(o=>{const h=o._head;if(!h)return;if(seen.has(h.key))return;seen.add(h.key);
const g=edgeGeom.get(h.s.e);if(!g)return;let a,b2;
if(!h.s.double){a=0;b2=g.total;}else{a=h.bi*h.s.block_len;b2=Math.min(g.total,(h.bi+1)*h.s.block_len);}
if(b2-a<1)return;const pts=subPolyline(g,a,b2);
if(pts.length>1)L.polyline(pts,{pane:'blockOccP',color:'#ffb02e',weight:3.5,opacity:.4,interactive:false}).addTo(blockOccLayer);});}
function renderPreview(){if(!map)return;previewLayer.clearLayers();
if(edgeQuote)[...(edgeQuote.bridges||[]),...(edgeQuote.overpasses||[])].forEach(br=>bridgeSymbol(br,null,previewLayer,true));
if(pendingSplit)L.circleMarker([pendingSplit.lat,pendingSplit.lng],{pane:'prevP',radius:12,color:'#a97bff',weight:3,fillColor:'#a97bff',fillOpacity:.2,interactive:false}).addTo(previewLayer);}
function labelIcon(o,kind){const mine=me&&o.owner_id===me.id;const d=document.createElement('div');
if(kind==='depot'){d.className='dlbl'+(mine?' drag':'')+(o.is_building?' build':'');d.textContent='🏭 '+o.name+(o.is_building?' (стройка)':'');}
else if(kind==='hq'){d.className='lbl hq'+(mine?' mine drag':'');d.style.setProperty('--oc',o.color||'#a97bff');d.style.borderColor=o.color||'#a97bff';d.style.color=o.color||'#6f4fd8';d.textContent='🏛 '+(o.display_name||companyName(o.owner_id));}
else{let c='lbl';if(mine){c+=' mine drag';d.style.setProperty('--oc',o.color||'#1f9c5c');d.style.borderColor=o.color||'#1f9c5c';d.style.color=o.color||'#14713f';}
const idle=kind==='substation'?(o.idle!==undefined?o.idle:!o.powering):kind==='track_maint'?(o.idle!==undefined?o.idle:!o.serves):(!o.served&&!o.passed);
if(o.is_building||o.upgrading)c+=' build';else if(o.auction_status==='manual')c+=' sale';else if(o.auction_status==='bankrupt')c+=' bank';else if(idle)c+=' dead';
if(kind==='substation'||kind==='track_maint'){c='lbl'+(mine?' mine drag':'')+(o.is_building?' build':idle?' dead':'');d.style.setProperty('--oc',o.color||'#33507a');if(mine){d.style.borderColor=o.color;d.style.color=o.color;}}
d.className=c;d.textContent=o.name+(o.is_building?' 🏗':o.upgrading?' ⬆':(idle&&kind==='station'&&mine?' 💤':''));}
const off=o.labelOffset||{x:10,y:4};return L.divIcon({html:d,className:'',iconSize:null,iconAnchor:[-(off.x??10),-(off.y??4)]});}
function depotIcon(d){const c=d.color||companyColor(d.owner_id);const fill=d.is_building?'#ffd98a':(d.auction_status?'#f5c518':c);
return L.divIcon({html:'<svg width="26" height="26" viewBox="0 0 26 26"><path d="M3 12 L13 4 L23 12 V22 H3 Z" fill="'+fill+'" stroke="'+(me&&d.owner_id===me.id?'#fff':'#07101c')+'" stroke-width="1.8" stroke-linejoin="round"/><rect x="9" y="14" width="8" height="8" fill="#07101c" opacity=".72" rx="1"/><path d="M9 17h8M9 20h8" stroke="'+fill+'" stroke-width="1"/></svg>',className:'',iconSize:[26,26],iconAnchor:[13,13]});}
function nodeIcon(n){const own=me&&n.owner_id===me.id;const lbl=ui.labels?'<span class="nodeLbl">'+esc(n.name)+'</span>':'';
return L.divIcon({html:'<div class="nodeWrap"><i class="nodeSq'+(own?' own':'')+(n.served?'':' dead')+'" style="background:'+(n.color||companyColor(n.owner_id))+'"></i>'+lbl+'</div>',className:'',iconSize:[14,14],iconAnchor:[7,7]});}
function subIcon(b){const own=me&&b.owner_id===me.id,col=b.color||companyColor(b.owner_id);
const dead=b.idle!==undefined?!!b.idle:(!b.powering&&!b.is_building);
const cls='sbBox'+(own?' own':'')+(b.is_building?' build':'')+(dead?' dead':'');
return L.divIcon({html:'<div class="sbWrap"><i class="objRing" style="border-color:'+col+'"></i><div class="'+cls+'" style="background:'+col+'">⚡</div></div>',className:'',iconSize:[24,24],iconAnchor:[12,12]});}
function tmIcon(x){const own=me&&x.owner_id===me.id,col=x.color||companyColor(x.owner_id);
const dead=x.idle!==undefined?!!x.idle:(!x.serves&&!x.is_building);
const cls='tmBox'+(own?' own':'')+(x.is_building?' build':'')+(dead?' dead':'');
return L.divIcon({html:'<div class="tmWrap"><i class="objRing" style="border-color:'+col+'"></i><div class="'+cls+'" style="background:'+col+'">⚒</div></div>',className:'',iconSize:[24,24],iconAnchor:[12,12]});}
function hqIcon(h){const own=me&&h.owner_id===me.id;return L.divIcon({html:'<div class="hqWrap"><div class="hqBox'+(own?' own':'')+'" style="background:'+(h.color||companyColor(h.owner_id))+'">🏛</div></div>',className:'',iconSize:[30,30],iconAnchor:[15,15]});}
function attachDrag(marker,o,kind){const root=marker.getElement&&marker.getElement();const div=root&&root.firstElementChild;if(!div)return;
div.addEventListener('click',ev=>ev.stopPropagation());
div.addEventListener('pointerdown',e=>{if(!me||o.owner_id!==me.id||e.button!==0)return;e.stopPropagation();e.preventDefault();
const sx=e.clientX,sy=e.clientY,base={x:(o.labelOffset||{}).x??10,y:(o.labelOffset||{}).y??4};let dx=0,dy=0;map.dragging.disable();div.classList.add('dragging');try{div.setPointerCapture(e.pointerId);}catch(_){}
const mv=ev=>{dx=ev.clientX-sx;dy=ev.clientY-sy;div.style.transform='translate('+dx+'px,'+dy+'px)';};
const up=()=>{div.removeEventListener('pointermove',mv);div.removeEventListener('pointerup',up);div.removeEventListener('pointercancel',up);map.dragging.enable();div.classList.remove('dragging');div.style.transform='';
if(Math.abs(dx)+Math.abs(dy)>2){o.labelOffset={x:Math.round(base.x+dx),y:Math.round(base.y+dy)};marker.setIcon(labelIcon(o,kind));attachDrag(marker,o,kind);api('move_label','POST',{id:o.id,kind,x:o.labelOffset.x,y:o.labelOffset.y}).catch(()=>{});SFX.play('snap');}};
div.addEventListener('pointermove',mv);div.addEventListener('pointerup',up);div.addEventListener('pointercancel',up);});}
function renderStations(){if(!map)return;stationLayer.clearLayers();labelLayer.clearLayers();
if(!detailOk()){world.stations.forEach(s=>{const mine=me&&s.owner_id===me.id;
const c=L.circleMarker([s.lat,s.lng],{pane:'stP',radius:mine?3:2.5,fillColor:s.color||companyColor(s.owner_id),color:mine?'#fff':'rgba(7,16,28,.6)',weight:mine?1.2:.8,fillOpacity:1,interactive:true}).addTo(stationLayer);
c.bindTooltip('<b>'+esc(s.name)+'</b> · '+esc(companyName(s.owner_id)),{direction:'top',offset:[0,-4],className:'tip'});
c.on('click',ev=>onStationClick(ev,s));c.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxStation(ev.originalEvent,s);});});return;}
world.stations.forEach(s=>{const mine=me&&s.owner_id===me.id,ll=[s.lat,s.lng];const h=s.health??100;const vr=clamp(5+((s.class||1)-1)*.7,5,12);
let fill=s.color||companyColor(s.owner_id),stroke=mine?'#fff':'#0b1220',dash=null,fo=1;
if(s.is_building){fill='#ffd98a';stroke='#e0bf5c';dash='3 3';}
else if(s.upgrading){stroke='#ffb02e';dash='3 3';}
else if(s.auction_status==='bankrupt'){fill='#98a4b4';stroke='#5b6c85';}
else if(s.auction_status==='manual'){fill=lotAfford(s)?'#f5c518':'rgba(245,197,24,.45)';stroke='#c79f00';}
else if(h<HEALTH_CRIT)fill='#8a94a2';else if(h<50)fill='#d4a017';else if(!s.served&&!s.passed){fo=.62;stroke='#c3ccd8';dash='2 3';}
const c=L.circleMarker(ll,{pane:'stP',radius:s.is_building?6:vr,fillColor:fill,color:stroke,weight:mine?2.5:2,fillOpacity:fo,dashArray:dash}).addTo(stationLayer);
let tip='<b>'+esc(s.name)+'</b> · <span style="color:'+s.color+'">■</span> '+esc(companyName(s.owner_id))+'<br>класс '+(s.class||1)+' · маршрутов '+(s.routes_used||0)+'/'+(s.routes_limit||s.class||1)+' · путей '+(s.tracks??stTracks(s.id))+'<br>🚃 лимит: '+(s.wagon_limit??wagonLimitByClass(s.class||1))+' ваг.<br>🏢 '+(s.buildings??0)+' → '+bonusPct(s.buildings)+'% · состояние '+h+'%';
if(s.blocks_entry)tip+='<br>🚦 вход блокируется на время стоянки (кл. ≤3)';else tip+='<br>🚦 путевое развитие: поезда разъезжаются';
if(s.upgrading)tip+='<br>⬆️ до кл.'+s.upgrade_class+' · <span data-cd="'+s.upgrade_end+'">'+dur(left(s.upgrade_end))+'</span>';
if(me&&!mine)tip+='<br>остановки: '+(s.can_stop?'разрешены':'🔒')+' · подключение: '+(s.can_connect?'разрешено':'🔒');
if(s.is_building)tip+='<br>🏗 <span data-cd="'+s.build_end+'">'+dur(left(s.build_end))+'</span>';else if(s.passed&&!s.served)tip+='<br>⏩ проездом';else if(!s.served)tip+='<br>💤 нет маршрутов';
c.bindTooltip(tip,{direction:'top',offset:[0,-vr-2],className:'tip'});
c.on('click',ev=>onStationClick(ev,s));c.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxStation(ev.originalEvent,s);});
if(ui.labels){const m=L.marker(ll,{pane:'lblP',icon:labelIcon(s,'station'),keyboard:false}).addTo(labelLayer);m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxStation(ev.originalEvent,s);});attachDrag(m,s,'station');}});}
function renderNodes(){if(!map)return;nodeLayer.clearLayers();if(!ui.nodes||!detailOk())return;
world.nodes.forEach(n=>{const m=L.marker([n.lat,n.lng],{pane:'nodeP',icon:nodeIcon(n),keyboard:false}).addTo(nodeLayer);
let tip='<b>🔩 '+esc(n.name)+'</b> · <span style="color:'+n.color+'">■</span> '+esc(companyName(n.owner_id))+'<br>перегонов: '+(n.edges_count??0)+' · '+(n.served?'в сети':'💤');
if(me&&n.owner_id!==me.id)tip+='<br>подключение: '+(n.can_connect?'разрешено':'🔒');
m.bindTooltip(tip,{direction:'top',offset:[0,-8],className:'tip'});m.on('click',ev=>onNodeClick(ev,n));m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxNode(ev.originalEvent,n);});});}
function renderSubstations(){if(!map)return;subLayer.clearLayers();if(!detailOk())return;const range=constants.substation_range_m||SUBSTATION_RANGE_M;
world.substations.forEach(b=>{const ll=[b.lat,b.lng];const col=b.color||companyColor(b.owner_id);
if(ui.subCircles||mode==='substation')L.circle(ll,{pane:'hintP',radius:b.range_m||range,color:col,weight:1.4,fillColor:col,fillOpacity:.045,dashArray:'6 6',interactive:false}).addTo(subLayer);
const m=L.marker(ll,{pane:'nodeP',icon:subIcon(b),keyboard:false}).addTo(subLayer);
const dead=b.idle!==undefined?!!b.idle:(!b.powering&&!b.is_building);
let tip='<b>⚡ '+esc(b.name)+'</b> · <span style="color:'+col+'">■</span> '+esc(companyName(b.owner_id))+'<br>радиус '+((b.range_m||range)/1000).toFixed(0)+' км · '+(b.powering?'⚡ питает сеть':'💤 холостая · содержание ×0.5')+'<br>'+(b.health??100)+'% · '+money(sbUpkeep(b))+'/д';
if(b.is_building)tip+='<br>🏗 <span data-cd="'+b.build_end+'">'+dur(left(b.build_end))+'</span>';
m.bindTooltip(tip,{direction:'top',offset:[0,-12],className:'tip'});
m.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxSub(ev.originalEvent,b);else if(mode==='substation')buildSubstationFlow(L.latLng(b.lat,b.lng));});
m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxSub(ev.originalEvent,b);});
if(ui.labels){const lm=L.marker(ll,{pane:'lblP',icon:labelIcon(b,'substation'),keyboard:false}).addTo(subLayer);lm.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxSub(ev.originalEvent,b);});attachDrag(lm,b,'substation');}});}
function renderTrackMaint(){if(!map)return;tmLayer.clearLayers();if(!detailOk())return;const range=constants.track_maint_range_m||TRACK_MAINT_RANGE_M;
world.track_maint.forEach(x=>{const ll=[x.lat,x.lng];const col=x.color||companyColor(x.owner_id);
if(ui.tmCircles||mode==='trackmaint')L.circle(ll,{pane:'hintP',radius:x.range_m||range,color:col,weight:1.4,fillColor:col,fillOpacity:.045,dashArray:'6 6',interactive:false}).addTo(tmLayer);
const m=L.marker(ll,{pane:'nodeP',icon:tmIcon(x),keyboard:false}).addTo(tmLayer);
const dead=x.idle!==undefined?!!x.idle:(!x.serves&&!x.is_building);
let tip='<b>⚒ '+esc(x.name)+'</b> · <span style="color:'+col+'">■</span> '+esc(companyName(x.owner_id))+'<br>радиус '+((x.range_m||range)/1000).toFixed(0)+' км · '+(x.serves?'🛤 обслуживает двухпутки':'💤 холостая · содержание ×0.5')+'<br>'+money(tmUpkeep(x))+'/д';
if(x.is_building)tip+='<br>🏗 <span data-cd="'+x.build_end+'">'+dur(left(x.build_end))+'</span>';
m.bindTooltip(tip,{direction:'top',offset:[0,-12],className:'tip'});
m.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxTm(ev.originalEvent,x);else if(mode==='trackmaint')buildTrackMaintFlow(L.latLng(x.lat,x.lng));});
m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxTm(ev.originalEvent,x);});
if(ui.labels){const lm=L.marker(ll,{pane:'lblP',icon:labelIcon(x,'track_maint'),keyboard:false}).addTo(tmLayer);lm.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxTm(ev.originalEvent,x);});attachDrag(lm,x,'track_maint');}});}
function renderDepots(){if(!map)return;depotLayer.clearLayers();if(!detailOk())return;
world.depots.forEach(d=>{const ll=[d.lat,d.lng];const m=L.marker(ll,{icon:depotIcon(d),keyboard:false}).addTo(depotLayer);const s=st(d.station_id);
let tip='<b>🏭 '+esc(d.name)+'</b> · <span style="color:'+d.color+'">■</span> '+esc(companyName(d.owner_id))+'<br>ур.'+(d.level||1)+' · мест '+(d.trains_count||0)+'/'+d.capacity+'<br>у «'+esc(s?s.name:'—')+'» · '+(d.health??100)+'%<br><span style="color:#9db0c9">клик — поезда депо · ПКМ — меню</span>';
if(d.is_building)tip+='<br>🏗 <span data-cd="'+d.build_end+'">'+dur(left(d.build_end))+'</span>';else if(d.upgrading)tip+='<br>⬆️ <span data-cd="'+d.upgrade_end+'">'+dur(left(d.upgrade_end))+'</span>';
m.bindTooltip(tip,{direction:'top',offset:[0,-14],className:'tip'});
m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxDepot(ev.originalEvent,d);});
m.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')openDepotModal(d);});
if(ui.labels){const lm=L.marker(ll,{pane:'lblP',icon:labelIcon(d,'depot'),keyboard:false}).addTo(depotLayer);lm.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxDepot(ev.originalEvent,d);});lm.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')openDepotModal(d);});attachDrag(lm,d,'depot');}});}
function renderHqs(){if(!map)return;hqLayer.clearLayers();
world.hqs.forEach(h=>{const ll=[h.lat,h.lng];const m=L.marker(ll,{pane:'nodeP',icon:hqIcon(h),keyboard:false,zIndexOffset:500}).addTo(hqLayer);
m.bindTooltip('<b>🏛 Штаб · '+esc(h.display_name||companyName(h.owner_id))+'</b><br>'+(h.founder?'Основатель: '+esc(h.founder)+'<br>':'')+'клик — карточка компании',{direction:'top',offset:[0,-16],className:'tip'});
m.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxHq(ev.originalEvent,h);});
m.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxHq(ev.originalEvent,h);});
if(ui.labels){const lm=L.marker(ll,{pane:'lblP',icon:labelIcon(h,'hq'),keyboard:false}).addTo(hqLayer);lm.on('click',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxHq(ev.originalEvent,h);});lm.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxHq(ev.originalEvent,h);});attachDrag(lm,h,'hq');}});}
function renderDetailLayers(){renderStations();renderNodes();renderDepots();renderSubstations();renderTrackMaint();renderBridges();renderBlockTicks();syncTrains();updateScaleHint();}
function renderDraft(){if(!map)return;draftLayer.clearLayers();hintLayer.clearLayers();
if(mode==='edge'&&edgeDraft.start){const a=edgeDraft.start;
L.circleMarker([a.lat,a.lng],{pane:'prevP',radius:9,color:'#ff5a5a',weight:3,fillColor:'#fff',fillOpacity:.9,interactive:false}).addTo(draftLayer);
L.marker([a.lat,a.lng],{pane:'prevP',interactive:false,keyboard:false,icon:numBadge('0','#ff5a5a')}).addTo(draftLayer);
const pts=[[a.lat,a.lng],...edgeDraft.points.map(p=>[p.lat,p.lng])];
if(mouseLL){const s=snapHit?L.latLng(snapHit.lat,snapHit.lng):snap(mouseLL);pts.push([s.lat,s.lng]);}
if(pts.length>1)L.polyline(pts,{pane:'prevP',color:'#ff5a5a',weight:2.5,dashArray:'6 5',interactive:false}).addTo(draftLayer);
edgeDraft.points.forEach((p,i)=>{L.circleMarker([p.lat,p.lng],{pane:'prevP',radius:4.5,color:'#ff5a5a',weight:2,fillColor:'#0b1220',fillOpacity:1,interactive:false}).addTo(draftLayer);
L.marker([p.lat,p.lng],{pane:'prevP',interactive:false,keyboard:false,icon:numBadge(String(i+1),'#ff5a5a')}).addTo(draftLayer);});}
if(mode==='route'){
if(routeGeom&&routeGeom.length>1)L.polyline(routeGeom,{pane:'prevP',color:'#a97bff',weight:4,opacity:.8,interactive:false}).addTo(draftLayer);
routeDraft.stops.forEach((sid,i)=>{const s=st(sid);if(!s)return;
L.circle([s.lat,s.lng],{pane:'hintP',radius:stRadius(s.class||1)+6,color:'#a97bff',weight:2,fillColor:'#a97bff',fillOpacity:.16,interactive:false}).addTo(draftLayer);
L.marker([s.lat,s.lng],{pane:'prevP',interactive:false,keyboard:false,icon:numBadge(String(i+1),'#a97bff')}).addTo(draftLayer);});}
if((mode==='depot'||mode==='substation'||mode==='trackmaint')&&me){const col=mode==='depot'?'#24d3c4':(mode==='substation'?'#ffb02e':'#d9a441');
world.stations.filter(s=>!s.is_building&&(s.owner_id===me.id||s.can_connect)).forEach(s=>L.circle([s.lat,s.lng],{pane:'hintP',radius:DEPOT_RADIUS,color:col,weight:1.5,fillColor:col,fillOpacity:.06,dashArray:'5 5',interactive:false}).addTo(hintLayer));}}
function dock(html){const d=el('dock');if(!html){d.classList.remove('show');setTimeout(()=>{if(!d.classList.contains('show'))d.innerHTML='';},220);return;}d.innerHTML=html;d.classList.add('show');}
function updateDock(){if(mode==='idle'){dock(null);el('modeBadge').classList.remove('show');return;}
const badge=(ic,tx)=>{el('mbIc').textContent=ic;el('mbTx').textContent=tx;el('modeBadge').classList.add('show');};
if(mode==='station'){badge('🚉','Постановка станции');
dock('<div class="dock-head"><span>🚉</span>Кликните по карте<span class="sp"><button class="btn btn-sm" id="dkCancel">Отмена · Esc</button></span></div><div class="dock-stats"><span class="dstat">цена <b>'+money(pricing.station)+'</b></span><span class="dstat">класс 1 → <b>1 маршрут · '+wagonLimitByClass(1)+' ваг.</b></span><span class="dstat">1 день</span></div><div class="dock-note">Класс ограничивает число маршрутов и длину составов.</div>');
el('dkCancel').onclick=()=>setMode('idle');}
else if(mode==='edge'){badge('🛤','Перегон / ветка');const a=edgeDraft.start;let len=0;
if(a){let p=[a.lat,a.lng];edgeDraft.points.forEach(q=>{len+=hav(p[0],p[1],q.lat,q.lng);p=[q.lat,q.lng];});if(mouseLL){const s=snapHit?L.latLng(snapHit.lat,snapHit.lng):snap(mouseLL);len+=hav(p[0],p[1],s.lat,s.lng);}}
const over=len>maxEdgeLen();
dock('<div class="dock-head"><span>🛤</span>'+(a?'От «'+esc(a.name||'узел')+'» · точка 0':'Кликните станцию, узел или поле — начало')+'<span class="sp"><button class="btn btn-sm" id="dkCancel">Отмена</button></span></div><div class="dock-stats"><span class="dstat">изгибов <b>'+edgeDraft.points.length+'</b></span>'+(a?'<span class="dstat'+(over?' bad':'')+'">≈ <b>'+Math.round(len)+' м</b>'+(over?' / '+Math.round(maxEdgeLen()/1000)+' км!':'')+'</span><span class="dstat">≈ <b>'+money(Math.ceil(len*pricing.edge))+'</b></span>':'')+(snapHit?'<span class="dstat good">🔗 <b>'+esc(snapHit.name||'узел')+'</b></span>':'')+'</div><div class="dock-note'+(over?' bad':'')+'">'+(over?'⛔ Перегон длиннее 20 км — поставьте промежуточный узел или станцию. ':'')+'Изгибы нумеруются 1…N. Клик по станции/узлу — завершить; Enter — конец в поле (узел '+money(pricing.node)+'); клик по перегону без начала — врезка.</div>'+(a?'<div class="dock-actions"><button class="btn" id="dkUndo" '+(edgeDraft.points.length?'':'disabled')+'>⌫ Изгиб</button><button class="btn btn-primary" id="dkFinish" '+(over?'disabled':'')+'>⏹ Завершить · Enter</button></div>':''));
el('dkCancel').onclick=()=>setMode('idle');
if(a){el('dkUndo').onclick=()=>{edgeDraft.points.pop();renderDraft();updateDock();};el('dkFinish').onclick=finishEdgeInField;}}
else if(mode==='depot'||mode==='substation'||mode==='trackmaint'){const isSub=mode==='substation',isTm=mode==='trackmaint';
badge(isSub?'⚡':isTm?'⚒':'🏭',isSub?'Подстанция':isTm?'Путевая часть':'Депо');
let info='';if(mouseLL&&me){const ll=snap(mouseLL);let best=null,bd=Infinity;world.stations.filter(s=>!s.is_building&&(s.owner_id===me.id||s.can_connect)).forEach(s=>{const d=hav(ll.lat,ll.lng,s.lat,s.lng);if(d<bd){bd=d;best=s;}});
const rad=isSub?SUBSTATION_RADIUS:isTm?TRACK_MAINT_RADIUS:DEPOT_RADIUS;
info=best?'<span class="dstat '+(bd<=rad?'good':'bad')+'">«'+esc(best.name)+'» <b>'+Math.round(bd)+' м</b></span>':'<span class="dstat bad">нет станций</span>';}
const price=isSub?pricing.substation:isTm?pricing.track_maint:pricing.depot;
dock('<div class="dock-head"><span>'+(isSub?'⚡':isTm?'⚒':'🏭')+'</span>Кликните в круге (≤500 м от станции)<span class="sp"><button class="btn btn-sm" id="dkCancel">Отмена</button></span></div><div class="dock-stats"><span class="dstat">цена <b>'+money(price)+'</b></span>'+(isSub?'<span class="dstat">питание <b>8 км</b></span>':isTm?'<span class="dstat">обслуживание <b>10 км</b></span>':'<span class="dstat">мест <b>3</b></span>')+'<span class="dstat">2 дня</span>'+info+'</div>');
el('dkCancel').onclick=()=>setMode('idle');}
else if(mode==='hq'){badge('🏛','Перенос штаба');
dock('<div class="dock-head"><span>🏛</span>Кликните в любом месте — штаб переедет<span class="sp"><button class="btn btn-sm" id="dkCancel">Отмена</button></span></div><div class="dock-stats"><span class="dstat good">бесплатно</span><span class="dstat">мгновенно</span><span class="dstat bad">кроме запретных зон</span></div>');
el('dkCancel').onclick=()=>setMode('idle');}
else if(mode==='route'){badge('🧭','Сборка маршрута');const q=routeQuote;
const chips=routeDraft.stops.map((sid,i)=>{const s=st(sid);return '<span class="schip"><i>'+(i+1)+'</i>'+esc(s?s.name:'?')+(s&&s.upgrading?'<i class="up">⬆</i>':'')+'<button data-rm="'+i+'">×</button></span>';}).join('');
let stats='',note='Линия идёт строго по перегонам. Клик по первой станции замыкает кольцо.',bad=false,issuesHtml='',blocked=false;
if(routeDraft.stops.length>=2&&q){if(q.ok){const inf=q.info;const one=q.income_by_capacity&&q.income_by_capacity[1]?q.income_by_capacity[1].income:0;
stats='<span class="dstat good"><b>'+(inf.km/1000).toFixed(2)+' км</b></span><span class="dstat">линия <b>'+inf.line_speed+'</b> · средняя <b>'+inf.speed+'</b></span><span class="dstat">ёмкость <b>'+inf.slots+'</b></span><span class="dstat">🚃 ≤ <b>'+inf.wagon_limit+'</b></span><span class="dstat good">ёмк.1 → <b>'+money(one)+'/д</b></span>'+(inf.fee_total?'<span class="dstat warn">🤝 <b>'+money(inf.fee_total)+'</b></span>':'')+(inf.fully_powered?'':'<span class="dstat warn">⚡ не везде</span>');
note='Грузы выгодны с '+breakEvenKm()+' км.';if((inf.bottlenecks||[]).length)note+=' Узкие места: '+inf.bottlenecks.slice(0,2).map(b=>esc(b.name)+' '+b.speed).join(', ')+'.';}
else{bad=true;note='⛔ '+q.reason;}
const issues=q.slot_issues||[];
if(issues.length){blocked=true;issuesHtml='<div class="dock-note bad" style="margin-top:6px">⚠ Слоты исчерпаны — улучшите станции, не выходя из режима:</div>';
issues.forEach((isx,k)=>{if(isx.upgrading)issuesHtml+='<div class="row-line"><span>⬆ «'+esc(isx.name)+'» → кл.'+isx.upgrade_class+'</span><span class="mini" data-cd="'+isx.upgrade_end+'">'+dur(left(isx.upgrade_end))+'</span></div>';
else if(isx.maxed)issuesHtml+='<div class="row-line"><span>«'+esc(isx.name)+'» '+isx.used+'/'+isx.limit+'</span><span class="mini">макс класс</span></div>';
else if(isx.frozen)issuesHtml+='<div class="row-line"><span>«'+esc(isx.name)+'» '+isx.used+'/'+isx.limit+'</span><span class="mini">на продаже</span></div>';
else issuesHtml+='<div class="row-line"><span>«'+esc(isx.name)+'» '+isx.used+'/'+isx.limit+'</span><button class="btn btn-sm" data-upst="'+k+'" '+(me.money>=isx.cost?'':'disabled')+'>⬆ кл.'+isx.next_class+' · '+money(isx.cost)+' · ~'+isx.build_days+' д</button></div>';});}}
dock('<div class="dock-head"><span>🧭</span>Остановок '+routeDraft.stops.length+(routeDraft.loop?' · кольцо':'')+'<span class="sp"><button class="btn btn-sm" id="dkLoop">'+(routeDraft.loop?'⟲ Разомкнуть':'⟳ Замкнуть')+'</button><button class="btn btn-sm" id="dkCancel">Отмена</button></span></div><div class="dock-chips">'+(chips||'<span class="dock-note">Пока пусто</span>')+'</div>'+(stats?'<div class="dock-stats">'+stats+'</div>':'')+issuesHtml+'<div class="dock-note'+(bad?' bad':'')+'">'+esc(note)+(blocked?' Откроется после улучшений.':'')+'</div><div class="dock-actions"><button class="btn btn-violet" id="dkCreate" '+((!q||!q.ok||blocked)?'disabled':'')+'>🧭 Открыть маршрут</button></div>');
el('dkCancel').onclick=()=>setMode('idle');
el('dkLoop').onclick=()=>{if(routeDraft.stops.length<3){toast('Для кольца нужно ≥3 станции');return;}routeDraft.loop=!routeDraft.loop;SFX.play('click');renderDraft();quoteRoute();};
el('dkCreate').onclick=createRouteFlow;
el('dock').querySelectorAll('[data-rm]').forEach(b=>b.onclick=()=>{routeDraft.stops.splice(+b.dataset.rm,1);if(routeDraft.stops.length<3)routeDraft.loop=false;SFX.play('snap');renderDraft();quoteRoute();});
el('dock').querySelectorAll('[data-upst]').forEach(b=>b.onclick=async()=>{const isx=(routeQuote.slot_issues||[])[+b.dataset.upst];if(!isx)return;b.disabled=true;
const r=await act('upgrade_station',{station_id:isx.station_id},'Улучшаем станцию…','build');
if(r)toast('⬆ «'+isx.name+'» → кл.'+r.class+' · слотов '+r.routes_limit+' · '+dur(left(r.build_end)));quoteRoute();});}}
function findSnap(ll){const r=constants.node_snap_m||30;let best=null,bd=Infinity;idxEp.forEach(o=>{const d=hav(ll.lat,ll.lng,o.lat,o.lng);if(d<=r&&d<bd){bd=d;best=o;}});return best;}
function onMapMove(e){mouseLL=e.latlng;
if(mode==='edge'){snapHit=findSnap(e.latlng);const ll=snapHit?L.latLng(snapHit.lat,snapHit.lng):snap(e.latlng);
if(ghost)map.removeLayer(ghost);if(ghostBadge)map.removeLayer(ghostBadge);
ghost=L.circleMarker(ll,{pane:'prevP',radius:snapHit?11:6,color:snapHit?'#35d07f':'#ff5a5a',weight:2,fillColor:snapHit?'rgba(53,208,127,.25)':'#fff',fillOpacity:.8,dashArray:snapHit?null:'3 3',interactive:false}).addTo(map);
if(edgeDraft.start){ghostBadge=L.marker(ll,{pane:'prevP',interactive:false,keyboard:false,icon:numBadge(snapHit?'🔗':String(edgeDraft.points.length+1),snapHit?'#35d07f':'#ff5a5a',.65)}).addTo(map);
const a=edgeDraft.start;const pts=[[a.lat,a.lng],...edgeDraft.points.map(p=>[p.lat,p.lng]),[ll.lat,ll.lng]];
if(tempLine)map.removeLayer(tempLine);tempLine=L.polyline(pts,{pane:'prevP',color:'#ff5a5a',weight:2,dashArray:'5 5',interactive:false}).addTo(map);
let len=0;for(let i=0;i<pts.length-1;i++)len+=hav(pts[i][0],pts[i][1],pts[i+1][0],pts[i+1][1]);
if(tempTip)map.removeLayer(tempTip);tempTip=L.tooltip({permanent:true,direction:'top',className:'tip',offset:[0,-26]}).setLatLng(ll).setContent(Math.round(len)+' м · '+money(Math.ceil(len*pricing.edge))+(len>maxEdgeLen()?' · ⛔ >20 км':'')+(snapHit?' · 🔗 '+esc(snapHit.name||'узел'):'')).addTo(map);}
updateDock();}
else if(mode==='station'){const ll=snap(e.latlng);if(ghost)map.removeLayer(ghost);
ghost=L.circleMarker(ll,{pane:'prevP',radius:stRadius(1),fillColor:'rgba(53,208,127,.35)',color:'#35d07f',weight:1.5,dashArray:'3 3',fillOpacity:.55,interactive:false}).addTo(map);
clearTimeout(osmTimer);osmTimer=setTimeout(async()=>{try{const r=await api('check_osm_station_buildings','POST',{lat:ll.lat,lng:ll.lng});
if(osmTip){map.removeLayer(osmTip);osmTip=null;}const parts=[];if(r.discount>0)parts.push('🌟 скидка 10%');parts.push('🏢 '+r.buildings+' → '+r.income_percent+'%');if(r.forbidden_zone)parts.push('⛔ '+r.forbidden_zone);
osmTip=L.tooltip({permanent:true,direction:'top',className:'tip',offset:[0,-10]}).setLatLng(ll).setContent(parts.join(' · ')).addTo(map);}catch(err){}},420);}
else if(mode==='hq'){const ll=snap(e.latlng);if(ghost)map.removeLayer(ghost);
ghost=L.marker(ll,{pane:'prevP',interactive:false,icon:L.divIcon({html:'<div class="hqWrap" style="opacity:.8"><div class="hqBox own" style="background:'+(me?me.color:'#a97bff')+'">🏛</div></div>',className:'',iconSize:[30,30],iconAnchor:[15,15]})}).addTo(map);}
else if(mode==='depot'||mode==='substation'||mode==='trackmaint'){const ll=snap(e.latlng);if(ghost)map.removeLayer(ghost);
let best=null,bd=Infinity;if(me)world.stations.filter(s=>!s.is_building&&(s.owner_id===me.id||s.can_connect)).forEach(s=>{const d=hav(ll.lat,ll.lng,s.lat,s.lng);if(d<bd){bd=d;best=s;}});
const isSub=mode==='substation',isTm=mode==='trackmaint';const rad=isSub?SUBSTATION_RADIUS:isTm?TRACK_MAINT_RADIUS:DEPOT_RADIUS;const ok=best&&bd<=rad;
let ico;if(isSub)ico='<div style="width:24px;height:24px;border-radius:7px;border:2px solid #07101c;background:'+(ok?'#ffb02e':'#ff5a5a')+';display:flex;align-items:center;justify-content:center;font-size:13px;opacity:'+(ok?.95:.45)+'">⚡</div>';
else if(isTm)ico='<div style="width:24px;height:24px;border-radius:7px;border:2px solid #07101c;background:'+(ok?'#d9a441':'#ff5a5a')+';display:flex;align-items:center;justify-content:center;font-size:14px;opacity:'+(ok?.95:.45)+'">⚒</div>';
else ico='<svg width="26" height="26" viewBox="0 0 26 26" opacity="'+(ok?.95:.45)+'"><path d="M3 12 L13 4 L23 12 V22 H3 Z" fill="'+(ok?'#24d3c4':'#ff5a5a')+'" stroke="#07101c" stroke-width="1.6"/><rect x="9" y="14" width="8" height="8" fill="#07101c" opacity=".7" rx="1"/></svg>';
ghost=L.marker(ll,{pane:'prevP',interactive:false,icon:L.divIcon({html:ico,className:'',iconSize:[26,26],iconAnchor:[13,13]})}).addTo(map);updateDock();}}
function onMapClick(e){if(mode==='station')buildStationFlow(snap(e.latlng));
else if(mode==='depot')buildDepotFlow(snap(e.latlng));
else if(mode==='substation')buildSubstationFlow(snap(e.latlng));
else if(mode==='trackmaint')buildTrackMaintFlow(snap(e.latlng));
else if(mode==='hq')moveHqConfirm(snap(e.latlng));
else if(mode==='edge'){const s=findSnap(e.latlng);if(s){pickEdgeEndpoint(s);return;}const ll=snap(e.latlng);
if(!edgeDraft.start){edgeDraft.start={id:'',lat:ll.lat,lng:ll.lng,name:'точка в поле',kind:'field'};SFX.play('click');}
else{edgeDraft.points.push({lat:ll.lat,lng:ll.lng});SFX.play('snap');}renderDraft();updateDock();}
else if(mode==='idle')closeCtx();}
function pickEdgeEndpoint(o){if(!edgeDraft.start){if(me&&o.owner_id!==me.id&&!o.can_connect){toast('🔒 «'+o.name+'»: подключение запрещено');return;}
edgeDraft.start={id:o.id,lat:o.lat,lng:o.lng,name:o.name,kind:o.kind};edgeDraft.points=[];SFX.play('click');renderDraft();updateDock();return;}
if(edgeDraft.start.id&&edgeDraft.start.id===o.id){edgeDraft.start=null;edgeDraft.points=[];renderDraft();updateDock();return;}
if(me&&o.owner_id!==me.id&&!o.can_connect){toast('🔒 «'+o.name+'»: подключение запрещено');return;}
quoteAndBuildEdge({to:o.id},{lat:o.lat,lng:o.lng});}
function finishEdgeInField(){if(!edgeDraft.start){toast('Сначала выберите начало');return;}let ll=null;
if(edgeDraft.points.length)ll=edgeDraft.points.pop();else if(mouseLL){const s=snap(mouseLL);ll={lat:s.lat,lng:s.lng};}
if(!ll){toast('Нет точки');renderDraft();updateDock();return;}
const len=hav(edgeDraft.start.lat,edgeDraft.start.lng,ll.lat,ll.lng);
if(len<20){toast('Слишком короткий перегон');return;}
if(len>maxEdgeLen()){toast('⛔ Перегон длиннее 20 км — разбейте его узлами или станциями');renderDraft();updateDock();return;}
quoteAndBuildEdge({to_point:ll},ll);}
function onStationClick(ev,s){L.DomEvent.stopPropagation(ev);
if(mode==='route'){routePick(s);return;}if(mode==='edge'){pickEdgeEndpoint({...s,kind:'station'});return;}
if(mode==='depot'){buildDepotFlow(L.latLng(s.lat,s.lng));return;}if(mode==='substation'){buildSubstationFlow(L.latLng(s.lat,s.lng));return;}
if(mode==='trackmaint'){buildTrackMaintFlow(L.latLng(s.lat,s.lng));return;}if(mode==='idle')ctxStation(ev.originalEvent,s);}
function onNodeClick(ev,n){L.DomEvent.stopPropagation(ev);if(mode==='edge'){pickEdgeEndpoint({...n,kind:'node'});return;}
if(mode==='route'){toast('Узел — не остановка');return;}if(mode==='idle')ctxNode(ev.originalEvent,n);}
function previewHtml(r,cost){let html='';if(r.discount>0){const fc=Math.ceil(cost*.9);html+='<div class="disc">🌟 Реальная станция OSM — скидка 10 %</div><div style="margin-bottom:8px"><span class="price-old">'+money(cost)+'</span><span class="price-new">'+money(fc)+'</span></div>';}
else html+='<div style="margin-bottom:8px;font:800 16px var(--f-mono)">'+money(cost)+'</div>';
html+='<div style="font-size:13px">🏢 Зданий в 500 м: <b style="color:var(--ink)">'+r.buildings+'</b> → бонус <b style="color:var(--amber-t)">'+r.income_percent+'%</b></div>';return html;}
function buildStationFlow(ll){if(osmTip){map.removeLayer(osmTip);osmTip=null;}
modal({title:'🚉 Новая станция',body:'<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60">',input:'Станция '+(world.stations.filter(s=>me&&s.owner_id===me.id).length+1),ok:'Продолжить',
onOk:async name=>{loading('Проверяем местность…');
try{const r=await api('check_osm_station_buildings','POST',{lat:ll.lat,lng:ll.lng});unloading();
if(r.forbidden_zone){toast('⛔ '+r.forbidden_zone);return;}
let cost=pricing.station;if(r.discount>0)cost=Math.ceil(cost*.9);
let html=previewHtml(r,pricing.station)+'<div class="note">Класс 1 = 1 маршрут и составы до '+wagonLimitByClass(1)+' вагонов.</div>';
const poor=me.money<cost;if(poor)html+='<div class="alert">💸 Не хватает: '+money(me.money)+'</div>';
modal({title:'Подтверждение',body:'<p style="margin:0 0 10px;color:var(--ink)">Построить «'+esc(name)+'»?</p>'+html,ok:'Построить',disabled:poor,
onOk:async()=>{loading('Строим…');try{await api('build_station','POST',{name,lat:ll.lat,lng:ll.lng});unloading();SFX.play('build');toast('🚉 «'+name+'» строится');setMode('idle');await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});
}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function quoteAndBuildEdge(toPart,ll){const a=edgeDraft.start;const body={points:edgeDraft.points};
if(a.id)body.from=a.id;else body.from_point={lat:a.lat,lng:a.lng};Object.assign(body,toPart);
loading('Ищем реки и пересечения…');
api('quote_edge','POST',body).then(q=>{unloading();edgeQuote=q;setTypes(q.bridge_types);renderPreview();edgeModal(body,q);}).catch(e=>{unloading();toast('Ошибка: '+e.message);});}
function structRow(br,i){const isRail=br.kind==='rail';
const opts=bridgeTypes.filter(t=>!t.rail_only||isRail).map(t=>'<option value="'+t.key+'" '+(t.key===br.type?'selected':'')+'>'+esc(t.label)+' · '+t.speed+' км/ч · '+fmt(isRail?t.cost_rail:t.cost_river)+' ₽</option>').join('');
return '<div class="br-item"><div class="br-top"><span class="sw" data-sw="'+i+'" style="background:'+MAT_COLOR[bridgeMat(br.type)]+'"></span>'+(isRail?'🌉':'🏞')+' '+esc(br.name||'')+'</div><div class="br-meta">пролёт '+Math.round(br.span)+' м'+(br.angle?' · угол '+br.angle+'°':'')+' · скорость режется только здесь</div><select class="select" data-br="'+i+'">'+opts+'</select></div>';}
function blockLenControl(cur){const mn=constants.block_len_min||250,mx=constants.block_len_max||5000,stp=constants.block_len_step||50;
return '<div class="vcap">🚦 Длина блок-участка <b><span id="blVal">'+cur+'</span> м</b></div>'
+'<input type="range" id="blRange" min="'+mn+'" max="'+mx+'" step="'+stp+'" value="'+cur+'" aria-label="Длина блока">'
+'<div class="dock-chips" style="margin-top:6px">'+[250,500,750,1000,1500,2000].map(p=>'<button class="btn btn-sm" data-bl="'+p+'">'+p+'</button>').join('')+'</div>'
+'<div class="hint" id="blInfo"></div>';}
function wireBlockLen(root,len,dbl,cb){const r=el('blRange');if(!r)return;const upd=v=>{v=clamp(Math.round(v/(constants.block_len_step||50))*(constants.block_len_step||50),constants.block_len_min||250,constants.block_len_max||5000);
const bv=el('blVal');if(bv)bv.textContent=v;const n=dbl?Math.max(1,Math.ceil(len/v)):1;
const bi=el('blInfo');if(bi)bi.innerHTML=dbl?('После двухпутки: <b style="color:var(--ink)">'+n+'</b> бл. · сигнализация '+money(n*(constants.cost_block||10))):('Сейчас однопутка = <b style="color:var(--ink)">1 блок</b> (+10 ₽). После 2-го пути: <b style="color:var(--ink)">'+Math.max(1,Math.ceil(len/v))+'</b> бл.');
cb&&cb(v,n);};
r.oninput=()=>upd(+r.value);root.querySelectorAll('[data-bl]').forEach(b=>b.onclick=()=>{r.value=b.dataset.bl;upd(+b.dataset.bl);SFX.play('snap');});upd(+r.value);}
function edgeModal(reqBody,q){const epN=p=>p.mode==='existing'?(p.kind==='node'?'узел '+(p.name||''):p.name||'объект'):'новый узел в поле';
const all=[...(q.bridges||[]),...(q.overpasses||[])];
let head=q.coverage>.01?'<div class="disc">🛰 Покрытие ЖД: '+Math.round(q.coverage*100)+'% · скидка '+Math.round(q.discount_pct*100)+'%</div><div><span class="price-old">'+money(q.cost_base)+'</span><span class="price-new">'+money(q.cost_discount)+'</span></div>':'<div style="font:800 17px var(--f-mono)">'+money(q.cost_base)+'</div>';
let obs='';if((q.obstacles||[]).length)obs='<div class="box"><div class="box-head" data-box="obs"><span>🚧 Препятствия · '+q.obstacles.reduce((s,o)=>s+o.count,0)+'</span><span class="sum">'+money(q.obstacles_cost)+'</span><span data-arrow="obs">▶</span></div><div class="box-body" id="box-obs">'+q.obstacles.map(o=>'<div class="obs-row"><span>'+esc(o.label)+' × '+o.count+'</span><span><b>'+money(o.total)+'</b></span></div>').join('')+'</div></div>';
let riv='',ovr='';
if((q.bridges||[]).length)riv='<div class="box"><div class="box-head" data-box="riv"><span>🌉 Мосты · '+q.bridges.length+'</span><span class="sum" id="sumRiv">'+money(q.bridges_cost)+'</span><span data-arrow="riv">▼</span></div><div class="box-body open" id="box-riv">'+q.bridges.map((b,i)=>structRow(b,i)).join('')+'</div></div>';
if((q.overpasses||[]).length)ovr='<div class="box"><div class="box-head" data-box="ovr"><span>🚧 Путепроводы · '+q.overpasses.length+'</span><span class="sum" id="sumOvr">'+money(q.overpasses_cost)+'</span><span data-arrow="ovr">▼</span></div><div class="box-body open" id="box-ovr">'+q.overpasses.map((b,i)=>structRow(b,i+q.bridges.length)).join('')+'</div></div>';
let nl=q.nodes_new>0?'<div class="obs-row" style="margin-top:8px"><span>🔩 Узлы × '+q.nodes_new+'</span><span><b>'+money(q.node_cost)+'</b></span></div>':'';
const blocked=q.error?'<div class="alert">⛔ '+esc(q.error)+'</div>':'';
const body='<p style="margin:0 0 6px;color:var(--ink);font-weight:700">'+esc(epN(q.endpoints.from))+' → '+esc(epN(q.endpoints.to))+'</p><div style="font:11px var(--f-mono);color:var(--mut);margin-bottom:10px">'+q.length.toFixed(1)+' м · изгибов '+reqBody.points.length+' · потолок <span id="spdPrev">'+q.speed_preview+'</span> км/ч</div>'+head+riv+ovr+obs+nl+blocked
+'<div class="box"><div class="box-head" data-box="blk"><span>🚦 Блок-участки</span><span class="sum" id="sumBlk">'+money(q.blocks_cost||10)+'</span><span data-arrow="blk">▼</span></div><div class="box-body open" id="box-blk">'+blockLenControl(q.block_len||constants.block_len_default||750)+'</div></div>'
+'<div class="note">Без электрификации ('+EDGE_SPEED_BASE+' км/ч). ⚡ до '+EDGE_SPEED_ELECTRIC+' при подстанции в 8 км; двухпутка при ПЧ в 10 км. Мост режет скорость только в пролёте. Однопутка = 1 блок; длина блока задаёт пропускную способность.</div><div class="total">Итого: <span id="edgeTotal">'+money(q.final_cost)+'</span></div><div id="edgePoor"></div>';
const can=!q.error;let curBl=q.block_len||constants.block_len_default||750;
modal({title:'🛤 Смета перегона',body,wide:true,ok:'Построить',disabled:!can||me.money<q.final_cost,onClose:()=>{edgeQuote=null;renderPreview();},
onMount:root=>{root.querySelectorAll('[data-box]').forEach(h=>h.onclick=()=>{const b=el('box-'+h.dataset.box),ar=root.querySelector('[data-arrow="'+h.dataset.box+'"]');b.classList.toggle('open');ar.textContent=b.classList.contains('open')?'▼':'▶';SFX.play('click');});
const recalc=()=>{let rc=0,oc=0;(q.bridges||[]).forEach(b=>rc+=bridgeCost('river',b.type));(q.overpasses||[]).forEach(b=>oc+=bridgeCost('rail',b.type));
const blkC=constants.cost_block||10;q.final_cost=q.cost_discount+q.obstacles_cost+rc+oc+q.node_cost+blkC;
const sr=el('sumRiv'),so=el('sumOvr');if(sr)sr.textContent=money(rc);if(so)so.textContent=money(oc);
const sb=el('sumBlk');if(sb)sb.textContent=money(blkC);
el('edgeTotal').textContent=money(q.final_cost);const poor=me.money<q.final_cost;
el('edgePoor').innerHTML=poor?'<div class="alert">💸 Не хватает: '+money(me.money)+'</div>':'';el('actionOk').disabled=!can||poor;renderPreview();};
root.querySelectorAll('[data-br]').forEach(sel=>sel.onchange=()=>{const br=all[+sel.dataset.br];if(!br)return;br.type=sel.value;const sw=root.querySelector('[data-sw="'+sel.dataset.br+'"]');if(sw)sw.style.background=MAT_COLOR[bridgeMat(br.type)];SFX.play('snap');recalc();});
wireBlockLen(root,q.length,false,v=>{curBl=v;reqBody.block_len=v;recalc();});
recalc();},
onOk:async()=>{loading('Строим перегон…');
try{const sel=all.map(b=>({kind:b.kind,lat:b.lat,lng:b.lng,type:b.type}));
const r=await api('build_edge','POST',{...reqBody,double:false,bridges:sel,block_len:curBl});unloading();SFX.play('build');
let msg='🛤 Строится · '+money(r.cost);if(r.nodes_new)msg+=' · 🔩 '+r.nodes_new;if(r.bridges)msg+=' · 🌉 '+r.bridges;if(r.overpasses)msg+=' · 🚧 '+r.overpasses;
toast(msg);await loadWorld();await refreshAll();
const cont=ep(r.to);if(cont){edgeDraft.start={id:cont.id,lat:cont.lat,lng:cont.lng,name:cont.name,kind:cont.kind};edgeDraft.points=[];renderDraft();updateDock();}else setMode('idle');
}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
async function openSplitFlow(e,ll){if(e.is_building){toast('Перегон строится');return;}if(e.upgrading){toast('Перегон улучшается');return;}if(e.frozen){toast('Перегон на продаже');return;}
if(me&&e.owner_id!==me.id&&!e.can_use){toast('🔒 Врезка запрещена');return;}
loading('Проверяем местность…');let r;
try{r=await api('check_osm_station_buildings','POST',{lat:ll.lat,lng:ll.lng});}catch(err){unloading();toast('Ошибка: '+err.message);return;}
unloading();if(r.forbidden_zone){toast('⛔ '+r.forbidden_zone);return;}
pendingSplit={lat:ll.lat,lng:ll.lng};renderPreview();
let cost=pricing.station;if(r.discount>0)cost=Math.ceil(cost*.9);const poor=me.money<cost;
modal({title:'📍 Врезка станции',body:'<p style="margin:0 0 8px">'+esc(e.from_name)+' ↔ '+esc(e.to_name)+'</p><p style="margin:0 0 8px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60">'+previewHtml(r,pricing.station)+'<div class="note">Перегон разрежется на два с сохранением путей, электрификации, мостов и длины блока. Отступ ≥60 м.</div>'+(poor?'<div class="alert">💸 Не хватает</div>':''),
input:'Станция '+(world.stations.filter(s=>me&&s.owner_id===me.id).length+1),ok:'Врезать',disabled:poor,onClose:()=>{pendingSplit=null;renderPreview();},
onOk:async name=>{loading('Врезаем…');
try{const rr=await api('split_edge','POST',{edge_id:e.id,lat:ll.lat,lng:ll.lng,name});unloading();SFX.play('build');
toast('📍 «'+name+'» · '+Math.round(rr.len_a)+'+'+Math.round(rr.len_b)+' м · 🏢 '+rr.buildings+' → '+rr.income_percent+'%');
setMode('idle');await loadWorld();await refreshAll();const s=st(rr.station_id);if(s)flyToDetail(s.lat,s.lng);
}catch(err){unloading();toast('Ошибка: '+err.message);}}});}
function nearestAllowedStation(ll){let best=null,bd=Infinity;world.stations.filter(s=>!s.is_building&&me&&(s.owner_id===me.id||s.can_connect)).forEach(s=>{const d=hav(ll.lat,ll.lng,s.lat,s.lng);if(d<bd){bd=d;best=s;}});return{best,bd};}
function buildDepotFlow(ll){if(!me)return;const{best,bd}=nearestAllowedStation(ll);
if(!best){toast('Рядом нет станции');return;}if(bd>DEPOT_RADIUS){toast('Далеко: '+Math.round(bd)+' м');return;}
const cost=pricing.depot,poor=me.money<cost;
modal({title:'🏭 Новое депо',body:'<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60"><div class="dock-stats" style="margin-top:12px"><span class="dstat">станция <b>'+esc(best.name)+'</b></span><span class="dstat good">'+Math.round(bd)+' м</span><span class="dstat">мест 3</span><span class="dstat">2 дня</span></div><div style="margin-top:10px;font:800 17px var(--f-mono)">'+money(cost)+'</div>'+(poor?'<div class="alert">💸 Не хватает</div>':''),
input:'Депо «'+best.name+'»',ok:'Построить',disabled:poor,
onOk:async name=>{loading('Строим депо…');try{const r=await api('build_depot','POST',{name,lat:ll.lat,lng:ll.lng,station_id:best.id});unloading();SFX.play('build');toast('🏭 «'+name+'» у «'+r.station+'»');setMode('idle');await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function buildSubstationFlow(ll){if(!me)return;const{best,bd}=nearestAllowedStation(ll);
if(!best){toast('Рядом нет станции');return;}if(bd>SUBSTATION_RADIUS){toast('Далеко: '+Math.round(bd)+' м');return;}
const cost=pricing.substation,poor=me.money<cost;let cover=0;
world.edges.forEach(e=>{if(!e.electric||e.electric_legacy||e.is_building||e.owner_id!==me.id)return;const l2=edgeLL(e);if(!l2)return;if(distToPolylineM(ll.lat,ll.lng,l2)<=(constants.substation_range_m||SUBSTATION_RANGE_M))cover++;});
modal({title:'⚡ Новая подстанция',body:'<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60"><div class="dock-stats" style="margin-top:12px"><span class="dstat">станция <b>'+esc(best.name)+'</b></span><span class="dstat good">'+Math.round(bd)+' м</span><span class="dstat">8 км</span><span class="dstat">2 дня</span></div>'+(cover>0?'<div class="note">Запитает перегонов: <b>'+cover+'</b>.</div>':'')+'<div style="margin-top:10px;font:800 17px var(--f-mono)">'+money(cost)+'</div>'+(poor?'<div class="alert">💸 Не хватает</div>':''),
input:'Подстанция «'+best.name+'»',ok:'Построить',disabled:poor,
onOk:async name=>{loading('Строим…');try{const r=await api('build_substation','POST',{name,lat:ll.lat,lng:ll.lng,station_id:best.id});unloading();SFX.play('zap');toast('⚡ «'+name+'» · 8 км');setMode('idle');await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function buildTrackMaintFlow(ll){if(!me)return;const{best,bd}=nearestAllowedStation(ll);
if(!best){toast('Рядом нет станции');return;}if(bd>TRACK_MAINT_RADIUS){toast('Далеко: '+Math.round(bd)+' м');return;}
const cost=pricing.track_maint,poor=me.money<cost;let cover=0;
world.edges.forEach(e=>{if(!e.double||e.double_legacy||e.is_building||e.owner_id!==me.id)return;const l2=edgeLL(e);if(!l2)return;if(distToPolylineM(ll.lat,ll.lng,l2)<=(constants.track_maint_range_m||TRACK_MAINT_RANGE_M))cover++;});
modal({title:'⚒ Новая путевая часть',body:'<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60"><div class="dock-stats" style="margin-top:12px"><span class="dstat">станция <b>'+esc(best.name)+'</b></span><span class="dstat good">'+Math.round(bd)+' м</span><span class="dstat">10 км</span><span class="dstat">2 дня</span></div>'+(cover>0?'<div class="note">Двухпуток в зоне: <b>'+cover+'</b>.</div>':'')+'<div style="margin-top:10px;font:800 17px var(--f-mono)">'+money(cost)+'</div>'+(poor?'<div class="alert">💸 Не хватает</div>':''),
input:'Путевая часть «'+best.name+'»',ok:'Построить',disabled:poor,
onOk:async name=>{loading('Строим…');try{const r=await api('build_track_maint','POST',{name,lat:ll.lat,lng:ll.lng,station_id:best.id});unloading();SFX.play('zap');toast('⚒ «'+name+'» · 10 км');setMode('idle');await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function modelStub(m){const img=tImg(m.key);if(img)return '<img class="mimg" src="'+esc(img)+'" alt="" style="object-fit:contain;background:transparent;border-color:'+(m.traction==='electric'?'#ffb02e':'#7fa6d8')+'">';
return '<span class="mimg" style="color:'+(m.traction==='electric'?'#ffb02e':'#7fa6d8')+'">'+(m.traction==='electric'?'⚡':'🚂')+'</span>';}
function buyTrain(depotId){const d=idxDp.get(depotId);if(!d)return;if(d.free_slots<=0){toast('В депо нет мест');return;}
const elecNear=world.edges.some(e=>e.electric&&(e.powered||e.electric_legacy)&&!e.is_building&&(e.from===d.station_id||e.to===d.station_id));
const prs=myPresets();let selKey=tmMap.diesel_line?'diesel_line':trainModels[0].key;let selPreset='';
const grp=(tr,title)=>{const list=trainModels.filter(m=>m.traction===tr);if(!list.length)return '';
return '<div class="vcap">'+title+'</div>'+list.map(m=>'<label class="choice"><input type="radio" name="tmodel" value="'+esc(m.key)+'" '+(m.key===selKey?'checked':'')+'><span class="mrow">'+modelStub(m)+'<span><span class="cn">'+esc(m.label)+' · '+money(m.cost)+'</span><span class="cs">×'+m.power.toFixed(2)+' · до '+m.max_speed+' км/ч · '+(tr==='electric'?(elecNear?'180 при запитанной сети':'без сети не поедет'):'любые пути')+'</span></span></span></label>').join('');};
const presetBlock=prs.length?('<div class="vcap">📋 Пресеты</div>'+prs.map(p=>'<label class="choice"><input type="radio" name="tpreset" value="'+esc(p.id)+'"><span><span class="cn">'+esc(p.name)+' · '+money(presetPrice(p))+'</span><span class="cs">'+esc(tLabel(p.model))+' · '+presetConsist(p)+'</span></span></label>').join('')+'<label class="choice"><input type="radio" name="tpreset" value="" checked><span><span class="cn">Без пресета</span></span></label>'):'';
modal({title:'🚆 Заказ поезда',wide:true,body:'<p style="margin:0 0 10px">Депо «'+esc(d.name)+'» · свободно <b>'+d.free_slots+'/'+d.capacity+'</b></p><p style="margin:0 0 8px">Название:</p><input type="text" id="modalInput" class="input" maxlength="40">'+presetBlock+'<div class="vcap" style="margin-top:12px">Модель</div>'+grp('diesel','🚂 Тепловозы')+grp('electric','⚡ Электропоезда')+'<div class="total" style="margin-top:14px">К оплате: <span id="trPrice">—</span></div><div id="trPoor"></div>',
input:'Поезд '+(world.trains.filter(t=>me&&t.owner_id===me.id).length+1),ok:'Заказать',
onMount:root=>{const upd=()=>{const pr=root.querySelector('input[name=tpreset]:checked');selPreset=pr?pr.value:'';
const P=selPreset?prs.find(x=>x.id===selPreset):null;if(P)selKey=P.model;else{const mr=root.querySelector('input[name=tmodel]:checked');selKey=mr?mr.value:selKey;}
let cost=tCost(selKey),wag=0;if(P){wagonTypes.forEach(w=>{const n=P.wagons[w.key]||0;cost+=n*w.cost;wag+=n;});}
el('trPrice').textContent=money(cost)+(wag?' · '+wag+' ваг.':'');const poor=me.money<cost;
el('trPoor').innerHTML=poor?'<div class="alert">💸 Не хватает: '+money(me.money)+'</div>':'';el('actionOk').disabled=poor;};
root.querySelectorAll('input[name=tpreset]').forEach(r=>r.onchange=()=>{SFX.play('snap');upd();});
root.querySelectorAll('input[name=tmodel]').forEach(r=>r.onchange=()=>{const z=root.querySelector('input[name=tpreset][value=""]');if(z)z.checked=true;SFX.play('snap');upd();});upd();},
onOk:async name=>{loading('Заказываем…');try{const body={depot_id:depotId,name,model:selKey};if(selPreset)body.preset_id=selPreset;
const r=await api('buy_train','POST',body);unloading();SFX.play('build');toast(tIcon(r.model)+' «'+name+'» · '+(r.model_label||tLabel(selKey))+(r.wagons?' · '+r.wagons+' ваг.':''));await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function presetSaveFlow(t){if(myPresets().length>=presetsMax()){toast('Максимум '+presetsMax()+' пресетов');return;}
modal({title:'💾 Сохранить пресет',body:'<p style="margin:0 0 8px">«'+esc(t.name)+'» · '+tIcon(t.model)+' '+esc(tLabel(t.model))+' · '+consistShort(t)+'</p><p style="margin:0 0 6px">Название:</p><input type="text" id="modalInput" class="input" maxlength="40">',input:(t.name+' пресет').slice(0,40),ok:'Сохранить',
onOk:async n=>{const r=await act('save_preset',{from_train_id:t.id,name:n},'Сохраняем…','click');if(r)toast('📋 «'+n+'» · '+r.total+' ваг.');}});}
function presetApplyFlow(t){const list=myPresets();if(!list.length){toast('Нет пресетов');return;}
let refund=0;trainWagList(t).forEach(w=>refund+=wCost(w));refund=Math.ceil(refund*REFUND);
choiceModal({title:'📋 Пересобрать «'+t.name+'»',items:list.map(p=>{let ch=0;wagonTypes.forEach(w=>ch+=(p.wagons[w.key]||0)*w.cost);const net=ch-refund;
return{value:p.id,label:p.name,sub:tLabel(p.model)+' · '+presetConsist(p)+' · '+(net>0?'доплата '+money(net):'возврат '+money(-net))};}),ok:'Далее',
onOk:pid=>{const p=list.find(x=>x.id===pid);if(!p)return;
modal({title:'📋 Подтверждение',ok:'Пересобрать',body:'<p style="margin:0 0 8px">«'+esc(t.name)+'» станет: '+tIcon(p.model)+' '+esc(tLabel(p.model))+' · '+presetConsist(p)+'</p><div class="hint">Старые вагоны −50 % возврата, новые по полной цене. Модель, маршрут и здоровье сохраняются.</div>',
onOk:async()=>{const r=await act('apply_preset',{train_id:t.id,preset_id:p.id},'Пересобираем…','build');if(r)toast('📋 '+r.wagons+' ваг. · ёмк. '+r.capacity+' · '+(r.net>0?'−'+money(r.net):'+'+money(-r.net)));}});}});}
function presetBuyFlow(p){const ds=mineArr(world.depots).filter(d=>!d.is_building&&!d.frozen&&d.free_slots>0);
if(!ds.length){toast('Нет депо со свободными местами');return;}
choiceModal({title:'🛒 «'+p.name+'» → в депо',items:ds.map(d=>({value:d.id,label:d.name,sub:(d.trains_count||0)+'/'+d.capacity+' · у «'+((st(d.station_id)||{}).name||'—')+'»'})),ok:'Далее',
onOk:did=>{const cost=presetPrice(p);
modal({title:'🛒 Заказ по пресету',ok:'Заказать',disabled:me.money<cost,input:(p.name+' '+(world.trains.filter(t=>me&&t.owner_id===me.id).length+1)).slice(0,40),
body:'<p style="margin:0 0 8px">'+tIcon(p.model)+' '+esc(tLabel(p.model))+' · '+presetConsist(p)+'</p><p style="margin:0 0 6px">Название:</p><input type="text" id="modalInput" class="input" maxlength="40"><div class="total">'+money(cost)+'</div>'+(me.money<cost?'<div class="alert">💸 Не хватает</div>':''),
onOk:async n=>{const r=await act('buy_train',{depot_id:did,name:n,preset_id:p.id},'Заказываем…','build');if(r)toast(tIcon(r.model)+' «'+n+'» · '+r.wagons+' ваг.');}});}});}
function presetCreateFlow(){if(myPresets().length>=presetsMax()){toast('Максимум '+presetsMax()+' пресетов');return;}
let selKey=tmMap.diesel_line?'diesel_line':trainModels[0].key;const cnt={passenger:0,baggage:0,mail:0};
const grp=(tr,title)=>{const ms=trainModels.filter(m=>m.traction===tr);if(!ms.length)return '';
return '<div class="vcap">'+title+'</div>'+ms.map(m=>'<label class="choice"><input type="radio" name="pmodel" value="'+esc(m.key)+'" '+(m.key===selKey?'checked':'')+'><span class="mrow">'+modelStub(m)+'<span><span class="cn">'+esc(m.label)+' · '+money(m.cost)+'</span><span class="cs">×'+m.power.toFixed(2)+' · до '+m.max_speed+'</span></span></span></label>').join('');};
modal({title:'➕ Новый пресет',wide:true,ok:'Сохранить',
body:'<p style="margin:0 0 6px">Название:</p><input type="text" id="pName" class="input" maxlength="40" value="Пресет '+(myPresets().length+1)+'">'+grp('diesel','🚂 Тепловозы')+grp('electric','⚡ Электропоезда')+'<div class="vcap">Вагоны (макс '+maxWagons()+')</div>'+wagonTypes.map(w=>'<div class="vrow"><label style="flex:1">'+(w.freight?'🧳':'🚃')+' '+esc(w.label)+' · +'+w.cap+' · '+money(w.cost)+'</label><input type="number" class="input" style="width:74px" min="0" max="'+maxWagons()+'" value="0" data-wc="'+w.key+'"></div>').join('')+'<div id="pCalc" class="total"></div><div id="pErr"></div>',
onMount:root=>{const calc=()=>{const mr=root.querySelector('input[name=pmodel]:checked');selKey=mr?mr.value:selKey;let tot=0;
root.querySelectorAll('[data-wc]').forEach(i=>{cnt[i.dataset.wc]=clamp(parseInt(i.value)||0,0,maxWagons());tot+=cnt[i.dataset.wc];});
let price=tCost(selKey);wagonTypes.forEach(w=>price+=cnt[w.key]*w.cost);
el('pCalc').textContent='Итого: '+money(price)+' · вагонов '+tot;const bad=tot>maxWagons();
el('pErr').innerHTML=bad?'<div class="alert">Максимум '+maxWagons()+' вагонов</div>':'';el('actionOk').disabled=bad;};
root.querySelectorAll('[data-wc]').forEach(i=>i.oninput=calc);root.querySelectorAll('input[name=pmodel]').forEach(r=>r.onchange=()=>{SFX.play('snap');calc();});calc();},
onOk:async()=>{const n=((el('pName')?el('pName').value:'')||'').trim()||('Пресет '+(myPresets().length+1));
const r=await act('save_preset',{name:n,model:selKey,wagons:{passenger:cnt.passenger,baggage:cnt.baggage,mail:cnt.mail}},'Сохраняем…','click');if(r)toast('📋 «'+n+'» · '+r.total+' ваг.');}});}
function presetManager(){const list=myPresets();
let html='<div class="hint">Пресет = модель + количества вагонов. Личный инструмент: не продаётся и не изымается. '+list.length+'/'+presetsMax()+'.</div>';
list.forEach(p=>{const img=tImg(p.model);
html+='<div class="item"><div class="item-top">'+(img?'<img class="timg" src="'+esc(img)+'" alt="">':'')+'<span class="item-name">'+tIcon(p.model)+' '+esc(p.name)+'</span><span class="item-val">'+money(presetPrice(p))+'</span></div><div class="item-sub"><span>'+esc(tLabel(p.model))+'</span><span>'+presetConsist(p)+'</span></div><div class="dock-actions" style="margin-top:6px"><button class="btn btn-sm" data-pbuy="'+esc(p.id)+'">🛒 В депо</button><button class="btn btn-sm" data-pren="'+esc(p.id)+'">✏️</button><button class="btn btn-sm btn-danger" data-pdel="'+esc(p.id)+'">🗑</button></div></div>';});
html+='<button class="btn btn-violet" id="pNew" style="margin-top:10px">➕ Новый пресет</button>';
modal({title:'📋 Пресеты составов',wide:true,body:html,ok:'Закрыть',onMount:root=>{
root.querySelector('#pNew').onclick=()=>{closeActionModal();presetCreateFlow();};
root.querySelectorAll('[data-pbuy]').forEach(b=>b.onclick=()=>{const p=list.find(x=>x.id===b.dataset.pbuy);closeActionModal();presetBuyFlow(p);});
root.querySelectorAll('[data-pren]').forEach(b=>b.onclick=()=>{const p=list.find(x=>x.id===b.dataset.pren);closeActionModal();renameFlow('✏️ Пресет','rename_preset',{preset_id:p.id},p.name);});
root.querySelectorAll('[data-pdel]').forEach(b=>b.onclick=()=>{const p=list.find(x=>x.id===b.dataset.pdel);closeActionModal();deleteFlow('🗑 Пресет','Удалить «'+esc(p.name)+'»? Поезда не пострадают.','delete_preset',{preset_id:p.id});});}});}
function duplicateTrainFlow(t){const d=idxDp.get(t.depot_id);if(!d){toast('Поезд без депо');return;}if(d.free_slots<=0){toast('В депо нет мест');return;}
const cost=trainReplaceCost(t),poor=me.money<cost,sp=spriteImg(t.model,t.status,'');
modal({title:'📋 Дублирование состава',body:(sp?'<div class="ctx-sprite">'+sp+'</div>':'')+'<p style="margin:0 0 8px">Копия «'+esc(t.name)+'» прибудет в «'+esc(d.name)+'» через 1 день.</p><div class="dock-stats" style="margin:10px 0"><span class="dstat">'+tIcon(t.model)+' <b>'+esc(tLabel(t.model))+'</b></span><span class="dstat">'+consistShort(t)+'</span><span class="dstat">ёмк. <b>'+trainCapacity(t)+'</b></span></div><p style="margin:0 0 6px">Название:</p><input type="text" id="modalInput" class="input" maxlength="40"><div class="total">'+money(cost)+'</div><div class="note">Маршрут не копируется.</div>'+(poor?'<div class="alert">💸 Не хватает</div>':''),
input:(t.name+' (копия)').slice(0,40),ok:'Дублировать',disabled:poor,
onOk:async name=>{const r=await act('duplicate_train',{train_id:t.id,name},'Дублируем…','build');if(r)toast('📋 «'+r.name+'» · '+r.wagons+' ваг. · '+money(r.cost));}});}
function wagonFlow(t,dir){const list=trainWagList(t);const cnt={};list.forEach(w=>cnt[w]=(cnt[w]||0)+1);const phys=list.length;
const r=idxRt.get(t.route_id);const wLim=r?(r.wagon_limit??maxWagons()):maxWagons();
const maxAdd=Math.min(maxWagons()-phys,r?Math.max(0,wLim-phys):maxWagons());
if(dir>0&&maxAdd<=0){toast('⛔ Лимит вагонов');return;}
const opts=wagonTypes.filter(w=>dir>0||(cnt[w.key]||0)>0);if(!opts.length){toast(dir>0?'Нет типов':'Нет вагонов');return;}
let selType=opts[0].key;
modal({title:dir>0?'🚃 Прицепить':'🚃 Отцепить',input:'1',ok:dir>0?'Прицепить':'Отцепить',
body:'<p style="margin:0 0 8px">«'+esc(t.name)+'» · '+consistShort(t)+' · ёмк. '+trainCapacity(t)+' · '+phys+'/'+maxWagons()+'</p><div class="vcap">Тип</div>'+opts.map((w,i)=>'<label class="choice"><input type="radio" name="wtype" value="'+esc(w.key)+'" '+(i===0?'checked':'')+'><span><span class="cn">'+(w.freight?'🧳':'🚃')+' '+esc(w.label)+' · +'+w.cap+' · '+money(w.cost)+'</span><span class="cs">в составе: '+(cnt[w.key]||0)+'</span></span></label>').join('')+'<p style="margin:10px 0 6px">Количество (макс '+(dir>0?maxAdd:'по наличию')+'):</p><input type="number" id="modalInput" class="input" min="1" max="'+(dir>0?maxAdd:99)+'" value="1" style="font:700 15px var(--f-mono)"><div id="wgCalc" style="margin-top:10px;font:800 15px var(--f-mono)"></div>',
onMount:root=>{const inp=el('modalInput');
const calc=()=>{const rr=root.querySelector('input[name=wtype]:checked');selType=rr?rr.value:selType;const have=cnt[selType]||0;const maxN=dir>0?maxAdd:have;
const nn=clamp(Math.max(1,parseInt(inp.value)||1),1,Math.max(1,maxN));
if(dir>0){const c=nn*wCost(selType);el('wgCalc').textContent='Итого: '+money(c)+' · '+nn+' шт · ёмк. '+(trainCapacity(t)+nn*wCap(selType));el('actionOk').disabled=me.money<c||maxN<=0;}
else{el('wgCalc').textContent='Возврат: '+money(Math.ceil(nn*wCost(selType)*REFUND))+' · '+nn+' шт';el('actionOk').disabled=have<=0;}};
inp.oninput=calc;root.querySelectorAll('input[name=wtype]').forEach(x=>x.onchange=()=>{SFX.play('snap');calc();});calc();},
onOk:async v=>{const have=cnt[selType]||0;const maxN=dir>0?maxAdd:have;if(maxN<=0){toast('Нельзя');return;}
const n=clamp(Math.max(1,parseInt(v)||1),1,maxN);
loading(dir>0?'Прицепляем…':'Отцепляем…');
try{const rr=await api(dir>0?'attach_wagon':'detach_wagon','POST',{train_id:t.id,type:selType,count:n});unloading();SFX.play(dir>0?'build':'cash');
toast(dir>0?'🚃 +'+n+' · ёмк. '+rr.capacity+' · '+money(rr.cost):'🚃 −'+(rr.removed??n)+' · возврат '+money(rr.refund));
await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function assignTrainFlow(t){const d=idxDp.get(t.depot_id);if(!d){toast('Поезд без депо');return;}const cap=trainCapacity(t);
const items=world.routes.filter(r=>me&&r.owner_id===me.id).map(r=>{const onRoute=(r.stations||[]).includes(d.station_id);
const others=world.trains.filter(x=>x.route_id===r.id&&x.id!==t.id&&x.status!=='delivering'&&x.status!=='transit').reduce((a,x)=>a+trainCapacity(x),0);
const freeCap=(r.slots||0)-others;const wLim=r.wagon_limit??99;const tooMany=trainWagCount(t)>wLim;const noPower=tTraction(t.model)==='electric'&&!r.fully_powered;
const dis=!onRoute||r.status==='broken'||freeCap<cap||tooMany||noPower;
const why=!onRoute?'депо не на маршруте':r.status==='broken'?'разорван':tooMany?('вагонов '+trainWagCount(t)+' > '+wLim):noPower?'⚡ не запитан':freeCap<cap?('ёмкости '+freeCap+'<'+cap):('свободно '+freeCap+' · '+r.km+' км · '+money(r.income)+'/д');
return{value:r.id,label:r.name,sub:why,disabled:dis};});
if(t.route_id)items.unshift({value:'',label:'⏹ Снять с маршрута',sub:'отстой'});
choiceModal({title:'🎫 «'+t.name+'» · ёмк. '+cap,items,ok:'Назначить',empty:'Нет маршрутов через станцию депо',
onOk:async rid=>{loading('Назначаем…');
try{const r=await api('assign_train','POST',{train_id:t.id,route_id:rid});unloading();
if(rid){SFX.play('whistle');toast('🎫 линия '+r.line_speed+' · средняя '+r.speed+' · '+money(r.income)+'/д');}else{SFX.play('click');toast('Снят с маршрута');}
await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function moveTrainFlow(t){const items=world.depots.filter(d=>me&&d.owner_id===me.id&&d.id!==t.depot_id&&!d.is_building&&!d.frozen).map(d=>({value:d.id,label:d.name,sub:(d.trains_count||0)+'/'+d.capacity,disabled:d.free_slots<=0}));
choiceModal({title:'🔀 «'+t.name+'» в другое депо',items,ok:'Перевести',empty:'Нет других депо',
onOk:async did=>{loading('Переводим…');try{const r=await api('move_train','POST',{train_id:t.id,depot_id:did});unloading();SFX.play('click');toast('🔀 В пути');await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function repairTrainFlow(t){const d=idxDp.get(t.depot_id);const near=d&&myPos?hav(myPos.lat,myPos.lng,d.lat,d.lng)<=REPAIR_FREE_M:false;const cost=near?0:trainRepairCost(t);
modal({title:'🔧 Ремонт состава',ok:'Починить',disabled:!near&&me.money<cost,
body:'<p style="margin:0 0 8px">«'+esc(t.name)+'» · <b style="color:var(--red-t)">'+(t.health??100)+'%</b></p><div style="font:800 17px var(--f-mono)">'+(near?'бесплатно (вы у депо)':money(cost))+'</div><div class="hint">Износ 10 %/реал. день только в движении.</div>'+(!near&&me.money<cost?'<div class="alert">💸 Не хватает</div>':''),
onOk:async()=>{const r=await act('repair_train',{train_id:t.id,...(myPos?{lat:myPos.lat,lng:myPos.lng}:{})},'Ремонтируем…');if(r)toast(r.free?'🔧 Бесплатно':'🔧 '+money(r.cost));}});}
function elecFlow(e){const len=Number(e.length||0),cost=Math.ceil(len*COST_ELEC_M),range=constants.substation_range_m||SUBSTATION_RANGE_M;
const near=nearestOwnSubstationToEdge(e);const ok=!!near&&near.d<=range;
const ph=ok?'<div class="note">⚡ «'+esc(near.name)+'» — '+(near.d/1000).toFixed(1)+' км.</div>':'<div class="alert">⛔ Нет подстанции в '+(range/1000)+' км'+(near?' (ближайшая «'+esc(near.name)+'» '+(near.d/1000).toFixed(1)+' км)':'')+'. Клавиша 5.</div>';
const poor=me.money<cost;
modal({title:'⚡ Электрификация',ok:'Электрифицировать',disabled:!ok||poor,
body:'<p style="margin:0 0 8px">'+esc(e.from_name)+' ↔ '+esc(e.to_name)+' · '+Math.round(len)+' м</p><div style="font:800 17px var(--f-mono);margin-bottom:8px">'+money(cost)+'</div>'+ph+(poor?'<div class="alert">💸 Не хватает</div>':''),
onOk:async()=>{const r=await act('upgrade_edge_electric',{edge_id:e.id},'Электрифицируем…','zap');if(r)toast('⚡ '+dur(left(r.build_end)));}});}
function doubleFlow(e){const len=Number(e.length||0),range=constants.track_maint_range_m||TRACK_MAINT_RANGE_M;
const blen=e.block_len||constants.block_len_default||750;const newN=Math.max(1,Math.ceil(len/blen));const blkC=Math.max(0,newN-1)*(constants.cost_block||10);
const cost=Math.ceil(len*COST_DOUBLE_M)+blkC;
const near=nearestOwnTmToEdge(e);const legacy=!!e.double_legacy;const ok=legacy||(!!near&&near.d<=range);
const th=legacy?'<div class="note">🛤 Ранее построенные 2 пути — ПЧ не требуется.</div>':ok?'<div class="note">🛤 «'+esc(near.name)+'» — '+(near.d/1000).toFixed(1)+' км.</div>':'<div class="alert">⛔ Нет ПЧ в '+(range/1000)+' км'+(near?' (ближайшая «'+esc(near.name)+'» '+(near.d/1000).toFixed(1)+' км)':'')+'. Клавиша 6.</div>';
const poor=me.money<cost;
modal({title:'🛤 Второй путь',ok:'Построить',disabled:!ok||poor,
body:'<p style="margin:0 0 8px">'+esc(e.from_name)+' ↔ '+esc(e.to_name)+' · '+Math.round(len)+' м</p><div style="font:800 17px var(--f-mono);margin-bottom:8px">'+money(cost)+'</div><div class="dock-stats"><span class="dstat">полотно <b>'+money(Math.ceil(len*COST_DOUBLE_M))+'</b></span><span class="dstat">🚦 сигнализация <b>'+newN+' бл. · '+money(blkC)+'</b></span></div>'+th+(poor?'<div class="alert">💸 Не хватает</div>':''),
onOk:async()=>{const r=await act('upgrade_edge_double',{edge_id:e.id},'Строим…','build');if(r)toast('🛤 '+dur(left(r.build_end))+' · '+r.blocks_n+' бл.');}});}
function openBlockFlow(e){const len=Number(e.length||0);const cur=e.block_len||constants.block_len_default||750;
if(!e.double){modal({title:'🚦 Блок-участки',ok:'Понятно',showCancel:false,body:'<p style="margin:0 0 8px">'+esc(e.from_name)+' ↔ '+esc(e.to_name)+'</p><div class="note">Однопутка = <b>1 блок</b> на весь перегон: встречные и попутные ждут у входа. Чтобы дробить блоки, постройте второй путь («🛤 2-й путь» в меню перегона).</div>'});return;}
let newBl=cur;
modal({title:'🚦 Блок-участки перегона',ok:'Применить',
body:'<p style="margin:0 0 8px">'+esc(e.from_name)+' ↔ '+esc(e.to_name)+' · '+Math.round(len)+' м</p>'+blockLenControl(cur)+'<div class="total" id="blCost"></div><div id="blPoor"></div>',
onMount:root=>{const upd=v=>{newBl=v;const oldN=e.blocks_n||Math.max(1,Math.ceil(len/cur));const newN=Math.max(1,Math.ceil(len/v));const delta=newN-oldN;const cost=delta>0?delta*(constants.cost_block||10):0;
el('blCost').textContent=cost>0?('Доплата: '+money(cost)+' ('+delta+' нов. бл.)'):'Бесплатно (удлинение блоков, без возврата)';
const poor=me.money<cost;el('blPoor').innerHTML=poor?'<div class="alert">💸 Не хватает: '+money(me.money)+'</div>':'';el('actionOk').disabled=poor||newBl===cur;};
wireBlockLen(root,len,true,upd);},
onOk:async()=>{if(newBl===cur){toast('Длина не изменена');return;}
const r=await act('set_block_len',{edge_id:e.id,block_len:newBl},'Меняем блоки…','click');
if(r)toast('🚦 '+r.blocks_n+' бл. × '+r.block_len+' м'+(r.cost>0?' · '+money(r.cost):' · бесплатно'));}});}
function routePick(s){if(s.is_building){toast('Станция строится');return;}if(s.auction_status){toast('Станция на аукционе');return;}
if(me&&s.owner_id!==me.id&&!s.can_stop){toast('🔒 Остановки запрещены');return;}
const d=routeDraft;
if(d.stops.length&&d.stops[0]===s.id&&d.stops.length>=3){d.loop=true;SFX.play('route');renderDraft();quoteRoute();return;}
if(d.stops.includes(s.id)){toast('Уже в маршруте');return;}if(d.stops.length>=20){toast('Максимум 20');return;}
d.stops.push(s.id);SFX.play('snap');renderDraft();quoteRoute();}
function quoteRoute(){clearTimeout(routeQuoteTimer);
if(routeDraft.stops.length<2){routeQuote=null;routeGeom=null;renderDraft();updateDock();return;}
routeQuoteTimer=setTimeout(async()=>{try{routeQuote=await api('quote_route','POST',{stations:routeDraft.stops,loop:routeDraft.loop});
routeGeom=(routeQuote.ok&&Array.isArray(routeQuote.geometry)&&routeQuote.geometry.length>1)?routeQuote.geometry:null;}
catch(e){routeQuote={ok:false,reason:e.message};routeGeom=null;}
renderDraft();updateDock();},220);
updateDock();}
function createRouteFlow(){const q=routeQuote;if(!q||!q.ok){toast('Маршрут невалиден');return;}
if((q.slot_issues||[]).length){toast('Сначала улучшите станции');return;}
const rowsP=(q.income_by_capacity||[]).map(r=>'<tr class="'+(r.capacity===1?'hl':'')+'"><td>'+r.capacity+'</td><td class="pos">+'+fmt(r.income)+'</td><td class="neg">−'+fmt(Math.round(r.upkeep+r.fee))+'</td><td>'+fmt(Math.round(r.income-r.upkeep-r.fee))+'</td></tr>').join('');
const rowsF=(q.income_by_capacity_freight||[]).map(r=>'<tr><td>'+r.capacity+'</td><td class="pos">+'+fmt(r.income)+'</td><td class="neg">−'+fmt(Math.round(r.upkeep+r.fee))+'</td><td>'+fmt(Math.round(r.income-r.upkeep-r.fee))+'</td></tr>').join('');
const stops=(q.stops||[]).map(s=>'<div class="obs-row"><span><span style="color:'+s.color+'">■</span> '+esc(s.name)+' <small style="color:var(--mut)">кл.'+s.class+' · '+s.routes_used+'/'+s.routes_limit+' · 🚃≤'+s.wagon_limit+'</small>'+(s.upgrading?' <span class="mini" data-cd="'+s.upgrade_end+'">⬆→'+s.upgrade_class+' '+dur(left(s.upgrade_end))+'</span>':'')+'</span><span>🏢 <b>'+s.percent+'%</b></span></div>').join('');
const bns=(q.info.bottlenecks||[]).map(b=>'<div class="obs-row"><span>🌉 '+esc(b.name)+' <small style="color:var(--mut)">'+esc(b.type_label)+'</small></span><span><b>'+b.speed+'</b> · '+b.from_km+'–'+b.to_km+' км</span></div>').join('');
const owners=(q.owners||[]).map(o=>'<div class="obs-row"><span><span style="color:'+companyColor(o.player_id)+'">■</span> '+esc(companyName(o.player_id))+'</span><span>'+o.km.toFixed(2)+' км · '+Math.round(o.share*100)+'%</span></div>').join('');
modal({title:'🧭 Открытие маршрута',wide:true,ok:'Открыть',input:'Маршрут '+(world.routes.filter(r=>me&&r.owner_id===me.id).length+1),
body:'<p style="margin:0 0 10px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60"><div class="dock-stats" style="margin:12px 0"><span class="dstat good">'+(q.info.km/1000).toFixed(2)+' км</span><span class="dstat">линия '+q.info.line_speed+' · средняя '+q.info.speed+'</span><span class="dstat">ёмкость '+q.info.slots+'</span><span class="dstat">🚃 ≤'+q.info.wagon_limit+'</span></div><div class="box"><div class="box-head" data-box="stops"><span>🚉 Остановки · '+q.stops.length+'</span><span data-arrow="stops">▼</span></div><div class="box-body open" id="box-stops">'+stops+'</div></div>'+(bns?'<div class="box"><div class="box-head" data-box="bns"><span>🌉 Узкие места</span><span data-arrow="bns">▼</span></div><div class="box-body open" id="box-bns">'+bns+'</div></div>':'')+(owners?'<div class="box"><div class="box-head" data-box="own"><span>🤝 Субаренда</span><span data-arrow="own">▼</span></div><div class="box-body open" id="box-own">'+owners+'</div></div>':'')+'<div class="vcap">Пассажирская ёмкость</div><table class="tr-table"><thead><tr><th>Ёмк.</th><th>Доход</th><th>Расход</th><th>Чистыми</th></tr></thead><tbody>'+rowsP+'</tbody></table><div class="vcap">Грузовая ёмкость</div><table class="tr-table"><tbody>'+rowsF+'</tbody></table>'+(q.info.fully_powered?'':'<div class="alert">⚡ Не полностью запитан.</div>'),
onMount:root=>{root.querySelectorAll('[data-box]').forEach(h=>h.onclick=()=>{const b=el('box-'+h.dataset.box);b.classList.toggle('open');root.querySelector('[data-arrow="'+h.dataset.box+'"]').textContent=b.classList.contains('open')?'▼':'▶';});},
onOk:async name=>{loading('Открываем…');try{await api('create_route','POST',{name,stations:routeDraft.stops,loop:routeDraft.loop});unloading();SFX.play('route');toast('🧭 «'+name+'» открыт');setMode('idle');await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
async function autoRoutes(){loading('Автомаршруты…');try{const r=await api('auto_routes','POST',{});unloading();await loadWorld();await refreshAll();if(r.created){SFX.play('route');toast('✨ '+r.created+' маршрутов');}else toast('Новых автомаршрутов нет');}catch(e){unloading();toast('Ошибка: '+e.message);}}
async function starterPack(){loading('Стартовый пакет…');try{const r=await api('claim_starter_pack','POST',{});unloading();SFX.play('whistle');toast('🎁 Депо, поезд и маршрут!');await loadWorld();await refreshAll();const rt=idxRt.get(r.route_id);if(rt)zoomRoute(rt);}catch(e){unloading();toast('Ошибка: '+e.message);}}
function zoomRoute(r){const pts=r.geometry||[];if(!pts.length||!map)return;closeDrawer();map.fitBounds(L.latLngBounds(pts).pad(.25),{padding:[30,30]});if(!detailOk())map.setZoom(Math.max(map.getZoom(),14));}
function showRouteStops(r){zoomRoute(r);const stopIds=new Set(r.stations||[]);const marks=[];
(r.stations||[]).forEach(sid=>{const s=st(sid);if(!s)return;marks.push(L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:16,color:r.color,weight:3,fillColor:r.color,fillOpacity:.15,interactive:false}).addTo(previewLayer));});
(r.transit||[]).forEach(t=>{const s=st(t.id);if(!s||stopIds.has(t.id))return;marks.push(L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:11,color:'#cfe0f5',weight:2,dashArray:'3 3',fillColor:'#cfe0f5',fillOpacity:.1,interactive:false}).addTo(previewLayer));});
setTimeout(()=>marks.forEach(m=>previewLayer.removeLayer(m)),4200);toast('🚉 '+stopIds.size+' остановок');}
function showSubCoverage(b){const range=b.range_m||constants.substation_range_m||SUBSTATION_RANGE_M;const col=b.color||companyColor(b.owner_id);map.flyTo([b.lat,b.lng],Math.min(map.getZoom(),12),{duration:.6});
const c=L.circle([b.lat,b.lng],{pane:'prevP',radius:range,color:col,weight:2,fillColor:col,fillOpacity:.07,dashArray:'6 6',interactive:false}).addTo(previewLayer);setTimeout(()=>previewLayer.removeLayer(c),4500);}
function showTmCoverage(x){const range=x.range_m||constants.track_maint_range_m||TRACK_MAINT_RANGE_M;const col=x.color||companyColor(x.owner_id);map.flyTo([x.lat,x.lng],Math.min(map.getZoom(),12),{duration:.6});
const c=L.circle([x.lat,x.lng],{pane:'prevP',radius:range,color:col,weight:2,fillColor:col,fillOpacity:.07,dashArray:'6 6',interactive:false}).addTo(previewLayer);setTimeout(()=>previewLayer.removeLayer(c),4500);}
async function act(action,body,msg,snd){loading(msg||'Выполняем…');try{const r=await api(action,'POST',body);unloading();SFX.play(snd||'build');await loadWorld();await refreshAll();return r;}catch(e){unloading();toast('Ошибка: '+e.message);return null;}}
function renameFlow(title,action,body,val){modal({title,body:'<input type="text" id="modalInput" class="input" maxlength="60">',input:val,ok:'Сохранить',
onOk:async n=>{try{await api(action,'POST',{...body,name:n});await loadWorld();await refreshAll();toast('Переименовано');}catch(e){toast('Ошибка: '+e.message);}}});}
function deleteFlow(title,text,action,body){modal({title,danger:true,ok:'Снести',body:'<p style="margin:0;color:var(--red-t)">'+text+'</p>',onOk:()=>act(action,body,'Сносим…').then(r=>{if(r)toast('Снесено');})});}
function setAccess(kind,id,acc){act('set_object_access',{kind,id,access:acc},'Меняем доступ…','click').then(r=>{if(r)toast('Доступ: '+accessLabel(acc));});}
async function convertNodeFlow(n){loading('Проверяем местность…');let r;
try{r=await api('check_osm_station_buildings','POST',{lat:n.lat,lng:n.lng});}catch(e){unloading();toast('Ошибка: '+e.message);return;}
unloading();if(r.forbidden_zone){toast('⛔ '+r.forbidden_zone);return;}
let cost=pricing.station;if(r.discount>0)cost=Math.ceil(cost*.9);const poor=me.money<cost;
modal({title:'🚉 Узел → станция',ok:'Превратить',disabled:poor,input:'Станция '+(world.stations.filter(s=>me&&s.owner_id===me.id).length+1),
body:'<p style="margin:0 0 8px">«'+esc(n.name)+'» станет станцией; перегоны сохранятся.</p><p style="margin:0 0 8px">Название:</p><input type="text" id="modalInput" class="input" maxlength="60">'+previewHtml(r,pricing.station)+(poor?'<div class="alert">💸 Не хватает</div>':''),
onOk:async name=>{const rr=await act('convert_node',{node_id:n.id,name},'Строим…');if(rr)toast('🚉 «'+name+'» · 🏢 '+rr.buildings+' → '+rr.income_percent+'%');}});}
function upgradeBridgeFlow(e,br){const isRail=br.kind==='rail',cur=bridgeSpeed(br.type);
const items=bridgeTypes.filter(t=>(!t.rail_only||isRail)&&t.speed>cur).map(t=>({value:t.key,label:t.label+' · '+t.speed+' км/ч',sub:'доплата '+money(bridgeCost(br.kind,t.key)-bridgeCost(br.kind,br.type))}));
choiceModal({title:'⬆️ '+br.name,items,ok:'Заменить',empty:'Уже самый быстрый',onOk:async type=>{const r=await act('upgrade_bridge',{edge_id:e.id,bridge_id:br.id,type},'Меняем…');if(r)toast('🌉 '+r.speed+' км/ч в пролёте');}});}
const ctx=el('ctx');
function closeCtx(){ctx.classList.remove('open');}
el('ctxClose').onclick=closeCtx;
window.addEventListener('click',e=>{if(!ctx.contains(e.target))closeCtx();});
function accessWidget(kind,o){if(!me||o.owner_id!==me.id)return '';const cur=o.access||'inherit';
return '<div class="sec">Доступ для других</div><div class="acc-row">'+[['inherit','Как в компании'],['open','Открыт'],['closed','Закрыт']].map(([v,l])=>'<button data-acc="'+v+'" class="'+(cur===v?'sel':'')+'">'+l+'</button>').join('')+'</div>';}
function showCtx(x,y,title,rows,actions,extra,accKind,accObj){lastCtx={x,y};el('ctxTitle').innerHTML=title;
const b=el('ctxBody');b.innerHTML=extra||'';
rows.forEach(r=>{if(r&&r.sec){const d=document.createElement('div');d.className='sec';d.textContent=r.sec;b.appendChild(d);return;}
const d=document.createElement('div');d.className='row';d.innerHTML='<span class="k">'+esc(r[0])+'</span><span class="v">'+(r[2]?r[1]:esc(r[1]))+'</span>';b.appendChild(d);});
if(accKind&&accObj){b.insertAdjacentHTML('beforeend',accessWidget(accKind,accObj));b.querySelectorAll('[data-acc]').forEach(btn=>btn.onclick=()=>{closeCtx();setAccess(accKind,accObj.id,btn.dataset.acc);});}
const a=el('ctxActions');a.innerHTML='';
actions.forEach(it=>{const btn=document.createElement('button');btn.textContent=it.label;if(it.disabled)btn.disabled=true;if(it.danger)btn.className='dngr';btn.onclick=()=>{closeCtx();it.fn&&it.fn();};a.appendChild(btn);});
a.style.display=actions.length?'':'none';ctx.classList.add('open');
const r=ctx.getBoundingClientRect();ctx.style.left=clamp(x,8,Math.max(8,window.innerWidth-r.width-8))+'px';ctx.style.top=clamp(y,8,Math.max(8,window.innerHeight-r.height-8))+'px';}
function hbar(h){const c=h<20?'var(--red)':h<50?'var(--amber)':'var(--green)';return '<div class="hbar"><i style="width:'+h+'%;background:'+c+'"></i></div>';}
function ownerRow(o){return['Владелец','<span style="color:'+(o.color||companyColor(o.owner_id))+'">■</span> '+esc(companyName(o.owner_id)),true];}
function edgeOccupants(e){const out=[];trainMarkers.forEach(o=>{const h=o._head;if(h&&h.s.e===e.id)out.push(o);});return out;}
function ctxStation(ev,s){ev.preventDefault();ev.stopPropagation();const mine=me&&s.owner_id===me.id,h=s.health??100,cls=s.class||1;
const serving=world.routes.filter(r=>(r.stations||[]).includes(s.id));
const rows=[ownerRow(s),['Класс',cls+'/'+MAX_CLASS+' → маршрутов '+(s.routes_limit||cls)],['Маршрутов',(s.routes_used||0)+'/'+(s.routes_limit||cls)],['Лимит состава',(s.wagon_limit??wagonLimitByClass(cls))+' ваг.'],['Путей',(s.tracks??stTracks(s.id))],['Зданий',(s.buildings??0)+' → '+bonusPct(s.buildings)+'%'],['Состояние',h+'%'],['Статус',s.served?'🚉 в сети':s.passed?'⏩ проездом':'💤 простой'],['🚦 Вход',s.blocks_entry?'блокируется на стоянке (кл. ≤3)':'путевое развитие'],['Содержание',money(Math.ceil(stUpkeep(s)))+'/д']];
if(s.upgrading)rows.push(['⬆️ До кл.'+s.upgrade_class,'<span data-cd="'+s.upgrade_end+'">'+dur(left(s.upgrade_end))+'</span>',true]);
if(!mine)rows.push(['Остановки',s.can_stop?'разрешены':'🔒'],['Подключение',s.can_connect?'разрешено':'🔒']);
if(s.is_building)rows.push(['🏗','<span data-cd="'+s.build_end+'">'+dur(left(s.build_end))+'</span>',true]);
if(s.auction_status)rows.push(['Лот',s.auction_status==='manual'?'продажа':'банкрот']);
if(serving.length){rows.push({sec:'Маршруты'});serving.forEach(r=>rows.push([r.name,(r.status==='active'?'🚆':'💤')+' '+money(r.income)+'/д']));}
const acts=[];
if(mine&&!s.is_building&&!s.auction_status&&!s.upgrading){
if(h<100){const near=nearMe(s),cost=near?0:Math.ceil(stReplace(s)*REPAIR_RATIO);acts.push({label:near?'🔧 Бесплатно':'🔧 '+money(cost),disabled:!near&&me.money<cost,fn:()=>act('repair_station',{station_id:s.id,...(myPos?{lat:myPos.lat,lng:myPos.lng}:{})},'Ремонт…').then(r=>{if(r)toast(r.free?'🔧 Бесплатно':'🔧 '+money(r.cost));})});}
if(cls<MAX_CLASS)acts.push({label:'⬆️ Кл.'+(cls+1)+' ('+money(stUpgCost(cls))+')',disabled:me.money<stUpgCost(cls),fn:()=>act('upgrade_station',{station_id:s.id},'Улучшаем…').then(r=>{if(r)toast('⬆️ Кл.'+r.class+' · маршрутов '+r.routes_limit);})});
acts.push({label:'🧭 Маршрут отсюда',fn:()=>{setMode('route');routeDraft.stops=[s.id];renderDraft();quoteRoute();}});
acts.push({label:'🛤 Ветка отсюда',fn:()=>{setMode('edge');edgeDraft.start={id:s.id,lat:s.lat,lng:s.lng,name:s.name,kind:'station'};renderDraft();updateDock();}});
acts.push({label:'⚡ Подстанция рядом',fn:()=>{setMode('substation');map.panTo([s.lat,s.lng]);}});
acts.push({label:'⚒ ПЧ рядом',fn:()=>{setMode('trackmaint');map.panTo([s.lat,s.lng]);}});
acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Станция','rename_station',{station_id:s.id},s.name)});
acts.push({label:'🏷 На продажу',fn:()=>openSell([s.id],[],[],[],[],[],[])});
acts.push({label:'🗑 Снести',danger:true,fn:()=>deleteFlow('🗑 Снос','Снести «'+esc(s.name)+'»? Перегоны удалятся. Возврат '+money(Math.ceil(stReplace(s)*REFUND))+'.','delete_station',{station_id:s.id})});}
serving.forEach(r=>acts.push({label:'🧭 '+r.name,fn:()=>openRouteMenu(r,lastCtx.x,lastCtx.y)}));
showCtx(ev.clientX,ev.clientY,'<span class="cdot" style="background:'+s.color+'"></span> '+esc(s.name),rows,acts,hbar(h),'station',s);}
function ctxNode(ev,n){ev.preventDefault();ev.stopPropagation();const mine=me&&n.owner_id===me.id;
const rows=[ownerRow(n),['Перегонов',String(n.edges_count??0)],['Статус',n.served?'в сети':'💤'],['Содержание',money(ndUpkeep(n))+'/д']];
if(!mine)rows.push(['Подключение',n.can_connect?'разрешено':'🔒']);
const acts=[];
if(mine&&!n.frozen){acts.push({label:'🚉 В станцию ('+money(pricing.station)+')',disabled:me.money<pricing.station,fn:()=>convertNodeFlow(n)});
acts.push({label:'🛤 Ветка отсюда',fn:()=>{setMode('edge');edgeDraft.start={id:n.id,lat:n.lat,lng:n.lng,name:n.name,kind:'node'};renderDraft();updateDock();}});
acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Узел','rename_node',{node_id:n.id},n.name)});
acts.push({label:'🗑 Убрать',danger:true,fn:()=>deleteFlow('🗑 Узел','Убрать «'+esc(n.name)+'»? Перегоны удалятся.','delete_node',{node_id:n.id})});}
showCtx(ev.clientX,ev.clientY,'🔩 '+esc(n.name),rows,acts,'','node',n);}
function ctxSub(ev,b){ev.preventDefault();ev.stopPropagation();const mine=me&&b.owner_id===me.id,h=b.health??100,s=st(b.station_id);
const range=((b.range_m||constants.substation_range_m||SUBSTATION_RANGE_M)/1000).toFixed(0);
const dead=b.idle!==undefined?!!b.idle:(!b.powering&&!b.is_building);
const rows=[ownerRow(b),['Станция',s?s.name:'—'],['Радиус',range+' км'],['Питает',b.powering?'⚡ да':'нет · содержание ×0.5'],['Состояние',h+'%'],['Содержание',money(sbUpkeep(b))+'/д']];
if(dead)rows.push(['Вид на карте','потухшая (не используется)']);
if(b.is_building)rows.push(['🏗','<span data-cd="'+b.build_end+'">'+dur(left(b.build_end))+'</span>',true]);
const acts=[];
if(mine&&!b.is_building&&!b.auction_status){acts.push({label:'🔦 Радиус',fn:()=>showSubCoverage(b)});
if(h<100){const near=nearMe(b),cost=near?0:Math.ceil(SUBSTATION_COST*REPAIR_RATIO);acts.push({label:near?'🔧 Бесплатно':'🔧 '+money(cost),disabled:!near&&me.money<cost,fn:()=>act('repair_substation',{substation_id:b.id,...(myPos?{lat:myPos.lat,lng:myPos.lng}:{})},'Ремонт…')});}
acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Подстанция','rename_substation',{substation_id:b.id},b.name)});
acts.push({label:'🏷 На продажу',fn:()=>openSell([],[],[],[],[],[b.id],[])});
acts.push({label:'🗑 Снести',danger:true,fn:()=>modal({title:'🗑 Снос подстанции',danger:true,ok:'Снести',body:'<p style="margin:0;color:var(--red-t)">Перегоны обесточатся, электропоезда встанут. Возврат '+money(Math.ceil(SUBSTATION_COST*REFUND))+'.</p>',onOk:async()=>{const r=await act('delete_substation',{substation_id:b.id},'Сносим…');if(r)toast('🔌 обесточено: '+(r.lost_edges??0));}})});}
showCtx(ev.clientX,ev.clientY,'⚡ '+esc(b.name),rows,acts,hbar(h),'substation',b);}
function ctxTm(ev,x){ev.preventDefault();ev.stopPropagation();const mine=me&&x.owner_id===me.id,s=st(x.station_id);
const range=((x.range_m||constants.track_maint_range_m||TRACK_MAINT_RANGE_M)/1000).toFixed(0);
const dead=x.idle!==undefined?!!x.idle:(!x.serves&&!x.is_building);
const rows=[ownerRow(x),['Станция',s?s.name:'—'],['Радиус',range+' км'],['Обслуживает',x.serves?'🛤 да':'нет · содержание ×0.5'],['Содержание',money(tmUpkeep(x))+'/д']];
if(dead)rows.push(['Вид на карте','потухшая (не используется)']);
if(x.is_building)rows.push(['🏗','<span data-cd="'+x.build_end+'">'+dur(left(x.build_end))+'</span>',true]);
const acts=[];
if(mine&&!x.is_building&&!x.auction_status){acts.push({label:'🔦 Радиус',fn:()=>showTmCoverage(x)});
acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ ПЧ','rename_track_maint',{track_maint_id:x.id},x.name)});
acts.push({label:'🏷 На продажу',fn:()=>openSell([],[],[],[],[],[],[x.id])});
acts.push({label:'🗑 Ликвидировать',danger:true,fn:()=>modal({title:'🗑 Ликвидация ПЧ',danger:true,ok:'Ликвидировать',body:'<p style="margin:0;color:var(--red-t)">Построенные двухпутки сохранятся; новые потребуют другую ПЧ. Возврат '+money(Math.ceil(TRACK_MAINT_COST*REFUND))+'.</p>',onOk:async()=>{const r=await act('delete_track_maint',{track_maint_id:x.id},'Ликвидируем…');if(r)toast('⚒ возврат '+money(r.refund));}})});}
showCtx(ev.clientX,ev.clientY,'⚒ '+esc(x.name),rows,acts,null,'track_maint',x);}
function ctxEdge(ev,e,ll){ev.preventDefault();ev.stopPropagation();const mine=me&&e.owner_id===me.id,len=Number(e.length||0),cap=e.speed||EDGE_SPEED_BASE;
const actR=activeRoutesOnEdge(e.id);
const rows=[ownerRow(e),['Участок',(e.from_name||epName(e.from))+' ↔ '+(e.to_name||epName(e.to))],['Длина',len.toFixed(0)+' м'],['Путей',dblLabel(e)],['Электрификация',elecLabel(e)],['Потолок',cap+' км/ч'],['🚦 Блоки',blocksLabel(e)],['Статус',e.served?'в сети':'💤 ×0.5'],['Содержание',money(Math.ceil(edUpkeep(e)))+'/д']];
(e.bridges||[]).forEach(br=>rows.push(['🌉 '+br.name,br.speed+' км/ч в пролёте']));
const occ=edgeOccupants(e);
if(occ.length){rows.push({sec:'🚦 Занято · '+occ.length});occ.slice(0,6).forEach(o=>rows.push([tIcon(o.t.model)+' '+o.t.name,(o.phase==='blocked'?'🚦 ждёт':o.phase==='dwell'?'🛑 стоянка':'⏩ едет')]));}
if(!mine)rows.push(['Проезд',e.can_use?'разрешён (30 %)':'🔒']);
if(e.is_building)rows.push(['🏗','<span data-cd="'+e.build_end+'">'+dur(left(e.build_end))+'</span>',true]);
if(e.upgrading)rows.push(['⬆️ '+(e.upgrade_type==='double'?'2-й путь':'электрификация'),'<span data-cd="'+e.upgrade_end+'">'+dur(left(e.upgrade_end))+'</span>',true]);
if(actR.length){rows.push({sec:'Активные маршруты · '+actR.length});actR.forEach(r=>rows.push([r.name,'🚆 '+r.speed+' км/ч · '+money(r.income)+'/д']));}
const acts=[];
if(!e.is_building&&!e.frozen&&(mine||e.can_use))acts.push({label:'📍 Врезка ('+money(pricing.station)+')',disabled:me.money<pricing.station||!!e.upgrading,fn:()=>openSplitFlow(e,ll||L.latLng((ep(e.from)||{}).lat,(ep(e.from)||{}).lng))});
if(mine&&!e.is_building&&!e.auction_status){
acts.push({label:'🚦 Блок-участки…',fn:()=>openBlockFlow(e)});
if(!e.double&&!e.upgrading){const c=Math.ceil(len*COST_DOUBLE_M);const nr=nearestOwnTmToEdge(e);const ok=!!e.double_legacy||(!!nr&&nr.d<=(constants.track_maint_range_m||TRACK_MAINT_RANGE_M));
acts.push({label:'🛤 2-й путь ('+money(c)+')'+(ok?'':' · ⚠ нужна ПЧ'),disabled:me.money<c||!ok,fn:()=>doubleFlow(e)});}
if(!e.electric&&!e.upgrading)acts.push({label:'⚡ Электрифицировать ('+money(Math.ceil(len*COST_ELEC_M))+')',disabled:me.money<Math.ceil(len*COST_ELEC_M),fn:()=>elecFlow(e)});
if(e.electric&&!e.powered&&!e.electric_legacy)acts.push({label:'⚠ Обесточен — постройте подстанцию (5)',disabled:true,fn:()=>{}});
(e.bridges||[]).forEach(br=>{const cur=bridgeSpeed(br.type);if(bridgeTypes.some(t=>(!t.rail_only||br.kind==='rail')&&t.speed>cur))acts.push({label:'⬆️ '+br.name+': убрать провал '+cur,fn:()=>upgradeBridgeFlow(e,br)});});
acts.push({label:'🏷 На продажу',disabled:!!e.upgrading,fn:()=>openSell([],[e.id],[],[],[],[],[])});
acts.push({label:'🗑 Снести',danger:true,fn:()=>deleteFlow('🗑 Перегон','Маршруты разорвутся. Возврат 50 %.','delete_edge',{edge_id:e.id})});}
routesOnEdge(e.id).forEach(r=>acts.push({label:'🧭 '+r.name,fn:()=>openRouteMenu(r,lastCtx.x,lastCtx.y)}));
showCtx(ev.clientX,ev.clientY,'🛤 Перегон',rows,acts,'','edge',e);}
function ctxDepot(ev,d){ev.preventDefault();ev.stopPropagation();const mine=me&&d.owner_id===me.id,s=st(d.station_id),h=d.health??100;
const trains=world.trains.filter(t=>t.depot_id===d.id);
const rows=[ownerRow(d),['Станция',s?s.name:'—'],['Уровень',(d.level||1)+'/'+DEPOT_MAX_LVL],['Мест',(d.trains_count||0)+'/'+d.capacity],['Состояние',h+'%'],['Содержание',money(dpUpkeep(d))+'/д']];
if(d.is_building)rows.push(['🏗','<span data-cd="'+d.build_end+'">'+dur(left(d.build_end))+'</span>',true]);
if(d.upgrading)rows.push(['⬆️','<span data-cd="'+d.upgrade_end+'">'+dur(left(d.upgrade_end))+'</span>',true]);
const acts=[{label:'🚆 Открыть депо и поезда',fn:()=>openDepotModal(d)}];
if(mine&&!d.auction_status){if(!d.is_building){const mc=minTrainCost();
acts.push({label:'🚆 Купить (от '+money(mc)+')',disabled:d.free_slots<=0||me.money<mc,fn:()=>buyTrain(d.id)});
if((d.level||1)<DEPOT_MAX_LVL){const c=DEPOT_UPG_COST*(d.level||1);acts.push({label:'⬆️ Расширить ('+money(c)+')',disabled:me.money<c||d.upgrading,fn:()=>act('upgrade_depot',{depot_id:d.id},'Расширяем…').then(r=>{if(r)toast('⬆️ ур.'+r.level+' · мест '+r.capacity);})});}
if(h<100){const near=nearMe(d),cost=near?0:Math.ceil(dpReplace(d)*REPAIR_RATIO);acts.push({label:near?'🔧 Бесплатно':'🔧 '+money(cost),disabled:!near&&me.money<cost,fn:()=>act('repair_depot',{depot_id:d.id,...(myPos?{lat:myPos.lat,lng:myPos.lng}:{})},'Ремонт…')});}
acts.push({label:'🏷 На продажу',disabled:!!d.upgrading,fn:()=>openSell([],[],[],[d.id],[],[],[])});}
acts.push({label:'🗑 Снести',danger:true,fn:()=>deleteFlow('🗑 Депо','Поезда останутся без депо. Возврат '+money(Math.ceil(dpReplace(d)*REFUND))+'.','delete_depot',{depot_id:d.id})});}
showCtx(ev.clientX,ev.clientY,'🏭 '+esc(d.name),rows,acts,hbar(h),'depot',d);}
/* ---------- МОДАЛКА ДЕПО ---------- */
function openDepotModal(d){depotModalId=d.id;renderDepotModal();el('depotModal').classList.add('open');SFX.play('click');}
function closeDepotModal(){depotModalId=null;el('depotModal').classList.remove('open');}
function trainActions(t){const d=idxDp.get(t.depot_id);const items=[];
if(!t.is_building){
items.push({value:'assign',label:t.route_id?'🎫 Переназначить / снять':'🎫 Назначить на маршрут',sub:'ёмк. '+trainCapacity(t),disabled:!t.depot_id});
items.push({value:'attach',label:'🚃 Прицепить вагоны',sub:'физ. '+trainWagCount(t)+'/'+maxWagons(),disabled:trainWagCount(t)>=maxWagons()});
items.push({value:'detach',label:'🚃 Отцепить вагоны',sub:consistShort(t),disabled:trainWagCount(t)<=0});
const dc=trainReplaceCost(t);items.push({value:'dup',label:'📋 Дублировать ('+money(dc)+')',sub:'копия в это депо',disabled:!d||d.free_slots<=0||me.money<dc});
items.push({value:'psave',label:'💾 Состав в пресет',sub:consistShort(t),disabled:myPresets().length>=presetsMax()});
if(myPresets().length)items.push({value:'papply',label:'📋 Пересобрать по пресету',sub:'дельта показывается до подтверждения'});
if((t.health??100)<100){const near=d&&myPos?hav(myPos.lat,myPos.lng,d.lat,d.lng)<=REPAIR_FREE_M:false;const cost=near?0:trainRepairCost(t);
items.push({value:'repair',label:near?'🔧 Ремонт бесплатно':'🔧 Ремонт ('+money(cost)+')',sub:(t.health??100)+'%',disabled:!near&&me.money<cost});}}
items.push({value:'move',label:'🔀 В другое депо',sub:t.depot_id?'('+money(REBASE_COST)+')':'бесплатно',disabled:!!t.is_building});
items.push({value:'rename',label:'✏️ Переименовать',sub:t.name});
items.push({value:'sell',label:'🏷 На продажу',sub:'лот на рынке'});
items.push({value:'scrap',label:'🗑 Списать ('+money(Math.ceil(trainReplaceCost(t)*REFUND))+')',sub:'возврат 50 %'});
choiceModal({title:tIcon(t.model)+' '+t.name,items,ok:'Выполнить',onOk:v=>{
if(v==='assign')assignTrainFlow(t);else if(v==='attach')wagonFlow(t,1);else if(v==='detach')wagonFlow(t,-1);
else if(v==='dup')duplicateTrainFlow(t);else if(v==='psave')presetSaveFlow(t);else if(v==='papply')presetApplyFlow(t);
else if(v==='repair')repairTrainFlow(t);else if(v==='move')moveTrainFlow(t);
else if(v==='rename')renameFlow('✏️ Поезд','rename_train',{train_id:t.id},t.name);
else if(v==='sell')openSell([],[],[],[],[t.id],[],[]);
else if(v==='scrap')deleteFlow('🗑 Списание','Списать «'+esc(t.name)+'» с вагонами?','sell_train',{train_id:t.id});}});}
function renderDepotModal(){if(!depotModalId)return;const d=idxDp.get(depotModalId);
if(!d){closeDepotModal();return;}
const s=st(d.station_id),h=d.health??100,mine=me&&d.owner_id===me.id;
const trains=world.trains.filter(t=>t.depot_id===d.id);
el('depotTitle').textContent='🏭 '+d.name;
let html='<div class="dock-stats"><span class="dstat">станция <b>'+esc(s?s.name:'—')+'</b></span><span class="dstat">ур. <b>'+(d.level||1)+'/'+DEPOT_MAX_LVL+'</b></span><span class="dstat'+((d.trains_count||0)>=d.capacity?' bad':' good')+'">мест <b>'+(d.trains_count||0)+'/'+d.capacity+'</b></span><span class="dstat">содержание <b>'+money(dpUpkeep(d))+'/д</b></span></div>'+hbar(h);
if(d.is_building)html+='<div class="note">🏗 Строится · <span data-cd="'+d.build_end+'">'+dur(left(d.build_end))+'</span></div>';
if(d.upgrading)html+='<div class="note">⬆️ Расширение · <span data-cd="'+d.upgrade_end+'">'+dur(left(d.upgrade_end))+'</span></div>';
html+='<div class="dock-actions" style="margin-top:10px">';
if(mine&&!d.auction_status&&!d.is_building){const mc=minTrainCost();
html+='<button class="btn btn-primary btn-sm" data-da="buy" '+(d.free_slots<=0||me.money<mc?'disabled':'')+'>🚆 Купить (от '+money(mc)+')</button>';
if((d.level||1)<DEPOT_MAX_LVL){const c=DEPOT_UPG_COST*(d.level||1);html+='<button class="btn btn-sm" data-da="up" '+(me.money<c||d.upgrading?'disabled':'')+'>⬆️ Расширить ('+money(c)+')</button>';}
if(h<100){const near=nearMe(d),cost=near?0:Math.ceil(dpReplace(d)*REPAIR_RATIO);html+='<button class="btn btn-sm" data-da="rep" '+(!near&&me.money<cost?'disabled':'')+'>'+(near?'🔧 Бесплатно':'🔧 '+money(cost))+'</button>';}
html+='<button class="btn btn-sm" data-da="sell" '+(d.upgrading?'disabled':'')+'>🏷 На продажу</button>';}
if(mine)html+='<button class="btn btn-sm btn-danger" data-da="del">🗑 Снести</button>';
html+='<button class="btn btn-sm" data-da="map">🔍 На карте</button></div>';
html+='<div class="vcap" style="margin-top:12px">Поезда приписки · '+trains.length+'</div>';
if(!trains.length)html+='<div class="hint">В депо нет поездов. Купите первый — или закажите по пресету 📋.</div>';
trains.forEach(t=>{const r=idxRt.get(t.route_id),mk=t.model||'diesel_line',th=Number(t.health??100);
const sp=spriteImg(mk,t.status,'timg');
html+='<div class="item dep-train" data-tr="'+esc(t.id)+'" style="--rc:'+(r?r.color:'#61789a')+'"><div class="item-top">'+sp+'<span class="item-name">'+tIcon(mk)+' '+esc(t.name)+'</span><span class="pill '+(t.status==='running'?'ok':t.status==='idle'?'idle':'info')+'">'+statusRu(t.status)+'</span></div>'
+'<div class="item-sub"><span>'+esc(tLabel(mk))+' · ×'+(t.power??tPower(mk)).toFixed(2)+'</span><span>'+consistShort(t)+' · ёмк. '+trainCapacity(t)+'</span><span>'+th+'%</span><span>'+(r?esc(r.name):'не назначен')+'</span></div>'
+'<div class="bar"><i style="width:'+th+'%;background:'+(th<20?'var(--red)':th<50?'var(--amber)':'var(--green)')+'"></i></div>'
+(t.status==='running'?'<div class="item-live"><span class="sd"></span><span data-tstatus="'+esc(t.id)+'">…</span></div>':'')
+'<div class="dock-actions"><button class="btn btn-sm" data-tq="assign" '+(t.is_building||!t.depot_id?'disabled':'')+'>🎫</button><button class="btn btn-sm" data-tq="attach" '+(t.is_building||trainWagCount(t)>=maxWagons()?'disabled':'')+'>🚃+</button><button class="btn btn-sm" data-tq="detach" '+(t.is_building||trainWagCount(t)<=0?'disabled':'')+'>🚃−</button><button class="btn btn-sm" data-tq="more">⋯ ещё</button></div></div>';});
const body=el('depotBody');body.innerHTML=html;
body.querySelectorAll('[data-da]').forEach(b=>b.onclick=ev=>{ev.stopPropagation();const a=b.dataset.da;
if(a==='buy')buyTrain(d.id);
else if(a==='up')act('upgrade_depot',{depot_id:d.id},'Расширяем…').then(r=>{if(r)toast('⬆️ ур.'+r.level+' · мест '+r.capacity);});
else if(a==='rep')act('repair_depot',{depot_id:d.id,...(myPos?{lat:myPos.lat,lng:myPos.lng}:{})},'Ремонт…').then(r=>{if(r)toast(r.free?'🔧 Бесплатно':'🔧 '+money(r.cost));});
else if(a==='sell')openSell([],[],[],[d.id],[],[],[]);
else if(a==='del')deleteFlow('🗑 Депо','Снести «'+esc(d.name)+'»? Поезда останутся без депо. Возврат '+money(Math.ceil(dpReplace(d)*REFUND))+'.','delete_depot',{depot_id:d.id});
else if(a==='map'){closeDepotModal();flyToDetail(d.lat,d.lng);}});
body.querySelectorAll('[data-tr]').forEach(row=>{const t=world.trains.find(x=>x.id===row.dataset.tr);if(!t)return;
row.onclick=ev=>{if(ev.target.closest('button'))return;trainActions(t);};
row.querySelectorAll('[data-tq]').forEach(b=>b.onclick=ev=>{ev.stopPropagation();const q=b.dataset.tq;
if(q==='assign')assignTrainFlow(t);else if(q==='attach')wagonFlow(t,1);else if(q==='detach')wagonFlow(t,-1);else trainActions(t);});});
updateTrainStatuses();}
function statusRu(s){return{running:'🚆 на маршруте',idle:'💤 отстой',delivering:'🏗 поставка',transit:'🔀 в пути',orphan:'⚠️ без депо',broken:'⛔ разорван',no_power:'⛔ нет питания'}[s]||s;}
function ctxTrain(ev,t){ev.preventDefault();ev.stopPropagation();openTrainMenu(t,ev.clientX,ev.clientY);}
function openTrainMenu(t,x,y){const mine=me&&t.owner_id===me.id,d=idxDp.get(t.depot_id),r=idxRt.get(t.route_id);
const live=trainLive(trainMarkers.get(t.id));const mk=t.model||'diesel_line',h=Number(t.health??100),wagN=trainWagCount(t),parts=trainCapParts(t);
const rows=[ownerRow(t),['Модель',tIcon(mk)+' '+(t.model_label||tLabel(mk))+' · ×'+(t.power??tPower(mk)).toFixed(2)+' · макс '+(t.max_speed||tSpeed(mk))],['Состав',consistShort(t)+' · ёмк. '+trainCapacity(t)+' (пасс '+parts.p+'/груз '+parts.g+')'],['Здоровье',h+'%'],['Статус',statusRu(t.status)],['Депо',d?d.name:'—'],['Маршрут',r?r.name:'—'],['Содержание',money(trUpkeep(t))+'/д']];
if(r){rows.push(['Скорость','линия '+(r.line_speed||r.speed)+' · средняя '+r.speed]);
(r.bottlenecks||[]).slice(0,4).forEach(b=>rows.push(['⚠ '+b.name,b.type_label+' · '+b.speed+' · '+b.from_km+'–'+b.to_km+' км']));
rows.push(['Лимит','🚃 ≤ '+(r.wagon_limit??'?')]);
if(tTraction(mk)==='electric')rows.push(['Питание',r.fully_powered?'⚡ запитан':'⛔ не запитан']);}
if(live)rows.push(['Движение',live.text]);else if(t.status==='running'&&!detailOk())rows.push(['Движение','скрыто на обзоре']);
if(t.is_building)rows.push(['🏗','<span data-cd="'+t.build_end+'">'+dur(left(t.build_end))+'</span>',true]);
if(t.transit_end>srvNow())rows.push(['🔀','<span data-cd="'+t.transit_end+'">'+dur(left(t.transit_end))+'</span>',true]);
const acts=[];
if(mine&&!t.frozen){if(!t.is_building){
acts.push({label:t.route_id?'🎫 Переназначить':'🎫 Назначить',disabled:!t.depot_id,fn:()=>assignTrainFlow(t)});
acts.push({label:'🚃 Прицепить',disabled:wagN>=maxWagons(),fn:()=>wagonFlow(t,1)});
acts.push({label:'🚃 Отцепить',disabled:wagN<=0,fn:()=>wagonFlow(t,-1)});
const dc=trainReplaceCost(t);acts.push({label:'📋 Дублировать ('+money(dc)+')',disabled:!d||d.free_slots<=0||me.money<dc,fn:()=>duplicateTrainFlow(t)});
acts.push({label:'💾 В пресет',disabled:myPresets().length>=presetsMax(),fn:()=>presetSaveFlow(t)});
if(myPresets().length)acts.push({label:'📋 Пересобрать',fn:()=>presetApplyFlow(t)});
if(h<100){const near=d&&myPos?hav(myPos.lat,myPos.lng,d.lat,d.lng)<=REPAIR_FREE_M:false;const cost=near?0:trainRepairCost(t);
acts.push({label:near?'🔧 Бесплатно':'🔧 '+money(cost),disabled:!near&&me.money<cost,fn:()=>repairTrainFlow(t)});}}
acts.push({label:'🔀 В другое депо',disabled:!!t.is_building,fn:()=>moveTrainFlow(t)});
if(r)acts.push({label:'🧭 '+r.name,fn:()=>openRouteMenu(r,x,y)});
acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Поезд','rename_train',{train_id:t.id},t.name)});
acts.push({label:'🏷 На продажу',fn:()=>openSell([],[],[],[],[t.id],[],[])});
acts.push({label:'🗑 Списать ('+money(Math.ceil(trainReplaceCost(t)*REFUND))+')',danger:true,fn:()=>deleteFlow('🗑 Списание','Списать «'+esc(t.name)+'» с вагонами?','sell_train',{train_id:t.id})});}
const sp=spriteImg(mk,t.status,'');
showCtx(x,y,tIcon(mk)+' '+esc(t.name),rows,acts,(sp?'<div class="ctx-sprite">'+sp+'</div>':'')+hbar(h));}
function ctxRoute(ev,r){ev.preventDefault();ev.stopPropagation();openRouteMenu(r,ev.clientX,ev.clientY);}
function openRouteMenu(r,x,y){const mine=me&&r.owner_id===me.id,trains=world.trains.filter(t=>t.route_id===r.id),capNow=routeCapNow(r),qN=routeQueue(r);
const rows=[ownerRow(r),['Статус',r.status==='active'?'🚆 работает':r.status==='idle'?'💤 без поездов':'⛔ разорван'],['Длина',(r.km||0)+' км'],['Скорость','линия '+(r.line_speed||r.speed)+' · средняя '+(r.speed||0)],['Ёмкость',capNow+'/'+(r.slots||0)],['🚦 В очереди',qN?String(qN):'0',true],['Груз. множитель','×'+(r.dist_mult??1).toFixed(2)],['Лимит','🚃 ≤ '+(r.wagon_limit??'?')],['Питание',r.fully_powered?'⚡ запитан':'⛔ не полностью'],['2 пути',Math.round((r.double_share||0)*100)+'%'],['Электрификация',Math.round((r.elec_share||0)*100)+'%'],['Доход',money(r.income)+'/д'],['Содержание',money(Math.ceil((r.upkeep||0)+(r.fee||0)))+'/д'],['Кольцо',r.loop?'да':'нет']];
(r.bottlenecks||[]).forEach(b=>rows.push(['🌉 '+b.name,b.type_label+' · '+b.speed+' · '+b.from_km+'–'+b.to_km+' км']));
if(r.reason)rows.push(['Причина',r.reason]);
if((r.owners||[]).length){rows.push({sec:'Субаренда'});r.owners.forEach(o=>rows.push([companyName(o.player_id),o.km.toFixed(2)+' км · '+Math.round(o.share*100)+'%']));rows.push(['Платёж/д',money(r.fee)]);}
rows.push({sec:'Остановки'});
(r.stations||[]).forEach((sid,i)=>{const s=st(sid);rows.push([(i+1)+'. '+(s?s.name:'?'),s?(bonusPct(s.buildings)+'% · кл.'+s.class+(s.upgrading?' · ⬆':'')):'—']);});
if(trains.length){rows.push({sec:'Составы'});trains.forEach(t=>{const live=trainLive(trainMarkers.get(t.id));rows.push([tIcon(t.model)+' '+t.name,consistShort(t)+' · '+(t.health??100)+'%'+(live?' · '+live.text:'')]);});}
const acts=[];
if(mine){const free=world.trains.filter(t=>t.owner_id===me.id&&!t.route_id&&t.status==='idle'&&t.depot_id&&(r.stations||[]).includes((idxDp.get(t.depot_id)||{}).station_id));
acts.push({label:'🎫 Назначить поезд'+(free.length?' ('+free.length+')':''),disabled:!free.length||capNow>=(r.slots||0)||r.status==='broken',
fn:()=>choiceModal({title:'🎫 На «'+r.name+'»',items:free.map(t=>({value:t.id,label:tIcon(t.model)+' '+t.name,sub:tLabel(t.model)+' · '+consistShort(t)+' · ёмк. '+trainCapacity(t)})),ok:'Назначить',
onOk:async tid=>{const z=await act('assign_train',{train_id:tid,route_id:r.id},'Назначаем…','whistle');if(z)toast('🎫 '+money(z.income)+'/д');}})});
acts.push({label:'🚉 Остановки',disabled:r.status==='broken',fn:()=>showRouteStops(r)});
acts.push({label:'🔍 На карте',fn:()=>zoomRoute(r)});
acts.push({label:'✏️ Переименовать',fn:()=>renameFlow('✏️ Маршрут','rename_route',{route_id:r.id},r.name)});
acts.push({label:'🚫 Закрыть',danger:true,fn:()=>deleteFlow('🚫 Закрытие','Поезда уйдут в отстой.','delete_route',{route_id:r.id})});}
else{acts.push({label:'🚉 Остановки',disabled:r.status==='broken',fn:()=>showRouteStops(r)});acts.push({label:'🔍 На карте',fn:()=>zoomRoute(r)});}
trains.forEach(t=>acts.push({label:tIcon(t.model)+' '+t.name,fn:()=>openTrainMenu(t,x,y)}));
showCtx(x,y,'<span class="cdot" style="background:'+r.color+'"></span> '+esc(r.name),rows,acts);}
function ctxHq(ev,h){ev.preventDefault();ev.stopPropagation();const pid=h.owner_id,mine=me&&pid===me.id;
const c=companies.find(x=>x.id===pid)||{};const fin=ratingCache.find(x=>x.id===pid);
const km=world.edges.filter(e=>e.owner_id===pid).reduce((a,e)=>a+(Number(e.length)||0),0)/1000;
const mt=world.trains.filter(t=>t.owner_id===pid);
const rows=[['Основатель',h.founder||c.founder||'не указан'],['Основана',h.created_at?new Date(h.created_at).toLocaleDateString('ru-RU'):'—'],{sec:'Инфраструктура'},
['Станции',String(c.stations??world.stations.filter(s=>s.owner_id===pid).length)],['Узлы',String(c.nodes??world.nodes.filter(n=>n.owner_id===pid).length)],['Перегоны',(c.edges??world.edges.filter(e=>e.owner_id===pid).length)+' · '+km.toFixed(1)+' км'],['Депо',String(c.depots??world.depots.filter(d=>d.owner_id===pid).length)],['Подстанции',String(c.substations??world.substations.filter(b=>b.owner_id===pid).length)],['ПЧ',String(c.track_maint??world.track_maint.filter(x=>x.owner_id===pid).length)],['Маршруты',String(c.routes??world.routes.filter(r=>r.owner_id===pid).length)],{sec:'Парк'},['Поезда',String(mt.length)],['Вагоны',String(mt.reduce((a,t)=>a+trainWagCount(t),0))],['Ёмкость',String(mt.reduce((a,t)=>a+trainCapacity(t),0))],{sec:'Финансы'}];
if(fin){rows.push(['Баланс',money(fin.money)],['Доход/д','+'+fmt(fin.income_per_day)],['Содержание/д','−'+fmt(fin.upkeep_per_day)],['Чистыми/д',(fin.net_per_day>=0?'+':'')+fmt(fin.net_per_day)]);}
else if(mine&&lastPoll)rows.push(['Баланс',money(me.money)],['Чистыми/д',fmt(lastPoll.net_per_day)]);
const acts=[{label:'📈 Графики компании',fn:()=>openChartsFor(pid)}];
if(mine){acts.push({label:'✏️ Название и основатель',fn:editCompanyFlow});acts.push({label:'📍 Перенести штаб',fn:()=>setMode('hq')});acts.push({label:'🎨 Цвет',fn:()=>openDrawer('company')});}
showCtx(ev.clientX,ev.clientY,'<span class="cdot" style="background:'+(h.color||companyColor(pid))+'"></span> 🏛 '+esc(h.display_name||companyName(pid)),rows,acts);}
function editCompanyFlow(){const c=companies.find(x=>me&&x.id===me.id)||{};
modal({title:'✏️ Карточка компании',ok:'Сохранить',
body:'<p style="margin:0 0 6px">Вывеска (логин не меняется):</p><input type="text" id="cnInput" class="input" maxlength="40" value="'+esc(c.company_name||'')+'" placeholder="'+esc(me?me.name:'')+'"><p style="margin:12px 0 6px">Основатель (опционально):</p><input type="text" id="foInput" class="input" maxlength="40" value="'+esc(c.founder||'')+'">',
onOk:async()=>{const cn=(el('cnInput')?el('cnInput').value:'').trim(),fo=(el('foInput')?el('foInput').value:'').trim();
loading('Сохраняем…');try{const r=await api('update_company','POST',{company_name:cn,founder:fo});unloading();SFX.play('cash');if(me)me.display_name=r.display_name;el('meName').textContent=r.display_name;updPlate();toast('🏢 Обновлено');await loadWorld();await refreshAll();}catch(e){unloading();toast('Ошибка: '+e.message);}}});}
function moveHqConfirm(ll){modal({title:'🏛 Перенос штаба',ok:'Перенести',body:'<p style="margin:0 0 8px">Штаб переедет в точку клика.</p><div class="dock-stats"><span class="dstat good">бесплатно</span><span class="dstat">мгновенно</span></div>',
onOk:async()=>{const r=await act('move_hq',{lat:ll.lat,lng:ll.lng},'Переносим…','click');if(r){toast('🏛 Перенесён');setMode('idle');map.panTo([ll.lat,ll.lng]);}}});}
const DRAWERS={routes:{ico:'🧭',title:'Маршруты',sec:'dRoutes',btn:'btnRoutes'},infra:{ico:'🏗',title:'Инфраструктура',sec:'dInfra',btn:'btnInfra'},market:{ico:'🏷',title:'Рынок',sec:'dMarket',btn:'btnMarket'},companies:{ico:'👥',title:'Компании',sec:'dCompanies',btn:'btnCompanies'},settings:{ico:'⚙️',title:'Настройки',sec:'dSettings',btn:'btnSettings'},company:{ico:'🏢',title:'Компания',sec:'dCompany',btn:'btnCompany'}};
function openDrawer(name){if(drawerName===name){closeDrawer();return;}closeChat();drawerName=name;const d=DRAWERS[name];
el('drawerIco').textContent=d.ico;el('drawerTitle').textContent=d.title;
document.querySelectorAll('.dsec').forEach(s=>s.classList.remove('on'));el(d.sec).classList.add('on');
Object.values(DRAWERS).forEach(x=>el(x.btn).classList.remove('on'));el(d.btn).classList.add('on');
document.querySelectorAll('#mNav [data-mact]').forEach(x=>x.classList.toggle('on',x.dataset.mact===name));
el('drawer').classList.add('open');el('drawerBody').scrollTop=0;SFX.play('click');
if(name==='market')refreshLots();if(name==='companies'){refreshRating();refreshPlayers();}
if(name==='company')renderCompanyDrawer();if(name==='infra'){renderInfraLists();renderMyStations();}if(name==='routes')renderRoutesList();}
function closeDrawer(){drawerName=null;el('drawer').classList.remove('open');Object.values(DRAWERS).forEach(x=>el(x.btn).classList.remove('on'));document.querySelectorAll('#mNav [data-mact]').forEach(x=>x.classList.remove('on'));}
el('drawerClose').onclick=closeDrawer;
el('btnRoutes').onclick=()=>openDrawer('routes');el('btnInfra').onclick=()=>openDrawer('infra');el('btnMarket').onclick=()=>openDrawer('market');
el('btnCompanies').onclick=()=>openDrawer('companies');el('btnSettings').onclick=()=>openDrawer('settings');el('btnCompany').onclick=()=>openDrawer('company');
el('btnNewRoute').onclick=()=>{if(!me)return;closeDrawer();setMode('route');};
el('btnAuto').onclick=autoRoutes;el('btnPresets').onclick=()=>{if(me)presetManager();};
el('btnGoHq').onclick=()=>{const h=world.hqs.find(x=>me&&x.owner_id===me.id);if(!h){toast('Штаб создаётся…');return;}closeDrawer();map.flyTo([h.lat,h.lng],Math.max(map.getZoom(),14),{duration:.7});fxPulse(h.lat,h.lng,h.color||'#a97bff',false);};
el('btnEditCompany').onclick=editCompanyFlow;
function mineArr(a){return me?a.filter(x=>x.owner_id===me.id):[];}
function renderBoard(){renderMission();renderCompanyDrawer();renderRoutesList();renderInfraLists();renderMyStations();updatePrices();updateBadges();if(depotModalId)renderDepotModal();}
function updatePrices(){el('pStation').textContent=fmt(pricing.station);el('pEdge').textContent=Number(pricing.edge).toFixed(2);el('pDepot').textContent=fmt(pricing.depot);el('pSubstation').textContent=fmt(pricing.substation);el('pTrackMaint').textContent=fmt(pricing.track_maint);}
function updateBadges(){const need=repairTargets().length;
[[el('repairMark'),need],[el('mnRepair'),need]].forEach(([m,n])=>{if(!m)return;if(n>0){m.style.display='';m.textContent=n>9?'9+':n;}else m.style.display='none';});
const nl=lots.length;
[[el('lotsMark'),nl],[el('mnLots'),nl]].forEach(([m,n])=>{if(!m)return;if(n>0){m.style.display='';m.textContent=n>9?'9+':n;}else m.style.display='none';});
[[el('newsMark'),newsUnread],[el('mnNews'),newsUnread]].forEach(([m,n])=>{if(!m)return;if(n>0){m.style.display='';m.textContent=n>9?'9+':n;}else m.style.display='none';});}
function missionGone(){try{return localStorage.getItem('railMissionGone')==='1';}catch(e){return false;}}
function setMissionGone(){try{localStorage.setItem('railMissionGone','1');}catch(e){}}
function renderMission(){const card=el('missionCard');if(!me||missionGone()){card.style.display='none';return;}card.style.display='';
const ms=mineArr(world.stations),me2=mineArr(world.edges),mr=mineArr(world.routes),md=mineArr(world.depots),mt=mineArr(world.trains);
const built=ms.filter(s=>!s.is_building).length,edB=me2.filter(e=>!e.is_building).length,running=mt.filter(t=>t.status==='running').length;
const steps=[{d:built>=2,t:'Постройте <b>2 станции</b>',s:'Клавиша 1 или ветка → узел → станция'},{d:edB>=1,t:'Соедините <b>перегоном</b>',s:'Клавиша 2; клик по перегону — врезка'},{d:mr.length>=1,t:'Откройте <b>маршрут</b>',s:'Клавиша 4'},{d:md.length>=1&&mt.length>=1,t:'<b>Депо и поезд</b>',s:'Клавиша 3 · 6 моделей'},{d:running>=1,t:'<b>Назначьте поезд</b>',s:'Клик по депо → модалка → 🎫'}];
const done=steps.filter(s=>s.d).length;const canClaim=!me.starter_claimed&&built>=2&&edB>=1;
let html='<div class="mission-head"><span class="mission-title">'+(done===steps.length?'✅ Сеть работает':'Пульт запуска')+'</span><span class="mini" style="margin-left:auto">'+done+'/'+steps.length+'</span></div><div class="mission-bar"><i style="width:'+(done/steps.length*100)+'%"></i></div>';
html+=steps.map(s=>'<div class="step'+(s.d?' done':'')+'"><span class="box">'+(s.d?'✓':'')+'</span><span class="lbl">'+s.t+'<span class="sub">'+s.s+'</span></span></div>').join('');
if(canClaim)html+='<button class="btn btn-violet" id="mClaim" style="margin-top:8px">🎁 Депо + поезд + маршрут</button>';
el('missionBody').innerHTML=html;const c=el('mClaim');if(c)c.onclick=starterPack;
if(done===steps.length){setMissionGone();card.classList.add('gone');setTimeout(()=>{card.style.display='none';},450);toast('✅ Сеть работает! Пульт скрыт навсегда.');}}
el('msToggle').onclick=()=>{const card=el('missionCard');card.classList.toggle('mini');el('msToggle').textContent=card.classList.contains('mini')?'🎯':'▾';SFX.play('click');};
function renderCompanyDrawer(){if(!me)return;el('ccColor').style.background=me.color||'#35d07f';el('ccName').textContent=me.display_name||me.name;
const c=companies.find(x=>x.id===me.id)||{};el('ccFounder').textContent='Основатель: '+(c.founder||'не указан')+' · логин: '+me.name;
el('ccTrackage').checked=!!myPolicies.allow_trackage;el('ccStops').checked=!!myPolicies.allow_stops;el('ccConnect').checked=!!myPolicies.allow_connect;
el('ccIn').textContent='+'+fmt(lastPoll?lastPoll.tractage_in:0)+' ₽';el('ccOut').textContent='−'+fmt(lastPoll?lastPoll.tractage_out:0)+' ₽';
palSel=me.color;const used=new Set(companies.filter(x=>x.id!==me.id).map(x=>(x.color||'').toLowerCase()));
el('palGrid').innerHTML=palette.map(col=>'<span class="pal-sw'+(used.has(col.toLowerCase())?' used':'')+(col===palSel?' sel':'')+'" data-c="'+col+'" style="background:'+col+';color:'+col+'"></span>').join('');
el('palGrid').querySelectorAll('.pal-sw:not(.used)').forEach(sw=>sw.onclick=()=>{palSel=sw.dataset.c;SFX.play('snap');el('palGrid').querySelectorAll('.pal-sw').forEach(x=>x.classList.toggle('sel',x.dataset.c===palSel));});
el('cmInfo').textContent='Субаренда: '+Math.round((constants.trackage_share||.3)*100)+' % дохода пропорционально км. Цвет перекрасит всё: полотно, поезда, станции, узлы, ⚡, ⚒ и штаб.';}
async function savePolicy(key,val){try{const r=await api('company_settings','POST',{[key]:val?1:0});myPolicies=r.policies;SFX.play('click');toast((val?'🔓 ':'🔒 ')+({allow_trackage:'Субаренда',allow_stops:'Остановки',allow_connect:'Подключение'}[key])+(val?' разрешены':' запрещены'));await loadWorld();await refreshAll();}catch(e){toast('Ошибка: '+e.message);renderCompanyDrawer();}}
el('ccTrackage').onchange=e=>savePolicy('allow_trackage',e.target.checked);
el('ccStops').onchange=e=>savePolicy('allow_stops',e.target.checked);
el('ccConnect').onchange=e=>savePolicy('allow_connect',e.target.checked);
el('ccColor').onclick=()=>el('palGrid').scrollIntoView({behavior:'smooth',block:'center'});
el('cmSave').onclick=async()=>{if(!palSel||palSel===me.color){toast('Цвет не изменён');return;}
try{const r=await api('company_settings','POST',{color:palSel});me.color=r.color;el('meChip').style.background=r.color;updPlate();SFX.play('cash');toast('🎨 Цвет обновлён');await loadWorld();await refreshAll();}catch(e){toast('Ошибка: '+e.message);}};
function renderRoutesList(){const list=el('routesList'),rs=mineArr(world.routes);el('cntRoutes').textContent=rs.length;
if(!rs.length){list.innerHTML='<div class="hint">Маршрутов нет. Клавиша 4 или автомаршруты.</div>';return;}
list.innerHTML='';
rs.forEach(r=>{const cls=r.status==='active'?'st-active':r.status==='idle'?'st-idle':'st-broken';
const pill=r.status==='active'?'<span class="pill ok">работает</span>':r.status==='idle'?'<span class="pill idle">без поездов</span>':'<span class="pill bad">разорван</span>';
const cap=routeCapNow(r),qN=routeQueue(r);const d=document.createElement('div');d.className='item '+cls;d.style.setProperty('--rc',r.color);
d.innerHTML='<div class="item-top"><span class="item-name">'+esc(r.name)+'</span>'+pill+'<span class="item-val '+(r.income>0?'pos':'')+'">'+(r.income>0?'+'+fmt(r.income)+' ₽':'0 ₽')+'</span></div><div class="item-sub"><span>'+r.km+' км</span><span>линия '+(r.line_speed||r.speed)+' · ср. '+(r.speed||0)+'</span><span>ёмк. '+cap+'/'+(r.slots||0)+'</span><span>🚃≤'+(r.wagon_limit??'?')+'</span>'+(r.fully_powered?'<span class="warnv">⚡</span>':'')+(r.loop?'<span>⟳</span>':'')+(qN?'<span class="warnv">🚦 '+qN+'</span>':'')+'</div>'+((r.bottlenecks||[]).length?'<div class="item-sub"><span class="warnv">🌉 узких мест: '+r.bottlenecks.length+'</span></div>':'');
d.onclick=ev=>{closeDrawer();ctxRoute(ev,r);};d.oncontextmenu=ev=>{closeDrawer();ctxRoute(ev,r);};list.appendChild(d);});}
function renderInfraLists(){const ds=mineArr(world.depots),ts=mineArr(world.trains),bs=mineArr(world.substations),tms=mineArr(world.track_maint);
el('cntDepots').textContent=ds.length;el('cntTrains').textContent=ts.length;el('cntSubs').textContent=bs.length;el('cntTm').textContent=tms.length;
const dl=el('depotList');dl.innerHTML='';if(!ds.length)dl.innerHTML='<div class="hint">Нет депо. Клавиша 3.</div>';
ds.forEach(d=>{const s=st(d.station_id);const pill=d.is_building?'<span class="pill idle">стройка</span>':d.upgrading?'<span class="pill info">расширение</span>':d.served?'<span class="pill ok">работает</span>':'<span class="pill idle">простой</span>';
const n=document.createElement('div');n.className='item';n.style.setProperty('--rc',d.color||'#24d3c4');
n.innerHTML='<div class="item-top"><span class="item-name">🏭 '+esc(d.name)+'</span>'+pill+'<span class="mini">'+(d.trains_count||0)+'/'+d.capacity+'</span></div><div class="item-sub"><span>ур.'+(d.level||1)+'</span><span>'+esc(s?s.name:'—')+'</span><span>'+(d.health??100)+'%</span>'+(d.is_building?'<span data-cd="'+d.build_end+'">'+dur(left(d.build_end))+'</span>':'')+(d.upgrading?'<span data-cd="'+d.upgrade_end+'">⬆ '+dur(left(d.upgrade_end))+'</span>':'')+'</div>';
n.onclick=()=>{closeDrawer();flyToDetail(d.lat,d.lng);openDepotModal(d);};n.oncontextmenu=ev=>{closeDrawer();ctxDepot(ev,d);};dl.appendChild(n);});
const tl=el('trainList');tl.innerHTML='';if(!ts.length)tl.innerHTML='<div class="hint">Нет поездов. Купите в депо или по пресету 📋.</div>';
ts.forEach(t=>{const r=idxRt.get(t.route_id),d=idxDp.get(t.depot_id),mk=t.model||'diesel_line',h=Number(t.health??100);
const pill=t.status==='running'?'<span class="pill ok">на маршруте</span>':t.status==='delivering'?'<span class="pill idle">поставка</span>':t.status==='transit'?'<span class="pill info">в пути</span>':t.status==='orphan'?'<span class="pill bad">без депо</span>':t.status==='broken'?'<span class="pill bad">разорван</span>':t.status==='no_power'?'<span class="pill bad">нет питания</span>':'<span class="pill idle">отстой</span>';
const sp=spriteImg(mk,t.status,'timg');const n=document.createElement('div');n.className='item';n.style.setProperty('--rc',r?r.color:'#61789a');
n.innerHTML='<div class="item-top">'+sp+'<span class="item-name">'+tIcon(mk)+' '+esc(t.name)+'</span><span class="pill '+(tTraction(mk)==='electric'?'el':'di')+'">'+tIcon(mk)+' ×'+(t.power??tPower(mk)).toFixed(2)+'</span>'+pill+'</div><div class="item-sub"><span>'+consistShort(t)+' · ёмк. '+trainCapacity(t)+'</span><span>'+h+'%</span><span>'+esc(d?d.name:'без депо')+'</span><span>'+(r?esc(r.name):'не назначен')+'</span>'+(t.is_building?'<span data-cd="'+t.build_end+'">'+dur(left(t.build_end))+'</span>':'')+'</div><div class="bar"><i style="width:'+h+'%;background:'+(h<20?'var(--red)':h<50?'var(--amber)':'var(--green)')+'"></i></div>'+(t.status==='running'?'<div class="item-live"><span class="sd"></span><span data-tstatus="'+esc(t.id)+'">…</span></div>':'');
n.onclick=ev=>{closeDrawer();ctxTrain(ev,t);};n.oncontextmenu=ev=>{closeDrawer();ctxTrain(ev,t);};tl.appendChild(n);});
const sl=el('subList');sl.innerHTML='';if(!bs.length)sl.innerHTML='<div class="hint">Нет подстанций. Клавиша 5.</div>';
bs.forEach(b=>{const s=st(b.station_id);const dead=b.idle!==undefined?!!b.idle:(!b.powering&&!b.is_building);
const pill=b.is_building?'<span class="pill idle">стройка</span>':b.powering?'<span class="pill ok">питает</span>':'<span class="pill idle">холостая</span>';
const n=document.createElement('div');n.className='item';n.style.setProperty('--rc',b.color||companyColor(b.owner_id));
n.innerHTML='<div class="item-top"><span class="item-name">⚡ '+esc(b.name)+'</span>'+pill+'</div><div class="item-sub"><span>8 км</span><span>'+esc(s?s.name:'—')+'</span><span>'+(b.health??100)+'%</span>'+(dead?'<span class="warnv">потухшая</span>':'')+(b.is_building?'<span data-cd="'+b.build_end+'">'+dur(left(b.build_end))+'</span>':'')+'</div>';
n.onclick=ev=>{closeDrawer();flyToDetail(b.lat,b.lng);ctxSub(ev,b);};n.oncontextmenu=ev=>{closeDrawer();ctxSub(ev,b);};sl.appendChild(n);});
const ml=el('tmList');ml.innerHTML='';if(!tms.length)ml.innerHTML='<div class="hint">Нет ПЧ. Клавиша 6.</div>';
tms.forEach(x=>{const s=st(x.station_id);const dead=x.idle!==undefined?!!x.idle:(!x.serves&&!x.is_building);
const pill=x.is_building?'<span class="pill idle">стройка</span>':x.serves?'<span class="pill ok">обслуживает</span>':'<span class="pill idle">холостая</span>';
const n=document.createElement('div');n.className='item';n.style.setProperty('--rc',x.color||companyColor(x.owner_id));
n.innerHTML='<div class="item-top"><span class="item-name">⚒ '+esc(x.name)+'</span>'+pill+'</div><div class="item-sub"><span>10 км</span><span>'+esc(s?s.name:'—')+'</span>'+(dead?'<span class="warnv">потухшая</span>':'')+(x.is_building?'<span data-cd="'+x.build_end+'">'+dur(left(x.build_end))+'</span>':'')+'</div>';
n.onclick=ev=>{closeDrawer();flyToDetail(x.lat,x.lng);ctxTm(ev,x);};n.oncontextmenu=ev=>{closeDrawer();ctxTm(ev,x);};ml.appendChild(n);});
updateTrainStatuses();}
function renderMyStations(){const list=el('myStations');if(!me){list.innerHTML='<div class="hint">Войдите.</div>';el('cntSt').textContent='0';return;}
let ms=mineArr(world.stations);el('cntSt').textContent=ms.length;
const q=(el('stSearch').value||'').toLowerCase();ms=ms.filter(s=>s.name.toLowerCase().includes(q));
const sort=el('stSort').value;
ms.sort((a,b)=>sort==='name'?a.name.localeCompare(b.name,'ru'):sort==='class'?(b.class||1)-(a.class||1):sort==='health'?(a.health??100)-(b.health??100):sort==='bonus'?(b.buildings||0)-(a.buildings||0):(b.routes_used||0)-(a.routes_used||0));
if(!ms.length){list.innerHTML='<div class="hint">'+(q?'Ничего не найдено.':'Постройте станцию.')+'</div>';return;}
list.innerHTML='';
ms.forEach(s=>{const h=s.health??100,hc=h<HEALTH_CRIT?'var(--red)':h<50?'var(--amber)':'var(--green)';
const pill=s.is_building?'<span class="pill idle">стройка</span>':s.upgrading?'<span class="pill info">⬆ кл.'+s.upgrade_class+'</span>':s.auction_status?'<span class="pill idle">лот</span>':s.served?'<span class="pill ok">в сети</span>':s.passed?'<span class="pill info">проезд</span>':'<span class="pill bad">простой</span>';
const d=document.createElement('div');d.className='item';d.style.setProperty('--rc',s.served||s.passed?(s.color||'#35d07f'):'#61789a');
d.innerHTML='<div class="item-top"><span class="item-name">'+esc(s.name)+'</span>'+pill+'<span class="mini">кл.'+(s.class||1)+'</span></div><div class="item-sub"><span>🧭 '+(s.routes_used||0)+'/'+(s.routes_limit||s.class||1)+'</span><span>🛤 '+(s.tracks??stTracks(s.id))+'</span><span>🚃≤'+(s.wagon_limit??wagonLimitByClass(s.class||1))+'</span><span>🏢 '+bonusPct(s.buildings)+'%</span><span>'+h+'%</span></div>'+(s.upgrading?'<div class="item-sub"><span class="warnv">⬆ до кл.'+s.upgrade_class+' · <span data-cd="'+s.upgrade_end+'">'+dur(left(s.upgrade_end))+'</span></span></div>':'')+'<div class="bar"><i style="width:'+h+'%;background:'+hc+'"></i></div>';
d.onclick=()=>{flyToDetail(s.lat,s.lng);SFX.play('click');const ring=L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:20,color:s.color||'#35d07f',weight:3,fillColor:s.color||'#35d07f',fillOpacity:.12,interactive:false}).addTo(map);setTimeout(()=>map.removeLayer(ring),1500);};
d.oncontextmenu=ev=>{closeDrawer();ctxStation(ev,s);};list.appendChild(d);});}
el('stSearch').addEventListener('input',renderMyStations);el('stSort').addEventListener('change',renderMyStations);
function repairTargets(){if(!me)return[];const out=[];
mineArr(world.stations).filter(x=>!x.is_building&&!x.frozen&&(x.health??100)<100).forEach(x=>out.push({o:x,kind:'station',cost:nearMe(x)?0:Math.ceil(stReplace(x)*REPAIR_RATIO)}));
mineArr(world.depots).filter(x=>!x.is_building&&!x.frozen&&(x.health??100)<100).forEach(x=>out.push({o:x,kind:'depot',cost:nearMe(x)?0:Math.ceil(dpReplace(x)*REPAIR_RATIO)}));
mineArr(world.substations).filter(x=>!x.is_building&&!x.frozen&&(x.health??100)<100).forEach(x=>out.push({o:x,kind:'substation',cost:nearMe(x)?0:Math.ceil(SUBSTATION_COST*REPAIR_RATIO)}));
mineArr(world.trains).filter(x=>!x.is_building&&!x.frozen&&(x.health??100)<100).forEach(x=>{const d=idxDp.get(x.depot_id);const near=d&&myPos?hav(myPos.lat,myPos.lng,d.lat,d.lng)<=REPAIR_FREE_M:false;out.push({o:x,kind:'train',cost:near?0:trainRepairCost(x)});});
return out;}
function openRepair(){const body=el('repairBody'),ok=el('repairOk');if(!me){body.innerHTML='<div class="hint">Войдите.</div>';ok.disabled=true;return;}
const need=repairTargets();el('repairNear').disabled=!myPos;
if(!need.length){body.innerHTML='<div style="color:var(--green-t);font-weight:700">✓ Всё в идеале.</div>';ok.disabled=true;el('repairModal').classList.add('open');return;}
const total=need.reduce((a,x)=>a+x.cost,0),freeN=need.filter(x=>x.cost===0).length;
const icon={station:'🚉',depot:'🏭',substation:'⚡',train:'🚆'};
let html='<div style="margin-bottom:10px">Объектов: <b>'+need.length+'</b> · <b style="color:var(--amber-t)">'+money(total)+'</b>'+(myPos?' · рядом '+freeN+' бесплатно':'')+'</div><table class="rep-table">';
need.forEach(x=>{html+='<tr><td>'+(icon[x.kind]||'🚉')+' '+esc(x.o.name)+'</td><td class="rh">'+x.o.health+'%</td><td class="rc">'+(x.cost===0?'<span class="free">бесплатно</span>':money(x.cost))+'</td></tr>';});
html+='</table><div class="hint" style="margin-top:8px">Составы: 10 %/реал. день в движении; бесплатно у депо по GPS. ПЧ не изнашивается.</div>';
body.innerHTML=html;ok.disabled=me.money<total;el('repairModal').classList.add('open');}
el('btnRepairTop').onclick=openRepair;
el('repairOk').onclick=async()=>{const need=repairTargets();if(!need.length)return;loading('Ремонтируем…');
try{for(const x of need){const b=myPos?{lat:myPos.lat,lng:myPos.lng}:{};
const a0=x.kind==='station'?'repair_station':x.kind==='depot'?'repair_depot':x.kind==='substation'?'repair_substation':'repair_train';
const key=x.kind==='station'?'station_id':x.kind==='depot'?'depot_id':x.kind==='substation'?'substation_id':'train_id';
await api(a0,'POST',{[key]:x.o.id,...b});}
unloading();SFX.play('build');el('repairModal').classList.remove('open');await loadWorld();await refreshAll();toast('🔧 Готово');}catch(e){unloading();toast('Ошибка: '+e.message);}};
el('repairNear').onclick=async()=>{if(!myPos){toast('Включите GPS 📍');return;}const r=await act('gps_repair_nearby',{lat:myPos.lat,lng:myPos.lng},'Чиним рядом…');if(r){toast(r.fixed.length?'🔧 '+r.fixed.length:'Рядом нечего чинить');openRepair();}};
async function openSell(sIds,eIds,nIds,dIds,tIds,bIds,mIds){if(!me)return;sIds=sIds||[];eIds=eIds||[];nIds=nIds||[];dIds=dIds||[];tIds=tIds||[];bIds=bIds||[];mIds=mIds||[];
sellState={stations:[...sIds],edges:[...eIds],nodes:[...nIds],depots:[...dIds],trains:[...tIds],substations:[...bIds],track_maint:[...mIds],min:0,max:0};
el('sellPrice').value='';
const ms=mineArr(world.stations).filter(s=>!s.auction_status&&!s.is_building&&!s.upgrading);
const me2=mineArr(world.edges).filter(e=>!e.auction_status&&!e.is_building&&!e.upgrading);
const mn=mineArr(world.nodes).filter(n=>!n.frozen&&!n.auction_status);
const md=mineArr(world.depots).filter(d=>!d.auction_status&&!d.is_building&&!d.upgrading);
const mt=mineArr(world.trains).filter(t=>!t.auction_status&&!t.is_building);
const mb=mineArr(world.substations).filter(b=>!b.auction_status&&!b.is_building&&!b.frozen);
const mm=mineArr(world.track_maint).filter(x=>!x.auction_status&&!x.is_building&&!x.frozen);
const fill=(boxId,title,arr,ids,key,label)=>{const box=el(boxId);box.innerHTML='<h4>'+title+'</h4>';
if(!arr.length){box.innerHTML+='<div class="hint">Нет доступных</div>';return;}
arr.forEach(o=>{const l=document.createElement('label');l.className='sell-row';l.innerHTML='<input type="checkbox" '+(ids.includes(o.id)?'checked':'')+'> '+esc(label(o));
l.querySelector('input').onchange=ev=>{if(ev.target.checked){if(!sellState[key].includes(o.id))sellState[key].push(o.id);}else sellState[key]=sellState[key].filter(x=>x!==o.id);sellQuote();};box.appendChild(l);});};
fill('sellStations','Станции',ms,sIds,'stations',s=>s.name+' · кл.'+s.class);
fill('sellEdges','Перегоны',me2,eIds,'edges',e=>e.from_name+' ↔ '+e.to_name+' · '+Math.round(e.length)+' м');
fill('sellNodes','Узлы',mn,nIds,'nodes',n=>n.name);
fill('sellDepots','Депо (с поездами)',md,dIds,'depots',d=>d.name+' · ур.'+d.level);
fill('sellTrains','Поезда',mt,tIds,'trains',t=>tIcon(t.model)+' '+t.name+' · '+consistShort(t));
fill('sellSubs','Подстанции',mb,bIds,'substations',b=>b.name);
fill('sellTm','ПЧ',mm,mIds,'track_maint',x=>x.name);
const hidden=mineArr(world.stations).filter(s=>s.is_building||s.upgrading||s.auction_status).length;
if(hidden>0)el('sellStations').insertAdjacentHTML('beforeend','<div class="hint">Скрыто: '+hidden+' (стройка/апгрейд/продажа)</div>');
await sellQuote();el('sellModal').classList.add('open');}
async function sellQuote(){const s=sellState;
if(!s.stations.length&&!s.edges.length&&!s.nodes.length&&!s.depots.length&&!s.trains.length&&!s.substations.length&&!s.track_maint.length){el('minPrice').textContent='0';el('maxPrice').textContent='0';s.min=0;s.max=0;return;}
try{const j=await api('get_sale_quote','POST',{station_ids:s.stations,edge_ids:s.edges,node_ids:s.nodes,depot_ids:s.depots,train_ids:s.trains,substation_ids:s.substations,track_maint_ids:s.track_maint});
s.min=j.min_price;s.max=j.max_price;el('minPrice').textContent=fmt(s.min);el('maxPrice').textContent=fmt(s.max);
const i=el('sellPrice');if(!i.value||+i.value<s.min)i.value=s.min;}catch(e){}}
el('sellOk').onclick=async()=>{const p=+el('sellPrice').value,s=sellState;
if(!s.stations.length&&!s.edges.length&&!s.nodes.length&&!s.depots.length&&!s.trains.length&&!s.substations.length&&!s.track_maint.length){toast('Выберите объект');return;}
if(!(p>=s.min&&p<=s.max)){toast('Цена: '+fmt(s.min)+'–'+fmt(s.max));return;}
try{await api('list_for_sale','POST',{station_ids:s.stations,edge_ids:s.edges,node_ids:s.nodes,depot_ids:s.depots,train_ids:s.trains,substation_ids:s.substations,track_maint_ids:s.track_maint,price:p});
el('sellModal').classList.remove('open');SFX.play('cash');await loadWorld();await refreshAll();toast('🏷 Лот на рынке');}catch(e){toast('Ошибка: '+e.message);}};
async function refreshLots(){try{const j=await api('auctions');lots=j.auctions;const list=el('lotsList');list.innerHTML='';
el('cntLots').textContent=lots.filter(a=>a.is_mine).length+' моих';
if(!lots.length){list.innerHTML='<div class="hint">Пусто.</div>';updateBadges();return;}
lots.forEach(a=>{const d=document.createElement('div');d.className='lot'+(a.type==='bankrupt'?' bank':'');
const names=(a.stations_preview||[]).map(s=>s.name).join(', ');
const more=a.stations_count>(a.stations_preview||[]).length?'… +'+(a.stations_count-a.stations_preview.length):'';
const parts=[a.stations_count+' ст.',a.edges_count+' пер.'];
if(a.nodes_count)parts.push(a.nodes_count+' узл.');if(a.depots_count)parts.push(a.depots_count+' депо');if(a.trains_count)parts.push(a.trains_count+' 🚆');if(a.substations_count)parts.push(a.substations_count+' ⚡');if(a.track_maint_count)parts.push(a.track_maint_count+' ⚒');
const btn=a.is_mine?(a.type==='manual'?'<button class="lot-btn cancel" data-cancel="'+a.id+'">Отменить</button>':'<button class="lot-btn" data-buy="'+a.id+'">Выкупить</button>'):'<button class="lot-btn" data-buy="'+a.id+'">Купить</button>';
d.innerHTML='<div class="head"><b><span class="cdot" style="background:'+a.seller_color+';display:inline-block"></span> '+parts.join(' · ')+'</b><span>'+dur(a.seconds_left)+'</span></div><div class="names">'+esc(names)+more+'</div><div class="bottom"><span class="lot-price">'+fmt(a.my_price)+' ₽ <small>(восст. '+fmt(a.replacement_cost)+')</small></span>'+btn+'</div><div style="font-size:10px;color:var(--mut);margin-top:4px">'+(a.type==='bankrupt'?'💥 Банкрот':'🏷 Продажа')+'</div>';
d.onclick=ev=>{if(ev.target.closest('button'))return;zoomLot(a);};list.appendChild(d);});
list.querySelectorAll('[data-buy]').forEach(b=>b.onclick=ev=>{ev.stopPropagation();buyLot(+b.dataset.buy);});
list.querySelectorAll('[data-cancel]').forEach(b=>b.onclick=ev=>{ev.stopPropagation();cancelLot(+b.dataset.cancel);});
updateBadges();}catch(e){}}
function zoomLot(a){const pts=(a.stations_preview||[]).filter(s=>s.lat&&s.lng);if(!pts.length)return;closeDrawer();
map.fitBounds(L.latLngBounds(pts.map(s=>[s.lat,s.lng])).pad(.6),{padding:[30,30]});if(!detailOk())map.setZoom(Math.max(map.getZoom(),14));
pts.forEach(s=>{const c=L.circleMarker([s.lat,s.lng],{pane:'prevP',radius:14,color:'#ffb02e',weight:3,fillColor:'#ffb02e',fillOpacity:.2,interactive:false}).addTo(map);setTimeout(()=>map.removeLayer(c),2600);});}
function buyLot(id){modal({title:'💰 Покупка лота',ok:'Купить',body:'<p style="margin:0">Объекты перейдут вам, деньги — продавцу.</p>',onOk:async()=>{const r=await act('buy_auction',{auction_id:id},'Сделка…','cash');if(r)toast('💰 '+money(r.price));}});}
function cancelLot(id){modal({title:'↩️ Отмена',ok:'Вернуть',body:'<p style="margin:0">Снять лот?</p>',onOk:async()=>{const r=await act('cancel_sale',{auction_id:id},'Отменяем…','click');if(r)toast('Лот снят');}});}
async function refreshPlayers(){try{const j=await api('players'),list=el('playersList');list.innerHTML='';
companies=j.players.map(p=>{const old=companies.find(c=>c.id===p.id);return old?{...old,...p}:p;});
j.players.forEach(p=>{const pol=p.policies||{};const icons=(pol.allow_trackage?'🛤':'')+(pol.allow_stops?'🚉':'')+(pol.allow_connect?'🔌':'');
const d=document.createElement('div');d.className='row-line';
d.innerHTML='<span class="cdot" style="background:'+p.color+'"></span><b>'+esc(p.display_name||p.name)+'</b><span style="font-size:10px">'+(icons||'🔒')+'</span><span style="margin-left:auto;color:var(--ink2);font:11px var(--f-mono)">'+fmt(p.money)+' ₽</span>'+(me&&p.id===me.id?' <span class="tag">вы</span>':'');
list.appendChild(d);});}catch(e){}}
async function refreshRating(){try{const j=await api('rating'),list=el('ratingList');list.innerHTML='';ratingCache=j.rating;
j.rating.forEach((p,i)=>{const d=document.createElement('div');d.className='row-line';
d.innerHTML='<span class="rank">'+(i+1)+'.</span><span class="cdot" style="background:'+p.color+'"></span><b>'+esc(p.display_name||p.name)+'</b><span style="margin-left:auto;color:'+(p.net_per_day>=0?'var(--green-t)':'var(--red-t)')+';font:700 11.5px var(--f-mono)">'+fmt(p.net_per_day)+' ₽/д</span>'+(me&&p.id===me.id?' <span class="tag">вы</span>':'');
d.title='Доход '+fmt(p.income_per_day)+' · содержание '+fmt(p.upkeep_per_day);list.appendChild(d);});
if(!j.rating.length)list.innerHTML='<div class="hint">Пока никого.</div>';}catch(e){}}
async function refreshEvents(){try{const j=await api('events'),list=el('eventsList');list.innerHTML='';
const vis=j.events.filter(e=>EV_ICON[e.kind]);
if(!vis.length){list.innerHTML='<div class="hint">Пока тихо.</div>';return;}
vis.forEach(e=>{const d=document.createElement('div');d.className='ev';
d.innerHTML='<span>'+(EV_ICON[e.kind]||'•')+'</span><span class="et">'+esc(e.text)+'</span><span class="ed">'+new Date(e.created_at).toLocaleTimeString('ru-RU',{hour:'2-digit',minute:'2-digit'})+'</span>';list.appendChild(d);});
const top=j.events.length?j.events[0].id:0;
if(newsSeen&&top>newsSeen&&!el('newsModal').classList.contains('open')){newsUnread+=j.events.filter(e=>e.id>newsSeen&&EV_ICON[e.kind]).length;updateBadges();}
newsSeen=top;}catch(e){}}
el('btnNews').onclick=()=>{el('newsModal').classList.add('open');newsUnread=0;updateBadges();refreshEvents();};
function loadChartJs(){if(window.Chart)return Promise.resolve();if(chartLoading)return chartLoading;
chartLoading=new Promise((res,rej)=>{const s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/chart.js';s.onload=()=>res();s.onerror=()=>rej(new Error('Chart.js не загрузился'));document.head.appendChild(s);});return chartLoading;}
const TT={backgroundColor:'#1a2740',borderColor:'#33507a',borderWidth:1,titleColor:'#e8f0fa',bodyColor:'#9db0c9',padding:10,cornerRadius:10,boxPadding:4};
function chartDefaults(){Chart.defaults.color='#9db0c9';Chart.defaults.borderColor='rgba(157,176,201,.13)';Chart.defaults.font.family="'Manrope',sans-serif";Chart.defaults.font.size=11;}
document.querySelectorAll('#chartTabs .tab').forEach(b=>b.onclick=async()=>{document.querySelectorAll('#chartTabs .tab').forEach(x=>x.classList.remove('active'));b.classList.add('active');chartMetric=b.dataset.m;SFX.play('click');
if(chartMetric==='net')await netChart();else if(chartMetric==='breakdown')breakdown();else await companyChart();});
el('btnChart').onclick=async()=>{chartFocusPid=null;el('chartModal').classList.add('open');chartCache={h:null,c:null};chartMetric='net';
document.querySelectorAll('#chartTabs .tab').forEach(b=>b.classList.toggle('active',b.dataset.m==='net'));await netChart();};
async function openChartsFor(pid){chartFocusPid=pid;closeDrawer();el('chartModal').classList.add('open');chartCache={h:null,c:null};chartMetric='net';
document.querySelectorAll('#chartTabs .tab').forEach(b=>b.classList.toggle('active',b.dataset.m==='net'));await netChart();}
function dsStyle(p){const f=chartFocusPid===null||p.id===chartFocusPid;return{borderColor:f?(p.color||'#35d07f'):((p.color||'#35d07f')+'55'),backgroundColor:(p.color||'#35d07f')+'22',borderWidth:f?(chartFocusPid!==null?3:2):1.2};}
async function netChart(){const box=el('chartBox');box.innerHTML='<div style="padding:20px;color:var(--mut)">Загрузка…</div>';
try{await loadChartJs();chartDefaults();}catch(e){box.innerHTML='<div style="padding:20px;color:var(--red-t)">'+esc(e.message)+'</div>';return;}
try{if(!chartCache.h)chartCache.h=await api('history');const j=chartCache.h;const days=[...new Set(j.history.map(r=>r.day))].sort((a,b)=>a-b);
if(!days.length){box.innerHTML='<div style="padding:20px;color:var(--mut)">Нет данных — переждите день.</div>';return;}
const ds=[];j.players.forEach(p=>{const m=new Map(j.history.filter(h=>h.player_id===p.id).map(h=>[h.day,h.net]));if(!m.size)return;
ds.push({label:p.display_name||p.name,data:days.map(d=>m.has(d)?m.get(d):null),...dsStyle(p),tension:.3,spanGaps:true,pointRadius:0,pointHitRadius:10});});
box.innerHTML='<canvas id="hChart"></canvas>';if(chart)chart.destroy();
chart=new Chart(el('hChart').getContext('2d'),{type:'line',data:{labels:days,datasets:ds},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top',labels:{usePointStyle:true,boxWidth:8}},tooltip:{...TT,callbacks:{title:c=>'День '+c[0].label,label:c=>c.dataset.label+': '+fmt(c.parsed.y)+' ₽/д'}}},scales:{x:{title:{display:true,text:'Игровой день'},ticks:{maxTicksLimit:20}},y:{title:{display:true,text:'₽/день'}}}}});
}catch(e){box.innerHTML='<div style="padding:20px;color:var(--red-t)">Ошибка: '+esc(e.message)+'</div>';}}
async function companyChart(){const box=el('chartBox');box.innerHTML='<div style="padding:20px;color:var(--mut)">Загрузка…</div>';
try{await loadChartJs();chartDefaults();}catch(e){box.innerHTML='<div style="padding:20px;color:var(--red-t)">'+esc(e.message)+'</div>';return;}
try{if(!chartCache.c)chartCache.c=await api('company_history');const j=chartCache.c,days=j.days,metric=chartMetric;
if(!days.length){box.innerHTML='<div style="padding:20px;color:var(--mut)">Нет данных.</div>';return;}
const titles={company_value:'Стоимость ₽',stations:'Станций',edge_km:'Км',routes:'Маршрутов',trains:'Поездов',depots:'Депо',substations:'Подстанций',track_maint:'ПЧ'};
const ds=[];j.players.forEach(p=>{const m=new Map(p.history.map(h=>[h.day,h[metric]]));const data=days.map(d=>m.has(d)?m.get(d):null);if(data.every(v=>v===null))return;
ds.push({label:p.display_name||p.name,data,...dsStyle(p),tension:.3,spanGaps:true,pointRadius:0,pointHitRadius:10});});
box.innerHTML='<canvas id="hChart"></canvas>';if(chart)chart.destroy();const isM=metric==='company_value';
chart=new Chart(el('hChart').getContext('2d'),{type:'line',data:{labels:days,datasets:ds},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top',labels:{usePointStyle:true,boxWidth:8}},tooltip:{...TT,callbacks:{title:c=>'День '+c[0].label,label:c=>c.dataset.label+': '+(c.parsed.y===null?'—':(isM?fmt(c.parsed.y)+' ₽':c.parsed.y))}}},scales:{x:{ticks:{maxTicksLimit:20}},y:{title:{display:true,text:titles[metric]||''},beginAtZero:!isM}}}});
}catch(e){box.innerHTML='<div style="padding:20px;color:var(--red-t)">Ошибка: '+esc(e.message)+'</div>';}}
function bdHTML(){if(!me)return '<div class="hint">Войдите.</div>';
const rs=mineArr(world.routes),ms=mineArr(world.stations),ns=mineArr(world.nodes),es=mineArr(world.edges),ds=mineArr(world.depots),ts=mineArr(world.trains),bs=mineArr(world.substations),tms=mineArr(world.track_maint);
const e=lastEco||{income:0,upkeep:0,net:0,tractage_in:0};
let html='<div style="padding:4px 2px"><div class="bd-sum"><div class="bd-chip i">Доход: +'+money(e.income)+'/д</div><div class="bd-chip u">Расход: −'+money(Math.ceil(e.upkeep))+'/д</div><div class="bd-chip n">Чистыми: '+money(Math.floor(e.net))+'/д</div></div><div class="bd-h v">🧭 Маршруты</div>';
if(!rs.length)html+='<div class="hint">Маршрутов нет.</div>';
rs.forEach(r=>{const rT=ts.filter(t=>t.route_id===r.id),capNow=routeCapNow(r),qN=routeQueue(r);
html+='<div class="bd-card"><div class="bd-row"><span>'+esc(r.name)+' <small>'+(r.status==='active'?'работает':r.status==='idle'?'без поездов':'разорван')+' · '+r.km+' км · линия '+(r.line_speed||r.speed)+' · ср. '+(r.speed||0)+' · ёмк. '+capNow+'/'+(r.slots||0)+(qN?' · 🚦 очередь '+qN:'')+'</small></span><span class="'+(r.income>0?'pos':'')+'">'+(r.income>0?'+':'')+money(r.income)+'</span></div>';
html+='<div class="bd-sub"><span>2 пути '+Math.round((r.double_share||0)*100)+'% · ⚡ '+Math.round((r.elec_share||0)*100)+'%'+(r.fully_powered?' (запитано)':' (НЕ запитано)')+'</span><span>×'+(1+.25*(r.double_share||0)).toFixed(2)+' · ×'+(1+.2*(r.elec_share||0)).toFixed(2)+' · ×'+((r.speed||0)/ROUTE_SPEED_BASE).toFixed(2)+'</span></div>';
(r.bottlenecks||[]).forEach(b=>{html+='<div class="bd-sub"><span>🌉 '+esc(b.name)+' <small>'+esc(b.type_label)+'</small></span><span class="warnv">'+b.speed+' · '+b.from_km+'–'+b.to_km+' км</span></div>';});
rT.forEach(t=>{html+='<div class="bd-sub"><span>'+tIcon(t.model)+' '+esc(t.name)+' · '+consistShort(t)+' · ёмк. '+trainCapacity(t)+' · '+(t.health??100)+'%</span><span>'+statusRu(t.status)+'</span></div>';});
(r.stations||[]).forEach(sid=>{const s=st(sid);if(!s)return;html+='<div class="bd-sub"><span>🚉 '+esc(s.name)+' <small>'+s.health+'%'+(s.upgrading?' · ⬆':'')+'</small></span><span>🏢 '+s.buildings+' → '+bonusPct(s.buildings)+'%</span></div>';});
(r.owners||[]).forEach(o=>{html+='<div class="bd-sub"><span>🤝 '+esc(companyName(o.player_id))+' · '+o.km.toFixed(2)+' км</span><span class="neg">−'+money(Math.round(r.income*o.share*(constants.trackage_share||.3)))+'</span></div>';});
html+='</div>';});
if(e.tractage_in)html+='<div class="bd-line"><span>🤝 Субаренда ваших путей</span><span class="pos">+'+money(e.tractage_in)+'</span></div>';
html+='<div class="bd-h r">🔧 Содержание</div>';
const grp=(title,arr,fn,note)=>{if(!arr.length)return '';let s='<div class="bd-h r" style="font-size:10px;margin-top:10px">'+title+'</div>';
arr.forEach(o=>{s+='<div class="bd-line"><span>'+esc(o.name||'')+' <small>'+note(o)+'</small></span><span class="neg">−'+money(Math.ceil(fn(o)))+'</span></div>';});return s;};
html+=grp('Станции',ms,stUpkeep,s=>'кл.'+s.class+(s.served?'':s.passed?' · проезд':' ×0.5'));
html+=grp('Узлы',ns,ndUpkeep,n=>n.served?'в сети':'×0.5');
html+=grp('Перегоны',es,edUpkeep,x=>Math.round(x.length)+' м · '+dblLabel(x)+' · '+(x.speed||0)+' км/ч'+(x.served?'':' ×0.5'));
html+=grp('Депо',ds,dpUpkeep,d=>'ур.'+d.level+(d.served?'':' ×0.5'));
html+=grp('Подстанции',bs,sbUpkeep,b=>b.powering?'питает':'холостая ×0.5');
html+=grp('ПЧ',tms,tmUpkeep,x=>x.serves?'обслуживает':'холостая ×0.5');
html+=grp('Поезда',ts,trUpkeep,t=>tIcon(t.model)+' '+consistShort(t)+' · '+(t.health??100)+'% · '+statusRu(t.status));
const rtU=rs.reduce((a,r)=>a+(r.upkeep||0)+(r.fee||0),0);
if(rtU)html+='<div class="bd-line"><span>🧭 Маршруты + субаренда</span><span class="neg">−'+money(Math.ceil(rtU))+'</span></div>';
return html+'</div>';}
function breakdown(){const box=el('chartBox');box.innerHTML=bdHTML();}
function openFinance(){modal({title:'💰 Доходы и расходы',wide:true,body:bdHTML(),ok:'Закрыть',showCancel:false});}
el('btnGps').onclick=()=>{if(!navigator.geolocation){toast('GPS не поддерживается');return;}
if(gpsWatch!==null){navigator.geolocation.clearWatch(gpsWatch);gpsWatch=null;myPos=null;if(gpsCircle&&map){map.removeLayer(gpsCircle);gpsCircle=null;}el('btnGps').classList.remove('on');toast('GPS выключен');updateBadges();return;}
gpsWatch=navigator.geolocation.watchPosition(p=>{myPos={lat:p.coords.latitude,lng:p.coords.longitude,acc:p.coords.accuracy};el('btnGps').classList.add('on');
if(!map)return;if(gpsCircle)map.removeLayer(gpsCircle);
gpsCircle=L.circle([myPos.lat,myPos.lng],{radius:Math.max(30,myPos.acc),color:'#3d9bff',fillColor:'#3d9bff',fillOpacity:.14,weight:2}).addTo(map);updateBadges();},err=>toast('GPS: '+err.message),{enableHighAccuracy:true,maximumAge:10000,timeout:15000});
toast('GPS включён — ремонт рядом бесплатный');};
el('devMoney').onclick=async()=>{try{await api('dev_give_money','POST',{amount:10000});SFX.play('cash');await loadWorld();await refreshAll();toast('💵 +10 000 ₽');}catch(e){toast('Ошибка: '+e.message);}};
el('devBank').onclick=()=>modal({title:'💥 Банкротство',danger:true,ok:'Обанкротиться',body:'<p style="margin:0;color:var(--red-t)">Объекты уйдут на аукцион. Штаб и пресеты не изымаются.</p>',onOk:async()=>{const r=await act('dev_bankrupt',{},'Банкротим…','error');if(r)toast('💥 '+r.stations+' ст., '+r.edges+' пер.');}});
el('btnHelp').onclick=()=>el('helpModal').classList.add('open');
/* ---------- мобильная навигация ---------- */
el('mPlate').onclick=()=>openFinance();
el('mpSnd').onclick=e=>{e.stopPropagation();ui.sound=!ui.sound;soundIcon();saveUI();if(ui.sound){SFX.unlock();SFX.play('click');}};
document.querySelectorAll('#mNav [data-mact]').forEach(b=>b.onclick=()=>{const a=b.dataset.mact;SFX.play('click');
if(a==='repair')openRepair();
else if(a==='charts')el('btnChart').click();
else if(a==='news')el('btnNews').click();
else if(a==='chat')el('btnChat').click();
else if(a==='help')el('helpModal').classList.add('open');
else if(a==='logout')el('logoutBtn').click();
else openDrawer(a);});
function updPlate(){if(!me)return;el('mpName').textContent=me.display_name||me.name;el('mpChip').style.background=me.color||'#35d07f';
el('mpBal').textContent=money(me.money);el('mpDay').textContent=curDay;
el('mpNet').textContent=(lastPoll?(lastPoll.net_per_day>=0?'+':'')+fmt(lastPoll.net_per_day)+' ₽/д':'=0 ₽/д');}
/* ---------- горячие клавиши вида (e.code: обе раскладки) ---------- */
const BASE_ORDER=['voyager','positron','osm','sat','hybrid','esri'];
const BASE_NAME={voyager:'Carto Voyager',positron:'Carto Positron',osm:'OSM',sat:'🛰 Google',hybrid:'🛰 Google+подписи',esri:'🛰 Esri'};
function setUiFlag(key,val){ui[key]=val;const mapIds={fade:'tgFade',trains:'tgTrains',labels:'tgLabels',subCircles:'tgSubCircles',tmCircles:'tgTmCircles',blocks:'tgBlocks',blockOcc:'tgBlockOcc',tlabels:'tgTrainLabels'};
const cb=el(mapIds[key]);if(cb)cb.checked=val;saveUI();
if(key==='fade')renderEdges();else if(key==='trains')syncTrains();else if(key==='labels'){renderDetailLayers();renderHqs();}
else if(key==='subCircles')renderSubstations();else if(key==='tmCircles')renderTrackMaint();else if(key==='blocks')renderBlockTicks();else if(key==='blockOcc')renderBlockOcc();else if(key==='tlabels')refreshTrainIcons();
const names={fade:'🌫 Полупрозрачность',trains:'🚆 Поезда',labels:'🏷 Подписи объектов',subCircles:'⚡ Радиусы подстанций',tmCircles:'⚒ Радиусы ПЧ',blocks:'🚦 Блок-участки',blockOcc:'🟠 Занятость',tlabels:'🏷 Подписи поездов'};
toast((names[key]||key)+': '+(val?'вкл':'выкл'));SFX.play('click');}
function cycleBase(){const i=BASE_ORDER.indexOf(ui.style);const nx=BASE_ORDER[(i+1)%BASE_ORDER.length];ui.style=nx;el('mapStyle').value=nx;setBase(nx);saveUI();toast('🗺 Подложка: '+BASE_NAME[nx]);SFX.play('click');}
function setMode(m){mode=m;edgeDraft={start:null,points:[]};routeDraft={stops:[],loop:false};routeQuote=null;routeGeom=null;edgeQuote=null;pendingSplit=null;
closeCtx();clearGhost();clearTimeout(routeQuoteTimer);
['mStation','mEdge','mDepot','mRoute','mSub','mTrackMaint'].forEach((id,i)=>el(id).classList.toggle('active',['station','edge','depot','route','substation','trackmaint'][i]===m));
const mp=el('map');['station','edge','depot','route','substation','trackmaint','hq'].forEach(x=>mp.classList.toggle('m-'+x,m===x));
if(map){if(m==='edge')map.doubleClickZoom.disable();else map.doubleClickZoom.enable();}
renderEdges();renderDetailLayers();renderHqs();renderDraft();renderPreview();renderBlockTicks();updateDock();updateScaleHint();}
el('mStation').onclick=()=>{if(!me)return;if(me.money<pricing.station){toast('Нужно '+money(pricing.station));return;}SFX.play('click');setMode(mode==='station'?'idle':'station');};
el('mEdge').onclick=()=>{if(!me)return;SFX.play('click');setMode(mode==='edge'?'idle':'edge');};
el('mDepot').onclick=()=>{if(!me)return;if(me.money<pricing.depot){toast('Нужно '+money(pricing.depot));return;}SFX.play('click');setMode(mode==='depot'?'idle':'depot');};
el('mRoute').onclick=()=>{if(!me)return;SFX.play('click');setMode(mode==='route'?'idle':'route');};
el('mSub').onclick=()=>{if(!me)return;if(me.money<pricing.substation){toast('Нужно '+money(pricing.substation));return;}SFX.play('click');setMode(mode==='substation'?'idle':'substation');};
el('mTrackMaint').onclick=()=>{if(!me)return;if(me.money<pricing.track_maint){toast('Нужно '+money(pricing.track_maint));return;}SFX.play('click');setMode(mode==='trackmaint'?'idle':'trackmaint');};
async function tryLogin(){try{const j=await api('me');if(j.player)logged(j.player);else el('loginModal').classList.add('open');}catch(e){el('loginModal').classList.add('open');}}
function logged(p){me=p;el('meName').textContent=me.display_name||me.name;el('meChip').style.background=me.color||'#35d07f';el('balance').textContent=money(me.money);
el('logoutBtn').style.display='';el('loginModal').classList.remove('open');el('lamp').className='lamp ok';
el('devCard').style.display=['localhost','127.0.0.1',''].includes(location.hostname)?'':'none';
updPlate();
if(!map)initMap();chromeH();setTimeout(()=>map&&map.invalidateSize(),160);
loadWorld().then(()=>{refreshAll();startPoll();startLive();startStatus();fitAll();startChat();});}
el('loginBtn').onclick=async()=>{const n=el('loginName').value.trim();if(!n)return;try{const j=await api('login','POST',{name:n});SFX.play('click');logged(j.player);}catch(e){toast('Ошибка входа: '+e.message);}};
el('loginName').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();el('loginBtn').click();}});
el('logoutBtn').onclick=async()=>{try{await api('logout');}catch(e){}
me=null;stopPoll();stopChat();stopStatus();closeCtx();closeDrawer();closeChat();closeDepotModal();setMode('idle');
if(gpsWatch!==null){navigator.geolocation.clearWatch(gpsWatch);gpsWatch=null;myPos=null;if(gpsCircle&&map){map.removeLayer(gpsCircle);gpsCircle=null;}el('btnGps').classList.remove('on');}
trainMarkers.forEach(v=>{if(map.hasLayer(v.marker))map.removeLayer(v.marker);});trainMarkers.clear();singleReq.clear();
if(tickerT){clearTimeout(tickerT);tickerT=null;}tickerQ=[];nextToast();
world={stations:[],nodes:[],edges:[],routes:[],depots:[],trains:[],substations:[],track_maint:[],hqs:[],presets:[]};companies=[];ratingCache=[];reindex();render();
el('meName').textContent='—';el('meChip').style.background='var(--mut)';el('balance').textContent='0 ₽';
el('bInc').textContent='+0 ₽';el('bUpk').textContent='−0 ₽';el('bNet').textContent='=0 ₽/д';
el('bGrace').style.display='none';el('bRisk').style.display='none';el('logoutBtn').style.display='none';el('devCard').style.display='none';
el('mpName').textContent='—';el('mpBal').textContent='0 ₽';el('mpNet').textContent='=0 ₽/д';
el('lamp').className='lamp err';el('loginModal').classList.add('open');};
async function loadWorld(){try{const j=await api('load');world=j.data||{};
['stations','nodes','edges','routes','depots','trains','substations','track_maint','hqs','presets'].forEach(k=>{if(!Array.isArray(world[k]))world[k]=[];});
lastVersion=j.version;lastEco=j.economy||null;companies=j.companies||companies;palette=j.palette||palette;myPolicies=j.my_policies||myPolicies;
constants=Object.assign(constants,j.constants||{});setTypes(j.bridge_types);setTrainModels(j.train_models);setWagonTypes(j.wagon_types);
if(j.constants){pricing.depot=j.constants.depot_cost;pricing.node=j.constants.node_cost;if(j.constants.substation_cost)pricing.substation=j.constants.substation_cost;if(j.constants.track_maint_cost)pricing.track_maint=j.constants.track_maint_cost;}
if(j.tick_left!==undefined){tickLeft=j.tick_left;lastSrvAt=Date.now();}
if(j.day!==undefined){curDay=j.day;el('dayNum').textContent=curDay;}
if(j.now!==undefined)srvOffset=j.now-Date.now()/1000;
if(me){const c=companies.find(x=>x.id===me.id);if(c){me.color=c.color;me.display_name=c.display_name||me.display_name;el('meChip').style.background=c.color;el('meName').textContent=me.display_name||me.name;}}
updPlate();render();}catch(e){}}
function render(){if(!map)return;reindex();renderEdges();renderDetailLayers();renderHqs();renderDraft();renderPreview();renderBlockTicks();renderBoard();}
function startPoll(){if(polling)return;polling=true;poll();}
function stopPoll(){polling=false;clearTimeout(pollTimer);}
async function poll(){if(!polling)return;
try{const j=await api('poll');lastPoll=j;el('lamp').className='lamp ok';
if(j.tick_left!==undefined){tickLeft=j.tick_left;lastSrvAt=Date.now();}
if(j.day!==undefined&&curDay!==j.day){curDay=j.day;el('dayNum').textContent=curDay;SFX.play('day');}
if(j.money!==undefined&&me){const prev=me.money;me.money=j.money;const b=el('balance');b.textContent=money(me.money);
if(j.money>prev){b.classList.remove('up');void b.offsetWidth;b.classList.add('up');SFX.play('cash');}
else if(j.money<prev){b.classList.remove('down');void b.offsetWidth;b.classList.add('down');}}
if(j.color&&me){me.color=j.color;el('meChip').style.background=j.color;}
if(j.display_name&&me){me.display_name=j.display_name;el('meName').textContent=j.display_name;}
el('bInc').textContent='+'+fmt(j.income_per_day)+' ₽';el('bUpk').textContent='−'+fmt(j.upkeep_per_day)+' ₽';el('bNet').textContent='='+fmt(j.net_per_day)+' ₽/д';
el('bGrace').style.display=j.grace_active?'':'none';
if(j.days_to_bankrupt!=null&&j.days_to_bankrupt<60){el('bRisk').style.display='';el('bRisk').textContent='⚠ банкрот через '+j.days_to_bankrupt+' д';}else el('bRisk').style.display='none';
if(j.now!==undefined)srvOffset=j.now-Date.now()/1000;
pricing.station=j.next_station_cost;pricing.edge=Number(j.edge_cost_per_m);
if(j.next_depot_cost)pricing.depot=j.next_depot_cost;if(j.next_substation_cost)pricing.substation=j.next_substation_cost;if(j.next_track_maint_cost)pricing.track_maint=j.next_track_maint_cost;if(j.node_cost)pricing.node=j.node_cost;
updatePrices();renderCompanyDrawer();updateBadges();updPlate();
if(j.version!==lastVersion){await loadWorld();await refreshAll();if(mode==='route'&&routeDraft.stops.length>=2)quoteRoute();}
}catch(e){el('lamp').className='lamp warn';if(e.message&&e.message.includes('not logged in')){stopPoll();el('lamp').className='lamp err';el('loginModal').classList.add('open');return;}}
pollTimer=setTimeout(poll,document.hidden?10000:POLL_MS);}
document.addEventListener('visibilitychange',()=>{if(!document.hidden&&polling){clearTimeout(pollTimer);poll();}});
async function refreshAll(){await Promise.all([refreshPlayers(),refreshRating(),refreshLots(),refreshEvents()]);renderBoard();}
let liveT=null;
function startLive(){if(liveT)return;liveT=setInterval(()=>{if(lastSrvAt>0){const l=Math.max(0,tickLeft-Math.floor((Date.now()-lastSrvAt)/1000));el('tickSec').textContent=l;el('tickBar').style.width=clamp((1-l/DAY)*100,0,100)+'%';
const mt=el('mpTick'),mb=el('mpBar');if(mt)mt.textContent=l+'с';if(mb)mb.style.width=clamp((1-l/DAY)*100,0,100)+'%';}
document.querySelectorAll('[data-cd]').forEach(n=>{const s=left(+n.dataset.cd);n.textContent=s>0?dur(s):'готово';});},1000);}
function closeChat(){el('chat').classList.remove('show');el('btnChat').classList.remove('on');}
async function refreshChat(){if(!me)return;try{const j=await api('chat_get'),box=el('chatMsgs'),msgs=j.messages||[];
const last=msgs.length?msgs[msgs.length-1].id:0;if(last===chatLast&&box.dataset.r==='1')return;
const newOther=box.dataset.r==='1'&&msgs.some(m=>m.id>chatLast&&m.player_id!==me.id);
chatLast=last;box.innerHTML='';
msgs.forEach(m=>{const d=document.createElement('div');d.className='cmsg';const col=m.color||companyColor(m.player_id);
d.innerHTML='<span class="a" style="color:'+col+'">'+esc(m.player_name||'игрок')+':</span> '+esc(m.text)+' <span class="t">'+new Date(m.created_at).toLocaleTimeString('ru-RU',{hour:'2-digit',minute:'2-digit'})+'</span>';box.appendChild(d);});
box.dataset.r='1';box.scrollTop=box.scrollHeight;if(newOther&&el('chat').classList.contains('show'))SFX.play('chat');}catch(e){}}
function startChat(){if(chatTimer)return;refreshChat();chatTimer=setInterval(refreshChat,3000);}
function stopChat(){clearInterval(chatTimer);chatTimer=null;}
el('btnChat').onclick=()=>{const c=el('chat');if(c.classList.contains('show')){closeChat();return;}closeDrawer();c.classList.add('show');el('btnChat').classList.add('on');refreshChat();};
el('chatClose').onclick=closeChat;
async function sendChat(){const i=el('chatInput'),t=i.value.trim();if(!t)return;i.value='';try{await api('chat_send','POST',{text:t});SFX.play('click');await refreshChat();}catch(e){toast('Ошибка чата: '+e.message);}}
el('chatSend').onclick=sendChat;
el('chatInput').addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();sendChat();}});
document.addEventListener('keydown',e=>{const tag=e.target.tagName;const typing=tag==='INPUT'||tag==='TEXTAREA'||tag==='SELECT';
if(e.key==='Enter'&&el('actionModal').classList.contains('open')){const b=el('actionOk');if(b&&!b.disabled){e.preventDefault();b.click();}return;}
if(e.key==='Enter'&&el('sellModal').classList.contains('open')&&document.activeElement===el('sellPrice')){e.preventDefault();el('sellOk').click();return;}
if(typing){if(e.key==='Escape'&&el('actionModal').classList.contains('open')){e.preventDefault();el('actionCancel').click();}return;}
if(el('actionModal').classList.contains('open')){if(e.key==='Escape'){e.preventDefault();el('actionCancel').click();}return;}
if(e.key==='Escape'){for(const id of['sellModal','newsModal','helpModal','repairModal','chartModal','depotModal']){if(el(id).classList.contains('open')){el(id).classList.remove('open');if(id==='depotModal')depotModalId=null;if(id==='chartModal'&&chart){chart.destroy();chart=null;chartCache={h:null,c:null};chartFocusPid=null;}return;}}
if(ctx.classList.contains('open')){closeCtx();return;}if(drawerName){closeDrawer();return;}if(el('chat').classList.contains('show')){closeChat();return;}setMode('idle');return;}
if(document.querySelector('.modal.open'))return;
if(e.key==='1'&&me)setMode(mode==='station'?'idle':'station');
else if(e.key==='2'&&me)setMode(mode==='edge'?'idle':'edge');
else if(e.key==='3'&&me)setMode(mode==='depot'?'idle':'depot');
else if(e.key==='4'&&me)setMode(mode==='route'?'idle':'route');
else if(e.key==='5'&&me)setMode(mode==='substation'?'idle':'substation');
else if(e.key==='6'&&me)setMode(mode==='trackmaint'?'idle':'trackmaint');
else if(e.key==='Enter'&&mode==='edge'&&edgeDraft.start){e.preventDefault();finishEdgeInField();}
else if(e.key===' '){if(mode==='edge'&&edgeDraft.start&&mouseLL){e.preventDefault();const s=findSnap(mouseLL);const ll=s?L.latLng(s.lat,s.lng):snap(mouseLL);edgeDraft.points.push({lat:ll.lat,lng:ll.lng});SFX.play('snap');renderDraft();updateDock();}
else if(mode==='idle'){e.preventDefault();ui.paused=!ui.paused;saveUI();updateTrainStatuses();toast(ui.paused?'⏸ Пауза':'▶ Движение');}}
else if(e.key==='Backspace'){if(mode==='edge'&&edgeDraft.points.length){e.preventDefault();edgeDraft.points.pop();renderDraft();updateDock();}
else if(mode==='route'&&routeDraft.stops.length){e.preventDefault();routeDraft.stops.pop();if(routeDraft.stops.length<3)routeDraft.loop=false;renderDraft();quoteRoute();}}
else if(e.code==='KeyX')setUiFlag('fade',!ui.fade);
else if(e.code==='KeyM')cycleBase();
else if(e.code==='KeyZ')setUiFlag('trains',!ui.trains);
else if(e.code==='KeyC')el('btnChat').click();
else if(e.code==='KeyV')setUiFlag('labels',!ui.labels);
else if(e.code==='KeyB')setUiFlag('subCircles',!ui.subCircles);
else if(e.code==='KeyN')setUiFlag('tmCircles',!ui.tmCircles);
else if(e.code==='Comma')setUiFlag('blocks',!ui.blocks);
else if(e.code==='Period')setUiFlag('blockOcc',!ui.blockOcc);
else if(e.code==='Slash'){e.preventDefault();openDrawer('market');}
else if(e.code==='KeyH')el('helpModal').classList.add('open');
else if(e.code==='KeyO')openDrawer('settings');});
let rzT=null;
window.addEventListener('resize',()=>{clearTimeout(rzT);rzT=setTimeout(()=>{chromeH();if(map){map.invalidateSize();drawGrid();renderDetailLayers();renderDraft();renderBlockTicks();}},160);});
/* ---------- АНИМАЦИЯ + БЛОК-УЧАСТКИ ---------- */
function trainK(){if(!map)return 1;const m=mpp(map.getCenter().lat);return clamp(0.70+0.30*Math.min(1,m/20),0.5,1);}
function trainSize(k){const len=Math.max(6,Math.round(clamp(SIM.tlen,10,34)*k));
return{len,wid:Math.max(3,Math.round(len*.4)),wLen:Math.max(4,Math.round(len*.72)),wWid:Math.max(2,Math.round(len*.34)),gap:Math.max(1,Math.round(2*k))};}
function drawWagCount(o){return Math.min((o.wagTypes||[]).length,MAX_DRAW_WAGONS);}
function trainLenM(o){return 25+drawWagCount(o)*30;}
function buildTrainIcon(o){const k=trainK();o.k=k;const s=trainSize(k);o.dim=s;
const wt=(o.wagTypes||[]).slice(0,MAX_DRAW_WAGONS);o.drawW=wt.length;
let cars='<i class="tb loco'+(o.electric?' el':'')+'" style="width:'+s.len+'px;height:'+s.wid+'px;background-color:'+o.color+'"></i>';
wt.forEach(w=>{const cls=w==='baggage'?'bg':w==='mail'?'ml':'ps';
cars+='<i class="tb wg '+cls+'" style="width:'+s.wLen+'px;height:'+s.wWid+'px;background-color:'+o.color+';--cp:'+s.gap+'px"></i>';});
const lab=ui.tlabels?'<span class="tl"></span>':'';
return L.divIcon({html:'<div class="tw">'+cars+lab+'</div>',className:'',iconSize:[1,1],iconAnchor:[0,0]});}
function bindTrainDom(o){let root=o.marker.getElement&&o.marker.getElement();let els=root?Array.from(root.querySelectorAll('.tb')):[];
const expected=1+drawWagCount(o);
if(root&&els.length!==expected){o.marker.setIcon(buildTrainIcon(o));root=o.marker.getElement&&o.marker.getElement();els=root?Array.from(root.querySelectorAll('.tb')):[];o.wseg=[];}
o.carEls=els;o.label=root?root.querySelector('.tl'):null;return els.length===expected;}
function refreshTrainIcons(){trainMarkers.forEach(o=>{o.marker.setIcon(buildTrainIcon(o));o.wseg=[];bindTrainDom(o);});updateTrainStatuses();}
function planOf(r){const pts=r.geometry||[];if(pts.length<2)return null;const g=pathOf(pts);if(!(g.total>1))return null;
let marks=(r.stops_dist||[]).map((d,i)=>({dist:clamp(Number(d)||0,0,g.total),sid:(r.stations||[])[i]})).filter(m=>m.sid);
if(marks.length<2)return null;
if(r.loop&&marks[marks.length-1].dist<g.total-1)marks.push({dist:g.total,sid:r.stations[0]});
const transit=(r.transit||[]).map(t=>({dist:clamp(Number(t.dist)||0,0,g.total),sid:t.id}));
transit.sort((a,b)=>a.dist-b.dist);return{g,stopList:marks,transit};}
function computeTarget(o){const total=o.g.total,list=o.stopList;if(!list||list.length<2){o.targetDist=total;o.targetSid=null;return;}
if(o.loop){o.dir=1;for(const s of list){if(s.dist>o.dist+.5){o.targetDist=s.dist;o.targetSid=s.sid;return;}}o.targetDist=list[0].dist+total;o.targetSid=list[0].sid;return;}
if(o.dir>0){for(const s of list){if(s.dist>o.dist+.5){o.targetDist=s.dist;o.targetSid=s.sid;return;}}o.dir=-1;return computeTarget(o);}
for(let i=list.length-1;i>=0;i--){if(list[i].dist<o.dist-.5){o.targetDist=list[i].dist;o.targetSid=list[i].sid;return;}}
o.dir=1;return computeTarget(o);}
function trainArrive(o){const total=o.g.total;o.dist=o.loop?((o.targetDist%total)+total)%total:clamp(o.targetDist,0,total);
o.v=0;o.phase='dwell';o.dwellLeft=dwellReal();o.atSid=o.targetSid;const s=st(o.targetSid);
if(s){fxPulse(s.lat,s.lng,o.color,false);if(trainMarkers.size<=8)SFX.play('stop');}}
function trainDepart(o){const total=o.g.total;if(!o.loop){if(o.dist>=total-.5)o.dir=-1;else if(o.dist<=.5)o.dir=1;}computeTarget(o);o.phase='run';o.atSid=null;}
function crossedMark(prev,cur,dir,m){if(dir>0)return cur>=prev?(m>prev&&m<=cur):(m>prev||m<=cur);return cur<=prev?(m<prev&&m>=cur):(m<prev||m>=cur);}
function profAt(o,d){const stt=o.prof;if(!stt||!stt.length)return o.lineSpeed;let v=stt[0][1];for(let i=0;i<stt.length;i++){if(d>=stt[i][0])v=stt[i][1];else break;}return v;}
function capInfo(o,d){const capNow=profAt(o,d);let vCap=capNow/3.6,zoneBrake=false,zoneV=0;const stt=o.prof||[];
for(let i=0;i<stt.length;i++){if(stt[i][0]>d+.5&&stt[i][1]<capNow){const a=Math.max(.05,(capNow/3.6)/Math.max(.1,SIM.accel));const vz=stt[i][1]/3.6;const db=Math.max(0,stt[i][0]-d);const vb=Math.sqrt(vz*vz+2*a*db);if(vb<vCap){vCap=vb;zoneBrake=true;zoneV=stt[i][1];}break;}}
const modelCap=tSpeed(o.t?o.t.model:null)/3.6;if(modelCap<vCap)vCap=modelCap;
return{vCap,zoneBrake,zoneV,inZone:capNow<o.lineSpeed-.5,capNow};}
function spanAt(o,d){const sp=o.spans;if(!sp||!sp.length)return null;const total=o.g.total;d=((d%total)+total)%total;
for(let i=0;i<sp.length;i++){if(d>=sp[i].from_m&&d<sp[i].to_m)return{i,s:sp[i]};}
return{i:sp.length-1,s:sp[sp.length-1]};}
function locAt(o,d){const f=spanAt(o,d);if(!f)return null;const s=f.s;const len=s.to_m-s.from_m;
const p=s.f?(d-s.from_m):(len-(d-s.from_m));
const ed=o.dir*(s.f?1:-1);
const bi=s.double?clamp(Math.floor(p/Math.max(1,s.block_len)),0,s.blocks_n-1):0;
return{i:f.i,s,p,ed,bi,len,key:s.e+'|'+(s.double?ed:0)+'|'+bi};}
function buildRegistry(){blockReg.clear();stationOcc.clear();
trainMarkers.forEach(o=>{o._head=null;o._appr=null;if(!o.spans||!o.spans.length)return;
if(o.phase==='dwell'&&o.atSid)stationOcc.set(o.atSid,o.t.id);
const h=locAt(o,o.dist);const t=locAt(o,o.dist-o.dir*trainLenM(o));
o._head=h;
[h,t].forEach(Lc=>{if(!Lc)return;let set=blockReg.get(Lc.key);if(!set){set=new Set();blockReg.set(Lc.key,set);}set.add(o.t.id);});});}
function singleHolder(eid){let occ=null;trainMarkers.forEach(o=>{if(o._head&&o._head.s.e===eid)occ=o.t.id;});
if(occ)return occ;const m=singleReq.get(eid);if(!m||!m.size)return null;
let best=null,bt=Infinity;m.forEach((ts,id)=>{if(ts<bt||(ts===bt&&(best===null||id<best))){bt=ts;best=id;}});return best;}
function registerApprovers(now){trainMarkers.forEach(o=>{const h=o._head;if(!h)return;const ni=h.i+o.dir;
if(ni>=0&&ni<o.spans.length){const ns=o.spans[ni];if(!ns.double){const rem=(o.dir>0?ns.from_m-o.dist:o.dist-ns.to_m);
if(rem>=-1&&rem<APPROACH_M){let m=singleReq.get(ns.e);if(!m){m=new Map();singleReq.set(ns.e,m);}if(!m.has(o.t.id))m.set(o.t.id,now);o._appr=ns.e;}}}});
singleReq.forEach((m,e)=>{m.forEach((ts,id)=>{const Lc=trainMarkers.get(id);const on=Lc&&Lc._head&&Lc._head.s.e===e;const appr=Lc&&Lc._appr===e;if(!on&&!appr)m.delete(id);});if(!m.size)singleReq.delete(e);});}
function blockConstraint(o,now){if(o.releaseUntil&&now<o.releaseUntil)return null;
const h=o._head;if(!h)return null;const dir=o.dir;let stop=null,reason='';
const setStop=(d,r)=>{const rem=(d-o.dist)*dir;if(rem<-1)return;if(stop===null||rem<(stop-o.dist)*dir){stop=d;reason=r;}};
const mates=blockReg.get(h.key);
if(mates)mates.forEach(id=>{if(id===o.t.id)return;const Lc=trainMarkers.get(id);if(!Lc||!Lc._head||Lc._head.key!==h.key)return;
const ahead=(Lc.dist-o.dist)*dir;if(ahead>0)setStop(Lc.dist-dir*(trainLenM(Lc)+BLOCK_GAP_M),'блок занят «'+Lc.t.name+'»');});
const s=h.s;
if(s.double){const bEdge=h.ed>0?(h.bi+1)*s.block_len:h.bi*s.block_len;const remB=(bEdge-h.p)*h.ed;
if(remB>0){const nbi=h.bi+h.ed;
if(nbi>=0&&nbi<s.blocks_n){const nk=s.e+'|'+h.ed+'|'+nbi;const occ=blockReg.get(nk);
if(occ){const other=[...occ].find(id=>id!==o.t.id);if(other){const Lc=trainMarkers.get(other);
setStop(o.dist+dir*remB-dir*BLOCK_MARGIN_M,'🚦 блок впереди занят'+(Lc?' «'+Lc.t.name+'»':''));}}}}}
const spanRem=(dir>0?s.to_m-o.dist:o.dist-s.from_m);const ni=h.i+dir;
if(ni>=0&&ni<o.spans.length){const ns=o.spans[ni];const nlen=ns.to_m-ns.from_m;
const entryP=ns.f?(dir>0?0:nlen):(dir>0?nlen:0);
const nbi=ns.double?clamp(Math.floor(entryP/Math.max(1,ns.block_len)),0,ns.blocks_n-1):0;
const ned=dir*(ns.f?1:-1);const nk=ns.e+'|'+(ns.double?ned:0)+'|'+nbi;
const occ=blockReg.get(nk);const other=occ&&[...occ].find(id=>id!==o.t.id);
if(!ns.double){const hold=singleHolder(ns.e);
if(other||(hold&&hold!==o.t.id)){const Lc=other?trainMarkers.get(other):null;
setStop(o.dist+dir*spanRem-dir*BLOCK_MARGIN_M,other?('однопутка занята'+(Lc?' «'+Lc.t.name+'»':'')):'🚦 очередь на однопутку');}}
else if(other){const Lc=trainMarkers.get(other);setStop(o.dist+dir*spanRem-dir*BLOCK_MARGIN_M,'🚦 блок впереди занят'+(Lc?' «'+Lc.t.name+'»':''));}}
if(o.targetSid&&stationOcc.has(o.targetSid)&&stationOcc.get(o.targetSid)!==o.t.id){
const sObj=st(o.targetSid);
if(sObj&&sObj.blocks_entry!==false){const occId=stationOcc.get(o.targetSid);const Lc=trainMarkers.get(occId);
setStop(o.targetDist-dir*ST_HOLD_M,'🚉 станция занята'+(Lc?' «'+Lc.t.name+'»':''));}}
return stop===null?null:{stop,reason};}
function syncTrains(){if(!map)return;const show=ui.trains&&detailOk();updateScaleHint();
if(!show){trainMarkers.forEach(v=>{if(map.hasLayer(v.marker))map.removeLayer(v.marker);});return;}
const K=trainK();const want=new Map();
world.trains.forEach(t=>{if(t.status!=='running'||!t.route_id)return;const r=idxRt.get(t.route_id);if(!r||r.status!=='active')return;
const plan=planOf(r);if(!plan)return;want.set(t.id,{t,r,plan,color:r.color||t.color||'#a97bff',electric:tTraction(t.model)==='electric',wagTypes:trainWagList(t).slice(),wsig:trainWagList(t).join(',')});});
trainMarkers.forEach((o,id)=>{if(!want.has(id)){if(map.hasLayer(o.marker))map.removeLayer(o.marker);trainMarkers.delete(id);}});
const fresh=[];
want.forEach((v,id)=>{const ex=trainMarkers.get(id);
if(ex){const changed=Math.abs(ex.g.total-v.plan.g.total)>1||ex.r.id!==v.r.id;
ex.g=v.plan.g;ex.r=v.r;ex.t=v.t;ex.stopList=v.plan.stopList;ex.transit=v.plan.transit;ex.loop=!!v.r.loop;ex.color=v.color;ex.electric=v.electric;
ex.prof=v.r.speed_profile||null;ex.lineSpeed=v.r.line_speed||v.r.speed||EDGE_SPEED_BASE;ex.spans=v.r.edge_spans||[];
if(ex.wsig!==v.wsig||Math.abs((ex.k||0)-K)>.02){ex.wsig=v.wsig;ex.wagTypes=v.wagTypes;ex.marker.setIcon(buildTrainIcon(ex));ex.wseg=[];}
ex.wagTypes=v.wagTypes;ex.dist=clamp(ex.dist,0,ex.g.total);
if(changed){ex.seg=0;ex.wseg=[];}
if(changed||ex.phase==='run')computeTarget(ex);
if(!map.hasLayer(ex.marker))ex.marker.addTo(map);
if(!ex.carEls||!ex.carEls.length||!ex.carEls[0].isConnected)bindTrainDom(ex);}
else fresh.push([id,v]);});
fresh.forEach(([id,v])=>{const g=v.plan.g;const dists=[];trainMarkers.forEach(o=>{if(o.r&&o.r.id===v.r.id)dists.push(o.dist);});
const dist=largestGapDist(dists,g.total,!!v.r.loop,routePhase(v.r.id));const p=pointAt(g,dist);
const o={marker:null,g,r:v.r,t:v.t,stopList:v.plan.stopList,transit:v.plan.transit,loop:!!v.r.loop,color:v.color,electric:v.electric,
wagTypes:v.wagTypes,wsig:v.wsig,dim:null,k:K,drawW:Math.min(v.wagTypes.length,MAX_DRAW_WAGONS),seg:p.seg,wseg:[],reg:1,fi:0,
prof:v.r.speed_profile||null,lineSpeed:v.r.line_speed||v.r.speed||EDGE_SPEED_BASE,spans:v.r.edge_spans||[],
dist,dir:1,v:0,phase:'run',dwellLeft:0,angle:p.bearing-90,carEls:null,label:null,dwClass:false,bkClass:false,atSid:null,lastPass:0,passSid:null,passAt:0,
blockedSince:0,blockedReason:'',releaseUntil:0,_head:null,_appr:null};
o.marker=L.marker([p.lat,p.lng],{pane:'trainP',icon:buildTrainIcon(o),keyboard:false,interactive:false}).addTo(map);
computeTarget(o);o.v=((v.r.speed||EDGE_SPEED_BASE)/3.6)*SIM.speed*.4;trainMarkers.set(id,o);bindTrainDom(o);});
updateTrainStatuses();}
function refreshTrainPlans(){trainMarkers.forEach(o=>{const plan=planOf(o.r);if(!plan)return;o.g=plan.g;o.stopList=plan.stopList;o.transit=plan.transit;o.dist=clamp(o.dist,0,o.g.total);o.seg=0;o.wseg=[];if(o.phase!=='dwell')computeTarget(o);});updateTrainStatuses();}
function startAnim(){if(animRunning)return;animRunning=true;lastFrame=performance.now();requestAnimationFrame(frame);}
function frame(now){requestAnimationFrame(frame);const dt=Math.min(.1,(now-lastFrame)/1000);lastFrame=now;frameNo++;
if(!map||document.hidden||!ui.trains||!trainMarkers.size)return;
const mul=ui.paused?0:SIM.speed;
if(mul<=0){if(frameNo%30===0)updateTrainStatuses();return;}
buildRegistry();registerApprovers(now);
const heavy=trainMarkers.size>16;const byRoute=new Map();
trainMarkers.forEach(o=>{if(!o.r)return;let a=byRoute.get(o.r.id);if(!a){a=[];byRoute.set(o.r.id,a);}a.push(o);});
const deadlockMs=(constants.block_deadlock_sec||28)*1000;
trainMarkers.forEach(o=>{const g=o.g;if(!g||!(g.total>1))return;o.fi=(o.fi||0)+1;const skipWags=heavy&&(o.fi&1)===1;
const accelModel=(o.t&&tModel(o.t.model).accel)||1;let regTarget=1;const siblings=byRoute.get(o.r.id)||[];
if(siblings.length>1&&o.phase==='run'){const total=g.total,target=total/siblings.length;let minGap=Infinity;
for(const s of siblings){if(s===o)continue;let gap;if(o.loop){gap=((s.dist-o.dist)%total+total)%total;if(gap<1)continue;}else{if(s.dir!==o.dir)continue;gap=(s.dist-o.dist)*o.dir;if(gap<=1)continue;}if(gap<minGap)minGap=gap;}
if(minGap!==Infinity){const ratio=minGap/Math.max(1,target);regTarget=ratio<.5?.8:ratio<.8?.9:ratio>1.7?1.12:ratio>1.25?1.05:1;}}
o.reg+=(regTarget-o.reg)*Math.min(1,dt*1.6);
const ci=capInfo(o,o.dist);const vmax=ci.vCap*mul*(o.reg||1);const a=(vmax/Math.max(.2,SIM.accel))*accelModel;
const bc=blockConstraint(o,now);
if(o.phase==='dwell'){o.dwellLeft-=dt;if(o.dwellLeft<=0)trainDepart(o);}
else{
let remTarget=Math.abs(o.targetDist-o.dist);
let remBlock=Infinity;
if(bc){remBlock=(bc.stop-o.dist)*o.dir;}
if(bc&&remBlock<=.6){
if(o.phase!=='blocked'){o.phase='blocked';o.blockedSince=now;}
o.blockedReason=bc.reason;o.v=0;
if(now-o.blockedSince>deadlockMs){o.releaseUntil=now+8000;o.phase='run';o.blockedSince=0;o.blockedReason='';}
}else{
if(o.phase==='blocked'){o.phase='run';o.blockedSince=0;o.blockedReason='';}
const rem=Math.min(remTarget,Math.max(0,remBlock));
const vT=Math.min(vmax,Math.sqrt(2*a*Math.max(0,rem)));
o.v=Math.min(o.v+a*dt,Math.max(vT,Math.min(vmax*.06,vmax)));
const step=Math.min(o.v*dt,Math.max(0,remBlock));
if(step>=remTarget-.05&&remBlock>remTarget)trainArrive(o);
else{const prev=o.dist;o.dist+=o.dir*step;
if(o.loop)o.dist=((o.dist%g.total)+g.total)%g.total;else o.dist=clamp(o.dist,0,g.total);
for(const tr of(o.transit||[])){if(!crossedMark(prev,o.dist,o.dir,tr.dist))continue;const s=st(tr.sid);if(!s)continue;
fxPulse(s.lat,s.lng,'#cfe0f5',true);o.passSid=tr.sid;o.passAt=now;
if(trainMarkers.size<=6&&now-o.lastPass>6000){o.lastPass=now;SFX.play('pass');}}}}}
const locoSeg=segAt(g,o.dist,o.seg!==undefined?o.seg:0);o.seg=locoSeg;const loco=pointAtSeg(g,o.dist,locoSeg);
o.marker.setLatLng([loco.lat,loco.lng]);
const flip=(o.dir<0&&!o.loop)?180:0;const rotL=loco.bearing-90+flip;
if(!o.carEls||!o.carEls.length||!o.carEls[0].isConnected)bindTrainDom(o);
if(!o.carEls||!o.carEls.length)return;
const dim=o.dim||trainSize(o.k||1);const mppNow=mpp(loco.lat);const kx=111320*Math.cos(loco.lat*Math.PI/180);
o.carEls[0].style.transform='translate(-50%,-50%) rotate('+(Math.round(rotL*10)/10)+'deg)';
const dw=o.phase==='dwell',bk=o.phase==='blocked';
if(dw!==o.dwClass){o.dwClass=dw;o.carEls[0].classList.toggle('dw',dw);}
if(bk!==o.bkClass){o.bkClass=bk;o.carEls[0].classList.toggle('bk',bk);}
if(skipWags)return;
const n=Math.min(o.carEls.length-1,drawWagCount(o));if(o.wseg.length!==n)o.wseg=new Array(n).fill(locoSeg);
for(let iW=0;iW<n;iW++){const pxOff=dim.len/2+dim.gap+iW*(dim.wLen+dim.gap)+dim.wLen/2;
const offM=pxOff*mppNow;let dW=o.dist-o.dir*offM;
if(o.loop)dW=((dW%g.total)+g.total)%g.total;else dW=clamp(dW,0,g.total);
const wSeg=segAt(g,dW,o.wseg[iW]!==undefined?o.wseg[iW]:locoSeg);o.wseg[iW]=wSeg;const wp=pointAtSeg(g,dW,wSeg);
const dxPx=((wp.lng-loco.lng)*kx)/mppNow;const dyPx=(-(wp.lat-loco.lat)*111320)/mppNow;const rotW=wp.bearing-90+flip;
o.carEls[iW+1].style.transform='translate('+dxPx.toFixed(1)+'px,'+dyPx.toFixed(1)+'px) translate(-50%,-50%) rotate('+(Math.round(rotW*10)/10)+'deg)';}
o.angle=rotL;if(o.label&&o.label.isConnected&&ui.tlabels)o.label.style.transform='translate(-50%,'+(dim.wid/2+6)+'px)';});
if(frameNo%15===0)renderBlockOcc();
if(frameNo%30===0)updateTrainStatuses();}
document.addEventListener('visibilitychange',()=>{lastFrame=performance.now();});
function trainLive(o){if(!o)return null;const mul=ui.paused?0:SIM.speed;const kmh=mul>0?Math.round((o.v/mul)*3.6):0;
const nextName=(st(o.targetSid)||{}).name||'—';
if(mul<=0)return{cls:'pass',text:'⏸ пауза · далее «'+nextName+'»'};
if(o.releaseUntil&&performance.now()<o.releaseUntil)return{cls:'stop',text:'⚠ разпуск клинча · '+kmh};
if(o.phase==='blocked')return{cls:'stop',text:'🚦 '+(o.blockedReason||'ожидание')};
if(o.phase==='dwell')return{cls:'stop',text:'🛑 «'+((st(o.atSid)||{}).name||'—')+'» · '+Math.max(0,o.dwellLeft).toFixed(1)+' с'};
const ci=capInfo(o,o.dist);const accelModel=(o.t&&tModel(o.t.model).accel)||1;
const vmax=ci.vCap*mul*(o.reg||1);const a=(vmax/Math.max(.2,SIM.accel))*accelModel;const rem=Math.abs(o.targetDist-o.dist);
const braking=Math.sqrt(2*a*Math.max(0,rem))<vmax*.97;
if(o.passSid&&performance.now()-o.passAt<2200)return{cls:'pass',text:'⏩ «'+((st(o.passSid)||{}).name||'—')+'» · '+kmh};
if(ci.zoneBrake)return{cls:'stop',text:'◀ перед 🌉 '+ci.zoneV+' · '+kmh};
if(ci.inZone)return{cls:'stop',text:'🌉 '+Math.round(ci.capNow)+' км/ч в пролёте'};
if(braking)return{cls:'stop',text:'◀ торможение · '+kmh+' → «'+nextName+'»'};
if(o.v<vmax*.97)return{cls:'',text:'▶ разгон · '+kmh+' → «'+nextName+'»'};
return{cls:'',text:'⏩ '+kmh+' км/ч → «'+nextName+'»'};}
function updateTrainStatuses(){document.querySelectorAll('[data-tstatus]').forEach(n=>{const live=trainLive(trainMarkers.get(n.dataset.tstatus));const wrap=n.closest('.item-live');
if(!live){n.textContent=detailOk()?'нет анимации':'скрыто на обзоре';if(wrap)wrap.className='item-live pass';return;}
n.textContent=live.text;if(wrap)wrap.className='item-live '+live.cls;});
if(ui.tlabels)trainMarkers.forEach(o=>{if(!o.label||!o.label.isConnected)bindTrainDom(o);if(!o.label)return;
const mul=ui.paused?0:SIM.speed,kmh=mul>0?Math.round((o.v/mul)*3.6):0;
o.label.textContent=o.phase==='blocked'?(o.t.name+' · 🚦'):o.phase==='dwell'?(o.t.name+' · стоянка '+Math.max(0,o.dwellLeft).toFixed(0)+'с'):(o.t.name+' · ёмк.'+trainCapacity(o.t)+' · '+kmh);});}
function startStatus(){if(statusT)return;statusT=setInterval(updateTrainStatuses,350);}
function stopStatus(){clearInterval(statusT);statusT=null;}
function clearGhost(){[ghost,ghostBadge,tempLine,tempTip,osmTip].forEach(l=>{if(l&&map)map.removeLayer(l);});ghost=ghostBadge=tempLine=tempTip=osmTip=null;clearTimeout(osmTimer);}
if('serviceWorker' in navigator)window.addEventListener('load',()=>navigator.serviceWorker.register('sw.js').catch(()=>{}));
window.addEventListener('offline',()=>toast('⛔ Нет сети'));
window.addEventListener('online',()=>{toast('🌐 Снова в сети');if(me)loadWorld().then(refreshAll);});
setTypes(FALLBACK_TYPES);setTrainModels(FALLBACK_MODELS);setWagonTypes(FALLBACK_WAGONS);
const perfBuf=[];window.__perf=perfBuf;
async function api(action,method,body){
const t0=performance.now();const o={method:method||'GET',headers:{}};
if(body){o.headers['Content-Type']='application/json';o.body=JSON.stringify(body);}
const r=await fetch(API+'?action='+action,o);const txt=await r.text();
const ms=Math.round(performance.now()-t0);
perfBuf.push({t:Date.now(),action,ms,kb:Math.round(txt.length/1024),code:r.status});
if(perfBuf.length>300)perfBuf.shift();
if(ms>800)console.warn('🐢 '+action+' '+ms+' мс · '+Math.round(txt.length/1024)+' КБ');
let j;try{j=JSON.parse(txt);}catch(e){throw new Error(txt.replace(/<[^>]*>/g,'').trim().slice(0,220)||('HTTP '+r.status));}
if(!r.ok)throw new Error(j.error||('HTTP '+r.status));return j;}
window.__perfStat=()=>{const g={};perfBuf.forEach(r=>{(g[r.action]=g[r.action]||[]).push(r.ms);});
Object.entries(g).forEach(([a,v])=>{v.sort((x,y)=>x-y);
console.log(a.padEnd(20),'n='+v.length,'avg='+Math.round(v.reduce((s,x)=>s+x,0)/v.length),'p95='+v[Math.floor(.95*(v.length-1))],'max='+v[v.length-1]);});};
loadUI();chromeH();tryLogin();

/* ===== ФИКС: служба перегона ≠ доступ ≠ полупрозрачность ===== */
let edgeLive=new Map(),edgeSig=0,edgeWatchT=null;

function buildEdgeService(){
edgeLive=new Map();
const put=(eid,kind,ref)=>{if(!eid)return;let o=edgeLive.get(eid);
if(!o){o={active:0,trains:0,routes:[],names:[]};edgeLive.set(eid,o);}
if(kind==='route'){if(!o.routes.includes(ref.id)){o.routes.push(ref.id);o.active++;}}
else{o.trains++;if(ref&&o.names.length<6&&!o.names.includes(ref))o.names.push(ref);}};
const spansOf=r=>{
if(Array.isArray(r.edge_spans)&&r.edge_spans.length)return r.edge_spans.map(s=>s.e);
if(Array.isArray(r.path)&&r.path.length)return r.path.map(s=>s.e);
return Array.isArray(r.edges)?r.edges:[];};
const act=new Map();
(world.routes||[]).forEach(r=>{if(r.status!=='active')return;act.set(r.id,r);
[...new Set(spansOf(r))].forEach(eid=>put(eid,'route',r));});
(world.trains||[]).forEach(t=>{if(t.status!=='running'||!t.route_id)return;const r=act.get(t.route_id);if(!r)return;
[...new Set(spansOf(r))].forEach(eid=>put(eid,'train',t.name));});}

function edgeServiceInfo(e){
const rec=edgeLive.get(e.id)||{active:0,trains:0,routes:[],names:[]};
const srvN=Number(e.routes_active||0);
const server=!!e.served||srvN>0;
const live=rec.active>0||rec.trains>0;
return{on:server||live,server,live,active:Math.max(rec.active,srvN),trains:rec.trains,names:rec.names,routes:rec.routes};}
function edgeServed(e){return edgeServiceInfo(e).on;}

function edgeServiceSig(){let h=2166136261>>>0;const add=n=>{h=(((h^(n>>>0))>>>0)*16777619)>>>0;};
add(ui.fade?1:0);add(me?me.id:0);add(world.edges.length);
world.edges.forEach(e=>{const s=edgeServiceInfo(e);let x=0;const id=String(e.id);
for(let i=0;i<id.length;i++)x=(x*31+id.charCodeAt(i))>>>0;
add(x);add(s.on?1:0);add(s.trains);add(e.can_use?1:0);add(e.is_building?1:0);add(e.upgrading?1:0);
add(e.auction_status==='manual'?1:e.auction_status==='bankrupt'?2:0);
add(e.double?1:0);add(e.electric?1:0);add(e.powered?1:0);});
return h;}

function reindex(){
idxSt=new Map();idxNd=new Map();idxEp=new Map();idxEd=new Map();idxDp=new Map();idxRt=new Map();idxSb=new Map();idxTm=new Map();idxHq=new Map();
world.stations.forEach(s=>{idxSt.set(s.id,s);idxEp.set(s.id,{...s,kind:'station'});});
world.nodes.forEach(n=>{idxNd.set(n.id,n);idxEp.set(n.id,{...n,kind:'node'});});
world.edges.forEach(e=>idxEd.set(e.id,e));world.depots.forEach(d=>idxDp.set(d.id,d));world.routes.forEach(r=>idxRt.set(r.id,r));
world.substations.forEach(b=>idxSb.set(b.id,b));world.track_maint.forEach(x=>idxTm.set(x.id,x));world.hqs.forEach(h=>idxHq.set(h.id,h));
edgeGeom=new Map();world.edges.forEach(e=>{const ll=edgeLL(e);if(ll&&ll.length>1)edgeGeom.set(e.id,pathOf(ll));});
buildEdgeService();edgeSig=edgeServiceSig();}

function renderEdges(){
if(!map)return;edgeLayer.clearLayers();edgeHitLayer.clearLayers();
if(!edgeLive.size)buildEdgeService();
const ON=ui.fade?.78:.95,OFF=ui.fade?.22:.38,live=(mode==='idle'||mode==='edge');
world.edges.forEach(e=>{
const ll=edgeLL(e);if(!ll)return;
const mine=me&&e.owner_id===me.id,sv=edgeServiceInfo(e);
let color=e.color||companyColor(e.owner_id)||'#7d8aa0',dash=null,opacity=sv.on?ON:OFF;
const weight=e.double?4.5:2.4;
if(e.is_building){dash='6 5';opacity=.6;}
else if(e.auction_status==='bankrupt'){color='#98a4b4';dash='2 5';opacity=Math.max(opacity,.5);}
else if(e.auction_status==='manual'){color=lotAfford(e)?'#f5c518':'rgba(245,197,24,.55)';dash='2 5';opacity=Math.max(opacity,.55);}
const closed=!mine&&me&&!e.can_use&&!e.is_building;
if(closed)opacity=sv.on?Math.max(ON*.85,.62):OFF;
if(sv.on&&!e.is_building)L.polyline(ll,{color,weight:weight+7,opacity:.1,interactive:false,smoothFactor:1}).addTo(edgeLayer);
L.polyline(ll,{color:'#0a1120',weight:weight+2.5,opacity:opacity*.34,interactive:false,smoothFactor:1}).addTo(edgeLayer);
L.polyline(ll,{color,weight,opacity,dashArray:dash,smoothFactor:1,interactive:false}).addTo(edgeLayer);
if(e.electric&&!e.is_building)L.polyline(ll,{color:(e.powered||e.electric_legacy)?'#ffffff':'#ff5a5a',weight:1.3,opacity:Math.min(.9,opacity+.22),dashArray:'2 6',interactive:false,smoothFactor:1}).addTo(edgeLayer);
if(closed)L.polyline(ll,{color:'#e8f0fa',weight:1.2,opacity:.5,dashArray:'1 9',interactive:false,smoothFactor:1}).addTo(edgeLayer);
if(!live)return;
const hit=L.polyline(ll,{pane:'edgeHitP',color:'#fff',weight:EDGE_HIT_PX,opacity:0,interactive:true,smoothFactor:1}).addTo(edgeHitLayer);
hit.bindTooltip(edgeTip(e),{sticky:true,className:'tip'});
hit.on('contextmenu',ev=>{L.DomEvent.stopPropagation(ev);if(mode==='idle')ctxEdge(ev.originalEvent,e,ev.latlng);});
hit.on('click',ev=>{L.DomEvent.stopPropagation(ev);
if(mode==='edge'&&!edgeDraft.start){openSplitFlow(e,ev.latlng);return;}
if(mode==='edge'&&edgeDraft.start){edgeDraft.points.push({lat:ev.latlng.lat,lng:ev.latlng.lng});SFX.play('snap');renderDraft();updateDock();return;}
if(mode==='idle')ctxEdge(ev.originalEvent,e,ev.latlng);});});}

function edgeTip(e){
const cap=e.speed||EDGE_SPEED_BASE,sv=edgeServiceInfo(e);
let tip='<b>'+esc(e.from_name||epName(e.from))+' ↔ '+esc(e.to_name||epName(e.to))+'</b><br>'
+Math.round(e.length)+' м · '+dblLabel(e)+'<br>потолок <b>'+cap+'</b> км/ч · '+elecLabel(e)+'<br>🚦 '+blocksLabel(e);
(e.bridges||[]).forEach(br=>{tip+='<br>🌉 '+esc(br.name||'')+': '+br.speed+' км/ч <span style="color:#9db0c9">только в пролёте</span>';});
tip+='<br><span style="color:'+e.color+'">■</span> '+esc(companyName(e.owner_id));
if(me&&e.owner_id!==me.id)tip+='<br>'+(e.can_use?'🔓 проезд разрешён · субаренда 30 %':'🔒 проезд закрыт для вас')+' <span style="color:#9db0c9">('+accessStateLabel(e.access_state)+')</span>';
if(e.is_building)tip+='<br>🏗 <span data-cd="'+e.build_end+'">'+dur(left(e.build_end))+'</span>';
else if(sv.on){tip+='<br>🚆 в работе'+(sv.trains?' · составов: '+sv.trains:'')+(sv.active?' · маршрутов: '+sv.active:'');
if(sv.names.length)tip+='<br><span style="color:#9db0c9">'+esc(sv.names.slice(0,3).join(', '))+'</span>';}
else tip+='<br>💤 не обслуживается — нет активных маршрутов · содержание ×0.5';
tip+='<br><span style="color:#9db0c9">клик — меню · режим 2 — врезка</span>';
return tip;}

function ctxEdge(ev,e,ll){
ev.preventDefault();ev.stopPropagation();
const mine=me&&e.owner_id===me.id,len=Number(e.length||0),cap=e.speed||EDGE_SPEED_BASE,sv=edgeServiceInfo(e);
const rs=routesOnEdge(e.id),actR=(world.routes||[]).filter(r=>r.status==='active'&&sv.routes.includes(r.id));
const rows=[ownerRow(e),
['Участок',(e.from_name||epName(e.from))+' ↔ '+(e.to_name||epName(e.to))],
['Длина',len.toFixed(0)+' м'],['Путей',dblLabel(e)],['Электрификация',elecLabel(e)],
['Потолок',cap+' км/ч'],['🚦 Блоки',blocksLabel(e)],
['Статус',sv.on?('🚆 в работе'+(sv.trains?' · '+sv.trains+' сост.':'')):'💤 простой ×0.5'],
['Содержание',money(Math.ceil(edUpkeep(e)))+'/д']];
(e.bridges||[]).forEach(br=>rows.push(['🌉 '+br.name,br.speed+' км/ч в пролёте']));
const occ=edgeOccupants(e);
if(occ.length){rows.push({sec:'🚦 Занято · '+occ.length});occ.slice(0,6).forEach(o=>rows.push([tIcon(o.t.model)+' '+o.t.name,(o.phase==='blocked'?'🚦 ждёт':o.phase==='dwell'?'🛑 стоянка':'⏩ едет')]));}
if(!mine)rows.push(['Проезд',e.can_use?'🔓 разрешён (30 %)':'🔒 закрыт владельцем']);
if(e.is_building)rows.push(['🏗','<span data-cd="'+e.build_end+'">'+dur(left(e.build_end))+'</span>',true]);
if(e.upgrading)rows.push(['⬆️ '+(e.upgrade_type==='double'?'2-й путь':'электрификация'),'<span data-cd="'+e.upgrade_end+'">'+dur(left(e.upgrade_end))+'</span>',true]);
if(actR.length){rows.push({sec:'Активные маршруты · '+actR.length});actR.forEach(r=>rows.push([r.name,'🚆 '+r.speed+' км/ч · '+money(r.income)+'/д']));}
const acts=[];
if(!e.is_building&&!e.frozen&&(mine||e.can_use))acts.push({label:'📍 Врезка ('+money(pricing.station)+')',disabled:me.money<pricing.station||!!e.upgrading,fn:()=>openSplitFlow(e,ll||L.latLng((ep(e.from)||{}).lat,(ep(e.from)||{}).lng))});
if(mine&&!e.is_building&&!e.auction_status){
acts.push({label:'🚦 Блок-участки…',fn:()=>openBlockFlow(e)});
if(!e.double&&!e.upgrading){const c=Math.ceil(len*COST_DOUBLE_M),nr=nearestOwnTmToEdge(e),ok=!!e.double_legacy||(!!nr&&nr.d<=(constants.track_maint_range_m||TRACK_MAINT_RANGE_M));
acts.push({label:'🛤 2-й путь ('+money(c)+')'+(ok?'':' · ⚠ нужна ПЧ'),disabled:me.money<c||!ok,fn:()=>doubleFlow(e)});}
if(!e.electric&&!e.upgrading)acts.push({label:'⚡ Электрифицировать ('+money(Math.ceil(len*COST_ELEC_M))+')',disabled:me.money<Math.ceil(len*COST_ELEC_M),fn:()=>elecFlow(e)});
if(e.electric&&!e.powered&&!e.electric_legacy)acts.push({label:'⚠ Обесточен — постройте подстанцию (5)',disabled:true,fn:()=>{}});
(e.bridges||[]).forEach(br=>{const cur=bridgeSpeed(br.type);if(bridgeTypes.some(t=>(!t.rail_only||br.kind==='rail')&&t.speed>cur))acts.push({label:'⬆️ '+br.name+': убрать провал '+cur,fn:()=>upgradeBridgeFlow(e,br)});});
acts.push({label:'🏷 На продажу',disabled:!!e.upgrading,fn:()=>openSell([],[e.id],[],[],[],[],[])});
acts.push({label:'🗑 Снести',danger:true,fn:()=>deleteFlow('🗑 Перегон','Маршруты разорвутся. Возврат 50 %.','delete_edge',{edge_id:e.id})});}
rs.forEach(r=>acts.push({label:'🧭 '+r.name,fn:()=>openRouteMenu(r,lastCtx.x,lastCtx.y)}));
showCtx(ev.clientX,ev.clientY,'🛤 Перегон',rows,acts,'','edge',e);}

async function api(action,method,body){
const m=method||'GET',o={method:m,headers:{},cache:'no-store'};
if(body){o.headers['Content-Type']='application/json';o.body=JSON.stringify(body);}
let url=API+'?action='+action;if(m==='GET')url+='&_='+Date.now();
const r=await fetch(url,o);const txt=await r.text();let j;
try{j=JSON.parse(txt);}catch(e){throw new Error(txt.replace(/<[^>]*>/g,'').trim().slice(0,220)||('HTTP '+r.status));}
if(!r.ok)throw new Error(j.error||('HTTP '+r.status));return j;}

document.addEventListener('visibilitychange',()=>{if(!document.hidden&&me&&map){loadWorld().then(()=>{buildEdgeService();edgeSig=edgeServiceSig();renderEdges();}).catch(()=>{});}});
buildEdgeService();edgeSig=edgeServiceSig();
if(!edgeWatchT)edgeWatchT=setInterval(()=>{if(!map||!me)return;buildEdgeService();const s=edgeServiceSig();if(s!==edgeSig){edgeSig=s;renderEdges();}},1200);

var __loadP=null,__loadAgain=false;
async function loadWorld(){
if(__loadP){__loadAgain=true;return __loadP;}
__loadP=__doLoad();
try{await __loadP;}finally{__loadP=null;}
if(__loadAgain){__loadAgain=false;return loadWorld();}
}
async function __doLoad(){try{const j=await api('load');world=j.data||{};
['stations','nodes','edges','routes','depots','trains','substations','track_maint','hqs','presets'].forEach(k=>{if(!Array.isArray(world[k]))world[k]=[];});
lastVersion=j.version;lastEco=j.economy||null;companies=j.companies||companies;palette=j.palette||palette;myPolicies=j.my_policies||myPolicies;
constants=Object.assign(constants,j.constants||{});setTypes(j.bridge_types);setTrainModels(j.train_models);setWagonTypes(j.wagon_types);
if(j.constants){pricing.depot=j.constants.depot_cost;pricing.node=j.constants.node_cost;if(j.constants.substation_cost)pricing.substation=j.constants.substation_cost;if(j.constants.track_maint_cost)pricing.track_maint=j.constants.track_maint_cost;}
if(j.tick_left!==undefined){tickLeft=j.tick_left;lastSrvAt=Date.now();}
if(j.day!==undefined){curDay=j.day;el('dayNum').textContent=curDay;}
if(j.now!==undefined)srvOffset=j.now-Date.now()/1000;
if(me){const c=companies.find(x=>x.id===me.id);if(c){me.color=c.color;me.display_name=c.display_name||me.display_name;el('meChip').style.background=c.color;el('meName').textContent=me.display_name||me.name;}}
updPlate();render();}catch(e){}}

</script>
</body>
</html>