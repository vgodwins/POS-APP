<?php
declare(strict_types=1);

namespace App\Core {
    class Request {
        public string $method;
        public string $path;
        public array $query;
        public array $body;
        public array $files;
        public array $server;

        public static function capture(): self {
            $r = new self();
            $r->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $r->path = parse_url($uri, PHP_URL_PATH) ?: '/';
            $r->query = $_GET;
            $r->body = $_POST;
            $r->files = $_FILES;
            $r->server = $_SERVER;
            return $r;
        }
    }

    class Response {
        public static function json(array $data, int $status = 200): void {
            http_response_code($status);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }
        public static function redirect(string $url): void {
            header('Location: ' . $url);
            exit;
        }
    }

    class Router {
        private array $routes = [];

        public function get(string $path, callable|array $handler): void {
            $this->routes['GET'][$path] = $handler;
        }
        public function post(string $path, callable|array $handler): void {
            $this->routes['POST'][$path] = $handler;
        }
        public function any(string $path, callable|array $handler): void {
            $this->routes['GET'][$path] = $handler;
            $this->routes['POST'][$path] = $handler;
        }
        public function dispatch(Request $request): void {
            $path = rtrim($request->path, '/') ?: '/';
            $method = $request->method;
            $handler = $this->routes[$method][$path] ?? null;
            if (!$handler) {
                http_response_code(404);
                echo '404 Not Found';
                return;
            }
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $instance = new $class();
                $instance->$action($request);
            } else {
                $handler($request);
            }
        }
    }

    class DB {
        private static ?\PDO $pdo = null;
        public static function conn(): \PDO {
            if (self::$pdo === null) {
                $cfg = \App\Core\Config::get('db');
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['database']);
                self::$pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
            }
            return self::$pdo;
        }
    }

    class Config {
        private static array $cfg = [];
        public static function init(array $cfg): void { self::$cfg = $cfg; }
        public static function get(string $key, $default = null) {
            return self::$cfg[$key] ?? $default;
        }
    }

    class Auth {
        public static function user(): ?array {
            return $_SESSION['user'] ?? null;
        }
        public static function check(): bool { return isset($_SESSION['user']); }
        public static function login(array $user): void { $_SESSION['user'] = $user; }
        public static function logout(): void { unset($_SESSION['user']); }
        public static function hasRole(string $role): bool {
            $u = self::user();
            if (!$u) return false;
            return in_array($role, $u['roles'] ?? [], true);
        }
        public static function effectiveStoreId(): ?int {
            $u = self::user();
            if (!$u) return null;
            // Admin can switch context to view another store
            if (self::hasRole('admin') && isset($_SESSION['admin_view_store_id']) && (int)$_SESSION['admin_view_store_id'] > 0) {
                return (int)$_SESSION['admin_view_store_id'];
            }
            return isset($u['store_id']) ? (int)$u['store_id'] : null;
        }
        public static function isWriteLocked(?int $storeId): bool {
            if (!$storeId) return false;
            try {
                $pdo = DB::conn();
                $st = $pdo->prepare('SELECT locked, active_hours_start, active_hours_end FROM stores WHERE id = ?');
                $st->execute([$storeId]);
                $row = $st->fetch();
                if (!$row) return false;
                $locked = (int)($row['locked'] ?? 0) === 1;
                if ($locked) return true;
                $start = $row['active_hours_start'] ?? null;
                $end = $row['active_hours_end'] ?? null;
                if ($start && $end) {
                    // Compare in store's local time; assume server time for simplicity
                    $now = strtotime(date('H:i'));
                    $s = strtotime($start);
                    $e = strtotime($end);
                    if ($s === false || $e === false) { return false; }
                    // Handle overnight windows (e.g., 20:00 - 06:00)
                    $inWindow = $s <= $e ? ($now >= $s && $now <= $e) : ($now >= $s || $now <= $e);
                    return !$inWindow; // locked when outside active window
                }
            } catch (\Throwable $e) { /* ignore DB errors */ }
            return false;
        }
    }
}

namespace {
    // Simple PSR-4 like autoloader for App\\ namespace
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });

    // Initialize config
    \App\Core\Config::init($config ?? []);

    // Global helpers
    function view(string $template, array $data = []): void {
        extract($data);
        $viewFile = __DIR__ . '/Views/' . $template . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($template);
            return;
        }
        include __DIR__ . '/Views/layouts/main.php';
    }

    function csrf_token(): string {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf'];
    }

    function verify_csrf(?string $token): bool {
        return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token);
    }
}
