import base64
import json
import re
import urllib.request
import os

md_path = 'ApkVault_Complete_Report.md'
assets_dir = 'assets/diagrams'
os.makedirs(assets_dir, exist_ok=True)

with open(md_path, 'r', encoding='utf-8') as f:
    content = f.read()

counter = 1
def replacer(match):
    global counter
    code = match.group(1).strip()
    payload = {'code': code, 'mermaid': {'theme': 'default', 'backgroundColor': 'white'}}
    encoded = base64.urlsafe_b64encode(json.dumps(payload).encode()).decode().replace('=', '')
    url = 'https://mermaid.ink/img/' + encoded
    
    img_path = f"{assets_dir}/diagram_{counter}.png"
    
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req) as response, open(img_path, 'wb') as out_file:
            out_file.write(response.read())
    except Exception as e:
        print(f"Failed to download {url}: {e}")
        res = f"![Diagram]({url})"
        counter += 1
        return res
        
    res = f"![Diagram]({img_path})"
    counter += 1
    return res

new_content = re.sub(r'```mermaid(.*?)```', replacer, content, flags=re.DOTALL)

with open(md_path, 'w', encoding='utf-8') as f:
    f.write(new_content)

print(f"Converted {counter-1} diagrams.")
