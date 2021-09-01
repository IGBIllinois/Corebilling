<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
        echo html::error_message("You do not have permission to view this page.","403 Forbidden");
        require_once 'includes/footer.inc.php';
        exit;
}
?>

<?php
require_once 'includes/footer.inc.php';

?>
