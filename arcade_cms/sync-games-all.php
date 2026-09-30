<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ignore_user_abort(true);
set_time_limit(285);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function out(array $data, int $status=200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    exit;
}

$url = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?: getenv('NEON_DATABASE_URL') ?: getenv('SUPABASE_DB_URL');
if (!$url) out(['ok'=>false,'error'=>'database_url_missing'],503);
$p = parse_url($url);
$dsn = 'pgsql:host='.($p['host']??'').' ;port='.(int)($p['port']??5432).';dbname='.rawurldecode(ltrim((string)($p['path']??''),'/')).';sslmode=require;connect_timeout=8';
$dsn = str_replace(' ;',';', $dsn);
try {
    $pdo = new PDO($dsn, rawurldecode((string)($p['user']??'')), rawurldecode((string)($p['pass']??'')), [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
} catch (Throwable $e) { out(['ok'=>false,'error'=>'database_connection_failed'],503); }

$token = trim((string)($_GET['token']??''));
$stored = trim((string)($pdo->query("SELECT settings_10 FROM gm_setting WHERE id=1")->fetchColumn() ?: ''));
if ($token==='' || $stored==='' || !hash_equals($stored,$token)) out(['ok'=>false,'error'=>'unauthorized'],403);

$lock = (bool)$pdo->query('SELECT pg_try_advisory_lock(91472027)')->fetchColumn();
if (!$lock) out(['ok'=>false,'error'=>'import_already_running'],409);
$lockReleased=false;
register_shutdown_function(function() use ($pdo, &$lockReleased): void {
    if ($lockReleased) return;
    try { $pdo->query('SELECT pg_advisory_unlock(91472027)'); } catch (Throwable $e) {}
});

$categoryParam = trim((string)($_GET['category'] ?? 'All'));
$popularityParam = trim((string)($_GET['popularity'] ?? 'newest'));
$amountParam = trim((string)($_GET['amount'] ?? 'All'));
$pageParam = (int)($_GET['page'] ?? 0);
if ($pageParam < 0) $pageParam = 0;
$allowedPopularity = ['newest','oldest','popular','trending','best','random'];
if (!in_array(strtolower($popularityParam), $allowedPopularity, true)) {
    $popularityParam = 'newest';
}
if ($amountParam === '' || !preg_match('/^(?:All|[1-9][0-9]{0,4})$/i', $amountParam)) {
    $amountParam = 'All';
}
$categoryParam = $categoryParam === '' ? 'All' : $categoryParam;

$feedQuery = http_build_query([
    'format' => 'json',
    'category' => $categoryParam,
    'type' => 'html5',
    'popularity' => $popularityParam,
    'company' => 'All',
    'amount' => $amountParam,
]);
$legacyPageUrl = 'https://gamemonetize.com/feed.php?format=0&page=' . max(1, $pageParam);
$feedUrls = $pageParam > 0
    ? [$legacyPageUrl]
    : [
        'https://rss.gamemonetize.com/rssfeed.php?' . $feedQuery,
        'https://gamemonetize.com/rssfeed.php?' . $feedQuery
      ];
$body=''; $used=''; $errors=[];
foreach($feedUrls as $feed){
    $ch=curl_init($feed);
    if($ch===false){$errors[]='curl_init_failed';continue;}
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_MAXREDIRS=>3,
        CURLOPT_CONNECTTIMEOUT=>8, CURLOPT_TIMEOUT=>120, CURLOPT_ENCODING=>'',
        CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2,
        CURLOPT_USERAGENT=>'PokiCrazyGames-CatalogImporter/1.0',
        CURLOPT_HTTPHEADER=>['Accept: application/json,text/plain,*/*']
    ]);
    $r=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
    if(is_string($r) && $r!=='' && $code>=200 && $code<300){$body=$r;$used=$feed;break;}
    $errors[]='http_'.$code.($err!==''?':'.$err:'');
}
if($body==='') out(['ok'=>false,'error'=>'feed_unavailable','details'=>$errors],502);

$data=json_decode($body,true);
if(!is_array($data)) out(['ok'=>false,'error'=>'invalid_feed_json'],502);
if(isset($data['games']) && is_array($data['games'])) $data=$data['games'];
$isFullCatalogRequest = $pageParam === 0 && strtolower($categoryParam) === 'all' && strtolower($amountParam) === 'all' && strtolower($popularityParam) === 'newest';
if ($isFullCatalogRequest && count($data) < 10000) {
    out(['ok'=>false,'error'=>'feed_did_not_return_full_catalog','source_count'=>count($data),'feed_url'=>$used],502);
}
if ($pageParam > 0 && count($data) === 0) {
    out(['ok'=>true,'page'=>$pageParam,'source_count'=>0,'prepared_rows'=>0,'inserted'=>0,'skipped_existing'=>0,'skipped_invalid'=>0,'total_games'=>(int)$pdo->query('SELECT COUNT(*) FROM gm_games')->fetchColumn(),'categories'=>(int)$pdo->query('SELECT COUNT(*) FROM gm_categories')->fetchColumn(),'feed_url'=>$used,'done'=>true]);
}

/* Normalize and create every source category encountered in the feed. */
$slug=function(string $s): string {
    $s=trim($s);
    $s=preg_replace('/[^\pL\d]+/u','-',$s)??'';
    $a=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s); if(is_string($a)&&$a!=='')$s=$a;
    $s=preg_replace('/[^-\w]+/','',$s)??'';
    $s=trim($s,'-'); $s=preg_replace('/-+/','-',$s)??'';
    $s=strtolower($s);
    return $s!==''?$s:'other';
};
$key=function(string $s): string {
    $s=strtolower(trim(preg_replace('/\s+games?$/i','',preg_replace('/\s+/',' ',$s)??$s)??$s));
    return $s;
};

