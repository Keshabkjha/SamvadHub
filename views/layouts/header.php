<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="description" content="SamvadHub — Connect, share, and engage with your community. A modern social media platform.">
    <meta name="theme-color" content="#6C47FF">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= isset($data['page_title']) ? e($data['page_title']) . ' · SamvadHub' : 'SamvadHub' ?>">
    <meta property="og:description" content="Connect, share, and engage with your community.">
    <meta property="og:type" content="website">

    <!-- Stylesheets -->
    <link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/bootstrap/icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="/assets/images/icon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/assets/images/icon.ico">

    <title><?= isset($data['page_title']) ? e($data['page_title']) . ' · SamvadHub' : 'SamvadHub' ?></title>

    <!-- Dark mode: apply before paint to avoid flash -->
    <script>
        (function() {
            var theme = localStorage.getItem('sh-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>

<body>