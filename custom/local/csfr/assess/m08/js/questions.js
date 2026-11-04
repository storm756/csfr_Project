/* Module 8 · Ransomware, Malware & Account-Compromise Response · 6 crosswords. Each word: dir (A across / D down), r and c (row and column, from 0), a (answer, letters only), clue, why. Edit a crossword by changing its words; the numbers are worked out automatically. */
window.CCFR_MODULE = {
 "code": "m08",
 "num": "08",
 "kicker": "Module 8 · Assessment",
 "title": "Ransomware, Malware & Account-Compromise Response",
 "gameTitle": "The Recovery Clinic",
 "tagline": "Disconnect, do not wipe. Clean device, email first. Never pay, always escalate.",
 "story": "Six crosswords, one for each stage of a response: spot, isolate, recover, swap, ransomware and escalate. Tap a square or a clue, then type with the on-screen keys. You score for every word you get right.",
 "minutes": 15,
 "icon": "<svg viewBox=\"0 0 32 32\" fill=\"none\" stroke=\"#fff\" stroke-width=\"2.4\" stroke-linecap=\"round\"><ellipse cx=\"16\" cy=\"18\" rx=\"7\" ry=\"9\"/><path d=\"M16 9V5M11 6l2 4M21 6l-2 4M9 15H4M9 21H4M23 15h5M23 21h5M16 13v14\"/></svg>",
 "revise": "Revise Module 8: Topic 1 (fake APKs), Topic 2 (signs of compromise), Topic 3 (isolate and preserve), Topic 4 (recovery and lockdown), Topic 5 (SIM swap) and Topic 6 (ransomware and backups).",
 "questions": [
  {
   "type": "crossword",
   "title": "Crossword 1 · Spot the infection",
   "format": "Crossword",
   "mod": "Topic 1–2",
   "scene": "A citizen’s phone has started acting strangely after an app was installed from a chat link. Fill the grid to name the signs and the cause.",
   "sceneLabel": "Case",
   "ask": "Tap a square or a clue, then type with the keys. Tap the same square again to switch between across and down.",
   "words": [
    {
     "dir": "A",
     "r": 0,
     "c": 0,
     "a": "PERMISSION",
     "clue": "Accessibility ___ lets a bad app read the screen",
     "why": "Accessibility permission lets a bad app read the screen."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 5,
     "a": "SCREEN",
     "clue": "A remote-access app lets a stranger watch your ___",
     "why": "Remote-access apps expose what is on the screen."
    },
    {
     "dir": "A",
     "r": 2,
     "c": 4,
     "a": "TROJAN",
     "clue": "A banking ___ reads OTPs and screens on the phone",
     "why": "A banking trojan reads OTPs and screens on the phone."
    },
    {
     "dir": "A",
     "r": 4,
     "c": 1,
     "a": "STORE",
     "clue": "Install apps only from the official app ___",
     "why": "Official stores only."
    },
    {
     "dir": "D",
     "r": 3,
     "c": 2,
     "a": "OTP",
     "clue": "One-time password a bad app can read from your SMS (3 letters)",
     "why": "An OTP is a key, never information to share."
    },
    {
     "dir": "D",
     "r": 2,
     "c": 8,
     "a": "APK",
     "clue": "A fake app file installed from a link in a chat (3 letters)",
     "why": "Install apps only from the official store; a fake APK can read OTPs and mirror the screen."
    }
   ],
   "why": ""
  },
  {
   "type": "crossword",
   "title": "Crossword 2 · Isolate and preserve",
   "format": "Crossword",
   "mod": "Topic 3",
   "scene": "A “bank officer” is on the line and a remote-access app is open on the citizen’s phone. Fill the grid with your first actions.",
   "sceneLabel": "Case",
   "ask": "Tap a square or a clue, then type with the keys. Tap the same square again to switch between across and down.",
   "words": [
    {
     "dir": "A",
     "r": 0,
     "c": 0,
     "a": "PHOTOGRAPH",
     "clue": "Preserve first: ___ the app list and suspicious screens",
     "why": "Photograph before anything changes."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 7,
     "a": "AIRPLANE",
     "clue": "Switch on ___ mode to cut the connection",
     "why": "Airplane mode isolates the phone; also turn Wi-Fi and Bluetooth off."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 3,
     "a": "TIMES",
     "clue": "Record the ___ of each event",
     "why": "Record times before handing over."
    },
    {
     "dir": "A",
     "r": 7,
     "c": 1,
     "a": "ISOLATE",
     "clue": "First step for a compromised device: ___ it",
     "why": "Isolate first, investigate later."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 0,
     "a": "POLICE",
     "clue": "Hand over to the ___ after preserving",
     "why": "Isolate, preserve, then route."
    },
    {
     "dir": "A",
     "r": 5,
     "c": 4,
     "a": "CLEAN",
     "clue": "Call the bank from a ___ phone",
     "why": "Use a device the attacker never controlled."
    },
    {
     "dir": "A",
     "r": 3,
     "c": 5,
     "a": "WIPE",
     "clue": "Do not ___ or reset the phone",
     "why": "A wipe destroys evidence."
    }
   ],
   "why": ""
  },
  {
   "type": "crossword",
   "title": "Crossword 3 · Recover the accounts",
   "format": "Crossword",
   "mod": "Topic 4",
   "scene": "A Gmail and an Instagram account have been taken over. Fill the grid with the recovery steps.",
   "sceneLabel": "Case",
   "ask": "Tap a square or a clue, then type with the keys. Tap the same square again to switch between across and down.",
   "words": [
    {
     "dir": "A",
     "r": 2,
     "c": 0,
     "a": "FORWARDING",
     "clue": "A hidden ___ rule can silently send bank emails to the attacker",
     "why": "Recovery is not complete until hidden settings are checked."
    },
    {
     "dir": "D",
     "r": 1,
     "c": 7,
     "a": "HIDDEN",
     "clue": "Check ___ settings such as filters and recovery details",
     "why": "Hidden settings can let the attacker back in."
    },
    {
     "dir": "A",
     "r": 6,
     "c": 1,
     "a": "SESSIONS",
     "clue": "Sign out all other ___",
     "why": "Sign out unknown devices and sessions."
    },
    {
     "dir": "D",
     "r": 4,
     "c": 2,
     "a": "CLEAN",
     "clue": "Start recovery on a ___ device",
     "why": "Use a device the attacker never controlled."
    },
    {
     "dir": "A",
     "r": 8,
     "c": 1,
     "a": "UNIQUE",
     "clue": "Set a new ___ password, never reused",
     "why": "A unique passphrase for every account."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 4,
     "a": "EMAIL",
     "clue": "Secure this account first, because most others reset by it",
     "why": "The email account comes first."
    },
    {
     "dir": "A",
     "r": 0,
     "c": 0,
     "a": "HACKED",
     "clue": "instagram.com/___ is the official route for a hijacked account",
     "why": "Use the official recovery route."
    }
   ],
   "why": ""
  },
  {
   "type": "crossword",
   "title": "Crossword 4 · SIM swap",
   "format": "Crossword",
   "mod": "Topic 5",
   "scene": "The phone shows “No Service” and a password-reset alert arrives on another device. Fill the grid.",
   "sceneLabel": "Case",
   "ask": "Tap a square or a clue, then type with the keys. Tap the same square again to switch between across and down.",
   "words": [
    {
     "dir": "A",
     "r": 5,
     "c": 0,
     "a": "TELECOM",
     "clue": "Contact the ___ provider to block the swap",
     "why": "Ask the provider to restore your number."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 4,
     "a": "SERVICE",
     "clue": "The phone suddenly shows “No ___”",
     "why": "A sudden loss of signal is a classic sign."
    },
    {
     "dir": "D",
     "r": 1,
     "c": 0,
     "a": "ALERT",
     "clue": "A password-reset ___ you did not request",
     "why": "A reset alert you did not request means someone is trying to get in."
    },
    {
     "dir": "A",
     "r": 0,
     "c": 4,
     "a": "SWAP",
     "clue": "SIM ___ is when a criminal moves your number to their SIM",
     "why": "A SIM swap hands the criminal your calls and OTPs."
    },
    {
     "dir": "A",
     "r": 2,
     "c": 2,
     "a": "NCRP",
     "clue": "The national cybercrime reporting portal (4 letters)",
     "why": "File on the NCRP and keep the acknowledgement number."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 2,
     "a": "BANK",
     "clue": "Call the ___ first, from another phone",
     "why": "Order: bank, 1930, telecom, email, other accounts, NCRP."
    },
    {
     "dir": "D",
     "r": 3,
     "c": 6,
     "a": "SIM",
     "clue": "Criminals swap this card’s number to receive your OTPs (3 letters)",
     "why": "After a SIM swap the criminal receives your OTPs."
    }
   ],
   "why": ""
  },
  {
   "type": "crossword",
   "title": "Crossword 5 · Ransomware and backups",
   "format": "Crossword",
   "mod": "Topic 6",
   "scene": "A shop’s billing computer shows a ransom note. Fill the grid with what you should and should not do.",
   "sceneLabel": "Case",
   "ask": "Tap a square or a clue, then type with the keys. Tap the same square again to switch between across and down.",
   "words": [
    {
     "dir": "A",
     "r": 3,
     "c": 0,
     "a": "CERT",
     "clue": "Organisations report to ___-In within 6 hours (4 letters)",
     "why": "Report specified incidents to CERT-In within 6 hours."
    },
    {
     "dir": "D",
     "r": 1,
     "c": 0,
     "a": "BACKUP",
     "clue": "A clean ___ is the best way to recover the files",
     "why": "3-2-1: three copies, two media, one offline."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 3,
     "a": "RESTORE",
     "clue": "Test the ___ regularly, not just the backup",
     "why": "A backup you have never restored is a guess."
    },
    {
     "dir": "A",
     "r": 4,
     "c": 3,
     "a": "OFFLINE",
     "clue": "Keep one backup copy ___",
     "why": "An offline copy cannot be encrypted by the attacker."
    },
    {
     "dir": "A",
     "r": 2,
     "c": 3,
     "a": "SWACHHTA",
     "clue": "Cyber ___ Kendra offers free tools to clean infected devices",
     "why": "Cyber Swachhta Kendra provides free cleaning tools."
    },
    {
     "dir": "A",
     "r": 0,
     "c": 3,
     "a": "RANSOM",
     "clue": "The note demands this to unlock the files",
     "why": "Never pay, negotiate or try to decrypt."
    },
    {
     "dir": "D",
     "r": 1,
     "c": 10,
     "a": "PAY",
     "clue": "Never ___ the criminals (3 letters)",
     "why": "Payment funds crime and does not guarantee the files back."
    }
   ],
   "why": ""
  },
  {
   "type": "crossword",
   "title": "Crossword 6 · Respond and escalate",
   "format": "Crossword",
   "mod": "All topics",
   "scene": "A WhatsApp account is hijacked, and contacts are getting money requests. Fill the grid with the response.",
   "sceneLabel": "Case",
   "ask": "Tap a square or a clue, then type with the keys. Tap the same square again to switch between across and down.",
   "words": [
    {
     "dir": "A",
     "r": 7,
     "c": 2,
     "a": "TWOSTEP",
     "clue": "Turn on ___ verification (7 letters)",
     "why": "Two-step verification keeps the account locked if a password leaks."
    },
    {
     "dir": "D",
     "r": 3,
     "c": 7,
     "a": "LINKED",
     "clue": "Check ___ devices for ones you do not know",
     "why": "Remove unknown linked devices."
    },
    {
     "dir": "D",
     "r": 1,
     "c": 2,
     "a": "CONTACTS",
     "clue": "Warn your ___ by another channel",
     "why": "Warn contacts by another channel; those who paid call 1930."
    },
    {
     "dir": "A",
     "r": 1,
     "c": 0,
     "a": "ESCALATE",
     "clue": "If it is bigger than you, ___ to the owner, IT lead or police",
     "why": "Escalate, never handle alone."
    },
    {
     "dir": "D",
     "r": 0,
     "c": 0,
     "a": "REGISTER",
     "clue": "___ the number again on your phone with the SMS code",
     "why": "Re-registering locks the attacker out of WhatsApp."
    },
    {
     "dir": "D",
     "r": 1,
     "c": 5,
     "a": "AMBER",
     "clue": "Triage colour for an active takeover",
     "why": "Active takeover and spreading harm is AMBER."
    },
    {
     "dir": "D",
     "r": 6,
     "c": 4,
     "a": "LOG",
     "clue": "Keep a follow-up ___ (3 letters)",
     "why": "A follow-up log shows what was done and when."
    }
   ],
   "why": ""
  }
 ]
};
