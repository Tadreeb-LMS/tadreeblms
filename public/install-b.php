<?php
// Legacy installer entrypoint kept for backward compatibility.
// Redirect all usage to the canonical installer page.
header('Location: install.php', true, 302);
exit;
