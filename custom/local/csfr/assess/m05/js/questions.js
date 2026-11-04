/* Module 5 · Evidence Preservation · 10 challenges · one format throughout: Evidence bag
   Question pattern follows the CCFR_All_Assessments set. */
window.CCFR_MODULE = {
 "code": "m05",
 "num": "05",
 "kicker": "Module 5 · Assessment",
 "title": "Evidence Preservation",
 "gameTitle": "The Evidence Lab",
 "tagline": "Minimum, complete, fast. Keep the original untouched, register every item, and prove nothing changed.",
 "story": "Ten evidence-bag challenges. Tick what belongs in the bag and leave out what would harm the case or the victim.",
 "minutes": 15,
 "icon": "<svg viewBox=\"0 0 32 32\" fill=\"none\" stroke=\"#fff\" stroke-width=\"2.4\" stroke-linejoin=\"round\"><path d=\"M7 11h18l-2 16H9z\"/><path d=\"M12 11V8a4 4 0 0 1 8 0v3\"/><path d=\"M13 19l2 2 4-4\" stroke-linecap=\"round\"/></svg>",
 "revise": "Revise Module 5: Topic 2 (native record and good capture), Topic 3 (time in IST), Topic 4 (register and custody), Topic 5 (SHA-256) and Topic 6 (safeguarding and the role boundary).",
 "questions": [
  {
   "type": "pick",
   "title": "A fake buyer’s QR cost ₹6,000. Tick what goes into the bag.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "UTR / transaction ID",
     "ok": true
    },
    {
     "t": "Amount, date and time",
     "ok": true
    },
    {
     "t": "Payee UPI ID",
     "ok": true
    },
    {
     "t": "Chat with the buyer",
     "ok": true
    },
    {
     "t": "Listing link",
     "ok": true
    },
    {
     "t": "Delete the chat to feel calmer",
     "ok": false
    },
    {
     "t": "A cropped screenshot of one message",
     "ok": false
    },
    {
     "t": "Forward the chat to a friends group",
     "ok": false
    }
   ],
   "why": "Save identifiers and full records. Never delete, crop or pass evidence around chats."
  },
  {
   "type": "pick",
   "title": "An Instagram account was taken over. Tick the evidence to save.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "Security emails and login alerts",
     "ok": true
    },
    {
     "t": "Notice of a changed email or phone",
     "ok": true
    },
    {
     "t": "Screenshots of messages sent in the victim’s name",
     "ok": true
    },
    {
     "t": "The account’s profile link",
     "ok": true
    },
    {
     "t": "Delete the account so it stops",
     "ok": false
    },
    {
     "t": "Reset every setting before noting anything",
     "ok": false
    }
   ],
   "why": "Preserve what shows the takeover before changing anything."
  },
  {
   "type": "pick",
   "title": "A loan app is sending threats to contacts. Tick the evidence to save.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "App name and where it was installed from",
     "ok": true
    },
    {
     "t": "Chat and call logs",
     "ok": true
    },
    {
     "t": "Screenshots of threats from the contacts’ phones",
     "ok": true
    },
    {
     "t": "Payment records",
     "ok": true
    },
    {
     "t": "Uninstall the app before noting its name",
     "ok": false
    },
    {
     "t": "Reply with threats of your own",
     "ok": false
    }
   ],
   "why": "Note the app first. The contacts’ screenshots are sent to them, not forwarded on."
  },
  {
   "type": "pick",
   "title": "A ransom note appears on a shop computer. Tick the right actions.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "Photograph the screen",
     "ok": true
    },
    {
     "t": "Note the time first seen",
     "ok": true
    },
    {
     "t": "List the file names affected",
     "ok": true
    },
    {
     "t": "Note who uses the computer",
     "ok": true
    },
    {
     "t": "Unplug the network cable",
     "ok": true
    },
    {
     "t": "Restart it again and again",
     "ok": false
    },
    {
     "t": "Pay to see if the files return",
     "ok": false
    }
   ],
   "why": "Contain, record and escalate to the owner or IT lead. Never pay."
  },
  {
   "type": "pick",
   "title": "A fake profile uses a woman’s photos. Tick the evidence to save.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "Profile URL and user ID",
     "ok": true
    },
    {
     "t": "Posts and their dates",
     "ok": true
    },
    {
     "t": "Messages the profile sent",
     "ok": true
    },
    {
     "t": "The report link or reference",
     "ok": true
    },
    {
     "t": "Message the fake profile to confront it",
     "ok": false
    },
    {
     "t": "Rely on the display name alone",
     "ok": false
    }
   ],
   "why": "Display names change; IDs do not. Never contact the suspect."
  },
  {
   "type": "pick",
   "title": "A morphed intimate image is circulating. Tick what you may keep.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "The URLs where it appears",
     "ok": true
    },
    {
     "t": "Usernames and the platform",
     "ok": true
    },
    {
     "t": "Times it was seen",
     "ok": true
    },
    {
     "t": "A copy of the image “for proof”",
     "ok": false
    },
    {
     "t": "A forward of the image to a friend for advice",
     "ok": false
    }
   ],
   "why": "Links, not images. Then use StopNCII or Take It Down and report."
  },
  {
   "type": "pick",
   "title": "Tick what makes a good chat capture.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "Full screenshots showing the sender, date and thread",
     "ok": true
    },
    {
     "t": "Numbered scrolling sequence, such as E-01a, E-01b",
     "ok": true
    },
    {
     "t": "Chat export from the app",
     "ok": true
    },
    {
     "t": "The original left untouched in the app",
     "ok": true
    },
    {
     "t": "A cropped image of one message",
     "ok": false
    },
    {
     "t": "Forwarding messages to yourself",
     "ok": false
    },
    {
     "t": "Deleting the original after screenshots",
     "ok": false
    }
   ],
   "why": "Copies help; the original still matters."
  },
  {
   "type": "pick",
   "title": "Tick the right way to record times.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "Write date, time and IST",
     "ok": true
    },
    {
     "t": "Convert UTC by adding 5 hours 30 minutes and note it",
     "ok": true
    },
    {
     "t": "Note the gap if the phone clock is wrong",
     "ok": true
    },
    {
     "t": "Write “approx.” with a reason if unsure",
     "ok": true
    },
    {
     "t": "Invent a precise time to look neat",
     "ok": false
    },
    {
     "t": "Write the time with no date or zone",
     "ok": false
    }
   ],
   "why": "Event, capture and transfer times differ. Never invent a precise time."
  },
  {
   "type": "pick",
   "title": "Tick the right way to register and hash an item.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "Give it an item ID such as E-01",
     "ok": true
    },
    {
     "t": "Record description, source, event time and capture time",
     "ok": true
    },
    {
     "t": "Record the method and the SHA-256 hash",
     "ok": true
    },
    {
     "t": "Hash the master once and keep it unedited",
     "ok": true
    },
    {
     "t": "Edit the master to fix a typo",
     "ok": false
    },
    {
     "t": "Open the master for editing, then hash it",
     "ok": false
    }
   ],
   "why": "A matching hash shows sameness, not authorship or truth. Never edit the master."
  },
  {
   "type": "pick",
   "title": "Tick what goes on a transfer receipt to the police.",
   "format": "Evidence bag",
   "ask": "Tick what belongs in the bag. Leave out anything that would harm the evidence.",
   "checkLabel": "Check my bag",
   "items": [
    {
     "t": "Items listed with their hashes",
     "ok": true
    },
    {
     "t": "From and to, with names",
     "ok": true
    },
    {
     "t": "Date and time in IST",
     "ok": true
    },
    {
     "t": "Purpose, such as attachment to a complaint",
     "ok": true
    },
    {
     "t": "Signatures of giver and receiver",
     "ok": true
    },
    {
     "t": "Handing the items over in a personal chat",
     "ok": false
    },
    {
     "t": "No receipt, to save time",
     "ok": false
    }
   ],
   "why": "Every item, every transfer, every signature."
  }
 ]
};
