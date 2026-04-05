<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PsiManager - Sistema de Gestão para Psicólogos">
    <title>PsiManager</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuração do Tailwind - Tema Azul Claro -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-color': '#f0f9ff',
                        'card-bg': '#ffffff',
                        'primary': '#0ea5e9',
                        'primary-light': '#38bdf8',
                        'primary-dark': '#0284c7',
                        'secondary': '#6366f1',
                        'accent': '#06b6d4',
                        'text-main': '#0f172a',
                        'text-light': '#64748b',
                        'success': '#10b981',
                        'warning': '#f59e0b',
                        'danger': '#ef4444',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    borderRadius: {
                        'DEFAULT': '0.5rem',
                        'lg': '0.75rem',
                        'xl': '1rem',
                        '2xl': '1.5rem',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'fade-in-up': 'fadeInUp 0.5s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <?php
    // Detecta se está na raiz ou na pasta public
    $isRoot = !str_contains(str_replace('\\', '/', __DIR__), '/public');
    $basePath = '';
    if (basename(dirname($_SERVER['SCRIPT_FILENAME'])) !== 'public') {
        // Estamos na raiz
        $basePath = 'public/';
    }
    ?>

    <!-- Estilos Customizados -->
    <?php 
    $cssFile = $basePath . 'assets/css/style.css'; 
    $cssRealPath = dirname(__DIR__) . '/public/assets/css/style.css';
    $cacheV = file_exists($cssRealPath) ? filemtime($cssRealPath) : time();
    ?>
    <link rel="stylesheet" href="<?php echo $cssFile; ?>?v=<?php echo $cacheV; ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-bg-color text-text-main font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Desktop -->
        <aside class="sidebar w-64 text-white hidden md:flex flex-col shadow-2xl z-50">
            <!-- Logo -->
            <div class="p-6 text-center border-b border-white/10">
                <div class="flex items-center justify-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur">
                        <i class="fas fa-brain text-xl"></i>
                    </div>
                    <div class="text-left">
                        <h1 class="text-xl font-bold tracking-tight">PsiManager</h1>
                        <p class="text-xs opacity-70 font-medium">Gestão Inteligente</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <?php
                $currentPage = basename($_SERVER['PHP_SELF']);
                $menuItems = [
                    ['icon' => 'fa-home', 'label' => 'Dashboard', 'href' => 'index.php'],
                    ['icon' => 'fa-calendar-alt', 'label' => 'Agenda', 'href' => 'agenda.php'],
                    ['icon' => 'fa-users', 'label' => 'Meus Pacientes', 'href' => 'pacientes.php'],
                    ['icon' => 'fa-file-signature', 'label' => 'Anamneses', 'href' => 'anamneses.php'],
                    ['icon' => 'fa-chart-line', 'label' => 'Financeiro', 'href' => 'financeiro.php'],
                    ['icon' => 'fa-file-invoice-dollar', 'label' => 'Cobranças', 'href' => 'cobrancas.php'],
                    ['icon' => 'fa-calculator', 'label' => 'Contador', 'href' => 'contador.php'],
                    ['icon' => 'fa-brain', 'label' => 'Testes', 'href' => 'testes.php'],
                    ['icon' => 'fa-credit-card', 'label' => 'Asaas', 'href' => 'asaas_config.php'],
                ];
                
                foreach ($menuItems as $item):
                    $isActive = ($currentPage === $item['href']) ? 'active' : '';
                ?>
                <a href="<?php echo $item['href']; ?>" class="sidebar-link <?php echo $isActive; ?>">
                    <i class="fas <?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
                <?php endforeach; ?>
            </nav>
            
            <!-- Footer -->
            <div class="p-4 border-t border-white/10">
                <div class="text-center text-xs opacity-60">
                    <p>&copy; 2025 PsiManager</p>
                    <p class="mt-1">v3.0 Premium</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile Header -->
            <header class="md:hidden bg-gradient-to-r from-primary-dark via-primary to-accent text-white p-4 flex justify-between items-center shadow-lg z-40">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-brain text-sm"></i>
                    </div>
                    <h1 class="text-lg font-bold">PsiManager</h1>
                </div>
                <button id="mobile-menu-btn" class="w-10 h-10 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-bg-color p-4 md:p-6 lg:p-8 animate-fade-in">
