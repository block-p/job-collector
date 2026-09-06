#!/bin/sh
# Shared entrypoint for web + queue services.
set -e

test -f .env || cp .env.example .env

# The `.env` FILE is the source of truth at request time: `php artisan serve`
# (cli-server SAPI) does not expose process environment variables to handled
# requests (artisan CLI itself is unaffected). So mirror every key already
# present in the file from the container environment (which comes from the
# host `.env` via compose `env_file` plus service `environment:` overrides).
php -r '
$path = ".env";
$lines = file($path, FILE_IGNORE_NEW_LINES);
if ($lines === false) { fwrite(STDERR, "cannot read .env\n"); exit(1); }
// Keys the container is allowed to introduce into the file when missing
// (e.g. DB_DATABASE is commented out in .env.example, so it has no line
// to update). Everything else is only ever updated in place, never added.
$allowAdd = ["APP_KEY","APP_URL","APP_ENV","APP_DEBUG","DB_CONNECTION","DB_DATABASE","LOG_CHANNEL","LOG_STACK","QUEUE_CONNECTION","CACHE_STORE","SESSION_DRIVER"];
$out = [];
foreach ($lines as $line) {
    if (preg_match("/^([A-Za-z_][A-Za-z0-9_]*)=/", $line, $m)) {
        $v = getenv($m[1]);
        if ($v !== false && $v !== "") { $line = $m[1]."=".$v; }
    }
    $out[] = $line;
}
$content = implode("\n", $out)."\n";
foreach ($allowAdd as $k) {
    $v = getenv($k);
    if ($v !== false && $v !== "" && !preg_match("/^{$k}=/m", $content)) {
        $content .= "{$k}={$v}\n";
    }
}
file_put_contents($path, $content);
$e = $content;
if (!preg_match("/^APP_KEY=.+/m", $e)) {
    $k = "base64:".base64_encode(random_bytes(32));
    file_put_contents($path, preg_replace("/^APP_KEY=.*/m", "APP_KEY=".$k, $e));
    fwrite(STDERR, "generated APP_KEY in .env\n");
}'

if [ "${RUN_MIGRATIONS:-}" = "1" ]; then
    touch "${DB_DATABASE:-database/database.sqlite}"
    php artisan migrate --force
    php artisan db:seed --force
    php artisan storage:link || true
fi

exec "$@"
