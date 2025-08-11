<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🚨 Exception Alert - {{ config('app.name') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            animation: slideInUp 0.6s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: rotate(0deg) translate(-50%, -50%); }
            100% { transform: rotate(360deg) translate(-50%, -50%); }
        }
        
        .header h1 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }
        
        .pulse-icon {
            display: inline-block;
            animation: pulse 2s infinite;
            margin-right: 8px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .intro-section {
            margin-bottom: 35px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9ff 0%, #f1f3ff 100%);
            border-radius: 15px;
            border-left: 5px solid #667eea;
        }
        
        .intro-section p {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 15px;
        }
        
        .intro-section p:last-child {
            margin-bottom: 0;
        }
        
        .error-card {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 50%, #fff5f5 100%);
            border: 2px solid #fed7d7;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(254, 215, 215, 0.3);
        }
        
        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #ee5a24, #ff6b6b);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        .subsection-title {
            font-size: 16px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .subsection-title::before {
            content: '▸';
            color: #667eea;
            font-weight: bold;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .detail-row {
            display: flex;
            align-items: flex-start;
            background: rgba(255, 255, 255, 0.7);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .detail-row:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .detail-label {
            font-weight: 600;
            min-width: 140px;
            color: #2d3748;
            font-size: 14px;
        }
        
        .detail-value {
            color: #4a5568;
            word-break: break-all;
            font-size: 14px;
            flex: 1;
        }
        
        .code-block {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: #e2e8f0;
            padding: 25px;
            border-radius: 12px;
            overflow-x: auto;
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
            position: relative;
            border: 1px solid #4a5568;
            margin-top: 15px;
        }
        
        .code-block::before {
            content: 'Stack Trace';
            position: absolute;
            top: -10px;
            left: 15px;
            background: #2d3748;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            color: #a0aec0;
            font-weight: 500;
        }
        
        .additional-context {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #e2e8f0;
        }
        
        .footer {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        
        .footer p {
            margin-bottom: 8px;
        }
        
        .footer p:last-child {
            margin-bottom: 0;
        }
        
        .status-badge {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 10px;
        }
        
        .highlight {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
            color: black;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 8px;
            }
            
            .detail-label {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span class="pulse-icon">⚠️</span>EXCEPTION ALERT<span class="status-badge">Critical</span></h1>
            <p>
                <span class="highlight">{{ config('app.name') }}</span> • {{ now()->format('Y-m-d H:i:s') }}
            </p>
        </div>

        <div class="content">
            <div class="intro-section">
                <p><strong>Hello Development Team,</strong></p>
                <p>A new exception has occurred that requires your immediate attention. Please review the details below and take appropriate action.</p>
            </div>

            <div class="error-card">
                <div class="section">
                    <h3 class="section-title">🔥 Error Summary</h3>
                    <div class="detail-grid">
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
                </div>

                <div class="section">
                    <h4 class="subsection-title">📍 Location</h4>
                    <div class="detail-grid">
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
                </div>

                <div class="section">
                    <h4 class="subsection-title">🌐 Request Data</h4>
                    <div class="detail-grid">
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
                </div>

                <div class="section">
                    <h4 class="subsection-title">🔍 Stack Trace</h4>
                    <div class="code-block">{{ $newErrorData['trace'] ?? 'No stack trace available' }}</div>
                </div>
            </div>

            <div class="additional-context">
                <h4 class="section-title">📋 Additional Context</h4>
                <div class="detail-grid">
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
        </div>

        <div class="footer">
            <p><strong>This is an automated notification.</strong> Please investigate this error promptly.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>