"""Turn the teammates' module PDFs (text extracted to .txt) into setup/modules_ext.json.
Pages 4-8 of each 8-page module follow one pattern; this reads them into fields the lesson builder draws:
  steps   (page 4: title, intro, four numbered steps, a "stop and check" prompt)
  cards   (page 5: title, intro, six situation cards with two labelled lines each, a "how to think" note)
  lab     (page 6: title, intro, three stations, expected performance)
  cases   (page 7: title, intro, three role-play cases, observer rubric, discussion prompt)
  sheet   (page 8: title, intro, six questions, a script, a self-check)
Usage: python parse_modules.py <folder with .txt files> <out.json>"""
import json, re, sys, glob, os

NOISE = re.compile(r'^(Scammed\? Call 1930.*|Community Cyber First Responder ·.*|PAGE \d+ OF \d+|MODULE \d+ · .*PAGE \d+ OF \d+|MODULE \d+ · [A-Z&,\-/ ]+|Learner / team.*|Date: _+.*|□|0\s+1\s+2)$')

def lines(page):
    out = []
    for l in page.splitlines():
        l = re.sub(r'\s+', ' ', l).strip()
        if l and not NOISE.match(l):
            out.append(l)
    return out

def join(ls):
    return re.sub(r'\s+', ' ', ' '.join(ls)).replace(' ,', ',').strip()

def caps(l):
    letters = [c for c in l if c.isalpha()]
    return len(letters) > 7 and not l.endswith('.') and sum(c.isupper() for c in letters) / len(letters) > .85

ACR = {'Upi': 'UPI', 'Sms': 'SMS', 'Cctv': 'CCTV', 'Pc': 'PC', 'Ot': 'OT', 'Apk': 'APK', 'Whatsapp': 'WhatsApp', 'Usb': 'USB', 'Id': 'ID', 'Otp': 'OTP', 'Ai': 'AI', 'Kyc': 'KYC', 'Wi-fi': 'Wi-Fi', 'Fir': 'FIR', 'Cert-in': 'CERT-In', 'Posco': 'POCSO', 'Pocso': 'POCSO'}
def nice(s):
    s = s.lower(); i = next((k for k, c in enumerate(s) if c.isalpha()), 0); s = s[:i] + s[i:i + 1].upper() + s[i + 1:]
    s = s.replace(' i ', ' I ')
    return ' '.join(ACR.get(w.capitalize(), ACR.get(w, w)) if w.lower().capitalize() in ACR else w for w in s.split(' ')).replace('’S', '’s')

def split_on(ls, test):
    """Groups of lines, a new group at each line where test(line) is true."""
    groups, cur = [], None
    for l in ls:
        if test(l):
            cur = [l]; groups.append(cur)
        elif cur is not None:
            cur.append(l)
    return groups

def page_steps(ls):
    title = ls[0]; i = 1; intro = []
    while i < len(ls) and not re.fullmatch(r'[1-4]', ls[i]):
        intro.append(ls[i]); i += 1
    stop = next((k for k, l in enumerate(ls) if l.startswith('STOP AND CHECK')), len(ls))
    steps = []
    for g in split_on(ls[i:stop], lambda l: re.fullmatch(r'[1-4]', l) is not None):
        steps.append({'h': nice(g[1]) if len(g) > 1 else '', 'p': join(g[2:])})
    return {'title': nice(title), 'intro': join(intro), 'steps': steps, 'check': join(ls[stop + 1:])}

