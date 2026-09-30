<?php
declare(strict_types=1);

function gps_feature_migration_db(): mysqli
{
    global $GameMonetizeConnect;
    if (!isset($GameMonetizeConnect) || !($GameMonetizeConnect instanceof mysqli) || $GameMonetizeConnect->connect_errno) {
        throw new RuntimeException('The CMS database connection is unavailable.');
    }
    return $GameMonetizeConnect;
}

function gps_feature_migration_identifier(string $identifier): string
{
    $identifier = trim($identifier, "` \t\r\n");
    if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $identifier)) {
        throw new RuntimeException('The migration contains an invalid database identifier.');
    }
    return $identifier;
}

function gps_feature_migration_split(string $sql): array
{
    if ($sql === '' || strlen($sql) > 2097152 || preg_match('/^\s*DELIMITER\b/im', $sql)) {
        throw new RuntimeException('The database migration is empty, too large, or uses an unsupported delimiter.');
    }
    $statements = [];
    $buffer = '';
    $quote = '';
    $lineComment = false;
    $blockComment = false;
    $length = strlen($sql);
    for ($index = 0; $index < $length; $index++) {
        $char = $sql[$index];
        $next = $index + 1 < $length ? $sql[$index + 1] : '';
        if ($lineComment) {
            if ($char === "\n") {
                $lineComment = false;
                $buffer .= ' ';
            }
            continue;
        }
        if ($blockComment) {
            if ($char === '*' && $next === '/') {
                $blockComment = false;
                $index++;
                $buffer .= ' ';
            }
            continue;
        }
        if ($quote === '') {
            if (($char === '-' && $next === '-' && ($index + 2 >= $length || ctype_space($sql[$index + 2]))) || $char === '#') {
                $lineComment = true;
                if ($char === '-') {
                    $index++;
                }
                continue;
            }
            if ($char === '/' && $next === '*') {
                $blockComment = true;
                $index++;
                continue;
            }
            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
                continue;
            }
            if ($char === ';') {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }
        } else {
            if ($char === '\\' && $quote !== '`' && $next !== '') {
                $buffer .= $char . $next;
                $index++;
                continue;
            }
            if ($char === $quote) {
                if ($next === $quote) {
                    $buffer .= $char . $next;
                    $index++;
                    continue;
                }
                $quote = '';
            }
        }
        $buffer .= $char;
    }
    if ($quote !== '' || $blockComment) {
        throw new RuntimeException('The database migration contains an unterminated string or comment.');
    }
    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }
    if ($statements === [] || count($statements) > 100) {
        throw new RuntimeException('The database migration must contain between 1 and 100 statements.');
    }
    return $statements;
}

function gps_feature_migration_plan(array $statements): array
{
    $plan = [];
    $dataStarted = false;
    foreach ($statements as $statement) {
        $compact = preg_replace('/\s+/', ' ', trim((string)$statement));
        if (preg_match('/^(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\b/i', $compact)) {
            $dataStarted = true;
            $plan[] = ['type' => 'data', 'sql' => $statement];
            continue;
        }
        if ($dataStarted) {
            throw new RuntimeException('Schema statements must appear before data statements in a feature migration.');
        }
        if (preg_match('/^CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?([A-Za-z0-9_]+)`?/i', $compact, $match)) {
            $plan[] = ['type' => 'create_table', 'table' => gps_feature_migration_identifier($match[1]), 'sql' => $statement];
            continue;
        }
        if (preg_match('/^ALTER\s+TABLE\s+`?([A-Za-z0-9_]+)`?\s+ADD\s+(?:UNIQUE\s+)?(?:INDEX|KEY)\s+`?([A-Za-z0-9_]+)`?/i', $compact, $match)
            && !preg_match('/\b(DROP|RENAME|CHANGE|MODIFY|CONVERT|DISABLE|ENABLE|DISCARD|IMPORT|EXCHANGE|TRUNCATE|CONSTRAINT|FOREIGN|PRIMARY|CHECK|PARTITION)\b/i', $compact)
            && !preg_match('/,\s*ADD\b/i', $compact)) {
            $plan[] = ['type' => 'add_index', 'table' => gps_feature_migration_identifier($match[1]), 'name' => gps_feature_migration_identifier($match[2]), 'sql' => $statement];
            continue;
        }
        if (preg_match('/^ALTER\s+TABLE\s+`?([A-Za-z0-9_]+)`?\s+ADD\s+(?:COLUMN\s+)?(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z0-9_]+)`?/i', $compact, $match)
            && !preg_match('/\b(DROP|RENAME|CHANGE|MODIFY|CONVERT|DISABLE|ENABLE|DISCARD|IMPORT|EXCHANGE|TRUNCATE|CONSTRAINT|FOREIGN|PRIMARY|UNIQUE|FULLTEXT|SPATIAL|CHECK|PARTITION)\b/i', $compact)
            && !preg_match('/,\s*ADD\b/i', $compact)) {
            $plan[] = ['type' => 'add_column', 'table' => gps_feature_migration_identifier($match[1]), 'name' => gps_feature_migration_identifier($match[2]), 'sql' => $statement];
            continue;
        }
        if (preg_match('/^CREATE\s+(?:UNIQUE\s+)?INDEX\s+`?([A-Za-z0-9_]+)`?\s+ON\s+`?([A-Za-z0-9_]+)`?/i', $compact, $match)) {
            $plan[] = ['type' => 'add_index', 'table' => gps_feature_migration_identifier($match[2]), 'name' => gps_feature_migration_identifier($match[1]), 'sql' => $statement];
            continue;
        }
        throw new RuntimeException('Unsupported migration statement. Allowed: CREATE TABLE IF NOT EXISTS, one ADD COLUMN/INDEX per ALTER, CREATE INDEX, INSERT, UPDATE and DELETE.');
    }
    return $plan;
}

