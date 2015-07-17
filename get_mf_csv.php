<?php
/**
 * get moneyforward csv data
 *
 * @author egmc
 */
require __DIR__ . "/vendor/autoload.php";
use Goutte\Client;

$login_url = 'https://moneyforward.com/users/sign_in';
$csv_url = 'https://moneyforward.com/cf/csv';

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
$crawler = $client->submit($form, ['sign_in_session_service[email]' => "{$login_id}", 'sign_in_session_service[password]' => "{$password}"]);

$query = http_build_query($params);
$client->followRedirects(false);
$client->request('GET', "$csv_url?$query");
if ($client->getResponse()->getStatus() == "200") {
	echo $client->getResponse()->getContent();
} else {
	die("failed to get csv data" . PHP_EOL);
}