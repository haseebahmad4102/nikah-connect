<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    public $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die('<div style="font-family:sans-serif;color:#c0392b;padding:20px;background:#fdf0f0;border:1px solid #e74c3c;margin:20px;border-radius:8px;">
                <h3>Database Connection Failed</h3>
                <p>Please make sure XAMPP MySQL is running and the database <strong>nikahconnect</strong> exists.</p>
                <p>Error: ' . $this->conn->connect_error . '</p>
                <p><a href="http://localhost/phpmyadmin" target="_blank">Open phpMyAdmin</a> and import <strong>database.sql</strong></p>
            </div>');
        }
        $this->conn->set_charset('utf8mb4');
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query($sql, $params = [], $types = '') {
        if (empty($params)) {
            return $this->conn->query($sql);
        }
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function fetchOne($sql, $params = [], $types = '') {
        $result = $this->query($sql, $params, $types);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function fetchAll($sql, $params = [], $types = '') {
        $result = $this->query($sql, $params, $types);
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function insert($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        if (!empty($params)) {
            if (empty($types)) $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function execute($sql, $params = [], $types = '') {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        if (!empty($params)) {
            if (empty($types)) $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        return $stmt->execute();
    }

    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }
}

function db() {
    return Database::getInstance();
}

// Auth helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return db()->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

// ---- Guardian (Parent) helpers ----

// Is the currently logged-in account a parent/guardian account?
function isParent() {
    return isLoggedIn() && (($_SESSION['role'] ?? '') === 'parent');
}

function requireParent() {
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'parent') {
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

// The child (boy/girl) user id that a parent account guards
function guardedChildId($parentUserId) {
    $row = db()->fetchOne("SELECT child_id FROM users WHERE id = ? AND role = 'parent'", [$parentUserId]);
    return $row ? (int)$row['child_id'] : 0;
}

// The parent (guardian) account row for a given child user, or null
function getGuardian($childUserId) {
    return db()->fetchOne("SELECT * FROM users WHERE role = 'parent' AND child_id = ? LIMIT 1", [$childUserId]);
}

// Send a notification to a child's guardian (if one exists)
function notifyGuardian($childUserId, $type, $message, $link = '') {
    $g = getGuardian($childUserId);
    if ($g) addNotification($g['id'], $type, $message, $link);
}

function addNotification($userId, $type, $message, $link = '') {
    db()->insert(
        "INSERT INTO notifications (user_id, type, message, link) VALUES (?,?,?,?)",
        [$userId, $type, $message, $link]
    );
}

function getUnreadNotifications($userId) {
    return db()->fetchAll(
        "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10",
        [$userId]
    );
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hr ago';
    if ($time < 604800) return floor($time/86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}
