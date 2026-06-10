<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dominio base
    |--------------------------------------------------------------------------
    |
    | Dominio raíz de la aplicación. Las congregaciones se acceden mediante
    | subdominios de este dominio, p. ej. "central.reuniones-jw.local".
    | El acceso directo al dominio base se considera el área global
    | (login del SuperAdministrador).
    |
    */

    'base_domain' => env('APP_DOMAIN', 'reuniones-jw.local'),

    /*
    |--------------------------------------------------------------------------
    | Rol global
    |--------------------------------------------------------------------------
    |
    | Rol que opera de forma global, sin restricción de tenant.
    |
    */

    'super_admin_role' => 'SuperAdministrador',

];
