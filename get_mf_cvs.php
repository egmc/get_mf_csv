<?php
require __DIR__ . "/vendor/autoload.php";
use Goutte\Client;

$login_url = 'https://moneyforward.com/users/sign_in';

if ($argc < 3) {
	die("usage: php $argv[0] your_id your_password (optional)date[yyyy-mm]" . PHP_EOL);
}

$login_id = $argv[1];
$password = $argv[2];
$datetime = new DateTime();
if (isset($argv[3])) {
	$datetime = new DateTime($argv[3]);
}
$params = [
	'from' => $datetime->format('Y/m') . "/01",
	'month' => $datetime->format('m') ,
	'year' =>  $datetime->format('Y') ,
];

$client = new Client();

$crawler = $client->request('GET', $login_url);

$form = $crawler->selectButton('commit')->form();

$crawler = $client->submit($form, array('user[email]' => $login_id, 'user[password]' => $password));

$query = http_build_query($params);

$client->request('GET', 'https://moneyforward.com/cf/csv?' . $query);

echo $client->getResponse()->getContent();
