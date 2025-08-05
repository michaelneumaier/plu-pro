<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Update - PLUPro</title>
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
        .status-update {
            background-color: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin: 0 8px;
        }
        .status-open { background-color: #fef3c7; color: #d97706; }
        .status-in_progress { background-color: #dbeafe; color: #2563eb; }
        .status-resolved { background-color: #dcfce7; color: #16a34a; }
        .status-closed { background-color: #f3f4f6; color: #6b7280; }
        .arrow {
            font-size: 18px;
            color: #6b7280;
            margin: 0 8px;
        }
        .feedback-summary {
            background-color: #f9fafb;
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
            margin-right: 8px;
        }
        .badge-bug { background-color: #fee2e2; color: #dc2626; }
        .badge-feature { background-color: #fef3c7; color: #d97706; }
        .badge-improvement { background-color: #dbeafe; color: #2563eb; }
        .badge-general { background-color: #f3f4f6; color: #6b7280; }
        .response-box {
            background-color: #f0fdf4;
            border: 1px solid #16a34a;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
        }
        .response-box h4 {
            margin: 0 0 8px 0;
            color: #16a34a;
        }
        .footer {
            background-color: #f9fafb;
            padding: 16px 24px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .thank-you {
            background-color: #fef9e7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Feedback Update</h1>
        </div>
        
        <div class="content">
            @if($user)
            <p>Hello {{ $user->name }},</p>
            @else
            <p>Hello,</p>
            @endif
            
            <p>Your feedback has been updated! Here's what's changed:</p>
            
            <div class="status-update">
                <h3 style="margin: 0 0 16px 0;">Status Change</h3>
                <div>
                    <span class="status-badge status-{{ $oldStatus }}">
                        {{ ucfirst(str_replace('_', ' ', $oldStatus)) }}
                    </span>
                    <span class="arrow">→</span>
                    <span class="status-badge status-{{ $newStatus }}">
                        {{ $feedback->status_display }}
                    </span>
                </div>
            </div>
            
            <div class="feedback-summary">
                <h4 style="margin: 0 0 8px 0;">Your Feedback</h4>
                <div style="margin-bottom: 8px;">
                    <span class="badge badge-{{ $feedback->type }}">{{ $feedback->type_display }}</span>
                    <span style="color: #6b7280; font-size: 14px;">
                        Submitted {{ $feedback->created_at->format('M j, Y') }}
                    </span>
                </div>
                <h5 style="margin: 8px 0;">{{ $feedback->subject }}</h5>
                <p style="color: #6b7280; font-size: 14px; margin: 8px 0;">
                    "{{ Str::limit($feedback->message, 100) }}"
                </p>
            </div>
            
            @if($feedback->admin_response && $feedback->status === 'resolved')
            <div class="response-box">
                <h4>📝 Admin Response</h4>
                <p style="margin: 0;">{!! nl2br(e($feedback->admin_response)) !!}</p>
                @if($feedback->responded_at)
                <small style="color: #16a34a; font-style: italic;">
                    Responded on {{ $feedback->responded_at->format('M j, Y g:i A') }}
                </small>
                @endif
            </div>
            @endif
            
            @if($feedback->status === 'resolved')
            <div class="thank-you">
                <h4 style="margin: 0 0 8px 0; color: #f59e0b;">🎉 Thank You!</h4>
                <p style="margin: 0;">
                    We've marked your feedback as resolved. Thank you for helping us improve PLUPro!
                    If you have any additional comments or concerns, feel free to submit new feedback.
                </p>
            </div>
            @elseif($feedback->status === 'in_progress')
            <div style="background-color: #eff6ff; border: 1px solid #3b82f6; border-radius: 6px; padding: 16px; margin: 16px 0;">
                <h4 style="margin: 0 0 8px 0; color: #3b82f6;">🔄 In Progress</h4>
                <p style="margin: 0;">
                    We're actively working on your feedback. We'll keep you updated as we make progress.
                </p>
            </div>
            @endif
            
            <p style="color: #6b7280; font-size: 14px;">
                <strong>Feedback ID:</strong> #{{ $feedback->id }}<br>
                <strong>Last Updated:</strong> {{ $feedback->updated_at->format('M j, Y g:i A') }}
            </p>
        </div>
        
        <div class="footer">
            <p>PLUPro Feedback System<br>
            Thanks for helping us improve your PLU experience!</p>
        </div>
    </div>
</body>
</html>