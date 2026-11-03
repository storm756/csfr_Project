<?php
// Module practice (graded, recorded in the gradebook, unlimited tries): about ten items per module in several formats.
// Formats (Moodle question types): tf = safe or risky (truefalse), multi = pick every right one (multichoice, several
// answers: warning signs, the evidence bag), match = who do I call / which category (match), gap = fill the gaps
// (gapselect; [[n]] picks the n-th choice, choices of the same group share a drop-down), mc = one answer; items with a
// 'Talk to the victim' stem form a short branching chain in order (interactive, one per page).
// Seeded from the teammate's assessment prototype (CCFR Assessment Studio) and the module PDFs; brands in sample
// messages are fictional on purpose.

defined('CLI_SCRIPT') || defined('MOODLE_INTERNAL') || die();

$victim = 'Talk to the victim · ';
return [
    1 => [
        ['tf', 'You switch on two-step verification for WhatsApp. This is safe.', true, 'Good habit: it blocks code-theft takeovers.'],
        ['tf', 'Installing "PM Kisan update.apk" sent in a family chat is safe if a relative sent it.', false, 'APK files from chats can read your OTPs, and the relative\'s account may be taken over.'],
        ['tf', 'A bank officer may ask you to read out an OTP to "verify" your account.', false, 'No bank, police or company ever asks for an OTP.'],
        ['multi', 'Which of these are good, strong ways to log in? Pick every right one.', [['A four-word passphrase used only for email', true], ['A passkey offered by the service', true], ['Name + birth year, used everywhere', false], ['The same password as your shopping site', false]], 'Long and unique wins; passkeys are even better. Reuse means one leak opens everything.'],
        ['match', 'Match each warning to its first safe step.', [['Phone shows "No service" for a long time', 'Call your telecom operator and bank'], ['Login alert from an unknown device', 'Review active sessions and change the password'], ['A free hotspot asks for your email password', 'Disconnect and use mobile data'], ['A shopping site you used was breached', 'Change reused passwords, email and banking first']], 'Each signal has an official, independent response.'],
        ['gap', 'Your digital identity is a chain: mobile number → Aadhaar → bank → UPI → [[1]] → social media. Lose your SIM or [[1]] and the rest can be [[2]].', [['email', 1], ['reset', 2], ['TV', 1], ['ignored', 2]], 'Email and SIM are the master keys.'],
        ['mc', 'A torch app asks for SMS access. What should you do?', ['Allow it, torch apps need it', 'Deny it: a torch needs no SMS access', 'Uninstall your SMS app'], 1, 'Review permissions monthly; deny what an app does not need.'],
        ['mc', $victim . 'A neighbour says: "My bank officer asked for the WhatsApp code." What do you say first?', ['Read it out quickly so he can help', 'That code is a key, not information. Don\'t share it. Let\'s check with the bank on its official number.', 'Ignore it, he must be genuine'], 1, 'Calm, clear, and verify independently.'],
    ],
    2 => [
        ['tf', 'To receive money on UPI you must enter your PIN.', false, 'PIN means paying, never receiving.'],
        ['tf', 'A "recovery agent" who charges a fee to get your money back is a second fraud.', true, 'Official reporting is free. Report that number too.'],
        ['multi', 'This SMS arrives: "XYZ BANK ALERT: Dear Customer, your account will be blocked today. Update KYC at bit.ly/xyz-kyc. Call officer on 98123 45612. Share OTP to confirm. Reply STOP to opt out." Which parts are warning signs? Pick every one.', [['"blocked today" (urgency)', true], ['A shortened bit.ly link', true], ['A 10-digit mobile number for an "officer"', true], ['"Share OTP to confirm"', true], ['"Reply STOP to opt out"', false]], 'Urgency, a short link, a personal number and an OTP request are the giveaways. Real bank SMS come from registered sender IDs.'],
        ['match', 'Translate each message into what it really asks you to do.', [['"Scan to receive"', 'Approve a payment out of your account'], ['"Unlock your profit"', 'Send another payment before a fake withdrawal'], ['"Prepay to get tasks"', 'Deposit money to a scammer'], ['"Install customer support"', 'Give a stranger remote control']], 'The story changes; the action behind it does not.'],
        ['gap', 'Money sent by UPI moves through [[1]] accounts quickly. Calling [[2]] within the first [[3]] gives the best chance of a hold.', [['mule', 1], ['1930', 2], ['hour', 3], ['savings', 1], ['100', 2], ['week', 3]], 'The golden hour: minutes matter.'],
        ['mc', 'A trading app shows ₹11 lakh profit but asks for a 20% "tax" before withdrawal. What does the balance prove?', ['That the profit is real', 'Nothing: a profit you cannot withdraw is not a profit', 'That the app is SEBI-registered'], 1, 'Stop paying and keep every payment reference.'],
        ['mc', 'Where do you check if a crypto platform is registered to operate in India?', ['SEBI', 'FIU-IND', 'TRAI'], 1, 'Crypto platforms must register with FIU-IND; advisers with SEBI; lenders with RBI.'],
        ['mc', $victim . 'A student paid ₹18,000 for "VIP tasks" 25 minutes ago. The group wants ₹35,000 more. What first?', ['Pay once more to unlock the money', 'Stop paying. Call 1930 and the bank now; keep the UTRs and the group.', 'Leave the group and delete the chat'], 1, 'Stop, report inside the golden hour, keep evidence.'],
        ['mc', $victim . 'He asks: "Will I definitely get my money back?"', ['Yes, definitely', 'I can\'t promise that, but reporting quickly gives the best chance. Let\'s do it now.', 'No, it\'s gone, forget it'], 1, 'Never promise recovery; never take away hope either.'],
    ],
    3 => [
        ['tf', 'A video call from "CBI" saying you are under digital arrest is genuine if the officer is in uniform.', false, 'There is no digital arrest. Hang up and call 112 or 1930.'],
        ['tf', 'A voice note from your "son" asks for urgent money but doesn\'t say the family safe word. Call back on his saved number before paying.', true, 'Verify through a second channel, not by how the voice sounds.'],
        ['tf', 'If the lips look normal, the video is real.', false, 'Glitches disappear as AI improves. Check the source and confirm independently.'],
        ['multi', 'Which checks actually help verify a shocking viral video? Pick every one.', [['Find the original post and its date', true], ['Reverse image search, as a lead', true], ['Look for a PIB Fact Check or trusted fact-check', true], ['Count how many groups forwarded it', false], ['Trust it if it has a celebrity in it', false]], 'Source, date and independent confirmation beat popularity.'],
        ['match', 'Match the situation to the better check.', [['Familiar voice, unknown number', 'Call back on the saved number'], ['Celebrity investment ad', 'Check the official account and the regulator'], ['Old flood video, new caption', 'Search for earlier posts and compare dates'], ['Uniformed caller demands secrecy', 'End the call; contact police yourself']], 'Use an independent route every time.'],
        ['gap', 'Urgency + [[1]] + secrecy = [[2]].', [['money', 1], ['fraud', 2], ['weather', 1], ['friendship', 2]], 'The pattern behind almost every AI-enabled scam.'],
        ['mc', 'Someone threatens to share a morphed intimate image unless paid. The image is fake. What is true?', ['It\'s fake, so there is no harm', 'It is still real harm: don\'t pay, save evidence, report', 'Pay a small amount to end it'], 1, 'Treat the threat as real; paying brings more demands.'],
        ['mc', $victim . 'A mother says her "son" called crying from a police station and she must send ₹40,000 and tell no one.', ['Send it quickly, he sounded real', 'Let\'s hang up and call him back on his saved number, or call someone who is with him.', 'Ask the caller his name and badge number'], 1, 'The call-back rule works even when the voice is perfect.'],
    ],
    4 => [
        ['mc', 'A caller on video says "you are under arrest" and is still on the line. Which colour?', ['Red', 'Amber', 'Yellow', 'Green'], 0, 'Red: live pressure. Call 112, end the call, stay with the person.'],
        ['mc', 'Money sent by UPI 30 minutes ago to a stranger. Which colour?', ['Red', 'Amber', 'Yellow', 'Green'], 1, 'Amber: call 1930 within the golden hour.'],
        ['mc', 'Lost money three days ago; no threat now. Which colour?', ['Red', 'Amber', 'Yellow', 'Green'], 2, 'Yellow: file on cybercrime.gov.in the same day.'],
        ['mc', 'Suspicious SMS, nothing clicked, nothing lost. Which colour?', ['Red', 'Amber', 'Yellow', 'Green'], 3, 'Green: report on Chakshu and give hygiene advice.'],
        ['match', 'Match each report to its incident category.', [['"Friends get messages from me"', 'Account compromise'], ['"A stranger keeps messaging my daughter"', 'Child-related'], ['"My files have strange names"', 'Device / malware'], ['"A fake profile with my photo asks for money"', 'Impersonation and misinformation']], 'Category first, then urgency.'],
        ['multi', 'Which are among the 6 first-response questions? Pick every one.', [['What happened?', true], ['Is anyone in danger?', true], ['Is it still happening?', true], ['Why did you trust them?', false], ['What evidence is there?', true]], '"Why did you" blames; it is not a triage question.'],
        ['gap', 'Safety first, [[1]] second, evidence third, [[2]] last.', [['money', 1], ['paperwork', 2], ['blame', 1], ['gossip', 2]], 'If anyone\'s safety is at risk, it is RED whatever else is happening.'],
        ['mc', 'A "fake social profile" case was Yellow. Now the account threatens to visit the citizen tonight. What changes?', ['Nothing, it stays Yellow', 'Check immediate safety; RED if danger is imminent', 'Close the case'], 1, 'New facts can raise the colour at any time.'],
    ],
    5 => [
        ['multi', 'Mr. Kulkarni lost money in a fake trading app. What goes into the evidence bag? Pick every one.', [['UTR and transaction IDs', true], ['Group name and admin phone numbers', true], ['App name and download link', true], ['Exported chat with media', true], ['Delete the chat to feel calmer', false], ['Crop the screenshot to look neat', false], ['Edit the file to fix a typo', false]], 'Save first, never edit, never delete; copy links instead of clicking them.'],
        ['tf', 'Block the scammer first, then think about evidence.', false, 'Screenshot first, block later.'],
        ['tf', 'A screenshot should show the time, date and the other person\'s number or profile.', true, 'Never crop or edit a screenshot.'],
        ['match', 'Match the incident to the one item you must not miss.', [['UPI fraud', 'UTR / transaction ID'], ['Fake profile', 'The profile URL, not only the name'], ['Ransomware', 'A photo of the ransom note'], ['Crypto fraud', 'Wallet addresses and transaction hashes']], 'Each incident type has its own checklist.'],
        ['gap', 'Keep [[1]] copies, one off the phone, and never [[2]] a saved file.', [['two', 1], ['edit', 2], ['zero', 1], ['print', 2]], 'Two copies, originals untouched.'],
        ['mc', 'A citizen wants to forward you a child sexual image "so you can report it". What do you do?', ['Accept it and forward it to the police', 'Refuse it: note the link and report through the portal', 'Save it on your phone as evidence'], 1, 'Possessing or sharing it is a crime, even to report it.'],
        ['mc', 'Where do you find the UTR after a UPI payment?', ['On the bank branch\'s notice board', 'In the payment\'s details in the UPI app', 'In the scammer\'s chat'], 1, 'Open the payment → UPI transaction ID / UTR → save the receipt.'],
    ],
    6 => [
        ['match', 'Who do I call? Match each situation to the right channel.', [['Money left my account 10 minutes ago', '1930'], ['A stranger keeps messaging my daughter', '1098'], ['Suspicious SMS, I lost nothing', 'Chakshu'], ['My bank has not fixed my complaint in 30 days', 'RBI Ombudsman']], 'Money lost? 1930. Everything else? cybercrime.gov.in, plus the right specialist channel.'],
        ['match', 'Match the organisational problem to its channel.', [['Ransomware at a clinic', 'CERT-In + police'], ['Broker cheated an investor', 'SEBI SCORES'], ['Illegal chit or Ponzi scheme', 'RBI Sachet'], ['Fake e-commerce product', 'National Consumer Helpline 1915']], 'Specialist channels alongside the portal.'],
        ['tf', 'A police station can refuse a complaint because the fraud happened in another state.', false, 'Any station must register a Zero FIR (BNSS s.173) and transfer it.'],
        ['tf', 'The only national cybercrime helpline is 1930, and it never charges a fee.', true, 'Fake "cyber cell" numbers and paid recovery services are frauds.'],
        ['gap', 'Cheating by personation online is IT Act section [[1]]; identity theft of passwords and OTPs is section [[2]].', [['66D', 1], ['66C', 2], ['67', 1], ['43', 2]], 'Legal awareness, not legal advice.'],
        ['mc', 'What must the citizen keep after filing on cybercrime.gov.in?', ['Nothing, the portal remembers', 'The acknowledgement number', 'The officer\'s personal phone number'], 1, 'Save it and track the status.'],
        ['mc', 'Personal data misused by a company: where does it go after the company\'s grievance officer?', ['Data Protection Board of India', 'PIB Fact Check', 'CERT-In'], 0, 'Under the DPDP Act, 2023.'],
    ],
    7 => [
        ['mc', $victim . 'Mr. Kulkarni: "I invested ₹3.5 lakh in a trading app and lost it. I feel so ashamed. Maybe I should keep quiet."', ['You did the right thing by telling me. Let\'s handle this together.', 'How could you be so careless?', 'Don\'t worry, the money will definitely come back.'], 0, 'Calm and no blame. Blame stops people reporting; false promises break trust.'],
        ['mc', $victim . '"The group admin says: pay ₹15,000 more within one hour or my account is frozen."', ['Don\'t pay. Let\'s call 1930 now and save the UTRs.', 'Pay it, we can\'t lose everything.', 'Call the admin and negotiate.'], 0, 'Stop payments, report inside the golden hour; never contact the fraudster.'],
        ['mc', $victim . '"A man from the Cyber Cell called. He can recover my money for ₹15,000."', ['That is a second fraud. Police never charge a fee. Let\'s report that number too.', 'That sounds official. Pay him.', 'Give him your bank details.'], 0, 'Recovery scams follow first frauds; real helplines never charge.'],
        ['mc', $victim . '"My son will be angry. Don\'t tell him."', ['I won\'t contact anyone without your consent. Would it help to tell him together?', 'I\'ll call him now, he should know.', 'Then let\'s not report it.'], 0, 'Consent before involving anyone; offer support, keep the report going.'],
        ['match', 'Match the person to the helpline.', [['Senior citizen', 'Elder Line 14567'], ['Woman facing morphing', 'Women Helpline 181'], ['Child being groomed', 'Childline 1098'], ['Someone talking of not wanting to live', 'Tele-MANAS 14416 or 112']], 'Keep a referral list ready.'],
        ['multi', 'Which are things a First Responder must never say? Pick every one.', [['"How could you be so careless?"', true], ['"Why did you send photos?"', true], ['"The money will definitely come back."', true], ['"You did the right thing by telling me."', false]], 'No blame, no judgement, no false promises.'],
        ['gap', 'CALM: [[1]] and connect · [[2]] on urgent steps · Listen and log · Map next steps.', [['Calm', 1], ['Act', 2], ['Confront', 1], ['Argue', 2]], 'The approach used throughout the course.'],
    ],
    8 => [
        ['multi', 'A phone shows these. Which are warning signs of malware or takeover? Pick every one.', [['Hot, slow, fast battery drain', true], ['An unknown app with Accessibility rights', true], ['OTPs you didn\'t request', true], ['Friends get messages you never sent', true], ['A system update from the official settings menu', false]], 'Accessibility rights let an app tap the screen for you.'],
        ['tf', 'When a ransom note appears, switch the computer off at once.', false, 'Unplug the network and USB drives, but don\'t switch off or wipe until advised.'],
        ['tf', 'Paying the ransom guarantees you get your files back.', false, 'It funds criminals, guarantees nothing, and marks you for repeat attacks.'],
        ['mc', 'A shop computer shows a ransom note. The first action is:', ['Pay quickly', 'Disconnect it from the network', 'Delete the note'], 1, 'Disconnect first, think second.'],
        ['gap', '3-2-1 backup: [[1]] copies, on [[2]] kinds of media, [[3]] kept offline or off-site.', [['3', 1], ['2', 2], ['1', 3], ['5', 1], ['4', 2], ['0', 3]], 'And test a restore every month.'],
        ['match', 'Match the hacked account to its recovery route.', [['WhatsApp', 'Re-register the number, then set two-step PIN'], ['Gmail', 'g.co/recover'], ['Facebook', 'facebook.com/hacked'], ['Infected home PC', 'Free tools on csk.gov.in']], 'Then warn your contacts.'],
        ['mc', 'Why secure your email first after a takeover?', ['It looks nicer', 'It can reset most of your other passwords', 'Banks require it'], 1, 'Email is the master key.'],
    ],
    9 => [
        ['tf', 'A camera\'s factory password is fine if the camera is only used at home.', false, 'Criminals scan the internet for factory passwords. Change it on day one.'],
        ['tf', 'Charging from your own adapter on a wall socket is safer than a free public USB port.', true, 'Charge from the plug, not the port.'],
        ['multi', 'Securing a home Wi-Fi router: pick every right step.', [['New admin and Wi-Fi passwords', true], ['WPA2 or WPA3', true], ['WPS and remote management off', true], ['Firmware updated', true], ['Keep "admin / admin" so the family remembers', false]], 'Default password = no password.'],
        ['match', 'Who do I call? Match the smart-device problem to the channel.', [['Private footage from a home camera leaked', 'cybercrime.gov.in (112 if safety at risk)'], ['A water-pump controller misused', 'Plant in-charge, then CERT-In + police'], ['Infected home devices', 'csk.gov.in'], ['Critical infrastructure hit', 'CERT-In + NCIIPC']], 'Know where your role stops.'],
        ['gap', 'IoT means everyday [[1]] devices; OT means systems that control [[2]] processes.', [['connected', 1], ['physical', 2], ['wooden', 1], ['imaginary', 2]], 'An OT attack can affect safety, not just data.'],
        ['mc', 'A factory pump controller looks hacked. As a First Responder you:', ['Switch it off yourself', 'Tell the plant in-charge and follow the site safety procedure', 'Post a photo online to warn others'], 1, 'Don\'t go beyond your role.'],
        ['mc', 'Where should a room camera never point?', ['The front door', 'Bedrooms, bathrooms or changing areas', 'The parking area'], 1, 'A camera inside your home is only as private as its password.'],
    ],
];
