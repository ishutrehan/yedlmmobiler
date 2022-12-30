<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


define('UPLOADS_URL', "http://3.138.245.146/api/uploads/");
define('AWS_PROPERTIES_URL','https://yedimmobiler.s3.us-east-2.amazonaws.com/properties/');										
define('AWS_PROFILES_URL','https://yedimmobiler.s3.us-east-2.amazonaws.com/profiles/');	

define('ADD_NEW', 'Ajouter de nouveaux');
define('ADD_TYPE', 'Ajouter Type');
define('ADD_AMENITY', 'Ajouter Commodités');
define('WELCOME', 'Bienvenue');
define('USERS', 'Utilisateurs');
define('PROPERTIES', 'Biens immobiliers');
define('TOTAL_USERS', 'Nombre de Utilisateurs');
define('TOTAL_PROPERTIES', 'Nombre de biens');
define('ALL_USERS', 'Tous les utilisateurs');
define('ADD_NEW_USER', 'Ajouter de nouveaux utilisateurs');
define('ALL_PROPERTIES', 'Tous les biens');
define('PROFILE', 'Profil');
define('VIEW_PROFILE', 'Afficher le Profil');
define('LOG_OUT', 'Se déconnecter');
define('CLOSE', 'Fermer');
define('NAME', 'Nom');
define('PHONE', 'Téléphone');
define('APPROVE_USER', 'Approuver');
define('PREVIOUS', 'Avant');
define('NEXT', 'Après');
define('SHOW', 'Afficher');
define('ENTRIES', 'Insertions');
define('PROFILE_IMAGE', 'Photo de Profil');
define('FULL_NAME', 'Nom Complet');
define('EMAIL_ADDRESS', 'E-mail');
define('PASSWORD', 'Mot de Passe');
define('ADD_USER', 'Ajouter Utilisateur');
define('LIST_OF_ALL_PROPERTIES', 'Liste de tous les biens');
define('LIST_OF_ALL_AMENITIES', 'Liste de tous les Commodités');
define('LIST_OF_ALL_TYPES', 'Liste de tous les Types');
define('TITLE', 'Titre');
define('PRICE', 'Prix');
define('AREA', 'Superficie');
define('LISTED_BY', 'Auteur');
define('CREATED_AT', 'Date/Heure');
define('SETTINGS', 'Réglages');
define('FULL_SCREEN', 'Plein Écran');
define('LOCK', 'Verrouiller');
define('SEE_ALL_ALERTS', 'Voir toutes les notifications');
define('NEW_USER_NOTIFICATION', 'Un nouvel utilisateur a été ajouté');
define('LOGIN', 'Se connecter');
define('AMENITIES', 'Commodités');
define('VIEW', 'Afficher');
define('DEL', 'Supprimer');
define('PURPOSE', 'Destination');
define('SEARCH', 'Rechercher');
define('EDIT', 'Modifier');
define('INSERTIONS', 'Entrées');
define('CONTACT_DETAILS', 'Coordonnées de YED Immobilier');
define('UPD', 'Mettre à jour');
define('UPDATE_PROFILE', 'Mettre à jour le profil');
define('MY_PROFILE_DATA', 'Mes coordonnées');
define('MY_PROFILE', 'Mon Profil');
define('BULK_ACTION', 'Action en masse');
define('SELECTED', 'sélectionnés');
define('DELETE_SELECTED', 'Supprimer sélectionnée');
define('ALL_INDIVIDUALS', 'Tous les particuliers');
define('ALL_AGENTS', 'Tous les professionnels');
define('LIST_OF_ALL_INDIVIDUALS', 'Liste de toutes les particuliers');
define('LIST_OF_ALL_AGENTS', 'Liste de toutes les professionnels');
define('TOTAL_INDIVIDUALS', 'Nombre de particuliers');
define('TOTAL_AGENTS', 'Nombre de professionnels');