def page_cards(ls):
    title = ls[0]; think = next((k for k, l in enumerate(ls) if l.startswith('HOW TO THINK')), len(ls))
    body = ls[1:think]; i = 0; intro = []
    while i < len(body) and not caps(body[i]):
        intro.append(body[i]); i += 1
    cards = []
    for g in split_on(body[i:], caps):
        text = join(g[1:])
        m = re.match(r'([A-Z][A-Za-z ]{1,24}):\s*(.*?)\s+([A-Z][A-Za-z ]{1,24}):\s*(.*)$', text)
        if m:
            cards.append({'h': nice(g[0]), 'k1': m.group(1), 'v1': m.group(2), 'k2': m.group(3), 'v2': m.group(4)})
        else:
            cards.append({'h': nice(g[0]), 'k1': '', 'v1': text, 'k2': '', 'v2': ''})
    t = join(ls[think + 1:]); m = re.match(r'([A-Z][A-Za-z ]{1,24}):\s*(.*)$', t)
    return {'title': nice(title), 'intro': join(intro), 'cards': cards, 'thinkk': m.group(1) if m else '', 'think': m.group(2) if m else t}

def page_lab(ls):
    title = ls[0]; exp = next((k for k, l in enumerate(ls) if l.startswith('EXPECTED PERFORMANCE')), len(ls))
    i = next((k for k, l in enumerate(ls) if l.startswith('STATION')), exp)
    st = [{'h': nice(g[0].split('·', 1)[-1].strip()) if '·' in g[0] else g[0], 'k': g[0].split('·')[0].strip().title(), 'p': join(g[1:])}
          for g in split_on(ls[i:exp], lambda l: l.startswith('STATION'))]
    e = join(ls[exp + 1:]); e = re.sub(r'^Success looks like this:\s*', '', e)
    return {'title': nice(title), 'intro': join(ls[1:i]), 'stations': st, 'expect': e}

def page_cases(ls):
    title = ls[0]; obs = next((k for k, l in enumerate(ls) if l.startswith('OBSERVER FEEDBACK')), len(ls))
    i = next((k for k, l in enumerate(ls) if l.startswith('CASE ')), obs)
    cases = [{'k': g[0].split('·')[0].strip().title(), 'h': nice(g[0].split('·', 1)[-1].strip()), 'p': join(g[1:])}
             for g in split_on(ls[i:obs], lambda l: l.startswith('CASE '))]
    disc = next((k for k, l in enumerate(ls) if l.startswith('Discuss:')), len(ls))
    rubric = [l for l in ls[obs + 1:disc]]
    return {'title': nice(title), 'intro': join(ls[1:i]), 'cases': cases, 'rubric': rubric, 'discuss': re.sub(r'^Discuss:\s*', '', join(ls[disc:]))}

def page_sheet(ls):
    title = ls[0]; q0 = next((k for k, l in enumerate(ls) if re.match(r'1\. ', l)), len(ls))
    qs, k = [], q0
    while k < len(ls) and re.match(r'\d\. ', ls[k]):
        qs.append(ls[k][3:]); k += 1
    rest = ls[k:]; sc = next((j for j, l in enumerate(rest) if l.startswith('Self-check:')), len(rest))
    return {'title': nice(title), 'intro': join(ls[1:q0]), 'questions': qs, 'scripth': nice(rest[0]) if rest else '', 'script': join(rest[1:sc]),
            'selfcheck': re.sub(r'^Self-check:\s*', '', join(rest[sc:]))}

def main(src, out):
    data = {}
    for f in sorted(glob.glob(os.path.join(src, 'CSFR_Module_0*_8_Pages.txt'))):
        n = int(re.search(r'Module_(\d+)', f).group(1))
        pages = open(f, encoding='utf-8').read().split('\f')
        # pages 4-8 are the last five pages
        p = [lines(x) for x in pages[-5:]]
        data[n] = {'steps': page_steps(p[0]), 'cards': page_cards(p[1]), 'lab': page_lab(p[2]), 'cases': page_cases(p[3]), 'sheet': page_sheet(p[4])}
    json.dump(data, open(out, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    for n, d in data.items():
        print(n, len(d['steps']['steps']), 'steps', len(d['cards']['cards']), 'cards', len(d['lab']['stations']), 'stations', len(d['cases']['cases']), 'cases',
              len(d['cases']['rubric']), 'rubric', len(d['sheet']['questions']), 'q', '|', d['cards']['cards'][0]['k1'] if d['cards']['cards'] else '?', '/', d['cards']['cards'][0]['k2'] if d['cards']['cards'] else '?')

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
