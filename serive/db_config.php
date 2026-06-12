<?php
/**
 * Database Configuration with Connection Pooling
 * Provides resilient database connections with retry logic and shared pooling
 * to guard against "Too many connections" errors.
 */

define('DB_HOST', 'localhost');
define('DB_USERNAME', 'josh296689_max');
define('DB_PASSWORD', 'josh296689_max');
define('DB_NAME', 'josh296689_max');
define('DB_MAX_RETRIES', 3);
define('DB_RETRY_DELAY', 2);

define('DB_DEFAULT_PORT', 3306);
define('DB_DEFAULT_CHARSET', 'utf8mb4');

$GLOBALS['__db_connection_pool'] = $GLOBALS['__db_connection_pool'] ?? [];
$GLOBALS['__db_connection_meta'] = $GLOBALS['__db_connection_meta'] ?? [];

/**
 * Merge overrides with the default connection parameters.
 *
 * @param array $overrides
 * @return array
 */
function normalizeDatabaseConfig(array $overrides = []): array
{
    $defaults = [
        'host' => DB_HOST,
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'database' => DB_NAME,
        'port' => DB_DEFAULT_PORT,
        'socket' => null,
        'charset' => DB_DEFAULT_CHARSET,
    ];

    return array_merge($defaults, array_change_key_case($overrides, CASE_LOWER));
}

/**
 * Produce a repeatable hash for cached connections.
 *
 * @param array $config
 * @return string
 */
function getDatabasePoolKey(array $config): string
{
    return hash('sha256', implode('|', [
        $config['host'] ?? '',
        $config['username'] ?? '',
        $config['database'] ?? '',
        $config['port'] ?? '',
        $config['socket'] ?? '',
    ]));
}

/**
 * Retrieve a mysqli connection with retry logic and pooling.
 *
 * The first parameter is backwards compatible with the previous signature.
 * Passing an integer treats it as the retry count, while arrays are interpreted
 * as connection overrides (host, username, password, database, port, socket).
 *
 * @param array|int $optionsOrRetries
 * @param int|null  $maxRetries
 * @return mysqli|false
 */
function getDatabaseConnection($optionsOrRetries = [], ?int $maxRetries = null)
{
    global $__db_connection_pool, $__db_connection_meta;

    if (is_int($optionsOrRetries)) {
        $maxRetries = $optionsOrRetries;
        $optionsOrRetries = [];
    }

    $config = normalizeDatabaseConfig(is_array($optionsOrRetries) ? $optionsOrRetries : []);
    $maxRetries = $maxRetries ?? DB_MAX_RETRIES;
    $poolKey = getDatabasePoolKey($config);

    if (isset($__db_connection_pool[$poolKey]) && $__db_connection_pool[$poolKey] instanceof mysqli) {
        $existing = $__db_connection_pool[$poolKey];
        if (@mysqli_ping($existing)) {
            return $existing;
        }

        mysqli_close($existing);
        unset($__db_connection_pool[$poolKey], $__db_connection_meta[$poolKey]);
    }

    $retryCount = 0;

    while ($retryCount < $maxRetries) {
        try {
            $conn = mysqli_init();

            if (
                !mysqli_real_connect(
                    $conn,
                    $config['host'],
                    $config['username'],
                    $config['password'],
                    $config['database'],
                    (int) $config['port'],
                    $config['socket'] ?: null
                )
            ) {
                throw new RuntimeException(mysqli_connect_error());
            }

            mysqli_set_charset($conn, $config['charset']);
            mysqli_query($conn, "SET SESSION wait_timeout=300");
            mysqli_query($conn, "SET SESSION interactive_timeout=300");
            mysqli_query($conn, "SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");

            $__db_connection_pool[$poolKey] = $conn;
            $__db_connection_meta[$poolKey] = $config;

            return $conn;
        } catch (Throwable $e) {
            $retry = $retryCount + 1;
            error_log("Database connection attempt {$retry} failed: " . $e->getMessage());
        }

        $retryCount++;

        if ($retryCount < $maxRetries) {
            sleep((int) pow(DB_RETRY_DELAY, $retryCount));
        }
    }

    error_log("Failed to connect to database after {$maxRetries} attempts");
    return false;
}

/**
 * Close one or all pooled mysqli connections.
 *
 * @param mysqli|null $connection
 * @return void
 */
function closeDatabaseConnection($connection = null): void
{
    global $__db_connection_pool, $__db_connection_meta;

    if (!is_array($__db_connection_pool)) {
        $__db_connection_pool = [];
    }

    if ($connection === null) {
        foreach ($__db_connection_pool as $key => $pooled) {
            if ($pooled instanceof mysqli) {
                mysqli_close($pooled);
            }
            unset($__db_connection_pool[$key], $__db_connection_meta[$key]);
        }

        return;
    }

    foreach ($__db_connection_pool as $key => $pooled) {
        if ($pooled === $connection) {
            if ($pooled instanceof mysqli) {
                mysqli_close($pooled);
            }

            unset($__db_connection_pool[$key], $__db_connection_meta[$key]);
            return;
        }
    }

    if ($connection instanceof mysqli) {
        mysqli_close($connection);
    }
}

/**
 * Execute a SQL query with error reporting.
 *
 * @param string            $query
 * @param mysqli|array|null $connection Optional connection or override config
 * @return mysqli_result|false
 */
function executeQuery(string $query, $connection = null)
{
    if (is_array($connection)) {
        $connection = getDatabaseConnection($connection);
    }

    $connection = $connection ?? getDatabaseConnection();

    if (!$connection instanceof mysqli) {
        error_log('Cannot execute query: No database connection');
        return false;
    }

    $result = mysqli_query($connection, $query);

    if (!$result) {
        error_log('Query failed: ' . mysqli_error($connection) . ' - Query: ' . $query);
    }

    return $result;
}

/**
 * Fetch a single row helper.
 *
 * @param string            $query
 * @param mysqli|array|null $connection
 * @return array|false
 */
function getSingleRow(string $query, $connection = null)
{
    $result = executeQuery($query, $connection);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return false;
}

/**
 * Fetch all rows helper.
 *
 * @param string            $query
 * @param mysqli|array|null $connection
 * @return array
 */
function getMultipleRows(string $query, $connection = null): array
{
    $result = executeQuery($query, $connection);
    $rows = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    return $rows;
}

/**
 * Expose the default credentials in the legacy array shape used by
 * DataTables utilities and older scripts.
 *
 * @return array{host:string,user:string,pass:string,db:string,port:int,charset:string}
 */
function getDatabaseCredentials(): array
{
    $config = normalizeDatabaseConfig();

    return [
        'host' => $config['host'],
        'user' => $config['username'],
        'pass' => $config['password'],
        'db' => $config['database'],
        'port' => (int) $config['port'],
        'charset' => $config['charset'],
    ];
}

date_default_timezone_set('Asia/Kolkata');

$conn = getDatabaseConnection();

register_shutdown_function('closeDatabaseConnection');

?>