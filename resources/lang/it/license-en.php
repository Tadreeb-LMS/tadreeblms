<?php

return array(
  'errors' => array(
    'no_license' => 'Nessuna licenza attiva trovata. Si prega di attivare una licenza valida per continuare a utilizzare il sistema.',
    'expired' => 'La tua licenza è scaduta. Si prega di rinnovare la licenza per continuare a utilizzare il sistema.',
    'revoked' => 'La tua licenza è stata revocata. Si prega di contattare il supporto per assistenza.',
    'invalid' => 'La tua licenza non è valida. Attiva una licenza valida per continuare.',
    'user_limit_exceeded' => 'Limite utente superato. La tua licenza consente un massimo di :max utenti e attualmente hai :current utenti attivi.',
    'user_limit_reached' => 'Impossibile creare un nuovo utente. È stato raggiunto il limite di licenze di :max utenti.',
  ),
  'warnings' => array(
    'no_license' => 'Nessuna licenza attiva trovata. Si prega di attivare una licenza per garantire l\'accesso continuo al sistema.',
    'expired' => 'La tua licenza è scaduta. Si prega di rinnovare la licenza per mantenere la piena funzionalità.',
    'invalid' => 'La tua licenza non è valida o è stata revocata. Si prega di contattare l\'assistenza.',
    'limit_exceeded' => 'Limite utente superato! Hai :current utenti attivi ma la tua licenza consente solo :max utenti.',
    'limit_warning' => 'Ti stai avvicinando al limite di utenti. Attualmente utilizzo :current di :max utenti (soglia del 90% raggiunta).',
  ),
  'status' => array(
    'active' => 'Attivo',
    'expired' => 'Scaduto',
    'revoked' => 'Revocato',
    'invalid' => 'Non valido',
    'pending' => 'In sospeso',
  ),
  'messages' => array(
    'activated' => 'Licenza attivata con successo.',
    'validated' => 'Licenza convalidata con successo.',
    'removed' => 'Licenza rimossa con successo.',
    'validation_failed' => 'Convalida della licenza non riuscita.',
  ),
);
