/**
 * COMELEC QMS — shared queue voice announcements.
 * Priority tokens (P-042) say "priority" — never the letter "P".
 */
(function (global) {
    function isPriorityToken(token) {
        return /^P-/i.test(String(token || '').trim());
    }

    function tokenDigits(token) {
        const numeric = String(token || '').trim().replace(/^P-/i, '');
        return numeric.split('').map(function (d) {
            return d === '0' ? 'zero' : d;
        }).join(' ');
    }

    function buildAnnouncement(token, location) {
        const where = location === 'counter' ? 'the counter' : 'the window';
        const digits = tokenDigits(token);

        if (isPriorityToken(token)) {
            return 'Now serving, priority token number ' + digits + '. Please proceed to ' + where + '.';
        }

        return 'Now serving, token number ' + digits + '. Please proceed to ' + where + '.';
    }

    function speak(token, options) {
        options = options || {};
        if (!token || !global.speechSynthesis) return;

        global.speechSynthesis.cancel();

        var msg = new global.SpeechSynthesisUtterance(
            buildAnnouncement(token, options.location || 'window')
        );
        msg.rate = options.rate != null ? options.rate : 0.88;
        msg.pitch = 1.0;
        msg.volume = 1.0;

        var voices = global.speechSynthesis.getVoices();
        var eng = voices.find(function (v) { return v.lang.startsWith('en'); });
        if (eng) msg.voice = eng;

        global.speechSynthesis.speak(msg);
    }

    global.QmsVoice = {
        isPriorityToken: isPriorityToken,
        buildAnnouncement: buildAnnouncement,
        speak: speak,
    };
})(window);
