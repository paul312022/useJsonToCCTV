<?php
// 讀取 JSON 檔案
$filePath = './cctv.json';  // 定義 JSON 檔案的路徑
$jsonData = file_get_contents($filePath);  // 讀取檔案內容
$data = json_decode($jsonData, true);  // 解碼 JSON 資料為 PHP 陣列

// 用來儲存抓取的資料
$cctvData = array();  // 儲存 CCTV 資料的空陣列

// 初始化 cURL
$ch = curl_init();  // 初始化 cURL 會話

// 設定 cURL 選項
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 設定返回響應而不是直接輸出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // 跟隨 301 重定向

// 禁用 SSL 憑證驗證
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 禁用對 SSL 憑證的驗證
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 禁用主機名的驗證

// 迭代 JSON 數據，對每個 CCTV 項目進行處理
foreach ($data as $item) {
    $url = $item['url'];  // 取得每個項目的 URL
    
    // 設定要抓取的 URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // 執行 cURL 請求並獲取網頁內容
    $response = curl_exec($ch);

    if ($response !== false) {  // 檢查 cURL 請求是否成功
        // 強制將抓取到的內容轉換為 UTF-8
        $response = mb_convert_encoding($response, 'UTF-8', 'auto');

        // 使用正規表達式找出所有圖片的 src 屬性
        preg_match_all('/<img[^>]+src="([^"]+)"/', $response, $matches);

        // 如果找到了圖片 URL，將其儲存
        if (isset($matches[1])) {
            foreach ($matches[1] as $imgSrc) {
                // 儲存 CCTV ID、URL 和圖片 URL
                $cctvData[] = array(
                    'cctvid' => $item['cctvid'],
                    'url' => $url,
                    'imgSrc' => $imgSrc
                );
            }
        }
    } else {
        // cURL 請求失敗，顯示錯誤
        echo "錯誤: 無法抓取 URL $url\n";
    }
}
// 關閉 cURL 連接
curl_close($ch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">  <!-- 設置頁面的字符編碼為 UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>攝影機資料</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .card-body img {
            max-width: 100%;        /* 確保圖片寬度不超過 card-body 的範圍 */
            height: auto;           /* 高度自動調整，保持圖片比例 */
            object-fit: contain;    /* 保持圖片比例，避免裁切 */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="display-2 mt-2">台中市即時道路影像</div>  <!-- 顯示頁面標題 -->
        <div class="display-5 mt-2 ms-5">注意！！！ 此網頁"無使用SSL"</div>  <!-- 顯示警告內容 -->
        <div class="row" id="mydata">  <!-- 開始顯示每個 CCTV 影像 -->
            <?php foreach ($cctvData as $item): ?>  <!-- 遍歷所有 CCTV 資料 -->
                <div class="col-md-3 d-flex flex-column mt-3">  <!-- 設定每個卡片的佈局 -->
                    <div class="card">  <!-- 卡片容器 -->
                        <div class="card-body">  <!-- 卡片內容 -->
                            <h5 class="card-title">
                                監視器ID:<?php echo htmlspecialchars($item['cctvid'], ENT_QUOTES, 'UTF-8'); ?>  <!-- 顯示 CCTV ID -->
                            </h5>
                            <img src="<?php echo htmlspecialchars($item['imgSrc'], ENT_QUOTES, 'UTF-8'); ?>"alt="CCTV 影像"> <!-- 顯示 CCTV 影像 設定圖片寬度為 200px，保持比例 -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>  <!-- 結束遍歷 -->
        </div>
    </div>
</body>
</html>
