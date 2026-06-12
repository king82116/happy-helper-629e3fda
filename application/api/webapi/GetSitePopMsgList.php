<?php
// API Response JSON
$response = [
    "data" => [
        [
            "title" => "𝐖𝐄𝐋𝐂𝐎𝐌𝐄 𝐓𝐎 𝐉𝐎𝐒𝐇 𝐆𝐀𝐌𝐄",
            "siteMessage" => '<div class="van-dialog__content">
                <div data-v-88d7f5ef="" class="promptContent" style="text-align:center;">
                    <p style="text-align: center;">
                        <img src="https://ossimg.joshclub.fun/images/banner/Banner_20251812180120amou.jpg" style="width:317.734px; height:317.734px;">
                        <br>
                        <span style="font-family: Arial Black;"></span>
                        <strong style="font-family: Arial Black;">
                            <font color="#e76363"></font>
                        </strong>
                        <span style="font-family: Arial Black;"></span>
                    </p>
                    <div style="text-align: center;">
                        <b><font color="#ffffff" style="font-family: Microsoft YaHei;"></font></b>
                    </div>
                    <div style="text-align: center;">
                        <b><font color="#ffffff" style="font-family: Microsoft YaHei;"></font></b>
                    </div>
                    <div style="text-align: center;">
                        <b><font color="#ffffff" style="font-family: Microsoft YaHei;"></font></b>
                    </div>
                    <p style="text-align: center;">
                        <b>
                            <font color="#94bd7b" style="font-family: Microsoft YaHei;"</font>
                        </b>
                    </p>
                    <p style="text-align: center;">
                        <span style="font-family: Arial Black;">
                            <a href="https://joshgame.online/" target="_blank">
                                <font color="#efc631"><u></u></font>
                            </a>
                        </span>
                    </p>
                    <p style="text-align: center;">
                        <span style="font-family: Arial Black;"> <strong>
                                <font color="#b5d6a5" style="font-family: Microsoft YaHei;">
                                </font>
                            </strong>
                        </span>
                    </p>
                </div>
            </div>',
            "sort" => 0,
            "addtime" => "2025-01-05 20:29:59"
        ]
    ],
    "code" => 0,
    "msg" => "Succeed",
    "msgCode" => 0,
    "serviceNowTime" => "2025-01-16 00:36:25"
];

// JSON Response Output
header('Content-Type: application/json');
echo json_encode($response);
?>