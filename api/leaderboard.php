<?php
/**
 * BigRig Poker — Public Leaderboard API
 * GET /api/leaderboard.php[?limit=25]
 *
 * Returns top players ranked by net chips.
 * Minimum 3 games to qualify.
 */

// ── CORS ─────────────────────────────────────────────────────────────────────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── Config ───────────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'chrisca3_bigrig');
define('DB_USER', 'chrisca3_bigrig');
define('DB_PASS', 'g)VO~~E)gR4An@ql');

// ── DB ───────────────────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// ── Query ────────────────────────────────────────────────────────────────────
try {
    $limit = min((int)($_GET['limit'] ?? 25), 50);

    $stmt = $pdo->prepare(
        'SELECT
            un.full_name,
            COUNT(*) AS games_played,
            SUM(gl.result = "win") AS games_won,
            ROUND(SUM(gl.result = "win") / COUNT(*) * 100, 1) AS win_rate,
            SUM(gl.net_chips) AS total_net_chips,
            MAX(gl.net_chips) AS best_game
         FROM game_log gl
         JOIN user_names un ON gl.alexa_id = un.alexa_id AND un.is_active = 1
         GROUP BY gl.alexa_id, un.full_name
         HAVING games_played >= 3
         ORDER BY total_net_chips DESC
         LIMIT :lim'
    );
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Fill empty slots with fake players ───────────────────────────────
    if (count($rows) < $limit) {
        $cities = [
            'Amarillo', 'Abilene', 'Lubbock', 'Odessa', 'Midland', 'Laredo',
            'El Paso', 'Waco', 'Tyler', 'Beaumont', 'Killeen', 'Temple',
            'Nacogdoches', 'Longview', 'Texarkana', 'Sherman', 'Lufkin',
            'Palestine', 'Corsicana', 'Stephenville', 'Brownwood', 'Jasper',
            'Pecos', 'Alpine', 'Marfa', 'Uvalde', 'Eagle Pass', 'Del Rio',
            'Sweetwater', 'Snyder', 'Breckenridge', 'Mineral Wells',
            'Gatesville', 'Lampasas', 'Llano', 'Fredericksburg', 'Kerrville',
            'Hondo', 'Pearsall', 'Carrizo Springs', 'Crystal City',
            'Sonora', 'Ozona', 'Fort Stockton', 'Monahans', 'Seminole',
            'Levelland', 'Plainview', 'Hereford', 'Dalhart',
        ];
        $suffixes = [
            'Lucky', 'Ace', 'Bluff', 'Shark', 'Wild', 'Slick', 'Boss',
            'King', 'Hustler', 'Maverick', 'Outlaw', 'Gambler', 'Flash',
            'Joker', 'Duke', 'Viper', 'Ghost', 'Bandit', 'Ranger', 'Diesel',
            'Hammer', 'Bullet', 'Spike', 'Cobra', 'Storm', 'Blaze',
        ];

        // Seed RNG by day so fake names stay stable for 24h
        $daySeed = crc32(date('Y-m-d'));
        mt_srand($daySeed);

        // Shuffle both arrays deterministically
        $cityPool   = $cities;
        $suffixPool = $suffixes;
        for ($i = count($cityPool) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$cityPool[$i], $cityPool[$j]] = [$cityPool[$j], $cityPool[$i]];
        }
        for ($i = count($suffixPool) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$suffixPool[$i], $suffixPool[$j]] = [$suffixPool[$j], $suffixPool[$i]];
        }

        // Determine chip floor from last real player (or default)
        $lastRealChips = empty($rows) ? 800 : max(50, (int)end($rows)['total_net_chips'] - 100);

        $needed = $limit - count($rows);
        for ($i = 0; $i < $needed; $i++) {
            $city   = $cityPool[$i % count($cityPool)];
            $suffix = $suffixPool[$i % count($suffixPool)];
            $name   = "$city $suffix";

            $gamesPlayed = mt_rand(3, 20);
            $gamesWon    = mt_rand(1, $gamesPlayed);
            $winRate     = round($gamesWon / $gamesPlayed * 100, 1);
            $netChips    = max(10, $lastRealChips - ($i * mt_rand(20, 80)));
            $bestGame    = mt_rand((int)($netChips * 0.3), (int)($netChips * 0.8));

            $rows[] = [
                'full_name'       => $name,
                'games_played'    => (string)$gamesPlayed,
                'games_won'       => (string)$gamesWon,
                'win_rate'        => (string)$winRate,
                'total_net_chips' => (string)$netChips,
                'best_game'       => (string)max(50, $bestGame),
            ];
        }
    }

    echo json_encode(['leaderboard' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch leaderboard']);
}
