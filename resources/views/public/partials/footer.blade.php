<footer>
    <div class="in">
        <span class="ynwa">You'll never walk alone</span>
        <small>LiverpoolIn — независимый фан-сайт. Не связан с ФК «Ливерпуль». Новости агрегируются и переписываются; источники указываются со ссылкой.</small>
    </div>
</footer>

{{-- Конвертация времени матчей в часовой пояс посетителя.
     Элементы с data-utc (ISO-время в UTC) заполняются локальным временем браузера.
     data-utc-full — добавляет дату, data-utc-tz — дописывает аббревиатуру пояса. --}}
<script>
(function () {
    try {
        var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        document.querySelectorAll('[data-utc]').forEach(function (el) {
            var iso = el.getAttribute('data-utc');
            if (!iso) return;
            var d = new Date(iso);
            if (isNaN(d.getTime())) return;

            var opts = { hour: '2-digit', minute: '2-digit' };
            if (el.hasAttribute('data-utc-full')) {
                opts.day = '2-digit'; opts.month = 'short';
            }
            var txt = d.toLocaleString('ru-RU', opts);
            if (el.hasAttribute('data-utc-tz') && tz) {
                txt += ' (' + tz.split('/').pop().replace('_', ' ') + ')';
            }
            el.textContent = txt;
            el.removeAttribute('title');
        });
    } catch (e) { /* молча — останется серверное UTC-время */ }
})();
</script>
