<?php
/**
 * store moneyforward csv data to specified db
 *
 * @author egmc
 */
require __DIR__ . "/vendor/autoload.php";
use Symfony\Component\Yaml\Yaml;

$db_config = Yaml::parse(file_get_contents(__DIR__ . "/conf/db.yaml"));
$mf_config = Yaml::parse(file_get_contents(__DIR__ . "/conf/mf.yaml"));


ORM::configure("mysql:host={$db_config['mysql']['host']};dbname={$db_config['mysql']['db']}");
ORM::configure('username', $db_config['mysql']['user']);
if (isset($db_config['mysql']['pass'])) {
    ORM::configure('password', $db_config['mysql']['pass']);
}
ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);
//ORM::configure('error_mode', PDO::ERRMODE_SILENT);

$login_id = $mf_config['mf']['user'];
$password = $mf_config['mf']['pass'];
$datetime = new DateTime();
if (isset($argv[1])) {
    $datetime = new DateTime($argv[1]);
}
$now = new DateTime();

$csv_data = shell_exec("php get_mf_csv.php {$login_id} {$password} {$datetime->format('Y-m')}");
$csv_data = mb_convert_encoding($csv_data, 'utf8', 'sjis-win');
$csv_list = explode("\n", str_replace(array("\r\n", "\r", "\n"), "\n", $csv_data));
//var_dump($csv_list);
$cols = ['calc', 'adate', 'note', 'amnt', 'serv', 'lctg', 'mctg', 'memo', 'transfer', 'mfid'];
array_shift($csv_list);
foreach ($csv_list as $row) {
    if (!$row) {
        continue;
    }
    $crec = str_getcsv($row);
    if (count($crec) !== count($cols)) {
        echo "cols count not match : {$row}";
        continue;
    }
    $drec = array_combine($cols, $crec);

    $orm = ORM::for_table('account_book');
    $dobj = $orm->whereEqual('mfid', $drec['mfid'])->findOne();
    if ($dobj) {
        $updated = false;
        foreach ($cols as $k) {
            if ($k == 'adate') {
                continue;
            }
            if ($dobj[$k] != $drec[$k]) {
                echo "$k is updated\n";
                $updated = true;
                break;
            }
        }
        if ($updated) {
            $dobj->set($drec);
            $dobj->set('updated', $now->format('Y-m-d H:i:s'));
            $dobj->save();
        }
    } else {
        $account = $orm->create();
        $account->set($drec);
        $account->set('updated', $now->format('Y-m-d H:i:s'));
        $account->set(['created' => $now->format('Y-m-d H:i:s')]);
        $account->save();
    }

}
