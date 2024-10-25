<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../class/absensi.php';

$database = new Database();
$db = $database->getConnection();

$item = new Absensi($db);
$item->uid = isset($_GET['uid']) ? $_GET['uid'] : die('wrong structure!');

// Panggil fungsi isCardRegistered
$isRegistered = $item->isCardRegistered();

if ($isRegistered) {
    // Kartu terdaftar, kirim respons berhasil
    $response = array("status" => "success", "message" => "Kartu terdaftar.");
    http_response_code(200);
    echo json_encode($response);
} else {
    // Kartu tidak terdaftar, kirim respons gagal
    $response = array("status" => "error", "message" => "Kartu tidak terdaftar.");
    http_response_code(404);
    echo json_encode($response);
}
?>
