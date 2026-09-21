<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>You're offline — Trenakt</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F7F4EE;
            color: #14130F;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background: #14130F;
                color: #FFFFFF;
            }
            .card p.desc {
                color: #9CA3AF;
            }
        }
        .card {
            text-align: center;
            max-width: 340px;
        }
        .icon-badge {
            width: 56px;
            height: 56px;
            border-radius: 9999px;
            background: rgba(15, 138, 79, 0.12);
            color: #0F8A4F;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 8px;
        }
        p.desc {
            font-size: 14px;
            color: #6B7280;
            margin: 0 0 32px;
            line-height: 1.5;
        }
        button {
            background: #0F8A4F;
            color: #fff;
            border: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            padding: 10px 20px;
            cursor: pointer;
        }
        button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-badge">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>
        <h1>You're offline</h1>
        <p class="desc">Check your internet connection. Some pages you've already visited may still be available.</p>
        <button type="button" onclick="window.location.reload()">Try again</button>
    </div>
</body>
</html>