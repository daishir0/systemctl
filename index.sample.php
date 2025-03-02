<?php
// エラー表示を無効化
error_reporting(0);
ini_set('display_errors', 0);

// AJAXリクエストかどうかを確認
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Basic authentication
// 注意: 実際の使用時には、ユーザー名とパスワードを変更してください
if (!isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== 'username@example.com' ||
    $_SERVER['PHP_AUTH_PW'] !== 'your_password') {
    header('WWW-Authenticate: Basic realm="Server Management"');
    header('HTTP/1.0 401 Unauthorized');
    if ($isAjax) {
        header('Content-Type: application/json');
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode([
            'error' => '認証が必要です。ページを再読み込みしてください。',
            'debug' => [
                'status' => '401 Unauthorized',
                'reason' => 'Basic認証が必要です',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo 'Authentication required';
    }
    exit;
}

// Server configurations
// 注意: 実際の使用時には、以下の設定を実際のサーバー情報に置き換えてください
$servers = [
    'Server1' => [
        'host' => '192.168.1.10',
        'user' => 'user1',
        'key_path' => '/path/to/your/private_key1.pem',
        'services' => ['service1.service', 'service2.service', 'service3.service']
    ],
    'Server2' => [
        'host' => '192.168.1.20',
        'user' => 'user2',
        'key_path' => '/path/to/your/private_key2.pem',
        'services' => ['nginx', 'php-fpm']
    ],
    'Server3' => [
        'host' => '192.168.1.30',
        'user' => 'user3',
        'key_path' => '/path/to/your/private_key3.pem',
        'services' => ['docker.service', 'app.service']
    ]
];

function testSSHConnection($server) {
    if (!file_exists($server['key_path'])) {
        return [
            'success' => false,
            'message' => "SSHキーが見つかりません: {$server['key_path']}"
        ];
    }

    $testCommand = "ssh -i {$server['key_path']} -o ConnectTimeout=5 -o BatchMode=yes "
                 . "-o StrictHostKeyChecking=no {$server['user']}@{$server['host']} 'echo 1'";
    
    exec($testCommand . " 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        $error = implode("\n", $output);
        return [
            'success' => false,
            'message' => "SSH接続テスト失敗: $error"
        ];
    }
    
    return ['success' => true];
}

function executeSSHCommand($server, $command) {
    // 接続テスト
    $test = testSSHConnection($server);
    if (!$test['success']) {
        return [
            'output' => $test['message'],
            'status' => 'error'
        ];
    }

    $connection = "ssh -i {$server['key_path']} "
                . "-o ConnectTimeout=10 "
                . "-o BatchMode=yes "
                . "-o StrictHostKeyChecking=no "
                . "{$server['user']}@{$server['host']} ";
    
    $fullCommand = $connection . escapeshellarg($command);
    
    $output = [];
    exec($fullCommand . " 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        $errorMsg = implode("\n", $output);
        return [
            'output' => "コマンド実行エラー: $errorMsg",
            'status' => 'error'
        ];
    }
    
    return [
        'output' => implode("\n", $output),
        'status' => 'success'
    ];
}

function getServiceStatus($server, $service) {
    $result = executeSSHCommand($server, "sudo systemctl status $service");
    if ($result['status'] === 'error') {
        return [
            'name' => $service,
            'status' => 'error',
            'error' => $result['output']
        ];
    }

    return [
        'name' => $service,
        'status' => 'running',
        'details' => $result['output']
    ];
}

function getSystemStatus($server) {
    $status = [];
    
    // システムリソース情報
    $commands = [
        'CPU使用率' => "top -bn1 | grep 'Cpu(s)' | awk '{print $2 + $4}' | awk '{print $1 \"%\"}'",
        'メモリ使用状況' => "free -h | awk 'NR==2{printf \"%s/%s (%s使用中)\", $3,$2,$3}'",
        'ディスク使用状況' => "df -h / | awk 'NR==2{printf \"%s/%s (%s使用中)\", $3,$2,$5}'",
        'システム負荷' => "uptime | awk -F'load average:' '{print $2}'",
        'システム稼働時間' => "uptime -p"
    ];
    
    foreach ($commands as $label => $cmd) {
        $result = executeSSHCommand($server, $cmd);
        if ($result['status'] === 'error') {
            $status[$label] = "取得エラー: " . $result['output'];
        } else {
            $status[$label] = $result['output'];
        }
    }
    
    // サービスステータス
    $status['サービス状態'] = [];
    foreach ($server['services'] as $service) {
        $serviceStatus = getServiceStatus($server, $service);
        $status['サービス状態'][$service] = $serviceStatus['status'] === 'running'
            ? "稼働中"
            : "エラー: " . $serviceStatus['error'];
    }
    
    return $status;
}

// AJAXリクエストの処理を最初に行う
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
    header('Content-Type: application/json');
    
    $response = ['error' => 'Invalid request'];
    $serverName = $_POST['server'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if (!isset($servers[$serverName])) {
        $response = [
            'error' => '指定されたサーバーが見つかりません',
            'debug' => [
                'リクエスト情報' => [
                    'サーバー名' => $serverName,
                    'アクション' => $action,
                    'メソッド' => $_SERVER['REQUEST_METHOD'],
                    'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '不明'
                ]
            ]
        ];
    } else {
        $server = $servers[$serverName];
        if ($action === 'status') {
            try {
                // エラー出力をバッファリング
                ob_start();
                $status = getSystemStatus($server);
                $error_output = ob_get_clean();
                
                $debug_info = [
                    'サーバー情報' => [
                        'ホスト' => $server['host'],
                        'ユーザー' => $server['user'],
                        'SSHキーパス' => $server['key_path'],
                        'SSHキー存在' => file_exists($server['key_path']) ? 'はい' : 'いいえ'
                    ],
                    'PHPバージョン' => PHP_VERSION,
                    'メモリ使用量' => memory_get_usage(true),
                    'エラー出力' => $error_output ?: 'なし'
                ];
                
                if ($error_output) {
                    $response = [
                        'error' => 'PHPエラー: ' . $error_output,
                        'debug' => $debug_info
                    ];
                } else if (!is_array($status)) {
                    $response = [
                        'error' => 'システムステータスの取得に失敗しました',
                        'debug' => $debug_info
                    ];
                } else {
                    $response = [
                        'data' => $status,
                        'debug' => $debug_info
                    ];
                }
            } catch (Exception $e) {
                error_log("System error: " . $e->getMessage());
                $response = [
                    'error' => 'システムエラー: ' . $e->getMessage(),
                    'debug' => [
                        'エラー詳細' => [
                            'メッセージ' => $e->getMessage(),
                            'ファイル' => $e->getFile(),
                            'ライン' => $e->getLine(),
                            'トレース' => $e->getTraceAsString()
                        ]
                    ]
                ];
            }
        }
    }
    
    echo json_encode($response);
    exit;
}

// 通常のフォーム送信の処理
$result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serverName = $_POST['server'] ?? '';
    $service = $_POST['service'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if (isset($servers[$serverName])) {
        $server = $servers[$serverName];
        if ($action === 'status') {
            $status = getSystemStatus($server);
            $result = json_encode($status, JSON_PRETTY_PRINT);
        } else {
            $command = "sudo systemctl $action $service";
            $result = executeSSHCommand($server, $command)['output'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Server Management Interface</h1>
        
        <form method="POST" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="server" class="form-select" required>
                        <option value="">Select Server</option>
                        <?php foreach ($servers as $name => $config): ?>
                            <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <select name="service" class="form-select" required disabled>
                        <option value="">Select Service</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <select name="action" class="form-select" required>
                        <option value="">Select Action</option>
                        <option value="start">Start</option>
                        <option value="stop">Stop</option>
                        <option value="restart">Restart</option>
                        <option value="status">Status</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Execute</button>
                </div>
            </div>
        </form>

        <?php if ($result): ?>
            <div class="card">
                <div class="card-header">
                    Command Output
                </div>
                <div class="card-body">
                    <pre class="mb-0"><?= htmlspecialchars($result) ?></pre>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <h2>System Status</h2>
            <?php foreach ($servers as $name => $config): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <?= htmlspecialchars($name) ?>
                        <button class="btn btn-sm btn-info float-end check-status"
                                data-server="<?= htmlspecialchars($name, ENT_QUOTES) ?>"
                                onclick="console.log('Button clicked for:', '<?= htmlspecialchars($name, ENT_QUOTES) ?>')">
                            Refresh Status
                        </button>
                    </div>
                    <div class="card-body status-content" id="status-<?= htmlspecialchars($name) ?>">
                        Click Refresh Status to view system information
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // サーバー設定をJavaScriptで利用できるように
        const servers = <?= json_encode($servers) ?>;
        $(document).ready(function() {
            // 初期化時のデバッグ情報
            console.log('Document ready');
            console.log('Found status buttons:', $('.check-status').length);
            $('.check-status').each(function() {
                console.log('Button data:', {
                    server: $(this).data('server'),
                    html: $(this).html(),
                    parent: $(this).parent().html()
                });
            });

            // イベントハンドラーの登録確認
            $(document).on('click', '.check-status', function(e) {
                console.log('Event delegation click detected');
            });

            // サーバー選択時の処理
            // サーバー選択時の処理
            $('select[name="server"]').change(function() {
                const serverName = $(this).val();
                const serviceSelect = $('select[name="service"]');
                
                // サービス選択をリセット
                serviceSelect.empty().append('<option value="">Select Service</option>');
                
                if (serverName) {
                    // 選択されたサーバーのサービスを追加
                    const serverServices = servers[serverName].services;
                    serverServices.forEach(service => {
                        serviceSelect.append(`<option value="${service}">${service}</option>`);
                    });
                    serviceSelect.prop('disabled', false);
                } else {
                    // サーバーが選択されていない場合は無効化
                    serviceSelect.prop('disabled', true);
                }
            });
            
            // 直接のイベントハンドラーを削除し、イベント委譲を使用
            $(document).off('click', '.check-status').on('click', '.check-status', function(e) {
                e.preventDefault();
                const server = $(this).data('server');
                const statusDiv = $(`#status-${server}`);
                
                statusDiv.html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
                
                console.log('Sending request for server:', server);
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        server: server,
                        action: 'status'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log('Received response:', data);
                        
                        let html = '';
                        
                        // エラーチェック
                        if (data.error) {
                            html = `
                                <div class="alert alert-danger">
                                    <h5>エラーが発生しました</h5>
                                    <p>${data.error}</p>
                            `;
                            
                            // デバッグ情報の表示
                            if (data.debug) {
                                html += `
                                    <hr>
                                    <div class="debug-info">
                                        <h6>デバッグ情報:</h6>
                                        <pre class="mt-2 mb-0" style="font-size: 0.85em;">
${JSON.stringify(data.debug, null, 2)}
                                        </pre>
                                    </div>
                                `;
                            }
                            
                            html += '</div>';
                            statusDiv.html(html);
                            return;
                        }
                        
                        // 正常なデータの表示
                        if (data.data) {
                            html = '<dl class="row">';
                            for (const [key, value] of Object.entries(data.data)) {
                                if (key === 'サービス状態') {
                                    html += `<dt class="col-sm-12 mt-3 mb-2"><h5>${key}</h5></dt>`;
                                    for (const [service, serviceStatus] of Object.entries(value)) {
                                        const statusClass = serviceStatus.includes('エラー') ? 'text-danger' : 'text-success';
                                        html += `
                                            <dt class="col-sm-3">${service}</dt>
                                            <dd class="col-sm-9 ${statusClass}"><pre class="mb-0">${serviceStatus}</pre></dd>
                                        `;
                                    }
                                } else {
                                    html += `
                                        <dt class="col-sm-3">${key}</dt>
                                        <dd class="col-sm-9"><pre class="mb-0">${value}</pre></dd>
                                    `;
                                }
                            }
                            html += '</dl>';
                            
                            // デバッグ情報の表示（正常時）
                            if (data.debug) {
                                html += `
                                    <hr>
                                    <div class="debug-info">
                                        <h6>デバッグ情報:</h6>
                                        <pre class="mt-2 mb-0" style="font-size: 0.85em;">
${JSON.stringify(data.debug, null, 2)}
                                        </pre>
                                    </div>
                                `;
                            }
                            
                            statusDiv.html(html);
                        } else {
                            statusDiv.html(`<div class="alert alert-danger">不正なレスポンス形式です</div>`);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', {xhr, status, error});
                        let debugInfo = [];
                        let errorMessage = '通信エラーが発生しました';
                        let errorDetails = '';
                        let needsReload = false;
                        
                        // エラー情報の収集
                        debugInfo.push(`=== リクエスト情報 ===`);
                        debugInfo.push(`HTTPステータス: ${xhr.status} (${xhr.statusText})`);
                        debugInfo.push(`URL: ${this.url}`);
                        debugInfo.push(`メソッド: ${this.type}`);
                        debugInfo.push(`ヘッダー: ${JSON.stringify(this.headers)}`);
                        
                        try {
                            const response = xhr.responseText;
                            console.log('Error response:', response);
                            
                            // レスポースの解析
                            if (xhr.status === 401) {
                                errorMessage = '認証エラー: セッションが切れました';
                                errorDetails = 'ページを再読み込みして再度ログインしてください';
                                needsReload = true;
                            }
                            
                            debugInfo.push(`\n=== レスポンス内容 ===`);
                            if (response) {
                                try {
                                    const jsonResponse = JSON.parse(response);
                                    if (jsonResponse.error) {
                                        errorMessage = jsonResponse.error;
                                        if (jsonResponse.debug) {
                                            debugInfo.push('サーバーデバッグ情報:');
                                            debugInfo.push(JSON.stringify(jsonResponse.debug, null, 2));
                                        }
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                    debugInfo.push(`JSONパースエラー: ${e.message}`);
                                    
                                    if (response.includes('<!DOCTYPE html>') || response.includes('<html>')) {
                                        errorMessage = '認証エラー: HTMLが返されました';
                                        errorDetails = 'ページを再読み込みして再度ログインしてください';
                                        needsReload = true;
                                        debugInfo.push('HTMLレスポンスを検出');
                                    } else {
                                        debugInfo.push(`生のレスポンス内容: ${response.substring(0, 200)}${response.length > 200 ? '...' : ''}`);
                                    }
                                }
                            } else {
                                debugInfo.push('レスポンスが空です');
                            }
                        } catch (e) {
                            console.error('Error handling response:', e);
                            debugInfo.push(`\n=== エラー処理中の例外 ===\n${e.message}`);
                        }
                        
                        // エラー情報を画面に表示
                        let html = `
                            <div class="alert alert-danger">
                                <h5>エラーが発生しました</h5>
                                <p>${errorMessage}</p>
                                ${errorDetails ? `<p class="text-danger">${errorDetails}</p>` : ''}
                                ${needsReload ? `
                                    <div class="mt-3">
                                        <button class="btn btn-primary btn-sm" onclick="location.reload()">
                                            ページを再読み込み
                                        </button>
                                    </div>
                                ` : ''}
                                <hr>
                                <div class="debug-info">
                                    <p class="mb-0">デバッグ情報:</p>
                                    <pre class="mt-2 mb-0" style="font-size: 0.85em; white-space: pre-wrap;">${debugInfo.join('\n')}</pre>
                                </div>
                            </div>
                        `;
                        statusDiv.html(html);
                    }
                });
            });
        });
    </script>
</body>
</html>
