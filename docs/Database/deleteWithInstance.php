<?php

$user = User::find($id);
if ($user) {
    // value returned from find() must be checked for null
    try {
        $success = $user->delete();
        // ...
    } catch (PDOException $e) {
        // Failed due to database error or primary key value was not set (NULL)
    }
}
