<?php
// Configuration file to set session names based on the path

function init_user_session() {
    session_name('user_session');
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function init_admin_session() {
    session_name('admin_session');
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}
?>