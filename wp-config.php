<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе установки.
 * Необязательно использовать веб-интерфейс, можно скопировать файл в "wp-config.php"
 * и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://ru.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define( 'DB_NAME', 'ini_bd' );

/** Имя пользователя MySQL */
define( 'DB_USER', 'root' );

/** Пароль к базе данных MySQL */
define( 'DB_PASSWORD', '' );

/** Имя сервера MySQL */
define( 'DB_HOST', 'localhost' );

/** Кодировка базы данных для создания таблиц. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Схема сопоставления. Не меняйте, если не уверены. */
define( 'DB_COLLATE', '' );

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу. Можно сгенерировать их с помощью
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}.
 *
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными.
 * Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'bPOtC3kw e-kQ|6w}c^?y^hGQt;vM<~snHQsa,g;*JO/.HNluQlyz,yCg 9p&&N/' );
define( 'SECURE_AUTH_KEY',  'u kL[wUjFv12+A}H|,|[]DwJ=t24kv@KyhEs}=6)$Im <Uyagb7(pKh}rffGAZ?(' );
define( 'LOGGED_IN_KEY',    'ZDZQDpk%!u+@5u</dI_ES*6uJc>`tQ0rc2a4ex?`&V[0<rrM%FuDa+K>WWF!sFc}' );
define( 'NONCE_KEY',        '[?>b[G:5-wv.[0jpzDN^303y%0q,Nee0E)ql]s+f>ijy?PbD}O=aIxw/QPf/F##L' );
define( 'AUTH_SALT',        '-_o$%fTMjLjz%Kom]A OdxSS];&.1]H/X@Ff=GE>Fr>xoO;nL?f].ZnNKxj7&lpb' );
define( 'SECURE_AUTH_SALT', '$U<~2l[gU*hOrbM|Me`@2~`^C(g$aMktYd32Ypn/~q,LPQNYP[g!2og:AKrL*X:G' );
define( 'LOGGED_IN_SALT',   'e*%[HbjSVz<b9iBOFR%/HU|=U8= 1?dRo6;R$+-};VjgR60j^$|{RI<ZNm?e&jFt' );
define( 'NONCE_SALT',       'I vxcH>r~5V94hfbL+[2bm7{!/=|H$6s$#)B2+Z8^z@f;q`jdL9/NqnA{FKgoNj(' );

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 *
 * Информацию о других отладочных константах можно найти в документации.
 *
 * @link https://ru.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Произвольные значения добавляйте между этой строкой и надписью "дальше не редактируем". */



/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Инициализирует переменные WordPress и подключает файлы. */
require_once ABSPATH . 'wp-settings.php';
