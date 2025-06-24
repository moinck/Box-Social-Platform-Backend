<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🚨 Exception Alert - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            padding: 25px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            color: black;
        }
        .content {
            border: 1px solid #e1e1e1;
            border-top: none;
            padding: 25px;
            background-color: #fff;
        }
        .error-card {
            background-color: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 0 4px 4px 0;
        }
        .section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .section:last-child {
            border-bottom: none;
        }
        h1, h2, h3, h4 {
            color: #dc3545;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            min-width: 140px;
        }
        .detail-value {
            color: #6c757d;
            word-break: break-all;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 24px;color:black">⚠️ EXCEPTION ALERT</h1>
        <p style="margin: 8px 0 0; opacity: 0.9; font-size: 16px;color:black">
            {{ config('app.name') }} • {{ now()->format('Y-m-d H:i:s') }}
        </p>
    </div>

    <div class="content">
        <div class="section">
            <p>Hello Development Team,</p>
            <p>A new exception has occurred that requires your attention:</p>
        </div>

        <div class="error-card">
            <div class="section">
                <h3 style="margin-top: 0;">Error Summary</h3>
                <div class="detail-row">
                    <span class="detail-label">Error Message:</span>
                    <span class="detail-value">{{ $newErrorData['message'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Exception Type:</span>
                    <span class="detail-value">{{ $newErrorData['exception'] ?? 'ErrorException' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Environment:</span>
                    <span class="detail-value">{{ $newErrorData['environment'] ?? config('app.env') }}</span>
                </div>
            </div>

            <div class="section">
                <h4>Location</h4>
                <div class="detail-row">
                    <span class="detail-label">File:</span>
                    <span class="detail-value">{{ $newErrorData['file'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Line:</span>
                    <span class="detail-value">{{ $newErrorData['line'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">URL:</span>
                    <span class="detail-value">{{ $newErrorData['url'] ?? request()->fullUrl() }}</span>
                </div>
            </div>

            <div class="section">
                <h4>Request Data</h4>
                <div class="detail-row">
                    <span class="detail-label">Method:</span>
                    <span class="detail-value">{{ $newErrorData['method'] ?? request()->method() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">IP Address:</span>
                    <span class="detail-value">{{ $newErrorData['ipAddress'] ?? request()->ip() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">User Agent:</span>
                    <span class="detail-value">{{ $newErrorData['userAgent'] ?? request()->userAgent() }}</span>
                </div>
            </div>

            <div class="section">
                <h4>Stack Trace</h4>
                <pre>{{ $newErrorData['trace'] ?? 'No stack trace available' }}</pre>
            </div>
        </div>

        <div class="section">
            <h4>Additional Context</h4>
            <div class="detail-row">
                <span class="detail-label">Timestamp:</span>
                <span class="detail-value">{{ now()->format('Y-m-d H:i:s') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Application Version:</span>
                <span class="detail-value">{{ $newErrorData['version'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification. Please investigate this error promptly.</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>