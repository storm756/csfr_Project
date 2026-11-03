/* Module 9 · IoT/OT & Smart-Device Safety Fundamentals · 10 challenges · one format throughout: Fix the settings
   Question pattern follows the CCFR_All_Assessments set. */
window.CCFR_MODULE = {
 "code": "m09",
 "num": "09",
 "kicker": "Module 9 · Assessment",
 "title": "IoT/OT & Smart-Device Safety Fundamentals",
 "gameTitle": "Secure the Smart Home",
 "tagline": "Change every default password. Router first. Ask “who else can see this?”. For OT: escalate, never operate.",
 "story": "Ten Fix-the-settings panels. Tap each setting to change it to the safe value, leave the ones that are already safe, then press Apply.",
 "minutes": 15,
 "icon": "<svg viewBox=\"0 0 32 32\" fill=\"none\" stroke=\"#fff\" stroke-width=\"2.4\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M4 15l12-10 12 10\"/><path d=\"M8 13v14h16V13\"/><path d=\"M12.5 20.5a5 5 0 0 1 7 0M14.6 23a2 2 0 0 1 2.8 0\"/></svg>",
 "revise": "Revise Module 9: Topic 2 (why devices get hacked), Topic 3 (router), Topic 4 (cameras and CCTV), Topic 5 (wearables and trackers) and Topic 6 (incident response and OT).",
 "questions": [
  {
   "type": "settings",
   "title": "Harden this home router.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Admin password",
     "kind": "seg",
     "opts": [
      "admin (factory default)",
      "Unique passphrase"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Wi-Fi security",
     "kind": "seg",
     "opts": [
      "WEP",
      "WPA2-AES or WPA3",
      "Open"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Firmware",
     "kind": "seg",
     "opts": [
      "Old version",
      "Latest version"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Guest network",
     "kind": "seg",
     "opts": [
      "Off",
      "On, for smart devices and visitors"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "WPS",
     "kind": "seg",
     "opts": [
      "On",
      "Off"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Remote management",
     "kind": "seg",
     "opts": [
      "On",
      "Off"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "The router is the front door of every device behind it."
  },
  {
   "type": "settings",
   "title": "This router is partly set up. Fix only what is risky.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Wi-Fi security",
     "kind": "seg",
     "opts": [
      "WPA3",
      "WEP"
     ],
     "start": 0,
     "want": 0
    },
    {
     "t": "Admin password",
     "kind": "seg",
     "opts": [
      "Same as the Wi-Fi password",
      "Different from the Wi-Fi password"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "UPnP",
     "kind": "seg",
     "opts": [
      "On",
      "Off"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Firmware updates",
     "kind": "seg",
     "opts": [
      "Automatic",
      "Never"
     ],
     "start": 1,
     "want": 0
    },
    {
     "t": "Guest network",
     "kind": "seg",
     "opts": [
      "On",
      "Off"
     ],
     "start": 0,
     "want": 0
    }
   ],
   "why": "Two passwords matter: the Wi-Fi password and the admin password. Leave safe settings alone."
  },
  {
   "type": "settings",
   "title": "Secure this shop camera.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Password",
     "kind": "seg",
     "opts": [
      "Default from the sticker",
      "New unique password"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Firmware",
     "kind": "seg",
     "opts": [
      "Old",
      "Current"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "App multi-factor authentication",
     "kind": "seg",
     "opts": [
      "Off",
      "On"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Public cloud sharing",
     "kind": "seg",
     "opts": [
      "On",
      "Off"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Remote access",
     "kind": "seg",
     "opts": [
      "Open to everyone",
      "Only what is needed"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "A secure camera has a changed password, current firmware, app MFA and no unnecessary remote access."
  },
  {
   "type": "settings",
   "title": "Make this camera respectful of others’ privacy.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "View",
     "kind": "seg",
     "opts": [
      "Overlooks a neighbour’s balcony",
      "Covers only the entrance"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Notice",
     "kind": "seg",
     "opts": [
      "None",
      "A notice says cameras are in use"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Recordings kept",
     "kind": "seg",
     "opts": [
      "Forever",
      "Short retention"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Live-view link",
     "kind": "seg",
     "opts": [
      "Shared in a group",
      "Not shared"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "Video of identifiable people is personal data under the DPDP Act 2023."
  },
  {
   "type": "settings",
   "title": "Set up this smart speaker.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Voice history",
     "kind": "seg",
     "opts": [
      "Kept forever",
      "Deleted regularly"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Voice purchases",
     "kind": "seg",
     "opts": [
      "Anyone can buy",
      "PIN required"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Account password",
     "kind": "seg",
     "opts": [
      "Reused from another site",
      "Unique"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Microphone mute when not needed",
     "kind": "seg",
     "opts": [
      "Never used",
      "Used"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "Ask “who else can see or hear this?” for every smart device."
  },
  {
   "type": "settings",
   "title": "Set up this smart TV.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Viewing-data sharing",
     "kind": "seg",
     "opts": [
      "Share everything",
      "Limit or turn off"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Built-in microphone and camera",
     "kind": "seg",
     "opts": [
      "Always on",
      "Off unless needed"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Default account password",
     "kind": "seg",
     "opts": [
      "Unchanged",
      "Changed"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Software updates",
     "kind": "seg",
     "opts": [
      "Off",
      "On"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "Smart TVs record viewing habits. Review the settings that are rarely changed."
  },
  {
   "type": "settings",
   "title": "Set up a child’s smartwatch.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Location sharing",
     "kind": "seg",
     "opts": [
      "Anyone with the number",
      "Only parent-approved contacts"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Default password",
     "kind": "seg",
     "opts": [
      "Unchanged",
      "Changed"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Unknown contacts",
     "kind": "seg",
     "opts": [
      "Allowed",
      "Blocked"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Privacy settings",
     "kind": "seg",
     "opts": [
      "Never reviewed",
      "Reviewed with a parent"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "Children’s watches share location. Each device has privacy settings that are rarely changed."
  },
  {
   "type": "settings",
   "title": "Set up a fitness band and its app.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Routes and location",
     "kind": "seg",
     "opts": [
      "Public",
      "Private"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Health data",
     "kind": "seg",
     "opts": [
      "Shared with every app",
      "Only with apps needed"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Profile",
     "kind": "seg",
     "opts": [
      "Public",
      "Private"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Bluetooth",
     "kind": "seg",
     "opts": [
      "Always discoverable",
      "Only when pairing"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "Fitness bands record routes and health data. Share the minimum."
  },
  {
   "type": "settings",
   "title": "Secure this shop network.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Guest Wi-Fi and billing PC",
     "kind": "seg",
     "opts": [
      "On the same network",
      "Separate"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "UPI sound-box password",
     "kind": "seg",
     "opts": [
      "Default",
      "Changed"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "POS terminal firmware",
     "kind": "seg",
     "opts": [
      "Never updated",
      "Updated"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "CCTV remote access",
     "kind": "seg",
     "opts": [
      "Open",
      "Limited and protected with MFA"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "One weak device usually means others. Check every device on the same network."
  },
  {
   "type": "settings",
   "title": "Fix the housing society’s gate-camera system.",
   "format": "Fix the settings",
   "device": "Settings panel",
   "screen": "Settings",
   "ask": "Tap a setting to change it. Leave anything that is already safe.",
   "rows": [
    {
     "t": "Installer’s default password",
     "kind": "seg",
     "opts": [
      "Kept",
      "Changed on every device"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Recorder firmware",
     "kind": "seg",
     "opts": [
      "Old",
      "Updated"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Public cloud sharing",
     "kind": "seg",
     "opts": [
      "On",
      "Off"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Camera facing a flat’s balcony",
     "kind": "seg",
     "opts": [
      "Unchanged",
      "Moved"
     ],
     "start": 0,
     "want": 1
    },
    {
     "t": "Resident notice",
     "kind": "seg",
     "opts": [
      "None",
      "Put up"
     ],
     "start": 0,
     "want": 1
    }
   ],
   "why": "The society committee fixed all of these after the feed was found on a website of exposed cameras."
  }
 ]
};
