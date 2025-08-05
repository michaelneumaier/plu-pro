<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Feedback Submitted - PLUPro</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #10b981;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 24px;
        }
        .feedback-info {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-bug { background-color: #fee2e2; color: #dc2626; }
        .badge-feature { background-color: #fef3c7; color: #d97706; }
        .badge-improvement { background-color: #dbeafe; color: #2563eb; }
        .badge-general { background-color: #f3f4f6; color: #6b7280; }
        .priority {
            margin: 8px 0;
        }
        .priority-critical { color: #dc2626; font-weight: 600; }
        .priority-high { color: #ea580c; font-weight: 600; }
        .priority-medium { color: #2563eb; }
        .priority-low { color: #6b7280; }
        .message-box {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
        }
        .metadata {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 12px;
            margin: 16px 0;
            font-size: 14px;
            color: #6b7280;
        }
        .metadata dt {
            font-weight: 600;
            display: inline-block;
            width: 120px;
        }
        .metadata dd {
            display: inline;
            margin: 0;
        }
        .action-button {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 16px 0;
        }
        .action-button:hover {
            background-color: #059669;
        }
        .footer {
            background-color: #f9fafb;
            padding: 16px 24px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗣️ New Feedback Submitted</h1>
        </div>
        
        <div class="content">
            <p>Hello Admin,</p>
            
            <p>A new feedback has been submitted on PLUPro and requires your attention.</p>
            
            <div class="feedback-info">
                <div style="margin-bottom: 12px;">
                    <span class="badge badge-{{ $feedback->type }}">{{ $feedback->type_display }}</span>
                    <span class="priority priority-{{ $feedback->priority }}">
                        Priority: {{ ucfirst($feedback->priority) }}
                    </span>
                </div>
                
                <h3 style="margin: 8px 0;">{{ $feedback->subject }}</h3>
                
                <div class="message-box">
                    <strong>Message:</strong><br>
                    {!! nl2br(e($feedback->message)) !!}
                </div>
                
                <div class="metadata">
                    <dl>
                        @if($user)
                        <dt>User:</dt>
                        <dd>{{ $user->name }} ({{ $user->email }})</dd><br>
                        @else
                        <dt>User:</dt>
                        <dd>Anonymous</dd><br>
                        @endif
                        
                        <dt>Submitted:</dt>
                        <dd>{{ $feedback->created_at->format('M j, Y g:i A') }}</dd><br>
                        
                        @if($feedback->metadata && isset($feedback->metadata['page_url']))
                        <dt>Page URL:</dt>
                        <dd><a href="{{ $feedback->metadata['page_url'] }}" style="color: #2563eb;">{{ $feedback->metadata['page_url'] }}</a></dd><br>
                        @endif
                        
                        @if($feedback->metadata && isset($feedback->metadata['user_agent']))
                        <dt>Browser:</dt>
                        <dd style="word-break: break-all;">{{ $feedback->metadata['user_agent'] }}</dd><br>
                        @endif
                        
                        <dt>Feedback ID:</dt>
                        <dd>#{{ $feedback->id }}</dd>
                    </dl>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $adminUrl }}" class="action-button">
                    View & Manage Feedback
                </a>
            </div>
            
            <p style="color: #6b7280; font-size: 14px;">
                You can assign this feedback to an admin, update its status, and respond to the user directly from the admin panel.
            </p>
        </div>
        
        <div class="footer">
            <p>PLUPro Admin Notification System<br>
            <a href="{{ url('/admin/feedback') }}" style="color: #10b981;">Manage All Feedback</a></p>
        </div>
    </div>
</body>
</html>