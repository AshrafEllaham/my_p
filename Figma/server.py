import http.server
import socketserver
import urllib.parse
import json
import os
from datetime import datetime

PORT = 8085
DIRECTORY = os.path.dirname(os.path.abspath(__file__))
STATE_FILE = os.path.join(DIRECTORY, ".active_screen_state.json")

class TrackerHandler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=DIRECTORY, **kwargs)

    def do_OPTIONS(self):
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()

    def do_GET(self):
        parsed = urllib.parse.urlparse(self.path)
        if parsed.path.startswith('/api/hover') or parsed.path.startswith('/api/active'):
            query = urllib.parse.parse_qs(parsed.query)
            file_name = query.get('file', [''])[0]
            title = query.get('title', [''])[0]
            section = query.get('section', [''])[0]
            theme = query.get('theme', ['light'])[0]
            lang = query.get('lang', ['ar'])[0]
            action = "hover" if "hover" in parsed.path else "active"

            state = {
                "file": file_name,
                "title": title,
                "section": section,
                "theme": theme,
                "lang": lang,
                "action": action,
                "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            }
            try:
                with open(STATE_FILE, 'w', encoding='utf-8') as f:
                    json.dump(state, f, ensure_ascii=False, indent=2)
            except Exception as e:
                pass

            self.send_response(200)
            self.send_header('Content-Type', 'application/json; charset=utf-8')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            self.wfile.write(json.dumps({"ok": True, "state": state}, ensure_ascii=False).encode('utf-8'))
            return

        elif parsed.path == '/api/current':
            self.send_response(200)
            self.send_header('Content-Type', 'application/json; charset=utf-8')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            if os.path.exists(STATE_FILE):
                with open(STATE_FILE, 'r', encoding='utf-8') as f:
                    self.wfile.write(f.read().encode('utf-8'))
            else:
                self.wfile.write(b'{}')
            return

        super().do_GET()

if __name__ == '__main__':
    socketserver.TCPServer.allow_reuse_address = True
    with socketserver.TCPServer(("", PORT), TrackerHandler) as httpd:
        print(f"Server with AI Screen Tracker running on http://localhost:{PORT}")
        httpd.serve_forever()
