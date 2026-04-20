<?php

return array(
  'errors' => array(
    'no_license' => 'Aucune licence active trouvée. Veuillez activer une licence valide pour continuer à utiliser le système.',
    'expired' => 'Votre licence a expiré. Veuillez renouveler votre licence pour continuer à utiliser le système.',
    'revoked' => 'Votre permis a été révoqué. Veuillez contacter le support pour obtenir de l\'aide.',
    'invalid' => 'Votre licence n\'est pas valide. Veuillez activer une licence valide pour continuer.',
    'user_limit_exceeded' => 'Limite d\'utilisateurs dépassée. Votre licence autorise un maximum de :max utilisateurs et vous avez actuellement :current utilisateurs actifs.',
    'user_limit_reached' => 'Impossible de créer un nouvel utilisateur. Votre limite de licence de :max utilisateurs a été atteinte.',
  ),
  'warnings' => array(
    'no_license' => 'Aucune licence active trouvée. Veuillez activer une licence pour garantir un accès continu au système.',
    'expired' => 'Votre licence a expiré. Veuillez renouveler votre licence pour conserver toutes les fonctionnalités.',
    'invalid' => 'Votre licence n\'est pas valide ou a été révoquée. Veuillez contacter l\'assistance.',
    'limit_exceeded' => 'Limite d\'utilisateurs dépassée ! Vous avez :current utilisateurs actifs mais votre licence n\'autorise que :max utilisateurs.',
    'limit_warning' => 'Vous approchez de votre limite d\'utilisateurs. Vous utilisez actuellement :current sur :max utilisateurs (seuil de 90 % atteint).',
  ),
  'status' => array(
    'active' => 'Actif',
    'expired' => 'Expiré',
    'revoked' => 'Révoqué',
    'invalid' => 'Invalide',
    'pending' => 'En attente',
  ),
  'messages' => array(
    'activated' => 'Licence activée avec succès.',
    'validated' => 'Licence validée avec succès.',
    'removed' => 'Licence supprimée avec succès.',
    'validation_failed' => 'La validation de la licence a échoué.',
  ),
);
