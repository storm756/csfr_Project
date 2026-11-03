<?php
// One crossword per module (1-9), on the lesson page under "Practise it". Answers are single words, A-Z only; the page
// lays them out itself. [answer, clue].

defined('CLI_SCRIPT') || defined('MOODLE_INTERNAL') || die();

return [
    1 => [['PASSPHRASE', 'Four random words, used for one account only'], ['OTP', 'A one-time code: never share it'], ['APK', 'App file sent in a chat; never install it'],
        ['PASSKEY', 'Login without a password, where a service offers it'], ['SIMSWAP', 'A duplicate SIM issued in your name (two words, no space)'], ['MASKED', 'The only kind of Aadhaar copy to give out'], ['UPDATES', 'Keep these switched on: auto-___']],
    2 => [['PIN', 'It only ever sends money, never receives it'], ['GOLDENHOUR', 'The first sixty minutes after a loss (two words, no space)'], ['MULE', 'Account that receives and moves stolen money'],
        ['TASKSCAM', 'Paid to like videos, then asked to prepay (two words, no space)'], ['SEBI', 'Regulator to check an investment adviser'], ['QRCODE', 'Scanning one never brings money in (two letters + a word)'], ['LOANAPP', 'Instant credit that harvests your contacts (two words, no space)']],
    3 => [['DEEPFAKE', 'AI-made video of something that never happened'], ['VOICECLONE', 'A copy of someone\'s voice from a few seconds of audio (two words, no space)'], ['SAFEWORD', 'Family secret for any "emergency" call (two words, no space)'],
        ['CALLBACK', 'Hang up and dial the saved number: the ___ rule'], ['SEXTORTION', 'Threat to share images unless paid'], ['FACTCHECK', 'Verify before forwarding, e.g. PIB ___ (two words, no space)']],
    4 => [['TRIAGE', 'Sorting a case by type and urgency'], ['RED', 'Danger to a person'], ['AMBER', 'Money lost in the last 24 hours'],
        ['YELLOW', 'Older loss or harassment with no immediate threat'], ['GREEN', 'Suspicious, nothing lost'], ['MENTOR', 'Escalate every RED case to your ___'], ['INTAKE', 'The form where you record the six answers']],
    5 => [['UTR', 'The fraudster\'s footprint in a UPI payment'], ['SCREENSHOT', 'Take it first, block later'], ['EXPORT', 'Save a whole WhatsApp chat with media'],
        ['URL', 'Copy the profile ___, not just the name'], ['LOG', 'Simple dated record of every item saved'], ['CONSENT', 'Collect only what is needed, with ___'], ['HASH', 'Crypto transaction ID to save']],
    6 => [['ZEROFIR', 'Any police station must register it (two words, no space)'], ['CHAKSHU', 'Report suspicious calls and SMS here'], ['OMBUDSMAN', 'RBI channel after 30 days without a fix'],
        ['CERTIN', 'Where organisations report ransomware (no hyphen)'], ['PORTAL', 'cybercrime.gov.in is the national reporting ___'], ['NALSA', 'Free legal aid, 15100'], ['BNSS', 'Law with section 173 on FIRs']],
    7 => [['CALM', 'The first-response approach in four letters'], ['CONSENT', 'Ask for it before involving family'], ['SHAME', 'Why many victims stay silent'],
        ['TELEMANAS', 'Free 24×7 mental-health line, 14416 (no hyphen)'], ['LISTEN', 'More of this than talking'], ['REFERRAL', 'Keep a ___ list: cyber cell, legal aid, counsellors']],
    8 => [['RANSOMWARE', 'Locks files and demands payment'], ['BACKUP', 'The real ransomware cure'], ['ACCESSIBILITY', 'Permission that lets an app tap the screen for you'],
        ['TROJAN', 'Fake app that steals OTPs: banking ___'], ['DISCONNECT', 'First step when a ransom note appears'], ['EMAIL', 'The master key: secure it first']],
    9 => [['IOT', 'Everyday connected devices (abbr.)'], ['DEFAULT', 'Change this password on day one'], ['FIRMWARE', 'Device software that must be updated'],
        ['ROUTER', 'Home Wi-Fi box to secure first'], ['JUICEJACKING', 'Data theft through public USB charging (two words, no space)'], ['SCADA', 'Industrial control system (abbr.)'], ['WPA', 'Wi-Fi security standard, version 2 or 3']],
];
