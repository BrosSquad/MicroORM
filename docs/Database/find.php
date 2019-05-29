<?php

// This method is made to be called statically
// by using the __callStatic magic method
// Simulating the Laravel's ::find() method
$user = User::find($id);
if ($user !== NULL) {
    // User is found in database
}
