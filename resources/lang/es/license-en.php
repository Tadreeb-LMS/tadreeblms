<?php

return array(
  'errors' => array(
    'no_license' => 'No se encontró ninguna licencia activa. Active una licencia válida para continuar usando el sistema.',
    'expired' => 'Su licencia ha caducado. Renueve su licencia para continuar usando el sistema.',
    'revoked' => 'Su licencia ha sido revocada. Comuníquese con el soporte para obtener ayuda.',
    'invalid' => 'Su licencia no es válida. Active una licencia válida para continuar.',
    'user_limit_exceeded' => 'Se superó el límite de usuarios. Su licencia permite un máximo de :max usuarios y actualmente tiene :current usuarios activos.',
    'user_limit_reached' => 'No se puede crear un nuevo usuario. Se alcanzó su límite de licencia de :max usuarios.',
  ),
  'warnings' => array(
    'no_license' => 'No se encontró ninguna licencia activa. Active una licencia para garantizar el acceso continuo al sistema.',
    'expired' => 'Su licencia ha caducado. Renueve su licencia para mantener la funcionalidad completa.',
    'invalid' => 'Su licencia no es válida o ha sido revocada. Por favor contacte al soporte.',
    'limit_exceeded' => '¡Se superó el límite de usuarios! Tiene :current usuarios activos pero su licencia solo permite :max usuarios.',
    'limit_warning' => 'Te estás acercando a tu límite de usuarios. Actualmente utilizando :current de :max usuarios (se alcanzó el umbral del 90 %).',
  ),
  'status' => array(
    'active' => 'Activo',
    'expired' => 'Caducado',
    'revoked' => 'Revocado',
    'invalid' => 'Inválido',
    'pending' => 'Pendiente',
  ),
  'messages' => array(
    'activated' => 'Licencia activada exitosamente.',
    'validated' => 'Licencia validada exitosamente.',
    'removed' => 'Licencia eliminada exitosamente.',
    'validation_failed' => 'La validación de la licencia falló.',
  ),
);
