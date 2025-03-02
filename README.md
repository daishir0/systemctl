## Overview
systemctl is a powerful web-based server management interface that allows you to monitor and control system services on multiple remote Linux servers through a simple and intuitive UI. It provides real-time system resource information and service status monitoring through a secure web interface.

## Key Features
✅ **Centralized Management** - Control multiple servers from a single dashboard  
✅ **Real-time Monitoring** - Get instant updates on system resources and service status  
✅ **One-click Service Control** - Start, stop, and restart services with a single click  
✅ **Secure Access** - Protected with Basic authentication and SSH key security  
✅ **Responsive Design** - Works on desktop and mobile devices  
✅ **No Agent Required** - Uses SSH for secure remote execution without installing agents  
✅ **Detailed System Metrics** - View CPU, memory, disk usage, system load, and uptime at a glance  
✅ **Error Handling** - Comprehensive error reporting with detailed debug information  

## Interface Description

### Main Dashboard
The main dashboard provides a comprehensive overview of all your servers at a glance. Each server is displayed as a card with its name prominently shown in the header. The dashboard features:

- A clean, organized layout with each server in its own card
- Status indicators showing the current state of each server
- "Refresh Status" buttons for each server to get real-time updates
- Responsive design that works well on both desktop and mobile devices

### Service Management Interface
The service management section offers intuitive controls for managing your services:

- Dropdown menus to select servers and services
- Action buttons for start, stop, restart, and status operations
- Clear command output display in a formatted card
- Form validation to prevent invalid operations

### System Status Details
When viewing system status, you'll see detailed metrics presented in a well-organized format:

