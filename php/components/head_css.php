<?php
// php/components/head_css.php

// Determine path depth
// If we are inside the 'php' folder or 'php/listings' folder, we need to go up levels.
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

if ($currentDir == 'php') {
    $path = '../';
} elseif ($currentDir == 'admin' || $currentDir == 'api' || $currentDir == 'auth' || $currentDir == 'bids' || $currentDir == 'components'|| $currentDir == 'config'|| $currentDir == 'farmer') {
    // If you have deeper folders like php/listings/create.php
    $path = '../../';
} else {
    // We are at root (index.php)
    $path = './';
}
?>

<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<link rel="stylesheet" href="<?php echo $path; ?>css/bootstrap/bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
<link rel="stylesheet" href="<?php echo $path; ?>css/core/root.css" />
<link rel="stylesheet" href="<?php echo $path; ?>css/core/default.css" />
<link rel="stylesheet" href="<?php echo $path; ?>css/components/header.css" />