# JS and CSS Compressor for PHP

```php
<head>
    <?php Compressor::start() ?>
    <link rel="stylesheet" href="/css/file1.css">
    <link rel="stylesheet" href="/css/file2.css">
    <script src="/js/file1.js"></script>
    <script src="/js/file2.js"></script>
    <?php Compressor::stop() ?>
</head>
```

This will output something like:

```html
<head>
    <script src="/js/compressor/2puc4b6qbsu8ok00o0gk4k4ck_40z8o75yaqyokc44sgwkosgow.js"></script>
    <link href="/css/compressor/2nvxrxnwc6kgwoswwwg88o84c_xjvyk7x5cqowo48ckgs44g8g.css" rel="stylesheet">
</head>
```

The files have been concatenated, minified and cached.