$catMap=[];
$q=$pdo->query('SELECT id,name,category_pilot FROM gm_categories');
while($row=$q->fetch()) {
    $catMap[$key((string)$row['name'])]=(int)$row['id'];
    $catMap[$key((string)$row['category_pilot'])]=(int)$row['id'];
}

$catInsert=$pdo->prepare('INSERT INTO gm_categories (category_pilot,name,image) VALUES (?,?,?) RETURNING id');
$getCat=$pdo->prepare('SELECT id FROM gm_categories WHERE category_pilot=? LIMIT 1');

foreach($data as $g){
    if(!is_array($g)) continue;
    $raw=trim((string)($g['category']??''));
    $cats=preg_split('/\s*,\s*/',$raw)?:[];
    foreach($cats as $rawCat){
        $rawCat=trim((string)$rawCat);
        if($rawCat==='') continue;
        $ck=$key($rawCat);
        if(isset($catMap[$ck])) continue;
        $name=preg_match('/games?$/i',$rawCat)?$rawCat:$rawCat.' Games';
        $pilot=$slug($name);
        $getCat->execute([$pilot]);
        $id=$getCat->fetchColumn();
        if($id!==false){$catMap[$ck]=(int)$id;continue;}
        $catInsert->execute([$pilot,$name,'/cat/action-games.jpg']);
        $catMap[$ck]=(int)$catInsert->fetchColumn();
    }
}

$existing=[];
$q=$pdo->query('SELECT catalog_id,game_name FROM gm_games');
while($row=$q->fetch()){$existing[(string)$row['catalog_id']]=true;$existing['name:'.(string)$row['game_name']]=true;}

$rows=[]; $seen=[]; $invalid=0; $dupe=0; $now=time();
foreach($data as $g){
    if(!is_array($g)){$invalid++;continue;}
    $id=trim((string)($g['id']??'')); $title=trim((string)($g['title']??'')); $file=trim((string)($g['url']??''));
    if($id===''||$title===''||$file===''){$invalid++;continue;}
    $catalog='gamemonetize-'.$id;
    $gameName=$slug($title);
    if(isset($seen[$id])||isset($existing[$catalog])||isset($existing['name:'.$gameName])){$dupe++;continue;}
    $seen[$id]=1;
    $rawCat=trim((string)($g['category']??''));
    $firstCat=trim((string)(preg_split('/\s*,\s*/',$rawCat)[0]??'Other'));
    $categoryId=$catMap[$key($firstCat)]??null;
    if($categoryId===null){
        $name=preg_match('/games?$/i',$firstCat)?$firstCat:$firstCat.' Games';
        $getCat->execute([$slug($name)]);
        $categoryId=(int)$getCat->fetchColumn();
        if($categoryId<1){$invalid++;continue;}
    }
    $name=mb_substr($title,0,249,'UTF-8');
    $desc=mb_substr(trim((string)($g['description']??'')),0,14990,'UTF-8');
    $inst=mb_substr(trim((string)($g['instructions']??'')),0,590,'UTF-8');
    $img=mb_substr(trim((string)($g['thumb']??'')),0,499,'UTF-8');
    if($img==='')$img='/templates/kizi/image/data-game/default_game-thumb.png';
    $w=max(1,(int)($g['width']??800)); $h=max(1,(int)($g['height']??600));
    $mobile=!empty($g['mobile'])?1:0;
    $rows[]=[
      $catalog,$gameName,$name,$img,$categoryId,$desc,$inst,
      mb_substr($file,0,499,'UTF-8'),'html5',$w,$h,$now,$mobile,'[]','1'
    ];
    $existing[$catalog]=true; $existing['name:'.$gameName]=true;
}

$insert=$pdo->prepare('INSERT INTO gm_games
(catalog_id,game_name,name,image,category,description,instructions,file,game_type,w,h,date_added,mobile,tags_ids,published)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
ON CONFLICT (catalog_id) DO NOTHING');

$inserted=0; $batch=200;
$pdo->beginTransaction();
try {
  foreach(array_chunk($rows,$batch) as $chunk){
    foreach($chunk as $r){$insert->execute($r);$inserted++;}
  }
  $pdo->commit();
  try { $pdo->query('SELECT pg_advisory_unlock(91472027)'); } catch (Throwable $e) {}
  $lockReleased=true;
} catch(Throwable $e) {
  if($pdo->inTransaction())$pdo->rollBack();
  try { $pdo->query('SELECT pg_advisory_unlock(91472027)'); } catch (Throwable $unlockError) {}
  $lockReleased=true;
  out(['ok'=>false,'error'=>'batch_insert_failed','db_error'=>$e->getMessage(),'source_count'=>count($data),'prepared_rows'=>count($rows),'inserted_so_far'=>$inserted],500);
}

$pdo->prepare('UPDATE gm_setting SET custom_game_feed_url=? WHERE id=1')->execute([$used]);
$total=(int)$pdo->query('SELECT COUNT(*) FROM gm_games')->fetchColumn();
$catCount=(int)$pdo->query('SELECT COUNT(*) FROM gm_categories')->fetchColumn();

out(['ok'=>true,'page'=>$pageParam,'source_count'=>count($data),'prepared_rows'=>count($rows),'inserted'=>$inserted,'skipped_existing'=>$dupe,'skipped_invalid'=>$invalid,'total_games'=>$total,'categories'=>$catCount,'feed_url'=>$used,'done'=>($pageParam>0 && count($data)<1000)]);
