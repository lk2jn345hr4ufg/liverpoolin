<footer>
    <div class="in">
        <span class="ynwa">You'll never walk alone</span>
        <small>LiverpoolIn — независимый фан-сайт. Не связан с ФК «Ливерпуль». Новости агрегируются и переписываются; источники указываются со ссылкой.</small>
    </div>
</footer>

{{-- Время матчей в часовом поясе посетителя.
     [data-user-time] — строка «🕑 у вас» в блоке времени матча.
       Скрывается, если пояс посетителя совпадает с поясом стадиона.
       Если дата у посетителя другая (поздний матч и т.п.) — добавляет дату.
     [data-utc] — простое время в локальном поясе (используется в других местах). --}}
<script>
(function () {
    var userTz = '';
    try { userTz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}

    function localDateIso(d) {
        // YYYY-MM-DD в поясе посетителя
        var p = new Intl.DateTimeFormat('en-CA', { year: 'numeric', month: '2-digit', day: '2-digit' }).format(d);
        return p;
    }

    document.querySelectorAll('[data-user-time]').forEach(function (el) {
        try {
            var d = new Date(el.getAttribute('data-user-time'));
            if (isNaN(d.getTime())) return;

            var venueTz = el.getAttribute('data-venue-tz') || '';
            if (venueTz && userTz && venueTz === userTz) {
                el.hidden = true;           // то же время, что на стадионе — не дублируем
                return;
            }

            var txt = d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            var venueDate = el.getAttribute('data-venue-date');
            if (venueDate && localDateIso(d) !== venueDate) {
                txt = d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' }) + ', ' + txt;
            }

            var t = el.querySelector('.ko-t');
            if (t) t.textContent = txt;
        } catch (e) { /* остаётся UTC */ }
    });

    document.querySelectorAll('[data-utc]').forEach(function (el) {
        try {
            var d = new Date(el.getAttribute('data-utc'));
            if (isNaN(d.getTime())) return;
            var opts = { hour: '2-digit', minute: '2-digit' };
            if (el.hasAttribute('data-utc-full')) { opts.day = '2-digit'; opts.month = 'short'; }
            el.textContent = d.toLocaleString('ru-RU', opts);
        } catch (e) {}
    });

    var lbl = document.getElementById('tzlabel');
    if (lbl) lbl.textContent = userTz ? userTz.replace(/_/g, ' ') : 'локальный';
})();
</script>
