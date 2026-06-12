<?php
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
header("Access-Control-Allow-Origin: " . $origin);
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
date_default_timezone_set('Asia/Kolkata');
$serviceNowTimeFormatted = date('Y-m-d H:i:s');

$jsonData = '{
    "data": {
        "popular": {
            "platformList": [
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "c4b2813f6bbc5abf502ddfb857e604eb",
                    "gameNameEn": "Chicken Road",
                    "imgUrl": "https://luckmedia.link/iog_chicken_road/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.42
                },
                
                {
                    "gameID": "562b299961b0ec40f252a832453c67b0",
                    "gameNameEn": "Cricket",
                    "imgUrl": "https://luckmedia.link/iog_chicken_road_2/thumb_3_4_custom.webp",
                    "vendorId": 87,
                    "vendorCode": "INOUT",
                    "imgUrl2": "",
                    "winOdds": 96.58
                },
                {
                    "vendorId": "37",
                    "vendorCode": "JILI",
                    "gameCode": "9ec2a18752f83e45ccedde8dfeb0f6a7",
                    "gameNameEn": "WD Golden Fortune Fishing",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDGoldenFortuneFishing.png",
                    "imgUrl2": "",
                    "winOdds": 97.57
                },
                {
                    "vendorId": "37",
                    "vendorCode": "JILI",
                    "gameCode": "caacafe3f64a6279e10a378ede09ff38",
                    "gameNameEn": "WD Golden Tyrant Fishing",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDGoldenTyrantFishing.png",
                    "imgUrl2": "",
                    "winOdds": 96.15
                },
                {
                    "vendorId": "37",
                    "vendorCode": "JILI",
                    "gameCode": "e794bf5717aca371152df192341fe68b",
                    "gameNameEn": "WD Merry Island Fishing",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDMerryIslandFishing.png",
                    "imgUrl2": "",
                    "winOdds": 96.93
                },
                {
                    "vendorId": "37",
                    "vendorCode": "JILI",
                    "gameCode": "e794bf5717aca371152df192341fe68b",
                    "gameNameEn": "Fruit King",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDGoldBlastFishing.png",
                    "imgUrl2": "",
                    "winOdds": 96.23
                },
                {
                    "vendorId": "2",
                    "vendorCode": "cq9",
                    "gameCode": "c0adf8478aef16eae670cce8258623b1",
                    "gameNameEn": "10000 Wishes",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_10000Wishes.png",
                    "imgUrl2": "",
                    "winOdds": 96.19
                },
                {
                    "vendorId": "4",
                    "vendorCode": "cq9",
                    "gameCode": "SMG_108Heroes",
                    "gameNameEn": "108 Heroes",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_108Heroes.png",
                    "imgUrl2": "",
                    "winOdds": 96.44
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_108HeroesWaterMargin",
                    "gameNameEn": "108 Heroes Water Margin",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_108HeroesWaterMargin.png",
                    "imgUrl2": "",
                    "winOdds": 97.24
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_15Tridents",
                    "gameNameEn": "15 Tridents",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_15Tridents.png",
                    "imgUrl2": "",
                    "winOdds": 96.11
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_25000Talons",
                    "gameNameEn": "25000 Talons",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_25000Talons.png",
                    "imgUrl2": "",
                    "winOdds": 97.06
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_4DiamondBlues",
                    "gameNameEn": "4 Diamond Blues™ - Megaways™",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_4DiamondBlues.png",
                    "imgUrl2": "",
                    "winOdds": 97.69
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_5ReelDrive",
                    "gameNameEn": "5 Reel Drive",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_5ReelDrive.png",
                    "imgUrl2": "",
                    "winOdds": 96.14
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_5StarKnockout",
                    "gameNameEn": "5 Star Knockout",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_5StarKnockout.png",
                    "imgUrl2": "",
                    "winOdds": 96.75
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_777MegaDeluxe",
                    "gameNameEn": "777 Mega Deluxe™",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_777MegaDeluxe.png",
                    "imgUrl2": "",
                    "winOdds": 97.52
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    "gameCode": "SMG_777RoyalWheel",
                    "gameNameEn": "777 Royal Wheel",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG/SMG_777RoyalWheel.png",
                    "imgUrl2": "",
                    "winOdds": 97.58
                }
            ],
            "clicksTopList": [
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "cb66d23b547498132598589af324d558",
                    "gameNameEn": "AviaFly",
                    "imgUrl": "https://luckmedia.link/iog_aviafly/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.19
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "81c4999d70b1ebbb7cf89d8e41ad493c",
                    "gameNameEn": "Limbo",
                    "imgUrl": "https://luckmedia.link/iog_limbo/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.44
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "3a3affa176283107288f5da3698ffe7c",
                    "gameNameEn": "Lucky mines",
                    "imgUrl": "https://luckmedia.link/iog_luckymines/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 97.24
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "9b48efcf6b18b12fdd7dc8efe9ae971e",
                    "gameNameEn": "Сoinflip",
                    "imgUrl": "https://luckmedia.link/iog_coinflip/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.11
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "eb3f4260c17737e09767bc4c06796a61",
                    "gameNameEn": "Plinko 1000",
                    "imgUrl": "https://luckmedia.link/iog_plinko1000/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 97.06
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "445289d56c9ee8fa590bf6b29b13dc37",
                    "gameNameEn": "Cryptos",
                    "imgUrl": "https://luckmedia.link/iog_cryptos/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 97.69
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "6180fdaf4dfab4042194a7d595aca4bb",
                    "gameNameEn": "Hot-mines",
                    "imgUrl": "https://luckmedia.link/iog_hotmines/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.14
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "90741c45a03fbfc800f79c3f5a23be44",
                    "gameNameEn": "Diver",
                    "imgUrl": "https://luckmedia.link/iog_diver/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.75
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "66d311ff6cf531e40b61c483dd34c5c9",
                    "gameNameEn": "Penalty Unlimited",
                    "imgUrl": "https://luckmedia.link/iog_penalty_unlimited/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 97.52
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "458bd34bc83e34501df7e7f96626df6b",
                    "gameNameEn": "Forest Arrow",
                    "imgUrl": "https://luckmedia.link/iog_forest_fortune/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 97.58
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "af95a673c8f3a420444d73421bcf0e7a",
                    "gameNameEn": "Hamster Run",
                    "imgUrl": "https://luckmedia.link/iog_hamster_run/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 97.84
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "134bbc0f61b73824cf9a68411aa32dc6",
                    "gameNameEn": "Plinko Aztec",
                    "imgUrl": "https://luckmedia.link/iog_aztec_plinko_1000/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.35
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "3c802c686f7f9270057b6bb69567ea98",
                    "gameNameEn": "SugarDaddy",
                    "imgUrl": "https://luckmedia.link/iog_sugar_daddy/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.10
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "23d59c10bc65c3cfb6cafdf49969a2b7",
                    "gameNameEn": "Joker Poker",
                    "imgUrl": "https://luckmedia.link/iog_joker_poker/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 97.32
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "a6212c1c462a2442a369a4ec25bf40d7",
                    "gameNameEn": "Stairs",
                    "imgUrl": "https://luckmedia.link/iog_stairs/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.01
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "4169b950c7cbd0bbd392941a13e56767",
                    "gameNameEn": "Triple",
                    "imgUrl": "https://luckmedia.link/iog_triple/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.25
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "09a138907ccf03d5c064c5ca71e9d9b3",
                    "gameNameEn": "Jogo Do Bicho",
                    "imgUrl": "https://luckmedia.link/iog_jogodobicho/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.18
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "c81286ce4034dcf5b71d44c106f968db",
                    "gameNameEn": "Roulette",
                    "imgUrl": "https://luckmedia.link/iog_roulette/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.48
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "f84949bd783c7395b5f3092f4d4ec600",
                    "gameNameEn": "Bubbles",
                    "imgUrl": "https://luckmedia.link/iog_bubbles/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.01
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "e5e23d4c8f7256a1753793cba5fb5aaf",
                    "gameNameEn": "Mines",
                    "imgUrl": "https://luckmedia.link/iog_mines/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.25
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "2c62ddc0ad8e2c175ec771882e91789b",
                    "gameNameEn": "Sweet Keno",
                    "imgUrl": "https://luckmedia.link/iog_sweetkeno/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.18
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "46d14949aa244609aaa03fa58b198784",
                    "gameNameEn": "Goblin-tower",
                    "imgUrl": "https://luckmedia.link/iog_goblintower/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.48
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "d0ddc8acfbc6836f0db6d270bd83243d",
                    "gameNameEn": "Robo dice",
                    "imgUrl": "https://luckmedia.link/iog_robodice/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.01
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "f4fd956965b6f08ce48fee7d4407aaed",
                    "gameNameEn": "New Hilo",
                    "imgUrl": "https://luckmedia.link/iog_newhilo/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.25
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "d8439d6083288f9171930e60836ba505",
                    "gameNameEn": "Double",
                    "imgUrl": "https://luckmedia.link/iog_double/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.18
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "a51b03beafa1773484d1e9c866709589",
                    "gameNameEn": "Tower",
                    "imgUrl": "https://luckmedia.link/iog_tower/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.48
                },
                 {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "757bdd7e1d8c260807bc78449258d00c",
                    "gameNameEn": "Wheel",
                    "imgUrl": "https://luckmedia.link/iog_wheel/thumb_3_4_custom.webp",
                    "imgUrl2": "", 
                    "winOdds": 96.25
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "8ac6b247bd94d71ecdeaa1e62d74f382",
                    "gameNameEn": "Crash",
                    "imgUrl": "https://luckmedia.link/iog_crash/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.18
                },
                {
                    "vendorId": "87",
                    "vendorCode": "INOUT",
                    "gameCode": "c6345c538d4eb8c4f6d91373009ffc8b",
                    "gameNameEn": "BalloniX",
                    "imgUrl": "https://luckmedia.link/iog_balloonix/thumb_3_4_custom.webp",
                    "imgUrl2": "",
                    "winOdds": 96.48
                }
            ],
            "clicksVideoTopList": [
                {
                    "vendorId": "38",
                    "vendorCode": "dreamgaming",
                    "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                    "gameNameEn": "Airwave Roulette",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_AirwaveRoulette.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "ongaming",
                    "gameCode": "3630a6a3c836afa6864578ef21f8fa93",
                    "gameNameEn": "Amstel Roulette",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_AmstelRoulette.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "dreamgaming",
                    "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                    "gameNameEn": "Auto Roulette",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_AutoRoulette.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "ongaming",
                    "gameCode": "3630a6a3c836afa6864578ef21f8fa93",
                    "gameNameEn": "Blackjack Amsterdam",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_BlackjackAmsterdam.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "dreamgaming",
                    "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                    "gameNameEn": "Blackjack Berlin",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_BlackjackBerlin.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "ongaming",
                    "gameCode": "3630a6a3c836afa6864578ef21f8fa93",
                    "gameNameEn": "Blackjack Calgary",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_BlackjackCalgary.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "dreamgaming",
                    "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                    "gameNameEn": "Blackjack London",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_BlackjackLondon.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "ongaming",
                    "gameCode": "3630a6a3c836afa6864578ef21f8fa93",
                    "gameNameEn": "Blackjack Madrid",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_BlackjackMadrid.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "dreamgaming",
                    "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                    "gameNameEn": "Blackjack Manchester",
                    "imgUrl": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Video/SMG_MGLiveGrand_BlackjackManchester.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                }
            ]
        },
        "sport": [
            {
                "slotsTypeID": 25,
                "slotsName": "Wickets9",
                "vendorId": 25,
                "vendorCode": "Wickets9",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108152132x4qk.png"
            },
            {
                "slotsTypeID": 8,
                "slotsName": "CMD",
                "vendorId": 8,
                "vendorCode": "CMD",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_202501081520597t7x.png"
            },
            {
                "slotsTypeID": 14,
                "slotsName": "SaBa",
                "vendorId": 14,
                "vendorCode": "sexy",
                "gameCode": "a225b3ced269ae6545ce3750bcb15175",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108152121fwuv.png"
            }
        ],
        "video": [
            {
                "slotsTypeID": 7,
                "slotsName": "DG",
                "vendorId": 7,
                "vendorCode": "dreamgaming",
                "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108124722cpqn.png"
            },
            {
                "slotsTypeID": 16,
                "slotsName": "EVO_Video",
                "vendorId": 16,
                "vendorCode": "dreamgaming",
                "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108151741ltsi.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "vendorId": 38,
                "vendorCode": "dreamgaming",
                "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108123419vsg6.png"
            }
        ],
        "slot": [
            {
                "slotsTypeID": 4,
                "slotsName": "MG",
                "vendorId": 4,
                "vendorCode": "MG",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108124612oty7.png"
            },
            {
                "slotsTypeID": 17,
                "slotsName": "EVO_Electronic",
                "vendorId": 17,
                "vendorCode": "EVO_Electronic",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108124204goie.png"
            },
            {
                "slotsTypeID": 18,
                "slotsName": "JILI",
                "vendorId": 18,
                "vendorCode": "JILI",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108124227a11m.png"
            },
            {
                "slotsTypeID": 5,
                "slotsName": "PG",
                "vendorId": 5,
                "vendorCode": "PG",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250108124456pvnq.png"
            },
            {
                "slotsTypeID": 6,
                "slotsName": "JDB",
                "vendorId": 6,
                "vendorCode": "JDB",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_202501081245084ylq.png"
            },
            {
                "slotsTypeID": 2,
                "slotsName": "CQ9",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_202501081245369d3b.png"
            },
            {
                "slotsTypeID": 41,
                "slotsName": "G9",
                "vendorId": 41,
                "vendorCode": "G9",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_20250213094547fkyu.png"
            },
            {
                "slotsTypeID": 37,
                "slotsName": "MG_Fish",
                "vendorId": 37,
                "vendorCode": "JILI",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_202501081234127q3m.png"
            }
        ],
        "chess": [
            {
                "slotsTypeID": 19,
                "slotsName": "Card365",
                "vendorId": 19,
                "vendorCode": "Card365",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_202501081515049t7l.png"
            },
            {
                "slotsTypeID": 21,
                "slotsName": "V8Card",
                "vendorId": 21,
                "vendorCode": "V8Card",
                "state": 1,
                "vendorImg": "https://ossimg.dogeclubimage.com/dogeclub/vendorlogo/vendorlogo_202501081531413jgm.png"
            }
        ],
        "fish": [
            {
                "gameID": "9ec2a18752f83e45ccedde8dfeb0f6a7",
                "gameNameEn": "WD FuWa Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDFuWaFishing.png",
                "vendorId": 37,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "e794bf5717aca371152df192341fe68b",
                "gameNameEn": "WD Gold Blast Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDGoldBlastFishing.png",
                "vendorId": 37,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "e333695bcff28acdbecc641ae6ee2b23",
                "gameNameEn": "WD Golden Fortune Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDGoldenFortuneFishing.png",
                "vendorId": 37,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "9ec2a18752f83e45ccedde8dfeb0f6a7",
                "gameNameEn": "WD Golden Fuwa Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDGoldenFuwaFishing.png",
                "vendorId": 37,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "caacafe3f64a6279e10a378ede09ff38",
                "gameNameEn": "WD Golden Tyrant Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDGoldenTyrantFishing.png",
                "vendorId": 37,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "71c68a4ddb63bdc8488114a08e603f1c",
                "gameNameEn": "WD Merry Island Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/MG_Fish/SFG_WDMerryIslandFishing.png",
                "vendorId": 37,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "9ec2a18752f83e45ccedde8dfeb0f6a7",
                "gameNameEn": "Fishing Wars",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/V8Card/510.png",
                "vendorId": 21,
                "vendorCode": "V8Card",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "e794bf5717aca371152df192341fe68b",
                "gameNameEn": "Royal Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/1.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "9ec2a18752f83e45ccedde8dfeb0f6a7",
                "gameNameEn": "All-star Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/119.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "e333695bcff28acdbecc641ae6ee2b23",
                "gameNameEn": "Bombing Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/20.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "bbae6016f79f3df74e453eda164c08a4",
                "gameNameEn": "Dinosaur Tycoon II",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/212.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "564c48d53fcddd2bcf0bf3602d86c958",
                "gameNameEn": "Ocean King Jackpot",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/289.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "71c68a4ddb63bdc8488114a08e603f1c",
                "gameNameEn": "Jack Pot Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/32.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "eef3e28f0e3e7b72cbca61e7924d00f1",
                "gameNameEn": "Dinosaur Tycoon",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/42.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "f2b04833d555ef9989748f9ecabd5249",
                "gameNameEn": "Fortune King Jackpot",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/464.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "1LMzMWfCnPKGFACuYc2VhE7WMVr7BS1BwC",
                "gameNameEn": "Dragon Fortune",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/60.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "f02ede19c5953fce22c6098d860dadf4",
                "gameNameEn": "Boom Legend",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/71.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "caacafe3f64a6279e10a378ede09ff38",
                "gameNameEn": "Mega Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/74.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "71c68a4ddb63bdc8488114a08e603f1c",
                "gameNameEn": "Happy Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/82.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7001",
                "gameNameEn": "Dragon Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7001.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7002",
                "gameNameEn": "Dragon Fishing II",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7002.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7003",
                "gameNameEn": "CaiShen Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7003.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7004",
                "gameNameEn": "Shade Dragons Fishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7004.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7005",
                "gameNameEn": "Fishing YiLuFa",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7005.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7006",
                "gameNameEn": "DragonMaster",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7006.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7007",
                "gameNameEn": "Fishing Disco",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7007.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7009",
                "gameNameEn": "Spirit Tide Legend",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JDB/7009.png",
                "vendorId": 6,
                "vendorCode": "JDB",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "AB3",
                "gameNameEn": "Paradise",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/CQ9/AB3.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "AT01",
                "gameNameEn": "OneShotFishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/CQ9/AT01.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "AT05",
                "gameNameEn": "LuckyFishing",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/CQ9/AT05.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            }
        ],
        "flash": [
            {
                "gameID": "7a762edbe411ebc9be416870a734bd03",
                "gameNameEn": "Keno80",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/900.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "5c4a12fb0a9b296d9b0d5f9e1cd41d65",
                "gameNameEn": "Mines Pro",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/811.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "a04d1f3eb8ccec8a4823bdf18e3f0e84",
                "gameNameEn": "Aviator",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/800.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "ad5973a7625b5d18257e64340fe22ca1",
                "gameNameEn": "Treasure",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/119.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "eabf08253165b6bb2646e403de625d1a",
                "gameNameEn": "JILI",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/110.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "b31720b3cd65d917a1a96ef61a72b672",
                "gameNameEn": "Hotline",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/107.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "c311eb4bbba03b105d150504931f2479",
                "gameNameEn": "Keno",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/106.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "8a87aae7a3624d284306e9c6fe1b3e9c",
                "gameNameEn": "Goal",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/105.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "9dc7ac6155c5a19c1cc204853e426367",
                "gameNameEn": "Mini Roulette",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/104.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "6ab7a4fe5161936012d6b06143918223",
                "gameNameEn": "Plinko",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/103.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "8a87aae7a3624d284306e9c6fe1b3e9c",
                "gameNameEn": "Dice",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/102.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "a669c993b0e1f1b7da100fcf95516bdf",
                "gameNameEn": "Hilo",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/101.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "5c4a12fb0a9b296d9b0d5f9e1cd41d65",
                "gameNameEn": "Mines",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/TB_Chess/100.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "H5-69050857",
                "gameNameEn": "Go Rush",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/224.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "72ce7e04ce95ee94eef172c0dfd6dc17",
                "gameNameEn": "Mines",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/229.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "8e939551b9e785001fcb5b0a32f88aba",
                "gameNameEn": "Tower",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/232.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "bd8a2bb2dd63503b93cf6ac9492786ce",
                "gameNameEn": "HILO",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/233.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "eabf08253165b6bb2646e403de625d1a",
                "gameNameEn": "Limbo",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/235.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "6e19e03c50f035ddd9ffd804c30f8c80",
                "gameNameEn": "Wheel",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/236.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "d528913b832aba97654b6393b3a915b4",
                "gameNameEn": "Fortune Gems Scratch",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/441.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "45a2a090dd3f8c5e51a20e5f7c24830b",
                "gameNameEn": "Go For Champion",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/JILI/442.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "a04d1f3eb8ccec8a4823bdf18e3f0e84",
                "gameNameEn": "Aviator",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/aviator.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "de88f202c5a8beeaccabbd944f8acfbf",
                "gameNameEn": "Balloon",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/balloon.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "8a87aae7a3624d284306e9c6fe1b3e9c",
                "gameNameEn": "Dice",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/dice.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "c68a515f0b3b10eec96cf6d33299f4e2",
                "gameNameEn": "Goal",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/goal.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "a669c993b0e1f1b7da100fcf95516bdf",
                "gameNameEn": "Hi Lo",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/hi-lo.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "b31720b3cd65d917a1a96ef61a72b672",
                "gameNameEn": "Hotline",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/hotline.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "c311eb4bbba03b105d150504931f2479",
                "gameNameEn": "Keno",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/keno.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "5c4a12fb0a9b296d9b0d5f9e1cd41d65",
                "gameNameEn": "Mines",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/mines.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "9dc7ac6155c5a19c1cc204853e426367",
                "gameNameEn": "Mini Roulette",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/mini-roulette.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "7a762edbe411ebc9be416870a734bd03",
                "gameNameEn": "Keno 80",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/multikeno.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "6ab7a4fe5161936012d6b06143918223",
                "gameNameEn": "Plinko",
                "img": "https://ossimg.dogeclubimage.com/dogeclub/gamelogo/SPRIBE/plinko.png",
                "vendorId": 20,
                "vendorCode": "TB_Chess",
                "imgUrl2": "",
                "customGameType": 0
            }
        ],
        "lottery": [
            {
                "id": 1,
                "categoryCode": "Win Go",
                "categoryName": "WinGo彩票",
                "state": 1,
                "sort": 0,
                "categoryImg": "https://ossimg.tashanedc.com/Tashanwin/lotterycategory/lotterycategory_20250412120719dqfv.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            },
            {
                "id": 2,
                "categoryCode": "K3",
                "categoryName": "K3彩票",
                "state": 1,
                "sort": 0,
                "categoryImg": "https://ossimg.tashanedc.com/Tashanwin/lotterycategory/lotterycategory_2025041212074073ug.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            },
            {
                "id": 3,
                "categoryCode": "5D",
                "categoryName": "5D彩票",
                "state": 1,
                "sort": 0,
                "categoryImg": "https://ossimg.tashanedc.com/Tashanwin/lotterycategory/lotterycategory_2025041212080195lo.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            },
            {
                "id": 4,
                "categoryCode": "Trx Win Go",
                "categoryName": "TrxWinGo彩票",
                "state": 1,
                "sort": 0,
                "categoryImg": "https://ossimg.tashanedc.com/Tashanwin/lotterycategory/lotterycategory_20250412120818j8wq.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            },
            {
                "id": 99,
                "categoryCode": "MotoRace",
                "categoryName": "Moto Racing",
                "state": 1,
                "sort": 0,
                "categoryImg": "/assets/png/motorace-card.png",
                "gameCode": "MotoRace_1M",
                "vendorCode": "ARLottery",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            }
        ],
        "awardRecordList": [
            {
                "orderId": 12567680,
                "userId": 698590,
                "userPhoto": "9",
                "userName": "919828801913",
                "gameName": "Fortune Gems 2",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/223.png",
                "imgUrl2": "",
                "multiple": 66.67,
                "bonusAmount": 300.00,
                "multipleName": "50-999999",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567679,
                "userId": 4270140,
                "userPhoto": "6",
                "userName": "917870087696",
                "gameName": "Super Ace",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/49.png",
                "imgUrl2": "",
                "multiple": 35.30,
                "bonusAmount": 10.00,
                "multipleName": "35-49",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567678,
                "userId": 1771451,
                "userPhoto": "1",
                "userName": "919575354782",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 100.00,
                "bonusAmount": 50.00,
                "multipleName": "50-999999",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567677,
                "userId": 2105155,
                "userPhoto": "1",
                "userName": "919198997855",
                "gameName": "Aviator",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/SPRIBE/22001.png",
                "imgUrl2": "",
                "multiple": 21.38,
                "bonusAmount": 10.00,
                "multipleName": "20-34",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567676,
                "userId": 1248688,
                "userPhoto": "1",
                "userName": "918018256757",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 30.00,
                "bonusAmount": 5.00,
                "multipleName": "20-34",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567675,
                "userId": 5170159,
                "userPhoto": "1",
                "userName": "917894640990",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 23.00,
                "bonusAmount": 5.00,
                "multipleName": "20-34",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567674,
                "userId": 5267211,
                "userPhoto": "1",
                "userName": "916207187004",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 20.20,
                "bonusAmount": 10.00,
                "multipleName": "20-34",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567673,
                "userId": 1445592,
                "userPhoto": "1",
                "userName": "919982079135",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 22.00,
                "bonusAmount": 5.00,
                "multipleName": "20-34",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567672,
                "userId": 4397697,
                "userPhoto": "1",
                "userName": "917878816365",
                "gameName": "Fortune Gems 2",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/223.png",
                "imgUrl2": "",
                "multiple": 36.00,
                "bonusAmount": 10.00,
                "multipleName": "35-49",
                "createTime": "2025-03-03 14:18:00"
            },
            {
                "orderId": 12567671,
                "userId": 5739066,
                "userPhoto": "1",
                "userName": "919862351307",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.Big-Mumadmin888.com/bigmumbai/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 22.20,
                "bonusAmount": 5.00,
                "multipleName": "20-34",
                "createTime": "2025-03-03 14:18:00"
            }
        ],
        "awardRecordList": []
    },
    "code": 0,
    "msg": "Succeed",
    "msgCode": 0,
    "serviceNowTime": "' . $serviceNowTimeFormatted . '"
}';

$data = json_decode($jsonData, true);

$response = json_encode($data, JSON_PRETTY_PRINT);

header('Content-Type: application/json');
echo $response;

?>