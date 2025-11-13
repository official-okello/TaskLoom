<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$db = (new Dbh())->connect();
$updates = [
    ['desc' => 'completed-like -> done', 'sql' => "UPDATE tasks SET status='done' WHERE LOWER(status) IN ('completed','complete','finished')"],
    ['desc' => 'pending-like -> open', 'sql' => "UPDATE tasks SET status='open' WHERE LOWER(status) IN ('pending','notdone','todo','to do','')"],
    ['desc' => 'NULL -> open', 'sql' => "UPDATE tasks SET status='open' WHERE status IS NULL"],
    ['desc' => 'others -> open', 'sql' => "UPDATE tasks SET status='open' WHERE LOWER(COALESCE(status,'')) NOT IN ('open','done')"]
];
foreach ($updates as $u) {
    try {
        $affected = $db->exec($u['sql']);
        echo $u['desc'] . ': ' . (int)$affected . " rows affected\n";
    } catch (PDOException $e) {
        echo $u['desc'] . ': ERROR - ' . $e->getMessage() . "\n";
    }
}

// Show final distribution
$rows = $db->query("SELECT LOWER(COALESCE(status,'(NULL)')) as s, COUNT(*) as c FROM tasks GROUP BY LOWER(COALESCE(status,'(NULL)'))")->fetchAll(PDO::FETCH_ASSOC);
echo "Final status distribution:\n";
foreach ($rows as $r) echo $r['s'] . "\t" . $r['c'] . "\n";
