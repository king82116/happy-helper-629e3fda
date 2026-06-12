<?php
include "../../conn.php";
include "../../functions2.php";

try {
header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");
$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
        $typeId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['typeId']));
        $shonustr = '{"language":' . $language . ',"random":"' . $random . '","typeId":' . $typeId . '}';
        $shonusign = strtoupper(md5($shonustr));
        if ($shonusign == $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $author = $bearer[1];
            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, 1);
            if ($data_auth['status'] === 'Success') {
                $sesquery = "SELECT akshinak
					  FROM shonu_subjects
					  WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                if ($sesnum == 1) {
                    if ($typeId == 1) {
                        $samasye = "SELECT atadaaidi, dinankavannuracisi
							  FROM gelluonduhogu
							  ORDER BY kramasankhye DESC LIMIT 1";
                        $samasyephalitansa = $conn->query($samasye);
                        $samasyesreni = mysqli_fetch_array($samasyephalitansa);

                        if ($samasyesreni && !empty($samasyesreni['atadaaidi'])) {
                            $data['issueNumber'] = wingo_resolve_live_period_id((string) $samasyesreni['atadaaidi'], 1);
                            wingo_ensure_db_open_period($conn, 'gelluonduhogu', $data['issueNumber'], 1);
                            $live = wingo_live_round_times(1);
                            $data['startTime'] = $live['startTime'];
                            $data['endTime'] = $live['endTime'];
                            $data['serviceTime'] = date('Y-m-d H:i:s');
                            $data['intervalM'] = 1;
                        } else {
                            $fallbackIssue = wingo1m_build_period_id(time());
                            wingo_ensure_db_open_period($conn, 'gelluonduhogu', $fallbackIssue, 1);
                            $live = wingo_live_round_times(1);
                            $data['issueNumber'] = $fallbackIssue;
                            $data['startTime'] = $live['startTime'];
                            $data['endTime'] = $live['endTime'];
                            $data['serviceTime'] = date('Y-m-d H:i:s');
                            $data['intervalM'] = 1;
                        }
                    } else if ($typeId == 2) {
                        $samasye = "SELECT atadaaidi, dinankavannuracisi
							  FROM gelluonduhogu_drei
							  ORDER BY kramasankhye DESC LIMIT 1";
                        $samasyephalitansa = $conn->query($samasye);
                        $samasyesreni = mysqli_fetch_array($samasyephalitansa);

                        $data['issueNumber'] = $samasyesreni['atadaaidi'];
                        $data['startTime'] = $samasyesreni['dinankavannuracisi'];
                        $ondusamaya = strtotime('+4 minute', strtotime($samasyesreni['dinankavannuracisi']));
                        $data['endTime'] = date('Y-m-d H:i:s', $ondusamaya);
                        $data['serviceTime'] = date('Y-m-d H:i:s');
                        $data['intervalM'] = 3;
                    } else if ($typeId == 3) {
                        $samasye = "SELECT atadaaidi, dinankavannuracisi
							  FROM gelluonduhogu_funf
							  ORDER BY kramasankhye DESC LIMIT 1";
                        $samasyephalitansa = $conn->query($samasye);
                        $samasyesreni = mysqli_fetch_array($samasyephalitansa);

                        $data['issueNumber'] = $samasyesreni['atadaaidi'];
                        $data['startTime'] = $samasyesreni['dinankavannuracisi'];
                        $ondusamaya = strtotime('+1 minute', strtotime($samasyesreni['dinankavannuracisi']));
                        $data['endTime'] = date('Y-m-d H:i:s', $ondusamaya);
                        $data['serviceTime'] = date('Y-m-d H:i:s');
                        $data['intervalM'] = 1;
                    } else if ($typeId == 4) {
                        $currentDate = date('Ymd');
                        $timeInSeconds = time() % 86400;
                        $maxSequencesPerDay = intdiv(86400, 30); // 2880 periods
                        $sequenceNumber = intdiv($timeInSeconds, 30) + 1; // 1-based sequence to mirror cron
                        if ($sequenceNumber > $maxSequencesPerDay) {
                            $sequenceNumber = 1;
                        }

                        $uniqueSequence = str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT);
                        $currentGameId = $currentDate . "10005" . $uniqueSequence;

                        $samasyesreni = null;

                        $checkCurrent = mysqli_query($conn, "SELECT atadaaidi, dinankavannuracisi FROM gelluonduhogu_zehn WHERE atadaaidi = '$currentGameId' LIMIT 1");
                        if ($checkCurrent) {
                            $samasyesreni = mysqli_fetch_assoc($checkCurrent);
                            mysqli_free_result($checkCurrent);
                        }

                        if (!$samasyesreni) {
                            $samasye = "SELECT atadaaidi, dinankavannuracisi FROM gelluonduhogu_zehn ORDER BY kramasankhye DESC LIMIT 1";
                            $samasyephalitansa = $conn->query($samasye);
                            if ($samasyephalitansa) {
                                $samasyesreni = mysqli_fetch_assoc($samasyephalitansa);
                                mysqli_free_result($samasyephalitansa);
                            }
                        }

                        if (!$samasyesreni) {
                            // Absolute fallback when the table is empty (e.g. fresh install)
                            $samasyesreni = [
                                'atadaaidi' => $currentGameId,
                                'dinankavannuracisi' => date('Y-m-d H:i:s'),
                            ];
                        }

                        $data['issueNumber'] = $samasyesreni['atadaaidi'];

                        $startTime = $samasyesreni['dinankavannuracisi'];
                        if (!$startTime || $startTime === '0000-00-00 00:00:00') {
                            $derivedStart = null;
                            if (!empty($samasyesreni['atadaaidi']) && strlen($samasyesreni['atadaaidi']) >= 12) {
                                $issueDate = substr($samasyesreni['atadaaidi'], 0, 8);
                                $sequencePart = substr($samasyesreni['atadaaidi'], -4);
                                $sequenceIndex = max(((int) $sequencePart) - 1, 0);

                                try {
                                    $reykjavikTz = new DateTimeZone('Atlantic/Reykjavik');
                                    $kolkataTz = new DateTimeZone('Asia/Kolkata');
                                    $periodStart = new DateTimeImmutable($issueDate . ' 00:00:00', $reykjavikTz);
                                    if ($sequenceIndex > 0) {
                                        $periodStart = $periodStart->add(new DateInterval('PT' . ($sequenceIndex * 30) . 'S'));
                                    }
                                    $derivedStart = $periodStart->setTimezone($kolkataTz)->format('Y-m-d H:i:s');
                                } catch (Exception $e) {
                                    $derivedStart = null;
                                }
                            }

                            if (!$derivedStart) {
                                $derivedStart = date('Y-m-d H:i:s');
                            }

                            $startTime = $derivedStart;
                        }

                        $startTimestamp = strtotime($startTime);
                        if ($startTimestamp === false) {
                            $startTimestamp = time();
                            $startTime = date('Y-m-d H:i:s', $startTimestamp);
                        }

                        $data['startTime'] = $startTime;
                        $data['endTime'] = date('Y-m-d H:i:s', $startTimestamp + 30);
                        $data['serviceTime'] = date('Y-m-d H:i:s');
                        $data['intervalM'] = 0.5;
                    }
                    $res['data'] = $data;
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                } else {
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                    http_response_code(401);
                    echo json_encode($res);
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);
    }
} else {
    http_response_code(405);
    echo json_encode($res);
}
} finally {
    closeDBConnection();
    $conn = null;
}
?>
