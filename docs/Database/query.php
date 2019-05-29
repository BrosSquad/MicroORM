<?php
try {
    $users = User::query()
        ->select()// Select is optional - If there is not SELECT, every column will be selected
        ->where('email', '=', 'test@test.com')
        ->orWhere('some_colum', 'sql operator', 'value')
        ->orderBy('name')
        ->get();
}catch (PDOException $e) {
    // Do something with the error
}
