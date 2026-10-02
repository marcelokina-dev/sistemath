<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Thunder Fight - Admin</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        :root {
            --thunder-red: #d32f2f;
            --thunder-dark: #1a1a1a;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--thunder-red) !important;
            letter-spacing: -0.5px;
        }

        /* Estilização customizada para o Select2 se ajustar ao seu layout */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
            min-height: calc(1.5em + 0.75rem + 2px);
        }

        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        #content {
            flex: 1;
            padding: 20px;
            transition: all 0.3s;
        }

        .sidebar label {
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-outline-light me-2 d-md-none"
                aria-label="Abrir Menu">
                <i class="fas fa-bars"></i>
            </button>

            <a class="navbar-brand d-flex align-items-center gap-2" href="/thunder/public/">
                <span class="text-white">THUNDER</span>
                <span style="color: var(--thunder-red);">FIGHT</span>
            </a>
            
            <div class="ms-auto d-flex align-items-center">
                </div>
        </div>
    </nav>

    <div class="main-wrapper">