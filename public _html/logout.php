<?php
// public_html/logout.php

// config.php को शामिल करें ताकि सेशन काम कर सके
require_once __DIR__ . '/config/config.php';

// सेशन को नष्ट करें
session_destroy();

// नए, साफ-सुथरे URL पर होमपेज पर भेजें
header('Location: ' . BASE_URL . '/');
exit();