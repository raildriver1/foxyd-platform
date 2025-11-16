<?php
session_destroy();
setFlash('info', 'Вы вышли из системы');
redirect('/');
