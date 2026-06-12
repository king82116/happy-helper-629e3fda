<?php
// Set the Content-Type header to application/json
header('Content-Type: application/json');

// Your JSON data as a PHP array
$data = array(
    "data" => [
        [
            "vendorCode" => "JILI",
            "sort" => 90,
            "childList" => [
                [
                    "gameID" => "72ce7e04ce95ee94eef172c0dfd6dc17",
                    "gameNameEn" => "Mines",
                    "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/229.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "edef29b5eda8e2eaf721d7315491c51d",
                    "gameNameEn" => "Go Rush",
                    "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/224.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "db249defce63610fccabfa829a405232",
                    "gameNameEn" => "Money Coming",
                    "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/51.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "a990de177577a2e6a889aaac5f57b429",
                    "gameNameEn" => "Fortune Gems",
                    "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/109.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "8e939551b9e785001fcb5b0a32f88aba",
                    "gameNameEn" => "Tower",
                    "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/232.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "664fba4da609ee82b78820b1f570f4ad",
                    "gameNameEn" => "Fortune Gems 2",
                    "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/223.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "e5091890bbb65a5f9ceb657351fa73c1",
                    "gameNameEn" => "Pappu",
                    "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/200.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "3cf4a85cb6dcf4d8836c982c359cd72d",
                    "gameNameEn" => "Jack Pot Fishing",
                    "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/JILI/32.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                ],
                [
                    "gameID" => "71c68a4ddb63bdc8488114a08e603f1c",
                    "gameNameEn" => "Happy Fishing",
                    "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/JILI/82.png",
                    "vendorId" => 18,
                    "vendorCode" => "JILI",
                    "imgUrl2" => null,
                    "customGameType" => 0
                
                
                ]
            ]
        ]
    ],
    "code" => 0,
    "msg" => "Succeed",
    "msgCode" => 0,
    "serviceNowTime" => "2025-01-16 00:36:25"
);

// Encode the PHP array to JSON and output it
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

?>