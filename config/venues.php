<?php

/*
 | Часовые пояса стадионов.
 |
 | Порядок определения пояса матча (Fixture::venueTimezone):
 |   1) ручное поле venue_tz (админка → Матчи → Редактировать);
 |   2) английский турнир или домашний матч «Ливерпуля» → Europe/London;
 |   3) клуб-хозяин найден в списке 'teams' ниже (поиск по подстроке);
 |   4) не определено → показываем время в UTC.
 |
 | Добавляйте клубы по мере появления новых соперников в еврокубках.
 */

return [

    'teams' => [
        // Испания
        'Real Madrid' => 'Europe/Madrid', 'Barcelona' => 'Europe/Madrid', 'Atlético' => 'Europe/Madrid',
        'Atletico' => 'Europe/Madrid', 'Athletic' => 'Europe/Madrid', 'Villarreal' => 'Europe/Madrid',
        'Real Sociedad' => 'Europe/Madrid', 'Sevilla' => 'Europe/Madrid', 'Betis' => 'Europe/Madrid',
        'Girona' => 'Europe/Madrid', 'Valencia' => 'Europe/Madrid',
        // Германия
        'Bayern' => 'Europe/Berlin', 'Dortmund' => 'Europe/Berlin', 'Leverkusen' => 'Europe/Berlin',
        'Leipzig' => 'Europe/Berlin', 'Frankfurt' => 'Europe/Berlin', 'Stuttgart' => 'Europe/Berlin',
        'Wolfsburg' => 'Europe/Berlin', 'Union Berlin' => 'Europe/Berlin', 'Freiburg' => 'Europe/Berlin',
        // Франция
        'Paris' => 'Europe/Paris', 'PSG' => 'Europe/Paris', 'Monaco' => 'Europe/Paris',
        'Marseille' => 'Europe/Paris', 'Lille' => 'Europe/Paris', 'Lyon' => 'Europe/Paris',
        'Brest' => 'Europe/Paris', 'Lens' => 'Europe/Paris', 'Nice' => 'Europe/Paris',
        // Италия
        'Inter' => 'Europe/Rome', 'Milan' => 'Europe/Rome', 'Juventus' => 'Europe/Rome',
        'Napoli' => 'Europe/Rome', 'Atalanta' => 'Europe/Rome', 'Roma' => 'Europe/Rome',
        'Lazio' => 'Europe/Rome', 'Bologna' => 'Europe/Rome', 'Fiorentina' => 'Europe/Rome',
        // Нидерланды / Бельгия
        'Ajax' => 'Europe/Amsterdam', 'PSV' => 'Europe/Amsterdam', 'Feyenoord' => 'Europe/Amsterdam',
        'AZ' => 'Europe/Amsterdam', 'Brugge' => 'Europe/Brussels', 'Union' => 'Europe/Brussels',
        'Anderlecht' => 'Europe/Brussels', 'Genk' => 'Europe/Brussels',
        // Португалия
        'Benfica' => 'Europe/Lisbon', 'Porto' => 'Europe/Lisbon', 'Sporting' => 'Europe/Lisbon', 'Braga' => 'Europe/Lisbon',
        // Шотландия
        'Celtic' => 'Europe/London', 'Rangers' => 'Europe/London',
        // Остальная Европа
        'Galatasaray' => 'Europe/Istanbul', 'Fenerbahçe' => 'Europe/Istanbul', 'Fenerbahce' => 'Europe/Istanbul',
        'Beşiktaş' => 'Europe/Istanbul', 'Besiktas' => 'Europe/Istanbul',
        'Salzburg' => 'Europe/Vienna', 'Sturm' => 'Europe/Vienna',
        'Slavia' => 'Europe/Prague', 'Sparta' => 'Europe/Prague', 'Plzeň' => 'Europe/Prague', 'Plzen' => 'Europe/Prague',
        'Young Boys' => 'Europe/Zurich', 'Basel' => 'Europe/Zurich',
        'København' => 'Europe/Copenhagen', 'Copenhagen' => 'Europe/Copenhagen',
        'Bodø' => 'Europe/Oslo', 'Bodo' => 'Europe/Oslo',
        'Olympiacos' => 'Europe/Athens', 'Olympiakos' => 'Europe/Athens', 'PAOK' => 'Europe/Athens',
        'Dinamo Zagreb' => 'Europe/Zagreb', 'Crvena' => 'Europe/Belgrade', 'Red Star' => 'Europe/Belgrade',
        'Slovan' => 'Europe/Bratislava', 'Ferencváros' => 'Europe/Budapest', 'Ferencvaros' => 'Europe/Budapest',
        'Qarabağ' => 'Asia/Baku', 'Qarabag' => 'Asia/Baku', 'Kairat' => 'Asia/Almaty',
        'Pafos' => 'Asia/Nicosia', 'Shakhtar' => 'Europe/Kyiv', 'Dynamo Kyiv' => 'Europe/Kyiv',
    ],

    /* Подписи городов для часовых поясов (что показывать рядом со временем). */
    'cities' => [
        'Europe/London'     => 'Лондон',
        'Europe/Madrid'     => 'Мадрид',
        'Europe/Berlin'     => 'Берлин',
        'Europe/Paris'      => 'Париж',
        'Europe/Rome'       => 'Рим',
        'Europe/Amsterdam'  => 'Амстердам',
        'Europe/Brussels'   => 'Брюссель',
        'Europe/Lisbon'     => 'Лиссабон',
        'Europe/Istanbul'   => 'Стамбул',
        'Europe/Vienna'     => 'Вена',
        'Europe/Prague'     => 'Прага',
        'Europe/Zurich'     => 'Цюрих',
        'Europe/Copenhagen' => 'Копенгаген',
        'Europe/Oslo'       => 'Осло',
        'Europe/Athens'     => 'Афины',
        'Europe/Zagreb'     => 'Загреб',
        'Europe/Belgrade'   => 'Белград',
        'Europe/Bratislava' => 'Братислава',
        'Europe/Budapest'   => 'Будапешт',
        'Europe/Kyiv'       => 'Киев',
        'Asia/Baku'         => 'Баку',
        'Asia/Almaty'       => 'Алматы',
        'Asia/Nicosia'      => 'Никосия',
    ],
];