function gps_feature_migration_table_exists(mysqli $db, string $table): bool
{
    $escaped = $db->real_escape_string($table);
    $result = $db->query("SHOW TABLES LIKE '{$escaped}'");
    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function gps_feature_migration_column_exists(mysqli $db, string $table, string $column): bool
{
    $escaped = $db->real_escape_string($column);
    $result = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$escaped}'");
    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function gps_feature_migration_index_exists(mysqli $db, string $table, string $index): bool
{
    $escaped = $db->real_escape_string($index);
    $result = $db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$escaped}'");
    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function gps_feature_migration_query(mysqli $db, string $sql): void
{
    if ($db->query($sql) !== true) {
        throw new RuntimeException('Database migration failed: ' . $db->error);
    }
}

function gps_feature_migration_preserved_counts(mysqli $db): array
{
    $tables = [];
    foreach (['GAMES', 'CATEGORIES', 'TAGS', 'BLOGS', 'ACCOUNTS', 'USERS', 'USER_GAME', 'SLIDERS', 'SIDEBAR', 'FOOTER_DESCRIPTION'] as $constant) {
        if (!defined($constant)) {
            continue;
        }
        $table = gps_feature_migration_identifier((string)constant($constant));
        if (!in_array($table, $tables, true) && gps_feature_migration_table_exists($db, $table)) {
            $tables[] = $table;
        }
    }

    $counts = [];
    foreach ($tables as $table) {
        $result = $db->query('SELECT COUNT(*) AS total FROM `' . $table . '`');
        if (!($result instanceof mysqli_result)) {
            throw new RuntimeException('Could not verify existing Free database content before the PRO upgrade.');
        }
        $row = $result->fetch_assoc();
        $counts[$table] = max(0, (int)($row['total'] ?? 0));
    }
    return $counts;
}

function gps_feature_migration_assert_preserved_counts(mysqli $db, array $before): array
{
    $after = [];
    foreach ($before as $table => $minimum) {
        $table = gps_feature_migration_identifier((string)$table);
        if (!gps_feature_migration_table_exists($db, $table)) {
            throw new RuntimeException('PRO upgrade refused: an existing Free database table disappeared.');
        }
        $result = $db->query('SELECT COUNT(*) AS total FROM `' . $table . '`');
        $row = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        $count = max(0, (int)($row['total'] ?? -1));
        if ($row === null || $count < (int)$minimum) {
            throw new RuntimeException('PRO upgrade refused: existing Free content would be removed from ' . $table . '.');
        }
        $after[$table] = $count;
    }
    return $after;
}

function gps_feature_migration_rollback_schema(mysqli $db, array $rollback): void
{
    foreach (array_reverse($rollback) as $action) {
        if ($action['type'] === 'drop_table') {
            @$db->query('DROP TABLE IF EXISTS `' . $action['table'] . '`');
        } elseif ($action['type'] === 'drop_column' && gps_feature_migration_column_exists($db, $action['table'], $action['name'])) {
            @$db->query('ALTER TABLE `' . $action['table'] . '` DROP COLUMN `' . $action['name'] . '`');
        } elseif ($action['type'] === 'drop_index' && gps_feature_migration_index_exists($db, $action['table'], $action['name'])) {
            @$db->query('ALTER TABLE `' . $action['table'] . '` DROP INDEX `' . $action['name'] . '`');
        }
    }
}

function gps_feature_run_database_migration(string $slug, string $version, string $migrationFile): array
{
    $sql = is_file($migrationFile) ? file_get_contents($migrationFile) : false;
    if (!is_string($sql)) {
        throw new RuntimeException('The signed database migration file is missing.');
    }
    $migrationChecksum = hash('sha256', $sql);
    $plan = gps_feature_migration_plan(gps_feature_migration_split($sql));
    $db = gps_feature_migration_db();
    $preservedBefore = gps_feature_migration_preserved_counts($db);
    gps_feature_migration_query($db, "CREATE TABLE IF NOT EXISTS `gm_pro_feature_migrations` (`slug` varchar(64) NOT NULL, `version` varchar(32) NOT NULL, `checksum` char(64) NOT NULL, `applied_at` datetime NOT NULL, PRIMARY KEY (`slug`,`version`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $lockName = 'gps_feature_migration_' . substr(hash('sha256', $slug), 0, 24);
    $lockEscaped = $db->real_escape_string($lockName);
    $lockResult = $db->query("SELECT GET_LOCK('{$lockEscaped}', 15) AS acquired");
    $lockRow = $lockResult instanceof mysqli_result ? $lockResult->fetch_assoc() : null;
    if ((int)($lockRow['acquired'] ?? 0) !== 1) {
        throw new RuntimeException('Another PRO database migration is currently running.');
    }

    $rollback = [];
    $transaction = false;
    try {
        $check = $db->prepare('SELECT checksum FROM gm_pro_feature_migrations WHERE slug = ? AND version = ? LIMIT 1');
        if (!$check) {
            throw new RuntimeException('Could not prepare the migration history check.');
        }
        $check->bind_param('ss', $slug, $version);
        $check->execute();
        $existingChecksum = null;
        $check->bind_result($existingChecksum);
        $hasExisting = $check->fetch();
        $check->close();
        if ($hasExisting) {
            if (!hash_equals((string)$existingChecksum, $migrationChecksum)) {
                throw new RuntimeException('This feature version already used a different database migration. Publish a new version.');
            }
            return ['status' => 'already_applied', 'checksum' => $migrationChecksum, 'preserved_rows' => $preservedBefore];
        }

        foreach ($plan as $step) {
            if ($step['type'] === 'data') {
                if (!$transaction) {
                    if (!$db->begin_transaction()) {
                        throw new RuntimeException('Could not start the database migration transaction.');
                    }
                    $transaction = true;
                }
                gps_feature_migration_query($db, $step['sql']);
                continue;
            }
            if ($step['type'] === 'create_table') {
                $existed = gps_feature_migration_table_exists($db, $step['table']);
                gps_feature_migration_query($db, $step['sql']);
                if (!$existed) {
                    $rollback[] = ['type' => 'drop_table', 'table' => $step['table']];
                }
            } elseif ($step['type'] === 'add_column') {
                $existed = gps_feature_migration_column_exists($db, $step['table'], $step['name']);
                gps_feature_migration_query($db, $step['sql']);
                if (!$existed) {
                    $rollback[] = ['type' => 'drop_column', 'table' => $step['table'], 'name' => $step['name']];
                }
            } elseif ($step['type'] === 'add_index') {
                $existed = gps_feature_migration_index_exists($db, $step['table'], $step['name']);
                gps_feature_migration_query($db, $step['sql']);
                if (!$existed) {
                    $rollback[] = ['type' => 'drop_index', 'table' => $step['table'], 'name' => $step['name']];
                }
            }
        }

        if (!$transaction && !$db->begin_transaction()) {
            throw new RuntimeException('Could not start the migration history transaction.');
        }
        $transaction = true;
        $record = $db->prepare('INSERT INTO gm_pro_feature_migrations (slug, version, checksum, applied_at) VALUES (?, ?, ?, UTC_TIMESTAMP())');
        if (!$record) {
            throw new RuntimeException('Could not prepare the migration history record.');
        }
        $record->bind_param('sss', $slug, $version, $migrationChecksum);
        if (!$record->execute()) {
            throw new RuntimeException('Could not record the completed database migration.');
        }
        $record->close();
        $preservedAfter = gps_feature_migration_assert_preserved_counts($db, $preservedBefore);
        if (!$db->commit()) {
            throw new RuntimeException('Could not commit the database migration.');
        }
        $transaction = false;
        return ['status' => 'applied', 'checksum' => $migrationChecksum, 'preserved_rows' => $preservedAfter];
    } catch (Throwable $error) {
        if ($transaction) {
            @$db->rollback();
        }
        gps_feature_migration_rollback_schema($db, $rollback);
        throw $error;
    } finally {
        @$db->query("SELECT RELEASE_LOCK('{$lockEscaped}')");
    }
}
