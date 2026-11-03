<?php
// The HOD's complete booklet (files/booklet.pdf, "Certificate in Cyber Incident First Response", 303 pages): which pages
// learners may see per module, and each module's topics. Printed page numbers equal PDF page numbers.
//
// Learners never see: each module's assessment blueprint (its role-play script cards hold hidden facts), the capstone
// case files and scoring grid (pp. 239-242), the practical station bank and assessor guide (pp. 247-248), and the
// question and viva banks (pp. 249-280). Trainers and assessors use the full PDF.

defined('CLI_SCRIPT') || defined('MOODLE_INTERNAL') || die();

$r = fn($a, $b) => range($a, $b);
return [
    'title' => 'Certificate in Cyber Incident First Response',
    'pages' => [
        1 => $r(22, 45), 2 => $r(49, 72), 3 => $r(77, 98), 4 => $r(102, 121), 5 => $r(125, 143),
        6 => $r(147, 166), 7 => $r(170, 189), 8 => $r(193, 211), 9 => $r(215, 231),
        10 => array_merge($r(235, 238), [243]), 11 => array_merge($r(244, 246), [281]),
        0 => array_merge($r(1, 21), [299, 300, 301, 302, 303]),   // the programme guide: Parts A and B, help directory, glossary
    ],
    // [title, first page, last page]
    'topics' => [
        1 => [['Introduction to cyber hygiene', 29, 30], ['Password and account security', 31, 32], ['Device security and app permissions', 33, 34],
            ['Privacy and personal information', 35, 36], ['Safe internet practices', 37, 38], ['Basic cyber incident response', 39, 40]],
        2 => [['How online frauds work', 56, 56], ['Phishing: fake messages and websites', 57, 58], ['UPI, QR code and OTP frauds', 59, 60],
            ['Fake bank, KYC and customer-care frauds', 61, 62], ['Job, investment and "digital arrest" scams', 63, 64],
            ['Impersonation and recovery scams', 65, 66], ['Reporting and golden-hour response', 67, 68]],
        3 => [['Deepfakes and AI-generated content', 84, 85], ['AI-generated images, videos and voices', 86, 87], ['Fake news and misinformation', 88, 89],
            ['Impersonation and manipulated media', 90, 91], ['AI-based cyber threats and phishing', 92, 93], ['Verification, responsible AI use and privacy', 94, 95]],
        4 => [['What triage is, and your role', 109, 109], ['The structured intake', 110, 111], ['Six incident categories', 112, 112],
            ['The four-colour urgency scale', 113, 114], ['Stabilise safely', 115, 115], ['Records, consent and hand-over', 116, 116]],
        5 => [['What digital evidence is', 132, 132], ['Native record and working copy', 133, 134], ['Time, time zones and metadata', 135, 135],
            ['Evidence register and chain of custody', 136, 136], ['Integrity hashing with SHA-256', 137, 138], ['Safeguarding, the law and your role', 139, 140]],
        6 => [['The national reporting system', 154, 155], ['The reporting map', 156, 156], ['Filing on cybercrime.gov.in', 157, 158],
            ['Writing an actionable complaint', 159, 159], ['Escalation and follow-up', 160, 161], ['Legal quick reference and honest expectations', 162, 163]],
        7 => [['The impact of cyber harm', 177, 177], ['Psychological first aid: look, listen, link', 178, 179], ['Distress, self-harm and safeguarding', 180, 181],
            ['Adapting communication to the person', 182, 183], ['Supporting specific harms and referral', 184, 185], ['Confidentiality, closure and self-care', 186, 186]],
        8 => [['Malware in plain words', 200, 200], ['Signs of compromise', 201, 202], ['Isolate and preserve', 203, 203],
            ['Account recovery and lockdown', 204, 205], ['SIM swap and banking trojans', 206, 206], ['Ransomware in small organisations, and prevention', 207, 208]],
        9 => [['What IoT and OT are', 222, 222], ['Why smart devices get hacked', 223, 223], ['Securing the home or shop router', 224, 225],
            ['Smart cameras and CCTV', 226, 226], ['Wearables, speakers, TVs and trackers', 227, 227], ['Smart-device incidents and OT basics', 228, 228]],
    ],
];
