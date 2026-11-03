/* Module 11 · Final Assessment · Modules 1–9 · 18 challenges · mixed formats, two per module. Edit one challenge block at a time; keep "mod" so the module profile still works. */
window.CCFR_MODULE = {
 "code": "m11",
 "num": "11",
 "kicker": "Module 11 · Final assessment",
 "title": "Final Assessment · Modules 1–9",
 "gameTitle": "The Final Shift",
 "tagline": "Eighteen challenges, two for every module. Prove you can prevent, recognise, respond and report.",
 "story": "Eighteen rounds, two for every module, and every round plays differently: fix settings, spot scams, triage cases, pack evidence, talk to people, route complaints and secure devices. Each round scores 0 to 1, and you get a score for every module at the end.",
 "minutes": 25,
 "icon": "<svg viewBox=\"0 0 32 32\" fill=\"none\" stroke=\"#fff\" stroke-width=\"2.4\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"16\" cy=\"12\" r=\"7\"/><path d=\"M11 18l-3 10 8-4 8 4-3-10\"/><path d=\"M13 12l2 2 4-4\"/></svg>",
 "groups": [
  {
   "key": "Module 1",
   "name": "1 · Cyber hygiene"
  },
  {
   "key": "Module 2",
   "name": "2 · Frauds and scams"
  },
  {
   "key": "Module 3",
   "name": "3 · Deepfakes and AI"
  },
  {
   "key": "Module 4",
   "name": "4 · Triage"
  },
  {
   "key": "Module 5",
   "name": "5 · Evidence"
  },
  {
   "key": "Module 6",
   "name": "6 · Reporting and law"
  },
  {
   "key": "Module 7",
   "name": "7 · Victim support"
  },
  {
   "key": "Module 8",
   "name": "8 · Malware and accounts"
  },
  {
   "key": "Module 9",
   "name": "9 · Smart devices"
  }
 ],
 "revise": "Use your module profile above: revise any module below 60% before the invigilated final paper and practical station.",
 "questions": [
  {
   "type": "settings",
   "title": "Meera’s phone: fix only what is risky",
   "format": "Fix the settings",
   "mod": "Module 1",
   "device": "Phone settings",
   "screen": "Security check-up",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Automatic updates",
     "kind": "seg",
     "opts": [
      "Off",
      "On"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Two-step verification on email and UPI",
     "kind": "seg",
     "opts": [
      "Off",
      "On"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Flashlight app: access to contacts and SMS",
     "kind": "seg",
     "opts": [
      "Allowed",
      "Denied"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Install apps from",
     "kind": "seg",
     "opts": [
      "Any link or chat file",
      "Official app store only"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Screen lock",
     "kind": "seg",
     "opts": [
      "Strong PIN or biometric",
      "None"
     ],
     "start": 0,
     "want": 0
    }
   ],
   "why": "Updates, two-step verification, minimum permissions and store-only installs are the core habits. The screen lock was already safe, so it should stay as it was."
  },
  {
   "type": "mc",
   "title": "Which reply keeps Priya’s accounts safest?",
   "format": "Choose one",
   "mod": "Module 1",
   "scene": "Priya reused one password on shopping, email and her bank app. A shopping site has just leaked. She asks what to do first.",
   "ask": "Choose the best advice.",
   "opts": [
    "Change only the shopping password; the others are fine",
    "Give every account its own unique passphrase, starting with email, and switch on two-step verification",
    "Use the same strong password everywhere so she remembers it",
    "Wait to see whether anything happens"
   ],
   "a": 1,
   "why": "One leak became three takeovers. Unique passphrases, email first, plus two-step verification keep an account locked even if a password leaks."
  },
  {
   "type": "spot",
   "title": "Tap every warning sign in this “bank” call.",
   "format": "Spot the signs",
   "mod": "Module 2",
   "ask": "Tap every warning sign. Leave the normal parts alone.",
   "style": "sms",
   "text": "Caller: “Sir, I am from the bank’s KYC team. Your account will be [[!frozen in one hour]]. Please [[!install this support app]] so I can help. Then [[!read me the OTP]] that arrives. If you prefer, you can also [[visit your home branch]] or use the [[official bank app]].”",
   "why": "Urgency, a remote-access app and an OTP request are the giveaways. KYC is updated at the branch or in the official app."
  },
  {
   "type": "sort",
   "title": "Which stage of the scam is each line?",
   "format": "Sort board",
   "mod": "Module 2",
   "ask": "Tap a card, then tap the stage it belongs to.",
   "bins": [
    {
     "t": "Hook",
     "d": "Gets your attention"
    },
    {
     "t": "Hurry",
     "d": "Removes your time to think"
    },
    {
     "t": "Ask",
     "d": "Wants a secret or an action"
    },
    {
     "t": "Cash-out",
     "d": "Moves the money"
    }
   ],
   "items": [
    {
     "t": "“Congratulations, you won a free phone!”",
     "b": 0,
     "why": "A reward is bait."
    },
    {
     "t": "“Pay within 10 minutes or the offer ends.”",
     "b": 1,
     "why": "A deadline stops you checking."
    },
    {
     "t": "“Share the OTP to confirm your prize.”",
     "b": 2,
     "why": "Asks for a key to your account."
    },
    {
     "t": "“Send the ₹25 fee to this personal UPI ID.”",
     "b": 3,
     "why": "The money leaves your account."
    },
    {
     "t": "“Your parcel is stuck, click to reschedule.”",
     "b": 0,
     "why": "A familiar problem is the hook."
    },
    {
     "t": "“Last warning: your account closes tonight.”",
     "b": 1,
     "why": "Fear plus a clock."
    }
   ],
   "why": "Hook, hurry, ask, cash-out. Learn the pattern and you can spot a scam you have never seen before."
  },
  {
   "type": "chat",
   "title": "A “boss” voice note asks for money",
   "format": "Talk to the person",
   "mod": "Module 3",
   "who": "Accounts clerk",
   "ask": "Choose the best reply at each step.",
   "steps": [
    {
     "say": "My boss just sent a voice note asking me to transfer ₹90,000 urgently. It sounds exactly like him. Should I pay?",
     "replies": [
      {
       "t": "Stop. Do not pay yet. A voice can be cloned from a short clip. Call your boss on his known number to confirm.",
       "s": 2,
       "why": "Verify through a second, trusted channel before paying."
      },
      {
       "t": "Pay half now and check later.",
       "s": 1,
       "why": "Any payment before verification is still a risk."
      },
      {
       "t": "Pay it. It sounds exactly like him.",
       "s": 0,
       "gate": true,
       "why": "A voice that sounds right proves nothing. This is how voice-clone fraud works."
      }
     ]
    },
    {
     "say": "He is not answering. A colleague says forward the audio to the whole office group so everyone can judge.",
     "replies": [
      {
       "t": "Do not forward. Keep the original, note who sent it and when, and report to the owner and 1930 if money moved.",
       "s": 2,
       "why": "Preserve, do not spread."
      },
      {
       "t": "Forward it to a few people only.",
       "s": 1,
       "why": "Forwarding still spreads it."
      },
      {
       "t": "Forward it to everyone with a warning.",
       "s": 0,
       "why": "Sharing a suspect clip helps it travel."
      }
     ]
    }
   ]
  },
  {
   "type": "match",
   "title": "Connect each S·I·F·T letter to its step",
   "format": "Connect the pairs",
   "mod": "Module 3",
   "leftLabel": "Letter",
   "rightLabel": "Action",
   "ask": "Tap a letter on the left, then its step on the right.",
   "pairs": [
    {
     "l": "S",
     "r": "Stop before you react or forward"
    },
    {
     "l": "I",
     "r": "Investigate the source"
    },
    {
     "l": "F",
     "r": "Find better coverage"
    },
    {
     "l": "T",
     "r": "Trace claims to the original"
    }
   ],
   "why": "A detector score is one clue only. The honest verdict may be “cannot verify yet”."
  },
  {
   "type": "sort",
   "title": "Triage each case by colour",
   "format": "Colour triage board",
   "mod": "Module 4",
   "ask": "Tap a case, then tap its colour.",
   "bins": [
    {
     "t": "Red",
     "c": "red",
     "d": "Danger to life or a child"
    },
    {
     "t": "Amber",
     "c": "amb",
     "d": "Active loss or spreading harm"
    },
    {
     "t": "Yellow",
     "c": "yel",
     "d": "Past loss, nothing active now"
    },
    {
     "t": "Green",
     "c": "grn",
     "d": "Suspicious message, no loss"
    }
   ],
   "items": [
    {
     "t": "A teenager says he wants to hurt himself over a morphed photo",
     "b": 0,
     "why": "Risk to life: 112 and stay with him."
    },
    {
     "t": "Card fraud of ₹12,000 happening right now; OTPs still arriving",
     "b": 1,
     "why": "Active loss: bank and 1930 now."
    },
    {
     "t": "Loss of ₹50,000 three weeks ago; accounts secured since",
     "b": 2,
     "why": "Past loss, nothing active: NCRP and bank the same day."
    },
    {
     "t": "A suspicious SMS about an electricity cut; nothing clicked",
     "b": 3,
     "why": "Report on Chakshu and give hygiene advice."
    },
    {
     "t": "A hijacked Instagram account is messaging the owner’s friends for money",
     "b": 1,
     "why": "Spreading harm."
    },
    {
     "t": "A child is being pressured to send images by an older stranger",
     "b": 0,
     "why": "A child in danger: police and 1098 now."
    }
   ],
   "why": "Danger first. Present danger, active loss and spread decide urgency, not the size of the loss."
  },
  {
   "type": "mc",
   "title": "Which case do you handle first?",
   "format": "Choose one",
   "mod": "Module 4",
   "scene": "Two people reach your desk at the same time. Mr Rao lost ₹3,00,000 to a fake investment two weeks ago, and nothing is happening now. Anita’s phone is showing a remote-access app open on a live call with a “bank officer”.",
   "ask": "Who is first?",
   "opts": [
    "Mr Rao, because the loss is bigger",
    "Anita, because the loss is active right now",
    "Whoever came first",
    "Neither; send both to the police station"
   ],
   "a": 1,
   "why": "Active loss and spread outrank size. Anita goes AMBER: airplane mode, then the bank from another phone. Mr Rao is YELLOW and is handled the same day."
  },
  {
   "type": "pick",
   "title": "A fake profile is blackmailing a woman. Tick what goes in the evidence bag.",
   "format": "Evidence bag",
   "mod": "Module 5",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence or the person.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "The profile URL and username ID",
     "ok": true,
     "why": "Display names change; IDs do not."
    },
    {
     "t": "Screenshots of the threats with date and time visible",
     "ok": true,
     "why": "Shows the content in context."
    },
    {
     "t": "The platform and the date you first saw it",
     "ok": true,
     "why": "Needed to request records later."
    },
    {
     "t": "A forward of the intimate image to a friend “for advice”",
     "ok": false,
     "gate": true,
     "why": "Never pass the image around. Share links, not images."
    },
    {
     "t": "A reply to the suspect asking them to stop",
     "ok": false,
     "why": "Do not contact the suspect."
    },
    {
     "t": "A deleted chat “to calm things down”",
     "ok": false,
     "why": "Never delete evidence."
    }
   ],
   "gateMsg": "Forwarding an intimate image is a safety-gate choice.",
   "why": "Minimum, complete, fast. Save identifiers and records, never delete, never forward the image, and then report."
  },
  {
   "type": "order",
   "title": "Preserve a chat on a victim’s phone",
   "format": "Put in order",
   "mod": "Module 5",
   "scene": "A victim shows you a threatening chat. Put your actions in order.",
   "ask": "Tap the steps in the order you would do them.",
   "items": [
    {
     "t": "Photograph or screenshot the chat with the number, date and time",
     "why": "Capture before anything can change."
    },
    {
     "t": "Note the event, capture and discovery times separately",
     "why": "These times differ. Never invent a precise time."
    },
    {
     "t": "Work from a copy, never the master, and record its SHA-256 hash",
     "why": "A matching hash shows the copy equals the master."
    },
    {
     "t": "Register each item on the evidence sheet",
     "why": "Every item is listed."
    },
    {
     "t": "Hand over with a signed transfer receipt",
     "why": "Chain of custody: who held it, when and why."
    }
   ],
   "why": "Keep the original untouched, register every item, and prove nothing changed."
  },
  {
   "type": "dial",
   "title": "Set the right numbers",
   "format": "Dial it in",
   "mod": "Module 6",
   "rows": [
    {
     "label": "A platform must act on a reported intimate or morphed image within",
     "start": 6,
     "unit": "hours",
     "steps": [
      -12,
      -1,
      1,
      12
     ],
     "min": 0,
     "max": 72,
     "answer": 24,
     "tol": 0,
     "why": "Platforms must act within 24 hours. Appeals go to the GAC."
    },
    {
     "label": "An organisation reports a specified incident to CERT-In within (hours)",
     "start": 24,
     "unit": "hours",
     "steps": [
      -6,
      -1,
      1,
      6
     ],
     "min": 0,
     "max": 72,
     "answer": 6,
     "tol": 0,
     "why": "Within 6 hours of noticing the incident."
    },
    {
     "label": "A bank complaint unresolved after (days) goes to the RBI Ombudsman",
     "start": 10,
     "unit": "days",
     "steps": [
      -10,
      -1,
      1,
      10
     ],
     "min": 0,
     "max": 90,
     "answer": 30,
     "tol": 0,
     "why": "After 30 days, or an unsatisfactory reply."
    }
   ],
   "why": "The right door and the right time. Never promise an outcome."
  },
  {
   "type": "build",
   "title": "Rohit lost ₹18,000 on UPI twenty minutes ago",
   "format": "Build the routing",
   "mod": "Module 6",
   "scene": "I just paid a fake buyer’s QR and ₹18,000 is gone. What do I do? Will I get it back?",
   "sceneLabel": "They say",
   "ask": "Build your answer: pick one card for each step.",
   "slots": [
    {
     "label": "First",
     "a": 0,
     "chips": [
      0,
      1,
      2
     ]
    },
    {
     "label": "Then",
     "a": 3,
     "chips": [
      3,
      4,
      5
     ]
    },
    {
     "label": "Say",
     "a": 6,
     "chips": [
      6,
      7,
      8
     ]
    }
   ],
   "chips": [
    {
     "t": "Call 1930 now so the details can reach the bank and payment app."
    },
    {
     "t": "Wait a week to see if the money returns."
    },
    {
     "t": "Post about it first to warn others."
    },
    {
     "t": "Complete the complaint on the NCRP and keep the acknowledgement number."
    },
    {
     "t": "File only a Chakshu report."
    },
    {
     "t": "Go to the RBI Ombudsman first."
    },
    {
     "t": "We will try to act fast, but I cannot promise the money will return."
    },
    {
     "t": "You will definitely get it all back.",
     "gate": true
    },
    {
     "t": "This was your fault for scanning the code."
    }
   ],
   "gateMsg": "Promising an outcome is a safety-gate choice.",
   "why": "1930 in the golden hour, then the NCRP complaint. Never promise an outcome, and never blame."
  },
  {
   "type": "build",
   "title": "Mr Sharma, 74",
   "format": "Build the reply",
   "mod": "Module 7",
   "scene": "I gave my OTP to a man from the bank. I am embarrassed. My daughter must not know.",
   "sceneLabel": "They say",
   "ask": "Build your reply: pick one card for each step.",
   "slots": [
    {
     "label": "Acknowledge",
     "a": 0,
     "chips": [
      0,
      1,
      2
     ]
    },
    {
     "label": "Act",
     "a": 3,
     "chips": [
      3,
      4,
      5
     ]
    },
    {
     "label": "Link",
     "a": 6,
     "chips": [
      6,
      7,
      8
     ]
    }
   ],
   "chips": [
    {
     "t": "This happens to many careful people. It is not your fault."
    },
    {
     "t": "You should have known better."
    },
    {
     "t": "Let’s not talk about that."
    },
    {
     "t": "Let’s call your bank and 1930 right now."
    },
    {
     "t": "Wait and see what happens tomorrow."
    },
    {
     "t": "Delete the messages first."
    },
    {
     "t": "If you wish, I will help you tell your daughter, and I will write the steps on a card."
    },
    {
     "t": "Your daughter will deal with it."
    },
    {
     "t": "You will need to handle it alone."
    }
   ],
   "why": "No blame, act in the golden hour, and offer support. Confidentiality and choice stay with the person."
  },
  {
   "type": "chat",
   "title": "“I cannot take this anymore”",
   "format": "Talk to the person",
   "mod": "Module 7",
   "who": "Citizen",
   "ask": "Choose the best response at each step.",
   "steps": [
    {
     "say": "I lost all my savings to a loan app. I cannot take this anymore. I feel like ending it.",
     "replies": [
      {
       "t": "I am really sorry. I am glad you told me. Are you safe right now? I am staying with you.",
       "s": 2,
       "why": "Look: check safety first, with warmth."
      },
      {
       "t": "Do not say that. Think of your family.",
       "s": 1,
       "why": "Well meant, but it shuts the person down."
      },
      {
       "t": "The loan was your choice. Let us look at the app.",
       "s": 0,
       "gate": true,
       "why": "Blame and skipping safety. Safety overrides everything."
      }
     ]
    },
    {
     "say": "I do not know. I might do something tonight.",
     "replies": [
      {
       "t": "Let us call 112 or Tele-MANAS 14416 together now, and I will stay with you until someone is with you.",
       "s": 2,
       "why": "Link to urgent help. 112 for danger to life; Tele-MANAS for free mental-health support."
      },
      {
       "t": "Please sleep on it and come back tomorrow.",
       "s": 1,
       "why": "Leaves a person at risk alone."
      },
      {
       "t": "Send me the loan app screenshots and we will talk Monday.",
       "s": 0,
       "gate": true,
       "why": "Treats an immediate risk as a paperwork task."
      }
     ]
    }
   ],
   "gateMsg": "Skipping a safety check for a person in danger is a safety-gate choice.",
   "why": "Look, listen, link. Safety overrides everything."
  },
  {
   "type": "order",
   "title": "A shop computer shows a ransom note",
   "format": "Put in order",
   "mod": "Module 8",
   "scene": "Files on the shop’s billing computer are locked and a note demands payment. Put your first actions in order.",
   "ask": "Tap the steps in the order you would do them.",
   "items": [
    {
     "t": "Disconnect the cable and turn off Wi-Fi; leave the machine on",
     "why": "Stops the spread. Do not wipe or reboot."
    },
    {
     "t": "Photograph the note and screen, and record the time",
     "why": "Preserve before anything changes."
    },
    {
     "t": "Tell the owner or IT lead and keep others away from the machine",
     "why": "Escalate, do not handle alone."
    },
    {
     "t": "Check clean backups and the No More Ransom site, never paying",
     "why": "Some ransomware can be decrypted free."
    },
    {
     "t": "Report: CERT-In within 6 hours for organisations, and the police if there is loss",
     "why": "The right door, at the right time."
    }
   ],
   "why": "Disconnect, do not wipe. Never pay, always escalate."
  },
  {
   "type": "cards",
   "title": "Email takeover: do now, or never?",
   "format": "Verdict cards",
   "mod": "Module 8",
   "ask": "Swipe or tap for each action.",
   "labels": [
    {
     "t": "Do now",
     "c": "grn"
    },
    {
     "t": "Never",
     "c": "red"
    }
   ],
   "items": [
    {
     "t": "Change the email password from a clean device",
     "a": 0,
     "why": "The clean device comes first."
    },
    {
     "t": "Check the forwarding rules and sign out all sessions",
     "a": 0,
     "why": "A forwarding rule can silently send bank emails to the attacker."
    },
    {
     "t": "Switch on two-step verification",
     "a": 0,
     "why": "Locks the door again."
    },
    {
     "t": "Pay the “recovery agent” a fee to get the account back",
     "a": 1,
     "gate": true,
     "why": "A recovery fee is a second fraud."
    },
    {
     "t": "Reuse the old password with one digit changed",
     "a": 1,
     "why": "Use a unique passphrase."
    },
    {
     "t": "Change the password first from the infected phone",
     "a": 1,
     "why": "The malware can read what you type."
    }
   ],
   "gateMsg": "Paying a recovery fee is a safety-gate choice.",
   "why": "Clean device, email first. Never pay, always escalate."
  },
  {
   "type": "hotspot",
   "title": "Tap every weak point in this smart home",
   "format": "Tap the picture",
   "mod": "Module 9",
   "ask": "Tap only the items that are a risk. Leave the safe ones alone.",
   "svg": "<svg viewBox=\"0 0 640 300\" role=\"img\" aria-label=\"Home network diagram\"><rect x=\"0\" y=\"0\" width=\"640\" height=\"300\" fill=\"none\"/><g data-h=\"router\"><rect class=\"ring\" x=\"240\" y=\"110\" width=\"160\" height=\"70\" rx=\"10\"/><rect x=\"250\" y=\"120\" width=\"140\" height=\"50\" rx=\"8\" fill=\"#4a5568\"/><text x=\"320\" y=\"142\" text-anchor=\"middle\" fill=\"#fff\" font-size=\"16\" font-weight=\"700\">Router</text><text x=\"320\" y=\"162\" text-anchor=\"middle\" fill=\"#e2e8f0\" font-size=\"13\">admin / admin</text></g><g data-h=\"camera\"><rect class=\"ring\" x=\"30\" y=\"20\" width=\"150\" height=\"70\" rx=\"10\"/><rect x=\"40\" y=\"30\" width=\"130\" height=\"50\" rx=\"8\" fill=\"#4a5568\"/><text x=\"105\" y=\"52\" text-anchor=\"middle\" fill=\"#fff\" font-size=\"15\" font-weight=\"700\">Door camera</text><text x=\"105\" y=\"70\" text-anchor=\"middle\" fill=\"#e2e8f0\" font-size=\"13\">pass: 12345</text></g><g data-h=\"speaker\"><rect class=\"ring\" x=\"30\" y=\"205\" width=\"150\" height=\"70\" rx=\"10\"/><rect x=\"40\" y=\"215\" width=\"130\" height=\"50\" rx=\"8\" fill=\"#4a5568\"/><text x=\"105\" y=\"237\" text-anchor=\"middle\" fill=\"#fff\" font-size=\"15\" font-weight=\"700\">Smart speaker</text><text x=\"105\" y=\"255\" text-anchor=\"middle\" fill=\"#e2e8f0\" font-size=\"13\">mic mute used</text></g><g data-h=\"wps\"><rect class=\"ring\" x=\"460\" y=\"20\" width=\"150\" height=\"70\" rx=\"10\"/><rect x=\"470\" y=\"30\" width=\"130\" height=\"50\" rx=\"8\" fill=\"#4a5568\"/><text x=\"535\" y=\"52\" text-anchor=\"middle\" fill=\"#fff\" font-size=\"15\" font-weight=\"700\">Wi-Fi panel</text><text x=\"535\" y=\"70\" text-anchor=\"middle\" fill=\"#e2e8f0\" font-size=\"13\">WPS: ON</text></g><g data-h=\"firmware\"><rect class=\"ring\" x=\"460\" y=\"205\" width=\"150\" height=\"70\" rx=\"10\"/><rect x=\"470\" y=\"215\" width=\"130\" height=\"50\" rx=\"8\" fill=\"#4a5568\"/><text x=\"535\" y=\"237\" text-anchor=\"middle\" fill=\"#fff\" font-size=\"15\" font-weight=\"700\">Smart TV</text><text x=\"535\" y=\"255\" text-anchor=\"middle\" fill=\"#e2e8f0\" font-size=\"13\">updated; guest Wi-Fi</text></g></svg>",
   "legend": "Read each label. Tap a device only if its setting is a risk.",
   "spots": {
    "router": {
     "label": "Router with the factory admin password",
     "ok": true,
     "why": "The router is the front door. Set a unique admin passphrase."
    },
    "camera": {
     "label": "Door camera with a default password",
     "ok": true,
     "why": "A default password is how botnets such as Mirai grew."
    },
    "wps": {
     "label": "WPS switched on",
     "ok": true,
     "why": "WPS and remote management should be off unless needed."
    },
    "speaker": {
     "label": "Smart speaker with the mic mute used",
     "ok": false,
     "why": "Muting the mic is a good habit."
    },
    "firmware": {
     "label": "Updated smart TV on the guest network",
     "ok": false,
     "why": "Updated and on the guest network is the safer set-up."
    }
   },
   "why": "Change every default password and fix the router first. Ask “who else can see this?”."
  },
  {
   "type": "settings",
   "title": "Secure this shop’s network and camera",
   "format": "Fix the settings",
   "mod": "Module 9",
   "device": "Shop router and CCTV",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Router admin password",
     "kind": "seg",
     "opts": [
      "Factory default",
      "Unique, not the Wi-Fi password"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "CCTV on the same network as billing",
     "kind": "seg",
     "opts": [
      "Same network",
      "Guest or separate network"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Remote management",
     "kind": "seg",
     "opts": [
      "On",
      "Off unless needed"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Camera pointing at the staff changing area",
     "kind": "seg",
     "opts": [
      "Yes",
      "Turned away or removed"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Wi-Fi security",
     "kind": "seg",
     "opts": [
      "WPA2-AES or WPA3",
      "Open"
     ],
     "start": 0,
     "want": 0
    }
   ],
   "why": "Router first, separate networks, no needless remote access, and respect others’ privacy. The Wi-Fi security was already safe."
  }
 ]
};

/* Shuffle the order of the 18 challenges on every page load and every "Try again".
   (Inside each challenge, cards, options, chips and steps already shuffle by themselves.) */
window.CCFR_MODULE.reshuffle = function () {
  var a = window.CCFR_MODULE.questions;
  for (var i = a.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)); var t = a[i]; a[i] = a[j]; a[j] = t; }
};
window.CCFR_MODULE.reshuffle();
