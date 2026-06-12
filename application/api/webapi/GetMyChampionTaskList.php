<?php
// आवश्यक फ़ाइलों को शामिल करें
include "../../conn.php";
include "../../functions2.php";

// हेडर सेट करें
header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

// टाइमज़ोन सेट करें
date_default_timezone_set("Asia/Kolkata");
$serviceNowTime = date("Y-m-d H:i:s");

// डिफ़ॉल्ट प्रतिक्रिया संरचना
$response = [
    'data' => [
        'list' => [],
        'pageNo' => 1,
        'totalPage' => 0,
        'totalCount' => 0
    ],
    'code' => 0,
    'msg' => 'Succeed',
    'msgCode' => 0,
    'serviceNowTime' => $serviceNowTime,
];

// POST अनुरोध की जांच करें
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputData = json_decode(file_get_contents("php://input"), true);

    // आवश्यक पैरामीटर की जांच करें
    if (isset($inputData['language'], $inputData['random'], $inputData['signature'], $inputData['timestamp'])) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $inputData['language']));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $inputData['random']));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $inputData['signature']));
        $timestamp = htmlspecialchars(mysqli_real_escape_string($conn, $inputData['timestamp']));

        // सिग्नेचर सत्यापन
        $signatureString = '{"language":' . $language . ',"random":"' . $random . '"}';
        $calculatedSignature = strtoupper(md5($signatureString));

        if ($calculatedSignature === $signature) {
            // ऑथेंटिकेशन टोकन प्राप्त करें
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
            $authToken = $bearer[1];
            $authData = json_decode(is_jwt_valid($authToken), true);

            if ($authData['status'] === 'Success') {
                $userId = $authData['userId']; // मान लीजिए JWT में userId है

                // डेटाबेस से कार्य प्राप्त करें
                $query = "SELECT * FROM champion_tasks WHERE user_id = '$userId' ORDER BY task_id DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    $tasks = [];
                    while ($row = $result->fetch_assoc()) {
                        $tasks[] = [
                            'taskID' => $row['task_id'],
                            'taskAmount' => $row['task_amount'],
                            'rechargeAmount' => $row['recharge_amount'],
                            'rechargeAmount_All' => $row['recharge_amount_all'],
                            'taskPeople' => $row['task_people'],
                            'rechargePeople' => $row['recharge_people'],
                            'taskRechargePeople' => $row['task_recharge_people'],
                            'efficientPeople' => $row['efficient_people'],
                            'title' => $row['title'],
                            'title2' => $row['title2'],
                            'isReceive' => $row['is_receive'],
                            'isFinshed' => (bool)$row['is_finished'],
                            'beginDate' => $row['begin_date'],
                            'endDate' => $row['end_date'],
                        ];
                    }

                    // प्रतिक्रिया में डेटा सेट करें
                    $response['data'] = [
                        'list' => $tasks,
                        'pageNo' => 1, // पेजिनेशन के लिए लॉजिक जोड़ें यदि आवश्यक हो
                        'totalPage' => ceil($result->num_rows / 10), // उदाहरण के लिए, प्रति पृष्ठ 10 कार्य
                        'totalCount' => $result->num_rows,
                    ];
                } else {
                    $response['msg'] = 'No tasks found';
                    $response['msgCode'] = 1;
                }
            } else {
                $response['msg'] = 'Authentication failed';
                $response['msgCode'] = 2;
            }
        } else {
            $response['code'] = 0;
            $response['msg'] = 'Succeed';
            $response['msgCode'] = 0;
        }
    } else {
        $response['msg'] = 'Invalid parameters';
        $response['msgCode'] = 4;
    }
} else {
    $response['msg'] = 'Invalid request method';
    $response['msgCode'] = 5;
}

// प्रतिक्रिया भेजें
http_response_code(200);
echo json_encode($response);
?>