- CPU usage percentage with visual indicators
- Memory usage showing used/total and percentage
- Disk usage with capacity information
- System load averages
- Uptime information in a human-readable format
- Service status section with color-coded indicators (green for running, red for errors)
- Comprehensive error reporting with debug information when needed

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/daishir0/systemctl
   cd systemctl
   ```

2. Rename the sample configuration file:
   ```bash
   cp index.sample.php index.php
   ```

3. Edit the configuration in index.php:
   - Update the Basic authentication credentials
   - Configure your server information (hostname, username, SSH key path)
   - Define the services you want to monitor for each server


## Usage

1. Place the files on a PHP-enabled web server (PHP 7.0 or higher recommended)

2. Access the interface through your web browser:
   ```
   http://your-server/path-to-systemctl/
   ```

3. Enter the Basic authentication credentials you configured

4. From the interface, you can:
   - Select a server and service to manage
   - Start, stop, restart services
   - View system status including:
     - CPU usage
     - Memory usage
     - Disk usage
     - System load
     - Uptime
     - Service status

5. Click "Refresh Status" to get real-time updates on system information

## Security Considerations

Security is a critical aspect of server management tools. Here are important security measures to implement when using systemctl:

### Authentication Security
- **Strong Passwords**: Use complex, unique passwords for Basic authentication
- **IP Restrictions**: Consider restricting access to specific IP addresses using web server configuration
- **Two-Factor Authentication**: Implement additional authentication layers when possible
- **Session Management**: Set appropriate timeout values for authentication sessions

### SSH Security
- **Key-based Authentication Only**: Disable password authentication for SSH
- **Restricted Permissions**: Ensure SSH private keys have 600 permissions (read/write for owner only)
- **Dedicated Keys**: Use dedicated SSH keys for this application, not shared with other services
- **Regular Key Rotation**: Change SSH keys periodically (every 90-180 days)

### Server Hardening
- **Minimal Privileges**: Use a dedicated user with minimal required permissions
- **Firewall Rules**: Implement strict firewall rules to limit access to necessary ports only
- **Regular Updates**: Keep the server OS, PHP, and all dependencies up to date
- **Audit Logging**: Enable comprehensive logging for all authentication and command execution events

### Data Protection
- **HTTPS Encryption**: Always use HTTPS with valid SSL certificates
- **Sensitive Data Handling**: Never expose full SSH keys or credentials in error messages or logs
- **Input Validation**: Implement strict validation for all user inputs to prevent injection attacks
- **Output Sanitization**: Properly sanitize all command outputs before displaying them

## Notes

- This tool requires:
  - PHP 7.0 or higher with exec() function enabled
  - SSH access to the remote servers
  - Private key authentication set up for the remote servers
  - sudo privileges on the remote servers for systemctl commands

- Security considerations:
  - Always use HTTPS in production environments
  - Store SSH keys securely with appropriate permissions
  - Consider implementing additional authentication methods for production use
  - Regularly update passwords and rotate SSH keys

## License
This project is licensed under the MIT License - see the LICENSE file for details.

---

# systemctl

## 概要
systemctlは、シンプルで直感的なUIを通じて複数のリモートLinuxサーバー上のシステムサービスを監視および制御できる強力なWebベースのサーバー管理インターフェースです。安全なWebインターフェースを通じて、リアルタイムのシステムリソース情報とサービスステータスの監視を提供します。

## 主な機能
✅ **一元管理** - 単一のダッシュボードから複数のサーバーを制御  
✅ **リアルタイム監視** - システムリソースとサービスステータスの即時更新  
✅ **ワンクリックサービス制御** - サービスの起動、停止、再起動をワンクリックで実行  
✅ **セキュアなアクセス** - Basic認証とSSHキーセキュリティによる保護  
✅ **レスポンシブデザイン** - デスクトップとモバイルデバイスの両方で動作  
✅ **エージェント不要** - エージェントをインストールせずにSSHを使用した安全なリモート実行  
✅ **詳細なシステムメトリクス** - CPU、メモリ、ディスク使用率、システム負荷、稼働時間を一目で確認  
✅ **エラー処理** - 詳細なデバッグ情報を含む包括的なエラーレポート  

## インターフェース説明

### メインダッシュボード
メインダッシュボードでは、すべてのサーバーの概要を一目で確認できます。各サーバーはカードとして表示され、ヘッダーにサーバー名が目立つように表示されます。ダッシュボードの特徴：

- 各サーバーが独自のカードに表示される整理されたレイアウト
- 各サーバーの現在の状態を示すステータスインジケーター
- リアルタイム更新を取得するための各サーバーの「Refresh Status」ボタン
- デスクトップとモバイルデバイスの両方で適切に動作するレスポンシブデザイン

### サービス管理インターフェース
サービス管理セクションでは、サービスを管理するための直感的なコントロールを提供します：

- サーバーとサービスを選択するためのドロップダウンメニュー
- 起動、停止、再起動、ステータス操作のためのアクションボタン
- フォーマットされたカードでの明確なコマンド出力表示
- 無効な操作を防止するためのフォームバリデーション

### システムステータスの詳細
システムステータスを表示すると、整理された形式で詳細なメトリクスが表示されます：

- 視覚的なインジケーターを備えたCPU使用率のパーセンテージ
- 使用済み/合計とパーセンテージを示すメモリ使用状況
- 容量情報を含むディスク使用状況
- システム負荷平均
- 人間が読みやすい形式でのアップタイム情報
- 色分けされたインジケーター（稼働中は緑、エラーは赤）を持つサービスステータスセクション
- 必要に応じてデバッグ情報を含む包括的なエラーレポート

## インストール方法

1. リポジトリをクローンします：
   ```bash
   git clone https://github.com/daishir0/systemctl
   cd systemctl
   ```

2. サンプル設定ファイルの名前を変更します：
   ```bash
   cp index.sample.php index.php
   ```

3. index.phpの設定を編集します：
   - Basic認証の認証情報を更新
   - サーバー情報（ホスト名、ユーザー名、SSHキーパス）を設定
   - 各サーバーで監視したいサービスを定義


## 使い方

1. PHPが有効なWebサーバーにファイルを配置します（PHP 7.0以上推奨）

2. Webブラウザからインターフェースにアクセスします：
   ```
   http://your-server/path-to-systemctl/
   ```

3. 設定したBasic認証の認証情報を入力します

4. インターフェースから以下の操作が可能です：
   - 管理するサーバーとサービスを選択
   - サービスの起動、停止、再起動
   - 以下を含むシステムステータスの表示：
     - CPU使用率
     - メモリ使用状況
     - ディスク使用状況
     - システム負荷
     - システム稼働時間
     - サービス状態

5. 「Refresh Status」をクリックして、システム情報をリアルタイムで更新

## セキュリティに関する考慮事項

セキュリティはサーバー管理ツールの重要な側面です。systemctlを使用する際に実装すべき重要なセキュリティ対策は以下の通りです：

### 認証セキュリティ
- **強力なパスワード**: Basic認証には複雑でユニークなパスワードを使用する
- **IP制限**: Webサーバーの設定を使用して、特定のIPアドレスからのアクセスのみを許可することを検討する
- **二要素認証**: 可能な場合は追加の認証層を実装する
- **セッション管理**: 認証セッションに適切なタイムアウト値を設定する

### SSHセキュリティ
- **鍵ベースの認証のみ**: SSHのパスワード認証を無効にする
- **制限付き権限**: SSH秘密鍵が600の権限（所有者のみ読み書き可能）を持つようにする
- **専用キー**: このアプリケーション専用のSSHキーを使用し、他のサービスと共有しない
- **定期的なキーローテーション**: SSHキーを定期的に変更する（90〜180日ごと）

### サーバー強化
- **最小限の権限**: 必要な権限のみを持つ専用ユーザーを使用する
- **ファイアウォールルール**: 必要なポートへのアクセスのみを許可する厳格なファイアウォールルールを実装する
- **定期的な更新**: サーバーOS、PHP、およびすべての依存関係を最新の状態に保つ
- **監査ログ**: すべての認証およびコマンド実行イベントの包括的なログを有効にする

### データ保護
- **HTTPS暗号化**: 常に有効なSSL証明書を使用したHTTPSを使用する
- **機密データの取り扱い**: エラーメッセージやログにSSHキーや認証情報を完全に露出させない
- **入力検証**: インジェクション攻撃を防ぐために、すべてのユーザー入力に対して厳格な検証を実装する
- **出力サニタイズ**: 表示前にすべてのコマンド出力を適切にサニタイズする

## 注意点

- このツールには以下が必要です：
  - exec()関数が有効なPHP 7.0以上
  - リモートサーバーへのSSHアクセス
  - リモートサーバー用に設定された秘密鍵認証
  - systemctlコマンド用のリモートサーバーでのsudo権限

- セキュリティに関する考慮事項：
  - 本番環境では常にHTTPSを使用してください
  - SSHキーを適切な権限で安全に保管してください
  - 本番環境での使用には追加の認証方法の実装を検討してください
  - パスワードを定期的に更新し、SSHキーをローテーションしてください

## ライセンス
このプロジェクトはMITライセンスの下でライセンスされています。詳細はLICENSEファイルを参照してください。
