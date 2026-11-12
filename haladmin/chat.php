<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat Interface</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fb;
            display: flex;
            height: 100vh;
        }

        .chat-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        .sidebar {
            width: 300px;
            background-color: #ffffff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background-color: #3b82f6;
            color: white;
            text-align: center;
            font-weight: 600;
        }

        .user-list {
            flex-grow: 1;
            overflow-y: auto;
        }

        .user {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }

        .user:hover {
            background-color: #f1f5f9;
        }

        .user img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-name {
            font-weight: 500;
        }

        .chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
        }

        .chat-header {
            padding: 20px;
            background-color: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header .chat-title {
            font-weight: 600;
        }

        .chat-body {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 60%;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.5;
        }

        .message.received .message-content {
            background-color: #f1f5f9;
            color: #333;
        }

        .message.sent .message-content {
            background-color: #3b82f6;
            color: white;
        }

        .chat-footer {
            padding: 15px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }

        .chat-footer input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            font-size: 14px;
        }

        .chat-footer button {
            margin-left: 10px;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .chat-footer button:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Sidebar for user list -->
        <div class="sidebar">
            <div class="sidebar-header">User List</div>
            <div class="user-list">
                <div class="user" data-user-id="1">
                    <img src="https://via.placeholder.com/40" alt="User">
                    <span class="user-name">John Doe</span>
                </div>
                <div class="user" data-user-id="2">
                    <img src="https://via.placeholder.com/40" alt="User">
                    <span class="user-name">Jane Smith</span>
                </div>
            </div>
        </div>

        <!-- Chat main area -->
        <div class="chat-main">
            <div class="chat-header">
                <span class="chat-title">Chat with John Doe</span>
            </div>

            <div class="chat-body">
                <div class="message received">
                    <div class="message-content">Hi, how can I help you?</div>
                </div>
                <div class="message sent">
                    <div class="message-content">I have an issue with my account.</div>
                </div>
            </div>

            <div class="chat-footer">
                <input type="text" placeholder="Type a message...">
                <button>Send</button>
            </div>
        </div>
    </div>
</body>
</html>
