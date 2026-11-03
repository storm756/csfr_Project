/* SCORM 1.2 bridge for the CCFR module assessments.
   Finds the LMS API in the parent/opener chain, opens a session on load, and reports the final score when the
   engine finishes a run (engine.js calls window.CCFR_scorm(pct, gateCount) on the result screen).
   Competent = score >= 50% and no safety-gate choice; a gate forces "failed" whatever the score. */
(function () {
  'use strict';
  var PASS = 50;
  var api = null, open = false;

  function find(win) {
    var tries = 0;
    while (win && tries++ < 12) {
      if (win.API) { return win.API; }
      if (win.parent && win.parent !== win) { win = win.parent; continue; }
      break;
    }
    return null;
  }
  function locate() {
    var a = find(window);
    if (!a && window.opener) { a = find(window.opener); }
    return a;
  }
  function init() {
    api = locate();
    if (!api) { return; }                       // opened outside an LMS (e.g. preview): run as a normal page
    try { open = api.LMSInitialize('') === 'true' || api.LMSInitialize('') === true; } catch (e) { open = false; }
    // hide the module's own hub/next links; Moodle handles navigation between activities
    try {
      var s = document.createElement('style');
      s.textContent = '.btnlink{display:none !important}';
      document.head.appendChild(s);
    } catch (e) {}
  }
  function set(k, v) { try { api.LMSSetValue(k, String(v)); } catch (e) {} }
  function finishSession() { if (api && open) { try { api.LMSCommit(''); api.LMSFinish(''); } catch (e) {} open = false; } }

  // called by engine.js end(): pct is 0-100, gates is the number of safety-gate choices
  window.CCFR_scorm = function (pct, gates) {
    if (!api || !open) { return; }
    var passed = gates === 0 && pct >= PASS;
    set('cmi.core.score.min', 0);
    set('cmi.core.score.max', 100);
    set('cmi.core.score.raw', Math.round(pct));
    set('cmi.core.lesson_status', passed ? 'passed' : 'failed');
    try { api.LMSCommit(''); } catch (e) {}
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  window.addEventListener('pagehide', finishSession);
  window.addEventListener('unload', finishSession);
})();
