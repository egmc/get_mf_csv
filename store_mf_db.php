<?php
/**
 * store moneyforward csv data to specified db
 *
 * @author egmc
 */
require __DIR__ . "/vendor/autoload.php";
use Symfony\Component\Yaml\Yaml;

$db_config = Yaml::parseFile(__DIR__ . "/conf/db.yaml");
$mf_config = Yaml::parseFile(__DIR__ . "/conf/mf.yaml");


ORM::configure("mysql:host={$db_config['mysql']['host']};dbname={$db_config['mysql']['db']}");
ORM::configure('username', $db_config['mysql']['user']);
if (isset($db_config['mysql']['pass'])) {
    ORM::configure('password', $db_config['mysql']['pass']);
}
//ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);
ORM::configure('error_mode', PDO::ERRMODE_SILENT);

$login_id = $mf_config['mf']['user'];
$password = $mf_config['mf']['pass'];
$datetime = new DateTime();
if (isset($argv[1])) {
    $datetime = new DateTime($argv[1]);
}

$csv_data = shell_exec("php get_mf_csv.php {$login_id} {$password} {$datetime->format('Y-m')}");
$csv_data = mb_convert_encoding($csv_data, 'utf8', 'sjis-win');
//var_dump($csv_data);
$csv_list = explode("\n", str_replace(array("\r\n", "\r", "\n"), "\n", $csv_data));
var_dump($csv_list);
$rows = ['calc', 'adate', 'note', 'amnt', 'serv', 'lctg', 'mctg', 'memo', 'transfer', 'mfid'];
array_shift($csv_list);
array_pop($csv_list);
foreach ($csv_list as $row) {
    $crec = str_getcsv($row);
    $drec = array_combine($rows, $crec);
    $account = ORM::for_table('account_book')->create();
    $account->set($drec);
    $account->set(['created' => (new DateTime())->format('Y-m-d H:i:s')]);
    $account->save();
}
