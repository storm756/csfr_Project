<?php
// CSFR demo content, condensed from the HOD's participant handbook
// ("Community Cyber First Responder - Infographic Modules") and the Module 1 trainer guide.
// Each module follows the handbook pattern: what it is, how it works, ask yourself,
// Stop - Save - Report, protect yourself, remember.

defined('CLI_SCRIPT') || die();

return [
    // Modules 1-9 come from the teammates' revised module PDFs; see modules.php.
    'modules' => require(__DIR__ . '/modules.php'),

    // The handbook's 25-question bank. 'm' = module it belongs to; 'a' = correct option (0-3).
    'bank' => [
        ['m' => 6, 'q' => 'Which number is the National Cyber Crime Helpline for financial fraud?', 'o' => ['100', '1930', '1098', '1915'], 'a' => 1],
        ['m' => 2, 'q' => 'To receive money on UPI you must:', 'o' => ['Enter your UPI PIN', 'Scan the sender\'s QR code', 'Do nothing; no PIN is needed to receive', 'Share the OTP'], 'a' => 2],
        ['m' => 1, 'q' => 'The 6-digit code WhatsApp sends by SMS should be:', 'o' => ['Shared with WhatsApp support', 'Never shared with anyone', 'Shared with family', 'Posted in the group'], 'a' => 1],
        ['m' => 1, 'q' => 'Which portal lets you check mobile connections registered in your name?', 'o' => ['DigiLocker', 'Sanchar Saathi', 'UMANG', 'SCORES'], 'a' => 1],
        ['m' => 3, 'q' => 'A "digital arrest" by CBI over a video call is:', 'o' => ['A new legal procedure', 'Allowed in urgent cases', 'Not a real procedure; it is a fraud', 'Done only by state police'], 'a' => 2],
        ['m' => 2, 'q' => 'Reporting an unauthorised bank transaction within how many working days can limit your liability to zero (if you are not at fault)?', 'o' => ['7', '3', '10', '30'], 'a' => 1],
        ['m' => 3, 'q' => 'The best defence against a voice-clone emergency call is:', 'o' => ['Listening for background noise', 'Calling back on a known number or asking the safe word', 'Asking the caller\'s name', 'Paying a small amount first'], 'a' => 1],
        ['m' => 2, 'q' => 'A "task-based job" that pays small amounts, then asks you to prepay for bigger tasks, is:', 'o' => ['A genuine job', 'A fraud', 'A government scheme', 'An internship'], 'a' => 1],
        ['m' => 5, 'q' => 'The most important detail to save after a UPI fraud is:', 'o' => ['The fraudster\'s profile photo', 'The UTR / transaction ID', 'Your account balance', 'The bank branch address'], 'a' => 1],
        ['m' => 5, 'q' => 'Under which law are electronic records admissible as evidence in India today?', 'o' => ['Indian Evidence Act, 1872', 'Bharatiya Sakshya Adhiniyam, 2023', 'IT Rules, 2021', 'DPDP Act, 2023'], 'a' => 1],
        ['m' => 6, 'q' => 'Cheating by personation using a computer resource is covered under:', 'o' => ['IT Act s.43', 'IT Act s.66D', 'IT Act s.67', 'BNS s.78'], 'a' => 1],
        ['m' => 6, 'q' => 'A police station refuses a complaint because the fraud happened in another state. The citizen can ask for:', 'o' => ['A lawyer', 'A Zero FIR', 'A refund', 'An RTI'], 'a' => 1],
        ['m' => 8, 'q' => 'Besides the police, where should a ransomware attack on an organisation be reported?', 'o' => ['RBI', 'SEBI', 'CERT-In', 'TRAI'], 'a' => 2],
        ['m' => 8, 'q' => 'The first action when a shop computer shows a ransom note is:', 'o' => ['Pay quickly', 'Restart the computer', 'Disconnect it from the network', 'Delete the note'], 'a' => 2],
        ['m' => 8, 'q' => 'The 3-2-1 backup rule means:', 'o' => ['3 passwords, 2 devices, 1 email', '3 copies, 2 media, 1 offline / off-site', 'Back up 3 times a day', '3 cloud accounts'], 'a' => 1],
        ['m' => 4, 'q' => 'A suspicious SMS, with no money lost, should be reported on:', 'o' => ['1930 only', 'Chakshu', 'SCORES', 'Sachet'], 'a' => 1],
        ['m' => 2, 'q' => 'Someone sends you a QR code "to pay you". They actually want you to:', 'o' => ['Receive money', 'Send money', 'Verify KYC', 'Update the app'], 'a' => 1],
        ['m' => 7, 'q' => 'Which approach is used for empathetic first response in this course?', 'o' => ['STOP', 'CALM', 'SAFE', 'FAST'], 'a' => 1],
        ['m' => 7, 'q' => 'A victim of sextortion should:', 'o' => ['Pay quickly to end it', 'Delete everything', 'Stop replying, not pay, save evidence, report', 'Confront the caller'], 'a' => 2],
        ['m' => 7, 'q' => 'Which helpline offers free, 24x7 mental health support?', 'o' => ['181', '1098', '14416', '14567'], 'a' => 2],
        ['m' => 4, 'q' => 'A First Responder may:', 'o' => ['Ask for the victim\'s OTP to help', 'Take the victim\'s phone home to fix it', 'Help the victim save evidence and report, with consent', 'Contact the fraudster to negotiate'], 'a' => 2],
        ['m' => 9, 'q' => 'The first thing to change on a new Wi-Fi camera is:', 'o' => ['The camera angle', 'The default password', 'The brand logo', 'The video quality'], 'a' => 1],
        ['m' => 2, 'q' => 'Crypto exchanges operating in India must be registered with:', 'o' => ['SEBI', 'FIU-IND', 'TRAI', 'NPCI'], 'a' => 1],
        ['m' => 6, 'q' => 'If a bank doesn\'t resolve a fraud complaint within 30 days, the customer can approach:', 'o' => ['RBI Integrated Ombudsman (cms.rbi.org.in)', 'SEBI', 'CERT-In', 'PIB Fact Check'], 'a' => 0],
        ['m' => 6, 'q' => 'Someone offers to recover your lost money for a "processing fee". This is:', 'o' => ['A police service', 'A bank service', 'A second fraud', 'Legal aid'], 'a' => 2],
    ],

    // Extra formative questions (not in the pre/post test), including Module 1 trainer-guide MCQs.
    'extra' => [
        ['m' => 1, 'q' => 'Which password approach is safest for your email account?', 'o' => ['Your name and birth year', 'The same strong password you use for shopping', 'A four-word passphrase used only for this account', 'Your mobile number'], 'a' => 2],
        ['m' => 1, 'q' => 'A weather app asks for permission to read your SMS and contacts. What is the best response?', 'o' => ['Allow everything so it works properly', 'Deny the unnecessary permissions and check the app is genuine', 'Allow once and uninstall later', 'Ignore it and keep using the app'], 'a' => 1],
        ['m' => 1, 'q' => 'You need to check your bank balance at a railway station. Which choice is safest?', 'o' => ['Free station Wi-Fi', 'Your own mobile data and the official bank app', 'A stranger\'s hotspot', 'Wait for an SMS link from the bank'], 'a' => 1],
        ['m' => 3, 'q' => 'Which is the strongest clue that a celebrity investment video is fake?', 'o' => ['It has many views', 'It promises guaranteed returns and isn\'t on their verified account', 'It is in Hindi', 'It is longer than a minute'], 'a' => 1],
        ['m' => 4, 'q' => 'A caller is on a live "digital arrest" video call and the citizen is in distress. The urgency level is:', 'o' => ['Green', 'Yellow', 'Amber', 'Red'], 'a' => 3],
        ['m' => 5, 'q' => 'A good screenshot for evidence must show:', 'o' => ['Only the message text', 'The date, time and the sender\'s number or ID', 'Your own profile photo', 'A crop of the important part'], 'a' => 1],
        ['m' => 9, 'q' => 'Charging your phone at a public USB port can lead to:', 'o' => ['Faster charging', 'Data theft or malware ("juice jacking")', 'A free software update', 'Better battery life'], 'a' => 1],
        ['m' => 9, 'q' => 'Which router setting should be switched off at home?', 'o' => ['WPA3 encryption', 'Firmware updates', 'WPS and remote management', 'The guest network'], 'a' => 2],
    ],
];
